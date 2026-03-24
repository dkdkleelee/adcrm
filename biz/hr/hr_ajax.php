<?php
require_once '../../common.php';

$act = isset($_POST['act']) ? strip_tags($_POST['act']) : '';
$comm_pcd = isset($_POST['comm_pcd']) ? strip_tags($_POST['comm_pcd']) : '';

//인사 > 승인
if ($act === "sign_one_appr") {

	$mb_no = isset($_POST['mb_no']) ? strip_tags($_POST['mb_no']) : '';
    $val = isset($_POST['val']) ? strip_tags($_POST['val']) : '';

	//flag값 변경
    $upd_sql = "
    update {$g5['member_table']} set
    is_login = 'Y'
    where mb_no = {$mb_no}
    ";
    $result1 = sql_query($upd_sql);
	echo "처리완료";
} 
//인사 > 삭제
else if ($act === "sign_one_dele") {

	$mb_no = isset($_POST['mb_no']) ? strip_tags($_POST['mb_no']) : '';
    $val = isset($_POST['val']) ? strip_tags($_POST['val']) : '';

    $del_sql = "
    delete from {$g5['member_table']} where mb_no = {$mb_no}
    ";
    sql_query($del_sql);
    echo "삭제완료";
} else if ($act === "parent_deptlist") {

	$sel_sql = "
    select * from {$g5['crm_depart']} where parent_deptno = '1'
    ";

    $dept_list = sql_query($sel_sql);
    $response = "";

    for ($i = 0; $dept = sql_fetch_array($dept_list); $i++) {
        $response .= '<option value="'.$dept['deptno'].'">'.$dept['deptnm'].'</option>';
    }

    echo json_encode($response);

} else if ($act === "dup_member") {

    $mb_id = isset($_POST['mb_id']) ? strip_tags($_POST['mb_id']) : '';

    $dup_sql = "
    select count(*) as partnerCnt
    from {$g5['member_table']}
    where mb_id = '{$mb_id}'
    ";
    $row = sql_fetch($dup_sql);
    
    echo json_encode((int)$row['partnerCnt']);

} else if ($act === "dup_email") {
    $mb_email = isset($_POST['mb_email']) ? strip_tags($_POST['mb_email']) : '';

    $dup_sql = "
    select count(*) as emailCnt
    from {$g5['member_table']}
    where mb_email = '{$mb_email}'
    ";
    $row = sql_fetch($dup_sql);
    
    echo json_encode((int)$row['emailCnt']);
} else if ($act === "get_vaca_emp") {

    $result = array();
    
    $cnt_sql = "
    select a.mb_vaca_cnt as total_cnt
         , IFNULL(sum(b.vaca_calc), 0) as used_cnt
         , a.mb_vaca_cnt - IFNULL(sum(b.vaca_calc), 0) as remain_cnt
    from {$g5['member_table']} a
    left join {$g5['crm_vaca_mng']} AS b ON a.mb_no = b.vaca_mb_no and b.vaca_status = 2 and year(b.vaca_end_date) = year(curdate())
    where mb_no = '{$member['mb_no']}'
    group by a.mb_no, a.mb_vaca_cnt
    ";
    $cnt = sql_fetch($cnt_sql);
    array_push( $result, $cnt );
    
    $lstSql = "
        select a.*
        from {$g5['crm_vaca_mng']} a
        where vaca_mb_no = {$member['mb_no']}
        and vaca_code != 5
        and year(vaca_end_date) = year(curdate())
        order by vaca_start_date asc
        ";
        $lst = sql_query($lstSql);

        $used_vaca_cnt = 0;

        for ($i = 0; $row = sql_fetch_array($lst); $i++) {
            
            if($row['vaca_status'] == "1") {
                $class = "class='bg-success'";
                $status = "상신대기";
            } else if($row['vaca_status'] == "2") {
                $class = "class='bg-primary'";
                $status = "상신완료";

                if($row['vaca_code'] == "1" || $row['vaca_code'] == "6") {
                    $used_vaca_cnt = $used_vaca_cnt + $row['vaca_calc'];
                } else if($row['vaca_code'] == "2" || $row['vaca_code'] == "3") {
                    $used_vaca_cnt = $used_vaca_cnt + $row['vaca_calc'];
                }
                
            } else if($row['vaca_status'] == "3") {
                $class = "class='bg-danger'";
                $status = "상신반려";
            } else {
                
            }

            $response .= '
            <tr '.$class.'>
                <td>'.($i+1).'</td>
                <td>'.$row['vaca_name'].'</td>
                <td>'.$status.'</td>
                <td>'.$row['vaca_start_date'].' ~ '.$row['vaca_end_date'].' </td>
                <td>'.$used_vaca_cnt.'</td>
            </tr>
            ';
        }

        array_push( $result, $response );


        if($total_cnt == $used_cnt) {
            array_push( $result, "false" );
        }

        echo json_encode($result);
        

    
} else if ($act === "get_vaca_list") {

    $start = isset($_POST['start']) ? strip_tags($_POST['start']) : '';
    $end = isset($_POST['end']) ? strip_tags($_POST['end']) : '';
    $dept = isset($_POST['dept']) ? strip_tags($_POST['dept']) : '';
    $mb_no = isset($_POST['mb_no']) ? strip_tags($_POST['mb_no']) : '';
    $today = new DateTime();

    $add_cond = "";

    if($dept != "") {
        $add_cond .= "and vaca_mb_deptno = {$dept}";
    }
    if($mb_no != "") {
        $add_cond .= " and vaca_mb_no = {$mb_no}";
    }

    $vaca_sql = "
    select 
        a.vaca_idx,
        a.vaca_mb_deptno,
        a.vaca_mb_no,
        a.vaca_status,
        concat(a.vaca_mb_name, a.vaca_name
        ,' (月',(select sum(case when vaca_code = '1' and vaca_status = 2 then 1 when vaca_code in ('2', '3') then 0.5 else 0 end)
        from {$g5['crm_vaca_mng']} sub
        where sub.vaca_mb_no = a.vaca_mb_no 
        and year(sub.vaca_end_date) = year(a.vaca_end_date)
        and month(sub.vaca_end_date) = month(a.vaca_end_date)
        and sub.vaca_end_date >= date_format(a.vaca_end_date, '%y-%m-01')
        and sub.vaca_idx <= a.vaca_idx),')','[年',
        (select sum(case when vaca_code = '1' and vaca_status = 2 then 1 when vaca_code in ('2', '3') then 0.5 else 0 end)
        from {$g5['crm_vaca_mng']} sub
        where sub.vaca_mb_no = a.vaca_mb_no 
        and year(sub.vaca_end_date) = year(a.vaca_end_date)
        and sub.vaca_idx <= a.vaca_idx),']'
        ) as vaca_name,
        a.vaca_code,
        a.vaca_start_date,
        a.vaca_end_date
    from {$g5['crm_vaca_mng']} a
    where a.vaca_end_date between '{$start}' and '{$end}'
    and a.vaca_status != 3
    {$add_cond}
    order by a.vaca_idx
    ";

    $vaca_list = sql_query($vaca_sql);
    $response = "";
    $events = [];

    while ($vaca = sql_fetch_array($vaca_list)) {
        $startDate = new DateTime($vaca['vaca_start_date']);
        $endDate = new DateTime($vaca['vaca_end_date']);

        // 색상 설정
        $colors = [
            '3' => 'Pink',
            '4' => 'RosyBrown',
            '5' => 'RoyalBlue',
            '6' => 'LightSalmon',
            '7' => 'Maroon',
            '9' => 'MediumSlateBlue',
            '11' => 'LimeGreen'
        ];
        $color = isset($colors[$vaca['vaca_mb_deptno']]) ? $colors[$vaca['vaca_mb_deptno']] : 'gray';
        
        if($vaca['vaca_code'] == "5") {
            $color = "Black";
        }

        if($vaca['vaca_status'] == "1") {
            $color = "Red";
            $title = '☆'.$vaca['vaca_name'];
        } else {
            $title = $vaca['vaca_name'];
        }

        // 휴가 시작일부터 종료일까지 반복
        for ($date = clone $startDate; $date <= $endDate; $date->modify('+1 day')) {
            $events[] = [
                'id' => $vaca['vaca_idx'],
                'title' => $title,
                'start' => $date->format('Y-m-d'),
                'end' => $date->format('Y-m-d'),
                'color' => $color,
                'editable' => ($endDate  >= $today),
                'vaca_mb_deptno' => $vaca['vaca_mb_deptno'],
                'vaca_mb_no' => $vaca['vaca_mb_no'],
                'vaca_code' => $vaca['vaca_code'],
                'vaca_status' => $vaca['vaca_status']
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($events);
    
    
    
} else if ($act === "upd_vaca_emp") {
    $vaca_idx = isset($_POST['vaca_idx']) ? strip_tags($_POST['vaca_idx']) : '';
    $buttonType = isset($_POST['buttonType']) ? strip_tags($_POST['buttonType']) : '';
    $manager_comment = isset($_POST['comment']) ? strip_tags($_POST['comment']) : '';

    if($buttonType == "9") {
        $upd_sql = "
        delete from {$g5['crm_vaca_mng']}
        where vaca_idx = {$vaca_idx}
        ";
        $result1 = sql_query($upd_sql);
    } else {
        //flag값 변경
        $upd_sql = "
        update {$g5['crm_vaca_mng']} set
        vaca_status = '{$buttonType}'
      , manager_comment = '{$manager_comment}'
        where vaca_idx = {$vaca_idx}
        ";
        $result1 = sql_query($upd_sql);
    }

    echo json_encode("ok");
    
} else if ($act === "vaca_one_emp") {
    
    $sql = "
    
    ";

    $list = sql_query($sql);

    $result = array();
    while($row = sql_fetch_array($list)) {
        $result[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($result);

} else if ($act === "chg_dept") {
    
    $dept = isset($_POST['dept']) ? strip_tags($_POST['dept']) : '';

    $emp_sql = "
    select mb_no 
         , mb_id 
         , mb_name
         , mb_ptnidx 
         , mb_gubun
    from {$g5['member_table']}
    where mb_gubun = 'E'
    and is_login = 'Y'
    and mb_deptno = {$dept}
    ";
    $emp_list = sql_query($emp_sql);
    $response = "<option value=''>전체</option>";
    
    for ($i = 0; $emp = sql_fetch_array($emp_list); $i++) {
        $response .= '<option value="'.$emp['mb_no'].'">'.$emp['mb_name'].'</option>';
    }
    echo json_encode($response);

} else if ($act === "one_emp_vaca_list") {
    
    $vaca_mb_no = isset($_POST['vaca_mb_no']) ? strip_tags($_POST['vaca_mb_no']) : '';

    $vaca_sql = "
    select vaca_mb_name
         , vaca_name
         , case when vaca_status = '1' then '상신대기' 
                when vaca_status = '2' then '상신완료'
                when vaca_status = '3' then '반려'
                else '' end as vaca_status
         , concat(vaca_start_date, '~' , vaca_end_date) as vaca_date
         , vaca_comment
         , if(a.manager_comment is null or a.manager_comment = '', '미입력상태', a.manager_comment) as manager_comment
         , sum(vaca_calc) over(partition by year(vaca_end_date) order by vaca_end_date asc) as year_cnt
    from {$g5['crm_vaca_mng']} a
    where vaca_mb_no = {$vaca_mb_no}
    and vaca_status = 2
    order by vaca_end_date desc
    ";
    $vaca_list = sql_query($vaca_sql);

    $result = array();
    while($row = sql_fetch_array($vaca_list)) {
        $result[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($result);

} else if ($act === "get_meet_list") {
    

    $start = isset($_POST['start']) ? strip_tags($_POST['start']) : '';
    $end = isset($_POST['end']) ? strip_tags($_POST['end']) : '';

    $today = new DateTime();

    $meet_sql = "
    select meet_idx, meet_mb_no, meet_mb_deptno, meet_startday, meet_endday, meet_reason, insert_date, update_date, insert_user, update_user, insert_user_name, update_user_name, b.mb_name 
    from {$g5['crm_meet_mng']} a
    left join {$g5['member_table']} b on a.meet_mb_no = b.mb_no 
    where meet_endday between '{$start}' and '{$end}'
    ";

    $meet_list = sql_query($meet_sql);
    $response = "";
    $events = [];

    while ($meet = sql_fetch_array($meet_list)) {
        
        $startDate = new DateTime($meet['meet_startday']);
        $endDate = new DateTime($meet['meet_endday']);

        // 색상 설정
        $colors = [
            '3' => 'Pink',
            '4' => 'RosyBrown',
            '5' => 'RoyalBlue',
            '6' => 'LightSalmon',
            '7' => 'Maroon',
            '9' => 'MediumSlateBlue',
            '11' => 'LimeGreen'
        ];
        $color = isset($colors[$meet['meet_mb_deptno']]) ? $colors[$meet['meet_mb_deptno']] : 'gray';
        

        // 휴가 시작일부터 종료일까지 반복
        for ($date = clone $startDate; $date <= $endDate; $date->modify('+1 day')) {
            $events[] = [
                'id' => $meet['meet_idx'],
                'title' => '(예약자:'.$meet['insert_user_name'].') '.$meet['mb_name'] .' - '.$meet['meet_reason'],
                'start' => $meet['meet_startday'],
                'end' => $meet['meet_endday'],
                'color' => $color,
                'meet_mb_deptno' => $meet['meet_mb_deptno'],
                'meet_mb_no' => $meet['meet_mb_no'],
                'meet_idx' => $meet['meet_idx']
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($events);

} else if ($act === "del_meet") {
    $meet_idx = isset($_POST['meet_idx']) ? strip_tags($_POST['meet_idx']) : '';

    $del_sql = "
    delete from {$g5['crm_meet_mng']} where meet_idx = {$meet_idx}
    ";
    $result = sql_query($del_sql);

    echo json_encode("삭제완료");
} else if ($act === "get_meet_emp_list") {
    
    //파트너별직원리스트
    $result = array();

    $mb_deptno = $member['mb_deptno'];
    $mb_no = $member['mb_no'];

    $emp_sql = "
    select mb_no 
         , mb_id 
         , mb_name
         , mb_ptnidx 
         , mb_gubun
    from {$g5['member_table']}
    where mb_gubun = 'E'
    and is_login = 'Y'
    and mb_deptno = {$mb_deptno}
    ";
    $emp_list = sql_query($emp_sql);
    $response = "";

    for ($i = 0; $emp = sql_fetch_array($emp_list); $i++) {
        
        $mb_gubun = $emp['mb_no'] == $mb_no ? "본인예약" : "대리예약";
        $mb_selected = $emp['mb_no'] == $mb_no ? " selected" : "";

        $response .= '<option value="'.$emp['mb_no'].'" '. $mb_selected .'>'.$emp['mb_name'].'('.$mb_gubun.')</option>';
    }
    array_push( $result, $response );

    echo json_encode($result);

} else if ($act === "dup_chk_meetroom") {

    $fromDateTime = isset($_POST['fromDateTime']) ? strip_tags($_POST['fromDateTime']) : '';
    $toDateTime = isset($_POST['toDateTime']) ? strip_tags($_POST['toDateTime']) : '';

    
    $resultOneSql = "
    select meet_idx, meet_mb_no, meet_mb_deptno, meet_startday, meet_endday, meet_reason, insert_date, update_date, insert_user, update_user, insert_user_name, update_user_name, b.mb_name 
    from {$g5['crm_meet_mng']} a
    left join {$g5['member_table']} b on a.meet_mb_no = b.mb_no 
    WHERE ('{$fromDateTime}' < meet_endday AND '{$toDateTime}' > meet_startday)
    ";
    $resultOne = sql_fetch($resultOneSql);

    $mb_name = $resultOne['mb_name'];

    if($mb_name != "") {
        echo json_encode($resultOne);
    } else {
        echo json_encode("OK");
    }

    
} else if ($act === "etc_manager_comment") {

    $vaca_idx = isset($_POST['vaca_idx']) ? strip_tags($_POST['vaca_idx']) : '';
    $manager_comment = isset($_POST['comment']) ? strip_tags($_POST['comment']) : '';

    $upd_sql = "
    update {$g5['crm_vaca_mng']} set
    manager_comment = '{$manager_comment}'
    where vaca_idx = {$vaca_idx}
    ";
    $result1 = sql_query($upd_sql);

    echo json_encode("ok");
}
