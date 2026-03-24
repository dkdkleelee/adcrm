<?php

require_once '../../common.php';

$post_count_chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;
$chk            = (isset($_POST['chk']) && is_array($_POST['chk'])) ? $_POST['chk'] : array();
$act_button     = isset($_POST['act_button']) ? strip_tags($_POST['act_button']) : '';
$board_table    = (isset($_POST['board_table']) && is_array($_POST['board_table'])) ? $_POST['board_table'] : array();


if ($act_button === "직원추가") {
    $mb_ptnidx  = isset($_POST['mb_ptnidx']) ? strip_tags(clean_xss_attributes($_POST['mb_ptnidx'])) : '';
    $mb_id      = isset($_POST['mb_id']) ? strip_tags(clean_xss_attributes($_POST['mb_id'])) : '';
    $mb_password= isset($_POST['mb_password']) ? strip_tags(clean_xss_attributes($_POST['mb_password'])) : '';
    $mb_name    = isset($_POST['mb_name']) ? strip_tags(clean_xss_attributes($_POST['mb_name'])) : '';
    $mb_hp      = isset($_POST['mb_hp']) ? strip_tags(clean_xss_attributes($_POST['mb_hp'])) : '';
    $mb_gubun   = isset($_POST['mb_gubun']) ? strip_tags(clean_xss_attributes($_POST['mb_gubun'])) : '';
    
    
    if($mb_gubun == "P") { 
        //대표
        $mb_level = 3;
    } 
    else {
        //직원
        $mb_level = 2;
    }
    $encode_pw  = get_encrypt_string($mb_password);

    $getPartner_sql = "
    select *
    from {$g5['crm_partner']} 
    where ptn_idx = {$mb_ptnidx}
    ";
    $ptnInfo = sql_fetch($getPartner_sql);
    

    $_POST['mb_ptnidx']       = $mb_ptnidx;
    $_POST['mb_id']           = $mb_id;
    $_POST['mb_password']     = $mb_password;
    $_POST['mb_password_re']  = $mb_password;
    $_POST['mb_name']         = $mb_name;
    $_POST['mb_nick']         = $ptnInfo['ptn_nick'];
    $_POST['mb_email']        = $ptnInfo['ptn_email'];
    
    $_POST['admin_approve']   = "Y";
    $_POST['mb_gubun']        = $mb_gubun;
    $_POST['is_login']        = "Y";
    
    $addPtnEmpSql = "
    insert into {$g5['member_table']} set
     mb_id = '{$mb_id}'
    ,mb_password = '{$encode_pw}'
    ,mb_name = '{$mb_name}'
    ,mb_nick = '{$mb_name}'
    ,mb_nick_date = '".G5_TIME_YMD."'
    ,mb_email = '{$ptnInfo['ptn_email']}'
    ,mb_level = {$mb_level}
    ,mb_tel = '{$ptnInfo['ptn_tel']}'
    ,mb_hp = '{$mb_hp}'
    ,mb_adult = 0
    ,mb_point = 0
    ,mb_today_login = '".G5_TIME_YMDHIS."'
    ,mb_login_ip = '".getRealClientIp()."'
    ,mb_datetime = '".G5_TIME_YMDHIS."'
    ,mb_ip = '".getRealClientIp()."'
    ,mb_memo = '고객사관리_ID부여'
    ,mb_mailling = 0
    ,mb_sms = 0
    ,mb_open = 1
    ,mb_open_date = '".G5_TIME_YMD."'
    ,mb_gubun = '{$mb_gubun}'
    ,mb_ptnidx = '{$mb_ptnidx}'
    ,login_fail_cnt = 0
    ,is_login = 'Y'
    ,is_lock = 'N'
    ,confirm_date = '".G5_TIME_YMDHIS."'
    ";
    isSqlError(sql_query($addPtnEmpSql), $addPtnEmpSql);
    goto_url('./partner_list?' . $qstr);
    
}


if (!$post_count_chk) {
    alert($act_button . '체크 한개이상 선택해주세요.');
}

for ($i = 0; $i < $post_count_chk; $i++) {

    $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;
    
    $ptn_idx     = isset($_POST['ptn_idx'][$k]) ? strip_tags(clean_xss_attributes($_POST['ptn_idx'][$k])) : '';
    $ptn_startday = isset($_POST['ptn_startday'][$k]) ? clean_xss_tags($_POST['ptn_startday'][$k], 1, 1) : '';
    $ptn_endday = isset($_POST['ptn_endday'][$k]) ? clean_xss_tags($_POST['ptn_endday'][$k], 1, 1) : '';
    $ptn_status = isset($_POST['ptn_status'][$k]) ? clean_xss_tags($_POST['ptn_status'][$k], 1, 1) : '';
    
    if($ptn_startday > $ptn_endday){
        alert("시작일 종료일이 유효하지 않습니다.");
    }
    
    //수정 & 삭제 전 백업
    // $backup_sql = "
    // insert into {$g5['crm_partner_hist']} 
    //     select null
    //     ,a.ptn_idx
    //     ,ptn_nm
    //     ,cate_code
    //     ,a.ptn_deptno as ptn_deptno
    //     ,b.deptnm as ptn_deptnm
    //     ,a.ptn_mb_emp 
    //     ,ptn_phone
    //     ,ptn_bznm
    //     ,ptn_reprnm
    //     ,ptn_bznum
    //     ,ptn_addr
    //     ,ptn_email
    //     ,ptn_memo
    //     ,ptn_id
    //     ,mb_id
    //     ,ptn_status
    //     ,ptn_startday
    //     ,ptn_endday
    //     ,ptn_dposday
    //     ,ptn_budget
    //     ,ptn_ad_gubun
    //     ,ptn_db_amount
    //     ,ptn_ntc_useyn
    //     ,ptn_ntc_date
    //     ,ptn_cont_ref
    //     ,ptn_ntc_pct
    //     ,ptn_show_dash
    //     ,ptn_tel
    //     ,ptn_is_upload
    //     ,ptn_ban_phone
    //     ,isconn
    //     ,a.use_yn
    //     ,now()
    //     ,'{$member['mb_id']}'
    // from {$g5['crm_partner']} a 
    // left join {$g5['crm_depart']} b on a.ptn_deptno = b.deptno 
    // where 1=1
    //     and a.ptn_idx = {$ptn_idx}
    // ";
    // isSqlError(sql_query($backup_sql), $backup_sql);
    

    if ($act_button === "선택수정") {
        $upd_sql = "
        update {$g5['crm_partner']} set
              ptn_startday = '{$ptn_startday}'
            , ptn_endday = '{$ptn_endday}'
            , ptn_status = '{$ptn_status}'
            , update_date = now()
            , update_user = '{$member['mb_id']}'
            , update_user_name = '{$member['mb_name']}'
        where ptn_idx = {$ptn_idx}
        ";
        isSqlError(sql_query($upd_sql), $upd_sql);
    } elseif ($act_button === "선택삭제") {
        
        $del_sql = "
        delete from {$g5['crm_partner_hist']} where ptn_idx = {$ptn_idx}
        ";
        isSqlError(sql_query($del_sql), $del_sql);


        $del_sql = "
        delete from {$g5['crm_partner']} where ptn_idx = {$ptn_idx}
        ";
        isSqlError(sql_query($del_sql), $del_sql);
    }
}

goto_url('./partner_list?' . $qstr);
