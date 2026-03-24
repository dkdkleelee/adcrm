<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!$board['bo_use_sns']) return;
?>
<?php if ($list[$i]['wr_facebook_user']) { ?>
<li class="list-inline-item mr-0"><a href="https://www.facebook.com/profile.php?id=<?php echo $list[$i]['wr_facebook_user']; ?>" target="_blank"><img src="<?php echo G5_SNS_URL; ?>/icon/facebook_cmt.png" alt="페이스북에도 등록됨" style="margin-bottom: 4px;"></a></li>
<?php } ?>
<?php if ($list[$i]['wr_twitter_user']) { ?>
<li class="list-inline-item mr-0"><a href="https://www.twitter.com/<?php echo $list[$i]['wr_twitter_user']; ?>" target="_blank"><img src="<?php echo G5_SNS_URL; ?>/icon/twitter_cmt.png" alt="트위터에도 등록됨" style="margin-bottom: 4px;"></a></li>
<?php } ?>