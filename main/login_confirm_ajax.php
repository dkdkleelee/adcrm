<?php
include_once('./_common.php');

// 로그인 요청에 대한 응답을 JSON으로 설정
header('Content-Type: application/json');
$idxHidden = $_POST['idxHidden'];
$dateHidden = $_POST['dateHidden'];
$phoneHidden = $_POST['phoneHidden'];
$auth_code = $_POST['auth_code'];
$mb_id = $_POST['idHidden'];
$valid_date = date('Y-m-d H:i:s', strtotime($dateHidden . '+3 minutes'));


$sql = "
SELECT * 
  FROM gnp_crm_sms 
 WHERE sms_idx = {$idxHidden} 
   AND sms_phone = '{$phoneHidden}'
   AND sms_code = '{$auth_code}'
   AND insert_date <= NOW()
   AND TIMESTAMPDIFF(MINUTE, insert_date, NOW()) <= 5;
";

$result = sql_query($sql);
$result_cnt = mysqli_num_rows($result);
run_event('member_login_check_before', $mb_id);
$mb = get_member($mb_id);

if ($result_cnt > 0) {
    run_event('login_session_before', $mb, $is_social_login);

    @include_once($member_skin_path.'/login_check.skin.php');

    // 회원아이디 세션 생성
    set_session('ss_mb_id', $mb['mb_id']);
    // FLASH XSS 공격에 대응하기 위하여 회원의 고유키를 생성해 놓는다. 관리자에서 검사함 - 110106
    set_session('ss_mb_key', md5($mb['mb_datetime'] . get_real_client_ip() . $_SERVER['HTTP_USER_AGENT']));
    // 회원의 토큰키를 세션에 저장한다. /common.php 에서 해당 회원의 토큰값을 검사한다.
    if(function_exists('update_auth_session_token')) update_auth_session_token($mb['mb_datetime']);


    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'];
    $record_hist_sql = "
    insert into gnp_record_hist (hist_join_gubun, hist_function, hist_mb_no, hist_mb_name, hist_detail, client_ip) values
    ('직원', 'login', '{$mb['mb_no']}','{$mb['mb_name']}','문자인증 로그인성공','{$ip}');
    ";
    isSqlError(sql_query($record_hist_sql), $record_hist_sql);

    
    $response = array('status' => 'success'
                    , 'redirect_url' => G5_URL          
    );
    echo json_encode($response);
} else {

    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'];
    $record_hist_sql = "
    insert into gnp_record_hist (hist_join_gubun, hist_function, hist_mb_no, hist_mb_name, hist_detail, client_ip) values
    ('직원', 'login', '{$mb['mb_no']}','{$mb['mb_name']}','문자인증실패','{$ip}');
    ";
    isSqlError(sql_query($record_hist_sql), $record_hist_sql);

    $response = array('status' => 'fail');
    echo json_encode($response);
}