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
-- limit {$from_record}, {$rows}
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

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>부서관리</h1>
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
            <div class="row">

                <div class="col-md-6">
                    <button type="button" class="btn btn-primary btn-block border border-dark" data-toggle="modal" data-target="#modal-team" <?php echo $disabled ?>>
                    <i class="fas fa-plus"></i> 팀등록
                    </button>
                </div>

                <div class="col-md-6">
                <!-- <button type="button" class="btn btn-primary btn-block mb-3 addDept" data-toggle="modal" data-target="#modal-success"> -->
                    <button type="button" class="btn btn-primary btn-block border border-dark mb-3" id="addDept" <?php echo $disabled ?> >
                    <i class="fas fa-plus"></i> 부서등록
                </button>
                </div>
                
            </div>

            <div class="card <?php echo $collapsed ?>">
                <div class="card-header">
                <h3 class="card-title"> <i class="fas fa-building"></i>미지정</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                    </button>
                </div>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item <?php echo $bgcolor ?>">
                            <a href="<?php echo G5_BIZ_URL ?>/hr/hr_deptemp_list?prnt=na&chld=na" class="nav-link">
                                <i class="fas fa-filter"></i> 부서없음
                            </a>
                        </li>
                    </ul>
                </div>
            </div>


            <?php for ($i = 0; $row1 = sql_fetch_array($dpet_parent_result); $i++) { ?>
                <div class="card <?php echo $prnt != $row1['deptno'] ? 'collapsed-card' : '' ?>">
                    <div class="card-header">
                    <h3 class="card-title"> <i class="fas fa-building"></i> <?php echo $row1['deptnm'] ?></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="nav nav-pills flex-column">
                            <?php 
                            //parent 자식
                            $dpet_child_sql = "
                            select *
                              from {$g5['crm_depart']}
                             where parent_deptno = {$row1['deptno']}
                             order by deptno 
                            ";
                            $dpet_child_result = sql_query($dpet_child_sql);
                            for ($j = 0; $row2 = sql_fetch_array($dpet_child_result); $j++) {
                            ?>
                            <li class="nav-item <?php echo $chld == $row2['deptno'] ? 'bg-gradient-warning' : '' ?>">
                                <a href="<?php echo G5_BIZ_URL ?>/hr/hr_deptemp_list?prnt=<?php echo $row1['deptno'] ?>&chld=<?php echo $row2['deptno']?>" class="nav-link">
                                    <i class="fas fa-filter"></i> <?php echo '['.$row2['deptno'].'] '.$row2['deptnm'] ?>
                                </a>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            <?php } ?>

                
        </div>

        <div class="col-md-9">
            <div class="card card-primary card-outline">
               
            
                <div class="card-header d-none d-sm-block">
                    <h3 class="card-title">임직원리스트</h3>
                    <div class="card-tools">
                        <form>
                        <div class="input-group input-group-sm " >
                            <button type="button" name="btn_ins" class="btn btn-primary border border-dark btn-sm <?php echo $visible_ins ?>" value="등록" onclick="location.href='<?php echo G5_BIZ_URL; ?>/hr/employee_form?'"><i class="fas fa-pen"></i> 입력</button>
                            <button type="submit" form="hr_form" class="btn btn-warning border border-dark btn-sm <?php echo $visible_upd ?>" name="act_button" value="선택수정"><i class="fas fa-eraser"></i>수정</button>
                            <button type="submit" form="hr_form" class="btn btn-danger border border-dark btn-sm mr-1 <?php echo $visible_del ?>" name="act_button" value="선택삭제"><i class="far fa-trash-alt"></i>삭제</button>


                            <!-- <input type="text" class="form-control" placeholder="[ID|이름] 검색"> -->
                            <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="form-control mr-sm-1" placeholder="[ID|이름] 검색" aria-label="[ID|이름] 검색">
                            <span class="input-group-append">
                                <button type="submit" class="btn btn-info btn-flat" style="user-select: auto;">검색</button>
                            </span>
                        </div>
                        </form>
                    </div>
                </div>



                <form name="hr_form" id="hr_form" action="./hr_deptemp_list_update" onsubmit="return listForm_submit(this);" method="post">
                    <input type="hidden" name="sst" value="<?php echo $sst ?>">
                    <input type="hidden" name="sod" value="<?php echo $sod ?>">
                    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
                    <input type="hidden" name="stx" value="<?php echo $stx ?>">
                    <input type="hidden" name="page" value="<?php echo $page ?>">
                    <input type="hidden" name="chld" value="<?php echo $chld ?>">
                    <input type="hidden" name="prnt" value="<?php echo $prnt ?>">
                    <input type="hidden" name="token" value="<?php echo isset($token) ? $token : ''; ?>">


                    <div class="row">
                        <div class="col-sm-12">
                            <table id="hr_table" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)"></th>
                                        <th>ID</th>
                                        <th>이름</th>
                                        <th>핸드폰</th>
                                        <th>부서</th>
                                        <th>시스템권한</th>
                                        <th>접속권한</th>
                                        <th>입사일</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php for ($i = 0; $row = sql_fetch_array($result); $i++) {  ?>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="mb_no[<?php echo $i ?>]" value="<?php echo $row['mb_no'] ?>">
                                            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
                                        </td>
                                        

                                        <td class="d-none d-md-table-cell">
                                            <div class="dropdown d-inline">
                                                <a href="#" data-toggle="dropdown" class="text-dark" aria-expanded="false"><?php echo $row['mb_id']?></a>
                                                <div class="dropdown-menu" x-placement="bottom-start" >
                                                    
                                                    <?php if($member['mb_level'] >= 6 && $member['mb_deptno'] == $row['mb_deptno']) { ?>
                                                        <a href="<?php echo G5_BIZ_URL ?>/hr/employee_form?w=u&mb_id=<?php echo $row['mb_id']?>" class="dropdown-item">상세보기</a>                                                        
                                                    <?php } else if($member['mb_id'] == $row['mb_id'] && $member['mb_deptno'] == $row['mb_deptno']){ ?>
                                                        <a href="<?php echo G5_BIZ_URL ?>/hr/employee_form?w=u&mb_id=<?php echo $member['mb_id']?>" class="dropdown-item">상세보기</a>
                                                    <?php } ?>
                                                    <!-- <a href="<?php echo G5_BBS_URL ?>/memo_form?me_recv_mb_id=<?php echo $row['mb_id']?>" class='dropdown-item' onclick='win_memo(this.href); return false;'>쪽지보내기</a> -->
                                                    
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" id="mb_name" name="mb_name[]" class="custom_select" value="<?php echo $row['mb_name'] ?>">
                                        </td>
                                        <td>
                                            <input type="text" id="mb_hp" name="mb_hp[]" class="custom_select" value="<?php echo $row['mb_hp'] ?>">
                                        </td>
                                        <td>
                                            <?php echo comm_select_mb_deptno("mb_deptno", "mb_deptno[]", $row['mb_deptno'] ) ?>
                                        </td>
                                        <td>
                                            <select id="mb_level" name="mb_level[]" class="">
                                                <option value="4" <?php echo get_selected($row['mb_level'], '4'); ?>>1:영업직원</option>
                                                <option value="5" <?php echo get_selected($row['mb_level'], '5'); ?>>2:일반직원</option>
                                                <option value="6" <?php echo get_selected($row['mb_level'], '6'); ?>>3:관리자</option>
                                                <?php if($member['mb_level'] >= 10) { ?>
                                                <option value="7" <?php echo get_selected($row['mb_level'], '7'); ?>>개발자</option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="is_login[]" id="is_login" class="">
                                            <option value="Y"  <?php echo get_selected($row['is_login'], 'Y'); ?>>접속가능</option>
                                            <option value="N"  <?php echo get_selected($row['is_login'], 'N'); ?>>불가능</option>
                                            </select>
                                        </td>
                                        <td>
                                            <?php echo substr($row['mb_datetime'],0,10) ?>
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

    });

</script>

<?php
include_once(G5_PATH . '/tail.php');
