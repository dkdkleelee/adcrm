<?php
require_once '../../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');

$g5['title'] = "DESIGN 조회";
include_once(G5_PATH . '/head.php');

$sql_columns    = "";
$sql_common     = "";
$sql_search     = "";
$sql_gruop      = "";
$sql_order      = "";
$total_count    = 0;

$des_mb_no = "";
if($member['mb_deptno'] != "9") {
    if ($member['mb_level'] == 4) {

        //1팀 규표 세컨계정 디자인 풀어줌
        if($member['mb_no'] != 1045) {
            $des_mb_no = 'and a.des_mb_no = '.$member['mb_no'];
        }
    }
}


//select
$sql_columns = "
  a.design_idx 
, a.design_name 
, a.des_memo 
, f_getcode(a.des_cate_code) as des_cate_code_nm
, a.des_status
, case a.des_status when '1' then 'bg-warning'
       when '2' then 'bg-primary'
       when '3' then 'bg-danger'
    END AS des_status_color
, a.des_deptno 
, b.deptnm 
, a.des_mb_no 
, c.mb_name 
, a.insert_date
, a.update_date
, a.update_user_name
, (select count(*) from gnp_crm_page page where a.design_idx = page.pg_des_idx) as used_pg_cnt
";

$sql_common = "
from {$g5['crm_design']} a
left join {$g5['crm_depart']} b on a.des_deptno = b.deptno
left join {$g5['member_table']} c on a.des_mb_no = c.mb_no 
";

$sql_search .= " 
    where 1=1
    and a.use_yn = 'Y'
    $des_mb_no
";

//search
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "design_name":
            $sql_search .= " ($sfl like '%$stx%') ";
            $vis1 = "";
            $vis2 = "d-none";
            $vis3 = "d-none";
            $vis4 = "d-none";
            
            $dis1 = "";
            $dis2 = "disabled";
            $dis3 = "disabled";
            $dis4 = "disabled";
            break;

        case "des_cate_code":
            $sql_search .= " $sfl = '$stx' ";
            $vis1 = "d-none";
            $vis2 = "";
            $vis3 = "d-none";
            $vis4 = "d-none";
            
            $dis1 = "disabled";
            $dis2 = "";
            $dis3 = "disabled";
            $dis4 = "disabled";
            break;

        case "deptno":
            $sql_search .= " $sfl = '$stx' ";
            $vis1 = "d-none";
            $vis2 = "d-none";
            $vis3 = "";
            $vis4 = "d-none";
            
            $dis1 = "disabled";
            $dis2 = "disabled";
            $dis3 = "";
            $dis4 = "disabled";
            break;
        case "des_gubun":
            $sql_search .= " $sfl = '$stx' ";
            $vis1 = "d-none";
            $vis2 = "d-none";
            $vis3 = "d-none";
            $vis4 = "";
            
            $dis1 = "disabled";
            $dis2 = "disabled";
            $dis3 = "disabled";
            $dis4 = "";
            break;
    }
    $sql_search .= " ) ";
} else {
    if( ($stx == "" && $sfl == "") || $sfl == "design_name") {
        $vis1 = "";
        $vis2 = "d-none";
        $vis3 = "d-none";
        $vis4 = "d-none";
        
        $dis1 = "";
        $dis2 = "disabled";
        $dis3 = "disabled";
        $dis4 = "disabled";
    } else if($sfl == "des_cate_code") {
        $vis1 = "d-none";
        $vis2 = "";
        $vis3 = "d-none";
        $vis4 = "d-none";
        
        $dis1 = "disabled";
        $dis2 = "";
        $dis3 = "disabled";
        $dis4 = "disabled";
    } else if($sfl == "deptno") {
        $vis1 = "d-none";
        $vis2 = "d-none";
        $vis3 = "";
        $vis4 = "d-none";
        
        $dis1 = "disabled";
        $dis2 = "disabled";
        $dis3 = "";
        $dis4 = "disabled";
    } else if($sfl == "des_gubun") {
        $vis1 = "d-none";
        $vis2 = "d-none";
        $vis3 = "d-none";
        $vis4 = "";
        
        $dis1 = "disabled";
        $dis2 = "disabled";
        $dis3 = "disabled";
        $dis4 = "";
    }
}


$cnt_sql = " 
select count(*) as cnt
from {$g5['crm_design']} a
left join {$g5['crm_depart']} b on a.des_deptno = b.deptno
left join {$g5['member_table']} c on a.des_mb_no = c.mb_no 
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
    $sql_order = "order by a.design_idx desc";
}else{
    $sql_order = " order by $sst $sod ";    
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
 order by comm_pcd, comm_cd
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
                        <h3 class="card-title">디자인조회(<?php echo $total_count ?>)</h3>
                    </div>

                    <div class="card-body">
                        <div class="dataTables_wrapper dt-bootstrap4">

                            <div class="d-flex flex-sm-row flex-column justify-content-sm-between">
                                <div class="d-flex justify-content-center mb-2 mb-sm-0">
                                    <div class="btn-group xs-100">
                                        <button type="submit" name="btn_ins" value="등록" onclick="location.href='<?php echo G5_BIZ_URL; ?>/design/design_form'" class="btn btn-primary btn-sm border border-dark"><i class="fas fa-pen"></i> 입력</button>
                                        <button type="submit" form="listForm" class="btn btn-warning btn-sm border border-dark" name="act_button" value="선택수정"><i class="fas fa-eraser"></i>수정</button>
                                        <button type="submit" form="listForm" class="btn btn-danger btn-sm border border-dark" name="act_button" value="선택삭제"><i class="far fa-trash-alt"></i>삭제</button>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <div class="btn-group xs-100">

                                        <form class="form-inline my-2 my-lg-0 ng-pristine ng-valid">
                                            <select name="sfl" id="sfl" class="custom-select">
                                                <option value="design_name" <?php echo get_selected($sfl, "design_name"); ?>>디자인명</option>
                                                <option value="des_cate_code" <?php echo get_selected($sfl, "des_cate_code"); ?>>코드명</option>
                                                <option value="deptno" <?php echo get_selected($sfl, "deptno"); ?>>부서명</option>
                                                <option value="des_gubun" <?php echo get_selected($sfl, "des_gubun"); ?>>구분</option>
                                            </select>
                                            <input type="text" id="search_keyword1" name="stx" value="<?php echo $sfl == "design_name" ? $stx : '' ?>" class="form-control mr-sm-1 <?php echo $vis1 ?>" placeholder="검색어" aria-label="검색어" <?php echo $dis1 ?>>
                                            <select id="search_keyword2" name="stx" class="form-control <?php echo $vis2 ?>" <?php echo $dis2 ?>>
                                            <option value="">전체</option>
                                            <?php for ($i = 0; $code = sql_fetch_array($code_list); $i++) { ?>
                                                <option value="<?php echo $code['comm_idx'] ?>" <?php echo $sfl == "des_cate_code" ? get_selected($stx, $code['comm_idx']) : '' ?> ><?php echo '['.$code['comm_pcd'].':'.$code['comm_pnm'] .'] '. $code['comm_nm'] ?></option>
                                            <?php } ?>
                                            </select>
                                            <select id="search_keyword3" name="stx" class="form-control <?php echo $vis3 ?>" <?php echo $dis3 ?>>
                                            <option value="">전체</option>
                                            <?php for ($i = 0; $dept = sql_fetch_array($dept_list); $i++) { ?>
                                                <option value="<?php echo $dept['deptno'] ?>" data-tokens="<?php echo $dept['deptnm'] ?>" <?php echo  $sfl == "deptno" ? get_selected($stx, $dept['deptno']) : '' ?>><?php echo $dept['deptnm'] ?></option>
                                            <?php } ?>
                                            </select>

                                            <select id="search_keyword4" name="stx" class="form-control <?php echo $vis4 ?>" <?php echo $dis4 ?>>
                                                <option value=""  <?php echo get_selected($stx, ''); ?>>미지정</option>
                                                <option value="1" <?php echo get_selected($stx, '1'); ?>>PC/모바일 분리형</option>
                                                <option value="2" <?php echo get_selected($stx, '2'); ?>>단일 이벤트형</option>
                                                <option value="3" <?php echo get_selected($stx, '3'); ?>>기사형</option>
                                                <option value="5" <?php echo get_selected($stx, '5'); ?>>복사제작</option>
                                                <option value="4" <?php echo get_selected($stx, '4'); ?>>기타</option>
                                            </select>

                                            <button type="submit" class="btn btn-outline-success my-2 my-sm-0">검색</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <form name="listForm" id="listForm" action="./design_list_update" onsubmit="return listForm_submit(this);" method="post">
                                <input type="hidden" name="sst" value="<?php echo $sst ?>">
                                <input type="hidden" name="sod" value="<?php echo $sod ?>">
                                <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
                                <input type="hidden" name="stx" value="<?php echo $stx ?>">
                                <input type="hidden" name="page" value="<?php echo $page ?>">
                                <input type="hidden" name="token" value="<?php echo isset($token) ? $token : ''; ?>">

                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="tbl_design" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)"></th>
                                                    <th>NO</th>
                                                    <th>디자인</th>
                                                    <th>상태</th>
                                                    <th>디자인설명</th>
                                                    <th>카테고리</th>
                                                    <th>담당자</th>
                                                    <th>last수정자</th>
                                                    <th>last수정일</th>
                                                    <th>사용</th>
                                                    <th>복사</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php for ($i = 0; $row = sql_fetch_array($result); $i++) { ?>
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="design_idx[<?php echo $i ?>]" value="<?php echo $row['design_idx'] ?>">
                                                    <?php echo isCheckbox($i, $row['des_deptno'], $row['des_mb_no'], $member); ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['design_idx'] ?>
                                                </td>
                                                <td>
                                                    <input type="hidden" name="design_name" value="<?php echo $row['design_name'] ?>">
                                                    <a href="design_form?w=u&design_idx=<?php echo $row['design_idx'].$qstr ?>"><?php echo $row['design_name'] ?></a>
                                                </td>

                                                <td>
                                                    <select name="des_status[]" id="des_status" class="custom_select <?php echo $row['des_status_color'] ?>" <?php echo isShowListInput($row['des_deptno'], $row['des_mb_no'], $member, "readonly"); ?> >
                                                        <option value="1" <?php echo get_selected($row['des_status'], '1'); ?>>수정중</option>
                                                        <option value="2" <?php echo get_selected($row['des_status'], '2'); ?>>운영중</option>
                                                        <option value="3" <?php echo get_selected($row['des_status'], '3'); ?>>미사용</option>
                                                    </select>
                                                </td>
                                                
                                                <td>
                                                    <input type="text" id="des_memo" name="des_memo[]" class="custom_select" value="<?php echo $row['des_memo'] ?>">
                                                </td>

                                                <td>
                                                    <?php echo $row['des_cate_code_nm'] ?>
                                                </td>

                                                <td>
                                                    <?php echo '['.$row['deptnm'] .'] '. $row['mb_name'] ?>
                                                </td>

                                                <td>
                                                    <?php echo $row['update_user_name'] ?>
                                                </td>

                                                <td>
                                                    <?php echo view_dateformat($row['update_date']) ?>
                                                </td>

                                                <td>
                                                    <?php echo $row['used_pg_cnt'] ?>
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


<div class="modal fade" id="modal_page">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
        <div class="modal-header bg-success">
            <h4 class="modal-title">페이지 사용현황</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        
        <div class="card-body">
        <table class="table table-striped table-bordered">
            <thead>
            <tr>
                <th>NO</th>
                <th>부서</th>
                <th>디자인명</th>
                <th>페이지코드</th>
                <th>등록일자</th>
            </tr>
            </thead>
            <tbody id="dynamicTbody">
            
            </tbody>
        </table>
        </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modal-copy">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header bg-success">
            <h4 class="modal-title">디자인 복사</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form name="update_form" action="./design_list_update" method="post">
        <input type="hidden" id="design_idx" name="design_idx" value="">
            <div class="modal-body">
                <div class="form-group">
                    <label>이름</label>
                    <input type="text" id="design_name" name="design_name" class="form-control" disabled>
                </div>
                <div class="form-group">
                    <label>부서</label>
                    <!-- <select id="des_deptno" name="des_deptno" class="selectpicker form-control" data-live-search="true"> -->
                    <select id="des_deptno" name="des_deptno" class="form-control custom-select">
                    </select>
                </div>
                <div class="form-group">
                    <label>직원</label>
                    <select id="des_mb_no" name="des_mb_no" class="form-control custom-select">
                    </select>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" data-dismiss="modal">닫기</button>
                <button type="submit" class="btn btn-primary" name="act_button" value="카피">저장</button>
            </div>
        </form>
        </div>
    </div>
</div>

<script>
    $(function() {

        $("#sfl").change(function () {

            var obj = $(this).val();
            if(obj == "design_name") {
                
                $('#search_keyword1').removeClass("d-none");
                $('#search_keyword2').addClass("d-none");
                $('#search_keyword3').addClass("d-none");
                $('#search_keyword4').addClass("d-none");

                $('#search_keyword1').attr("disabled", false);
                $('#search_keyword2').attr("disabled", true);
                $('#search_keyword3').attr("disabled", true);
                $('#search_keyword4').attr("disabled", true);
                
            } else if(obj == "des_cate_code") {

                $('#search_keyword1').addClass("d-none");
                $('#search_keyword2').removeClass("d-none");
                $('#search_keyword3').addClass("d-none");
                $('#search_keyword4').addClass("d-none");

                $('#search_keyword1').attr("disabled", true);
                $('#search_keyword2').attr("disabled", false);
                $('#search_keyword3').attr("disabled", true);
                $('#search_keyword4').attr("disabled", true);

            } else if(obj == "deptno") {

                $('#search_keyword1').addClass("d-none");
                $('#search_keyword2').addClass("d-none");
                $('#search_keyword3').removeClass("d-none");
                $('#search_keyword4').addClass("d-none");

                $('#search_keyword1').attr("disabled", true);
                $('#search_keyword2').attr("disabled", true);
                $('#search_keyword3').attr("disabled", false);
                $('#search_keyword4').attr("disabled", true);
            } else if(obj == "des_gubun") {

                $('#search_keyword1').addClass("d-none");
                $('#search_keyword2').addClass("d-none");
                $('#search_keyword3').addClass("d-none");
                $('#search_keyword4').removeClass("d-none");

                $('#search_keyword1').attr("disabled", true);
                $('#search_keyword2').attr("disabled", true);
                $('#search_keyword3').attr("disabled", true);
                $('#search_keyword4').attr("disabled", false);
            } 

        });
        var table = $('#tbl_design').DataTable({
            "paging": false,
            "searching": false,
            "ordering": false,
            "info": false,
            "autoWidth": true,
            columnDefs:[
                {responsivePriority : 0     , targets: 0, "width":"1%"},
                {responsivePriority : 108   , targets: 1, "width":"2%"},
                {responsivePriority : 1     , targets: 2},
                {responsivePriority : 102   , targets: 3},
                {responsivePriority : 103   , targets: 4},
                {responsivePriority : 104   , targets: 5},
                {responsivePriority : 105   , targets: 6},
                {responsivePriority : 105   , targets: 7},
                {responsivePriority : 105   , targets: 8},
                {responsivePriority : 110   , targets: 9, "width":"1%",
                    render: function(data,type,row){
                        if (type === 'display') {
                            return "<button id='btn_info' type='button' class='btn btn-primary btn-xs w-100' data-toggle='modal' data-target='#modal_page' data-title='"+row[1]+"' data-p1='"+row[9]+"'>"+row[9]+"</button>";
                        }
                    }
                },
                {responsivePriority : 111   , targets: 10, "width":"1%",
                    render: function(data,type,row){
                        if (type === 'display') {
                            return "<button id='btn_info' type='button' class='btn btn-info btn-xs' data-toggle='modal' data-target='#modal-copy' data-title='"+row[1]+"' data-p1='"+row[2]+"'>복사</button>";
                        }
                    }
                },
            ]            
        });
    });

    $('#modal_page').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var design_idx = button.data('title');
        var act = "getPageList";
        var page_cnt = button.data('p1');

        var $target = $("#dynamicTbody");
        $target.empty();

        if(page_cnt == 0) {
            return false;
        }

        $.ajax({
                type: "post",
                url: "design_ajax",
                dataType: "json", 
                data: {
                    design_idx: design_idx ,
                    act: act
                },
                success: function (result) {
                    $target.append(result);
                }
        });


    });

    $('#modal-copy').on('show.bs.modal', function (event) {
        var act = "design_copy";

        var button = $(event.relatedTarget);
        var design_idx = button.data('title');
        var p_name = button.data('p1');
        var design_name = p_name.replace(/<\/?("[^"]*"|'[^']*'|[^>])*(>|$)/gi, "").replace(/^\s+/,"");
        //var modal = $(this);

        $('#modal-copy #design_idx').val(design_idx);
        $('#modal-copy #design_name').val(design_name);

        var $target1 = $("#modal-copy #des_deptno");
        $target1.empty();

        var $target2 = $("#modal-copy #des_mb_no");
        $target2.empty();

        $.ajax({
                type: "post",
                url: "design_ajax",
                dataType: "json", 
                data: {
                    design_idx: design_idx ,
                    act: act
                },
                success: function (result) {
                    var deptlist = result[0];
                    var emplist = result[1];
                    var dept_html = "";
                    var emp_html = "<option value=''>미지정</option>";
                    for(i=0; i<deptlist.length; i++) {
                        dept_html += '<option value="'+deptlist[i].deptno+'">'+deptlist[i].deptnm+'</option>';
                    }
                    $target1.append(dept_html);
                    for(i=0; i<emplist.length; i++) {
                        emp_html += '<option value="'+emplist[i].mb_no+'">'+emplist[i].mb_name+'</option>';
                    }
                    $target2.append(emp_html);
                }
        });        
    });


    $("#modal-copy #des_deptno").change(function() {
        var deptno = $(this).val();
        var act = "deptByEmp";
        var $target = $("#modal-copy #des_mb_no");
        $target.empty();

        $.ajax({
            type: "post",
            data: {
                deptno: deptno ,
                act: act
            },
            url: "<?php echo G5_BIZ_URL?>/common/code_ajax",
            dataType: "json", //전송받는 데이터형태 json
            success:function(result) {
                $target.append(result);
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                return;
            }
        });
    });


</script>

<script>
function execute(gubun, design_idx) {
    if(gubun == "2") {
        act = "design_delete";
        msg = "삭제";
        var result = confirm(msg+" 하시겠습니까?");
        if(result){
            var type = "POST";
            var url  = "design_ajax";
            var param = {
                    "act": act,
                    "design_idx":design_idx,
            };
            ajaxNetVoid(type , url , param);
            location.reload();
        }
    }
}
</script>

<?php
include_once(G5_PATH . '/tail.php');
