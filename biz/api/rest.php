<?php
header('Content-Type: application/json; charset=UTF-8');

require_once '../../common.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);

$head = apache_request_headers();
$auth_id = $head['auth_id'];
$auth_key = $head['auth_key'];
$content_type = $head['Content-Type'];
$user_agent = $head['User-Agent'];


if(!in_array('application/json',explode(';',$_SERVER['CONTENT_TYPE']))){
    echo json_encode(array('result_code' => '500'));
    exit;
}

$groupData = array();

$param = file_get_contents('php://input');
$jsonData = json_decode($param, true);
$client_ip = getRealClientIp();

$code = $jsonData['code'];


$jsonDataStr = print_r($jsonData, true);
error_log("[".date("Y-m-d H:i:s")."] auth_id : " .$auth_id ." |  auth_key : " .$auth_key ." |  content_type : " .$content_type ." |  user_agent : " .$user_agent . " |  client_ip : " .$client_ip . " |  code : " .$code . " | jsonData : " . $jsonDataStr ."\n",3, G5_DATA_PATH."/log/receiveApi.log");


$authSql = "
select *
  from {$g5['crm_api_auth']}
 where auth_id = '{$auth_id}'
 and auth_key = '{$auth_key}'
 and auth_ip = '{$client_ip}'
";
$auth = sql_fetch($authSql);

$exist_sql = "
select a.page_idx 
     , a.pg_ptn_idx 
     , a.pg_deptno
     , a.pg_mb_emp
     , a.pg_api_yn
     , a.pg_db_sms_yn
 from {$g5['crm_page']} a
where pg_uri = '{$code}'
";
$exist = sql_fetch($exist_sql);

if($exist == null) {
    $groupData["resultYn"] = "N";
    $groupData["resultMsg"] = "please check the API (HEAD) specification or Contact the manager";
    $groupData["resultDate"] = date("Y-m-d H:i:s");
    $memberData = array();
    $groupData["resultList"] = $memberData;
    echo json_encode($groupData, JSON_UNESCAPED_UNICODE);  
    exit;
} else {
    $groupData["resultYn"] = "Y";
    $groupData["resultMsg"] = "success";
    $groupData["resultDate"] = date("Y-m-d H:i:s");
    $memberData = array();
    $groupData["resultList"] = $memberData;
}

//인증IP체크
if($auth['auth_ip'] != $client_ip ) {
    $groupData["resultYn"] = "N";
    $groupData["resultMsg"] = $client_ip." authentication failed";
    $groupData["resultDate"] = date("Y-m-d H:i:s");
    $memberData = array();
    $groupData["resultList"] = $memberData;
    echo json_encode($groupData, JSON_UNESCAPED_UNICODE);  
    exit;
} 

$inflow_path = $auth['auth_comp'];

$page_idx = $exist['page_idx'];
$ptn_idx = $exist['pg_ptn_idx'];
$pg_deptno = $exist['pg_deptno'];
$pg_mb_emp = $exist['pg_mb_emp'];
$pg_api_yn = $exist['pg_api_yn'];
$pg_db_sms_yn = $exist['pg_db_sms_yn'];

if($pg_api_yn == "Y") {
    $getApiInfoSql = "
    select a.pg_api_kind 
         , a.pg_api_url 
         , a.pg_api_add_param 
         , a.pg_api_param_way
         , a.pg_api_return_way
         , a.pg_api_success 
         , a.pg_api_fail 
         , a.pg_api_duplicate 
        from {$g5['crm_page']} a
        where page_idx = {$page_idx}
    ";
    $data = sql_fetch($getApiInfoSql);
    
    $pg_api_kind = $data['pg_api_kind'];
    $pg_api_url = $data['pg_api_url'];
    $pg_api_add_param = $data['pg_api_add_param'];
    $pg_api_param_way = $data['pg_api_param_way'];
    $pg_api_return_way = $data['pg_api_return_way'];

    $pg_api_success = htmlspecialchars_decode($data['pg_api_success']);
    $pg_api_fail = htmlspecialchars_decode($data['pg_api_fail']);
    $pg_api_duplicate = htmlspecialchars_decode($data['pg_api_duplicate']);

    $url =  $pg_api_url;
}

//$resultList['resultList'] = array();

foreach ($jsonData['data'] as $row) {

    $hp = preg_replace('/[^0-9]/', '', $row['tel']);
    $tel = preg_replace('/(^02.{0}|^01.{1}|^15.{2}|^16.{2}|^18.{2}|[0-9]{3})([0-9]+)([0-9]{4})/', '$1-$2-$3', $hp);

    $hp_arr = explode( '-', $tel );
    $tel1 = $hp_arr[0];
    $tel2 = $hp_arr[1];
    $tel3 = $hp_arr[2];
    
    
    $valid = valid_phone($tel1, $tel2, $tel3);
    if($valid == false) {
        array_push($groupData["resultList"],[
            "code" => "404", "tel" => $tel, "result" => "check tel number"
        ]);
        continue; 
    } 

    $name  = $row['name'];
    $option1 = $row['option1'];
    $option2 = $row['option2'];
    $option3 = $row['option3'];
    $option4 = $row['option4'];
    $option5 = $row['option5'];
    $option6 = $row['option6'];
    $option7 = $row['option7'];
    $option8 = $row['option8'];
    $option9 = $row['option9'];
    $api_send_yn = "";

    $use_yn = existByPtn($ptn_idx, $tel, $g5, $auth['auth_comp']);

    if($use_yn == "S") {
        array_push($groupData["resultList"],[
            "code" => "999", "tel" => $tel, "result" => "duplication tel number"
        ]);
        continue;
    } 
   

    $ins_sql = "
    insert into {$g5['crm_landing']} set
         land_pg_idx = {$exist['page_idx']}
        ,land_ptn_idx = {$exist['pg_ptn_idx']}
        ,land_deptno = {$exist['pg_deptno']}
        ,land_empno = {$exist['pg_mb_emp']}
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
        ,option7 = '{$option7}'
        ,option8 = '{$option8}'
        ,option9 = '{$option9}'
        ,inflow_path = '{$inflow_path}'
        ,inflow_env = 'A'
        ,api_send_yn = '{$api_send_yn}'
        ,land_used_data = 'N'
        ,use_yn = '{$use_yn}'
        ,insert_date = now()
        ,update_date = now()
        ,insert_user = '{$jsonData['auth_id']}'
        ,update_user = '{$jsonData['auth_id']}'
        ,client_ip = '{$client_ip}'
        ,ip = '{$client_ip}'
    ";
    isSqlError(sql_query($ins_sql), $ins_sql);
    $land_idx = sql_insert_id();

    if($land_idx != "") {
        array_push($groupData["resultList"],[
            "code" => "200", "tel" => $tel, "result" => "ok"
        ]);
    } else {
        array_push($groupData["resultList"],[
            "code" => "500", "tel" => $tel, "result" => "error"
        ]);
    }

    if($pg_api_yn == "Y") {

        //GET
        if ($pg_api_param_way == "4") {
            $sendParam = $url;
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
                    $split2 = explode('=', $split1[$i]);
                    $key = $split2[0];
                    $value = $split2[1];
                    $param[$key] = $value;
                }
                $sendParam = json_encode($param);
            }

            $oCurl = curl_init();
            curl_setopt($oCurl, CURLOPT_URL, $url);
            curl_setopt($oCurl, CURLOPT_POST, true);
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, $sendParam);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
            $ret = curl_exec($oCurl);
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

        $logData = date("Y-m-d h:i:s") . ' \n | ret:' . $ret . ' | url:' . $url . ' | ' . 'var_dump:' . print_r($sendParam, true) . "\n";
        file_put_contents('/home/withus/withusCRM/data/log/sendApi.log', "RESULT : [" . $logData . "]" . PHP_EOL, FILE_APPEND | LOCK_EX);

        $sql1 = "	
        update gnp_crm_landing set 
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
        from gnp_crm_page         a
        left join gnp_crm_partner b on a.pg_ptn_idx = b.ptn_idx
        left join gnp_member      c on a.pg_ptn_idx = c.mb_ptnidx 
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
            insert into gnp_crm_sms (
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
}

$resv_param = str_replace("\r\n", "", $param);

$resv_result = json_encode($groupData, JSON_UNESCAPED_UNICODE);


//받은 api 저장
$apiSaveSql = "
insert into {$g5['crm_api_resv']} set
  resv_auth_idx = '{$auth['auth_idx']}'
, resv_page_idx = '{$page_idx}'
, resv_ptn_idx = '{$ptn_idx}'
, resv_ip = '{$client_ip}'
, resv_param = '{$resv_param}'
, resv_result = '{$resv_result}'
, resv_resultyn = 'Y'
, resv_apidate = now()
";

isSqlError(sql_query($apiSaveSql), $apiSaveSql);

echo $resv_result;  
exit;



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


function existByPtn($ptn_idx, $tel, $g5, $auth_comp) {

    $query = "
    select a.land_idx 
         , a.insert_date 
         , a.inflow_path 
         , count(*) as as_cnt 
         , (select count(*) from gnp_crm_landing sub where a.land_idx = sub.land_idx and sub.tel = '{$tel}' and inflow_path = '{$auth_comp}') as api_cnt
    from gnp_crm_landing a
    left join gnp_crm_page b on a.land_pg_idx = b.page_idx
    where b.pg_ptn_idx = {$ptn_idx}
    and a.tel = '{$tel}'
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