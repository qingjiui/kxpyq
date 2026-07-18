<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$comments = $this->comments;
if ($comments && $comments->have()):
?>
<div class="pyq-comments">
  <?php while($comments->next()): ?>
  <div class="pyq-comment-item">
    <span class="pyq-comment-user"><?php $comments->author(); ?></span>
    <span class="pyq-comment-text">：<?php $comments->content(); ?></span>
  </div>
  <?php endwhile; ?>
</div>
<?php endif; ?>
