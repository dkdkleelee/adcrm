<?php
require_once '../../common.php';

//nessesary
$ptn_nm = isset($_POST['ptn_nm']) ? strip_tags(clean_xss_attributes($_POST['ptn_nm'])) : '';
$category = isset($_POST['category']) ? clean_xss_tags($_POST['category'], 1, 1) : '';
$cate_code = isset($_POST['cate_code']) ? clean_xss_tags($_POST['cate_code'], 1, 1) : '';

$ptn_deptno = isset($_POST['ptn_deptno']) ? $_POST['ptn_deptno'] : "";

$ptn_mb_emp = $_POST['ptn_mb_emp']!="" ? trim($_POST['ptn_mb_emp']) : "NULL";

$isconn = isset($_POST['isconn']) ? strip_tags(clean_xss_attributes($_POST['isconn'])) : 0;
$ptn_phone = isset($_POST['ptn_phone']) ? strip_tags(clean_xss_attributes($_POST['ptn_phone'])) : '';

//history
$ptn_status = isset($_POST['ptn_status']) ? strip_tags(clean_xss_attributes($_POST['ptn_status'])) : null;
$ptn_status_custom = isset($_POST['ptn_status_custom']) ? strip_tags(clean_xss_attributes($_POST['ptn_status_custom'])) : null;
$ptn_ad_gubun = isset($_POST['ptn_ad_gubun']) ? strip_tags(clean_xss_attributes($_POST['ptn_ad_gubun'])) : null;


$ptn_startday = isset($_POST['ptn_startday']) ? strip_tags(clean_xss_attributes($_POST['ptn_startday'])) : NULL;
$ptn_endday = isset($_POST['ptn_endday']) && !empty($_POST['ptn_endday']) ? strip_tags(clean_xss_attributes($_POST['ptn_endday'])) : NULL;
$ptn_budget = isset($_POST['ptn_budget']) ? (int) preg_replace("/[^0-9]/", "",$_POST['ptn_budget']) : 0;

$ptn_cont_ref = isset($_POST['ptn_cont_ref']) ? strip_tags(clean_xss_attributes($_POST['ptn_cont_ref'])) : '';

$ptn_db_amount = isset($_POST['ptn_db_amount']) ? (int) preg_replace("/[^0-9]/", "",$_POST['ptn_db_amount']) : 0;
$ptn_as_amt = isset($_POST['ptn_as_amt']) ? (int) preg_replace("/[^0-9]/", "",$_POST['ptn_as_amt']) : 0;
$ptn_dposday = isset($_POST['ptn_dposday']) ? strip_tags(clean_xss_attributes($_POST['ptn_dposday'])) : null;

$ptn_memo = isset($_POST['ptn_memo']) ? strip_tags(clean_xss_attributes($_POST['ptn_memo'])) : '';

//additional
$ptn_bznm = isset($_POST['ptn_bznm']) ? strip_tags(clean_xss_attributes($_POST['ptn_bznm'])) : '';
$ptn_reprnm = isset($_POST['ptn_reprnm']) ? strip_tags(clean_xss_attributes($_POST['ptn_reprnm'])) : '';
$ptn_bznum = isset($_POST['ptn_bznum']) ? strip_tags(clean_xss_attributes($_POST['ptn_bznum'])) : '';
$ptn_addr = isset($_POST['ptn_addr']) ? strip_tags(clean_xss_attributes($_POST['ptn_addr'])) : '';
$ptn_email = isset($_POST['ptn_email']) ? strip_tags(clean_xss_attributes($_POST['ptn_email'])) : '';


$ptn_id = isset($_POST['mb_id']) ? strip_tags(clean_xss_attributes($_POST['mb_id'])) : '';
$mb_id = isset($_POST['mb_id']) ? strip_tags(clean_xss_attributes($_POST['mb_id'])) : '';

$db_is_ptn_share = $_POST['db_is_ptn_share'] == "on" ? strip_tags(clean_xss_attributes($_POST['db_is_ptn_share'])) : '';
$ptn_is_upload = $_POST['ptn_is_upload'] == "on" ? strip_tags(clean_xss_attributes($_POST['ptn_is_upload'])) : '';
$ptn_is_tooltip = $_POST['ptn_is_tooltip'] == "on" ? strip_tags(clean_xss_attributes($_POST['ptn_is_tooltip'])) : '';
if($ptn_is_upload == "on") {
  $ptn_is_upload = "Y";
}else{
  $ptn_is_upload = "N";
}

if($ptn_is_tooltip == "on") {
    $ptn_is_tooltip = "Y";
  }else{
    $ptn_is_tooltip = "N";
  }

//$ptn_ban_phone = $_POST['ptn_ban_phone'] == "on" ? strip_tags(clean_xss_attributes($_POST['ptn_is_upload'])) : '';

$ptn_ban_phone = strip_tags(clean_xss_attributes($_POST['ptn_ban_phone_array'])); // 배열로 변환


$historyCheckbox = isset($_POST['historyCheckbox']) ? strip_tags(clean_xss_attributes($_POST['historyCheckbox'])) : '';
$ptn_ntc_useyn = isset($_POST['ptn_ntc_useyn']) ? strip_tags(clean_xss_attributes($_POST['ptn_ntc_ctrst'])) : '';
$ptn_ntc_date = isset($_POST['ptn_ntc_date']) ? strip_tags(clean_xss_attributes($_POST['ptn_ntc_date'])) : '';
$ptn_ntc_pct = isset($_POST['ptn_ntc_pct']) ? strip_tags(clean_xss_attributes($_POST['ptn_ntc_pct'])) : '0';
$ptn_show_dash = isset($_POST['ptn_show_dash']) == "on" ? "Y" : "N";

if ($ptn_startday === '') {
  $ptn_startday = null;
}
if ($ptn_endday === '') {
  $ptn_endday = null;
}
if ($ptn_dposday === '') {
  $ptn_dposday = null;
}


if($ptn_ntc_useyn == "on") {
    $ptn_ntc_useyn = "Y";
  }else{
    $ptn_ntc_useyn = "N";
  }

$redirect_url = "./partner_form?w=u&amp;ptn_idx='$ptn_idx'&amp;'.$qstr";

if (!$ptn_nm) {
  alert('고객명 필수입력항목.', $redirect_url);
}
if (!$ptn_deptno) {
  alert('부서 필수입력항목.', $redirect_url);
}
// if (!$ptn_phone) {
//   alert('연락처 필수입력항목.', $redirect_url);
// }

if (!$ptn_startday) {
  alert('시작일 필수입력항목.', $redirect_url);
}
if ($ptn_ad_gubun != "CPA") {

  if($ptn_cont_ref == "") {
    alert('CPC 혹은 CPP -> 예산기간 or 광고기간 항목 필수입력항목.', $redirect_url);
  }

  if ($ptn_startday > $ptn_endday) {
    alert('시작일 종료일이 유효하지 않습니다.', $redirect_url);
  }
}
  

sql_query(" SET autocommit=0 ");


//auth admin connect
$isconn = isset($_POST['isconn']) ? strip_tags(clean_xss_attributes($_POST['isconn'])) : '0';
if($isconn == "1") {

  $w_backup = $w;
  $direct_auth = true;
  
  $_POST['mb_id']           = trim($_POST['mb_id']);
  $_POST['mb_password']     = trim($_POST['mb_password']);
  $_POST['mb_password_re']  = trim($_POST['mb_password']);
  $_POST['mb_name']         = trim($_POST['ptn_reprnm']) == "" ? $_POST['ptn_nm'] : $_POST['ptn_reprnm'];
  $_POST['mb_nick']         = trim($_POST['ptn_nm']);
  $_POST['mb_email']        = trim($_POST['ptn_email']) == "" ? $_POST['mb_id'].'@gonplan.co.kr' : trim($_POST['ptn_email']);
  $_POST['mb_level']        = 3;
  $_POST['mb_addr1']        = trim($_POST['ptn_addr']);
 
  $w = '';
  $_POST['admin_approve']   = "Y";
  $_POST['mb_gubun']        = "P";
  $_POST['is_login']        = "Y";

  include_once (G5_BBS_PATH.'/register_form_update_ptn.php');
  $w = $w_backup;

  if($toAuthMember == false) {
    sql_query("ROLLBACK");
    alert("admin 접속권한부여 실패하였습니다.");
  }
}




if ($w == '') {

    //partner 입력
    $ins_sql1 = "
    insert IGNORE into {$g5['crm_partner']} (
        ptn_nm
       ,cate_code
       ,ptn_deptno
       ,ptn_mb_emp
       ,ptn_phone
       ,ptn_tel
       ,ptn_bznm
       ,ptn_reprnm
       ,ptn_bznum
       ,ptn_addr
       ,ptn_email
       ,ptn_memo
       ,ptn_id
       ,mb_id
       ,ptn_status
       ,ptn_status_custom
       ,ptn_ad_gubun
       ,ptn_startday
       ,ptn_endday
       ,ptn_dposday
       ,ptn_budget
       ,ptn_cont_ref
       ,ptn_db_amount
       ,ptn_as_amt
       ,ptn_ntc_useyn
       ,ptn_ntc_date
       ,ptn_ntc_pct
       ,ptn_show_dash 
       ,ptn_is_upload
       ,ptn_is_tooltip
       ,ptn_ban_phone
       ,isconn
       ,use_yn
       ,insert_date
       ,update_date
       ,insert_user
       ,insert_user_name
       ,update_user
       ,update_user_name
  ) VALUES (
       '{$ptn_nm}'
      , {$cate_code}
      , {$ptn_deptno}
      , {$ptn_mb_emp}
      ,'{$ptn_phone}'
      ,'{$ptn_tel}'
      ,'{$ptn_bznm}'
      ,'{$ptn_reprnm}'
      ,'{$ptn_bznum}'
      ,'{$ptn_addr}'
      ,'{$ptn_email}'
      ,'{$ptn_memo}'
      ,'{$ptn_id}'
      ,'{$mb_id}'
      ,'{$ptn_status}'
      ,'{$ptn_status_custom}'
      ,'{$ptn_ad_gubun}'
      ,'{$ptn_startday}'
      ,'{$ptn_endday}'
      ,'{$ptn_dposday}'
      , {$ptn_budget}
      , '{$ptn_cont_ref}'
      , {$ptn_db_amount}
      , {$ptn_as_amt}
      , '{$ptn_ntc_useyn}'
      , '{$ptn_ntc_date}'
      , {$ptn_ntc_pct}
      , '{$ptn_show_dash}'
      , '{$ptn_is_upload}'
      , '{$ptn_is_tooltip}'
      , '{$ptn_ban_phone}'
      , {$isconn}
      ,'Y'
      ,now()
      ,now()
      ,'{$member['mb_id']}'
      ,'{$member['mb_name']}'
      ,'{$member['mb_id']}'
      ,'{$member['mb_name']}'
    )";

    file_put_contents(__DIR__ . '/templog.log', $ins_sql1 . PHP_EOL, FILE_APPEND);

    isSqlError(sql_query($ins_sql1), $ins_sql1);
    $ptn_idx = sql_insert_id();

} else {

    if($historyCheckbox == "on") {
        //수정 & 삭제 전 백업
        $backup_sql = "
        insert into {$g5['crm_partner_hist']}  (
          ptn_idx,
          ptn_nm,
          cate_code,
          ptn_deptno,
          ptn_deptnm,
          ptn_mb_emp,
          ptn_id,
          mb_id,
          ptn_status,
          ptn_status_custom,
          ptn_ad_gubun,
          ptn_budget,
          ptn_cont_ref,
          ptn_startday,
          ptn_endday,
          ptn_db_amount,
          ptn_as_amt,
          ptn_dposday,
          ptn_ntc_useyn,
          ptn_ntc_date,
          ptn_ntc_pct,
          ptn_show_dash,
          ptn_memo,
          ptn_phone,
          ptn_bznm,
          ptn_reprnm,
          ptn_bznum,
          ptn_addr,
          ptn_email,
          ptn_tel,
          ptn_is_upload,
          ptn_is_tooltip,
          ptn_ban_phone,
          isconn,
          use_yn,
          insert_date,
          update_user
      )
      SELECT
          a.ptn_idx,
          a.ptn_nm,
          a.cate_code,
          a.ptn_deptno,
          b.deptnm as ptn_deptnm,
          a.ptn_mb_emp,
          a.ptn_id,
          a.mb_id,
          a.ptn_status,
          a.ptn_status_custom,
          a.ptn_ad_gubun,
          a.ptn_budget,
          a.ptn_cont_ref,
          a.ptn_startday,
          a.ptn_endday,
          a.ptn_db_amount,
          a.ptn_as_amt,
          a.ptn_dposday,
          a.ptn_ntc_useyn,
          a.ptn_ntc_date,
          a.ptn_ntc_pct,
          a.ptn_show_dash,
          a.ptn_memo,
          a.ptn_phone,
          a.ptn_bznm,
          a.ptn_reprnm,
          a.ptn_bznum,
          a.ptn_addr,
          a.ptn_email,
          a.ptn_tel,
          a.ptn_is_upload,
          a.ptn_is_tooltip,
          a.ptn_ban_phone,
          a.isconn,
          a.use_yn,
          now(),
          '{$member['mb_id']}'
      from {$g5['crm_partner']} a
      left join {$g5['crm_depart']} b on a.ptn_deptno = b.deptno 
      where a.ptn_idx = {$ptn_idx};
      ";
      isSqlError(sql_query($backup_sql), $backup_sql);
    }

    //partner 수정
    $upd_sql1 = "
    update {$g5['crm_partner']} set
           ptn_nm = '{$ptn_nm}'
         , cate_code = {$cate_code}
         , ptn_deptno = {$ptn_deptno}
         , ptn_mb_emp = {$ptn_mb_emp}
         , ptn_phone = '{$ptn_phone}'
         , ptn_tel = '{$ptn_tel}'
         , ptn_bznm = '{$ptn_bznm}'
         , ptn_reprnm = '{$ptn_reprnm}'
         , ptn_bznum = '{$ptn_bznum}'
         , ptn_addr = '{$ptn_addr}'
         , ptn_email = '{$ptn_email}'
         , ptn_memo = '{$ptn_memo}'
         , ptn_id = '{$ptn_id}'
         , mb_id = '{$mb_id}'
         , ptn_status = '{$ptn_status}'
         , ptn_status_custom = '{$ptn_status_custom}'
         , ptn_startday = '{$ptn_startday}'
         , ptn_endday = '{$ptn_endday}'
         , ptn_dposday = '{$ptn_dposday}'
         , ptn_budget = {$ptn_budget}
         , ptn_ad_gubun = '{$ptn_ad_gubun}'
         , ptn_db_amount = '{$ptn_db_amount}'
         , ptn_as_amt = '{$ptn_as_amt}'
         , ptn_ntc_useyn = '{$ptn_ntc_useyn}'
         , ptn_ntc_date = '{$ptn_ntc_date}'
         , ptn_cont_ref = '{$ptn_cont_ref}'
         , ptn_ntc_pct = '{$ptn_ntc_pct}'
         , ptn_show_dash = '{$ptn_show_dash}'
         , ptn_is_upload = '{$ptn_is_upload}'
         , ptn_is_tooltip = '{$ptn_is_tooltip}'
         , ptn_ban_phone = '{$ptn_ban_phone}'
         , isconn = {$isconn}
         , update_date = now()
         , update_user = '{$member['mb_id']}'
         , update_user_name = '{$member['mb_name']}'
    where ptn_idx = {$ptn_idx}
    ";
    isSqlError(sql_query($upd_sql1), $upd_sql1);
    

}




if($ptn_ban_phone != "") {
  $getPageSql = "
  select pg_domain
       , pg_uri 
    from {$g5['crm_page']}
  where pg_ptn_idx = {$ptn_idx}
  ";
  $pageList = sql_query($getPageSql);

  for ($i = 0; $page = sql_fetch_array($pageList); $i++) {

    $pg_domain = $page['pg_domain'];
    $pg_uri = $page['pg_uri'];
    $find_dir = DOCUMENT_ROOT."withusLanding/$pg_domain/$pg_uri";
    $find_html = DOCUMENT_ROOT."withusLanding/$pg_domain/$pg_uri/index.html";


    if(file_exists($find_html)) {
          

    // 파일에서 내용을 불러옵니다.
    $html_content = file_get_contents($find_html);

    // 정규 표현식을 사용하여 특정 JavaScript 코드를 삭제합니다.
    // 삭제할 코드의 마지막에 \n를 추가하여 줄바꿈을 강제합니다.
    $pattern = '/\s*var input_share = document\.createElement\("input"\);\s*' .
                'input_share\.type = "hidden";\s*' .
                'input_share\.name = "ban_phone";\s*' .
                'input_share\.value = "[^"]+";\s*' .
                'form\[i\]\.append\(input_share\);/';

    $html_content = preg_replace($pattern, '', $html_content);

    // 중괄호와 관련된 부분이 잘못 배치되지 않도록 줄바꿈을 적절히 조정합니다.
    // 예를 들어, 필요하다면 다음 코드를 추가합니다:
    $html_content = str_replace('form[i].append(input_deptno);}', "form[i].append(input_deptno);\n}", $html_content);

    // 파일에 변경된 내용을 다시 씁니다.
    $bytesWritten = file_put_contents($find_html, $html_content);
    if ($bytesWritten === false) {
        echo "파일 쓰기 실패";
    } else {
        echo "파일 업데이트 성공";
    }
      
      $html_content = file_get_contents($find_html);

      if (preg_match('/(\s*)var input_deptno = document\.createElement\("input"\);/', $html_content, $matches)) {
        $indentation = $matches[1]; // 매칭된 공백(들여쓰기)을 가져옵니다.
      } else {
          // 들여쓰기를 찾을 수 없는 경우 기본 들여쓰기를 사용합니다.
        $indentation = "        ";
      }
      $new_input_code = "{$indentation}var input_share = document.createElement(\"input\");"
                      . "{$indentation}input_share.type = \"hidden\";"
                      . "{$indentation}input_share.name = \"ban_phone\";"
                      . "{$indentation}input_share.value = \"{$ptn_ban_phone}\";"
                      . "{$indentation}form[i].append(input_share);";
  
      $pattern = '/(form\[i\]\.append\(input_deptno\);)/';
      $replacement = "$1" . $new_input_code;
      $modified_content = preg_replace($pattern, $replacement, $html_content, 1);
      $bytesWritten = file_put_contents($find_html, $modified_content);
  
      if ($bytesWritten === false) {
          alert("파일쓰기실패");
      } 
    } 
  }
}



if($db_is_ptn_share == "on") {

  //기존에 share 이력이있으면 해당 파일 찾아서 삭제해야함
  $share_find_sql = "
  select group_concat(b.pg_domain) as pg_domain
       , group_concat(b.pg_uri) as pg_uri
       , count(*) as cnt
    from {$g5['crm_db_share']} a
    left join {$g5['crm_page']} b on a.share_child_page_idx = b.page_idx 
    where share_parent_ptn = {$ptn_idx}
    order by share_no
  ";
  $share_find = sql_fetch($share_find_sql);

  $token = bin2hex(random_bytes(16));
  $share_parent_ptn = $ptn_idx;
  $share_parent_page_idx = isset($_POST['share_parent_page_idx']) ? strip_tags(clean_xss_attributes($_POST['share_parent_page_idx'])) : '';

  for($i = 1; $i <= 5; $i++) {
      
      $share_child_ptn = isset($_POST['share_child_ptn'.$i]) ? strip_tags(clean_xss_attributes($_POST['share_child_ptn'.$i])) : '';
      $share_child_page_idx = isset($_POST['share_child_page_idx'.$i]) ? strip_tags(clean_xss_attributes($_POST['share_child_page_idx'.$i])) : '';
      $share_cnt = isset($_POST['share_cnt'.$i]) ? strip_tags(clean_xss_attributes($_POST['share_cnt'.$i])) : '';

      if(!empty($share_child_ptn) && !empty($share_child_page_idx) && !empty($share_cnt)) {

        if($i == 1){
          $getPageSql = "
          select pg_domain
               , pg_uri 
            from {$g5['crm_page']}
          where page_idx = {$share_parent_page_idx}
          ";
          $resultOne = sql_fetch($getPageSql);

          $pg_domain = $resultOne['pg_domain'];
          $pg_uri = $resultOne['pg_uri'];
          $find_dir = DOCUMENT_ROOT . "landing/$pg_domain/$pg_uri";
          $find_html = DOCUMENT_ROOT . "landing/$pg_domain/$pg_uri/index.html";

          if (file_exists($find_html)) {

              $html_content = file_get_contents($find_html);

              // 1. 기존 코드에서 들여쓰기 추출
              if (preg_match('/^(\s*)var\s+input_deptno\s*=.*$/m', $html_content, $matches)) {
                  $indentation = $matches[1];
              } else {
                  $indentation = ""; // 기본 들여쓰기를 빈 문자열로 설정
              }

              // 2. 새로운 코드 작성
              $new_input_code = "\n{$indentation}var input_share = document.createElement(\"input\");\n"
                              . "{$indentation}input_share.type = \"hidden\";\n"
                              . "{$indentation}input_share.name = \"share_token\";\n"
                              . "{$indentation}input_share.value = \"{$token}\";\n"
                              . "{$indentation}form.appendChild(input_share);";

              // 3. 기존 코드 블록을 찾아서 새로운 코드를 추가
              $search_pattern = '/(var\s+input_deptno\s*=.*?form\.appendChild\(\s*input_deptno\s*\);)/s';
              if (preg_match($search_pattern, $html_content, $match)) {
                  $replacement = $match[1] . $new_input_code;
                  $modified_content = str_replace($match[1], $replacement, $html_content);

                  // 4. 파일 저장
                  $bytesWritten = file_put_contents($find_html, $modified_content);

                  if ($bytesWritten === false) {
                      alert("파일 쓰기 실패");
                  } else {
                      // 성공적으로 파일이 수정되었습니다.
                  }
              } else {
                  alert("지정한 코드 블록을 찾을 수 없습니다.");
              }
          } else {
              alert("해당 페이지에 코드가 없습니다. 개발자에게 문의하세요.");
          }
          
        }

        $ins_sql1 = "
        insert into {$g5['crm_db_share']} (
           share_token
          ,share_no
          ,share_parent_ptn
          ,share_parent_page_idx
          ,share_child_ptn
          ,share_child_page_idx
          ,share_count
          ,insert_date
          ,update_date
          ,insert_user
          ,update_user
          ,insert_user_name
          ,update_user_name
        ) VALUES (
          '{$token}'
          , {$i}
          , {$ptn_idx}
          , {$share_parent_page_idx}
          , {$share_child_ptn}
          , {$share_child_page_idx}
          , {$share_cnt}
          , now()
          , now()
          ,'{$member['mb_id']}'
          ,'{$member['mb_id']}'
          ,'{$member['mb_name']}'
          ,'{$member['mb_name']}'
        )";
        isSqlError(sql_query($ins_sql1), $ins_sql1);
      }
  }
} else {
  //사용안함으로 파악중 주석처리
  // $isShared = isset($_POST['isShared']) ? strip_tags(clean_xss_attributes($_POST['isShared'])) : '';
  
  // if($share_flag == "0" && $share_flag != "") {

  //   $share_find_sql = "
  //   select group_concat(b.pg_domain) as pg_domain
  //        , group_concat(b.pg_uri) as pg_uri
  //        , count(*) as cnt
  //     from {$g5['crm_db_share']} a
  //     left join {$g5['crm_page']} b on a.share_child_page_idx = b.page_idx 
  //     where share_parent_ptn = {$ptn_idx}
  //     order by share_no
  //   ";
  //   $share_find = sql_fetch($share_find_sql);
  //   $cnt = $share_find['cnt'];
    

  //   if($cnt > 0) {

  //     $delSql = "
  //     delete 
  //       from {$g5['crm_db_share']}
  //     where share_parent_ptn = {$ptn_idx}
  //     ";
  //     isSqlError(sql_query($delSql), $delSql);

  //     $pg_domain = $share_find['pg_domain'];
  //     $pg_uri = $share_find['pg_uri'];

  //     $split_pg_domain = explode( ',', $pg_domain );
  //     $split_pg_uri = explode( ',', $pg_uri );

  //     for ($i = 0; $i < count($split_pg_uri); $i++) {

  //       $find_html = DOCUMENT_ROOT."withusLanding/$split_pg_domain[$i]/$split_pg_uri[$i]/index.html";
  //       $html_content = file_get_contents($find_html);
  //       // 정규 표현식으로 추가된 코드 블록을 찾습니다
  //       $pattern = "/\tvar newInput = document.createElement\\(\"input\"\\);[\r\n\s]*\tnewInput.type = \"hidden\";[\r\n\s]*\tnewInput.name = \"share_token\";[\r\n\s]*\tnewInput.value = \"[^\"]*\";[\r\n\s]*\tform\[0\]\.append\(newInput\);/";
        
  //       // 추가된 코드 블록을 빈 문자열로 대체하여 제거합니다
  //       $modified_content = preg_replace($pattern, "", $html_content);
        
  //       // 변경된 내용을 파일에 저장합니다
  //       $bytesWritten = file_put_contents($find_html, $modified_content);
  //       if ($bytesWritten === false) {
  //           echo "Failed to write to file";
  //       }
  //     }
  //   }
  // }
}

sql_query("COMMIT");

//alert('저장완료');
goto_url('partner_list?' . $qstr);










//   $ins_sql1 = "
//   insert into {$g5['crm_db_share']} (
//      share_token
//     ,share_parent_ptn
//     ,share_child_ptn
//     ,share_child_page_idx
//     ,share_count
//     ,insert_date
//     ,update_date
//     ,insert_user
//     ,update_user
//     ,insert_user_name
//     ,update_user_name
// ) VALUES (
//      '{$token}'
//     , {$ptn_idx}
//     , {$share_child_ptn}
//     , {$share_child_page_idx}
//     , {$share_cnt}
//     , now()
//     , now()
//     ,'{$member['mb_id']}'
//     ,'{$member['mb_id']}'
//     ,'{$member['mb_name']}'
//     ,'{$member['mb_name']}'
//   )";
//   isSqlError(sql_query($ins_sql1), $ins_sql1);

