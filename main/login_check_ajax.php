<?php
include_once('./_common.php');

// 로그인 요청에 대한 응답을 JSON으로 설정
header('Content-Type: application/json');

// POST 데이터 받아오기
$mb_id       = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
$mb_password = isset($_POST['mb_password']) ? trim($_POST['mb_password']) : '';

// 기본적인 응답 템플릿
$response = [
    'success' => false,
    'message' => '',
    'require_auth_code' => false,
];

// SQL 인젝션 및 XSS 방지를 위한 필터링
$pattern = '/(union|select|insert|delete|update|drop|;|\-\-|\')/i';
$script_pattern = '/<\s*script\b[^>]*>(.*?)<\s*\/\s*script\s*>/is';

if (preg_match($pattern, $mb_id) || preg_match($pattern, $mb_password)) {
    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'];
    $record_hist_sql = "
    insert into gnp_record_hist (hist_join_gubun, hist_function, hist_mb_no, hist_mb_name, hist_detail, client_ip) values
    ('직원', 'login', NULL, NULL, '검증되지않은 로그인시도 (로그인실패)','{$ip}');
    ";
    isSqlError(sql_query($record_hist_sql), $record_hist_sql);

    $response['message'] = '검증되지않은 로그인 시도입니다.';
    echo json_encode($response);
    exit;
}

if (preg_match($script_pattern, $mb_id) || preg_match($script_pattern, $mb_password)) {
    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'];
    $record_hist_sql = "
    insert into gnp_record_hist (hist_join_gubun, hist_function, hist_mb_no, hist_mb_name, hist_detail, client_ip) values
    ('직원', 'login', NULL, NULL, '검증되지않은 로그인시도 (로그인실패)','{$ip}');
    ";
    isSqlError(sql_query($record_hist_sql), $record_hist_sql);

    $response['message'] = '검증되지않은 로그인 시도입니다.';
    echo json_encode($response);
    exit;
}

if (!$mb_id || !$mb_password) {
    $response['message'] = '회원아이디나 비밀번호가 공백이면 안됩니다.';
    echo json_encode($response);
    exit;
}

$mb = get_member($mb_id);

if (!$mb || empty($mb['mb_no'])) {
    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'];
    $record_hist_sql = "
    insert into gnp_record_hist (hist_join_gubun, hist_function, hist_mb_no, hist_mb_name, hist_detail, client_ip) values
    ('직원', 'login', NULL, NULL, '회원 정보를 찾을 수 없습니다.', '{$ip}');
    ";
    isSqlError(sql_query($record_hist_sql), $record_hist_sql);

    $response['message'] = '회원 정보를 찾을 수 없습니다.';
    echo json_encode($response);
    exit;
}

$mb_no = isset($mb['mb_no']) ? $mb['mb_no'] : 'UNKNOWN';
$is_social_login = false;
$is_social_password_check = false;

if (function_exists('social_is_login_check')) {
    $is_social_login = social_is_login_check();
    $is_social_password_check = social_is_login_password_check($mb_id);
}

if (!$is_social_password_check && (! (isset($mb['mb_id']) && $mb['mb_id']) || !login_password_check($mb, $mb_password, $mb['mb_password']))) {
    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'];
    $record_hist_sql = "
    insert into gnp_record_hist (hist_join_gubun, hist_function, hist_mb_no, hist_mb_name, hist_detail, client_ip) values
    ('직원', 'login', '{$mb['mb_no']}','{$mb['mb_name']}','로그인 아이디|비밀번호 다름','{$ip}');
    ";
    isSqlError(sql_query($record_hist_sql), $record_hist_sql);

    $response['message'] = '가입된 회원아이 디가 아니거나 비밀번호가 틀립니다.';
    echo json_encode($response);
    exit;
}


$allowed_ips = ['218.52.175.32', '175.195.214.44', '127.0.0.1']; // 허용된 IP 주소 목록
$ip = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'];

if (!in_array($ip, $allowed_ips)) {

    $return_result = true;
    $sms_code = mt_rand(100000, 999999);
    $send_msg = "위드어스 로그인 [".$sms_code."] 인증번호를 입력해주세요.";

    $mb_no = $mb['mb_no'];
    $mb_hp = $mb['mb_hp'];
    $mb_deptno = $mb['mb_deptno'];

    $pattern = '/^010-\d{4}-\d{4}$/';

    if (!preg_match($pattern, $mb_hp)) {
        $response = array('status' => 'not phone'
                        , 'message' => '[인증번호 발송실패] 등록된 연락처가 휴대전화가 아닙니다.('.$mb_hp.')'
        );
        echo json_encode($response);
        exit;
    }


    if (empty($mb_hp)) {
        $response = array('status' => 'not phone'
                        , 'message' => '[인증번호 발송실패] 등록된 연락처가 없습니다.'
        );
        echo json_encode($response);
        exit;
    }
    
     
    /******************** 인증정보 ********************/
    $sms_url = "https://apis.aligo.in/send/"; // 전송요청 URL
    $sms['user_id'] = "withus1"; // SMS 아이디
    $sms['key'] = "cwlrjrxhj89o9p2fdjqqvcpq4h5a61oo";//인증키
    /****************** 인증정보 끝 ********************/
    /****************** 전송정보 설정시작 ****************/
    $_POST['msg'] = $send_msg; // 메세지 내용 : euc-kr로 치환이 가능한 문자열만 사용하실 수 있습니다. (이모지 사용불가능)
    $_POST['receiver'] = $mb_hp; // 수신번호
    $_POST['sender'] = '010-8168-8151'; // 발신번호
    $_POST['testmode_yn'] = ''; // Y 인경우 실제문자 전송X , 자동취소(환불) 처리
    $_POST['subject'] = 'Y'; //  LMS, MMS 제목 (미입력시 본문중 44Byte 또는 엔터 구분자 첫라인)
    $_POST['msg_type'] = ''; //  SMS, LMS, MMS등 메세지 타입을 지정
    /****************** 전송정보 설정끝 ***************/
    $sms['msg'] = stripslashes($_POST['msg']);
    $sms['receiver'] = $_POST['receiver'];
    $sms['sender'] = $_POST['sender'];
    $sms['testmode_yn'] = empty($_POST['testmode_yn']) ? '' : $_POST['testmode_yn'];
    $sms['title'] = $_POST['subject'];
    $sms['msg_type'] = $_POST['msg_type'];
    $host_info = explode("/", $sms_url);

    $port = $host_info[0] == 'https:' ? 443 : 80;

    $oCurl = curl_init();
    curl_setopt($oCurl, CURLOPT_PORT, $port);
    curl_setopt($oCurl, CURLOPT_URL, $sms_url);
    curl_setopt($oCurl, CURLOPT_POST, 1);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS, $sms);
    curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
    $ret = curl_exec($oCurl);
    curl_close($oCurl);

    $sms_msg_id = "";
    $result_code = "";
    $result = json_decode($ret, true);
    if ($result !== null) {
        $result_code = $result['result_code'];
        $message = $result['message'];
        $sms_msg_id = $result['msg_id'];
        $success_cnt = $result['success_cnt'];
    }

    $sms_gubun = "1";
    $result_code_value = ($result_code !== "") ? $result_code : NULL;
    $sms_msg_id_value = ($sms_msg_id !== "") ? $sms_msg_id : NULL;

    $sql = "
    insert into {$g5['crm_sms']} (
          sms_gubun
        , sms_phone
        , sms_code
        , sms_deptno
        , sms_pg_no
        , sms_result_code
        , sms_msg_id
        , sms_send_msg
        , sms_send_log
        , insert_date
        , insert_date2
        , client_ip
    ) values (
          $sms_gubun
        , '$mb_hp'
        , '$sms_code'
        , $mb_deptno
        , NULL
        , '$result_code_value'
        , '$sms_msg_id_value'
        , '$send_msg'
        , '$sms_send_log'
        , now()
        , curdate()
        , NULL
    )";

    isSqlError(sql_query($sql), $sql);
    $sms_idx = sql_insert_id();


    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'];
    $record_hist_sql = "
    insert into gnp_record_hist (hist_join_gubun, hist_function, hist_mb_no, hist_mb_name, hist_detail, client_ip) values
    ('직원', 'login', '{$mb['mb_no']}','{$mb['mb_name']}','문자인증시도','{$ip}');
    ";
    isSqlError(sql_query($record_hist_sql), $record_hist_sql);



    if($result_code_value == "1") {
        $sms_send_yn = "Y";
    } else {
        $sms_send_yn = "N";
    }

    $insert_date = date('Y-m-d H:i:s');
    $response = array('status' => 'success'
                    , 'phone' => $mb_hp
                    , 'sms_idx' => $sms_idx
                    , 'insert_date' => $insert_date
                    , 'mb_id' => $mb['mb_id']
    );
    echo json_encode($response);
    exit;
}

// 로그인 성공 처리
set_session('ss_mb_id', $mb['mb_id']);
set_session('ss_mb_key', md5($mb['mb_datetime'] . get_real_client_ip() . $_SERVER['HTTP_USER_AGENT']));

if(function_exists('update_auth_session_token')) update_auth_session_token($mb['mb_datetime']);

$record_hist_sql = "
insert into gnp_record_hist (hist_join_gubun, hist_function, hist_mb_no, hist_mb_name, hist_detail, client_ip) values
('직원', 'login', '{$mb['mb_no']}','{$mb['mb_name']}','로그인성공','{$ip}');
";
isSqlError(sql_query($record_hist_sql), $record_hist_sql);

$response['success'] = true;
$response['message'] = '로그인 성공';
echo json_encode($response);
exit;
