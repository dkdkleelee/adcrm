<?php
require_once '../../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');

$g5['title'] = "시스템 접근로그";
include_once(G5_PATH . '/head.php');

$sql_columns    = "";
$sql_common     = "";
$sql_search     = "";
$sql_gruop      = "";
$sql_order      = "";
$total_count    = 0;

//select
$sql_columns = "
   hist_idx
  ,hist_join_gubun
  ,hist_function
  ,hist_mb_no
  ,concat('(', c.ptn_nm, ')' , hist_mb_name) as hist_mb_name
  ,hist_detail
  ,client_ip
  ,a.insert_date
";

$sql_common = "
from {$g5['record_hist']} a
left join {$g5['member_table']} b on a.hist_mb_no = b.mb_no 
left join {$g5['crm_partner']} c on b.mb_ptnidx = c.ptn_idx 
";

$sql_search = "
where 1=1
and hist_join_gubun = '고객사'
";



//search
if ($search_empno) {
    $sql_search .= " and hist_mb_no = {$search_empno}";
} 

if ($search_empno == "0") {
    $sql_search .= " and hist_mb_no = 0";
} 


if ($search_function) {
    $sql_search .= " and hist_function = '{$search_function}'";    
} 

$cnt_sql = " 
select count(*) as cnt
from {$g5['record_hist']} a
{$sql_search}
";
$row = sql_fetch($cnt_sql);
$total_count = $row['cnt'];

$rows = 500;
$total_page  = ceil($total_count / $rows);
if ($page < 1) {
    $page = 1;
}
$from_record = ($page - 1) * $rows;

if (!$sst) {
    $sql_order = "order by hist_idx desc";
}else{
    $sql_order = " order by $sst $sod ";    
}

$sql = " select {$sql_columns} {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$rownum = $total_count - $from_record;





$emp_sql = "
select mb_no
     , concat('(',b.ptn_nm , ') ' , a.mb_name) as mb_name  
  from {$g5['member_table']} a
  left join {$g5['crm_partner']} b on a.mb_ptnidx = b.ptn_idx 
 where mb_ptnidx is not null
 order by b.ptn_nm asc
";
$emp_sql = sql_query($emp_sql);

$emp_html = '<option value="">전체</option>';
if ($search_empno == "0") {
    $emp_html .= '<option value="0" selected>비회원</option>';
} else {
    $emp_html .= '<option value="0">비회원</option>';
}

$emp_seled = "";

for ($i = 0; $emp = sql_fetch_array($emp_sql); $i++) {
    if($search_empno == $emp['mb_no']) {
        $emp_html .= '<option value="'.$emp['mb_no'].'" selected>'.$emp['mb_name'].'</option>';
    } else {
        $emp_html .= '<option value="'.$emp['mb_no'].'">'.$emp['mb_name'].'</option>';
    }
}


?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">시스템 접근로그</h3>
                    </div>

                    <div class="card-body">
                        <div class="dataTables_wrapper dt-bootstrap4">
                            <div class="d-flex flex-sm-row flex-column justify-content-sm-between">
                                <div class="d-flex justify-content-center mb-2 mb-sm-0">
                                    <div class="btn-group xs-100">
                                        
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <div class="btn-group xs-100">

                                        <form class="form-inline my-2 my-lg-0 ng-pristine ng-valid">
                                            
                                            <select id="search_empno" name="search_empno" class="form-control " data-live-search="true" data-style="border border-secondary" >
                                                <?php echo $emp_html ?>
                                            </select>

                                            <select id="search_function" name="search_function" class="form-control " data-live-search="true" data-style="border border-secondary" >
                                                <option value="">전체</option>
                                                <option value="login" <?php echo get_selected($search_function, "login"); ?>>login</option>
                                                <option value="exceldown" <?php echo get_selected($search_function, "exceldown"); ?>>exceldown</option>                                                
                                            </select>


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
                                                    <th>NO</th>
                                                    <th>구분</th>
                                                    <th>기능</th>
                                                    <th>작업자(고객사)</th>
                                                    <th>작업상세</th>
                                                    <th>작업IP</th>
                                                    <th>작업일시</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php for ($i = 0; $row = sql_fetch_array($result); $i++) { 
                                            //$isAssign = $row['pg_ptn_idx'] != "" ? "" : "class='table-danger'";
                                            ?>
                                            <tr <?php echo $isAssign?> >
                                                <td>
                                                    <?php echo $rownum; $rownum = $rownum - 1; ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['hist_join_gubun'] ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['hist_function'] ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['hist_mb_name'] ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['hist_detail'] ?>
                                                </td>
                                               
                                                <td>
                                                    <?php echo $row['client_ip'] ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['insert_date'] ?>
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


<script>
    
       
</script>

<?php
include_once(G5_PATH . '/tail.php');
