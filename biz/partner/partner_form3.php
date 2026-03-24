<?php

header("Cache-Control:no-cache");
header("Pragma:no-cache");

require_once '../../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');

$hist_row = array();
$visible1 = false;
$visible2 = false;

if ($w == '') {
  $title = "고객사등록";

  //초기 부서설정
  $sel_code = "1";
  $sel_dept = $member['mb_deptno'];
  $sel_emp = $member['mb_no'];
  
  
  

  //$visible1 = true;
  $visible1 = false;

  $time = time();
  $ptn_startday = date("Y-m-d",strtotime("now", $time));
  $ptn_endday = date("Y-m-d",strtotime("+1 month", $time));

  //입력시 쉐어기능 안보이게
  $share_flag = "1";
  
} elseif ($w == 'u') {
  
  $title = "고객사수정";
  $resultOneSql = "
  select a.ptn_idx 
       , a.ptn_nm 
       , d.comm_idx
       , d.comm_pcd
       , a.ptn_deptno
       , a.ptn_mb_emp
       , b.deptnm
       , a.ptn_phone 
       , a.ptn_status
       , a.ptn_startday 
       , a.ptn_endday 
       , a.ptn_dposday 
       , a.ptn_budget
       , a.ptn_memo
       , a.ptn_bznm
       , a.ptn_reprnm
       , a.ptn_bznum
       , a.ptn_addr
       , a.ptn_email
       , a.ptn_tel
       , a.mb_id
       , a.isconn
       , a.insert_date
       , a.update_date
       , a.insert_user
       , a.insert_user_name
       , a.update_user
       , a.update_user_name
    from {$g5['crm_partner']} a
    left join {$g5['crm_depart']}    b on a.ptn_deptno = b.deptno
    left join {$g5['crm_common']}    d on a.cate_code = d.comm_idx
  where 1=1
  and a.ptn_idx = {$ptn_idx}
  ";
  $resultOne = sql_fetch($resultOneSql);

  $visible1 = $resultOne['isconn'] == "0" ? false : true;
  
  $sel_code = $resultOne['comm_pcd'];
  $sel_dept = $resultOne['ptn_deptno'];
  $sel_emp = $resultOne['ptn_mb_emp'];
  $visible2 = $resultOne['isconn'] == "1" ? true : false;

  $ptn_startday = $resultOne['ptn_startday'];
  $ptn_endday = $resultOne['ptn_endday'];

  $ptn_phone = $resultOne['ptn_phone'];

  if($member['mb_deptno'] != $sel_dept) {
    $pattern = '/([0-9]+)-([0-9]+)-([0-9]{4})/';
    $replacement = '${1}-****-${3}';
    $ptn_phone = preg_replace($pattern, $replacement, $ptn_phone);
  }
  
  //고객사 > 직원리스트 ID 출력 추후에 배지 클릭스 비밀번호 아이디와 동일하게 변경
  $badge = "";
  $ptn_id_list = "
  select mb_no
        ,mb_id
        ,mb_gubun
    from {$g5['member_table']} 
  where mb_ptnidx = {$resultOne['ptn_idx']}
  ";
  $mbm_list = sql_query($ptn_id_list);
  $user_cnt = mysqli_num_rows($mbm_list);

  for ($i = 0; $user = sql_fetch_array($mbm_list); $i++) {
    if($user['mb_gubun'] == "P") {
      $badge .= '<a href="javascript:initPW('.$user['mb_no'].');" class="badge badge-danger">'.$user['mb_id'].' </a>';
    } else {
      $badge .= '<a href="javascript:initPW('.$user['mb_no'].');" class="badge badge-primary">'.$user['mb_id'].' </a>';
    }
  }

  $hist_sql = "
  select insert_date
        , ptn_status
        , ptn_startday
        , ptn_endday
        , ptn_dposday
        , ptn_budget
    from {$g5['crm_partner_hist']}
    where ptn_idx = {$ptn_idx}
    order by insert_date desc
    limit 0, 5
    ";
  $hist_list = sql_query($hist_sql);  
}

$g5['title'] = $title;
include_once(G5_PATH . '/head.php');

//공통코드리스트
$code_sql = "
select comm_idx
     , comm_pcd 
     , comm_pnm 
     , comm_cd 
     , comm_nm 
     , comm_bigo
 from {$g5['crm_common']}
 where 1=1 
 and use_yn = 'Y' 
 and comm_pcd = {$sel_code}
";
$code_list = sql_query($code_sql);


//부서리스트
$dept_sql = "
select deptno
     , deptnm
     , parent_deptno
  from {$g5['crm_depart']} 
 where use_yn = 'Y'
   and parent_deptno != 1
 order by coalesce(parent_deptno, deptno), parent_deptno is not null, deptno
";
$dept_list = sql_query($dept_sql);


//부서별직원코드
$member_sql = "
select mb_no 
    , mb_id 
    , mb_name
    , mb_deptno 
  from {$g5['member_table']}
where mb_gubun = 'E'
  and is_login = 'Y'
  and mb_deptno = {$sel_dept}
";
$member_list = sql_query($member_sql);


//부서별직원코드
$member_sql = "
select mb_no 
    , mb_id 
    , mb_name
    , mb_deptno 
  from {$g5['member_table']}
where mb_gubun = 'E'
  and is_login = 'Y'
  and mb_deptno = {$sel_dept}
";
$member_list = sql_query($member_sql);




$shared_flag = "";
if($w == "u") {
  $shared_sql = "
  select b.pg_domain 
       , b.pg_uri 
       , c.ptn_nm 
       , a.*
       , (select pg_uri from gnp_crm_page sub where page_idx = share_parent_page_idx) as parent_pg_uri
  from gnp_crm_db_share a
  left join gnp_crm_page b on a.share_child_page_idx = b.page_idx 
  left join gnp_crm_partner c on a.share_child_ptn = c.ptn_idx 
  where share_parent_ptn = {$ptn_idx}
  order by share_no asc
  ";
  $shared_list = sql_query($shared_sql);
  $row_count = sql_num_rows($shared_list);

  //쉐어기능 있음
  if ($row_count > 0) {
    $share_flag = "2";
    

    $shared_show_data = "";
    $shared_code_label = "";

    $shared_show_data .= "
    <div id='divApiSet' class='card-body '>
      <div class='row'>
    ";

    for ($i = 0; $shared = sql_fetch_array($shared_list); $i++) {

      if($i == 0) {
        $hidden_page_idx = $shared['share_parent_page_idx'];
        $shared_code_label = $shared['parent_pg_uri'];
      }
      $shared_show_data .= "
      <div class='col-md-5'>
        <label>고객사</label>
        <input text class='form-control' value='{$shared['ptn_nm']}' disabled>
      </div>
      <div class='col-md-5'>
        <label>코드</label>
        <input text class='form-control' value='{$shared['pg_uri']}' disabled>
      </div>
      <div class='col-md-2'>
        <label>건수</label>
        <input text class='form-control' value='{$shared['share_count']}' disabled>
      </div>
      ";
      
    }

    $shared_show_data .= "
      </div>
    </div>
    ";

  } 
  //쉐어기능 없음
  else {
    $share_flag = "3";
    
    $pg_exist_sql = "
     select count(*) as cnt 
         , group_concat(page_idx) as page_idx
         , group_concat(pg_domain) as pg_domain
         , group_concat(pg_uri) as pg_uri
      from {$g5['crm_page']} 
     where pg_ptn_idx = {$ptn_idx}
    ";
    $pg_exist_res = sql_fetch($pg_exist_sql);
    $pg_cnt = $pg_exist_res['cnt'];
  
    //page가 만들어진 고객사만 페이지 분배 선택가능 
    if($pg_cnt > 0) {
      
      //분배할 고객사 조회 -> 페이지 있는 고객사만 조회
      $partner_sql = "
      select distinct ptn_idx
           , a.ptn_nm   
      from gnp_crm_partner a
      left join gnp_crm_page b on a.ptn_idx = b.pg_ptn_idx 
      where b.pg_deptno = {$member['mb_deptno']}
      and a.use_yn = 'Y'
      and b.use_yn = 'Y'
      and a.ptn_idx != {$ptn_idx}
      and not exists (
          select 1
          from gnp_crm_db_share c
          where c.share_parent_ptn = a.ptn_idx
      )
      order by a.ptn_nm
      ";

      //분배할 고객사들 option값 불러옴
      $partner_share = sql_query($partner_sql);
      $share_str = "<option value=''>미지정</option>";
      for ($i = 0; $share = sql_fetch_array($partner_share); $i++) {
        $share_str .= "<option value='{$share['ptn_idx']}'>{$share['ptn_nm']}</option>";
      }


      $split_page_idx = explode( ',', $pg_exist_res['page_idx'] );
      $split_pg_domain = explode( ',', $pg_exist_res['pg_domain'] );
      $split_pg_uri = explode( ',', $pg_exist_res['pg_uri'] );

      $master_uri_str = "";
      for ($i = 0; $i < count($split_pg_uri); $i++) {
        $master_uri_str .= "<option value='{$split_page_idx[$i]}'>{$split_pg_domain[$i]}/{$split_pg_uri[$i]}</option>";
      }

    }

    
     
  }

  



}


?>


<!-- Bootstrap4 Duallistbox -->
<link rel="stylesheet" href="<?php echo G5_THEME_URL ?>/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">
<!-- Bootstrap4 Duallistbox -->
<script src="<?php echo G5_THEME_URL ?>/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script>


<section class="content">
  <div class="container-fluid">
    <form name="listForm" id="listForm" action="./partner_form_update" method="post" enctype="multipart/form-data" onSubmit="return validateForm()">

      <div class="card card-danger">
        <div class="card-header">
          <h3 class="card-title">고객사 기본항목</h3>
          <div class="text-right">          
            <?php echo isSaveBtn($w, $resultOne['ptn_deptno'], $resultOne['ptn_mb_emp'], $member, 'btn_small', 'btn btn-primary btn-xs') ?>
            <button type="button" class="btn btn-default btn-xs" id="btn_list" onclick="location.href='<?php echo G5_BIZ_URL; ?>/partner/partner_list?<?php echo $qstr;?>'">목록</button>
          </div>
        </div>
        

        <div class="card-body">
          <input type="hidden" name="w" value="<?php echo $w ?>">
          <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
          <input type="hidden" name="stx" value="<?php echo $stx ?>">
          <input type="hidden" name="sst" value="<?php echo $sst ?>">
          <input type="hidden" name="sod" value="<?php echo $sod ?>">
          <input type="hidden" name="page" value="<?php echo $page ?>">
          <input type="hidden" name="token" value="">
          <input type="hidden" name="ptn_idx" value="<?php echo $resultOne['ptn_idx'] ?>">
          <input type="hidden" name="share_flag" value="<?php echo $share_flag ?>">

          
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="form_name"><code>고객명*</code></label> <?php echo $badge ?>
                <input type="text" id="ptn_nm" name="ptn_nm" class="form-control border-info" value="<?php echo $resultOne['ptn_nm'] ?>" placeholder="고객 관리명(식별자)" required>
              </div>
            </div>
          </div>

          <!-- customer 고객도 접속가능하게 설정값 setting -->


          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label><code>카테고리*</code></label>
                <select id="category" name=category class="custom-select border-info">
                  <option value="1" <?php echo get_selected($resultOne['comm_pcd'], '1'); ?>>1:광고문의</option>
                  <option value="2" <?php echo get_selected($resultOne['comm_pcd'], '2'); ?>>2:스토어</option>
                  <option value="3" <?php echo get_selected($resultOne['comm_pcd'], '3'); ?>>3:DB</option>
                  <option value="4" <?php echo get_selected($resultOne['comm_pcd'], '4'); ?>>4:기타</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label><code>업태*</code></label>
                <select id="cate_code" name="cate_code" class="custom-select border-info">
                <?php for ($i = 0; $code = sql_fetch_array($code_list); $i++) { ?>
                  <option value="<?php echo $code['comm_idx'] ?>" <?php echo get_selected($resultOne['comm_idx'], $code['comm_idx']); ?> ><?php echo $code['comm_nm'] ?></option>
                <?php } ?>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label><code>부서*</code></label>
                <select id="ptn_deptno" name="ptn_deptno" class="form-control" data-live-search="true" data-style="border border-info">
                  <?php for ($i = 0; $dept = sql_fetch_array($dept_list); $i++) { ?>
                      <option value="<?php echo $dept['deptno'] ?>" data-tokens="<?php echo $dept['deptnm'] ?>" <?php echo get_selected($sel_dept, $dept['deptno']); ?>><?php echo $dept['deptnm'] ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                  <label><code>직원*</code></label>
                  <select id="ptn_mb_emp" name=ptn_mb_emp class="custom-select border-info">
                      <option value="">미지정</option>
                      <?php for ($i = 0; $emp = sql_fetch_array($member_list); $i++) { ?>
                          <option value="<?php echo $emp['mb_no'] ?>" <?php echo get_selected($sel_emp, $emp['mb_no']); ?> ><?php echo $emp['mb_name'] ?></option>
                      <?php } ?>
                  </select>
              </div>
            </div>
          </div>


          <!-- <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label><code>연락처*</code></label>
                <input type="text" id="ptn_phone" name="ptn_phone" class="form-control border-info" value="<?php echo $ptn_phone ?>" placeholder="연락처(010-1234-5678)" oninput="telHyphen(this);" maxlength="13" required>
              </div>
            </div>
          </div> -->


        </div>
      </div>

      <?php if($w == "u" && $share_flag == "3") { ?>
          <div class="card" id="card_shard1">
            <div class="card-header">
              <h3 class="card-title">DB분배</h3>

                <label class="ml-4 switch">
                    <input type="checkbox" id="db_is_ptn_share" name="db_is_ptn_share" class="switch-input">
                    <span class="switch-label" data-on="ON" data-off="OFF"></span>
                    <span class="switch-handle"></span>
                </label>

                <div class="custom-control custom-radio custom-control-inline">
                  <select class="custom-select custom-select-sm d-none" id="share_parent_page_idx1" name="share_parent_page_idx1">
                    <?php echo $master_uri_str ?>
                  </select>  
                  <button type="button" id="add_div1" class="btn btn-warning btn-sm d-none" style="white-space: nowrap;">추가</button>
                </div>

                <div id="divApiSet" class="card-body <?php echo $resultOne['pg_api_kind'] != 'normal' ? 'd-none' : ''?>">
                    <div class="row">
                        <div class="col-md-5">
                            <label>고객사</label>
                            <select id="share_child_ptn_A1" name="share_child_ptn_A1" class="form-control share_child_ptn selectpicker" data-live-search="true" data-style="border border-info" data-size="5">
                                <?php echo $share_str ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label>코드</label>
                            <select id="share_child_page_idx_A1" name="share_child_page_idx_A1" class="form-control border-info selectpicker"></select>
                        </div>
                        <div class="col-md-1">
                            <label>건수</label>
                            <input type="text" id="share_cnt_A1" name="share_cnt_A1" class="form-control" value="" placeholder="">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <div style="height: 24px;"></div>
                            <button class="btn btn-primary" type="button" id="add_row1">추가</button>
                        </div>
                    </div>
                    <div id="dynamicInputContainer"></div>
                </div>
              </div>
          </div>





          <div class="card d-none" id="card_shard2">
            <div class="card-header">
              <h3 class="card-title">추가분배2</h3>
                <label class="ml-4"></label>
                <div class="custom-control custom-radio custom-control-inline">
                  <select class="custom-select custom-select-sm" id="share_parent_page_idx2" name="share_parent_page_idx2">
                    <?php echo $master_uri_str ?>
                  </select>  

                  <button type="button" id="add_div2" class="btn btn-warning btn-sm" style="white-space: nowrap;">추가</button>
                  <button type="button" id="del_div2" class="btn btn-danger btn-sm" style="white-space: nowrap;">삭제</button>
                </div>

                <div id="divApiSet" class="card-body">
                    <div class="row">
                        <div class="col-md-5">
                            <label>고객사</label>
                            <select id="share_child_ptn_B1" name="share_child_ptn_B1" class="form-control share_child_ptn selectpicker" data-live-search="true" data-style="border border-info" data-size="5">
                                <?php echo $share_str ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label>코드</label>
                            <select id="share_child_page_idx_B1" name="share_child_page_idx_B1" class="form-control border-info selectpicker"></select>
                        </div>
                        <div class="col-md-1">
                            <label>건수</label>
                            <input type="text" id="share_cnt_B1" name="share_cnt_B1" class="form-control" value="" placeholder="">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <div style="height: 24px;"></div>
                            <button class="btn btn-primary" type="button" id="add_row2">추가</button>
                        </div>
                    </div>
                    <div id="dynamicInputContainer"></div>
                </div>
              </div>
          </div>



          <div class="card d-none" id="card_shard3">
            <div class="card-header">
              <h3 class="card-title">추가분배3</h3>
                <label class="ml-4"></label>
                <div class="custom-control custom-radio custom-control-inline">
                  <select class="custom-select custom-select-sm" id="share_parent_page_idx3" name="share_parent_page_idx3">
                    <?php echo $master_uri_str ?>
                  </select>  

                  <button type="button" id="del_div3" class="btn btn-danger btn-sm" style="white-space: nowrap;">삭제</button>
                </div>

                <div id="divApiSet" class="card-body">
                    <div class="row">
                        <div class="col-md-5">
                            <label>고객사</label>
                            <select id="share_child_ptn_C1" name="share_child_ptn_C1" class="form-control share_child_ptn selectpicker" data-live-search="true" data-style="border border-info" data-size="5">
                                <?php echo $share_str ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label>코드</label>
                            <select id="share_child_page_idx_C1" name="share_child_page_idx_C1" class="form-control border-info selectpicker"></select>
                        </div>
                        <div class="col-md-1">
                            <label>건수</label>
                            <input type="text" id="share_cnt_C1" name="share_cnt_C1" class="form-control" value="" placeholder="">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <div style="height: 24px;"></div>
                            <button class="btn btn-primary" type="button" id="add_row3">추가</button>
                        </div>
                    </div>
                    <div id="dynamicInputContainer"></div>
                </div>
              </div>
          </div>



          
          <?php } ?>



      <?php if($w == "u" && $share_flag == "2") { ?>
      <div class="card">
          <div class="card-header">
              <h3 class="card-title">DB분배</h3>

                <label class="ml-4 switch">
                    <input type="checkbox" id="db_is_ptn_share" name="db_is_ptn_share" class="switch-input" checked disabled>
                    <span class="switch-label" data-on="ON" data-off="OFF"></span>
                    <span class="switch-handle"></span>                    
                </label>

                <div class="custom-control custom-radio custom-control-inline">
                  <span class="btn btn-secondary btn-xs border border-dark">분배코드 : <?php echo $shared_code_label ?></span>
                  <?php if($member['mb_deptno'] == $resultOne['ptn_deptno']) { ?>
                    <button type="button" class="ml-2 btn btn-primary btn-xs border border-dark" onclick="init_db_share('<?php echo $ptn_idx ?>')">초기화</button>
                  <?php } ?>
                  
                </div>


                <div id="divApiSet" class="card-body ">
                    <div class="row">
                        <?php echo $shared_show_data ?>
                    </div>
                </div>


              </div>
          </div>
          <?php } ?>





      </div>


      <?php if($sel_dept == $member['mb_deptno']) { ?>
      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">계약 HISTORY</h3>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>상태</label>
                <select name=ptn_status id="ptn_status" class="form-control custom-select">
                    <option value="1" <?php echo get_selected($resultOne['ptn_status'], '1'); ?>>대기</option>
                    <option value="2" <?php echo get_selected($resultOne['ptn_status'], '2'); ?>>진행</option>
                    <option value="3" <?php echo get_selected($resultOne['ptn_status'], '3'); ?>>중지</option>
                    <option value="4" <?php echo get_selected($resultOne['ptn_status'], '4'); ?>>종료</option>
                    <option value="5" <?php echo get_selected($resultOne['ptn_status'], '5'); ?>>환불</option>
                    <option value="5" <?php echo get_selected($resultOne['ptn_status'], '6'); ?>>블랙</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>예산</label>
                <input type="text" name="ptn_budget" id="ptn_budget" class="form-control" value="<?php echo $resultOne['ptn_budget'] == null ? 0 : number_Format($resultOne['ptn_budget']) ?>" onkeyup="numberWithCommas(this.vale)">
              </div>
            </div>
          </div>
          <div class="row">

            <div class="col-md-6">
              <div class="form-group">
                <label>시작일</label>
                <input type="date" name="ptn_startday" id="ptn_startday" class="form-control" value="<?php echo $ptn_startday ?>">
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label>종료일</label>
                <input type="date" name="ptn_endday" id="ptn_endday" class="form-control" value="<?php echo $ptn_endday ?>">
              </div>
            </div>
            
            
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>입금일</label>
                <input type="date" name="ptn_dposday" id="ptn_dposday" class="form-control" value="<?php echo $resultOne['ptn_dposday'] ?>">
              </div>
            </div>
          </div>


          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>메모</label>
                <textarea name="ptn_memo" class="form-control" rows="4" placeholder="메모" value="<?php echo $resultOne['ptn_memo'] ?>"><?php echo $resultOne['ptn_memo'] ?></textarea>
              </div>
            </div>
          </div>
        </div>

        <?php if($hist_list->num_rows > 0) { ?>
        <div class="container">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>수정일</th>
                  <th>상태</th>
                  <th>예산</th>
                  <th>시작일</th>
                  <th>종료일</th>
                  <th>입금일</th>
                </tr>
              </thead>
              <tbody>
              <?php for ($i = 0; $hist_row = sql_fetch_array($hist_list); $i++) { ?>
                <tr>
                  <td><?php echo $i +1 ?></td>
                  <td><?php echo $hist_row['insert_date'] ?></td>
                  <td>
                    <?php 
                    if($hist_row['ptn_status'] == "1") echo "대기";
                    else if($hist_row['ptn_status'] == "2") echo "진행";
                    else if($hist_row['ptn_status'] == "3") echo "중지";
                    else if($hist_row['ptn_status'] == "4") echo "종료";
                    else if($hist_row['ptn_status'] == "5") echo "환불";
                     ?>
                  </td>
                  <td><?php echo number_format ($hist_row['ptn_budget'] ) ?></td>
                  <td><?php echo $hist_row['ptn_startday'] == "0000-00-00" ? '' : $hist_row['ptn_startday'] ?></td>
                  <td><?php echo $hist_row['ptn_endday'] == "0000-00-00" ? '' : $hist_row['ptn_endday'] ?></td>
                  <td><?php echo $hist_row['ptn_dposday'] == "0000-00-00" ? '' : $hist_row['ptn_dposday'] ?></td>
                </tr>
              <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php } ?>


      </div>
      <?php } ?>

      <div class="card card-primary">
        <div class="card-header">
          <h3 class="card-title">추가정보 > 페이지관리로드</h3>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>상호</label>
                <input type="text" id=ptn_bznm name="ptn_bznm" class="form-control" value="<?php echo $resultOne['ptn_bznm'] ?>" placeholder="사업자상호명">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>대표자</label>
                <input type="text" id="ptn_reprnm" name="ptn_reprnm" class="form-control" value="<?php echo $resultOne['ptn_reprnm'] ?>" placeholder="사업자대표자명">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>사업자번호</label>
                <input type="text" id="ptn_bznum" name="ptn_bznum" class="form-control" value="<?php echo $resultOne['ptn_bznum'] ?>" placeholder="사업자번호(ex:123-45-67890)" data-inputmask='"mask": "999-99-99999"' data-mask>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>주소지</label>
                <input type="text" id="ptn_addr" name="ptn_addr" class="form-control" value="<?php echo $resultOne['ptn_addr'] ?>" placeholder="사업자주소지">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>이메일</label>
                <input type="email" id="ptn_email" name="ptn_email"class="form-control" value="<?php echo $resultOne['ptn_email'] ?>" placeholder="이메일주소">
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
              <label>고객센터</label>
              <input type="text" id="ptn_tel" name="ptn_tel" class="form-control" value="<?php echo $resultOne['ptn_tel'] ?>" placeholder="고객센터(02-1234-5678)" oninput="telHyphen(this);" maxlength="13">              </div>
            </div>
            
          </div>


          


        </div>

        
        <div class="card-footer">
        <div class="col-12">
          <?php if($w == "u") { ?>
            <a href="#" class="btn btn-default">최초등록 <span class="badge badge-primary"><?php echo $resultOne['insert_user_name'] ?></span><span class="badge badge-primary"><?php echo substr($resultOne['insert_date'],0,16) ?></span></a>
            <a href="#" class="btn btn-default">최종수정 <span class="badge badge-warning"><?php echo $resultOne['update_user_name'] ?></span><span class="badge badge-warning"><?php echo substr($resultOne['update_date'],0,16) ?></span></a>
          <?php } ?>
          
            <?php echo isSaveBtn($w, $resultOne['ptn_deptno'], $resultOne['ptn_mb_emp'], $member, 'btn_normal', 'btn btn-primary float-right') ?>
            <button type="button" class="btn btn-default float-right" id="btn_list" onclick="location.href='<?php echo G5_BIZ_URL; ?>/partner/partner_list?<?php echo $qstr;?>'">목록</button>
        </div>
        </div>
      </div>


    </form>
  </div>
</section>


<div class="modal fade" id="percentageModal" tabindex="-1" role="dialog" aria-labelledby="percentageModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="percentageModalLabel">퍼센테이지 입력</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="number" class="form-control" id="percentageInput" placeholder="퍼센테이지 입력">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="savePercentage">저장</button>
      </div>
    </div>
  </div>
</div>

<script>

var initialCardShard2HTML = $("#card_shard2").html();
var initialCardShard3HTML = $("#card_shard3").html();

  $(document).ready(function() {


    $('#add_div1, #add_div2').click(function() {
        // 클릭된 요소의 ID에 따라 분기
        if (this.id === 'add_div1') {
            // #add_div1에 대한 처리
            $("#card_shard2").removeClass("d-none");
        } else if (this.id === 'add_div2') {
            // #add_div2에 대한 처리
            $("#card_shard3").removeClass("d-none");
        }

        var selectedValues = {};

        // 이미 선택된 값들을 수집합니다.
        $('select[id^="share_parent_page_idx"]').each(function() {
            if ($(this).val() !== '') {
                selectedValues[$(this).val()] = true;
            }
        });

        // 아직 선택되지 않은 첫 번째 유효한 옵션을 찾아 선택합니다.
        var allOptionsSelected = true;
        $('select[id^="share_parent_page_idx"]').each(function() {
            var $select = $(this);
            // 이미 선택값이 있는 경우 스킵합니다.
            if ($select.val() !== '') return;

            $select.find('option').each(function() {
                var optionValue = $(this).val();
                // 첫 번째 '미선택' 상태가 아니고, 아직 선택되지 않은 옵션을 찾습니다.
                if (optionValue !== '' && !selectedValues[optionValue]) {
                    $select.val(optionValue); // 해당 옵션을 선택합니다.
                    selectedValues[optionValue] = true; // 선택된 값으로 표시합니다.
                    allOptionsSelected = false; // 아직 선택되지 않은 옵션이 있음을 표시합니다.
                    return false; // 더 이상의 순회를 중단합니다.
                }
            });

            // 첫 번째 미선택된 옵션이 선택되면 순회를 중단합니다.
            if (!allOptionsSelected) return false;
        });

        if (allOptionsSelected) {
            alert('모든 선택지가 이미 선택되었습니다.');
        }
    });

    $('#ptn_deptno').selectpicker();
    //$('#share_child_ptn').selectpicker();
    
    $('input[type=checkbox][name=db_is_ptn_share]').change(function() {
        var checked = $(this).is(':checked');
        if(checked == true) {
          $("#divApiSet").removeClass("d-none");
          $("#add_div1").removeClass("d-none");
          $("#divApiSet").attr("disabled",false);

          $("#share_parent_page_idx1").removeClass("d-none");
          $("#share_parent_page_idx1").attr("disabled",false);
          
          
        }else{
          $("#divApiSet").addClass("d-none");
          $("#add_div1").addClass("d-none");
          $("#divApiSet").attr("disabled",true);

          $("#share_parent_page_idx1").addClass("d-none");
          $("#share_parent_page_idx1").attr("disabled",true);

          //여기에 card_shard2 + card_shard3 초기화 코드 삽입 (완전 삭제하면 안됨 체크박스 재활성화시 고려)

          $("#card_shard2").addClass("d-none");
          $("#card_shard3").addClass("d-none");

          $('[id^="share_child_ptn_A"], [id^="share_child_ptn_B"], [id^="share_child_ptn_C"]').each(function() {
              $(this).val(''); // 선택되지 않은 상태로 설정
              $(this).selectpicker('refresh'); // selectpicker 업데이트
          });

          // share_child_page_idx_A*, B*, C* 초기화
          $('[id^="share_child_page_idx_A"], [id^="share_child_page_idx_B"], [id^="share_child_page_idx_C"]').each(function() {
              $(this).val(''); // 선택되지 않은 상태로 설정
              $(this).selectpicker('refresh'); // selectpicker 업데이트
          });

          // share_cnt_A*, B*, C* 초기화
          $('[id^="share_cnt_A"], [id^="share_cnt_B"], [id^="share_cnt_C"]').each(function() {
              $(this).val(''); // 빈 문자열로 설정
          });
          

          // $("#card_shard2").html(initialCardShard2HTML);
          // $("#card_shard3").html(initialCardShard3HTML);

        }
    });

    $('select[id^="share_parent_page_idx"]').change(function() {
        var currentId = $(this).attr('id');
        var currentValue = $(this).val();
        var isDuplicate = false;

        // 다른 select 요소들의 값을 검사하여 중복되는지 확인
        $('select[id^="share_parent_page_idx"]').not(this).each(function() {
            if ($(this).val() === currentValue && currentValue !== '') {
                isDuplicate = true;
            }
        });

        if (isDuplicate) {
            alert('중복된 선택입니다. 다른 옵션을 선택해주세요.');
            // 중복이 발견되면 이전 값을 선택
            $(this).val(prevValues[currentId]);
            $(this).selectpicker('refresh'); // bootstrap-select 사용 시 필요
        } else {
            // 중복이 없으면 이전 값을 현재 값으로 업데이트
            prevValues[currentId] = currentValue;
        }
    });



    //dept select bug
    $("form[name='listForm']").keypress(function(e) {
      if (e.which == 13) {
        // $('#searchForm').submit();
        $(this).next('input').focus();
        return false;
      }
    });

    //onchange category 
    $("#category").change(function() {
      var comm_pcd = $(this).val();
      var act = "commonCode";

      var $target = $("#cate_code");
      $target.empty();

      $.ajax({
        type: "post",
        data: {
          comm_pcd: comm_pcd ,
          act: act
        },
        url: "partner_ajax",
        dataType: "json", //전송받는 데이터형태 json
        success:function(result) {
          $target.append(result);
        },
        error: function(xhr) {
          console.log(xhr.responseText);
          alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
          return;
        }
      });
    });


    //고객명 중복체크
    $("#ptn_nm").change(function () {
        if ($(this).val() != "") {
              var len = $(this).val().length;
              if (len <= 2) {
                  alert("고개명 3글자이상이어야 합니다");
                  return false;
              }
              var ptn_nm = $(this).val();
              var act = "dup_partner";
              $.ajax({
                  type: "post",
                  url: "partner_ajax",
                  data: {
                    ptn_nm: ptn_nm ,
                    act: act
                  },
                  success: function (result) {
                    
                      //alert(result.employeeCnt);//JSON.stringify(result)
                      if (result == "0") {//ptn_nm 존재하지 않으면
                          $("#btn_insert").attr("disabled", false);
                          $("#btn_insert").css("opacity", "1");
                          $("#ptn_nm").attr('class', 'form-control is-valid');

                      } else if (result == "1") {//ptn_nm 존재

                          alert("["+ptn_nm + "] 고객명 중복");
                          $("#btn_insert").attr("disabled", true);
                          $("#btn_insert").css("opacity", "0.5");

                          $("#ptn_nm").val("");
                          $("#ptn_nm").attr('class', 'form-control is-invalid');
                      } else if (result == "2") {//ptn_nm 존재

                        
                        var result = confirm("["+ptn_nm + "] 가입대기 고객사입니다 가입데이터를 불러오시겠습니까?");
                        if(result){
                            
                          var act = "load_partner";

                          $.ajax({
                            type: "post",
                            url: "partner_ajax",
                            dataType: "json",
                            data: {
                              ptn_nm: ptn_nm ,
                              act: act
                            },
                            success: function (row) {

                              $("#ptn_reprnm").val("");
                              $("#ptn_phone").val("");
                              $("#ptn_tel").val("");
                              $("#ptn_email").val("");

                              $("#ptn_reprnm").val(row.ptn_reprnm);
                              $("#ptn_phone").val(row.ptn_phone);
                              $("#ptn_tel").val(row.ptn_tel);
                              $("#ptn_email").val(row.ptn_email);
                            }
                          });
                              
                        }

                        $("#btn_insert").attr("disabled", false);
                        $("#btn_insert").css("opacity", "1");
                        $("#ptn_nm").attr('class', 'form-control is-valid');

                      }
                  },
                  error: function () {
                      alert("RestAPI서버가 작동하지 않습니다. 다음에 이용해 주세요.");
                  }
              });

          } else {
              $("#btn_insert").attr("disabled", true);
              $("#btn_insert").css("opacity", "0.5");

              $("#emp_id").val("");
              $("#emp_id").attr('class', 'form-control is-invalid');
          }
    });
        

    $("#ptn_deptno").change(function() {
        var deptno = $(this).val();
        var act = "deptByEmp";

        var $target = $("#ptn_mb_emp");
        $target.empty();

        $target.append('<option value="">미지정</option>');

        $.ajax({
            type: "post",
            data: {
                deptno: deptno ,
                act: act
            },
            url: "<?php echo G5_BIZ_URL?>/common/code_ajax",
            dataType: "json", //전송받는 데이터형태 json
            success:function(result) {
                $target.append(result);
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                return;
            }
        });
    });

    
    $(document).on('change', '[id^="share_child_ptn"]', handleShareChildPtnChange);
    
    function handleShareChildPtnChange() {
        var ptn_idx = $(this).val();
        var currentId = $(this).attr('id');
        // ID에서 접미사를 추출하여 대응하는 share_child_page_idx 요소를 찾습니다.
        var match = currentId.match(/^(share_child_ptn_)([A-C])(\d+)$/);
        var group = match[2]; // A, B, C 등의 그룹을 식별합니다.
        var targetIdSuffix = match[3];
        var $target = $(this).closest('.row').find(`select[name="share_child_page_idx_${group}${targetIdSuffix}"]`);

        // AJAX 호출 전에 기존 옵션을 제거
        $target.empty();
        $target.selectpicker('refresh');

        var selectedValue = $(this).val();

        // A 그룹에서만 중복 선택을 체크합니다.
        if (group === 'A' && isCustomerAlreadySelected(currentId, selectedValue, group)) {
            alert("이미 선택된 고객사입니다.");
            $(this).val('');
            $(this).selectpicker('refresh');
            return; // 추가적인 AJAX 호출을 방지
        }

        // AJAX 요청
        $.ajax({
            type: "post",
            data: { ptn_idx: ptn_idx, act: "chg_ptn_share" },
            url: "partner_ajax",
            dataType: "json",
            success: function(result) {
                $target.append(result);
                $target.selectpicker('refresh');
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("에러 발생");
            }
        });
    }


    function isCustomerAlreadySelected(currentDropdownId, selectedValue, group) {
      var allSelectedValues = [];
      // A 그룹 내의 모든 select 요소를 순회합니다.
      $(`[id^="share_child_ptn_${group}"]`).each(function() {
          var id = $(this).attr('id');
          if (id !== currentDropdownId) {
              allSelectedValues.push($(this).val());
          }
      });
      return allSelectedValues.indexOf(selectedValue) !== -1;
  }



    $('#add_row1').click(function() {
        // A 그룹 내의 요소 개수를 기반으로 새 아이디를 생성합니다.
        var rowCount = $('[id^="share_child_ptn_A"]').length;
        if (rowCount >= 5) {
            alert("더 이상 추가할 수 없습니다.");
            return;
        }

        // 새로운 row 생성
        var newRow = $('<div class="row"></div>');
        var newDbPtnShare = $('#share_child_ptn_A1').clone().attr('id', 'share_child_ptn_A' + (rowCount + 1)).attr('name', 'share_child_ptn_A' + (rowCount + 1)).addClass('share_child_ptn_A');
        var newShareCode = $('<select class="form-control border-info selectpicker" id="share_child_page_idx_A' + (rowCount + 1) + '" name="share_child_page_idx_A' + (rowCount + 1) + '" data-live-search="true" data-style="border border-info" data-size="5"></select>');
        var newShareCnt = $('#share_cnt_A1').clone().attr('id', 'share_cnt_A' + (rowCount + 1)).attr('name', 'share_cnt_A' + (rowCount + 1)).val('');

        // 삭제 버튼 추가
        var newDelButton = $('<button class="btn btn-danger" type="button">삭제</button>').click(function() {
            $(this).closest('.row').remove();
        });

        // 구성된 요소들을 새 row에 추가
        newRow.append($('<div class="col-md-5"></div>').append(newDbPtnShare));
        newRow.append($('<div class="col-md-5"></div>').append(newShareCode));
        newRow.append($('<div class="col-md-1"></div>').append(newShareCnt));
        newRow.append($('<div class="col-md-1 d-flex align-items-end"></div>').append(newDelButton));

        // 새 row를 페이지에 추가
        $('#dynamicInputContainer').append(newRow);
        
        // 새롭게 추가된 selectpicker들을 초기화
        newDbPtnShare.selectpicker();
        newShareCode.selectpicker();
    });

      



    $(".inpt").change(function(){
      $(this).removeClass("is-invalid");
    });

    $('.selc').focusout(function() {
        $(this).removeClass("is-invalid");
    });

  });

  function init_db_share(ptn_idx) {

    var result = confirm("적용한 DB분배를 초기화 하겠습니까?");
    var hidden_page_idx = '<?php echo $hidden_page_idx ?>';

    if(result){
     
      $.ajax({
          type: "post",
          data: { ptn_idx: ptn_idx, hidden_page_idx: hidden_page_idx, act: "init_db_share" },
          url: "partner_ajax",
          dataType: "json",
          success: function(result) {
              alert(result);
              location.reload();
          },
          error: function(xhr) {
              console.log(xhr.responseText);
              alert("에러 발생");
          }
      });

    }
  }

  
  


  function validateForm() {
    var ptn_budget = listForm.ptn_budget.value;
    var ptn_startday = listForm.ptn_startday.value;
    var ptn_endday = listForm.ptn_endday.value;
    var ptn_deptno = listForm.ptn_deptno.value;
    var mode = '<?php echo $w ?>';

    if(ptn_startday == "" || ptn_startday == null) {
      alert("시작일 필수입력항목 입니다.");
      return false;
    }
    if(ptn_endday == "" || ptn_endday == null) {
      alert("종료일 필수입력항목 입니다.");
      return false;
    }

    var date1 = new Date(ptn_startday);
    var date2 = new Date(ptn_endday);

    if(date1 > date2) {
      alert("시작일 종료일이 유효하지 않습니다.");
      return false;
    }


    if ($('#db_is_ptn_share').is(':checked')) {
        // 모든 행을 순회하면서 검사
        var isValid = true;
        var share_flag = '<?php echo $share_flag ?>';
        var w = '<?php echo $w ?>';

        $('#divApiSet .row').each(function() {
            var dbPtnShare = $(this).find('select[name^="share_child_ptn"]').val();
            var shareCode = $(this).find('select[name^="share_child_page_idx"]').val();
            var shareCnt = $(this).find('input[name^="share_cnt"]').val();

            if (!dbPtnShare || !shareCode || !shareCnt) {
                isValid = false;
                return false; // 루프 중단
            }
        });

        if (!isValid) {
            if(w == 'u' && share_flag == '3') {
                alert("DB 분배 필드를 채워주세요.");
                return false; // 폼 제출 중단
            }
        }
    }


    
    document.getElementById("btn_small").disabled = "disabled";
    document.getElementById("btn_normal").disabled = "disabled";
    
    return true;
  }
  
  const input = document.querySelector('#ptn_budget');
  input.addEventListener('keyup', function(e) {
    let value = e.target.value;
    value = Number(value.replaceAll(',', ''));
    if(isNaN(value)) {         //NaN인지 판별
      input.value = 0;   
    }else {                   //NaN이 아닌 경우
      const formatValue = value.toLocaleString('ko-KR');
      input.value = formatValue;
    }
  })

</script>






<?php
include_once(G5_PATH . '/tail.php');
