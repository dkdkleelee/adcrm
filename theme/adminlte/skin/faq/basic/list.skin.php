<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

add_stylesheet('<link rel="stylesheet" href="'.$faq_skin_url.'/custom.css">', 0);
?>

<div class="mb-4">

	<?php
	if ($himg_src)
		echo '<div><img src="'.$himg_src.'" class="img-fluid"></div>';

	// 상단 HTML
	echo '<div>'.conv_content($fm['fm_head_html'], 1).'</div>';
	?>

	<div class="card mb-4">
		<form name="faq_search_form" method="get">
		<input type="hidden" name="fm_id" value="<?php echo $fm_id;?>">
		<div class="card-body bg-light">
			<div class="form-group row mb-0">
				<label class="col-sm-2 col-form-label">FAQ 검색</label>
				<div class="col-sm-10">
					<div class="input-group">
						<input class="form-control" type="text" name="stx" value="<?php echo $stx;?>" required placeholder="검색어">
						<div class="input-group-append">
							<button type="submit" class="btn btn-primary"><i class="fas fa-search" aria-hidden="true"></i> 검색</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		</form>
	</div>

	<?php if(count($faq_master_list)){ ?>
	<div class="list-group mb-4">
		<?php foreach( $faq_master_list as $v ){ ?>
		<a href="<?php echo $category_href;?>?fm_id=<?php echo $v['fm_id'];?>" class="list-group-item list-group-item-action <?php if($v['fm_id'] == $fm_id) echo 'active'; ?>"><?php echo $v['fm_subject'];?></a>
		<?php } ?>
	</div>
	<?php } ?>

	<?php if(count($faq_list)) { ?>
	<div id="faq">
		<?php $i=0; foreach($faq_list as $key=>$v){ if(empty($v)) continue; $i++; ?>
		<div class="card">
			<div class="card-header">
				<h5 class="mb-0">
					<button class="btn btn-link text-dark p-0" data-toggle="collapse" data-target="#collapse_<?php echo $i ?>" aria-expanded="true" aria-controls="collapse_<?php echo $i ?>">
						<?php echo conv_content($v['fa_subject'], 1); ?>
					</button>
				</h5>
			</div>
			<div id="collapse_<?php echo $i ?>" class="collapse <?php if($i==1) echo 'show'; ?>" data-parent="#faq">
				<div class="card-body">
					<?php echo conv_content($v['fa_content'], 1); ?>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
	<?php }else{ ?>
	<div class="alert alert-danger py-5"><?php if($stx) echo '검색된 게시물이 없습니다.'; else '등록된 FAQ가 없습니다.'; ?></div>
	<?php } ?>

	<?php echo chg_paging(get_paging($page_rows, $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;page=')); ?>

	<?php
	// 하단 HTML
	echo '<div>'.conv_content($fm['fm_tail_html'], 1).'</div>';

	if ($timg_src)
		echo '<div><img src="'.$timg_src.'" class="img-fluid"></div>';
	?>

</div>