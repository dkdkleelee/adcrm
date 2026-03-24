<?php
require_once '../../common.php';
include_once(G5_PATH . '/head.php');

$g5['title'] = "가입대기";


$sql_columns    = "";
$sql_common     = "";
$sql_search     = "";
$sql_gruop      = "";
$sql_order      = "";
$total_count = 0;
$curr_order = "";


$sql_columns = "
  sign_idx 
, ptn_id 
, ptn_nm     
, ptn_reprnm 
, ptn_phone 
, ptn_tel 
, ptn_email 
, ptn_nick 
, sign_status 
, insert_date 
";

$sql_common = "from {$g5['crm_signup']}";


$sql_search = " 
    where 1=1
      and sign_status = 'N'
";

//search
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "ptn_nm":
            $sql_search .= " ($sfl like '$stx%') ";
            break;
    }
    $sql_search .= " ) ";
}

$cnt_sql = " 
select count(*) as cnt
$sql_common
{$sql_search}
";
$row = sql_fetch($cnt_sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) {
    $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
}
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst) {
    $sst  = "sign_idx";
    $sod = "asc";
}else{
    $sql_order = " order by $sst $sod ";    
}


$sql = " select {$sql_columns} {$sql_common} {$sql_search} {$sql_gruop} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 12;

$is_checkbox = false;
if ($member['mb_level'] >= 7) {
    $is_checkbox = true;
}

$new_date = date("Y-m-d H:i:s", G5_SERVER_TIME - (24 * 3600));


$mb_level = $member['mb_level'];
$dept_cond = "";
if($mb_level <= 7) {
    $dept_cond = "and ptn_deptno = {$member['mb_deptno']}";
}

//partner list
$ptn_sql = "
select ptn_idx
     , ptn_nm
  from {$g5['crm_partner']}
 where use_yn = 'Y'
 $dept_cond
";
$ptn_list = sql_query($ptn_sql);

//$ptn_html .= '<option value="">신규고객사</option>';

for ($i = 0; $row = sql_fetch_array($ptn_list); $i++) {
    $ptn_html .= '<option value="'.$row['ptn_idx'].'">'.$row['ptn_nm'].'</option>';
}
$ptn_html .= "</select>";

//dept list
// $dept_sql = "
// select deptno
//      , deptnm
//      , parent_deptno
//   from {$g5['crm_depart']} 
//  where use_yn = 'Y'
//    and parent_deptno != 1
//  order by coalesce(parent_deptno, deptno), parent_deptno is not null, deptno
// ";
// $dept_list = sql_query($dept_sql);
// $dept_html .= '<option value="">미지정</option>';
// for ($i = 0; $row = sql_fetch_array($dept_list); $i++) {
//     $dept_html .= '<option value="'.$row['deptno'].'">'.$row['deptnm'].'</option>';
// }

?>


<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">고객사 가입대기(<?php echo $total_count ?>)</h3>
                    </div>

                    <div class="card-body">
                        <div class="dataTables_wrapper dt-bootstrap4">
                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <div class="dt-buttons"> 
                                        <button type="submit" form="listForm" class="btn btn-warning" name="act_button" value="선택수정"><i class="fas fa-eraser"></i>수정</button>
                                    </div>
                                </div>
                            </div>
                            <form name="listForm" id="listForm" action="./partner_signup_list_update" onsubmit="return listForm_submit(this);" method="post">
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
                                                    <th>상태</th>
                                                    <th>고객사지정</th>
                                                    <th>구분</th>
                                                    <th>신청ID</th>
                                                    <th>업체명</th>
                                                    <th>이름</th>
                                                    <th>휴대전화</th>
                                                    <th>이메일</th>
                                                    <th>가입일</th>
                                                    <th>관리</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php for ($i = 0; $row = sql_fetch_array($result); $i++) { ?>
                                                <tr>
                                                    <td>
                                                        <input type="hidden" name="sign_idx[<?php echo $i ?>]" value="<?php echo $row['sign_idx'] ?>">
                                                        <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
                                                    </td>
                                                    <td>
                                                        <?php echo $row['sign_idx'] ?>
                                                    </td>
                                                    <td>
                                                    <select name="sign_status[]" id="sign_status<?php echo $i ?>" class="custom_select">
                                                        <option value="N" selected>대기</option>
                                                        <option value="Y" >승인</option>
                                                        <option value="D" >삭제</option>
                                                        <option value="R" >반려</option>
                                                    </select>
                                                    </td>

                                                    <td>
                                                    <select name="ptn_idx[]" id="ptn_idx<?php echo $i ?>" class="custom_select">
                                                        <?php echo $ptn_html ?>
                                                    </select>
                                                    </td>

                                                    <td>
                                                    <select name="ptn_gubun[]" id="ptn_gubun<?php echo $i ?>" class="custom_select">
                                                        <option value="1" selected>대표</option>
                                                        <option value="2" >직원</option>
                                                    </select>
                                                    </td>

                                                    <td>
                                                        <input type="hidden" name="ptn_id[<?php echo $i ?>]" value="<?php echo $row['ptn_id'] ?>">
                                                        <?php echo $row['ptn_id'] ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $row['ptn_nm'] ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $row['ptn_reprnm'] ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $row['ptn_phone'] ?>
                                                    </td>
                                                    
                                                    <td>
                                                        <?php echo $row['ptn_email'] ?>
                                                    </td>
                                                    <td>
                                                        <?php echo view_dateformat($row['insert_date']) ?>
                                                    </td>
                                                    <td>
                                                        
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

        var table = $('#tbl_partner').DataTable({
            "paging": false,
            "searching": false,
            "ordering": false,
            "info": false,
            "autoWidth": true,
            columnDefs:[
                {responsivePriority : 0     , targets: 0, "width":"1%"},
                {responsivePriority : 108   , targets: 1,visible:false},
                {responsivePriority : 1     , targets: 2, "width":"50px"},
                {responsivePriority : 102   , targets: 3},
                {responsivePriority : 103   , targets: 4, "width":"50px"},
                {responsivePriority : 104   , targets: 5},
                {responsivePriority : 105   , targets: 6},
                {responsivePriority : 106   , targets: 7},
                {responsivePriority : 107   , targets: 8},
                {responsivePriority : 108   , targets: 9},
                {responsivePriority : 109   , targets: 10},
                {responsivePriority : 110   , targets: 11, "width":"5%",
                    render: function(data,type,row){
                        return "<button type='button' id='btn_apr' class='btn btn-info btn-xs')>승인</button>"+
                               "<button type='button' id='btn_del' class='btn btn-danger btn-xs')>삭제</button>"
                        ;
	    		    }
                },
            ]            
        });


        $('#tbl_partner tbody').on ('click', '.btn-xs', function () {

            
            var index = $(this).closest('tr').index();

            var data = table.row( $(this).parents('tr') ).data();
            var chk = document.getElementsByName("chk[]");
            
            var rows = table.rows(index).nodes();
            $('input[type="checkbox"]', rows).prop('checked', true);


            //승인d
            if(this.id == "btn_apr") {
                $('#sign_status'+index).val("Y");

            } else if(this.id == "btn_del") {
                $('#sign_status'+index).val("D");
            }

            $("#listForm").submit();

        });

        
    });
   
    function listForm_submit(f) {
        
    }
    
</script>

<?php
include_once(G5_PATH . '/tail.php');
