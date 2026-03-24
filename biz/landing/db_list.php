<?php
require_once '../../common.php';

$g5['title'] = "DB조회";
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
  @rownum := @rownum + 1 as rownum
  , a.land_idx 
  , a.land_pg_idx
  , a.name 
  , a.tel 
  , a.option1
  , a.option2
  , a.option3
  , b.pg_chk_data4
  , a.option4
  , b.pg_chk_data5
  , a.option5
  , b.pg_chk_data6
  , a.option6
  , a.insert_date 
  , a.client_ip
";

$sql_common = "
from {$g5['crm_landing']} a
left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx
, (select @rownum := 0) r
";



if($member['mb_gubun'] == "P") {
    //대표
    $sql_search .= "
    where b.pg_ptn_idx = {$member['mb_ptnidx']}
    and a.insert_date <= now()
    and a.use_yn = 'Y'
    ";
    
} else {
    //직원
    $sql_search .= "
    where b.pg_ptn_idx = {$member['mb_ptnidx']}
    and b.pg_mb_ptn = {$member['mb_no']}
    and a.insert_date <= now()
    and a.use_yn = 'Y'
    ";
}

//search
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "insert_date":
            $split = explode("  ",$stx); 
            $from = $split[0];
            $to   = $split[1];
            $sql_search .= "a.$sfl between '{$from} 00:00:00.000' and '{$to} 23:59:59.999'";
            break;

        case "tel":
            $sql_search .= " tel = '$stx' ";
            break;
       
    }
    $sql_search .= " ) ";
} else {

    $timestamp = strtotime("-1 months");
    $from = date("Y-m-d", $timestamp);

    $timestamp = strtotime("Now");
    $to = date("Y-m-d", $timestamp);

    $sql_search .= " and a.insert_date between '{$from} 00:00:00.000' and '{$to} 23:59:59.999'";
}



$cnt_sql = "
select count(*) as cnt
from {$g5['crm_landing']} a
left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx
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
    $sql_order = "order by rownum desc";
}else{
    $sql_order = " order by $sst $sod ";    
}

$sql = " select {$sql_columns} {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);



?>
<link rel="stylesheet" href="<?php echo G5_THEME_URL?>/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
<link rel="stylesheet" href="<?php echo G5_THEME_URL?>/plugins/daterangepicker/daterangepicker.css">

<script src="<?php echo G5_THEME_URL?>/plugins/moment/moment.min.js"></script>
<script src="<?php echo G5_THEME_URL?>/plugins/daterangepicker/daterangepicker.js"></script>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">DB (<?php echo $total_count ?>건)</h3>
                    </div>
                    <div class="card-body">
                        <div class="dataTables_wrapper dt-bootstrap4">

                                
                        <div class="d-flex flex-sm-row flex-column justify-content-sm-between">
                            <div class="d-flex justify-content-center mb-2 mb-sm-0">
                                <div class="btn-group xs-100">

                                    <!-- <button type="button" class="btn btn-success btn-sm border border-dark" data-toggle="modal" data-target="#modal-exc-down">
                                        <i class="fas fa-file-download"></i> 엑셀다운
                                    </button> -->

                                    <button type="submit" form="listForm" class="btn btn-success btn-sm border border-dark" name="act_button" value="선택불량">
                                    <i class="fas fa-file-download"></i> 엑셀다운
                                    </button>
                                    
                                </div>
                            </div>
                            <div class="d-flex justify-content-center">
                                <div class="btn-group xs-100">

                                    <form class="form-inline my-2 my-lg-0 ng-pristine ng-valid">
                                        <select name="sfl" id="sfl" class="custom-select">
                                            <option value="insert_date" <?php echo get_selected($sfl, "insert_date"); ?>>등록일시</option>
                                            <option value="tel" <?php echo get_selected($sfl, "tel"); ?>>휴대번호</option>
                                        </select>

                                        <input type="text" id="search_fromto" name="stx" value="<?php echo $sfl == "insert_date" ? $stx : '' ?>" class="form-control sm-1" >
                                        <input type="text" id="search_phone" name="stx" value="<?php echo $sfl == "tel" ? $stx : '' ?>" class="form-control sm-1" placeholder="연락처 검색" aria-label="검색어" oninput="telHyphen(this);" minlength="13" maxlength="13">

                                        <button type="submit" class="btn btn-outline-success my-2 my-sm-0">검색</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        

                            <form name="listForm" id="listForm" action="./db_list_update" onsubmit="return listForm_submit(this);" method="post">

                                <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
                                <input type="hidden" name="stx" value="<?php echo $stx ?>">

                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="tbl_land" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">NO</th>
                                                    <th>이름</th>
                                                    <th class="text-center">휴대번호</th>
                                                    <th>옵션1</th>
                                                    <th>옵션2</th>
                                                    <th>옵션3</th>
                                                    <th>옵션4</th>
                                                    <th>옵션5</th>
                                                    <th>옵션6</th>
                                                    <th>등록일시</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php for ($i = 0; $row = sql_fetch_array($result); $i++) { ?>
                                            <tr>

                                                <td class="text-center">
                                                    <?php echo $row['rownum'] ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['name'] == "" ? 'N/A': $row['name'] ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php echo $row['tel'] ?>
                                                </td>

                                                <td>
                                                    <?php echo $row['option1'] ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['option2'] ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['option3'] ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['option4'] ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['option5'] ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['option6'] ?>
                                                </td>

                                                <td>
                                                    <?php echo view_dateformat($row['insert_date']) ?>
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
    $(function() {

        
        var flag = '<?php echo $sfl ?>';

        if(flag == "") {
            $('#search_fromto').removeClass("d-none");
            $('#search_fromto').attr("disabled"    , false);
            $('#search_phone').addClass("d-none");
            $('#search_phone').attr("disabled"   , true);
            make_datepicker();
        }
        else if(flag != "" && flag == "insert_date") {
            $('#search_fromto').removeClass("d-none");
            $('#search_fromto').attr("disabled"    , false);
            $('#search_phone').addClass("d-none");
            $('#search_phone').attr("disabled"   , true);
            make_datepicker();
        }
        else if(flag != "" && flag == "tel") {
            $('#search_fromto').addClass("d-none");
            $('#search_fromto').attr("disabled"    , true);
            $('#search_phone').removeClass("d-none");
            $('#search_phone').attr("disabled"   , false);
        }

        //조회조건 변경시
        $("#sfl").change(function () {

            var obj = $(this).val();
            if(obj == "insert_date") {
                $('#search_fromto').removeClass("d-none");
                $('#search_fromto').attr("disabled"    , false);
                $('#search_phone').addClass("d-none");
                $('#search_phone').attr("disabled"   , true);
                make_datepicker();
            } else if(obj == "tel") {
                $('#search_fromto').addClass("d-none");
                $('#search_fromto').attr("disabled"    , true);
                $('#search_phone').removeClass("d-none");
                $('#search_phone').attr("disabled"   , false);
            } 
        });

        //datatable load
        var table = $('#tbl_land').DataTable({
            "paging": false,
            "searching": false,
            "ordering": false,
            "info": false,
            "autoWidth": true,
            columnDefs:[
                {responsivePriority : 0     , targets: 0, "width":"3%"},
                {responsivePriority : 1     , targets: 1, "width":"10%"},
                {responsivePriority : 2     , targets: 2},
                {responsivePriority : 102   , targets: 3},
                {responsivePriority : 103   , targets: 4},
                {responsivePriority : 104   , targets: 5},
                {responsivePriority : 105   , targets: 6},
                {responsivePriority : 106   , targets: 7},
                {responsivePriority : 107   , targets: 8},
                {responsivePriority : 108   , targets: 9},
            
            ]            
        });
    });
    
    function make_datepicker(){

        var sfl = '<?php echo $sfl ?>';
        var stx = '<?php echo $stx ?>';
        
        var maxDay = moment().format("YYYY-MM-DD");

        if(sfl == "" && stx == "") {
            startDay = moment().add(-1, 'month');
            endDay = moment().format("YYYY-MM-DD");
        } else {
            var split = stx.split("  ");
            startDay = split[0];
            toDay = split[1];

            startDay = split[0];
            endDay = split[1];
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

        $('#search_fromto').keypress(function(e){
            if (e.keyCode == 10 || e.keyCode == 13)
                e.preventDefault();
        });
    }

    

</script>

<?php
include_once(G5_PATH . '/tail.php');
