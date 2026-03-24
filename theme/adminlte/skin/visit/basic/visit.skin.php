<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

global $is_admin;
?>

<div class="card">
	<div class="card-header">
		접속자집계
	</div>
	<ul class="list-group">
		<li class="list-group-item border-0">오늘 : <?php echo number_format($visit[1]) ?></li>
		<li class="list-group-item border-0">어제 : <?php echo number_format($visit[2]) ?></li>
		<li class="list-group-item border-0">최대 : <?php echo number_format($visit[3]) ?></li>
		<li class="list-group-item border-0">전체 : <?php echo number_format($visit[4]) ?></li>
	</ul>

	<?php if ($is_admin == "super") { ?>
	<div class="card-body">
		<a href="<?php echo G5_ADMIN_URL ?>/visit_list.php" class="btn btn-danger btn-block"><i class="fas fa-cog fa-spin fa-fw"></i> 관리자</a>
	</div>
	<?php } ?>
</div>