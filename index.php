<?php
/**
 * 高仿微信朋友圈而生的主题
 *
 * @package KXpyq
 * @author 清酒
 * @version 1.0.0
 * @link https://qu.pw
 */

$this->need('header.php'); ?>

<!-- Feed -->
<?php
$db = Typecho_Db::get();
$options = Typecho_Widget::widget('Widget_Options');
$slug = $options->category_slug ?: 'shuoshuo';
$cat = $db->fetchRow($db->select()->from('table.metas')->where('table.metas.type=?','category')->where('table.metas.slug=?',$slug));
$per_page = 10;

if ($cat):
  $cid_rows = $db->fetchAll($db->select('table.relationships.cid')->from('table.relationships')->where('table.relationships.mid=?',$cat['mid']));
  $cids = array_column($cid_rows,'cid');
  $total = 0;
  if ($cids):
    $total_row = $db->fetchRow($db->select(array('COUNT(1)'=>'t'))->from('table.contents')->where('table.contents.type=?','post')->where('table.contents.status=?','publish')->where('table.contents.parent=0')->where('table.contents.cid IN ?',$cids));
    $total = $total_row['t'];
    $posts = $db->fetchAll($db->select()->from('table.contents')->where('table.contents.type=?','post')->where('table.contents.status=?','publish')->where('table.contents.parent=0')->where('table.contents.cid IN ?',$cids)->order('table.contents.created',Typecho_Db::SORT_DESC)->limit($per_page));
    
    // 置顶排序：批量查询is_top，避免N+1
    $pinned_cids = [];
    if ($posts) {
      $all_cids = array_column($posts, 'cid');
      $top_rows = $db->fetchAll($db->select('cid')->from('table.fields')
        ->where('table.fields.name = ?', 'is_top')
        ->where('table.fields.str_value = ?', '1')
        ->where('table.fields.cid IN ?', $all_cids));
      foreach ($top_rows as $r) { $pinned_cids[] = $r['cid']; }
    }
    $pinned = []; $normal = [];
    foreach ($posts as $post) {
      if (in_array($post['cid'], $pinned_cids)) { $pinned[] = $post; }
      else { $normal[] = $post; }
    }
    $posts = array_merge($pinned, $normal);
    
    $avatar = $options->avatar_url ?: (rtrim($options->siteUrl,'/') . '/usr/themes/kxpyq/assets/img/default-avatar.svg');
    $username = $options->username ?: ($options->title ?: '');
    
    foreach ($posts as $post):
      $data = pyq_format_post_data($post, $db, $options);
      // 加载评论
      $data['comments'] = $db->fetchAll($db->select()->from('table.comments')->where('table.comments.cid=?',$post['cid'])->where('table.comments.status=?','approved')->order('table.comments.created',Typecho_Db::SORT_ASC));
      $liked = isset($_COOKIE['pyq_like_'.$post['cid']]);
      echo pyq_render_card($data, $avatar, $username, $liked);
    endforeach;
  endif;
endif;
?>

<?php if($total==0): ?>
<div class="pyq-no-more">还没有说说，去后台发一条吧 ✨</div>
<?php endif; ?>

<div class="pyq-loading" id="pyq-loading"><span class="dot"></span><span class="dot"></span><span class="dot"></span></div>
<div class="pyq-no-more" id="pyq-no-more" style="display:none">— 没有更多了 —</div>

<!-- 回到底部/评论区的锚点 -->
<div id="pyq-feed-end"></div>

<?php $this->need('footer.php'); ?>
