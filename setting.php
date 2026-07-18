<?php
/**
 * 开心朋友圈 Theme Settings Page
 * 仿照 wutoutu 的 Tab 式后台设置界面
 */

if (!class_exists('PyqEchoHtml')) {
    class PyqEchoHtml extends \Typecho\Widget\Helper\Layout {
        public function __construct($html) {
            $this->html($html);
            $this->start();
            $this->end();
        }
        public function start() {}
        public function end() {}
    }
}

function themeConfig($form) {
    $options = Helper::options();
    $themeUrl = $options->rootUrl . __TYPECHO_THEME_DIR__ . '/' . $options->theme . '/';
    ?>
</div><div class="clearfix"></div><div style="width: 100%;">
<link href="<?php echo $themeUrl; ?>assets/css/houtai.css?202606281157" rel="stylesheet">
<script src="<?php echo $themeUrl; ?>assets/js/houtai.js?202606281158"></script>

<!-- 头部：主题信息 -->
<div class="pyq-theme-header">
    <h2>开心朋友圈 主题设置</h2>
    <span class="pyq-version">朋友圈风格主题 · KXpyq v1.0.0</span>
</div>

<?php
// 备份功能
if (strpos($_SERVER['SCRIPT_NAME'], "options-theme.php")) {
    echo pyq_backup_panel();
}
?>

<?php
// ===== 左右容器开始 =====
// 左侧：Tab导航（col-mb-4 = 33%, col-tb-2 = 16.67%）
$form->addItem(new PyqEchoHtml('<div id="theme-page" class="row" style="width:100%;margin:0"><div class="col-mb-4 col-tb-2" style="position:sticky;top:60px"><div class="tab">'));
    $form->addItem(new PyqEchoHtml('<div data-item="pyq-basic" class="tabLinks active">基础设置</div>'));
    $form->addItem(new PyqEchoHtml('<div data-item="pyq-music" class="tabLinks">音乐设置</div>'));
    $form->addItem(new PyqEchoHtml('<div data-item="pyq-social" class="tabLinks">社交菜单</div>'));
    $form->addItem(new PyqEchoHtml('<div data-item="pyq-feature" class="tabLinks">功能设置</div>'));
    $form->addItem(new PyqEchoHtml('<div data-item="pyq-advanced" class="tabLinks">高级设置</div>'));
    $form->addItem(new PyqEchoHtml('</div></div>'));

// 右侧：配置内容（col-mb-8 = 66%, col-tb-10 = 83.33%）
$form->addItem(new PyqEchoHtml('<div class="col-mb-8 col-tb-10"><div class="card">'));

    // ========== 基础设置 ==========
    $form->addItem(new PyqEchoHtml('<div id="pyq-basic" class="pyq-tab-content active"><div class="pyq-section-title">基础设置</div>'));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text('avatar_url', NULL, '', _t('头像URL'), _t('用户头像，不是网站logo')));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text('cover_url', NULL, '', _t('封面图URL'), _t('顶部大图')));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text('cover_height', NULL, '280', _t('封面高度px'), _t('默认280，移动端自动缩小')));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text('username', NULL, '', _t('用户名'), _t('显示在头像旁边')));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text('signature', NULL, '', _t('个性签名'), _t('显示在头像下方')));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text('icp', NULL, '', _t('ICP备案号'), _t('显示在页脚')));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text('category_slug', NULL, 'shuoshuo', _t('说说分类slug'), _t('说说功能使用的分类标识')));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text('static_url', NULL, '', _t('静态资源URL'), _t('留空用本地，填CDN域名加速')));
    $form->addItem(new PyqEchoHtml('</div>'));

    // ========== 音乐设置 ==========
    $form->addItem(new PyqEchoHtml('<div id="pyq-music" class="pyq-tab-content"><div class="pyq-section-title">音乐设置</div>'));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text('bgm_url', NULL, '', _t('背景音乐URL'), _t('支持本地音频或外链')));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text('netease_id', NULL, '', _t('网易云歌曲ID'), _t('填入歌曲ID，自动通过代理获取播放链接')));
    $form->addItem(new PyqEchoHtml('</div>'));

    // ========== 社交与菜单 ==========
    $form->addItem(new PyqEchoHtml('<div id="pyq-social" class="pyq-tab-content"><div class="pyq-section-title">社交账号</div>'));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text('github', NULL, '', _t('GitHub'), _t('GitHub主页链接')));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text('weibo', NULL, '', _t('微博'), _t('微博主页链接')));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text('wechat', NULL, '', _t('微信'), _t('微信号')));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text('qq_num', NULL, '', _t('QQ'), _t('QQ号')));
    $form->addItem(new PyqEchoHtml('<div class="pyq-section-title">菜单设置</div>'));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea('menu_items', NULL, '首页|/
后台|/admin/
友情链接|/links.html', _t('菜单项'), _t('每行一个，格式：名称|链接')));
    $form->addItem(new PyqEchoHtml('</div>'));

    // ========== 功能设置 ==========
    $form->addItem(new PyqEchoHtml('<div id="pyq-feature" class="pyq-tab-content"><div class="pyq-section-title">头像源设置</div>'));
    $gravatars = new \Typecho\Widget\Helper\Form\Element\Select('gravatar_source', array(
        'https://cdn.helingqi.com/avatar/' => _t('禾令奇[默认]'),
        'https://weavatar.com/avatar/' => _t('weavatar'),
        'https://cn.cravatar.com/avatar/' => _t('Cravatar'),
        'https://www.gravatar.com/avatar/' => _t('gravatar官方'),
        'https://gravatar.loli.net/avatar/' => _t('loli.net'),
    ), 'https://cdn.helingqi.com/avatar/', _t('Gravatar头像源'), _t('选择头像CDN源'));
    $form->addInput($gravatars->multiMode());
    $form->addItem(new PyqEchoHtml('<div class="pyq-section-title">公告系统</div>'));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea('notice_text', NULL, '', _t('公告内容'), _t('支持HTML。右下角小喇叭点击弹窗展示，留空不显示喇叭')));
    $form->addItem(new PyqEchoHtml('<div class="pyq-section-title">文章版权</div>'));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea('copyright_text', NULL, '', _t('文章底部版权'), _t('支持HTML，留空不显示。可用变量：{title} {url} {author} {date}')));
    $form->addItem(new PyqEchoHtml('</div>'));

    // ========== 高级设置 ==========
    $form->addItem(new PyqEchoHtml('<div id="pyq-advanced" class="pyq-tab-content"><div class="pyq-section-title">主题样式</div>'));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text('theme_color', NULL, '07c160', _t('主题色'), _t('HEX色值，如 07c160（微信绿）')));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text('theme_max_width', NULL, '680', _t('最大宽度px'), _t('默认680px，朋友圈风格建议600-800')));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text('theme_radius', NULL, '12', _t('卡片圆角px'), _t('默认12px')));
    $form->addItem(new PyqEchoHtml('<div class="pyq-section-title">自定义代码</div>'));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea('custom_css', NULL, '', _t('自定义CSS'), _t('直接写CSS代码')));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea('custom_js', NULL, '', _t('自定义JS'), _t('直接写JS代码')));
    $form->addItem(new PyqEchoHtml('<div class="pyq-section-title">注入代码</div>'));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea('header_html', NULL, '', _t('头部信息'), _t('统计代码等，放在 &lt;/head&gt; 前')));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea('footer_html', NULL, '', _t('底部信息'), _t('备案号等，放在 &lt;/body&gt; 前')));
    $form->addItem(new PyqEchoHtml('</div>'));

    // ===== 底部统一保存按钮 =====
    $form->addItem(new PyqEchoHtml('<button type="submit" class="pyq-save-btn">保存设置</button>'));

    // 关闭右侧容器
    $form->addItem(new PyqEchoHtml('</div></div>'));

    // 关闭整体容器
    $form->addItem(new PyqEchoHtml('</div>'));
}

/**
 * 主题设置备份/还原面板
 */
function pyq_backup_panel() {
    $name = Helper::options()->theme;
    $db = Typecho_Db::get();
    $current = $db->fetchRow($db->select()->from('table.options')->where('name = ?', 'theme:' . $name));
    $data = @$current['value'];

    if (isset($_POST['type'])) {
        if ($_POST['type'] == '备份设置') {
            if ($db->fetchRow($db->select()->from('table.options')->where('name = ?', 'theme:' . $name . 'bf'))) {
                $db->query($db->update('table.options')->rows(array('value' => $data))->where('name = ?', 'theme:' . $name . 'bf'));
            } else {
                $db->query($db->insert('table.options')->rows(array('name' => 'theme:' . $name . 'bf', 'user' => '0', 'value' => $data)));
            }
            echo '<div class="pyq-notice">备份完成，正在刷新...<script>setTimeout(function(){location.href="' . Helper::options()->adminUrl('options-theme.php') . '"},1500);</script></div>';
        }
        if ($_POST['type'] == '还原设置') {
            $backup = $db->fetchRow($db->select()->from('table.options')->where('name = ?', 'theme:' . $name . 'bf'));
            if ($backup) {
                $db->query($db->update('table.options')->rows(array('value' => $backup['value']))->where('name = ?', 'theme:' . $name));
                echo '<div class="pyq-notice">还原完成，正在刷新...<script>setTimeout(function(){location.href="' . Helper::options()->adminUrl('options-theme.php') . '"},1500);</script></div>';
            } else {
                echo '<div class="pyq-notice">没有备份数据可还原</div>';
            }
        }
        if ($_POST['type'] == '删除备份') {
            if ($db->fetchRow($db->select()->from('table.options')->where('name = ?', 'theme:' . $name . 'bf'))) {
                $db->query($db->delete('table.options')->where('name = ?', 'theme:' . $name . 'bf'));
                echo '<div class="pyq-notice">备份已删除，正在刷新...<script>setTimeout(function(){location.href="' . Helper::options()->adminUrl('options-theme.php') . '"},1500);</script></div>';
            } else {
                echo '<div class="pyq-notice">备份不存在，无需删除</div>';
            }
        }
    }

    return '<div class="pyq-backup-bar"><form action="?' . $name . 'bf" method="post" style="display:inline"><input type="submit" name="type" class="btn btn-primary" value="备份设置" /> <input type="submit" name="type" class="btn" value="还原设置" /> <input type="submit" name="type" class="btn btn-danger" value="删除备份" /></form></div>';
}
?>
