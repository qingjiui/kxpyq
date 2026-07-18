<?php
/**
 * 关于我
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
$options = Typecho_Widget::widget('Widget_Options');
$stat = Typecho_Widget::widget('Widget_Stat');

// 社交平台配置：[名称, SVG路径, 类型:link/account]
$socials = [
  'github'   => ['GitHub',   'M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22', 'link'],
  'twitter'  => ['Twitter',  'M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z', 'link'],
  'telegram' => ['Telegram', 'M21.198 2.433a2.242 2.242 0 0 0-1.022.215l-8.609 3.33c-2.068.8-4.133 1.598-5.724 2.21a405.15 405.15 0 0 1-2.849 1.09c-.42.147-.99.332-1.473.901-.728.859-.416 1.907.168 2.475l3.935 3.376c.2.175.454.27.68.27.195 0 .388-.068.524-.175l2.463-1.904 3.377 2.613c.2.156.432.238.664.238.352 0 .688-.188.86-.515l3.425-6.368c.148-.272.156-.59.022-.872a1.136 1.136 0 0 0-.502-.536L9.2 12.44l7.452-6.58c.236-.205.263-.55.06-.786a.557.557 0 0 0-.786-.06l-7.452 6.58L2.14 8.238a1.136 1.136 0 0 0-.844-.13L20.14 2.633a1.136 1.136 0 0 1 .844.13c.258.168.402.438.402.726 0 .288-.144.558-.402.726l-3.425 6.368c-.148.272-.156.59-.022.872.134.28.398.465.698.465h.001z', 'link'],
  'weibo'    => ['微博',    'M20.196 9.4a1.136 1.136 0 0 0-1.87-.906c-.822.28-1.28 1.12-1.065 1.9.217.78 1.052 1.185 1.874.903.82-.28 1.277-1.118 1.06-1.897zM10.098 20.323c-3.977.391-7.414-1.406-7.672-4.02-.259-2.609 2.759-5.047 6.74-5.441 3.979-.394 7.413 1.404 7.671 4.018.259 2.6-2.759 5.049-6.739 5.443z', 'link'],
  'qq_num'   => ['QQ',      'M12.003 2c-2.265 0-6.29 1.364-6.29 7.325v1.195S3.55 14.96 3.55 17.474c0 .665.17 1.025.395 1.025.215 0 .52-.36 1.135-.965.215.595.77 1.73 2.155 2.555-.475.17-.965.305-1.395.305-.395 0-.715-.065-.965-.215-.215-.135-.36-.36-.36-.625 0-.395.36-.715.79-.715.215 0 .395.065.555.17.36.215.715.36 1.065.36.395 0 .79-.17 1.065-.36.16-.105.345-.17.555-.17.43 0 .79.32.79.715 0 .265-.145.49-.36.625-.25.15-.57.215-.965.215-.43 0-.92-.135-1.395-.305 1.385-.825 1.94-1.96 2.155-2.555.615.605.92.965 1.135.965.225 0 .395-.36.395-1.025 0-2.514-2.165-6.954-2.165-6.954V9.325C16.293 3.364 14.268 2 12.003 2z', 'account'],
  'wechat'   => ['微信',    'M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 0 1 .213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 0 0 .167-.054l1.903-1.114a.864.864 0 0 1 .717-.098 10.16 10.16 0 0 0 2.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178A1.17 1.17 0 0 1 4.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178 1.17 1.17 0 0 1-1.162-1.178c0-.651.52-1.18 1.162-1.18z', 'account'],
];
$has_social = false;
foreach ($socials as $key => $info) {
  if (!empty($options->$key)) { $has_social = true; break; }
}
?>

<div class="pyq-wrap">
<?php while ($this->next()): ?>

  <!-- 数据统计 -->
  <div class="pyq-about-stats">
    <div class="pyq-about-stat-item">
      <div class="pyq-about-stat-num"><?php echo $stat->myPublishedPostsNum; ?></div>
      <div class="pyq-about-stat-label">文章</div>
    </div>
    <div class="pyq-about-stat-item">
      <div class="pyq-about-stat-num"><?php echo $stat->myPublishedCommentsNum; ?></div>
      <div class="pyq-about-stat-label">评论</div>
    </div>
    <div class="pyq-about-stat-item">
      <div class="pyq-about-stat-num"><?php echo $stat->categoriesNum; ?></div>
      <div class="pyq-about-stat-label">分类</div>
    </div>
    <div class="pyq-about-stat-item">
      <div class="pyq-about-stat-num"><?php echo max(1, floor((time() - strtotime($stat->firstPostDate ?: 'now')) / 86400)); ?></div>
      <div class="pyq-about-stat-label">天</div>
    </div>
  </div>

  <!-- 关于正文 -->
  <div class="pyq-card">
    <div class="pyq-card-right" style="width:100%;margin:0;padding:24px">
      <div class="pyq-about-section-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        <span>关于</span>
      </div>
      <div class="pyq-about-content"><?php $this->content(); ?></div>
    </div>
  </div>

  <!-- 社交链接 -->
  <?php if ($has_social): ?>
  <div class="pyq-card" style="margin-top:10px">
    <div class="pyq-card-right" style="width:100%;margin:0;padding:24px">
      <div class="pyq-about-section-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
        <span>社交</span>
      </div>
      <div class="pyq-about-social-grid">
        <?php foreach ($socials as $key => $info): ?>
          <?php if (!empty($options->$key)): ?>
            <?php if ($info[2] === 'account'): ?>
            <div class="pyq-about-social-card" onclick="pyq.showAccount('<?php echo $info[0]; ?>','<?php echo htmlspecialchars($options->$key); ?>')">
              <div class="pyq-about-social-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="<?php echo $info[1]; ?>"/></svg>
              </div>
              <div class="pyq-about-social-name"><?php echo $info[0]; ?></div>
              <div class="pyq-about-social-arrow">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
              </div>
            </div>
            <?php else: ?>
            <a class="pyq-about-social-card" href="<?php echo htmlspecialchars($options->$key); ?>" target="_blank" rel="noopener">
              <div class="pyq-about-social-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="<?php echo $info[1]; ?>"/></svg>
              </div>
              <div class="pyq-about-social-name"><?php echo $info[0]; ?></div>
              <div class="pyq-about-social-arrow">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17l9.2-9.2M17 17V7H7"/></svg>
              </div>
            </a>
            <?php endif; ?>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- 评论区 -->
  <?php if ($this->allow('comment')): ?>
  <div class="pyq-card" style="margin-top:10px">
    <div class="pyq-card-right" style="width:100%;margin:0;padding:24px">
      <?php $this->need('comments.php'); ?>
    </div>
  </div>
  <?php endif; ?>

<?php endwhile; ?>
</div>

<!-- 账号弹窗 -->
<div id="pyq-account-modal" class="pyq-account-modal" onclick="if(event.target===this)pyq.closeAccount()">
  <div class="pyq-account-dialog">
    <div class="pyq-account-header">
      <span id="pyq-account-title"></span>
      <button class="pyq-account-close" onclick="pyq.closeAccount()">×</button>
    </div>
    <div class="pyq-account-body">
      <div id="pyq-account-value" class="pyq-account-value"></div>
      <button class="pyq-account-copy" onclick="pyq.copyAccount()">复制</button>
    </div>
  </div>
</div>

<?php $this->need('footer.php'); ?>
