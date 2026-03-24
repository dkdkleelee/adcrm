<?php
require_once '../../common.php';

$act_button     = isset($_POST['act_button']) ? strip_tags($_POST['act_button']) : '';
$pg_uri = isset($_POST['pg_uri']) ? strip_tags(clean_xss_attributes($_POST['pg_uri'])) : '';
$ranTimeYN = isset($_POST['ranTimeYN']) ? strip_tags(clean_xss_attributes($_POST['ranTimeYN'])) : '';
$end_hour = isset($_POST['end_hour']) ? strip_tags(clean_xss_attributes((int)$_POST['end_hour'])) : '';
$allowDuplicates = isset($_POST['allowDuplicates']) ? strip_tags(clean_xss_attributes($_POST['allowDuplicates'])) : '';
if($allowDuplicates == "on") {
    $allowDuplicates = "Y";
} else {
    $allowDuplicates = "N";
}

if ($act_button === "업로드") {

    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'];

    $total_rows = $num_rows - 1;
    $duplicate_rows = 0;
    $dup_messages = array();

    set_time_limit ( 0 );
    ini_set('memory_limit', '50M');

    if(!$pg_uri) {
        alert("코드값 필수항목");
    }

    $is_upload_file = (isset($_FILES['file']['tmp_name']) && $_FILES['file']['tmp_name']) ? 1 : 0;
    
    if( ! $is_upload_file){
        alert("엑셀 파일을 업로드해 주세요.");
    }

    if($is_upload_file) {

        $land_deptno = $member['mb_deptno'];
                
        $exist_sql = "
        select a.page_idx 
             , a.pg_ptn_idx 
             , a.pg_api_yn
             , a.pg_db_sms_yn
             , a.pg_mb_emp
          from {$g5['crm_page']} a
        where pg_uri = '{$pg_uri}'
        ";
        $exist = sql_fetch($exist_sql);

        $pg_api_yn = $exist['pg_api_yn'];
        $pg_db_sms_yn = $exist['pg_db_sms_yn'];
        $land_empno = $exist['pg_mb_emp'];
        $land_empno_value = is_null($land_empno) ? 'NULL' : $land_empno;
        
        if($ranTimeYN == "R") {
            
            // API 사용일시 H || SMS 사용시 H

            if($pg_api_yn == "Y") {
                $api_send_yn = "H";
            } 

            if($pg_db_sms_yn == "Y") {
                $sms_send_yn = "H";
            } 
            
        } 
        else if($ranTimeYN == "U") {
            
            // API 사용일시 H || SMS 사용시 H

            if($pg_api_yn == "Y") {
                $api_send_yn = "H";
            } 

            if($pg_db_sms_yn == "Y") {
                $sms_send_yn = "H";
            } 
            
        } 
        
        if($exist['pg_ptn_idx'] == "" || $exist['pg_ptn_idx'] == null) {
            alert("고객사가 설정되지않아 엑셀 업로드 불가합니다.");
            exit;
        }

        $file = $_FILES['file']['tmp_name'];

        include_once(G5_LIB_PATH.'/PHPExcel/IOFactory.php');

        $objPHPExcel = PHPExcel_IOFactory::load($file);
        $sheet = $objPHPExcel->getSheet(0);

        $num_rows = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        

        $is_continue = true;

        $total_cnt = $num_rows - 1;

        if ($total_cnt > 500) {
            alert("500건 이상 업로드할 수 없습니다. 데이터를 나누어 업로드하세요.", "./land_list?" . $qstr, false);
            exit;
        }
        
        // if($total_cnt % 2 == 1){
        //     $total_cnt = $total_cnt + 1;
        // } 


        if($ranTimeYN == "R") {
            //해당 page code 의 last_insert 시간을 가져옴
            $last_insert_sql = "
            select max(a.insert_date) as last_date 
            from {$g5['crm_landing']} a
            left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx 
            where b.pg_uri = '{$pg_uri}'
            and a.use_yn = 'Y'
            ";
            $last_insert = sql_fetch($last_insert_sql);
            $last_date = $last_insert['last_date'];

            if($last_date > date("Y-m-d H:i:s")) {
                $current_stamp = strtotime($last_date);
                $current_date = date("Y-m-d H:i:s", $current_stamp);
                $finish_stamp = strtotime("+$end_hour minute", strtotime($last_date));
                $finish_date = date("Y-m-d H:i:s", $finish_stamp);
            } else {
                $current_stamp = strtotime("Now");
                $current_date = date("Y-m-d H:i:s", $current_stamp);
                $finish_stamp = strtotime("+$end_hour minute");
                $finish_date = date("Y-m-d H:i:s", $finish_stamp);
            }
           
            //$devide = ((int)$end_hour / $total_cnt);
            //총건수에 시간을 나눈 second 값
            //$gap_term = ($finish_stamp - $current_stamp) / $total_cnt;
            // $ran1 = rand(0,5);
            // $ran2 = rand(0,5);
            // if($ran1 == $ran2) {
            //     $addSec = $gap_term + rand(1,111);
            // } else {
            //     $addSec = $gap_term + rand(1,10);
            // }
            // $timestamp = strtotime("+$addSec seconds");
            // $date = date("Y-m-d H:i:s", $timestamp);
    
            $date_idx = 0;
            $arr_date = array();
    
            for($i=0; $i <$total_cnt; $i++){
                $val = rand($current_stamp, $finish_stamp);
                array_push($arr_date, date('Y-m-d H:i:s', $val));  
            }
            sort($arr_date);
        }
        
        $suc_cnt = 0;
        $err_cnt = 0;
        $dup_cnt = 0;


        if($pg_api_yn == "Y"){

            $getApiInfoSql = "
            select a.pg_uri
                , a.pg_api_yn
                , a.pg_api_kind 
                , a.pg_api_url 
                , a.pg_api_header
                , a.pg_api_key
                , a.pg_api_hmac_use_yn
                , a.pg_api_add_param 
                , a.pg_api_param_way
                , a.pg_api_return_way
                , a.pg_api_success 
                , a.pg_api_fail 
                , a.pg_api_duplicate 
                , a.pg_api_kr_convert
                , a.pg_api_contype
                , a.google_addr
                , a.google_sheet
                , a.google_cell
                from {$g5['crm_page']} a
                where page_idx = {$exist['page_idx']}
            ";
            $data = sql_fetch($getApiInfoSql);

            //$pg_api_yn = $data['pg_api_yn'];
            $pg_uri = $data['pg_uri'];
            
            $pg_api_kind = $data['pg_api_kind'];
            $pg_api_url = $data['pg_api_url'];
            $pg_api_header = $data['pg_api_header'];

            $pg_api_key = $data['pg_api_key'];
            $pg_api_hmac_use_yn = $data['pg_api_hmac_use_yn'];

            $pg_api_add_param = $data['pg_api_add_param'];
            $pg_api_param_way = $data['pg_api_param_way'];
            $pg_api_return_way = $data['pg_api_return_way'];

            $pg_api_success = htmlspecialchars_decode($data['pg_api_success']);
            $pg_api_fail = htmlspecialchars_decode($data['pg_api_fail']);
            $pg_api_duplicate = htmlspecialchars_decode($data['pg_api_duplicate']);

            $pg_api_kr_convert = htmlspecialchars_decode($data['pg_api_kr_convert']);
            $pg_api_contype = htmlspecialchars_decode($data['pg_api_contype']);

            $google_addr = $data['google_addr'];
            $google_sheet = $data['google_sheet'];
            $google_cell = $data['google_cell'];

            $url =  $pg_api_url;    
        }

        for ($i = 2; $i <= $num_rows; $i++) {
            $rowData = $sheet->rangeToArray('A' . $i . ':' . $highestColumn . $i, TRUE, FALSE);
            $name   = $rowData[0][0] != "1" ? addslashes($rowData[0][0]) : '';
            $tel      = $rowData[0][1] != "1" ? addslashes($rowData[0][1]) : '';
            $option1  = $rowData[0][2] != "1" ? addslashes($rowData[0][2]) : '';
            $option2  = $rowData[0][3] != "1" ? addslashes($rowData[0][3]) : '';
            $option3  = $rowData[0][4] != "1" ? addslashes($rowData[0][4]) : '';
            $option4  = $rowData[0][5] != "1" ? addslashes($rowData[0][5]) : '';
            $option5  = $rowData[0][6] != "1" ? addslashes($rowData[0][6]) : '';
            $option6  = $rowData[0][7] != "1" ? addslashes($rowData[0][7]) : '';
            $option7  = $rowData[0][8] != "1" ? addslashes($rowData[0][8]) : '';
            $option8  = $rowData[0][9] != "1" ? addslashes($rowData[0][9]) : '';
            $option9  = $rowData[0][10] != "1" ? addslashes($rowData[0][10]) : '';
            $land_memo  = $rowData[0][11] != "1" ? addslashes($rowData[0][11]) : '';

            //
            if ($rowData[0][12] != "1") {
                $dateStr = $rowData[0][12];
                
                // DateTime 객체 생성 시도
                $dt = DateTime::createFromFormat('n/j/Y H:i', $dateStr);
                
                // 변환이 성공했는지와 형식이 정확한지 검증
                $errors = DateTime::getLastErrors();
                if ($dt !== false && $errors['warning_count'] == 0 && $errors['error_count'] == 0) {
                    // 원하는 형식으로 변환

                    // 초(second)를 0~59 사이의 랜덤 값으로 설정
                    $randomSeconds = rand(0, 59);
                    $dt->setTime($dt->format('H'), $dt->format('i'), $randomSeconds); // 시, 분, 초 설정

                    $insert_date = $dt->format('Y-m-d H:i:s.000');
                } else {
                    $is_continue = false;
                    $return_msg = $i."번째 행 에러발생 엑셀일자 형식이 정해진 양식과 다릅니다. (ex:2024-01-01  10:00:00 AM)";
                }
            } 
            
            $phoneSplit = explode( '-', $tel );
            
            if(strlen($tel) != 13){
                $is_continue = false;
                $return_msg = $i."번째 행 에러발생 [".$tel."] 연락처 양식이 잘못되었습니다.(ex:010-9988-8899)";
            }

            $num1 = $phoneSplit[0];
            $num2 = (int)$phoneSplit[1];
            $num3 = $phoneSplit[2];
            $hp = $phoneSplit[0].$phoneSplit[1].$phoneSplit[2];

            $num2 = preg_replace("/\s+/", "", $num2);
            $num3 = preg_replace("/\s+/", "", $num3);

            if($num1 != "010"){
                $is_continue = false;
                $return_msg = $i."번째 행 에러발생[".$tel."] 010 번호만 가능합니다. 에러발생 상위 데이터는 저장되었습니다.";
            } 

            if($num2 <= 1999) {
                $is_continue = false;
                $return_msg = $i."번째 행 에러발생[".$tel."] 2000번이하 결번입니다. 에러발생 상위 데이터는 저장되었습니다.";
            }

            $phone2_array = str_split($num2);
            $phone3_array = str_split($num3);

            if(count($phone2_array) != 4 || count($phone3_array) != 4 ){
                $is_continue = false;
                $return_msg = $i."번째 행 에러발생[".$tel."] 유효하지않은 자릿수(1)입니다. 에러발생 상위 데이터는 저장되었습니다.";
            }

            if($num2 == $num3) {
                $is_continue = false;
                $return_msg = $i."번째 행 에러발생[".$tel."] 유효하지않은 자릿수(2)입니다. 에러발생 상위 데이터는 저장되었습니다.";
            }

            $unique1 = array_unique($phone2_array);
            $unique2 = array_unique($phone3_array);
            if(count($unique1) == 1 && count($unique2) == 1) {
                $is_continue = false;
                $return_msg = $i."번째 행 에러발생[".$tel."] 유효하지않은 자릿수(3)입니다. 에러발생 상위 데이터는 저장되었습니다.";
            }
            
            if($is_continue == false) {
                //sql_query("ROLLBACK");
                alert($return_msg ,"./land_list");
                exit;
            }
            
            //RANDOM 시간
            if($ranTimeYN == "R") {
                $date = $arr_date[$date_idx];
                $date_idx = $date_idx + 1;
            }
            else if($ranTimeYN == "U") {
                $date = $insert_date;
            }
            //현재 시간
            else {
                $date = date("Y-m-d H:i:s");
            }

            $insert_date2 = date("Y-m-d", strtotime($date));

            //중복허용
            if($allowDuplicates == "N") {
                $encrypted_phone_sql = "HEX(AES_ENCRYPT('{$tel}', 'withus_secret_key'))";
                $dup_chk_sql = "
                    SELECT COUNT(*) AS cnt
                    FROM {$g5['crm_landing']} a
                    WHERE tel = {$encrypted_phone_sql}
                    AND land_ptn_idx = {$exist['pg_ptn_idx']}
                    AND use_yn = 'Y'
                ";
                $chk_cnt = sql_fetch($dup_chk_sql);
            
                if ($chk_cnt['cnt'] > 0) {
                    $duplicate_rows++;
                    $dup_messages[] = $i . "번째 행 [" . $tel . "] 중복연락처,";
                    continue;
                }
            }
            
            //API전송
            if($pg_api_yn == "Y") {

                //Current
                if($ranTimeYN == "C") {

                    $ins_sql = "
                    insert into {$g5['crm_landing']} set
                         land_pg_idx    = {$exist['page_idx']}
                        ,land_ptn_idx   = {$exist['pg_ptn_idx']}
                        ,land_deptno    = $land_deptno
                        ,land_empno     = " . (is_null($land_empno) ? 'NULL' : $land_empno) . "
                        ,land_used_data = 'N'
                        ,name       = '{$name}'
                        ,tel          = HEX(AES_ENCRYPT('{$tel}', 'withus_secret_key'))
                        ,hp             = HEX(AES_ENCRYPT('{$hp}', 'withus_secret_key'))
                        ,tel1         = '{$phoneSplit[0]}'
                        ,tel2         = HEX(AES_ENCRYPT('{$phoneSplit[1]}', 'withus_secret_key'))
                        ,tel3         = '{$phoneSplit[2]}'
                        ,option1      = '{$option1}'
                        ,option2      = '{$option2}'
                        ,option3      = '{$option3}'
                        ,option4      = '{$option4}'
                        ,option5      = '{$option5}'
                        ,option6      = '{$option6}' 
                        ,option7      = '{$option7}' 
                        ,option8      = '{$option8}' 
                        ,option9      = '{$option9}' 
                        ,land_memo      = '{$land_memo}'
                        ,inflow_path    = '엑셀업로드'
                        ,inflow_env     = 'U'
                        ,api_send_yn    = '{$api_send_yn}'
                        ,sms_send_yn    = '{$sms_send_yn}'
                        ,use_yn         = '{$use_yn}'
                        ,insert_date    = '{$date}'
                        ,insert_date2   = '{$insert_date2}'
                        ,update_date    = '{$date}'
                        ,insert_user    = '{$member['mb_id']}'
                        ,update_user    = '{$member['mb_id']}'
                        ,client_ip      = '{$ip}'
                        ,ip      = '{$ip}'
                    ";
                    isSqlError(sql_query($ins_sql), $ins_sql);
                    $land_idx = sql_insert_id();


                    $param = "";

                    if (preg_match('/{.*?}/', $url)) {

                        $ip = "27.102.82.88";
            
                        $url = str_replace("{land_idx}", urlencode($land_idx), $url);
                        $url = str_replace("{name}", urlencode($name), $url);
                        $url = str_replace("{tel}", $tel, $url);
                        $url = str_replace("{hp}", $hp, $url);
                        $url = str_replace("{pg_uri}", urlencode($pg_uri), $url);
                        $url = str_replace("{tel1}", $tel1, $url);
                        $url = str_replace("{tel2}", $tel2, $url);
                        $url = str_replace("{tel3}", $tel3, $url);
                        $url = str_replace("{date('Y-m-d H:i:s')}", urlencode(date('Y-m-d H:i:s')), $url);
                        $url = str_replace("{date('Y-m-d H:i')}", urlencode(date('Y-m-d H:i')), $url);
                        $url = str_replace("{date('Y-m-d')}", urlencode(date('Y-m-d')), $url);
                        $url = str_replace("{client_ip}", $ip, $url);
                        $url = str_replace("{option1}", urlencode($option1), $url);
                        $url = str_replace("{option2}", urlencode($option2), $url);
                        $url = str_replace("{option3}", urlencode($option3), $url);
                        $url = str_replace("{option4}", urlencode($option4), $url);
                        $url = str_replace("{option5}", urlencode($option5), $url);
                        $url = str_replace("{option6}", urlencode($option6), $url);
                        $url = str_replace("{option7}", urlencode($option7), $url);
                        $url = str_replace("{option8}", urlencode($option8), $url);
                        $url = str_replace("{option9}", urlencode($option9), $url);
                    }

                    // 일반 API 호출
                    if($pg_api_kind == "normal") {
                        // pg_api_param_way = GET 방식 처리
                        if($pg_api_param_way == "4") {
                            $param = $url;

                            $param = str_replace("{land_idx}" , $land_idx, $param);
                            $param = str_replace("{name}" , $name, $param);
                            $param = str_replace("{tel}"    , $tel, $param);
                            $param = str_replace("{hp}"       , $hp, $param);

                            $param = str_replace("{pg_uri}", $pg_uri, $param);

                            $param = str_replace("{tel1}", $num1, $param);
                            $param = str_replace("{tel2}", $num2, $param);
                            $param = str_replace("{tel3}", $num3, $param);

                            $param = str_replace("{date('Y-m-d H:i:s')}", date('Y-m-d H:i:s'), $param);
                            $param = str_replace("{date('Y-m-d H:i')}", date('Y-m-d H:i'), $param);
                            $param = str_replace("{date('Y-m-d')}", date('Y-m-d'), $param);
                            $param = str_replace("{client_ip}", "27.102.82.88", $param);
                                        
                            $param = str_replace("{option1}", $option1, $param);
                            $param = str_replace("{option2}", $option2, $param);
                            $param = str_replace("{option3}", $option3, $param);
                            $param = str_replace("{option4}", $option4, $param);
                            $param = str_replace("{option5}", $option5, $param);
                            $param = str_replace("{option6}", $option6, $param);
                            $param = str_replace("{option7}", $option7, $param);
                            $param = str_replace("{option8}", $option8, $param);
                            $param = str_replace("{option9}", $option9, $param);
                            $sendParam = $param;
                            
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
                        // POST + GET 혼합방식
                        else if($pg_api_param_way == "5") {

                            $sendParam = $url;
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
                            if (isset($pg_api_header)) {
                                if (is_array($pg_api_header)) {
                                    $headers = array_merge($headers, $pg_api_header);
                                } else {
                                    $headerArray = array_map('trim', explode(',', $pg_api_header));
                                    $headers = array_merge($headers ?? [], $headerArray);
                                }
                            }
                            if (!empty($headers)) {
                                curl_setopt($oCurl, CURLOPT_HTTPHEADER, $headers);
                            }
                            curl_setopt($oCurl, CURLOPT_URL, $sendParam);
                            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);  // 응답을 문자열로 반환
                            curl_setopt($oCurl, CURLOPT_POST, true);  // POST 방식으로 설정
                            $ret = curl_exec($oCurl);
                            curl_close($oCurl);

                        }

                        // POST 방식들 pg_api_param_way ( 1 : 일반 param 방식 + 2 : json 변환 방식 + 3: array 변환 방식 )
                        else {
                            if(!empty($pg_api_add_param)) {

                                $param = $pg_api_add_param;

                                $param = str_replace("{land_idx}", $land_idx , $param);
                                $param = str_replace("{name}", $name , $param);
                                $param = str_replace("{tel}", $tel, $param);
                                $param = str_replace("{hp}", $hp, $param);
                    
                                $param = str_replace("{pg_uri}", $pg_uri, $param);

                                $param = str_replace("{tel1}", $num1, $param);
                                $param = str_replace("{tel2}", $num2, $param);
                                $param = str_replace("{tel3}", $num3, $param);

                                $param = str_replace("{date('Y-m-d H:i:s')}", date('Y-m-d H:i:s'), $param);
                                $param = str_replace("{date('Y-m-d H:i')}", date('Y-m-d H:i'), $param);
                                $param = str_replace("{date('Y-m-d')}", date('Y-m-d'), $param);
                                $param = str_replace("{client_ip}", "27.102.82.88", $param);
                    
                                $param = str_replace("{option1}", $option1, $param);
                                $param = str_replace("{option2}", $option2, $param);
                                $param = str_replace("{option3}", $option3, $param);
                                $param = str_replace("{option4}", $option4, $param);
                                $param = str_replace("{option5}", $option5, $param);
                                $param = str_replace("{option6}", $option6, $param);
                                $param = str_replace("{option7}", $option7, $param);
                                $param = str_replace("{option8}", $option8, $param);
                                $param = str_replace("{option9}", $option9, $param);
                            }

                            //pg_api_param_way = 1 = 기본
                            if($pg_api_param_way == "1") {
                                $sendParam = $param;
                            } 
                            //pg_api_param_way = 2 = array 변환
                            else if($pg_api_param_way == "2") {
                                $sendParam = array();
                                
                                $split1 = explode( '&', $param );

                                for($ii=0; $ii<count($split1); $ii++) {
                                    $split2 = explode( '=', $split1[$ii] );
                                    $key = $split2[0];
                                    $value = $split2[1];
                                    $sendParam[$key] = $value;
                                }
                            } 
                            // pg_api_param_way = 3 = json 구조
                            else if ($pg_api_param_way == "3") {

                                $isHmac = isset($pg_api_hmac_use_yn) && $pg_api_hmac_use_yn === 'Y' && !empty($pg_api_key);

                                $jsonParam = [];
                                $split1 = explode('&', $param);

                                //기존코드
                                foreach ($split1 as $pair) {
                                    $split2 = explode('=', $pair, 2);
                                    $key = $split2[0];
                                    $value = isset($split2[1]) ? urldecode($split2[1]) : '';

                                    if (preg_match('/^(.+?)\[(.+?)\]$/', $key, $matches)) {
                                        $jsonParam[$matches[1]][$matches[2]] = $value;
                                    } else {
                                        $jsonParam[$key] = $value;
                                    }
                                }

                                // 통신직구 hmac 구조 추가
                                if ($isHmac) {
                                    $apiKeyData = json_decode($pg_api_key, true);

                                    if (isset($apiKeyData['access']) && isset($apiKeyData['secret'])) {
                                        $accessKey = $apiKeyData['access'];
                                        $secretKey = $apiKeyData['secret'];
                                        $time = round(microtime(true) * 1000);

                                        $body = json_encode([
                                            'url' => $url,
                                            'current_time' => $time,
                                            'access_key' => $accessKey,
                                        ]);

                                        $sig = base64_encode(hash_hmac('sha256', $body, $secretKey, true));

                                        if (isset($pg_api_header)) {
                                            $pg_api_header = str_replace('{timestamp}', $time, $pg_api_header);
                                            $pg_api_header = str_replace('{accessKey}', $accessKey, $pg_api_header);
                                            $pg_api_header = str_replace('{signature}', $sig, $pg_api_header);
                                        }
                                    }

                                    // HMAC일 경우엔 key=value 방식으로 보냄
                                    parse_str($param, $parsedParam);
                                    $sendParam = http_build_query($parsedParam);
                                } else {
                                    // 일반 JSON 구조로 전송
                                    $sendParam = json_encode($jsonParam, JSON_UNESCAPED_UNICODE);
                                }
                            }

                            // 1. pg_api_header 병합
                            if (isset($pg_api_header)) {
                                if (is_array($pg_api_header)) {
                                    $headers = array_merge($headers ?? [], $pg_api_header);
                                } else {
                                    $headerArray = array_map('trim', explode(',', $pg_api_header));
                                    $headers = array_merge($headers ?? [], $headerArray);
                                }
                            }

                            // 2. pg_api_contype 처리
                            if (isset($pg_api_contype) && !empty($pg_api_contype)) {
                                // iconv 변환 (단, json이 아닐 때만 권장)
                                if ($pg_api_kr_convert === 'Y') {
                                    $sendParam = iconv("UTF-8", "EUC-KR//IGNORE", $sendParam);
                                }
                            }

                            // 최종 헤더 설정
                            if (!empty($headers)) {
                                curl_setopt($oCurl, CURLOPT_HTTPHEADER, $headers);
                            }

                            // CURL 설정
                            curl_setopt($oCurl, CURLOPT_URL, $url);
                            curl_setopt($oCurl, CURLOPT_POST, true);
                            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($oCurl, CURLOPT_POSTFIELDS, $sendParam);
                            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
                            $ret = curl_exec($oCurl);
                            curl_close($oCurl);
                            
                        }

                        $logData = date("Y-m-d h:i:s").' \n | ret:'.$ret.' | url:'.$url.' | '.'var_dump:'. print_r($sendParam, true). "\n";
                        error_log($logData ,3, "./api.log");

                        $use_yn = "Y";
                        $api_send_yn = ""; 

                        //JSON RESULT
                        if($pg_api_return_way == "3") {
                            $isJson = false;
                            $jsdecode = json_decode($ret, true);

                            $ret1 = json_encode($jsdecode, JSON_UNESCAPED_UNICODE);
                            
                            if( json_last_error() == JSON_ERROR_NONE ) {
                                $isJson = true;
                            }

                            $pos = strpos($ret, $pg_api_success);
                            if($pos == true) {
                                $api_send_yn = "Y";
                                $use_yn = "Y";
                                $suc_cnt = $suc_cnt + 1;
                            }
                            $pos = strpos($ret, $pg_api_fail);
                            if($pos == true) {
                                $api_send_yn = "E";
                                $use_yn = "E";
                                $err_cnt = $err_cnt + 1;
                            }
                            $pos = strpos($ret, $pg_api_duplicate);
                            if($pos == true) {
                                $api_send_yn = "D";
                                $use_yn = "R";
                                $dup_cnt = $dup_cnt + 1;
                            }

                        } 
                        //NORMAL RESULT
                        else {
                            
                            if($ret == $pg_api_success) {
                                $api_send_yn = "Y"; //succ
                                $use_yn = "Y";
                                $suc_cnt = $suc_cnt + 1;

                            } else if($ret == $pg_api_fail) {
                                $api_send_yn = "E"; //error
                                $use_yn = "E";
                                $err_cnt = $err_cnt + 1;
                            } else if($ret == $pg_api_duplicate) {
                                $api_send_yn = "D"; //dup
                                $use_yn = "R";
                                $dup_cnt = $dup_cnt + 1;
                            } else {
                                $api_send_yn = "?"; //what
                            }
                        }
                    } else if($pg_api_kind == "google") {

                        require_once '/home/withus/withusLanding/google-api/vendor/autoload.php';
                        //require_once 'D:/Develop/workspace/php/withusLanding/google-api/vendor/autoload.php';
            
                        // Google Client 설정
                        $client = new Google_Client();
                        $client->setAuthConfig('/home/withus/withusLanding/google-api/withus.json'); // 서비스 계정 키 파일 경로
                        //$client->setAuthConfig('D:/Develop/workspace/php/withusLanding/google-api/withus.json'); // 서비스 계정 키 파일 경로
            
                        $client->addScope(Google_Service_Sheets::SPREADSHEETS);
                        $client->setAccessType('offline');
            
                        $service = new Google_Service_Sheets($client);
                        $spreadsheetId = $google_addr; // 스프레드시트 ID

                        $use_yn = "Y";
                        try {
                            $response = $service->spreadsheets_values->get($spreadsheetId, "{$google_sheet}!A1:Z");
                            $values = $response->getValues();
                            $lastRow = count($values) + 1;
                        } catch (Exception $e) {
                            $errorMessage = $e->getMessage();
                            error_log("Error fetching spreadsheet data: " . $errorMessage, 3, "./api_error.log");
                            $logData = date("Y-m-d h:i:s") . ' | land_idx:' . $land_idx . ' | result : ' . $errorMessage . "\n";
                            error_log($logData, 3, "./api.log");
                            $api_send_yn = "E";
                            $use_yn = "E";
                        }
            
                        $cell_mappings = explode('||', $google_cell);
            
                        $ordered_data = [];
                        foreach ($cell_mappings as $mapping) {
                            list($column, $variable) = explode(':', $mapping);
                            if ($variable == '$insert_date') {
                                $ordered_data[$column] = date("Y-m-d H:i:s");
                            } else {
                                $ordered_data[$column] = ${trim($variable, '$')};
                            }
            
                        }
            
                        $api_send_yn = "Y";
                        foreach ($ordered_data as $column => $value) {
                            $range = "{$google_sheet}!{$column}{$lastRow}";
            
                            $body = new Google_Service_Sheets_ValueRange([
                                'values' => [[$value]]
                            ]);
            
                            $params = [
                                'valueInputOption' => 'RAW'
                            ];
            
                            try {
                                $result = $service->spreadsheets_values->update($spreadsheetId, $range, $body, $params);
                            } catch (Exception $e) {
                                $api_send_yn = "E";
                                $use_yn = "E";
                                $errorMessage = $e->getMessage();
                                error_log("Error updating spreadsheet: " . $errorMessage, 3, "./api_error.log");
                                $logData = date("Y-m-d h:i:s") . ' | land_idx:' . $land_idx . ' | result : ' . $errorMessage . "\n";
                                error_log($logData, 3, "./api.log");
                            }
                        }

                    }

                    //api 결과 update처리
                    $sql1 = "	
                    update {$g5['crm_landing']} set 
                      api_send_yn  = '{$api_send_yn}'
                    , update_date  = now()
                    , use_yn       = '$use_yn'
                    where land_idx = {$land_idx}
                    ";
                    isSqlError(sql_query($sql1), $sql1);
                }
                //END Current


                
                //Random 
                else if($ranTimeYN == "R" || $ranTimeYN == "U") {

                    $ins_sql = "
                    insert into {$g5['crm_landing']} set
                        land_pg_idx    = {$exist['page_idx']}
                        ,land_ptn_idx   = {$exist['pg_ptn_idx']}
                        ,land_deptno    = $land_deptno
                        ,land_empno     = " . (is_null($land_empno) ? 'NULL' : $land_empno) . "
                        ,land_used_data = 'N'
                        ,name       = '{$name}'
                        ,tel          = HEX(AES_ENCRYPT('{$tel}', 'withus_secret_key'))
                        ,hp             = HEX(AES_ENCRYPT('{$hp}', 'withus_secret_key'))
                        ,tel1         = '{$phoneSplit[0]}'
                        ,tel2         = HEX(AES_ENCRYPT('{$phoneSplit[1]}', 'withus_secret_key'))
                        ,tel3         = '{$phoneSplit[2]}'
                        ,option1      = '{$option1}'
                        ,option2      = '{$option2}'
                        ,option3      = '{$option3}'
                        ,option4      = '{$option4}'
                        ,option5      = '{$option5}'
                        ,option6      = '{$option6}' 
                        ,option7      = '{$option7}' 
                        ,option8      = '{$option8}' 
                        ,option9      = '{$option9}' 
                        ,land_memo      = '{$land_memo}'
                        ,inflow_path    = '엑셀업로드'
                        ,inflow_env     = 'U'
                        ,api_send_yn    = '{$api_send_yn}'
                        ,sms_send_yn    = '{$sms_send_yn}'
                        ,use_yn         = 'Y'
                        ,insert_date    = '{$date}'
                        ,insert_date2   = '{$insert_date2}'
                        ,update_date    = '{$date}'
                        ,insert_user    = '{$member['mb_id']}'
                        ,update_user    = '{$member['mb_id']}'
                        ,client_ip      = '{$ip}'
                        ,ip      = '{$ip}'
                    ";
                    isSqlError(sql_query($ins_sql), $ins_sql);
                    $land_idx = sql_insert_id();
                    
                    $ins_sql2 = "
                    insert into {$g5['crm_api_send']} set
                         land_idx       = {$land_idx}
                        ,land_pg_idx    = {$exist['page_idx']}
                        ,name       = '{$name}'
                        ,tel          = HEX(AES_ENCRYPT('{$tel}', 'withus_secret_key'))
                        ,hp             = HEX(AES_ENCRYPT('{$hp}', 'withus_secret_key'))
                        ,tel1         = '{$phoneSplit[0]}'
                        ,tel2         = HEX(AES_ENCRYPT('{$phoneSplit[1]}', 'withus_secret_key'))
                        ,tel3         = '{$phoneSplit[2]}'
                        ,option1      = '{$option1}'
                        ,option2      = '{$option2}'
                        ,option3      = '{$option3}'
                        ,option4      = '{$option4}'
                        ,option5      = '{$option5}'
                        ,option6      = '{$option6}' 
                        ,option7      = '{$option7}' 
                        ,option8      = '{$option8}' 
                        ,option9      = '{$option9}' 
                        ,result_yn      = 'N'
                        ,api_date       = '{$date}'
                    ";
                    isSqlError(sql_query($ins_sql2), $ins_sql2);

                    if($land_idx != "") {
                        $suc_cnt = $suc_cnt + 1;
                    } else {
                        $err_cnt = $err_cnt + 1;
                    }
                }
            } else { 
                $ins_sql = "
                insert into {$g5['crm_landing']} set
                     land_pg_idx    = {$exist['page_idx']}
                    ,land_ptn_idx   = {$exist['pg_ptn_idx']}
                    ,land_deptno    = $land_deptno
                    ,land_empno     = " . (is_null($land_empno) ? 'NULL' : $land_empno) . "
                    ,land_used_data = 'N'
                    ,name       = '{$name}'
                    ,tel          = HEX(AES_ENCRYPT('{$tel}', 'withus_secret_key'))
                    ,hp             = HEX(AES_ENCRYPT('{$hp}', 'withus_secret_key'))
                    ,tel1         = '{$phoneSplit[0]}'
                    ,tel2         = HEX(AES_ENCRYPT('{$phoneSplit[1]}', 'withus_secret_key'))
                    ,tel3         = '{$phoneSplit[2]}'
                    ,option1      = '{$option1}'
                    ,option2      = '{$option2}'
                    ,option3      = '{$option3}'
                    ,option4      = '{$option4}'
                    ,option5      = '{$option5}'
                    ,option6      = '{$option6}' 
                    ,option7      = '{$option7}' 
                    ,option8      = '{$option8}' 
                    ,option9      = '{$option9}' 
                    ,land_memo      = '{$land_memo}'
                    ,inflow_path    = '엑셀업로드'
                    ,inflow_env     = 'U'
                    ,api_send_yn    = '{$api_send_yn}'
                    ,sms_send_yn    = '{$sms_send_yn}'
                    ,use_yn         = 'Y'
                    ,insert_date    = '{$date}'
                    ,insert_date2   = '{$insert_date2}'
                    ,update_date    = '{$date}'
                    ,insert_user    = '{$member['mb_id']}'
                    ,update_user    = '{$member['mb_id']}'
                    ,client_ip      = '{$ip}'
                    ,ip      = '{$ip}'
                ";

                file_put_contents('ins_sql_log.txt', $ins_sql . PHP_EOL, FILE_APPEND);

                isSqlError(sql_query($ins_sql), $ins_sql);
                $land_idx = sql_insert_id();
                
                if($land_idx != "") {
                    $suc_cnt = $suc_cnt + 1;
                } else {
                    $err_cnt = $err_cnt + 1;
                }
                
            }

            if ($pg_db_sms_yn == "Y" && ($ranTimeYN == "R" || $ranTimeYN == "U")) {
                $sms_success_cnt = 0;
                $ins_sql3 = "
                insert into {$g5['crm_landing_sms']} set
                     land_idx       = {$land_idx}
                    ,land_pg_idx    = {$exist['page_idx']}
                    ,name       = '{$name}'
                    ,tel          = HEX(AES_ENCRYPT('{$tel}', 'withus_secret_key'))
                    ,hp             = HEX(AES_ENCRYPT('{$hp}', 'withus_secret_key'))
                    ,tel1         = '{$phoneSplit[0]}'
                    ,tel2         = HEX(AES_ENCRYPT('{$phoneSplit[1]}', 'withus_secret_key'))
                    ,tel3         = '{$phoneSplit[2]}'
                    ,option1      = '{$option1}'
                    ,option2      = '{$option2}'
                    ,option3      = '{$option3}'
                    ,option4      = '{$option4}'
                    ,option5      = '{$option5}'
                    ,option6      = '{$option6}' 
                    ,option7      = '{$option7}' 
                    ,option8      = '{$option8}' 
                    ,option9      = '{$option9}' 
                    ,result_yn      = 'N'
                    ,sms_date       = '{$date}'
                ";
                isSqlError(sql_query($ins_sql3), $ins_sql3);
            }
        }

        

        $total_inserted = $suc_cnt; // 성공적으로 삽입된 수
        $return_msg = "(성공:" . $suc_cnt . ") (API실패:" . $err_cnt . ") (API중복:" . $dup_cnt . ") 업로드 되었습니다.";
        
        // 중복 메시지가 있을 경우 추가
        if (!empty($dup_messages)) {
            $return_msg .= "\n" . implode("\n", $dup_messages);
            $return_msg .= "\n총 " . $total_cnt . "건 중 " . $duplicate_rows . "건 중복데이터 " . ($total_cnt - $duplicate_rows) . "건 업로드 되었습니다.";
            $return_msg = addslashes($return_msg); // 이스케이프
            $return_msg = str_replace("\n", "\\n", $return_msg);
        }
        
        
        alert($return_msg, "./land_list?" . $qstr, false);
        
        //alert("(성공:".$suc_cnt.") (API실패:".$err_cnt.") (API중복:".$dup_cnt.") 업로드 되었습니다","./land_list?". $qstr, false);
        //goto_url('./land_list?' . $qstr);
    }
} else if ($act_button == "양식다운") {

    $file = G5_DATA_PATH.'/file/excel/excelSample.xls'; // 파일의 전체 경로
    $file_name = 'excelSample.xls'; // 저장될 파일 이름

    header('Content-type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Content-Transfer-Encoding: binary');
    header('Content-length: ' . filesize($file));
    header('Expires: 0');
    header("Pragma: public");

    $fp = fopen($file, 'rb');
    fpassthru($fp);
    fclose($fp);

    //goto_url('./land_list?' . $qstr);
}





 

?>