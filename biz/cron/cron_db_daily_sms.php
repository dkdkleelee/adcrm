<?php

$conn = new mysqli("localhost", "gonplan", "gon!@34qwer@@", "gonplan");

if ($conn->connect_error) {
    file_put_contents('/home/withus/withusCRM/data/log/cron_db_daily_sms.log', " DATABASE ERROR " . PHP_EOL, FILE_APPEND | LOCK_EX);
    die("Connection failed: " . $conn->connect_error);
}

// 문자 인코딩 설정
$conn->set_charset("utf8");

// 세션 시작
session_start();



$dailySql = "
select a.land_deptno, c.ptn_nm, count(a.land_idx) as count
from gnp_crm_landing a
left join gnp_crm_page b on a.land_pg_idx = b.page_idx
left join gnp_crm_partner c on a.land_ptn_idx = c.ptn_idx
where date(a.insert_date) = subdate(curdate(), 1)
and a.use_yn = 'Y'
and a.land_deptno != 9
and a.land_ptn_idx is not null
group by a.land_deptno, c.ptn_nm
";

$resultList = mysqli_query($conn, $dailySql);
$cnt = mysqli_affected_rows($conn);
$deptTexts = []; // 부서별 텍스트를 저장할 연관 배열 초기화


// 문자 발송을 위한 기본 설정
$_apiURL = 'https://kakaoapi.aligo.in/akv10/alimtalk/send/';
$_hostInfo = parse_url($_apiURL);
$_port = (strtolower($_hostInfo['scheme']) == 'https') ? 443 : 80;
$sender = '010-8168-8151'; // 발신번호

// 부서별 담당자 정보
$deptManagers = [];


$teamNames = [];

$lastDeptNo = null; // 마지막으로 처리된 부서 번호
$deptText = ""; // 부서별 문자 내용
$totalCount = 0; // 부서별 건수
$send_sms_cnt = 1; // 부서별 건수

while ($row = mysqli_fetch_assoc($resultList)) {
    $land_deptno = $row['land_deptno'];
    $ptn_nm = $row['ptn_nm'];
    $count = $row['count'];

    if ($lastDeptNo !== null && $land_deptno != $lastDeptNo) {

        $deptText .= '['.date('Y-m-d', $_SERVER['REQUEST_TIME']-86400).']';
        $deptText .= " 총 건수: $totalCount\n";

        $message =<<<EOT
        GONPALN #{팀명}
        #{일자} 
        총건수 : #{총건수} 건

        #{고객사별수량}
        EOT;

        $date = new DateTime('yesterday');
        $yesterday = $date->format('Y-m-d');
        $message = str_replace("#{팀명}", $teamNames[$lastDeptNo], $message);
        $message = str_replace("#{일자}", $yesterday, $message);
        $message = str_replace("#{총건수}", $totalCount, $message);
        $message = str_replace("#{고객사별수량}", $deptText, $message);
        
        // 문자를 보내는 로직
        foreach ($deptManagers[$lastDeptNo] as $receiver) {

            if ($receiver === "010-9060-5782") {

                $sms_url = "https://apis.aligo.in/send/";
                $user_id = "withus1"; // SMS 아이디
                $key = "cwlrjrxhj89o9p2fdjqqvcpq4h5a61oo"; // 인증키

                $sms_data = [
                    'user_id'      => $user_id,
                    'key'          => $key,
                    'msg'          => stripslashes($message),  // 전송할 메시지
                    'receiver'     => $receiver,
                    'sender'       => '010-8168-8151',
                    'testmode_yn'  => 'N',
                    'title'        => '',
                    'msg_type'     => 'LMS'
                ];
    
                $oCurl = curl_init();
                curl_setopt($oCurl, CURLOPT_URL, $sms_url);
                curl_setopt($oCurl, CURLOPT_PORT, 443);
                curl_setopt($oCurl, CURLOPT_POST, 1);
                curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($oCurl, CURLOPT_POSTFIELDS, http_build_query($sms_data));
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
                $ret = curl_exec($oCurl);
                if ($ret === false) {
                    file_put_contents("/home/withus/withusCRM/data/log/cron_db_daily_sms.log", "cURL Error: " . curl_error($oCurl) . PHP_EOL, FILE_APPEND | LOCK_EX);
                } else {
                    file_put_contents("/home/withus/withusCRM/data/log/cron_db_daily_sms.log", "API Response: " . $ret . PHP_EOL, FILE_APPEND | LOCK_EX);
                }
                curl_close($oCurl);

                ins_db_sms($conn, $ret, $receiver, $lastDeptNo, stripslashes($deptText), $send_sms_cnt);
                $send_sms_cnt = $send_sms_cnt + 1;

                continue;
            }


            $_variables =   array(
                'apikey'      => 'cwlrjrxhj89o9p2fdjqqvcpq4h5a61oo',
                'userid'      => 'withus1',
                'senderkey'   => '18f7e7aa04c103db58eb8f405e8d12d9c4ad578c',
                'tpl_code'    => 'TX_1459',
                'sender'      => '0327155274',
                'subject_1'   => '오늘의DB건수',
                'receiver_1'  => $receiver,
                'message_1'   => $message,
            );

            $oCurl = curl_init();
            curl_setopt($oCurl, CURLOPT_PORT, $_port);
            curl_setopt($oCurl, CURLOPT_URL, $_apiURL);
            curl_setopt($oCurl, CURLOPT_POST, 1);
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, http_build_query($_variables));
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            
            $ret = curl_exec($oCurl);
            if ($ret === false) {
                file_put_contents("/home/withus/withusCRM/data/log/cron_db_daily_sms.log", "cURL Error: " . curl_error($oCurl) . PHP_EOL, FILE_APPEND | LOCK_EX);
            } else {
                file_put_contents("/home/withus/withusCRM/data/log/cron_db_daily_sms.log", "API Response: " . $ret . PHP_EOL, FILE_APPEND | LOCK_EX);
            }
            curl_close($oCurl);

            ins_db_sms($conn, $ret, $receiver, $lastDeptNo, stripslashes($deptText), $send_sms_cnt);
            $send_sms_cnt = $send_sms_cnt + 1;
        }
        
        // 부서 내용과 건수를 초기화
        $deptText = "";
        $totalCount = 0;
    }

    // 새로운 부서 내용 누적
    $deptText .= "$ptn_nm: $count 건\n";
    $totalCount += $count;
    $lastDeptNo = $land_deptno;
}

// 마지막 부서에 대한 문자 메시지 전송
if ($deptText !== "") {

    $deptText .= "총 건수: $totalCount\n";

    $message =<<<EOT
    GONPALN #{팀명}
    #{일자} 
    총건수 : #{총건수} 건

    #{고객사별수량}
    EOT;

    $date = new DateTime('yesterday');
    $yesterday = $date->format('Y-m-d');
    $message = str_replace("#{팀명}", $teamNames[$lastDeptNo], $message);
    $message = str_replace("#{일자}", $yesterday, $message);
    $message = str_replace("#{총건수}", $totalCount, $message);
    $message = str_replace("#{고객사별수량}", $deptText, $message);
    
    foreach ($deptManagers[$lastDeptNo] as $receiver) {
        
        $_variables =   array(
            'apikey'      => 'cwlrjrxhj89o9p2fdjqqvcpq4h5a61oo',
            'userid'      => 'withus1',
            'senderkey'   => '18f7e7aa04c103db58eb8f405e8d12d9c4ad578c',
            'tpl_code'    => 'TX_1459',
            'sender'      => '0327155274',
            'subject_1'   => '오늘의DB건수',
            'receiver_1'  => $receiver,
            'message_1'   => $message,
        );

        $oCurl = curl_init();
        curl_setopt($oCurl, CURLOPT_PORT, $_port);
        curl_setopt($oCurl, CURLOPT_URL, $_apiURL);
        curl_setopt($oCurl, CURLOPT_POST, 1);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, http_build_query($_variables));
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        
        $ret = curl_exec($oCurl);
        if ($ret === false) {
            file_put_contents("/home/withus/withusCRM/data/log/cron_db_daily_sms.log", "cURL Error: " . curl_error($oCurl) . PHP_EOL, FILE_APPEND | LOCK_EX);
        } else {
            file_put_contents("/home/withus/withusCRM/data/log/cron_db_daily_sms.log", "API Response: " . $ret . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
        curl_close($oCurl);

        ins_db_sms($conn, $ret, $receiver, $lastDeptNo, stripslashes($deptText), $send_sms_cnt);
        $send_sms_cnt = $send_sms_cnt + 1;
    }
}

function ins_db_sms($conn, $ret, $receiver, $deptno, $send_msg, $send_sms_cnt){
    $sms_msg_id = "";
    $result_code = "";
    $message = "";
    $success_cnt = 0;
    $result = json_decode($ret, true);
    if ($result['code'] == 0) {
        $result_code = 1;
        $message = $result['message'];
        $sms_msg_id = $result['info']['mid'];
        $success_cnt = 1;
    } else {
        $message = $result ? $result['message'] : "Invalid response";
    }

    file_put_contents("/home/withus/withusCRM/data/log/cron_db_daily_sms.log", "result_code:" . $result_code . "||message:" . $message . "||sms_msg_id:" . $sms_msg_id . "||success_cnt:" . $success_cnt. "||send_sms_cnt:" . $send_sms_cnt  . PHP_EOL, FILE_APPEND | LOCK_EX);

    $sms_gubun = "3";
    $sql = "insert into gnp_crm_sms (sms_gubun, sms_phone, sms_code, sms_deptno, sms_pg_no, sms_result_code, sms_msg_id, sms_send_msg, sms_send_log, insert_date, insert_date2, client_ip) values (?, ?, ?, ?, ?, ?, ?, ?, ?, now(), curdate(), '27.102.82.88')";
    $stmt = $conn->prepare($sql);

    $result_code_value = ($result_code !== "") ? $result_code : NULL;
    $sms_msg_id_value = ($sms_msg_id !== "") ? $sms_msg_id : NULL;
    $sms_code = NULL; // sms_code를 NULL로 초기화
    $page_idx = NULL; // sms_code를 NULL로 초기화

    try {
        $stmt->bind_param("sssiiisss", $sms_gubun, $receiver, $sms_code, $deptno, $page_idx, $result_code_value, $sms_msg_id_value, $send_msg, $ret);
        $stmt->execute();
        $sms_idx = mysqli_insert_id($conn);
    } catch (Exception $e) {
        file_put_contents("/home/withus/withusCRM/data/log/cron_db_daily_sms.log", "Error inserting SMS log: " . $e->getMessage() . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

mysqli_close($conn);
file_put_contents("/home/withus/withusCRM/data/log/cron_db_daily_sms.log", "[" . date("Y-m-d h:i:s") . "]----- ] -----" . PHP_EOL, FILE_APPEND | LOCK_EX);
