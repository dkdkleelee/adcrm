<?php
require_once '../../common.php';

$act_button     = isset($_POST['act_button']) ? strip_tags($_POST['act_button']) : '';
$calc_month = isset($_POST['calc_month']) ? strip_tags(clean_xss_attributes($_POST['calc_month'])) : '';
$calc_comp = isset($_POST['calc_comp']) ? strip_tags(clean_xss_attributes($_POST['calc_comp'])) : '';

if ($act_button === "업로드") {

    set_time_limit(0);
    ini_set('memory_limit', '2000M');

    $is_upload_file = isset($_FILES['file']['tmp_name']) && $_FILES['file']['tmp_name'];

    if (!$is_upload_file) {
        alert("엑셀 파일을 업로드해 주세요.");
        return; // End execution if no file uploaded
    }

    include_once(G5_LIB_PATH.'/PHPExcel/PHPExcel.php');
    include_once(G5_LIB_PATH.'/PHPExcel/IOFactory.php');
    include_once(G5_LIB_PATH.'/PHPExcel/CachedObjectStorageFactory.php');
    


    // Setup cell caching to reduce memory usage
    $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_discISAM;
    if (!PHPExcel_Settings::setCacheStorageMethod($cacheMethod)) {
        // Handle the failure to set caching method, e.g., log or alert
        alert("Unable to set cell caching method.");
        return;
    }

    $file = $_FILES['file']['tmp_name'];
    include_once(G5_LIB_PATH . '/PHPExcel/IOFactory.php');

    // Load the workbook with cell caching enabled
    $objPHPExcel = PHPExcel_IOFactory::load($file);
    $sheet = $objPHPExcel->getSheet(0);

    $num_rows = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();

    // Initialize counters
    $total_cnt = $num_rows - 1;

    $charge_sql = "
    select chg_naver_id, chg_emp_no, chg_emp_name, use_yn, insert_date, update_date, insert_user, update_user, insert_user_name, update_user_name 
    from gnp_kwd_calc_charge 
    where use_yn = 'Y'
    and chg_emp_no is not null
    ";
    $charge_list = sql_query($charge_sql);

    $charge_data = [];
    while ($row = sql_fetch_array($charge_list)) {
        $charge_data[$row['chg_naver_id']] = 
        [
            'chg_emp_no' => $row['chg_emp_no'],
            'chg_emp_name' => $row['chg_emp_name']
        ];
    }



    $delete_sql = "
    delete from gnp_kwd_calc_main
    where calc_month = {$calc_month}
      and calc_comp = '{$calc_comp}'
    ";
    $deleted = sql_query($delete_sql);
    

    for ($i = 5; $i <= $num_rows; $i++) {
        // A부터 G열까지의 데이터를 배열로 가져옵니다.
        // A부터 G열까지의 데이터를 배열로 가져옵니다.
        $rowData = $sheet->rangeToArray('B' . $i . ':G' . $i, NULL, TRUE, FALSE);

        // B(n) 또는 C(n) 열에서 '합계'를 찾기 위한 로직
        $columnBValue = sql_escape_string($rowData[0][0]);// B열의 값
        $columnCValue = $rowData[0][1]; // C열의 값
        $columnDValue = $rowData[0][2]; // D열의 값


        $columnEValue = trim($rowData[0][3]);
        $columnFValue = trim($rowData[0][4]);
        $columnGValue = trim($rowData[0][5]);
        
        // 조건: 세 변수 모두가 공백이거나 '-'일 경우
        if (($columnEValue === '' || $columnEValue === '-') &&
            ($columnFValue === '' || $columnFValue === '-') &&
            ($columnGValue === '' || $columnGValue === '-')) {
            continue;
        }

        // 유효한 숫자가 아니면 0으로 설정
        $columnEValue = is_numeric($columnEValue) ? $columnEValue : 0;
        $columnFValue = is_numeric($columnFValue) ? $columnFValue : 0;
        $columnGValue = is_numeric($columnGValue) ? $columnGValue : 0;

        // 조건: 세 변수 모두가 0일 경우
        if ($columnEValue == 0 && $columnFValue == 0 && $columnGValue == 0) {
            continue;
        }


        $chg_emp_no  = 'NULL';
        // chg_naver_id가 일치하는지 확인
        if (isset($charge_data[$columnDValue])) {
            $chg_emp_no = $charge_data[$columnDValue]['chg_emp_no'];
        }


        // 스페이스 제거
        $columnBValue = str_replace(' ', '', $columnBValue);
        $columnCValue = str_replace(' ', '', $columnCValue);

        // '합계'와 비교
        if ($columnBValue == '합계' || $columnCValue == '합계') {
            break; // '합계'를 찾으면 for문 종료
        }

        $columnCValue = str_replace("'", "''", $columnCValue);
        $ins_sql = "
        insert into gnp_kwd_calc_main (
              calc_month
            , calc_comp
            , calc_media
            , calc_acct
            , calc_naver_id
            , calc_supl_amount
            , calc_tax_amount
            , calc_sum_amount
        ) VALUES (
              '{$calc_month}'
            , '{$calc_comp}'
            , '{$columnBValue}'
            , '{$columnCValue}'
            , '{$columnDValue}'
            ,  {$columnEValue}
            ,  {$columnFValue}
            ,  {$columnGValue}
        )";
        isSqlError(sql_query($ins_sql), $ins_sql);

        

        // 메모리 최적화를 위해 사용한 변수를 해제합니다.
        unset($rowData);

    }

    alert("업로드 완료","./kwd_calc_emp?". $qstr);

} else if ($act_button === "신규계정저장") {

    $chg_naver_id = isset($_POST['chg_naver_id']) ? strip_tags($_POST['chg_naver_id']) : '';
    $chg_emp_no = isset($_POST['chg_emp_no']) ? strip_tags($_POST['chg_emp_no']) : '';
    
    $emp = explode("||", $chg_emp_no);

    $emp_no = $emp[0];
    $emp_name = $emp[1];


    $ins_sql = "
        insert into gnp_kwd_calc_charge (
              chg_naver_id
            , chg_emp_no
            , chg_emp_name
            , use_yn
        ) VALUES (
              '{$chg_naver_id}'
            , {$emp_no}
            , '{$emp_name}'
            , 'Y'
        )";
        isSqlError(sql_query($ins_sql), $ins_sql);

    alert("저장완료","./kwd_calc_emp?". $qstr);
} else if ($act_button === "엑셀다운") {
    
    $stx_calc_comp = isset($_POST['stx_calc_comp']) ? strip_tags($_POST['stx_calc_comp']) : '';
    $stx_calc_month = isset($_POST['stx_calc_month']) ? strip_tags($_POST['stx_calc_month']) : '';
    $mb_no = $member['mb_no'];

    $add_cond = "";
    if($stx_calc_month != "") {
        $add_cond = "
        and a.calc_month = '{$stx_calc_month}' 
        ";
    }

    if ($member['mb_level'] >= 6) {
        
    } else {
        $add_cond .= "
        and b.chg_emp_no = '{$mb_no}' 
        ";
    }

    $sql = "
    select 
          c.mb_name 
        , b.chg_emp_no 
        , a.*
    from gnp_kwd_calc_main a
    left join gnp_kwd_calc_charge b on a.calc_naver_id = b.chg_naver_id 
    left join {$g5['member_table']} c on b.chg_emp_no = c.mb_no 
    where 1=1
    and b.chg_emp_no is not null
    and b.chg_emp_no != ''
    {$add_cond}
    and a.calc_comp = '{$stx_calc_comp}' 
    order by c.mb_name, a.calc_naver_id
    ";

        
    $result = sql_query($sql);
    $result_cnt = mysqli_num_rows($result);
    if($result_cnt == 0) {
        alert("엑셀 다운로드 할 최신화 데이터가 존재하지않습니다.");
    }

    $sql2 = "
    select 
        c.mb_name 
       , b.chg_emp_no 
       , a.*
    from gnp_kwd_calc_main a
    left join gnp_kwd_calc_charge b on a.calc_naver_id = b.chg_naver_id 
    left join {$g5['member_table']} c on b.chg_emp_no = c.mb_no 
    where 1=1
    and (b.chg_emp_no is null or b.chg_emp_no = '')
    {$add_cond}
    and a.calc_comp = '{$stx_calc_comp}' 
    order by c.mb_name, a.calc_naver_id
    ";
    $result2 = sql_query($sql2);

        
    $table_header = "
    <table border='1'>
    <thead>
    </thead>
    <tbody>
    ";

    $EXCEL_STR = "
    <table border='1'>
    <tr>
    <td>NO</td>
    <td>이름</td>
    <td>업체</td>
    <td>년월</td>
    <td>미디어</td>
    <td>계정명</td>
    <td>네이버ID</td>
    <td>공급가</td>
    <td>세액</td>
    <td>총액</td>
    </tr>";


    
    $i = 1;
    while ($res = sql_fetch_array( $result )) {
        $EXCEL_STR .= "  
        <tr>  
            <td>".$i."</td>  
            <td>".$res['mb_name']."</td>
            <td>".$res['calc_comp']."</td>  
            <td>".$res['calc_month']."</td>  
            <td>".$res['calc_media']."</td>  
            <td>".$res['calc_acct']."</td>  
            <td>".$res['calc_naver_id']."</td>  
            <td>".$res['calc_supl_amount']."</td>  
            <td>".$res['calc_tax_amount']."</td>
            <td>".$res['calc_sum_amount']."</td>  
        </tr>";  

        $i = $i + 1;
    }

   
    while ($res = sql_fetch_array( $result2 )) {
        $EXCEL_STR .= "  
        <tr>  
            <td style='background: red'>".$i."</td>  
            <td style='background: red'>미지정</td>
            <td style='background: red'>".$res['calc_comp']."</td>  
            <td style='background: red'>".$res['calc_month']."</td>  
            <td style='background: red'>".$res['calc_media']."</td>  
            <td style='background: red'>".$res['calc_acct']."</td>  
            <td style='background: red'>".$res['calc_naver_id']."</td>  
            <td style='background: red'>".$res['calc_supl_amount']."</td>  
            <td style='background: red'>".$res['calc_tax_amount']."</td>
            <td style='background: red'>".$res['calc_sum_amount']."</td>  
        </tr>";  

        $i = $i + 1;
    }








    $EXCEL_STR .= "</table>";

    header("Content-type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=정산엑셀다운로드_".date("Ymd_Hms").".xls" );
    header("Content-Description: PHP4 Generated Data");
    header("Pragma: no-cache");
    header("Expires: 0");
    print("<meta http-equiv=\"Content-Type\" content=\"application/vnd.ms-excel; charset=utf-8\">");
    
    echo $EXCEL_STR;

}





?>
