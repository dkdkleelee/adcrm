<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if( ! $config['cf_social_login_use']) {     //소셜 로그인을 사용하지 않으면
    return;
}

add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/custom.css">');

add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/remodal/remodal.css">', 11);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/remodal/remodal-default-theme.css">', 12);
//add_stylesheet('<link rel="stylesheet" href="'.get_social_skin_url().'/style.css">', 13);
add_javascript('<script src="'.G5_JS_URL.'/remodal/remodal.js"></script>', 10);

$email_msg = $is_exists_email ? '등록할 이메일이 중복되었습니다.다른 이메일을 입력해 주세요.' : '';
?>
<div class="form-join">
	<form name="fregisterform" action="<?php echo $register_action_url ?>" onsubmit="return fregisterform_submit(this);" method="POST" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="url" value="<?php echo $urlencode; ?>">
    <input type="hidden" name="mb_name" value="<?php echo $user_name ? $user_name : $user_nick ?>" >
    <input type="hidden" name="provider" value="<?php echo $provider_name;?>" >
    <input type="hidden" name="action" value="register">

    <input type="hidden" name="mb_id" value="<?php echo $user_id; ?>" id="reg_mb_id">
    <input type="hidden" name="mb_nick_default" value="<?php echo isset($user_nick)?get_text($user_nick):''; ?>">
    <input type="hidden" name="mb_nick" value="<?php echo isset($user_nick)?get_text($user_nick):''; ?>" id="reg_mb_nick">

	<div class="text-center mb-5">
		<a href="<?php echo G5_URL ?>"><img src="<?php echo G5_URL ?>/logo.png" alt="<?php echo $config['cf_title']; ?>" class="logo"></a>
	</div>

	<div class="mb-2">
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input" id="agree11" name="agree" value="1">
			<label class="custom-control-label" for="agree11">회원가입약관 동의</label>
		</div>
		<textarea class="form-control" rows="5" style="font-size: 12px;"><?php echo get_text($config['cf_stipulation']) ?></textarea>
	</div>

	<div class="mb-4">
		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input" id="agree21" name="agree2" value="1">
			<label class="custom-control-label" for="agree21">개인정보처리방침안내 동의</label>
		</div>
		<textarea class="form-control" rows="5" style="font-size: 12px;"><?php echo get_text($config['cf_privacy']) ?></textarea>
	</div>

	<div class="form-group">
		<label for="reg_mb_email">이메일</label>
		<input type="text" name="mb_email" value="<?php echo isset($user_email)?$user_email:''; ?>" id="reg_mb_email" required class="form-control email" maxlength="100" placeholder="이메일">
	</div>

	<div>
		<input class="btn btn-primary btn-block" type="submit" value="다음">
	</div>
	
	</form>


	<form method="post" action="<?php echo $login_action_url ?>" onsubmit="return social_obj.flogin_submit(this);">
	<input type="hidden" id="url" name="url" value="<?php echo $login_url ?>">
	<input type="hidden" id="provider" name="provider" value="<?php echo $provider_name ?>">
	<input type="hidden" id="action" name="action" value="social_account_linking">

	<div class="mt-2">
		<button type="button" class="btn btn-outline-primary btn-block" data-toggle="modal" data-target="#linking">기존 계정에 연결하기</button>
	</div>

	<div class="modal" id="linking">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">기존 계정에 연결하기</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<p>기존 아이디에 SNS 아이디를 연결합니다.<br>
					이 후 SNS 아이디로 로그인 하시면 기존 아이디로 로그인 할 수 있습니다.</p>

					<div class="input-group">
						<input type="text" name="mb_id" class="form-control <?php echo $readonly ?>" maxlength="20" placeholder="아이디" required>
						<input type="password" name="mb_password" class="form-control <?php echo $readonly ?>" maxlength="20" placeholder="비밀번호" required>
					</div>

				</div>
				<div class="modal-footer">
					<input type="submit" class="btn btn-primary" value="연결하기">
				</div>
			</div>
		</div>
	</div>

	</form>
</div>

<script>

// submit 최종 폼체크
function fregisterform_submit(f)
{

	if (!f.agree.checked) {
		alert("회원가입약관의 내용에 동의하셔야 회원가입 하실 수 있습니다.");
		f.agree.focus();
		return false;
	}

	if (!f.agree2.checked) {
		alert("개인정보처리방침안내의 내용에 동의하셔야 회원가입 하실 수 있습니다.");
		f.agree2.focus();
		return false;
	}

	// E-mail 검사
	if ((f.w.value == "") || (f.w.value == "u" && f.mb_email.defaultValue != f.mb_email.value)) {
		var msg = reg_mb_email_check();
		if (msg) {
			alert(msg);
			jQuery(".email_msg").html(msg);
			f.reg_mb_email.select();
			return false;
		}
	}

	document.getElementById("btn_submit").disabled = "disabled";

	return true;
}

function flogin_submit(f)
{
	var mb_id = $.trim($(f).find("input[name=mb_id]").val()),
		mb_password = $.trim($(f).find("input[name=mb_password]").val());

	if(!mb_id || !mb_password){
		return false;
	}

	return true;
}

jQuery(function($){
	if( jQuery(".toggle .toggle-title").hasClass('active') ){
		jQuery(".toggle .toggle-title.active").closest('.toggle').find('.toggle-inner').show();
	}
	jQuery(".toggle .toggle-title .right_i").click(function(){

		var $parent = $(this).parent();
		
		if( $parent.hasClass('active') ){
			$parent.removeClass("active").closest('.toggle').find('.toggle-inner').slideUp(200);
		} else {
			$parent.addClass("active").closest('.toggle').find('.toggle-inner').slideDown(200);
		}
	});
	// 모두선택
	$("input[name=chk_all]").click(function() {
		if ($(this).prop('checked')) {
			$("input[name^=agree]").prop('checked', true);
		} else {
			$("input[name^=agree]").prop("checked", false);
		}
	});
});
</script>