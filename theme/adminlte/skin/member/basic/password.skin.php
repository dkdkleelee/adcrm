<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
$delete_str = "";
if ($w == 'x') $delete_str = "댓";
if ($w == 'u') $g5['title'] = $delete_str."글 수정";
else if ($w == 'd' || $w == 'x') $g5['title'] = $delete_str."글 삭제";
else $g5['title'] = $g5['title'];

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>



<div class="lockscreen-wrapper">
  <div class="lockscreen-logo">
    <?php echo $g5['title'] ?>
  </div>


  <div class="lockscreen-name"></div>

  <div class="lockscreen-item">
    <div class="lockscreen-image">
      <?php echo get_member_profile_image($member['mb_id'], '22', '22', 'User Image'); ?>
    </div>

    <form name="fboardpassword" action="<?php echo $action;  ?>" method="post" class="lockscreen-credentials">
        <input type="hidden" name="w" value="<?php echo $w ?>">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
        <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
        <input type="hidden" name="comment_id" value="<?php echo $comment_id ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
        <input type="hidden" name="stx" value="<?php echo $stx ?>">
        <input type="hidden" name="page" value="<?php echo $page ?>">

        <div class="input-group">

            <input type="password" name="wr_password" id="confirm_mb_password" required class="form-control required" size="15" maxLength="20" placeholder="비밀번호">

            <div class="input-group-append">
            <button type="submit" class="btn">
                <i class="fas fa-arrow-right text-muted"></i>
            </button>
            </div>
        </div>
    </form>

  </div>
  <div class="help-block text-center">
    <p>
        <?php if ($w == 'u') { ?>
        <strong>작성자만 글을 수정할 수 있습니다.</strong>
        작성자 본인이라면, 글 작성시 입력한 비밀번호를 입력하여 글을 수정할 수 있습니다.
        <?php } else if ($w == 'd' || $w == 'x') {  ?>
        <strong>작성자만 글을 삭제할 수 있습니다.</strong>
        작성자 본인이라면, 글 작성시 입력한 비밀번호를 입력하여 글을 삭제할 수 있습니다.
        <?php } else {  ?>
        <strong>작성자와 관리자만 열람하실 수 있습니다.</strong>
        <br>작성자의 비밀번호를 입력하세요.
        <?php }  ?>
    </p>
  </div>
  <div class="text-center">
    <a href="/">메인화면으로 이동</a>
  </div>
  <div class="lockscreen-footer text-center">
    Copyright &copy; 2022 <b><a href="#" class="text-black"><?php echo $config['cf_title'] ?></a></b><br>
    All rights reserved
  </div>
</div>

