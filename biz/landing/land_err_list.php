<?php
require_once '../../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');

$g5['title'] = "중복 DB";
include_once(G5_PATH . '/head.php');

$sql_columns    = "";
$sql_common     = "";
$sql_search     = "";
$sql_gruop      = "";
$sql_order      = "";
$total_count    = 0;

$dataTbl = "visible: false,";

//select
$sql_columns = "
  a.land_idx 
, a.land_pg_idx
, a.land_used_data
, b.pg_memo
, b.pg_domain 
, b.pg_uri 
, b.pg_des_idx 
, c.design_name 
, a.land_ptn_idx
, d.ptn_nm
, ifnull (f_get_mb_name(b.pg_mb_ptn), b.pg_mb_ptn) as mb_ptn_name
, a.land_deptno
, e.deptnm 
, b.pg_mb_emp 
, ifnull (f_get_mb_name(b.pg_mb_emp), b.pg_mb_emp) as mb_emp_name
, a.name 
, convert(aes_decrypt(unhex(tel), 'withus_secret_key') using utf8) as tel
, case a.db_status
    when '1' then '부재'
    when '2' then '불량'
    when '3' then '거절'
    when '4' then '리콜'
    when '5' then '중복'
    when '6' then '유망'
    when '7' then '승인'
    else ''
    end as db_status
, a.land_memo
, a.insert_date 
, a.update_date 
, a.client_ip 
, a.utm_source
, b.pg_api_yn
, a.api_send_yn 
, a.inflow_path 
, a.inflow_env
";

$sql_common = "
from {$g5['crm_landing']} a
left join {$g5['crm_page']}     b on a.land_pg_idx = b.page_idx
left join {$g5['crm_design']}   c on b.pg_des_idx  = c.design_idx 
left join {$g5['crm_partner']}  d on a.land_ptn_idx  = d.ptn_idx
left join {$g5['crm_depart']}   e on b.pg_deptno   = e.deptno
";

$add_cont = "";

if ($member['mb_deptno'] != "9") {
    if ($member['mb_level'] <= 6) {

        if ($member['mb_level'] == 4) {
            $add_cont = "and a.land_empno = {$member['mb_no']}";
        } else {
            $add_cont = "and a.land_deptno = {$member['mb_deptno']}";
        }
    }
}

$sql_search = "
where 1=1
and a.use_yn = 'E'
{$add_cont}
";

//search
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "ptn_idx":
            $sql_search .= " a.land_ptn_idx = $stx";
            break;

        case "mb_no":
            $sql_search .= " a.land_empno = '$stx'";
            break;
            
        case "pg_uri":
            //$split = implode( ',', $stx );
            //$split = str_replace("," , "','", $split);
            //$sql_search .= "and b.pg_uri in ('$split') ";
            $sql_search .= " pg_uri = '$stx'";
            break;

        case "insert_date":
            $from = substr($stx,0,10);
            $to   = substr($stx,10,10);
            $sql_search .= "a.$sfl between '{$from} 00:00:00.000' and '{$to} 23:59:59.999'";
            $stx = $from."~".$to;
            break;

        case "tel":
            $sql_search .= " tel = HEX(AES_ENCRYPT('{$stx}', 'withus_secret_key')) ";
            break;
        case "utm_source":
            $sql_search .= " utm_source = '$stx' ";
            break;
    }
    $sql_search .= " ) ";
}

if($sfl == "pg_uri") {
    $add_join = "left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx ";
}

$cnt_sql = "
select count(*) as cnt
from {$g5['crm_landing']} a
{$add_join}
{$sql_search}
";
$row = sql_fetch($cnt_sql);
$total_count = $row['cnt'];

//$rows = $config['cf_page_rows'];
$rows = 50;
$total_page  = ceil($total_count / $rows);
if ($page < 1) {
    $page = 1;
}
$from_record = ($page - 1) * $rows;

if (!$sst) {
    //$sql_order = "order by a.land_idx desc";
    $sql_order = "order by a.land_idx desc";
} else {
    $sql_order = " order by $sst $sod ";
}

$sql = " select {$sql_columns} {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);


//고객사코드
if ($member['mb_level'] <= 6) {

    if ($member['mb_level'] == 4) {
        $add_cond = "and ptn_mb_emp  = {$member['mb_no']}";
    } else {
        $add_cond = "and ptn_deptno = {$member['mb_deptno']}";
    }
}
$partner_sql = "
select ptn_idx
    , ptn_nm
from {$g5['crm_partner']} 
where use_yn = 'Y'
$add_cond
order by ptn_idx desc
";
$partner_list = sql_query($partner_sql);

if($member['mb_level'] >= 5) {
    $emp_sql = "
    select mb_no
         , mb_name
         , ifnull((select count(*) from {$g5['crm_landing']} sub where a.mb_no = sub.land_empno and sub.use_yn = 'E' and insert_date2 = curdate() group by land_empno) ,0) as today_cnt
    from {$g5['member_table']} a
    where 1=1
    and is_login = 'Y'
    and mb_deptno = {$member['mb_deptno']}
    order by mb_name
    ";
    $emp_list = sql_query($emp_sql);
}
    
//URI 코드
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
 where a.use_yn = 'E'
 $add_cond
group by b.pg_uri
order by page_idx desc
";
$code_list = sql_query($code_sql);

$rownum = $total_count - $from_record;


?>
<link rel="stylesheet" href="<?php echo G5_THEME_URL ?>/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
<link rel="stylesheet" href="<?php echo G5_THEME_URL ?>/plugins/daterangepicker/daterangepicker.css">

<script src="<?php echo G5_THEME_URL ?>/plugins/moment/moment.min.js"></script>
<script src="<?php echo G5_THEME_URL ?>/plugins/daterangepicker/daterangepicker.js"></script>
<!-- <script src="<?php echo G5_THEME_URL ?>/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script> -->

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">중복 DB 조회(<?php echo number_format($total_count) ?>)</h3>
                    </div>
                    <div class="card-body">
                        <div class="dataTables_wrapper dt-bootstrap4">

                            <div class="d-flex flex-sm-row flex-column justify-content-sm-between">
                                <div class="d-flex justify-content-center mb-2 mb-xs-0">
                                    <div class="btn-group xs-100">
                                        <button type="submit" form="listForm" class="btn btn-primary btn-sm border border-dark" name="act_button" value="정상처리"><i class="fas fa-eraser"></i>정상</button>
                                        <button type="submit" form="listForm" class="btn btn-danger btn-xs border border-dark" name="act_button" value="선택삭제"><i class="far fa-trash-alt"></i>삭제</button>
                                        <button type="button" class="btn btn-success btn-xs border border-dark" data-toggle="modal" data-target="#modal-exc-down">
                                            <i class="fas fa-file-download"></i> 엑셀다운
                                        </button>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <div class="btn-group xs-100">

                                        <form class="form-inline my-2 my-lg-0 ng-pristine ng-valid">
                                            <select name="sfl" id="sfl" class="border border-dark custom-select">
                                                <option value="ptn_idx" <?php echo get_selected($sfl, "ptn_idx"); ?>>고객사</option>
                                                <option value="mb_no" <?php echo get_selected($sfl, "mb_no"); ?>>담당자</option>
                                                <option value="pg_uri" <?php echo get_selected($sfl, "pg_uri"); ?>>코드</option>
                                                <option value="insert_date" <?php echo get_selected($sfl, "insert_date"); ?>>접수일시</option>
                                                <option value="tel" <?php echo get_selected($sfl, "tel"); ?>>연락처</option>
                                                <option value="utm_source" <?php echo get_selected($sfl, "utm_source"); ?>>UTM</option>
                                            </select>
                                            <select id="search_ptn_idx" name="stx" class="form-control selectpicker" data-live-search="true" data-style="border border-secondary" data-width="200px" data-size="10">
                                                <option value="">미선택</option>
                                                <?php for ($i = 0; $partner = sql_fetch_array($partner_list); $i++) { ?>
                                                    <option value="<?php echo $partner['ptn_idx'] ?>" data-tokens="<?php echo $partner['ptn_nm'] ?>" <?php echo  $sfl == "ptn_idx" ? get_selected($stx, $partner['ptn_idx']) : '' ?>><?php echo $partner['ptn_nm'] ?></option>
                                                <?php } ?>
                                            </select>
                                            <?php if($member['mb_level'] >= 5) { ?>
                                            <select id="search_emp_idx" name="stx" class="form-control selectpicker" data-live-search="true" data-style="border border-secondary" data-width="200px" data-size="10">
                                                <option value="">미선택</option>
                                                <?php for ($i = 0; $emp = sql_fetch_array($emp_list); $i++) { ?>
                                                    <option value="<?php echo $emp['mb_no'] ?>" data-tokens="<?php echo $emp['mb_name'] ?>" <?php echo  $sfl == "mb_no" ? get_selected($stx, $emp['mb_no']) : '' ?>><?php echo $emp['mb_name'] . "(D:". $emp['today_cnt'] .")"?></option>
                                                <?php } ?>
                                            </select>
                                            <?php } ?>
                                            <select id="search_pg_uri" name="stx" class="selectpicker form-control" data-live-search="true" data-style="border border-secondary" data-width="200px" data-size="10">
                                                <option value="">미선택</option>
                                                <?php for ($i = 0; $code = sql_fetch_array($code_list); $i++) { ?>
                                                    <option value="<?php echo $code['pg_uri'] ?>" data-tokens="<?php echo $code['pg_uri'] ?>" <?php echo  $sfl == "pg_uri" ? get_selected($stx, $code['pg_uri']) : '' ?>><?php echo $code['pg_uri'] ?></option>
                                                <?php } ?>
                                            </select>
                                            <input type="text" id="search_fromto" name="stx" value="<?php echo $sfl == "insert_date" ? $stx : '' ?>" class="form-control sm-1" style="width:210px">
                                            <input type="text" id="search_phone" name="stx" value="<?php echo $sfl == "tel" ? $stx : '' ?>" class="form-control sm-1" placeholder="연락처 검색" aria-label="검색어" oninput="telHyphen(this);" minlength="13" maxlength="13">
                                            <input type="text" id="search_utm" name="stx" value="<?php echo $sfl == "utm_source" ? $stx : '' ?>" class="form-control sm-1" placeholder="utm value">
                                            <button type="submit" class="btn btn-outline-success my-2 my-sm-0">검색</button>
                                        </form>
                                    </div>
                                </div>
                            </div>


                            <form name="listForm" id="listForm" action="./land_list_update" onsubmit="return listForm_submit(this);" method="post">
                                <input type="hidden" name="sst" value="<?php echo $sst ?>">
                                <input type="hidden" name="sod" value="<?php echo $sod ?>">
                                <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
                                <input type="hidden" name="stx" value="<?php echo $stx ?>">
                                <input type="hidden" name="page" value="<?php echo $page ?>">
                                <input type="hidden" name="token" value="<?php echo isset($token) ? $token : ''; ?>">

                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="tbl_land" class="table table-striped table-bordered dt-responsive nowrap landpg-font-size" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)"></th>
                                                    <th>NO</th>
                                                    <th>이름</th>
                                                    <th>핸드폰번호</th>
                                                    <th><?php echo $sfl == "ptn_idx" && $stx != "" ? get_sort_bootst('a.land_pg_idx', '', 'desc', $sst, $sod, '페이지') : "페이지" ?></th>
                                                    <th>디자인</th>
                                                    <th>고객사</th>
                                                    <th>코드</th>
                                                    <th>담당자</th>
                                                    <th>고객메모</th>
                                                    <th>접수일시</th>
                                                    <th>수정일시</th>
                                                    <th>접수IP</th>
                                                    <th class="text-center">API</th>
                                                    <th>유입</th>
                                                    <th>utm</th>
                                                    <th>환경</th>
                                                    <th>DB사용</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php for ($i = 0; $row = sql_fetch_array($result); $i++) { ?>
                                                    <tr>
                                                        <td>
                                                            <input type="hidden" name="land_idx[<?php echo $i ?>]" value="<?php echo $row['land_idx'] ?>">
                                                            <input type="hidden" name="land_pg_idx[<?php echo $i ?>]" value="<?php echo $row['land_pg_idx'] ?>">
                                                            <input type="hidden" name="land_ptn_idx[<?php echo $i ?>]" value="<?php echo $row['land_ptn_idx'] ?>">
                                                            <?php echo isCheckbox($i, $row['land_deptno'], $row['pg_mb_emp'], $member); ?>
                                                        </td>
                                                        <td>
                                                            <a href="land_form?w=u&land_idx=<?php echo $row['land_idx'] . $qstr ?>"><?php echo $rownum; $rownum = $rownum - 1 ?></a>
                                                        </td>
                                                        <td>
                                                            <a href="land_form?w=u&land_idx=<?php echo $row['land_idx'] . $qstr ?>"><?php echo $row['name'] == "" ? 'N/A' : $row['name'] ?></a>
                                                        </td>
                                                        <td>
                                                            <a href="land_form?w=u&land_idx=<?php echo $row['land_idx'] . $qstr ?>"><?php echo $row['tel'] ?></a>
                                                        </td>

                                                        <td>
                                                            <?php echo $row['pg_memo'] ?><a href="<?php echo G5_BIZ_URL ?>/page/page_form?w=u&page_idx=<?php echo $row['land_pg_idx'] ?>"> <i class="fas fa-link"></i></a>
                                                        </td>

                                                        <td>
                                                            <?php echo $row['design_name'] ?><a href="<?php echo G5_BIZ_URL ?>/design/design_form?w=u&design_idx=<?php echo $row['pg_des_idx'] ?>"> <i class="fas fa-link"></i></a>
                                                        </td>

                                                        <td>
                                                            <a href="<?php echo G5_BIZ_URL ?>/landing/land_err_list?sfl=ptn_idx&stx=<?php echo $row['land_ptn_idx'] ?>" target="_self">
                                                                <?php echo "[" . $row['ptn_nm'] . "]" . $row['mb_ptn_name'] ?>
                                                            </a>
                                                            <a href="<?php echo G5_BIZ_URL ?>/partner/partner_form?w=u&ptn_idx=<?php echo $row['land_ptn_idx'] ?>"> <i class="fas fa-link"></i></a>
                                                        </td>

                                                        <td>
                                                            <a href="<?php echo G5_BIZ_URL ?>/landing/land_err_list?sfl=pg_uri&stx=<?php echo $row['pg_uri'] ?>" target="_self">
                                                                <?php echo $row['pg_uri'] ?>
                                                            </a>
                                                            <a href="http://<?php echo $row['pg_domain'] . '/' . $row['pg_uri'] ?>" target="_blank"><i class="fas fa-plane-departure text-success"></i></a>
                                                        </td>
                                                        <td>
                                                            <?php echo $row['mb_emp_name'] ?>
                                                        </td>
                                                        <td>
                                                            <input type="text" id="land_memo" name="land_memo[]" class="custom_select w-100" value="<?php echo $row['db_status'] . ($row['land_memo'] == "" ? "" : "-" . $row['land_memo']); ?>" readonly>
                                                        </td>
                                                        <td>
                                                            <?php echo view_dateformat($row['insert_date']) ?>
                                                        </td>
                                                        <td>
                                                            <?php echo view_dateformat($row['update_date']) ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $row['client_ip'] ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php
                                                            $icon = "";
                                                            if ($row['pg_api_yn'] == "Y") {
                                                                if ($row['api_send_yn'] == "Y") {
                                                                    $icon = "<i class='fas fa-cloud text-success' data-toggle='tooltip' data-placement='bottom' title='성공'></i>";
                                                                } else if ($row['api_send_yn'] == "N") {
                                                                    $icon = "<i class='fas fa-exclamation-triangle text-danger data-toggle='tooltip' data-placement='bottom' title='실패'></i>";
                                                                } else if ($row['api_send_yn'] == "D") {
                                                                    $icon = "<i class='fas fa-copy text-warning data-toggle='tooltip' data-placement='bottom' title='중복'></i>";
                                                                } else if ($row['api_send_yn'] == "H") {
                                                                    $icon = "<i class='fas fa-pause' data-toggle='tooltip' data-placement='bottom' title='대기'></i>";
                                                                }
                                                            }
                                                            echo $icon;
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $row['inflow_path'] ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $row['utm_source'] ?>
                                                        </td>

                                                        <td class="text-center">
                                                            <?php
                                                            $icon = "";
                                                            if ($row['inflow_env'] == "P") {
                                                                $icon = "<i class='fas fa-desktop'></i>";
                                                            } else if ($row['inflow_env'] == "M") {
                                                                $icon = "<i class='fas fa-mobile-alt'></i>";
                                                            } else {
                                                                $icon = "";
                                                            }
                                                            echo $icon;
                                                            ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <input type='checkbox' id='land_used_data' name='land_used_data' <?php echo $row['land_used_data'] == "Y" ? 'checked readonly' : '' ?>>
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

<div class="modal fade" id="modal-exc-down" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h4 class="modal-title">중복DB 엑셀 다운로드</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="modal_form" name="modal_form" action="./land_exceldown2" method="post" onSubmit="return validateForm()">
                <input type="hidden" name="use_yn" value="E">
                <div class="modal-body">
                    <div class="form-group clearfix">
                        <div class="icheck-primary d-inline float-left">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                <input type="checkbox" class="custom-control-input" id="curr_yn" name="curr_yn">
                                <label class="custom-control-label" for="curr_yn">최신화처리</label>
                            </div>
                        </div>
                        <div class="icheck-danger d-inline float-right">
                            <input type="radio" id="rdo_collect" name="rdo_data" value="collect" disabled>
                            <label for="rdo_collect">취합양식</label>
                        </div>
                        <div class="icheck-primary d-inline float-right">
                            <input type="radio" id="rdo_normal" name="rdo_data" value="normal" checked>
                            <label for="rdo_normal"> 일반양식</label>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="inputGroup-sizing-default">고객사</span>
                        </div>
                        <select id="ptn_idx_xls" name="ptn_idx_xls" class="selectpicker form-control" data-style="border border-secondary" data-live-search="true" data-size="10">
                        </select>
                    </div>
                    <div id="cond_currdate" class="input-group mb-3 d-none">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="inputGroup-sizing-default">최신화일시</span>
                        </div>
                        <input type="text" class="form-control" id="curr_datetime" name="curr_datetime">
                    </div>
                    <div id="cond_page" class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="inputGroup-sizing-default">페이지</span>
                        </div>
                        <select id="pg_uri" name="pg_uri[]" class="selectpicker form-control" data-style="border border-secondary" data-selected-text-format="count" data-live-search="true" multiple>
                        </select>
                    </div>
                    <div id="cond_date" class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="inputGroup-sizing-default">시작&종료일</span>
                        </div>
                        <input class="form-control" id="fromDate" name="fromDate" readonly>
                        <input class="form-control" id="toDate" name="toDate" readonly>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">닫기</button>
                    <button type="submit" class="btn btn-primary" name="act_button" value="다운">다운</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(function() {
        
        var flag = '<?php echo $sfl ?>';

        if(flag == "") {
            $('#search_ptn_idx').selectpicker('show');
            $('#search_ptn_idx').attr("disabled" , false);
            $('#search_emp_idx').selectpicker('hide');
            $('#search_emp_idx').attr("disabled"  , true);
            $('#search_pg_uri').selectpicker('hide');
            $('#search_pg_uri').attr("disabled"  , true);
            $('#search_fromto').addClass("d-none");
            $('#search_fromto').attr("disabled"    , true);
            $('#search_phone').addClass("d-none");
            $('#search_phone').attr("disabled"   , true);
            $('#search_utm').addClass("d-none");
            $('#search_utm').attr("disabled"   , true);
        }
        else if(flag != "" && flag == "ptn_idx") {
            $('#search_ptn_idx').selectpicker('show');
            $('#search_ptn_idx').attr("disabled" , false);
            $('#search_emp_idx').selectpicker('hide');
            $('#search_emp_idx').attr("disabled"  , true);
            $('#search_pg_uri').selectpicker('hide');
            $('#search_pg_uri').attr("disabled"  , true);
            $('#search_fromto').addClass("d-none");
            $('#search_fromto').attr("disabled"    , true);
            $('#search_phone').addClass("d-none");
            $('#search_phone').attr("disabled"   , true);
            $('#search_utm').addClass("d-none");
            $('#search_utm').attr("disabled"   , true);
        }
        else if(flag != "" && flag == "mb_no") {
            $('#search_ptn_idx').selectpicker('hide');
            $('#search_ptn_idx').attr("disabled" , true);
            $('#search_emp_idx').selectpicker('show');
            $('#search_emp_idx').attr("disabled" , false);
            $('#search_pg_uri').selectpicker('hide');
            $('#search_pg_uri').attr("disabled"  , true);
            $('#search_fromto').addClass("d-none");
            $('#search_fromto').attr("disabled"    , true);
            $('#search_phone').addClass("d-none");
            $('#search_phone').attr("disabled"   , true);
            $('#search_utm').addClass("d-none");
            $('#search_utm').attr("disabled"   , true);
        }
        else if(flag != "" && flag == "pg_uri") {
            $('#search_ptn_idx').selectpicker('hide');
            $('#search_ptn_idx').attr("disabled" , true);
            $('#search_emp_idx').selectpicker('hide');
            $('#search_emp_idx').attr("disabled" , true);
            $('#search_pg_uri').selectpicker('show');
            $('#search_pg_uri').attr("disabled"  , false);
            $('#search_fromto').addClass("d-none");
            $('#search_fromto').attr("disabled"    , true);
            $('#search_phone').addClass("d-none");
            $('#search_phone').attr("disabled"   , true);
            $('#search_utm').addClass("d-none");
            $('#search_utm').attr("disabled"   , true);
        } 
        else if(flag != "" && flag == "insert_date") {
            $('#search_ptn_idx').selectpicker('hide');
            $('#search_ptn_idx').attr("disabled" , true);
            $('#search_emp_idx').selectpicker('hide');
            $('#search_emp_idx').attr("disabled" , true);
            $('#search_pg_uri').selectpicker('hide');
            $('#search_pg_uri').attr("disabled"  , true);
            $('#search_fromto').removeClass("d-none");
            $('#search_fromto').attr("disabled"    , false);
            $('#search_phone').addClass("d-none");
            $('#search_phone').attr("disabled"   , true);
            $('#search_utm').addClass("d-none");
            $('#search_utm').attr("disabled"   , true);
            make_datepicker();
        }
        else if(flag != "" && flag == "tel") {
            $('#search_ptn_idx').selectpicker('hide');
            $('#search_ptn_idx').attr("disabled" , true);
            $('#search_emp_idx').selectpicker('hide');
            $('#search_emp_idx').attr("disabled" , true);
            $('#search_pg_uri').selectpicker('hide');
            $('#search_pg_uri').attr("disabled"  , true);
            $('#search_fromto').addClass("d-none");
            $('#search_fromto').attr("disabled"    , true);
            $('#search_phone').removeClass("d-none");
            $('#search_phone').attr("disabled"   , false);
            $('#search_utm').addClass("d-none");
            $('#search_utm').attr("disabled"   , true);
        } else if(flag != "" && flag == "utm_source") {
            $('#search_ptn_idx').selectpicker('hide');
            $('#search_ptn_idx').attr("disabled" , true);
            $('#search_emp_idx').selectpicker('hide');
            $('#search_emp_idx').attr("disabled" , true);
            $('#search_pg_uri').selectpicker('hide');
            $('#search_pg_uri').attr("disabled"  , true);
            $('#search_fromto').addClass("d-none");
            $('#search_fromto').attr("disabled"    , true);
            $('#search_phone').addClass("d-none");
            $('#search_phone').attr("disabled"   , true);
            $('#search_utm').removeClass("d-none");
            $('#search_utm').attr("disabled"   , false);
        }

        //조회조건 일자 변경
        $("#search_fromto").change(function(e) {
            var asis = this.value;;
            var tobe = asis.replace(' ~ ', '~');
            $("#search_fromto").val(tobe);
        });

        //조회조건 변경시
        $("#sfl").change(function () {

            var obj = $(this).val();
            if(obj == "ptn_idx") {
                $('#search_ptn_idx').selectpicker('show');
                $('#search_ptn_idx').attr("disabled" , false);
                $('#search_emp_idx').selectpicker('hide');
                $('#search_emp_idx').attr("disabled"  , true);
                $('#search_pg_uri').selectpicker('hide');
                $('#search_pg_uri').attr("disabled"  , true);
                $('#search_fromto').addClass("d-none");
                $('#search_fromto').attr("disabled"    , true);
                $('#search_phone').addClass("d-none");
                $('#search_phone').attr("disabled"   , true);
                $('#search_utm').addClass("d-none");
                $('#search_utm').attr("disabled"   , true);
                
            } else if(obj == "mb_no") {

                $('#search_ptn_idx').selectpicker('hide');
                $('#search_ptn_idx').attr("disabled" , true);
                $('#search_emp_idx').selectpicker('show');
                $('#search_emp_idx').attr("disabled"  , false);
                $('#search_pg_uri').selectpicker('hide');
                $('#search_pg_uri').attr("disabled" , true);
                $('#search_fromto').addClass("d-none");
                $('#search_fromto').attr("disabled"    , true);
                $('#search_phone').addClass("d-none");
                $('#search_phone').attr("disabled"   , true);
                $('#search_utm').addClass("d-none");
                $('#search_utm').attr("disabled"   , true);

            } else if(obj == "pg_uri") {

                $('#search_ptn_idx').selectpicker('hide');
                $('#search_ptn_idx').attr("disabled" , true);
                $('#search_emp_idx').selectpicker('hide');
                $('#search_emp_idx').attr("disabled"  , true);
                $('#search_pg_uri').selectpicker('show');
                $('#search_pg_uri').attr("disabled"  , false);
                $('#search_fromto').addClass("d-none");
                $('#search_fromto').attr("disabled"    , true);
                $('#search_phone').addClass("d-none");
                $('#search_phone').attr("disabled"   , true);
                $('#search_utm').addClass("d-none");
                $('#search_utm').attr("disabled"   , true);

            } else if(obj == "insert_date") {
                $('#search_ptn_idx').selectpicker('hide');
                $('#search_ptn_idx').attr("disabled" , true);
                $('#search_emp_idx').selectpicker('hide');
                $('#search_emp_idx').attr("disabled"  , true);
                $('#search_pg_uri').selectpicker('hide');
                $('#search_pg_uri').attr("disabled"  , true);
                $('#search_fromto').removeClass("d-none");
                $('#search_fromto').attr("disabled"    , false);
                $('#search_phone').addClass("d-none");
                $('#search_phone').attr("disabled"   , true);
                $("#search_fromDate").val("");
                $("#search_toDate").val("");
                $('#search_utm').addClass("d-none");
                $('#search_utm').attr("disabled"   , true);

                make_datepicker();
                
            } else if(obj == "tel") {
                $('#search_ptn_idx').selectpicker('hide');
                $('#search_ptn_idx').attr("disabled" , true);
                $('#search_emp_idx').selectpicker('hide');
                $('#search_emp_idx').attr("disabled"  , true);
                $('#search_pg_uri').selectpicker('hide');
                $('#search_pg_uri').attr("disabled"  , true);
                $('#search_fromto').addClass("d-none");
                $('#search_fromto').attr("disabled"    , true);
                $('#search_phone').removeClass("d-none");
                $('#search_phone').attr("disabled"   , false);
                $('#search_utm').addClass("d-none");
                $('#search_utm').attr("disabled"   , true);

            } else if(obj == "utm_source") {
                $('#search_ptn_idx').selectpicker('hide');
                $('#search_ptn_idx').attr("disabled" , true);
                $('#search_emp_idx').selectpicker('hide');
                $('#search_emp_idx').attr("disabled"  , true);
                $('#search_pg_uri').selectpicker('hide');
                $('#search_pg_uri').attr("disabled"  , true);
                $('#search_fromto').addClass("d-none");
                $('#search_fromto').attr("disabled"    , true);
                $('#search_phone').addClass("d-none");
                $('#search_phone').attr("disabled"   , true);
                $('#search_utm').removeClass("d-none");
                $('#search_utm').attr("disabled"   , false);
            }
        });

        //modal 고객사 onchange
        $("#ptn_idx_xls").change(function(e) {
            e.stopPropagation();
            $("#pg_uri").selectpicker("refresh");
            $('#pg_uri').selectpicker('val', '');
        });

        //modal 페이지 onchange
        $("#pg_uri").change(function(e) {

           e.stopPropagation();
            $('#ptn_idx_xls').val('default').selectpicker('deselectAll');
            $('#ptn_idx_xls').selectpicker('refresh');
        });
        
        //modal 최신화 onchange
        $('input[type=checkbox][name=curr_yn]').change(function() {
            if ($(this).is(':checked')) {
                $("#curr_yn").prop("checked", true) ;
                $("#rdo_collect").attr("disabled", false);
                
                $("#cond_currdate").removeClass("d-none");
                $("#cond_page").addClass("d-none");
                $("#cond_date").addClass("d-none");
                
                $("#fromDate").val("");
                $("#toDate").val("");
            } else {
                $("#rdo_collect").prop("checked", false) ;
                $("#rdo_normal").prop("checked", true) ;
                $("#rdo_collect").attr("disabled", true);
                
                $("#cond_currdate").addClass("d-none");
                $("#cond_page").removeClass("d-none");
                $("#cond_date").removeClass("d-none");
                
                var toDay = moment().format("YYYY-MM-DD");
                var fromDay = moment().subtract(1, 'month').format("YYYY-MM-DD");

                $("#fromDate").val(fromDay);
                $("#toDate").val(toDay);
            }
        });

        //show modal default setting
        $('#modal-exc-down').on('show.bs.modal', function (event) {
            
            var toDay = moment().format("YYYY-MM-DD");
            var fromDay = moment().subtract(1, 'month').format("YYYY-MM-DD");

            var dateNow = new Date();

            var ptn_idx = $('#search_ptn_idx').val();
            var search_pg_uri = $('#search_pg_uri').val();

            $("#curr_datetime").daterangepicker({
                timePicker: true,
                timePicker24Hour: true,
                timePickerIncrement: 1,
                singleDatePicker: true,
                startDate: moment().subtract(-1,'hour') ,
                locale: {
                    format: "YYYY-MM-DD HH:mm",
                    daysOfWeek: ["일", "월", "화", "수", "목", "금", "토"],
                    monthNames: ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"]
                },
            });
          
            $('#fromDate, #toDate').daterangepicker({
              
                locale: {
                    format: "YYYY-MM-DD",
                    daysOfWeek: ["일", "월", "화", "수", "목", "금", "토"],
                    monthNames: ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"]
                },
                "startDate": fromDay,
                "endDate": toDay,
                "minDate": moment().subtract(6, 'month'),
                //"maxDate": toDay,
                 autoApply: true,
                 autoUpdateInput: false,
            
            }, function(start, end, label) {
                end.format('YYYY-MM-DD') ;
                
                var selectedStartDate = start.format('YYYY-MM-DD'); // selected start
                var selectedEndDate = end.format('YYYY-MM-DD'); // selected end

                $checkinInput = $('#fromDate');
                $checkoutInput = $('#toDate');

                // Updating Fields with selected dates
                $checkinInput.val(selectedStartDate);
                $checkoutInput.val(selectedEndDate);

                // Setting the Selection of dates on calender on CHECKOUT FIELD (To get this it must be binded by Ids not Calss)
                var checkOutPicker = $checkoutInput.data('daterangepicker');
                checkOutPicker.setStartDate(selectedStartDate);
                checkOutPicker.setEndDate(selectedEndDate);

                // Setting the Selection of dates on calender on CHECKIN FIELD (To get this it must be binded by Ids not Calss)
                var checkInPicker = $checkinInput.data('daterangepicker');
                checkInPicker.setStartDate(selectedStartDate);
                checkInPicker.setEndDate(selectedEndDate);

            });
            $("#fromDate").val(fromDay);
            $("#toDate").val(toDay);
            
            var deptno = "<?php echo $member['mb_deptno']; ?>";
            var $target1 = $("#ptn_idx_xls");
            var $target2 = $("#pg_uri");

            var act = "modal-excel-down1";

            $target1.children().remove().end();
            $target1.selectpicker('refresh').empty();
            $target1.selectpicker();

            $target2.children().remove().end();
            $target2.selectpicker('refresh').empty();
            $target2.selectpicker();


            $.ajax({
                type: "post",
                data: {
                    deptno:deptno,
                    act: act
                },
                url: "land_ajax",
                dataType: "json",
                success:function(result) {
                    $target1.append(result[0]);
                    $target1.selectpicker("refresh");

                    $target2.append(result[1]);
                    $target2.selectpicker("refresh");
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    return;
                }
            });
        });
        
        $('#modal-exc-upload').on('show.bs.modal', function (event) {

            $("#apiDelay").find('option:first').prop('selected', true);
            
            var act = "modal-excel-upload";
            var deptno = "<?php echo $member['mb_deptno']; ?>";
            var $target = $("#modal-exc-upload #pg_uri");
            

            $target.children().remove().end();
            $target.selectpicker('refresh').empty();
            $target.selectpicker();

            $.ajax({
                type: "post",
                data: {
                    deptno:deptno,
                    act: act
                },
                url: "land_ajax",
                dataType: "json",
                success:function(result) {
                    $target.append(result);
                    $target.selectpicker("refresh");
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    return;
                }
            });

        });
        //datatable load
        var table = $('#tbl_land').DataTable({
            "paging": false,
            "searching": false,
            "ordering": false,
            "info": false,
            "autoWidth": true,
            "scrollY": true,
            "scrollX": true,
            "responsive": false,
            "details": false,
            "stateSave": true,
            "deferRender": true,
            "dom": 'BRlfrtip',
            "buttons": [
                'columnsToggle',
            ],
            columnDefs:[
                {responsivePriority : 0     , targets: 0, "width":"1%"},
                {responsivePriority : 1     , targets: 1, "width":"2%"},
                {responsivePriority : 2     , targets: 2},
                {responsivePriority : 102   , targets: 3},
                {responsivePriority : 103   , targets: 4},
                {responsivePriority : 104   , targets: 5},
                {responsivePriority : 105   , targets: 6},
                {responsivePriority : 106   , targets: 7},
                {responsivePriority : 107   , targets: 8, "width":"2%"},
                {responsivePriority : 108   , targets: 9, "width":"4%"},
                {responsivePriority : 109   , targets: 10},
                {responsivePriority : 110   , targets: 11, "width":"1%"},
                {responsivePriority : 110   , targets: 12, "width":"1%"},
                {responsivePriority : 111   , targets: 13, "width":"1%"},
                {responsivePriority : 112   , targets: 14, "width":"1%"},
                {responsivePriority : 113   , targets: 15, "width":"1%"},
                {responsivePriority : 114   , targets: 16, "width":"1%"},
            ]            
        });

        // 초기 상태 로딩: localStorage에서 열 상태 가져오기
        var savedColumnStates = localStorage.getItem('columnStates');
        if (savedColumnStates) {
            savedColumnStates = JSON.parse(savedColumnStates);
            table.columns().every(function(index) {
                var isVisible = savedColumnStates[index];
                this.visible(isVisible);
            });
        }

        // 열이 변경될 때마다 그 상태를 localStorage에 저장
        table.on('column-visibility.dt', function(e, settings, column, state) {
            var columnStates = table.columns().visible().toArray();
            localStorage.setItem('columnStates', JSON.stringify(columnStates));
        });


        //Table 체크박스 선택시 최신화처리
        $('#tbl_land tbody').on ('click', '#land_used_data', function () {

            var already_chk = $(this).prop('checked');
            if(already_chk == false) {
                return false;
            }
            
            var sfl = '<?php echo $sfl ?>';
            var ptn = $("#search_ptn_idx").val();
            

            if(sfl != "ptn_idx" || ptn == "") {
                alert("고객사 선택 & 조회 후 DB최신화 할수있습니다.");
                $(this).prop('checked', false);
                return false;
            }
            if (confirm("가장마지막 DB사용부터 여기까지 사용처리하시겠습니까?")) {
                var index = $(this).closest('tr').index();
                var data = table.row( $(this).parents('tr') ).data();
                var chk = document.getElementsByName("chk[]");
                var rows = table.rows(index).nodes();
                $('input[type="checkbox"]', rows).prop('checked', true);

                var input1 = document.createElement('input');
                input1.setAttribute("type", "hidden");
                input1.setAttribute("name", "act_button");
                input1.setAttribute("value", "DB사용2");
                $("#listForm").append(input1);

                var input2 = document.createElement('input');
                input2.setAttribute("type", "hidden");
                input2.setAttribute("name", "use_yn");
                input2.setAttribute("value", "E");
                $("#listForm").append(input2);
                
                $("#listForm").submit();

            } else {
                $(this).prop('checked', false);
            }
        });

    });

    //list form submit validation 
    function listForm_submit(f) {
        
    }

    //modal form submit validation 
    function validateForm() {

        //최신화체크시
        var curr_yn = $('input[name="curr_yn"]').is(":checked");
        //var rdo_collect = $('input[id="rdo_collect"]').is(":checked");

        if(curr_yn == true) {
            //고객사 필수체크
            var ptn_idx =  $('#ptn_idx_xls').val();
            if(ptn_idx == "") {
                alert("최신화 기능 이용시 고객사 필수선택");
                return false;
            }

            if (!confirm("선택한 고객사 최신화 처리를 진행합니다. 진행하시겠습니까?\n엑셀다운후 최신화 데이터 확인은 새로고침 해주세요.")) {
                return false;
            } 
        }

        //document.getElementById("btn_small").disabled = "disabled";
        $('#modal-exc-down').modal('hide');

        return true;
    }

    function validateForm2() {

        return true;
    }

    function make_datepicker(){

        var sfl = '<?php echo $sfl ?>';
        var stx = '<?php echo $stx ?>';
        
        var maxDay = moment().format("YYYY-MM-DD");

        if(sfl == "" && stx == "") {
            startDay = moment().add(-1, 'month');
            endDay = moment().format("YYYY-MM-DD");
        } else if(sfl != "insert_date") {
            startDay = moment().add(-1, 'month');
            endDay = moment().format("YYYY-MM-DD");
        } else if(sfl == "insert_date") {
            db_date = stx.split("~");
            startDay = db_date[0];
            endDay = db_date[1];
        }
            
        $('#search_fromto').daterangepicker({
            locale: {
                "format": "YYYY-MM-DD",
                "separator": " ~ ",
                "applyLabel": "확인",
                "cancelLabel": "취소",
                "fromLabel": "From",
                "toLabel": "To",
                "customRangeLabel": "Custom",
                "weekLabel": "W",
                "daysOfWeek": ["일", "월", "화", "수", "목", "금", "토"],
                "monthNames": ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"],
            },
            //minDate: minDay,
            maxDate: maxDay,
            showDropdowns: true,
            startDate: startDay,
            endDate: endDay

        });

        var asis = $("#search_fromto").val();
        var tobe = asis.replace(' ~ ', '~');
        $("#search_fromto").val(tobe);

        $('#search_fromto').keypress(function(e){
            if (e.keyCode == 10 || e.keyCode == 13)
                e.preventDefault();
        });
    }
</script>

<?php
include_once(G5_PATH . '/tail.php');

