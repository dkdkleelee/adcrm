<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 선택삭제으로 인해 셀합치기가 가변적으로 변함
$colspan = 5;

if ($is_admin) $colspan++;

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$new_skin_url.'/custom.css">', 0);

$group_select = str_replace('id="gr_id">', 'id="gr_id" class="custom-select form-control">', $group_select);
$write_pages = chg_paging($write_pages);
?>
<div>
	<form name="fnew" method="get">

	<div class="card mb-3">
		<div class="card-body px-md-5 bg-light">
			<div class="row">
				<div class="col-6 col-md-2 pr-1 px-md-1 mb-2 mb-md-0">
					<?php echo $group_select ?>
					<script>document.getElementById("gr_id").value = "<?php echo $gr_id ?>";</script>
				</div>
				<div class="col-6 col-md-3 pl-1 px-md-1 mb-2 mb-md-0">
					<select name="view" id="view" class="custom-select form-control">
						<option value="">전체게시물
						<option value="w">원글만
						<option value="c">코멘트만
					</select>
				</div>
				<div class="col-12 col-md-5 px-md-1 mb-2 mb-md-0">
					<input type="text" name="mb_id" value="<?php echo $mb_id ?>" id="mb_id" required class="form-control">
				</div>
				<div class="col-12 col-md-2 px-md-1 mb-0">
					<button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search" aria-hidden="true"></i> 검색</button>
				</div>
			</div>
		</div>
	</div>
	<script>
	/* 셀렉트 박스에서 자동 이동 해제
	function select_change()
	{
		document.fnew.submit();
	}
	*/
	document.getElementById("gr_id").value = "<?php echo $gr_id ?>";
	document.getElementById("view").value = "<?php echo $view ?>";
	</script>
	</form>

	<!-- 전체게시물 목록 시작 { -->
	<form name="fnewlist" id="fnewlist" method="post" action="#" onsubmit="return fnew_submit(this);">
	<input type="hidden" name="sw"       value="move">
	<input type="hidden" name="view"     value="<?php echo $view; ?>">
	<input type="hidden" name="sfl"      value="<?php echo $sfl; ?>">
	<input type="hidden" name="stx"      value="<?php echo $stx; ?>">
	<input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
	<input type="hidden" name="page"     value="<?php echo $page; ?>">
	<input type="hidden" name="pressed"  value="">

	<h2 class="font-weight-normal pt-3 mb-4"><strong class="font-weight-extra-bold">최신글</strong></h2>

	<table class="table xs-full"> <!-- table-striped table-hover  -->
		<thead>
			<tr class="d-none d-md-table-row">
				<?php if($is_admin) { ?>
				<th style="width: 20px;">
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input" id="all_chk">
						<label class="custom-control-label custom-checkbox" for="all_chk"></label>
					</div>
				</th>
				<?php } ?>
				<!-- <th class="d-none d-md-table-cell" style="width: 8em;">그룹</th> -->
				<th class="d-none d-md-table-cell" style="width: 8em;">게시판</th>
				<th>제목</th>
				<th class="d-none d-md-table-cell" style="width: 11em;">글쓴이</th>
				<th class="d-none d-md-table-cell" style="width: 6em;">날짜</th>
			</tr>
		</thead>
		<tbody>
			<?php
			for ($i=0; $i<count($list); $i++)
			{
				$num = $total_count - ($page - 1) * $config['cf_page_rows'] - $i;
				$gr_subject = cut_str($list[$i]['gr_subject'], 20);
				$bo_subject = cut_str($list[$i]['bo_subject'], 20);
				$wr_subject = get_text(cut_str($list[$i]['wr_subject'], 80));

				$mb_info = get_member_info($list[$i]['mb_id'], $list[$i]['wr_name'], $list[$i]['wr_email'], $list[$i]['wr_homepage']);
			?>
			<tr>
				<?php if ($is_admin) { ?>
				<td style="width: 20px;">
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input" name="chk_bn_id[]" value="<?php echo $i; ?>" id="chk_bn_id_<?php echo $i; ?>">
						<label class="custom-control-label custom-checkbox" for="chk_bn_id_<?php echo $i; ?>"></label>
					</div>
					<input type="hidden" name="bo_table[<?php echo $i; ?>]" value="<?php echo $list[$i]['bo_table']; ?>">
					<input type="hidden" name="wr_id[<?php echo $i; ?>]" value="<?php echo $list[$i]['wr_id']; ?>">
				</td>
				<?php } ?>
				<!--
				<td class="d-none d-md-table-cell"><a href="./new.php?gr_id=<?php echo $list[$i]['gr_id'] ?>" class="text-dark"><?php echo $gr_subject ?></a></td>
				-->
				<td class="d-none d-md-table-cell"><a href="./board.php?bo_table=<?php echo $list[$i]['bo_table'] ?>" class="text-dark"><?php echo $bo_subject ?></a></td>
				<td>
					<a href="<?php echo $list[$i]['href'] ?>" class="text-dark"><?php echo $list[$i]['comment'] ?><?php echo $wr_subject ?></a>
					<!-- 모바일 -->
					<ul class="list-inline small text-muted mt-1 mb-0 d-md-none">
						<li class="list-inline-item"><img class="list-icon rounded" src="<?php echo $mb_info['img'] ?>"> <?php echo get_text($list[$i]['wr_name']); ?></li>
						<li class="list-inline-item"><a href="./board.php?bo_table=<?php echo $list[$i]['bo_table'] ?>" class="text-muted"><?php echo $bo_subject ?></a></li>
						<li class="list-inline-item float-right"><i class="fas fa-clock"></i> <?php echo $list[$i]['datetime2'] ?></li>
					</ul>
				</td>
				<td class="d-none d-md-table-cell">
					<img class="list-icon rounded" src="<?php echo $mb_info['img'] ?>"> 
					<div class="dropdown d-inline">
						<a href="#" data-toggle="dropdown" class="text-dark"><?php echo get_text($list[$i]['wr_name']); ?></a>
						<?php echo $mb_info['menu'] ?>
					</div>
				</td>
				<td class="d-none d-md-table-cell"><?php echo $list[$i]['datetime2'] ?></td>
			</tr>
			<?php }  ?>

			<?php if ($i == 0)
				echo '<tr><td colspan="'.$colspan.'" class="empty_table">게시물이 없습니다.</td></tr>';
			?>
		</tbody>
	</table>

	<div class="pt-3 d-flex justify-content-center justify-content-sm-end">
		<?php echo $write_pages ?>
	</div>

	<?php if ($is_admin) { ?>
	<div class="d-flex justify-content-end mb-2">
		<div class="btn-group xs-100">
			<button type="submit" name="btn_submit" title="선택삭제" onclick="document.pressed=this.title" class="btn btn-danger"><i class="fas fa-trash-alt"></i> 삭제</button>
		</div>
	</div>
	<?php } ?>

	</form>
</div>

<?php if ($is_admin) { ?>
<script>
$(function(){
    $('#all_chk').click(function(){
        $('[name="chk_bn_id[]"]').attr('checked', this.checked);
    });
});

function fnew_submit(f)
{
    f.pressed.value = document.pressed;

    var cnt = 0;
    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_bn_id[]" && f.elements[i].checked)
            cnt++;
    }

    if (!cnt) {
        alert(document.pressed+"할 게시물을 하나 이상 선택하세요.");
        return false;
    }

    if (!confirm("선택한 게시물을 정말 "+document.pressed+" 하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다")) {
        return false;
    }

    f.action = "./new_delete.php";

    return true;
}
</script>
<?php } ?>