<?php

require_once '../../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');

$g5['title'] = "네이버 고객관리";
include_once(G5_PATH . '/head.php');

$sql_columns    = "";
$sql_common     = "";
$sql_search     = "";
$sql_gruop      = "";
$sql_order      = "";
$total_count    = 0;


//select
$sql_columns = "
  customerLinkId
, b.mb_no
, b.mb_id
, comp_name
, rpt_term
, rpt_type
, b.mb_name
, managerCustomerId
, clientCustomerId
, roleId
, linkStatus
, description
, regTm
, editTm
, managerLoginId
, clientLoginId
, managerName
, managerEnable
, managerPenaltySt
, managerCustomerDelFlag
, clientEnable
, clientPenaltySt
, clientCustomerDelFlag
, delFlag
, bizmoney
, api_ins_date
, api_upd_date
, is_sms_bizmoney
, cond_bizmoney
";

$sql_common = "
from gnp_kwd_customers a
left join {$g5['member_table']} b on a.mb_no = b.mb_no
";

$sql_search = "
where 1=1
and a.use_yn = 'Y'
and a.delFlag = 0
and a.mb_no is not null
";

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "clientLoginId":
            $sql_search .= " ($sfl like '%$stx%') ";
            break;
        case "comp_name":
            $sql_search .= " ($sfl like '%$stx%') ";
            break;
        case "managerName":
            $sql_search .= " ($sfl like '%$stx%') ";
            break;
        case "mb_id":
            $sql_search .= " ($sfl like '%$stx%') ";
            break;
        case "mb_name":
            $sql_search .= " ($sfl like '%$stx%') ";
            break;
    }
    $sql_search .= " ) ";
}


$cnt_sql = "
select count(*) as cnt
from gnp_kwd_customers a
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
    //$sql_order = "order by a.api_ins_date desc";
    $sql_order = "order by field(a.mb_no, ".$member['mb_no'].") desc , b.mb_name asc";
} else {
    $sql_order = " order by $sst $sod ";
}

$sql = " select {$sql_columns} {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$tr_num1 = $from_record +1;

//////////////////////////////////////////////////////////////////////////////////////////////////////



$sql_columns2    = "";
$sql_common2     = "";
$sql_search2     = "";
$sql_gruop2      = "";
$sql_order2      = "";
$total_count2    = 0;


//select
$sql_columns2 = "
  customerLinkId
, b.mb_no
, b.mb_id
, comp_name
, rpt_term
, rpt_type
, b.mb_name
, managerCustomerId
, clientCustomerId
, roleId
, linkStatus
, description
, regTm
, editTm
, managerLoginId
, clientLoginId
, managerName
, managerEnable
, managerPenaltySt
, managerCustomerDelFlag
, clientEnable
, clientPenaltySt
, clientCustomerDelFlag
, delFlag
, bizmoney
, api_ins_date
, api_upd_date
";

$sql_common2 = "
from gnp_kwd_customers a
left join {$g5['member_table']} b on a.mb_no = b.mb_no
";

$sql_search2 = "
where 1=1
and use_yn = 'Y'
and a.mb_no is null
and a.delFlag = 0
";



$cnt_sql2 = "
select count(*) as cnt
from gnp_kwd_customers a
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
    $sql_order2 = "order by a.managerLoginId desc";
} else {
    $sql_order2 = " order by $sst $sod ";
}

$sql2 = " select {$sql_columns2} {$sql_common2} {$sql_search2} {$sql_order2}";
$result2 = sql_query($sql2);

$tr_num2 = $from_record2 +1;

$replacement1 = array(
    "1" => "월",
    "2" => "주",
    "3" => "일"
);

$replacement2 = array(
    "1" => "검색",
    "2" => "소재",
    "3" => "파워"
);
?>






<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">[네이버 API연동완료] 담당자 지정완료(<?php echo $total_count ?>)</h3>
                    </div>
                    <div class="card-body">
                        <div class="dataTables_wrapper dt-bootstrap4">

                            <div class="d-flex flex-sm-row flex-column justify-content-sm-between">
                                <div class="d-flex justify-content-center mb-2 mb-xs-0">
                                    <div class="btn-group xs-100">
                                    <?php if($member['mb_level'] >= 6 || $member['mb_no'] == 98) { ?>
                                        <button type="button" form="listForm" class="btn btn-primary btn-sm border border-dark" id="btn_call_api" name="act_button" value="고객최신화" onclick="call_api()">
                                            <i class="fas fa-sync"></i> api최신화
                                        </button>
                                    <?php } ?>
                                    <?php if($member['mb_level'] >= 6) { ?>
                                        <button type="button" class="btn btn-success btn-sm border border-dark" data-toggle="modal" data-target="#modal-add-naver">
                                            <i class="fas fa-pen"></i> N 계정등록
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm border border-dark" data-toggle="modal" data-target="#modal-lst-naver">
                                            <i class="fas fa-list"></i> N 계정리스트
                                        </button>
                                    <?php } ?>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <div class="btn-group xs-100">

                                        <form class="form-inline my-2 my-lg-0 ng-pristine ng-valid">
                                            <select name="sfl" id="sfl" class="border border-dark custom-select">
                                                <option value="clientLoginId" <?php echo get_selected($sfl, "clientLoginId"); ?>>(고객)네이버 ID</option>
                                                <option value="managerName" <?php echo get_selected($sfl, "managerName"); ?>>네이버 담당자</option>
                                                <option value="comp_name" <?php echo get_selected($sfl, "comp_name"); ?>>업체명</option>
                                                <option value="mb_name" <?php echo get_selected($sfl, "mb_name"); ?>>직원이름</option>
                                            </select>
                                            
                                            <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="border-dark form-control sm-1" placeholder="검색어" aria-label="검색어">
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
                                                    <th>API KEY</th>
                                                    <th><?php echo get_sort_bootst('clientLoginId','', 'desc', $sst, $sod, '(고객)네이버 ID'); ?></th>
                                                    <th>네이버계정:이름</th>
                                                    <th>업체명</th>
                                                    <th>담당직원ID</th>
                                                    <th>담당직원이름</th>
                                                    <th>보고서주기</th>
                                                    <th>보고서타입</th>
                                                    <th>알람액</th>
                                                    <th>잔액</th>
                                                    <th><?php echo get_sort_bootst('api_ins_date','', 'desc', $sst, $sod, 'API 등록일자'); ?></th>
                                                    <th><?php echo get_sort_bootst('api_upd_date','', 'desc', $sst, $sod, 'API 수정일자'); ?></th>
                                                    <th>삭제Flag</th>
                                                    <th>관리</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php for ($i = 0; $row = sql_fetch_array($result); $i++) { ?>
                                                    <tr>
                                                        <td class="text-center">
                                                            <?php echo $tr_num1;
                                                            $tr_num1 = $tr_num1 + 1;
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $row['customerLinkId'] ?>
                                                        </td>
                                                       
                                                        <td>
                                                            <a href="https://manage.searchad.naver.com/customers/<?php echo $row['clientCustomerId'] ?>/campaigns" target="_blank">
                                                                <?php echo $row['clientLoginId'] ?>
                                                            </a>
                                                        </td>

                                                        <td>
                                                            <?php echo $row['managerLoginId'].':'.$row['managerName'] ?>
                                                        </td>

                                                        <td>
                                                            <?php echo $row['comp_name'] ?>
                                                        </td>
                                                        
                                                        <td>
                                                            <a href="<?php echo G5_BIZ_URL ?>/keywd/kwd_customers_list?sfl=mb_id&stx=<?php echo $row['mb_id'] ?>" target="_self">
                                                                <?php echo $row['mb_id'] ?>
                                                            </a>
                                                        </td>


                                                        <td>
                                                            <?php echo $row['mb_name'] ?>
                                                        </td>

                                                        <td>
                                                            <?php 
                                                                $rpt_term = str_replace(array_keys($replacement1), array_values($replacement1), $row['rpt_term']);
                                                                echo $rpt_term;
                                                            ?>
                                                        </td>

                                                        <td>
                                                            <?php 
                                                                $rpt_type = str_replace(array_keys($replacement2), array_values($replacement2), $row['rpt_type']);
                                                                echo $rpt_type;
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $row['is_sms_bizmoney'] == "Y" ? "[Y]".number_format($row['cond_bizmoney']) : "[N]" ?>
                                                        </td>
														<td>
                                                            <?php echo number_format($row['bizmoney']) ?>
                                                        </td>

                                                        <td>
                                                            <?php echo view_dateformat($row['api_ins_date']) ?>
                                                        </td>

                                                        <td>
                                                            <?php echo view_dateformat($row['api_upd_date']) ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php echo $row['delFlag'] == 0 ? "" : "<button id='btn_info' type='button' class='btn btn-danger btn-xs' onclick='deletePartner(".$row['customerLinkId'].")'>삭제됨</button>" ?>
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



        <div class="row">
            <div class="col-12">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title">[네이버 API연동완료] 담당자 미지정(<?php echo $total_count2 ?>)</h3>
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
                                                    <th>API KEY</th>
                                                    <th>(고객)네이버 ID</th>
                                                    <th>네이버계정:이름</th>
                                                    
                                                    <th>잔액</th>
                                                    <th>API 등록일자</th>
                                                    <th>API 수정일자</th>
                                                    <th>삭제Flag</th>
                                                    <th>관리</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php for ($i = 0; $row = sql_fetch_array($result2); $i++) { ?>
                                                    <tr>
                                                        <td class="text-center">
                                                            <?php echo $tr_num2;
                                                            $tr_num2 = $tr_num2 + 1;
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $row['customerLinkId'] ?>
                                                        </td>
                                                       
                                                        <td>
                                                            <a href="https://manage.searchad.naver.com/customers/<?php echo $row['clientCustomerId'] ?>/campaigns" target="_blank">
                                                                <?php echo $row['clientLoginId'] ?>
                                                            </a>
                                                        </td>

                                                        <td>
                                                            <?php echo $row['managerLoginId'].':'.$row['managerName'] ?>
                                                        </td>

                                                        <td>
                                                            <?php echo number_format($row['bizmoney']) ?>
                                                        </td>

                                                        <td>
                                                            <?php echo view_dateformat($row['api_ins_date']) ?>
                                                        </td>

                                                        <td>
                                                            <?php echo view_dateformat($row['api_upd_date']) ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php echo $row['delFlag'] == 0 ? "" : "<button id='btn_info' type='button' class='btn btn-danger btn-xs' onclick='deletePartner(".$row['customerLinkId'].")'>삭제됨</button>" ?>
                                                        </td>
                                                        <td>
                                                            
                                                        </td>
                                                        
                                                    </tr>
                                                <?php } ?>
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


<!-- tbody naver id start -->
<div class="modal fade" id="modal-add-naver">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header bg-success">
            <h4 class="modal-title">네이버 마스터계정 등록</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form name="update_form" action="./kwd_ajax" method="post">
            <input type="hidden" id="act" name="act" value="ajax_add_naver">
            <div class="modal-body">
                <div class="form-group">
                    <label>담당자</label>
                    <select id="mb_no" name="mb_no" class="form-control custom-select">
                    </select>
                </div>

                <div class="form-group">
                    <label>네이버(광고) 아이디</label>
                    <input type="text" id="naver_id" name="naver_id" class="form-control">
                </div>
                <div class="form-group">
                    <label>네이버(광고) 패스워드</label>
                    <input type="text" id="naver_pw" name="naver_pw" class="form-control">
                </div>
                <div class="form-group">
                    <label>CUSTOMER_ID</label>
                    <input type="text" id="customer_id" name="customer_id" class="form-control">
                </div>
                <div class="form-group">
                    <label>엑세스라이선스</label>
                    <input type="text" id="access_license" name="access_license" class="form-control">
                </div>
                <div class="form-group">
                    <label>비밀키</label>
                    <input type="text" id="access_secretkey" name="access_secretkey" class="form-control">
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" data-dismiss="modal">닫기</button>
                <button type="submit" class="btn btn-primary" name="act_button" value="네이버계정등록">저장</button>
            </div>
        </form>
        </div>
    </div>
</div>


<div class="modal fade" id="modal-upd-naver" style="z-index:9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header bg-success">
            <h4 class="modal-title">네이버 마스터계정 수정</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form name="update_form" action="./kwd_ajax" method="post">
            <input type="hidden" id="act" name="act" value="ajax_upd_naver">
            <input type="hidden" id="naver_idx" name="naver_idx" value="">
            <div class="modal-body">
                <div class="form-group">
                    <label>아이디</label>
                    <input type="text" id="naver_id" name="naver_id" value="" class="form-control">
                </div>
                <div class="form-group">
                    <label>패스워드</label>
                    <input type="text" id="naver_pw" name="naver_pw" value="" class="form-control">
                </div>
                <div class="form-group">
                    <label>CUSTOMER_ID</label>
                    <input type="text" id="customer_id" name="customer_id" class="form-control">
                </div>
                <div class="form-group">
                    <label>엑세스라이선스</label>
                    <input type="text" id="access_license" name="access_license" class="form-control">
                </div>
                <div class="form-group">
                    <label>비밀키</label>
                    <input type="text" id="access_secretkey" name="access_secretkey" class="form-control">
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-danger" data-dismiss="modal">닫기</button>
                <button type="submit" class="btn btn-primary" name="act_button" value="네이버계정수정">수정</button>
            </div>
        </form>
        </div>
    </div>
</div>



<div class="modal fade" id="modal-lst-naver">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h4 class="modal-title">네이버 아이디 리스트</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            </div>
            <div class="modal-body">
                <table class="table table-striped table-bordered dt-responsive nowrap table-dark">
                    <thead>
                    <tr>
                        <th>NO</th>
                        <th>아이디</th>
                        <th>비밀번호</th>
                        <th>API ID</th>
                        <th>담당자</th>
                        <th>등록일</th>
                        <th>수정일</th>
                        <th>관리</th>
                    </tr>
                    </thead>
                    <tbody id="dynamic-tbody">
                    
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- tbody naver id end -->


<!-- tbody modal start -->
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
            <input type="hidden" id="customerLinkId" name="customerLinkId" value="">
            <div class="modal-body">
                <div class="form-group">
                    <label>업체명</label>
                    <input type="text" id="comp_name" name="comp_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>직원</label>
                    <select id="mb_no" name="mb_no" class="form-control custom-select" required>
                    </select>
                </div>

                <div class="form-group">
                    <label>구분</label>
                    <select id="rpt_term" name="rpt_term[]" class="selectpicker form-control custom-select" multiple data-actions-box="true" multiple required>
                        <option value="1" selected>(1)월간 보고서</option>
                        <option value="2">(2)주간 보고서</option>
                        <option value="3">(3)일별 데이터</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>타입</label>
                    <select id="rpt_type" name="rpt_type[]" class="selectpicker form-control custom-select" multiple data-actions-box="true" multiple required>
                        <option value="1" selected>(1)쇼핑검색</option>
                        <option value="2" selected>(2)쇼핑소재</option>
                        <option value="3" selected>(3)파워링크</option>
                    </select>
                </div>

                <label>광고비(이하)알람</label>
                <div class="input-group mb-3">
                    <label class="input-group-text">
                        <input type="checkbox" name="is_sms_bizmoney" id="is_sms_bizmoney">
                    </label>
                    <input type="text" name="cond_bizmoney" id="cond_bizmoney" class="form-control" placeholder="금액을 입력해주세요. (해당 금액 미만 시 알람)" oninput="addCommas(this)" maxlength="11" disabled>
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
            <input type="hidden" id="customerLinkId" name="customerLinkId" value="">
            <div class="modal-body">
                <div class="form-group">
                    <label>업체명</label>
                    <input type="text" id="comp_name" name="comp_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>직원</label>
                    <select id="mb_no" name="mb_no" class="form-control custom-select" required>
                    </select>
                </div>

                <div class="form-group">
                    <label>구분</label>
                    <select id="rpt_term" name="rpt_term[]" class="selectpicker form-control custom-select" multiple data-actions-box="true" multiple>
                        <option value="1" selected>(1)쇼핑검색</option>
                        <option value="2" selected>(2)쇼핑소재</option>
                        <option value="3" selected>(3)파워링크</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>타입</label>
                    <select id="rpt_type" name="rpt_type[]" class="selectpicker form-control custom-select" multiple data-actions-box="true" multiple>
                        
                    </select>
                </div>
                
                <label>광고비(이하)알람</label>
                <div class="input-group mb-3">
                    <label class="input-group-text">
                        <input type="checkbox" name="is_sms_bizmoney" id="is_sms_bizmoney">
                    </label>
                    <input type="text" name="cond_bizmoney" id="cond_bizmoney" class="form-control" placeholder="금액을 입력해주세요. (해당 금액 미만 시 알람)" oninput="addCommas(this)" maxlength="11" disabled>
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
<!-- tbody modal end -->

<script>


     $(function() {

        $('#modal-add #is_sms_bizmoney').change(function() {
            if (!$(this).is(':checked')) {
                $('#modal-add #cond_bizmoney').attr("disabled"  , true);
                $('#modal-add #cond_bizmoney').val("0");
            } else {
                $('#modal-add #cond_bizmoney').attr("disabled"  , false);
            }
        });

        $('#modal-upd #is_sms_bizmoney').change(function() {
            if (!$(this).is(':checked')) {
                $('#modal-upd #cond_bizmoney').attr("disabled"  , true);
                $('#modal-upd #cond_bizmoney').val("0");
            } else {
                $('#modal-upd #cond_bizmoney').attr("disabled"  , false);
            }
        });


        //// DataTable 초기화
        // var table = $('#tbl_list2').DataTable({
        // scrollY: '300px', // 원하는 스크롤 높이로 설정
        // paging: false, // 페이지네이션 비활성화
        // });

        // // 스크롤 이벤트 리스너
        // $('#tbl_list2').on('scroll', function() {
        //     var tableHeight = $('#tbl_list2').outerHeight(); // 테이블의 높이
        //     var scrollHeight = $('#tbl_list2').get(0).scrollHeight; // 테이블 내용 전체의 스크롤 높이
        //     var scrollTop = $('#tbl_list2').scrollTop(); // 현재 스크롤 위치

        //     if (scrollTop === scrollHeight - tableHeight) {
        //         // 스크롤이 맨 아래에 도달한 경우

        //         // Ajax 호출을 통해 추가 데이터를 가져온다.
        //         $.ajax({
        //         url: '데이터를 가져올 URL',
        //         method: 'GET',
        //         // 필요한 경우 데이터나 쿼리 매개변수를 설정한다.

        //         success: function(data) {
        //             // Ajax 요청이 성공한 경우

        //             // 가져온 데이터를 사용하여 <tr>을 생성하고 DataTable에 추가한다.
        //             data.forEach(function(item) {
        //             var row = table.row.add([
        //                 item.column1,
        //                 item.column2,
        //                 // 추가 열을 필요에 따라 설정한다.
        //             ]).draw(false).node();
        //             });
        //         },
        //         error: function() {
        //             // Ajax 요청이 실패한 경우 에러 처리
        //         }
        //         });
        //     }
        // });



        /* 네이버 계정 리스트*/
        $('#modal-add-naver').on('show.bs.modal', function (event) {

            var act = "add_acc";
            $.ajax({
                type: "post",
                url: "kwd_ajax",
                dataType: "json", 
                data: {
                    act: act
                },
                success: function (result) {
                    $('#modal-add-naver #mb_no').val(result[0].comp_name);
                    $('#modal-add-naver #mb_no').append(result);
                }
            });
        });

        /* 네이버 계정 리스트*/
        $('#modal-lst-naver').on('show.bs.modal', function (event) {
            $("#dynamic-tbody").empty();
            var act = "lst-naver";
            $.ajax({
                type: "post",
                data: {
                    act: act
                },
                url: "kwd_ajax",
                dataType: "json",
                success:function(result) {
                    $("#dynamic-tbody").append(result);
                    $('#modal-lst-naver').modal("show");
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    return;
                }
            });
        });

        /* 담당자 추가 */
        $('#modal-add').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var act = "add_comp";
            var customerLinkId = button.data('p1');

            $('#modal-add #comp_name').val("");
            $("#modal-add #mb_no option:eq(0)").prop("selected", true);
            $('#modal-add #customerLinkId').val(customerLinkId);
            
            var $target = $("#modal-add #mb_no");
            $target.empty();
           
            $.ajax({
                type: "post",
                url: "kwd_ajax",
                dataType: "json", 
                data: {
                    act: act
                  , customerLinkId : customerLinkId
                },
                success: function (result) {
                    $target.append(result);
                }
            });
        });

        /* 담당자 수정 */
        $('#modal-upd').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var act = "upd_comp";
            var mb_no = button.data('title');
            var customerLinkId = button.data('p1');
            
            $('#modal-upd #comp_name').val("");
            $("#modal-upd #mb_no option:eq(0)").prop("selected", true);
            $('#modal-upd #customerLinkId').val(customerLinkId);

            $("#modal-upd #rpt_term option:eq(0)").prop("selected", true);
            $("#modal-upd #rpt_type option:eq(0)").prop("selected", true);

            var $target = $("#modal-upd #mb_no");
            $target.empty();

            $("#modal-upd #rpt_term").selectpicker('destroy');
            $("#modal-upd #rpt_term").empty();

            $("#modal-upd #rpt_type").selectpicker('destroy');
            $("#modal-upd #rpt_type").empty();
            

            $.ajax({
                type: "post",
                url: "kwd_ajax",
                dataType: "json", 
                data: {
                    act: act
                  , mb_no: mb_no
                  , customerLinkId : customerLinkId
                },
                success: function (result) {
                    $('#modal-upd #comp_name').val(result[0].comp_name);
                    $target.append(result);
                    
                    var rpt_term_html = "";
                    var rpt_term = "";
                    var rpt_type = "";

                    if(result[0].rpt_term != null) {
                        rpt_term = result[0].rpt_term.split("||");
                    }
                    
                    var sel1 = "";
                    var sel2 = "";
                    var sel3 = "";

                    if (rpt_term[0] == "1" || rpt_term[1] == "1" || rpt_term[2] == "1") sel1 = " selected";
                    if (rpt_term[0] == "2" || rpt_term[1] == "2" || rpt_term[2] == "2") sel2 = " selected";
                    if (rpt_term[0] == "3" || rpt_term[1] == "3" || rpt_term[2] == "3") sel3 = " selected";

                    $("#modal-upd #rpt_term").append("<option value='1'" + sel1 + ">(1)월간 보고서</option>");
                    $("#modal-upd #rpt_term").append("<option value='2'" + sel2 + ">(2)주간 보고서</option>");
                    $("#modal-upd #rpt_term").append("<option value='3'" + sel3 + ">(3)일별 보고서</option>");
                    $("#modal-upd #rpt_term").selectpicker('refresh');

                    if(result[0].rpt_type != null) {
                        rpt_type = result[0].rpt_type.split("||");
                    }

                    var sel1 = "";
                    var sel2 = "";
                    var sel3 = "";

                    if (rpt_type[0] == "1" || rpt_type[1] == "1" || rpt_type[2] == "1") sel1 = " selected";
                    if (rpt_type[0] == "2" || rpt_type[1] == "2" || rpt_type[2] == "2") sel2 = " selected";
                    if (rpt_type[0] == "3" || rpt_type[1] == "3" || rpt_type[2] == "3") sel3 = " selected";

                    $("#modal-upd #rpt_type").append("<option value='1'" + sel1 + ">(1)쇼핑검색</option>");
                    $("#modal-upd #rpt_type").append("<option value='2'" + sel2 + ">(2)쇼핑소재</option>");
                    $("#modal-upd #rpt_type").append("<option value='3'" + sel3 + ">(3)파워링크</option>");
                    $("#modal-upd #rpt_type").selectpicker('refresh');

                    if(result[0].is_sms_bizmoney == "Y") {
                        $('#modal-upd #is_sms_bizmoney').prop('checked', true);

                        var numberString = result[0].cond_bizmoney; // 문자열 형식의 숫자
                        var number = parseFloat(numberString); // 문자열을 숫자로 변환
                        var formattedNumber = number.toLocaleString(); // 형식화된 숫자
                        $('#modal-upd #cond_bizmoney').val(formattedNumber);
                        $('#modal-upd #cond_bizmoney').attr("disabled"  , false);
                    } else {
                        $('#modal-upd #is_sms_bizmoney').prop('checked', false);
                        $('#modal-upd #cond_bizmoney').attr("disabled"  , true);
                    }
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
                {responsivePriority : 103   , targets: 4},
                {responsivePriority : 104   , targets: 5},
                {responsivePriority : 105   , targets: 6},
                {responsivePriority : 106   , targets: 7, "width":"2%"},
                {responsivePriority : 107   , targets: 8, "width":"2%"},
                {responsivePriority : 108   , targets: 9, "width":"2%"},
                {responsivePriority : 109   , targets: 10, "width":"2%"},
                {responsivePriority : 110   , targets: 11, "width":"2%"},
                {responsivePriority : 111   , targets: 12, "width":"2%"},
                {responsivePriority : 112   , targets: 13, "width":"2%"},
                {responsivePriority : 113   , targets: 14, "width":"1%",
                    render: function(data,type,row){
                        if (type === 'display') {
                            if(row[6] != "" || row[6] != "undefined") {
                                return "<button id='btn_info' type='button' class='btn btn-warning btn-xs' data-toggle='modal' data-target='#modal-upd' data-title='"+row[4]+"' data-p1='"+row[1]+"'>수정</button>";
                            }                           
                        }
                    }
                },
            ]            
        });

        var table = $('#tbl_list2').DataTable({
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
                {responsivePriority : 103   , targets: 4},
                {responsivePriority : 104   , targets: 5},
                {responsivePriority : 105   , targets: 6},
                {responsivePriority : 106   , targets: 7, "width":"2%"},
                {responsivePriority : 107   , targets: 8, "width":"1%",
                    render: function(data,type,row){
                        if (type === 'display') {
                            return "<button id='btn_info' type='button' class='btn btn-danger btn-xs' data-toggle='modal' data-target='#modal-add' data-p1='"+row[1]+"'>등록</button>";
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

     function click_modal_upd(param){
        obj = param.parentElement.parentElement.id;
        var tr = document.getElementById(obj);
        var tds = tr.getElementsByTagName("td");

        var naver_idx = tds[1].textContent
        var naver_id = tds[2].textContent;
        var naver_pw = tds[3].textContent;

        var customer_id = tds[4].textContent;
        var access_license = tds[5].textContent;
        var access_secretkey = tds[6].textContent;

        $('#modal-upd-naver').modal("show");

        $("#modal-upd-naver #naver_idx").val(naver_idx);
        $("#modal-upd-naver #naver_id").val(naver_id);
        $("#modal-upd-naver #naver_pw").val(naver_pw);

        $("#modal-upd-naver #customer_id").val(customer_id);
        $("#modal-upd-naver #access_license").val(access_license);
        $("#modal-upd-naver #access_secretkey").val(access_secretkey);
     }
     
     function deletePartner(param){
        var result = confirm("(네이버에서 해지된 계정으로 확인 삭제전 확인필수)\n시스템에서 삭제하시겠습니까?");
        if(result){

            var customerLinkId = param;
            var act = "del_comp";
            var url  = "kwd_ajax";
           
            $.ajax({
                type: "post",
                url: "kwd_ajax",
                dataType: "json", 
                data: {
                    act: act,
                    customerLinkId: customerLinkId
                },
                success: function (result) {
                    if(result == "deleted") {

                        alert("삭제처리 되었습니다.");
                        document.location.reload();

                    } else {
                        alert("삭제되지않았습니다. 담당개발자에게 문의해주세요.")
                    }
                }
            });
        }
    }
</script>


<?php







include_once(G5_PATH . '/tail.php');

