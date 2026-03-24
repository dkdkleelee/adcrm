<?php
require_once '../../common.php';
$g5['title'] = "후팝업 cpa 조회";
include_once(G5_PATH . '/head.php');

$sql_columns    = "";
$sql_common     = "";
$sql_search     = "";
$sql_gruop      = "";
$sql_order      = "";
$curr_order     = "";
$total_count    = 0;
$colspan        = 4;

//select
$sql_columns = "
  script_idx 
, script_name 
, script_code
, update_date 
, update_user 
";

$sql_common = "
from {$g5['crm_page_script']} a
";

$sql_search = " 
where 1=1 
  and use_yn = 'Y'
";


if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "":
            $sql_search .= " ($sfl like '$stx%') ";
            break;
    }
    $sql_search .= " ) ";
}


$cnt_sql = " 
select count(*) as cnt
  from {$g5['crm_page_script']}
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

$sql = " select {$sql_columns} {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);



?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">스크립트 조회(<?php echo $total_count ?>)</h3>
                    </div>
                    <div class="card-body">
                        <div class="dataTables_wrapper dt-bootstrap4">
                            <div class="d-flex flex-sm-row flex-column justify-content-sm-between">
                                <div class="d-flex justify-content-center mb-2 mb-sm-0">
                                    <div class="btn-group xs-100">
                                        <!-- <button type="submit" name="btn_ins" value="등록" onclick="" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i> 입력</button> -->
                                        
                                        <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#modal-add-script">
                                            <i class="fas fa-plus"></i> 등록
                                        </button>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <div class="btn-group xs-100">

                                        <form class="form-inline my-2 my-lg-0 ng-pristine ng-valid">
                                            <select name="sfl" id="sfl" class="custom-select">
                                                <option value="ptn_nm" <?php echo get_selected($sfl, "ptn_nm"); ?>>스크립트</option>
                                            </select>
                                            <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="form-control mr-sm-1" placeholder="검색어" aria-label="검색어">
                                            <button type="submit" class="btn btn-outline-success my-2 my-sm-0">검색</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <form name="listForm" id="listForm" action="./page_script_list_update" onsubmit="return listForm_submit(this);" method="post">
                                <input type="hidden" name="sst" value="<?php echo $sst ?>">
                                <input type="hidden" name="sod" value="<?php echo $sod ?>">
                                <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
                                <input type="hidden" name="stx" value="<?php echo $stx ?>">
                                <input type="hidden" name="page" value="<?php echo $page ?>">
                                <input type="hidden" name="token" value="<?php echo isset($token) ? $token : ''; ?>">

                                <div class="row">
                                    <div class="col-sm-12">
                                        <table id="script_table" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)"></th>
                                                    <th>NO</th>
                                                    <th>이름</th>
                                                    <th>스크립트</th>
                                                    <th>수정일</th>
                                                    <th>수정자</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php for ($i = 0; $row = sql_fetch_array($result); $i++) { 
                                                $new_icon = "";
                                                if ($row['insert_date'] >= $new_date) {
                                                    $new_icon = '<img src="'.G5_THEME_URL.'/img/new.gif" alt="새글"> ';
                                                }                                                
                                            ?>
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="script_idx[<?php echo $i ?>]" value="<?php echo $row['script_idx'] ?>">
                                                    <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
                                                </td>
                                                <td>
                                                    <?php echo $row['script_idx'] ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['script_name'] ?>
                                                </td>
                                                <td>
                                                    <?php echo strip_tags(conv_subject($row['script_code'], 100)) ?>
                                                </td>
                                                <td>
                                                    <?php echo view_dateformat($row['update_date']) ?>
                                                </td>
                                                <td>
                                                    <?php echo $row['update_user'] ?>
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

<div class="modal fade" id="modal-add-script">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header bg-success">
            <h4 class="modal-title">스크립트등록</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form name="update_form" action="./page_script_list_update" method="post">
            <div class="modal-body">
                <div class="form-group">
                    <label for="email">스크립트이름</label>
                    <input type="text" id="script_name" name="script_name" class="form-control" value="<?php echo $resultOne['script_name'] ?>" placeholder="스크립트이름">
                </div>
                <div class="form-group">
                    <label for="name">스크립트</label>
                    <textarea name="script_code" id="script_code" class="form-control" rows="8"></textarea>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" data-dismiss="modal">닫기</button>
                <button type="submit" class="btn btn-primary" name="act_button" value="저장">저장</button>
            </div>
        </form>
        </div>
    </div>
</div>


<div class="modal fade" id="modal-upd-script">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header bg-success">
            <h4 class="modal-title">스크립트수정</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form name="update_form" action="./page_script_list_update" method="post">
            <input type="hidden" id="script_idx" name="script_idx" value="">
            <div class="modal-body">
                <div class="form-group">
                    <label for="email">스크립트이름</label>
                    <input type="text" id="script_name" name="script_name" class="form-control" value="<?php echo $resultOne['script_name'] ?>" placeholder="스크립트이름">
                </div>
                <div class="form-group">
                    <label for="name">스크립트</label>
                    <textarea name="script_code" id="script_code" class="form-control" rows="8"></textarea>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" data-dismiss="modal">닫기</button>
                <button type="submit" class="btn btn-primary" name="act_button" value="수정">수정</button>
            </div>
        </form>
        </div>
    </div>
</div>



<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip();

        var table = $('#script_table').DataTable({
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
                {responsivePriority : 105   , targets: 6, "width":"2%",
                    render: function(data,type,row){
                        if (type === 'display') {
                            return "<button id='btn_info' type='button' class='btn btn-info btn-xs' data-toggle='modal' data-target='#modal-upd-script' data-title='"+row[1]+"'>수정</button>"+
                                   "<button id='btn_info' type='button' class='btn btn-danger btn-xs' onClick='execute(2,"+row[1]+")'>삭제</button>";
                        }
                    }
                },
            ]             
        });


        $('#modal-upd-script').on('show.bs.modal', function (event) {
            var act = "getScript";

            var button = $(event.relatedTarget);
            var script_idx = button.data('title');
            $('#modal-upd-script #script_idx').val(script_idx);

            var $target1 = $("#modal-upd-script #script_name");
            $target1.empty();

            var $target2 = $("#modal-upd-script #script_code");
            $target2.empty();
            
            $.ajax({
                type: "post",
                url: "page_ajax",
                contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                dataType: "json", 
                data: {
                    script_idx: script_idx ,
                    act: act
                },
                success: function (result) {
                    $target1.val(result.script_name);
                    $target2.val(result.script_code);
                }
            });
        });
    });
</script>
<script>
function execute(gubun, script_idx) {
    
    if(gubun == "2") {
        act = "page_delete";
        msg = "삭제";

        var result = confirm(msg+" 하시겠습니까?");
        if(result){
            var type = "POST";
            var url  = "page_ajax";
            var param = {
                    "act": act,
                    "script_idx":script_idx,
            };
            ajaxNetVoid(type , url , param);
            location.reload();
        }
    }
}
</script>

<?php
include_once(G5_PATH . '/tail.php');
