<?php
require_once '../common.php';
include_once(G5_BIZ_PATH . '/common/access_control.php');

$g5['title'] = "DASH BOARD";
include_once(G5_PATH . '/head.php');

$customerCount = rand(5, 10);
?>

<section class="content">
    <div class="container-fluid">

        <!-- <div class="row">
            <div class="col-12">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">근태</h3>

                        <div class="card-body">

                            <div class="row">
                                <div class="col-sm-12">
                                    <div id="calendar"></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div> -->


        <div class="row">
            <div class="col-12">
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">통합 고객사 차트</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="combinedChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        

    </div>
</section>

<!-- Chart.js 스크립트 추가 -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// 각 고객사별 데이터 세트 준비
const datasets = [];
for (let i = 1; i <= <?php echo $customerCount; ?>; i++) {
    const r = Math.floor(Math.random() * 255);
    const g = Math.floor(Math.random() * 255);
    const b = Math.floor(Math.random() * 255);
    // 랜덤 데이터 생성
    let dataPoints = [];
    for (let j = 0; j < 7; j++) { // 7일 간의 데이터
        dataPoints.push(Math.floor(Math.random() * 100)); // 0부터 100 사이의 랜덤 값
    }
    datasets.push({
        label: `고객사${i} 데이터`,
        backgroundColor: `rgba(${r}, ${g}, ${b}, 0.2)`, // 랜덤 색상
        borderColor: `rgba(${r}, ${g}, ${b}, 1)`,
        data: dataPoints,
    });
}

// 차트 생성
new Chart(document.getElementById('combinedChart'), {
    type: 'line',
    data: {
        labels: ['일', '월', '화', '수', '목', '금', '토'],
        datasets: datasets
    },
    options: {
        responsive: true,
        maintainAspectRatio: true, // aspectRatio를 유지하도록 설정
        aspectRatio: 2, // 기본값, 너비가 높이의 두 배. 이 값을 조정하여 높이 조절
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
</script>

<?php
include_once(G5_PATH . '/tail.php');
?>
