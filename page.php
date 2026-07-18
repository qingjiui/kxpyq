<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
?>
<div class="pyq-wrap">
<?php while ($this->next()): ?>
  <div class="pyq-card">
    <div class="pyq-card-right" style="width:100%;margin:0;padding:20px">
      <h1 style="font-size:20px;font-weight:600;margin-bottom:12px"><?php $this->title(); ?></h1>
      <div class="pyq-card-text"><?php $this->content(); ?></div>
    </div>
  </div>
  <?php if ($this->allow('comment')): ?>
  <div class="pyq-card" style="margin-top:10px">
    <div class="pyq-card-right" style="width:100%;margin:0;padding:20px">
      <?php $this->need('comments.php'); ?>
    </div>
  </div>
  <?php endif; ?>
<?php endwhile; ?>
</div>
<?php $this->need('footer.php'); ?>
