<?php

require_once '../../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');

$g5['title'] = "키워드 정산시스템";
include_once(G5_PATH . '/head.php');

// 조회조건에 값이있으면 이값 쓰면안됨
$currentMonth = date('Y-m');

// <select> 태그의 옵션으로 넣어줄 문자열을 시작합니다.
$options = "";





$sql_columns    = "";
$sql_common     = "";
$sql_search     = "";
$sql_gruop      = "";
$sql_order      = "";
$total_count    = 0;


//select
$sql_columns = "
  c.mb_name 
, b.chg_emp_no 
, a.*
";

$sql_common = "
from gnp_kwd_calc_main a
left join gnp_kwd_calc_charge b on a.calc_naver_id = b.chg_naver_id 
left join gnp_member c on b.chg_emp_no = c.mb_no 
";

$add_cont = "";

$add_opt_sql = "";

$is_upload = false;

//$is_comp_chk = false;

if ($member['mb_deptno'] != "9") {
    if ($member['mb_level'] <= 6) {

        if ($member['mb_level'] == 4) {
            $add_cont = "and b.chg_emp_no = {$member['mb_no']}";

            $nomral_emp_sql = "
            select count(*) as cnt
              from gnp_kwd_calc_confirm
             where conf_mb_no = {$member['mb_no']}
               and conf_yyyymm = '{$curr_month}'
            ";
            $conf = sql_fetch($nomral_emp_sql);
            $conf_count = $conf['cnt'];

            $is_conf = "";
            if($conf_count > 0) {
                $is_conf = "disabled";
            }

        } else {
            $add_opt_sql = "
            select *
              from gnp_member 
             where mb_deptno = 11
             order by mb_name
            ";
            $add_opt_result = sql_query($add_opt_sql);

            $is_upload = true;

            //$is_comp_chk = true;
        }
    }
}




// $max_month_sql = "
// select max(calc_month) as curr_month
// from gnp_kwd_calc_main 
// ";

// $curr_month = "";
// $max_result = sql_fetch($max_month_sql);
// $curr_month = $max_result['curr_month'];

// 이번 달부터 과거 6개월까지 반복합니다.
// for ($i = 0; $i < 6; $i++) {
//     $month = date('Ym', strtotime("-$i month"));
//     if($stx_calc_month) {
//         if($stx_calc_month == $month) {
//             $selected = 'selected';
//             $curr_month = $stx_calc_month;
//         }
//     } else {
//         if($month == $curr_month) {
//             $selected = 'selected';
//         }
//     }
//     $options .= "<option value='{$month}' {$selected}>{$month}</option>";
//     $selected = "";
// }


//쿼리 기준이 아닌 현재월 기준으로 변경
$options = '';
$currentMonth = date('Y-m-01'); // 이번 달의 첫 날
$stx_calc_month = isset($stx_calc_month) && !empty($stx_calc_month) ? $stx_calc_month : date('Ym', strtotime($currentMonth . ' -1 month')); // 입력된 값이 없으면 기본값 설정

for ($i = 0; $i < 6; $i++) {
    $month = date('Ym', strtotime($currentMonth . " -{$i} months"));
    $monthLabel = date('Ym', strtotime($currentMonth . " -{$i} months"));
    $selected = ($stx_calc_month == $month) ? 'selected' : ''; 
    $options .= "<option value='{$month}' {$selected}>{$monthLabel}</option>";
}


$sql_search = "
where 1=1
and b.chg_emp_no is not null
and b.chg_emp_no != ''
{$add_cont}
";


if ($stx_calc_comp) {
    $sql_search .= "and a.calc_comp = '{$stx_calc_comp}'";
} else {
    $sql_search .= "and a.calc_comp = 'prog'";
    $stx_calc_comp = 'prog';
}

if ($stx_calc_month) {
    $sql_search .= "and a.calc_month = '{$stx_calc_month}'";
} else {
    $sql_search .= "and a.calc_month = '{$curr_month}'";
}

if ($stx_mb_no) {
    $sql_search .= "and c.mb_no = {$stx_mb_no}";
}


// 각 input 값들을 변수에 저장합니다.
$stx_calc_comp = $stx_calc_comp;
$stx_calc_month = $stx_calc_month;
$stx_mb_no = $stx_mb_no;

// key-value 형태의 배열 생성
$stx_array = array(
    'stx_calc_comp' => $stx_calc_comp,
    'stx_calc_month' => $stx_calc_month,
    'stx_mb_no' => $stx_mb_no
);

// 배열을 JSON 형식으로 인코딩
$stx_json = json_encode($stx_array);


$cnt_sql = "
select count(*) as cnt
from gnp_kwd_calc_main a
left join gnp_kwd_calc_charge b on a.calc_naver_id = b.chg_naver_id 
left join gnp_member c on b.chg_emp_no = c.mb_no 
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

// if($is_comp_chk == true){
//     $add_order = "a.calc_comp, ";
// }

if (!$sst) {
    //$sql_order = "order by a.land_idx desc";
    $sql_order = "order by c.mb_name, ".$add_order."a.calc_naver_id";
} else {

    if($sst == "c.mb_name") {
        $sql_order = " order by $sst $sod ";
    } else {
        $sql_order = " order by c.mb_name, $sst $sod ";
    }
}

$sql = " select {$sql_columns} {$sql_common} {$sql_search} {$sql_order}";
$result = sql_query($sql);

$tr_num1 = $from_record +1;





$sql_columns2    = "";
$sql_common2     = "";
$sql_search2     = "";
$sql_gruop2      = "";
$sql_order2      = "";
$total_count2    = 0;


$sql_search2 = "
where 1=1
and (b.chg_emp_no is null or b.chg_emp_no = '')
";

if ($stx_calc_comp) {
    $sql_search2 .= "and a.calc_comp = '{$stx_calc_comp}'";
} else {
    $sql_search2 .= "and a.calc_comp = 'prog'";
}

if ($stx_calc_month) {
    $sql_search2 .= "and a.calc_month = '{$stx_calc_month}'";
}


$sql_columns2 = "
  c.mb_name 
, b.chg_emp_no 
, a.*
";

$sql_common2 = "
from gnp_kwd_calc_main a
left join gnp_kwd_calc_charge b on a.calc_naver_id = b.chg_naver_id 
left join gnp_member c on b.chg_emp_no = c.mb_no 
";



$cnt_sql2 = "
select count(*) as cnt
from gnp_kwd_calc_main a
left join gnp_kwd_calc_charge b on a.calc_naver_id = b.chg_naver_id 
left join gnp_member c on b.chg_emp_no = c.mb_no 
{$sql_search2}
";
$row2 = sql_fetch($cnt_sql2);
$total_count2 = $row2['cnt'];

//$rows = $config['cf_page_rows'];
$rows2 = 100;
$total_page2  = ceil($total_count2 / $rows2);
if ($page2 < 1) {
    $page2 = 1;
}
$from_record2 = ($page - 1) * $rows2;

if (!$sst2) {
    //$sql_order = "order by a.api_ins_date desc";
    $sql_order2 = "order by a.calc_naver_id asc";
} else {
    $sql_order2 = " order by $sst $sod ";
}

$sql2 = " select {$sql_columns2} {$sql_common2} {$sql_search2} {$sql_order2}";
$result2 = sql_query($sql2);

$tr_num2 = $from_record2 +1;


$confirm_sql = "
select count(*) as cnt
  from gnp_kwd_calc_confirm 
 where conf_mb_no = {$member['mb_no']}
   and conf_yyyymm = '{$curr_month}'
";
$confirm = sql_fetch($confirm_sql);

$confirm_cnt = $confirm['cnt'];

$is_confirm = "";

if($total_count == 0) {
    $is_confirm = "disabled";
} else if($confirm_cnt > 0) {
    $is_confirm = "disabled";
}



?>


<style>
#tbl_list2 tbody tr:hover {
    background-color: #f2f2f2; /* 원하는 색상으로 변경 */
}
</style>


<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">키워드 정산시스템(<?php echo $total_count ?>)</h3>
                    </div>
                    <div class="card-body">
                        <div class="dataTables_wrapper dt-bootstrap4">

                            <div class="d-flex flex-sm-row flex-column justify-content-sm-between">
                                <div class="d-flex justify-content-center mb-2 mb-xs-0">
                                    <div class="btn-group xs-100">

                                        <button type="button" class="btn btn-success btn-xs border border-dark" onclick="excelDown()">
                                            <i class="fas fa-file-download"></i> 엑셀다운로드
                                        </button>


                                        <?php if($member['mb_level'] >= 6) { ?>
                                            <button type="button" class="btn btn-success btn-xs border border-dark" data-toggle="modal" data-target="#modal-exc-upload">
                                                <i class="fas fa-file-upload"></i> 엑셀업로드
                                            </button>

                                            <button type="button" class="btn btn-primary btn-xs border border-dark" data-toggle="modal" data-target="#modal-sel_emp">
                                                <i class="fas fa-user-circle"></i> 계정 관리
                                            </button>

                                            <button type="button" class="btn btn-info btn-xs border border-dark" data-toggle="modal" data-target="#modal-conf_stat">
                                                <i class="fas fa-list"></i> 현황
                                            </button>
                                        <?php } else { ?>
                                            <button type="button" id="btn_conf" class="btn btn-info btn-xs border border-dark" onclick="confirm()" <?php echo $is_confirm ?>>
                                                <i class="fas fa-check"></i> 정산확인
                                            </button>
                                        <?php }  ?>

                                    </div>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <div class="btn-group xs-100">

                                        <form class="form-inline my-2 my-lg-0 ng-pristine ng-valid">
                                           
                                            <select id="search_calc_comp" name="stx_calc_comp" class="form-control border border-secondary">
                                                <option value="prog" <?php echo  get_selected($stx_calc_comp, "prog") ?>>프로그레스</option>
                                                <option value="ampm" <?php echo  get_selected($stx_calc_comp, "ampm") ?>>AMPM</option>
                                            </select>
                                            
                                            <select id="search_calc_month" name="stx_calc_month" class="form-control border border-secondary">
                                                <?php echo $options; ?>
                                            </select>
                                            
                                            <select id="search_mb_no" name="stx_mb_no" class="selectpicker form-control" data-live-search="true" data-style="border border-secondary" data-width="200px" data-size="10">
                                                <option value="">미선택</option>
                                                <?php for ($i = 0; $add_opt = sql_fetch_array($add_opt_result); $i++) { ?>
                                                    <option value="<?php echo $add_opt['mb_no'] ?>" data-tokens="<?php echo $add_opt['mb_no'] ?>" <?php echo  get_selected($stx_mb_no, $add_opt['mb_no']) ?>><?php echo $add_opt['mb_name'] ?></option>
                                                <?php } ?>
                                            </select>

                                            <button type="submit" class="btn btn-outline-success my-2 my-sm-0">검색</button>
                                        </form>
                                    </div>
                                </div>
                            </div>


                            <form name="listForm" id="listForm" action="./kwd_customers_list_update" method="post">
                                <input type="hidden" name="sst" value="<?php echo $sst ?>">
                                <input type="hidden" name="sod" value="<?php echo $sod ?>">
                                <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
                                <input type="hidden" name="stx" value="<?php echo $stx ?>">
                                <input type="hidden" name="page" value="<?php echo $page ?>">
                                <input type="hidden" name="token" value="<?php echo isset($token) ? $token : ''; ?>">

                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="tbl_list" class="table table-striped table-bordered dt-responsive nowrap landpg-font-size" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>NO</th>
                                                    <th><?php echo get_sort_custom('c.mb_name', '', 'desc', $sst, $sod, '이름', $stx_json); ?></th>
                                                    <th>업체</th>
                                                    <th>년월</th>
                                                    <th><?php echo get_sort_custom('calc_media', '', 'desc', $sst, $sod, '미디어', $stx_json); ?></th>
                                                    <th><?php echo get_sort_custom('calc_acct', '', 'desc', $sst, $sod, '계정명', $stx_json); ?></th>
                                                    <th>네이버ID</th>
                                                    <th>공급가</th>
                                                    <th>세액</th>
                                                    <th>총액</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $prev_emp_no = null;
                                                $totals = ['calc_supl_amount' => 0, 'calc_tax_amount' => 0, 'calc_sum_amount' => 0];

                                                while ($row = sql_fetch_array($result)) {
                                                    // 변환하지 않고, 데이터베이스에서 바로 숫자 형식으로 값을 가져오는 것이 좋습니다.
                                                    // 콤마 등의 형식화 문자가 포함되어 있다면, PHP에서 str_replace 또는 similar를 사용하여 제거해야 합니다.

                                                    // Check if we've moved to a new employee
                                                    if ($prev_emp_no !== null && $prev_emp_no !== $row['chg_emp_no']) {
                                                        // Print totals row for the previous employee
                                                        echo "<tr>";
                                                        echo "<td colspan='7' class='text-right'><strong>합:</strong></td>";
                                                        echo "<td class='text-right'><strong>" . number_format($totals['calc_supl_amount']) . "</strong></td>";
                                                        echo "<td class='text-right'><strong>" . number_format($totals['calc_tax_amount']) . "</strong></td>";
                                                        echo "<td class='text-right'><strong>" . number_format($totals['calc_sum_amount']) . "</strong></td>";
                                                        echo "</tr>";
                                                        // Reset totals
                                                        $totals = ['calc_supl_amount' => 0, 'calc_tax_amount' => 0, 'calc_sum_amount' => 0];
                                                    }

                                                    // Update totals
                                                    $totals['calc_supl_amount'] += $row['calc_supl_amount'];
                                                    $totals['calc_tax_amount'] += $row['calc_tax_amount'];
                                                    $totals['calc_sum_amount'] += $row['calc_sum_amount'];

                                                    // Save the current employee number
                                                    $prev_emp_no = $row['chg_emp_no'];

                                                    // Print the normal row
                                                    echo "<tr>";
                                                    echo "<td class='text-center'>" . ($tr_num1++) . "</td>";
                                                    echo "<td>" . $row['mb_name'] . "</td>";
                                                    echo "<td>" . $row['calc_comp'] . "</td>";
                                                    echo "<td>" . $row['calc_month'] . "</td>";
                                                    echo "<td>" . $row['calc_media'] . "</td>";
                                                    echo "<td>" . $row['calc_acct'] . "</td>";
                                                    echo "<td>" . $row['calc_naver_id'] . "</td>";
                                                    echo "<td class='text-right'>" . number_format($row['calc_supl_amount']) . "</td>";
                                                    echo "<td class='text-right'>" . number_format($row['calc_tax_amount']) . "</td>";
                                                    echo "<td class='text-right'>" . number_format($row['calc_sum_amount']) . "</td>";
                                                    echo "</tr>";
                                                }

                                                // Print the last employee's totals if any rows have been processed
                                                if ($prev_emp_no !== null) {
                                                    echo "<tr>";
                                                    echo "<td colspan='7' class='text-right'><strong>합:</strong></td>";
                                                    echo "<td class='text-right'><strong>" . number_format($totals['calc_supl_amount']) . "</strong></td>";
                                                    echo "<td class='text-right'><strong>" . number_format($totals['calc_tax_amount']) . "</strong></td>";
                                                    echo "<td class='text-right'><strong>" . number_format($totals['calc_sum_amount']) . "</strong></td>";
                                                    echo "</tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center justify-content-sm-end">

                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="row">
            <div class="col-12">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title">담당자를 찾습니다.(<?php echo $total_count2 ?>)</h3>
                    </div>
                    <div class="card-body">
                        <div class="dataTables_wrapper dt-bootstrap4">

                            <form name="listForm" id="listForm" action="./kwd_customers_list_update" method="post">
                                <input type="hidden" name="sst" value="<?php echo $sst ?>">
                                <input type="hidden" name="sod" value="<?php echo $sod ?>">
                                <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
                                <input type="hidden" name="stx" value="<?php echo $stx ?>">
                                <input type="hidden" name="page" value="<?php echo $page ?>">
                                <input type="hidden" name="token" value="<?php echo isset($token) ? $token : ''; ?>">

                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="tbl_list2" class="table table-striped table-secondary table-bordered dt-responsive nowrap landpg-font-size" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th>NO</th>
                                                    <th>이름</th>
                                                    <th>업체</th>
                                                    <th>년월</th>
                                                    <th>미디어</th>
                                                    <th>계정명</th>
                                                    <th>네이버ID</th>
                                                    <th>공급가</th>
                                                    <th>세액</th>
                                                    <th>총액</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            while ($row2 = sql_fetch_array($result2)) {
                                                echo "<tr>";
                                                echo "<td class='text-center'>" . ($tr_num2++) . "</td>";
                                                echo "<td></td>";
                                                echo "<td>" . $row2['calc_comp'] . "</td>";
                                                echo "<td>" . $row2['calc_month'] . "</td>";
                                                echo "<td>" . $row2['calc_media'] . "</td>";
                                                echo "<td>" . $row2['calc_acct'] . "</td>";
                                                echo "<td>" . $row2['calc_naver_id'] . "</td>";
                                                echo "<td>" . number_format($row2['calc_supl_amount']) . "</td>";
                                                echo "<td>" . number_format($row2['calc_tax_amount']) . "</td>";
                                                echo "<td>" . number_format($row2['calc_sum_amount']) . "</td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                            </tbody>
                                        </table>
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



<div class="modal fade" id="modal-exc-upload" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h4 class="modal-title">엑셀 업로드 <i class="fas fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="엑셀업로드시 빈셀에 커서있을시 에러가 날수있습니다."></i></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="modal_form" name="modal_form" action="./kwd_calc_emp_update" method="post" onSubmit="return validateForm2()" enctype="multipart/form-data">
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
                        <select id="calc_month" name="calc_month" class="form-control">
                            <?php echo $options; ?>
                        </select>
                    </div>
                    

                    <div class="input-group mb-3" id="closed_time">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="inputGroup-sizing-default">업체</span>
                        </div>
                        <select id="calc_comp" name="calc_comp" class="form-control">
                            <option value="prog">프로그레스</option>
                            <option value="ampm">AMPM</option>
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
                    <button type="submit" class="btn btn-primary" name="act_button" value="업로드">업로드</button>
                </div>
            </form>
        </div>
    </div>
</div>



<div class="modal fade" id="modal-sel_emp" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modal-sel_empLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-sel_empLabel">직원 목록</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table id="empTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>NAVER ID</th>
                            <th>사번</th>
                            <th>이름</th>
                            <th>작업</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- 데이터는 AJAX를 통해 동적으로 채워집니다 -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="newEmpButton">신규</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="newEmpModal" tabindex="-1" role="dialog" aria-labelledby="newEmpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newEmpModalLabel">신규 직원 추가</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="modal_form" name="modal_form" action="./kwd_calc_emp_update" method="post" onSubmit="return validateForm2()">
                <div class="modal-body">
                        <div class="form-group">
                            <label for="newEmpId">NAVER ID</label>
                            <input type="text" class="form-control" id="chg_naver_id" name="chg_naver_id" placeholder="NAVER ID 입력">
                        </div>
                        
                        <div class="form-group">
                            <label for="newEmpTest">직원</label>
                            <select class="form-control" id="chg_emp_no" name="chg_emp_no">
                                
                            </select>
                        </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" name="act_button" value="신규계정저장">저장</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                </div>
            </form>
        </div>
    </div>
</div>



<div class="modal fade" id="modal-conf_stat" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modal-conf_statLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-conf_statLabel">직원 목록</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table id="empTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>순서</th>
                            <th>이름</th>
                            <th>기준년월</th>
                            <th>확인일시</th>
                        </tr>
                    </thead>
                    <tbody id="dynamic-tbody">
                    
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                
                <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
            </div>
        </div>
    </div>
</div>

<!-- <div id="loading-overlay" class="loading-overlay">
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
</div> -->



<script>

$(function() {

    document.querySelector('.custom-file-input').addEventListener('change',function(e){
        var fileName = document.getElementById("fileInput").files[0].name;
        var nextSibling = e.target.nextElementSibling
        nextSibling.innerText = fileName
    })

    $('#tbl_list2 tbody').on('mouseenter', 'tr', function () {
        $(this).addClass('highlight');
    }).on('mouseleave', 'tr', function () {
        $(this).removeClass('highlight');
    });


    //직원 목록 가져오는 MODAL 기본 셋팅해둠
    var empTable = $('#empTable').DataTable({
        "autoWidth": false, // 열 너비 자동 조절 비활성화
        "info": false,
        "details": true,
        "lengthChange": false,
        "columnDefs": [
            { "width": "65%", "targets": 0 }, // ID 열 너비
            { "visible": false, "targets": 1 }, // 사번 열 숨김
            { "width": "20%", "targets": 2 }, // 이름 열 너비
            { "width": "15%", "targets": 3 }, // 작업 열 너비
            {
                "targets": -1, // 마지막 열에 버튼 추가
                "data": null,
                "defaultContent": "<button class='btn-delete btn-xs'>삭제</button>"
            }
        ],
        "order": [] // 정렬 비활성화
        
    });

    var table2 = $('#tbl_list2').DataTable({
        "paging": false,
        "searching": false,
        "ordering": false,
        "info": false,
        "autoWidth": true,
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        "columnDefs":[
            {responsivePriority : 0     , targets: 0, "width":"1%"},
            {responsivePriority : 1     , targets: 1, "width":"1%",
                render: function(data, type, row) {
                    if (type === 'display') {
                        return "<button type='button' class='btn btn-warning btn-xs btn-block pick-button'>픽업</button>";
                    }
                }
            },
            {responsivePriority : 2     , targets: 2},
            {responsivePriority : 3     , targets: 3},
            {responsivePriority : 4     , targets: 4},
            {responsivePriority : 5     , targets: 5},
            {responsivePriority : 6     , targets: 6},
            {responsivePriority : 7     , targets: 7},
            {responsivePriority : 8     , targets: 8},
            {responsivePriority : 9     , targets: 9},
        ]
    });

    $('#tbl_list2').on('click', '.pick-button', function() {
        var data = table2.row($(this).parents('tr')).data();
        var naver_id = data[6];
        var my_emp = <?php echo $member['mb_no'] ?>;
        var act = "pick_ownerless";

        // prompt 사용하여 사용자 입력 받기
        var userInput = prompt(naver_id + " 계정을 픽업 하시겠습니까?", "취소를 원하시면 취소버튼 누르세요");

        // 사용자가 '확인'을 눌렀을 때만 실행
        if (userInput !== null) {
            $.ajax({
                type: "post",
                data: {
                    my_emp: my_emp,
                    naver_id: naver_id,
                    act: act
                },
                url: "kwd_ajax",
                dataType: "json",
                success: function(result) {
                    window.location.reload();
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    return;
                }
            });
        }
    });
    
    //모달에서 삭제버튼 클릭하면 ~
    $('#empTable tbody').on('click', 'button.btn-delete', function() {

        var userInput = prompt("해당 계정을 삭제하겠습니까?", "취소를 원하시면 취소버튼 누르세요");

        if (userInput !== null) {
            var data = empTable.row($(this).parents('tr')).data();
            var naver_id = data[0];
            var emp_no = data[1];
            var act = "del_calc_emp";
            var $button = $(this);
            
            $.ajax({
                type: "post",
                data: {
                    naver_id:naver_id,
                    emp_no: emp_no,
                    act: act
                },
                url: "kwd_ajax",
                dataType: "json",
                success:function(result) {
                    var row = empTable.row($button.parents('tr'));
                    row.remove().draw(false);
                    alert("계정이 삭제되었습니다.");
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    return;
                }
            });
        }
    });
    
    // 계정관리 버튼 클릭시 event
    $('#modal-sel_emp').on('show.bs.modal', function (e) {
        var act = "get_calc_emp";

        $.ajax({
            type: "post",
            data: {
                act: act
            },
            url: "kwd_ajax",
            dataType: "json",
            success: function(data) {
                empTable.clear();
                for (var i = 0; i < data.length; i++) {
                    empTable.row.add([
                        data[i].chg_naver_id,
                        data[i].chg_emp_no,
                        data[i].chg_emp_name
                    ]).draw(false);
                }
            }
        });
    });


    $('#modal-conf_stat').on('show.bs.modal', function (e) {
        var act = "conf_stat";
        var cur_month = '<?php echo $curr_month ?>';
        $("#dynamic-tbody").empty();
        
        $.ajax({
            type: "post",
            data: {
                act: act
              , cur_month: cur_month
            },
            url: "kwd_ajax",
            dataType: "json",
            success: function(data) {
                $("#dynamic-tbody").append(data);
            }
        });
    });


    //계정관리 -> 신규 버튼 클릭시 모달
    $('#newEmpButton').on('click', function() {

        var act = "get_emp_list";
        var chg_naver_id = $("#newEmpModal #chg_naver_id").val();
        var chg_emp_no = $("#newEmpModal #chg_emp_no").val();
        
        $.ajax({
            type: "post",
            data: {
                chg_naver_id:chg_naver_id,
                chg_emp_no: chg_emp_no,
                act: act
            },
            url: "kwd_ajax",
            dataType: "json",
            success:function(result) {
                $("#newEmpModal #chg_emp_no").append(result[0]);
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                return;
            }
        });


        $('#newEmpModal').modal('show');
    });



    var table = $('#tbl_list').DataTable({
        "paging": false,
        "searching": false,
        "ordering": false,
        "info": false,
        "autoWidth": true,
        "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        "columnDefs":[
            {responsivePriority : 0     , targets: 0, "width":"1%"},
            {responsivePriority : 1     , targets: 1, "width":"2%"},
            {responsivePriority : 2     , targets: 2},
            {responsivePriority : 3     , targets: 3},
            {responsivePriority : 4     , targets: 4},
            {responsivePriority : 5     , targets: 5},
            {responsivePriority : 6     , targets: 6},
            {responsivePriority : 7     , targets: 7},
            {responsivePriority : 8     , targets: 8},
            {responsivePriority : 9     , targets: 9},
        ]
    });

    
});

function validateForm2() {
    $('#modal-exc-upload').modal('hide');
    //$('#loading-overlay').show(); // 로딩 오버레이를 표시
    showLoading();
    return true;
}


function confirm(){
    var param = '<?php echo $curr_month ?>';
    var act = "confirm";
    $.ajax({
        type: "post",
        data: {
            param:param,
            act: act
        },
        url: "kwd_ajax",
        dataType: "json",
        success:function(result) {

            if(result == 'success') {
                alert('<?php echo $curr_month?> 정산 확인했습니다.');
            } else {
                alert('<?php echo $curr_month?> 이미 정산 했습니다.');
            }

            $('#btn_conf').attr("disabled"   , true);
            
        },
        error: function(xhr) {
            console.log(xhr.responseText);
            alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
            return;
        }
    });
}
function excelDown(){

    var stx_calc_comp = '<?php echo $stx_calc_comp ?>';
    var stx_calc_month = $('#search_calc_month').val();

    var form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "kwd_calc_emp_update");
    var fields = {
        'act_button': "엑셀다운",
        'stx_calc_comp': stx_calc_comp,
        'stx_calc_month': stx_calc_month
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
</script>


<?php


include_once(G5_PATH . '/tail.php');


