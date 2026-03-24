<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 회원정보 찾기 시작 { -->
<div class="login-box">
  <div class="login-logo">
    <a href="#"><b>회원정보</b> 찾기</a>
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body">
    <p class="login-box-msg">회원가입 시 등록하신 이메일 주소를 입력해 주세요.<br>
    해당 이메일로 아이디와 비밀번호 정보를 보내드립니다.</p>

    <form name="fpasswordlost" action="<?php echo $action_url ?>" onsubmit="return fpasswordlost_submit(this);" method="post" autocomplete="off">
    <input type="hidden" name="url" value="<?php echo $login_url; ?>">
	
      <div class="form-group has-feedback">
        <input type="text" name="mb_email" id="mb_email" required class="required form-control full_input email" size="30" placeholder="E-mail 주소">
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
	  
      <div class="row">
        <div class="col-xs-12"><?php echo captcha_html();  ?></div>
		<div class="col-xs-12">&nbsp;</div>
        <!-- /.col -->
      </div>
	  
      <div class="row">
        <div class="col-xs-12">
		  <button type="submit" value="확인" class="btn btn-primary btn-block btn-flat">확인</button>
        </div>
        <!-- /.col -->
      </div>
    </form>
  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->


<script>
function fpasswordlost_submit(f)
{
    <?php echo chk_captcha_js();  ?>

    return true;
}

$(function() {
    var sw = screen.width;
    var sh = screen.height;
    var cw = document.body.clientWidth;
    var ch = document.body.clientHeight;
    var top  = sh / 2 - ch / 2 - 100;
    var left = sw / 2 - cw / 2;
    moveTo(left, top);
});
</script>
<!-- } 회원정보 찾기 끝 -->