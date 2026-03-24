<?php
require_once '../../common.php';


$post_count_chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;
$chk            = (isset($_POST['chk']) && is_array($_POST['chk'])) ? $_POST['chk'] : array();
$act_button     = isset($_POST['act_button']) ? strip_tags($_POST['act_button']) : '';
$board_table    = (isset($_POST['board_table']) && is_array($_POST['board_table'])) ? $_POST['board_table'] : array();

if (!$post_count_chk) {
    alert($act_button . '체크 한개이상 선택해주세요.');
}


if ($act_button === "선택수정") {
    for ($i = 0; $i < $post_count_chk; $i++) {

        $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;
        
        $mb_no     = isset($_POST['mb_no'][$k]) ? strip_tags(clean_xss_attributes($_POST['mb_no'][$k])) : '';

        $mb_name    = isset($_POST['mb_name'][$k]) ? strip_tags(clean_xss_attributes($_POST['mb_name'][$k])) : '';
        $mb_hp      = isset($_POST['mb_hp'][$k]) ? strip_tags(clean_xss_attributes($_POST['mb_hp'][$k])) : '';
        $mb_deptno  = isset($_POST['mb_deptno'][$k]) != "" ? strip_tags(clean_xss_attributes($_POST['mb_deptno'][$k])) : 'NULL';
        //$mb_deptno  = $_POST['mb_deptno'][$k] != "" ? $_POST['mb_deptno'][$k] : null;
        //$mb_deptno      = $_POST['mb_deptno'][$k] !=""  ? trim($_POST['mb_deptno'][$k]) : 'NULL';

        $mb_level   = isset($_POST['mb_level'][$k]) ? strip_tags(clean_xss_attributes($_POST['mb_level'][$k])) : '';
        $is_login   = isset($_POST['is_login'][$k]) ? strip_tags(clean_xss_attributes($_POST['is_login'][$k])) : '';
        

        $upd_sql = "
         update {$g5['member_table']} set
         mb_name = '{$mb_name}',
         mb_hp = '{$mb_hp}',
         mb_deptno = {$mb_deptno},
         mb_level = {$mb_level},
         is_login = '{$is_login}'
         where mb_no = {$mb_no}
         ";
         $result1 = sql_query($upd_sql);

    }
} else if ($act_button === "선택삭제") {

    for ($i = 0; $i < $post_count_chk; $i++) {

        $k          = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;
        $mb_no      = isset($_POST['mb_no'][$k]) ? strip_tags(clean_xss_attributes($_POST['mb_no'][$k])) : '';
        $mb_name    = isset($_POST['mb_name'][$k]) ? strip_tags(clean_xss_attributes($_POST['mb_name'][$k])) : '';
        $mb_hp      = isset($_POST['mb_hp'][$k]) ? strip_tags(clean_xss_attributes($_POST['mb_hp'][$k])) : '';
        $mb_deptno  = isset($_POST['mb_deptno'][$k]) ? strip_tags(clean_xss_attributes($_POST['mb_deptno'][$k])) : '';
        $mb_level   = isset($_POST['mb_level'][$k]) ? strip_tags(clean_xss_attributes($_POST['mb_level'][$k])) : '';

        $del_sql = "
        delete from {$g5['member_table']} where mb_no = {$mb_no}
        ";
        $result1 = sql_query($del_sql);

    }


} else if ($act_button === "부서저장") {

    $ins_sql = "
    insert into {$g5['crm_depart']} (
         deptnm
        ,parent_deptno
        ,use_yn
        ,insert_date
        ,update_date
        ,insert_user
        ,update_user
    ) VALUES (
        '{$deptnm}'
        ,{$parent_deptno}
        ,'Y'
        ,now()
        ,now()
        ,'{$member['mb_id']}'
        ,'{$member['mb_id']}'
    )
    ";
    sql_query($ins_sql);
    $key = sql_insert_id();
    
    $prnt = $parent_deptno;
    $chld = $key;

} else if ($act_button === "팀저장") {
    $ins_sql = "
    insert into {$g5['crm_depart']} (
        deptnm
        ,parent_deptno
        ,use_yn
        ,insert_date
        ,update_date
        ,insert_user
        ,update_user
    ) VALUES (
         '{$deptnm}'
        , 1
        ,'Y'
        ,now()
        ,now()
        ,'{$member['mb_id']}'
        ,'{$member['mb_id']}'
    )
    ";
    sql_query($ins_sql);
    $key = sql_insert_id();
 
    $prnt = $key;
    $chld = '';
}



goto_url('./hr_deptemp_list?'.$qstr.'&prnt='.$prnt.'&chld='.$chld);