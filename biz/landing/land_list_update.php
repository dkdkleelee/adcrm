<?php
require_once '../../common.php';


$post_count_chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;
$chk            = (isset($_POST['chk']) && is_array($_POST['chk'])) ? $_POST['chk'] : array();
$act_button     = isset($_POST['act_button']) ? strip_tags($_POST['act_button']) : '';
$prevPage       = $_SERVER['HTTP_REFERER'];

if ($act_button === "중복제거") {

    $sql_affected_rows = 0;
    $mb_level = $member['mb_level'];

    //영업직 레벨 직원들은 본인것만 중복제거
    $add_cond = "";
    if($mb_level <= 4) {
        $add_cond .= "and b.pg_mb_emp = {$member['mb_no']}";
    }

    $dupUpdQuery = "
    update {$g5['crm_landing']} set
           use_yn = 'E'
         , update_date = now()
         , update_user = '{$member['mb_id']}'
         , update_log = '중복제거'
    where land_idx in (
        select land_idx
        from (
            select a.land_idx
                , concat(a.land_ptn_idx, '-', convert(aes_decrypt(unhex(a.tel), 'withus_secret_key') using utf8)  ) as dupchk
                , row_number() over (partition by dupchk order by land_idx) as rownum
                , a.land_deptno 
            from {$g5['crm_landing']} a
            left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx 
            where a.land_deptno = {$member['mb_deptno']}
            $add_cond
            and a.use_yn = 'Y'
            order by land_idx desc
            ) a 
        where rownum > 1
    )
    ";
    isSqlError(sql_query($dupUpdQuery), $dupUpdQuery);
    $sql_affected_rows = sql_affected_rows();

    $hist_memo = "{$sql_affected_rows}건 중복제거";
    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'];
    $record_hist_sql = "
    insert into {$g5['record_hist']} (hist_join_gubun, hist_function, hist_mb_no, hist_mb_name, hist_detail, client_ip) values
    ('직원', 'update', '{$member['mb_no']}','{$member['mb_name']}','{$hist_memo}','{$ip}');
    ";
    isSqlError(sql_query($record_hist_sql), $record_hist_sql);
    alert($sql_affected_rows . "건 중복 DB 이동", $prevPage);
} 

if ($act_button === "연락처저장") {

    $tel = isset($_POST['tel']) ? strip_tags(clean_xss_attributes($_POST['tel'])) : '';
    $hp_arr = explode( '-', $tel );
    $tel1 = $hp_arr[0];
    $tel2 = $hp_arr[1];
    $tel3 = $hp_arr[2];
    $hp = $tel1.$tel2.$tel3;

    $tel = isset($_POST['tel']) ? strip_tags($_POST['tel']) : '';
    $pg_uri = isset($_POST['pg_uri']) ? strip_tags($_POST['pg_uri']) : '';
    $insert_date = isset($_POST['insert_date']) ? strip_tags($_POST['insert_date']) : '';
    $client_ip = isset($_POST['client_ip']) ? strip_tags($_POST['client_ip']) : '';

    $resultOneSql = "
    select *
    from {$g5['crm_page']} a
    where pg_uri = '{$pg_uri}'
    ";
    $result = sql_fetch($resultOneSql);

    $name = isset($_POST['name']) ? strip_tags(clean_xss_attributes($_POST['name'])) : '';
    $option1 = isset($_POST['option1']) ? strip_tags(clean_xss_attributes($_POST['option1'])) : '';
    $option2 = isset($_POST['option2']) ? strip_tags(clean_xss_attributes($_POST['option2'])) : '';
    $option3 = isset($_POST['option3']) ? strip_tags(clean_xss_attributes($_POST['option3'])) : '';
    $option4 = isset($_POST['option4']) ? strip_tags(clean_xss_attributes($_POST['option4'])) : '';
    $option5 = isset($_POST['option5']) ? strip_tags(clean_xss_attributes($_POST['option5'])) : '';
    $option6 = isset($_POST['option6']) ? strip_tags(clean_xss_attributes($_POST['option6'])) : '';
    $option7 = isset($_POST['option7']) ? strip_tags(clean_xss_attributes($_POST['option7'])) : '';
    $option8 = isset($_POST['option8']) ? strip_tags(clean_xss_attributes($_POST['option8'])) : '';
    $option9 = isset($_POST['option9']) ? strip_tags(clean_xss_attributes($_POST['option9'])) : '';

    $ins_sql = "
    insert into {$g5['crm_landing']} set
         land_pg_idx = {$result['page_idx']}
        ,land_ptn_idx = {$result['pg_ptn_idx']}
        ,land_deptno = {$member['mb_deptno']}
        ,land_empno = " . (is_null($result['pg_mb_emp']) ? 'NULL' : $result['pg_mb_emp']) . "
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
        ,inflow_path = '문자발송건'
        ,api_send_yn = 'N'
        ,land_used_data = 'N'
        ,insert_date = now()
        ,update_date = now()
        ,insert_user = '{$member['mb_id']}'
        ,update_user = '{$member['mb_id']}'
        ,client_ip = '{$_SERVER['REMOTE_ADDR']}'
    ";
    isSqlError(sql_query($ins_sql), $ins_sql);
    $land_idx = sql_insert_id();

    $update_sql = "
    update {$g5['crm_sms']} set
      sms_used_yn = 'Y'
    where sms_phone = CONVERT(AES_DECRYPT(UNHEX('{$tel}'), 'withus_secret_key') USING UTF8) 
    and sms_pg_no = {$result['page_idx']}
    and insert_date = '{$insert_date}'
    ";
    $updated = sql_query($update_sql);

    goto_url('land_list?' . $qstr);
}
if ($act_button === "엑셀다운") { 
    $sql = "
    select  a.*
          , b.pg_uri
          , (select count(*) from {$g5['crm_landing']} sub where sub.tel = a.sms_phone) as cnt
      from {$g5['crm_sms']} a
      left join {$g5['crm_page']} b on a.sms_pg_no = b.page_idx and b.use_yn = 'Y'
      where 1=1
      and sms_deptno = {$member['mb_deptno']}
      and a.sms_gubun = 1
      and insert_date2 >= curdate() - interval 1 week
      and sms_confirm_yn = 'N'
      and sms_used_yn = 'N'
      order by sms_idx desc;
  ";
  $result = sql_query($sql);
  $result_cnt = mysqli_num_rows($result);
  if($result_cnt == 0) {
      alert("엑셀 다운로드 데이터가 존재하지않습니다.");
  }


  $table_header = "
  <table border='1'>
  <thead>
  </thead>
  <tbody>
  ";

  $EXCEL_STR = "
  <table border='1'>
  <tr>
  <td>NO</td>
  <td>연락처</td>
  <td>항목</td>
  <td>인증값</td>
  <td>코드</td>
  <td>발송결과</td>
  <td>별송메시지</td>
  <td>인증여부</td>
  <td>재사용여부</td>
  <td>등록일시</td>
  <td>건수</td>
  </tr>";
  
  $i = 1;
  while ($res = sql_fetch_array( $result )) {
      $EXCEL_STR .= "  
      <tr>  
          <td>".$i."</td>  
          <td>".$res['sms_phone']."</td>
          <td style='white-space: nowrap;'>".$res['sms_bigo']."</td>
          <td>".$res['sms_code']."</td>  
          <td>".$res['pg_uri']."</td>  
          <td>".$res['sms_result_code']."</td>  
          <td style='white-space: nowrap;'>".$res['sms_send_msg']."</td>  
          <td>".$res['sms_confirm_yn']."</td>  
          <td>".$res['sms_used_yn']."</td>  
          <td>".$res['insert_date']."</td>  
          <td>".$res['cnt']."</td>  
      </tr>";  

      $i = $i + 1;
  }

  $EXCEL_STR .= "</table>";
  header("Content-type: application/vnd.ms-excel; charset=utf-8");
  header("Content-Disposition: attachment; filename=문자발송건_엑셀다운로드_".date("Ymd_Hms").".xls" );
  header("Content-Description: PHP4 Generated Data");
  header("Pragma: no-cache");
  header("Expires: 0");
  print("<meta http-equiv=\"Content-Type\" content=\"application/vnd.ms-excel; charset=utf-8\">");
  
  echo $EXCEL_STR;
}

else {

    if (!$post_count_chk) {
        alert($act_button . '체크 한개이상 선택해주세요.');
    }
    
    for ($i = 0; $i < $post_count_chk; $i++) {
    
        $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;
        $land_idx     = isset($_POST['land_idx'][$k]) ? strip_tags(clean_xss_attributes($_POST['land_idx'][$k])) : '';
        $ptn_idx      = isset($_POST['land_ptn_idx'][$k]) ? strip_tags(clean_xss_attributes($_POST['land_ptn_idx'][$k])) : '';
            
        if ($act_button === "선택삭제") {

            // db delete
            $del_sql = "
            update {$g5['crm_landing']} set 
                  use_yn = NULL 
                , update_date = now()
                , update_user = '{$member['mb_id']}'
                , update_log = '선택삭제'
            where land_idx = {$land_idx}
            ";
            isSqlError(sql_query($del_sql), $del_sql);

            // api 정지 로직
            $api_sql = "
            update {$g5['crm_api_send']} set 
                  result_yn = NULL
            where land_idx = {$land_idx}
            ";
            isSqlError(sql_query($api_sql), $api_sql);

            // sms 정지 로직
            $sms_sql = "
            update {$g5['crm_landing_sms']} set 
                  result_yn = NULL
            where land_idx = {$land_idx}
            ";
            isSqlError(sql_query($sms_sql), $sms_sql);

        } else if ($act_button === "선택불량") {

            // db delete
            $upd_sql = "
            update {$g5['crm_landing']} set 
                  use_yn = 'E'
                , update_date = now()
                , update_user = '{$member['mb_id']}'
                , update_log = '선택불량'
            where land_idx = {$land_idx}
            ";
            isSqlError(sql_query($upd_sql), $upd_sql);


            // api 정지 로직
            $api_sql = "
            update {$g5['crm_api_send']} set 
                  result_yn = 'E'
            where land_idx = {$land_idx}
            and result_yn = 'N'
            ";
            isSqlError(sql_query($api_sql), $api_sql);


            // sms 정지 로직
            $sms_sql = "
            update {$g5['crm_landing_sms']} set 
                  result_yn = NULL
            where land_idx = {$land_idx}
            ";
            isSqlError(sql_query($sms_sql), $sms_sql);

        } else if ($act_button === "DB사용") {
    
            //get last chcked index
            $last_idx_sql = "
            select a.land_idx
                 , min(land_idx) as min
            from {$g5['crm_landing']} a
            left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx 
            where a.land_ptn_idx = {$ptn_idx}
            and a.use_yn = 'Y'
            and a.land_used_data = 'N'
            order by a.land_idx desc
            ";
            $data = sql_fetch($last_idx_sql);
            $min = $data['min'];
    
            //update
            $upd_sql = "
            update {$g5['crm_landing']} a inner join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx set 
                  a.land_used_data = 'Y'
                , a.update_date = now()
                , a.update_user = '{$member['mb_id']}'
                , a.update_log = 'DB사용'
            where a.use_yn = 'Y'
            and a.land_used_data = 'N'
            and a.land_ptn_idx = {$ptn_idx} 
            and a.land_idx >= {$min} and a.land_idx <= {$land_idx}
            ";
    
            isSqlError(sql_query($upd_sql), $upd_sql);
            
        } else if ($act_button === "DB사용2") {
    
            $use_yn = isset($_POST['use_yn']) ? strip_tags(clean_xss_attributes($_POST['use_yn'])) : '';

            $last_idx_sql = "
            select a.land_idx
                 , min(land_idx) as min
            from {$g5['crm_landing']} a
            left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx 
            where a.land_ptn_idx = {$ptn_idx}
            and a.use_yn = '{$use_yn}'
            and a.land_used_data = 'N'
            order by a.land_idx desc
            ";
            $data = sql_fetch($last_idx_sql);
            $min = $data['min'];
    
            //update
            $upd_sql = "
            update {$g5['crm_landing']} a inner join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx set 
                  a.land_used_data = 'Y'
                , a.update_date = now()
                , a.update_user = '{$member['mb_id']}'
                , a.update_log = 'DB사용2'
            where a.use_yn = '{$use_yn}'
            and a.land_used_data = 'N'
            and a.land_ptn_idx = {$ptn_idx} 
            and a.land_idx >= {$min} and a.land_idx <= {$land_idx}
            ";
            isSqlError(sql_query($upd_sql), $upd_sql);
            
            
        } else if ($act_button === "정상처리") {
            $upd_sql = "
            update {$g5['crm_landing']} set 
                  use_yn = 'Y'
                , update_date = now()
                , update_user = '{$member['mb_id']}'
                , update_log = '정상처리'
            where land_idx = {$land_idx}
            ";
            isSqlError(sql_query($upd_sql), $upd_sql);
        } else if ($act_button === "분배이동") {

            if ($_POST['selChgPtn'] == "") {
                alert('분배할 파트너를 선택해주세요.');
                exit;
            }

            list($ptn_idx, $page_idx) = explode('||', $_POST['selChgPtn']);
            $ptn_idx = (int) $ptn_idx;
            $page_idx = (int) $page_idx;

            $upd_sql = "
            update {$g5['crm_landing']} set 
                  land_pg_idx = {$page_idx}
                , land_ptn_idx = {$ptn_idx}
                , update_date = now()
                , update_user = '{$member['mb_id']}'
                , update_log = '분배이동'
            where land_idx = {$land_idx}
            ";
            isSqlError(sql_query($upd_sql), $upd_sql);
            $sql_affected_rows = sql_affected_rows();

        }
    
    }

    header('location:'.$prevPage);  
}




// if ($act_button === "정상처리") {
//     goto_url('./land_err_list?' . $qstr);
// } else {
//     goto_url('land_list?' . $qstr);
// }

