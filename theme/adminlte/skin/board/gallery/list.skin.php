<?php
if(!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/custom.css">', 0);

$write_pages = chg_paging($write_pages);
?>

<div>

	<blockquote><h3><?php echo $board['bo_subject'] ?></h3></blockquote>

	<?php 
		if($is_category)
		{
			$category_href = G5_BBS_URL.'/board.php?bo_table='.$bo_table;
	?>

	<ul class="nav nav-tabs border-bottom-0">
		<li class="nav-item">
			<a class="nav-link <?php if($sca=='') echo 'active'; ?>" href="<?php echo $category_href ?>">전체</a>
		</li>
		<?php
			$categories = explode('|', $board['bo_category_list']);
			foreach($categories as $category)
			{
		?>
		<li class="nav-item">
			<a class="nav-link <?php if($category==$sca) echo 'active'; ?>" href="<?php echo $category_href.'&sca='.urlencode($category); ?>"><?php echo $category ?></a>
		</li>
		<?php
			}
		?>
	</ul>
	<?php } ?>

	<form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
	<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
	<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
	<input type="hidden" name="stx" value="<?php echo $stx ?>">
	<input type="hidden" name="spt" value="<?php echo $spt ?>">
	<input type="hidden" name="sca" value="<?php echo $sca ?>">
	<input type="hidden" name="sst" value="<?php echo $sst ?>">
	<input type="hidden" name="sod" value="<?php echo $sod ?>">
	<input type="hidden" name="page" value="<?php echo $page ?>">
	<input type="hidden" name="sw" value="">

	<div class="row">
	<?
		for ($i=0; $i<count($list); $i++) 
		{	
			$mb_info = get_member_info($list[$i]['mb_id'], $list[$i]['wr_name'], $list[$i]['wr_email'], $list[$i]['wr_homepage']); 
			$thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'], 320, 240, false, true);
	?>
		<div class="col-md-6 col-lg-4 mb-3">
			<div class="card">
				<div class="corner-card">
					<?php if($list[$i]['icon_new']) { ?>
					<div class="corner-ribbon shadow">NEW</div>
					<?php } ?>
					<a href="<?php echo $list[$i]['href'] ?>"><img src="<?php echo $thumb['src'] ?>" class="card-img-top"></a>
				</div>
				<div class="card-body">
					<div class="card-title text-truncate">
						<?php if($is_checkbox) { ?>
						<div class="custom-control custom-checkbox d-inline">
							<input type="checkbox" class="custom-control-input" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>">
							<label class="custom-control-label custom-checkbox" for="chk_wr_id_<?php echo $i ?>"></label>
						</div>
						<?php } ?>
						<a href="<?php echo $list[$i]['href'] ?>" class="text-dark"><?php echo $list[$i]['subject'] ?></a>
					</div>
					<div class="d-flex justify-content-between">
						<small class="text-muted">
							<img class="list-icon rounded" src="<?php echo $mb_info['img'] ?>"> 
							<div class="dropdown d-inline">
								<a href="#" data-toggle="dropdown" class="text-dark"><?php echo get_text($list[$i]['wr_name']); ?></a>
								<?php echo $mb_info['menu'] ?>
							</div>					
						</small>
						<small class="text-muted text-right">
							<i class="far fa-clock"></i> <?php echo $list[$i]['datetime2'] ?>
							<i class="far fa-eye pl-1"></i> <?php echo number_format($list[$i]['wr_hit']) ?>
							<i class="far fa-comment-dots pl-1"></i> <?php echo number_format($list[$i]['wr_comment']) ?>
						</small>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
	</div>

	<div class="d-flex justify-content-center justify-content-sm-end">
		<?php echo $write_pages;  ?>
	</div>

	<div class="d-flex flex-sm-row flex-column justify-content-sm-between mb-4">
		<div class="d-flex justify-content-center mb-2 mb-sm-0">
			<?php if($is_checkbox) { ?>
			<div class="btn-group xs-100">
				<button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value" class="btn btn-danger"><i class="fas fa-trash-alt"></i> 삭제</button>
				<button type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value" class="btn btn-danger"><i class="fas fa-file"></i> 복사</button>
				<button type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value" class="btn btn-danger"><i class="fas fa-arrows-alt"></i> 이동</button>

				<?php if($admin_href) { ?>
				<a href="<?php echo $admin_href ?>" class="btn btn-danger"><i class="fas fa-user-circle" aria-hidden="true"></i> 관리자</a>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
		<div class="d-flex justify-content-center">
			<div class="btn-group xs-100">
				<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#search"><i class="fas fa-search"></i> 검색</button>
				<?php if($list_href) { ?><a href="<?php echo $list_href ?>" class="btn btn-primary"><i class="fas fa-list" aria-hidden="true"></i> 목록</a><?php } ?>
				<?php if($write_href) { ?><a href="<?php echo $write_href ?>" class="btn btn-primary"><i class="fas fa-pen" aria-hidden="true"></i> 글쓰기</a><?php } ?>
			</div>
		</div>
	</div>

	</form>

	<!-- Search Modal -->
	<form name="fsearch" method="get">
	<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
	<input type="hidden" name="sca" value="<?php echo $sca ?>">
	<input type="hidden" name="sop" value="and">
	<div id="search" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title"><i class="fas fa-search"></i> 검색어 입력</h5>
					<button type="button" class="close" data-dismiss="modal"><i class="fas fa-times-circle"></i></button>
				</div>
				<div class="modal-body">
					<div class="input-group">
						<div class="input-group-prepend">
							<div class="input-group-text bg-white">
							<select class="bg-transparent border-0" name="sfl" id="sfl">
								<option value="wr_subject"<?php echo get_selected($sfl, 'wr_subject', true); ?>>제목</option>
								<option value="wr_content"<?php echo get_selected($sfl, 'wr_content'); ?>>내용</option>
								<option value="wr_subject||wr_content"<?php echo get_selected($sfl, 'wr_subject||wr_content'); ?>>제목+내용</option>
								<option value="mb_id,1"<?php echo get_selected($sfl, 'mb_id,1'); ?>>아이디</option>
								<option value="mb_id,0"<?php echo get_selected($sfl, 'mb_id,0'); ?>>아이디(코)</option>
								<option value="wr_name,1"<?php echo get_selected($sfl, 'wr_name,1'); ?>>글쓴이</option>
								<option value="wr_name,0"<?php echo get_selected($sfl, 'wr_name,0'); ?>>글쓴이(코)</option>
							</select>
							</div>
						</div>
						<input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx" class="form-control" size="25" maxlength="20" placeholder="검색어">
					</div>
				</div>
				<div class="modal-footer">
					<div>
						<button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> 검색</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	</form>

</div>

<?php if($is_checkbox) { ?>
<noscript>
<p>자바스크립트를 사용하지 않는 경우<br>별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
</noscript>
<?php } ?>

<?php if($is_checkbox) { ?>
<script>
function all_checked(sw) {
    var f = document.fboardlist;

    for (var i=0; i<f.length; i++) {
        if(f.elements[i].name == "chk_wr_id[]")
            f.elements[i].checked = sw;
    }
}

function fboardlist_submit(f) {
    var chk_count = 0;

    for (var i=0; i<f.length; i++) {
        if(f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
            chk_count++;
    }

    if(!chk_count) {
        alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택복사") {
        select_copy("copy");
        return;
    }

    if(document.pressed == "선택이동") {
        select_copy("move");
        return;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다\n\n답변글이 있는 게시글을 선택하신 경우\n답변글도 선택하셔야 게시글이 삭제됩니다."))
            return false;

        f.removeAttribute("target");
        f.action = g5_bbs_url+"/board_list_update.php";
    }

    return true;
}

// 선택한 게시물 복사 및 이동
function select_copy(sw) {
    var f = document.fboardlist;

    if(sw == "copy")
        str = "복사";
    else
        str = "이동";

    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

    f.sw.value = sw;
    f.target = "move";
    f.action = g5_bbs_url+"/move.php";
    f.submit();
}
</script>
<?php } ?>
<!-- } 게시판 목록 끝 -->
