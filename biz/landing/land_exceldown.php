<?php
require_once '../../common.php';

// ============================================
// [보안 로직 추가] 
// 1. IP를 먼저 확인. 허용 IP면 무조건 패스
// 2. 허용 IP가 아닐 경우, '문자인증 통과 세션'이 있는지 검사
// ============================================
$client_ip = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'];
$allowed_ips = ['218.52.175.32', '175.195.214.44', '127.0.0.1'];

if (!in_array($client_ip, $allowed_ips)) {
    if (
        empty($_SESSION['ss_excel_download_passed']) ||
        $_SESSION['ss_excel_download_passed'] !== true ||
        empty($_SESSION['ss_excel_download_passed_until']) ||
        $_SESSION['ss_excel_download_passed_until'] < time()
    ) {
        alert("비정상적인 접근이거나 비밀번호 재확인이 완료되지 않았습니다.");
        exit;
    }

    // 1회용 세션 제거
    unset($_SESSION['ss_excel_download_passed']);
    unset($_SESSION['ss_excel_download_passed_until']);
}

// ============================================

$curr_yn      = isset($_POST['curr_yn']) ? strip_tags(clean_xss_attributes($_POST['curr_yn'])) : '';
$rdo_data  = isset($_POST['rdo_data']) ? strip_tags(clean_xss_attributes($_POST['rdo_data'])) : alert("파트너필수입력");

$curr_datetime = isset($_POST['curr_datetime']) ? strip_tags(clean_xss_attributes($_POST['curr_datetime'])) : '';
$ptn_idx    = isset($_POST['ptn_idx_xls']) ? strip_tags(clean_xss_attributes($_POST['ptn_idx_xls'])) : '';
$pg_uri    = isset($_POST['pg_uri']) ? strip_tags(clean_xss_attributes( implode(", ", $_POST['pg_uri'])) ): '';
$fromDate   = isset($_POST['fromDate']) ? strip_tags(clean_xss_attributes($_POST['fromDate'])) : '';
$toDate   = isset($_POST['toDate']) ? strip_tags(clean_xss_attributes($_POST['toDate'])) : '';

$level = $member['mb_level'];
if ($level <= 5) {
    if (empty($ptn_idx) && empty($pg_uri)) {
      alert("고객사 또는 페이지를 반드시 선택해야 합니다.");
    }
}

$hist_memo = "일자:{$fromDate}~{$toDate} || ";

if($ptn_idx != "") {
    $getPtnSql= "
    select max(b.ptn_nm) as ptn_nm
    from {$g5['crm_page']} a
    left join {$g5['crm_partner']} b on a.pg_ptn_idx = b.ptn_idx  
    where b.ptn_idx  = {$ptn_idx}
    ";
} else if($pg_uri != "") {
    $getPtnSql= "
    select group_concat(distinct b.ptn_nm order by b.ptn_nm separator ', ') as ptn_nm
         , group_concat(distinct a.pg_uri order by a.pg_uri separator ', ') as pg_uri_nm
    from {$g5['crm_page']} a
    left join {$g5['crm_partner']} b on a.pg_ptn_idx = b.ptn_idx  
    where a.page_idx in ({$pg_uri})
    order by b.ptn_nm asc
    ";
}

if($getPtnSql == NULL || $getPtnSql == ""){
    $partner_name = "전체";
    $hist_memo .= "조건:전체 || ";
} else {
    $ptn_info = sql_fetch($getPtnSql);
    $partner_name = $ptn_info['ptn_nm'];
    $pg_uri_nm = $ptn_info['pg_uri_nm'];

    $partner_name = str_replace(', ' , '-', $partner_name);

    if($ptn_idx != "") {
        $hist_memo .= "조건:{$partner_name} || ";
    } else if($pg_uri != "") {
        $hist_memo .= "조건:{$pg_uri_nm} || ";
    }
}


$add_cond = "";

if($member['mb_deptno'] != "9") {
    if($member['mb_level'] <= 6) {
        if($member['mb_level'] == 4) {
            $add_cond = "and b.pg_mb_emp = {$member['mb_no']}";
        } else {
            $add_cond = "and b.pg_deptno = {$member['mb_deptno']}";
        }
    }
}


$append_title = "";
if($curr_yn == "on") {
    $append_title = "취합양식_";
    $hist_memo .= "엑셀취합양식";
} else {
    $append_title = "일반양식_";
    $hist_memo .= "엑셀일반양식";
}


$append_sql = "";

if($curr_yn == "on") {

    if($rdo_data == "collect") {

        $mainSql = "
        select  b.pg_uri
              , group_concat(CONVERT(AES_DECRYPT(UNHEX(a.tel), 'withus_secret_key') USING UTF8) order by a.insert_date desc) as tel 
              , group_concat(a.name order by a.insert_date desc) as name 
              , group_concat(a.option1 order by a.insert_date desc) as option1
              , group_concat(a.option2 order by a.insert_date desc) as option2
              , group_concat(a.option3 order by a.insert_date desc) as option3
              , group_concat(a.option4 order by a.insert_date desc) as option4
              , group_concat(a.option5 order by a.insert_date desc) as option5
              , group_concat(a.option6 order by a.insert_date desc) as option6
              , group_concat(a.option7 order by a.insert_date desc) as option7
              , group_concat(a.option8 order by a.insert_date desc) as option8
              , group_concat(a.option9 order by a.insert_date desc) as option9
              , group_concat(a.land_memo order by a.insert_date desc) as land_memo
              , count(b.pg_uri) as cnt
              , b.pg_chk_name 
              , b.pg_chk_data1
              , b.pg_chk_data2
              , b.pg_chk_data3
              , b.pg_chk_data4
              , b.pg_chk_data5
              , b.pg_chk_data6
              , b.pg_chk_data7
              , b.pg_chk_data8
              , b.pg_chk_data9
        from {$g5['crm_landing']} a
        left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx 
        where a.land_ptn_idx = {$ptn_idx}
        and a.land_used_data = 'N'
        and a.use_yn = 'Y'
        {$add_cond}
        and a.insert_date <= '{$curr_datetime}'
        group by b.pg_uri 
        order by pg_uri asc
        ";
        $mainList = sql_query($mainSql);
    
        $table_header = "
<table border='1'>
<thead>
</thead>
<tbody>
";

        $table_body = "";
        $array = array();
        $tr_count = 0;

        for ($i = 1; $main = sql_fetch_array($mainList); $i++) {
            $row['pg_uri'] = $main['pg_uri'];
            $row['tel'] = explode (",", $main['tel']);
            //$row['pg_chk_name'] = $main['pg_chk_name'] != "" ? explode (",", $main['name']) : "";

            if($main['pg_chk_name'] == "1") {
                $row['pg_chk_name'] = $main['pg_chk_name'] != "" ? explode (",", $main['name']) : "";
            } else {
                $row['pg_chk_name'] = "";
            }

            $row['pg_chk_data1'] = $main['pg_chk_data1'] != "" ? explode (",", $main['option1']) : "";
            $row['pg_chk_data2'] = $main['pg_chk_data2'] != "" ? explode (",", $main['option2']) : "";
            $row['pg_chk_data3'] = $main['pg_chk_data3'] != "" ? explode (",", $main['option3']) : "";
            $row['pg_chk_data4'] = $main['pg_chk_data4'] != "" ? explode (",", $main['option4']) : "";
            $row['pg_chk_data5'] = $main['pg_chk_data5'] != "" ? explode (",", $main['option5']) : "";
            $row['pg_chk_data6'] = $main['pg_chk_data6'] != "" ? explode (",", $main['option6']) : "";
            $row['pg_chk_data7'] = $main['pg_chk_data7'] != "" ? explode (",", $main['option7']) : "";
            $row['pg_chk_data8'] = $main['pg_chk_data8'] != "" ? explode (",", $main['option8']) : "";
            $row['pg_chk_data9'] = $main['pg_chk_data9'] != "" ? explode (",", $main['option9']) : "";
            $row['land_memo'] = $main['land_memo'] == "1" ? explode (",", $main['land_memo']) : "";

            array_push( $array, $row );

            if($main['cnt'] > $tr_count) {
                $tr_count = (int)$main['cnt'];
            }
        }
        
        // $tr_count = count( (array) $array );
        // $max_int_v = max($array);

        if($tr_count == 0) {
            alert("엑셀 다운로드 할 최신화 데이터가 존재하지않습니다.");
        }
        $i = 0;

        $table_body .= "<tr>";
        for($j=0; $j < count( (array) $array ); $j++) {

            if($array[$j]['pg_chk_name'] != "") {
                $table_body .= "<td>이름</td>\n";
            }
            
            $table_body .= "<td>".$array[$j]['pg_uri']."</td>\n";
            
            if($array[$j]['pg_chk_data1'] != "") {
                $table_body .= "<td>옵션1</td>\n";
            }
            if($array[$j]['pg_chk_data2'] != "") {
                $table_body .= "<td>옵션2</td>\n";
            }
            if($array[$j]['pg_chk_data3'] != "") {
                $table_body .= "<td>옵션3</td>\n";
            }
            if($array[$j]['pg_chk_data4'] != "") {
                $table_body .= "<td>옵션4</td>\n";
            }
            if($array[$j]['pg_chk_data5'] != "") {
                $table_body .= "<td>옵션5</td>\n";
            }
            if($array[$j]['pg_chk_data6'] != "") {
                $table_body .= "<td>옵션6</td>\n";
            }
            if($array[$j]['pg_chk_data7'] != "") {
                $table_body .= "<td>옵션7</td>\n";
            }
            if($array[$j]['pg_chk_data8'] != "") {
                $table_body .= "<td>옵션8</td>\n";
            }
            if($array[$j]['pg_chk_data9'] != "") {
                $table_body .= "<td>옵션9</td>\n";
            }
            if($array[$j]['land_memo'] != "") {
                $table_body .= "<td>메모</td>\n";
            }
            $table_body .= "<td></td>\n";
        }

        $table_body .= "</tr>";
        while ($i < $tr_count){ 
            $table_body .= "<tr>";
            for($j=0; $j < count( (array) $array ); $j++) {

                if($array[$j]['pg_chk_name'] != "") {
                    $table_body .= "<td>".$array[$j]['pg_chk_name'][$i]."</td>\n";
                }
                
                $table_body .= "<td>".$array[$j]['tel'][$i]."</td>\n";
                
                if($array[$j]['pg_chk_data1'] != "") {
                    $table_body .= "<td>".$array[$j]['pg_chk_data1'][$i]."</td>\n";
                }
                if($array[$j]['pg_chk_data2'] != "") {
                    $table_body .= "<td>".$array[$j]['pg_chk_data2'][$i]."</td>\n";
                }
                if($array[$j]['pg_chk_data3'] != "") {
                    $table_body .= "<td>".$array[$j]['pg_chk_data3'][$i]."</td>\n";
                }
                if($array[$j]['pg_chk_data4'] != "") {
                    $table_body .= "<td>".$array[$j]['pg_chk_data4'][$i]."</td>\n";
                }
                if($array[$j]['pg_chk_data5'] != "") {
                    $table_body .= "<td>".$array[$j]['pg_chk_data5'][$i]."</td>\n";
                }
                if($array[$j]['pg_chk_data6'] != "") {
                    $table_body .= "<td>".$array[$j]['pg_chk_data6'][$i]."</td>\n";
                }
                if($array[$j]['pg_chk_data7'] != "") {
                    $table_body .= "<td>".$array[$j]['pg_chk_data7'][$i]."</td>\n";
                }
                if($array[$j]['pg_chk_data8'] != "") {
                    $table_body .= "<td>".$array[$j]['pg_chk_data8'][$i]."</td>\n";
                }
                if($array[$j]['pg_chk_data9'] != "") {
                    $table_body .= "<td>".$array[$j]['pg_chk_data9'][$i]."</td>\n";
                }
                $table_body .= "<td></td>\n";

            }
            $table_body .= "</tr>";
            $i++;
        }

        $table_body .= "
        </tbody>
        </table>
       ";

        $EXCEL_STR = $table_header . $table_body;
        

    } else if($rdo_data == "normal") {

        $sql = "
        select a.land_idx 
            , a.land_pg_idx 
            , a.land_ptn_idx
            , c.ptn_nm 
            , b.pg_domain
            , b.pg_uri
            , b.pg_inflow
            , a.name 
            , CONVERT(AES_DECRYPT(UNHEX(a.tel), 'withus_secret_key') USING UTF8) as tel
            , a.option1
            , a.option2 
            , a.option3 
            , a.option4 
            , a.option5 
            , a.option6 
            , a.option7 
            , a.option8 
            , a.option9 
            , a.land_memo
            , a.inflow_path
            , a.inflow_env
            , a.utm_source
            , a.user_agent
            , a.api_send_yn
            , a.land_used_data
            , a.ip
            , a.city
            , a.region
            , a.insert_date
        from {$g5['crm_landing']}      a
        left join {$g5['crm_page']}    b on a.land_pg_idx = b.page_idx 
        left join {$g5['crm_partner']} c on a.land_ptn_idx = c.ptn_idx 
        where 1=1
        and a.land_used_data = 'N'
        and a.insert_date <= '{$curr_datetime}'
        and a.use_yn = 'Y'
        {$add_cond}
        and a.land_ptn_idx = {$ptn_idx}
        order by land_idx desc";
       
        $result = sql_query($sql);
        $result_cnt = mysqli_num_rows($result);
        if($result_cnt == 0) {
            alert("엑셀 다운로드 할 최신화 데이터가 존재하지않습니다.");
        }
        
        $EXCEL_STR = "
        <table border='1'>
        <tr>
        <td>NO</td>
        <td>이름</td>
        <td>코드</td>
        <td>연락처</td>
        <td>옵션1</td>
        <td>옵션2</td>
        <td>옵션3</td>
        <td>옵션4</td>
        <td>옵션5</td>
        <td>옵션6</td>
        <td>옵션7</td>
        <td>옵션8</td>
        <td>옵션9</td>
        <td>메모</td>
        <td>고객사</td>
        <td>유입루트</td>
        <td>유입채널</td>
        <td>P/M</td>
        <td>UTM</td>
        <td>브라우저</td>
        <td>API전송</td>
        <td>아이피</td>
        <td>위치</td>
        <td>지역</td>
        <td>입력일시</td>
        </tr>";
        

        $i = 1;
        while ($res = sql_fetch_array( $result )) {
            $EXCEL_STR .= "  
            <tr>  
                <td>".$i."</td>  
                <td>".$res['name']."</td>
                <td>".$res['pg_uri']."</td>
                <td>".$res['tel']."</td>  
                <td>".$res['option1']."</td>  
                <td>".$res['option2']."</td>  
                <td>".$res['option3']."</td>  
                <td>".$res['option4']."</td>  
                <td>".$res['option5']."</td>  
                <td>".$res['option6']."</td>  
                <td>".$res['option7']."</td>
                <td>".$res['option8']."</td>
                <td>".$res['option9']."</td>
                <td>".$res['land_memo']."</td>  
                <td>".$res['ptn_nm']."</td>
                <td>".$res['inflow_path']."</td>
                <td>".$res['pg_inflow']."</td>
                <td>".$res['inflow_env']."</td>
                <td>".$res['utm_source']."</td>
                <td>".$res['user_agent']."</td>
                <td>".$res['api_send_yn']."</td>
                <td>".$res['ip']."</td>
                <td>".$res['region']."</td>
                <td>".$res['city']."</td>
                <td>".$res['insert_date']."</td>  
            </tr>";  
        
            $i = $i + 1;
        }

        $EXCEL_STR .= "</table>";

    }

    $last_idx_sql = "
    select a.land_idx
        , min(land_idx) as min
        , max(land_idx) as max
    from {$g5['crm_landing']} a
    left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx 
    where a.land_ptn_idx = {$ptn_idx}
    and a.land_used_data = 'N'
    and a.use_yn = 'Y'
    {$add_cond}
    and a.insert_date <= '{$curr_datetime}'
    order by a.land_idx desc
    ";
    $data = sql_fetch($last_idx_sql);
    $min = $data['min'];
    $max = $data['max'];

    if(isset($min) && isset($max)) {
        //update
        $upd_sql = "
        update {$g5['crm_landing']} a inner join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx set 
            a.land_used_data = 'Y'
          , a.update_date = now()
          , a.update_user = '{$member['mb_id']}'
          , a.update_log = '최신화1'
        where a.use_yn = 'Y'
        and a.land_used_data = 'N'
        and a.land_ptn_idx = {$ptn_idx} 
        and a.land_idx >= {$min} and a.land_idx <= {$max}
        and a.insert_date <= '{$curr_datetime}'
        {$add_cond}
        ";
        isSqlError(sql_query($upd_sql), $upd_sql);
    }

    //echo "<meta http-equiv='Content-Type' content='text/html; charset=euc-kr'> ";    
    header("Content-type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename={$partner_name}_{$append_title}엑셀다운로드_".date("Ymd_Hms").".xls" );
    header("Content-Description: PHP4 Generated Data");
    header("Pragma: no-cache");
    header("Expires: 0");
    print("<meta http-equiv=\"Content-Type\" content=\"application/vnd.ms-excel; charset=utf-8\">");
    
    echo $EXCEL_STR;

} 


//최신화 NO 
else {

    
    //조회조건 체크
    if($ptn_idx) {
        $append_sql .= " and a.land_ptn_idx = {$ptn_idx}";
    } else if($pg_uri) {
        $append_sql .= " and b.page_idx in ({$pg_uri})";
    }
    if($fromDate) {
        $append_sql .= " and a.insert_date between '{$fromDate} 00:00:00.000' and '{$toDate} 23:59:59.999'";
    } 

    $sql = "
    select a.land_idx 
         , a.land_pg_idx 
         , a.land_ptn_idx
         , c.ptn_nm 
         , b.pg_domain
         , b.pg_uri
         , b.pg_inflow
         , a.name 
         , CONVERT(AES_DECRYPT(UNHEX(a.tel), 'withus_secret_key') USING UTF8) as tel
         , a.option1
         , a.option2 
         , a.option3 
         , a.option4 
         , a.option5 
         , a.option6 
         , a.option7 
         , a.option8 
         , a.option9 
         , case a.db_status
            when '1' then '부재'
            when '2' then '불량'
            when '3' then '거절'
            when '4' then '리콜'
            when '5' then '중복'
            when '6' then '유망'
            when '7' then '승인'
            else '대기'
            end as db_status
         , a.land_memo
         , a.inflow_path
         , a.inflow_env
         , a.utm_source
         , a.user_agent
         , a.api_send_yn
         , a.land_used_data
         , a.ip
         , a.city
         , a.region
         , a.insert_date
      from {$g5['crm_landing']}      a
      left join {$g5['crm_page']}    b on a.land_pg_idx = b.page_idx 
      left join {$g5['crm_partner']} c on a.land_ptn_idx = c.ptn_idx 
      where 1=1
      and a.use_yn = 'Y'
      {$add_cond}
      {$append_sql}
      order by land_idx desc
    ";
    
    $result = sql_query($sql);
    
    
    $EXCEL_STR = "
    <table border='1'>
    <tr>
        <td>NO</td>
        <td>이름</td>
        <td>코드</td>
        <td>연락처</td>
        <td>옵션1</td>
        <td>옵션2</td>
        <td>옵션3</td>
        <td>옵션4</td>
        <td>옵션5</td>
        <td>옵션6</td>
        <td>옵션7</td>
        <td>옵션8</td>
        <td>옵션9</td>
        <td>메모</td>
        <td>고객사</td>
        <td>유입루트</td>
        <td>유입채널</td>
        <td>P/M</td>
        <td>UTM</td>
        <td>브라우저</td>
        <td>최신화</td>
        <td>API전송</td>
        <td>아이피</td>
        <td>위치</td>
        <td>지역</td>
        <td>입력일시</td>
    </tr>";
    
    $i = 1;
    while ($res = sql_fetch_array( $result )) {
        $EXCEL_STR .= "  
       <tr>  
           <td>".$i."</td>  
           <td>".$res['name']."</td>
           <td>".$res['pg_uri']."</td>
           <td>".$res['tel']."</td>  
           <td>".$res['option1']."</td>  
           <td>".$res['option2']."</td>  
           <td>".$res['option3']."</td>  
           <td>".$res['option4']."</td>  
           <td>".$res['option5']."</td>  
           <td>".$res['option6']."</td>  
           <td>".$res['option7']."</td>  
           <td>".$res['option8']."</td>  
           <td>".$res['option9']."</td>  
           <td>".$res['db_status'].($res['land_memo'] == "" ? "" : "-" . $res['land_memo'])."</td>  
           <td>".$res['ptn_nm']."</td>
           <td>".$res['inflow_path']."</td>
           <td>".$res['pg_inflow']."</td>
           <td>".$res['inflow_env']."</td>
           <td>".$res['utm_source']."</td>
           <td>".$res['user_agent']."</td>
           <td>".$res['land_used_data']."</td>
           <td>".$res['api_send_yn']."</td>
           <td>".$res['ip']."</td>
           <td>".$res['region']."</td>
           <td>".$res['city']."</td>
           <td>".$res['insert_date']."</td>
       </tr>  
       ";  
    
       $i = $i + 1;
     }


    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR'];
    $record_hist_sql = "
    insert into {$g5['record_hist']} (hist_join_gubun, hist_function, hist_mb_no, hist_mb_name, hist_detail, client_ip) values
    ('직원', 'exceldown', '{$member['mb_no']}','{$member['mb_name']}','{$hist_memo}','{$ip}');
    ";
    isSqlError(sql_query($record_hist_sql), $record_hist_sql);

    $EXCEL_STR .= "</table>";

    header("Content-type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename={$partner_name}_{$append_title}엑셀다운로드_".date("Ymd_Hms").".xls" );
    header("Content-Description: PHP4 Generated Data");
    header("Pragma: no-cache");
    header("Expires: 0");
    print("<meta http-equiv=\"Content-Type\" content=\"application/vnd.ms-excel; charset=utf-8\">");

    //echo "<meta http-equiv='Content-Type' content='text/html; charset=euc-kr'> ";
    echo $EXCEL_STR;
}



 



//goto_url('./land_list.php?' . $qstr);

 

?>