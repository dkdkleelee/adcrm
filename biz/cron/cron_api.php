<?php

//cron_db_sms먼저실행후 처리
include 'cron_db_sms.php';

file_put_contents('/home/withus/withusCRM/data/log/sendApi.log', "[" . date("Y-m-d h:i:s") . "]----- START CRONTAB -----" . PHP_EOL, FILE_APPEND | LOCK_EX);

$conn = new mysqli("localhost", "gonplan", "gon!@34qwer@@", "gonplan");

if ($conn->connect_error) {
    file_put_contents('/home/withus/withusCRM/data/log/sendApiError.log', " DATABASE ERROR " . PHP_EOL, FILE_APPEND | LOCK_EX);
    die("Connection failed: " . $conn->connect_error);
}
// 세션 시작
session_start();

$apiSql = "
select a.api_idx
     , a.land_idx
     , a.land_pg_idx
     , a.name
     , convert(aes_decrypt(unhex(a.tel), 'withus_secret_key') using utf8) as tel
     , convert(aes_decrypt(unhex(a.hp), 'withus_secret_key') using utf8) as hp
     , a.tel1
     , convert(aes_decrypt(unhex(a.tel2), 'withus_secret_key') using utf8) as tel2
     , a.tel3
     , a.option1
     , a.option2
     , a.option3
     , a.option4
     , a.option5
     , a.option6
     , a.option7
     , a.option8
     , a.option9
     , a.result_yn
     , a.api_date
     , a.cron_date 
     , b.pg_uri
     , b.pg_api_yn
     , b.pg_api_kind 
     , b.pg_api_url 
     , b.pg_api_header
     , b.pg_api_key
     , b.pg_api_hmac_use_yn
     , b.pg_api_add_param 
     , b.pg_api_param_way
     , b.pg_api_return_way
     , b.pg_api_success 
     , b.pg_api_fail 
     , b.pg_api_duplicate 
     , b.pg_api_kr_convert
     , b.pg_api_contype
     , b.google_addr
     , b.google_sheet
     , b.google_cell
  from {$g5['crm_api_send']} a
  left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx 
 where 1=1
 and b.pg_api_yn = 'Y'
 and result_yn = 'N' 
 and api_date <= now()
 order by a.land_idx asc
 limit 0, 50
";
$conn->set_charset("utf8mb4");
$resultList = mysqli_query($conn, $apiSql);
$cnt = mysqli_affected_rows($conn);

if (mysqli_affected_rows($conn) > 0) {
    while ($row = mysqli_fetch_assoc($resultList)) {
        $api_idx = $row['api_idx'];
        $land_idx = $row['land_idx'];
        $name = $row['name'];
        $tel = $row['tel'];
        $hp = $row['hp'];
        $pg_uri = $row['pg_uri'];

        $tel1 = $row['tel1'];
        $tel2 = $row['tel2'];
        $tel3 = $row['tel3'];

        $option1 = $row['option1'];
        $option2 = $row['option2'];
        $option3 = $row['option3'];
        $option4 = $row['option4'];
        $option5 = $row['option5'];
        $option6 = $row['option6'];

        $option7 = $row['option7'];
        $option8 = $row['option8'];
        $option9 = $row['option9'];
        $pg_api_url = $row['pg_api_url'];
        $pg_api_header = $row['pg_api_header'];

        $pg_api_key = $row['pg_api_key'];
        $pg_api_hmac_use_yn = $row['pg_api_hmac_use_yn'];

        $pg_api_add_param = $row['pg_api_add_param'];
        $pg_api_param_way = $row['pg_api_param_way'];
        $pg_api_return_way = $row['pg_api_return_way'];

        $pg_api_success = htmlspecialchars_decode($row['pg_api_success']);
        $pg_api_fail = htmlspecialchars_decode($row['pg_api_fail']);
        $pg_api_duplicate = htmlspecialchars_decode($row['pg_api_duplicate']);

        $pg_api_kr_convert = htmlspecialchars_decode($row['pg_api_kr_convert']);
        $pg_api_contype = htmlspecialchars_decode($row['pg_api_contype']);

        $pg_api_kind = $row['pg_api_kind'];
        $google_addr = $row['google_addr'];
        $google_sheet = $row['google_sheet'];
        $google_cell = $row['google_cell'];


        $url = $pg_api_url;

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
            if ($pg_api_param_way == "4") {
                $sendParam = $url;
                $sendParam = str_replace("{land_idx}", $land_idx, $sendParam);
                $sendParam = str_replace("{name}", $name, $sendParam);
                $sendParam = str_replace("{tel}", $tel, $sendParam);
                $sendParam = str_replace("{hp}", $hp, $sendParam);
                $sendParam = str_replace("{pg_uri}", $pg_uri, $sendParam);
                $sendParam = str_replace("{tel1}", $tel1, $sendParam);
                $sendParam = str_replace("{tel2}", $tel2, $sendParam);
                $sendParam = str_replace("{tel3}", $tel3, $sendParam);

                $sendParam = str_replace("{date('Y-m-d H:i:s')}", date('Y-m-d H:i:s'), $sendParam);
                $sendParam = str_replace("{date('Y-m-d H:i')}", date('Y-m-d H:i'), $sendParam);
                $sendParam = str_replace("{date('Y-m-d')}", date('Y-m-d'), $sendParam);
                $sendParam = str_replace("{client_ip}", "27.102.82.88", $sendParam);

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
                if (!empty($pg_api_add_param)) {
                    $pg_api_add_param = str_replace("{land_idx}", $land_idx, $pg_api_add_param);

                    $pg_api_add_param = str_replace("{name}", $name, $pg_api_add_param);
                    $pg_api_add_param = str_replace("{tel}", $tel, $pg_api_add_param);
                    $pg_api_add_param = str_replace("{hp}", $hp, $pg_api_add_param);
                    $pg_api_add_param = str_replace("{pg_uri}", $pg_uri, $pg_api_add_param);
                    $pg_api_add_param = str_replace("{tel1}", $tel1, $pg_api_add_param);
                    $pg_api_add_param = str_replace("{tel2}", $tel2, $pg_api_add_param);
                    $pg_api_add_param = str_replace("{tel3}", $tel3, $pg_api_add_param);

                    $pg_api_add_param = str_replace("{date('Y-m-d H:i:s')}", date('Y-m-d H:i:s'), $pg_api_add_param);
                    $pg_api_add_param = str_replace("{date('Y-m-d H:i')}", date('Y-m-d H:i'), $pg_api_add_param);
                    $pg_api_add_param = str_replace("{date('Y-m-d')}", date('Y-m-d'), $pg_api_add_param);
                    $pg_api_add_param = str_replace("{client_ip}", "27.102.82.88", $pg_api_add_param);

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

                $oCurl = curl_init();

                //pg_api_param_way = 1 = 기본
                if ($pg_api_param_way == "1") {
                    $sendParam = $pg_api_add_param;
                }
                //pg_api_param_way = 2 = array 변환
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
                //pg_api_param_way = 3 = json 구조
                else if ($pg_api_param_way == "3") {

                    $isHmac = isset($pg_api_hmac_use_yn) && $pg_api_hmac_use_yn === 'Y' && !empty($pg_api_key); //true
                
                    $param = [];
                    $split1 = explode('&', $pg_api_add_param);
                    
                    //기존코드
                    foreach ($split1 as $pair) {
                        $split2 = explode('=', $pair, 2);
                        $key = $split2[0];
                        $value = isset($split2[1]) ? urldecode($split2[1]) : '';
                
                        if (preg_match('/^(.+?)\[(.+?)\]$/', $key, $matches)) {
                            $param[$matches[1]][$matches[2]] = $value;
                        } else {
                            $param[$key] = $value;
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
                            $pg_api_header = str_replace('{timestamp}', $time, $pg_api_header);
                            $pg_api_header = str_replace('{accessKey}', $accessKey, $pg_api_header);
                            $pg_api_header = str_replace('{signature}', $sig, $pg_api_header);
                        }
                        
                        // HMAC일 경우엔 key=value 방식으로 보냄
                        parse_str($pg_api_add_param, $parsedParam);
                        $sendParam = http_build_query($parsedParam);
                        
                    } else {
                        // 일반 JSON 구조로 전송
                        $sendParam = json_encode($param, JSON_UNESCAPED_UNICODE);
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
                
                
                curl_setopt($oCurl, CURLOPT_URL, $url);
                curl_setopt($oCurl, CURLOPT_POST, true);
                curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($oCurl, CURLOPT_POSTFIELDS, $sendParam);
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
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
            update {$g5['crm_landing']} set 
            api_send_yn  = '{$api_send_yn}'
            , update_date  = now()
            , use_yn       = '$use_yn'
            where land_idx = {$land_idx}
            ";
            $result = mysqli_query($conn, $sql1);

            $sql2 = "	
            update {$g5['crm_api_send']} set 
            result_yn   = 'Y'
            , api_date    = now()
            where api_idx = {$api_idx}
            ";
            $result = mysqli_query($conn, $sql2);

        } else if($pg_api_kind == "google") {

            require_once '/home/devgon/landing/google-api/vendor/autoload.php';
            //require_once 'D:/Develop/workspace/php/landing/google-api/vendor/autoload.php';

            // Google Client 설정
            $client = new Google_Client();
            $client->setAuthConfig('/home/devgon/landing/google-api/google_gonplan.json'); // 서비스 계정 키 파일 경로
            //$client->setAuthConfig('D:/Develop/workspace/php/landing/google-api/google_gonplan.json'); // 서비스 계정 키 파일 경로

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

            $sql1 = "	
            update {$g5['crm_landing']} set 
              api_send_yn  = '{$api_send_yn}'
            , update_date  = now()
            , use_yn       = '$use_yn'
            where land_idx = {$land_idx}
            ";
            $result = mysqli_query($conn, $sql1);
    
            $sql2 = "	
            update {$g5['crm_api_send']} set 
            result_yn   = 'Y'
            , api_date    = now()
            where api_idx = {$api_idx}
            ";
            $result = mysqli_query($conn, $sql2);
            
        }
    }
}

mysqli_close($conn);
file_put_contents('/home/withus/withusCRM/data/log/sendApi.log', "[" . date("Y-m-d h:i:s") . "]----- END CRONTAB [CALL COUNT : " . $cnt . "] -----" . PHP_EOL, FILE_APPEND | LOCK_EX);
