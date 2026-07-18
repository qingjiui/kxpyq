<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
$options = Typecho_Widget::widget('Widget_Options');
$avatar_url = $options->avatar_url ?: (rtrim($options->siteUrl, '/') . '/usr/themes/kxpyq/assets/img/default-avatar.svg');
$username = $options->username ?: ($options->title ?: '');
$db = Typecho_Db::get();
?>

<?php while ($this->next()):
  $data = pyq_format_post_data([
    'cid' => $this->cid,
    'title' => $this->title,
    'text' => $this->content,
    'created' => $this->created,
    'authorId' => $this->author->uid,
  ], $db, $options);
  $data['comments'] = $db->fetchAll($db->select()->from('table.comments')
    ->where('table.comments.cid = ?', $this->cid)
    ->where('table.comments.status = ?', 'approved')
    ->order('table.comments.created', Typecho_Db::SORT_ASC));
  $liked = isset($_COOKIE['pyq_like_' . $this->cid]);
  echo pyq_render_card($data, $avatar_url, $username, $liked);
endwhile; ?>

<!-- 文章版权 -->
<?php if (!empty($options->copyright_text)): ?>
<div class="pyq-copyright">
  <?php
  $copyright = $options->copyright_text;
  $copyright = str_replace('{title}', $this->title, $copyright);
  $copyright = str_replace('{url}', $this->permalink, $copyright);
  $copyright = str_replace('{author}', $this->author->screenName, $copyright);
  $copyright = str_replace('{date}', date('Y-m-d', $this->created), $copyright);
  echo $copyright;
  ?>
</div>
<?php endif; ?>

<!-- 相关推荐 -->
<?php
$related = $db->fetchAll($db->select()->from('table.contents')
  ->where('table.contents.type = ?', 'post')
  ->where('table.contents.status = ?', 'publish')
  ->where('table.contents.parent = 0')
  ->where('table.contents.cid != ?', $this->cid)
  ->join('table.relationships', 'table.relationships.cid = table.contents.cid')
  ->where('table.relationships.mid = (SELECT r2.mid FROM table.relationships r2 WHERE r2.cid = ?)', $this->cid)
  ->order('table.contents.created', Typecho_Db::SORT_DESC)
  ->limit(6));
if (!empty($related)):
?>
<div class="pyq-related">
  <div class="pyq-related-title">相关推荐</div>
  <div class="pyq-related-grid">
    <?php foreach ($related as $r): ?>
    <a class="pyq-related-item" href="<?php echo rtrim($options->siteUrl, '/'); ?>/archives/<?php echo $r['slug']; ?>.html">
      <div class="pyq-related-item-title"><?php echo htmlspecialchars($r['title'] ?: mb_substr(strip_tags($r['text']), 0, 30)); ?></div>
      <div class="pyq-related-item-time"><?php echo date('m-d', $r['created']); ?></div>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php $this->need('footer.php'); ?>
