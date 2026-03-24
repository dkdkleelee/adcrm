<?php
require_once '../../common.php';

include_once(G5_EDITOR_LIB);
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

//nessesary
$design_idx = isset($_POST['design_idx']) ? strip_tags(clean_xss_attributes($_POST['design_idx'])) : '';

$design_name = isset($_POST['design_name']) ? strip_tags(clean_xss_attributes($_POST['design_name'])) : '';
$des_memo = isset($_POST['des_memo']) ? strip_tags(clean_xss_attributes($_POST['des_memo'])) : '';

$des_deptno = isset($_POST['des_deptno']) ? strip_tags(clean_xss_attributes($_POST['des_deptno'])) : 'NULL';
//$des_mb_no = isset($_POST['des_mb_no']) ? strip_tags(clean_xss_attributes($_POST['des_mb_no'])) : 'NULL';
$des_mb_no = $_POST['des_mb_no']!="" ? trim($_POST['des_mb_no']) : "NULL";

$des_cate_code = isset($_POST['des_cate_code']) ? strip_tags(clean_xss_attributes($_POST['des_cate_code'])) : 'NULL';
$des_status = isset($_POST['des_status']) ? strip_tags(clean_xss_attributes($_POST['des_status'])) : 'NULL';
$des_gubun = $_POST['des_gubun']!="" ? trim($_POST['des_gubun']) : "NULL";

$html_content = '';
if (isset( $_POST['des_html'])) {
    //$html_content = substr(trim($_POST['des_html']),0,65536);
    $html_content = trim($_POST['des_html']);
    $html_content = preg_replace("#[[\\\]+$#", "", $html_content);
    $html_content2 = stripslashes($html_content);
}

sql_query(" SET autocommit=0 ");

if ($w == '') {

    $ins_sql = "
    INSERT INTO {$g5['crm_design']} (
         design_name
        ,des_memo
        ,des_deptno
        ,des_mb_no
        ,des_status
        ,des_gubun
        ,des_cate_code
        ,des_html
        ,des_shortcut
        ,des_screen
        ,use_yn
        ,insert_date
        ,update_date
        ,insert_user
        ,update_user
        ,insert_user_name
        ,update_user_name
    ) VALUES (
         '{$design_name}'
        ,'{$des_memo}'
        , {$des_deptno}
        , {$des_mb_no}
        , {$des_status}
        , {$des_gubun}
        , {$des_cate_code}
        ,'{$html_content}'
        ,'{$des_shortcut}'
        ,'{$des_screen}'
        ,'Y'
        ,now()
        ,now()
        ,'{$member['mb_id']}'
        ,'{$member['mb_id']}'
        ,'{$member['mb_name']}'
        ,'{$member['mb_name']}'
    ); 
    ";
    isSqlError(sql_query($ins_sql), $ins_sql);
    $design_idx = sql_insert_id();

    
} else {


    //$path = DOCUMENT_ROOT.'/landing/'.$exist['pg_uri'];
    //rmdir_all($path);
    
    //고객사코드
    $updPageSql = "
    select a.page_idx
         , a.pg_ptn_idx
         , a.pg_deptno 
         , a.pg_mb_emp
         , a.pg_api_yn
         , a.pg_db_sms_yn
         , a.pg_db_user_sms_yn
         , a.pg_domain 
         , a.pg_uri 
         , a.pg_add_script
         , a.pg_reprnm
         , a.pg_compnm
         , a.pg_tel
         , a.pg_email
         , a.pg_addr
         , a.pg_bznum
         , a.pg_bef_head
         , a.pg_aft_head
         , b.des_html 
         , b.des_shortcut
         , b.des_screen
         , c.comm_pnm 
         , c.comm_nm 
         , a.pg_title
         , a.pg_meta_desc
         , a.pg_meta_keyword
         , a.pg_redirect_url
         , a.pg_alert_msg
         , a.pg_popup_img
         , a.pg_sms_yn
    from {$g5['crm_page']} a
    left join {$g5['crm_design']} b on a.pg_des_idx = b.design_idx 
    left join {$g5['crm_common']} c on b.des_cate_code = c.comm_idx 
    where a.use_yn = 'Y'
    and b.design_idx = {$design_idx}
    ";
    $pageList = sql_query($updPageSql);

    for ($i = 0; $data = sql_fetch_array($pageList); $i++) {

        $path = DOCUMENT_ROOT.'withusLanding/'.$data['pg_domain'].$data['pg_uri'];
        rmdir_all($path);

        $design_html = $html_content2;
        $pg_mb_emp = $data['pg_mb_emp'];
        $pg_deptno = $data['pg_deptno'];
        $pg_domain = $data['pg_domain'];
        $pg_uri = $data['pg_uri'];
        
        $pg_add_script = $data['pg_add_script'];

        $pg_sms_yn = $data['pg_sms_yn'];

        $image_regex = "/(\.(gif|jpe?g|png))$/i";
        if (isset($_FILES['des_shortcut']) && is_uploaded_file($_FILES['des_shortcut']['tmp_name'])) {
            if (preg_match($image_regex, $_FILES['des_shortcut']['name'])) {
                if ($_FILES['des_shortcut']['size'] <= 500000) {
                    $img_name = $_FILES['des_shortcut']['name'];
                    $shortcut = G5_LAND_URL."/design/shortcut/".$design_idx."/".$img_name."";
                }
            }
        } else {
            $shortcut = $data['des_shortcut'];
        }

        if (isset($_FILES['des_screen']) && is_uploaded_file($_FILES['des_screen']['tmp_name'])) {
            if (preg_match($image_regex, $_FILES['des_screen']['name'])) {
                if ($_FILES['des_screen']['size'] <= 500000) {
                    $img_name = $_FILES['des_screen']['name'];
                    $thumbnail = G5_LAND_URL."/design/shortcut/".$design_idx."/".$img_name."";
                }
            }
        } else {
            $thumbnail = $data['des_screen'];
        }
        
        $page_idx = $data['page_idx'];
        $pg_ptn_idx = $data['pg_ptn_idx'];
        $pg_api_yn = $data['pg_api_yn'];
        $pg_db_sms_yn = $data['pg_db_sms_yn'];
        $pg_db_user_sms_yn = $data['pg_db_user_sms_yn'];

        $pg_reprnm = $data['pg_reprnm'];
        $pg_compnm = $data['pg_compnm'];
        $pg_tel = $data['pg_tel'];
        $pg_email = $data['pg_email'];
        $pg_addr = $data['pg_addr'];
        $pg_bznum = $data['pg_bznum'];

        $pg_title = $data['pg_title'];
        $pg_meta_desc = $data['pg_meta_desc'];
        $pg_meta_keyword = $data['pg_meta_keyword'];
        $pg_redirect_url = $data['pg_redirect_url'];
        $pg_alert_msg = $data['pg_alert_msg'];
        $pg_popup_img = $data['pg_popup_img'];


        $pg_bef_head = '';
        $pg_bef_head2 = '';

        $pg_aft_head = '';
        $pg_aft_head2 = '';

        $thirdScript1 = '';
        $thirdScript2 = '';

        $pg_bef_head = $data['pg_bef_head'];
        $pg_aft_head = $data['pg_aft_head'];


        
$add_auth_import = "";
$add_auth_script = "";

if($pg_sms_yn == "Y") {
    
    $css_file = DOCUMENT_ROOT."withusLanding/$pg_domain/phone_auth.css";

    // 파일이 존재하지 않을 경우
    if (!file_exists($css_file)) {
        
        $add_css = "
.modal-overlay_sms {
    display: none;
    position: fixed;
    z-index: 9998;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal_sms {
    display: flex;
    align-items: center;
    justify-content: center;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content_sms {
    display: flex;
    flex-direction: column;
    align-items: center;
    background-color: #fff;
    padding: 30px;
    border-radius: 20px;
    width: 400px;
    text-align: center;
    box-sizing: border-box;
}

.certification_sms {
    display: inline;
    width: 100%;
}

.modal_input_sms {
    width: 100%;
    border-radius: 5px;
    background-color: #eeeeee;
    border: none;
    padding-left: 10px;
    height: 40px;
}

.resend-button {
    flex-grow: 1;
    margin: 0 2px;
    padding: 0px 15px;
    width: 25%;
    height: 40px;
    border-radius: 5px;
    border: 1px solid #c9c9c9;
    background-color: #fff;
    font-size: 14px;
    line-height: 40px;
}

.modal-title_sms {
    padding: 0px 0;
    font-size: 28px;
    font-weight: bold;
    color: #ff4374;
}

.timer_sms {
    margin-top: 25px;
    margin-bottom: 10px;
    font-size: 24px;
    font-weight: 600;
    color: #666;
}

.button-container_sms {
    display: block;
    justify-content: space-between;
    margin-top: 25px;
    width: 100%;
}

.button-container_sms button {
    flex-grow: 1;
    margin: 5px 2px;
    height: 45px;
    width: 100%;
    border-radius: 5px;
    font-size: 16px;
    font-weight: 600;
}

.confirm-button_sms {
    background-color: #ff4374;
    border: none;
    color: #fff;
}

.cancel-button_sms {
    background-color: #fff;
    border: 1px solid #ff4374;
    color: #ff4374;
}

.landing-form {
    margin-bottom: 20px;
}
";

        file_put_contents($css_file, $add_css);
    }

    $script_file = DOCUMENT_ROOT."withusLanding/$pg_domain/sms_auth.js";

    // 파일이 존재하지 않을 경우
    if (!file_exists($script_file)) {

$ajax_url1 = G5_LAND_URL."/process/sms_send";
$ajax_url2 = G5_LAND_URL."/process/sms_confirm";
$add_script = "
var formData;
$('form').submit(function(e) {
    var asisDate = window.localStorage.getItem('sms_send_date');
    if(asisDate != '') {
        var currentDate = new Date(); // 현재 날짜와 시간을 가져옴
        currentDate.setMinutes(currentDate.getMinutes() - 3); // 현재 시간에 3분을 더함
        var year = currentDate.getFullYear(); // 연도 가져오기 (YYYY 형식)
        var month = String(currentDate.getMonth() + 1).padStart(2, '0'); // 월 가져오기 (MM 형식)
        var day = String(currentDate.getDate()).padStart(2, '0'); // 일 가져오기 (DD 형식)
        var hours = String(currentDate.getHours()).padStart(2, '0'); // 시간 가져오기 (HH 형식)
        var minutes = String(currentDate.getMinutes()).padStart(2, '0'); // 분 가져오기 (mm 형식)
        var seconds = String(currentDate.getSeconds()).padStart(2, '0'); // 초 가져오기 (ss 형식)
        var formattedDate = year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds; // 원하는 포맷으로 조합
        
        if(asisDate == 'undefined' || asisDate == undefined){
            asisDate = '';
        }
        
        if(asisDate > formattedDate) {
            alert('인증코드 발송후 3분이 지나지 않았습니다.잠시후 이용해주세요');
            window.validationDisabled = true;
            return false;
        }
    }

    e.preventDefault();
    var form = $(this);
    var tel1 = form.find(\"select[name='tel1']\").val() || '010'; // Select box 추가
    var tel2 = form.find(\"input[name='tel2']\").val() || '';
    var tel3 = form.find(\"input[name='tel3']\").val() || '';
    var hp = form.find(\"input[name='hp']\").val() || '';

    if (!hp && (!tel1 && !tel2 && !tel3)) {
        alert('연락처 입력값이 없습니다.');
        window.validationDisabled = true;
        return false;
    }

    if (hp) {
        var rawHp = hp.replace(/-/g, ''); 
    
        if (rawHp.length === 8 && /^\d{8}$/.test(rawHp)) {
            tel = '010' + rawHp;
        } else if (rawHp.length === 10 && /^010\d{7}$/.test(rawHp) && (hp.length === 10 || (hp.length === 13 && /^010-\d{4}-\d{4}$/.test(hp)))) {
            tel = rawHp;
        } else {
            alert('연락처 형식이 올바르지 않습니다.');
            window.validationDisabled = true;
            return false;
        }
    }
    else {
        if (tel2.length + tel3.length !== 8) {
            alert('연락처가 입력되지 않았습니다.');
            window.validationDisabled = true;
            return false;
        } else if (tel2.substring(0, 1) < 2) {
            alert('유효하지 않은 연락처입니다.');
            window.validationDisabled = true;
            return false;
        }

        tel = tel1 + '-' + tel2 + '-' + tel3;
    }

    var phoneHiddenInput = document.getElementById('phoneHidden');
    phoneHiddenInput.value = tel;

    var land_pg_idx = document.getElementsByName('land_pg_idx');
    land_pg_idx = land_pg_idx[0].value;

    var land_ptn_idx = document.getElementsByName('land_ptn_idx');
    land_ptn_idx = land_ptn_idx[0].value;

    formData = $(this).serialize();

    formData += '&land_pg_idx=' + encodeURIComponent(land_pg_idx);
    formData += '&land_ptn_idx=' + encodeURIComponent(land_ptn_idx);
    formData += '&tel=' + encodeURIComponent(tel);
    
    $.ajax({
        url: '".$ajax_url1."',
        method: 'POST',
        dataType: 'json',
        data: formData,
        success: function(response) {
            if(response.status == 'over') {
                alert('금일 인증횟수 초과');
                window.validationDisabled = true;
                return false;
            } else if(response.status == 'today') { 
                alert('금일 접수된 데이터가 존재합니다.');
                window.validationDisabled = true;
                return false;
            } else {
                var idxHiddenInput = document.getElementById('idxHidden');
                idxHiddenInput.value = response.sms_idx;
                var dateHiddenInput = document.getElementById('dateHidden');
                dateHiddenInput.value = response.insert_date;
                window.localStorage.setItem('sms_send_date', response.insert_date);
                showModal();
                // 폼 데이터 저장
                // formData = form.serialize();
            }
        }
    });
});

var modalOverlay = document.getElementById('modalOverlay');
var modal = document.getElementById('modal_sms_auth');
var verificationCodeInput = document.getElementById('verificationCode');
var timerDisplay = document.getElementById('timer');

var countdown;
function setTimer(minutes) {
    var seconds = minutes * 60;
    countdown = setInterval(function () {
    var min = Math.floor(seconds / 60);
    var sec = seconds % 60;
    timerDisplay.textContent = min.toString().padStart(2, '0') + ':' + sec.toString().padStart(2, '0');
    seconds--;

    if (seconds < 0) {
        clearInterval(countdown);
        alert('인증이 만료되었습니다.');
        hideModal();
    }
    }, 1000);
}

function showModal() {
    modalOverlay.style.display = 'block';
    setTimer(3);
}

function hideModal() {
    modalOverlay.style.display = 'none';
    clearInterval(countdown);
    timerDisplay.textContent = '03:00';
    verificationCodeInput.value = '';
}

var confirmButton = document.getElementById('confirmButton');
confirmButton.addEventListener('click', function () {
    var sms_idx = document.getElementById('idxHidden').value;
    var verificationCode = verificationCodeInput.value;
    var tel = document.getElementById('phoneHidden').value;
    var insert_date = document.getElementById('dateHidden').value;

    if (formData === null) {
        alert('폼 인덱스를 추출할 수 없습니다.');
        return;
    }
    
    $.ajax({
        url: '".$ajax_url2."',
        method: 'POST',
        dataType: 'json',
        data: { sms_code: verificationCode, tel: tel, sms_idx: sms_idx, insert_date: insert_date }, 
        success: function(response) {
            if(response.status == 'success'){
                hideModal();
                alert('인증 성공');
                var fields = formData.split('&');
                var form = document.createElement('form');
                form.action = 'apply.html';
                form.method = 'POST';
                document.body.appendChild(form);
                
                for (var i = 0; i < fields.length; i++) {
                    var field = fields[i].split('=');
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = decodeURIComponent(field[0]);
                    input.value = decodeURIComponent(field[1]);
                    form.appendChild(input);
                }
                
                // sms_code 값 추가
                var smsCodeInput = document.createElement('input');
                smsCodeInput.type = 'hidden';
                smsCodeInput.name = 'sms_code';
                smsCodeInput.value = verificationCode;
                form.appendChild(smsCodeInput);

                // sms_idx 값 추가
                var smsIdxInput = document.createElement('input');
                smsIdxInput.type = 'hidden';
                smsIdxInput.name = 'sms_idx';
                smsIdxInput.value = sms_idx;
                form.appendChild(smsIdxInput);

                form.submit();
            } else {
                alert('인증실패');
                window.validationDisabled = true;
                return false;
            }
        }
    });
});

var cancelButton = document.getElementById('cancelButton');
cancelButton.addEventListener('click', function () {
    hideModal();
});
";

        file_put_contents($script_file, $add_script);
    }


    $add_auth_import = "<link rel='stylesheet' href='../phone_auth.css'>
    <script src='https://code.jquery.com/jquery-3.6.0.js'></script>
    ";
    $add_auth_html = "
<div class='modal-overlay_sms' id='modalOverlay'>
    <div class='modal_sms' id='modal_sms_auth'>
        <div class='modal-content_sms'>
        <div class='modal-title_sms'>휴대전화 간편인증</div>
        <div class='timer_sms' id='timer'>03:00</div>
        <div class='certification_sms'>
            <input type='text' id='verificationCode' class='modal_input_sms' placeholder='인증코드 (6자리)' required='' minlength='6' maxlength='6' oninput='this.value = this.value.replace(/[^0-9]/g, '')'>
        </div>
        <div class='button-container_sms'>
            <button class='confirm-button_sms' id='confirmButton'>확인</button>
            <button class='cancel-button_sms' id='cancelButton'>취소</button>
        </div>
        </div>
    </div>
</div>
<input type='hidden' id='phoneHidden' name='phoneHidden' value=''>
<input type='hidden' id='idxHidden' name='idxHidden' value=''>
<input type='hidden' id='dateHidden' name='dateHidden' value=''>
<script src='../sms_auth.js'></script>
";

}



$script_file2 = DOCUMENT_ROOT."withusLanding/$pg_domain/validator.js";
// 파일이 존재하지 않을 경우
if (!file_exists($script_file2)) {
    $add_script2 = '
    document.addEventListener(\'DOMContentLoaded\', function() {
        var forms = document.querySelectorAll(\'form\');
        
        forms.forEach(function(form) {
            form.addEventListener(\'submit\', function(e) {
                if (!window.validationDisabled) {
                    var targetElement = e.explicitOriginalTarget || document.activeElement;
    
                    if (targetElement.tagName === \'A\' || targetElement.tagName === \'BUTTON\') {
                        targetElement.disabled = true;
                        targetElement.classList.add(\'disabled\');
                    }
                }
            });
        });
    });
    ';
    file_put_contents($script_file2, $add_script2);
}

        if (isset($pg_bef_head)) {
            //$pg_bef_head = substr(trim($pg_bef_head),0,65536);
            $pg_bef_head = trim($pg_bef_head);
            $pg_bef_head = preg_replace("#[\\\]+$#", "", $pg_bef_head);

            if(strpos($pg_bef_head, 'dable.io') == true) {  
                $thirdScript1 = '
                <script async="" charset="utf-8" src="https://static.dable.io/dist/dablena.min.js"></script>
                ';
            }
            else if(strpos($pg_bef_head, '_tfa.push') == true) {
                $thirdScript1 = '
        <script type="text/javascript" src="https://cdn.taboola.com/scripts/eid.es5.js" charset="UTF-8" async="async"></script>
        <script type="text/javascript" src="https://cdn.taboola.com/scripts/cds-pips.js" charset="UTF-8" async="async"></script>
        <script async="" src="//cdn.taboola.com/libtrc/unip/1396405/tfa.js" id="tb_tfa_script"></script>
                ';
            }
            
            $pg_bef_head2 = stripslashes($thirdScript1.$pg_bef_head);
            $pg_bef_head3 = stripslashes($pg_bef_head);
            
        }

        

        if (isset( $pg_aft_head)) {
            //$pg_aft_head = substr(trim($pg_aft_head),0,65536);
            $pg_aft_head = trim($pg_aft_head);
            $pg_aft_head = preg_replace("#[\\\]+$#", "", $pg_aft_head);

            if(strpos($pg_aft_head, 'dable') == true && $pg_bef_head2 != "") {  
                $thirdScript2 = '<script async="" charset="utf-8" src="https://static.dable.io/dist/dablena.min.js"></script>';
                $pg_bef_head3 = "";
            }
            
            else if(strpos($pg_aft_head, '_tfa.push') == true && $pg_bef_head2 != "") {
                $thirdScript2 = '
        <script type="text/javascript" src="https://cdn.taboola.com/scripts/eid.es5.js" charset="UTF-8" async="async"></script>
        <script type="text/javascript" src="https://cdn.taboola.com/scripts/cds-pips.js" charset="UTF-8" async="async"></script>
        <script async="" src="//cdn.taboola.com/libtrc/unip/1396405/tfa.js" id="tb_tfa_script"></script>
                ';
            }

            $pg_aft_head2 = stripslashes($thirdScript2.$pg_bef_head3.$pg_aft_head);

        }



        if(!empty($pg_add_script)) {
            $add_script_sql = "
            select *
                from {$g5['crm_page_script']} 
                where script_idx in (".$pg_add_script.");
            ";
            $script_list = sql_query($add_script_sql);
            for ($i = 0; $script = sql_fetch_array($script_list); $i++) {
                $append_script .= $script['script_code'];
            }
        }

        $rep_metaTag = '
        <meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1">
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
        <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, maximum-scale=1, user-scalable=no">
        <link rel="shortcut icon" href="'.$shortcut.'">
        <meta name="keywords" content="'.$pg_meta_keyword.'">
        <meta name="news_keywords" content="'.$pg_meta_keyword.'">
        <meta name="description" content="'.$pg_meta_desc.'">

        <meta property="og:type" content="article">
        <meta property="og:locale" content="ko_KR">
        <meta property="og:site_name" content="'.$pg_compnm.'">
        <meta property="og:title" content="'.$pg_meta_keyword.'">
        <meta property="og:url" content="'.$pg_domain.'/'.$pg_uri.'">
        <meta property="og:description" content="'.$pg_meta_desc.'">
        <meta property="og:image" content="'.$thumbnail.'">

        <meta property="dable:author" content="'.$pg_reprnm.'">
        <meta property="dable:image" content="'.$thumbnail.'">
        <meta property="dable:item_id" content="'.$page_idx.'">

        <meta property="article:section" content="'.$resultOne['comm_pnm'].'">
        <meta property="article:section2" content="'.$resultOne['comm_nm'].'">
        <meta property="article:published_time" content="'.$date.'">
        <meta property="article:author" content="'.$pg_reprnm.'">
        <meta property="article:id" content="'.$page_idx.'">

        <meta name="twitter:card" content="'.$thumbnail.'">
        <meta name="twitter:site" content="'.$pg_compnm.'">
        <meta name="twitter:creator" content="'.$pg_compnm.'">
        <meta name="twitter:url" content="'.$pg_domain.'/'.$pg_uri.'">
        <meta name="twitter:image" content="'.$thumbnail.'">
        <meta name="twitter:title" content="'.$pg_meta_keyword.'">
        <meta name="twitter:description" content="'.$pg_meta_desc.'">

        <meta property="discovery:articleId" content="'.$page_idx.'">
        <meta property="discovery:thumbnail" content="'.$thumbnail.'">

        <meta property="dd:content_id" content="'.$page_idx.'">
        <meta property="dd:author" content="'.$pg_reprnm.'">
        <meta property="dd:category" content="'.$resultOne['comm_pnm'].','.$resultOne['comm_nm'].'">
        <meta property="dd:published_time" content="'.$date.'">
        <meta property="dd:modified_time" content="'.$date.'">
        <meta property="dd:publisher" content="'.$pg_compnm.'">
        <meta property="dd:availability" content="true">
        '.$add_auth_import.'
        <script src="../validator.js"></script>
        ';

        $design_html=preg_replace("!<meta(.*?)>!is","" ,$design_html);

        //html 태그 분리
        $split1 = substr($design_html, 0, strpos($design_html, '</head>') +7);
        $split2 = substr($design_html, strpos($design_html, '</head>') +7, strlen($design_html));

        //title 태그 있으면 내용만 replace 없으면 title tag 생성
        if(strpos($split1, '<title>') !== false) {  
            $split1 = preg_replace("/(<(title+)([^>]*)>)(.*?)(<\/(title)>)/", "<title>".$pg_title."</title>" ,$split1); 
        } else {
            $append_script = "<title>".$pg_title."</title>";
        }

        if($pg_db_sms_yn == "Y") {
            $cond_sms_opt = '
        var input_ptn = document.createElement("input");
        input_ptn.type = "hidden";
        input_ptn.name = "pg_db_sms_yn";
        input_ptn.value = "'.$pg_db_sms_yn.'";
        form.appendChild(input_ptn);
            ';
        }

        if($pg_db_user_sms_yn == "Y") {
            $cond_sms_opt2 = '
        var input_ptn = document.createElement("input");
        input_ptn.type = "hidden";
        input_ptn.name = "pg_db_user_sms_yn";
        input_ptn.value = "'.$pg_db_user_sms_yn.'";
        form.appendChild(input_ptn);
            ';
        }


        $shared_sql="
        select count(*) as asis_cnt
             , (select ptn_nm from {$g5['crm_partner']} sub where sub.ptn_idx = {$pg_ptn_idx}) as ptn_nm
             , share_token
          from {$g5['crm_db_share']} 
         where share_parent_ptn = {$pg_ptn_idx}
         and share_parent_page_idx = {$page_idx}
        ";
        $shared = sql_fetch($shared_sql);
        $shared_cnt = (int)$shared['asis_cnt'];
    
        if($shared_cnt > 0) {
            $token = $shared['share_token'];
            $share_script = '
        var input_share = document.createElement("input");
        input_share.type = "hidden";
        input_share.name = "share_token";
        input_share.value = "'.$token.'";
        form.appendChild(input_share);
            ';
        }



        $partner_sql="
        select ptn_ban_phone
          from {$g5['crm_partner']}
         where ptn_idx = {$pg_ptn_idx}
        ";
        $partner = sql_fetch($partner_sql);
    
        if(isset($partner)) {
    
            $ban_phone_script = '
        var input_share = document.createElement("input");
        input_share.type = "hidden";
        input_share.name = "ban_phone";
        input_share.value = "'.$partner['ptn_ban_phone'].'";
        form.appendChild(input_share);
        ';
        }
    


        $ptn_hid_src = '
<script id="default_scrt">
document.addEventListener("DOMContentLoaded", function() {
    var forms = document.getElementsByTagName("form"); 
    for (var i = 0; i < forms.length; i++) {
        var form = forms[i];
        
        var input_page = document.createElement("input");
        input_page.type = "hidden";
        input_page.name = "land_pg_idx";
        input_page.value = "'.$page_idx.'";
        form.appendChild(input_page);
        
        var input_ptn = document.createElement("input");
        input_ptn.type = "hidden";
        input_ptn.name = "land_ptn_idx";
        input_ptn.value = "'.$pg_ptn_idx.'";
        form.appendChild(input_ptn);
    }
});
</script>
        ';

        $php_visit_src = '
        <?php 
        $param_page = '.$page_idx.';
        include_once("../../process/count_plus.php");
        ?>
        ';

        if($pg_sms_yn == "Y") {
            $position = stripos($split2, '</body>');
        
            if ($position !== false) { // `</body>` 태그가 존재하는 경우
                $split2 = substr_replace($split2, $add_auth_html, $position, 0); // "testtest"를 `</body>` 태그 앞에 추가
            } else {
                $split2 .= $add_auth_html; // $add_auth_html을 $split2의 끝에 추가
            }
        }
        
        $final_html = $php_visit_src . $split1 . $rep_metaTag . $pg_bef_head2 .$append_script . $ptn_hid_src . $split2 ;

        //업체명 연락처 사업자번호 등 replace
        $final_html = str_replace("{contReprnm}", $pg_reprnm, $final_html);
        $final_html = str_replace("{contCompName}", $pg_compnm, $final_html);
        $final_html = str_replace("{contTel}", $pg_tel, $final_html);
        $final_html = str_replace("{contEmail}", $pg_email, $final_html);
        $final_html = str_replace("{contAddr}", $pg_addr, $final_html);
        $final_html = str_replace("{contCompNum}", $pg_bznum, $final_html);

        $upload_dir = DOCUMENT_ROOT."withusLanding/$pg_domain";
        if(!is_dir($upload_dir)){
            @mkdir($upload_dir, G5_DIR_PERMISSION);
            @chmod($upload_dir, G5_DIR_PERMISSION);
        }

        $upload_dir = DOCUMENT_ROOT."withusLanding/$pg_domain/$pg_uri";
        if(!is_dir($upload_dir)){
            @mkdir($upload_dir, G5_DIR_PERMISSION);
            @chmod($upload_dir, G5_DIR_PERMISSION);
        }

        $myfile = fopen($upload_dir."/index.html", "w") or die("Unable to open file!");

        //$final_html = trim($final_html);
        //$final_html = trim(html_entity_decode($final_html), " \t\n\r\0\x0B\xC2\xA0");
        $final_html = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $final_html);

        fwrite($myfile, $final_html);
        fclose($myfile);


        $aft_scr = "";
        if($pg_aft_head2 != "") {
            $aft_scr = "
        <html>
        <head>
        ".$pg_aft_head2."
        </head>
        </html>
            ";
        }


        $myfile = fopen($upload_dir."/apply.html", "w") or die("Unable to open file!");
        $submit_html = "
        <?php 
        echo \$submit_pos;
        include_once('../../process/submit.php');
        ?>
        ";

        fwrite($myfile, $submit_html.$aft_scr);
        fclose($myfile);
        
    }

    $upd_sql1 = "
    update {$g5['crm_design']} set
           design_name  = '{$design_name}'
         , des_memo     = '{$des_memo}'
         , des_deptno   = {$des_deptno}
         , des_mb_no    = {$des_mb_no}
         , des_status    = {$des_status}
         , des_gubun    = {$des_gubun}
         , des_cate_code= {$des_cate_code}
         , des_html     = '{$html_content}'
         , update_date  = now()
         , update_user  = '{$member['mb_id']}'
         , update_user_name  = '{$member['mb_name']}'
    where design_idx = {$design_idx}
    ";
    isSqlError(sql_query($upd_sql1), $upd_sql1);
}

// $replace1 = substr_replace($html_content, "<testtest>", 0);
// $replace2 = substr_replace($html_content, "<testtest>", 5);
// $replace3 = preg_replace("<head>", "변경할 문자", $html_content);

// if(strpos($html_content, '<head>') !== false) {  
//     $find = "포함";  
// } else {  
//     $find = "불포함";  
// }  


// design 폴더생성
$design_dir = DOCUMENT_ROOT.'/landing/design';
if(!is_dir($design_dir)){
    @mkdir($design_dir, G5_DIR_PERMISSION);
    @chmod($design_dir, G5_DIR_PERMISSION);
}


// design.shortcut 아이콘 폴더생성
$icon_dir = $design_dir.'/shortcut';
if(!is_dir($icon_dir)){
    @mkdir($icon_dir, G5_DIR_PERMISSION);
    @chmod($icon_dir, G5_DIR_PERMISSION);
}


// 이미지 업로드 시작
$image_regex = "/(\.(gif|jpe?g|png))$/i";
$mb_icon_img = get_mb_icon_name($design_name).'.gif';

if (isset($_FILES['des_shortcut']) && is_uploaded_file($_FILES['des_shortcut']['tmp_name'])) {
    if (preg_match($image_regex, $_FILES['des_shortcut']['name'])) {
        // 아이콘 용량이 설정값보다 이하만 업로드 가능
        if ($_FILES['des_shortcut']['size'] <= 500000) {
            // $mb_dir = G5_DATA_PATH.'/shortcut/';
           
            $final_dir = $icon_dir.'/'.$design_idx;
            @mkdir($final_dir, G5_DIR_PERMISSION);
            @chmod($final_dir, G5_DIR_PERMISSION);

            $img_name = $_FILES['des_shortcut']['name'];
            $dest_path = $final_dir.'/'.$img_name;
            

            move_uploaded_file($_FILES['des_shortcut']['tmp_name'], $dest_path);

            $shortcut_url = G5_LAND_URL."/design/shortcut/".$design_idx."/".$img_name."";
            $upd_sql = "
            update {$g5['crm_design']} set
                  des_shortcut  = '{$shortcut_url}'
            where design_idx = {$design_idx}
            ";
            isSqlError(sql_query($upd_sql), $upd_sql);
            
            chmod($dest_path, G5_FILE_PERMISSION);
            if (file_exists($dest_path)) {
                //=================================================================\
                // 090714
                // gif 파일에 악성코드를 심어 업로드 하는 경우를 방지
                // 에러메세지는 출력하지 않는다.
                //-----------------------------------------------------------------
                $size = @getimagesize($dest_path);
                if (!($size[2] === 1 || $size[2] === 2 || $size[2] === 3)) { // jpg, gif, png 파일이 아니면 올라간 이미지를 삭제한다.
                    @unlink($dest_path);
                } else if ($size[0] > $config['cf_member_icon_width'] || $size[1] > $config['cf_member_icon_height']) {
                    $thumb = null;
                    if($size[2] === 2 || $size[2] === 3) {
                        //jpg 또는 png 파일 적용
                        $thumb = thumbnail($img_name, $final_dir, $final_dir, 50, 50, true, true);
                        if($thumb) {
                            @unlink($dest_path);
                            rename($final_dir.'/'.$thumb, $dest_path);
                        }
                    }
                    if( !$thumb ){
                        // 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
                        //@unlink($dest_path);
                    }
                }
                //=================================================================\
            }
        } 

    } else {
        alert($_FILES['des_shortcut']['name'].'은(는) 이미지 파일이 아닙니다.');
    }
}



$screen_dir = $design_dir.'/screen';
if(!is_dir($screen_dir)){
    @mkdir($screen_dir, G5_DIR_PERMISSION);
    @chmod($screen_dir, G5_DIR_PERMISSION);
}
// 이미지 업로드 시작
if (isset($_FILES['des_screen']) && is_uploaded_file($_FILES['des_screen']['tmp_name'])) {
    if (preg_match($image_regex, $_FILES['des_screen']['name'])) {
        // 아이콘 용량이 설정값보다 이하만 업로드 가능
        if ($_FILES['des_screen']['size'] <= 500000) {
            // $mb_dir = G5_DATA_PATH.'/shortcut/';
           
            $final_dir = $screen_dir.'/'.$design_idx;
            @mkdir($final_dir, G5_DIR_PERMISSION);
            @chmod($final_dir, G5_DIR_PERMISSION);

            $img_name = $_FILES['des_screen']['name'];
            $dest_path = $final_dir.'/'.$img_name;
            

            move_uploaded_file($_FILES['des_screen']['tmp_name'], $dest_path);

            $screen_url = G5_LAND_URL."/design/screen/".$design_idx."/".$img_name."";
            $upd_sql = "
            update {$g5['crm_design']} set
                  des_screen = '{$screen_url}'
            where design_idx = {$design_idx}
            ";
            isSqlError(sql_query($upd_sql), $upd_sql);
            
            chmod($dest_path, G5_FILE_PERMISSION);
            if (file_exists($dest_path)) {
                //=================================================================\
                // 090714
                // gif 파일에 악성코드를 심어 업로드 하는 경우를 방지
                // 에러메세지는 출력하지 않는다.
                //-----------------------------------------------------------------
                $size = @getimagesize($dest_path);
                if (!($size[2] === 1 || $size[2] === 2 || $size[2] === 3)) { // jpg, gif, png 파일이 아니면 올라간 이미지를 삭제한다.
                    @unlink($dest_path);
                }
            }
        } 

    } else {
        alert($_FILES['des_screen']['name'].'은(는) 이미지 파일이 아닙니다.');
    }
}

sql_query("COMMIT");

//alert('저장완료');
//goto_url('design_list?' . $qstr);
goto_url('./design_form?w=u&amp;design_idx=' . $design_idx . '&amp;' . $qstr);
