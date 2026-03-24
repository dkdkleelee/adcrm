<?php
require_once '../../common.php';




$name = isset($_POST['name']) ? strip_tags(clean_xss_attributes($_POST['name'])) : '';
$tel = isset($_POST['tel']) ? strip_tags(clean_xss_attributes($_POST['tel'])) : '';

$hp_arr = explode( '-', $tel );
$tel1 = $hp_arr[0];
$tel2 = $hp_arr[1];
$tel3 = $hp_arr[2];
$hp = $tel1.$tel2.$tel3;

$option1 = isset($_POST['option1']) ? strip_tags(clean_xss_attributes($_POST['option1'])) : '';
$option2 = isset($_POST['option2']) ? strip_tags(clean_xss_attributes($_POST['option2'])) : '';
$option3 = isset($_POST['option3']) ? strip_tags(clean_xss_attributes($_POST['option3'])) : '';
$option4 = isset($_POST['option4']) ? strip_tags(clean_xss_attributes($_POST['option4'])) : '';
$option5 = isset($_POST['option5']) ? strip_tags(clean_xss_attributes($_POST['option5'])) : '';
$option6 = isset($_POST['option6']) ? strip_tags(clean_xss_attributes($_POST['option6'])) : '';
$option7 = isset($_POST['option7']) ? strip_tags(clean_xss_attributes($_POST['option7'])) : '';
$option8 = isset($_POST['option8']) ? strip_tags(clean_xss_attributes($_POST['option8'])) : '';
$option9 = isset($_POST['option9']) ? strip_tags(clean_xss_attributes($_POST['option9'])) : '';
$client_ip = isset($_POST['client_ip']) ? strip_tags(clean_xss_attributes($_POST['client_ip'])) : '27.102.82.88';

// IP 형식 체크 (x.x.x.x 형식인지 확인)
if (!filter_var($client_ip, FILTER_VALIDATE_IP)) {
    // 유효하지 않은 경우 기본값으로 설정
    $client_ip = '27.102.82.88';
}

$land_memo = isset($_POST['land_memo']) ? strip_tags(clean_xss_attributes($_POST['land_memo'])) : '';

$pg_domain = isset($_POST['pg_domain']) ? strip_tags(clean_xss_attributes($_POST['pg_domain'])) : '';
$pg_uri = isset($_POST['pg_uri']) ? strip_tags(clean_xss_attributes($_POST['pg_uri'])) : '';

//$pg_api_yn = isset($_POST['pg_api_yn']) ? strip_tags(clean_xss_attributes($_POST['pg_api_yn'])) : '';

if ($w == '') {

    $sel_sql = "
    select *
      from {$g5['crm_page']}
     where pg_uri = '{$pg_uri}'
    ";
    $page_info = sql_fetch($sel_sql);
    $pg_api_yn = $page_info['pg_api_yn'];
    $page_idx = $page_info['page_idx'];

    $ins_sql = "
    insert into {$g5['crm_landing']} set
         land_pg_idx = {$page_info['page_idx']}
        ,land_ptn_idx = {$page_info['pg_ptn_idx']}
        ,land_deptno = {$page_info['pg_deptno']}
        ,land_empno = " . (is_null($page_info['pg_mb_emp']) ? 'NULL' : $page_info['pg_mb_emp']) . "
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
        ,land_memo = '{$land_memo}'
        ,inflow_path = '직접입력'
        ,api_send_yn = 'N'
        ,land_used_data = 'N'
        ,insert_date = now()
        ,insert_date2 = curdate()
        ,update_date = now()
        ,insert_user = '{$member['mb_id']}'
        ,update_user = '{$member['mb_id']}'
        ,client_ip = '{$client_ip}'
    ";
    isSqlError(sql_query($ins_sql), $ins_sql);
    $land_idx = sql_insert_id();

    //API Y일시 처리
    if($pg_api_yn == "Y") {

      $getApiInfoSql = "
      select a.pg_api_yn
           , a.pg_api_kind 
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

      //$page_idx = $data['page_idx'];
      $pg_api_yn = $data['pg_api_yn'];
      $pg_api_kind = $data['pg_api_kind'];
      $pg_api_url = $data['pg_api_url'];
      
      $pg_api_add_param = $data['pg_api_add_param'];
      $pg_api_param_way = $data['pg_api_param_way'];
      $pg_api_return_way = $data['pg_api_return_way'];

      $pg_api_success = htmlspecialchars_decode($data['pg_api_success']);
      $pg_api_fail = htmlspecialchars_decode($data['pg_api_fail']);
      $pg_api_duplicate = htmlspecialchars_decode($data['pg_api_duplicate']);

      $url =  $pg_api_url;

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

      } //POST
      else {
        if(!empty($pg_api_add_param)) {
          $pg_api_add_param = str_replace("{name}", $name , $pg_api_add_param);
          $pg_api_add_param = str_replace("{tel}", $tel, $pg_api_add_param);
          $pg_api_add_param = str_replace("{hp}", $hp, $pg_api_add_param);
  
          $pg_api_add_param = str_replace("{tel1}", $tel1, $pg_api_add_param);
          $pg_api_add_param = str_replace("{tel2}", $tel2, $pg_api_add_param);
          $pg_api_add_param = str_replace("{tel3}", $tel3, $pg_api_add_param);

          $pg_api_add_param = str_replace("{date('Y-m-d h:i:s')}", date('Y-m-d h:i:s'), $pg_api_add_param);
          $pg_api_add_param = str_replace("{date('Y-m-d h:i')}", date('Y-m-d h:i'), $pg_api_add_param);
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
  
  
          //그대로
          if($pg_api_param_way == "1") {
              $sendParam = $pg_api_add_param;
          } 
          //array 변환
          else if($pg_api_param_way == "2") {
              $sendParam = array();
              
              $split1 = explode( '&', $pg_api_add_param );
  
              for($j=0; $j<count($split1); $j++) {
                  $split2 = explode( '=', $split1[$j] );
                  $key = $split2[0];
                  $value = $split2[1];
                  $sendParam[$key] = $value;
              }
          } 
          //json 변환
          else if($pg_api_param_way == "3") {
  
              $sendParam = array();
              $param = array();
              $split1 = explode( '&', $pg_api_add_param );
  
              for($k=0; $k<count($split1); $k++) {
                  $split2 = explode( '=', $split1[$k] );
                  $key = $split2[0];
                  $value = $split2[1];
                  $param[$key] = $value;
              }
              $sendParam = json_encode($param);
          }
        } else {
          $sendParam = str_replace("{name}" , $name, $url);
          $sendParam = str_replace("{tel}"    , $tel, $url);
          $sendParam = str_replace("{hp}"       , $hp, $url);
          $sendParam = str_replace("{option1}", $option1, $url);
          $sendParam = str_replace("{option2}", $option2, $url);
          $sendParam = str_replace("{option3}", $option3, $url);
          $sendParam = str_replace("{option4}", $option4, $url);
          $sendParam = str_replace("{option5}", $option5, $url);
          $sendParam = str_replace("{option6}", $option6, $url);
          $sendParam = str_replace("{option7}", $option7, $url);
          $sendParam = str_replace("{option8}", $option8, $url);
          $sendParam = str_replace("{option9}", $option9, $url);
        }
  
        $oCurl = curl_init();
        
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_POST, 1);
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
      
      
      $logData = date("Y-m-d h:i:s").' \n | ret:'.$ret.' | url:'.$url.' | '.'var_dump:'. print_r($send, true);
      error_log($logData ,3, G5_DATA_PATH."/log/sendApi.log");

      $sql ="	
      update {$g5['crm_landing']} set 
        api_send_yn = '{$api_send_yn}'
      , use_yn = '{$use_yn}'
      where land_idx = {$land_idx}
      ";

      isSqlError(sql_query($sql), $sql);
    }


} else {

    $exist_sql = "
    select a.land_idx
         , b.page_idx 
         , b.pg_domain 
         , b.pg_uri
      from {$g5['crm_landing']} a
      left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx
     where land_idx = '{$land_idx}'
    ";
    $exist = sql_fetch($exist_sql);


    if($exist['pg_uri'] != $pg_uri) {
        //코드이관으로 이전데이터 백업필요

        //backup sql 작성
        $bak_sql = "
        insert into {$g5['crm_landing_bk']} 
        select null as land_bk_idx
        , land_idx
        , land_pg_idx
        , land_ptn_idx
        , land_used_data
        , name
        , tel
        , hp
        , tel1
        , tel2
        , tel3
        , option1
        , option2
        , option3
        , option4
        , option5
        , option6
        , option7
        , option8
        , option9
        , db_status
        , land_memo
        , submit_pos
        , inflow_path
        , inflow_env
        , utm_source
        , utm_medium
        , user_agent
        , user_agent2
        , api_send_yn
        , use_yn
        , insert_date
        , insert_date2
        , update_date
        , insert_user
        , update_user
        , update_log
        , client_ip
        from {$g5['crm_landing']}  
        where land_idx = {$land_idx}
        ";
        //isSqlError(sql_query($bak_sql), $bak_sql);


        $exist_sql = "
        select page_idx
             , pg_uri
             , pg_ptn_idx
        from {$g5['crm_page']} a
        where pg_uri = '{$pg_uri}'
        ";
        $exist = sql_fetch($exist_sql);
        
        // $code_upd = "
        //     , land_pg_idx = " . (!empty($exist['page_idx']) ? $exist['page_idx'] : "NULL") . "
        //     , land_ptn_idx = " . (!empty($exist['pg_ptn_idx']) ? $exist['pg_ptn_idx'] : "NULL") . "
        // ";

        if ($pg_uri === '') {
            $code_upd = "
                , land_ptn_idx = " . (!empty($exist['pg_ptn_idx']) ? $exist['pg_ptn_idx'] : "NULL") . "
            ";
        } else {
            $code_upd = "
                , land_pg_idx = " . (!empty($exist['page_idx']) ? $exist['page_idx'] : "NULL") . "
                , land_ptn_idx = " . (!empty($exist['pg_ptn_idx']) ? $exist['pg_ptn_idx'] : "NULL") . "
            ";
        }

    }

    $upd_sql = "
    update {$g5['crm_landing']} set
      name  = '{$name}'
    , tel     = HEX(AES_ENCRYPT('{$tel}', 'withus_secret_key'))
    , hp        = HEX(AES_ENCRYPT('{$hp}', 'withus_secret_key')) 
    , tel1    = '{$tel1}'
    , tel2    = HEX(AES_ENCRYPT('{$tel2}', 'withus_secret_key'))
    , tel3    = '{$tel3}'
    , option1 = '{$option1}'
    , option2 = '{$option2}'
    , option3 = '{$option3}'
    , option4 = '{$option4}'
    , option5 = '{$option5}'
    , option6 = '{$option6}'
    , option7 = '{$option7}'
    , option8 = '{$option8}'
    , option9 = '{$option9}'
    , client_ip = '{$client_ip}'
    , land_memo = '{$land_memo}'
    , update_date = now()
    , update_user = '{$member['mb_id']}'
      $code_upd
    where land_idx  = {$land_idx}
    ";
    isSqlError(sql_query($upd_sql), $upd_sql);

}



// 업로드 디렉토리 설정
$uploadDir = G5_DATA_PATH . '/file/land_file/';

// 디렉토리가 존재하지 않으면 생성
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 파일이 업로드되었는지 확인
if (isset($_FILES['fileInput']) && $_FILES['fileInput']['error'] === UPLOAD_ERR_OK) {
    // 파일 변수 정의
    $fileTmpPath = $_FILES['fileInput']['tmp_name'];
    $fileName = $_FILES['fileInput']['name'];
    $fileSize = $_FILES['fileInput']['size'];
    $fileType = $_FILES['fileInput']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    $maxFileSize = 10 * 1024 * 1024; // 10MB
    if ($fileSize > $maxFileSize) {
        echo "파일 크기가 너무 큽니다.";
        exit;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fileTmpPath);
    finfo_close($finfo);

    $extensionMimeTypeMap = array(
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'wav'  => 'audio/wav',
        'mp3'  => 'audio/mpeg',
        'ogg'  => 'audio/ogg',
        'm4a'  => array('audio/mp4', 'audio/m4a', 'audio/x-m4a', 'video/3gpp', 'audio/3gpp'),
        '3gp'  => array('video/3gpp', 'audio/3gpp'),
        '3gpp' => array('video/3gpp', 'audio/3gpp')
    );

    if (isset($extensionMimeTypeMap[$fileExtension])) {
        $expectedMimeTypes = (array)$extensionMimeTypeMap[$fileExtension];

        if (!in_array($mimeType, $expectedMimeTypes)) {
            echo "파일의 확장자와 MIME 타입이 일치하지 않습니다.";
            exit;
        }
    } else {
        echo "허용되지 않는 파일 형식입니다.";
        exit;
    }

    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

    // 파일 이동
    $dest_path = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmpPath, $dest_path)) {
        // 파일 업로드 성공
        // 데이터베이스에 파일 경로 저장
        $filePathForDB = $dest_path;
        $originalFileName = basename($fileName);
        $originalFileName = preg_replace('/[^A-Za-z0-9.\-_가-힣]/u', '_', $originalFileName); // 한글 허용
        $originalFileName = sql_real_escape_string($originalFileName);

        // land_idx 변수 정의 (안전하게 형변환)
        $land_idx = isset($_POST['land_idx']) ? (int)$_POST['land_idx'] : 0;

        $ins_sql = "
        INSERT INTO gnp_crm_db_file SET
            db_land_idx = {$land_idx},
            db_file_org_name = '{$originalFileName}',
            db_file_name = '{$newFileName}',
            db_file_path = '{$filePathForDB}'
        ";

        isSqlError(sql_query($ins_sql), $ins_sql);

    } else {
        // 파일 이동 실패
        echo "파일 업로드 중 오류가 발생하였습니다.";
    }
} else {
    // 파일이 업로드되지 않음 또는 오류 발생
    echo "파일 업로드 중 오류가 발생하였습니다.";
}


//alert('저장완료');
goto_url('land_list?' . $qstr);