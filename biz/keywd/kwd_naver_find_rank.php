<?php

require_once '../../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');

$g5['title'] = "네이버 쇼핑순위 관리";
include_once(G5_PATH . '/head.php');


$host = '115.71.19.114';
$dbname = 'naver_gonplan';
$username = 'gonplan'; // 데이터베이스 사용자 이름
$password = 'gon!@34qwer@@'; // 데이터베이스 비밀번호

// 데이터베이스 연결
$mysqli = new mysqli($host, $username, $password, $dbname);

// 연결 오류 확인
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$mbNo = $member['mb_no']; // Make sure this value is secure and properly filtered

if (!empty($stx)) {
    // Append to the existing query to filter by productName or mallName
    $query .= " AND (a.productName LIKE CONCAT('%', ?, '%') OR a.mallName LIKE CONCAT('%', ?, '%'))";
}

if ($sst) {
    $sql_order = " order by $sst $sod ";    
}


// SQL 쿼리 준비
$query = "SELECT a.mstIdx,
                 a.mbNo,
                 a.mbId,
                 a.mbName,
                 a.nvMid,
                 IF(CHAR_LENGTH(a.mallName) > 25, CONCAT(SUBSTRING(a.mallName, 1, 25), '...'), a.mallName) AS mallName,
                 IF(CHAR_LENGTH(a.productName) > 25, CONCAT(SUBSTRING(a.productName, 1, 25), '...'), a.productName) AS productName,
                 a.mallProductId,
                 a.id,
                 a.shopnno,
                 a.seq,
                 a.selKeyword,
                 a.selGubun,
                 a.selValue,
                 a.init_rank,
                 a.startDate,
                 a.endDate,
                 a.mstMemo,
                 a.useYn,
                 a.insertDate,
                 (SELECT count(*) FROM gnp_naver_rank_daily b WHERE b.mstIdx = a.mstIdx) AS cntran,
                 (SELECT c.dailyRank FROM gnp_naver_rank_daily c WHERE c.mstIdx = a.mstIdx ORDER BY c.insertDate DESC LIMIT 1) AS curran,
                 (SELECT ROUND(AVG(c.dailyRank), 2) FROM gnp_naver_rank_daily c WHERE c.mstIdx = a.mstIdx AND c.dailyRank IS NOT NULL) AS avgrank
          FROM gnp_naver_rank_master a
          WHERE a.useYn = 'Y'
          AND a.mbNo = ?
          {$sql_order}
          ";
          

// $stmt = $mysqli->prepare($query);
// $stmt->bind_param("i", $mbNo); // Assuming $mbNo is an integer
// $stmt->execute();
// $result = $stmt->get_result();
// $stmt->close(); // 첫 번째로 스테이트먼트를 닫습니다.
// $mysqli->close(); // 그 다음 데이터베이스 연결을 닫습니다.

if (!empty($stx)) {
    // Append to the existing query to filter by productName or mallName
    $query .= " AND (a.productName LIKE CONCAT('%', ?, '%') OR a.mallName LIKE CONCAT('%', ?, '%'))";
}

$stmt = $mysqli->prepare($query);

// Bind parameters based on the presence of $stx
if (!empty($stx)) {
    $stmt->bind_param("iss", $mbNo, $stx, $stx); // 'i' for integer (mbNo), 's' for string (stx), 's' for string (stx)
} else {
    $stmt->bind_param("i", $mbNo); // Only bind $mbNo if $stx is empty
}

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$mysqli->close();
?>



<style>
.modal-backdrop.show {
    opacity: 0.8 !important; /* Dim the background */
}

.loading-spinner {
    z-index: 1051; /* Above the modal content */
}
.up {
    color: red; /* 상승시 빨강색 */
}
.down {
    color: blue; /* 하락시 파랑색 */
}

</style>


<link rel="stylesheet" href="<?php echo G5_THEME_URL ?>/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
<link rel="stylesheet" href="<?php echo G5_THEME_URL ?>/plugins/daterangepicker/daterangepicker.css">

<script src="<?php echo G5_THEME_URL ?>/plugins/moment/moment.min.js"></script>
<script src="<?php echo G5_THEME_URL ?>/plugins/daterangepicker/daterangepicker.js"></script>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">네이버 쇼핑순위 관리</h3><small class="text-danger">&nbsp;※매일 10시경 차례대로 업데이트 됩니다※</small>
                    </div>
                    <div class="card-body">
                        <div class="dataTables_wrapper dt-bootstrap4">

                            <div class="d-flex flex-sm-row flex-column justify-content-sm-between">
                                <div class="d-flex justify-content-center mb-2 mb-xs-0">
                                    <div class="btn-group xs-100">
                                        <button type="button" class="btn btn-success btn-xs border border-dark" data-toggle="modal" data-target="#modal-get-naver-rank">
                                            네이버 쇼핑순위 <i class="fas fa-search"></i>
                                        </button>

                                        <button type="submit" form="listForm" class="ml-1 btn btn-success btn-xs border border-dark" name="act_button" value="선택연장">
                                            연장 <i class="fas fa-handshake"></i>
                                        </button>

                                        <button type="submit" form="listForm" class="ml-1 btn btn-success btn-xs border border-dark" name="act_button" value="엑셀다운">
                                            엑셀다운 <i class="fas fa-file-excel"></i>
                                        </button>

                                    </div>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <div class="btn-group xs-100">

                                        <form class="form-inline my-2 my-lg-0 ng-pristine ng-valid">

                                            <select name="sfl" id="sfl" class="custom-select border border-secondary">
                                                <option value="">스토어명||상품명</option>
                                            </select>

                                            <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" class="border-dark form-control sm-1" placeholder="검색어" aria-label="검색어">
                                            <button type="submit" class="btn btn-outline-success my-2 my-sm-0">검색</button>
                                        </form>
                                    </div>
                                </div>
                            </div>


                            <form name="listForm" id="listForm" action="./kwd_naver_find_rank_update" method="post">
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
                                                    <th><input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)"></th>
                                                    <th>NO</th>
                                                    <th>상품ID</th>
                                                    <th>NVMID</th>
                                                    <th><?php echo get_sort_bootst('mallName','', 'desc', $sst, $sod, '스토어명'); ?></th>
                                                    <th><?php echo get_sort_bootst('productName','', 'desc', $sst, $sod, '상품명'); ?></th>
                                                    <th>키워드</th>
                                                    <th>초기</th>
                                                    <th>현재</th>
                                                    <th>평균</th>
                                                    <th>기록</th>
                                                    <th>시작-종료일</th>
                                                    <th style="width: 140px;">관리</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            $i = 0;
                                            while ($row = $result->fetch_assoc()) {

                                                $isAssign = "";
                                                $isStop = "";
                                                $isAddBtn = "";
                                                $memoBtnClass = "btn-secondary";

                                                if ($row['endDate'] < date('Y-m-d')) {
                                                    $isAssign = 'table-danger';
                                                    $isStop = "<i class='fas fa-ban'></i>";
                                                    $isAddBtn = "<button type='button' class='btn btn-success btn-xs' onclick='alertMstIdx(" . $row['mstIdx'] . ")'>연장</button>";
                                                }

                                                if (!empty($row['mstMemo'])) {
                                                    $memoBtnClass = "btn-info";  // 메모 값이 있는 경우 info 색상으로 변경
                                                }

                                                $i = $i + 1;
                                                echo "<tr class='".$isAssign."'>";
                                                echo '<td>';
                                                echo '<input type="checkbox" class="row-checkbox" name="chk[]" value="' . htmlspecialchars($row['mstIdx']) . '">';
                                                echo '<input type="hidden" name="mstIdx[' . htmlspecialchars($row['mstIdx']) . ']" value="' . htmlspecialchars($row['mstIdx']) . '">';
                                                echo '</td>';
                                                echo "<td>" . htmlspecialchars($i) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['mallProductId']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['nvMid']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['mallName']) . "</td>";
                                                echo "<td>" . $isStop . " " . htmlspecialchars($row['productName']) . " " .$isAddBtn. " </td>";
                                                echo "<td>" . htmlspecialchars($row['selKeyword']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['init_rank']) . "위</td>";
                                                echo "<td>" . htmlspecialchars($row['curran']) . "위</td>";
                                                echo "<td>" . htmlspecialchars($row['avgrank']) . "위</td>";
                                                echo "<td><button type='button' class='btn btn-primary btn-xs' onclick='viewDetails(" . $row['mstIdx'] . ")'>".htmlspecialchars($row['cntran'])."보기</button></td>";
                                                echo "<td>" . htmlspecialchars($row['startDate'] ." || ". $row['endDate']) . "</td>";
                                                echo "<td>";
                                                echo "<button type='button' class='btn btn-danger btn-xs' onclick='confirmDelete(" . $row['mstIdx'] . ")'>삭제</button> ";
                                                echo "<button type='button' class='btn btn-warning btn-xs' onclick='researchRank(" . $row['mstIdx'] . ")'>재조회</button> ";
                                                echo "<button type='button' class='btn " . $memoBtnClass . " btn-xs' data-toggle='modal' data-target='#memoModal' onclick='openMemoModal(" . $row['mstIdx'] . ")'>메모</button>";
                                                echo "</td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center justify-content-sm-end">
                                    
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="modal-get-naver-rank" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h4 class="modal-title">네이버 쇼핑순위 <i class="fas fa-search"></i></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="input-group p-2">
                <input type="text" id="sel_keyword" name="sel_keyword" class="form-control col-2" placeholder="키워드" aria-label="Keyword" required>

                <select class="custom-select col-2" id="sel_gubun" name="sel_gubun">
                    <option value="1">address</option>
                    <option value="2">mvid</option>
                </select>

                <input type="text" id="sel_value" name="sel_value" class="form-control" placeholder="주소입력" aria-label="주소입력" required>

                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" onclick="call_api()" id="btn_naver_search">검색</button>
                </div>
            </div>
            <div id="naver_search_result">

            </div>
        </div>
    </div>
</div>
<!-- tbody modal end -->
<div class="loading-spinner" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
    <div class="spinner-border text-light" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>

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


<div class="modal fade" id="dataModal" tabindex="-1" role="dialog" aria-labelledby="dataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success d-flex align-items-center">
                <!-- Input text 추가, 왼쪽에 배치하고 패딩 p-2 적용 -->
                <input type="text" id="search_fromto" class="form-control form-control-sm p-2 d-none d-md-block" style="width: 190px;">

                <h5 class="modal-title" id="dataModalLabel"></h5>
                <button type="button" class="close ml-auto" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <canvas id="dataChart"></canvas>
                    </div>
                    <div class="col-md-4">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>일자</th>
                                    <th>랭킹</th>
                                    <th>변화</th>
                                    <th>등락률</th>
                                    <th>리뷰</th>
                                    <th>구매</th>
                                </tr>
                            </thead>
                            <tbody id="dataTable">
                                <!-- Data rows will be inserted here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

var currentMstIdx = "";
var lastSuccessfulData = null; // 성공적인 요청의 데이터를 저장할 변수


document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    let isDragging = false;
    let dragValue = false;
    let skipClick = false;

    // 전체 선택 함수
    window.check_all = function(form) {
        const checkboxes = form.querySelectorAll('input[name="chk[]"]');
        const chkall = form.querySelector('#chkall');
        checkboxes.forEach(cb => cb.checked = chkall.checked);
    };

    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('mousedown', function(e) {
            isDragging = true;
            dragValue = !this.checked; 
            this.checked = dragValue;
            skipClick = true; 
            e.preventDefault(); 
        });

        checkbox.addEventListener('mouseover', function() {
            if (isDragging) {
                this.checked = dragValue;
            }
        });

        checkbox.addEventListener('click', function(e) {
            if (skipClick) {
                e.preventDefault();
                skipClick = false;
            }
        });
    });

    document.addEventListener('mouseup', function() {
        isDragging = false;
        dragValue = false;
    });

    document.addEventListener('mouseleave', function() {
        isDragging = false;
        dragValue = false;
    });
});


$(document).ready(function() {

    var startDay = moment().subtract(29, 'days');
    var endDay = moment();
    var maxDay = moment();

    $('#search_fromto').daterangepicker({
        locale: {
            format: 'YYYY-MM-DD',
            separator: ' ~ ',
            applyLabel: '확인',
            cancelLabel: '취소',
            fromLabel: 'From',
            toLabel: 'To',
            customRangeLabel: 'Custom',
            weekLabel: 'W',
            daysOfWeek: ["일", "월", "화", "수", "목", "금", "토"],
            monthNames: ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"]
        },
        maxDate: maxDay,
        showDropdowns: true,
        startDate: startDay,
        endDate: endDay,
        maxSpan: {
            "days": 31
        }
    }, function(start, end, label) {
        // 새로운 날짜 범위가 선택되면 이 함수를 실행
        updateChart(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
    });


    // 모달이 열릴 때 이벤트 리스너를 등록합니다.
    $('#modal-get-naver-rank').on('show.bs.modal', function (e) {
        // #naver_search_result의 내용을 초기화합니다.
        $('#naver_search_result').empty();
    });
});

function call_api(){

    if (!confirm("약 5초~30초정도 소요됩니다.")) {
        return false;
    } else {

        const searchButton = document.getElementById("btn_naver_search");
        searchButton.disabled = true;

        document.querySelector('.loading-spinner').style.display = 'block';

        //document.getElementById("btn_naver_search").disabled = "disabled";
        var act = "find_naver_rank";
        var sel_keyword = $('#sel_keyword').val();
        var sel_gubun = $("#sel_gubun option:selected").val();
        var sel_value = $('#sel_value').val();

        $.ajax({
            type: "post",
            data: {
                act: act,
                sel_keyword: sel_keyword,
                sel_gubun: sel_gubun,
                sel_value: sel_value
            },
            url: "kwd_ajax",
            dataType: "json", //전송받는 데이터형태 json
            success:function(result) {
                if(result == null || result == "") {
                    alert("검색 결과가 없습니다.");
                } else {
                    $('#naver_search_result').html(result);
                }          
                
                document.querySelector('.loading-spinner').style.display = 'none';
                searchButton.disabled = false;

            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                searchButton.disabled = false;
                return;
            }
        });
        
        
    }
}

function confirmDelete(mstIdx) {
    if (confirm('삭제시 복구할수 없습니다. 정말 삭제하시겠습니까?')) {
        var act = "del_master_rank";
        $.ajax({
            type: "post",
            data: {
                act: act,
                mstIdx: mstIdx
            },
            url: "kwd_ajax",
            dataType: "json", 
            success:function(result) {
                location.href = location.href;
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                return;
            }
        });
    }
}

function alertMstIdx(mstIdx) {
    if (confirm('해당 쇼핑몰의 상품을 30일 연장 하시겠습니까?')) {
        var act = "continue_history";
        $.ajax({
            type: "post",
            data: {
                act: act,
                mstIdx: mstIdx
            },
            url: "kwd_ajax",
            dataType: "json", 
            success:function(result) {
                location.href = location.href;
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                return;
            }
        });
    }
}

function researchRank(mstIdx) {
    if (confirm('해당 키워드로 상품 순위를 재조회 하겠습니까?')) {
        var act = "research_rank";
        $.ajax({
            type: "post",
            data: {
                act: act,
                mstIdx: mstIdx
            },
            url: "kwd_ajax",
            dataType: "json", 
            success:function(result) {
                location.href = location.href;
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
                return;
            }
        });
    }
}

function viewDetails(mstIdx) {
    currentMstIdx = mstIdx;
    var act = "get_chart";
    var startDate = moment().subtract(31, 'days').format('YYYY-MM-DD'); // 31일 전 날짜를 계산하고 포맷
    var endDate = moment().format('YYYY-MM-DD'); // 오늘 날짜를 'YYYY-MM-DD' 형식으로 포맷
    getAjaxChart(act, mstIdx, startDate, endDate);
}


function updateChart(startDate, endDate) {
    $('#dataModal').on('hidden.bs.modal', function () {
        var act = "get_chart";
        getAjaxChart(act, currentMstIdx, startDate, endDate);
        $(this).off('hidden.bs.modal'); // 이벤트 리스너 제거하여 다음에 영향을 주지 않도록 함
    }).modal('hide');
}


function getAjaxChart(act, mstIdx, startDate, endDate) {
    $.ajax({
        type: "post",
        data: {
            act: act,
            mstIdx: mstIdx,
            startDate: startDate,
            endDate: endDate
        },
        url: "kwd_ajax",
        dataType: "json",
        success: function(result) {
            if (!result || result.length === 0 || result[0] === null) {
                alert("해당 일자에 데이터가 존재하지않습니다.");
                $('#dataModal').modal('show'); // 모달 보이기
            } else {
                const labels = [];
                const dataPoints = [];
                const pointBackgroundColors = []; // 데이터 포인트의 배경색 배열
                const tableData = [];
                const title = result[0].selKeyword;

                var productName = "";
                if(result[0].productName.length > 20) {
                    productName = result[0].productName.substring(0,20) + "...";
                } else {
                    productName = result[0].productName;
                }
                document.getElementById("dataModalLabel").innerHTML = "&nbsp;&nbsp;&nbsp;<i class='fas fa-chart-line text-warning'></i> " + "[" + result[0].mallName + "] " + productName;

                result = result[1];

                result.forEach(function(item, index, array) {
                    labels.push(item.insertDate.split(' ')[0]);
                    dataPoints.push(parseInt(item.dailyRank, 10));
                    tableData.push(`<tr data-index="${index}">
                                        <td>${item.insertDate}</td>
                                        <td>${item.dailyRank}위</td>
                                        <td class="comparison"></td>
                                        <td class="percentage"></td>
                                        <td>${item.dailyReview}</td>
                                        <td>${item.dailyPurchase}</td>
                                    </tr>`);
                    // 색상 결정 로직
                    if (index > 0) {
                        const prevRank = parseInt(array[index - 1].dailyRank, 10);
                        const currentRank = parseInt(item.dailyRank, 10);
                        if (currentRank < prevRank) {
                            pointBackgroundColors.push('red'); // 상승했을 때 빨간색
                        } else if (currentRank > prevRank) {
                            pointBackgroundColors.push('blue'); // 하락했을 때 파란색
                        } else {
                            pointBackgroundColors.push('grey'); // 변화 없음
                        }
                    } else {
                        pointBackgroundColors.push('grey'); // 첫 번째 데이터 포인트는 비교 대상이 없으므로 회색
                    }
                });

                const canvas = document.getElementById('dataChart');
                if (window.myChart) {
                    window.myChart.destroy();
                }
                const ctx = canvas.getContext('2d');
                window.myChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: title,
                            data: dataPoints,
                            borderColor: 'rgb(0, 0, 0)',
                            backgroundColor: pointBackgroundColors, // 데이터 포인트 색상 사용
                            tension: 0.1,
                            pointRadius: 5, // 점 크기 설정
                            fill: false // 선 아래 채우기 비활성화
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                reverse: true
                            }
                        },
                        plugins: {
                            tooltip: {
                                enabled: true,
                                mode: 'index',
                                intersect: false
                            }
                        }
                    }
                });

                const dataTable = document.getElementById('dataTable');
                dataTable.innerHTML = tableData.join('');

                for (let i = 1; i < result.length; i++) {
                    const prevRank = parseInt(result[i - 1].dailyRank, 10);
                    const currentRank = parseInt(result[i].dailyRank, 10);
                    let comparison = '';
                    let comparisonClass = '';
                    let changePercentage = 0; // 변화율을 저장할 변수

                    if (currentRank < prevRank) {
                        comparison = '▲ ' + (prevRank - currentRank);
                        comparisonClass = 'up';
                        // 순위가 개선된 경우, 상승 비율 계산
                        changePercentage = ((prevRank - currentRank) / prevRank * 100).toFixed(2);
                    } else if (currentRank > prevRank) {
                        comparison = '▼ ' + (currentRank - prevRank);
                        comparisonClass = 'down';
                        // 순위가 악화된 경우, 하락 비율 계산
                        changePercentage = -((currentRank - prevRank) / prevRank * 100).toFixed(2);
                    } else {
                        comparison = '-';
                        changePercentage = '0.00'; // 변화가 없는 경우
                    }
                    dataTable.rows[i].cells[2].textContent = comparison;
                    dataTable.rows[i].cells[2].className = comparisonClass;
                    dataTable.rows[i].cells[3].textContent = changePercentage + '%';
                    dataTable.rows[i].cells[3].className = comparisonClass; // 새 셀에 변화율 표시, 동일 클래스 적용
                }

                $('#dataModal').modal('show');
            }
        },
        error: function(xhr) {
            console.error("Error: ", xhr.responseText);
            alert("지금은 시스템 사정으로 요청하신 작업을 처리할 수 없습니다.\n잠시 후 다시 이용해주세요.");
        }
    });
}

function validateForm() {
    var startDate = new Date(document.getElementById('startDate').value);
    var endDate = new Date(document.getElementById('endDate').value);
    var timeDiff = endDate - startDate;
    var daysDiff = timeDiff / (1000 * 3600 * 24);
    
    if (daysDiff > 31) {
        alert('시작일과 종료일은 1달을 초과할 수 없습니다.');
        return false;
    }
    return true;
}


function openMemoModal(mstIdx) {
    $('#memoText').val(''); // 초기화
    $('#currentMstIdx').val(mstIdx);

    var act = "open_memo_modal";

    $.ajax({
        type: "post",
        data: {
            act: act,
            mstIdx: mstIdx
        },
        url: "kwd_ajax",
        dataType: "json", 
        success:function(result) {
            if (result.memoText) {
                var safeText = $("<div/>").text(result.memoText).html();
                $('#memoText').val(safeText);
            } else {
                $('#memoText').val(''); // 결과가 없으면 비워줍니다.
            }


            // 현재 날짜와 시간을 포맷에 맞게 생성
            var now = new Date();
            var formattedDate = now.getFullYear() + '-' + (now.getMonth() + 1).toString().padStart(2, '0') + '-' + now.getDate().toString().padStart(2, '0');
            var formattedTime = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');

            // 메모의 끝에 날짜와 시간 정보 추가
            var dateMemoSuffix = "\n[" + formattedDate + " " + formattedTime + " 메모]\n";
            $('#memoText').val(function(index, value) {
                // 값이 있는 경우에만 줄바꿈을 추가합니다.
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
        saveMemo(mstIdx);
    });
}

function saveMemo(mstIdx) {

    var act = "save_memo_modal";
    //var mstIdx = $('#currentMstIdx').val();
    var mstMemo = $('#memoText').val();
    $.ajax({
        type: "post",
        data: {
            act: act,
            mstIdx: mstIdx,
            mstMemo: mstMemo
        },
        url: "kwd_ajax",
        dataType: "json", 
        success:function(result) {
            alert("저장되었습니다.");
            $('#memoModal').modal('hide');


            // 메모 버튼 클래스 업데이트
            var memoButton = $('button[data-target="#memoModal"][onclick="openMemoModal(' + mstIdx + ')"]');
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