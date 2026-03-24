<?php

require_once '../../common.php';

$post_count_chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;
$chk            = (isset($_POST['chk']) && is_array($_POST['chk'])) ? $_POST['chk'] : array();
$act_button     = isset($_POST['act_button']) ? strip_tags($_POST['act_button']) : '';
$board_table    = (isset($_POST['board_table']) && is_array($_POST['board_table'])) ? $_POST['board_table'] : array();


if (!$post_count_chk) {
    alert($act_button . '체크 한개이상 선택해주세요.');
}


sql_query("SET autocommit=0 ");
//sql_query("SET global innodb_autoinc_lock_mode = 1");

$succ_cnt = 0;
$ret_url = './partner_signup_list?' . $qstr;

for ($i = 0; $i < $post_count_chk; $i++) {

    $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;
    
    $sign_idx     = isset($_POST['sign_idx'][$k]) ? strip_tags(clean_xss_attributes($_POST['sign_idx'][$k])) : '';
    $sign_status = isset($_POST['sign_status'][$k]) ? clean_xss_tags($_POST['sign_status'][$k], 1, 1) : '';

    $ptn_idx = isset($_POST['ptn_idx'][$k]) ? clean_xss_tags($_POST['ptn_idx'][$k], 1, 1) : '';

    //승인
    if($sign_status == "Y") {

        $ptn_gubun = isset($_POST['ptn_gubun'][$k]) ? clean_xss_tags($_POST['ptn_gubun'][$k], 1, 1) : '';
        $mb_gubun = "";

        if($ptn_gubun == "1") {
            $mb_gubun = "P"; //대표자
        } else if($ptn_gubun == "2") { 
            $mb_gubun = "C"; //직원
        }

        //신규 고객사를 등록함
        if($ptn_idx == "") {
            //1: partner 신규등록
            $ins_sql = "
            insert into {$g5['crm_partner']} 
            select null 	    as ptn_idx
                , null          as cate_code
                , null          as ptn_deptno
                , ptn_phone     as ptn_phone
                , '' 			as ptn_bznm
                , ptn_reprnm	as ptn_reprnm
                , '' 			as ptn_bznum
                , '' 			as ptn_addr
                , ptn_email 	as ptn_email
                , ''			as ptn_memo
                , ptn_id 		as ptn_id
                , ptn_id        as mb_id
                , null          as ptn_status
                , null          as ptn_startday
                , null          as ptn_endday
                , null          as ptn_dposday
                , 0             as ptn_budget
                , ptn_tel       as ptn_tel
                , '1'           as isconn
                , 'Y'           as use_yn
                , now()         as insert_date
                , null          as update_date
                , ''            as insert_user
                , null          as update_user
            from {$g5['crm_signup']} 
            where sign_idx = {$sign_idx}
            ";
            //$ins_result = sql_query_new($ins_sql);
            isSqlError(sql_query($ins_sql), $ins_sql);
            $ptn_idx = sql_insert_id();

        } else {

            $ptn_id = isset($_POST['ptn_id'][$k]) ? clean_xss_tags($_POST['ptn_id'][$k], 1, 1) : '';

            // if($ptn_gubun == "1") {
            //     //valid check 한명이상 대표자 등록 불가
            //     $dup_gubun_sql = "
            //     select count(*) as cnt
            //     from {$g5['member_table']} 
            //     where mb_ptnidx = {$ptn_idx}
            //     and mb_gubun = 'P'
            //     ";

            //     $dup_gubun = sql_fetch($dup_gubun_sql);
            //     $cnt = (int)$dup_gubun['cnt'];
            //     if($cnt > 0) {
            //         sql_query("ROLLBACK");
            //         alert("[신청ID : ".$ptn_id. "] 1개의 고객사에 두 대표자 지정이 불가능합니다.");
            //     }
            // } else {
            //     $ptn_id = "";
            // }

            //update 
            $upd_sql = "
            update {$g5['crm_partner']} 
            set isconn = '1'
            where ptn_idx = {$ptn_idx}
            ";
            isSqlError(sql_query($upd_sql), $upd_sql);

        }
        

        //2: member 회원가입처리
        $sel_sql = "
        select *
        from {$g5['crm_signup']}
        where sign_idx = {$sign_idx}
        ";
        $row = sql_fetch($sel_sql);
    
        $_POST['mb_ptnidx']       = $ptn_idx;
        $_POST['mb_id']           = $row['ptn_id'];
        $_POST['mb_password']     = $row['ptn_pw'];
        $_POST['mb_password_re']  = $row['ptn_pw'];
        $_POST['mb_name']         = $row['ptn_nm'];
        $_POST['mb_hp']           = $row['ptn_phone'];
        $_POST['mb_nick']         = $row['ptn_nick'];
        $_POST['mb_email']        = $row['ptn_email'];
        
        $_POST['admin_approve']   = "Y";
        $_POST['mb_gubun']        = $mb_gubun;
        $_POST['is_login']        = "Y";
         
        include(G5_BBS_PATH.'/register_form_update_ptn.php');

        if($toAuthMember == false) {
            sql_query("ROLLBACK");
            alert("admin 접속권한부여 실패하였습니다.");
        }

        //3:flag값 변경
        $upd_sql = "
        update {$g5['crm_signup']} set
        sign_status = 'Y'
        where sign_idx = {$sign_idx}
        ";
        $result1 = sql_query($upd_sql);

    } 
    //삭제
    else if($sign_status == "D") {

        $del_sql = "
        delete from {$g5['crm_signup']} where sign_idx = {$sign_idx}
        ";
        sql_query($del_sql);
        
    } 
    //반려
    else if($sign_status == "R") {
        $upd_sql = "
        update {$g5['crm_signup']} set
            sign_status = 'R'
        where sign_idx = {$sign_idx}
        ";
        $result1 = sql_query($upd_sql);
    }

    $succ_cnt = $succ_cnt+1;
    
}

sql_query("COMMIT");

goto_url($ret_url);
