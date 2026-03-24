<?php
require_once '../../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');

$g5['title'] = "페이지 조회";
include_once(G5_PATH . '/head.php');

$sql_columns    = "";
$sql_common     = "";
$sql_search     = "";
$sql_gruop      = "";
$sql_order      = "";
$total_count    = 0;

$des_mb_no = "";

if($member['mb_deptno'] != "9") {
    if ($member['mb_level'] == 4) {
        $pg_mb_emp = 'and a.pg_mb_emp = '.$member['mb_no'];
    }
}

if($sfl == "") {
    if($member['mb_deptno'] != 9) {
        $search_deptno = $member['mb_deptno'];
    }    
}
//select
$sql_columns = "
  a.page_idx
, pg_domain
, a.pg_uri
, a.pg_memo
, a.pg_des_idx
, b.design_name 
, f_getcode(b.des_cate_code) as des_cate_code_nm
, a.pg_deptno
, c.deptnm 
, a.pg_mb_emp
, ifnull (f_get_mb_name(a.pg_mb_emp), a.pg_mb_emp) as mb_emp_name
, d.mb_name 
, a.pg_ptn_idx
, ifnull(f_get_mb_name(a.pg_mb_ptn), '') as mb_ptn_name
, e.ptn_nm 
, a.pg_title
, a.pg_visit_cnt
, a.update_date
, a.update_user_name
, COALESCE(sub.db_cnt, 0) AS db_cnt
, a.pg_sms_yn
, a.pg_db_sms_yn
, a.pg_db_user_sms_yn
, a.pg_api_yn
, a.pg_aft_ad_yn
";

$sql_common = "
from {$g5['crm_page']} a
left join {$g5['crm_design']} b on a.pg_des_idx = b.design_idx 
left join {$g5['crm_depart']} c on a.pg_deptno = c.deptno 
left join {$g5['member_table']} d on a.pg_mb_emp = d.mb_no and d.mb_gubun != 'E'
left join {$g5['crm_partner']} e on a.pg_ptn_idx = e.ptn_idx 
left join (select land_pg_idx, count(*) as db_cnt from {$g5['crm_landing']} where use_yn = 'y' group by land_pg_idx) sub on a.page_idx = sub.land_pg_idx
";

$sql_search = "
where 1=1
and a.use_yn = 'Y'
$pg_mb_emp
";
$can_modify_partner = false;
$selected_ptn_idx = "";

//부서만 있을때
if ($search_deptno) {

    $vis1 = "";
    $vis2 = "d-none";
    $vis3 = "d-none";
    $vis4 = "d-none";
    $vis5 = "d-none";

    $dis1 = "";
    $dis2 = "disabled";
    $dis3 = "disabled";
    $dis4 = "disabled";
    $dis5 = "disabled";
    
    $sql_search .= "and pg_deptno = '{$search_deptno}' ";
    $def_detp = "and ptn_deptno = {$search_deptno}";

    //부서존재하고 고객사도 선택일시
    if ($search_ptn_idx) {
        if($search_ptn_idx[0] == "") {
            $search_ptn_idx= array_slice($search_ptn_idx,1);
        }

        //고객사 정상적인데이터 여러개 선택시 split
        if(count($search_ptn_idx) >= 1) {
            $insql = implode( ',', $search_ptn_idx );
            $sql_search .= "and pg_ptn_idx in ($insql) ";

            if(count($search_ptn_idx) == 1) {
                $can_modify_partner = true;
                $selected_ptn_idx = $search_ptn_idx[0];
            }
        }
    }
}

//search
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "des_cate_code":
            $sql_search .= " $sfl = '$stx' ";
            $vis1 = "d-none";
            $vis2 = "";
            $vis3 = "d-none";
            $vis4 = "d-none";
            $vis5 = "d-none";
            $dis1 = "disabled";
            $dis2 = "";
            $dis3 = "disabled";
            $dis4 = "disabled";
            $dis5 = "disabled";
            break;
        case "pg_uri":
            $sql_search .= " ($sfl like '%$stx%') ";
            $vis1 = "d-none";
            $vis2 = "d-none";
            $vis3 = "";
            $vis4 = "d-none";
            $vis5 = "d-none";
            $dis1 = "disabled";
            $dis2 = "disabled";
            $dis3 = "";
            $dis4 = "disabled";
            $dis5 = "disabled";
            break;
        case "mb_no":
            $sql_search .= " pg_mb_emp = '$stx'";
            $vis1 = "d-none";
            $vis2 = "d-none";
            $vis3 = "d-none";
            $vis4 = "";
            $vis5 = "d-none";
            $dis1 = "disabled";
            $dis2 = "disabled";
            $dis3 = "disabled";
            $dis4 = "";
            $dis5 = "disabled";
            break;
        case "pg_memo":
            $sql_search .= " ($sfl like '%$stx%') ";
            $vis1 = "d-none";
            $vis2 = "d-none";
            $vis3 = "d-none";
            $vis4 = "d-none";
            $vis5 = "";
            $dis1 = "disabled";
            $dis2 = "disabled";
            $dis3 = "disabled";
            $dis4 = "disabled";
            $dis5 = "";
            break;
    }
    $sql_search .= " ) ";
} else {
    if( ($stx == "" && $sfl == "") || $sfl == "deptno" ) {
        $vis1 = "";
        $vis2 = "d-none";
        $vis3 = "d-none";
        $vis4 = "d-none";
        $vis5 = "d-none";
        $dis1 = "";
        $dis2 = "disabled";
        $dis3 = "disabled";
        $dis4 = "disabled";
        $dis5 = "disabled";
    } else if($sfl == "des_cate_code") {
        $vis1 = "d-none";
        $vis2 = "";
        $vis3 = "d-none";
        $vis4 = "d-none";
        $vis5 = "d-none";
        $dis1 = "disabled";
        $dis2 = "";
        $dis3 = "disabled";
        $dis4 = "disabled";
        $dis5 = "disabled";
    } else if($sfl == "pg_uri") {
        $vis1 = "d-none";
        $vis2 = "d-none";
        $vis3 = "";
        $vis4 = "d-none";
        $vis5 = "d-none";
        $dis1 = "disabled";
        $dis2 = "disabled";
        $dis3 = "";
        $dis4 = "disabled";
        $dis5 = "disabled";
    } else if($sfl == "mb_no") {
        $vis1 = "d-none";
        $vis2 = "d-none";
        $vis3 = "d-none";
        $vis4 = "";
        $vis5 = "d-none";
        $dis1 = "disabled";
        $dis2 = "disabled";
        $dis3 = "disabled";
        $dis4 = "";
        $dis5 = "disabled";
    } else if($sfl == "pg_memo") {
        $vis1 = "d-none";
        $vis2 = "d-none";
        $vis3 = "d-none";
        $vis4 = "d-none";
        $vis5 = "";
        $dis1 = "disabled";
        $dis2 = "disabled";
        $dis3 = "disabled";
        $dis4 = "disabled";
        $dis5 = "";
    }
}

$cnt_sql = " 
select count(*) as cnt
from {$g5['crm_page']} a
left join {$g5['crm_design']} b on a.pg_des_idx = b.design_idx 
left join {$g5['crm_depart']} c on a.pg_deptno = c.deptno 
left join {$g5['member_table']} d on a.pg_mb_emp = d.mb_no and d.mb_gubun != 'E'
left join {$g5['crm_partner']} e on a.pg_ptn_idx = e.ptn_idx 
{$sql_search}
";
$row = sql_fetch($cnt_sql);
$total_count = $row['cnt'];

$rows = 50;
$total_page  = ceil($total_count / $rows);
if ($page < 1) {
    $page = 1;
}
$from_record = ($page - 1) * $rows;

if (!$sst) {
    $sql_order = "order by a.page_idx desc";
}else{
    $sql_order = " order by $sst $sod ";    
}

$sql = " select {$sql_columns} {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);


//코드리스트
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
 and comm_pcd in (1,2,3)
 order by comm_pcd, comm_cd
";
$code_list = sql_query($code_sql);
$code_html = '<option value="">전체</option>';
for ($i = 0; $code = sql_fetch_array($code_list); $i++) { 
    $code_html .= '<option value="'.$code['comm_idx'].'" '.get_selected($stx, $code['comm_idx']).'>['.$code['comm_pcd'].':'.$code['comm_pnm'].']'.$code['comm_nm'].'</option>';
}

//부서코드리스트
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

$dept_html = '<option value="">미선택</option>';
for ($i = 0; $dept = sql_fetch_array($dept_list); $i++) {
    $dept_html .= '<option value="'.$dept['deptno'].'"'.get_selected($search_deptno, $dept['deptno']).'>'.$dept['deptnm'].'</option>';
}


if ($member['mb_level'] <= 6) {

    if ($member['mb_level'] == 4) {
        $add_cond = "and ptn_mb_emp  = {$member['mb_no']}";
    } else {
        $add_cond = "and ptn_deptno = {$member['mb_deptno']}";
    }
} else {
    $add_cond = "";
}

if($search_deptno) {
    //고객사코드
    $partner_sql = "
    select ptn_idx
        , ptn_nm
    from {$g5['crm_partner']} 
    where use_yn = 'Y'
    {$def_detp}
    {$add_cond}
    order by ptn_idx desc
    ";
    $partner_list = sql_query($partner_sql);
    $ptn_html = '<option value="">미선택</option>';

    $cnt = is_null($search_ptn_idx) ? 0 : count($search_ptn_idx);
    for ($i = 0; $partner = sql_fetch_array($partner_list); $i++) { 
        for ($j =0; $j < $cnt; $j++ ) {
            $selected = "";
            if($search_ptn_idx[$j] == $partner['ptn_idx']) {
                $selected = " selected";
                break;
            } 
        }
        $ptn_html .= '<option value="'.$partner['ptn_idx'].'" '.$selected.'>'.$partner['ptn_nm'].'</option>';
    }
}


if($member['mb_level'] >= 5) {
    $emp_sql = "
    select mb_no
         , mb_name
    from {$g5['member_table']} a
    where 1=1
    and is_login = 'Y'
    and mb_deptno = {$member['mb_deptno']}
    order by mb_name
    ";
    $emp_list = sql_query($emp_sql);
}



if($sfl == "deptno" && $search_deptno!="") {
    $qstr .= "&search_deptno=".$search_deptno;

    if($search_ptn_idx!="") {

        if($search_ptn_idx[0] == "") {
            $qstr .= array_slice($search_ptn_idx,1);
        }
        //고객사 정상적인데이터 여러개 선택시 split
        if(count($search_ptn_idx) >= 1) {

            for($ptn=0; $ptn < count($search_ptn_idx); $ptn++ ) {
                $qstr .= "&search_ptn_idx[]=".$search_ptn_idx[$ptn];
            }
        }
    }
}


?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">페이지조회(<?php echo $total_count ?>)</h3>
                    </div>

                    <div class="card-body">
                        <div class="dataTables_wrapper dt-bootstrap4">

                            <div class="d-flex flex-sm-row flex-column justify-content-sm-between">
                                <div class="d-flex justify-content-center mb-2 mb-sm-0">
                                    <div class="btn-group xs-100">
                                        <button type="submit" name="btn_ins" value="등록" onclick="location.href='<?php echo G5_BIZ_URL; ?>/page/page_form'" class="btn btn-primary btn-sm border border-dark"><i class="fas fa-pen"></i> 입력</button>
                                        <button type="submit" form="listForm" class="btn btn-danger btn-sm border border-dark" name="act_button" value="선택삭제"><i class="far fa-trash-alt"></i>삭제</button>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <div class="btn-group xs-100">

                                        <form class="form-inline my-2 my-lg-0 ng-pristine ng-valid">
                                            <select name="sfl" id="sfl" class="custom-select border border-secondary">
                                                <option value="deptno" <?php echo get_selected($sfl, "deptno"); ?>>부서</option>
                                                <option value="mb_no" <?php echo get_selected($sfl, "mb_no"); ?>>담당자</option>
                                                <option value="des_cate_code" <?php echo get_selected($sfl, "des_cate_code"); ?>>카테고리</option>
                                                <option value="pg_uri" <?php echo get_selected($sfl, "pg_uri"); ?>>코드uri</option>
                                                <option value="pg_memo" <?php echo get_selected($sfl, "pg_memo"); ?>>페이지설명</option>
                                            </select>
                                       
                                            <select id="search_deptno" name="search_deptno" class="form-control " data-live-search="true" data-style="border border-secondary" >
                                                <?php echo $dept_html ?>
                                            </select>

                                            <?php if($member['mb_level'] >= 5) { ?>
                                            <select id="search_emp_idx" name="stx" class="form-control border-secondary <?php echo $vis4 ?>" <?php echo $dis4 ?>" >
                                                <option value="">미선택</option>
                                                <?php for ($i = 0; $emp = sql_fetch_array($emp_list); $i++) { ?>
                                                    <option value="<?php echo $emp['mb_no'] ?>" data-tokens="<?php echo $emp['mb_name'] ?>" <?php echo  $sfl == "mb_no" ? get_selected($stx, $emp['mb_no']) : '' ?>><?php echo $emp['mb_name'] ?></option>
                                                <?php } ?>
                                            </select>
                                            <?php } ?>
                                       
                                            <select id="search_ptn_idx" name="search_ptn_idx[]" class="form-control " data-live-search="true" data-style="border border-secondary" data-width="200px" data-size="15" data-max-options="3" multiple >
                                                <?php echo $ptn_html ?>
                                            </select>
                                            
                                            <select id="search_code" name="stx" class="form-control border-secondary <?php echo $vis2 ?>" <?php echo $dis2 ?>>
                                            <?php echo $code_html ?>
                                            </select>

                                            <input type="text" id="search_pg_uri" name="stx" value="<?php echo $sfl == "pg_uri" ? $stx : '' ?>" class="form-control border-secondary <?php echo $vis3 ?>" placeholder="코드 검색" aria-label="코드 검색" <?php echo $dis3 ?>>
                                            <input type="text" id="search_pg_memo" name="stx" value="<?php echo $sfl == "pg_memo" ? $stx : '' ?>" class="form-control border-secondary <?php echo $vis5 ?>" placeholder="페이지설명 검색" aria-label="페이지설명 검색" <?php echo $dis5 ?>>

                                            <button type="submit" class="btn btn-outline-success ml-1">검색</button>
                                        </form>

                                    </div>
                                </div>
                            </div>

                            <form name="listForm" id="listForm" action="./page_list_update" onsubmit="return listForm_submit(this);" method="post">
                                <input type="hidden" name="sst" value="<?php echo $sst ?>">
                                <input type="hidden" name="sod" value="<?php echo $sod ?>">
                                <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
                                <input type="hidden" name="stx" value="<?php echo $stx ?>">
                                <input type="hidden" name="page" value="<?php echo $page ?>">
                                <input type="hidden" name="token" value="<?php echo isset($token) ? $token : ''; ?>">

                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="tbl_page" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)"></th>
                                                    <th>NO</th>
                                                    <th>설명</th>
                                                    <th>[디자인]-카테고리</th>
                                                    <th>주소/코드</th>
                                                    <th>인증</th>
                                                    <th>알람</th>
                                                    <!-- <th>알람2</th> -->
                                                    <th>API</th>
                                                    <th>AD</th>
                                                    <th>DB/방문</th>
                                                    <th>[고객사]직원</th>
                                                    <th>[관리팀]직원</th>
                                                    <th>last수정자</th>
                                                    <th>last수정일</th>
                                                    <th>복사</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php for ($i = 0; $row = sql_fetch_array($result); $i++) { 
                                            $isAssign = $row['pg_ptn_idx'] != "" ? "" : "class='table-danger'";
                                            ?>
                                            <tr <?php echo $isAssign?> >
                                                <td>
                                                    <input type="hidden" name="page_idx[<?php echo $i ?>]" value="<?php echo $row['page_idx'] ?>">
                                                    <input type="hidden" name="pg_uri[<?php echo $i ?>]" value="<?php echo $row['pg_uri'] ?>">
                                                    <input type="hidden" name="pg_domain[<?php echo $i ?>]" value="<?php echo $row['pg_domain'] ?>">
                                                    <input type="hidden" name="pg_deptno[<?php echo $i ?>]" value="<?php echo $row['pg_deptno'] ?>">

                                                    <?php echo isCheckbox($i, $row['pg_deptno'], $row['pg_mb_emp'], $member); ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['page_idx'] ?>
                                                </td>
                                                <td>
                                                    <a href="page_form?<?php echo $qstr ?>&w=u&page_idx=<?php echo $row['page_idx']?>"><?php echo $row['pg_memo']?></a>
                                                </td>

                                                <td>
                                                    <?php echo '['.$row['design_name'].']-' .$row['des_cate_code_nm'] ?><a href="<?php echo G5_BIZ_URL ?>/design/design_form?w=u&design_idx=<?php echo $row['pg_des_idx'] ?>"> <i class="fas fa-link"></i></a>
                                                </td>
                                                <td>
                                                    <a href="<?php echo G5_BIZ_URL ?>/landing/land_list?sfl=pg_uri&stx=<?php echo $row['pg_uri'] ?>" target="_self">
                                                    <?php echo $row['pg_domain'] ."/".$row['pg_uri'].'</span>' ?></a><a href="https://<?php echo $row['pg_domain'].'/'.$row['pg_uri'] ?>" target="_blank"> <i class="fas fa-plane-departure text-success"></i></a>
                                                </td>
                                                <td class="text-center">
                                                    <?php echo $row['pg_sms_yn'] == "Y" ? "<i class='fas fa-toggle-on text-success'></i>" : "<i class='fas fa-toggle-off text-danger'></i>" ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php echo $row['pg_db_sms_yn'] == "Y" ? "<i class='fas fa-toggle-on text-success'></i>" : "<i class='fas fa-toggle-off text-danger'></i>" ?>
                                                </td>
                                                <!-- <td class="text-center">
                                                    <?php echo $row['pg_db_user_sms_yn'] == "Y" ? "<i class='fas fa-toggle-on text-success'></i>" : "<i class='fas fa-toggle-off text-danger'></i>" ?>
                                                </td> -->
                                                <td class="text-center">
                                                    <?php echo $row['pg_api_yn'] == "Y" ? "<i class='fas fa-toggle-on text-success'></i>" : "<i class='fas fa-toggle-off text-danger'></i>" ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php echo $row['pg_aft_ad_yn'] == "Y" ? "<i class='fas fa-toggle-on text-success'></i>" : "<i class='fas fa-toggle-off text-danger'></i>" ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['db_cnt'].'/'.$row['pg_visit_cnt'] ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['mb_ptn_name'] != "" ? "[".$row['ptn_nm']."]".$row['mb_ptn_name'] : "[".$row['ptn_nm']."]미지정" ?><a href="<?php echo G5_BIZ_URL ?>/partner/partner_form?w=u&ptn_idx=<?php echo $row['pg_ptn_idx'] ?>"> <i class="fas fa-link"></i></a>
                                                </td>
                                                <td>
                                                    <?php echo $row['pg_mb_emp'] != "" ? "[".$row['deptnm']."]".$row['mb_emp_name'] : "[".$row['deptnm']."]미지정" ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['update_user_name'] ?>
                                                </td>
                                                <td>
                                                    <?php echo view_dateformat($row['update_date']) ?>
                                                </td>
                                                <td>

                                                </td>
                                            </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mb-2">
                                    <?php if($can_modify_partner == true) { ?>
                                    <div class="d-flex">
                                        <div class="input-group input-group-sm">
                                            <select id="selChgPtn" class="custom-select">
                                                <?php echo $ptn_html; ?>
                                            </select>
                                            <div class="input-group-append">
                                                <button type="button" id="btnChgPtn" class="btn btn-secondary">고객사이동</button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                    <div class="d-flex justify-content-center justify-content-sm-end mt-2 mt-sm-0 w-100 w-sm-auto">
                                        <?php echo get_paging_bootst(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'] . '?' . $qstr . '&amp;page='); ?>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="modal-copy">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header bg-success">
            <h4 class="modal-title">페이지 복사</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form name="update_form" action="./page_list_update" method="post">
            <input type="hidden" id="page_idx" name="page_idx" value="">
            <div class="modal-body">


                <div class="form-group mb-3">
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label" for="asis_pg_uri">기존 코드</label>
                            <input type="text" id="asis_pg_uri" name="asis_pg_uri" class="form-control" value="" disabled>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="pg_uri">신규 코드</label>
                            <input type="text" id="pg_uri" name="pg_uri" class="form-control border-info" placeholder="신규코드입력" style="text-transform: lowercase;" pattern=".{3,16}" required title="자리수 3~16" onkeyup="this.value=this.value.replace(/[^a-z0-9_.-]/gi,'');" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>담당자</label>
                    <select id="pg_mb_emp" name="pg_mb_emp" class="form-control custom-select">
                    </select>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" data-dismiss="modal">닫기</button>
                <button type="submit" class="btn btn-primary" name="act_button" value="카피">저장</button>
            </div>
        </form>
        </div>
    </div>
</div>


<script>
    $(function() {

        <?php if($can_modify_partner == true) { ?>
        //이동시 자기자신 고객사와 + 미지정 제거해야함
        $('#selChgPtn option[value=""]').remove();
        var selfData = "<?php echo $selected_ptn_idx ?>";
        $('#selChgPtn option[value="' + selfData + '"]').remove();
        $('#selChgPtn option:first').prop('selected', true);
        <?php } ?>
        
        
        $('#search_deptno').selectpicker();
        $('#search_ptn_idx').selectpicker();

        var flag = '<?php echo $sfl ?>';

        if(flag != "" && flag != "deptno") {
            $('#search_deptno').selectpicker('hide');
            $('#search_ptn_idx').selectpicker('hide');
        }


        $('#btnChgPtn').on('click', function () {
            let selected = [];
            $('input[name="chk[]"]:checked').each(function () {
                const pageIdx = $(this).closest('td').find('input[name^="page_idx"]').val();
                if (pageIdx) {
                    selected.push(pageIdx);
                }
            });

            const selectedPartner = $('#selChgPtn').val();

            if (selected.length === 0) {
                alert('선택된 항목이 없습니다.');
                return;
            }

            $.ajax({
                type: "post",
                url: "page_ajax",
                dataType: "json",
                data: {
                    act: "chgPtn",
                    page_idx_list: selected,
                    selected_partner: selectedPartner,
                    orginal_partner: selfData
                },
                success: function (result) {
                        location.reload();
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                }
            });
        });
        
        // $('#btnChgPtn').on('click', function () {
        //     let selected = [];

        //     $('.chk-page:checked').each(function () {
        //         const idx = $(this).data('index');
        //         const pageIdx = $('input[name="page_idx[' + idx + ']"]').val();

        //         if (pageIdx) {
        //             selected.push(pageIdx);
        //         }
        //     });

        //     if (selected.length === 0) {
        //         alert('선택된 항목이 없습니다.');
        //         return;
        //     }

        //     alert(
        //         '선택된 Partner: ' + selectedPartner + '\n' +
        //         '선택된 page_idx 값들:\n' + selected.join(', ')
        //     );


        //     // $.ajax({
        //     //     url: '/your_ajax_url.php', // 여기에 처리할 PHP 경로 입력
        //     //     method: 'POST',
        //     //     data: {
        //     //         mode: 'change_partner',
        //     //         page_idx_list: selected,
        //     //         selected_partner: $('#selChgPtn').val()
        //     //     },
        //     //     success: function (res) {
        //     //         // 성공 처리
        //     //         console.log('성공:', res);
        //     //         location.reload(); // 필요시 새로고침
        //     //     },
        //     //     error: function (xhr) {
        //     //         console.error('오류:', xhr);
        //     //         alert('처리 중 오류가 발생했습니다.');
        //     //     }
        //     // });
        // });

        

        $("#sfl").change(function () {

            var obj = $(this).val();
            if(obj == "deptno") {
                
                
                $('#search_deptno').selectpicker('show');
                $('#search_ptn_idx').selectpicker('show');
                $('#search_emp_idx').addClass("d-none");
                $('#search_code').addClass("d-none");
                $('#search_pg_uri').addClass("d-none");
                $('#search_pg_memo').addClass("d-none");
                
                $('#search_deptno').attr("disabled" , false);
                $('#search_ptn_idx').attr("disabled", false);
                $('#search_emp_idx').attr("disabled" , true);
                $('#search_code').attr("disabled"   , true);
                $('#search_pg_uri').attr("disabled" , true);
                $('#search_pg_memo').attr("disabled" , true);
                
            } else if(obj == "mb_no") {
                
                $('#search_deptno').selectpicker('hide');
                $('#search_ptn_idx').selectpicker('hide');
                $('#search_emp_idx').removeClass("d-none");
                $('#search_code').addClass("d-none");
                $('#search_pg_uri').addClass("d-none");
                $('#search_pg_memo').addClass("d-none");
                
                $('#search_deptno').attr("disabled" , true);
                $('#search_ptn_idx').attr("disabled", true);
                $('#search_emp_idx').attr("disabled" , false);
                $('#search_code').attr("disabled"   , true);
                $('#search_pg_uri').attr("disabled" , true);
                $('#search_pg_memo').attr("disabled" , true);
                
            }
            
            else if(obj == "des_cate_code") {
               
                $('#search_deptno').selectpicker('hide');
                $('#search_ptn_idx').selectpicker('hide');
                $('#search_emp_idx').addClass("d-none");
                $('#search_code').removeClass("d-none");
                $('#search_pg_uri').addClass("d-none");
                $('#search_pg_memo').addClass("d-none");
                
                $('#search_deptno').attr("disabled" , true);
                $('#search_ptn_idx').attr("disabled", true);
                $('#search_emp_idx').attr("disabled" , true);
                $('#search_code').attr("disabled"   , false);
                $('#search_pg_uri').attr("disabled" , true);
                $('#search_pg_memo').attr("disabled" , true);

            } else if(obj == "pg_uri") {

                $('#search_deptno').selectpicker('hide');
                $('#search_ptn_idx').selectpicker('hide');
                $('#search_emp_idx').addClass("d-none");
                $('#search_code').addClass("d-none");
                $('#search_pg_uri').removeClass("d-none");
                $('#search_pg_memo').addClass("d-none");
                
                $('#search_deptno').attr("disabled" , true);
                $('#search_ptn_idx').attr("disabled", true);
                $('#search_emp_idx').attr("disabled" , true);
                $('#search_code').attr("disabled"   , true);
                $('#search_pg_uri').attr("disabled" , false);
                $('#search_pg_memo').attr("disabled"   , true);

            } else if(obj == "pg_memo") {

                $('#search_deptno').selectpicker('hide');
                $('#search_ptn_idx').selectpicker('hide');
                $('#search_emp_idx').addClass("d-none");
                $('#search_code').addClass("d-none");
                $('#search_pg_uri').addClass("d-none");
                $('#search_pg_memo').removeClass("d-none");

                $('#search_deptno').attr("disabled" , true);
                $('#search_ptn_idx').attr("disabled", true);
                $('#search_emp_idx').attr("disabled" , true);
                $('#search_code').attr("disabled"   , true);
                $('#search_pg_uri').attr("disabled" , true);
                $('#search_pg_memo').attr("disabled"   , false);

            } 
        });

        $("#search_deptno").change(function() {
            var deptno = $(this).val();
            var act = "condDeptByPtn";

            var $target2 = $("#search_ptn_idx");
            
            $target2.children().remove().end();
            $target2.selectpicker('refresh').empty();
            $target2.selectpicker();

            $.ajax({
                type: "post",
                data: {
                    deptno: deptno ,
                    act: act
                },
                url: "<?php echo G5_BIZ_URL?>/common/code_ajax",
                dataType: "json", //전송받는 데이터형태 json
                success:function(result) {
                    $target2.append(result);
                    $target2.selectpicker("refresh");
                    // $target.show();
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    return;
                }
            });
        });


        $("#search_ptn_idx").change(function() {
            //console.log(this.value, this.options[this.selectedIndex].value, $(this).find("option:selected").val(),);
            //취소
            // if(this.value == "") {
                

            //     $('#search_ptn_idx').selectpicker('refresh');
            //     selectobject=document.getElementById("search_ptn_idx").getElementsByTagName("option");
            //     selectobject[0].disabled=true;

            // }
        });

        var table = $('#tbl_page').DataTable({
            "paging": false,
            "searching": false,
            "ordering": false,
            "info": false,
            "autoWidth": true,
            columnDefs:[
                {responsivePriority : 0     , targets: 0, "width":"1%"},
                {responsivePriority : 108   , targets: 1, "width":"2%"},
                {responsivePriority : 1     , targets: 2},
                {responsivePriority : 102   , targets: 3},
                {responsivePriority : 103   , targets: 4},
                {responsivePriority : 104   , targets: 5, "width":"1%"},
                {responsivePriority : 105   , targets: 6, "width":"1%"},
                {responsivePriority : 106   , targets: 7, "width":"1%"},
                {responsivePriority : 107   , targets: 8},
                {responsivePriority : 108   , targets: 9},
                {responsivePriority : 109   , targets: 10},
                {responsivePriority : 110   , targets: 11},
                {responsivePriority : 110   , targets: 12},
                // {responsivePriority : 111   , targets: 13},
                {responsivePriority : 112   , targets: 13, "width":"1%",
                    render: function(data,type,row){
                        if (type === 'display') {
                            return "<button id='btn_info' type='button' class='btn btn-info btn-xs' data-toggle='modal' data-target='#modal-copy' data-title='"+row[1]+"' data-p1='"+row[0]+"'>복사</button>";
                        }
                    }
                },
            ]            
        });


        $('#modal-copy').on('show.bs.modal', function (event) {
            var act = "page_copy";

            $('#modal-copy #page_idx').val('');
            $('#modal-copy #asis_pg_uri').val('');

            var org_deptno = '<?php echo $member['mb_deptno']?>';

            var button = $(event.relatedTarget);
            var page_idx = button.data('title');
            var p_name = button.data('p1');

            // DOM 파서를 사용하여 pg_deptno 값을 추출
            var parser = new DOMParser();
            var doc = parser.parseFromString(p_name, 'text/html');
            var click_row_dept = doc.querySelector('input[name^="pg_deptno"]');
            var click_deptno = click_row_dept.value;


            var click_row_pguri = doc.querySelector('input[name^="pg_uri"]');
            var click_pg_uri = click_row_pguri.value;

            
            

            if(click_deptno != org_deptno) {
                alert('해당 코드는 부서가 달라 복사할수없습니다.');
                return false;
            } else {

                $('#modal-copy #page_idx').val(page_idx);
                $('#modal-copy #asis_pg_uri').val(click_pg_uri);

                var $target1 = $("#modal-copy #pg_mb_emp");
                $target1.empty();
                
                
                $.ajax({
                    type: "post",
                    data: {
                        act: act
                    },
                    url: "page_ajax",
                    dataType: "json", //전송받는 데이터형태 json
                    success:function(result) {
                        $target1.append(result[0]);
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                        return;
                    }
                });

            }
        });


        //uri중복방지
        $("#modal-copy #pg_uri").change(function() {
            var pg_uri = $(this).val();
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
    });
</script>

<?php
include_once(G5_PATH . '/tail.php');
