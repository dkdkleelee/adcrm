<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);

$sql = "
select *
  from {$g5['crm_partner']}
  where ptn_idx = {$member['mb_ptnidx']}
";
$result = sql_fetch($sql);

?>

<div class="register-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="#" class="h1"><b><?php echo $config['cf_title'] ?></b></a>
    </div>
    <div class="card-body">
      <p class="login-box-msg"><code>고객사</code> <?php echo $w == "" ? "가입" : "수정"?> PAGE</p>
      
      <form id="ptn_signup_form" name="ptn_signup_form" action="./partner_signup" onsubmit="return ptn_signup_form_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="w" value="<?php echo $w ?>">
        <input type="hidden" name="mb_id" value="<?php echo $member['mb_id'] ?>">
        <input type="hidden" name="url" value="<?php echo $urlencode ?>">

        <div class="input-group mb-3">
        <input type="text" id="ptn_id" name="ptn_id" value="<?php echo $member['mb_id'] ?>" id="mb_id" class="form-control" minlength="3" maxlength="20" placeholder="아이디" oninput="this.value = this.value.replace(/[^0-9a-z.]/g, '').replace(/(\..*)\./g, '$1');" <?php echo $required ?> <?php echo $readonly; ?> >
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="ptn_pw" id="ptn_pw" class="form-control" minlength="6" maxlength="20" placeholder="비밀번호6~20자리" <?php echo $required ?>>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="ptn_pw_re" id="ptn_pw_re" class="form-control" minlength="6" maxlength="20" placeholder="비밀번호 확인" <?php echo $required ?>>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
        <input type="text" name="ptn_nm" value="<?php echo $result['ptn_nm'] ?>" id="ptn_nm" class="form-control" minlength="2" maxlength="20" placeholder="업체명" <?php echo $required ." ". $readonly?>>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
        <input type="text" name="ptn_reprnm" value="<?php echo $result['ptn_reprnm'] ?>" id="ptn_reprnm" class="form-control" minlength="2" maxlength="20" placeholder="이름" <?php echo $required ?>>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
        <input type="text" name="ptn_phone" value="<?php echo $member['mb_hp'] ?>" id="ptn_phone" class="form-control" minlength="13" maxlength="13" placeholder="휴대전화" oninput="telHyphen(this);" <?php echo $required ?>>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-mobile"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
        <input type="text" name="ptn_tel" value="<?php echo $result['ptn_tel'] ?>" id="ptn_tel" class="form-control" minlength="10" maxlength="13" placeholder="내선번호" oninput="telHyphen(this);">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-phone-alt"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
        <input type="email" name="ptn_email" value="<?php echo $member['mb_email'] ?>" id="ptn_email" class="form-control" minlength="5" maxlength="50" placeholder="이메일" <?php echo $required ?>>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>

        <?php if ($w == "") {  ?>
        <p>※관리자 승인 후 사용할 수 있습니다※</p>
        <div class="social-auth-links text-center">
            <button type="submit" class="btn btn-block btn-primary"><i class="fas fa-user-plus"></i> 회원가입
            <button onclick="history.back()" class="btn btn-block btn-danger"> 가입취소
        </div>
        <?php } else if ($w == "u") { ?>
        
        <div class="input-group mb-3">
          <span class="frm_info">
              [프로필 IMAGE]
          </span>
          <input type="file" name="mb_img" id="reg_mb_img" >

          <?php if (file_exists($mb_img_path)) {  ?>
            <img src="<?php echo $mb_img_url ?>" alt="회원이미지">
            <input type="checkbox" name="del_mb_img" value="1" id="del_mb_img">
            <label for="del_mb_img">삭제</label>
          <?php }  ?>
        </div>

        <div class="social-auth-links text-center">
            <button type="submit" class="btn btn-block btn-primary"><i class="fas fa-user-plus"></i> 정보수정
            <button onclick="history.back()" class="btn btn-block btn-danger"> 뒤로가기
        </div>
        <?php } ?>
        
      </form>
        
    </div>
  </div>
</div>

<script>

$(document).ready(function() {


  $("#ptn_id").change(function () {
    if ($(this).val() != "") {
          var len = $(this).val().length;
          if (len <= 2) {
              alert("고객ID 3글자이상이어야 합니다");
              return false;
          }
          var ptn_id = $(this).val();
          var act = "dup_member2";
          $.ajax({
              type: "post",
              url: "<?php echo G5_URL?>/biz/partner/partner_ajax.php",
              data: {
                ptn_id: ptn_id ,
                act: act
              },
              success: function (result) {
                
                  //alert(result.employeeCnt);//JSON.stringify(result)
                  if (result == 0) {//중복ID가 존재하지 않으면
                      $("#btn_insert").attr("disabled", false);
                      $("#btn_insert").css("opacity", "1");
                      $("#ptn_id").attr('class', 'form-control is-valid');

                  } else {

                      alert(ptn_id + " < ID 중복으로 사용불가");
                      $("#btn_insert").attr("disabled", true);
                      $("#btn_insert").css("opacity", "0.5");

                      $("#ptn_id").val("");
                      $("#ptn_id").attr('class', 'form-control is-invalid');
                  }
              },
              error: function () {
                  alert("RestAPI서버가 작동하지 않습니다. 다음에 이용해 주세요.");
              }
          });

      } else {
          $("#btn_insert").attr("disabled", true);
          $("#btn_insert").css("opacity", "0.5");

          $("#emp_id").val("");
          $("#emp_id").attr('class', 'form-control is-invalid');
      }
  });



  $("#ptn_email").change(function () {
    var mb_email = $(this).val();

    if (mb_email !== "") {
        // 정규 표현식을 사용하여 이메일 형식 검증
        var emailRegex = /^[a-zA-Z0-9._-]{3,}@[^@]+\.[a-z]{2,}$/;
        if (!emailRegex.test(mb_email)) {
            alert("유효하지 않은 이메일 주소입니다. 다시 입력해 주세요.");
            $(this).val(""); // 입력된 값을 초기화
            $(this).focus(); // 입력란에 포커스를 다시 맞춤
        } else {
          var act = "dup_email";
          $.ajax({
              type: "post",
              url: "<?php echo G5_URL?>/biz/partner/partner_ajax.php",
              data: {
                mb_email: mb_email ,
                act: act
              },
              success: function (result) {
                  if (result == 0) {
                      $("#btn_insert").attr("disabled", false);
                      $("#btn_insert").css("opacity", "1");
                      $("#ptn_id").attr('class', 'form-control is-valid');

                  } else {

                      alert(mb_email + " < 중복 이메일 주소 입니다");
                      $("#btn_insert").attr("disabled", true);
                      $("#btn_insert").css("opacity", "0.5");

                      $("#ptn_email").val("");
                      $("#ptn_email").attr('class', 'form-control is-invalid');
                  }
              },
              error: function () {
                  alert("RestAPI서버가 작동하지 않습니다. 다음에 이용해 주세요.");
              }
          });
          return false;
        }
    }

  });

  
});

function ptn_signup_form_submit(f){

}
</script>