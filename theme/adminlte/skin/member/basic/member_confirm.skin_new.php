<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

?>




<div class="lockscreen-wrapper">
  <div class="lockscreen-logo">
    <a href="/"><b>GON</b>PLAN</a>
  </div>


  <div class="lockscreen-name"><?php echo $member['mb_id'] ?></div>

  <div class="lockscreen-item">
    <div class="lockscreen-image">
      <!-- <img src="https://adminlte.io/themes/v3/dist/img/user1-128x128.jpg" alt="User Image"> -->
      <?php echo get_member_profile_image($member['mb_id'], '22', '22', 'User Image'); ?>
    </div>

    <form class="lockscreen-credentials" action="<?php echo $url ?>" onsubmit="return fmemberconfirm_submit(this);" method="post">
        <input type="hidden" name="mb_id" value="<?php echo $member['mb_id'] ?>">
        <input type="hidden" name="w" value="u">
        <input type="hidden" name="signup" value="ptn">

        <div class="input-group">

            <input type="password" name="mb_password" id="confirm_mb_password" class="form-control" size="15" maxLength="20" placeholder="비밀번호" required>

            <div class="input-group-append">
            <button type="submit" class="btn">
                <i class="fas fa-arrow-right text-muted"></i>
            </button>
            </div>
        </div>
    </form>

  </div>
  <div class="help-block text-center">
  비밀번호를 한번 더 입력해주세요.<br>
  보안을 위한 비밀번호를 한번 더 확인합니다.
  </div>
  <div class="text-center">
    <a href="/">메인화면으로 이동</a>
  </div>
  <div class="lockscreen-footer text-center">
    Copyright &copy; 2022 <b><a href="#" class="text-black"><?php echo $config['cf_title'] ?></a></b><br>
    All rights reserved
  </div>
</div>


<script>
function fmemberconfirm_submit(f)
{
    document.getElementById("btn_submit").disabled = true;

    return true;
}
</script>