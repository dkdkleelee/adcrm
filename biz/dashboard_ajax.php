<?php
require_once '../common.php';

// header('Content-type: application/json'); 
// header('Access-Control-Allow-Origin: *');

$act = isset($_POST['act']) ? strip_tags($_POST['act']) : '';

if ($act === "confirm_vaca") {
    
    $vaca_idx = isset($_POST['vaca_idx']) ? strip_tags($_POST['vaca_idx']) : '';

    $sql = "
    update {$g5['crm_vaca_mng']} set
        vaca_status = '2'
    where vaca_idx = '$vaca_idx'
    ";
    isSqlError(sql_query($sql), $sql);

    echo json_encode("상신완료");

} else if ($act === "remove_dashboard") {

    $ptn_idx = isset($_POST['ptn_idx']) ? strip_tags($_POST['ptn_idx']) : '';

    $sql = "
    update {$g5['crm_partner']} set
        ptn_show_dash = 'N'
    where ptn_idx = '$ptn_idx'
    ";
    isSqlError(sql_query($sql), $sql);

    echo json_encode("삭제완료");
} else if ($act === "open_memo_modal") {

    $ptn_idx = isset($_POST['ptn_idx']) ? strip_tags($_POST['ptn_idx']) : '';

    $partner_sql = "
    select *
    from {$g5['crm_partner']} 
    where ptn_idx = {$ptn_idx}
    ";
    $resultOne = sql_fetch($partner_sql);

    if ($resultOne) {
        $result_memo = htmlspecialchars($resultOne['ptn_memo'], ENT_QUOTES, 'UTF-8');
        // 결과를 객체로 만들어 반환
        $response = ['memoText' => $result_memo];
    } else {
        $response = ['memoText' => ''];
    }

    echo json_encode($response);

    
} else if ($act === 'save_memo_modal') {
    $ptn_idx = isset($_POST['ptn_idx']) ? strip_tags($_POST['ptn_idx']) : '';
    $mstMemo = isset($_POST['mstMemo']) ? strip_tags($_POST['mstMemo']) : '';

    $query = "UPDATE {$g5['crm_partner']}  SET ptn_memo = '{$mstMemo}' WHERE ptn_idx = {$ptn_idx}";
    isSqlError(sql_query($query), $query);

    echo json_encode(['status' => 'success', 'message' => '메모가 저장되었습니다.']);

} else if ($act === 'detail_pg_uri_chart') { 

    $ptn_idx = isset($_POST['ptn_idx']) ? strip_tags($_POST['ptn_idx']) : '';

    $detail_pg_uri_sql = "
    select concat(date_format(a.date, '%y-%m-%d'), ' (', 
              case dayofweek(a.date)
                when 1 then '일'
                when 2 then '월'
                when 3 then '화'
                when 4 then '수'
                when 5 then '목'
                when 6 then '금'
                when 7 then '토'
              end, ')') as date,
       coalesce(b.count, 0) as count,
       {$g5['crm_page']}.pg_uri
    from (
    select curdate() - interval 6 day as date
    union all
    select curdate() - interval 5 day
    union all
    select curdate() - interval 4 day
    union all
    select curdate() - interval 3 day
    union all
    select curdate() - interval 2 day
    union all
    select curdate() - interval 1 day
    union all
    select curdate() as date
    ) a
    left join (
    select date(insert_date) as date, land_pg_idx, count(*) as count
    from {$g5['crm_landing']}
    where land_ptn_idx = {$ptn_idx}
        and insert_date >= curdate() - interval 6 day
        and use_yn = 'Y'
        
    group by date(insert_date), land_pg_idx
    ) b on a.date = b.date
    left join {$g5['crm_page']} on {$g5['crm_page']}.page_idx = b.land_pg_idx
    where b.count > 0
    order by a.date desc
    ";
    $detail_pg_uri_list = sql_query($detail_pg_uri_sql);

    $data = array();
    while ($row = sql_fetch_array($detail_pg_uri_list)) {
        $data[] = array(
            'date' => $row['date'],
            'count' => $row['count'],
            'pg_uri' => $row['pg_uri']
        );
    }

    echo json_encode($data);
    exit;


} else if ($act === 'detail_pg_uri_table') { 


    $ptn_idx = isset($_POST['ptn_idx']) ? strip_tags($_POST['ptn_idx']) : '';
    $date = isset($_POST['date']) ? strip_tags($_POST['date']) : '';
    $utm_mode = isset($_POST['utm_mode']) ? ($_POST['utm_mode'] === 'true' ? true : false) : false;


    // uri 코드 방식
    if($utm_mode == false) {

        $detail_pg_uri_sql = "
        select {$g5['crm_page']}.pg_uri,
               ifnull(f_get_mb_name({$g5['crm_page']}.pg_mb_emp), '미지정') as mb_emp_name,
               count({$g5['crm_landing']}.land_idx) as land_count
        from {$g5['crm_landing']}
        join {$g5['crm_page']} on {$g5['crm_landing']}.land_pg_idx = {$g5['crm_page']}.page_idx
        where {$g5['crm_landing']}.land_ptn_idx = '{$ptn_idx}'
        and {$g5['crm_landing']}.insert_date2 = '{$date}'
        and {$g5['crm_landing']}.use_yn = 'Y'
        group by {$g5['crm_landing']}.land_pg_idx
        order by land_count desc
        ";
    } else {
        $detail_pg_uri_sql = "
        select ifnull({$g5['crm_landing']}.utm_source, 'N/A') as pg_uri,
               ifnull(f_get_mb_name({$g5['crm_page']}.pg_mb_emp), '미지정') as mb_emp_name,
               count({$g5['crm_landing']}.land_idx) as land_count
        from {$g5['crm_landing']}
        join {$g5['crm_page']} 
            on {$g5['crm_landing']}.land_pg_idx = {$g5['crm_page']}.page_idx
        where {$g5['crm_landing']}.land_ptn_idx = '{$ptn_idx}'
            and {$g5['crm_landing']}.insert_date2 = '{$date}'
            and {$g5['crm_landing']}.use_yn = 'Y'
        group by {$g5['crm_landing']}.utm_source, {$g5['crm_page']}.pg_mb_emp
        order by land_count desc;
        ";
    }

    $detail_pg_uri_list = sql_query($detail_pg_uri_sql);

    $data = array();
    while ($row = sql_fetch_array($detail_pg_uri_list)) {
        $data[] = array(
            'pg_uri' => $row['pg_uri'],
            'mb_emp_name' => $row['mb_emp_name'],
            'land_count' => $row['land_count']
        );
    }
    
    echo json_encode($data);
    exit;



}
