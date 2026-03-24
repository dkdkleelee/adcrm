<?php
if (!defined('_GNUBOARD_')) exit;

add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<div class="container py-5 h-100">
  <div class="row d-flex justify-content-center align-items-center h-100">
    <div class="col-md-9 col-lg-6 col-xl-6 d-none d-lg-block">
      <img src="<?php echo G5_THEME_URL; ?>/dist/img/login_log.webp" class="img-fluid" alt="Sample image" />
    </div>
    <div class="col-md-8 col-lg-6 col-xl-5 offset-xl-1">
      <h2 class="text-center mb-4 fw-bold text-primary"><?php echo $config['cf_title'] ?></h2>
      <p class="text-center text-muted mb-4"><?php echo $config['cf_title'] ?> LOGIN</p>

      <form name="flogin" action="<?php echo $login_action_url ?>" onsubmit="return flogin_submit(this);" method="post">
        <input type="hidden" name="url" value="<?php echo $login_url; ?>">

        <!-- ID Input -->
        <div class="form-outline mb-4">
          <label class="form-label" for="login_id">아이디</label>
          <input type="text" name="mb_id" id="login_id" required class="form-control form-control-lg required" placeholder="아이디">
        </div>

        <!-- Password Input -->
        <div class="form-outline mb-4">
          <label class="form-label" for="login_pw">패스워드</label>
          <input type="password" name="mb_password" id="login_pw" required class="form-control form-control-lg required" placeholder="패스워드">
        </div>

        <!-- 로그인 버튼 -->
        <div class="d-grid mb-3">
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-unlock-alt me-1"></i> 로그인
          </button>
        </div>

        <!-- 회원가입 링크 -->
        <div class="text-center">
          <a href="./register?signup=emp" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-user-plus me-1"></i> 직원 회원가입
          </a>
        </div>
      </form>
    </div>
  </div>
</div>


<div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-sm"> <!-- modal-sm 클래스를 추가 -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="authModalLabel" style="font-size: 13px;"><i class="fas fa-lock"></i> 인증번호 발송됨</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="authForm">
          <input type='hidden' id='phoneHidden' name='phoneHidden' value=''>
          <input type='hidden' id='idxHidden' name='idxHidden' value=''>
          <input type='hidden' id='dateHidden' name='dateHidden' value=''>
          <input type='hidden' id='idHidden' name='idHidden' value=''>

          <div class="form-group">
            <input type="text" class="form-control" id="auth_code" name="auth_code" required placeholder="인증번호를 입력하세요" minlength="6" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);">
          </div>
          <button type="submit" id="confirm_auth" class="btn btn-primary btn-block">인증하기</button>
        </form>
      </div>
    </div>
  </div>
</div>



<script>
$(function(){
  // 인증 폼 제출 처리
  $("#authForm").submit(function(event) {

      var validchk = $("#auth_code").val().length;
      if(validchk != 6) {
        alert("인증코드가 올바르지 않습니다.");
        return false;
      }

      event.preventDefault();

      $.ajax({
          type: "POST",
          url: "login_confirm_ajax.php",
          data: $(this).serialize(),
          dataType: "json",
          success: function(response) {
              if (response.status == "success") {
                  $('#authModal').modal('hide');
                  //alert('인증이 완료되었습니다.\n메인페이지로 이동합니다.');
                  window.localStorage.removeItem('sms_send_date');
                  window.location.href = response.redirect_url || '/';
              } else {
                alert("인증코드가 올바르지 않습니다.");
                return false;
              }
          },
          error: function() {
              alert('인증 요청 중 오류가 발생했습니다.');
              return false;
          }
      });
  });
});

function flogin_submit(f) {

  var asisDate = window.localStorage.getItem('sms_send_date');
  if(asisDate != '') {
      var currentDate = new Date();
      currentDate.setMinutes(currentDate.getMinutes() - 3);
      var year = currentDate.getFullYear();
      var month = String(currentDate.getMonth() + 1).padStart(2, '0');
      var day = String(currentDate.getDate()).padStart(2, '0');
      var hours = String(currentDate.getHours()).padStart(2, '0');
      var minutes = String(currentDate.getMinutes()).padStart(2, '0');
      var seconds = String(currentDate.getSeconds()).padStart(2, '0');
      var formattedDate = year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;

      if(asisDate == 'undefined' || asisDate == undefined){
          asisDate = '';
      }

      if(asisDate > formattedDate) {
          alert('인증코드 발송후 3분이 지나지 않았습니다.잠시후 이용해주세요');
          window.validationDisabled = true;
          return false;
      }
  }

  $.ajax({
        type: "POST",
        url: "login_check_ajax.php",
        data: $(f).serialize(),
        dataType: "json",
        success: function(response) {
            if (response.success) {
                window.location.href = response.redirect_url || '/';
            } else if (response.status == "success") {
                var idHiddenInput = document.getElementById('idHidden');
                idHiddenInput.value = response.mb_id;
                var idxHiddenInput = document.getElementById('idxHidden');
                idxHiddenInput.value = response.sms_idx;
                var dateHiddenInput = document.getElementById('dateHidden');
                dateHiddenInput.value = response.insert_date;
                var phoneHiddenInput = document.getElementById('phoneHidden');
                phoneHiddenInput.value = response.phone;

                window.localStorage.setItem('sms_send_date', response.insert_date);

                document.getElementById('auth_code').placeholder = response.phone + " 인증번호 발송됨";

                $('#authModal').modal('show');


                if (timer) {
                    clearInterval(timer);
                }

                
                var countdown = 299; // 5분
                var countdownElement = document.createElement('span');
                countdownElement.style.marginLeft = '10px';
                countdownElement.style.fontWeight = 'bold';
                countdownElement.textContent = "(4:59)";

                document.getElementById('confirm_auth').appendChild(countdownElement);
                var timer = setInterval(function() {
                    var minutes = Math.floor(countdown / 60);
                    var seconds = countdown % 60;

                    countdownElement.textContent = "(" + minutes + ":" + (seconds < 10 ? "0" : "") + seconds + ")";
                    countdown--;
                    if (countdown < -1) {
                        countdownElement.textContent = "(만료됨)";
                        clearInterval(timer);
                        document.getElementById('confirm_auth').disabled = true;
                        document.getElementById('auth_code').value = "";

                        alert("문자인증 세션이 종료되었습니다.");
                        $('#authModal').modal('hide');

                    }
                }, 1000);
                
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert('로그인 요청 중 오류가 발생했습니다.');
        }
    });
    return false; 
}
</script>
