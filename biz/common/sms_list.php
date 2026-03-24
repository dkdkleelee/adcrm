<?php
require_once '../../common.php';
//require_once G5_ADMIN_PATH . '/admin.lib.php';
include_once(G5_PATH . '/head.php');

//하이라키쿼리
// $sql = "
// SELECT  deptno
//       , deptnm
//       , parent_deptno
// FROM    (select * FROM {$g5['crm_depart']}
//          order by parent_deptno, deptno) {$g5['crm_depart']},
//         (select @pv := '1') initialization
// WHERE   find_in_set(parent_deptno, @pv)
// AND     length(@pv := concat(@pv, ',', deptno));
// ";
// $dept_result = sql_query($sql);

//parent 부서
$dpet_parent_sql = "
select *
from {$g5['crm_depart']}
where parent_deptno = 1
order by deptno 
";
$dpet_parent_result = sql_query($dpet_parent_sql);


$sql_search = "";
if($chld == "na" || $chld == "na") {
    $sql_search .= "and mb_deptno is null";

    $collapsed = "";
    $bgcolor = "bg-gradient-warning";
}else if($chld == "" || $chld == "") {
    $sql_search .= "and mb_deptno = {$member['mb_deptno']}";

    $collapsed = "collapsed-card";
    $bgcolor = "";
} else {
    $sql_search .= "and mb_deptno = {$chld}";
    $collapsed = "collapsed-card";
    $bgcolor = "";
}

/* count sql */
$cnt_sql = "
select count(*) as cnt
from {$g5['member_table']} a
where mb_gubun = 'E'
and a.mb_no != 1
and a.is_login = 'Y'
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

/* main sql */
$main_sql = "
select a.mb_no 
     , a.mb_id 
     , a.mb_name 
     , a.mb_hp
     , a.mb_email 
     , a.mb_datetime 
     , a.mb_level
     , a.is_login
     , a.mb_deptno
     , b.deptnm 
from {$g5['member_table']} a
left join {$g5['crm_depart']} b on a.mb_deptno = b.deptno
where a.mb_gubun = 'E'
and a.mb_level <= 8
 {$sql_search}
limit {$from_record}, {$rows}
";
$result = sql_query($main_sql);


$mb_level = $member['mb_level'];
$disabled = "";
$visible_upd = "d-none";
$visible_ins = "d-none";
$visible_del = "d-none";

if($mb_level >= 8) {
    $visible_upd = "";
    $visible_ins = "";
    $visible_del = "";
} else {
    //부서 팀등록은 안됨
    $disabled = "disabled";
   
    if($member['mb_level'] == "6") {
        $visible_ins = "";
    }

    if(!isset($prnt) && !isset($chld) || $prnt =="na" && $chld =="na") {
        if($member['mb_level'] == "6") {
            $visible_upd = "";
        }
    } else {
        if($member['mb_level'] == "6" && $chld == $member['mb_deptno']) {
            $visible_upd = "";
            $visible_ins = "";
            $visible_del = "";
        }
    }
}

if($prnt == "" && $chld == "") {
    $defDeptSql = "
    select parent_deptno 
     , deptno
     , deptnm 
    from {$g5['crm_depart']} 
    where deptno = {$member['mb_deptno']} 
    ";
    $row = sql_fetch($defDeptSql);

    $prnt = $row['parent_deptno'];
    $chld = $row['deptno'];
}

?>

<link rel='stylesheet' href='//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.10.0/bootstrap-table.min.css'>
<link rel='stylesheet' href='//rawgit.com/vitalets/x-editable/master/dist/bootstrap3-editable/css/bootstrap-editable.css'><link rel="stylesheet" href="./style.css">

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>문자전송관리</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">부서관리</li>
                </ol>
            </div>
        </div>
    </div>
</section>


<section class="content">
    <div class="row">
        <div class="col-md-3">
        
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">문자전송</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <textarea class="form-control" rows="15" placeholder="내용입력 ..."></textarea>
                    </div>
                </div>


                <div class="card-footer">
                    <button type="button" class="btn btn-primary" id="btn_list">전송</button>
                    <button type="button" class="btn btn-danger float-right" id="btn_list">클리어</button>
                </div>
                
            </div>
        

        </div>

        <div class="col-md-9">
            <div class="card card-primary card-tabs">
                <div class="card-header p-0 pt-1">
                    <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="custom-tabs-one-home-tab" data-toggle="pill" href="#custom-tabs-one-home" role="tab" aria-controls="custom-tabs-one-home" aria-selected="true">임직원</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="custom-tabs-one-profile-tab" data-toggle="pill" href="#custom-tabs-one-profile" role="tab" aria-controls="custom-tabs-one-profile" aria-selected="false">고객사</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="custom-tabs-one-messages-tab" data-toggle="pill" href="#custom-tabs-one-messages" role="tab" aria-controls="custom-tabs-one-messages" aria-selected="false">Messages</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="custom-tabs-one-settings-tab" data-toggle="pill" href="#custom-tabs-one-settings" role="tab" aria-controls="custom-tabs-one-settings" aria-selected="false">Settings</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="custom-tabs-one-tabContent">

                        <div class="tab-pane fade active show" id="custom-tabs-one-home" role="tabpanel" aria-labelledby="custom-tabs-one-settings-tab">

                            <div class="card card-default">
                                <div class="card-header">
                                    <h3 class="card-title">Bootstrap Duallistbox</h3>

                                    <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    </div>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                        <label>Multiple</label>
                                        <select class="duallistbox" multiple="multiple">
                                            <option selected>Alabama</option>
                                            <option>Alaska</option>
                                            <option>California</option>
                                            <option>Delaware</option>
                                            <option>Tennessee</option>
                                            <option>Texas</option>
                                            <option>Washington</option>
                                        </select>
                                        </div>
                                        <!-- /.form-group -->
                                    </div>
                                    <!-- /.col -->
                                    </div>
                                    <!-- /.row -->
                                </div>
                                <!-- /.card-body -->
                                <div class="card-footer">
                                    Visit <a href="https://github.com/istvan-ujjmeszaros/bootstrap-duallistbox#readme">Bootstrap Duallistbox</a> for more examples and information about
                                    the plugin.
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="custom-tabs-one-profile" role="tabpanel" aria-labelledby="custom-tabs-one-profile-tab">
                            Mauris tincidunt mi at erat gravida, eget tristique urna bibendum. Mauris pharetra purus ut
                            ligula tempor, et vulputate metus facilisis. Lorem ipsum dolor sit amet, consectetur
                            adipiscing
                            elit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia
                            Curae;
                            Maecenas sollicitudin, nisi a luctus interdum, nisl ligula placerat mi, quis posuere purus
                            ligula eu lectus. Donec nunc tellus, elementum sit amet ultricies at, posuere nec nunc. Nunc
                            euismod pellentesque diam.
                        </div>
                        <div class="tab-pane fade" id="custom-tabs-one-messages" role="tabpanel" aria-labelledby="custom-tabs-one-messages-tab">
                            Morbi turpis dolor, vulputate vitae felis non, tincidunt congue mauris. Phasellus volutpat
                            augue
                            id mi placerat mollis. Vivamus faucibus eu massa eget condimentum. Fusce nec hendrerit sem,
                            ac
                            tristique nulla. Integer vestibulum orci odio. Cras nec augue ipsum. Suspendisse ut velit
                            condimentum, mattis urna a, malesuada nunc. Curabitur eleifend facilisis velit finibus
                            tristique. Nam vulputate, eros non luctus efficitur, ipsum odio volutpat massa, sit amet
                            sollicitudin est libero sed ipsum. Nulla lacinia, ex vitae gravida fermentum, lectus ipsum
                            gravida arcu, id fermentum metus arcu vel metus. Curabitur eget sem eu risus tincidunt
                            eleifend
                            ac ornare magna.
                        </div>
                        <div class="tab-pane fade" id="custom-tabs-one-settings" role="tabpanel" aria-labelledby="custom-tabs-one-messages-tab">
                            Pellentesque vestibulum commodo nibh nec blandit. Maecenas neque magna, iaculis tempus
                            turpis
                            ac, ornare sodales tellus. Mauris eget blandit dolor. Quisque tincidunt venenatis vulputate.
                            Morbi euismod molestie tristique. Vestibulum consectetur dolor a vestibulum pharetra. Donec
                            interdum placerat urna nec pharetra. Etiam eget dapibus orci, eget aliquet urna. Nunc at
                            consequat diam. Nunc et felis ut nisl commodo dignissim. In hac habitasse platea dictumst.
                            Praesent imperdiet accumsan ex sit amet facilisis.
                        </div>
                    </div>
                </div>

            </div>


        </div>

    </div>

</section>



<div class="modal fade" id="modal-team">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title">팀등록</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form name="update_form" action="./hr_deptemp_list_update" method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">팀명</label>
                        <input type="text" name="deptnm" class="form-control">
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">닫기</button>
                    <button type="submit" class="btn btn-primary" name="act_button" value="팀저장">저장</button>
                </div>
            </form>

        </div>
    </div>
</div>

<div class="modal fade" id="modal-dept">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h4 class="modal-title">부서등록</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form name="update_form" action="./hr_deptemp_list_update" method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="email">상위부서</label>
                        <select name="parent_deptno" id="parent_deptno" class="form-control custom-select"></select>
                    </div>
                    <div class="form-group">
                        <label for="name">부서명</label>
                        <input type="text" name="deptnm" class="form-control">
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">닫기</button>
                    <button type="submit" class="btn btn-primary" name="act_button" value="부서저장">저장</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script src='//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.10.0/bootstrap-table.js'></script>
<script src='//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.9.1/extensions/editable/bootstrap-table-editable.js'></script>
<script src='//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.9.1/extensions/export/bootstrap-table-export.js'></script>
<script src='//rawgit.com/hhurz/tableExport.jquery.plugin/master/tableExport.js'></script>
<script src='//cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.9.1/extensions/filter-control/bootstrap-table-filter-control.js'></script>

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
                {responsivePriority : 1     , targets: 1},
                {responsivePriority : 2     , targets: 2},
                {responsivePriority : 3     , targets: 3},
                {responsivePriority : 4     , targets: 4},
                {responsivePriority : 5     , targets: 5},
                {responsivePriority : 6     , targets: 6, "width":"3%"},
                {responsivePriority : 7     , targets: 7, "width":"3%"},
                
            ]            
        });


       

        $('#addDept').on('click',function(){

            var act = "parent_deptlist";
            var $target = $("#parent_deptno");
            $target.empty();

            $.ajax({
                type: "post",
                data: {
                    act: act
                },
                url: "hr_ajax",
                dataType: "json",
                success:function(result) {
                    $target.append(result);
                    $('#modal-dept').modal("show");
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    return;
                }
            });
        });


        //exporte les données sélectionnées
        var $table = $('#table');
            $(function () {
                $('#toolbar').find('select').change(function () {
                    $table.bootstrapTable('refreshOptions', {
                        exportDataType: $(this).val()
                    });
                });
            })

                var trBoldBlue = $("table");

            $(trBoldBlue).on("click", "tr", function (){
                    $(this).toggleClass("bold-blue");
            });


    });

    
</script>

<?php
include_once(G5_PATH . '/tail.php');
