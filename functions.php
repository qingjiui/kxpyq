<?php
/**
 * 开心朋友圈 - KXpyq Theme
 * 高仿微信朋友圈的 Typecho 主题
 *
 * @package KXpyq
 * @author 清酒
 * @version 1.0.0
 * @link https://qu.pw
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

// 引入设置页面
include("setting.php");


// 静态资源路径（切换CDN只需改这里或后台设置）
function pyq_static($path) {
    $options = Typecho_Widget::widget('Widget_Options');
    $base = rtrim($options->static_url ?: $options->themeUrl, '/');
    return $base . '/' . ltrim($path, '/');
}

function themeFields($layout) {
    $e = new \Typecho\Widget\Helper\Form\Element\Textarea('images', NULL, '', _t('图片'));
    $e->input->setAttribute('placeholder', '每行一个URL');
    $layout->addItem($e);

    $e = new \Typecho\Widget\Helper\Form\Element\Text('video_url', NULL, '', _t('视频URL'));
    $e->input->setAttribute('placeholder', '视频直链或外链地址');
    $layout->addItem($e);

    $layout->addItem(new \Typecho\Widget\Helper\Form\Element\Text('location', NULL, '', _t('位置')));

    $e = new \Typecho\Widget\Helper\Form\Element\Textarea('music_url', NULL, '', _t('音乐URL'));
    $e->input->setAttribute('placeholder', '网易云外链或本地音频');
    $layout->addItem($e);

    $layout->addItem(new \Typecho\Widget\Helper\Form\Element\Text('music_name', NULL, '', _t('歌曲名')));
    $layout->addItem(new \Typecho\Widget\Helper\Form\Element\Text('music_artist', NULL, '', _t('歌手')));
    $e = new \Typecho\Widget\Helper\Form\Element\Text('music_cover', NULL, '', _t('歌曲封面URL'));
    $e->input->setAttribute('placeholder', 'https://example.com/cover.jpg');
    $layout->addItem($e);

    $e = new \Typecho\Widget\Helper\Form\Element\Textarea('music_lrc', NULL, '', _t('LRC歌词'));
    $e->input->setAttribute('placeholder', '粘贴LRC歌词文本，或输入LRC文件链接');
    $e->input->setAttribute('rows', '3');
    $layout->addItem($e);

    $layout->addItem(new \Typecho\Widget\Helper\Form\Element\Radio('is_top', array('0'=>'否','1'=>'是'), '0', _t('置顶')));

    $e = new \Typecho\Widget\Helper\Form\Element\Text('link_url', NULL, '', _t('链接URL'));
    $e->input->setAttribute('placeholder', 'https://example.com');
    $layout->addItem($e);

    $layout->addItem(new \Typecho\Widget\Helper\Form\Element\Text('link_title', NULL, '', _t('链接标题')));
    $layout->addItem(new \Typecho\Widget\Helper\Form\Element\Text('link_desc', NULL, '', _t('链接描述')));
    $e = new \Typecho\Widget\Helper\Form\Element\Text('link_thumb', NULL, '', _t('链接缩略图'));
    $e->input->setAttribute('placeholder', 'https://example.com/thumb.jpg');
    $layout->addItem($e);

    $e = new \Typecho\Widget\Helper\Form\Element\Text('tag', NULL, '', _t('标签'));
    $e->input->setAttribute('placeholder', '如：广告、推荐、置顶等');
    $layout->addItem($e);
}

function pyq_render_comments_tree($comments, $cid = 0) {
    if (empty($comments)) return;
    $db = Typecho_Db::get();
    $owner = $db->fetchRow($db->select('mail')->from('table.users')->where('table.users.group = ?', 'administrator'));
    $owner_mail = $owner ? $owner['mail'] : '';
    $tree = [];
    foreach ($comments as $c) {
        $tree[intval($c['parent'])][] = $c;
    }
    function render_level($items, $tree, $owner_mail, $cid) {
        foreach ($items as $c) {
            $is_owner = !empty($owner_mail) && $c['mail'] === $owner_mail;
            echo '<div class="pyq-comment-item">';
            echo '<span class="pyq-comment-user">' . htmlspecialchars($c['author']);
            if ($is_owner) echo ' <span class="pyq-comment-badge">博主</span>';
            echo '</span>';
            if ($c['parent'] > 0) {
                foreach ($tree[0] ?? [] as $p) {
                    if ($p['coid'] == $c['parent']) {
                        echo ' <span class="pyq-comment-reply">回复</span>';
                        echo ' <span class="pyq-comment-target">@' . htmlspecialchars($p['author']) . '</span>';
                        break;
                    }
                }
            }
            echo '<span class="pyq-comment-text">：' . htmlspecialchars($c['text']) . '</span>';
            if (!empty($c['location'])) {
                echo ' <span class="pyq-comment-location">' . htmlspecialchars($c['location']) . '</span>';
            }
            if ($cid > 0) {
                echo ' <button class="pyq-reply-btn" onclick="pyq.replyTo(' . $cid . ',' . $c['coid'] . ',' . htmlspecialchars(json_encode($c['author'], JSON_HEX_APOS)) . ')">回复</button>';
            }
            echo '</div>';
            if (isset($tree[$c['coid']])) {
                render_level($tree[$c['coid']], $tree, $owner_mail, $cid);
            }
        }
    }
    if (isset($tree[0])) {
        render_level($tree[0], $tree, $owner_mail, $cid);
    }
}

function pyq_relative_time($ts) {
    $d = time() - $ts;
    if ($d < 60) return '刚刚';
    if ($d < 3600) return floor($d/60).'分钟前';
    if ($d < 86400) return floor($d/3600).'小时前';
    if ($d < 172800) return '昨天 '.date('H:i',$ts);
    if (date('Y')==date('Y',$ts)) return date('n月j日',$ts);
    return date('Y年n月j日',$ts);
}

/**
 * 处理音乐URL：网易云链接转代理，本地链接直出
 */
function pyq_resolve_music_url($url) {
    $url = trim($url);
    if (empty($url)) return '';
    // 匹配网易云链接: music.163.com/#/song?id=xxx 或 music.163.com/song?id=xxx
    if (preg_match('/music\.163\.com.*[?&]id=(\d+)/', $url, $m)) {
        return rtrim(Typecho_Widget::widget('Widget_Options')->siteUrl, '/') 
               . '/usr/themes/kxpyq/music-proxy.php?id=' . $m[1];
    }
    // 已经是代理链接或本地链接
    return $url;
}

// 话题标签处理
function pyq_parse_hashtags($text) {
    return preg_replace_callback('/#([^#]+)#/', function($m) {
        $tag = htmlspecialchars($m[1], ENT_QUOTES);
        return '<span class="pyq-hashtag" onclick="pyq.searchHashtag(\'' . $tag . '\')">#' . $tag . '#</span>';
    }, $text);
}

// 代码高亮处理
function pyq_parse_code($text) {
    // 多行代码块: ```language\ncode\n```
    $text = preg_replace_callback('/```(\w*)\n(.*?)```/s', function($m) {
        $lang = !empty($m[1]) ? strtolower($m[1]) : 'plaintext';
        $code = htmlspecialchars($m[2]);
        $lang_label = strtoupper($m[1] ?: 'CODE');
        return '<div class="pyq-code-block">'
            . '<div class="pyq-code-header">'
            . '<span class="pyq-code-lang">' . $lang_label . '</span>'
            . '<button class="pyq-code-copy" onclick="pyq.copyCode(this)">'
            . '<svg viewBox="0 0 24 24" width="14" height="14"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z" fill="currentColor"/></svg>'
            . '<span>复制</span>'
            . '</button>'
            . '</div>'
            . '<pre class="line-numbers"><code class="language-' . $lang . '">' . $code . '</code></pre>'
            . '</div>';
    }, $text);

    // 行内代码: `code`
    $text = preg_replace('/`([^`]+)`/', '<code class="pyq-inline-code">$1</code>', $text);

    return $text;
}

/**
 * Gravatar头像URL
 */
function pyq_gravatar_url($mail, $size = 40) {
    $options = Typecho_Widget::widget('Widget_Options');
    $source = $options->gravatar_source ?: 'https://cdn.helingqi.com/avatar/';
    $hash = md5(strtolower(trim($mail)));
    return rtrim($source, '/') . '/' . $hash . '?s=' . $size . '&d=mp&r=g';
}

/**
 * 格式化文章数据（供AJAX和PHP共用）
 */
function pyq_format_post_data($post, $db, $options) {
    $fields_rows = $db->fetchAll($db->select()->from('table.fields')
        ->where('table.fields.cid = ?', $post['cid']));
    
    $fields = [];
    foreach ($fields_rows as $f) {
        $fields[$f['name']] = $f['str_value'] ?: $f['int_value'] ?: '';
    }
    
    $images = [];
    if (!empty($fields['images'])) {
        foreach (explode("\n", trim($fields['images'])) as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            $d = json_decode($line, true);
            if (is_array($d)) $images = array_merge($images, $d);
            else $images[] = $line;
        }
    }
    
    $music = [];
    if (!empty($fields['music_url'])) {
        foreach (explode("\n", trim($fields['music_url'])) as $line) {
            $line = trim($line);
            if (!empty($line)) $music[] = pyq_resolve_music_url($line);
        }
    }
    $music_name = isset($fields['music_name']) ? $fields['music_name'] : '';
    $music_artist = isset($fields['music_artist']) ? $fields['music_artist'] : '';
    $music_cover = isset($fields['music_cover']) ? $fields['music_cover'] : '';
    $music_lrc = isset($fields['music_lrc']) ? trim($fields['music_lrc']) : '';
    
    // 自动获取歌曲信息
    if (!empty($music) && (empty($music_name) || empty($music_cover))) {
        $first_url = trim($fields['music_url'] ?? '');
        if (preg_match('/music\.163\.com.*[?&]id=(\d+)/', $first_url, $m)) {
            $api = @json_decode(@file_get_contents('https://api.injahow.cn/meting/?type=song&id=' . $m[1]), true);
            if (!empty($api[0])) {
                if (empty($music_name)) $music_name = $api[0]['name'] ?? '';
                if (empty($music_artist)) $music_artist = $api[0]['artist'] ?? '';
                if (empty($music_cover)) $music_cover = $api[0]['pic'] ?? '';
            }
        }
    }
    
    $likes = [];
    $like_field = $db->fetchRow($db->select()->from('table.fields')
        ->where('table.fields.cid = ?', $post['cid'])
        ->where('table.fields.name = ?', 'likes'));
    if ($like_field && !empty($like_field['str_value'])) {
        $likes = json_decode($like_field['str_value'], true) ?: [];
    }
    
    $plain = strip_tags($post['text']);
    $is_long = mb_strlen($plain) > 250;
    
    return [
        'cid'         => $post['cid'],
        'avatar'      => $options->avatar_url ?: (rtrim($options->siteUrl, '/') . '/usr/themes/kxpyq/assets/img/default-avatar.svg'),
        'title'       => $post['title'],
        'content'     => pyq_parse_code(pyq_parse_hashtags($post['text'])),
        'plain_text'  => $plain,
        'is_long'     => $is_long,
        'images'      => $images,
        'location'    => $fields['location'] ?? '',
        'music'       => $music,
        'music_name'  => $music_name,
        'music_artist'=> $music_artist,
        'music_cover' => $music_cover,
        'music_lrc'   => $music_lrc,
        'music_lrc_b64' => base64_encode($music_lrc),
        'is_top'      => isset($fields['is_top']) ? intval($fields['is_top']) : 0,
        'link_url'    => $fields['link_url'] ?? '',
        'link_title'  => $fields['link_title'] ?? '',
        'link_desc'   => $fields['link_desc'] ?? '',
        'link_thumb'  => $fields['link_thumb'] ?? '',
        'tag'         => $fields['tag'] ?? '',
        'video_url'   => $fields['video_url'] ?? '',
        'created'     => $post['created'],
        'date'        => date('Y-m-d H:i', $post['created']),
        'time_ago'    => pyq_relative_time($post['created']),
        'like_count'  => count($likes),
        'likes'       => $likes,
    ];
}

/**
 * 渲染单张说说卡片HTML
 */
function pyq_render_card($data, $avatar_url, $username, $liked = false) {
    $options = Typecho_Widget::widget('Widget_Options');
    $db = Typecho_Db::get();
    $siteUrl = rtrim($options->siteUrl, '/');
    $cid = $data['cid'];
    
    ob_start();
?>
<div class="pyq-card" data-cid="<?php echo $cid; ?>">
  <div class="pyq-card-left"><img src="<?php echo htmlspecialchars($avatar_url); ?>" alt=""></div>
  <div class="pyq-card-right">
    <div class="pyq-card-user">
      <span class="pyq-card-username"><?php echo htmlspecialchars($username); ?></span>
      <?php if ($data['is_top']): ?><span class="pyq-card-tag">置顶</span><?php endif; ?>
      <?php if (!empty($data['tag'])): ?><span class="pyq-card-tag"><?php echo htmlspecialchars($data['tag']); ?></span><?php endif; ?>
    </div>
    
    <?php if ($data['is_long']): ?>
    <div class="pyq-card-text collapsed" id="text-<?php echo $cid; ?>"><?php echo $data['content']; ?></div>
    <span class="pyq-card-expand" onclick="pyq.toggleText(this,<?php echo $cid; ?>)">展开全文</span>
    <?php else: ?>
    <div class="pyq-card-text"><?php echo $data['content']; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($data['images'])):
      $ic = count($data['images']);
      $gc = '';
      if ($ic == 1) $gc = 'g1';
      elseif ($ic == 2 || $ic == 4) $gc = 'g2 g4';
    ?>
    <div class="pyq-card-images <?php echo $gc; ?>">
      <?php foreach ($data['images'] as $img):
        $img_src = function_exists('gd_info')
          ? ($siteUrl . '/usr/themes/kxpyq/img-proxy.php?url=' . urlencode($img) . '&w=800')
          : $img;
      ?>
      <div class="pyq-card-img-item">
        <img class="lazy" src="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 1 1%22%3E%3C/svg%3E" data-src="<?php echo htmlspecialchars($img_src); ?>" data-original="<?php echo htmlspecialchars($img); ?>" alt="" onclick="pyq.openLightbox(this)">
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($data['video_url'])): ?>
    <div class="pyq-card-video">
      <video src="<?php echo htmlspecialchars($data['video_url']); ?>" preload="metadata" playsinline controls></video>
      <div class="play-btn" onclick="pyq.playVideo(this)"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($data['music'])):
      $mc = $data['music_cover'];
      $mn = $data['music_name'];
      $ma = $data['music_artist'];
      $mlrc = $data['music_lrc'];
    ?>
    <div class="pyq-music-wrap" data-bg="<?php echo htmlspecialchars($mc); ?>">
    <div class="pyq-music-card" data-src="<?php echo htmlspecialchars($data['music'][0]); ?>" data-name="<?php echo htmlspecialchars($mn); ?>" data-lrc="<?php echo base64_encode($mlrc); ?>">
      <?php if ($mc): ?>
      <div class="pyq-music-card-bg" style="background-image:url('<?php echo htmlspecialchars($mc); ?>')"></div>
      <?php endif; ?>
      <div class="pyq-music-card-left">
        <?php if ($mc): ?>
        <img src="<?php echo htmlspecialchars($mc); ?>" alt="">
        <?php else: ?>
        <div style="width:100%;height:100%;background:var(--card2);display:flex;align-items:center;justify-content:center">
          <svg viewBox="0 0 24 24" width="28" height="28" fill="var(--text2)"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>
        </div>
        <?php endif; ?>
      </div>
      <div class="pyq-music-card-right">
        <div class="pyq-music-card-info">
          <div class="pyq-music-card-title"><?php echo htmlspecialchars($mn ?: '未知歌曲'); ?></div>
          <div class="pyq-music-card-artist"><?php echo htmlspecialchars($ma ?: '未知歌手'); ?></div>
        </div>
        <div class="pyq-music-card-play" onclick="pyq.playCardMusic(this)">
          <svg class="icon-play" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
          <svg class="icon-pause" viewBox="0 0 24 24" style="display:none"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
        </div>
      </div>
      <div class="pyq-music-progress" onmousedown="pyq.seekMusic(event,this)" ontouchstart="pyq.seekMusic(event,this)">
        <div class="pyq-music-progress-bar"></div>
        <div class="pyq-music-progress-dot"></div>
      </div>
    </div>
      <div class="pyq-lrc-bar" onclick="pyq.toggleLrc(this)" title="展开歌词">
        <svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor"><path d="M16.59 8.59L12 13.17 7.41 8.59 6 10l6 6 6-6z"/></svg>
        <span>歌词</span>
      </div>
      <div class="pyq-music-lrc">
        <?php if ($mc): ?>
        <div class="pyq-lrc-bg" style="background-image:url('<?php echo htmlspecialchars($mc); ?>')"></div>
        <div class="pyq-lrc-overlay"></div>
        <?php endif; ?>
        <div class="pyq-lrc-lines"></div>
      </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($data['link_url'])): ?>
    <a class="pyq-card-link" href="<?php echo htmlspecialchars($data['link_url']); ?>" target="_blank" rel="noopener">
      <?php if (!empty($data['link_thumb'])): ?>
      <img class="pyq-card-link-thumb" src="<?php echo htmlspecialchars($data['link_thumb']); ?>" alt="">
      <?php endif; ?>
      <div class="pyq-card-link-info">
        <div class="pyq-card-link-title"><?php echo htmlspecialchars($data['link_title'] ?: $data['link_url']); ?></div>
        <?php if (!empty($data['link_desc'])): ?>
        <div class="pyq-card-link-desc"><?php echo htmlspecialchars($data['link_desc']); ?></div>
        <?php endif; ?>
      </div>
    </a>
    <?php endif; ?>
    
    <?php if (!empty($data['location'])): ?>
    <div class="pyq-card-location">
      <svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
      <a href="https://uri.amap.com/search?keyword=<?php echo urlencode($data['location']); ?>" target="_blank" rel="noopener" class="pyq-location-link"><?php echo htmlspecialchars($data['location']); ?></a>
    </div>
    <?php endif; ?>
    
    <!-- 时间 + 操作按钮 -->
    <div class="pyq-card-actions">
      <span class="pyq-card-time"><?php echo $data['date']; ?></span>
      <div class="pyq-card-btns">
        <div class="pyq-reaction-wrap">
          <div class="pyq-reaction-picker" id="rpicker-<?php echo $cid; ?>">
            <span onclick="pyq.react(<?php echo $cid; ?>,'👍',this)">👍</span>
            <span onclick="pyq.react(<?php echo $cid; ?>,'❤️',this)">❤️</span>
            <span onclick="pyq.react(<?php echo $cid; ?>,'😂',this)">😂</span>
            <span onclick="pyq.react(<?php echo $cid; ?>,'😮',this)">😮</span>
            <span onclick="pyq.react(<?php echo $cid; ?>,'😢',this)">😢</span>
          </div>
          <button class="pyq-card-action-btn<?php echo $liked ? ' liked' : ''; ?>" onclick="pyq.toggleReaction(this,<?php echo $cid; ?>)">
            <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
            <span><?php echo $data['like_count'] > 0 ? $data['like_count'] : '赞'; ?></span>
          </button>
        </div>
        <button class="pyq-card-action-btn" onclick="pyq.toggleComment(<?php echo $cid; ?>)">
          <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>评论</span>
        </button>
        <button class="pyq-card-action-btn" onclick="pyq.sharePost(<?php echo $cid; ?>, <?php echo htmlspecialchars(json_encode($options->title, JSON_UNESCAPED_UNICODE), ENT_QUOTES); ?>, <?php echo htmlspecialchars(json_encode(mb_substr($data['plain_text'], 0, 50), JSON_UNESCAPED_UNICODE), ENT_QUOTES); ?>)">
          <svg viewBox="0 0 24 24"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/></svg>
          <span>分享</span>
        </button>
        <button class="pyq-card-more" onclick="pyq.toggleActionBtns(this)">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
    
    <!-- 赞/评论区域 -->
    <div class="pyq-social<?php echo (!empty($data['likes']) || !empty($data['comments'])) ? ' has-content' : ''; ?>" id="social-<?php echo $cid; ?>">
      <?php if (!empty($data['likes'])): ?>
      <div class="pyq-likes">
        <div class="pyq-likes-icon"><svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg></div>
        <div class="pyq-likes-list">
          <?php
            $all_guest = true;
            foreach ($data['likes'] as $lk) { if ($lk['name'] !== '访客') { $all_guest = false; break; } }
            if ($all_guest):
          ?>
          <span><?php echo count($data['likes']); ?>位访客点赞</span>
          <?php else: foreach ($data['likes'] as $lk): ?>
          <span><?php echo htmlspecialchars($lk['name'] ?: '访客'); ?></span>
          <?php endforeach; endif; ?>
        </div>
      </div>
      <?php endif; ?>
      
      <?php if (!empty($data['likes']) && !empty($data['comments'])): ?><div class="pyq-likes-divider"></div><?php endif; ?>
      
      <?php if (!empty($data['comments'])):
        $total_comments = count($data['comments']);
        $has_more_comments = $total_comments > 5;
      ?>
      <div class="pyq-comments" id="comments-<?php echo $cid; ?>">
        <?php foreach ($data['comments'] as $i => $c): ?>
        <div class="pyq-comment-item<?php echo ($has_more_comments && $i >= 5) ? ' pyq-comment-hidden' : ''; ?>">
          <span class="pyq-comment-user"><?php echo htmlspecialchars($c['author']); ?>
            <?php 
            $owner = $db->fetchRow($db->select('mail')->from('table.users')->where('table.users.group = ?', 'administrator'));
            if ($owner && $c['mail'] === $owner['mail']): 
            ?>
            <span class="pyq-comment-badge">博主</span>
            <?php endif; ?>
          </span>
          <?php if ($c['parent'] > 0): 
            $pa = $db->fetchRow($db->select('author')->from('table.comments')->where('table.comments.coid = ?', $c['parent']));
          ?>
          <span class="pyq-comment-reply">回复</span>
          <span class="pyq-comment-target"><?php echo htmlspecialchars($pa ? $pa['author'] : '匿名'); ?></span>
          <?php endif; ?>
          <span class="pyq-comment-text">：<?php echo htmlspecialchars($c['text']); ?></span>
          <?php if (!empty($c['location'])): ?>
          <span class="pyq-comment-location"><?php echo htmlspecialchars($c['location']); ?></span>
          <?php endif; ?>
          <button class="pyq-reply-btn" onclick="pyq.replyTo(<?php echo $cid; ?>,<?php echo $c['coid']; ?>,<?php echo htmlspecialchars(json_encode($c['author'], JSON_UNESCAPED_UNICODE), ENT_QUOTES); ?>)">回复</button>
        </div>
        <?php endforeach; ?>
        <?php if ($has_more_comments): ?>
        <div class="pyq-comments-more" onclick="pyq.showAllComments(this, <?php echo $cid; ?>)">展开全部<?php echo $total_comments; ?>条评论</div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      
      <!-- 评论输入框 -->
      <div class="pyq-comment-box" id="cbox-<?php echo $cid; ?>">
        <div class="pyq-guest-fields" id="guest-<?php echo $cid; ?>">
          <input type="text" class="pyq-guest-name" placeholder="昵称（必填）" maxlength="20">
          <input type="email" class="pyq-guest-email" placeholder="邮箱（必填）" maxlength="50">
        </div>
        <div class="pyq-comment-input-wrap">
          <textarea class="pyq-comment-input" placeholder="写评论..." maxlength="500"></textarea>
          <button class="pyq-emoji-btn" onclick="pyq.toggleEmoji(this)"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M8 14s1.5 2 4 2 4-2 4-2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="9" cy="9.5" r="1" fill="currentColor"/><circle cx="15" cy="9.5" r="1" fill="currentColor"/></svg></button>
        </div>
        <div class="pyq-emoji-panel" id="emoji-<?php echo $cid; ?>"></div>
        <button class="pyq-comment-send" onclick="pyq.sendComment(this,<?php echo $cid; ?>)">发送</button>
      </div>
    </div>
  </div>
</div>
<?php
    return ob_get_clean();
}

