<?php
require_once '../../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');

$g5['title'] = "근태관리";
include_once(G5_PATH . '/head.php');


$is_manager = false;

if($member['mb_level'] >= 6) {
    $is_manager = true;
}

if($member['mb_deptno'] == "6" || $member['mb_deptno'] == "7") {
    $dept_condition = "vaca_mb_deptno in (6,7)";
} else {
    $dept_condition = "vaca_mb_deptno = {$member['mb_deptno']}";
}


$selected_dept = "";
if ($cond_dept) {
    $cond_dept = "";
}

//1.자기부서 미상신건 쿼리조회 
$sql_1 = "
select a.*
     , b.mb_name 
     , b.mb_vaca_cnt 
     , a.vaca_comment
     , concat(date_format(a.insert_date, '%Y년 %m월 %d일 '), 
       case dayofweek(a.insert_date)
           when 1 then '일요일'
           when 2 then '월요일'
           when 3 then '화요일'
           when 4 then '수요일'
           when 5 then '목요일'
           when 6 then '금요일'
           when 7 then '토요일'
       end,
       date_format(a.insert_date, ' %h시 %i분')) as insert_date2
     , b.mb_vaca_cnt - (select count(*) from {$g5['crm_vaca_mng']} sub where sub.vaca_mb_no = {$member['mb_no']} and vaca_status = '3' and sub.vaca_start_date <= last_day(date_format(curdate(), '%y-12-31')) and sub.vaca_end_date >= date_format(curdate(), '%y-01-01')) as total_vaca_cnt
  from {$g5['crm_vaca_mng']} a
  left join {$g5['member_table']} b on a.vaca_mb_no = b.mb_no  
 where $dept_condition
   and vaca_status = 1
   and vaca_end_date >= date_sub(curdate(), interval 3 month)
order by vaca_start_date asc
";
$list1 = sql_query($sql_1);
$count1 = sql_num_rows($list1);
$html1 = "";
for ($i = 1; $data1 = sql_fetch_array($list1); $i++) {
    $btn1 = "";

    //레별별 그리고 자신상신건은 삭제 가능하게 버튼 생성
    if($member['mb_level'] >= 6 && $member['mb_no'] == $data1['vaca_mb_no']) {
        $btn1 = "
        <div class='btn-group btn-group-toggle' data-toggle='buttons'>
            <label class='btn btn-primary active'>
                <input type='radio' name='options' id='confirm' checked> 상신
            </label>
            <label class='btn btn-danger'>
                <input type='radio' name='options' id='reject'> 반려
            </label>
            <label class='btn btn-info'>
                <input type='radio' name='options' id='cancle'> 취소
            </label>
        </div>
        ";
    } 
    else if($member['mb_level'] >= 6 && $member['mb_no'] != $data1['vaca_mb_no']) {
        $btn1 = "
        <div class='btn-group btn-group-toggle' data-toggle='buttons'>
            <label class='btn btn-primary active'>
                <input type='radio' name='options' id='confirm' checked> 상신
            </label>
            <label class='btn btn-danger'>
                <input type='radio' name='options' id='reject'> 반려
            </label>
        </div>
        ";
    } else if($member['mb_no'] == $data1['vaca_mb_no']) {
        $btn1 = "
        <div class='btn-group btn-group-toggle' data-toggle='buttons'>
            <label class='btn btn-info'>
                <input type='radio' name='options' id='cancle'> 취소
            </label>
        </div>
        ";
    }

    $html1 .= "
    <div class='row'>
        <input type='hidden' name='vaca_idx' id='vaca_idx' value='{$data1['vaca_idx']}'>
        <div class='col-auto text-center flex-column d-none d-sm-flex'>
            <div class='row h-50'>
                <div class='col'>&nbsp;</div>
                <div class='col'>&nbsp;</div>
            </div>
            <h5 class='m-2'>
                <span class='badge badge-pill bg-light border'>&nbsp;</span>
            </h5>
            <div class='row h-50'>
                <div class='col border-right'>&nbsp;</div>
                <div class='col'>&nbsp;</div>
            </div>
        </div>
        <div class='col py-2'>
            <div class='card'>
                <div class='card-body'>
                    <div class='float-right text-muted'>[상신일] {$data1['insert_date2']} <span class='badge badge-secondary'>미상신</span></div>
                    <h4 class='card-title'>{$data1['vaca_start_date']} ~ {$data1['vaca_end_date']}</h4>
                    <p class='card-text font-weight-bold'>{$i}. {$data1['mb_name']} {$data1['vaca_name']} (사유:{$data1['vaca_comment']})
                        <button type='button' class='btn btn-xs btn-primary'>남은휴가 <span class='badge badge-light'>{$data1['total_vaca_cnt']}</span></button>
                    </p>
                    {$btn1}
                </div>
            </div>
        </div>
    </div>
    ";
}
//2.자기부서 반려건 쿼리조회
$sql_2 = "
select a.*
     , b.mb_name 
     , b.mb_vaca_cnt 
     , a.manager_comment
     , concat(date_format(a.insert_date, '%Y년 %m월 %d일 '), 
     case dayofweek(a.insert_date)
         when 1 then '일요일'
         when 2 then '월요일'
         when 3 then '화요일'
         when 4 then '수요일'
         when 5 then '목요일'
         when 6 then '금요일'
         when 7 then '토요일'
     end,
     date_format(a.insert_date, ' %h시 %i분')) as insert_date2
  from {$g5['crm_vaca_mng']} a
  left join {$g5['member_table']} b on a.vaca_mb_no = b.mb_no  
 where $dept_condition
   and vaca_status = 3
   and vaca_end_date >= date_sub(curdate(), interval 3 month)
 order by vaca_start_date asc
";
$list2 = sql_query($sql_2);
$count2 = sql_num_rows($list2);
$html2 = "";

for ($i = 1; $data2 = sql_fetch_array($list2); $i++) {

    $html2 .= "
    <div class='row'>
        <input type='hidden' name='vaca_idx' id='vaca_idx' value='{$data2['vaca_idx']}'>
        <div class='col-auto text-center flex-column d-none d-sm-flex'>
            <div class='row h-50'>
                <div class='col'>&nbsp;</div>
                <div class='col'>&nbsp;</div>
            </div>
            <h5 class='m-2'>
                <span class='badge badge-pill bg-light border'>&nbsp;</span>
            </h5>
            <div class='row h-50'>
                <div class='col border-right'>&nbsp;</div>
                <div class='col'>&nbsp;</div>
            </div>
        </div>
        <div class='col py-2'>
            <div class='card'>
                <div class='card-body'>
                    <div class='float-right text-muted'>[반려일] {$data2['insert_date2']} <span class='badge badge-danger'>반려</span></div>
                    <h4 class='card-title'>{$data2['vaca_start_date']} ~ {$data2['vaca_end_date']} </h4>
                    <p class='card-text font-weight-bold'>{$i}. {$data2['mb_name']} {$data2['vaca_name']} (담당자 코맨트 : {$data2['manager_comment']})</p>
                </div>
            </div>
        </div>
    </div>
    ";
}






//3.자기부서 상신완료 쿼리조회
$sql_3 = "
select a.*
     , b.mb_name 
     , b.mb_vaca_cnt 
     , a.manager_comment
     , concat(date_format(a.insert_date, '%Y년 %m월 %d일 '), 
     case dayofweek(a.insert_date)
         when 1 then '일요일'
         when 2 then '월요일'
         when 3 then '화요일'
         when 4 then '수요일'
         when 5 then '목요일'
         when 6 then '금요일'
         when 7 then '토요일'
     end,
     date_format(a.insert_date, ' %h시 %i분')) as insert_date2
  from {$g5['crm_vaca_mng']} a
  left join {$g5['member_table']} b on a.vaca_mb_no = b.mb_no  
 where $dept_condition
   and vaca_status = 2
   and vaca_end_date >= curdate()
 order by vaca_start_date asc
";
$list3 = sql_query($sql_3);
$count3 = sql_num_rows($list3);
$html3 = "";





for ($i = 1; $data3 = sql_fetch_array($list3); $i++) {
    $btn3 = "";
    
    //레별별 그리고 자신상신건은 삭제 가능하게 버튼 생성
    if($member['mb_level'] >= 6) {
        $btn3 = "
        <div class='btn-group btn-group-toggle' data-toggle='buttons'>
            <label class='btn btn-info'>
                <input type='radio' name='options' id='cancle'> 취소
            </label>
        </div>
        ";
    } else if($member['mb_no'] == $data3['vaca_mb_no']) {
        $btn3 = "
        <div class='btn-group btn-group-toggle' data-toggle='buttons'>
            <label class='btn btn-info'>
                <input type='radio' name='options' id='cancle'> 취소
            </label>
        </div>
        ";
    }

    $html3 .= "
    <div class='row'>
        <input type='hidden' name='vaca_idx' id='vaca_idx' value='{$data3['vaca_idx']}'>
        <div class='col-auto text-center flex-column d-none d-sm-flex'>
            <div class='row h-50'>
                <div class='col'>&nbsp;</div>
                <div class='col'>&nbsp;</div>
            </div>
            <h5 class='m-2'>
                <span class='badge badge-pill bg-light border'>&nbsp;</span>
            </h5>
            <div class='row h-50'>
                <div class='col border-right'>&nbsp;</div>
                <div class='col'>&nbsp;</div>
            </div>
        </div>
        <div class='col py-2'>
            <div class='card'>
                <div class='card-body'>
                    <div class='float-right text-muted'>[상신일] {$data3['insert_date2']} <span class='badge badge-primary'>상신완료</span></div>
                    <h4 class='card-title'>{$data3['vaca_start_date']} ~ {$data3['vaca_end_date']} </h4>
                    <p class='card-text font-weight-bold'>{$i}. {$data3['mb_name']} {$data3['vaca_name']} (담당자 코맨트:{$data3['manager_comment']}) <i class='fas fa-circle-notch fa-spin'></i></p>
                    {$btn3}
                </div>
            </div>
        </div>
    </div>
    ";
}



//4.자기부서 기타상신 쿼리조회
$sql_4 = "
select a.*
     , b.mb_name 
     , b.mb_vaca_cnt 
     , concat(date_format(a.insert_date, '%Y년 %m월 %d일 '), 
     case dayofweek(a.insert_date)
         when 1 then '일요일'
         when 2 then '월요일'
         when 3 then '화요일'
         when 4 then '수요일'
         when 5 then '목요일'
         when 6 then '금요일'
         when 7 then '토요일'
     end,
     date_format(a.insert_date, ' %h시 %i분')) as insert_date2
  from {$g5['crm_vaca_mng']} a
  left join {$g5['member_table']} b on a.vaca_mb_no = b.mb_no  
 where $dept_condition
   and vaca_code >= 4
   and manager_comment is null
   and vaca_end_date >= date_sub(curdate(), interval 3 month)
";
$list4 = sql_query($sql_4);
$count4 = sql_num_rows($list4);
$html4 = "";




//관리자 코멘트 입력가능하게 구현
if($member['mb_level'] >= 6) {
    for ($i = 1; $data4 = sql_fetch_array($list4); $i++) {
        $btn4 = "";
        
        
            $btn4 = "
            <div class='form-group row'>
                <div class='col-10'>
                    <input type='text' class='form-control w-100' id='admin_comment_{$data4['vaca_idx']}' placeholder='코멘트를 입력하세요'>
                </div>
                <div class='col-2'>
                    <button type='button' class='btn btn-primary w-100' onclick='submitComment({$data4['vaca_idx']})'>코멘트 등록</button>
                </div>
            </div>
            ";
        

        $html4 .= "
        <div class='row' id='vaca_row_{$data4['vaca_idx']}'>
            <input type='hidden' name='vaca_idx' id='vaca_idx' value='{$data4['vaca_idx']}'>
            <div class='col-auto text-center flex-column d-none d-sm-flex'>
                <div class='row h-50'>
                    <div class='col'>&nbsp;</div>
                    <div class='col'>&nbsp;</div>
                </div>
                <h5 class='m-2'>
                    <span class='badge badge-pill bg-light border'>&nbsp;</span>
                </h5>
                <div class='row h-50'>
                    <div class='col border-right'>&nbsp;</div>
                    <div class='col'>&nbsp;</div>
                </div>
            </div>
            <div class='col py-2'>
                <div class='card'>
                    <div class='card-body'>
                        <div class='float-right text-muted'>[상신일] {$data4['insert_date2']} <span class='badge badge-primary'>시스템 자동상신</span></div>
                        <h4 class='card-title'>{$data4['vaca_start_date']} ~ {$data4['vaca_end_date']}</h4>
                        <p class='card-text font-weight-bold'>{$i}. {$data4['mb_name']} {$data4['vaca_name']}</p>
                            {$btn4}
                    </div>
                </div>
            </div>
        </div>
        ";
    }
} 

?>

<link rel="stylesheet" href="<?php echo G5_THEME_URL ?>/plugins/daterangepicker/daterangepicker.css">
<link href='https://unpkg.com/fullcalendar@5.8.0/main.min.css' rel='stylesheet' />

<script src="<?php echo G5_THEME_URL ?>/plugins/moment/moment.min.js"></script>
<script src="<?php echo G5_THEME_URL ?>/plugins/daterangepicker/daterangepicker.js"></script>


<script src='https://unpkg.com/fullcalendar@5.8.0/main.min.js'></script>
<script src='https://unpkg.com/fullcalendar@5.8.0/locales-all.min.js'></script>

<style>
    .fc-day-sun { /* 일요일 */
        background-color: #ffcccc; /* 빨간색 계열 */
    }
    .fc-day-sat { /* 토요일 */
        background-color: #cceeff; /* 파란색 계열 */
    }
</style>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">근태관리</h3>
                    </div>

                    <div class="card-body">
                        <div class="dataTables_wrapper dt-bootstrap4">

                            <div class="d-flex flex-sm-row flex-column justify-content-sm-between">
                                <div class="d-flex justify-content-center mb-2 mb-sm-0">
                                    <div class="btn-group xs-100">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#vacaInsModal">근태 신청</button>
                                    </div>
                                    <div class="btn-group xs-100">
                                        <select id="cond_dept" name="cond_dept" class="form-control">
                                            <option value="">전체</option>
                                            <option value="3">1팀</option>
                                            <option value="4">2팀</option>
                                            <option value="5">3팀</option>
                                            <option value="6">4팀</option>
                                            <option value="7">본부</option>
                                            <option value="9">IT</option>
                                            <option value="11">키워드</option>
                                        </select>
                                    </div>
                                    <div class="btn-group xs-100">
                                        <select id="cond_mb_no" name="cond_mb_no" class="form-control">
                                            <option value="">전체</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <div class="btn-group xs-100">

                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <div id="calendar"></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="row">
            <div class="col-12">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title">현황판</h3>
                    </div>
                    <div class="card-body">
                        <div class="dataTables_wrapper dt-bootstrap4">

                            <div class="row">
                                <div class="col-sm-12">

                                    <div class="card card-warning card-tabs">
                                        <div class="card-header p-0 pt-1">
                                            <ul class="nav nav-tabs" id="tab-content-tab" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" id="waiting-approval-tab" data-toggle="pill" href="#waiting-approval" role="tab" aria-controls="waiting-approval" aria-selected="true">미상신</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="rejected-tab" data-toggle="pill" href="#rejected" role="tab" aria-controls="rejected" aria-selected="false">반려</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="complete_approval-tab" data-toggle="pill" href="#complete_approval" role="tab" aria-controls="complete_approval" aria-selected="false">상신완료</a>
                                                </li>
                                                <?php if($member['mb_level'] >= 6) { ?>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="complete_approval-tab" data-toggle="pill" href="#complete_memo" role="tab" aria-controls="complete_approval" aria-selected="false">미팅|기타</a>
                                                </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                        <div class="card-body">
                                            <div class="tab-content" id="tab-content-tabContent">
                                            
                                                <div class="tab-pane fade show active" id="waiting-approval" role="tabpanel" aria-labelledby="waiting-approval-tab">
                                                    <?php echo $html1 ?>
                                                </div>

                                                <div class="tab-pane fade" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
                                                    <?php echo $html2 ?>
                                                </div>
                                               
                                                <div class="tab-pane fade" id="complete_approval" role="tabpanel" aria-labelledby="complete_approval-tab">
                                                    <?php echo $html3 ?>
                                                </div>
                                                <?php if($member['mb_level'] >= 6) { ?>
                                                <div class="tab-pane fade" id="complete_memo" role="tabpanel" aria-labelledby="complete_memo-tab">
                                                    <?php echo $html4 ?>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>


<div class="modal fade" id="commentModal" tabindex="-1" role="dialog" aria-labelledby="commentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentModalLabel">코멘트 입력</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <textarea id="comment" class="form-control" placeholder="코멘트를 입력하세요"></textarea>
                <input type="hidden" id="modal_vaca_idx" value="">
                <input type="hidden" id="modal_buttonType" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                <button type="button" class="btn btn-primary" id="submitComment">확인</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="vacaInsModal" tabindex="-1" role="dialog" aria-labelledby="vacaInsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document"> <!-- 모달 크기를 가장 큰 사이즈로 변경 -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vacaInsModalLabel">근태 신청</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-6"> <!-- 기존 폼 절반 크기로 변경 -->
                        <form id="modal_form" name="modal_form" action="./hr_work_list_update" method="post">
                            <input type="hidden" id="vaca_mb_no" name="vaca_mb_no" value="">
                            <input type="hidden" id="vaca_mb_name" name="vaca_mb_name" value="">
                            <div class="form-group">
                                <label for="name">이름:</label>
                                <input type="text" class="form-control" id="vaca_mb_name" value="" disabled readonly>
                            </div>
                            <div class="row">
                                <div class="col-4 form-group">
                                    <label for="total_vaca_cnt">총연차일수:</label>
                                    <input type="text" class="form-control" id="total_vaca_cnt" value="" disabled readonly>
                                </div>
                                <div class="col-4 form-group">
                                    <label for="used_vaca">연차사용일수:</label>
                                    <input type="text" class="form-control" id="used_vaca" name="used_vaca" value="0" readonly>
                                </div>
                                <div class="col-4 form-group">
                                    <label for="remain_vaca">남은연차일수:</label>
                                    <input type="text" class="form-control" id="remain_vaca" name="remain_vaca" value="0" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="vaca_code">근태구분:</label>
                                <select class="form-control" id="vaca_code" name="vaca_code">
                                    <option value="1:연차">1. 연차(-1)</option>
                                    <option value="2:오전반차">2. 오전반차(-0.5)</option>
                                    <option value="3:오후반차">3. 오후반차(-0.5)</option>
                                    <option value="4:공가">4. 공가(0)</option>
                                    <option value="5:미팅">5. 미팅(0)</option>
                                    <option value="6:병가">6. 병가(-1)</option>
                                    <option value="7:경조사">7. 경조사(0)</option>
                                    <option value="8:기타">8. 기타[특별,여름등](0)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="date">날짜:</label>
                                <input type="text" class="form-control" id="vaca_date" name="vaca_date" required>
                            </div>
                            <div class="form-group">
                                <label for="name">사유:</label>
                                <input type="text" class="form-control" id="vaca_comment" name="vaca_comment" placeholder="(ex)리프레시 제주도 여행 || (ex) A고객사 서울역 미팅" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                                <button type="submit" class="btn btn-primary" id="btn_approval" name="act_button" value="근태상신">근태상신</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-lg-6"> <!-- 새로운 테이블을 위한 절반 -->
                        <table class="table table-sm table-dark">
                            <thead>
                                <tr>
                                    <th scope="col">No</th>
                                    <th scope="col">연차구분</th>
                                    <th scope="col">상태</th>
                                    <th scope="col">사용일자</th>
                                    <th scope="col">누계</th>
                                </tr>
                            </thead>
                            <tbody id="dynamic-tbody">
                    
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>


<div class="modal fade" id="vacaListModal" tabindex="-1" role="dialog" aria-labelledby="vacaListModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-conf_statLabel">월차 사용내역</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table id="vacaTable" class="table table-sm table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>no</th>
                            <th>이름</th>
                            <th>휴가구분</th>
                            <th>근태일시</th>
                            <th>사유</th>
                            <th>담당자 코멘트</th>
                            <th>현황</th>
                            <th>cnt</th>
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



<script>

var vacaTable;

$(function() {
    //Date picker
    $('#vaca_date').daterangepicker({
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
        minDate: new Date() // 오늘 날짜부터 선택 가능
    }); 

    //직원 목록 가져오는 MODAL 기본 셋팅해둠
    vacaTable = $('#vacaTable').DataTable({
        "autoWidth": false, // 열 너비 자동 조절 비활성화
        "info": false,
        "details": true,
        "lengthChange": false,
        "order": [] ,
        "columns": [
                { "width": "3%" },
                { "width": "5%" },
                { "width": "10%" },
                { "width": "15%" },
                { "width": "15%" },
                { "width": "15%" },
                { "width": "7%" },
                { "width": "3%" }
            ]
    });
    
    
    $("#vaca_date").on("change.datetimepicker", function (e) {
        // 선택된 날짜 범위를 시작 날짜와 종료 날짜로 분리
        var dateRange = this.value.split(' ~ ');
        var startDate = new Date(dateRange[0]);
        var endDate = new Date(dateRange[1]);

        // 주말 여부를 확인하는 함수
        function isWeekend(date) {
            var dayOfWeek = date.getDay();
            return dayOfWeek === 0 || dayOfWeek === 6; // 0: 일요일, 6: 토요일
        }

        // 시작 날짜 또는 종료 날짜가 주말인 경우 알림 표시
        if (isWeekend(startDate) || isWeekend(endDate)) {
            alert('주말(토요일 또는 일요일)은 선택할 수 없습니다.');
            this.value = "";
            $('#used_vaca').val("0");
            return false;
        }

        //calc_vaca();
    });


    




    $('.btn-group-toggle input[type="radio"]').on('click', function() {
        var vaca_idx = $(this).closest('.row').find('input[name="vaca_idx"]').val();
        var buttonType = '';
        
        if (this.id === 'confirm') {
            buttonType = '2'; 
            modalTitle = '상신 코멘트';
            vaca_comment = $(this).closest('.card-body').find('.vaca_comment').text(); 
        } else if (this.id === 'reject') {
            buttonType = '3'; 
            modalTitle = '반려 코멘트';
        } else if (this.id === 'cancle') {
            buttonType = '9'; 
            modalTitle = '취소 코멘트';
        }

        var employeeName = $(this).closest('.card-body').find('.card-text').text().trim().split(' ')[1];
        if (buttonType === '2') {
            $('#comment').attr('placeholder', '임직원 ' + employeeName + ' 근태코멘트를 입력해주세요: ' + vaca_comment);
        } else {
            $('#comment').attr('placeholder', '임직원 ' + employeeName + ' 근태코멘트를 입력해주세요');
        }
        $('#commentModalLabel').text(modalTitle);
        
        // 모달에  설정
        $('#modal_vaca_idx').val(vaca_idx);
        $('#modal_buttonType').val(buttonType);

        // 모달 열기
        $('#commentModal').modal('show');
    });

    // 모달의 확인 버튼 클릭 시 AJAX 호출
    $('#submitComment').on('click', function() {
        var vaca_idx = $('#modal_vaca_idx').val();
        var buttonType = $('#modal_buttonType').val();
        var comment = $('#comment').val();
        
        if (comment.trim() === "") {
            alert("코멘트를 입력해 주세요.");
            return;
        }
        
        var act = "upd_vaca_emp";

        $.ajax({
            type: "post",
            data: {
                act: act,
                vaca_idx: vaca_idx,
                buttonType: buttonType,
                comment: comment 
            },
            url: "hr_ajax",
            dataType: "json",
            success:function(result) {
                if (result == "ok") {
                    location.reload();
                } else {
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                }
            },
            error: function(xhr) {
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
            }
        });
        $('#commentModal').modal('hide');
    });








    

    // $('.btn-group-toggle input[type="radio"]').on('click', function() {
    //     var act = "upd_vaca_emp";
    //     var vaca_idx = $(this).closest('.row').find('input[name="vaca_idx"]').val();
    //     var buttonType = '';
    //     if (this.id === 'confirm') {
    //         buttonType = '2';
    //     } else if (this.id === 'reject') {
    //         buttonType = '3';
    //     } else if (this.id === 'cancle') {
    //         buttonType = '9';
    //     }
    //     // alert('vaca_idx: ' + vaca_idx + '\\nButton Clicked: ' + buttonType);
    //     $.ajax({
    //         type: "post",
    //         data: {
    //             act: act
    //           , vaca_idx: vaca_idx
    //           , buttonType: buttonType
    //         },
    //         url: "hr_ajax",
    //         dataType: "json",
    //         success:function(result) {
    //             if(result=="ok") {
    //                 location.reload();
    //             } else {
    //                 alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
    //                 return;
    //             }
    //         },
    //         error: function(xhr) {
    //             alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
    //             return;
    //         }
    //     });
    // });

});


document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'ko',
        events: function(fetchInfo, successCallback, failureCallback) {
            // AJAX 호출을 사용하여 이벤트 로드
            loadEvents(fetchInfo.startStr, fetchInfo.endStr, successCallback);
        },
        datesSet: function(dateInfo) {
            // 캘린더 뷰가 변경될 때 새로운 범위의 데이터 로드
            loadEvents(dateInfo.startStr, dateInfo.endStr, function(events) {
                calendar.removeAllEvents();
                events.forEach(function(event) {
                    calendar.addEvent(event);
                });
            });
        },
        eventClick: function(info) {

            var vaca_mb_deptno = info. event._def.extendedProps.vaca_mb_deptno;
            var vaca_mb_no = info. event._def.extendedProps.vaca_mb_no;
            var vaca_code = info. event._def.extendedProps.vaca_code;
            var myDept = '<?php echo $member['mb_deptno'] ?>';

            var mb_level = <?php echo $member['mb_level'] ?>;

            if(mb_level >= 6) {
                $('#vacaListModal').data('eventData', info.event);
                $('#vacaListModal').modal('show');
            } else {

                if(myDept != vaca_mb_deptno) {
                    if( (myDept == "6" || myDept == "7") && (vaca_mb_deptno == "6" || vaca_mb_deptno == "7") ) {
                        $('#vacaListModal').data('eventData', info.event);
                        $('#vacaListModal').modal('show');
                    } else {
                        return false;
                    }
                } else {
                    $('#vacaListModal').data('eventData', info.event);
                    $('#vacaListModal').modal('show');
                }
            }
        }
    });

    calendar.render();


    $("#cond_dept").on("change", function() {

        $('#cond_mb_no').val("");

        calendar.removeAllEvents(); // 기존 이벤트를 모두 지웁니다.
        calendar.refetchEvents(); // 이벤트를 다시 불러옵니다.

        var curr = $(this).val();
        var act = "chg_dept";
        var dept = $('#cond_dept').val(); 

        var target = $('#cond_mb_no');
        target.empty();
        
        $.ajax({
            type: "post",
            data: {
                act: act,
                dept:dept
            },
            url: "hr_ajax",
            dataType: "json",
            success: function(result) {
                //successCallback(result);

                target.append(result);
            },
            error: function(xhr) {
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
            }
        });
    });


    $("#cond_mb_no").on("change", function() {
        calendar.removeAllEvents(); // 기존 이벤트를 모두 지웁니다.
        calendar.refetchEvents(); // 이벤트를 다시 불러옵니다.

        var mb_no = $(this).val();
        var dept = $('#cond_dept').val(); 
        
        $("#cond_dept").val(dept).prop("selected", true);
        $("#cond_mb_no").val(mb_no).prop("selected", true);
        
    });

    // Reset modal fields when the modal is opened
    $('#vacaListModal').on('show.bs.modal', function (e) {
        var eventData = $(this).data('eventData');
        var vaca_mb_no = eventData._def.extendedProps.vaca_mb_no;
        var act = "one_emp_vaca_list";

        $.ajax({
            type: "post",
            data: {
                act: act,
                vaca_mb_no: vaca_mb_no
            },
            url: "hr_ajax",
            dataType: "json",
            success:function(result) {
                vacaTable.clear();
                for (var i = 0; i < result.length; i++) {
                    vacaTable.row.add([
                        parseInt(i+1),
                        result[i].vaca_mb_name,
                        result[i].vaca_name,
                        result[i].vaca_date,
                        result[i].vaca_comment,
                        result[i].manager_comment,
                        result[i].vaca_status,
                        result[i].year_cnt,
                    ]).draw(false);
                }
            },
            error: function(xhr) {
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                return;
            }
        });


    });

    $('#vacaInsModal').on('show.bs.modal', function (e) {
        $('#vacaInsModal #vaca_mb_name').val('');
        $('#vacaInsModal #vaca_gubun').val('1');
        $('#vacaInsModal #total_vaca_cnt').val('0');
        $('#vacaInsModal #vaca_date').val('');

        var empName = '<?php echo $member['mb_name'] ?>';
        $('#vacaInsModal #vaca_mb_name').val(empName);
        var act = "get_vaca_emp";

        $("#dynamic-tbody").empty();

        $.ajax({
            type: "post",
            data: {
                act: act
            },
            url: "hr_ajax",
            dataType: "json",
            success:function(result) {

                if(result == "fail") {
                    $('#vacaInsModal #total_vaca_cnt').val("0");
                } else {
                    //$('#vacaInsModal #total_vaca_cnt').val(result);

                    $('#vacaInsModal #total_vaca_cnt').val(result[0].total_cnt);
                    $('#vacaInsModal #used_vaca').val(result[0].used_cnt);
                    $('#vacaInsModal #remain_vaca').val(result[0].remain_cnt);

                    $("#dynamic-tbody").append(result[1]);
                }
            },
            error: function(xhr) {
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                return;
            }
        });
    });

});

$("#vaca_code").on("change", function() {
    var selectedOption = $(this).val();
    //calc_vaca();
});


function btn_cancle() {
    var name = $('#vaca_mb_name').val();
    var vacationType = $('#vaca_code').val();
    var date = $('#vaca_date').val();
    alert('Name: ' + name + '\nVacation Type: ' + vacationType + '\nDate: ' + date);
}

function calc_vaca(){

    $('#used_vaca').val(0);

    vaca_code1 = $('#vaca_code').val();
    vaca_code = vaca_code1.split(":")[0];
    vaca_date = $('#vaca_date').val();

    var dateRange = vaca_date.split(' ~ ');
    var startDate = new Date(dateRange[0]);
    var endDate = new Date(dateRange[1]);

    var count = 0; // 주말을 제외한 휴가 일수 계산을 위한 변수
    for (var d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
        if (d.getDay() !== 0 && d.getDay() !== 6) { // 일요일(0)과 토요일(6) 제외
            count++;
        }
    }
    
    //연차시
    if(vaca_code == "1" || vaca_code == "6") {
        
    }

    //반차시
    else if(vaca_code == "2" || vaca_code == "3") {
        if(count >= 2) {
            alert("반차 선택시 연박선택 불가");
            $('#vaca_date').val("");
            return false;
        } else {
            if(vaca_date != ""){
                count = count - 0.5;
            }
        }
    }
    else {
        count = 0;
    }

    //alert(vaca_code + "휴가 일수 (주말 제외): " + count);
    $('#used_vaca').val(count);
}


function loadEvents(startStr, endStr, successCallback) {
    var start = formatDate(new Date(startStr));
    var end = formatDate(new Date(endStr));
    var act = "get_vaca_list";

    var dept = $('#cond_dept').val();
    var mb_no = $('#cond_mb_no').val();

    $.ajax({
        type: "post",
        data: {
            act: act,
            start: start,
            end: end,
            dept:dept,
            mb_no:mb_no
        },
        url: "hr_ajax",
        dataType: "json",
        success: function(result) {
            successCallback(result);
            $("#cond_dept").val(dept).prop("selected", true);
        },
        error: function(xhr) {
            alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
        }
    });
}

function formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1), // getMonth()는 0부터 시작하므로 1을 더해줍니다.
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) 
        month = '0' + month;
    if (day.length < 2) 
        day = '0' + day;

    return [year, month, day].join('-');
}


function submitComment(vaca_idx) {
    var comment = $('#admin_comment_' + vaca_idx).val();
    if (!comment) {
        alert('코멘트를 입력해 주세요.');
        return false;
    } 
    //upd_vaca_emp
    var act = "etc_manager_comment";
    $.ajax({
        type: "post",
        data: {
            act: act,
            vaca_idx: vaca_idx,
            comment: comment 
        },
        url: "hr_ajax",
        dataType: "json",
        success:function(result) {
            alert("코맨트 등록완료");
            $('#vaca_row_' + vaca_idx).remove();
        },
        error: function(xhr) {
            alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
        }
    });
    

    

}


</script>


<?php
include_once(G5_PATH . '/tail.php');
?>


<!-- 

INSERT INTO gonplan.{$g5['crm_vaca_mng']} (vaca_mb_deptno,vaca_mb_no,vaca_mb_name,vaca_code,vaca_name,vaca_status,vaca_comment,vaca_start_date,vaca_end_date,used_vaca,insert_date,update_date,insert_user,update_user,insert_user_name,update_user_name) VALUES
	 (4,25,'김지영','3','오후반차','2','시스템입력','2024-01-02','2024-01-02',0.5,'2024-01-01 00:00:00.000','2024-01-01 00:00:00.000','system','system','관리자','관리자'),
	 (4,25,'김지영','1','월차','2','시스템입력','2024-01-03','2024-01-03',1.0,'2024-01-01 00:00:00.000','2024-01-01 00:00:00.000','system','system','관리자','관리자'),
	 (5,4,'박지헌','3','오후반차','2','시스템입력','2024-01-11','2024-01-11',0.5,'2024-01-01 00:00:00.000','2024-01-01 00:00:00.000','system','system','관리자','관리자'),
	 (5,4,'박지헌','1','월차','2','시스템입력','2024-01-12','2024-01-12',1.0,'2024-01-01 00:00:00.000','2024-01-01 00:00:00.000','system','system','관리자','관리자'),
	 (5,23,'오지연','3','오후반차','2','시스템입력','2024-01-18','2024-01-18',0.5,'2024-01-01 00:00:00.000','2024-01-01 00:00:00.000','system','system','관리자','관리자'),
	 (5,23,'오지연','1','월차','2','시스템입력','2024-01-19','2024-01-19',1.0,'2024-01-01 00:00:00.000','2024-01-01 00:00:00.000','system','system','관리자','관리자'),
	 (6,34,'권혜준','1','월차','2','시스템입력','2024-01-05','2024-01-05',1.0,'2024-01-01 00:00:00.000','2024-01-01 00:00:00.000','system','system','관리자','관리자'),
	 (6,34,'권혜준','7','기타','2','여름휴가','2024-01-08','2024-01-08',0.0,'2024-01-01 00:00:00.000','2024-01-01 00:00:00.000','system','system','관리자','관리자'),
	 (6,34,'권혜준','7','기타','2','여름휴가','2024-01-09','2024-01-09',0.0,'2024-01-01 00:00:00.000','2024-01-01 00:00:00.000','system','system','관리자','관리자'),
	 (6,283,'최호준','1','월차','2','시스템입력','2024-01-17','2024-01-17',1.0,'2024-01-01 00:00:00.000','2024-01-01 00:00:00.000','system','system','관리자','관리자');

-- gonplan.{$g5['crm_vaca_mng']} definition

CREATE TABLE `{$g5['crm_vaca_mng']}` (
  `vaca_idx` int(11) NOT NULL AUTO_INCREMENT,
  `vaca_mb_deptno` int(11) DEFAULT NULL,
  `vaca_mb_no` int(11) DEFAULT NULL,
  `vaca_mb_name` varchar(20) DEFAULT NULL,
  `vaca_code` char(1) DEFAULT NULL COMMENT '1:연차 2:오전반차 3:오후반차 4:공가 5:미팅 6:병가 7:경조사 8:기타',
  `vaca_name` varchar(20) DEFAULT NULL,
  `vaca_status` char(1) DEFAULT NULL COMMENT '1:상신대기 2:상신완료 3: 상신반려',
  `vaca_comment` varchar(100) DEFAULT NULL COMMENT '휴가사유',
  `vaca_start_date` date DEFAULT NULL,
  `vaca_end_date` date DEFAULT NULL,
  `used_vaca` float DEFAULT NULL,
  `insert_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `insert_user` varchar(30) DEFAULT NULL,
  `update_user` varchar(30) DEFAULT NULL,
  `insert_user_name` varchar(30) DEFAULT NULL,
  `update_user_name` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`vaca_idx`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-->
