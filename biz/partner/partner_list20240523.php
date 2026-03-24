<?php
require_once '../../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');

$g5['title'] = "고객사조회";
include_once(G5_PATH . '/head.php');

$sql_columns    = "";
$sql_common     = "";
$sql_search     = "";
$sql_gruop      = "";
$sql_order      = "";
$total_count    = 0;

//select
$sql_columns = "
  a.ptn_idx 
, a.ptn_nm 
, f_getcode(cate_code) as cate_code_nm
, a.ptn_deptno
, b.deptnm as ptn_deptnm
, a.ptn_mb_emp
, c.mb_name
, a.ptn_startday 
, a.ptn_endday 
, a.ptn_status 
, a.mb_id
, a.isconn
, a.insert_date 
, a.update_date 
, a.update_user_name
, ifnull(a.ptn_endday between curdate() and date_add(curdate(), interval 7 day) , 2) as duedate
, (select count(*) from gnp_crm_db_share sub where a.ptn_idx = sub.share_parent_ptn) as share_cnt
, COALESCE(db_tot_cnt.db_tot_cnt, 0) AS db_tot_cnt
, COALESCE(db_tody_cnt.db_tody_cnt, 0) AS db_tody_cnt
";

$sql_common = "
from {$g5['crm_partner']}       a
left join {$g5['crm_depart']}   b on a.ptn_deptno = b.deptno  
left join {$g5['member_table']} c on a.ptn_mb_emp = c.mb_no
LEFT JOIN (
    SELECT land_ptn_idx, COUNT(*) AS db_tot_cnt
    FROM {$g5['crm_landing']}
    WHERE use_yn = 'Y'
    GROUP BY land_ptn_idx
) db_tot_cnt ON a.ptn_idx = db_tot_cnt.land_ptn_idx
LEFT JOIN (
    SELECT land_ptn_idx, COUNT(*) AS db_tody_cnt
    FROM {$g5['crm_landing']}
    WHERE use_yn = 'Y' AND insert_date2 = CURDATE()
    GROUP BY land_ptn_idx
) db_tody_cnt ON a.ptn_idx = db_tody_cnt.land_ptn_idx
";

// select a.ptn_idx 
// , a.ptn_nm 
// , f_getcode(cate_code) as cate_code_nm
// , a.ptn_deptno
// , b.deptnm as ptn_deptnm
// , a.ptn_mb_emp
// , c.mb_name
// , a.ptn_startday 
// , a.ptn_endday 
// , a.ptn_status 
// , a.mb_id
// , a.isconn
// , a.insert_date 
// , a.update_date 
// , a.update_user_name
// , ifnull(a.ptn_endday between curdate() and date_add(curdate(), interval 7 day) , 2) as duedate
// , (select count(*) from gnp_crm_db_share sub where a.ptn_idx = sub.share_parent_ptn) as share_cnt
// -- , (select count(*) from gnp_crm_landing land where a.ptn_idx = land.land_ptn_idx and land.use_yn = 'Y') as db_tot_cnt
// -- , (select count(*) from gnp_crm_landing land where a.ptn_idx = land.land_ptn_idx and land.use_yn = 'Y' and land.insert_date2 = curdate()) as db_tody_cnt
// , d.cnt as db_tot_cnt
// , e.cnt as db_tody_cnt
// from gnp_crm_partner     a
// left join gnp_crm_depart b on a.ptn_deptno = b.deptno
// left join gnp_member     c on a.ptn_mb_emp = c.mb_no
// left join 
//    (select land_ptn_idx, count(*) cnt 
// 	  from gnp_crm_landing
// 	 where use_yn = 'Y'
// 	 group by land_ptn_idx) d
// on d.land_ptn_idx = a.ptn_idx
// left join
//    (select land_ptn_idx, count(*) cnt 
// 	  from gnp_crm_landing
// 	 where use_yn = 'Y'
// 	   and land_ptn_idx is not null
// 	   and insert_date between curdate() and date_add(curdate(), interval 1 day)
// 	 group by land_ptn_idx) e
// on e.land_ptn_idx = a.ptn_idx
// where 1=1
// and b.deptno = 6

$sql_search .= " 
    where 1=1
";


//search
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "ptn_nm":
            $sql_search .= " ($sfl like '%$stx%') ";
            break;
        case "cate_code":
            $sql_search .= " $sfl = '$stx' ";
            break;
        case "deptno":
            $sql_search .= " $sfl = '$stx' ";
            break;
        case "ptn_status":
            $sql_search .= " $sfl = '$stx' ";
            break;
    }
    $sql_search .= " ) ";
} else {

    if($sfl != "deptno") {
        $sql_search .= " and deptno = ".$member['mb_deptno'];
        $sfl = "deptno";
        $stx = $member['mb_deptno'];
    }
}


$cnt_sql = " 
select count(*) as cnt
from {$g5['crm_partner']}       a
left join {$g5['crm_depart']}   b on a.ptn_deptno = b.deptno  
left join {$g5['member_table']} c on a.ptn_mb_emp = c.mb_no
{$sql_search}
";
$row = sql_fetch($cnt_sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);
if ($page < 1) {
    $page = 1;
}
$from_record = ($page - 1) * $rows;

if (!$sst) {
    $sql_order = "order by a.ptn_endday between curdate() and date_add(curdate(), interval 7 day) desc, a.ptn_idx desc";
}else{
    if("today_rank" == $sst) {
        $sql_order = " order by db_tody_cnt $sod ";
    } else {
        $sql_order = " order by $sst $sod ";
    }
        
}

$sql = " select {$sql_columns} {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);


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
 and comm_pcd in (1,2,3)
";
$code_list = sql_query($code_sql);

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


?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">고객사조회(<?php echo $total_count ?>)</h3>
                    </div>

                    <div class="card-body">
                        <div class="dataTables_wrapper dt-bootstrap4">
                        
                            <div class="d-flex flex-sm-row flex-column justify-content-sm-between">
                                <div class="d-flex justify-content-center mb-2 mb-sm-0">
                                    <div class="btn-group xs-100">
                                        <button type="submit" name="btn_ins" value="등록" onclick="location.href='<?php echo G5_BIZ_URL; ?>/partner/partner_form'" class="btn btn-primary btn-sm border border-dark"><i class="fas fa-pen"></i> 입력</button>
                                        <button type="submit" form="listForm" class="btn btn-warning btn-sm border border-dark" name="act_button" value="선택수정"><i class="fas fa-eraser"></i>수정</button>
                                        <button type="submit" form="listForm" class="btn btn-danger btn-sm border border-dark" name="act_button" value="선택삭제"><i class="far fa-trash-alt"></i>삭제</button>

                                        <button type="button" class="btn btn-info btn-sm border border-dark" id="addPtnEmp">계정추가</button>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <div class="btn-group xs-100">
                                        
                                    <form class="form-inline my-2 my-lg-0 ng-pristine ng-valid">
                                        <select name="sfl" id="sfl" class="custom-select" >
                                            <option value="ptn_nm" <?php echo get_selected($sfl, "ptn_nm"); ?> >고객명</option>
                                            <option value="cate_code" <?php echo get_selected($sfl, "cate_code"); ?>>카테고리</option>
                                            <option value="deptno" <?php echo get_selected($sfl, "deptno"); ?>>부서명</option>
                                            <option value="ptn_status" <?php echo get_selected($sfl, "ptn_status"); ?>>진행상태</option>
                                        </select>
                                        <input type="text" id="search_keyword1" name="stx" value="<?php echo $sfl == "ptn_nm" ? $stx : '' ?>" class="form-control" placeholder="검색어" aria-label="검색어">
                                        
                                        <select id="search_keyword2" name="stx" class="form-control">
                                        <option value="">전체</option>
                                        <?php for ($i = 0; $code = sql_fetch_array($code_list); $i++) { ?>
                                            <option value="<?php echo $code['comm_idx'] ?>" <?php echo $sfl == "cate_code" ? get_selected($stx, $code['comm_idx']) : '' ?> ><?php echo '['.$code['comm_pcd'].':'.$code['comm_pnm'] .'] '. $code['comm_nm'] ?></option>
                                        <?php } ?>
                                        </select>


                                        <select id="search_keyword3" name="stx" class="form-control selectpicker" data-style="border border-secondary" data-width="200px">
                                        <option value="">전체</option>
                                        <?php for ($i = 0; $dept = sql_fetch_array($dept_list); $i++) { ?>
                                            <option value="<?php echo $dept['deptno'] ?>" data-tokens="<?php echo $dept['deptnm'] ?>" <?php echo  $sfl == "deptno" ? get_selected($stx, $dept['deptno']) : '' ?>><?php echo $dept['deptnm'] ?></option>
                                        <?php } ?>
                                        </select>

                                        

                                        <select id="search_keyword4" name="stx" class="form-control">
                                        <option value="">전체</option>
                                            <option value="1" <?php echo get_selected($stx, '1'); ?>>대기  </option>
                                            <option value="2" <?php echo get_selected($stx, '2'); ?>>진행  </option>
                                            <option value="3" <?php echo get_selected($stx, '3'); ?>>중지  </option>
                                            <option value="4" <?php echo get_selected($stx, '4'); ?>>종료  </option>
                                            <option value="5" <?php echo get_selected($stx, '5'); ?>>환불  </option>
                                            <option value="6" <?php echo get_selected($stx, '6'); ?>>블랙  </option>
                                        </select>

                                        <button type="submit" class="btn btn-outline-success ml-1">검색</button>
                                    </form>
                                    </div>
                                </div>
                            </div>

                            <form name="listForm" id="listForm" action="./partner_list_update" onsubmit="return listForm_submit(this);" method="post">
                                <input type="hidden" name="sst" value="<?php echo $sst ?>">
                                <input type="hidden" name="sod" value="<?php echo $sod ?>">
                                <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
                                <input type="hidden" name="stx" value="<?php echo $stx ?>">
                                <input type="hidden" name="page" value="<?php echo $page ?>">
                                <input type="hidden" name="token" value="<?php echo isset($token) ? $token : ''; ?>">
                                
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="tbl_partner" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)"></th>
                                                    <th>NO</th>
                                                    <th><?php echo get_sort_bootst('ptn_nm','', 'desc', $sst, $sod, '고객명'); ?></th>

                                                    <?php if($sfl == "deptno" && $stx == $member['mb_deptno']) { ?>
                                                    <th>전체DB</th>
                                                    <th><?php echo get_sort_bootst('today_rank','', 'desc', $sst, $sod, '금일DB'); ?></th>
                                                    <?php } ?>
                                                    
                                                    <th>카테고리</th>
                                                    <th>담당자</th>
                                                    <th><?php echo get_sort_bootst('ptn_startday','', 'desc', $sst, $sod, '시작일'); ?></th>
                                                    <th><?php echo get_sort_bootst('ptn_endday','', 'desc', $sst, $sod, '종료일'); ?></th>
                                                    <th>상태</th>
                                                    <th>last수정자</th>
                                                    <th>last수정일</th>
                                                    <th>직원</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php for ($i = 0; $row = sql_fetch_array($result); $i++) { 
                                                
                                                $shared_icon = '';
                                                if($row['share_cnt'] > 0) {
                                                    $shared_icon = ' <i class="fas fa-share-alt text-danger"></i>';
                                                }

                                                ?>
                                                <tr>
                                                    <td>
                                                        <?php echo isCheckbox($i, $row['ptn_deptno'], $row['ptn_mb_emp'], $member); ?>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="ptn_idx[<?php echo $i ?>]" value="<?php echo $row['ptn_idx'] ?>">
                                                        <?php echo $row['ptn_idx'] ?>
                                                    </td>
                                                    <td>
                                                        <a href="partner_form?w=u&ptn_idx=<?php echo $row['ptn_idx'].$qstr ?>"><?php echo $row['ptn_nm'] . $shared_icon ?></a>
                                                    </td>

                                                    <?php if($sfl == "deptno" && $stx == $member['mb_deptno']) { ?>
                                                    <td>
                                                        <?php echo number_format($row['db_tot_cnt']); ?>건
                                                    </td>
                                                    <td>
                                                        <?php echo number_format($row['db_tody_cnt']); ?>건
                                                    </td>
                                                    <?php } ?>

                                                    <td>
                                                        <?php echo $row['cate_code_nm'] ?>
                                                    </td>
                                                    <td>
                                                        <?php echo '['.$row['ptn_deptnm'].'] '.$row['mb_name'] ?>
                                                    </td>
                                                    <td>
                                                        <input type="date" id="ptn_startday" name="ptn_startday[]" class="custom_select" value="<?php echo $row['ptn_startday'] ?>" <?php echo isShowListInput($row['ptn_deptno'], $row['ptn_mb_emp'], $member, "readonly"); ?>>
                                                    </td>
                                                    <td>
                                                        <input type="date" id="ptn_endday" name="ptn_endday[]" class="custom_select" value="<?php echo $row['ptn_endday'] ?>" <?php echo isShowListInput($row['ptn_deptno'], $row['ptn_mb_emp'], $member, "readonly"); ?>>
                                                        <?php if($row['duedate'] == 1) echo ' <i class="fas fa-sync fa-spin text-danger" data-toggle="tooltip" data-placement="bottom" title="종료1주전"></i>'; ?>
                                                    </td>
                                                   <td>
                                                    <select name="ptn_status[]" id="ptn_status" class="custom_select" <?php echo isShowListInput($row['ptn_deptno'], $row['ptn_mb_emp'], $member, "readonly"); ?>>
                                                        <option value="1" class="badge-primary" <?php echo get_selected($row['ptn_status'], '1'); ?>>대기</option>
                                                        <option value="2" class="badge-success" <?php echo get_selected($row['ptn_status'], '2'); ?>>진행</option>
                                                        <option value="3" class="badge-danger" <?php echo get_selected($row['ptn_status'], '3'); ?>>중지</option>
                                                        <option value="4" class="badge-danger" <?php echo get_selected($row['ptn_status'], '4'); ?>>종료</option>
                                                        <option value="5" class="badge-danger" <?php echo get_selected($row['ptn_status'], '5'); ?>>환불</option>
                                                        <option value="6" class="badge-danger" <?php echo get_selected($row['ptn_status'], '6'); ?>>블랙</option>
                                                    </select>
                                                    </td>
                                                    <td>
                                                        <?php echo $row['update_user_name'] ?>
                                                    </td>
                                                    <td>
                                                        <?php echo view_dateformat($row['update_date']) ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                            if( $member['mb_level'] >= 8 ||  $row['ptn_deptno'] == $member['mb_deptno']) {
                                                                $ptncnt_sql = "
                                                                select count(*) as cnt
                                                                from {$g5['member_table']}
                                                                where mb_gubun != 'E'
                                                                and is_login = 'Y'
                                                                and mb_ptnidx = {$row['ptn_idx']}
                                                                ";
                                                                $ptncnt = sql_fetch($ptncnt_sql);
                                                                $cnt = (int) $ptncnt['cnt'];
                                                                
                                                                if( $cnt>0 ) {
                                                                    echo '<button type="button" class="btn btn-info btn-xs listbtn" data-ptn="'.$row['ptn_idx'].'">보기</button>';
                                                                }
                                                            }

                                                        ?>
                                                    </td>
                                                    
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-center justify-content-sm-end">
                                    <?php echo get_paging_bootst(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'] . '?' . $qstr . '&amp;page='); ?>
                                </div>
                                
                            </form>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="modal-addPtnEmp">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h4 class="modal-title">admin접속 계정추가</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="modalForm" name="modalForm" action="./partner_list_update" method="post" onsubmit="return modalForm_submit(this);">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="">고객사</label>
                        <select name="mb_ptnidx" id="mb_ptnidx" class="form-control selectpicker" data-live-search="true" required="true" data-size="10"></select>
                    </div>
                    <div class="form-group">
                        <label for="name">아이디</label>
                        <input type="text" id="mb_id" name="mb_id" class="form-control" minlength="3" maxlength="10" oninput="this.value = this.value.replace(/[^0-9a-z.]/g, '').replace(/(\..*)\./g, '$1');" required disabled>
                    </div>
                    <div class="form-group">
                        <label for="name">패스워드</label>
                        <input type="password" id="mb_password" name="mb_password" class="form-control" minlength="6" maxlength="15" required>
                    </div>
                    <div class="form-group">
                        <label for="name">이름</label>
                        <input type="text" id="mb_name" name="mb_name" class="form-control" minlength="2" maxlength="5" required>
                    </div>
                    <div class="form-group">
                        <label for="name">휴대전화</label>
                        <input type="text" id="mb_hp" name="mb_hp" class="form-control" oninput="telHyphen(this);" minlength="13" maxlength="13" required>
                    </div>
                    <div class="form-group">
                        <label for="name">구분</label>
                        <select name="mb_gubun" id="mb_gubun" class="form-control custom-select">
                            <option value="P">대표</option>
                            <option value="C">직원</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">닫기</button>
                    <button type="submit" class="btn btn-primary" name="act_button" value="직원추가">저장</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .modal-body td {
        width: 50px; /* or whatever width you want */
    }
    .modal-body {
        font-size: 12px;
    }
    .modal-body .special-td {
        width: 50px; /* or whatever width you want for this specific <td> */
    }
    .modal-title {
        font-size: 16px;
    }

    /* Custom styles */
    .modal-table-min-width {
        min-width: 800px; /* Adjust as necessary */
    }

    .modal input[type="text"], .modal select.form-control {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }

    .modal .btn-xs {
        font-size: 0.6rem;
        padding: 0.25rem 0.5rem;
    }
}


</style>
<div class="modal fade" id="modal-listPtnEmp">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header bg-success">
        <h4 class="modal-title">직원리스트</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        
        <div class="table-responsive">
            <table class="table table-striped table-bordered dt-responsive nowrap modal-table-min-width">
            <thead>
            <tr>
                <th>NO</th>
                <th>아이디</th>
                <th>이름</th>
                <th>가입일</th>
                <th>연락처(DB알람)</th>
                <th>직급</th>
                <th>관리</th>
            </tr>
            </thead>
            <tbody id="dynamic-tbody">
            
            </tbody>
        </table>
        </div>
    </div>

    <!-- Modal footer -->
    <div class="modal-footer">
    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
    </div>

    </div>
  </div>
</div>




<script>
    
    $(function() {

        var flag = '<?php echo $sfl ?>';

        if(flag == "") {
            $('#search_keyword1').removeClass("d-none");
            $('#search_keyword2').addClass("d-none");
            $('#search_keyword3').selectpicker('hide');
            $('#search_keyword4').addClass("d-none");

            $('#search_keyword1').attr("disabled", false);
            $('#search_keyword2').attr("disabled", true);
            $('#search_keyword3').attr("disabled", true);
            $('#search_keyword4').attr("disabled", true);
        } else if(flag != "" && flag == "ptn_nm") {
            $('#search_keyword1').removeClass("d-none");
            $('#search_keyword2').addClass("d-none");
            $('#search_keyword3').selectpicker('hide');
            $('#search_keyword4').addClass("d-none");

            $('#search_keyword1').attr("disabled", false);
            $('#search_keyword2').attr("disabled", true);
            $('#search_keyword3').attr("disabled", true);
            $('#search_keyword4').attr("disabled", true);
        } else if(flag != "" && flag == "cate_code") {
            $('#search_keyword1').addClass("d-none");
            $('#search_keyword2').removeClass("d-none");
            $('#search_keyword3').selectpicker('hide');
            $('#search_keyword4').addClass("d-none");

            $('#search_keyword1').attr("disabled", true);
            $('#search_keyword2').attr("disabled", false);
            $('#search_keyword3').attr("disabled", true);
            $('#search_keyword4').attr("disabled", true);

        } else if(flag != "" && flag == "deptno") {
            $('#search_keyword1').addClass("d-none");
            $('#search_keyword2').addClass("d-none");
            $('#search_keyword3').selectpicker('show');
            $('#search_keyword4').addClass("d-none");

            $('#search_keyword1').attr("disabled", true);
            $('#search_keyword2').attr("disabled", true);
            $('#search_keyword3').attr("disabled", false);
            $('#search_keyword4').attr("disabled", true);

        } else if(flag != "" && flag == "ptn_status") {

            $('#search_keyword1').addClass("d-none");
            $('#search_keyword2').addClass("d-none");
            $('#search_keyword3').selectpicker('hide');
            $('#search_keyword4').removeClass("d-none");

            $('#search_keyword1').attr("disabled", true);
            $('#search_keyword2').attr("disabled", true);
            $('#search_keyword3').attr("disabled", true);
            $('#search_keyword4').attr("disabled", false);
        }


        $("#sfl").change(function () {

            var obj = $(this).val();
            if(obj == "ptn_nm") {
                $('#search_keyword1').removeClass("d-none");
                $('#search_keyword2').addClass("d-none");
                $('#search_keyword3').selectpicker('hide');
                $('#search_keyword4').addClass("d-none");

                $('#search_keyword1').attr("disabled", false);
                $('#search_keyword2').attr("disabled", true);
                $('#search_keyword3').attr("disabled", true);
                $('#search_keyword4').attr("disabled", true);
                
            } else if(obj == "cate_code") {
                $('#search_keyword1').addClass("d-none");
                $('#search_keyword2').removeClass("d-none");
                $('#search_keyword3').selectpicker('hide');
                $('#search_keyword4').addClass("d-none");

                $('#search_keyword1').attr("disabled", true);
                $('#search_keyword2').attr("disabled", false);
                $('#search_keyword3').attr("disabled", true);
                $('#search_keyword4').attr("disabled", true);

            } else if(obj == "deptno") {
                $('#search_keyword1').addClass("d-none");
                $('#search_keyword2').addClass("d-none");
                $('#search_keyword3').selectpicker('show');
                $('#search_keyword4').addClass("d-none");

                $('#search_keyword1').attr("disabled", true);
                $('#search_keyword2').attr("disabled", true);
                $('#search_keyword3').attr("disabled", false);
                $('#search_keyword4').attr("disabled", true);

            } else if(obj == "ptn_status") {
                $('#search_keyword1').addClass("d-none");
                $('#search_keyword2').addClass("d-none");
                $('#search_keyword3').selectpicker('hide');
                $('#search_keyword4').removeClass("d-none");

                $('#search_keyword1').attr("disabled", true);
                $('#search_keyword2').attr("disabled", true);
                $('#search_keyword3').attr("disabled", true);
                $('#search_keyword4').attr("disabled", false);
            }
        });



        //modal 1
        $('#addPtnEmp').on('click',function(){

            var act = "modalAddPtnEmp";
            var $target = $("#mb_ptnidx");
            var deptno = '<?php echo $member['mb_deptno']?>';
            $target.empty();

            $("#mb_id").attr("disabled",false); 

            $.ajax({
                type: "post",
                data: {
                    deptno:deptno,
                    act: act
                },
                url: "partner_ajax",
                dataType: "json",
                success:function(result) {
                    //$target.append(result);
                    $target.append(result);
                    $target.selectpicker("refresh");
                    
                    $('#modal-addPtnEmp').modal("show");
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    return;
                }
            });
        });

        //modal 2
        $(".listbtn").on('click', function() { 
            var ptn_idx = $(this).data('ptn');

            $("#dynamic-tbody").empty();

            var act = "modalListPtnEmp";
            var ptn_idx = ptn_idx;

            $.ajax({
                type: "post",
                data: {
                    ptn_idx:ptn_idx,
                    act: act
                },
                url: "partner_ajax",
                dataType: "json",
                success:function(result) {
                    
                    $("#dynamic-tbody").append(result);

                    $('#modal-listPtnEmp').modal("show");
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    return;
                }
            });

        });

        //고객명 중복체크
        $("#mb_id").change(function () {
            if ($(this).val() != "") {
                var len = $(this).val().length;
                if (len <= 2) {
                    alert("고객ID 3글자이상이어야 합니다");
                    return false;
                }
                var ptn_id = $(this).val();
                var act = "dup_member2";
                $.ajax({
                    type: "post",
                    url: "partner_ajax",
                    data: {
                        ptn_id: ptn_id ,
                        act: act
                    },
                    success: function (result) {
                        
                        //alert(result.employeeCnt);//JSON.stringify(result)
                        if (result == 0) {//중복ID가 존재하지 않으면
                            $("#btn_insert").attr("disabled", false);
                            $("#btn_insert").css("opacity", "1");
                            $("#mb_id").attr('class', 'form-control is-valid');

                        } else {

                            alert("["+ptn_id + "] 고객명 중복");
                            $("#btn_insert").attr("disabled", true);
                            $("#btn_insert").css("opacity", "0.5");

                            $("#mb_id").val("");
                            $("#mb_id").attr('class', 'form-control is-invalid');
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
        
        $('[data-toggle="tooltip"]').tooltip();
        
        var table = $('#tbl_partner').DataTable({
            "paging": false,
            "searching": false,
            "ordering": false,
            "info": false,
            "autoWidth": true,
            columnDefs:[
                {responsivePriority : 0     , targets: 0, "width":"1%"},
                {responsivePriority : 108   , targets: 1, "width":"2%"},
                {responsivePriority : 1     , targets: 2},
                {responsivePriority : 102   , targets: 3, "width":"1%"},
                {responsivePriority : 103   , targets: 4, "width":"1%"},
                {responsivePriority : 104   , targets: 5},
                {responsivePriority : 105   , targets: 6},
                {responsivePriority : 106   , targets: 7},
                {responsivePriority : 107   , targets: 8},
                {responsivePriority : 107   , targets: 9},
                {responsivePriority : 108   , targets: 10},
                {responsivePriority : 109   , targets: 11},
                {responsivePriority : 110   , targets: 12,"width":"1%"},
                //{responsivePriority : 3     , targets: 10, "width":"5%"},
            ]            
        });
        

        //$(document).on('change', '#upd_mb_hp', function() {
        //$(document).on('change', 'tr td input, tr td select', function() {

        $(document).on('change', '#dynamic-tbody tr td input, #dynamic-tbody tr td select', function() {
            // var changedElement = $(this); // 변경된 요소
            // var closestTR = $(this).closest('tr'); // 해당 요소를 포함하고 있는 가장 가까운 tr
            // var eventID = changedElement.attr('id') ;

            // alert(eventID);
            // return false;
            var mb_id = $(this).closest('tr').find('td:eq(1)').text();
            var mb_name = $(this).closest('tr').find('td:eq(2)').find('input').val();
            var mb_hp = $(this).closest('tr').find('td:eq(4)').find('input').val();
            var mb_gubun = $(this).closest('tr').find('td:eq(5)').find('select').val();
            var act = 'upd_mb_hp';
            
            if (mb_hp.length !== 13) {
                alert('연락처 13자리 확인해주세요.(ex:010-xxxx-xxxx)');
                return false;
            } else {
                //ajax 호출
                result = confirm("저장하시겠습니까?");
                if(result){
                    $.ajax({
                        type: "post",
                        data: {
                            mb_id:mb_id,
                            mb_name:mb_name,
                            mb_hp:mb_hp,
                            mb_gubun:mb_gubun,
                            act: act
                        },
                        url: "partner_ajax",
                        dataType: "json",
                        success:function(result) {
                            alert(mb_name + '님 정보가 저장되었습니다.');
                        },
                        error: function(xhr) {
                            console.log(xhr.responseText);
                            alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                            return;
                        }
                    });

                    
                } else {
                    // $("#upd_mb_hp").val(mb_hp);
                    // $("#upd_mb_name").val(mb_name);
                    // $("#upd_mb_gubun").val(upd_mb_gubun);
                    //$('#upd_mb_gubun').val(mb_gubun).trigger('change');

                    
                }
            }
        });

    });

    function listForm_submit(f) {
        
    }
    function modalForm_submit(f) {
        var mb_ptnidx = modalForm.mb_ptnidx.value;
        if(mb_ptnidx == "" || mb_ptnidx == null) {
            alert("고객사 필수선택");
            return false;
        }
    }
    
    function initPw(param1) {

        var mb_password = prompt("초기화 비밀번호 입력해주세요.");
        if(mb_password == null)
        {
            return false;
        }
        if(mb_password.length < 6 && mb_password.length > 12) {
            alert('비밀번호는 6자리 이상으로 12자리 이하 설정가능합니다.');
        }
        var hangulPattern = /[ㄱ-ㅎ|ㅏ-ㅣ|가-힣]/;
        if(hangulPattern.test(mb_password)) {
            alert('비밀번호에 한글을 포함할 수 없습니다.');
            return false;
        }


        var mb_id = param1;
        var mb_password = mb_password;
        var act = "initPtnEmpPW";

        $.ajax({
            type: "post",
            data: {
                mb_id:mb_id,
                mb_password:mb_password,
                act: act
            },
            url: "partner_ajax",
            dataType: "json",
            success:function(result) {
                alert(result);
                $('#modal-listPtnEmp').modal("hide");
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                return;
            }
        });


    }

    function delPtnEmp(param){
        var confirm1 = confirm("삭제하시겠습니까?");

        if(confirm1){

            var mb_no = param;
            var act = "delPtnEmp";

            $.ajax({
                type: "post",
                data: {
                    mb_no:mb_no,
                    act: act
                },
                url: "partner_ajax",
                dataType: "json",
                success:function(result) {
                    alert(result);
                    $('#modal-listPtnEmp').modal("hide");
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    return;
                }
            });
            
        }
    }
</script>


<?php
include_once(G5_PATH . '/tail.php');
