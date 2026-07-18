<?php $options = Typecho_Widget::widget('Widget_Options'); ?>
<footer class="pyq-footer">
  <div>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(Typecho_Widget::widget('Widget_Options')->title ?: ''); ?></div>
  <div class="pyq-footer-line2">
    By <a href="https://typecho.org" target="_blank">Typecho</a> · Theme <a href="https://kx.ms/archives/pyq.html">KXpyq</a>
    <?php $icp = Typecho_Widget::widget('Widget_Options')->icp; if($icp): ?>
    · <a href="https://beian.miit.gov.cn/" target="_blank"><?php echo htmlspecialchars($icp); ?></a>
    <?php endif; ?>
  </div>
</footer>

</div><!-- /.pyq-wrap -->

<!-- 以下元素在PJAX目标外，换页不消失 -->

<!-- 全局变量供JS使用 -->
<script>window._pyqAvatar = <?php echo json_encode(Typecho_Widget::widget('Widget_Options')->avatar_url ?: (rtrim(Typecho_Widget::widget('Widget_Options')->siteUrl,'/') . '/usr/themes/kxpyq/assets/img/default-avatar.svg')); ?>;</script>
<script>window._pyqUsername = <?php echo json_encode(Typecho_Widget::widget('Widget_Options')->username ?: (Typecho_Widget::widget('Widget_Options')->title ?: '')); ?>;</script>

<?php if ($this->is('post') || $this->is('page')): ?>
<script src="<?php echo pyq_static('assets/lib/prism/prism.min.js'); ?>" defer></script>
<script src="<?php echo pyq_static('assets/lib/prism/prism-line-numbers.min.js'); ?>" defer></script>
<?php
// 按需加载语言包：从内容中检测用到的语言
$content = $this->content ?? '';
preg_match_all('/```(\w+)/', $content, $matches);
$langs = !empty($matches[1]) ? array_unique(array_filter($matches[1])) : ['javascript'];
$known = ['bash','c','cpp','css','go','html','java','javascript','json','kotlin','markup','php','python','ruby','rust','sql','swift','typescript'];
foreach ($langs as $l) {
  $l = strtolower($l);
  if (in_array($l, $known)) {
    echo '<script src="' . pyq_static('assets/lib/prism/prism-' . $l . '.min.js') . '" defer></script>' . "\n";
  }
}
?>
<?php endif; ?>

<!-- 音乐浮控 -->
<div class="pyq-music-float" id="pyq-music-float">
  <button class="pyq-music-float-btn" id="pyq-music-toggle" onclick="pyq.musicToggle()" aria-label="音乐控制">
    <svg viewBox="0 0 24 24" width="20" height="20"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55C7.79 13 6 14.79 6 17s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z" fill="currentColor"/></svg>
  </button>
  <button class="pyq-music-float-sub" id="pyq-music-pp" onclick="pyq.musicPP()" aria-label="播放/暂停">
    <svg viewBox="0 0 24 24" width="18" height="18"><path d="M8 5v14l11-7z" fill="currentColor"/></svg>
  </button>
  <button class="pyq-music-float-sub" id="pyq-music-locate" onclick="pyq.musicLocate()" aria-label="定位">
    <svg viewBox="0 0 24 24" width="18" height="18"><path d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0 0 13 3.06V1h-2v2.06A8.994 8.994 0 0 0 3.06 11H1v2h2.06A8.994 8.994 0 0 0 11 20.94V23h2v-2.06A8.994 8.994 0 0 0 20.94 13H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z" fill="currentColor"/></svg>
  </button>
</div>

<!-- 公告喇叭 -->
<?php if (!empty($options->notice_text)): ?>
<button class="pyq-bell" id="pyq-bell" aria-label="公告">
  <svg viewBox="0 0 1024 1024" width="20" height="20"><g transform="scale(-1,1) translate(-1024,0)"><path d="M932.6665 550.07l-0.01-0.012L473.9685 91.396l-0.006-0.004-27.166-27.166c-11.714-11.718-30.71-11.714-42.426 0-11.716 11.716-11.716 30.71 0 42.426l11.306 11.306L52.7385 784.126l-1.522-1.522c-11.714-11.718-30.71-11.714-42.426 0-11.716 11.716-11.714 30.71 0 42.426l190.194 190.184c5.858 5.86 13.536 8.788 21.212 8.788s15.356-2.93 21.212-8.788c11.716-11.716 11.714-30.71 0-42.426l-1.52-1.52 114.894-62.59 72.33 59.162c14.682 12.008 32.536 18.148 50.54 18.148 13.306 0 26.696-3.356 38.946-10.172l100.312-55.812c31.078-17.292 46.838-52.248 39.216-86.988l-17.344-79.05 267.308-145.618 11.308 11.306c5.858 5.86 13.536 8.788 21.212 8.788s15.356-2.93 21.212-8.788c11.716-11.716 11.716-30.71 0-42.426zM597.5225 845.874a19.828 19.828 0 0 1-9.784 21.702l-100.312 55.812a19.838 19.838 0 0 1-22.326-1.99l-53.434-43.706 172.25-93.834zM195.6605 927.038l-98.69-98.686L459.9085 162.188 660.8845 363.154 861.8605 564.12zM690.0405 194.782c76.742 0 139.178 62.43 139.178 139.166 0 16.568 13.432 30 30 30s30-13.432 30-30c0-109.82-89.352-199.166-199.178-199.166-16.568 0-30 13.432-30 30s13.43 30 30 30z" fill="currentColor"/><path d="M732.8705 0c-16.568 0-30 13.432-30 30s13.432 30 30 30C860.3165 60 964.0045 163.68 964.0045 291.122c0 16.568 13.432 30 30 30s30-13.432 30-30C1024.0045 130.596 893.4025 0 732.8705 0z" fill="currentColor"/></g></svg>
  <span class="pyq-bell-dot"></span>
</button>
<div id="pyq-notice-popup" class="pyq-notice-popup">
  <div class="pyq-notice-mask"></div>
  <div class="pyq-notice-box">
    <button class="pyq-notice-x" id="pyq-notice-close" aria-label="关闭">&times;</button>
    <div class="pyq-notice-head">
      <svg viewBox="0 0 24 24" width="18" height="18"><path d="M12 22c1.1 0 2-.9 2-2h-4a2 2 0 0 0 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z" fill="currentColor"/></svg>
      <span>公告</span>
    </div>
    <div class="pyq-notice-body"><?php echo $options->notice_text; ?></div>
  </div>
</div>
<?php endif; ?>

<!-- 返回顶部 -->
<button class="pyq-backtop" id="pyq-backtop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
  <svg viewBox="0 0 24 24"><path d="M7.41 15.41L12 10.83l4.59 4.58L18 14l-6-6-6 6z"/></svg>
</button>

<script src="<?php echo pyq_static('assets/js/app.min.js'); ?>?v=1.0.0" defer></script>

<!-- Service Worker 注册 -->
<script>
if('serviceWorker' in navigator){
  navigator.serviceWorker.register('/sw.js').catch(function(){});
}
</script>

<!-- 自定义CSS -->
<?php if (!empty($options->custom_css)): ?>
<style><?php echo $options->custom_css; ?></style>
<?php endif; ?>

<!-- 自定义JS -->
<?php if (!empty($options->custom_js)): ?>
<script><?php echo $options->custom_js; ?></script>
<?php endif; ?>

<!-- 底部信息 -->
<?php if (!empty($options->footer_html)): ?>
<?php echo $options->footer_html; ?>
<?php endif; ?>

</body>
</html>
