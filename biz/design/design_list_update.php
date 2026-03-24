<?php

require_once '../../common.php';

$post_count_chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;
$chk            = (isset($_POST['chk']) && is_array($_POST['chk'])) ? $_POST['chk'] : array();
$act_button     = isset($_POST['act_button']) ? strip_tags($_POST['act_button']) : '';


if ($act_button === "카피") {
    $design_idx     = isset($_POST['design_idx']) ? strip_tags(clean_xss_attributes($_POST['design_idx'])) : '';
    $des_deptno = isset($_POST['des_deptno']) ? strip_tags(clean_xss_attributes($_POST['des_deptno'])) : 'NULL';
    $des_mb_no = !empty($_POST['des_mb_no']) ? strip_tags(clean_xss_attributes($_POST['des_mb_no'])) : 'NULL';

    $sel_sql = "
    select *
      from {$g5['crm_design']} 
    where design_idx = {$design_idx}
    ";
    $resultOne = sql_fetch($sel_sql);

    $date= date("ymd_His");

    $backup_sql = "
    insert into {$g5['crm_design']}
    select null 
     , concat(design_name,' 복사본_".$date."') as design_name
     , des_memo
	 , des_cate_code
	 , {$des_deptno}
	 , {$des_mb_no}
	 , des_html
     , des_status
     , NULL
     , des_shortcut
     , des_screen
	 , use_yn
	 , now()
     , now()
	 , '{$member['mb_id']}'
	 , '{$member['mb_id']}'
     , '{$member['mb_name']}'
     , '{$member['mb_name']}'
    from {$g5['crm_design']} 
    where design_idx = {$design_idx}
    ";
    isSqlError(sql_query($backup_sql), $backup_sql);
    goto_url('./design_list');
    
}

if (!$post_count_chk) {
    alert($act_button . '체크 한개이상 선택해주세요.');
}

for ($i = 0; $i < $post_count_chk; $i++) {

    $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;
    $design_idx     = isset($_POST['design_idx'][$k]) ? strip_tags(clean_xss_attributes($_POST['design_idx'][$k])) : '';
    $des_memo       = isset($_POST['des_memo'][$k]) ? strip_tags(clean_xss_attributes($_POST['des_memo'][$k])) : '';

    $des_status       = isset($_POST['des_status'][$k]) ? strip_tags(clean_xss_attributes($_POST['des_status'][$k])) : '';
    
    if ($act_button === "선택수정") {
        $upd_sql = "
        update {$g5['crm_design']} set
              des_memo = '{$des_memo}'
             ,des_status = '{$des_status}'
            , update_date = now()
            , update_user = '{$member['mb_id']}'
            , update_user_name = '{$member['mb_name']}'
        where design_idx = {$design_idx}
        ";
        isSqlError(sql_query($upd_sql), $upd_sql);

    } elseif ($act_button === "선택삭제") {
        $del_sql = "
        delete from {$g5['crm_design']} where design_idx = {$design_idx}
        ";
        isSqlError(sql_query($del_sql), $del_sql);
    } 
}

goto_url('./design_list?' . $qstr);
