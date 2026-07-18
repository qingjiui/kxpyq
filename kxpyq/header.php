<?php
$options = Typecho_Widget::widget('Widget_Options');
$db = Typecho_Db::get();
$avatar_url = $options->avatar_url ?: (rtrim($options->siteUrl,'/') . '/usr/themes/kxpyq/assets/img/default-avatar.svg');
$site_title = $options->title ?: '';
$username = $options->username ?: $site_title;
$cover_url = $options->cover_url ?: '';
$cover_h = intval($options->cover_height) ?: 280;
$sig = $options->signature ?: '';
$has_music = !empty($options->bgm_url) || !empty($options->netease_id);
$gd_available = function_exists('gd_info');
?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<?php
// SEO元标签
$seo_desc = $options->description ?: ($sig ?: $site_title);
$seo_img = $options->avatar_url ?: (rtrim($options->siteUrl, '/') . '/usr/themes/kxpyq/assets/img/default-avatar.svg');
$seo_url = $this->is('post') || $this->is('page') ? $this->permalink : rtrim($options->siteUrl, '/');
if ($this->is('post') || $this->is('page')) {
    $seo_title = htmlspecialchars($this->title . ' - ' . $site_title);
    $seo_desc = $this->description ?: mb_substr(strip_tags($this->content), 0, 200);
} else {
    $seo_title = htmlspecialchars($site_title);
}
?>
<meta name="description" content="<?php echo htmlspecialchars(mb_substr(strip_tags($seo_desc), 0, 200)); ?>">
<meta property="og:title" content="<?php echo htmlspecialchars($seo_title); ?>">
<meta property="og:description" content="<?php echo htmlspecialchars(mb_substr(strip_tags($seo_desc), 0, 200)); ?>">
<meta property="og:image" content="<?php echo htmlspecialchars($seo_img); ?>">
<meta property="og:url" content="<?php echo htmlspecialchars($seo_url); ?>">
<meta property="og:type" content="<?php echo ($this->is('post') || $this->is('page')) ? 'article' : 'website'; ?>">
<meta property="og:site_name" content="<?php echo htmlspecialchars($site_title); ?>">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="<?php echo htmlspecialchars($seo_title); ?>">
<meta name="twitter:description" content="<?php echo htmlspecialchars(mb_substr(strip_tags($seo_desc), 0, 200)); ?>">
<meta name="twitter:image" content="<?php echo htmlspecialchars($seo_img); ?>">
<link rel="alternate" type="application/rss+xml" title="<?php echo htmlspecialchars($site_title); ?> RSS" href="<?php echo rtrim($options->siteUrl, '/'); ?>/feed/">
<link rel="icon" href="<?php echo pyq_static('favicon.ico'); ?>" type="image/x-icon">
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#1a1a2e" media="(prefers-color-scheme: dark)">
<title><?php echo htmlspecialchars($site_title); ?></title>
<!-- DNS预解析 -->
<link rel="dns-prefetch" href="//api.injahow.cn">
<link rel="dns-prefetch" href="//gravatar.helingqi.com">
<link rel="dns-prefetch" href="//cdn.helingqi.com">
<link rel="preconnect" href="//api.injahow.cn" crossorigin>
<link rel="preconnect" href="//cdn.helingqi.com" crossorigin>
<?php if (!$gd_available): ?>
<script>window.__PYQ_NO_GD__=true;</script>
<?php endif; ?>
<script>
(function(){
  var p=null;
  try{p=localStorage.getItem('pyq-scheme')}catch(e){}
  if(!p){p=window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light'}
  if(p==='dark'){document.documentElement.classList.add('darkmode')}
})();
</script>
<link rel="stylesheet" href="<?php echo pyq_static('assets/css/style.min.css'); ?>?v=1.0.0">
<!-- 非关键CSS延迟加载 -->
<link rel="preload" href="<?php echo pyq_static('assets/lib/fancybox/fancybox.css'); ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
<link rel="preload" href="<?php echo pyq_static('assets/lib/prism/prism.css'); ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
<link rel="preload" href="<?php echo pyq_static('assets/lib/prism/prism-line-numbers.min.css'); ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="<?php echo pyq_static('assets/lib/fancybox/fancybox.css'); ?>"><link rel="stylesheet" href="<?php echo pyq_static('assets/lib/prism/prism.css'); ?>"><link rel="stylesheet" href="<?php echo pyq_static('assets/lib/prism/prism-line-numbers.min.css'); ?>"></noscript>
<?php $this->header('commentReply=&xmlRpc=&wlw=&rss2=&atom='); ?>
<style>
:root{--cover-h:<?php echo $cover_h; ?>px}
<?php $tc = $options->theme_color ?: '07c160'; $mw = $options->theme_max_width ?: '680'; $rd = $options->theme_radius ?: '12'; ?>
:root{--primary:#<?php echo htmlspecialchars($tc); ?>;--max-w:<?php echo intval($mw); ?>px;--card-radius:<?php echo intval($rd); ?>px}
<?php if($cover_url): ?>
.pyq-cover{background-image:url('<?php echo $cover_url; ?>')}
<?php endif; ?>
</style>
<!-- 头部信息 -->
<?php if (!empty($options->header_html)): ?>
<?php echo $options->header_html; ?>
<?php endif; ?>
</head>
<body>
<!-- Preloader -->
<div class="pyq-preloader" id="pyq-preloader"><div class="pyq-spinner"></div></div>

<!-- Top Bar (transparent) -->
<div class="pyq-topbar" id="pyq-topbar">
  <div class="pyq-topbar-left">
    <!-- 首页按钮 -->
    <a class="pyq-topbar-btn pyq-home-btn" href="<?php echo rtrim($options->siteUrl, '/'); ?>/" title="首页">
      <svg viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
    </a>
    <?php if($has_music): ?>
    <!-- 播放器 -->
    <div class="pyq-topbar-music" id="pyq-topbar-music">
      <button class="pyq-music-play-btn" id="pyq-music-btn" title="播放/暂停">
        <svg class="icon-play" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
        <svg class="icon-pause" viewBox="0 0 24 24" style="display:none"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
      </button>

    </div>
    <?php endif; ?>
  </div>
  <div class="pyq-topbar-right">
    <!-- 搜索按钮 -->
    <button class="pyq-topbar-btn" id="pyq-search-btn" title="搜索">
      <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
    </button>
    <!-- 多功能菜单 -->
    <div class="pyq-menu-wrap">
      <button class="pyq-topbar-btn" id="pyq-menu-btn" title="菜单">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
      </button>
      <div class="pyq-menu-popup" id="pyq-menu-popup">
        <?php
        $menu_text = $options->menu_items ?: "首页|/\n后台|/admin/\n友情链接|/links.html";
        foreach (explode("\n", trim($menu_text)) as $line):
          $line = trim($line);
          if (empty($line)) continue;
          $parts = explode('|', $line, 2);
          $m_name = trim($parts[0]);
          $m_url = trim($parts[1] ?? '');
          if (empty($m_url)) continue;
          // 相对链接补全域名
          if (strpos($m_url, '/') === 0) $m_url = rtrim($options->siteUrl, '/') . $m_url;
        ?>
        <a class="pyq-menu-item" href="<?php echo htmlspecialchars($m_url); ?>">
          <svg viewBox="0 0 24 24"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg>
          <span><?php echo htmlspecialchars($m_name); ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <!-- 深色模式 -->
    <button class="pyq-topbar-btn" id="pyq-dark-toggle" title="深色模式">
      <svg class="icon-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
      <svg class="icon-moon" viewBox="0 0 24 24" style="display:none"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
    </button>
  </div>
</div>

<?php if($has_music): ?>
<?php
  // 提取网易云歌曲ID
  $bgm_src = '';
  if(!empty($options->bgm_url)){
    $bgm_src = $options->bgm_url;
  } elseif(!empty($options->netease_id)){
    $bgm_src = rtrim($options->siteUrl,'/') . '/usr/themes/kxpyq/music-proxy.php?id=' . $options->netease_id;
  }
?>
<audio id="pyq-bgm" preload="auto" loop>
  <source src="<?php echo $bgm_src; ?>">
</audio>
<script>
window._pyqMusicName = <?php echo json_encode($options->music_name ?: '未知歌曲'); ?>;
window._pyqMusicArtist = <?php echo json_encode($options->music_artist ?: ''); ?>;
</script>
<?php endif; ?>

<!-- 搜索弹窗 -->
<div class="pyq-search-overlay" id="pyq-search-overlay">
  <div class="pyq-search-box">
    <input type="text" class="pyq-search-input" id="pyq-search-input" placeholder="搜索说说..." autocomplete="off">
    <button class="pyq-search-close" id="pyq-search-close">
      <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
    </button>
  </div>
  <div class="pyq-search-results" id="pyq-search-results"></div>
</div>

<div class="pyq-wrap" id="pyq-feed">
  <!-- 阅读进度条 -->
  <div class="pyq-progress" id="pyq-progress"><div class="pyq-progress-bar"></div></div>
  <!-- Cover -->
  <div class="pyq-cover"></div>
  <!-- Profile (叠在封面底部) -->
  <div class="pyq-profile">
    <div class="pyq-profile-row">
      <span class="pyq-profile-name"><?php echo htmlspecialchars($username); ?></span>
      <div class="pyq-profile-avatar"><img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="" loading="lazy"></div>
    </div>
    <?php if($sig): ?><div class="pyq-profile-sig"><?php echo htmlspecialchars($sig); ?></div><?php endif; ?>
  </div>
  

