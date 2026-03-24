<?php
if (!defined('_GNUBOARD_')) exit;

add_stylesheet('<link rel="stylesheet" href="' . $member_skin_url . '/style.css">', 0);

//parent 부서
$dpet_parent_sql = "
select *
from {$g5['crm_depart']}
where parent_deptno != 1
order by deptno 
";
$dpet_parent_result = sql_query($dpet_parent_sql);
?>


<script src="<?php echo G5_JS_URL ?>/jquery.register_form.js"></script>
<?php if ($config['cf_cert_use'] && ($config['cf_cert_ipin'] || $config['cf_cert_hp'])) { ?>
  <script src="<?php echo G5_JS_URL ?>/certify.js?v=<?php echo G5_JS_VER; ?>"></script>
<?php } ?>


<div class="container py-5 h-100">
  <div class="row d-flex justify-content-center align-items-center h-100">
    <div class="col-md-9 col-lg-6 col-xl-6 d-none d-lg-block">
      <img src="<?php echo G5_THEME_URL; ?>/dist/img/login_log.webp" class="img-fluid" alt="Sample image" />
    </div>
    <div class="col-md-8 col-lg-6 col-xl-5 offset-xl-1">
      <h2 class="text-center mb-4 fw-bold text-primary"><?php echo $config['cf_title'] ?></h2>
      <p class="text-center text-muted mb-4">회원가입</p>

      <form id="fregisterform" name="fregisterform" action="<?php echo $register_action_url ?>" onsubmit="return fregisterform_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="w" value="<?php echo $w ?>">
        <input type="hidden" name="url" value="<?php echo $urlencode ?>">
        <input type="hidden" name="agree" value="<?php echo $agree ?>">
        <input type="hidden" name="agree2" value="<?php echo $agree2 ?>">
        <input type="hidden" name="cert_type" value="<?php echo $member['mb_certify']; ?>">
        <input type="hidden" name="cert_no" value="">

        <!-- 기본 필드들 -->
        <div class="form-outline mb-4">
          <label class="form-label" for="reg_mb_id">아이디</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
            <input type="text" name="mb_id" value="<?php echo $member['mb_id'] ?>" id="reg_mb_id" class="form-control form-control-lg" placeholder="아이디">
          </div>
        </div>

        <div class="form-outline mb-4">
          <label class="form-label" for="reg_mb_password">비밀번호</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" name="mb_password" id="reg_mb_password" class="form-control form-control-lg" placeholder="비밀번호">
          </div>
        </div>

        <div class="form-outline mb-4">
          <label class="form-label" for="reg_mb_password_re">비밀번호 확인</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" name="mb_password_re" id="reg_mb_password_re" class="form-control form-control-lg" placeholder="비밀번호 확인">
          </div>
        </div>

        <div class="form-outline mb-4">
          <label class="form-label" for="reg_mb_name">이름</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
            <input type="text" name="mb_name" id="reg_mb_name" class="form-control form-control-lg" placeholder="이름">
          </div>
        </div>

        <div class="form-outline mb-4">
          <label class="form-label" for="reg_mb_email">E-mail</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input type="text" name="mb_email" id="reg_mb_email" class="form-control form-control-lg" placeholder="E-mail">
          </div>
        </div>

        <div class="form-outline mb-4">
          <label class="form-label" for="reg_mb_hp">휴대폰번호</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
            <input type="text" name="mb_hp" id="reg_mb_hp" class="form-control form-control-lg" placeholder="휴대폰번호">
          </div>
        </div>

        <div class="form-outline mb-4">
          <label class="form-label" for="reg_mb_nick">닉네임</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
            <input type="text" name="mb_nick" id="reg_mb_nick" class="form-control form-control-lg" placeholder="닉네임">
          </div>
        </div>

        <!-- 부서 (선택적 출력) -->
        <?php if ($w == "") { ?>
        <div class="form-outline mb-4">
          <label class="form-label" for="mb_deptno">부서</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-building"></i></span>
            <select name="mb_deptno" id="mb_deptno" class="form-control">
              <option value="">부서없음</option>
              <?php while ($dept = sql_fetch_array($dpet_parent_result)) { ?>
              <option value="<?php echo $dept['deptno'] ?>" <?php echo get_selected($member['mb_deptno'], $dept['deptno']); ?>><?php echo $dept['deptnm'] ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
        <?php } ?>

        <!-- 캡차 -->
        <div class="form-outline mb-4">
          <?php echo captcha_html(); ?>
        </div>

        <!-- 버튼 -->
        <div class="d-grid gap-2">
          <button type="submit" id="btn_insert" class="btn btn-primary btn-lg"><i class="fas fa-user-plus me-1"></i> 회원가입</button>
          <button type="button" onclick="history.back()" class="btn btn-outline-secondary btn-lg">가입취소</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
$(document).ready(function() {


$("#reg_mb_id").change(function () {
  if ($(this).val() != "") {
        var len = $(this).val().length;
        if (len <= 5) {
            alert("ID 5자 이상이어야 합니다");
            return false;
        }
        var mb_id = $(this).val();
        var act = "dup_member";
        $.ajax({
            type: "post",
            url: "<?php echo G5_URL?>/biz/hr/hr_ajax.php",
            data: {
              mb_id: mb_id ,
              act: act
            },
            success: function (result) {
              
                //alert(result.employeeCnt);//JSON.stringify(result)
                if (result == 0) {//중복ID가 존재하지 않으면
                    $("#btn_insert").attr("disabled", false);
                    $("#btn_insert").css("opacity", "1");
                    $("#ptn_id").attr('class', 'form-control is-valid');

                } else {

                    alert(mb_id + " < ID 중복입니다");
                    $("#btn_insert").attr("disabled", true);
                    $("#btn_insert").css("opacity", "0.5");

                    $("#reg_mb_id").val("");
                    $("#reg_mb_id").attr('class', 'form-control is-invalid');
                }
            },
            error: function () {
                alert("서버 작업중. 다음에 이용해 주세요.");
            }
        });

    } else {
        $("#btn_insert").attr("disabled", true);
        $("#btn_insert").css("opacity", "0.5");

        $("#reg_mb_id").val("");
        $("#reg_mb_id").attr('class', 'form-control is-invalid');
    }
});



$("#reg_mb_email").change(function () {
  var mb_email = $(this).val();

  if (mb_email !== "") {
      // 정규 표현식을 사용하여 이메일 형식 검증
      var emailRegex = /^[a-zA-Z0-9._-]{3,}@[^@]+\.[a-z]{2,}$/;
      if (!emailRegex.test(mb_email)) {
          alert("유효하지 않은 이메일 주소");
          $(this).val(""); // 입력된 값을 초기화
          $(this).focus(); // 입력란에 포커스를 다시 맞춤
      } else {
        var act = "dup_email";
        $.ajax({
            type: "post",
            url: "<?php echo G5_URL?>/biz/hr/hr_ajax.php",
            data: {
              mb_email: mb_email ,
              act: act
            },
            success: function (result) {
                if (result == 0) {
                    $("#btn_insert").attr("disabled", false);
                    $("#btn_insert").css("opacity", "1");
                    $("#reg_mb_email").attr('class', 'form-control is-valid');

                } else {

                    alert(mb_email + " < 중복 이메일");
                    $("#btn_insert").attr("disabled", true);
                    $("#btn_insert").css("opacity", "0.5");

                    $("#reg_mb_email").val("");
                    $("#reg_mb_email").attr('class', 'form-control is-invalid');
                }
            },
            error: function () {
                alert("서버 작업중. 다음에 이용해 주세요.");
            }
        });
        return false;
      }
  }

});


});


  
  // submit 최종 폼체크
  function fregisterform_submit(f) {
    // 회원아이디 검사
    if (f.w.value == "") {
      var msg = reg_mb_id_check();
      if (msg) {
        alert(msg);
        f.mb_id.select();
        return false;
      }
    }

    if (f.w.value == "") {
      if (f.mb_password.value.length <= 8) {
        alert("비밀번호를 8글자 이상 입력하십시오.");
        f.mb_password.focus();
        return false;
      }
    }

    if (f.mb_password.value != f.mb_password_re.value) {
      alert("비밀번호가 같지 않습니다.");
      f.mb_password_re.focus();
      return false;
    }

    if (f.mb_password.value.length > 0) {
      if (f.mb_password_re.value.length <= 8) {
        alert("비밀번호를 8글자 이상 입력하십시오.");
        f.mb_password_re.focus();
        return false;
      }
    }

    // 이름 검사
    if (f.w.value == "") {
      if (f.mb_name.value.length < 1) {
        alert("이름을 입력하십시오.");
        f.mb_name.focus();
        return false;
      }
    }

    // 닉네임 검사
    if ((f.w.value == "") || (f.w.value == "u" && f.mb_nick.defaultValue != f.mb_nick.value)) {
      var msg = reg_mb_nick_check();
      if (msg) {
        alert(msg);
        f.reg_mb_nick.select();
        return false;
      }
    }

    // E-mail 검사
    if ((f.w.value == "") || (f.w.value == "u" && f.mb_email.defaultValue != f.mb_email.value)) {
      var msg = reg_mb_email_check();
      if (msg) {
        alert(msg);
        f.reg_mb_email.select();
        return false;
      }
    }

    <?php if (($config['cf_use_hp'] || $config['cf_cert_hp']) && $config['cf_req_hp']) {  ?>
      // 휴대폰번호 체크
      var msg = reg_mb_hp_check();
      if (msg) {
        alert(msg);
        f.reg_mb_hp.select();
        return false;
      }
    <?php } ?>

    <?php echo chk_captcha_js();  ?>

    document.getElementById("btn_submit").disabled = "disabled";

    return true;
  }
</script>
