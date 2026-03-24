<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$connect_skin_url.'/custom.css">', 0);
?>

<!-- 현재접속자 목록 시작 { -->
<div>

	<blockquote><h3>현재접속자</h3></blockquote>

	<table class="table xs-full mb-4"> <!-- table-striped table-hover  -->
		<thead>
			<tr class="d-none d-md-table-row">
				<th class="d-none d-md-table-cell" style="width: 4em;">번호</th>
				<th style="width: 11em;">접속자</th>
				<th>위치</th>
			</tr>
		</thead>
		<tbody>
			<?php
			for ($i=0; $i<count($list); $i++) {
				//$location = conv_content($list[$i]['lo_location'], 0);
				$location = $list[$i]['lo_location'];
				// 최고관리자에게만 허용
				// 이 조건문은 가능한 변경하지 마십시오.
				if ($list[$i]['lo_url'] && $is_admin == 'super') $display_location = '<a href="'.$list[$i]['lo_url'].'" class="text-dark">'.$location.'</a>';
				else $display_location = $location;

				$mb_info = array();
				if($list[$i]['mb_id']) $mb_info = get_member_info($list[$i]['mb_id'], $list[$i]['mb_name'], $list[$i]['mb_email'], $list[$i]['mb_homepage']);
			?>
			<tr>
				<td class="d-none d-md-table-cell"><?php echo $list[$i]['num'] ?></td>
				<td>
					<?php if(!$list[$i]['mb_id']) { echo $list[$i]['name']; } else { ?>
					<img class="list-icon rounded" src="<?php echo $mb_info['img'] ?>"> 
					<div class="dropdown d-inline">
						<a href="#" data-toggle="dropdown" class="text-dark"><?php echo get_text($list[$i]['mb_name']); ?></a>
						<?php echo $mb_info['menu'] ?>
					</div>
					<?php } ?>
				</td>
				<td><?php echo $display_location ?></td>
			</tr>
			<?php
			}
			if ($i == 0) echo '<tr><td colspan="3"><span class="text-danger">현재 접속자가 없습니다.</span></td></tr>';
			?>
		</tbody>
	</table>

</div>
<!-- } 현재접속자 목록 끝 -->