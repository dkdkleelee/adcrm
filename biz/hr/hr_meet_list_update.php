<?php

require_once '../../common.php';

$act_button     = isset($_POST['act_button']) ? strip_tags($_POST['act_button']) : '';

if ($act_button === "회의실예약") {

    $mb_no = isset($_POST['mb_no']) ? strip_tags(clean_xss_attributes($_POST['mb_no'])) : '';
    $meet_mb_no = isset($_POST['meet_mb_no']) ? strip_tags(clean_xss_attributes($_POST['meet_mb_no'])) : '';
    $meetingReason = isset($_POST['meetingReason']) ? strip_tags(clean_xss_attributes($_POST['meetingReason'])) : '';
    $meetingTimeFrom = isset($_POST['meetingTimeFrom']) ? strip_tags(clean_xss_attributes($_POST['meetingTimeFrom'])) : '';
    $meetingTimeTo = isset($_POST['meetingTimeTo']) ? strip_tags(clean_xss_attributes($_POST['meetingTimeTo'])) : '';
    $mb_deptno = $member['mb_deptno'];

    $mb_id = $member['mb_id'];
    $mb_name = $member['mb_name'];

    $resultOneSql = "
    select meet_idx, meet_mb_no, meet_mb_deptno, meet_startday, meet_endday, meet_reason, insert_date, update_date, insert_user, update_user, insert_user_name, update_user_name, b.mb_name 
    from gnp_crm_meet_mng a
    left join gnp_member b on a.meet_mb_no = b.mb_no 
    WHERE ('{$meetingTimeFrom}' < meet_endday AND '{$meetingTimeTo}' > meet_startday)
    ";
    $resultOne = sql_fetch($resultOneSql);
    $mb_name = $resultOne['mb_name'];

    if($mb_name != "") {
        alert($mb_name . "님께서". $meetingTimeFrom . "~" . $meetingTimeTo ."에 예약된건이 존재합니다. 캘린더를 확인하여 다시 예약해주세요.");
    }

    $meet_sql = "
    INSERT INTO gnp_crm_meet_mng (meet_mb_no,meet_mb_deptno,meet_startday,meet_endday,meet_reason,insert_date,update_date,insert_user,update_user,insert_user_name,update_user_name) VALUES
	 ({$meet_mb_no},{$mb_deptno},'{$meetingTimeFrom}','{$meetingTimeTo}','{$meetingReason}',now(),now(),'{$mb_id}','{$mb_id}','{$mb_name}','{$mb_name}');
    ";
    isSqlError(sql_query($meet_sql), $meet_sql);
    $meet_idx = sql_insert_id();
    echo json_encode($meet_idx);
}

goto_url('./hr_meet_list?' . $qstr);
