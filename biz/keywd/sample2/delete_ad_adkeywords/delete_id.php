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

echo "DELETE Ad\n";

// csv파일 내 소재ID를 사용하여 Ad소재 삭제
$csv = array_map('str_getcsv', file('file.csv'));

 for ($i = 0; $i < count($csv); $i++) {
    $api->DELETE('/ncc/ads/' . $csv[$i][0]);
 }

// csv파일 내 키워드ID를 사용하여 Ad키워드 삭제
echo "DELETE AdKeyword\n";

$csv = array_map('str_getcsv', file('file.csv'));

 for ($i = 0; $i < count($csv); $i++) {
    $api->DELETE('/ncc/keywords/' . $csv[$i][0]);
 }

?>

