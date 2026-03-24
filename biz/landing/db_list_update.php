<?php
require_once '../../common.php';

$act_button     = isset($_POST['act_button']) ? strip_tags($_POST['act_button']) : '';

if($member['mb_gubun'] == "P") {
    //대표
    $sql_search .= "
    and b.pg_ptn_idx = {$member['mb_ptnidx']}
    and a.insert_date <= now()
    and a.use_yn = 'Y'
    ";
    
} else {
    //직원
    $sql_search .= "
    and b.pg_ptn_idx = {$member['mb_ptnidx']}
    and b.pg_mb_ptn = {$member['mb_no']}
    and a.insert_date <= now()
    and a.use_yn = 'Y'
    ";
}

if($stx != "") {
    switch ($sfl) {

        case "insert_date":
            $split = explode("  ",$stx); 
            $from = $split[0];
            $to   = $split[1];
            $sql_search .= " and a.$sfl between '{$from} 00:00:00.000' and '{$to} 23:59:59.999'";

            break;
        case "tel":
            $sql_search .= "and tel = '$stx' ";
            break;
    }
} else {

    $timestamp = strtotime("-1 months");
    $from = date("Y-m-d", $timestamp);

    $timestamp = strtotime("Now");
    $to = date("Y-m-d", $timestamp);

    $sql_search .= " and a.insert_date between '{$from} 00:00:00.000' and '{$to} 23:59:59.999'";
}

$sql = "
select @rownum := @rownum + 1 as rownum
, a.land_idx 
, a.land_pg_idx
, a.name 
, a.tel 
, a.option1
, a.option2
, a.option3
, a.option4
, a.option5
, a.option6
, a.option7
, a.option8
, a.option9
, a.insert_date 
, a.client_ip
from {$g5['crm_landing']} a
left join {$g5['crm_page']} b on a.land_pg_idx = b.page_idx
, (select @rownum := 0) r
where 1=1
and a.land_used_data = 'N'
and a.use_yn = 'Y'
{$sql_search}
order by land_idx desc";


$result = sql_query($sql);
$result_cnt = mysqli_num_rows($result);
if($result_cnt == 0) {
    alert("엑셀 다운로드 할 최신화 데이터가 존재하지않습니다.");
}


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
<td>입력일시</td>
</tr>";


$i = 1;
while ($res = sql_fetch_array( $result )) {
    $EXCEL_STR .= "  
    <tr>  
        <td>".$i."</td>  
        <td>".$res['name']."</td>
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
        <td>".$res['insert_date']."</td>  
    </tr>";  

    $i = $i + 1;
}

$EXCEL_STR .= "</table>";

header("Content-type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=엑셀다운로드_".date("Ymd_Hms").".xls" );
header("Content-Description: PHP4 Generated Data");
header("Pragma: no-cache");
header("Expires: 0");
print("<meta http-equiv=\"Content-Type\" content=\"application/vnd.ms-excel; charset=utf-8\">");

echo $EXCEL_STR;