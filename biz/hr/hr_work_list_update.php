<?php

require_once '../../common.php';

$act_button     = isset($_POST['act_button']) ? strip_tags($_POST['act_button']) : '';

if ($act_button === "근태상신") {

    $vaca_mb_deptno = $member['mb_deptno'];
    $vaca_mb_no = $member['mb_no'];
    $vaca_mb_name = $member['mb_name'];
    $vaca_gubun = isset($_POST['vaca_code']) ? strip_tags(clean_xss_attributes($_POST['vaca_code'])) : '';
    $vaca_comment = isset($_POST['vaca_comment']) ? strip_tags(clean_xss_attributes($_POST['vaca_comment'])) : '';
    $vaca_date = isset($_POST['vaca_date']) ? strip_tags(clean_xss_attributes($_POST['vaca_date'])) : '';
    $vaca_calc = isset($_POST['vaca_calc']) ? strip_tags(clean_xss_attributes($_POST['vaca_calc'])) : '';

    $parts = explode(':', $vaca_gubun);
    $vaca_code = $parts[0];
    $vaca_name = $parts[1];

    $from = substr($vaca_date, 0, 10);
    $to = substr($vaca_date, 13, 10);

    $fromDate = new DateTime($from);
    $toDate = new DateTime($to);
    
    // toDate를 포함하기 위해 하루 추가
    $toDate->modify('+1 day'); 
    
    $period = 0; // 주말을 제외한 휴가 사용 일자 총합
    $currentDate = clone $fromDate;
    
    while ($currentDate < $toDate) {
        $dayOfWeek = (int)$currentDate->format('N');
        // 주말(토요일과 일요일)을 제외하고 카운트
        if ($dayOfWeek !== 6 && $dayOfWeek !== 7) {
            // 연차일 경우 1을 더하고, 반차일 경우 0.5를 더함
            if($vaca_code == "1" || $vaca_code == "6") {
                $period += 1;
            } elseif ($vaca_code == "2" || $vaca_code == "3") {
                $period += 0.5;
            }
        }
        $currentDate->modify('+1 day');
    }
    
    $vaca_calc = $period;


    $vaca_status = "";

    if($vaca_code == "5") {
        $vaca_status = "2";
    } else {
        $vaca_status = "1";

        //중복 validation 체크
        $exist_vaca = "
        select count(*) as cnt
        from {$g5['crm_vaca_mng']}
        where ('{$to}' < vaca_end_date AND '{$from}' > vaca_start_date)
        and vaca_code != 5
        and vaca_mb_no = {$member['mb_no']}
        ";
        $exist = sql_fetch($exist_vaca);
        if($exist['cnt'] > 0) {
            alert($from ." ~ " . $to. "신청일자에 중복 등록된 근태가 존재합니다.");
        }

    }

    $vaca_sql = "
    insert into {$g5['crm_vaca_mng']} (vaca_mb_deptno,vaca_mb_no,vaca_mb_name,vaca_code,vaca_name,vaca_status,vaca_comment,vaca_start_date,vaca_end_date,vaca_calc,insert_date, update_date, insert_user, update_user, insert_user_name, update_user_name) values
	 ({$vaca_mb_deptno},{$vaca_mb_no},'{$vaca_mb_name}','{$vaca_code}','{$vaca_name}','{$vaca_status}','{$vaca_comment}','{$from}','{$to}', {$vaca_calc}, now(), now(), '{$member['mb_id']}', '{$member['mb_id']}', '{$member['mb_name']}', '{$member['mb_name']}' );
    ";
    isSqlError(sql_query($vaca_sql), $vaca_sql);
    $vaca_idx = sql_insert_id();
    echo json_encode($vaca_idx);
    
}

goto_url('./hr_work_list?' . $qstr);
