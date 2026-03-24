<?php
require_once '../../common.php';
include_once(G5_PATH . '/head.php');

if ($w == '') {
  $title = " 임직원등록";
} elseif ($w == 'u') {
  $title = "임직원수정";
  $resultOneSql = "
    select a.*
    from {$g5['member_table']} a
    where mb_id = '{$mb_id}'
    ";
  $resultOne = sql_fetch($resultOneSql);

  $resultOneSql2 = "
    select *
    from {$g5['crm_whiteip']}
    where insert_user = '{$member['mb_id']}'
    and temp_yn = 'Y'
    and start_date <= now() 
    and end_date >= now()
    ";
    $resultOne2 = sql_fetch($resultOneSql2);
  
    $white_ip = $resultOne2['white_ip'];
    if($white_ip != "") {
      $start_date = $resultOne2['start_date'];
      $end_date = $resultOne2['end_date'];

      $white_checked = "checked";
      //$white_disabled = "disabled";
    }
}

$g5['title'] = $title;
include_once(G5_PATH . '/head.php');


$dept_sql = "select *
from {$g5['crm_depart']}
where parent_deptno != 1
order by deptno ";
$dept_result = sql_query($dept_sql);

add_javascript('<script src="'.G5_JS_URL.'/common.js"></script>', 0);
add_javascript(G5_POSTCODE_JS, 0);    

?>



<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><?php echo $title ?></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="/">Home</a></li>
          <li class="breadcrumb-item"><a href="javascript:history.back()">부서별직원</a></li>
          <li class="breadcrumb-item active"><?php echo $title ?></li>
        </ol>
      </div>
    </div>
</section>




<section class="content">
  <div class="container-fluid">
    <form name="employeeForm" id="employeeForm" action="<?php echo G5_BBS_URL.'/register_form_update_emp' ?>" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">

      <div class="card card-danger">
        <div class="card-header">
          <h3 class="card-title">필수항목</h3>
        </div>

        <div class="card-body">
          <input type="hidden" name="w" value="<?php echo $w ?>">
          <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
          <input type="hidden" name="stx" value="<?php echo $stx ?>">
          <input type="hidden" name="sst" value="<?php echo $sst ?>">
          <input type="hidden" name="sod" value="<?php echo $sod ?>">
          <input type="hidden" name="page" value="<?php echo $page ?>">
          <input type="hidden" name="token" value="">
          
          <input type="hidden" name="mb_deptno" value="<?php echo $member['mb_deptno'] ?>">
          


          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label><code>아이디*</code></label>
                <input type="text" name="mb_id" value="<?php echo $resultOne['mb_id'] ?>" id="reg_mb_id" class="form-control" minlength="3" maxlength="20" placeholder="아이디" oninput="this.value = this.value.replace(/[^0-9a-z.]/g, '').replace(/(\..*)\./g, '$1');" <?php echo $required ?> <?php echo $readonly; ?>>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label><code>비밀번호*</code></label>
                <input type="password" name="mb_password" id="reg_mb_password" class="form-control" minlength="6" maxlength="20" placeholder="(초기비빌번호는 ID와 동일하게 설정)" >
              </div>
            </div>
          </div>


          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label><code>이름*</code></label>
                <input type="text" id="reg_mb_name" name="mb_name" value="<?php echo get_text($resultOne['mb_name']) ?>" class="form-control" size="10" placeholder="이름" <?php echo $required ?> <?php echo $readonly; ?>>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label><code>닉네임*</code></label>
                <input type="text" name="mb_nick" value="<?php echo get_text($resultOne['mb_nick']) ?>" id="reg_mb_nick" class="form-control" size="10" maxlength="20" placeholder="닉네임" <?php echo $required ?>>
              </div>
            </div>
          </div>


          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label><code>휴대전화*</code></label>
                <input type="text" name="mb_hp" value="<?php echo get_text($resultOne['mb_hp']) ?>" id="reg_mb_hp" class="form-control" minlength="13" maxlength="13" placeholder="연락처(010-1234-5678)" oninput="telHyphen(this);" <?php echo $required ?>>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label><code>이메일*</code></label>
                <input type="text" name="mb_email" value="<?php echo get_text($resultOne['mb_email']) ?>" id="reg_mb_email" class="form-control" size="70" maxlength="100" placeholder="E-mail" <?php echo $required ?>>
              </div>
            </div>
          </div>


          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label><code>생년월일*</code></label>
                <input type="date" name="mb_birth" value="<?php echo $w == "u" ? get_text($resultOne['mb_hp']) : "1990-01-01"?>" id="reg_mb_birth" class="form-control" size="11" maxlength="10">
              </div>
            </div>
          
            <div class="col-md-6">
              <div class="form-group">
                <label><code>부서*</code></label>
                  <select id="mb_deptno" name="mb_deptno" class="form-control" <?php echo $member['mb_level'] >= 8 ? '' : 'disabled' ?>>
                  <?php for ($i = 0; $dept = sql_fetch_array($dept_result); $j++) {  ?>
                    <option value="<?php echo $dept['deptno'] ?>" <?php echo get_selected($member['mb_deptno'], $dept['deptno']); ?>><?php echo $dept['deptnm'] ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
          </div>


          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label><code>시스템권한*</code></label>
                <select id="mb_level" name="mb_level" class="form-control" <?php echo $member['mb_level'] >= 6 ? '' : 'disabled' ?>>
                    <option value="4" <?php echo get_selected($resultOne['mb_level'], '4'); ?>>영업직</option>
                    <option value="5" <?php echo get_selected($resultOne['mb_level'], '5'); ?>>일반직원</option>
                    <option value="6" <?php echo get_selected($resultOne['mb_level'], '6'); ?>>관리자</option>
                    <?php if($member['mb_level'] >= 10) { ?>
                    <option value="7" <?php echo get_selected($resultOne['mb_level'], '7'); ?>>개발자</option>
                    <?php } ?>
                </select>
              </div>
            </div>
            <?php if($w == "u") { ?>

            <div class="col-md-6">
                <label>랜딩IP등록 [<?php echo substr($end_date , 0,10) ?>까지]</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <input type="checkbox" id="chkWhiteIp" aria-label="Checkbox" <?php echo $white_checked ?>>
                        </div>
                    </div>
                    <input type="text" id="inpWhiteIp" name="inpWhiteIp" value="<?php echo $white_ip ?>" class="form-control" placeholder="입력 텍스트" disabled>
                    <div class="input-group-append">
                        <button type="button" id="saveButton" class="btn btn-primary" disabled>저장</button>
                    </div>
                </div>
            </div>

            <?php } ?>

          </div>



          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label><code>연차개수*</code></label>
                <input type="text" id="mb_vaca_cnt" name="mb_vaca_cnt" value="<?php echo get_text($resultOne['mb_vaca_cnt']) ?>" id="mb_vaca_cnt" class="form-control" maxlength="5" placeholder="반차포함한 연차 총 개수 (숫자만)" <?php echo $member['mb_level'] >= 6 ? '' : 'disabled' ?>>
              </div>
            </div>
           
          </div>

        </div>
      </div>


      <div class="card card-primaty">
        <div class="card-header">
          <h3 class="card-title">옵션입력</h3>
        </div>
        <div class="card-body">
          <div class="row">
            
            <div class="col-md-2">
              <div class="form-group">
                <label>주소</label>
                <div class="input-group">
                  <input type="text" name="mb_zip" value="<?php echo $resultOne['mb_zip1'].$resultOne['mb_zip2']; ?>" id="reg_mb_zip" <?php echo $config['cf_req_addr']?"required":""; ?> class="form-control" maxlength="6"  placeholder="우편번호">
                  <div class="input-group-prepend">
                    <button type="button" class="btn btn-outline-warning" onclick="win_zip('employeeForm', 'mb_zip', 'mb_addr1', 'mb_addr2', 'mb_addr3', 'mb_addr_jibeon');">주소검색</button>
                  </div>
                </div>
                
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
              <label>기본주소</label>
              <input type="text" name="mb_addr1" value="<?php echo get_text($resultOne['mb_addr1']) ?>" id="reg_mb_addr1" <?php echo $config['cf_req_addr']?"required":""; ?> class="form-control" placeholder="기본주소">
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
              <label>상세주소</label>
              <input type="text" name="mb_addr2" value="<?php echo get_text($resultOne['mb_addr2']) ?>" id="reg_mb_addr2" class="form-control" placeholder="상세주소">
              </div>
            </div>

            <div class="col-md-2">
              <div class="form-group">
              <label>최종주소</label>
              <input type="text" name="mb_addr3" value="<?php echo get_text($resultOne['mb_addr3']) ?>" id="reg_mb_addr3" class="form-control" readonly="readonly"  placeholder="최종주소">
              </div>
            </div>

          </div>


          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>프로필이미지</label>
                <div class="custom-file">
                  <input type="file" name="mb_img" id="reg_mb_img" >
                </div>
              </div>
            </div>


            <div class="col-md-6">
              <div class="form-group">
                <label>명함이미지</label>
                <div class="custom-file">
                  <input type="file" name="mb_icon" id="reg_mb_icon" >
                </div>

              </div>
            </div>

          </div>

          <div class="card-footer text-right">
              <button type="button" class="btn btn-default" id="btn_list" onclick="location.href='<?php echo G5_BIZ_URL; ?>/hr/hr_deptemp_list?<?php echo $qstr;?>'">목록</button>
              <button type="submit" class="btn btn-primary" id="btn_insert">등록</button>
          </div>
        </div>

    </form>

  </div>
</section>


<script>
$(document).ready(function() {
      $("#reg_mb_id").change(function () {
        if ($(this).val() != "") {
              var len = $(this).val().length;
              if (len <= 2) {
                  alert("고객ID 3글자이상이어야 합니다");
                  return false;
              }
              var mb_id = $(this).val();
              var act = "dup_member";
              $.ajax({
                  type: "post",
                  url: "<?php echo G5_BIZ_URL?>/common/code_ajax",
                  data: {
                    mb_id: mb_id ,
                    act: act
                  },
                  success: function (result) {
                    
                      //alert(result.employeeCnt);//JSON.stringify(result)
                      if (result == 0) {//중복ID가 존재하지 않으면
                          $("#btn_insert").attr("disabled", false);
                          $("#btn_insert").css("opacity", "1");
                          $("#reg_mb_id").attr('class', 'form-control is-valid');

                      } else {

                          alert("["+mb_id + "] 아이디 중복");
                          $("#btn_insert").attr("disabled", true);
                          $("#btn_insert").css("opacity", "0.5");

                          $("#reg_mb_id").val("");
                          $("#reg_mb_id").attr('class', 'form-control is-invalid');
                      }
                  },
                  error: function () {
                      alert("RestAPI서버가 작동하지 않습니다. 다음에 이용해 주세요.");
                  }
              });

          } else {
              $("#btn_insert").attr("disabled", true);
              $("#btn_insert").css("opacity", "0.5");

              $("#reg_mb_id").val("");
              $("#reg_mb_id").attr('class', 'form-control is-invalid');
          }
    });

    //휴가건수 validation 처리작업 (이정환)
    document.getElementById('mb_vaca_cnt').addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1').replace(/^(\d{2})\d+/, '$1');

        if (this.value.includes('.')) {
            var parts = this.value.split('.');
            var integerPart = parts[0];
            let decimalPart = parts[1];

            if (decimalPart) {
                decimalPart = decimalPart.substring(0, 1); 
                if (decimalPart !== '5') decimalPart = ''; 
            }
            this.value = decimalPart ? `${integerPart}.${decimalPart}` : `${integerPart}.`;
        }
    });

});

function validateForm() {
  document.getElementById("btn_insert").disabled = "disabled";
  return true;
}

// 체크박스 상태에 따라 입력란과 버튼을 활성화 또는 비활성화하는 함수
function toggleInputAndButton() {
    const checkbox = $("#chkWhiteIp");
    const input = $("#inpWhiteIp");
    const saveButton = $("#saveButton");

    if (checkbox.is(":checked")) {
        input.prop("disabled", false);
        saveButton.prop("disabled", false);

        // checkbox가 체크된 상태일 때 IP 주소를 가져오고 input text에 적용
        add_white_ip();
    } else {
        input.prop("disabled", true);
        saveButton.prop("disabled", true);
        input.val(""); // checkbox가 해제되면 input text를 비웁니다.
    }
}


// 체크박스의 변경 이벤트 리스너 추가
$("#chkWhiteIp").change(toggleInputAndButton);

// IP 주소 가져오기 함수
function add_white_ip() {
    $.ajax({
        url: 'https://httpbin.org/ip',
        dataType: 'json',
        success: function (data) {
            // const clientIP = data.origin;
            // alert('랜딩IP등록은 현재부터 일주일간 유효합니다.\n현재 IP 주소: ' + clientIP);
            // $("#inpWhiteIp").val(clientIP); // 가져온 IP 주소를 입력란에 설정

            const clientIP = data.origin;
            const today = new Date();
            const expirationDate = new Date(today);
            expirationDate.setMonth(today.getMonth() + 1);
            const formattedDate = `${expirationDate.getFullYear()}년 ${expirationDate.getMonth() + 1}월 ${expirationDate.getDate()}일 23시59분`;
            const message = `등록 IP는 ${formattedDate}까지 유효합니다.\n현재 IP 주소: ${clientIP}`;
            alert(message);

            // 가져온 IP 주소를 입력란에 설정
            $("#inpWhiteIp").val(clientIP);


        },
        error: function (error) {
            console.error('오류:', error);
            alert('IP 주소를 가져올 수 없습니다.');
        }
    });
}

// 저장 버튼 클릭 이벤트 리스너 추가 (AJAX 호출 등 추가 필요)
$("#saveButton").click(function () {

    var act = "addWhiteIp";
    var inpWhiteIp = $('#inpWhiteIp').val();

    $.ajax({
        type: "post",
        dataType: "json",
        url: "<?php echo G5_BIZ_URL?>/common/code_ajax",
        data: {
          inpWhiteIp: inpWhiteIp ,
          act: act
        },
        success:function(result) {
            if(result < 0) {
                alert("아이피가 수정되었습니다.");
            } else if(result == 0) {
                alert("아이피가 입력되지않았습니다.");
            } else {
                alert("아이피가 등록되었습니다.");
            }
        },
        error: function(xhr) {
            console.log(xhr.responseText);
            alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
            return;
        }
    });
});


</script>





<?php
include_once(G5_PATH . '/tail.php');
