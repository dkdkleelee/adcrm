<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<li class="nav-item dropdown">
	<a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo $nick ?> 님</a>
	<div class="dropdown-menu"><!-- dropdown-menu-right -->
		<a class="dropdown-item win_point" href="<?php echo G5_BBS_URL ?>/point.php" target="_blank">포인트 (<?php echo $point ?>)</a>
		<a class="dropdown-item win_scrap" href="<?php echo G5_BBS_URL ?>/scrap.php" target="_blank">스크랩</a>
		<a class="dropdown-item" href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=<?php echo G5_BBS_URL ?>/register_form.php">정보수정</a>
		<a class="dropdown-item" href="<?php echo G5_BBS_URL ?>/logout.php">로그아웃</a>

		<?php if ($is_admin == 'super' || $is_auth) {  ?>
		<div class="dropdown-divider"></div>
		<a class="dropdown-item" href="<?php echo G5_ADMIN_URL ?>"><strong>관리자 모드</strong></a>
		</li>
		<?php }  ?>

	</div>
</li>

<script>
// 탈퇴의 경우 아래 코드를 연동하시면 됩니다.
function member_leave()
{
    if (confirm("정말 회원에서 탈퇴 하시겠습니까?"))
        location.href = "<?php echo G5_BBS_URL ?>/member_confirm.php?url=member_leave.php";
}
</script>
