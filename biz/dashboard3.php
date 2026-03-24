<?php
    require_once '../common.php';
    include_once(G5_BIZ_PATH . '/common/access_control.php');

    $g5['title'] = "DASH BOARD";
    include_once(G5_PATH . '/head.php');

    $customerCount = rand(5, 10);
    $currentYear = date('Y'); // 현재 년도



    // 랜덤한 한국 연예인 이름 목록
    $koreanCelebrities = ["송중기", "송혜교", "이종석", "김태리", "조정석", "박보검", "한지민", "공유", "김고은", "박서준"];

    // 오늘 휴가자 데이터 생성
    $todayVacations = "";
    $todayVacationCount = rand(0, 3); // 오늘 휴가자 수 (0 또는 3)
    for ($i = 1; $i <= $todayVacationCount; $i++) {
        $name = $koreanCelebrities[array_rand($koreanCelebrities)]; // 한국 연예인 이름 랜덤 선택
        $date = date("Y-m-d"); // 날짜
        $type = rand(0, 1) ? "반차" : "연차"; // 휴가 종류 (반차 또는 연차)
        $todayVacations .= "<li class='list-group-item'>$i $name $date $type</li>";
    }

    // 내일 휴가자 데이터 생성
    $tomorrowVacations = "";
    $tomorrowVacationCount = rand(0, 3); // 내일 휴가자 수 (0 또는 3)
    for ($i = 1; $i <= $tomorrowVacationCount; $i++) {
        $name = $koreanCelebrities[array_rand($koreanCelebrities)]; // 한국 연예인 이름 랜덤 선택
        $date = date("Y-m-d", strtotime("+1 day")); // 내일 날짜
        $type = rand(0, 1) ? "반차" : "연차"; // 휴가 종류 (반차 또는 연차)
        $tomorrowVacations .= "<li class='list-group-item'>$i $name $date $type</li>";
    }

    // 미승인 연차 목록 생성
    $unapprovedVacations = "";
    $unapprovedVacationCount = rand(0, 3); // 미승인 연차 수 (0 또는 3)
    for ($i = 1; $i <= $unapprovedVacationCount; $i++) {
        $name = $koreanCelebrities[array_rand($koreanCelebrities)]; // 한국 연예인 이름 랜덤 선택
        $date = date("Y-m-d"); // 날짜
        $type = rand(0, 1) ? "반차" : "연차"; // 휴가 종류 (반차 또는 연차)
        $unapprovedVacations .= "<li class='list-group-item'>$i $name $date $type <button class='btn btn-xs btn-danger btn-sm float-right' onclick='approveVacation($i)'>승인</button></li>";
    }

    // 로그인한 사용자가 관리자인지 여부
    $isAdmin = rand(0, 1); // 랜덤으로 결정 (0 또는 1)
    $colClass = $isAdmin ? "col-md-4" : "col-md-6"; // 관리자이면 col-md-4, 일반 직원이면 col-md-6 할당

   
?>



<link rel="stylesheet" href="<?php echo G5_THEME_URL ?>/plugins/daterangepicker/daterangepicker.css">
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.8.0/main.min.css' rel='stylesheet' />

<script src="<?php echo G5_THEME_URL ?>/plugins/moment/moment.min.js"></script>
<script src="<?php echo G5_THEME_URL ?>/plugins/daterangepicker/daterangepicker.js"></script>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.8.0/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.8.0/locales-all.min.js'></script>


<!-- <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.2/dist/umd/popper.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>


<style>

.bootstrap-datetimepicker-widget.dropdown-menu {
    min-width: 24rem; /* 너비 조정 */
}

</style>
<section class="content">
    <div class="container-fluid">

        <div class="row">
            <?php if ($isAdmin): ?>
            <div class="col-md-4">
            <?php else: ?>
            <div class="col-md-6">
            <?php endif; ?>
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">오늘 연차 <i class="fas fa-umbrella-beach"></i> </h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php echo $todayVacations; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php if ($isAdmin): ?>
            <div class="col-md-4">
            <?php else: ?>
            <div class="col-md-6">
            <?php endif; ?>
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">내일 연차 <i class="fas fa-thumbs-up"></i></h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php echo $tomorrowVacations; ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <?php if ($isAdmin): ?>
            <div class="<?php echo $isAdmin ? 'col-md-4' : 'col-md-6'; ?>">
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
                        <h3 class="card-title">회의실 현황</h3>
                        <button type="button" class="btn btn-xs btn-warning float-right" data-toggle="modal" data-target="#reservationModal">
                            예약하기
                        </button>
                    </div>

                    <div class="card-body">
                        <div id='calendar'></div>
                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">통합 고객사 차트</h3>
                        <select id="weekSelector" onchange="updateChart();" class="float-right">
                            <?php
                            $currentYear = date('Y'); // 현재 년도
                            $currentDate = new DateTime(); // 현재 날짜
                            $currentWeek = $currentDate->format('W'); // 현재 주차
                            $oneWeekInterval = new DateInterval('P1W'); // 1주 간격
                            $threeMonthsAgoDate = (new DateTime())->modify('-3 months'); // 3개월 전 날짜

                            // 현재 날짜부터 시작하여 3개월 전까지의 주차 정보를 계산
                            while ($currentDate >= $threeMonthsAgoDate) {
                                $weekOfYear = $currentDate->format('W'); // 해당 날짜의 주차
                                $month = $currentDate->format('m'); // 월
                                $year = $currentDate->format('Y'); // 년
                                
                                // 현재 주차보다 크지 않은 경우에만 옵션으로 추가
                                if ($weekOfYear <= $currentWeek || $year < $currentYear) {
                                    echo "<option value='{$year}-{$month}-{$weekOfYear}'>{$year}년 {$month}월 {$weekOfYear}주차</option>";
                                }
                                
                                // 1주 전으로 이동
                                $currentDate->sub($oneWeekInterval);
                            }
                            ?>
                        </select>
                    </div>
                    <div class="card-body">
                        <canvas id="combinedChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>






<div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reservationModalLabel">회의실 예약</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="reservationForm" name="reservationForm" action="./dashboard_update" method="post" onsubmit="return submitReservation(this);">
        <input type="hidden" id="mb_no" name="mb_no" value="<?php echo $member['mb_no'] ?>">
          <div class="form-group">
            <label for="meetingName">이름</label>
            <input type="text" class="form-control" id="meetingName" required>
          </div>
          <div class="form-group">
            <label for="meetingReason">사유</label>
            <input type="text" class="form-control" id="meetingReason" required>
          </div>
          <div class="form-group">
            <label for="meetingTimeFrom" class="col-md-2 col-form-label">시작</label>
            <input type="text" class="form-control datetimepicker-input" id="meetingTimeFrom" data-toggle="datetimepicker" data-target="#meetingTimeFrom" required/>
          </div>

          <div class="form-group">
            <label for="meetingTimeTo" class="col-md-2 col-form-label">종료</label>
            <input type="text" class="form-control datetimepicker-input" id="meetingTimeTo" data-toggle="datetimepicker" data-target="#meetingTimeTo" required/>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">예약하기</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>





<script>
let myChart; // 전역 변수로 차트 인스턴스 저장

function generateRandomData() {
    const datasets = [];
    for (let i = 1; i <= <?php echo $customerCount; ?>; i++) {
        const r = Math.floor(Math.random() * 255);
        const g = Math.floor(Math.random() * 255);
        const b = Math.floor(Math.random() * 255);
        let dataPoints = [];
        for (let j = 0; j < 7; j++) {
            dataPoints.push(Math.floor(Math.random() * 100));
        }
        datasets.push({
            label: `고객사${i} 데이터`,
            backgroundColor: `rgba(${r}, ${g}, ${b}, 0.2)`,
            borderColor: `rgba(${r}, ${g}, ${b}, 1)`,
            data: dataPoints,
        });
    }
    return datasets;
}

function updateChart() {
    // 요일 레이블 배열
    const daysOfWeek = ['일', '월', '화', '수', '목', '금', '토'];

    // 현재 요일 인덱스 계산 (0: 일요일, 6: 토요일)
    const todayIndex = new Date().getDay();

    // 현재 요일을 기준으로 레이블 배열 재구성
    const reorderedLabels = [];
    for (let i = 1; i <= 7; i++) {
        const dayIndex = (todayIndex + i) % 7;
        reorderedLabels.push(daysOfWeek[dayIndex]);
    }

    const data = {
        labels: reorderedLabels,
        datasets: generateRandomData()
    };

    if (myChart) {
        myChart.data = data;
        myChart.update();
    } else {
        myChart = new Chart(document.getElementById('combinedChart'), {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        onClick: function(e, legendItem, legend) {
                            const index = legendItem.datasetIndex;
                            const chart = legend.chart;
                            chart.getDatasetMeta(index).hidden = chart.isDatasetVisible(index) ? !chart.getDatasetMeta(index).hidden : null;
                            chart.update();
                        }
                    }
                }
            }
        });
    }
}


function generateRandomMeetings() {
    var events = [];
    var fakeNames = ['홍길동', '김길동', '이길동', '박길동', '최길동'];
    var currentDate = new Date();
    var currentYear = currentDate.getFullYear();
    var currentMonth = currentDate.getMonth(); // 0부터 시작
    var startDate = new Date(currentYear, currentMonth, currentDate.getDate() - currentDate.getDay() + 1, 9, 0, 0); // 이번 주 월요일, 9시 시작

    for (var i = 0; i < 5; i++) {
        var nameIndex = Math.floor(Math.random() * fakeNames.length);
        var day = Math.floor(Math.random() * 5); // 0 (월요일)에서 4 (금요일) 사이
        var startHour = Math.floor(Math.random() * 8) + 9; // 9시부터 16시 사이 시작 시간
        var startMinutes = [0, 15, 30, 45][Math.floor(Math.random() * 4)]; // 15분 단위 시작 분
        var durationHours = Math.floor(Math.random() * 2); // 회의 지속 시간 (0~1시간)
        var durationMinutes = [0, 15, 30, 45][Math.floor(Math.random() * 4)]; // 추가 회의 지속 분 (15분 단위)

        var meetingStartDate = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate() + day, startHour, startMinutes, 0);
        var meetingEndDate = new Date(meetingStartDate.getTime() + durationHours * 60 * 60 * 1000 + durationMinutes * 60 * 1000);

        events.push({
            title: fakeNames[nameIndex] + ' 회의',
            start: meetingStartDate.toISOString(),
            end: meetingEndDate.toISOString(), // 종료 시간 추가
        });
    }

    return events;
}


function submitReservation() {
    
    const name = document.getElementById('meetingName').value;
    const reason = document.getElementById('meetingReason').value;
    const startTime = $('#meetingTimeFrom').datetimepicker('date');
    const endTime = $('#meetingTimeTo').datetimepicker('date');

    // 시작일이 종료일보다 크거나 종료일이 시작일보다 작은 경우에는 예약을 제출하지 않고 모달을 닫음
    if (startTime >= endTime) {
        alert('시작일은 종료일보다 이전이어야 합니다.');
        return false;
    }

    // 회의 시간이 3시간 이상인 경우 예약을 제출하지 않고 모달을 닫음
    const duration = moment.duration(endTime.diff(startTime));
    const hours = duration.asHours();
    if (hours > 3) {
        alert('한번 등록시 최대 3시간까지 가능합니다.');
        return false;
    }

    console.log('예약 정보:', name, reason, startTime, endTime);

    // 예약 성공 후 모달 닫기
    $('#reservationModal').modal('hide');
}


function approveVacation(vacationId) {
    // 여기에 승인 처리하는 로직을 추가하세요
    alert('연차를 승인하시겠습니까? ID: ' + vacationId);
}



// 페이지 로드 시 차트 초기화
window.onload = function() {
    updateChart();

    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        contentHeight: 'auto',
        headerToolbar: {
            left: 'next today',
            center: 'title',
            right: 'timeGridWeek,timeGridDay'
        },
        allDaySlot: false,
        hiddenDays: [0, 6], // 주말 숨김
        slotMinTime: "09:00:00",
        slotMaxTime: "18:30:00",
        slotDuration: "00:15:00", // 15분 단위 슬롯
        locale: 'ko', // 한국어 설정
        events: generateRandomMeetings() // 회의 일정 생성
    });
    calendar.render();

    var now = moment();
    var roundedMinutes = Math.ceil(now.minutes() / 15) * 15;
    var startTime = now.clone().minutes(roundedMinutes).add(1, 'hours').seconds(0);
    var endTime = startTime.clone().add(1, 'hours');

    // 주말 날짜를 비활성화하는 코드
    var disabledDates = [];
    var startOfWeek = moment().startOf('week');
    for (var i = 0; i < 52; i++) { // 1년치 주말 계산
        disabledDates.push(startOfWeek.clone().add(i, 'weeks').day(6).toDate()); // 토요일
        disabledDates.push(startOfWeek.clone().add(i, 'weeks').day(0).toDate()); // 일요일
    }

    $('#meetingTimeFrom').datetimepicker({
        format: 'YYYY-MM-DD HH:mm',
        stepping: 15,
        defaultDate: startTime,
        disabledDates: disabledDates,
        icons: {
            time: 'far fa-clock'
        }
    });

    $('#meetingTimeTo').datetimepicker({
        format: 'YYYY-MM-DD HH:mm',
        stepping: 15,
        defaultDate: endTime,
        useCurrent: false,
        disabledDates: disabledDates,
        icons: {
            time: 'far fa-clock'
        }
    });
}



</script>


<?php
include_once(G5_PATH . '/tail.php');
?>
