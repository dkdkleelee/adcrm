<?php

require_once '../../common.php';



$post_count_chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;
$chk            = (isset($_POST['chk']) && is_array($_POST['chk'])) ? $_POST['chk'] : array();
$act_button     = isset($_POST['act_button']) ? strip_tags($_POST['act_button']) : '';

if ($act_button === "고객최신화") {
    require_once './naver_api/naver_comm_api.php';
    call_api_customer();
    goto_url('./kwd_customers_list?' . $qstr);
} 

else if ($act_button === "담당자등록" || $act_button === "담당자수정") {

    $customerLinkId     = isset($_POST['customerLinkId']) ? strip_tags(clean_xss_attributes($_POST['customerLinkId'])) : '';
    $comp_name     = isset($_POST['comp_name']) ? strip_tags(clean_xss_attributes($_POST['comp_name'])) : '';
    $mb_no = isset($_POST['mb_no']) ? strip_tags(clean_xss_attributes($_POST['mb_no'])) : 'NULL';

    $rpt_term = isset($_POST['rpt_term']) ? implode('||', $_POST['rpt_term']) : NULL;
    $rpt_type = isset($_POST['rpt_type']) ? implode('||', $_POST['rpt_type']) : NULL;

    $is_sms_bizmoney = isset($_POST['is_sms_bizmoney']) ? strip_tags(clean_xss_attributes($_POST['is_sms_bizmoney'])) : '';
    $cond_bizmoney = isset($_POST['cond_bizmoney']) ? strip_tags(clean_xss_attributes($_POST['cond_bizmoney'])) : 0;
    $cond_bizmoney = str_replace(',', '', $cond_bizmoney);
    
    if($is_sms_bizmoney == "on") {
        $is_sms_bizmoney = "Y";
    } else {
        $is_sms_bizmoney = "N";
    }

    $upd_sql = "
    update gnp_kwd_customers set
          comp_name = '{$comp_name}'
        , mb_no = '{$mb_no}'
        , rpt_term = '{$rpt_term}'
        , rpt_type = '{$rpt_type}'
        , is_sms_bizmoney = '{$is_sms_bizmoney}'
        , cond_bizmoney = {$cond_bizmoney}
    where customerLinkId = '{$customerLinkId}'
    ";
    isSqlError(sql_query($upd_sql), $upd_sql);
    goto_url('./kwd_customers_list?' . $qstr);
} 



if (!$post_count_chk) {
    alert($act_button . '체크 한개이상 선택해주세요.');
}


for ($i = 0; $i < $post_count_chk; $i++) {

    $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;

    if ($act_button === "선택수정") {

    }
}