<?php
require_once '../../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');

if ($w == '') {
    $title = "PAGE 등록";

    $sel_dept = $member['mb_deptno'];
    $sel_ptn = "";
   
    
    


    $ptn_lastest_sql = "
    select design_idx
         , f_getcode(des_cate_code) as des_cate_code_nm
    from {$g5['crm_design']} a
    where use_yn = 'Y'
    order by design_idx desc
    limit 0,1
    ";
    $row = sql_fetch($ptn_lastest_sql);
    $des_cate_code_nm = $row['des_cate_code_nm'];

    $none_opt = '<option value="">미지정</option>';

} else if($w == 'u') {
    $title = "PAGE 수정";

    $resultOneSql = "
    select page_idx
         , pg_uri
         , pg_domain
         , pg_memo
         , pg_des_idx
         , pg_deptno
         , pg_mb_emp
         , pg_ptn_idx
         , pg_mb_ptn
         , f_getcode(b.des_cate_code) as des_cate_code_nm
         , pg_inflow
         , pg_title
         , pg_meta_desc
         , pg_meta_keyword
         , pg_redirect_url
         , pg_alert_msg
         , pg_popup_img
         , pg_compnm
         , pg_reprnm
         , pg_bznum
         , pg_addr
         , pg_tel
         , pg_email
         , pg_bef_head
         , pg_aft_head
         , pg_add_script
         , pg_sms_yn
         , pg_db_sms_yn
         , pg_db_sms_kind
         , pg_db_sms_msg
         , pg_db_user_sms_yn
         , pg_db_user_sms_msg
         , pg_aft_ad_yn
         , pg_aft_ad_cat
         , pg_aft_ad_size
         , pg_aft_ad_title
         , pg_aft_ad_desc
         , pg_aft_ad_sdate
         , pg_aft_ad_edate
         , pg_aft_ad_file_idx
         , pg_api_yn
         , pg_api_kind
         , pg_api_url
         , pg_api_add_param
         , pg_api_param_way
         , pg_api_return_way
         , pg_api_success
         , pg_api_fail
         , pg_api_duplicate
         , google_addr
         , google_sheet
         , google_cell
         , pg_chk_name
         , pg_chk_data1
         , pg_chk_data2
         , pg_chk_data3
         , pg_chk_data4
         , pg_chk_data5
         , pg_chk_data6
         , pg_chk_data7
         , pg_chk_data8
         , pg_chk_data9
         , pg_chk_code
         , pg_chk_utm
         , pg_chk_ip
         , a.insert_date
         , a.update_date
         , a.insert_user
         , a.update_user
         , b.design_name
    from {$g5['crm_page']} a
    left join {$g5['crm_design']} b on a.pg_des_idx = b.design_idx
    where page_idx = {$page_idx}
    ";
    $resultOne = sql_fetch($resultOneSql);

    $aft_pop_file = null;
    if ($resultOne['pg_aft_ad_yn'] == 'Y') {
        $aft_pop_file = sql_fetch(" select * from gnp_crm_aft_pop_file where page_idx = '{$page_idx}' limit 0, 1 ");

        // 시작일/종료일 기본값 설정 (값 없을 때만)
        if (!$resultOne['pg_aft_ad_sdate']) $resultOne['pg_aft_ad_sdate'] = date('Y-m-d');
        if (!$resultOne['pg_aft_ad_edate']) $resultOne['pg_aft_ad_edate'] = date('Y-m-d', strtotime('+1 year'));
    }

    $page_idx = $resultOne['page_idx'];
    $pg_uri = $resultOne['pg_uri'];
    $sel_dept = $resultOne['pg_deptno'];
    $sel_ptn = $resultOne['pg_ptn_idx'];
    $pg_ptn_idx = $resultOne['pg_ptn_idx'];
    $des_cate_code_nm = $resultOne['des_cate_code_nm'];

    $pg_popup_img = $resultOne['pg_popup_img'];
    // $img_parts = explode('||||', $pg_popup_img);

    // $pg_popup_img = $img_parts[0];
    // $pg_popup_img_size = isset($img_parts[1]) ? $img_parts[1] : '';


    $linkByLand = '<a href="https://'.$resultOne['pg_domain'].'/'.$resultOne['pg_uri'].'" target="_blank"> <i class="fas fa-plane-departure text-success"></i></a>';
    $linkByDes = G5_BIZ_URL.'/design/design_form?w=u&design_idx='.$resultOne['pg_des_idx'];
    $linkByPage = '<a href="https://'.$resultOne['pg_domain'] . '/' . $resultOne['pg_uri'] .'" target="_blank"><i class="fas fa-plane-departure"></i></a>';

    $dynamicHTML = "";
    for ($i = 1; $i <= 9; $i++) {
        if (isset($resultOne["pg_chk_data$i"]) && $resultOne["pg_chk_data$i"] !== '') {
            $dynamicHTML .= "<div id='containerForData$i'>";
            $dynamicHTML .= "<label id='labelForData$i' for='pg_chk_data$i'>옵션$i :</label>";
            $dynamicHTML .= "<input type='text' id='pg_chk_data$i' name='pg_chk_data$i' class='form-control' placeholder='옵션$i 값 입력' value='" . htmlspecialchars($resultOne["pg_chk_data$i"]) . "'>";
            $dynamicHTML .= "</div>";
        }
    }


    //is parent share check
    $shared_sql = "
    select b.ptn_nm, a.*
    from {$g5['crm_db_share']} a
    left join {$g5['crm_partner']} b on a.share_parent_ptn = b.ptn_idx
    where (share_parent_page_idx = {$page_idx} or share_child_page_idx = {$page_idx})
    limit 0,1
    ";
    $shared_row = sql_fetch_array(sql_query($shared_sql));

    //내가 자식페이지인데 부모 쉐어기능에 소속되어 있음
    if ($shared_row) {
        
        if($shared_row['share_parent_page_idx'] == $page_idx){
            $isSharedYn = "N";
            $isNotiMsg = "<small class='text-danger'> [ (<a href='" . G5_BIZ_URL . "/partner/partner_form?w=u&ptn_idx=" . $shared_row['share_parent_ptn'] . "' target='_blank'>{$shared_row['ptn_nm']}</a>) MASTER로 지정된 코드입니다.]</small>";
        } else {
            $isSharedYn = "Y";
            $isNotiMsg = "<small class='text-danger'> [ (<a href='" . G5_BIZ_URL . "/partner/partner_form?w=u&ptn_idx=" . $shared_row['share_parent_ptn'] . "' target='_blank'>{$shared_row['ptn_nm']}</a>) 고객사에 ({$shared_row['share_count']}건) 상속된 코드입니다..]</small>";
        }
    
        
    }
}

if($w == ""){
    $linkByLand = '';
    $linkByDes = '#';
    $linkByPage = '';
}

$g5['title'] = $title;
include_once(G5_PATH . '/head.php');

$cond_gubun = "";
$order_gubun = "";
if($member['mb_deptno'] != 9) {
    //$cond_gubun = "and des_deptno = {$member['mb_deptno']}";
    $order_gubun = "order by field(a.des_deptno, {$member['mb_deptno']}) desc, a.design_idx desc";
} else {
    $order_gubun = "order by a.design_idx desc";
}
//디자인리스트
$design_sql = "
select design_idx 
     , design_name
     , f_getcode(des_cate_code) as des_cate_code_nm
     , a.des_deptno 
     , b.deptnm
  from {$g5['crm_design']} a
  left join {$g5['crm_depart']} b on a.des_deptno = b.deptno 
 where a.use_yn = 'Y'
 {$cond_gubun}
 {$order_gubun}
";
$design_list = sql_query($design_sql);


if($member['mb_deptno'] != "9") {
    if($member['mb_level'] <= 6) {
        if($member['mb_level'] == 4) {
            $add_cond = "and deptno  = {$member['mb_deptno']}";
        } else {
            //$add_cond = "and ptn_deptno = {$member['mb_deptno']}";
        }
    }
} 

//부서코드
$dept_sql = "
select deptno
     , deptnm
     , parent_deptno
  from {$g5['crm_depart']} a
 where use_yn = 'Y'
   and parent_deptno != 1
group by a.deptno 
order by coalesce(parent_deptno, deptno), parent_deptno is not null, deptno
";
$dept_list = sql_query($dept_sql);

if($member['mb_deptno'] != "9") {
    if($member['mb_level'] <= 6) {
        if($member['mb_level'] == 4) {
            $add_cond = "and mb_no  = {$member['mb_no']}";
        } else {
            $add_cond = "and mb_deptno = {$sel_dept}";
        }
    }
} else if($sel_dept != "") {
    $add_cond = "and mb_deptno = {$sel_dept}";
}
//부서별직원코드
$member_sql = "
select mb_no 
     , mb_id 
     , mb_name
     , mb_deptno 
  from {$g5['member_table']}
 where mb_gubun = 'E'
   and is_login = 'Y'
   {$add_cond}
 order by mb_name asc
   
";
$member_list = sql_query($member_sql);

if($member['mb_deptno'] != "9") {
    if($member['mb_level'] <= 6) {
        if($member['mb_level'] == 4) {
            $add_cond = "and ptn_mb_emp  = {$member['mb_no']}";
        } else {
            $add_cond = "and ptn_deptno = {$sel_dept}";
        }
    }
} else if($sel_dept != "") {
    $add_cond = "and ptn_deptno = {$sel_dept}";
}
//고객사코드
$partner_sql = "
select ptn_idx
     , ptn_nm
  from {$g5['crm_partner']} 
 where use_yn = 'Y'
 {$add_cond}
 order by ptn_idx desc
";
$partner_list = sql_query($partner_sql);

//처음 매핑된 고객사 임직원 list 출력해야함
// if($w=="") {
//     $ptn_emp = sql_fetch_array($partner_list);
//     //$first_ptn_emp = 'mb_deptno = '.$ptn_emp['ptn_idx'];
//     $sel_ptn = $ptn_emp['ptn_idx'];
//     sql_data_seek($partner_list, 0);
// }

if($member['mb_deptno'] != "9") {
    if($member['mb_level'] <= 6) {
        if($member['mb_level'] == 4) {
            $add_cond = "and mb_ptnidx  = {$member['mb_deptno']}
            ";
        } else {
            $add_cond = "and mb_ptnidx = {$sel_ptn}";
        }
    }
} else if($w == "u" && $sel_dept != "") {
    $add_cond = "and mb_ptnidx = {$sel_ptn}";
}

//고객사 직원코드
$ptn_member_sql = "
select mb_no 
    , mb_id 
    , mb_name
    , mb_ptnidx 
    , mb_gubun
from {$g5['member_table']}
where 1=1
{$add_cond}
and is_login = 'Y'
";
$ptn_member_list = sql_query($ptn_member_sql);


//추가스크립트
$add_script_sql = "
select *
from {$g5['crm_page_script']}
where use_yn = 'Y'
";
$add_script = sql_query($add_script_sql);


//부서별 도메인 주소 매핑작업
//$domain = explode ( '&&', $config['cf_domain'] );

if($member['mb_deptno'] != 9) {
    $my_dept = $member['mb_deptno'];

    $add_cont = "
    and domain_deptno is null
    or domain_deptno = {$my_dept}
    ";
} else {
    $add_cont = "order by domain_deptno asc";
}

$domain_sql = "
select *
  from {$g5['comm_domain']}
 where use_yn = 'Y'
  {$add_cont}
";
$domain_list = sql_query($domain_sql);

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
 and comm_pcd = 4
";
$code_list = sql_query($code_sql);


$search_deptno1 = "";
$search_ptn_idx1 = "";

if($sfl == "deptno" && $search_deptno!="") {
    $search_deptno1 .= "&search_deptno=".$search_deptno;

    if($search_ptn_idx!="") {

        if($search_ptn_idx[0] == "") {
            $search_ptn_idx1 .= array_slice($search_ptn_idx,1);
        }
        //고객사 정상적인데이터 여러개 선택시 split
        if(count($search_ptn_idx) >= 1) {

            for($ptn=0; $ptn < count($search_ptn_idx); $ptn++ ) {
                $search_ptn_idx1 .= "&search_ptn_idx[]=".$search_ptn_idx[$ptn];
            }
        }
    }
}

?>

<style>
.dropdown-menu {
	-webkit-touch-callout: none;
	-webkit-user-select: none;
	-khtml-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none;
	padding: 0;
}
.dropdown-menu a {
	overflow: hidden;
	outline: none;
}

.bss-input{
	border:0;
	outline: none;
	color: #000;
	width: 100%;
	background-color: #ffffff;
	font-size: 16px;
	/* border-bottom: 2px solid #000000; */
}
.bss-input::-webkit-input-placeholder{opacity:1; color: #000000;}
.bss-input:-moz-placeholder{opacity:1; color: #000000;}
.bss-input::-moz-placeholder{opacity:1; color: #000000;}
.bss-input:-ms-input-placeholder{opacity:1; color: #000000;}
.bss-input:hover{
	background-color: #ffffff;
}
.dropdown-item.addItem{
	background: none;
	padding: 0;
	position: relative;
}
.addItem .bss-input{
	height: 40px;
	padding: 0 1.5rem;
}
.addItem:focus{
	background: none;
}
.bootstrap-select .dropdown-menu li a.addItem span.text{
	display: block;
}
.additem .check-mark{
	opacity: 0;
	z-index: -1000;
}
.addnewicon {
	position: absolute;
	padding: 0;
	margin: 0;
	right: 0;
	top: 0;
	line-height: 40px;
	text-align: center;
	font-size: 25px;
	width: 40px;
	height: 40px;
	color: #000000;
}
.addnewicon:hover {
	color: #000000;
}
</style>

<section class="content">
    <div class="container-fluid">
        <form name="pageForm" id="pageForm" action="./page_form_update" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">

            <div class="card card-danger">

                <div class="card-header">
                    <h3 class="card-title">페이지 기본설정 <?php echo $linkByPage ?></h3>
                    <div class="text-right">
                        <?php echo isSaveBtn($w, $resultOne['pg_deptno'], $resultOne['pg_mb_emp'], $member, 'btn_small', 'btn btn-primary btn-xs') ?>
                        <button type="button" class="btn btn-default btn-xs" id="btn_list" onclick="location.href='<?php echo G5_BIZ_URL; ?>/page/page_list?<?php echo $qstr;?>'">목록</button>
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
                    <input type="hidden" name="page_idx" value="<?php echo $resultOne['page_idx'] ?>">

                    <input type="hidden" name="search_deptno" value="<?php echo $search_deptno1 ?>">
                    <input type="hidden" name="search_ptn_idx" value="<?php echo $search_ptn_idx1 ?>">
                    <input type="hidden" name="asis_ptn" value="<?php echo $resultOne['pg_ptn_idx'] ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code id="designLabel">디자인*<a href="<?php echo $linkByDes ?>" id="linkByDes" target="_blank"> <i class="fas fa-link"></i></a></code></label>
                                <select id="pg_des_idx" name=pg_des_idx class="form-control" data-live-search="true" data-style="border border-info" data-size="10" required>
                                <?php for ($i = 0; $design = sql_fetch_array($design_list); $i++) { 
                                    if($w == "" && $i == 0) {
                                        echo $none_opt;
                                    }
                                ?>
                                    <option value="<?php echo $design['design_idx'] ?>" data-tokens="<?php echo $design['deptnm'].':'.$design['design_name'] ?>" <?php echo get_selected($resultOne['pg_des_idx'], $design['design_idx']); ?>  ><?php echo $design['deptnm'].':'.$design['design_name'] ?></option>
                                <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>PAGE설명*</code></label>
                                <input type="text" id="pg_memo" name="pg_memo" class="form-control border-info" value="<?php echo $resultOne['pg_memo'] ?>" placeholder="PAGE 설명" required>
                                
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>도메인*</code></label>
                                <select id="pg_domain" name=pg_domain class="form-control border-info">
                                    <?php for ($i = 0; $domain = sql_fetch_array($domain_list); $i++) { ?>
                                        <option value="<?php echo $domain['domain'] ?>" <?php echo get_selected($domain['domain'], $resultOne['pg_domain']); ?>> <?php echo "[".$domain['domain_desc']. "] ".  $domain['domain'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>코드*<?php echo $linkByLand ?></code></label> <?php echo $isNotiMsg ?>
                                <input type="text" id="pg_uri" name="pg_uri" class="form-control border-info" value="<?php echo $resultOne['pg_uri'] ?>" placeholder="코드(URI)" style="text-transform: lowercase;" pattern=".{3,16}" required title="자리수 3~16" oninput="this.value = this.value.toLowerCase().replace(/[^a-z0-9_.-]/g, '');" required>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>부서*</code></label>
                                <select id="pg_deptno" name="pg_deptno" class="form-control" data-live-search="true" data-style="border border-info">
                                    <?php 
                                    $sel_deptno = $w == "" ? $member['mb_deptno'] : $resultOne['pg_deptno'];
                                    for ($i = 0; $dept = sql_fetch_array($dept_list); $i++) { 
                                    ?>
                                        <option value="<?php echo $dept['deptno'] ?>" data-tokens="<?php echo $dept['deptnm'] ?>" <?php echo get_selected($sel_deptno, $dept['deptno']); ?>  ><?php echo $dept['deptnm'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>담당자*</code></label>
                                <select id="pg_mb_emp" name=pg_mb_emp class="form-control border-info">
                                    <option value="">미지정</option>
                                    <?php 
                                    $sel_emp = $w == "" ? $member['mb_no'] : $resultOne['pg_mb_emp'];
                                    for ($i = 0; $emp = sql_fetch_array($member_list); $i++) { ?>
                                        <option value="<?php echo $emp['mb_no'] ?>" <?php echo get_selected($sel_emp, $emp['mb_no']); ?> ><?php echo $emp['mb_name'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>고객사*</code></label>
                                <select id="pg_ptn_idx" name=pg_ptn_idx class="form-control" data-live-search="true" data-style="border border-info" data-size="10">
                                    <option value="">미지정</option>
                                    <?php for ($i = 0; $partner = sql_fetch_array($partner_list); $i++) { 
                                        if($resultOne['pg_ptn_idx'] == $partner['ptn_idx']) {
                                            $sel_ptn = $resultOne['pg_ptn_idx'];

                                            // if($partner['ptn_send_sms_yn'] == "Y") {
                                            //     $ptn_send_sms_yn = $partner['ptn_send_sms_yn'];
                                            //     $ptn_send_sms_idx = $partner['ptn_send_sms_idx'];
                                            //     $ptn_send_sms_msg = $partner['ptn_send_sms_msg'];
                                            // }
                                        }
                                    ?>
                                        <option value="<?php echo $partner['ptn_idx'] ?>" data-tokens="<?php echo $partner['ptn_nm'] ?>" <?php echo get_selected($resultOne['pg_ptn_idx'], $partner['ptn_idx']); ?>><?php echo $partner['ptn_nm'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>고객사직원*</code></label>
                                <select id="pg_mb_ptn" name=pg_mb_ptn class="form-control border-info">
                                    <option value="">미지정</option>
                                    <?php for ($i = 0; $ptn_mb = sql_fetch_array($ptn_member_list); $i++) { 
                                        if($ptn_mb['mb_gubun'] == "P") {
                                            $gubun = "대표";
                                        } else {
                                            $gubun = "직원";
                                        }
                                    ?>
                                        <option value="<?php echo $ptn_mb['mb_no'] ?>" data-tokens="<?php echo $ptn_mb['mb_name'] ?>" <?php echo get_selected($resultOne['pg_mb_ptn'], $ptn_mb['mb_no']); ?>><?php echo $ptn_mb['mb_name'] . "(".$gubun.")"  ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        
                    </div>


                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>유입채널</label>
                                <input type="text" id="pg_inflow" name="pg_inflow" class="form-control" value="<?php echo $resultOne['pg_inflow'] ?>" maxlength="10">
                            </div>
                        </div>
                        
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>[디자인]카테고리-업종*</code></label>
                                <input type="text" id="desByCate" name="desByCate" class="form-control bg-secondary" value="<?php echo $des_cate_code_nm ?>" disabled>
                            </div>
                        </div>
                       
                        
                    </div>
                </div>
            </div>


            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title mb-0">후팝업 설정</h3>
                    <label class="ml-4 switch mb-0">
                        <input type="checkbox" id="pg_aft_ad_yn" name="pg_aft_ad_yn" value="Y" class="switch-input" <?php echo $resultOne['pg_aft_ad_yn'] == "Y" ? "checked" : ""?>>
                        <span class="switch-label" data-on="ON" data-off="OFF"></span>
                        <span class="switch-handle"></span>
                    </label>
                </div>
            
                <div id="div_aft_pop_area" class="card-body <?php echo $resultOne['pg_aft_ad_yn'] != "Y" ? "d-none" : ""?>">
            
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>카테고리</label>
                                <select id="pg_aft_ad_cat" name="pg_aft_ad_cat" class="form-control">
                                    <?php
                                    $pop_categories = array("인터넷.TV", "개인회생", "정수기", "창업", "정책자금", "보험", "렌트", "기타");
                                    foreach($pop_categories as $cat) {
                                        echo '<option value="'.$cat.'" '.get_selected($resultOne['pg_aft_ad_cat'], $cat).'>'.$cat.'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>후팝업 타이틀</label>
                                <input type="text" name="pg_aft_ad_title" class="form-control" value="<?php echo $resultOne['pg_aft_ad_title'] ?>" placeholder="제목 입력">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>노출 시작일</label>
                                <input type="date" name="pg_aft_ad_sdate" class="form-control" value="<?php echo $resultOne['pg_aft_ad_sdate'] ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>노출 종료일</label>
                                <input type="date" name="pg_aft_ad_edate" class="form-control" value="<?php echo $resultOne['pg_aft_ad_edate'] ?>">
                            </div>
                        </div>
                    </div>
            
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group mb-0">
                                <label>후팝업 상세설명</label>
                                <textarea name="pg_aft_ad_desc" class="form-control" rows="5"><?php echo $resultOne['pg_aft_ad_desc'] ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-0">
                                <label>후팝업 이미지 업로드</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="pg_aft_ad_img_ajax">
                                        <label class="custom-file-label" for="pg_aft_ad_img_ajax" id="label_aft_pop_img">
                                            이미지 파일 선택 (PNG, JPG)
                                        </label>
                                    </div>
                                    <div class="input-group-append">
                                        <button class="btn btn-success" type="button" onclick="ajax_upload_aft_pop_img()">업로드</button>
                                    </div>
                                </div>
            
                                <?php if($aft_pop_file): ?>
                                <div id="pop_img_preview_box" class="mt-2">
                                    <?php 
                                    $img_url = $aft_pop_file['file_path'].'/'.$aft_pop_file['file_name'];
                                    ?>
                                    <div class="preview-item" style="position:relative; display:inline-block;">
                                        <img src="<?php echo $img_url ?>" style="max-width:100%; border:1px solid #ddd; border-radius:4px;" class="img-thumbnail">
                                        <button type="button" class="btn btn-danger btn-xs" style="position:absolute; top:5px; right:5px;" onclick="ajax_delete_aft_pop_img('<?php echo $aft_pop_file['file_idx'] ?>')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
            
                </div>
            </div>


            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">API설정</h3>
                    
                    <label class="ml-4 switch">
                        <input type="checkbox" id="pg_api_yn" name="pg_api_yn" class="switch-input" <?php echo $resultOne['pg_api_yn'] == "Y" ? "checked" : ""?>>
                        <span class="switch-label" data-on="ON" data-off="OFF"></span>
                        <span class="switch-handle"></span>
                    </label>

                    <div id="rdo_grp1" name="rdo_grp" class="ml-4 custom-control custom-radio custom-control-inline <?php echo $resultOne['pg_api_yn'] != "Y" ? "d-none" : ""?>">
                        <input type="radio" id="pg_api_kind1" name="pg_api_kind" value="normal" class="custom-control-input" <?php echo $resultOne['pg_api_kind'] == "normal" ? "checked" : ""?>>
                        <label class="custom-control-label" for="pg_api_kind1">일반API</label>
                    </div>
                    <div id="rdo_grp2" name="rdo_grp" class="custom-control custom-radio custom-control-inline <?php echo $resultOne['pg_api_yn'] != "Y" ? "d-none" : ""?>">
                        <input type="radio" id="pg_api_kind2" name="pg_api_kind" value="google" class="custom-control-input" <?php echo $resultOne['pg_api_kind'] == "google" ? "checked" : ""?>>
                        <label class="custom-control-label" for="pg_api_kind2">구글시트API</label>
                        <button type="button" id="copyEmailBtn" class="btn btn-xs btn-info btn-sm ml-2">구글주소복사</button>
                    </div>
                </div>
                
                <div id="divApiSet" class="card-body <?php echo $resultOne['pg_api_kind'] != "normal" ? "d-none" : ""?>">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>API주소</label>
                                <input type="text" id=pg_api_url name="pg_api_url" class="form-control" value="<?php echo $resultOne['pg_api_url'] ?>" placeholder="ex: https://apidomain.com">    
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>전송타입</label>
                                <select id="pg_api_param_way" name="pg_api_param_way" class="form-control">
                                    <option value="1" <?php echo get_selected($resultOne['pg_api_param_way'], '1'); ?>> 1. POST 기본</option>
                                    <option value="2" <?php echo get_selected($resultOne['pg_api_param_way'], '2'); ?>> 2. POST Array변환</option>
                                    <option value="3" <?php echo get_selected($resultOne['pg_api_param_way'], '3'); ?>> 3. POST JSON변환</option>
                                    <option value="4" <?php echo get_selected($resultOne['pg_api_param_way'], '4'); ?>> 4. GET 기본</option>
                                    <option value="5" <?php echo get_selected($resultOne['pg_api_param_way'], '5'); ?>> 5. POST(GET)</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>추가데이터</label>
                                <?php echo $w=="u" && $resultOne['pg_api_add_param'] != "" ? '<a href="javascript:void(0);" onclick="initAddParam(this);" class="ml-2 badge badge-warning">초기화</a>' : '' ?>
                                <a href="javascript:void(0);" onclick="copyClipboard(this);" class="ml-3 badge badge-primary">{name}</a>
                                <a href="javascript:void(0);" onclick="copyClipboard(this);" class="badge badge-primary">{tel}</a>
                                <a href="javascript:void(0);" onclick="copyClipboard(this);" class="badge badge-primary">{hp}</a>

                                <select class="selectpicker form-control" id="pg_api_add_param" name="pg_api_add_param[]" multiple>
                                    <option value="" disabled>(ex)tel={tel}</option>

                                    <?php 
                                    if($w == "u") { 
                                        $scr_arr = explode( '&', $resultOne['pg_api_add_param'] );
                                        for($j=0; $j<count($scr_arr); $j++){ 
                                            if($scr_arr[$j] == "") continue;
                                    ?>
                                        <option value="<?php echo htmlspecialchars($scr_arr[$j], ENT_QUOTES, 'UTF-8'); ?>" class="bg-warning" selected>
                                            <?php echo $scr_arr[$j]; ?>
                                        </option>
                                    <?php  
                                        } 
                                    }
                                    ?>

                                </select>
                            </div>
                        </div>


                    </div>

                    <div class="row">
                       

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>리턴TYPE</label>
                                <select id="pg_api_return_way" name="pg_api_return_way" class="form-control">
                                    <option value="1" <?php echo get_selected($resultOne['pg_api_return_way'], '1'); ?>> 1. 기본</option>
                                    <option value="2" <?php echo get_selected($resultOne['pg_api_return_way'], '2'); ?>> 2. Array</option>
                                    <option value="3" <?php echo get_selected($resultOne['pg_api_return_way'], '3'); ?>> 3. JSON</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label>리턴성공</label>
                                <input type="text" id="pg_api_success" name="pg_api_success" class="form-control" value="<?php echo $resultOne['pg_api_success'] ?>" placeholder="ex: ok">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>리턴실패</label>
                                <input type="text" id="pg_api_fail" name="pg_api_fail" class="form-control" value="<?php echo $resultOne['pg_api_fail'] ?>" placeholder="ex: fail">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>리턴중복</label>
                                <input type="text" id="pg_api_duplicate" name="pg_api_duplicate" class="form-control" value="<?php echo $resultOne['pg_api_duplicate'] ?>" placeholder="ex: dup">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>리턴스펙</label>
                                <input type="text" id="pg_api_return_param" name="pg_api_return_param" class="form-control" value="<?php echo $resultOne['pg_api_return_param'] ?>" placeholder="">
                            </div>
                        </div>
                        
                    </div>

                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>API KEY</label>
                                <input type="text" id="pg_api_key" name="pg_api_key" class="form-control" value="" placeholder="작업준비중" disabled>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>API VALUE</label>
                                <input type="text" id="pg_api_value" name="pg_api_value" class="form-control" value="" placeholder="작업준비중" disabled>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>리턴CODE</label>
                                <input type="text" id="pg_return_code" name="pg_return_code" class="form-control" value="" placeholder="작업준비중" disabled>
                            </div>
                        </div>
                    </div>

                </div>





                <div id="divApiSet2" class="card-body <?php echo $resultOne['pg_api_kind'] == "google" ? "" : "d-none"?>">
                  
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>sheet주소</label>
                                <input type="text" id="google_addr" name="google_addr" class="form-control" value="<?php echo $resultOne['google_addr'] ?>" placeholder="스프레드시트ID 입력">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>sheet명</label>
                                <input type="text" id="google_sheet" name="google_sheet" class="form-control" value="<?php echo $resultOne['google_sheet'] ?>" placeholder="시트명 입력">
                            </div>
                        </div>

                    </div>


                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>옵션</label>

                                <select id="google_data" name="google_data[]" class="selectpicker form-control" multiple data-selected-text-format="count > 3" multiple data-actions-box="true">
                                    <optgroup label="주요 옵션">
                                    <option value="$name" <?php echo get_selected($resultOne['google_data'], 'name'); ?>> name (이름)</option>
                                    <option value="$tel" <?php echo get_selected($resultOne['google_data'], 'tel'); ?>> tel(연락처)</option>
                                    </optgroup>
                                    <optgroup label="옵션">
                                    <option value="$option1" <?php echo get_selected($resultOne['google_data']    , 'option1'); ?>> option1(옵션1)</option>
                                    <option value="$option2" <?php echo get_selected($resultOne['google_data']    , 'option2'); ?>> option2(옵션2)</option>
                                    <option value="$option3" <?php echo get_selected($resultOne['google_data']    , 'option3'); ?>> option3(옵션3)</option>
                                    <option value="$option4" <?php echo get_selected($resultOne['google_data']    , 'option4'); ?>> option4(옵션4)</option>
                                    <option value="$option5" <?php echo get_selected($resultOne['google_data']    , 'option5'); ?>> option5(옵션5)</option>
                                    <option value="$option6" <?php echo get_selected($resultOne['google_data']    , 'option6'); ?>> option6(옵션6)</option>
                                    <option value="$option7" <?php echo get_selected($resultOne['google_data']    , 'option7'); ?>> option7(옵션7)</option>
                                    <option value="$option8" <?php echo get_selected($resultOne['google_data']    , 'option8'); ?>> option8(옵션8)</option>
                                    <option value="$option9" <?php echo get_selected($resultOne['google_data']    , 'option9'); ?>> option9(옵션9)</option>

                                    <option value="$pg_memo" <?php echo get_selected($resultOne['google_data']    , 'pg_memo'); ?>> page_name(페이지명)</option>
                                    <option value="$design_name" <?php echo get_selected($resultOne['google_data']  , 'design_name'); ?>> design_name(디자인명)</option>
                                    
                                    <option value="$pg_uri" <?php echo get_selected($resultOne['google_data']     , 'pg_uri'); ?>> pg_uri(코드)</option>
                                    <option value="$pg_inflow" <?php echo get_selected($resultOne['google_data']    , 'pg_inflow'); ?>> pg_inflow(유입채널)</option>
                                    <option value="$inflow_env" <?php echo get_selected($resultOne['google_data']   , 'inflow_env'); ?>> inflow_env(유입환경:P/M)</option>
                                    <option value="$insert_date" <?php echo get_selected($resultOne['google_data']  , 'insert_date'); ?>> insert_date(입력시간)</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>셀위치</label>
                                <input type="text" id="google_cell" name="google_cell" class="form-control" value="<?php echo $resultOne['google_cell'] ?>" placeholder="옵션선택시 자동입력 (필요시수정)">
                            </div>
                        </div>   
                    </div>
                </div>
            </div>


            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">SMS인증</h3>
                    
                    <label class="ml-4 switch">
                        <input type="checkbox" id="pg_sms_yn" name="pg_sms_yn" class="switch-input" <?php echo $resultOne['pg_sms_yn'] == "Y" ? "checked" : ""?>>
                        <span class="switch-label" data-on="ON" data-off="OFF"></span>
                        <span class="switch-handle"></span>
                    </label>
                </div>
            </div>


            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">TO 고객사 SMS 발송</h3>


                    <label class="ml-4 switch">
                        <input type="checkbox" id="pg_db_sms_yn" name="pg_db_sms_yn" class="switch-input" <?php echo $resultOne['pg_db_sms_yn'] == "Y" ? "checked" : ""?>>
                        <span class="switch-label" data-on="ON" data-off="OFF"></span>
                        <span class="switch-handle"></span>
                    </label>

                    <?php
                    $smsChecked = '';
                    $kakaoChecked = '';

                    $isChecked = "";
                    $isDisabled = "";

                    if($w != "") {
                        if($resultOne['pg_db_sms_yn'] == "Y") {
                            $isChecked = "checked";
                            $isDisabled = "";
                            $pg_db_sms_msg = $resultOne['pg_db_sms_msg'];
                        }
                    }

                    if($resultOne['pg_db_sms_yn'] == "Y") {

                        if ($resultOne['pg_db_sms_kind'] === '1') {
                          $smsChecked = 'checked';
                        } elseif ($resultOne['pg_db_sms_kind'] === '2') {
                            $kakaoChecked = 'checked';
                        } 

                    }
                    
                    ?>

                    <div id="rdo_sms" class="ml-4 custom-control custom-radio custom-control-inline <?php echo $resultOne['pg_db_sms_yn'] != "Y" ? "d-none" : ""?>">
                        <input type="radio" id="pg_db_sms_kind1" name="pg_db_sms_kind" value="1" class="custom-control-input" <?php echo $smsChecked ?>>
                        <label class="custom-control-label" for="pg_db_sms_kind1">문자</label>
                    </div>

                    <div id="rdo_kakao" class="custom-control custom-radio custom-control-inline <?php echo $resultOne['pg_db_sms_yn'] != "Y" ? "d-none" : ""?>">
                        <input type="radio" id="pg_db_sms_kind2" name="pg_db_sms_kind" value="2" class="custom-control-input" <?php echo $kakaoChecked ?>>
                        <label class="custom-control-label" for="pg_db_sms_kind2">카카오톡</label>
                    </div>
                </div>



                <div id="divSmsSet" class="card-body <?php echo $resultOne['pg_db_sms_kind'] != "1" ? "d-none" : ""?>">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>DB 알람 설정 <i class="fas fa-question rounded-circle fa-border bg-blue" data-toggle="tooltip" title="페이지(코드) 단위로 자유롭게 메시지가 설정됩니다."></i>&nbsp;</label>
                                [고객:<span class="badge badge-info ml-1 mr-1 cust-sms-badge" name="badge">{ptn_nm}</span>]
                                [성함:<span class="badge badge-info ml-1 mr-1 cust-sms-badge" name="badge">{name}</span>]
                                [연락처:<span class="badge badge-info ml-1 mr-1 cust-sms-badge" name="badge">{tel}</span>]
                                [옵션:<span class="badge badge-info ml-1 mr-1 cust-sms-badge" name="badge">{option}</span>]
                                [코드:<span class="badge badge-info ml-1 cust-sms-badge" name="badge">{pageCode}</span>]
                                <input type="text" id="pg_db_sms_msg" name="pg_db_sms_msg" class="form-control" value="<?php echo $pg_db_sms_msg?>" placeholder="미설정시 기본값 -> {지오앤플랜} 고객님 {010-2222-3333} 연락처로 문의가 접수되었습니다..">

                            </div>
                        </div>                  
                    </div>
                </div>

                
                <?php
                $kakaoShotChecked = '';
                $kakaoLongChecked = '';

                // pg_db_sms_msg 값에 따라 분기
                if ($resultOne['pg_db_sms_msg'] === 'kakao_long') {
                    $kakaoLongChecked = 'checked';
                } elseif ($resultOne['pg_db_sms_msg'] === 'kakao_shot') {
                    $kakaoShotChecked = 'checked';
                } else {
                    $kakaoShotChecked = 'checked';
                }
                ?>


                <!-- 카카오톡 설정 Div -->
                <div id="divKakaoSet" class="card-body <?php echo $resultOne['pg_db_sms_kind'] != "2" ? "d-none" : ""?>">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>템플릿 선택</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="kakaoTemplate" id="kakao_shot" value="kakao_shot" <?php echo $kakaoShotChecked; ?>>
                                    <label class="form-check-label" for="kakao_shot">단문 템플릿</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="kakaoTemplate" id="kakao_long" value="kakao_long" <?php echo $kakaoLongChecked; ?>>
                                    <label class="form-check-label" for="kakao_long">장문 템플릿+(옵션)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                
                <div class="modal fade" id="templateModal" tabindex="-1" role="dialog" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered" role="document"> 
                    <div class="modal-content">
                      <div class="modal-header bg-warning">
                        <h5 class="modal-title">카카오톡 템플릿 미리보기</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body text-center">
                        <!-- 이미지를 보여줄 태그 -->
                        <img id="templateModalImg" src="" alt="카카오 템플릿 이미지" style="max-width: 100%;">
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                      </div>
                    </div>
                  </div>
                </div>

                
            </div>




            <!-- <div class="card">
                <div class="card-header">
                    <h3 class="card-title">TO 사용자 SMS발송</h3>

                    <label class="ml-4 switch">
                        <?php 

                        $isChecked = "";
                        $isDisabled = "";

                        if($w != "") {
                            if($resultOne['pg_db_user_sms_yn'] == "Y") {
                                $isChecked = "checked";
                                $isDisabled = "";
                                $pg_db_user_sms_msg = $resultOne['pg_db_user_sms_msg'];
                            }
                        }
                        ?>
                        <input type="checkbox" id="pg_db_user_sms_yn" name="pg_db_user_sms_yn" class="switch-input" <?php echo $isChecked. " " .$isDisabled?>>
                        <span class="switch-label" data-on="ON" data-off="OFF"></span>
                        <span class="switch-handle"></span>
                    </label>
                    <small class="text-danger">※참고※ SMS->70글자 || LSM ->1000글자</small>
                </div>



                <div id="divSmsSet2" class="card-body <?php echo $resultOne['pg_db_user_sms_yn'] != "Y" ? "d-none" : ""?>">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>DB 알람 설정 <i class="fas fa-question rounded-circle fa-border bg-blue" data-toggle="tooltip" title="페이지(코드) 단위로 자유롭게 메시지가 설정됩니다."></i>&nbsp;</label>
                                [고객명:<span class="badge badge-info ml-1 mr-1 user-sms-badge" name="badge">{ptn_nm}</span>]
                                [고객연락처:<span id="sms_alert_msg" class="badge badge-info ml-1 mr-1 user-sms-badge" name="badge">{ptn_phone}</span>]
                                [연락처2:<span class="badge badge-info ml-1 mr-1 user-sms-badge" name="badge">{tel2}</span>]
                                [연락처3:<span class="badge badge-info ml-1 mr-1 user-sms-badge" name="badge">{tel3}</span>]
                                [성함:<span class="badge badge-info ml-1 mr-1 user-sms-badge" name="badge">{name}님</span>]
                                [연락처:<span class="badge badge-info ml-1 mr-1 user-sms-badge" name="badge">{tel}</span>]
                                [옵션:<span class="badge badge-info ml-1 mr-1 user-sms-badge" name="badge">{option}</span>]
                                <input type="text" id="pg_db_user_sms_msg" name="pg_db_user_sms_msg" class="form-control" value="<?php echo $pg_db_user_sms_msg?>" placeholder="선택시 필수입력 해주세요.">
                            </div>
                        </div>                  
                    </div>
                </div>
            </div> -->


            
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">페이지설정</h3>
                </div>
                <div class="card-body">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>타이틀*</code></label>
                                <input type="text" id=pg_title name="pg_title" class="form-control" value="<?php echo $resultOne['pg_title'] ?>" placeholder="타이틀" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>meta-디스크립션*</code></label>
                                <input type="text" id=pg_meta_desc name="pg_meta_desc" class="form-control" value="<?php echo $resultOne['pg_meta_desc'] ?>" placeholder="meta-디스크립션" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><code>meta-키워드*</code></label>
                                <input type="text" id=pg_meta_keyword name="pg_meta_keyword" class="form-control" value="<?php echo $resultOne['pg_meta_keyword'] ?>" placeholder="meta-키워드" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>리다이렉트주소</label>
                                <input type="text" id=pg_redirect_url name="pg_redirect_url" class="form-control" value="<?php echo $resultOne['pg_redirect_url'] ?>" placeholder="DB완료후 리다이렉트할 주소">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>팝업멘트</label>
                                <input type="text" id=pg_alert_msg name="pg_alert_msg" class="form-control" value="<?php echo $resultOne['pg_alert_msg'] ?>" placeholder="DB완료후 팝업멘트 지정">
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <label>팝업이미지주소</label>
                                <input type="text" id=pg_popup_img name="pg_popup_img" class="form-control" value="<?php echo $pg_popup_img ?>" placeholder="팝업이미지 주소 지정">
                            </div>
                        </div>

                        <!-- <div class="col-md-2">
                            <div class="form-group">
                                <label>팝업 가로세로 size</label>
                                <input type="text" id=pg_popup_img_size name="pg_popup_img_size" class="form-control" value="<?php echo $pg_popup_img_size ?>" placeholder="ex:200X300 지정">
                            </div>
                        </div> -->

                    </div>
                    <hr/>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>업체명</label>
                                <input type="text" id=pg_compnm name="pg_compnm" class="form-control" value="<?php echo $resultOne['pg_compnm'] ?>" placeholder="업체명">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>대표자</label>
                                <input type="text" id=pg_reprnm name="pg_reprnm" class="form-control" value="<?php echo $resultOne['pg_reprnm'] ?>" placeholder="대표자">
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>사업자번호</label>
                                <input type="text" id=pg_bznum name="pg_bznum" class="form-control" value="<?php echo $resultOne['pg_bznum'] ?>" placeholder="사업자번호">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>주소</label>
                                <input type="text" id=pg_addr name="pg_addr" class="form-control" value="<?php echo $resultOne['pg_addr'] ?>" placeholder="주소">
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>연락처</label>
                                <input type="text" id=pg_tel name="pg_tel" class="form-control" value="<?php echo $resultOne['pg_tel'] ?>" placeholder="연락처">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>이메일</label>
                                <input type="text" id=pg_email name="pg_email" class="form-control" value="<?php echo $resultOne['pg_email'] ?>" placeholder="이메일">
                            </div>
                        </div>
                    </div>


                    <hr/>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>랜딩페이지 추가 스크립트</label>
                                <textarea name="pg_bef_head" id="pg_bef_head" class="form-control" rows="4"><?php echo $resultOne['pg_bef_head']; ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>전환페이지 추가 스크립트</label>
                                <textarea name="pg_aft_head" id="pg_aft_head" class="form-control" rows="4"><?php echo $resultOne['pg_aft_head']; ?></textarea>
                            </div>
                        </div>
                    </div>

                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>추가스크립트</label>
                                <select id="pg_add_script" name="pg_add_script[]" class="form-control" multiple>
                                    <?php for ($i = 0; $script = sql_fetch_array($add_script); $i++) { 
                                        if($w == "u") { 
                                            $scr_arr = explode( ',', $resultOne['pg_add_script'] );
                                            for($j=0; $j<count($scr_arr); $j++){ 
                                                if($script['script_idx'] == $scr_arr[$j]) {
                                                    $selVal = $scr_arr[$j];
                                                    break;
                                                }
                                            ?>
                                            <?php } ?>
                                            <option value="<?php echo $script['script_idx'] ?>" <?php echo get_selected($selVal, $script['script_idx']); ?>><?php echo $script['script_name'] ?></option>
                                        <?php } else { ?>
                                            <option value="<?php echo $script['script_idx'] ?>"><?php echo $script['script_name'] ?></option>
                                        <?php }  ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="mr-2">취합다운<code>||</code>사용자Open Data</label>
                                <div class="form-check form-check-inline">
                                    <?php if($w == "u") { ?>
                                        <input class="form-check-input" type="checkbox" id="pg_chk_name" name="pg_chk_name" value="1" <?php echo $resultOne['pg_chk_name'] == "1" ? 'checked' : ''?>>
                                    <?php } else { ?>
                                        <input class="form-check-input" type="checkbox" id="pg_chk_name" name="pg_chk_name" value="1" checked>
                                    <?php }  ?>
                                    <label class="form-check-label" for="chk_red">이름</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="id_pg_chk_data1" name="pg_chk_data1" value="1" <?php echo $resultOne['pg_chk_data1'] != "" ? 'checked' : ''?>>
                                    <label class="form-check-label" for="chk_green">옵션1</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="id_pg_chk_data2" name="pg_chk_data2" value="1" <?php echo $resultOne['pg_chk_data2'] != "" ? 'checked' : ''?>>
                                    <label class="form-check-label" for="chk_blue">옵션2</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="id_pg_chk_data3" name="pg_chk_data3" value="1" <?php echo $resultOne['pg_chk_data3'] != "" ? 'checked' : ''?>>
                                    <label class="form-check-label" for="chk_blue">옵션3</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="id_pg_chk_data4" name="pg_chk_data4" value="1" <?php echo $resultOne['pg_chk_data4'] != "" ? 'checked' : ''?>>
                                    <label class="form-check-label" for="chk_blue">옵션4</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="id_pg_chk_data5" name="pg_chk_data5" value="1" <?php echo $resultOne['pg_chk_data5'] != "" ? 'checked' : ''?>>
                                    <label class="form-check-label" for="chk_blue">옵션5</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="id_pg_chk_data6" name="pg_chk_data6" value="1" <?php echo $resultOne['pg_chk_data6'] != "" ? 'checked' : ''?>>
                                    <label class="form-check-label" for="chk_blue">옵션6</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="id_pg_chk_data7" name="pg_chk_data7" value="1" <?php echo $resultOne['pg_chk_data7'] != "" ? 'checked' : ''?>>
                                    <label class="form-check-label" for="chk_blue">옵션7</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="id_pg_chk_data8" name="pg_chk_data8" value="1" <?php echo $resultOne['pg_chk_data8'] != "" ? 'checked' : ''?>>
                                    <label class="form-check-label" for="chk_blue">옵션8</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="id_pg_chk_data9" name="pg_chk_data9" value="1" <?php echo $resultOne['pg_chk_data9'] != "" ? 'checked' : ''?>>
                                    <label class="form-check-label" for="chk_blue">옵션9</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input type="hidden" name="pg_chk_code" value="0">
                                    <input class="form-check-input" type="checkbox" id="pg_chk_code" name="pg_chk_code" value="1" <?php echo $resultOne['pg_chk_code'] == "1" ? 'checked' : ''?>>
                                    <label class="form-check-label" for="pg_chk_code">코드</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input type="hidden" name="pg_chk_utm" value="0">
                                    <input class="form-check-input" type="checkbox" id="pg_chk_utm" name="pg_chk_utm" value="1" <?php echo $resultOne['pg_chk_utm'] == "1" ? 'checked' : ''?>>
                                    <label class="form-check-label" for="pg_chk_utm">UTM</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input type="hidden" name="pg_chk_ip" value="0">
                                    <input class="form-check-input" type="checkbox" id="pg_chk_ip" name="pg_chk_ip" value="1" <?php echo $resultOne['pg_chk_ip'] == "1" ? 'checked' : ''?>>
                                    <label class="form-check-label" for="pg_chk_ip">IP</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <button type="button" id="btn_conf" class="btn btn-primary btn-xs border border-dark" onclick="adapt_ptn_page('<?php echo $pg_ptn_idx ?>')">
                                        <i class="fas fa-wrench"></i> 일괄적용
                                    </button>
                                </div>


                            </div>

                            <div class="row" id="hiddenRow" style="<?php echo $displayStyle; ?>">
                                <?php echo $dynamicHTML; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">미리보기</h3>
                </div>
                <div class="card-body d-none" id="htmlPreviewDiv">
                    <div class="col-md-12">
                        <iframe id="div_preview" name="div_preview" src="" frameborder="0" width="100%" height="1000px"></iframe>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="col-12">
                <?php if($w == "u") { ?>
                    <a href="#" class="btn btn-default">최초등록 <span class="badge badge-primary"><?php echo $resultOne['insert_user_name'] ?></span><span class="badge badge-primary"><?php echo substr($resultOne['insert_date'],0,16) ?></span></a>
                    <a href="#" class="btn btn-default">최종수정 <span class="badge badge-warning"><?php echo $resultOne['update_user_name'] ?></span><span class="badge badge-warning"><?php echo substr($resultOne['update_date'],0,16) ?></span></a>
                <?php } ?>
                    <?php echo isSaveBtn($w, $resultOne['pg_deptno'], $resultOne['pg_mb_emp'], $member, 'btn_normal', 'btn btn-primary float-right') ?>
                    <button type="button" class="btn btn-default float-right" id="btn_list" onclick="location.href='<?php echo G5_BIZ_URL; ?>/page/page_list?<?php echo $qstr;?>'">목록</button>
                </div>
            </div>


            
        </form>
    </div>
</section>


<script>
var isSharedYn = "<?php echo $isSharedYn ?>";
var org_ptn_idx = "<?php echo $pg_ptn_idx ?>";
var org_dept_no = "<?php echo $sel_dept ?>";
$(document).ready(function() {

    // var offset = 50; // 이미 로드된 아이템의 수
    // var act = "aft_design_all";
    // $.ajax({
    //     url: "<?php echo G5_BIZ_URL?>/common/code_ajax",
    //     type: "post",
    //     dataType: "json",
    //     data: {
    //             act: act,
    //             page: offset
    //           },
    //     success: function(data) {
    //         var newOptionsHtml = data; // 서버로부터 받은 HTML 옵션 문자열
    //         $('#pg_des_idx').append(newOptionsHtml).selectpicker('refresh');
    //     },
    //     error: function() {
    //         isLoading = false;
    //     }
    // });


    $('#pg_db_sms_msg, #pg_db_user_sms_msg').on('input', function() {
        var smsMessage = $(this).val();
        var elementId = $(this).attr('id');

        var ptn_nm = $('#pg_ptn_idx option:selected').text();
        var ptn_nm_len = ptn_nm.length;

        var define_len = {
            '{ptn_nm}': ptn_nm_len,
            '{name}': 3,
            '{tel}': 13,
            '{ptn_phone}': 13,
            '{tel2}': 3,
            '{tel3}': 3,
            '{option1}': 5,
            '{option2}': 5,
            '{option3}': 5,
            '{option4}': 5,
            '{option5}': 5,
            '{option6}': 5
        };

        var calculatedLength = smsMessage.replace(/({ptn_nm}|{name}|{tel}|{ptn_phone}|{tel2}|{tel3}|{option1}|{option2}|{option3}|{option4}|{option5}|{option6})/g, function(match) {
            return ' '.repeat(define_len[match]);
        }).length;

        if (calculatedLength > 70) {
            var alertMessage = '';

            if(elementId == "pg_db_sms_msg"){
                alertMessage = 'TO 고객사 SMS발송 70자 초과 했습니다. 텍스트 길이를 줄여주세요.\n\n' +
                    '70자초과!:\n' +
                    smsMessage.replace('{ptn_nm}', ptn_nm)
                                .replace('{name}', '홍길동')
                                .replace('{tel}', '010-1111-2222')
                                .replace('{ptn_phone}', '010-1111-2222')
                                .replace('{tel2}', 'XXX')
                                .replace('{tel3}', 'XXX')
                                .replace('{option1}', 'XXXXX')
                                .replace('{option2}', 'XXXXX')
                                .replace('{option3}', 'XXXXX')
                                .replace('{option4}', 'XXXXX')
                                .replace('{option5}', 'XXXXX')
                                .replace('{option6}', 'XXXXX');
            } else if(elementId == "pg_db_user_sms_msg"){
                alertMessage = 'DB 알람 설정 SMS발송 70자 초과 했습니다. 텍스트 길이를 줄여주세요.\n\n' +
                    '70자초과:\n' +
                    smsMessage.replace('{ptn_nm}', ptn_nm)
                                .replace('{name}', '홍길동')
                                .replace('{tel}', '010-1111-2222')
                                .replace('{ptn_phone}', '010-1111-2222')
                                .replace('{tel2}', 'XXX')
                                .replace('{tel3}', 'XXX')
                                .replace('{option1}', 'XXXXX')
                                .replace('{option2}', 'XXXXX')
                                .replace('{option3}', 'XXXXX')
                                .replace('{option4}', 'XXXXX')
                                .replace('{option5}', 'XXXXX')
                                .replace('{option6}', 'XXXXX');
            }
            alert(alertMessage);
        }
    });

    

    $('#copyEmailBtn').on('click', function() {
        var email = 'withus@withus-463215.iam.gserviceaccount.com';
        var tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(email).select();
        document.execCommand('copy');
        tempInput.remove();
        alert(email + ' (클립보드에 복사됨) 해당 주소를 구글 스프레드 시트 우측 상단 공유를 클릭해 공유해주세요');
    });



    $('[id^=id_pg_chk_data]').change(function() {
        var index = this.id.replace('id_pg_chk_data', '');
        handleCheckboxChange(index);
    });

    <?php if($w == "") { ?>

        var hiddenRow = $('#hiddenRow');
        for (var i = 1; i <= 6; i++) {
            // 체크박스 기본 선택
            $('#id_pg_chk_data' + i).prop('checked', true);

            // 필요한 HTML 요소 직접 생성
            var containerId = 'containerForData' + i;
            var inputId = 'pg_chk_data' + i;
            var labelId = 'labelForData' + i;

            var containerDiv = $('<div>', { id: containerId }).appendTo(hiddenRow);
            $('<label>', { id: labelId, for: inputId, text: '옵션' + i + ' :' }).appendTo(containerDiv);
            $('<input>', {
                type: 'text',
                id: inputId,
                name: inputId,
                class: 'form-control',
                value: '옵션' + i,
                maxlength: 5 
            }).appendTo(containerDiv);
        }
        hiddenRow.show();
    <?php } ?>
    

    function handleCheckboxChange(index) {
        var hiddenRow = $('#hiddenRow');
        var checkbox = $(`#id_pg_chk_data${index}`);
        var inputId = `pg_chk_data${index}`;
        var labelId = `labelForData${index}`;
        var containerId = `containerForData${index}`;

        // 체크박스가 선택되면 입력 필드와 레이블 생성, 아니면 제거
        if (checkbox.is(':checked')) {
            if (!$(`#${inputId}`).length) {
                // 레이블과 입력 필드를 포함할 새로운 div 생성
                var containerDiv = $('<div>', {
                    id: containerId
                }).appendTo(hiddenRow);

                // 레이블 생성
                $('<label>', {
                    id: labelId,
                    for: inputId,
                    text: `옵션${index} :`
                }).appendTo(containerDiv);

                // 입력 필드 생성
                $('<input>', {
                    type: 'text',
                    id: inputId,
                    name: `pg_chk_data${index}`,
                    class: `form-control`,
                    placeholder: `옵션${index} 값 입력`
                }).appendTo(containerDiv);

                hiddenRow.show(); // 숨겨진 row를 보이게 함
            }
        } else {
            $(`#${containerId}`).remove();

            // 모든 체크박스가 해제되면 row를 다시 숨김
            if (!hiddenRow.children().length) {
                hiddenRow.hide();
            }
        }
    }
    

    const cellValue = $('#google_cell').val();
    const mappings = cellValue.split('||');
    const selectedValues = mappings.map(mapping => mapping.split(':')[1]);

    selectedValues.forEach(value => {
        $(`#google_data option[value="${value}"]`).prop('selected', true);
    });
    
    // selectpicker를 업데이트합니다.
    $('#google_data').selectpicker('refresh');


    $('[data-toggle="tooltip"]').tooltip(); 

    var content = "<input type='text' class='bss-input' onKeyDown='event.stopPropagation();' onKeyPress='addSelectInpKeyPress(this,event)' onClick='event.stopPropagation()' placeholder='Add item'>"+
                  "<i class='fas fa-plus' onClick='addSelectItem(this,event,1);'></i>";
    var divider = $('<option/>').addClass('dropdown-divider').data('divider', true);
    var addoption = $('<option/>', {class: 'addItem'}).data('content', content)
    $('#pg_api_add_param').prepend(addoption).selectpicker();

    $('#pg_des_idx').selectpicker();
    $('#pg_deptno').selectpicker();
    $('#pg_ptn_idx').selectpicker();
    $('#pg_add_script').selectpicker();



    // 후팝업 스위치 변경 이벤트
    $('input[type=checkbox][name=pg_aft_ad_yn]').change(function() {
        if($(this).is(':checked')) {
            $("#div_aft_pop_area").removeClass("d-none");
        } else {
            $("#div_aft_pop_area").addClass("d-none");
        }
    });
    // 파일명 라벨 변경
    $(document).on('change', '#pg_aft_ad_img_ajax', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
    });

    $('input[type=checkbox][name=pg_api_yn]').change(function() {

        //divApiSet
        //rdo_grp
        var checked = $(this).is(':checked');
        if(checked == true) {

            $("#rdo_grp1").removeClass("d-none");
            $("#rdo_grp2").removeClass("d-none");
            $("#divApiSet").removeClass("d-none");
            $("#divApiSet2").removeClass("d-none");

            $("#rdo_grp1").attr("disabled",true);
            $("#rdo_grp2").attr("disabled",true);
            $("#divApiSet").attr("disabled",true);
            $("#divApiSet2").attr("disabled",true);

            $('#pg_api_kind1').prop('checked', true);

        }else{
            $("input[name=rdo_grp]").addClass("d-none");

            $("#rdo_grp1").addClass("d-none");
            $("#rdo_grp2").addClass("d-none");
            $("#divApiSet").addClass("d-none");
            $("#divApiSet2").addClass("d-none");

            $("#rdo_grp1").attr("disabled",false);
            $("#rdo_grp2").attr("disabled",false);
            $("#divApiSet").attr("disabled",false);
            $("#divApiSet2").attr("disabled",false);
        }
    });

    $('input[type=radio][name=pg_api_kind]').change(function() {
        var selectedValue = $('input[type=radio][name=pg_api_kind]:checked').val();
        
        if(selectedValue == "normal") {
            $("#divApiSet").removeClass("d-none");
            $("#divApiSet2").addClass("d-none");


            $("#divApiSet").attr("disabled",false);
            $("#divApiSet2").attr("disabled",true);
            
        } else {
            $("#divApiSet").addClass("d-none");
            $("#divApiSet2").removeClass("d-none");

            $("#divApiSet").attr("disabled",true);
            $("#divApiSet2").attr("disabled",false);
        }
    });


    
    // 선택된 옵션의 순서를 추적하는 배열
    let selectedOrder = [];

    $('#google_data').change(function() {
        let currentSelected = $(this).val() || [];

        // 새로 추가된 옵션 확인 및 배열에 추가
        currentSelected.forEach(item => {
            if (!selectedOrder.includes(item)) {
                selectedOrder.push(item);
            }
        });

        // 제거된 옵션 확인 및 배열에서 제거
        selectedOrder = selectedOrder.filter(item => currentSelected.includes(item));

        updateGoogleCell(selectedOrder);
    });



    $('#google_cell').change(function() {
        //resetGoogleData();
    });


   



    $('#pg_db_sms_yn').change(function() {
        if ($(this).is(':checked')) {
            // 체크박스 켜졌으면, 라디오 버튼(문자/카카오) 보이기
            $('#rdo_sms, #rdo_kakao').removeClass('d-none');
        } else {
            $('#rdo_sms, #rdo_kakao').addClass('d-none');
            $('#divSmsSet').addClass('d-none');
            $('#divKakaoSet').addClass('d-none');
            
            // 만약 체크박스 끌 때 라디오 선택도 초기화하려면(선택사항)
            $('input[name="pg_db_sms_kind"]').prop('checked', false);
        }
    });

    // ------------------------------
    // (B) 라디오 버튼(pg_db_sms_kind) 변화
    // ------------------------------
    $('input[name="pg_db_sms_kind"]').change(function() {
        // 선택된 라디오 값 (문자=1, 카카오=2)
        const val = $(this).val();

        if (val === '1') {
            // 문자 선택 시 → 문자 설정 div 보임, 카카오톡 div 숨김
            $('#divSmsSet').removeClass('d-none');
            $('#divKakaoSet').addClass('d-none');
        } else if (val === '2') {
            // 카카오톡 선택 시 → 카카오톡 div 보임, 문자 div 숨김
            $('#divKakaoSet').removeClass('d-none');
            $('#divSmsSet').addClass('d-none');
        }
    });


    let shownSimple = false;
    let shownLong = false;

    // 카카오톡 라디오 버튼이 변경될 때
    $('input[name="kakaoTemplate"]').change(function() {
      const selectedVal = $(this).val(); // 'template1' or 'template2'

      // kakao_shot: 단문 템플릿
      if (selectedVal === 'kakao_shot') {
        // 아직 안 보여줬다면 -> 모달 띄움
        if (!shownSimple) {
          $('#templateModalImg').attr('src', 'kakao_simple.png');
          // 모달 띄우기 (부트스트랩)
          $('#templateModal').modal('show');
          shownSimple = true;
        }
      }
      // kakao_long: 장문 템플릿+(옵션)
      else if (selectedVal === 'kakao_long') {
        if (!shownLong) {
          $('#templateModalImg').attr('src', 'kakao_long.png');
          $('#templateModal').modal('show');
          shownLong = true;
        }
      }
    });

    



    // #cust-sms-badge 클릭시 복사처리
    $('span.cust-sms-badge[name="badge"]').click(function() {
        if(!$('#pg_db_sms_yn').prop('checked')) {
            return false;
        }
        var spanText = $(this).text();
        var asis_msg = $('#pg_db_sms_msg').val();
        $('#pg_db_sms_msg').val(asis_msg + spanText);
        $('#pg_db_sms_msg').focus();
    });






    // $('input[type=checkbox][name=pg_db_user_sms_yn]').change(function() {       

    //     var checked = $(this).is(':checked');
    //     if(checked == true) {

    //         $("#divSmsSet2").attr("disabled",true);
    //         $("#divSmsSet2").removeClass("d-none");
    //     } else {
    //         $("#divSmsSet2").attr("disabled",false);
    //         $("#divSmsSet2").addClass("d-none");
    //     }
    // });

    // #user-sms-badge 클릭시 복사처리
    // $('span.user-sms-badge[name="badge"]').click(function() {

    //     if ($(this).text().trim() === '{ptn_phone}' || $(this).text().trim() === '') {
    //         alert('고객사에 등록된 번호로 (대표)010-1111-2222 (직원)010-3333-4444 와 같이 문자가 보내집니다. 고객사 연락처는 페이지 별로 직접 입력 권장합니다.');
    //     }

    //     if(!$('#pg_db_user_sms_yn').prop('checked')) {
    //         return false;
    //     }
    //     var spanText = $(this).text();
    //     var asis_msg = $('#pg_db_user_sms_msg').val();
    //     $('#pg_db_user_sms_msg').val(asis_msg + spanText);
    //     $('#pg_db_user_sms_msg').focus();
    // });




    
     
    //디자인 변경시
    $("#pg_des_idx").change(function() {
        var design_idx = $(this).val();
        var act = "desByCate";

        var $target = $("#desByCate");
        $target.val("");

        $.ajax({
            type: "post",
            data: {
                design_idx: design_idx ,
                act: act
            },
            url: "page_ajax",
            dataType: "json",
            success:function(result) {
                
                $("#linkByDes").attr("href", result.linkByDes);
                $target.val(result.des_cate_code_nm);

                var targetElement = $("#designLabel");
                $(".data-badge").remove();

                preview(result.des_html, '<?php echo G5_LAND_URL?>', 'div_preview');

                $("#htmlPreviewDiv").removeClass("d-none");

                var lastBadge;
                if (result.name == true) {
                    var userBadge = $('<span class="badge badge-secondary ml-1 data-badge">name</span>');
                    targetElement.after(userBadge);
                }

                for (var key in result) {
                    if (result[key] === true && key.startsWith("isUserData")) {
                        var dataNum = key.replace("isUserData", "");
                        var badge = $('<span class="badge badge-secondary ml-1 data-badge">data' + dataNum + '</span>');
                        targetElement.after(badge);
                    }
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                return;
            }
        });
    });


    //부서 변경시
    $("#pg_deptno").change(function() {

        if(isSharedYn == "Y") {
            alert('해당페이지는 마스터고객사에서 분배되고있는 페이지입니다. 해당 고객사 페이지에서 선처리 후 수정가능합니다.');
            $(this).val(org_dept_no).selectpicker('refresh');
            return false;
        }

        var deptno = $(this).val();
        var act = "deptByEmpAndPtn";

        var $target1 = $("#pg_mb_emp");
        $target1.empty();

        var $target2 = $("#pg_ptn_idx");

        $target2.children().remove().end();
        $target2.selectpicker('refresh').empty();
        $target2.selectpicker();
        
        var $target3 = $("#pg_mb_ptn");
        $target3.empty();
        

        $target1.append('<option value="">미지정</option>');

        $.ajax({
            type: "post",
            data: {
                deptno: deptno ,
                act: act
            },
            url: "<?php echo G5_BIZ_URL?>/common/code_ajax",
            dataType: "json", //전송받는 데이터형태 json
            success:function(result) {
                $target1.append(result[0]);
                //$target2.append(result[1]);
                $target2.selectpicker();
                
                var sel = +result[1].substr(15,1);
                $target2.append(result[1]);
                $target2.val(result[1].substr(15,1));
                $target2.selectpicker("refresh");

                var sVal = $('#pg_ptn_idx option:first').val();
                $target2.val(sVal);
                $target2.selectpicker("refresh");

                $target3.append(result[2]);
                
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                return;
            }
        });
    });


    //고객사 변경시
    $("#pg_ptn_idx").change(function() {

        if(isSharedYn == "Y") {
            alert('해당페이지는 마스터고객사에서 분배되고있는 페이지입니다. 해당 고객사 페이지에서 선처리 후 수정가능합니다.');
            $(this).val(org_ptn_idx).selectpicker('refresh');
            return false;
        }

        var ptn_idx = $(this).val();
        var act = "onChgPtn";

        var $target = $("#pg_mb_ptn");
        $target.empty();

        $target.append('<option value="">미지정</option>');

        $.ajax({
            type: "post",
            data: {
                ptn_idx: ptn_idx ,
                act: act
            },
            url: "page_ajax",
            dataType: "json", //전송받는 데이터형태 json
            success:function(result) {
                $target.append(result[0]);

                // $("#pg_compnm").val(result[1].ptn_bznm);
                // $("#pg_reprnm").val(result[1].ptn_reprnm);
                // $("#pg_bznum").val(result[1].ptn_bznum);
                // $("#pg_addr").val(result[1].ptn_addr);
                // $("#pg_tel").val(result[1].ptn_tel);
                // $("#pg_email").val(result[1].ptn_email);

                if(result[1].pg_db_sms_yn == "Y") {
                    // DB SMS 발송 체크박스 활성화 + SMS 내용 셋업
                    
                } 
                
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                return;
            }
        });
    });

    //uri중복방지
    $("#pg_uri").change(function() {

        if(isSharedYn == "Y") {
            var org_pg_uri = "<?php echo $pg_uri ?>";
            alert('해당페이지는 마스터고객사에서 분배되고있는 페이지입니다. 해당 고객사 페이지에서 선처리 후 수정가능합니다.');
            $("#pg_uri").val(org_pg_uri);
            return false;
        }

        var org_code = '<?php echo $resultOne['pg_uri'] ?>';
        var pg_uri = $(this).val();
        if(org_code == pg_uri) {
            $("#pg_uri").val(org_code);
            return;
        }

        var act = "dup_uri";

        $.ajax({
            type: "post",
            data: {
                pg_uri: pg_uri ,
                act: act
            },
            url: "page_ajax",
            dataType: "json",
            success:function(result) {
                if (result > 0) {
                    alert("["+pg_uri + "] URI 중복");
                    $("#pg_uri").val("");
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                return;
            }
        });
    });

    $(".inpt").change(function(){
        $(this).removeClass("is-invalid");
    });

    $('.selc').focusout(function() {
        $(this).removeClass("is-invalid");
    });

    // var isLoading = false;
    // var nextPage = 2;
    // $('#pg_des_idx').on('shown.bs.select', function () {
    //     $('.bootstrap-select .dropdown-menu .inner').on('scroll', function() {
    //         var div = $(this);
    //         // (div[0].scrollHeight - div.scrollTop() === div.height()) {
    //         if (div[0].scrollHeight - div.scrollTop() <= div.height() + 5) {    
    //             if (!isLoading) {
    //                 isLoading = true;

    //                 var act = "scroll_prc_design";
    //                 $.ajax({
    //                     url: "<?php echo G5_BIZ_URL?>/common/code_ajax",
    //                     type: "post",
    //                     dataType: "json",
    //                     data: {
    //                             act: act,
    //                             page: nextPage
    //                           },
    //                     success: function(data) {
    //                         var newOptionsHtml = data; // 서버로부터 받은 HTML 옵션 문자열
    //                         $('#pg_des_idx').append(newOptionsHtml).selectpicker('refresh');
    //                         nextPage++;
    //                         isLoading = false;
    //                     },
    //                     error: function() {
    //                         isLoading = false;
    //                     }
    //                 });
    //             }
    //         }
    //     });
    // });

    // $('#customSearchInput').on('input', function() {
    //     var searchTerm = $(this).val();
    //     isSearchMode = searchTerm.length > 0; // 검색어가 있는 경우 검색 모드 활성화

    //     if (isSearchMode) {
    //     } else {
    //     }
    // });

});

function preview(value, url, targetStr) {
        if(value == "") {
            return false;
        }
        var formObj = $('<form action="'+url+'" method="post" id="'+targetStr+'Form" target="'+targetStr+'"></form>');

        var inputObj = $('<textarea name="preview"></textarea>');
        inputObj.val(value);

        formObj.append(inputObj);

        $('body').prepend(formObj);

        formObj.submit();
        formObj.remove();
    }

function validateForm() {

    var smsYnCheckbox = document.getElementById("pg_db_sms_yn");
    if (smsYnCheckbox.checked) {
        // 라디오 버튼 중 체크된 것 찾기
        var selectedKind = document.querySelector('input[name="pg_db_sms_kind"]:checked');
        if (!selectedKind) {
            alert("문자나 카카오톡 중 하나를 선택하세요.");
            return false;
        }
    }


    var pg_ptn_idx = pageForm.pg_ptn_idx.value;

    if(pg_ptn_idx == "") {
        var result = confirm("[주의] 고객사 미지정시 테스트 페이지로 활용됩니다.\n등록 하시겠습니까?");
        if(result){
            return true;
        } else {
            return false;
        }
    }

    //(todo) api 필수값일때 validation check 

    document.getElementById("btn_small").disabled = "disabled";
    document.getElementById("btn_normal").disabled = "disabled";

    return true;
}

function initAddParam() {

    var result = confirm("초기화 하시겠습니까?(삭제후 재입력)");

    if(result){
        var page_idx = '<?php echo $page_idx ?>';
        var act = "initAddParam"

         $.ajax({
            type: "post",
            data: {
                page_idx: page_idx ,
                act: act
            },
            url: "page_ajax",
            dataType: "json",
            success:function(result) {
                if(result == "success") {
                    location.reload();
                } else {
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                }
                
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                return;
            }
        });
    }
}

function copyClipboard(param) {
    

    var aux = document.createElement("input");
    aux.setAttribute("value", param.innerHTML);
    document.body.appendChild(aux);
    aux.select();
    document.execCommand("copy");
    document.body.removeChild(aux);

}

function appendInput() {
  var content = "<input type='text' class='bss-input' onKeyDown='event.stopPropagation();' onKeyPress='addSelectInpKeyPress(this,event)' onClick='event.stopPropagation()' placeholder='Add item'> <i class='fas fa-plus' onClick='addSelectItem(this,event,1);'></i>";
  var inputEle = $('.addItem.dropdown-item span.text');
  $('.addItem.dropdown-item span.text').each(function(index, el) {
    if ($(this).text() == '') {
      $(this).html(content);
    }
  });
}

function adapt_ptn_page(pg_ptn_idx) {
  
    if (confirm("[일괄적용 주의]\n현재 셋팅한 옵션들을 해당 고객사에 연결된 페이지에 일괄 전체 적용됩니다. 엑셀 취합 다운로드와는 무관. (로그인하는 고객사 단위 이기때문에 페이지별로 옵션이 다른경우는 검토 후 적용해주세요)")) {

        var pg_chk_data1 = $("#pg_chk_data1").val();
        var pg_chk_data2 = $("#pg_chk_data2").val();
        var pg_chk_data3 = $("#pg_chk_data3").val();
        var pg_chk_data4 = $("#pg_chk_data4").val();
        var pg_chk_data5 = $("#pg_chk_data5").val();
        var pg_chk_data6 = $("#pg_chk_data6").val();
        var pg_chk_data7 = $("#pg_chk_data7").val();
        var pg_chk_data8 = $("#pg_chk_data8").val();
        var pg_chk_data9 = $("#pg_chk_data9").val();

        var pg_chk_code = $("#pg_chk_code").prop('checked') ? '1' : '0';
        var pg_chk_utm = $("#pg_chk_utm").prop('checked') ? '1' : '0';
        var pg_chk_ip = $("#pg_chk_ip").prop('checked') ? '1' : '0';

        var act = "adapt_ptn_page";
        $.ajax({
            type: "post",
            data: {
                pg_ptn_idx  : pg_ptn_idx,
                pg_chk_data1: pg_chk_data1,
                pg_chk_data2: pg_chk_data2,
                pg_chk_data3: pg_chk_data3,
                pg_chk_data4: pg_chk_data4,
                pg_chk_data5: pg_chk_data5,
                pg_chk_data6: pg_chk_data6,
                pg_chk_data7: pg_chk_data7,
                pg_chk_data8: pg_chk_data8,
                pg_chk_data9: pg_chk_data9,
                pg_chk_code : pg_chk_code,
                pg_chk_utm  : pg_chk_utm,
                pg_chk_ip   : pg_chk_ip,
                act: act
            },
            url: "page_ajax",
            dataType: "json", //전송받는 데이터형태 json
            success:function(result) {
                alert("일괄 저장 성공(ex -> 기존페이지에 옵션2가 사용중이였는데 옵션2 미선택이면 문제가 될수있으니 확인바랍니다.)");
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                return;
            }
        });
    }
  
}

// $('body').on('click', '.dropdown-toggle', function(event) {
//   event.preventDefault();
//   appendInput();
// });


$(document).on('shown.bs.select', '#pg_api_add_param', function(event) {
    event.preventDefault();
    appendInput();
});


 
function addSelectItem(t,ev) {
   ev.stopPropagation();
   
    var bs = $(t).closest('.bootstrap-select')
    var txt=bs.find('.bss-input').val().replace(/[|]/g,"");
    var txt=$(t).prev().val().replace(/[|]/g,"");
   
    if ($.trim(txt)=='') return;
   
    var p=bs.find('select');
    var o=$('option', p).eq(-2);
    o.before( $("<option>", { "selected": false, "text": txt}) );
    p.selectpicker('refresh');
    appendInput();
}
 
function addSelectInpKeyPress(t,ev) {
   ev.stopPropagation();
   if (ev.which==124) ev.preventDefault();
   if (ev.which==13) {
      ev.preventDefault();
      addSelectItem($(t).next(),ev);
   }
}



function updateGoogleCell(selectedOrder) {
    let cellMappingString = "";

    selectedOrder.forEach((value, index) => {
        let cellLetter = String.fromCharCode(65 + index);
        cellMappingString += cellLetter + ":" + value + "||";
    });

    cellMappingString = cellMappingString.slice(0, -2);
    $('#google_cell').val(cellMappingString);
}

function resetGoogleData() {
    $('#google_data').selectpicker('deselectAll');
}


// 이미지 실시간 업로드
function ajax_upload_aft_pop_img() {
    var page_idx = $("input[name='page_idx']").val();
    if(!page_idx || page_idx == "0") {
        alert("페이지를 먼저 저장(등록)하신 후 이미지를 업로드할 수 있습니다.");
        return;
    }

    var file_data = $('#pg_aft_ad_img_ajax').prop('files')[0];
    if(!file_data) {
        alert("업로드할 파일을 선택해주세요.");
        return;
    }

    var formData = new FormData();
    formData.append('aft_file', file_data);
    formData.append('page_idx', page_idx); // 직관적으로 page_idx 전송
    formData.append('act', 'ajax_aft_pop_img_upload');

    $.ajax({
        url: './page_ajax.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(res) {
            if(res.status == 'success') {
                var html = "<div class='preview-item' style='position:relative; display:inline-block;'>";
                html += "  <img src='"+res.url+"' style='max-width:250px; border:1px solid #ddd; border-radius:4px;' class='img-thumbnail'>";
                html += "  <button type='button' class='btn btn-danger btn-xs' style='position:absolute; top:5px; right:5px;' onclick=\"ajax_delete_aft_pop_img('"+res.file_idx+"')\"><i class='fas fa-times'></i></button>";
                html += "</div>";
                
                $('#pop_img_preview_box').html(html);
                alert("이미지가 성공적으로 업로드되었습니다.");
            } else {
                alert(res.msg);
            }
        },
        error: function(xhr) {
            alert("업로드 중 오류가 발생했습니다.");
        }
    });
}

// 이미지 실시간 삭제
function ajax_delete_aft_pop_img(file_idx) {
    if(!confirm("기존 이미지를 삭제하시겠습니까?")) return;

    $.ajax({
        url: './page_ajax.php',
        type: 'POST',
        data: {
            act: 'ajax_aft_pop_img_delete',
            file_idx: file_idx
        },
        dataType: 'json',
        success: function(res) {
            if(res.status == 'success') {
                $('#pop_img_preview_box').html('');
                $('#pg_aft_ad_img_ajax').val('');
                $('#label_aft_pop_img').html('이미지 파일 선택 (PNG, JPG)');
                alert("이미지가 삭제되었습니다.");
            } else {
                alert(res.msg);
            }
        }
    });
}






</script>



<?php
include_once(G5_PATH . '/tail.php');

