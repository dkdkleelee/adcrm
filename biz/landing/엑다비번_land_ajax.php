<?php
require_once '../../common.php';

if ($act === "modal-excel-down1") {

  $result = array();
  $deptno = isset($_POST['deptno']) ? strip_tags($_POST['deptno']) : '';

  if($member['mb_level'] <= 6) {
    $add_cond = "and ptn_deptno = {$deptno}";

    if($member['mb_level'] == 4) {
      $add_cond .= " and ptn_mb_emp = {$member['mb_no']}";
    }

  }

  //고객사코드
  $partner_sql = "
  select *
  from {$g5['crm_partner']} 
  where use_yn = 'Y'
    and ptn_status <= 3
    $add_cond
  order by ptn_idx desc
  ";
  $partner_list = sql_query($partner_sql);

  $response .= '<option value="" readonly>미선택</option>';  
  for ($i = 0; $partner = sql_fetch_array($partner_list); $i++) {
      // if($i == 0) {
      //   $first_ptn = $partner['ptn_idx'];
      //   array_push( $result, $first_ptn );
      // }
      $response .= '<option value="'.$partner['ptn_idx'].'">'.$partner['ptn_nm'].'</option>';
  }

  array_push( $result, $response );
  $response = "";
  
  //페이지
  if($member['mb_level'] <= 6) {
    $add_cond = "and pg_deptno = {$deptno}";

    if($member['mb_level'] == 4) {
      $add_cond = " and pg_mb_emp = {$member['mb_no']}";
    }
  }
  $code_sql = "
  select page_idx 
       , pg_domain 
       , pg_uri 
       , pg_ptn_idx 
  from {$g5['crm_page']} 
  where use_yn = 'Y'
  $add_cond
  order by page_idx desc
  ";
  $code_list = sql_query($code_sql);

  $response .= '<option value="" readonly>미선택</option>';  
  for ($i = 0; $code = sql_fetch_array($code_list); $i++) {
    $response .= '<option value="'.$code['page_idx'].'">'.$code['pg_uri'].'</option>';
  }

  array_push( $result, $response );

  echo json_encode($result);


} else if ($act === "codeByData") {
  $code = isset($_POST['code']) ? strip_tags($_POST['code']) : '';
  $sql = "
  select a.page_idx 
       , a.pg_memo 
       , a.pg_ptn_idx
       , b.design_idx 
       , b.design_name 
       , ifnull(a.pg_ptn_idx, '') as pg_ptn_idx
       , ifnull(c.ptn_nm , '') as ptn_nm
       , ifnull(a.pg_mb_emp , '') as pg_mb_emp
       , ifnull(f_get_mb_name(a.pg_mb_emp), '') as mb_emp_name
       , ifnull(a.pg_mb_ptn, '') as pg_mb_ptn
       , ifnull(f_get_mb_name(a.pg_mb_ptn), '') as mb_ptn_name
       , a.pg_deptno
       , d.deptnm
       , a.pg_api_yn
  from {$g5['crm_page']}         a
  left join {$g5['crm_design']}  b on a.pg_des_idx = b.design_idx 
  left join {$g5['crm_partner']} c on a.pg_ptn_idx = c.ptn_idx 
  left join {$g5['crm_depart']}  d on a.pg_deptno  = d.deptno 
  where a.pg_uri = '{$code}'
  ";
  $result = sql_fetch($sql);
  echo json_encode($result);
} else if ($act === "modal-excel-upload") {

  $deptno = isset($_POST['deptno']) ? strip_tags($_POST['deptno']) : '';

  if($member['mb_level'] <= 6) {
    $add_cond = "and pg_deptno = {$deptno}";

    if($member['mb_level'] == 4) {
      $add_cond = " and pg_mb_emp = {$member['mb_no']}";
    }
  }

  $code_sql = "
  select page_idx 
       , pg_domain 
       , pg_uri 
       , pg_ptn_idx 
       , pg_api_yn
       , pg_sms_yn
       , pg_db_sms_yn
  from {$g5['crm_page']} 
  where use_yn = 'Y'
  $add_cond
  order by page_idx desc
  ";
  $code_list = sql_query($code_sql);

  $response .= '<option value="" readonly>미선택</option>';  
  for ($i = 0; $code = sql_fetch_array($code_list); $i++) {

    $append_str = "";
    if($code['pg_api_yn'] == "Y") {
      $append_str = " (API사용)";
    }

    if($code['pg_sms_yn'] == "Y") {
      $append_str .= " (SMS인증)";
    }

    if($code['pg_db_sms_yn'] == "Y") {
      $append_str .= " (SMS알람)";
    }

    $response .= '<option value="'.$code['pg_uri'].'">'.$code['pg_uri']. $append_str.'</option>';
  }

  echo json_encode($response);

} else if ($act == "stopToApi") {

  $land_idx = isset($_POST['land_idx']) ? strip_tags($_POST['land_idx']) : '';

  $upd_sql = "
  update {$g5['crm_landing']} set 
        use_yn = 'R'
      , update_date = now()
      , update_user = '{$member['mb_id']}'
      , update_log = 'API중단처리'
  where land_idx = {$land_idx}
  ";
  isSqlError(sql_query($upd_sql), $upd_sql);

  $upd_sql2 = "
  update {$g5['crm_api_send']} set 
        result_yn = 'E'
  where land_idx = {$land_idx}
  and result_yn = 'N'
  ";
  isSqlError(sql_query($upd_sql2), $upd_sql2);
  echo json_encode("예약된 API가 중지되었습니다.");

} else if ($act == "banIp") {

  $ip = isset($_POST['ip']) ? strip_tags($_POST['ip']) : '';
  $mb_deptno = $member['mb_deptno'];

  $ban_ip_sql = "
  insert into gnp_crm_banip (ban_ip,ban_deptno,insert_date,insert_user,insert_user_name) values
  ('{$ip}',{$mb_deptno},now(),'{$member['mb_id']}','{$member['mb_name']}');
  ";

  isSqlError(sql_query($ban_ip_sql), $ban_ip_sql);
  $ban_idx = sql_insert_id();
  echo json_encode($ban_idx);

} else if ($act == "advanced-search") {

  $advanced_ptn_idx = isset($_POST['advanced_ptn_idx']) ? strip_tags($_POST['advanced_ptn_idx']) : '';
  $advanced_pg_uri = isset($_POST['advanced_pg_uri']) ? strip_tags($_POST['advanced_pg_uri']) : '';

  //고객사코드
  if ($member['mb_level'] <= 6) {

    if ($member['mb_level'] == 4) {
        $add_cond = "and ptn_mb_emp  = {$member['mb_no']}";
    } else {
        $add_cond = "and ptn_deptno = {$member['mb_deptno']}";
    }
  } else {
    $add_cond = "";
  }


  

  $partner_sql = "
  select ptn_idx
      , ptn_nm
  from {$g5['crm_partner']} 
  where use_yn = 'Y'
  and ptn_status < 4
  $add_cond
  order by ptn_nm asc
  ";
  $partner_list = sql_query($partner_sql);


  $add_cond2 = "";
  
  //고객사코드
  if ($member['mb_level'] <= 6) {
    $add_cond2 = " and b.pg_deptno  = {$member['mb_deptno']}";
  }

  if($advanced_ptn_idx != "") {
    //파트너에 값이 있으면 해당 파트너의 코드값만 불러옴
    $add_cond2 .= " and b.pg_ptn_idx = {$advanced_ptn_idx}";
  }
  
  $code_sql = "
  select land_ptn_idx 
       , pg_uri 
       , c.ptn_nm 
    from {$g5['crm_landing']}      a
    left join {$g5['crm_page']}    b on a.land_pg_idx = b.page_idx
    left join {$g5['crm_partner']} c on b.pg_ptn_idx = c.ptn_idx
  where a.use_yn = 'Y'
  {$add_cond2}
  group by b.pg_uri
  order by c.ptn_nm asc, b.pg_uri asc
  ";
  $code_list = sql_query($code_sql);


  $result = array();

  $response .= '<option value="" readonly>미선택</option>';  
  for ($i = 0; $partner = sql_fetch_array($partner_list); $i++) {
    $response .= '<option value="'.$partner['ptn_idx'].'">'.$partner['ptn_nm'].'</option>';
  }

  array_push( $result, $response );


  $response = '<option value="" readonly>미선택</option>';  
  for ($i = 0; $code = sql_fetch_array($code_list); $i++) { 
    //$response .= '<option value="'.$code['pg_uri'].'">'.$code['pg_uri'].' ['.$code['ptn_nm'].']</option>';
    $response .= '<option value="'.$code['pg_uri'].'">('.($i+1).') '.$code['pg_uri'].' ['.$code['ptn_nm'].']</option>';
  }

  array_push( $result, $response );

  echo json_encode($result);



} else if ($act == "onchg_ptn_idx") {
  //case1 : 고객사 선택시 호출
  //-> 결과 : 고객사에 해당하는 코드만 조회
  //case2 : 코드값 선택시 호출
  //-> 결과 :
  //case3 : 둘다 선택시 
  //case4 : 미선택 선택시

  
  $ptn_idx = isset($_POST['ptn_idx']) ? strip_tags($_POST['ptn_idx']) : '';

  //코드값 전체 유지
  if($ptn_idx == "") {

    //고객사코드
    if ($member['mb_level'] <= 6) {
      $add_cond2 = "and b.pg_deptno  = {$member['mb_deptno']}";
    }

    $code_sql = "
    select land_ptn_idx 
         , pg_uri 
         , c.ptn_nm 
      from {$g5['crm_landing']}      a
      left join {$g5['crm_page']}    b on a.land_pg_idx = b.page_idx
      left join {$g5['crm_partner']} c on b.pg_ptn_idx = c.ptn_idx
    where a.use_yn = 'Y'
    {$add_cond2}
    group by b.pg_uri
    order by c.ptn_nm asc, b.pg_uri asc
    ";
    $code_list = sql_query($code_sql);
  } else {
    $code_sql = "
    select land_ptn_idx 
         , pg_uri 
         , c.ptn_nm 
      from {$g5['crm_landing']}      a
      left join {$g5['crm_page']}    b on a.land_pg_idx = b.page_idx
      left join {$g5['crm_partner']} c on b.pg_ptn_idx = c.ptn_idx
    where a.use_yn = 'Y'
    and b.pg_ptn_idx = {$ptn_idx}
    group by b.pg_uri
    order by c.ptn_nm asc, b.pg_uri asc
    ";
    $code_list = sql_query($code_sql);
  }

  
  $result = array();


  $response = '<option value="" readonly>미선택</option>';  
  for ($i = 0; $code = sql_fetch_array($code_list); $i++) { 
    $response .= '<option value="'.$code['pg_uri'].'">('.($i+1).') '.$code['pg_uri'].' ['.$code['ptn_nm'].']</option>';
  }
  array_push( $result, $response );
  echo json_encode($response);



} else if ($act == "onchg_pg_uri") {

  $pg_uri = isset($_POST['pg_uri']) ? strip_tags($_POST['pg_uri']) : '';
  $ptn_idx = isset($_POST['ptn_idx']) ? strip_tags($_POST['ptn_idx']) : '';

  
  // 콤마(,)로 구분된 문자열을 배열로 변환
  $pg_uri_array = explode(',', $pg_uri);

  // 배열의 각 요소에 작은따옴표 추가
  $pg_uri_quoted = array_map(function($item) {
      return "'".$item."'";
  }, $pg_uri_array);

  // 작은따옴표가 추가된 요소들을 쉼표로 연결
  $pg_uri_in_clause = implode(',', $pg_uri_quoted);


  $dyn_cond = "";
  if($ptn_idx != "") {
    $dyn_cond = "and pg_ptn_idx = '{$ptn_idx}'";
  }



  $sql = "
  select *
    from gnp_crm_page 
  where pg_uri IN {$pg_uri_in_clause}
  {$dyn_cond}
  ";
  $result = sql_fetch($sql);
  echo json_encode($result);
  
} else if ($act == "sms-status") {


  $sql = "
  select  a.*
        , b.pg_uri
        , coalesce(c.cnt, 0) AS cnt
    from gnp_crm_sms a
    left join gnp_crm_page b on a.sms_pg_no = b.page_idx and b.use_yn = 'Y'
    left join ( select tel, count(*) as cnt from gnp_crm_landing group by tel ) c on a.sms_phone = c.tel
    where 1=1
    and sms_deptno = {$member['mb_deptno']}
    and a.sms_gubun = 1
    and sms_confirm_yn = 'N'
    and sms_used_yn = 'N'
    and sms_result_code = 1
    and insert_date2 >= curdate() - interval 1 week
    and sms_pg_no is not null
    order by sms_idx desc;
";
$list = sql_query($sql);

$result = array();
while($row = sql_fetch_array($list)) {
    $result[] = $row;
}

header('Content-Type: application/json');
echo json_encode($result);
  
} else if ($act == "del_sms") {

  $sms_idx = isset($_POST['sms_idx']) ? strip_tags($_POST['sms_idx']) : '';

  $update_sql = "
  update gnp_crm_sms set
     sms_confirm_yn = 'D'
   , sms_used_yn = 'D'
  where sms_idx = '{$sms_idx}'
  ";
  $updated = sql_query($update_sql);

  echo json_encode('updated');
  

} else if ($act == "get_ban_ip") {

  $sql = "
  select ban_ip
       , insert_date
    from gnp_crm_banip 
    where ban_deptno = {$member['mb_deptno']}
    order by ban_idx desc
  ";
  $list = sql_query($sql);

  $result = array();
  while($row = sql_fetch_array($list)) {
      $result[] = $row;
  }

  header('Content-Type: application/json');
  echo json_encode($result);


} else if ($act == "cancle_ban_ip") {
  $ip = isset($_POST['ip']) ? strip_tags($_POST['ip']) : '';

  $cancle_sql = "
  delete from gnp_crm_banip where ban_ip = '{$ip}' and ban_deptno = {$member['mb_deptno']}
  ";
  $cancle = sql_query($cancle_sql);
  echo json_encode('cancle');
 } else if ($act == "get_userData") {
  
  $tel = isset($_POST['tel']) ? strip_tags($_POST['tel']) : '';
  $pg_uri = isset($_POST['pg_uri']) ? strip_tags($_POST['pg_uri']) : '';
  $insert_date = isset($_POST['insert_date']) ? strip_tags($_POST['insert_date']) : '';
  $client_ip = isset($_POST['client_ip']) ? strip_tags($_POST['client_ip']) : '';

  $sql = "
  select a.sms_bigo
    from gnp_crm_sms a
    left join gnp_crm_page b on a.sms_pg_no = b.page_idx and b.use_yn = 'Y'
    where 1=1
    and a.sms_deptno = {$member['mb_deptno']} 
    and a.sms_phone = '{$tel}'
    and a.insert_date = '{$insert_date}'
  ";
  $row = sql_fetch($sql);

  
  // 줄바꿈으로 문자열을 분할
  $lines = explode("\n", $row['sms_bigo']);

  // 결과를 저장할 배열
  $result = [];

  // 각 줄을 순회
  foreach ($lines as $line) {
    // 정규 표현식을 사용하여 키와 값을 추출
    if (preg_match('/\[(option[1-9]|name)\] => (.+)/', $line, $matches)) {
      $key = $matches[1];
      $value = $matches[2];
      $result[$key] = $value;
    }
  }

  echo json_encode($result);

} else if ($act == "stopToSms") {

  $land_idx = isset($_POST['land_idx']) ? strip_tags($_POST['land_idx']) : '';

  $upd_sql = "
  update {$g5['crm_landing']} set 
        sms_send_yn = NULL
      , update_date = now()
      , update_user = '{$member['mb_id']}'
      , update_log = 'SMS중단처리'
  where land_idx = {$land_idx}
  ";
  isSqlError(sql_query($upd_sql), $upd_sql);

  $upd_sql2 = "
  update gnp_crm_landing_sms set 
        result_yn = NULL
  where land_idx = {$land_idx}
  and result_yn = 'N'
  ";
  isSqlError(sql_query($upd_sql2), $upd_sql2);
  echo json_encode("예약된 SMS가 중지되었습니다.");

} else if ($act == "get_cnt_by_date") {

  $ptn_idx = isset($_POST['ptn_idx']) ? strip_tags($_POST['ptn_idx']) : '';

  // $sql = "
  // select date_format(a.date, '%m-%d') as date
  //      , coalesce(b.count, 0) as count
  // from (select curdate() - interval (a.a + (10 * b.a) + (100 * c.a)) day as date
  //     from (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as a
  //     cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as b
  //     cross join (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as c) a
  // left join (select date(insert_date) as date, count(*) as count from gnp_crm_landing where insert_date >= curdate() - interval 7 day and land_ptn_idx = {$ptn_idx} and use_yn = 'Y' group by date(insert_date)) b on a.date = b.date where a.date > curdate() - interval 7 day
  // order by a.date
  // ";

  $sql = "
  select date_format(a.date, '%m-%d') as date,
        coalesce(b.count, 0) as count
  from (
    select curdate() - interval 6 day as date
    union all
    select curdate() - interval 5 day
    union all
    select curdate() - interval 4 day
    union all
    select curdate() - interval 3 day
    union all
    select curdate() - interval 2 day
    union all
    select curdate() - interval 1 day
    union all
    select curdate() as date
  ) a
  left join (
    select date(insert_date) as date, count(*) as count
    from gnp_crm_landing
    where land_ptn_idx = {$ptn_idx}
      and insert_date >= curdate() - interval 6 day
      and use_yn = 'Y'
    group by date(insert_date)
  ) b on a.date = b.date
  order by a.date;
  ";
  $list = sql_query($sql);

  $result = array();
  while($row = sql_fetch_array($list)) {
      $result[] = $row;
  }

  header('Content-Type: application/json');
  echo json_encode($result);
} else if ($act === "aft_code_all") {

  if ($member['mb_level'] <= 6) {
    if ($member['mb_level'] == 4) {
        $add_cond = "and pg_mb_emp  = {$member['mb_no']}";
    } else {
        $add_cond = "and pg_deptno = {$member['mb_deptno']}";
    }
  }

  $code_sql = "
  select land_ptn_idx 
      , pg_uri 
    from {$g5['crm_landing']}  a
    left join {$g5['crm_page']}    b on a.land_pg_idx = b.page_idx
  where trim(a.use_yn) = 'Y'
  {$add_cond}
  group by b.pg_uri
  order by page_idx desc
  LIMIT 30, 18446744073709551615
  ";
  $code_list = sql_query($code_sql);
  $result = array();
  for ($i = 0; $code = sql_fetch_array($code_list); $i++) {
    $response .= '<option value="'.$code['pg_uri'].'">'.$code['pg_uri'].'</option>';
  }
  array_push( $result, $response );
  echo json_encode($result);
} else if ($act === "file_delete") {

  $land_idx = isset($_POST['land_idx']) ? strip_tags($_POST['land_idx']) : '';


  $sql = "
  select *
    from gnp_crm_db_file a
    where 1=1
    and db_land_idx = {$land_idx} 
  ";
  $row = sql_fetch($sql);

  $real_file_path = $row['db_file_path'];

  // 실제 파일 삭제
  if (file_exists($real_file_path)) {
      if (!unlink($real_file_path)) {
          // 파일 삭제 실패
          echo json_encode(array('success' => false, 'error' => '파일을 삭제할 수 없습니다.'));
          exit;
      }
  }

  $delete_sql = "
  delete from gnp_crm_db_file where db_land_idx = '{$land_idx}' 
  ";
  $delete = sql_query($delete_sql);

  echo json_encode("삭제완료");

} else if ($_POST['act'] == 'excel_down_check') {
    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'];
    $allowed_ips = ['127.0.0.2'];

    // 1. IP 패스
    if (in_array($ip, $allowed_ips)) {
        echo json_encode(['status' => 'pass']);
        exit;
    }

    // 2. IP 미패스 -> SMS 발송 로직
    $mb_hp = $member['mb_hp'];
    $mb_deptno = $member['mb_deptno'] ? $member['mb_deptno'] : 'NULL';
    $pattern = '/^010-\d{4}-\d{4}$/';

    if (empty($mb_hp) || !preg_match($pattern, $mb_hp)) {
        echo json_encode(['status' => 'fail', 'message' => '등록된 연락처가 휴대전화가 아니거나 없습니다.']);
        exit;
    }

    $sms_code = mt_rand(100000, 999999);
    $send_msg = "위드어스 엑셀다운로드 [".$sms_code."] 인증번호를 입력해주세요.";

    // 세션 세팅 (코드 및 시도횟수 초기화)
    $_SESSION['ss_excel_auth_code'] = $sms_code;
    $_SESSION['ss_excel_auth_fail_cnt'] = 0; 

    // --- 알리고 API ---
    $sms_url = "https://apis.aligo.in/send/"; 
    $sms['user_id'] = "withus1"; 
    $sms['key'] = "cwlrjrxhj89o9p2fdjqqvcpq4h5a61oo";
    
    $_POST['msg'] = $send_msg; 
    $_POST['receiver'] = $mb_hp; 
    $_POST['sender'] = '010-8168-8151'; 
    $_POST['testmode_yn'] = ''; 
    $_POST['subject'] = 'Y'; 
    $_POST['msg_type'] = ''; 

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

    $result = json_decode($ret, true);
    $result_code = "";
    $sms_msg_id = "";
    if ($result !== null) {
        $result_code = $result['result_code'];
        $sms_msg_id = $result['msg_id'];
    }

    $sms_gubun = "1";
    $result_code_value = ($result_code !== "") ? $result_code : 'NULL';
    $sms_msg_id_value = ($sms_msg_id !== "") ? $sms_msg_id : 'NULL';

    // DB SMS 로그 기록
    $sql = "
    insert into gnp_crm_sms (
          sms_gubun, sms_phone, sms_code, sms_deptno, sms_pg_no
        , sms_result_code, sms_msg_id, sms_send_msg, sms_send_log
        , insert_date, insert_date2, client_ip
    ) values (
          $sms_gubun, '$mb_hp', '$sms_code', $mb_deptno, NULL
        , '$result_code_value', '$sms_msg_id_value', '$send_msg', ''
        , now(), curdate(), NULL
    )";
    isSqlError(sql_query($sql), $sql);

    // 액션 히스토리
    $record_hist_sql = "
    insert into gnp_record_hist (hist_join_gubun, hist_function, hist_mb_no, hist_mb_name, hist_detail, client_ip) values
    ('직원', 'excel_auth_sms', '{$member['mb_no']}','{$member['mb_name']}','엑셀 문자인증 발송','{$ip}');
    ";
    isSqlError(sql_query($record_hist_sql), $record_hist_sql);

    echo json_encode(['status' => 'auth_required', 'phone' => $mb_hp, 'insert_date' => date('Y-m-d H:i:s')]);
    exit;
} else if ($_POST['act'] == 'excel_auth_verify') {
    $user_code = isset($_POST['auth_code']) ? trim($_POST['auth_code']) : '';
    
    if (!isset($_SESSION['ss_excel_auth_fail_cnt'])) {
        $_SESSION['ss_excel_auth_fail_cnt'] = 0;
    }

    // 3회 이상 틀리면 세션 만료시키고 블락
    if ($_SESSION['ss_excel_auth_fail_cnt'] >= 3) {
        unset($_SESSION['ss_excel_auth_code']);
        echo json_encode(['status' => 'blocked', 'message' => '인증번호를 3회 이상 틀렸습니다. 다시 발송해주세요.']);
        exit;
    }

    if (!empty($_SESSION['ss_excel_auth_code']) && $_SESSION['ss_excel_auth_code'] == $user_code) {
        // 성공 시 다운로드 허가 세션 생성
        unset($_SESSION['ss_excel_auth_code']);
        $_SESSION['ss_excel_download_passed'] = true; 
        
        echo json_encode(['status' => 'success']);
    } else {
        $_SESSION['ss_excel_auth_fail_cnt']++;
        $remain = 3 - $_SESSION['ss_excel_auth_fail_cnt'];
        echo json_encode(['status' => 'fail', 'message' => "인증번호가 일치하지 않습니다. (남은기회: {$remain}회)"]);
    }
    exit;
} else if ($_POST['act'] == 'excel_down_check_pwd') {
    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'];

    // 실제 화이트IP로 교체
    $allowed_ips = ['127.0.0.2'];

    // 1. IP 패스
    if (in_array($ip, $allowed_ips)) {
        echo json_encode(['status' => 'pass']);
        exit;
    }

    // ========================================================
    // [기존 SMS 발송 방식 제거]
    // IP 미허용이면 비밀번호 재확인 모달을 띄우도록 응답만 보냄
    // ========================================================
    echo json_encode([
        'status' => 'password_required',
        'message' => '비밀번호 재확인이 필요합니다.'
    ]);
    exit;
} else if ($_POST['act'] == 'excel_password_verify') {
    $excel_password = isset($_POST['excel_password']) ? trim($_POST['excel_password']) : '';

    if ($excel_password == '') {
        echo json_encode([
            'status' => 'fail',
            'message' => '비밀번호를 입력해주세요.'
        ]);
        exit;
    }

    // ========================================================
    // 현재 로그인 사용자 기준으로 비밀번호 재검증
    // $member 는 기존 페이지 흐름에서 이미 로그인 회원 정보라고 가정
    // ========================================================
    if (empty($member['mb_id']) || empty($member['mb_password'])) {
        echo json_encode([
            'status' => 'fail',
            'message' => '로그인 정보가 올바르지 않습니다. 다시 로그인해주세요.'
        ]);
        exit;
    }

    if (!login_password_check($member, $excel_password, $member['mb_password'])) {
        $ip = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'];

        $record_hist_sql = "
        insert into gnp_record_hist (
            hist_join_gubun, hist_function, hist_mb_no, hist_mb_name, hist_detail, client_ip
        ) values (
            '직원', 'excel_password_verify', '{$member['mb_no']}', '{$member['mb_name']}',
            '엑셀 다운로드 비밀번호 재확인 실패', '{$ip}'
        )";
        isSqlError(sql_query($record_hist_sql), $record_hist_sql);

        echo json_encode([
            'status' => 'fail',
            'message' => '비밀번호가 일치하지 않습니다.'
        ]);
        exit;
    }

    // ========================================================
    // 성공 시 다운로드 허용 세션 부여
    // 1회성 + 3분 만료
    // ========================================================
    $_SESSION['ss_excel_download_passed'] = true;
    $_SESSION['ss_excel_download_passed_until'] = time() + 180;

    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'];
    $record_hist_sql = "
    insert into gnp_record_hist (
        hist_join_gubun, hist_function, hist_mb_no, hist_mb_name, hist_detail, client_ip
    ) values (
        '직원', 'excel_password_verify', '{$member['mb_no']}', '{$member['mb_name']}',
        '엑셀 다운로드 비밀번호 재확인 성공', '{$ip}'
    )";
    isSqlError(sql_query($record_hist_sql), $record_hist_sql);

    echo json_encode([
        'status' => 'success'
    ]);
    exit;
}


?>
