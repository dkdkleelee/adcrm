<?php

require_once '../../common.php';

$post_count_chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;
$chk            = (isset($_POST['chk']) && is_array($_POST['chk'])) ? $_POST['chk'] : array();
$act_button     = isset($_POST['act_button']) ? strip_tags($_POST['act_button']) : '';

$isSqlErr = false;

if (!$post_count_chk) {
    alert($act_button . '체크 한개이상 선택해주세요.');
}





for ($i = 0; $i < $post_count_chk; $i++) {

    $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;
    
    $mb_no     = isset($_POST['mb_no'][$k]) ? strip_tags(clean_xss_attributes($_POST['mb_no'][$k])) : '';
    $is_login = isset($_POST['is_login'][$k]) ? clean_xss_tags($_POST['is_login'][$k], 1, 1) : '';

    //승인
    if($is_login == "Y") {
        //flag값 변경
        $upd_sql = "
        update {$g5['member_table']} set
        is_login = 'Y'
        where mb_no = {$mb_no}
        ";
        isSqlError(sql_query($upd_sql), $upd_sql);
    } 
    //삭제
    else if($is_login == "D") {

        $del_sql = "
        delete from {$g5['member_table']} where mb_no = {$mb_no}
        ";
        isSqlError(sql_query($del_sql), $del_sql);
    } 
    
}

goto_url('./hr_signup_list?' . $qstr);
