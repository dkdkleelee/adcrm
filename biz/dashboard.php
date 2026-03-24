<?php
require_once '../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');

$g5['title'] = "DASH BOARD";
include_once(G5_PATH . '/head.php');



$isAdmin = ($member['mb_level'] == "6");
$colClass = $isAdmin ? "col-md-3" : "col-md-4";
$whatCurrDept = $member['mb_deptno'];
$whereCont1 = "";
$deptCondition = ($whatCurrDept == 6 || $whatCurrDept == 7) ? "vaca_mb_deptno IN (6, 7)" : "vaca_mb_deptno = $whatCurrDept";

if($whatCurrDept == "3" || $whatCurrDept == "4" || $whatCurrDept == "5" || $whatCurrDept == "6" || $whatCurrDept == "7" || $whatCurrDept == "9") {
    $whereCont1 .= "and vaca_mb_deptno not in (11)";
} else if($whatCurrDept == "11") {
    $whereCont1 .= "and vaca_mb_deptno in (11, 9)";
}

// 오늘 휴가자
$todayVacations = "";
$sql = "
    SELECT vaca_mb_name, vaca_name
    FROM {$g5['crm_vaca_mng']}
    WHERE CURDATE() BETWEEN vaca_start_date AND vaca_end_date
      AND vaca_status = 2
      {$whereCont1}
";
$result = sql_query($sql);
$i = 1;
while ($row = sql_fetch_array($result)) {
    $name = htmlspecialchars($row['vaca_mb_name']);
    $type = htmlspecialchars($row['vaca_name']);
    $date = date("Y-m-d"); // 오늘 날짜
    $todayVacations .= "<li class='list-group-item'>$i $name $date $type</li>";
    $i++;
}

// 내일 휴가자
$tomorrowVacations = "";
$sqlTomorrow = "
    SELECT vaca_mb_name, vaca_name
    FROM {$g5['crm_vaca_mng']}
    WHERE CURDATE() + INTERVAL 1 DAY BETWEEN vaca_start_date AND vaca_end_date
      AND vaca_status = 2
      {$whereCont1}
";
$resultTomorrow = sql_query($sqlTomorrow);

$j = 1;
while ($row = sql_fetch_array($resultTomorrow)) {
    $name = htmlspecialchars($row['vaca_mb_name']);
    $type = htmlspecialchars($row['vaca_name']);
    $date = date("Y-m-d", strtotime("+1 day")); 
    $tomorrowVacations .= "<li class='list-group-item'>$j $name $date $type</li>";
    $j++;
}

// 관리자인 경우 미상신 쿼리 데이터 추가
$unapprovedVacations = "";
if ($isAdmin) {
    $sqlUnapproved = "
        SELECT vaca_idx, vaca_mb_name, vaca_name, vaca_start_date
        FROM {$g5['crm_vaca_mng']}
        WHERE vaca_status = '1'
          AND vaca_start_date > CURDATE()
          AND vaca_code IN (1, 2, 3)
          AND {$deptCondition}
    ";
    $resultUnapproved = sql_query($sqlUnapproved);

    $k = 1;
    while ($row = sql_fetch_array($resultUnapproved)) {
        $vaca_idx = htmlspecialchars($row['vaca_idx']);
        $name = htmlspecialchars($row['vaca_mb_name']);
        $type = htmlspecialchars($row['vaca_name']);
        $startDate = htmlspecialchars($row['vaca_start_date']);
        //$unapprovedVacations .= "<li class='list-group-item'>$k $name $startDate $type <button class='btn btn-xs btn-danger btn-sm float-right' onclick='approveVacation($k)'>승인</button></li>";
        $unapprovedVacations .= "<li class='list-group-item' id='vacation-$vaca_idx'>$name $startDate $type <button class='btn btn-xs btn-danger btn-sm float-right' onclick='approveVacation($vaca_idx)'>승인</button></li>";
        $k++;
    }
}

$level_cond = "";

if($member['mb_level'] == "4") {
    $level_cond = "AND land_empno = {$member['mb_no']}";
}

$sql = "
    SELECT land_ptn_idx, 
           b.ptn_nm, 
           insert_date2, 
           COUNT(*) AS record_count
    FROM {$g5['crm_landing']} a
    LEFT JOIN {$g5['crm_partner']} b ON a.land_ptn_idx = b.ptn_idx 
    WHERE insert_date2 >= CURDATE() - INTERVAL 7 DAY
      AND land_deptno = {$member['mb_deptno']}
      {$level_cond}
      AND land_ptn_idx IS NOT NULL
      AND a.use_yn = 'Y'
    GROUP BY land_ptn_idx, insert_date2
    ORDER BY ptn_nm asc, insert_date2 asc;
";
$result = sql_query($sql);

$chartData = [];
while ($row = sql_fetch_array($result)) {
    $chartData[] = $row;
}

// Chart.js
$partners = [];
$data = [];
$dates = [];

foreach ($chartData as $entry) {
    $partners[$entry['land_ptn_idx']] = $entry['ptn_nm'];
    $dates[$entry['insert_date2']] = $entry['insert_date2'];
    $data[$entry['insert_date2']][$entry['land_ptn_idx']] = $entry['record_count'];
}

$partners = array_unique($partners);
$dates = array_unique($dates);

$labels = array_values($dates);
$datasets = [];

foreach ($partners as $id => $name) {
    $dataset = [
        'label' => $name,
        'data' => [],
        'borderColor' => sprintf('rgba(%d, %d, %d, 1)', rand(0, 255), rand(0, 255), rand(0, 255)),
        'fill' => false,
        'lineTension' => 0,
        'hidden' => true  // 기본적으로 비활성화
    ];
    foreach ($labels as $label) {
        $dataset['data'][] = isset($data[$label][$id]) ? $data[$label][$id] : 0;
    }
    $datasets[] = $dataset;
}

$jsonLabels = json_encode($labels);
$jsonDatasets = json_encode($datasets);



$sql = "
SELECT 
    b.ptn_idx,
    b.ptn_deptno,
    b.ptn_nm,
    b.ptn_db_amount,
    b.ptn_as_amt,
    COALESCE(COUNT(a.land_idx), 0) AS real_db_cnt, 
    COALESCE(((COUNT(a.land_idx) - b.ptn_as_amt) / b.ptn_db_amount) * 100, 0) AS target_percent, 
    b.ptn_ad_gubun,
    b.ptn_budget,
    b.ptn_cont_ref,
    b.ptn_startday,
    b.ptn_endday,
    b.ptn_memo
FROM {$g5['crm_partner']} b
LEFT JOIN {$g5['crm_landing']} a ON a.land_ptn_idx = b.ptn_idx 
WHERE b.ptn_show_dash = 'Y'
AND a.use_yn = 'Y' 
AND a.land_deptno = {$member['mb_deptno']}
AND b.ptn_deptno = {$member['mb_deptno']}
{$level_cond}
GROUP BY b.ptn_idx, b.ptn_nm
HAVING COUNT(a.land_idx) > 0
ORDER BY target_percent DESC;
";
$result = sql_query($sql);
$currentDate = new DateTime();


// 오늘 회의실
$todayMeetings = "";
$sqlMeetings = "
    SELECT a.meet_startday, a.meet_endday, a.meet_reason, b.mb_name
    FROM {$g5['crm_meet_mng']} a
    LEFT JOIN {$g5['member_table']} b ON a.meet_mb_no = b.mb_no
    WHERE a.meet_startday >= CURDATE() 
    AND a.meet_startday < CURDATE() + INTERVAL 1 DAY
    ORDER BY a.meet_startday 
";
$resultMeetings = sql_query($sqlMeetings);

$m = 1;
while ($row = sql_fetch_array($resultMeetings)) {
    $startDay = htmlspecialchars(date("H:i", strtotime($row['meet_startday'])));
    $endDay = htmlspecialchars(date("H:i", strtotime($row['meet_endday'])));
    $reason = htmlspecialchars($row['meet_reason']);
    $name = htmlspecialchars($row['mb_name']);
    $todayMeetings .= "<li class='list-group-item'>$m $name $reason ($startDay ~ $endDay)</li>";
    $m++;
}


// 최근 일주일 동안 데이터가 있는 고객사 추출
$sqlDistinctPartners = "
select
    a.ptn_idx,
    a.ptn_nm,
    sum(case when b.insert_date2 = curdate() then 1 else 0 end) as `today`,
    sum(case when b.insert_date2 = curdate() - interval 1 day then 1 else 0 end) as `day_1`,
    sum(case when b.insert_date2 = curdate() - interval 2 day then 1 else 0 end) as `day_2`,
    sum(case when b.insert_date2 = curdate() - interval 3 day then 1 else 0 end) as `day_3`,
    sum(case when b.insert_date2 = curdate() - interval 4 day then 1 else 0 end) as `day_4`,
    sum(case when b.insert_date2 = curdate() - interval 5 day then 1 else 0 end) as `day_5`,
    sum(case when b.insert_date2 = curdate() - interval 6 day then 1 else 0 end) as `day_6`
from {$g5['crm_partner']} a
left join {$g5['crm_landing']} b on b.land_ptn_idx = a.ptn_idx
and b.use_yn = 'Y'
and b.insert_date2 >= curdate() - interval 6 day
where b.land_deptno = {$member['mb_deptno']}
{$level_cond}
group by a.ptn_idx 
order by a.ptn_nm;
";
$resultDistinctPartners = sql_query($sqlDistinctPartners);
$total_today = 0;
$total_day_1 = 0;
$total_day_2 = 0;
$total_day_3 = 0;
$total_day_4 = 0;
$total_day_5 = 0;
$total_day_6 = 0;
$totals = array_fill(0, 7, 0);
?>

<link rel="stylesheet" href="<?php echo G5_THEME_URL ?>/plugins/daterangepicker/daterangepicker.css">
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.8.0/main.min.css' rel='stylesheet' />

<script src="<?php echo G5_THEME_URL ?>/plugins/moment/moment.min.js"></script>
<script src="<?php echo G5_THEME_URL ?>/plugins/daterangepicker/daterangepicker.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.8.0/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.8.0/locales-all.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.bootstrap-datetimepicker-widget.dropdown-menu {
    min-width: 24rem; /* 너비 조정 */
}

@keyframes blink {
    0% { opacity: 1; }
    50% { opacity: 0; }
    100% { opacity: 1; }
}

.blink {
    color: red;
    animation: blink 1s step-start infinite;
}

.tooltip-inner {
    max-width: 500px; /* 툴팁 최대 너비 조정 */
    font-size: 16px; /* 툴팁 폰트 크기 조정 */
    white-space: pre-line; /* 줄 바꿈 지원 */
}
.tooltip.bs-tooltip-top .arrow::before, 
.tooltip.bs-tooltip-bottom .arrow::before, 
.tooltip.bs-tooltip-left .arrow::before, 
.tooltip.bs-tooltip-right .arrow::before {
    border-width: 10px; /* 화살표 크기 조정 */
}

</style>

<section class="content">
    <div class="container-fluid">

        <div class="row">
            <div class="<?php echo $colClass; ?>">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">오늘의 근태 <i class="fas fa-umbrella-beach"></i> </h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php echo $todayVacations; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="<?php echo $colClass; ?>">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">내일의 근태 <i class="fas fa-thumbs-up"></i></h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php echo $tomorrowVacations; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="<?php echo $colClass; ?>">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">금일 회의실 <i class="fas fa-meeting"></i> </h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php echo $todayMeetings; ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <?php if ($isAdmin): ?>
            <div class="col-md-3">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">미승인 연차 <i class="fas fa-pause"></i></h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php echo $unapprovedVacations; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>


        <div class="row">
            <div class="col-12">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">고객사 분석판</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>고객사명</th>
                                    <th>목표db</th>
                                    <th>실제db</th>
                                    <th>AS수량</th>
                                    <th>남은수량</th>
                                    <th id="percentHeader" data-toggle="tooltip" data-html="true" title="퍼센트가 80% 이상 파랑">퍼센트</th>
                                    <th>구분</th>
                                    <th>예산</th>
                                    <th>비고</th>
                                    <th style="width: 100px;">시작일</th>
                                    <th style="width: 180px;">종료일</th>
                                    <th style="width: 130px;">관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = sql_fetch_array($result)) { 
                                    $percent = $row['target_percent'];
                                    if ($percent > 80) {
                                        $class = 'table-primary';
                                    } else {
                                        $class = '';
                                    }

                                    $remain = $row['ptn_db_amount'] - ($row['real_db_cnt'] - $row['ptn_as_amt'] );
                                    if ($remain <= 10) {
                                        $class1 = 'table-danger';
                                    } else {
                                        $class1 = '';
                                    }

                                    if($row['ptn_endday'] != NULL && $row['ptn_endday'] != "0000-00-00") {
                                        $endDate = new DateTime($row['ptn_endday']);
                                        $interval = $currentDate->diff($endDate);
                                        $daysDiff = $interval->format('%r%a'); // 양수 및 음수 포맷
                                        $dDay = ($daysDiff >= 0) ? "D-{$daysDiff}" : "D+" . abs($daysDiff);
                                    } else {
                                        $row['ptn_endday'] = "";
                                        $dDay = ""; 
                                    }
                                    
                                    $blinkClass = ($interval->days <= 7) ? 'blink' : '';

                                    $adGubunSuffix = '';
                                    if ($row['ptn_ad_gubun'] == 'CPA') {
                                        $adGubunSuffix = '(단가)';
                                    } elseif ($row['ptn_ad_gubun'] == 'CPC') {
                                        $adGubunSuffix = '(예산)';
                                    } elseif ($row['ptn_ad_gubun'] == 'CPP') {
                                        $adGubunSuffix = '(기간)';
                                    }

                                    $memoBtnClass = !empty($row['ptn_memo']) ? 'btn-info' : 'btn-secondary';
                                    
                                ?>
                                    <tr id="row-<?php echo $row['ptn_idx']; ?>">
                                        <td><?php echo htmlspecialchars($row['ptn_nm']); ?></td>
                                        <td><?php echo htmlspecialchars( number_format($row['ptn_db_amount']) ); ?></td>
                                        <td><?php echo htmlspecialchars( number_format($row['real_db_cnt']) ); ?></td>
                                        <td>-<?php echo htmlspecialchars( number_format($row['ptn_as_amt']) ); ?></td>

                                        <td class="<?php echo $class1; ?>"><?php echo htmlspecialchars( number_format( $remain ) ); ?></td>

                                        <td class="<?php echo $class; ?>">
                                            <?php 
                                            $targetPercent = $row['target_percent'];
                                            echo ($targetPercent == 0) ? '0.00%' : number_format(htmlspecialchars($targetPercent), 2) . '%';
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['ptn_ad_gubun']); ?></td>
                                        <td><?php echo htmlspecialchars( number_format($row['ptn_budget']) ); ?></td>
                                        <td><?php echo htmlspecialchars( $adGubunSuffix. ' ' . $row['ptn_cont_ref']); ?></td>
                                        <td><?php echo htmlspecialchars($row['ptn_startday']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($row['ptn_endday']); ?> 
                                            (<span class="<?php echo $blinkClass; ?>"><?php echo $dDay; ?></span>)
                                        </td>
                                        <td>
                                            <a href="<?php echo G5_BIZ_URL; ?>/partner/partner_form?w=u&ptn_idx=<?php echo $row['ptn_idx'].$qstr ?>" class="btn btn-xs btn-primary">수정</a>
                                            <button class="btn btn-xs btn-danger" onclick="deletePartner(<?php echo $row['ptn_idx']; ?>)">삭제</button>
                                            <button class="btn btn-xs <?php echo $memoBtnClass; ?>" data-ptn-idx="<?php echo $row['ptn_idx']; ?>" onclick="openMemoModal(<?php echo $row['ptn_idx']; ?>)">메모</button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>



        

        <div class="row"> 
            <div class="col-12"> 
                <div class="card card-danger card-outline"> 
                    <div class="card-header">
                        <h3 class="card-title">최근 정상DB수량</h3>
                    </div>
                    <div class="card-body">

                    <?php
                        $dates = array();
                        $dayNames = array();
                        for ($i = 0; $i <= 6; $i++) {
                            $date = date('Y-m-d', strtotime("-$i days"));
                            $dates[$i] = $date;
                            $dayOfWeek = date('w', strtotime($date));
                            switch ($dayOfWeek) {
                                case 0: $dayOfWeekStr = '일'; break;
                                case 1: $dayOfWeekStr = '월'; break;
                                case 2: $dayOfWeekStr = '화'; break;
                                case 3: $dayOfWeekStr = '수'; break;
                                case 4: $dayOfWeekStr = '목'; break;
                                case 5: $dayOfWeekStr = '금'; break;
                                case 6: $dayOfWeekStr = '토'; break;
                            }
                            $displayDate = ($i == 0) ? '오늘' : $dayOfWeekStr;
                            $dayNames[$i] = $date . " (" . $displayDate . ")";
                        }
                        $totals = array_fill(0, 7, 0);
                        ?>

                        <table id="tbl_recent_db" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>고객사명</th>
                                    <?php for ($i = 0; $i <= 6; $i++): ?>
                                        <th><?php echo $dayNames[$i]; ?></th>
                                    <?php endfor; ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while ($row = sql_fetch_array($resultDistinctPartners)) { ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo G5_BIZ_URL ?>/landing/land_list?sfl=ptn_idx&stx=<?php echo $row['ptn_idx']; ?>" target="_self">
                                            <?php echo htmlspecialchars($row['ptn_nm']); ?>
                                        </a>
                                        <i class="fas fa-chart-bar" onclick="showDetailCnt('<?php echo $row['ptn_idx']; ?>')" style="cursor: pointer;"></i>
                                    </td>
                                    <?php for ($i = 0; $i <= 6; $i++): ?>
                                        <?php
                                            $fieldName = ($i == 0) ? 'today' : 'day_'.$i;
                                            $count = isset($row[$fieldName]) ? $row[$fieldName] : 0;
                                            $totals[$i] += $count;
                                        ?>
                                        <td>
                                            <a href="<?php echo G5_BIZ_URL ?>/landing/land_list?stx=advanced&page=&advanced_ptn_idx=<?php echo $row['ptn_idx']; ?>&advanced_pg_uri=&advanced_from=<?php echo $dates[$i]; ?>&advanced_to=<?php echo $dates[$i]; ?>&advanced_db_status=" target="_self">
                                                <?php echo $count; ?>건
                                            </a>
                                            <?php if ($count > 0): ?>
                                                <i class="fas fa-table" style="cursor: pointer;" onclick="showTable('<?php echo $dates[$i]; ?>', '<?php echo $row['ptn_idx']; ?>', '<?php echo htmlspecialchars($row['ptn_nm']); ?>')"></i>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                                <!-- <tr id="detail_row_<?php echo $row['ptn_idx']; ?>" style="display: none;">
                                    <td colspan="8">
                                        <div id="detail_content_<?php echo $row['ptn_idx']; ?>"></div>
                                    </td>
                                </tr> -->

                            <?php } ?>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td><strong>합계</strong></td>
                                    <?php for ($i = 0; $i <= 6; $i++): ?>
                                        <td><strong><?php echo $totals[$i]; ?>건</strong></td>
                                    <?php endfor; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>




        <div class="row">
            <div class="col-12">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">DB차트</h3>
                        <button type="button" class="btn btn-xs btn-success float-right" onclick="toggleDatasets(true)">전체 활성화</button>
                        <button type="button" class="btn btn-xs btn-danger float-right mr-2" onclick="toggleDatasets(false)">전체 비활성화</button>
                    </div>
                    <div class="card-body">
                        <canvas id="combinedChart"></canvas>
                    </div>
                </div>
            </div>
        </div>


        
    </div>
</section>


<div class="modal fade" id="memoModal" tabindex="-1" role="dialog" aria-labelledby="memoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title" id="memoModalLabel">메모</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <textarea class="form-control" id="memoText" rows="12"></textarea>
                <input type="hidden" id="currentMstIdx" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" id="saveMemoButton">저장</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="detailModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
            </div>
        </div>
    </div>
</div>

        

<script>

let utm_mode = false;

$(document).ready(function() {

    $('[data-toggle="tooltip"]').tooltip();

    $('#tbl_recent_db').DataTable({
        paging: false,
        searching: true,
        ordering: true,
        info: false,
        autoWidth: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'csv',
                text: 'CSV 다운로드',
                className: 'btn btn-xs btn-success float-right',
                bom: true,
                charset: 'UTF-8',
                fieldBoundary: '"',
                fieldSeparator: ',',
                extension: '.csv',
                filename: 'exported-data',
                title: 'Exported Data'
            },
            {
                text: '정상 DB 이동',
                className: 'ml-1 btn btn-xs btn-info float-right',
                action: function ( e, dt, node, config ) {
                    window.location.href = '<?php echo G5_BIZ_URL?>/landing/land_list';
                }
            }
        ]
    });
});

let myChart;
let allHidden = true;

function showTable(date, ptn_idx, ptn_nm, utm_mode = false) {
    $.ajax({
        type: "post",
        data: {
            act: "detail_pg_uri_table",
            ptn_idx: ptn_idx,
            date : date,
            utm_mode: utm_mode
        },
        url: "dashboard_ajax",
        dataType: "json", 
        success:function(result) {

            $('#detailModalLabel').text(ptn_nm + ' ' + date + ' 상세건수');
            let totalCount = 0;

            if (result && result.length > 0) {
                let modalContent = '<table class="table table-bordered table-striped"><thead><tr><th>페이지 URI</th><th>담당자</th><th>수량</th></tr></thead><tbody>';
                result.forEach(function(row) {

                    totalCount += parseInt(row.land_count);

                    modalContent += '<tr>' +
                        '<td>' + row.pg_uri + '</td>' +
                        '<td>' + row.mb_emp_name + '</td>' +
                        '<td>' + row.land_count + '건</td>' +
                        '</tr>';
                });
                modalContent += '<tr><td colspan="2" style="text-align:right;"><strong>합계</strong></td><td><strong>' + totalCount + '건</strong></td></tr>';
                modalContent += '</tbody></table>';
                $('#modalBody').html(modalContent);

                // 기존 버튼 text제거
                let footer = $('.modal-footer');
                footer.find('.utm-toggle-btn').remove();

                let utmButton = $('<button>', {
                    type: "button",
                    class: "btn btn-primary utm-toggle-btn",
                    text: utm_mode ? "코드 조회" : "UTM 조회", 
                    click: function () {
                        showTable(date, ptn_idx, ptn_nm, !utm_mode);
                    }
                });
                footer.prepend(utmButton);
                $('#detailModal').modal('show');
            } else {
                alert('데이터가 없습니다.');
            }
        },
        error: function(xhr) {
            console.log(xhr.responseText);
            alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
            return;
        }
    });
}   

$('#memoModal').on('hidden.bs.modal', function () {
    utm_mode = false; 
});


function showDetailCnt(ptnIdx) {
    $.ajax({
        type: "post",
        data: {
            act: "detail_pg_uri_chart",
            ptn_idx: ptnIdx
        },
        url: "dashboard_ajax",
        dataType: "json", 
        success: function(result) {
            const groupedDataByDate = {};
            result.forEach(function(item) {
                const date = item.date;
                const pgUri = item.pg_uri;
                const count = parseInt(item.count, 10);
                
                if (!groupedDataByDate[date]) {
                    groupedDataByDate[date] = {};
                }
                if (!groupedDataByDate[date][pgUri]) {
                    groupedDataByDate[date][pgUri] = 0;
                }
                groupedDataByDate[date][pgUri] += count;
            });
            
            const modalHtml = `
            <div class="modal fade" id="chartModal" tabindex="-1" role="dialog" aria-labelledby="chartModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title" id="chartModalLabel">코드별 차트 <i class="fas fa-chart-bar"></i></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" id="chartContainer"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                        </div>
                    </div>
                </div>
            </div>`;
            
            if (!document.getElementById('chartModal')) {
                document.body.insertAdjacentHTML('beforeend', modalHtml);
            }
            
            // 모달 표시
            $('#chartModal').modal('show');
            const chartContainer = document.getElementById('chartContainer');
            chartContainer.innerHTML = ''; 
            
            Object.keys(groupedDataByDate).forEach(function(date, index) {
                const chartId = `pageUriChart-${index}`;
                const totalSum = Object.values(groupedDataByDate[date]).reduce((sum, value) => sum + value, 0);
                const chartHtml = `<div><h5>${date} (총 건수: ${totalSum})</h5><canvas id="${chartId}" width="400" height="200" style="margin-bottom: 20px;"></canvas></div>`;
                chartContainer.insertAdjacentHTML('beforeend', chartHtml);
                
                const sortedData = Object.entries(groupedDataByDate[date]).sort((a, b) => b[1] - a[1]);
                const labels = sortedData.map(item => item[0]);
                const data = sortedData.map(item => item[1]);
                const backgroundColors = labels.map((_, i) => 'rgba(' + (i * 50 % 255) + ', ' + (i * 100 % 255) + ', ' + (i * 150 % 255) + ', 0.6)');
                const borderColors = labels.map((_, i) => 'rgba(' + (i * 50 % 255) + ', ' + (i * 100 % 255) + ', ' + (i * 150 % 255) + ', 1)');
                
                const ctx = document.getElementById(chartId).getContext('2d');
                new Chart(ctx, {
                    type: 'bar', 
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: backgroundColors,
                            borderColor: borderColors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(tooltipItem) {
                                        return `${tooltipItem.label}: ${tooltipItem.raw}`;
                                    }
                                }
                            }
                        },
                        title: {
                            display: true,
                            text: `날짜: ${date}`
                        },
                        scales: {
                            x: {
                                beginAtZero: true
                            },
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
        },

        error: function(xhr) {
            console.log(xhr.responseText);
            alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
        }
    });
}

function updateChart() {
    const labels = <?php echo $jsonLabels; ?>;
    const datasets = <?php echo $jsonDatasets; ?>;

    // 차트
    const config = {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    mode: 'nearest',
                    intersect: true,
                    callbacks: {
                        afterLabel: function(context) {
                            let index = context.dataIndex;
                            let currentValue = context.raw;
                            let previousValue = index > 0 ? context.dataset.data[index - 1] : null;
                            if (previousValue === null) {
                                return '';
                            }
                            let change = ((currentValue - previousValue) / previousValue * 100).toFixed(2);
                            let symbol = change > 0 ? '▲' : '▼';
                            return `전일 대비: ${symbol} ${Math.abs(change)}%`;
                        }
                    }
                },
                legend: {
                    display: true,
                    position: 'top',
                    onClick: function(e, legendItem) {
                        const index = legendItem.datasetIndex;
                        const ci = this.chart;
                        const meta = ci.getDatasetMeta(index);
                        meta.hidden = meta.hidden === null ? !ci.data.datasets[index].hidden : !meta.hidden;
                        allHidden = ci.data.datasets.every(dataset => dataset.hidden);
                        ci.update();
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: true
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: '날짜'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    title: {
                        display: true,
                        text: '건수'
                    },
                    ticks: {
                        callback: function(value) {
                            return value;
                        }
                    }
                }
            }
        }
    };

    if (myChart) {
        myChart.destroy();
    }
    myChart = new Chart(document.getElementById('combinedChart'), config);
}

function toggleDatasets(visible) {
    if (visible) {
        myChart.data.datasets.forEach((dataset, index) => {
            dataset.hidden = false;
        });
    } else {
        myChart.data.datasets.forEach((dataset, index) => {
            dataset.hidden = true;
        });
    }

    allHidden = !visible; 
    myChart.update();
}

// 페이지 로드 시 차트 초기화
window.onload = function() {
    updateChart();
}

function approveVacation(vaca_idx) {
    var act = "confirm_vaca";
    $.ajax({
        type: "post",
        data: {
            vaca_idx:vaca_idx,
            act: act
        },
        url: "dashboard_ajax",
        dataType: "json",
        success:function(result) {
            document.getElementById('vacation-' + vaca_idx).remove();
            alert("상신완료");
        },
        error: function(xhr) {
            console.log(xhr.responseText);
            alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
            return;
        }
    });
}

function deletePartner(ptn_idx) {
    if (confirm("DASHBOARD에서 삭제 처리 하겠습니까? (실제삭제X) ")) {

        var row = document.getElementById('row-' + ptn_idx);
        if (row) {

            var act = "remove_dashboard";
            $.ajax({
                type: "post",
                data: {
                    ptn_idx:ptn_idx,
                    act: act
                },
                url: "dashboard_ajax",
                dataType: "json",
                success:function(result) {
                    row.remove();
                    alert("삭제완료");
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                    return;
                }
            });
        }
    }
}

function openMemoModal(ptn_idx) {
    $('#memoText').val(''); 
    $('#currentMstIdx').val(ptn_idx);

    var act = "open_memo_modal";

    $.ajax({
        type: "post",
        data: {
            act: act,
            ptn_idx: ptn_idx
        },
        url: "dashboard_ajax",
        dataType: "json", 
        success:function(result) {
            if (result.memoText) {
                var safeText = $("<div/>").text(result.memoText).html();
                $('#memoText').val(safeText);
            } else {
                $('#memoText').val('');
            }

            var now = new Date();
            var formattedDate = now.getFullYear() + '-' + (now.getMonth() + 1).toString().padStart(2, '0') + '-' + now.getDate().toString().padStart(2, '0');
            var formattedTime = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');

            // 메모의 끝에 날짜와 시간 정보 추가
            var dateMemoSuffix = "\n[" + formattedDate + " " + formattedTime + " 메모]\n";
            $('#memoText').val(function(index, value) {
                var prefix = value ? "\n" : "";
                var dateMemoSuffix = prefix + "[" + formattedDate + " " + formattedTime + " 메모]\n";
                return value + dateMemoSuffix;
            });
            $('#memoModal').modal('show');
        },
        error: function(xhr) {
            console.log(xhr.responseText);
            alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
            return;
        }
    });
    $('#memoModal').on('shown.bs.modal', function () {
        $('#memoText').focus().scrollTop($('#memoText')[0].scrollHeight);
    });
    $('#saveMemoButton').off('click').on('click', function() {
        saveMemo(ptn_idx);
    });
}


function saveMemo(ptn_idx) {
    var act = "save_memo_modal";
    var mstMemo = $('#memoText').val();
    $.ajax({
        type: "post",
        data: {
            act: act,
            ptn_idx: ptn_idx,
            mstMemo: mstMemo
        },
        url: "dashboard_ajax",
        dataType: "json", 
        success:function(result) {
            alert("저장되었습니다.");
            $('#memoModal').modal('hide');
            var memoButton = $('button[data-ptn-idx="' + ptn_idx + '"]');
            if (mstMemo.trim() !== '') {
                memoButton.removeClass('btn-secondary').addClass('btn-info');
            } else {
                memoButton.removeClass('btn-info').addClass('btn-secondary');
            }
        },
        error: function(xhr) {
            console.log(xhr.responseText);
            alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
            return;
        }
    });
}


</script>

<?php
include_once(G5_PATH . '/tail.php');
?>
