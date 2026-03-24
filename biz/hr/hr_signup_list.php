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
  mb_no 
, mb_id 
, mb_name
, mb_nick 
, mb_hp
, mb_email
, mb_gubun
, mb_datetime
";

$sql_common = "from {$g5['member_table']}";


$sql_search = " 
where 1=1
and mb_gubun = 'E'
and is_login = 'N'
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
    $sst  = "mb_no";
    $sod = "asc";
}else{
    $sql_order = " order by $sst $sod ";    
}


$sql = " select {$sql_columns} {$sql_common} {$sql_search} {$sql_gruop} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$colspan = 7;

$is_checkbox = false;
if ($member['mb_level'] >= 7) {
    $is_checkbox = true;
}

$new_date = date("Y-m-d H:i:s", G5_SERVER_TIME - (24 * 3600));

?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">임직원 가입대기(<?php echo $total_count ?>)</h3>
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
                            <form name="listForm" id="listForm" action="./hr_signup_list_update" onsubmit="return listForm_submit(this);" method="post">
                                <input type="hidden" name="sst" value="<?php echo $sst ?>">
                                <input type="hidden" name="sod" value="<?php echo $sod ?>">
                                <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
                                <input type="hidden" name="stx" value="<?php echo $stx ?>">
                                <input type="hidden" name="page" value="<?php echo $page ?>">
                                <input type="hidden" name="token" value="<?php echo isset($token) ? $token : ''; ?>">
                                
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="hr_table" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)"></th>
                                                    <th>NO</th>
                                                    <th>상태</th>
                                                    <th>신청ID</th>
                                                    <th>신청자</th>
                                                    <th>닉네임</th>
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
                                                        <input type="hidden" name="mb_no[<?php echo $i ?>]" value="<?php echo $row['mb_no'] ?>">
                                                        <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
                                                    </td>
                                                    <td>
                                                        <?php echo $row['mb_no'] ?>
                                                    </td>
                                                    <td>
                                                    <select name="is_login[]" id="is_login" class="custom_select">
                                                        <option value="Y" >승인</option>
                                                        <option value="D" >삭제</option>
                                                    </select>
                                                    </td>
                                                    <td>
                                                        <?php echo $row['mb_id'] ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $row['mb_name'] ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $row['mb_nick'] ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $row['mb_hp'] ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $row['mb_email'] ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $row['mb_datetime'] ?>
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

        var table = $('#hr_table').DataTable({
            "paging": false,
            "searching": false,
            "ordering": false,
            "info": false,
            "autoWidth": true,
            columnDefs:[
                {responsivePriority : 0     , targets: 0, "width":"1%"},
                {responsivePriority : 1     , targets: 1, visible:false},
                {responsivePriority : 2     , targets: 2},
                {responsivePriority : 102   , targets: 3},
                {responsivePriority : 103   , targets: 4},
                {responsivePriority : 104   , targets: 5},
                {responsivePriority : 105   , targets: 6},
                {responsivePriority : 106   , targets: 7},
                {responsivePriority : 106   , targets: 8},
                {responsivePriority : 107   , targets: 9,  "width":"5%",
                    render: function(data,type,row){
                        return "<button id='btn_info' type='button' class='btn btn-info btn-xs' onClick='execute(1,"+row[1]+")'>승인</button>"+
                               "<button id='btn_info' type='button' class='btn btn-danger btn-xs' onClick='execute(2,"+row[1]+")'>삭제</button>"
                        ;
	    		    }
                },
            ]            
        });
    });

   
    function listForm_submit(f) {
        
    }
    function execute(gubun, mb_no) {

        var act = "";
        var msg = "";
        var sign_status = "";

        if(gubun == "1") {
            act = "sign_one_appr";
            sign_status = "Y";
            msg = "승인";
        }else if(gubun == "2") {
            act = "sign_one_dele";
            sign_status = "D";
            msg = "삭제";
        }
        var result = confirm(msg+" 하시겠습니까?");
        if(result){
            var type = "POST";
            var url  = "hr_ajax";
            var param = {
                    "act": act,
                    "mb_no":mb_no,
                    "sign_status": sign_status
            };
            ajaxNetVoid(type , url , param);
            location.reload();
        }
    }

</script>

<?php
include_once(G5_PATH . '/tail.php');
