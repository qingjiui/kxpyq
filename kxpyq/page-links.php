<?php
/**
 * 友情链接
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
$options = Typecho_Widget::widget('Widget_Options');
$db = Typecho_Db::get();

// Gravatar 源：使用后台设置或默认
$gravatar_src = $options->gravatar_source ?: 'https://cdn.helingqi.com/avatar/';
?>

<div class="pyq-wrap">
  <div class="pyq-page-header">
    <h1>🔗 友情链接</h1>
  </div>

  <?php
  if (isset($options->plugins['activated']['Links'])):
    $links = $db->fetchAll($db->select()->from('table.links')
      ->where('table.links.state = ?', 1)
      ->order('table.links.order', Typecho_Db::SORT_ASC));

    if (!empty($links)):
      $grouped = [];
      foreach ($links as $link) {
        $s = !empty($link['sort']) ? $link['sort'] : '默认';
        $grouped[$s][] = $link;
      }
      foreach ($grouped as $sort_name => $group_items):
  ?>
  <div class="pyq-links-section">
    <?php if ($sort_name !== '默认'): ?>
    <h2 class="pyq-links-title"><?php echo htmlspecialchars($sort_name); ?></h2>
    <?php endif; ?>
    <div class="pyq-links-grid">
      <?php foreach ($group_items as $link):
        // 头像：优先图片链接 → 邮箱Gravatar → 默认无图图标
        $avatar = '';
        if (!empty($link['image'])) {
          $avatar = $link['image'];
        } elseif (!empty($link['email'])) {
          $avatar = $gravatar_src . md5(strtolower(trim($link['email']))) . '?s=80&d=mp';
        }
      ?>
      <a class="pyq-link-card-item" href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" rel="noopener"<?php if(!empty($link['description'])) echo ' title="'.htmlspecialchars($link['description']).'"'; ?>>
        <div class="pyq-link-card-avatar">
          <?php if ($avatar): ?>
          <img src="<?php echo htmlspecialchars($avatar); ?>" alt="<?php echo htmlspecialchars($link['name']); ?>" loading="lazy">
          <?php else: ?>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="var(--text2)"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg>
          <?php endif; ?>
        </div>
        <div class="pyq-link-card-meta">
          <div class="pyq-link-card-name"><?php echo htmlspecialchars($link['name']); ?></div>
          <?php if (!empty($link['description'])): ?>
          <div class="pyq-link-card-desc"><?php echo htmlspecialchars($link['description']); ?></div>
          <?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php
      endforeach;
    else:
  ?>
  <div class="pyq-no-more">暂无友情链接</div>
  <?php endif; else: ?>
  <div class="pyq-no-more">友情链接插件未启用，请在后台激活 Links 插件</div>
  <?php endif; ?>
</div>

<?php $this->need('footer.php'); ?>
