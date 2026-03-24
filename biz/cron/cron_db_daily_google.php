<?php

$conn = new mysqli("localhost", "gonplan", "gon!@34qwer@@", "gonplan");

if ($conn->connect_error) {
    file_put_contents('/home/withus/withusCRM/data/log/cron_db_daily_google.log', " DATABASE ERROR " . PHP_EOL, FILE_APPEND | LOCK_EX);
    die("Connection failed: " . $conn->connect_error);
}

// 문자 인코딩 설정
$conn->set_charset("utf8");

// 세션 시작
session_start();

$yestDt = date('Y-m-d', $_SERVER['REQUEST_TIME']-86400);

$team7_sql = "
select c.ptn_nm as ptn_nm
     , f_get_mb_name(a.land_empno) as sheet_name
     , count(a.land_idx) as daily_cnt
from {$g5['crm_landing']} a
left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx
left join {$g5['crm_partner']} c on a.land_ptn_idx = c.ptn_idx
where insert_date2 = '{$yestDt}'
and a.use_yn = 'Y'
and a.land_deptno = 7
and a.land_ptn_idx is not null
group by c.ptn_nm, a.land_empno, f_get_mb_name(a.land_empno)
union all
select c.ptn_nm as ptn_nm
     , f_get_mb_name(a.land_empno) as sheet_name
     , 0 as daily_cnt
from {$g5['crm_landing']} a
left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx
left join {$g5['crm_partner']} c on a.land_ptn_idx = c.ptn_idx
where a.land_empno in (
        select distinct a2.land_empno
        from {$g5['crm_landing']} a2
        where a2.insert_date2 >= curdate() - interval 7 day
        and a2.use_yn = 'Y'
        and a2.land_deptno = 7
        and a2.land_ptn_idx is not null
)
and a.land_ptn_idx in (
	select distinct a2.land_ptn_idx
	from {$g5['crm_landing']} a2
	where a2.insert_date2 >= curdate() - interval 7 day
	and a2.use_yn = 'Y'
	and a2.land_deptno = 7
	and a2.land_ptn_idx is not null
)
and a.land_deptno = 7
and a.land_ptn_idx is not null
and a.insert_date2 >= curdate() - interval 7 day
and not exists (
    select 1 
    from {$g5['crm_landing']} a3
    where 
        a3.land_ptn_idx = a.land_ptn_idx
        and a3.land_empno = a.land_empno
        and date(a3.insert_date) = '{$yestDt}'
        and a3.use_yn = 'Y'
)
group by c.ptn_nm, a.land_empno, f_get_mb_name(a.land_empno)
order by ptn_nm, sheet_name;
";
$resultList1 = mysqli_query($conn, $team7_sql);

$team6_sql = "
SELECT ptn_nm
     , ptn_type
     , SUM(daily_cnt) AS daily_cnt
FROM (
    SELECT c.ptn_nm AS ptn_nm
         , '법인' AS ptn_type
         , COUNT(a.land_idx) AS daily_cnt
    FROM {$g5['crm_landing']} a
    LEFT JOIN {$g5['crm_partner']} c ON a.land_ptn_idx = c.ptn_idx
    WHERE insert_date2 = '{$yestDt}'
      AND a.use_yn = 'Y'
      AND a.land_deptno = 6
      AND a.land_ptn_idx IS NOT NULL
      AND NOT (a.land_ptn_idx IN (166, 458) AND a.land_empno = 1030)
    GROUP BY a.land_ptn_idx 
    UNION ALL
    SELECT c.ptn_nm AS ptn_nm
         , '법인' AS ptn_type
         , 0 AS daily_cnt
    FROM {$g5['crm_landing']} a
    LEFT JOIN {$g5['crm_partner']} c ON a.land_ptn_idx = c.ptn_idx
    WHERE a.land_empno IN (
            SELECT DISTINCT a2.land_empno
            FROM {$g5['crm_landing']} a2
            WHERE a2.insert_date2 >= CURDATE() - INTERVAL 7 DAY
              AND a2.use_yn = 'Y'
              AND a2.land_deptno = 6
              AND a2.land_ptn_idx IS NOT NULL
              AND NOT (a2.land_ptn_idx IN (166, 458) AND a2.land_empno = 1030)
        )
      AND a.land_ptn_idx IN (
            SELECT DISTINCT a2.land_ptn_idx
            FROM {$g5['crm_landing']} a2
            WHERE a2.insert_date2 >= CURDATE() - INTERVAL 7 DAY
              AND a2.use_yn = 'Y'
              AND a2.land_deptno = 6
              AND a2.land_ptn_idx IS NOT NULL
              AND NOT (a2.land_ptn_idx IN (166, 458) AND a2.land_empno = 1030)
        )
      AND a.land_deptno = 6
      AND a.land_ptn_idx IS NOT NULL
      AND a.insert_date2 >= CURDATE() - INTERVAL 7 DAY
      AND NOT EXISTS (
          SELECT 1 
          FROM {$g5['crm_landing']} a3
          WHERE a3.land_ptn_idx = a.land_ptn_idx
            AND a3.land_empno = a.land_empno
            AND DATE(a3.insert_date) = '{$yestDt}'
            AND a3.use_yn = 'Y'
            AND NOT (a3.land_ptn_idx IN (166, 458) AND a3.land_empno = 1030)
      )
    GROUP BY a.land_ptn_idx
) AS combined_result
GROUP BY ptn_nm, ptn_type
ORDER BY ptn_nm;
";
$resultList2 = mysqli_query($conn, $team6_sql);



$team6_except_sql = "
select c.ptn_nm as ptn_nm,
       f_get_mb_name(a.land_empno) as sheet_name,
       count(a.land_idx) as daily_cnt
from {$g5['crm_landing']} a
left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx
left join {$g5['crm_partner']} c on a.land_ptn_idx = c.ptn_idx
where insert_date2 = '{$yestDt}'
  and a.use_yn = 'Y'
  and a.land_deptno = 6
  and a.land_ptn_idx in (166, 458)
  and a.land_empno = 1030
group by c.ptn_nm, a.land_empno, f_get_mb_name(a.land_empno)
union all
select c.ptn_nm as ptn_nm,
       f_get_mb_name(a.land_empno) as sheet_name,
       0 as daily_cnt
from {$g5['crm_landing']} a
left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx
left join {$g5['crm_partner']} c on a.land_ptn_idx = c.ptn_idx
where a.land_empno = 1030
  and a.land_ptn_idx in (166, 458)
  and a.land_deptno = 6
  and a.insert_date2 >= curdate() - interval 7 day
  and not exists (
      select 1 
      from {$g5['crm_landing']} a3
      where a3.land_ptn_idx = a.land_ptn_idx
        and a3.land_empno = a.land_empno
        and date(a3.insert_date) = '{$yestDt}'
        and a3.use_yn = 'Y'
  )
group by c.ptn_nm, a.land_empno, f_get_mb_name(a.land_empno)
order by ptn_nm, sheet_name;
";
$resultList3 = mysqli_query($conn, $team6_except_sql);




$lotto_sql = "
select 
    date(a.insert_date) as insert_date,
    b.pg_uri as pg_uri,
    count(*) as cnt
from {$g5['crm_landing']} a
left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx
left join {$g5['crm_partner']} c on a.land_ptn_idx = c.ptn_idx
where 
    date(a.insert_date) = date_sub(curdate(), interval 1 day)
    and a.use_yn = 'Y'
    and c.ptn_nm like '%로또분석%'
group by date(a.insert_date), b.pg_uri
order by cnt desc;
";
$resultList4 = mysqli_query($conn, $lotto_sql);



require_once '/home/devgon/landing/google-api/vendor/autoload.php';
//require_once 'D:/Develop/workspace/php/landing/google-api/vendor/autoload.php';

// Google Client 설정
$client = new Google_Client();

$client->setAuthConfig('/home/devgon/landing/google-api/google_gonplan.json'); // 서비스 계정 키 파일 경로
//$client->setAuthConfig('D:/Develop/workspace/php/landing/google-api/google_gonplan.json'); // 서비스 계정 키 파일 경로

$client->addScope(Google_Service_Sheets::SPREADSHEETS);
$client->setAccessType('offline');

$service = new Google_Service_Sheets($client);

$spreadsheetId = "1kO706YgksfA3ARjp4dF4fc8ZiGqRSwzMRk7Pl58_9io"; // 스프레드시트 ID

function batchAppendToSheet($service, $spreadsheetId, $sheet_name, $values) {
    $range = $sheet_name . '!A:C';
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
        file_put_contents('/home/withus/withusCRM/data/log/cron_db_daily_google.log', "[" . date("Y-m-d h:i:s") . "] Error appending to sheet: " . $e->getMessage() . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

$dataForSheets = [];

while ($row = mysqli_fetch_assoc($resultList1)) {
    $daily_cnt = $row['daily_cnt'];
    $ptn_nm = $row['ptn_nm'];
    $sheet_name = isset($row['sheet_name']) ? $row['sheet_name'] : 'Unknown';

    if ($ptn_nm === null) {
        continue;
    }

    if (!isset($dataForSheets[$sheet_name])) {
        $dataForSheets[$sheet_name] = [];
    }

    $dataForSheets[$sheet_name][] = [$yestDt, $ptn_nm, $daily_cnt];
}

while ($row2 = mysqli_fetch_assoc($resultList2)) {
    $daily_cnt = $row2['daily_cnt'];
    $ptn_nm = $row2['ptn_nm'];
    $sheet_name = '법인';

    if ($ptn_nm === null) {
        continue;
    }

    if (!isset($dataForSheets[$sheet_name])) {
        $dataForSheets[$sheet_name] = [];
    }

    $dataForSheets[$sheet_name][] = [$yestDt, $ptn_nm, $daily_cnt];
}

while ($row = mysqli_fetch_assoc($resultList3)) {
    $daily_cnt = $row['daily_cnt'];
    $ptn_nm = $row['ptn_nm'];
    $sheet_name = isset($row['sheet_name']) ? $row['sheet_name'] : 'Unknown';

    if ($ptn_nm === null) {
        continue;
    }

    if (!isset($dataForSheets[$sheet_name])) {
        $dataForSheets[$sheet_name] = [];
    }

    $dataForSheets[$sheet_name][] = [$yestDt, $ptn_nm, $daily_cnt];
}

while ($row = mysqli_fetch_assoc($resultList4)) {
    $insert_date = $row['insert_date'];
    $pg_uri = $row['pg_uri'];
    $cnt = $row['cnt'];

    $sheet_name = "로또분석연구소";

    if (!isset($dataForSheets[$sheet_name])) {
        $dataForSheets[$sheet_name] = [];
    }
    $dataForSheets[$sheet_name][] = [$insert_date, $pg_uri, $cnt];
}


foreach ($dataForSheets as $sheet_name => $values) {
    batchAppendToSheet($service, $spreadsheetId, $sheet_name, $values);
}

mysqli_close($conn);
file_put_contents("/home/withus/withusCRM/data/log/cron_db_daily_google.log", "[" . date("Y-m-d h:i:s") . "]----- ] -----" . PHP_EOL, FILE_APPEND | LOCK_EX);

?>

