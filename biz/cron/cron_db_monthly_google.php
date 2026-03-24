<?php

$conn = new mysqli("localhost", "gonplan", "gon!@34qwer@@", "gonplan");

if ($conn->connect_error) {
    file_put_contents('/home/withus/withusCRM/data/log/cron_db_monthly_google.log', " DATABASE ERROR " . PHP_EOL, FILE_APPEND | LOCK_EX);
    die("Connection failed: " . $conn->connect_error);
}

// 문자 인코딩 설정
$conn->set_charset("utf8");

// 세션 시작
session_start();

// 지난달 시작일과 종료일 구함
$monthStart = date('Y-m-01', strtotime("first day of last month"));
$monthEnd = date('Y-m-t', strtotime("last day of last month"));
$monthLabel = date('Y-m', strtotime("first day of last month")); // YYYY-MM 형식

// 4팀 영업부 데이터 조회 (월별 기준)
$team7_sql = "
select c.ptn_nm as ptn_nm
     , f_get_mb_name(a.land_empno) as mb_name
     , count(a.land_idx) as monthly_cnt
from {$g5['crm_landing']} a
left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx
left join {$g5['crm_partner']} c on a.land_ptn_idx = c.ptn_idx
where insert_date2 BETWEEN '{$monthStart}' AND '{$monthEnd}'
and a.use_yn = 'Y'
and a.land_deptno = 7
and a.land_ptn_idx is not null
group by c.ptn_nm, a.land_empno, f_get_mb_name(a.land_empno)
union all
select c.ptn_nm as ptn_nm
     , f_get_mb_name(a.land_empno) as mb_name
     , 0 as monthly_cnt
from {$g5['crm_landing']} a
left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx
left join {$g5['crm_partner']} c on a.land_ptn_idx = c.ptn_idx
where a.land_empno in (
        select distinct a2.land_empno
        from {$g5['crm_landing']} a2
        where a2.insert_date2 >= curdate() - interval 1 month
        and a2.use_yn = 'Y'
        and a2.land_deptno = 7
        and a2.land_ptn_idx is not null
)
and a.land_ptn_idx in (
    select distinct a2.land_ptn_idx
    from {$g5['crm_landing']} a2
    where a2.insert_date2 >= curdate() - interval 1 month
    and a2.use_yn = 'Y'
    and a2.land_deptno = 7
    and a2.land_ptn_idx is not null
)
and a.land_deptno = 7
and a.land_ptn_idx is not null
and a.insert_date2 >= curdate() - interval 1 month
and not exists (
    select 1 
    from {$g5['crm_landing']} a3
    where 
        a3.land_ptn_idx = a.land_ptn_idx
        and a3.land_empno = a.land_empno
        and date(a3.insert_date) BETWEEN '{$monthStart}' AND '{$monthEnd}'
        and a3.use_yn = 'Y'
)
group by c.ptn_nm, a.land_empno, f_get_mb_name(a.land_empno)
order by ptn_nm, mb_name;
";
$resultList1 = mysqli_query($conn, $team7_sql);

// 4팀 법인 데이터 조회 (월별 기준)
$team6_sql = "
SELECT ptn_nm
     , mb_name
     , SUM(monthly_cnt) AS monthly_cnt
FROM (
    SELECT c.ptn_nm AS ptn_nm
         , '법인' AS mb_name
         , COUNT(a.land_idx) AS monthly_cnt
    FROM {$g5['crm_landing']} a
    LEFT JOIN {$g5['crm_partner']} c ON a.land_ptn_idx = c.ptn_idx
    WHERE insert_date2 BETWEEN '{$monthStart}' AND '{$monthEnd}'
      AND a.use_yn = 'Y'
      AND a.land_deptno = 6
      AND a.land_ptn_idx IS NOT NULL
      AND NOT (a.land_ptn_idx IN (166, 458) AND a.land_empno = 1030)
    GROUP BY a.land_ptn_idx 
    UNION ALL
    SELECT c.ptn_nm AS ptn_nm
         , '법인' AS mb_name
         , 0 AS monthly_cnt
    FROM {$g5['crm_landing']} a
    LEFT JOIN {$g5['crm_partner']} c ON a.land_ptn_idx = c.ptn_idx
    WHERE a.land_empno IN (
            SELECT DISTINCT a2.land_empno
            FROM {$g5['crm_landing']} a2
            WHERE a2.insert_date2 >= CURDATE() - INTERVAL 1 MONTH
              AND a2.use_yn = 'Y'
              AND a2.land_deptno = 6
              AND a2.land_ptn_idx IS NOT NULL
              AND NOT (a2.land_ptn_idx IN (166, 458) AND a2.land_empno = 1030)
        )
      AND a.land_ptn_idx IN (
            SELECT DISTINCT a2.land_ptn_idx
            FROM {$g5['crm_landing']} a2
            WHERE a2.insert_date2 >= CURDATE() - INTERVAL 1 MONTH
              AND a2.use_yn = 'Y'
              AND a2.land_deptno = 6
              AND a2.land_ptn_idx IS NOT NULL
              AND NOT (a2.land_ptn_idx IN (166, 458) AND a2.land_empno = 1030)
        )
      AND a.land_deptno = 6
      AND a.land_ptn_idx IS NOT NULL
      AND a.insert_date2 >= CURDATE() - INTERVAL 1 MONTH
      AND NOT EXISTS (
          SELECT 1 
          FROM {$g5['crm_landing']} a3
          WHERE a3.land_ptn_idx = a.land_ptn_idx
            AND a3.land_empno = a.land_empno
            AND DATE(a3.insert_date) BETWEEN '{$monthStart}' AND '{$monthEnd}'
            AND a3.use_yn = 'Y'
            AND NOT (a3.land_ptn_idx IN (166, 458) AND a3.land_empno = 1030)
      )
    GROUP BY a.land_ptn_idx
) AS combined_result
GROUP BY ptn_nm, mb_name
ORDER BY ptn_nm;
";
$resultList2 = mysqli_query($conn, $team6_sql);

// 4팀 법인 예외 데이터 조회 (월별 기준)
$team6_except_sql = "
SELECT c.ptn_nm AS ptn_nm,
       f_get_mb_name(a.land_empno) AS mb_name,
       COUNT(a.land_idx) AS monthly_cnt
FROM {$g5['crm_landing']} a
LEFT JOIN {$g5['crm_page']} b ON a.land_pg_idx = b.page_idx
LEFT JOIN {$g5['crm_partner']} c ON a.land_ptn_idx = c.ptn_idx
WHERE insert_date2 BETWEEN '{$monthStart}' AND '{$monthEnd}'
  AND a.use_yn = 'Y'
  AND a.land_deptno = 6
  AND a.land_ptn_idx IN (166, 458)
  AND a.land_empno = 1030
GROUP BY c.ptn_nm, a.land_empno, f_get_mb_name(a.land_empno)
order by ptn_nm, mb_name;
";
$resultList3 = mysqli_query($conn, $team6_except_sql);

function batchAppendToSheet($service, $spreadsheetId, $sheet_name, $values) {
    $range = $sheet_name . '!A:D'; // A-D 열에 데이터 추가
    $body = new Google_Service_Sheets_ValueRange([
        'values' => $values
    ]);
    $params = [
        'valueInputOption' => 'RAW'
    ];

    try {
        $result = $service->spreadsheets_values->append(
            $spreadsheetId,
            $range,
            $body,
            $params
        );
    } catch (Exception $e) {
        file_put_contents('/home/withus/withusCRM/data/log/cron_db_monthly_google.log', "[" . date("Y-m-d h:i:s") . "] Error appending to sheet: " . $e->getMessage() . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

require_once '/home/devgon/landing/google-api/vendor/autoload.php';

// Google Client 설정
$client = new Google_Client();
$client->setAuthConfig('/home/devgon/landing/google-api/google_gonplan.json'); // 서비스 계정 키 파일 경로
$client->addScope(Google_Service_Sheets::SPREADSHEETS);
$client->setAccessType('offline');

$service = new Google_Service_Sheets($client);

$spreadsheetId = "1kO706YgksfA3ARjp4dF4fc8ZiGqRSwzMRk7Pl58_9io"; // 스프레드시트 ID
$sheet_name = "정산용";
$dataForSheet = [];
$mergedList = [];

while ($row = mysqli_fetch_assoc($resultList1)) {
    $mb_name = $row['mb_name'];
    $ptn_nm = $row['ptn_nm'];
    $monthly_cnt = $row['monthly_cnt'];
    $mergedList[] = [$monthLabel, $mb_name, $ptn_nm, $monthly_cnt];
}

while ($row = mysqli_fetch_assoc($resultList3)) {
    $mb_name = $row['mb_name'];
    $ptn_nm = $row['ptn_nm'];
    $monthly_cnt = $row['monthly_cnt'];
    $mergedList[] = [$monthLabel, $mb_name, $ptn_nm, $monthly_cnt];
}

// resultList1 + resultList3 정렬
setlocale(LC_ALL, 'ko_KR.UTF-8'); 
usort($mergedList, function($a, $b) {
    return strcoll($a[1], $b[1]); 
});
foreach ($mergedList as $row) {
    $dataForSheet[] = $row;
}

//법인
while ($row = mysqli_fetch_assoc($resultList2)) {
    $mb_name = $row['mb_name'];
    $ptn_nm = $row['ptn_nm'];
    $monthly_cnt = $row['monthly_cnt'];
    $dataForSheet[] = [$monthLabel, $mb_name, $ptn_nm, $monthly_cnt];
}

if (!empty($dataForSheet)) {
    batchAppendToSheet($service, $spreadsheetId, $sheet_name, $dataForSheet);
}

mysqli_close($conn);
file_put_contents("/home/withus/withusCRM/data/log/cron_db_monthly_google.log", "[" . date("Y-m-d h:i:s") . "]----- Done -----" . PHP_EOL, FILE_APPEND | LOCK_EX);

?>
