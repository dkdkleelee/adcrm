<?php
require_once '../../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');

$g5['title'] = "정상 DB"; 
include_once(G5_PATH . '/head.php');

$sql_columns    = "";
$sql_common     = "";
$sql_search     = "";
$sql_gruop      = "";
$sql_order      = "";
$total_count    = 0;
$trim_str       = "";

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
, f.mb_name as mb_ptn_name
, a.land_deptno
, e.deptnm 
, b.pg_mb_emp 
, g.mb_name  as mb_emp_name
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
, a.client_ip 
, a.utm_source
, b.pg_api_yn
, a.api_send_yn 
, a.sms_send_yn 
, a.submit_pos
, a.inflow_path 
, a.inflow_env
, case when a.ip like '%:%' then substring(a.ip, 1, 15)
    else a.ip 
    end as ip
, a.city
, a.region
, a.country
, a.loc
, a.org
, a.postal
, a.timezone
";

$sql_common = "
from {$g5['crm_landing']} a
left join {$g5['crm_page']}     b on a.land_pg_idx = b.page_idx
left join {$g5['crm_design']}   c on b.pg_des_idx  = c.design_idx 
left join {$g5['crm_partner']}  d on a.land_ptn_idx = d.ptn_idx
left join {$g5['crm_depart']}   e on b.pg_deptno   = e.deptno
left join {$g5['member_table']} f on b.pg_mb_ptn   = f.mb_no
left join {$g5['member_table']} g on b.pg_mb_emp   = g.mb_no
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

$sql_search .= "
where 1=1
and a.use_yn = 'Y'
{$add_cont}
";

//search
if ($stx) {

    $display = "<span class='badge bg-success'>일반검색中</span>";

    if($stx == "advanced") {

        $from = substr($advanced_from, 0, 10);
        $to = substr($advanced_to, 0, 10);

        if($from == "" || $to == "") {
            $timestamp = strtotime("-1 months");
            $from = date("Y-m-d", $timestamp);

            $timestamp = strtotime("Now");
            $to = date("Y-m-d", $timestamp);

            $sql_search .= " and a.insert_date between '{$from} 00:00:00.000' and now()";
        }
    
        $conditions = [];
        $display = "<span class='badge bg-danger'>상세검색中</span>";
        
    
        // 조건 체크
        if (!empty($advanced_ptn_idx)) {
            $conditions[] = "a.land_ptn_idx = {$advanced_ptn_idx}";
        }
        if (!empty($advanced_pg_uri)) {
            $pg_uris = explode(',', $advanced_pg_uri);
            $pg_uris = array_map(function($uri) {
                return "'{$uri}'";
            }, $pg_uris);
            $conditions[] = "b.pg_uri IN (" . implode(',', $pg_uris) . ")";
            $add_join = "left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx ";
        }
        if (!empty($advanced_db_status)) {
            $conditions[] = "a.db_status = '{$advanced_db_status}'";
        }
    
        // 조건 결합
        if (!empty($conditions)) {
            $sql_search .= " AND (" . implode(' AND ', $conditions) . ")";
        }
    
        // 날짜 범위 조건 추가
        $sql_search .= " AND a.insert_Date2 BETWEEN '{$from}' AND '{$to}'";


        $qstr = 'stx=advanced';
        $qstr .= '&page=' . $page; // 현재 페이지 번호 추가
        $qstr .= '&advanced_ptn_idx=' . urlencode($advanced_ptn_idx);
        $qstr .= '&advanced_pg_uri=' . urlencode($advanced_pg_uri);
        $qstr .= '&advanced_from=' . urlencode($advanced_from);
        $qstr .= '&advanced_to=' . urlencode($advanced_to);
        $qstr .= '&advanced_db_status=' . urlencode($advanced_db_status);

    } else {

        //$trim_str = "and a.use_yn = 'Y'";
        $trim_str = "";
        $sql_search .= $trim_str;

        $sql_search .= " and ( ";
        switch ($sfl) {
            case "ptn_idx":

                if($stx == "unspecified") {
                    $sql_search .= " a.land_ptn_idx IS NULL";
                } else {
                    $sql_search .= " a.land_ptn_idx = $stx";
                }
                
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

            case "insert_date2":
                $from = substr($stx,0,10);
                $to   = substr($stx,10,10);
                $sql_search .= "a.$sfl between '{$from}' and '{$to}'";
                $stx = $from."~".$to;
                break;

            case "tel":
                //$sql_search .= " tel = '$stx' ";
                $sql_search .= " tel = HEX(AES_ENCRYPT('{$stx}', 'withus_secret_key')) ";
                break;
            case "utm_source":
                $sql_search .= " utm_source like '%{$stx}%' ";
                break;
        }
        $sql_search .= " ) ";
    }
} 
//조회조건 X
else {
    //$trim_str = "and trim(a.use_yn) = 'Y'";
    $trim_str = "";
    $sql_search .= $trim_str;
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
$rows = 100;
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
} else {
    $add_cond = "";
}
$partner_sql = "
select ptn_idx
    , ptn_nm
from {$g5['crm_partner']} 
where 1=1
and use_yn = 'Y'
and ptn_status < 4
{$add_cond}
ORDER BY 
    CASE WHEN ptn_status = 2 THEN 0 ELSE 1 END ASC,
    ptn_nm COLLATE utf8mb4_unicode_ci ASC
";
$partner_list = sql_query($partner_sql);


if($member['mb_level'] >= 5) {
$emp_sql = "
select mb_no
     , mb_name
     , ifnull((select count(*) from gnp_crm_landing sub where a.mb_no = sub.land_empno and sub.use_yn = 'Y' and insert_date2 = curdate() group by land_empno) ,0) as today_cnt
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
 where 1=1
 {$trim_str}
 {$add_cond}
group by b.pg_uri
order by page_idx desc
";
$code_list = sql_query($code_sql); 


if($sfl == "ptn_idx") {
    $ptn_chg_sql = "
    select b.ptn_nm
        , a.page_idx
        , b.ptn_idx
        , a.pg_uri
    from gnp_crm_page a
    left join gnp_crm_partner b on a.pg_ptn_idx = b.ptn_idx
    where a.page_idx is not null
        and b.ptn_idx is not null
        and b.ptn_idx != {$stx}
    group by a.page_idx 
    order by b.ptn_nm asc
    ";
    $ptn_chg_list = sql_query($ptn_chg_sql);

    $ptn_page_html = '<option value="">미선택</option>';

    for ($i = 0; $partner = sql_fetch_array($ptn_chg_list); $i++) { 

        $value = $partner['ptn_idx'] . '||' . $partner['page_idx']; // 예: 34|112
        $label = $partner['ptn_nm'] . ' / ' . $partner['pg_uri'];   // 예: 위더스 / abc123
        
        $ptn_page_html .= '<option value="' . htmlspecialchars($value) . '">' . htmlspecialchars($label) . '</option>';
    }
}


$rownum = $total_count - $from_record;

?>
<link rel="stylesheet" href="<?php echo G5_THEME_URL ?>/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
<link rel="stylesheet" href="<?php echo G5_THEME_URL ?>/plugins/daterangepicker/daterangepicker.css">

<script src="<?php echo G5_THEME_URL ?>/plugins/moment/moment.min.js"></script>
<script src="<?php echo G5_THEME_URL ?>/plugins/daterangepicker/daterangepicker.js"></script>
<!-- <script src="<?php echo G5_THEME_URL ?>/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script> -->

<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<style>

.map-popup {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 600px;
    height: 400px;
    border: 1px solid black;
    z-index: 1000;
    background-color: white;
    padding: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.5);
}

.map-popup img {
    width: 100%;
    height: 100%;
}

.close-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    cursor: pointer;
}

</style>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">정상DB 조회(<?php echo number_format($total_count) ?>) <?php echo $display ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="dataTables_wrapper dt-bootstrap4">

                            <div class="d-flex flex-sm-row flex-column justify-content-sm-between">
                                <div class="d-flex justify-content-center mb-2 mb-xs-0">
                                    <div class="btn-group xs-100">
                                        <button type="submit" name="btn_ins" value="등록" onclick="location.href='<?php echo G5_BIZ_URL; ?>/landing/land_form'" class="btn btn-primary btn-xs border border-dark"><i class="fas fa-pen"></i> 입력</button>
                                        <button type="submit" form="listForm" class="btn btn-warning btn-xs border border-dark" name="act_button" value="선택불량"><i class="fas fa-eraser"></i>불량</button>
                                        <button type="submit" form="listForm" class="btn btn-danger btn-xs border border-dark" name="act_button" value="선택삭제"><i class="far fa-trash-alt"></i>삭제</button>
                                        <button type="button" form="listForm" class="btn btn-muted btn-xs border border-dark"  onclick="dup_clear()"><i class="fas fa-times"></i>중복제거</button>
                                        <button type="button" class="btn btn-success btn-xs border border-dark" data-toggle="modal" data-target="#modal-exc-down">
                                            <i class="fas fa-file-download"></i> 엑셀다운
                                        </button>
                                        <button type="button" class="btn btn-info btn-xs border border-dark" data-toggle="modal" data-target="#modal-exc-upload">
                                            <i class="fas fa-file-upload"></i> 엑셀업로드
                                        </button>

                                        <button type="button" class="btn btn-secondary btn-xs border border-dark" data-toggle="modal" data-target="#modal-sms-status">
                                            <i class="fas fa-sms"></i> SMS현황
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-xs border border-dark" data-toggle="modal" data-target="#modal-ban-ip">
                                            <i class="fas fa-shield-alt"></i> 차단현황
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
                                                <option value="insert_date2" <?php echo get_selected($sfl, "insert_date2"); ?>>접수일시</option>
                                                <option value="tel" <?php echo get_selected($sfl, "tel"); ?>>연락처</option>
                                                <option value="utm_source" <?php echo get_selected($sfl, "utm_source"); ?>>UTM</option>
                                            </select>
                                            <select id="search_ptn_idx" name="stx" class="form-control selectpicker" data-live-search="true" data-style="border border-secondary" data-width="200px" data-size="10">
                                                <option value="">미선택</option>
                                                <option value="unspecified" <?php echo get_selected($stx, "unspecified"); ?>>미지정 고객사</option>
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

                                            <button type="button" class="btn btn-outline-danger my-2 my-sm-0" data-toggle="modal" data-target="#advancedSearchModal">상세</button>

                                            <?php if($sfl == "ptn_idx" && $stx != "") { ?>
                                                <button type="button" class="btn btn-outline-warning my-2 my-sm-0" data-toggle="modal" data-target="#chartModal">차트</button>
                                            <?php } ?>

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

                                <input type="hidden" name="advanced_ptn_idx" value="<?php echo $advanced_ptn_idx ?>">
                                <input type="hidden" name="advanced_pg_uri" value="<?php echo $advanced_pg_uri ?>">
                                <input type="hidden" name="advanced_from" value="<?php echo $advanced_from ?>">
                                <input type="hidden" name="advanced_to" value="<?php echo $advanced_to ?>">
                                <input type="hidden" name="advanced_db_status" value="<?php echo $advanced_db_status ?>">


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
                                                    <th>접수IP</th>
                                                    <th class="text-center">API</th>
                                                    <th class="text-center">알람</th>
                                                    <th>유입폼</th>
                                                    <th>유입경로</th>
                                                    <th>utm</th>
                                                    <th>환경</th>
                                                    <th>사용</th>
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
                                                            <a href="land_form?w=u&land_idx=<?php echo $row['land_idx'] . '&' . ltrim($qstr, '?'); ?>">
                                                                <?php echo $rownum; $rownum = $rownum - 1; ?>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <a href="land_form?w=u&land_idx=<?php echo $row['land_idx'] . '&' . ltrim($qstr, '?'); ?>">
                                                                <?php echo $row['name'] == "" ? 'N/A' : $row['name']; ?>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <a href="land_form?w=u&land_idx=<?php echo $row['land_idx'] . '&' . ltrim($qstr, '?'); ?>">
                                                                <?php echo $row['tel']; ?>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <?php echo $row['pg_memo'] ?><a href="<?php echo G5_BIZ_URL ?>/page/page_form?w=u&page_idx=<?php echo $row['land_pg_idx'] ?>"> <i class="fas fa-link"></i></a>
                                                        </td>

                                                        <td>
                                                            <?php echo $row['design_name'] ?><a href="<?php echo G5_BIZ_URL ?>/design/design_form?w=u&design_idx=<?php echo $row['pg_des_idx'] ?>"> <i class="fas fa-link"></i></a>
                                                        </td>

                                                        <td>
                                                            <a href="<?php echo G5_BIZ_URL ?>/landing/land_list?sfl=ptn_idx&stx=<?php echo $row['land_ptn_idx'] ?>" target="_self">
                                                                <?php echo "[" . $row['ptn_nm'] . "]" . $row['mb_ptn_name'] ?>
                                                            </a>
                                                            <a href="<?php echo G5_BIZ_URL ?>/partner/partner_form?w=u&ptn_idx=<?php echo $row['land_ptn_idx'] ?>"> <i class="fas fa-link"></i></a>
                                                        </td>

                                                        <td>
                                                            <a href="<?php echo G5_BIZ_URL ?>/landing/land_list?sfl=pg_uri&stx=<?php echo $row['pg_uri'] ?>" target="_self">
                                                                <?php echo $row['pg_uri'] ?>
                                                            </a>
                                                            <a href="https://<?php echo $row['pg_domain'] . '/' . $row['pg_uri'] ?>" target="_blank"><i class="fas fa-plane-departure text-success"></i></a>
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
                                                            <a href='javascript:banIp("<?php echo $row['client_ip'] ?>");'>
                                                            <?php echo substr($row['client_ip'] , 0, 15); ?>
                                                            </a>

                                                            <?php if($row['loc'] != "") { 
                                                                $loc = explode(",", $row['loc']);
                                                                $lat = $loc[0];
                                                                $lng = $loc[1];
                                                            ?>
                                                                <a href="javascript:void(0);" onclick="openMapPopup('<?php echo $lat; ?>', '<?php echo $lng; ?>')"><i class="text-dark fas fa-street-view"></i></a>
                                                            <?php } ?>
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
                                                                    $icon = "<a href='javascript:stopToApi(".$row['land_idx'].");' class='text-secondary'><i class='fas fa-pause' data-toggle='tooltip' data-placement='bottom' title='대기'></i></a>";
                                                                } else if ($row['api_send_yn'] == "?") {
                                                                    $icon = "<i class='fas fa-question' data-toggle='tooltip' data-placement='bottom' title='확인필요'></i>";
                                                                }
                                                            } else {
                                                                $icon = "<i class='fas fa-toggle-off text-danger' data-toggle='tooltip' data-placement='bottom' title='미사용'></i>";
                                                            }
                                                            echo $icon;
                                                            ?>
                                                        </td>

                                                        <td class="text-center">
                                                            <?php
                                                            $icon = "";
                                                            if ($row['sms_send_yn'] == "Y") {
                                                                $icon = "<i class='fas fa-sms text-black'></i>";
                                                            } else if ($row['sms_send_yn'] == "N") {
                                                                $icon = "<i class='fas fa-exclamation-triangle text-danger data-toggle='tooltip' data-placement='bottom' title='실패'></i>";
                                                            } else if ($row['sms_send_yn'] == "H") {
                                                                $icon = "<a href='javascript:stopToSms(".$row['land_idx'].");' class='text-secondary'><i class='fas fa-pause' data-toggle='tooltip' data-placement='bottom' title='대기'></i></a>";
                                                            }
                                                            echo $icon;
                                                            ?>
                                                        </td>

                                                        <td>
                                                            <?php echo $row['submit_pos'] ?>
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
                                                                $icon = "<i class='fas fa-laptop'></i>";
                                                            } else if ($row['inflow_env'] == "M") {
                                                                $icon = "<i class='fas fa-mobile-alt'></i>";
                                                            } else if ($row['inflow_env'] == "U") {
                                                                $icon = "<i class='fas fa-upload text-success'></i>";
                                                            } else if ($row['inflow_env'] == "A") {
                                                                $icon = "<i class='fas fa-network-wired text-primary'></i>";
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
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mb-2">

                                    <?php if($sfl == "ptn_idx") { ?>
                                    <div class="d-flex">
                                        <div class="input-group input-group-sm">
                                            <select id="selChgPtn" name= "selChgPtn" class="custom-select">
                                                <?php echo $ptn_page_html; ?>
                                            </select>
                                            <div class="input-group-append">
                                                <button type="submit" id="btnChgPtn" class="btn btn-secondary" name="act_button" value="분배이동">분배이동</button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>

                                <?php 
                                if ($stx == "advanced") {
                                    // 고급 검색 조건을 배열로 생성
                                    $advanced_conditions = [
                                        'advanced_ptn_idx' => $advanced_ptn_idx,
                                        'advanced_pg_uri' => $advanced_pg_uri,
                                        'advanced_from' => $advanced_from,
                                        'advanced_to' => $advanced_to,
                                        'advanced_db_status' => $advanced_db_status
                                    ];

                                    echo get_paging_advanced($config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'], $advanced_conditions);
                                } else {
                                    echo get_paging_bootst(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'] . '?' . $qstr . '&amp;page=');
                                }
                                ?>
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
                <h4 class="modal-title">정상DB 엑셀 다운로드</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="modal_form" name="modal_form" action="./land_exceldown" method="post" onSubmit="return validateForm()">

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
                        <select id="pg_uri" name="pg_uri[]" class="selectpicker form-control" data-style="border border-secondary" data-selected-text-format="count" data-live-search="true" multiple data-size="10">
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

<form id="modal_form" name="modal_form" action="./land_excelupload" method="post" onSubmit="return validateForm2()" enctype="multipart/form-data">
    <div class="modal fade" id="modal-exc-upload" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h4 class="modal-title">엑셀 업로드 <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="엑셀업로드시 빈셀에 커서있을시 에러가 날수있습니다."></i></h4>

                    <div class="form-check ml-3 mb-0">
                        <input class="form-check-input" type="checkbox" id="allowDuplicates" name="allowDuplicates">
                        <label class="form-check-label text-white" for="allowDuplicates">중복허용</label>
                    </div>
                    
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <input type="hidden" name="sst" value="<?php echo $sst ?>">
                <input type="hidden" name="sod" value="<?php echo $sod ?>">
                <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
                <input type="hidden" name="stx" value="<?php echo $stx ?>">
                <input type="hidden" name="page" value="<?php echo $page ?>">

                <div class="modal-body">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="inputGroup-sizing-default">코드값</span>
                        </div>
                        <select id="pg_uri" name="pg_uri" class="selectpicker form-control" data-style="border border-secondary" data-selected-text-format="count" data-live-search="true" data-size="10">
                        </select>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="inputGroup-sizing-default">입력시간</span>
                        </div>
                        <div class="form-control">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="ranTimeYN" id="inlineRadio2" value="C" checked/>
                                <label class="form-check-label" for="inlineRadio2">현재시간</label>
                            </div>
                            <div class="ml-2 form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="ranTimeYN" id="inlineRadio1" value="R" />
                                <label class="form-check-label" for="inlineRadio1">랜덤시간</label>
                            </div>
                            <div class="ml-2 form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="ranTimeYN" id="inlineRadio1" value="U" />
                                <label class="form-check-label" for="inlineRadio1">시간지정</label>
                            </div>
                        </div>
                    </div>

                    <div class="input-group mb-3 d-none" id="closed_time">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="inputGroup-sizing-default">마감시간</span>
                        </div>
                        <select id="end_hour" name="end_hour" class="form-control">
                            <option value="30">30분</option>
                            <option value="60">60분</option>
                            <option value="90">90분</option>
                            <option value="120">2시간</option>
                            <option value="180">3시간</option>
                            <option value="240">4시간</option>
                            <option value="300">5시간</option>
                            <option value="360">6시간</option>
                            <option value="420">7시간</option>
                            <option value="480">8시간</option>
                            <option value="540">9시간</option>
                            <option value="600">10시간</option>
                            <option value="660">11시간</option>
                            <option value="720">12시간</option>
                            <option value="840">14시간</option>
                            <option value="960">16시간</option>
                            <option value="1080">18시간</option>
                            <option value="1200">20시간</option>
                            <option value="1320">22시간</option>
                            <option value="1440">24시간</option>
                        </select>
                    </div>

                    

                    <div class="input-group mb-3">
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" id="fileInput" name="file" class="custom-file-input" aria-describedby="fileInput">
                                <label class="custom-file-label" for="fileInput">파일선택</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">닫기</button>
                    <button type="submit" class="btn btn-default" name="act_button" value="양식다운"><i class="fas fa-download"></i> 양식다운</button>
                    <button type="submit" class="btn btn-primary" name="act_button" value="업로드">업로드</button>
                </div>
        </div>
    </div>
</div>
</form>

<!-- Advanced Search Modal -->
<div class="modal fade" id="advancedSearchModal" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h4 class="modal-title">상세 검색</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Advanced Search Options -->

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroup-sizing-default">고객사</span>
                    </div>
                    <select id="advanced_ptn_idx" name="advanced_ptn_idx[]" class="selectpicker form-control" data-style="border border-secondary" data-selected-text-format="count" data-live-search="true" data-size="10">
                    </select>
                </div>

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroup-sizing-default">코드값</span>
                    </div>
                    <select id="advanced_pg_uri" name="advanced_pg_uri[]" class="selectpicker form-control" data-style="border border-secondary" data-selected-text-format="count" data-live-search="true" multiple data-size="10">
                    </select>
                </div>
                
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroup-sizing-default">기준일</span>
                    </div>
                    <input class="form-control" id="advanced_from" name="advanced_from" readonly>
                    <input class="form-control" id="advanced_to" name="advanced_to" readonly>
                </div>


                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroup-sizing-default">상태값</span>
                    </div>
                    <select id="advanced_db_status" name="advanced_db_status" class="form-control">
                        <option value="">미선택</option>
                        <option value="1">부재</option>
                        <option value="2">불량</option>
                        <option value="3">거절</option>
                        <option value="4">리콜</option>
                        <option value="5">중복</option>
                        <option value="6">유망</option>
                        <option value="7">승인</option>
                    </select>
                </div>



            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                <button type="button" class="btn btn-primary" id="advancedSearch">검색</button>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="modal-sms-status" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modal-conf_statLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary">
                <h5 class="modal-title" id="modal-conf_statLabel">SMS현황 (최근7일)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table id="smsTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>연락처</th>
                            <th>코드</th>
                            <th>등록일시</th>
                            <th>아이피</th>
                            <th>cnt</th>
                            <th>DB이동</th>
                            <th>삭제</th>
                            <th style="display:none;">sms_idx</th>
                        </tr>
                    </thead>
                    <tbody id="dynamic-tbody">
                    
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                
                <button type="button" class="btn btn-info" id="smsExcelButton">엑셀다운</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="smsNewModal" tabindex="-1" role="dialog" aria-labelledby="smsNewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary">
                <h5 class="modal-title" id="smsNewModalLabel">신규입력 ※API & 문자발송X※</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="modal_form" name="modal_form" action="./land_list_update" method="post">
            <input type="hidden" id="insert_date" name="insert_date" value="">
            <input type="hidden" id="client_ip" name="client_ip" value="">

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tel">tel</label>
                                <input type="text" class="form-control" id="tel" name="tel" placeholder="연락처" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">이름</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="이름">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="option1">옵션1</label>
                                <input type="text" class="form-control" id="option1" name="option1" placeholder="옵션1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="option3">옵션2</label>
                                <input type="text" class="form-control" id="option2" name="option2" placeholder="옵션2">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="option3">옵션3</label>
                                <input type="text" class="form-control" id="option3" name="option3" placeholder="옵션3">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="option4">옵션4</label>
                                <input type="text" class="form-control" id="option4" name="option4" placeholder="옵션4">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="option6">옵션5</label>
                                <input type="text" class="form-control" id="option5" name="option5" placeholder="옵션5">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="option6">옵션6</label>
                                <input type="text" class="form-control" id="option6" name="option6" placeholder="옵션6">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="option7">옵션7</label>
                                <input type="text" class="form-control" id="option7" name="option7" placeholder="옵션7">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="option8">옵션8</label>
                                <input type="text" class="form-control" id="option8" name="option8" placeholder="옵션8">
                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="option9">옵션9</label>
                                <input type="text" class="form-control" id="option9" name="option9" placeholder="옵션9">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pg_uri">코드</label>
                                <input type="text" class="form-control" id="pg_uri" name="pg_uri" placeholder="코드" readonly>
                            </div>
                        </div>
                        
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" name="act_button" value="연락처저장">저장</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                </div>
            </form>
        </div>
    </div>
</div>



<div class="modal fade" id="modal-ban-ip" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modal-conf_statLabel_ipstatus" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary">
                <h5 class="modal-title" id="modal-conf_statLabel_ipstatus">IP차단현황</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table id="ipTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>아이피</th>
                            <th>등록일시</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="dynamic-tbody2">
                    
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
            </div>
        </div>
    </div>
</div>

<div class="map-popup" id="mapPopup">
    <span class="close-btn" onclick="closeMapPopup()">X</span>
    <img id="mapImage" src="" alt="Map Image" />
</div>


<div class="modal fade" id="chartModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modal-conf_statLabel_chart" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modal-conf_statLabel_chart">차트</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <canvas id="myChart"></canvas>
            </div>
        </div>
    </div>
</div>


<!-- 엑셀 다운로드 문자인증 모달 -->
<div class="modal fade" id="excelAuthModal" tabindex="-1" aria-labelledby="excelAuthModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title" id="excelAuthModalLabel" style="font-size: 13px;"><i class="fas fa-lock"></i> 인증번호 발송됨</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="excelAuthForm">
          <p class="text-sm text-center mb-2" id="auth_phone_txt">관리자 연락처로 발송되었습니다.</p>
          <div class="form-group">
            <input type="text" class="form-control" id="excel_auth_code" name="excel_auth_code" required placeholder="인증번호 6자리 입력" minlength="6" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);">
          </div>
          <button type="submit" id="btn_excel_auth" class="btn btn-primary btn-block">인증하기</button>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- 엑셀 다운로드 비밀번호 재확인 모달 -->
<div class="modal fade" id="excelPasswordModal" tabindex="-1" aria-labelledby="excelPasswordModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title" id="excelPasswordModalLabel" style="font-size: 13px;">
          <i class="fas fa-lock"></i> 비밀번호 재확인
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="excelPasswordForm">
          <p class="text-sm text-center mb-2">엑셀 다운로드를 위해 비밀번호를 다시 입력해주세요.</p>
          <div class="form-group">
            <input type="password" class="form-control" id="excel_password" name="excel_password" required placeholder="비밀번호 입력" autocomplete="current-password">
          </div>
          <button type="submit" id="btn_excel_password" class="btn btn-primary btn-block">확인</button>
        </form>
      </div>
    </div>
  </div>
</div>


<script>
    $(function() {

        //bs_input_file();

        $(".modal-dialog").draggable({
            handle: ".modal-header"
        });

        document.querySelector('.custom-file-input').addEventListener('change',function(e){
            var fileName = document.getElementById("fileInput").files[0].name;
            var nextSibling = e.target.nextElementSibling
            nextSibling.innerText = fileName
        })
        
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

        $("#modal-exc-upload #pg_uri").change(function(e) {
            var selectedText = $(this).find("option:selected").text();
            var hasSMSalarm = selectedText.indexOf("(SMS알람)") !== -1;
            
            var checked_time = $("input[name='ranTimeYN']:checked").val();
            if(checked_time == "C") {
                if (hasSMSalarm) {
                    alert("현재시간으로 업로드시 SMS알람 기능은 작동하지않습니다.");
                } 
            }
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

        
        $("input[name='ranTimeYN']:radio").change(function () {
            var radio = this.value;
            if(radio == "R") {
                $("#closed_time").removeClass("d-none");
            } else if(radio == "C" || radio == "U") {

                var pg_uri = $("#modal-exc-upload #pg_uri");
                var selectedText = pg_uri.find("option:selected").text();
                var hasSMSalarm = selectedText.indexOf("(SMS알람)") !== -1;
            
                var checked_time = $("input[name='ranTimeYN']:checked").val();
                if(checked_time == "C") {
                    if (hasSMSalarm) {
                        alert("현재시간으로 업로드시 SMS알람 기능은 작동하지않습니다.");
                    } 
                }
                if(radio == "U") {
                    alert("엑셀 M열 양식 :2024-01-01  10:30:00 AM 입력해주세요.");
                }

                $("#closed_time").addClass("d-none");
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
                "minDate": moment().subtract(12, 'month'),
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

        var smsTable = $('#smsTable').DataTable({
            "autoWidth": false,
            "info": false,
            "lengthChange": false,
            "columnDefs": [
                { "width": "15%", "targets": 0 },
                { "width": "10%", "targets": 1 },
                { "width": "20%", "targets": 2 },
                { "width": "10%", "targets": 3 },
                { "width": "7%", "targets": 4 },
                {
                    "width": "7%",
                    "targets": 5,
                    "data": null,
                    "defaultContent": "<button class='btn-insert'>입력</button>"
                },
                {
                    "width": "7%",
                    "targets": 6,
                    "data": null,
                    "defaultContent": "<button class='btn-delete'>삭제</button>"
                },
                {
                    "targets": -1, // 숨겨진 열 설정
                    "visible": false
                }
            ],
            "order": []
        });

        $('#modal-sms-status').on('show.bs.modal', function (e) {
            var act = "sms-status";

            $.ajax({
                type: "post",
                data: {
                    act: act
                },
                url: "land_ajax",
                dataType: "json",
                success: function(data) {
                    smsTable.clear();
                    for (var i = 0; i < data.length; i++) {
                        smsTable.row.add([
                            data[i].sms_phone,
                            data[i].pg_uri,
                            data[i].insert_date,
                            data[i].client_ip,
                            data[i].cnt,
                            '',
                            '',
                            data[i].sms_idx
                        ]).draw(false);
                    }
                }
            });
        });

        var ipTable = $('#ipTable').DataTable({
            "autoWidth": false, // 열 너비 자동 조절 비활성화
            "info": false,
            "details": true,
            "lengthChange": false,
            "columnDefs": [
                { "width": "40%", "targets": 0 }, 
                { "width": "40%", "targets": 1 },
                {
                    "width": "20%",
                    "targets": -1, // 마지막 열에 버튼 추가
                    "data": null,
                    "defaultContent": "<button class='btn-cancle'>취소</button>"
                }
            ],
            "order": [] // 정렬 비활성화
        });

        $('#modal-ban-ip').on('show.bs.modal', function (e) {
            var act = "get_ban_ip";

            $.ajax({
                type: "post",
                data: {
                    act: act
                },
                url: "land_ajax",
                dataType: "json",
                success: function(data) {
                    ipTable.clear();
                    for (var i = 0; i < data.length; i++) {
                        ipTable.row.add([
                            data[i].ban_ip,
                            data[i].insert_date,
                        ]).draw(false);
                    }
                }
            });
        });

        $('#smsExcelButton').on('click', function() {
            var form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", "land_list_update");
            var fields = {
                'act_button': "엑셀다운"
            };
            for (var key in fields) {
                var input = document.createElement("input");
                input.setAttribute("type", "hidden");
                input.setAttribute("name", key);
                input.setAttribute("value", fields[key]);

                form.appendChild(input);
            }
            document.body.appendChild(form);
            form.submit();
        });

        //모달에서 삭제버튼 클릭하면 ~
        $('#smsTable tbody').on('click', 'button.btn-delete', function() {
            if (confirm("해당 데이터를 삭제하시겠습니까?")) {
                var data = smsTable.row($(this).parents('tr')).data();
                var act = "del_sms";
                var $button = $(this);
                var sms_idx = data[7];

                $.ajax({
                    type: "post",
                    data: {
                        sms_idx:sms_idx,
                        act: act
                    },
                    url: "land_ajax",
                    dataType: "json",
                    success:function(result) {
                        var row = smsTable.row($button.parents('tr'));
                        row.remove().draw(false);
                        alert("삭제되었습니다.");
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                        return;
                    }
                });
                
            }
        });

        $('#smsTable tbody').on('click', 'button.btn-insert', function() {
            var data = smsTable.row($(this).parents('tr')).data();
            var tel = data[0];
            var pg_uri = data[1];
            var insert_date = data[2];
            var client_ip = data[3];

            var act = "get_userData";
            $('#smsNewModal').find('input').val('');

            $.ajax({
                type: "post",
                data: {
                    tel:tel,
                    pg_uri: pg_uri,
                    insert_date: insert_date,
                    client_ip: client_ip,
                    act: act
                },
                url: "land_ajax",
                dataType: "json",
                success:function(result) {

                    $('#smsNewModal').modal('show');
                    
                    $('#smsNewModal #tel').val(tel);
                    $('#smsNewModal #pg_uri').val(pg_uri);

                    $('#smsNewModal #insert_date').val(insert_date);
                    $('#smsNewModal #client_ip').val(client_ip);

                    $('#smsNewModal #name').val(result['name']);
                    $('#smsNewModal #option1').val(result['option1']);
                    $('#smsNewModal #option2').val(result['option2']);
                    $('#smsNewModal #option3').val(result['option3']);
                    $('#smsNewModal #option4').val(result['option4']);
                    $('#smsNewModal #option5').val(result['option5']);
                    $('#smsNewModal #option6').val(result['option6']);
                    $('#smsNewModal #option7').val(result['option7']);
                    $('#smsNewModal #option8').val(result['option8']);
                    $('#smsNewModal #option9').val(result['option9']);
                    
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    return;
                }
            });
        });

        $('#ipTable tbody').on('click', 'button.btn-cancle', function() {
            var data = ipTable.row($(this).parents('tr')).data();
            var ip = data[0];

            var act = "cancle_ban_ip";
            var $button = $(this);

            $.ajax({
                type: "post",
                data: {
                    ip:ip,
                    act: act
                },
                url: "land_ajax",
                dataType: "json",
                success:function(result) {
                    var row = ipTable.row($button.parents('tr'));
                    row.remove().draw(false);
                    alert("취소되었습니다.");
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    return;
                }
            });
        });

        $('#advancedSearchModal').on('show.bs.modal', function() {
            initAdvSearch();
        });

        $('#chartModal').on('show.bs.modal', function() {
            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = '<?php echo G5_THEME_URL ?>/plugins/chart.js/Chart.min.js';
            script.onload = function() {
                var act = "get_cnt_by_date";
                var ptn_idx = '<?php echo $stx ?>';

                $.ajax({
                    type: "post",
                    data: {
                        ptn_idx: ptn_idx,
                        act: act
                    },
                    url: "land_ajax",
                    dataType: "json",
                    success: function(result) {
                        
                        var labels = [];
                        var counts = [];
                        var backgroundColors = ['rgba(255, 99, 132, 0.2)', // 빨간색
                                'rgba(255, 159, 64, 0.2)', // 주황색
                                'rgba(255, 205, 86, 0.2)', // 노란색
                                'rgba(75, 192, 192, 0.2)', // 초록색
                                'rgba(54, 162, 235, 0.2)', // 파란색
                                'rgba(54, 77, 235, 0.2)',  // 남색
                                'rgba(153, 102, 255, 0.2)'];// 보라색


                        // 결과 데이터를 반복하여 레이블과 데이터 배열 채우기
                        result.forEach(function(item) {
                            labels.push(item.date); // 날짜 레이블 추가
                            counts.push(item.count); // 건수 데이터 추가
                        });

                        var data = {
                            labels: labels, // 일주일의 요일
                            datasets: [{
                                label: '일자별 DB건수',
                                data: counts, // 가상의 방문자 수
                                backgroundColor: backgroundColors, // 색상 배열 사용
                                borderColor: backgroundColors.map(color => color.replace('0.2', '1')), // 테두리 색상
                                borderWidth: 1
                            }]
                        };

                        // 차트 생성
                        var ctx = document.getElementById('myChart').getContext('2d');
                        var myChart = new Chart(ctx, {
                            type: 'bar', // 차트 유형 'bar'
                            data: data,
                            options: {
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                        
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    }
                });
            };
            document.head.appendChild(script);
        });

        $("#advanced_ptn_idx").change(function(e) {
            var act = "onchg_ptn_idx";
            var ptn_idx = this.value;
            var pg_uri = $("#advanced_pg_uri").val();

            $.ajax({
                type: "post",
                data: {
                    ptn_idx: ptn_idx,
                    act: act
                },
                url: "land_ajax",
                dataType: "json",
                success:function(result) {
                    var select = $('#advanced_pg_uri');
                    select.empty(); // 기존 옵션 제거

                    if (Array.isArray(result)) {
                        // 배열인 경우, 각 항목을 옵션으로 추가
                        result.forEach(function(item) {
                            select.append($('<option>', { 
                                value: item.value, 
                                text: item.text 
                            }));
                        });
                    } else {
                        select.html(result);
                    }

                    select.selectpicker('refresh'); // selectpicker 업데이트
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    return;
                }
            });
        });

        $("#advanced_pg_uri").change(function(e) {

            var act = "onchg_pg_uri";
            var ptn_idx = $("#advanced_ptn_idx").val();
            var pg_uri = this.value;

            if(pg_uri == "") {

                $("#advanced_pg_uri").val([]).selectpicker('refresh');

            } else {
                var selectedOptions = $(this).val() || [];
                var deselectValue = ""; // "미선택" 옵션의 값

                // 빈 문자열을 제외한 값들만 필터링
                var filteredOptions = selectedOptions.filter(function(value) {
                    return value !== "";
                });

                // 필터링된 값들을 쉼표로 연결
                var pg_uri = filteredOptions.join(',');

                // "미선택"과 다른 옵션이 동시에 선택된 경우, "미선택"을 해제
                if (selectedOptions.length > 1 && selectedOptions.includes(deselectValue)) {
                    selectedOptions = selectedOptions.filter(function(item) {
                        return item !== deselectValue;
                    });
                    $(this).val(selectedOptions).selectpicker('refresh');
                }

                $.ajax({
                    type: "post",
                    data: {
                        pg_uri: pg_uri,
                        ptn_idx: ptn_idx,
                        act: act
                    },
                    url: "land_ajax",
                    dataType: "json",
                    success:function(result) {

                        // var return_val = result['pg_ptn_idx'].replace("'", "");
                        // $("#advanced_ptn_idx").val(return_val).selectpicker('refresh');
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                        alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                        return;
                    }
                });
            }

            

            
                
            
            
            //코드변경시 -> 해당코드의 고객사로 selected 처리함
        });

        // Advanced Search Logic
        $('#advancedSearch').click(function() {

            var advanced_ptn_idx = $('#advanced_ptn_idx').val();
            var advanced_pg_uri = $('#advanced_pg_uri').val();
            var advanced_from = $('#advanced_from').val();
            var advanced_to = $('#advanced_to').val();
            var advanced_db_status = $('#advanced_db_status').val();

            var form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", "./land_list");
            var fields = {
                'stx': 'advanced',
                'advanced_ptn_idx': advanced_ptn_idx,
                'advanced_pg_uri': advanced_pg_uri,
                'advanced_from': advanced_from,
                'advanced_to': advanced_to,
                'advanced_db_status': advanced_db_status
            };
            for (var key in fields) {
                var input = document.createElement("input");
                input.setAttribute("type", "hidden");
                input.setAttribute("name", key);
                input.setAttribute("value", fields[key]);

                form.appendChild(input);
            }
            document.body.appendChild(form);
            form.submit();
            
        });







        //datatable load
        var table = $('#tbl_land').DataTable({
            "paging": false,
            "searching": false,
            "ordering": false,
            "info": false,
            "autoWidth": true,
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
                {responsivePriority : 115   , targets: 17, "width":"1%"},
                {responsivePriority : 116   , targets: 18, "width":"1%"},
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
                input1.setAttribute("value", "DB사용");

                $("#listForm").append(input1);
                $("#listForm").submit();

            } else {
                $(this).prop('checked', false);
            }
        });

    });

    //list form submit validation 
    function listForm_submit(f) {
        
    }

    function validateForm() {
        const level = <?php echo (int)$member['mb_level'] ?>;
        if(level <= 5) {
            const ptnIdxXls = document.getElementById("ptn_idx_xls");
            const pgUri = document.getElementById("pg_uri");

            const isPtnIdxSelected = ptnIdxXls.value && ptnIdxXls.value.trim() !== "";
            const isPgUriSelected = Array.from(pgUri.options).some(option => option.selected);

            // 둘 다 선택되지 않d음
            if (!isPtnIdxSelected && !isPgUriSelected) {
                alert("고객사 혹은 페이지 중 하나를 선택해주세요.");
                return false;
            }
        }

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
            if (!confirm("선택한 고객사 최신화 처리를 진행합니다. 진행하시겠습니까?")) {
                return false;
            } 
        }

        // ==========================================
        // [보안 핵심] 프론트에서는 무조건 서밋을 중단하고 AJAX로 백엔드에 물어봅니다.
        // ==========================================
        
        // 로컬스토리지 타이머 검사 (3분 이내 재요청 방지 - 로그인 로직과 동일)
        var asisDate = window.localStorage.getItem('excel_sms_send_date');
        if(asisDate != '' && asisDate != null) {
            var currentDate = new Date();
            currentDate.setMinutes(currentDate.getMinutes() - 3);
            var year = currentDate.getFullYear();
            var month = String(currentDate.getMonth() + 1).padStart(2, '0');
            var day = String(currentDate.getDate()).padStart(2, '0');
            var hours = String(currentDate.getHours()).padStart(2, '0');
            var minutes = String(currentDate.getMinutes()).padStart(2, '0');
            var seconds = String(currentDate.getSeconds()).padStart(2, '0');
            var formattedDate = year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;

            if(asisDate > formattedDate) {
                alert('인증코드 발송후 3분이 지나지 않았습니다. 기존 인증번호를 입력하거나 잠시 후 이용해주세요.');
                $('#modal-exc-down').modal('hide');
                $('#excelAuthModal').modal('show');
                return false;
            }
        }

        // 백엔드로 IP 검사 및 인증문자 발송 요청
        $.ajax({
            type: "POST",
            url: "land_ajax",
            data: { act: "excel_down_check_pwd" },
            dataType: "json",
            success: function(res) {
                if (res.status === 'password_required') {
                    $('#modal-exc-down').modal('hide');
                    $('#excel_password').val('');
                    $('#excelPasswordModal').modal('show');
                } else {
                    alert(res.message);
                }
            },

            //문자인증 success 코드 주석처리 
            // success: function(res) {
            //     if (res.status === 'pass') {
            //         // 허용된 IP라면 바로 폼 전송 실행
            //         var form = $('#modal-exc-down form');

            //         // act_button 이 없으면 추가
            //         if (form.find('input[name="act_button"]').length === 0) {
            //             $('<input>').attr({
            //                 type: 'hidden',
            //                 name: 'act_button',
            //                 value: '다운'
            //             }).appendTo(form);
            //         }
            //         // jQuery submit() 말고 네이티브 submit()
            //         form[0].submit();
            //     } else if (res.status === 'auth_required') {
            //         // 허용 IP가 아닐 경우 문자 발송 성공 -> 모달 띄우고 타이머 시작
            //         window.localStorage.setItem('excel_sms_send_date', res.insert_date);
            //         $('#auth_phone_txt').text(res.phone + " 발송됨");
            //         $('#modal-exc-down').modal('hide');
            //         $('#excelAuthModal').modal('show');
            //         startExcelTimer(); // 타이머 시작
            //     } else {
            //         alert(res.message);
            //     }
            // },
            error: function() {
                alert('요청 중 오류가 발생했습니다.');
            }
        });

        return false; // 기본 폼 서밋은 무조건 막음
    }

    function validateForm2() {
        $('#modal-exc-upload').modal('hide');
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

    function banIp(ip) {

        if (ip.includes(':')) {
            alert('해당 아이피는 차단할 수 없음');
            return false;
        }

        if (ip == "27.102.82.88") {
            alert('해당 아이피는 차단할 수 없음');
            return false;
        }
        
        var banConfirm = confirm(ip + ' 아이피를 블랙리스트에 등록하시겠습니까?');
        if (banConfirm) {

            var act = "banIp";
            $.ajax({
                type: "post",
                data: {
                    ip:ip,
                    act: act
                },
                url: "land_ajax",
                dataType: "json",
                success:function(result) {
                    alert('차단완료되었습니다.');
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("처리되지않았습니다. 중복된 아이피인지 확인해주세요.");
                    return;
            }});
        }
    }

    function stopToApi(param) {


        var delConfirm = confirm('예약 API를 중단하며 해당 데이터는 API중복 DB(OUT) 으로 이동됩니다.');
        if (delConfirm) {
            
            var act = "stopToApi";
            var land_idx = param;

            $.ajax({
                type: "post",
                data: {
                    land_idx:land_idx,
                    act: act
                },
                url: "land_ajax",
                dataType: "json",
                success:function(result) {
                    $('a[href="javascript:stopToApi(' + param + ');"]').closest('tr').remove();
                    alert(result);
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    return;
            }});
        }
    }

    function stopToSms(param) {

        var delConfirm = confirm('예약 SMS를 중단합니다.');
        if (delConfirm) {
            
            var act = "stopToSms";
            var land_idx = param;

            $.ajax({
                type: "post",
                data: {
                    land_idx:land_idx,
                    act: act
                },
                url: "land_ajax",
                dataType: "json",
                success:function(result) {
                    $('a[href="javascript:stopToSms(' + param + ');"]').find('.fas.fa-pause').remove();
                    alert(result);
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    return;
            }});
        }
    }

    function dup_clear(){
        
        if (confirm("중복 제거 하시겠습니까?")) {
            var form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", "land_list_update");
            var fields = {
                'act_button': "중복제거",
            };
            for (var key in fields) {
                var input = document.createElement("input");
                input.setAttribute("type", "hidden");
                input.setAttribute("name", key);
                input.setAttribute("value", fields[key]);

                form.appendChild(input);
            }
            document.body.appendChild(form);
            form.submit();
        }
    }

    function initAdvSearch(){


        var act = "advanced-search";
        
        $('#advanced_ptn_idx').empty();
        $('#advanced_pg_uri').empty();
        $("#advanced_db_status option:eq(0)").prop("selected", true); //첫번째 option 선택

        var maxDay = moment().format("YYYY-MM-DD");
        var startDay = moment().add(-1, 'month');
        var endDay = moment().format("YYYY-MM-DD");
            
        var toDay = moment().format("YYYY-MM-DD");
        var fromDay = moment().subtract(1, 'month').format("YYYY-MM-DD");

        var dateNow = new Date();

        $('#advanced_from, #advanced_to').daterangepicker({
            
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

            $checkinInput = $('#advanced_from');
            $checkoutInput = $('#advanced_to');

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
        $("#advanced_from").val(fromDay);
        $("#advanced_to").val(toDay);


        var stx = '<?php echo $stx ?>';

        if(stx == "advanced") {
            var advanced_ptn_idx = '<?php echo $advanced_ptn_idx ?>';
            var advanced_pg_uri = '<?php echo $advanced_pg_uri ?>';
            var advanced_from = '<?php echo $advanced_from ?>';
            var advanced_to = '<?php echo $advanced_to ?>';
            var advanced_db_status = '<?php echo $advanced_db_status ?>';

            $('#advanced_ptn_idx').val(advanced_ptn_idx);
            $('#advanced_pg_uri').val(advanced_pg_uri);
            $('#advanced_from').val(advanced_from);
            $('#advanced_to').val(advanced_to);
            $('#advanced_db_status').val(advanced_db_status);

        }
        

        var gubun = "normal";
        if(stx != "") {
            gubun = "advanced";
        }
                

        $.ajax({
            type: "post",
            data: {
                advanced_ptn_idx: advanced_ptn_idx,
                advanced_pg_uri: advanced_pg_uri,
                advanced_from: advanced_from,
                advanced_to: advanced_to,
                advanced_db_status: advanced_db_status,
                gubun: gubun,
                act: act
                
            },
            url: "land_ajax",
            dataType: "json",
            success:function(result) {


                $('#advanced_ptn_idx').empty().append(result[0]).selectpicker('refresh');
                $('#advanced_pg_uri').empty().append(result[1]).selectpicker('refresh');

                

                if (stx == "advanced") {
                if (advanced_ptn_idx) {
                    $('#advanced_ptn_idx').selectpicker('val', advanced_ptn_idx);
                }

                if (advanced_pg_uri) {
                    var pg_uris = advanced_pg_uri.split(',');
                    $('#advanced_pg_uri').selectpicker('val', pg_uris);
                }

                if (advanced_from) {
                    $('#advanced_from').val(advanced_from);
                }
                if (advanced_to) {
                    $('#advanced_to').val(advanced_to);
                }

                if (advanced_db_status) {
                    $('#advanced_db_status').val(advanced_db_status);
                }
            }

                // $('#advanced_ptn_idx').append(result[0]);
                // $('#advanced_pg_uri').append(result[1]);         
                
                // var stx = '<?php echo $stx ?>';
                // var advanced_ptn_idx = '<?php echo $advanced_ptn_idx ?>';
                // var advanced_pg_uri = '<?php echo $advanced_pg_uri ?>';
                // var advanced_from = '<?php echo $advanced_from ?>';
                // var advanced_to = '<?php echo $advanced_to ?>';
                // var advanced_db_status = '<?php echo $advanced_db_status ?>';
                // if (stx == "advanced") {
                //     if (advanced_ptn_idx) {
                //         $('#advanced_ptn_idx').val(advanced_ptn_idx);
                //     }

                //     if (advanced_pg_uri) {
                //         var pg_uris = advanced_pg_uri.split(',');
                //         pg_uris.forEach(function(uri) {
                //             $('#advanced_pg_uri option[value="' + uri + '"]').prop('selected', true);
                //         });
                //         $('#advanced_pg_uri').trigger('change');
                //     }
                // }

                // // Refresh selectpicker after setting the selected values
                // $('#advanced_ptn_idx').selectpicker("refresh");
                // $('#advanced_pg_uri').selectpicker("refresh");


            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                return;
            }
        });


    }

    function openMapPopup(lat, lng) {
        var url = "https://www.openstreetmap.org/?mlat=" + lat + "&mlon=" + lng + "#map=13/" + lat + "/" + lng;
        var windowName = "mapPopup";
        var windowSize = "width=800, height=600";

        window.open(url, windowName, windowSize);
    }


    // [변경] 엑셀 비밀번호 재확인 폼 전송
    $(function(){
        $("#excelPasswordForm").submit(function(event) {
            event.preventDefault();

            var excelPassword = $("#excel_password").val().trim();
            if (excelPassword === "") {
                alert("비밀번호를 입력해주세요.");
                $("#excel_password").focus();
                return false;
            }

            $.ajax({
                type: "POST",
                url: "land_ajax",
                data: { 
                    act: "excel_password_verify", 
                    excel_password: excelPassword 
                },
                dataType: "json",
                success: function(res) {
                    if (res.status === "success") {
                        // 화이트IP -> 비밀번호만 확인 후 바로 다운로드
                        $('#excelPasswordModal').modal('hide');

                        var form = $('#modal-exc-down form');

                        if (form.find('input[name="act_button"]').length === 0) {
                            $('<input>').attr({
                                type: 'hidden',
                                name: 'act_button',
                                value: '다운'
                            }).appendTo(form);
                        }

                        form[0].submit();

                    } else if (res.status === "auth_required") {
                        // 비화이트IP -> 비밀번호 성공 후 문자 인증까지 진행
                        $('#excelPasswordModal').modal('hide');

                        window.localStorage.setItem('excel_sms_send_date', res.insert_date);
                        $('#auth_phone_txt').text(res.phone + " 발송됨");
                        $('#excel_auth_code').val('');
                        $('#excelAuthModal').modal('show');
                        startExcelTimer();

                    } else {
                        alert(res.message);
                        $("#excel_password").val('').focus();
                    }
                },
                error: function() {
                    alert('비밀번호 확인 중 오류가 발생했습니다.');
                }
            });
        });
    });


    // 문자 인증 폼 전송
    var excelTimer;
    $(function(){
        $("#excelAuthForm").submit(function(event) {
            event.preventDefault();
            var validchk = $("#excel_auth_code").val().length;
            if(validchk != 6) {
                alert("인증코드가 올바르지 않습니다.");
                return false;
            }

            $.ajax({
                type: "POST",
                url: "land_ajax",
                data: { act: "excel_auth_verify", auth_code: $("#excel_auth_code").val() },
                dataType: "json",
                success: function(res) {
                    if (res.status === "success") {
                        window.localStorage.removeItem('excel_sms_send_date');
                        $('#excelAuthModal').modal('hide');
                        clearInterval(excelTimer);
                        
                        // ========================================================
                        // [버그 수정된 부분]
                        // 1. id="modal_form"이 중복되므로, 엑셀 모달 안의 폼을 정확히 지칭합니다.
                        var form = $('#modal-exc-down form');
                        
                        // 2. 버튼 값을 태워서 보냅니다.
                        $('<input>').attr({type: 'hidden', name: 'act_button', value: '다운'}).appendTo(form);
                        
                        // 3. jQuery의 submit()이 아닌, 네이티브 폼 submit()을 사용하여
                        // onSubmit="return validateForm()"을 타지 않고 바로 land_exceldown.php 로 쏴버립니다.
                        form[0].submit(); 
                        // ========================================================

                    } else {
                        alert(res.message); // 실패 횟수(3회 초과 등) 메시지 출력
                        if (res.status === "blocked") {
                            $('#excelAuthModal').modal('hide');
                            clearInterval(excelTimer);
                        }
                    }
                },
                error: function() {
                    alert('인증 요청 중 오류가 발생했습니다.');
                }
            });
        });
    });

    // 타이머 함수
    function startExcelTimer() {
        if (excelTimer) clearInterval(excelTimer);
        $('#btn_excel_auth').find('span').remove();
        document.getElementById('btn_excel_auth').disabled = false;

        var countdown = 179; // 3분
        var countdownElement = document.createElement('span');
        countdownElement.style.marginLeft = '10px';
        countdownElement.style.fontWeight = 'bold';
        countdownElement.textContent = "(2:59)";
        document.getElementById('btn_excel_auth').appendChild(countdownElement);

        excelTimer = setInterval(function() {
            var minutes = Math.floor(countdown / 60);
            var seconds = countdown % 60;
            countdownElement.textContent = "(" + minutes + ":" + (seconds < 10 ? "0" : "") + seconds + ")";
            countdown--;
            if (countdown < 0) {
                countdownElement.textContent = "(만료됨)";
                clearInterval(excelTimer);
                document.getElementById('btn_excel_auth').disabled = true;
                document.getElementById('excel_auth_code').value = "";
                alert("문자인증 시간이 만료되었습니다. 다시 시도해주세요.");
                $('#excelAuthModal').modal('hide');
            }
        }, 1000);
    }


</script>

<?php
include_once(G5_PATH . '/tail.php');
