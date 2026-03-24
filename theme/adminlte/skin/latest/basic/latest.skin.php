<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<div class="card mb-4">
	<div class="card-body">
		<h4 class="card-title mb-4 text-4"><a href="<?php echo get_pretty_url($bo_table); ?>"><?php echo $bo_subject ?></a></h4>
		<ul class="list-group list-group-flush">
			<?php for ($i=0; $i<count($list); $i++) {  ?>
			<li class="list-group-item px-0"><a href="<?php echo $list[$i]['href'] ?>"><?php echo $list[$i]['subject'] ?></a></li>
			<?php } ?>
	</div>
</div>