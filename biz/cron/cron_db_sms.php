<?php

$conn = new mysqli("localhost", "gonplan", "gon!@34qwer@@", "gonplan");

if ($conn->connect_error) {
    file_put_contents('/home/withus/withusCRM/data/log/cron_db_smsApiError.log', " DATABASE ERROR " . PHP_EOL, FILE_APPEND | LOCK_EX);
    die("Connection failed: " . $conn->connect_error);
}

// 문자 인코딩 설정
$conn->set_charset("utf8");

// 세션 시작
session_start();

$apiSql = "
select a.land_sms_idx 
     , a.land_idx
     , convert(aes_decrypt(unhex(a.tel), 'withus_secret_key') using utf8) as tel
     , a.name
     , a.option1
     , a.option2
     , a.option3
     , a.option4
     , a.option5
     , a.option6
     , a.option7
     , a.option8
     , a.option9
     , b.page_idx
     , b.pg_uri
     , b.pg_deptno
     , b.pg_db_sms_yn
     , b.pg_ptn_idx
     , b.pg_db_sms_msg
     , c.ptn_nm 
     , ( select group_concat(sub.mb_no) from {$g5['member_table']} sub where sub.mb_ptnidx = b.pg_ptn_idx ) as mb_no
     , ( select group_concat(sub.mb_hp) from {$g5['member_table']} sub where sub.mb_ptnidx = b.pg_ptn_idx ) as mb_hp
     , ( select group_concat(sub.mb_gubun) from {$g5['member_table']} sub where sub.mb_ptnidx = b.pg_ptn_idx ) as mb_gubun     
  from {$g5['crm_landing_sms']}  a
  left join {$g5['crm_page']}    b on a.land_pg_idx = b.page_idx  
  left join {$g5['crm_partner']} c on b.pg_ptn_idx = c.ptn_idx 
 where 1=1
 and a.result_yn = 'N' 
 and a.sms_date <= now()
 order by a.land_idx asc
 limit 0, 50
";

$resultList = mysqli_query($conn, $apiSql);
$cnt = mysqli_affected_rows($conn);

if (mysqli_affected_rows($conn) > 0) {
    while ($row = mysqli_fetch_assoc($resultList)) {
        $land_sms_idx = $row['land_sms_idx'];
        $land_idx = $row['land_idx'];
        $name = $row['name'];
        $tel = $row['tel'];
        $hp = $row['hp'];

        $option1 = $row['option1'];
        $option2 = $row['option2'];
        $option3 = $row['option3'];
        $option4 = $row['option4'];
        $option5 = $row['option5'];
        $option6 = $row['option6'];
        $option7 = $row['option7'];
        $option8 = $row['option8'];
        $option9 = $row['option9'];

        $pg_db_sms_msg = $row['pg_db_sms_msg'];
        
        $page_idx = $row['page_idx'];
        $sms_pg_uri = $row['pg_uri'];
        $pg_deptno = $row['pg_deptno'];
        
        $ptn_nm = $row['ptn_nm'];

        $sms_mb_no = $row['mb_no'];
        $sms_mb_hp = $row['mb_hp'];
        $sms_mb_gubun = $row['mb_gubun'];

        $split_sms_mb_no = explode( ',', $sms_mb_no );
        $split_mb_hp = explode( ',', $sms_mb_hp );
        $split_mb_gubun = explode( ',', $sms_mb_gubun );

        $send_msg_tmp = $pg_db_sms_msg;
        $send_msg_tmp = str_replace("{name}"    , $name , $send_msg_tmp);
        $send_msg_tmp = str_replace("{tel}"       , $tel    , $send_msg_tmp);
        $send_msg_tmp = str_replace("{hp}"          , $hp       , $send_msg_tmp);
        $send_msg_tmp = str_replace("{option1}"   , $option1, $send_msg_tmp);
        $send_msg_tmp = str_replace("{option2}"   , $option2, $send_msg_tmp);
        $send_msg_tmp = str_replace("{option3}"   , $option3, $send_msg_tmp);
        $send_msg_tmp = str_replace("{option4}"   , $option4, $send_msg_tmp);
        $send_msg_tmp = str_replace("{option5}"   , $option5, $send_msg_tmp);
        $send_msg_tmp = str_replace("{option6}"   , $option6, $send_msg_tmp);
        $send_msg_tmp = str_replace("{option7}"   , $option7, $send_msg_tmp);
        $send_msg_tmp = str_replace("{option8}"   , $option8, $send_msg_tmp);
        $send_msg_tmp = str_replace("{option9}"   , $option9, $send_msg_tmp);
        $send_msg_tmp = str_replace("{pageCode}"    , $sms_pg_uri, $send_msg_tmp);
        $send_msg_tmp = str_replace("{ptn_nm}"      , $sms_ptn_nm, $send_msg_tmp);
        $send_msg = $send_msg_tmp ;

        //crontab 기준 메시지 보낸걸로 처리하여 중복실행방지
        $sql1 = "	
        update {$g5['crm_landing_sms']} set 
          result_yn   = 'Y'
        where land_sms_idx = ?
        ";
        $stmt = $conn->prepare($sql1);
        $stmt->bind_param("i", $land_sms_idx);
        $stmt->execute();
        $stmt->close();

        for ($i = 0; $i < count($split_sms_mb_no); $i++) {

            $sms_send_yn = "N";
            $loop_mb_no = $split_sms_mb_no[$i];
            $loop_mb_hp = $split_mb_hp[$i];
            $loop_mb_gubun = $split_mb_gubun[$i];

            if(strlen($loop_mb_hp) != 13){
                continue;
            }

            if($loop_mb_gubun == 'C') {
                if($pg_mb_ptn != $loop_mb_no) {
                    continue;
                }
            }

            /******************** 인증정보 ********************/
            $sms_url = "https://apis.aligo.in/send/"; // 전송요청 URL
            $sms['user_id'] = "withus1"; // SMS 아이디
            $sms['key'] = "cwlrjrxhj89o9p2fdjqqvcpq4h5a61oo";//인증키
            /****************** 인증정보 끝 ********************/
            /****************** 전송정보 설정시작 ****************/
            $_POST['msg'] = $send_msg; // 메세지 내용 : euc-kr로 치환이 가능한 문자열만 사용하실 수 있습니다. (이모지 사용불가능)
            $_POST['receiver'] = $loop_mb_hp; // 수신번호
            $_POST['sender'] = '010-8168-8151'; // 발신번호
            $_POST['testmode_yn'] = ''; // Y 인경우 실제문자 전송X , 자동취소(환불) 처리
            $_POST['subject'] = 'Y'; //  LMS, MMS 제목 (미입력시 본문중 44Byte 또는 엔터 구분자 첫라인)
            $_POST['msg_type'] = ''; //  SMS, LMS, MMS등 메세지 타입을 지정
            /****************** 전송정보 설정끝 ***************/
            $sms['msg'] = stripslashes($_POST['msg']);
            $sms['receiver'] = $_POST['receiver'];
            $sms['sender'] = $_POST['sender'];
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

            $sms_send_log = "result_code:" . $result_code . "||message:" . $message . "||sms_msg_id:" . $sms_msg_id . "||success_cnt:" . $success_cnt;
            error_log($sms_send_log ,3, "./send_sms.log");

            $sms_gubun = "2";
            $sql = "insert into {$g5['crm_sms']} (sms_gubun, sms_phone, sms_code, sms_deptno, sms_pg_no, sms_result_code, sms_msg_id, sms_send_msg, sms_send_log, insert_date, insert_date2, client_ip) values (?, ?, ?, ?, ?, ?, ?, ?, ?, now(), curdate(), '27.102.82.88')";
            $stmt = $conn->prepare($sql);

            $result_code_value = ($result_code !== "") ? $result_code : NULL;
            $sms_msg_id_value = ($sms_msg_id !== "") ? $sms_msg_id : NULL;

            $stmt->bind_param("sssiiisss", $sms_gubun, $loop_mb_hp, $sms_code, $pg_deptno, $page_idx, $result_code_value, $sms_msg_id_value, $send_msg, $sms_send_log);
            $stmt->execute();
            $sms_idx = mysqli_insert_id($conn);

            if($result_code_value == "1") {
                $sms_send_yn = "Y";
            } else {
                $sms_send_yn = "N";
            }
        }

        // DB한건 기준 List에 메시지 전송된걸로 처리함
        $sql2 = "update {$g5['crm_landing']}
                set sms_send_yn = 'Y'
                where land_idx = ?";
        $stmt = $conn->prepare($sql2);
        $stmt->bind_param("i", $land_idx);
        $stmt->execute();
        $stmt->close();
       
    }
}

mysqli_close($conn);
file_put_contents('/home/withus/withusCRM/data/log/cron_db_smsApi.log', "[" . date("Y-m-d h:i:s") . "]----- END CRONTAB [CALL COUNT : " . $cnt . "] -----" . PHP_EOL, FILE_APPEND | LOCK_EX);
