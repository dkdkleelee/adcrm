<?php
// This code is based on the php example in naver search ad api github (https://github.com/naver/searchad-apidoc/tree/master/php-sample)

ini_set("default_socket_timeout", 30);
require_once 'restapi.php';

$config = parse_ini_file("api_key.ini");
print_r($config);

function debug($obj, $detail = false)
{
    if (is_array($obj)) {
        echo "size : " . print_r($obj) . "\n";
    }
    if ($detail) {
        print_r($obj);
    }
}

// #. detail log
$DEBUG = false;

$api = new RestApi($config['BASE_URL'], $config['API_KEY'], $config['SECRET_KEY'], $config['CUSTOMER_ID']);

echo "StatReport\n";
// 시작 날짜 ~ 종료 날짜 기간 동안의 키워드 리포트를 다운로드
// 함수로 만들었기 때문에 다른 계정의 API key를 사용하여 적용 가능

$start = "20160701";
$until = "20160703";

function StatReport ($api, $name, $statDt, $end_date) {
$statDt = date ("Ymd", strtotime("-1 day", strtotime($statDt)));
 while (strtotime($statDt) < strtotime($end_date)) {
$reportType = "AD";
$statDt = date ("Ymd", strtotime("+1 day", strtotime($statDt)));
$stat_req = array(
    "reportTp" => $reportType,
    "statDt" => $statDt
);
$response = $api->POST("/stat-reports", $stat_req);
debug($response, $DEBUG);
$reportjobid = $response["reportJobId"];
$status = $response["status"];
echo "registed : reportJobId = $reportjobid, status = " . $status . "\n";
while ($status == 'REGIST' || $status == 'RUNNING' || $status == 'WAITING') {
    echo "waiting a report..\n";
    sleep(60);
    $response = $api->GET("/stat-reports/" . $reportjobid);
    $status = $response["status"];
    echo "check : reportJobId = $reportjobid, status = " . $status . "\n";
}
if ($status == 'BUILT') {
    echo "downloadUrl => " . $response["downloadUrl"] . "\n";
    $api->DOWNLOAD($response["downloadUrl"], "mo_" . $name. "_" . $statDt . ".tsv");
} else if ($status == 'ERROR') {
    echo "failed to build stat report\n";
} else if ($status == 'NONE') {
    echo "report has no data\n";
} else if ($status == 'AGGREGATING') {
    echo "stat aggregation not yet finished\n";
}
}
}

StatReport ($api, "name", $start, $until);

?>
