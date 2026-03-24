<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<div class="card mb-4">
	<div class="card-header">
		<h4 class="mb-0">
			<span class="badge badge-primary align-top">답변</span> 
			<?php echo get_text($answer['qa_subject']); ?>
		</h4>
	</div>
	<div class="card-body">
		<ul class="list-inline text-muted small">
			<li class="list-inline-item float-right"><i class="fas fa-clock"></i> <?php echo $answer['qa_datetime'] ?></li>
		</ul>
		<div>
			<?php echo get_view_thumbnail(conv_content($answer['qa_content'], $answer['qa_html']), $qaconfig['qa_image_width']); ?>
		</div>
		<?php if ($answer_update_href||$answer_delete_href) { ?>
		<div class="d-flex justify-content-end mt-4">
			<div class="btn-group xs-100">
				<?php if ($answer_update_href) { ?>
				<a href="<?php echo $answer_update_href ?>" class="btn btn-danger" title="답변수정"><i class="fas fa-pen-square"></i> 수정</a><?php } ?>
				<?php if ($answer_delete_href) { ?>
				<a href="<?php echo $answer_delete_href ?>" class="btn btn-danger" onclick="del(this.href); return false;" title="답변삭제"><i class="fas fa-trash"></i> 삭제</a><?php } ?>
			</div>
		</div>
		<?php } ?>
	</div>
</div>

<div class="d-flex justify-content-center">
	<a href="<?php echo $rewrite_href; ?>" class="btn btn-primary btn-lg" title="추가질문">추가질문</a>  
</div>
