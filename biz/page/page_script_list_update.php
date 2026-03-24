<?php
require_once '../../common.php';


$act_button     = isset($_POST['act_button']) ? strip_tags($_POST['act_button']) : '';


$script_idx = isset($_POST['script_idx']) ? strip_tags(clean_xss_attributes($_POST['script_idx'])) : '';
$script_name = isset($_POST['script_name']) ? strip_tags(clean_xss_attributes($_POST['script_name'])) : '';
//$script_code = isset($_POST['script_code']) ? strip_tags(clean_xss_attributes($_POST['script_code'])) : '';

$script_code = '';
if (isset( $_POST['script_code'] )) {
    $script_code = substr(trim($_POST['script_code']),0,65536);
    $script_code = preg_replace("#[\\\]+$#", "", $script_code);
}


if ($act_button === "저장") {
    $ins_sql = "
    INSERT INTO {$g5['crm_page_script']} (
         script_name
        ,script_code
        ,use_yn
        ,insert_date
        ,update_date
        ,insert_user
        ,update_user
    ) VALUES (
         '{$script_name}'
        ,'{$script_code}'
        , 'Y'
        , now()
        , now()
        ,'{$member['mb_id']}'
        ,'{$member['mb_id']}'
    ); 
    ";
    isSqlError(sql_query($ins_sql), $ins_sql);
    $script_idx = sql_insert_id();
}
else if ($act_button === "수정") {

    $upd_sql = "
    update {$g5['crm_page_script']} set
           script_name  = '{$script_name}'
         , script_code  = '{$script_code}'
         , update_date  = now()
         , update_user  = '{$member['mb_id']}'
    where script_idx = {$script_idx}
    ";
    isSqlError(sql_query($upd_sql), $upd_sql);
}


goto_url('./page_script_list?' . $qstr);
