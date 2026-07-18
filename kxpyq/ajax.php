<?php
/**
 * KXpyq Theme AJAX Handler
 * 
 * 独立的AJAX处理文件，不依赖Typecho的插件钩子系统
 */

// 加载Typecho核心
if (!defined('__TYPECHO_ROOT_DIR__')) {
    define('__TYPECHO_ROOT_DIR__', dirname(dirname(dirname(dirname(__FILE__)))));
}
if (!@include_once __TYPECHO_ROOT_DIR__ . '/config.inc.php') {
    http_response_code(500);
    echo json_encode(['code' => 1, 'msg' => 'Config not found']);
    exit;
}
\Typecho\Common::init();

// 初始化插件系统（加载所有已激活插件的钩子）
$pluginsActivated = $db->fetchRow($db->select('value')->from('table.options')->where('name = ?', 'plugins'));
if ($pluginsActivated) {
    \Typecho\Plugin::init(json_decode($pluginsActivated['value'], true));
}

// 加载主题函数
require_once __TYPECHO_ROOT_DIR__ . '/usr/themes/kxpyq/functions.php';

$db = \Typecho\Db::get();
$request = \Typecho\Request::getInstance();
$action = $request->get('act', '');

header('Content-Type: application/json; charset=utf-8');

switch ($action) {
    case 'load':
        pyq_ajax_load($request, $db);
        break;
    case 'like':
        pyq_ajax_like($request, $db);
        break;
    case 'comment':
        pyq_ajax_comment($request, $db);
        break;
    default:
        echo json_encode(['code' => 1, 'msg' => 'Unknown action']); break;
}

exit;

/**
 * 加载更多说说
 */
function pyq_ajax_load($request, $db) {
    $page = max(1, intval($request->get('page', 1)));
    $limit = min(20, max(1, intval($request->get('limit', 10))));
    $offset = ($page - 1) * $limit;
    
    $options = \Widget\Options::alloc();
    $category_slug = $options->category_slug ?: 'shuoshuo';
    
    $category = $db->fetchRow($db->select()->from('table.metas')
        ->where('table.metas.type = ?', 'category')
        ->where('table.metas.slug = ?', $category_slug));
    
    if (!$category) {
        echo json_encode(['code' => 0, 'data' => [], 'hasMore' => false]);
        return;
    }
    
    $cid_list = $db->fetchAll($db->select('table.relationships.cid')
        ->from('table.relationships')
        ->where('table.relationships.mid = ?', $category['mid']));
    
    $cids = array_column($cid_list, 'cid');
    
    if (empty($cids)) {
        echo json_encode(['code' => 0, 'data' => [], 'hasMore' => false]);
        return;
    }
    
    $posts = $db->fetchAll($db->select()->from('table.contents')
        ->where('table.contents.type = ?', 'post')
        ->where('table.contents.status = ?', 'publish')
        ->where('table.contents.parent = 0')
        ->where('table.contents.cid IN ?', $cids)
        ->order('table.contents.created', \Typecho\Db::SORT_DESC)
        ->limit($limit)
        ->offset($offset));
    
    // 置顶排序：置顶帖子排最前面
    $pinned_cids = [];
    $top_rows = $db->fetchAll($db->select('table.fields.cid')->from('table.fields')
        ->where('table.fields.name = ?', 'is_top')
        ->where('table.fields.str_value = ?', '1')
        ->where('table.fields.cid IN ?', $cids));
    foreach ($top_rows as $r) { $pinned_cids[] = $r['cid']; }
    
    $pinned = []; $normal = [];
    foreach ($posts as $post) {
        if (in_array($post['cid'], $pinned_cids)) $pinned[] = $post;
        else $normal[] = $post;
    }
    $posts = array_merge($pinned, $normal);
    
    $data = [];
    foreach ($posts as $post) {
        $data[] = pyq_format_post_data($post, $db, $options);
    }
    
    $total = $db->fetchRow($db->select(array('COUNT(1)' => 'total'))
        ->from('table.contents')
        ->where('table.contents.type = ?', 'post')
        ->where('table.contents.status = ?', 'publish')
        ->where('table.contents.parent = 0')
        ->where('table.contents.cid IN ?', $cids));
    
    $hasMore = ($offset + $limit) < $total['total'];
    
    echo json_encode(['code' => 0, 'data' => $data, 'hasMore' => $hasMore]);
}

/**
 * 点赞
 */
function pyq_ajax_like($request, $db) {
    $cid = intval($request->get('cid', 0));
    if ($cid <= 0) {
        echo json_encode(['code' => 1, 'msg' => 'Invalid cid']);
        return;
    }
    
    $ip = pyq_get_ip();
    $cookie_key = 'pyq_like_' . $cid;
    $is_liked = isset($_COOKIE[$cookie_key]);
    
    $like_field = $db->fetchRow($db->select()->from('table.fields')
        ->where('table.fields.cid = ?', $cid)
        ->where('table.fields.name = ?', 'likes'));
    
    $likes = [];
    if ($like_field && !empty($like_field['str_value'])) {
        $likes = json_decode($like_field['str_value'], true) ?: [];
    }
    
    if ($is_liked) {
        $likes = array_filter($likes, function($l) use ($ip) {
            return !isset($l['ip']) || $l['ip'] !== $ip;
        });
        $likes = array_values($likes);
        setcookie($cookie_key, '', time() - 3600, '/');
    } else {
        $like_name = !empty($_COOKIE['__typecho_remember_author']) ? $_COOKIE['__typecho_remember_author'] : '访客';
        $likes[] = ['ip' => $ip, 'name' => $like_name, 'time' => time()];
        setcookie($cookie_key, '1', time() + 86400 * 365, '/');
    }
    
    $likes_json = json_encode($likes);
    
    if ($like_field) {
        $db->query($db->update('table.fields')
            ->rows(['str_value' => $likes_json])
            ->where('table.fields.cid = ?', $cid)
            ->where('table.fields.name = ?', 'likes'));
    } else {
        $db->query($db->insert('table.fields')
            ->rows([
                'cid' => $cid, 'name' => 'likes', 'type' => 'str',
                'str_value' => $likes_json, 'int_value' => 0, 'float_value' => 0,
            ]));
    }
    
    echo json_encode([
        'code' => 0, 'liked' => !$is_liked, 'count' => count($likes), 'likes' => $likes,
    ]);
}

/**
 * 获取IP归属地
 * 优先使用本地 ip2region.xdb（如存在），否则回退 ip-api.com
 */
function pyq_get_ip_location($ip) {
    if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1') return '';
    if (strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0) return '';
    
    // 尝试本地 ip2region
    $xdb = __DIR__ . '/lib/ip2region/ip2region.xdb';
    if (file_exists($xdb) && filesize($xdb) > 1024) {
        $result = pyq_ip2region_search($ip, $xdb);
        if ($result) return $result;
    }
    
    // 回退 ip-api.com
    $url = "http://ip-api.com/json/{$ip}?lang=zh-CN&fields=regionName,city";
    $ctx = stream_context_create(['http'=>['timeout'=>3]]);
    $data = @file_get_contents($url, false, $ctx);
    if ($data) {
        $json = json_decode($data, true);
        if ($json && isset($json['regionName'])) {
            $loc = $json['regionName'];
            if (!empty($json['city']) && $json['city'] !== $json['regionName']) {
                $loc .= ' · ' . $json['city'];
            }
            return $loc;
        }
    }
    return '';
}

/**
 * ip2region 本地查询
 */
function pyq_ip2region_search($ip, $xdb) {
    try {
        require_once __DIR__ . '/lib/ip2region/xdb/Searcher.class.php';
        $searcher = \ip2region\xdb\Searcher::newWithFileOnly(\ip2region\xdb\IPv4::default(), $xdb);
        $region = $searcher->search($ip);
        if ($region) {
            // ip2region 返回格式: 国家|区域|省份|城市|ISP
            $parts = explode('|', $region);
            $province = trim($parts[2] ?? '');
            $city = trim($parts[3] ?? '');
            if ($province === '0' || $province === '') return '';
            // 去掉省/市/自治区后缀使显示更简洁
            $province = preg_replace('/(省|市|自治区|特别行政区|维吾尔|回族|壮族|藏族|土家族|苗族|彝族|蒙古族|朝鲜族)$/u', '', $province);
            if ($city && $city !== '0' && $city !== $province) {
                return $province . ' · ' . preg_replace('/市$/u', '', $city);
            }
            return $province;
        }
    } catch (\Exception $e) {}
    return '';
}

/**
 * 评论
 */
function pyq_ajax_comment($request, $db) {
    $cid = intval($request->get('cid', 0));
    $author = trim($request->get('author', ''));
    $mail = trim($request->get('mail', ''));
    $url = trim($request->get('url', ''));
    $text = trim($request->get('text', ''));
    $parent = intval($request->get('parent', 0));
    
    if ($cid <= 0 || empty($text)) {
        echo json_encode(['code' => 1, 'msg' => '缺少必要参数']);
        return;
    }
    
    if (empty($author)) $author = '匿名访客';
    
    if (empty($mail) || !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['code' => 1, 'msg' => '请填写有效邮箱']);
        return;
    }
    
    // 获取IP归属地
    $ip = pyq_get_ip();
    $ip_location = pyq_get_ip_location($ip);
    
    $data = [
        'cid' => $cid, 'created' => time(),
        'author' => htmlspecialchars($author), 'authorId' => 0, 'ownerId' => 0,
        'mail' => htmlspecialchars($mail), 'url' => htmlspecialchars($url),
        'ip' => $ip, 'agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'text' => htmlspecialchars($text), 'type' => 'comment', 'status' => 'approved',
        'parent' => $parent, 'location' => $ip_location,
    ];
    
    $result = $db->query($db->insert('table.comments')->rows($data));
    $coid = $result;
    
    $post = $db->fetchRow($db->select('commentsNum')->from('table.contents')
        ->where('table.contents.cid = ?', $cid));
    if ($post) {
        $db->query($db->update('table.contents')
            ->rows(['commentsNum' => $post['commentsNum'] + 1])
            ->where('table.contents.cid = ?', $cid));
    }
    
    // 检查是否为博主
    $is_owner = false;
    $owner = $db->fetchRow($db->select('mail')->from('table.users')->where('table.users.group=?', 'administrator'));
    if ($owner && $mail === $owner['mail']) {
        $is_owner = true;
    }
    
    // 通过Typecho插件钩子触发邮件通知（CommentNotifier等插件自动响应）
    $email_sent = false;
    try {
        // 加载刚插入的评论
        $feedback = Typecho_Widget::widget('Widget_Base_Comments');
        $feedback->push($db->fetchRow($db->select()->from('table.comments')->where('coid = ?', $coid)));
        // 通过CommentNotifier发送邮件通知
        $email_sent = false;
        try {
            // 查询文章作者uid，补全ownerId（comments表该字段为0）
            $post_row = $db->fetchRow($db->select('authorId')->from('table.contents')->where('cid = ?', $cid));
            if ($post_row) {
                $feedback->ownerId = intval($post_row['authorId']);
            }
            require_once __DIR__ . '/../../plugins/CommentNotifier/Plugin.php';
            \TypechoPlugin\CommentNotifier\Plugin::refinishComment($feedback);
            $email_sent = true;
        } catch (\Exception $e) {
            error_log('[KXpyq CommentNotifier] ' . $e->getMessage());
        }
    } catch (\Exception $e) {
        error_log('[KXpyq CommentNotifier] ' . $e->getMessage());
    }

    echo json_encode([
        'code' => 0,
        'data' => [
            'coid' => $coid, 'author' => htmlspecialchars($author),
            'text' => htmlspecialchars($text), 'parent' => $parent,
            'created' => time(), 'time' => '刚刚',
            'location' => $ip_location,
            'is_owner' => $is_owner,
            'email_sent' => $email_sent,
        ],
    ]);
}

function pyq_get_ip() {
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key])[0];
            if (filter_var(trim($ip), FILTER_VALIDATE_IP)) return trim($ip);
        }
    }
    return '0.0.0.0';
}

/**
 * 搜索
 */
function pyq_ajax_search($request, $db) {
    $q = trim($request->get('q', ''));
    if (empty($q) || mb_strlen($q) < 2) {
        echo json_encode([]);
        return;
    }
    
    $options = \Typecho\Widget::widget('Widget_Options');
    $slug = $options->category_slug ?: 'shuoshuo';
    
    // 获取说说分类的cid
    $cat = $db->fetchRow($db->select()->from('table.metas')
        ->where('table.metas.type=?','category')
        ->where('table.metas.slug=?',$slug));
    
    $cids = [];
    if ($cat) {
        $cid_rows = $db->fetchAll($db->select('table.relationships.cid')
            ->from('table.relationships')
            ->where('table.relationships.mid=?',$cat['mid']));
        $cids = array_column($cid_rows,'cid');
    }
    
    if (empty($cids)) {
        echo json_encode([]);
        return;
    }
    
    // 搜索
    $like_q = '%' . $q . '%';
    $posts = $db->fetchAll($db->select()
        ->from('table.contents')
        ->where('table.contents.type=?','post')
        ->where('table.contents.status=?','publish')
        ->where('table.contents.parent=0')
        ->where('table.contents.cid IN ?',$cids)
        ->where('table.contents.text LIKE ? OR table.contents.title LIKE ?', $like_q, $like_q)
        ->order('table.contents.created',\Typecho\Db::SORT_DESC)
        ->limit(20));
    
    $results = [];
    foreach ($posts as $post) {
        $text = strip_tags($post['text']);
        $excerpt = mb_substr($text, 0, 100) . (mb_strlen($text) > 100 ? '...' : '');
        $results[] = [
            'cid' => $post['cid'],
            'title' => $post['title'] ?: mb_substr($text, 0, 30),
            'excerpt' => $excerpt,
            'permalink' => $options->siteUrl . $post['cid'] . '.html',
        ];
    }
    
    echo json_encode($results);
}

/**
 * 阅读计数
 */
