<?php
require_once '../../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');

$g5['title'] = "회의실예약";
include_once(G5_PATH . '/head.php');



?>

<link rel="stylesheet" href="<?php echo G5_THEME_URL ?>/plugins/daterangepicker/daterangepicker.css">
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.8.0/main.min.css' rel='stylesheet' />

<script src="<?php echo G5_THEME_URL ?>/plugins/moment/moment.min.js"></script>
<script src="<?php echo G5_THEME_URL ?>/plugins/daterangepicker/daterangepicker.js"></script>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.8.0/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.8.0/locales-all.min.js'></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/ko.js"></script>

<style>
    .fc-day-sun { /* 일요일 */
        background-color: #ffcccc; /* 빨간색 계열 */
    }
    .fc-day-sat { /* 토요일 */
        background-color: #cceeff; /* 파란색 계열 */
    }
    .bootstrap-datetimepicker-widget.dropdown-menu {
        min-width: 24rem; /* 너비 조정 */
    }
</style>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">회의실예약</h3>
                    </div>

                    <div class="card-body">
                        <div class="dataTables_wrapper dt-bootstrap4">

                            <div class="d-flex flex-sm-row flex-column justify-content-sm-between">
                                <div class="d-flex justify-content-center mb-2 mb-sm-0">
                                    <div class="btn-group xs-100">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#reservationModal">
                                            예약하기
                                        </button>
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

    </div>
</section>


<div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header bg-primary">
            <h5 class="modal-title" id="reservationModalLabel">회의실 예약</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="modal-body">
            <form id="reservationForm" name="reservationForm" action="./hr_meet_list_update" method="post" onsubmit="return submitReservation(this);">
            <input type="hidden" id="mb_no" name="mb_no" value="<?php echo $member['mb_no'] ?>">
            <input type="hidden" id="isContinue" name="isContinue" value="">

            <div class="form-group">
                <label for="meetingName">이름</label>
                <select id="meet_mb_no" name=meet_mb_no class="form-control border-info">
                </select>
            </div>
            <div class="form-group">
                <label for="meetingReason">사유</label>
                <input type="text" class="form-control" id="meetingReason" name="meetingReason" required>
            </div>
            <div class="form-group">
                <label for="meetingTimeFrom" class="col-md-2 col-form-label">시작</label>
                <input type="text" class="form-control datetimepicker-input" id="meetingTimeFrom" name="meetingTimeFrom" data-toggle="datetimepicker" data-target="#meetingTimeFrom" required/>
            </div>

            <div class="form-group">
                <label for="meetingTimeTo" class="col-md-2 col-form-label">종료</label>
                <input type="text" class="form-control datetimepicker-input" id="meetingTimeTo" name="meetingTimeTo" data-toggle="datetimepicker" data-target="#meetingTimeTo" required/>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                <button type="submit" class="btn btn-primary" id="btn_approval" name="act_button" value="회의실예약">회의실예약</button>
            </div>
            </form>
        </div>
    </div>
  </div>
</div>




<script>

$(function() {

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
        locale: 'ko',  // 한국어 설정 적용
        minDate: now, // 현재 시간을 최소 날짜로 설정하여 과거 선택 방지
        icons: { time: 'far fa-clock' }
    });

    $('#meetingTimeTo').datetimepicker({
        format: 'YYYY-MM-DD HH:mm',
        stepping: 15,
        defaultDate: endTime,
        useCurrent: false,
        disabledDates: disabledDates,
        locale: 'ko',  // 한국어 설정 적용
        minDate: now, // 현재 시간을 최소 날짜로 설정하여 과거 선택 방지
        icons: { time: 'far fa-clock' }
    });

    // 시작일 또는 종료일 변경 시
    $('#meetingTimeFrom, #meetingTimeTo').on('change.datetimepicker', function(e) {
        var fromDateTime = $('#meetingTimeFrom').datetimepicker('date');
        var toDateTime = $('#meetingTimeTo').datetimepicker('date');
               
        // 일자 동기화 - 마지막으로 변경된 datetimepicker에 따라
        if (e.target.id === 'meetingTimeFrom') {
            // 시작일 변경 시, 종료일 동기화
            if (fromDateTime.format('YYYY-MM-DD') !== toDateTime.format('YYYY-MM-DD')) {
                $('#meetingTimeTo').datetimepicker('date', fromDateTime.clone());
            }
        } else if (e.target.id === 'meetingTimeTo') {
            // 종료일 변경 시, 시작일 동기화
            if (fromDateTime.format('YYYY-MM-DD') !== toDateTime.format('YYYY-MM-DD')) {
                $('#meetingTimeFrom').datetimepicker('date', toDateTime.clone());
            }
        }

        var act = "dup_chk_meetroom";
        $.ajax({
            type: "post",
            data: {
                act: act,
                fromDateTime: fromDateTime.format('YYYY-MM-DD HH:mm'),
                toDateTime: toDateTime.format('YYYY-MM-DD HH:mm')
            },
            url: "hr_ajax",
            dataType: "json",
            success:function(result) {
                if(result != "OK") {
                    alert(result.mb_name + "님께서 " + result.meet_startday + " ~ " + result.meet_endday + "까지 예약건이 있습니다. 예약 캘린더를 확인 후 예약해주세요.");
                    // $('#meetingTimeFrom').val("");
                    // $('#meetingTimeTo').val("");
                    adjustTimeBasedOnRange(fromDateTime, toDateTime);
                    return false;
                }
            }
        });

    });

    function adjustTimeBasedOnRange(fromDateTime, toDateTime) {
        // 시작 및 종료 시간의 시간과 분을 추출
        var startHour = fromDateTime.hour();
        var startMinute = fromDateTime.minute();
        var endHour = toDateTime.hour();
        var endMinute = toDateTime.minute();

        // 종료 시간이 시작 시간보다 이전인 경우, 또는 종료 시간과 시작 시간의 차이가 15분 미만인 경우
        if (toDateTime.isBefore(fromDateTime) || toDateTime.diff(fromDateTime, 'minutes') < 15) {
            // 종료 시간을 시작 시간으로부터 최소 15분 후로 조정
            var adjustedEndTime = fromDateTime.clone().add(15, 'minutes');
            $('#meetingTimeTo').datetimepicker('date', adjustedEndTime);
            return; // 추가적인 조정 필요 없으므로 함수 종료
        }

        var shouldAdjustTime = startHour < 9 || startHour > 17 || endHour < 9 || endHour > 17;

        // 시간 범위를 벗어나면 10:00에서 11:00으로 조정
        if (shouldAdjustTime) {
            $('#meetingTimeFrom').datetimepicker('date', fromDateTime.clone().hour(10).minute(0));
            $('#meetingTimeTo').datetimepicker('date', fromDateTime.clone().hour(11).minute(0));
        }
    }





    $('#reservationModal').on('show.bs.modal', function (e) {
        var $target = $("#meet_mb_no");
        var act = "get_meet_emp_list";

        $target.empty();

        $.ajax({
            type: "post",
            data: {
                act: act
            },
            url: "hr_ajax",
            dataType: "json",
            success:function(result) {
                $target.append(result[0]);
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                return;
            }
        });

    });
});

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'ko',
        initialView: 'timeGridWeek',
        editable: false,
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
            var meet_mb_deptno = info.event._def.extendedProps.meet_mb_deptno;
            var meet_mb_no = info.event._def.extendedProps.meet_mb_no;
            var meet_idx = info.event._def.extendedProps.meet_idx;
            
            var mb_no = '<?php echo $member['mb_no'] ?>';

            //alert("mb_no:"+mb_no + "/ meet_mb_no:" + meet_mb_no);

            if(mb_no == meet_mb_no) {
                if (confirm("해당 예약건을 취소하겠습니까?")) {

                    var act = "del_meet";
                    $.ajax({
                        type: "post",
                        data: {
                            act: act,
                            meet_idx:meet_idx
                        },
                        url: "hr_ajax",
                        dataType: "json",
                        success: function(result) {
                            alert(result);
                            location.reload();
                        },
                        error: function(xhr) {
                            alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                        }
                    });
                }
            }
        }
    });
    calendar.render();
});

function loadEvents(startStr, endStr, successCallback) {
    var start = formatDate(new Date(startStr));
    var end = formatDate(new Date(endStr));
    var act = "get_meet_list";

    $.ajax({
        type: "post",
        data: {
            act: act,
            start: start,
            end: end
        },
        url: "hr_ajax",
        dataType: "json",
        success: function(result) {
            successCallback(result);
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

function submitReservation() {

    const name = document.getElementById('meet_mb_no').value;
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

    // 예약 시간이 15분 이하인 경우 검사
    const duration1 = moment.duration(endTime.diff(startTime));
    const minDurationMinutes = 15;
    if (duration1.asMinutes() < minDurationMinutes) {
        alert('예약 시간은 최소 15분 이상이어야 합니다.');
        return false;
    }


    // 시작일과 종료일이 09:00 ~ 18:00 사이에 있는지 확인
    const startHour = startTime.hours() + startTime.minutes() / 60;
    const endHour = endTime.hours() + endTime.minutes() / 60;
    if (startHour < 9 || endHour > 18 || endHour < 9 || startHour > 18) {
        alert('예약 시간은 09:00 ~ 18:00 사이여야 합니다.');
        return false;
    }
    

    console.log('예약 정보:', name, reason, startTime, endTime);

    // 예약 성공 후 모달 닫기
    $('#reservationModal').modal('hide');
}






</script>



<?php
include_once(G5_PATH . '/tail.php');
?>
