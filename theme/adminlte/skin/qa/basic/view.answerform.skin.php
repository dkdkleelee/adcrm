<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<?php if($is_admin) { // 관리자이면 답변등록 ?>
<div class="card mb-4">
	<form name="fanswer" method="post" action="./qawrite_update.php" onsubmit="return fwrite_submit(this);" autocomplete="off">
	<input type="hidden" name="qa_id" value="<?php echo $view['qa_id']; ?>">
	<input type="hidden" name="w" value="a">
	<input type="hidden" name="sca" value="<?php echo $sca ?>">
	<input type="hidden" name="stx" value="<?php echo $stx; ?>">
	<input type="hidden" name="page" value="<?php echo $page; ?>">
	<?php
	$option = '';
	$option_hidden = '';
	$option = '';

	if ($is_dhtml_editor) {
		$option_hidden .= '<input type="hidden" name="qa_html" value="1">';
	} else {
		$option .= '<div class="custom-control custom-checkbox custom-control-inline">'."\n".'<input type="checkbox" class="custom-control-input" id="qa_html" name="qa_html" onclick="html_auto_br(this);" value="'.$html_value.'" '.$html_checked.'>'."\n".'<label class="custom-control-label" for="qa_html">HTML</label></div>';
	}

	echo $option_hidden;
	?>

	<div class="card-header">
		<h4 class="mb-0">답변등록</h4>
	</div>
	<div class="card-body">

		<?php if ($option) { ?>
		<div class="form-group row">
			<label class="col-sm-2 col-form-label">옵션</label>
			<div class="col-sm-10">
				<?php echo $option ?>
			</div>
		</div>
		<?php } ?>

		<div class="form-group row">
			<label class="col-sm-2 col-form-label">제목</label>
			<div class="col-sm-10">
				<input class="form-control" type="text" name="qa_subject" value="<?php echo get_text($write['qa_subject']); ?>" id="qa_subject" required placeholder="제목">
			</div>
		</div>

		<div class="form-group row">
			<label class="col-sm-2 col-form-label">내용</label>

			<div class="qa_content col-sm-10 <?php echo $is_dhtml_editor ? $config['cf_editor'] : ''; ?>">
				<?php $editor_html = str_replace('<textarea id="qa_content" name="qa_content"', '<textarea id="qa_content" name="qa_content" class="form-control" rows="12"', $editor_html); ?>

				<?php echo $editor_html; // 에디터 사용시는 에디터로, 아니면 textarea 로 노출 ?>
			</div>
		</div>

		<div class="d-flex justify-content-end">
			<button type="submit" id="btn_submit" class="btn btn-primary">답변등록</button>
		</div>

	</div>

	</form>
</div>

<script>
function html_auto_br(obj)
{
	if (obj.checked) {
		result = confirm("자동 줄바꿈을 하시겠습니까?\n\n자동 줄바꿈은 게시물 내용중 줄바뀐 곳을<br>태그로 변환하는 기능입니다.");
		if (result)
			obj.value = "2";
		else
			obj.value = "1";
	}
	else
		obj.value = "";
}

function fwrite_submit(f)
{
	<?php echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>

	var subject = "";
	var content = "";
	$.ajax({
		url: g5_bbs_url+"/ajax.filter.php",
		type: "POST",
		data: {
			"subject": f.qa_subject.value,
			"content": f.qa_content.value
		},
		dataType: "json",
		async: false,
		cache: false,
		success: function(data, textStatus) {
			subject = data.subject;
			content = data.content;
		}
	});

	if (subject) {
		alert("제목에 금지단어('"+subject+"')가 포함되어있습니다");
		f.qa_subject.focus();
		return false;
	}

	if (content) {
		alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
		if (typeof(ed_qa_content) != "undefined")
			ed_qa_content.returnFalse();
		else
			f.qa_content.focus();
		return false;
	}

	document.getElementById("btn_submit").disabled = "disabled";

	return true;
}
</script>
<?php } else { ?>
<div class="alert alert-danger py-5">고객님의 문의에 대한 답변을 준비 중입니다.</div>
<?php } ?>
