<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

add_javascript('<script src="https://cdn.jsdelivr.net/npm/jqcloud2@2.0.3/dist/jqcloud.min.js"></script>');
add_stylesheet('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jqcloud2@2.0.3/dist/jqcloud.min.css">');
?>

<?php
	$words = array();
	$size = 10;
	foreach($list as $item)
	{
		$words[] = array(
			'text'=>get_text($item['pp_word']),
			'weight'=>$size,
			'link'=>G5_BBS_URL.'/search.php?sfl=wr_subject&sop=and&stx='.$item['pp_word']
		);

		$size--;
		if($size<1) $size = 1;
	}
?>

<?php if(!empty($words)){ ?>
<div class="card">
	<div class="card-header">
		인기검색어
	</div>
	
	<div class="card-body p-2">
		<div id="popular" style="width: 100%; height: 200px"></div>
	</div>
</div>

<script>
	var words = <?php echo json_encode($words); ?>;
	$("#popular").jQCloud(words, { autoResize: true });
</script>
<? } ?>
