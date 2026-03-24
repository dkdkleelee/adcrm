
<?php

header('Content-Type: application/json; charset=UTF-8');

require_once '../../common.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);

$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

$logFile = '/home/withus/withusCRM/data/log/googleParam.log';
$current_time = date('Y-m-d H:i:s');
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);
$logData2 = "TIME: [" . $current_time . "] PARAMETERS: [" . var_export($data, true) . "]";
file_put_contents($logFile, "RESULT : [" . $logData2 . "]" . PHP_EOL, FILE_APPEND | LOCK_EX);

// 절대 경로로 로그 파일 경로 설정
$logFilePath = __DIR__ . '/test.log';

if (is_array($data)) {
    // 로그에 기록할 문자열 초기화
    $logData = sprintf("[%s] Received Data:\n", date("Y-m-d H:i:s"));
    error_log($logData, 3, $logFilePath);

    $param_pg_uri = $data['pg_uri'];
    $param_phone = $data['tel'];
    $name = $data['name'];
    $option1 = $data['option1'];
    $option2 = $data['option2'];
    $option3 = $data['option3'];
    $option4 = $data['option4'];
    $option5 = $data['option5'];
    $option6 = $data['option6'];

    $tel = formatPhoneNumber($param_phone);

    $pageSql = "
    select *
        from {$g5['crm_page']}
        where pg_uri = '{$param_pg_uri}'
    ";
    $page = sql_fetch($pageSql);

    $page_idx = $page['page_idx'];
    $pg_uri = $page['pg_uri'];
    $pg_domain = $page['pg_domain'];
    $pg_memo = $page['pg_memo'];
    $pg_des_idx = $page['pg_des_idx'];
    $pg_deptno = $page['pg_deptno'];
    $pg_mb_emp = $page['pg_mb_emp'];
    $pg_ptn_idx = $page['pg_ptn_idx'];
    $pg_mb_ptn = $page['pg_mb_ptn'];
    $pg_platform = $page['pg_platform'];
    $pg_title = $page['pg_title'];
    $pg_api_yn = $page['pg_api_yn'];
    $pg_db_sms_yn = $page['pg_db_sms_yn'];
    $pg_db_user_sms_yn = $page['pg_db_user_sms_yn'];
    $pg_api_kind = $page['pg_api_kind'];
    $pg_api_url = $page['pg_api_url'];
    $pg_api_add_param = $page['pg_api_add_param'];
    $pg_api_return_param = $page['pg_api_return_param'];
    $pg_api_param_way = $page['pg_api_param_way'];
    $pg_api_return_way = $page['pg_api_return_way'];
    $pg_api_success = $page['pg_api_success'];
    $pg_api_fail = $page['pg_api_fail'];
    $pg_api_duplicate = $page['pg_api_duplicate'];

    if($param_pg_uri != $pg_uri) {
        echo "invalid page code";
        exit;
    }

    $hp = preg_replace('/[^0-9]/', '', $tel);
    $tel = preg_replace('/(^02.{0}|^01.{1}|^15.{2}|^16.{2}|^18.{2}|[0-9]{3})([0-9]+)([0-9]{4})/', '$1-$2-$3', $hp);

    $hp_arr = explode( '-', $tel );
    $tel1 = $hp_arr[0];
    $tel2 = $hp_arr[1];
    $tel3 = $hp_arr[2];

    $valid = valid_phone($tel1, $tel2, $tel3);
    $use_yn = existByPtn($pg_ptn_idx, $tel);

    if($use_yn != "Y") {
        echo "exist tel number";    
        exit;
    }

    if($valid == true) {

        $ins_sql = "
        insert into {$g5['crm_landing']} set
             land_pg_idx = {$page_idx}
            ,land_ptn_idx = {$pg_ptn_idx}
            ,land_deptno = " . (is_null($pg_deptno) ? 'NULL' : $pg_deptno) . "
            ,land_empno = " . (is_null($pg_mb_emp) ? 'NULL' : $pg_mb_emp) . "
            ,name = '{$name}'
            ,tel = HEX(AES_ENCRYPT('{$tel}', 'withus_secret_key'))
            ,hp = HEX(AES_ENCRYPT('{$hp}', 'withus_secret_key')) 
            ,tel1 = '{$tel1}'
            ,tel2 = HEX(AES_ENCRYPT('{$tel2}', 'withus_secret_key'))
            ,tel3 = '{$tel3}'
            ,option1 = '{$option1}'
            ,option2 = '{$option2}'
            ,option3 = '{$option3}'
            ,option4 = '{$option4}'
            ,option5 = '{$option5}'
            ,option6 = '{$option6}' 
            ,inflow_path = 'google_sheet'
            ,api_send_yn = 'N'
            ,land_used_data = 'N'
            ,insert_date = now()
            ,insert_date2 = CURDATE()
            ,update_date = now()
            ,insert_user = 'api'
            ,update_user = 'google'
            ,client_ip = '{$_SERVER['REMOTE_ADDR']}'
        ";
        isSqlError(sql_query($ins_sql), $ins_sql);
        $land_idx = sql_insert_id();
    
        if($pg_api_yn == "Y") {
            $oCurl = curl_init();
            //GET
            if ($pg_api_param_way == "4") {
                $sendParam = $pg_api_url;
                $sendParam = str_replace("{name}", $name, $sendParam);
                $sendParam = str_replace("{tel}", $tel, $sendParam);
                $sendParam = str_replace("{hp}", $hp, $sendParam);
                
                $sendParam = str_replace("{tel1}", $tel1, $sendParam);
                $sendParam = str_replace("{tel2}", $tel2, $sendParam);
                $sendParam = str_replace("{tel3}", $tel3, $sendParam);
    
                $sendParam = str_replace("{date('Y-m-d h:i:s')}", date('Y-m-d h:i:s'), $sendParam);
                $sendParam = str_replace("{date('Y-m-d h:i')}", date('Y-m-d h:i'), $sendParam);
                $sendParam = str_replace("{date('Y-m-d')}", date('Y-m-d'), $sendParam);
                $sendParam = str_replace("{client_ip}", $client_ip, $sendParam);
    
                $sendParam = str_replace("{option1}", $option1, $sendParam);
                $sendParam = str_replace("{option2}", $option2, $sendParam);
                $sendParam = str_replace("{option3}", $option3, $sendParam);
                $sendParam = str_replace("{option4}", $option4, $sendParam);
                $sendParam = str_replace("{option5}", $option5, $sendParam);
                $sendParam = str_replace("{option6}", $option6, $sendParam);
    
                $sendParam = str_replace("{option7}", $option7, $sendParam);
                $sendParam = str_replace("{option8}", $option8, $sendParam);
                $sendParam = str_replace("{option9}", $option9, $sendParam);
                // URL 파싱
                $urlParts = parse_url($sendParam);
                $baseUrl = $urlParts['scheme'] . '://' . $urlParts['host'] . $urlParts['path'];
    
                // 쿼리스트링 파싱
                $queryString = $urlParts['query'];
                parse_str($queryString, $params);
    
                // 필요한 파라미터만 선택하여 동적으로 쿼리스트링 생성
                $dynamicQueryString = http_build_query($params);
    
                // 동적으로 생성된 쿼리스트링을 포함하여 전체 URL 생성
                $dynamicUrl = $baseUrl . '?' . $dynamicQueryString;
                $sendParam = "";
                $sendParam = $dynamicUrl;
    
                $oCurl = curl_init();
                curl_setopt($oCurl, CURLOPT_AUTOREFERER, TRUE);
                curl_setopt($oCurl, CURLOPT_HEADER, 0);
                curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($oCurl, CURLOPT_URL, $sendParam);
                curl_setopt($oCurl, CURLOPT_FOLLOWLOCATION, TRUE);       
            
                $ret = curl_exec($oCurl);
                curl_close($oCurl);
            }
            //POST
            else {
                if (!empty($pg_api_add_param)) {
    
                    $pg_api_add_param = str_replace("{name}", $name, $pg_api_add_param);
                    $pg_api_add_param = str_replace("{tel}", $tel, $pg_api_add_param);
                    $pg_api_add_param = str_replace("{hp}", $hp, $pg_api_add_param);
    
                    $pg_api_add_param = str_replace("{tel1}", $tel1, $pg_api_add_param);
                    $pg_api_add_param = str_replace("{tel2}", $tel2, $pg_api_add_param);
                    $pg_api_add_param = str_replace("{tel3}", $tel3, $pg_api_add_param);
    
                    $pg_api_add_param = str_replace("{date('Y-m-d h:i:s')}", date('Y-m-d h:i:s'), $pg_api_add_param);
                    $pg_api_add_param = str_replace("{date('Y-m-d h:i')}", date('Y-m-d h:i'), $pg_api_add_param);
                    $pg_api_add_param = str_replace("{date('Y-m-d')}", date('Y-m-d'), $pg_api_add_param);
                    $pg_api_add_param = str_replace("{client_ip}", $client_ip, $pg_api_add_param);
    
                    $pg_api_add_param = str_replace("{option1}", $option1, $pg_api_add_param);
                    $pg_api_add_param = str_replace("{option2}", $option2, $pg_api_add_param);
                    $pg_api_add_param = str_replace("{option3}", $option3, $pg_api_add_param);
                    $pg_api_add_param = str_replace("{option4}", $option4, $pg_api_add_param);
                    $pg_api_add_param = str_replace("{option5}", $option5, $pg_api_add_param);
                    $pg_api_add_param = str_replace("{option6}", $option6, $pg_api_add_param);
                    $pg_api_add_param = str_replace("{option7}", $option7, $pg_api_add_param);
                    $pg_api_add_param = str_replace("{option8}", $option8, $pg_api_add_param);
                    $pg_api_add_param = str_replace("{option9}", $option9, $pg_api_add_param);
                }
    
                //기본
                if ($pg_api_param_way == "1") {
                    $sendParam = $pg_api_add_param;
                }
                //array 변환
                else if ($pg_api_param_way == "2") {
                    $sendParam = array();
    
                    $split1 = explode('&', $pg_api_add_param);
    
                    for ($i = 0; $i < count($split1); $i++) {
                        $split2 = explode('=', $split1[$i]);
                        $key = $split2[0];
                        $value = $split2[1];
                        $sendParam[$key] = $value;
                    }
                }
                //json 변환
                else if ($pg_api_param_way == "3") {
    
                    $sendParam = array();
                    $param = array();
                    $split1 = explode('&', $pg_api_add_param);
    
                    for ($i = 0; $i < count($split1); $i++) {
                        // URL 인코딩된 값을 한 번만 분할하여 키와 값을 분리
                        $split2 = explode('=', $split1[$i], 2); // Only split into 2 pieces
                        $key = isset($split2[0]) ? urldecode($split2[0]) : '';
                        $value = isset($split2[1]) ? urldecode($split2[1]) : '';
                        $param[$key] = $value;
                    }
                    $sendParam = json_encode($param);

                    
                    $headers = array("content-type: application/json;charset=utf8;");
                    curl_setopt($oCurl, CURLOPT_HTTPHEADER, $headers);
                }

                //$oCurl = curl_init();
                curl_setopt($oCurl, CURLOPT_URL, $pg_api_url);
                curl_setopt($oCurl, CURLOPT_POST, true);
                curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($oCurl, CURLOPT_POSTFIELDS, $sendParam);
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
                $ret = curl_exec($oCurl);
                
                if ($ret === false) {
                    $http_code = curl_getinfo($oCurl, CURLINFO_HTTP_CODE);
                    $error_msg = curl_error($oCurl);
                    $error_no = curl_errno($oCurl);
                    echo "cURL Error ($error_no): $error_msg\n";
                    exit;
                } else {
                    $http_code = curl_getinfo($oCurl, CURLINFO_HTTP_CODE);
                    echo "HTTP Status Code: $http_code\n";
                    echo "Response: $ret\n";
                }

                curl_close($oCurl);
            }
    
            $use_yn = "Y";
            $api_send_yn = "";
            //JSON RESULT
            if ($pg_api_return_way == "3") {
                $isJson = false;
                $jsdecode = json_decode($ret, true);
                $ret1 = json_encode($jsdecode, JSON_UNESCAPED_UNICODE);
                
                if (json_last_error() == JSON_ERROR_NONE) {
                    $isJson = true;
                }
    
                $pos = strpos($ret, $pg_api_success);
                if ($pos == true) {
                    $api_send_yn = "Y";
                    $use_yn = "Y";
                } 
                $pos = strpos($ret, $pg_api_fail);
                if ($pos == true) {
                    $api_send_yn = "E";
                    $use_yn = "E";
                }
                $pos = strpos($ret, $pg_api_duplicate);
                if ($pos == true) {
                    $api_send_yn = "R";
                    $use_yn = "R";
                }
            }
            //NORMAL RESULT
            else {
                $api_send_yn = "";
                if ($ret == $pg_api_success) {
                    $api_send_yn = "Y"; //succ
                    $use_yn = "Y";
                } else if ($ret == $pg_api_fail) {
                    $api_send_yn = "E"; //error
                    $use_yn = "E";
                } else if ($ret == $pg_api_duplicate) {
                    $api_send_yn = "R"; //dup
                    $use_yn = "R";
                } else {
                    $api_send_yn = "?"; //what
                }
            }
    
            $logData = date("Y-m-d h:i:s") . ' \n | ret:' . $ret . ' | url:' . $pg_api_url . ' | ' . 'var_dump:' . print_r($sendParam, true) . "\n";
            file_put_contents('/home/withus/withusCRM/data/log/googleApi.log', "RESULT : [" . $logData . "]" . PHP_EOL, FILE_APPEND | LOCK_EX);
    
            $sql1 = "	
            update {$g5['crm_landing']} set 
              api_send_yn  = '{$api_send_yn}'
            , update_date  = now()
            , use_yn       = '$use_yn'
            where land_idx = {$land_idx}
            ";
            isSqlError(sql_query($sql1), $sql1);
        }




        if($pg_db_sms_yn == "Y") {

            $query2 = "
            select a.page_idx
                , a.pg_uri
                , a.pg_deptno
                , a.pg_mb_ptn
                , a.pg_db_sms_msg
                , b.ptn_nm
                , group_concat(c.mb_no) as mb_no
                , group_concat(c.mb_hp) as mb_hp
                , group_concat(c.mb_gubun) as mb_gubun
            from {$g5['crm_page']}         a
            left join {$g5['crm_partner']} b on a.pg_ptn_idx = b.ptn_idx
            left join {$g5['member_table']}      c on a.pg_ptn_idx = c.mb_ptnidx 
            where a.page_idx = {$page_idx}
            ";

            $resultOne = sql_fetch($query2);

            // $stmt = $conn->prepare($query2);
            // $stmt->bind_param("i", $page_idx);
            // $stmt->execute();
            // $result2 = $stmt->get_result();
            // $onerow2 = mysqli_fetch_array($result2);

            $sms_page_idx = $resultOne['page_idx'];
            $sms_pg_uri = $resultOne['pg_uri'];
            $sms_pg_deptno = $resultOne['pg_deptno'];
            $pg_mb_ptn = $resultOne['pg_mb_ptn'];
            $sms_pg_db_sms_msg = $resultOne['pg_db_sms_msg'];
            $sms_ptn_nm = $resultOne['ptn_nm'];
        
            $sms_mb_no = $resultOne['mb_no'];
            $sms_mb_hp = $resultOne['mb_hp'];
            $sms_mb_gubun = $resultOne['mb_gubun'];

            $split_sms_mb_no = explode( ',', $sms_mb_no );
            $split_mb_hp = explode( ',', $sms_mb_hp );
            $split_mb_gubun = explode( ',', $sms_mb_gubun );

            $send_msg_tmp = $sms_pg_db_sms_msg;
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
            
            $sms_send_yn = "N";

            for ($i = 0; $i < count($split_sms_mb_no); $i++) {

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

                $sms_send_log = "result_code:" . $result_code . "||message:" . $message . "||sms_msg_id:" . $sms_msg_id . "||success_cnt:" . $success_cnt;
                error_log($sms_send_log ,3, "./send_sms.log");

                $sms_gubun = "2";
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
                    , $loop_mb_hp
                    , NULL
                    , $sms_pg_deptno
                    , $page_idx
                    , '$result_code_value'
                    , '$sms_msg_id_value'
                    , '$send_msg'
                    , '$sms_send_log'
                    , now()
                    , curdate()
                    , NULL
                )";

                isSqlError(sql_query($sql), $sql);
                //$sms_idx = sql_insert_id();

                if($result_code_value == "1") {
                    $sms_send_yn = "Y";
                } else {
                    $sms_send_yn = "N";
                }
            }
        }

        // 클라이언트에게 받은 데이터를 JSON 형식으로 응답
        header('Content-Type: application/json');
        echo json_encode($data);
    } else {
        echo "invalid tel number";    
        exit;
    }
} else {
    echo "Invalid JSON data received.\n";
    exit;
}

function valid_phone($num1, $num2, $num3){

    $num2 = preg_replace("/\s+/", "", $num2);
    $num3 = preg_replace("/\s+/", "", $num3);

    $phone2_array = str_split($num2);
    $phone3_array = str_split($num3);

    $unique1 = array_unique($phone2_array);
    $unique2 = array_unique($phone3_array);

    $second_num = (int) $num2;

    if( is_numeric($num2) == false || is_numeric($num3) == false )  {
        return false;
    } 
    //[1] 010 only
    else if($num1 != "010"){
        return false;
    } 
    //[1]결번조건
    else if($second_num <= 1999) {
        return false;
    }

    //[2] 그룹2, 그룹3 둘다 같은 번호가 연속될시 불량 ex)5555-7777 등
    else if(count($phone2_array) != 4 || count($phone3_array) != 4 ){
        return false;
    }

    else if($num2 == $num3) {
        return false;
    }
    
    else if(count($unique1) == 1 && count($unique2) == 1) {
        return false;
    }

    return true;    
}

function formatPhoneNumber($phoneNumber) {
    // 모든 비숫자 문자를 제거
    $numbersOnly = preg_replace('/\D/', '', $phoneNumber);

    // 국가 코드가 포함된 경우 제거 (예: '82'로 시작하는 경우 '0'으로 대체)
    if (strpos($numbersOnly, '82') === 0 && strlen($numbersOnly) > 10) {
        $numbersOnly = '0' . substr($numbersOnly, 2);
    }
    
    // 첫 번째 숫자가 1로 시작하고 길이가 10인 경우 '0'을 앞에 추가
    if (strlen($numbersOnly) == 10 && $numbersOnly[0] == '1') {
        $numbersOnly = '0' . $numbersOnly;
    }

    // 전화번호를 010-1111-2222 형식으로 변환
    if (strlen($numbersOnly) == 11) {
        return preg_replace('/(\d{3})(\d{4})(\d{4})/', '$1-$2-$3', $numbersOnly);
    }

    // 변환할 수 없는 형식은 그대로 반환
    return "Invalid format";
}

function existByPtn($ptn_idx, $tel) {

    $query = "
    select a.land_idx 
         , a.insert_date 
         , a.inflow_path 
         , count(*) as as_cnt 
    from {$g5['crm_landing']} a
    left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx
    where b.pg_ptn_idx = {$ptn_idx}
    and a.tel = HEX(AES_ENCRYPT('{$tel}', 'withus_secret_key'))
    and a.use_yn = 'Y'
    ";

    $dupchk = sql_fetch($query);

    $as_cnt = (int)$dupchk['as_cnt'];
    $api_cnt = (int)$dupchk['api_cnt'];

    if($api_cnt >= 1) {
        $result = "dup_phone_api";
        return "S";
    } else if($as_cnt >= 1) {
        $result = "dup_phone_landing";
        return "D";
    }

    return "Y";
}

?>
