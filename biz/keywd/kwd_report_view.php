<?php


require_once '../../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');

$g5['title'] = "네이버 보고서관리";
include_once(G5_PATH . '/head.php');



?>






<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">네이버 보고서관리</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="iframe-wrap">
                                    <!-- <iframe width="853" height="480" src="http://keyword.co.kr" allow="clipboard-write" frameborder="0" allowfullscreen></iframe> -->
                                    <iframe width="853" height="480" src="https://keyword.gonplan.co.kr" allow="clipboard-write" frameborder="0" allowfullscreen></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<div class="modal fade" id="modal-add">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header bg-success">
            <h4 class="modal-title">업체 담당자등록</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form name="update_form" action="./kwd_customers_list_update" method="post">
            <input type="hidden" id="clientCustomerId" name="clientCustomerId" value="">
            <div class="modal-body">
                <div class="form-group">
                    <label>업체명</label>
                    <input type="text" id="comp_name" name="comp_name" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>직원</label>
                    <select id="mb_no" name="mb_no" class="form-control custom-select">
                    </select>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" data-dismiss="modal">닫기</button>
                <button type="submit" class="btn btn-primary" name="act_button" value="담당자등록">등록</button>
            </div>
        </form>
        </div>
    </div>
</div>



<div class="modal fade" id="modal-upd">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header bg-success">
            <h4 class="modal-title">업체 담당자수정</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form name="update_form" action="./kwd_customers_list_update" method="post">
            <input type="hidden" id="clientCustomerId" name="clientCustomerId" value="">
            <div class="modal-body">
                <div class="form-group">
                    <label>업체명</label>
                    <input type="text" id="comp_name" name="comp_name" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>직원</label>
                    <select id="mb_no" name="mb_no" class="form-control custom-select">
                    </select>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" data-dismiss="modal">닫기</button>
                <button type="submit" class="btn btn-primary" name="act_button" value="담당자수정">수정</button>
            </div>
        </form>
        </div>
    </div>
</div>


<script>
     $(function() {

        $('#modal-add').on('show.bs.modal', function (event) {

            var button = $(event.relatedTarget);
            var act = "add_comp";
            
            var clientCustomerId = button.data('p1');

            $('#modal-add #comp_name').val("");
            $("#modal-add #mb_no option:eq(0)").prop("selected", true);
            $('#modal-add #clientCustomerId').val(clientCustomerId);
            
            var $target = $("#modal-add #mb_no");
            $target.empty();
           
            $.ajax({
                type: "post",
                url: "kwd_ajax",
                dataType: "json", 
                data: {
                    act: act
                  , clientCustomerId : clientCustomerId
                },
                success: function (result) {
                    $target.append(result);
                }
            });
        });


        $('#modal-upd').on('show.bs.modal', function (event) {
            
            var button = $(event.relatedTarget);
            var act = "upd_comp";

            var mb_no = button.data('title');
            var clientCustomerId = button.data('p1');
            
            $('#modal-upd #comp_name').val("");
            $("#modal-upd #mb_no option:eq(0)").prop("selected", true);
            $('#modal-upd #clientCustomerId').val(clientCustomerId);

            var $target = $("#modal-upd #mb_no");
            $target.empty();

            $.ajax({
                type: "post",
                url: "kwd_ajax",
                dataType: "json", 
                data: {
                    act: act
                  , mb_no: mb_no
                  , clientCustomerId : clientCustomerId
                },
                success: function (result) {
                    $('#modal-upd #comp_name').val(result[0].comp_name);
                    $target.append(result);
                }
            });
        });






        //datatable load
        var table = $('#tbl_list').DataTable({
            "paging": false,
            "searching": false,
            "ordering": false,
            "info": false,
            "autoWidth": true,
            columnDefs:[
                {responsivePriority : 0     , targets: 0, "width":"1%"},
                {responsivePriority : 1     , targets: 1, "width":"2%"},
                {responsivePriority : 2     , targets: 2},
                {responsivePriority : 102   , targets: 3},
                {responsivePriority : 103   , targets: 4, visible: false},
                {responsivePriority : 104   , targets: 5},
                {responsivePriority : 105   , targets: 6},
                {responsivePriority : 106   , targets: 7, "width":"2%"},
                {responsivePriority : 107   , targets: 8, "width":"2%"},
                {responsivePriority : 108   , targets: 9, "width":"1%",
                    render: function(data,type,row){
                        if (type === 'display') {
                            if(row[5] == "" || row[5] == "undefined") {
                                return "<button id='btn_info' type='button' class='btn btn-info btn-xs' data-toggle='modal' data-target='#modal-add' data-p1='"+row[1]+"'>등록</button>";
                            } else {
                                return "<button id='btn_info' type='button' class='btn btn-info btn-xs' data-toggle='modal' data-target='#modal-upd' data-title='"+row[4]+"' data-p1='"+row[1]+"'>수정</button>";
                            }
                            
                        }
                    }
                },
            ]            
        });


        $('#btn').click(function () {
            alert("button");
        });


     });

     function call_api(){

        if (!confirm("네이버 고객 데이터 API 최신화를 시작하겠습니까?\n완료까지는 약 1분정도 소요됩니다.")) {
            return false;
        } else {
            document.getElementById("btn_call_api").disabled = "disabled";

            var val = document.createElement('input');
            val.setAttribute("type", "hidden");
            val.setAttribute("name", "act_button");
            val.setAttribute("value", "고객최신화");
            $("#listForm").append(val);
            $("#listForm").submit();
        }
     }
</script>


<?php







include_once(G5_PATH . '/tail.php');

