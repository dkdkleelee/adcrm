<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

add_stylesheet('<link rel="stylesheet" href="'.$qa_skin_url.'/custom.css">', 0);

$mb_info = get_member_info($view['mb_id'], $view['qa_name'], $view['qa_email']);
//$view['datetime'] = substr($view['wr_datetime'],0,10) == G5_TIME_YMD ? substr($view['wr_datetime'], 11, 8) : substr($view['wr_datetime'], 2, 8);
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<div>

	<blockquote><h3><?php echo $view['subject']; ?></h3></blockquote>

	<div class="media mb-4">
		<img class="view-icon rounded" src="<?php echo $mb_info['img'] ?>">
		<div class="media-body ml-3">
			<div>
				<ul class="list-inline text-muted m-0">
					<li class="list-inline-item">
						<a href="#" data-toggle="dropdown" class="text-dark"><?php echo $view['name']; ?></a>
						<?php echo $mb_info['menu'] ?>
					</li>
				</ul>
			</div>
			<div>
				<ul class="list-inline text-muted small pt-1 m-0">
					<li class="list-inline-item"><i class="far fa-clock"></i> <?php echo $view['datetime'] ?></li>
					<?php if($view['email']) { ?>
					<li class="list-inline-item"><i class="far fa-envelope"></i> <?php echo $view['email']; ?></li>
					<?php } ?>
					<?php if($view['hp']) { ?>
					<li class="list-inline-item"><i class="fas fa-mobile-alt"></i> <?php echo $view['hp']; ?></li>
					<?php } ?>
				</ul>
			</div>
		</div>
	</div>

	<?php if($view['img_count']) { ?>
	<div class="mb-4">
		<?php
			for ($i=0; $i<=$view['img_count']; $i++)
			{
				//echo $view['img_file'][$i];
				echo get_view_thumbnail($view['img_file'][$i], $qaconfig['qa_image_width']);
			}
		?>
	</div>
	<?php } ?>

	<!-- 본문 내용 시작 { -->
	<div class="mb-4">
		<?php echo get_view_thumbnail($view['content'], $qaconfig['qa_image_width']); ?>
	</div>
	<!-- } 본문 내용 끝 -->

	<?php if($view['download_count']) { ?>
	<ul class="list-group mb-4">
		<!-- 첨부파일 -->
		<?php  for ($i=0; $i<$view['download_count']; $i++) { ?>
		<li class="list-group-item">
			<i class="fas fa-download"></i>
			<a href="<?php echo $view['download_href'][$i];  ?>" class="text-dark"><?php echo $view['download_source'][$i] ?></a>
		</li>
		<?php } ?>
	</ul>
	<?php } ?>

	<div class="d-flex flex-sm-row flex-column justify-content-sm-between mb-4">
		<div class="d-flex justify-content-center mb-2 mb-sm-0">
			<?php if($update_href||$delete_href) { ?>
			<div class="btn-group xs-100">
				<?php if ($update_href) { ?><a href="<?php echo $update_href ?>" class="btn btn-danger" title="수정"><i class="fas fa-pen-square"></i> 수정</a><?php } ?>
				<?php if ($delete_href) { ?><a href="<?php echo $delete_href ?>" class="btn btn-danger" onclick="del(this.href); return false;" title="삭제"><i class="fa fa-trash"></i> 삭제</a><?php } ?>
			</div>
			<?php } ?>
		</div>
		<div class="d-flex justify-content-center">
			<div class="btn-group xs-100">
				<?php if ($list_href) { ?>
				<a href="<?php echo $list_href ?>" class="btn btn-primary" title="목록"><i class="fa fa-list" aria-hidden="true"></i> 목록</a><?php } ?>
				<?php if ($write_href) { ?>
				<a href="<?php echo $write_href ?>" class="btn btn-primary" title="문의등록"><i class="fas fa-pen" aria-hidden="true"></i> 문의하기</a><?php } ?>
			</div>
		</div>
	</div>

	<?php if ($prev_href || $next_href) { ?>
	<div>
	<ul class="list-group mb-4">
		<?php if ($prev_href) { ?><li class="list-group-item"><small class="text-muted"><i class="fa fa-caret-up"></i> 이전글</small> <a href="<?php echo $prev_href ?>" class="text-dark"><?php echo $prev_qa_subject;?></a> <small class="float-right text-muted"><?php echo str_replace('-', '.', substr($prev_wr_date, '2', '8')); ?></small></li><?php } ?>
		<?php if ($next_href) { ?><li class="list-group-item"><small class="text-muted"><i class="fa fa-caret-down"></i> 다음글</small> <a href="<?php echo $next_href ?>" class="text-dark"><?php echo $next_qa_subject;?></a> <small class="float-right text-muted"><?php echo str_replace('-', '.', substr($next_wr_date, '2', '8')); ?></small></li><?php } ?>
	</ul>
	</div>
	<?php } ?>
</div>

<?php
// 질문글에서 답변이 있으면 답변 출력, 답변이 없고 관리자이면 답변등록폼 출력
if(!$view['qa_type']) {
	if($view['qa_status'] && $answer['qa_id'])
		include_once($qa_skin_path.'/view.answer.skin.php');
	else
		include_once($qa_skin_path.'/view.answerform.skin.php');
}
?>

<?php if(false&&$view['rel_count']) { // 그누보드 버그인지 뭔지는 모르겠지만 리스트가 1개만 떠서 추후 확인해보기 ?>
<div class="mt-4">
	<table class="table xs-full mb-0">
		<thead>
			<tr class="d-none d-md-table-row">
				<th>제목</th>
				<th class="d-none d-md-table-cell" style="width: 6em;">날짜</th>
				<th style="width: 5em;">상태</th>
			</tr>
		</thead>
		<tbody>
            <?php for($i=0; $i<$view['rel_count']; $i++) { ?>
            <tr>
				<td>
					<span class="badge badge-primary"><?php echo $rel_list[$i]['category']; ?></span>
					<a href="<?php echo $rel_list[$i]['view_href']; ?>" class="text-dark">
						<?php echo $rel_list[$i]['subject']; ?>
						<?php if ($rel_list[$i]['icon_file']) echo " <i class=\"fa fa-download\" aria-hidden=\"true\"></i>" ; ?>
					</a>
				</td>
				<td class="d-none d-md-table-cell"><?php echo $rel_list[$i]['date']; ?></td>
				<td><span class=" <?php echo ($list[$i]['qa_status'] ? 'txt_done' : 'txt_rdy'); ?>"><?php echo ($list[$i]['qa_status'] ? '완료' : '대기'); ?></span></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<?php } ?>

<script>
$(function() {
    $("a.view_image").click(function() {
        window.open(this.href, "large_image", "location=yes,links=no,toolbar=no,top=10,left=10,width=10,height=10,resizable=yes,scrollbars=no,status=no");
        return false;
    });
});
</script>