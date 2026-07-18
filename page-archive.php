<?php
/**
 * 归档（时光轴）
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
$options = Typecho_Widget::widget('Widget_Options');
$db = Typecho_Db::get();
$category_slug = $options->category_slug ?: 'shuoshuo';

// 获取说说分类
$category = $db->fetchRow($db->select()->from('table.metas')
  ->where('table.metas.type = ?', 'category')
  ->where('table.metas.slug = ?', $category_slug));

$cid_list = [];
if ($category) {
  $rows = $db->fetchAll($db->select('table.relationships.cid')
    ->from('table.relationships')
    ->where('table.relationships.mid = ?', $category['mid']));
  $cid_list = array_column($rows, 'cid');
}
?>

<div class="pyq-wrap">
  <div class="pyq-page-header">
    <h1>📅 时光轴</h1>
  </div>

  <?php if (!empty($cid_list)):
    $posts = $db->fetchAll($db->select()->from('table.contents')
      ->where('table.contents.type = ?', 'post')
      ->where('table.contents.status = ?', 'publish')
      ->where('table.contents.parent = 0')
      ->where('table.contents.cid IN ?', $cid_list)
      ->order('table.contents.created', Typecho_Db::SORT_DESC));
    
    // 统计信息
    $total = count($posts);
    $years = [];
    $months = [];
    foreach ($posts as $p) {
      $y = date('Y', $p['created']);
      $m = date('Y-n', $p['created']);
      if (!isset($years[$y])) $years[$y] = 0;
      $years[$y]++;
      if (!isset($months[$m])) $months[$m] = 0;
      $months[$m]++;
    }
  ?>
  
  <!-- 统计卡片 -->
  <div class="pyq-archive-stats">
    <div class="pyq-archive-stat">
      <div class="stat-num"><?php echo $total; ?></div>
      <div class="stat-label">条说说</div>
    </div>
    <div class="pyq-archive-stat">
      <div class="stat-num"><?php echo count($years); ?></div>
      <div class="stat-label">个年头</div>
    </div>
    <div class="pyq-archive-stat">
      <div class="stat-num"><?php echo count($months); ?></div>
      <div class="stat-label">个月份</div>
    </div>
  </div>

  <!-- 年份快速跳转 -->
  <div class="pyq-archive-nav">
    <?php foreach ($years as $y => $cnt): ?>
    <a href="#year-<?php echo $y; ?>" class="pyq-archive-nav-item"><?php echo $y; ?></a>
    <?php endforeach; ?>
  </div>

  <!-- 时间线 -->
  <div class="pyq-timeline">
    <?php
    $current_year = '';
    $current_month = '';
    $month_count = 0;
    
    foreach ($posts as $post):
      $year = date('Y', $post['created']);
      $month = date('n', $post['created']);
      $day = date('j日 H:i', $post['created']);
      
      $plain = strip_tags($post['text']);
      $snippet = mb_strlen($plain) > 60 ? mb_substr($plain, 0, 60) . '...' : $plain;
      
      // 年份变化
      if ($year !== $current_year):
        $current_year = $year;
        $current_month = '';
        $month_count = 0;
    ?>
    <div class="pyq-timeline-year" id="year-<?php echo $year; ?>">
      <span><?php echo $year; ?>年</span>
      <span class="year-count"><?php echo $years[$year]; ?>条</span>
    </div>
    <?php endif; ?>
    
    <!-- 月份变化 -->
    <?php
      $month_key = $year . '-' . $month;
      if ($month_key !== $current_month):
        $current_month = $month_key;
        $month_count = 0;
    ?>
    <div class="pyq-timeline-month"><?php echo $month; ?>月</div>
    <?php endif; ?>
    
    <div class="pyq-timeline-item">
      <div class="tl-dot"></div>
      <div class="tl-time"><?php echo $day; ?></div>
      <div class="tl-content">
        <a class="tl-title" href="<?php echo Typecho_Common::url('/archives/' . $post['slug'] . '.html', $options->siteUrl); ?>">
          <?php echo htmlspecialchars($post['title'] ?: $snippet); ?>
        </a>
        <?php if ($post['title'] && $snippet): ?>
        <div class="tl-snippet"><?php echo htmlspecialchars($snippet); ?></div>
        <?php endif; ?>
      </div>
    </div>
    
    <?php endforeach; ?>
  </div>
  
  <?php else: ?>
  <div class="pyq-no-more">还没有说说</div>
  <?php endif; ?>
</div>

<?php $this->need('footer.php'); ?>
