<?php
require_once '../../common.php';

$act = isset($_POST['act']) ? strip_tags($_POST['act']) : '';


if ($act === "commonCode") {
    //공통코드리스트
    $comm_pcd = isset($_POST['comm_pcd']) ? strip_tags($_POST['comm_pcd']) : '';

    $code_sql = "
    select comm_idx
        , comm_pcd 
        , comm_pnm 
        , comm_cd 
        , comm_nm 
        , comm_bigo
    from {$g5['crm_common']}
    where 1=1 
    and use_yn = 'Y' 
    and comm_pcd = {$comm_pcd}
    order by comm_cd
    ";
    $code_list = sql_query($code_sql);
    $response = "";

    for ($i = 0; $code = sql_fetch_array($code_list); $i++) {
        $response .= '<option value="'.$code['comm_idx'].'">'.$code['comm_nm'].'</option>';
    }

    echo json_encode($response);
} else if ($act === "deptByEmp") {

    //부서별직원리스트

    $deptno = isset($_POST['deptno']) ? strip_tags($_POST['deptno']) : '';

    $emp_sql = "
    select mb_no 
         , mb_id 
         , mb_name
         , mb_deptno 
    from {$g5['member_table']}
    where mb_gubun = 'E'
    and is_login = 'Y'
    and mb_deptno = {$deptno}
    order by mb_name asc
    ";
    $emp_list = sql_query($emp_sql);
    $response = "";

    for ($i = 0; $emp = sql_fetch_array($emp_list); $i++) {
        $response .= '<option value="'.$emp['mb_no'].'">'.$emp['mb_name'].'</option>';
    }

    echo json_encode($response);
} else if ($act === "deptByEmpAndPtn") {

    //부서별직원리스트
    $result = array();

    $deptno = isset($_POST['deptno']) ? strip_tags($_POST['deptno']) : '';

    $emp_sql = "
    select mb_no 
         , mb_id 
         , mb_name
         , mb_deptno 
    from {$g5['member_table']}
    where mb_gubun = 'E'
    and is_login = 'Y'
    and mb_deptno = {$deptno}
    order by mb_name asc
    ";
    $emp_list = sql_query($emp_sql);
    $response = "";

    for ($i = 0; $emp = sql_fetch_array($emp_list); $i++) {
        $response .= '<option value="'.$emp['mb_no'].'">'.$emp['mb_name'].'</option>';
    }
    array_push( $result, $response );
    $response = "";
    
    //고객사코드
    $partner_sql = "
    select ptn_idx
        , ptn_nm
    from {$g5['crm_partner']} 
    where use_yn = 'Y'
    and ptn_deptno = {$deptno}
    order by ptn_idx desc
    ";
    $partner_list = sql_query($partner_sql);
    $response .= '<option value="">미지정</option>';
    for ($i = 0; $partner = sql_fetch_array($partner_list); $i++) {

        if($i == 0) {
            $response .= '<option value="'.$partner['ptn_idx'].'" selected>'.$partner['ptn_nm'].'</option>';
        } else {
            $response .= '<option value="'.$partner['ptn_idx'].'">'.$partner['ptn_nm'].'</option>';
        }
        
    }
    array_push( $result, $response );
    $response = "";

    //고객사 직원코드
    $response .= '<option value="">미지정</option>';
    array_push( $result, $response );
    // $ptn_member_sql = "
    // select mb_no 
    //     , mb_id 
    //     , mb_name
    //     , mb_ptnidx 
    // from {$g5['member_table']}
    // where mb_gubun = 'P'
    // and is_login = 'Y'
    // and mb_ptnidx = {$deptno}
    // ";
    // $ptn_member_list = sql_query($ptn_member_sql);
    // for ($i = 0; $ptn_mb = sql_fetch_array($ptn_member_list); $i++) {
    //     $response .= '<option value="'.$ptn_mb['mb_no'].'">'.$ptn_mb['mb_name'].'</option>';
    // }
    // array_push( $result, $response );

    //echo json_encode($response);
    echo json_encode($result);

} else if ($act === "ptnByEmp") {

    //파트너별직원리스트
    $result = array();

    $ptn_idx = isset($_POST['ptn_idx']) ? strip_tags($_POST['ptn_idx']) : '';

    $emp_sql = "
    select mb_no 
         , mb_id 
         , mb_name
         , mb_ptnidx 
         , mb_gubun
    from {$g5['member_table']}
    where mb_gubun != 'E'
    and is_login = 'Y'
    and mb_ptnidx = {$ptn_idx}
    ";
    $emp_list = sql_query($emp_sql);
    $response = "";

    for ($i = 0; $emp = sql_fetch_array($emp_list); $i++) {
        
        $mb_gubun = $emp['mb_gubun'] == "P" ? "대표" : "직원";

        $response .= '<option value="'.$emp['mb_no'].'">'.$emp['mb_name'].'('.$mb_gubun.')</option>';
    }
    array_push( $result, $response );

    $ptn_info_sql = "
    select ptn_bznm
         , ptn_reprnm
         , ptn_bznum
         , ptn_addr
         , ptn_tel
         , ptn_email
    from {$g5['crm_partner']} 
    where ptn_idx = $ptn_idx
    ";
    $row = sql_fetch($ptn_info_sql);
    array_push( $result, $row );

    echo json_encode($result);

} else if ($act === "designByCate") {

    //카테고리function
    $pg_des_idx = isset($_POST['pg_des_idx']) ? strip_tags($_POST['pg_des_idx']) : '';
    $getCateSql = "
    select f_getcode({$pg_des_idx}) as des_cate_code_nm 
    ";
    $row = sql_fetch($getCateSql);
    echo json_encode($row);
} else if ($act === "dup_member") {

    $mb_id = isset($_POST['mb_id']) ? strip_tags($_POST['mb_id']) : '';

    $dup_sql = "
    select count(*) as partnerCnt
    from {$g5['member_table']}
    where mb_id = '{$mb_id}'
    ";
    $row = sql_fetch($dup_sql);
    
    echo json_encode((int)$row['partnerCnt']);

} else if ($act === "condDeptByPtn") {

    $deptno = isset($_POST['deptno']) ? strip_tags($_POST['deptno']) : '';

    //고객사코드
    $partner_sql = "
    select ptn_idx
        , ptn_nm
    from {$g5['crm_partner']} 
    where use_yn = 'Y'
    and ptn_deptno = {$deptno}
    order by ptn_idx desc
    ";
    $partner_list = sql_query($partner_sql);

    $response .= '<option value="">미지정</option>';
    for ($i = 0; $partner = sql_fetch_array($partner_list); $i++) {
        $response .= '<option value="'.$partner['ptn_idx'].'">'.$partner['ptn_nm'].'</option>';
    }
    
    echo json_encode($response);

} else if ($act === "addWhiteIp") {

    $inpWhiteIp = isset($_POST['inpWhiteIp']) ? strip_tags($_POST['inpWhiteIp']) : '';

    if($inpWhiteIp == "" || $inpWhiteIp == null) {
        echo json_encode(0);
    } else {
        $resultOneSql = "
            select *
            from {$g5['crm_whiteip']}
            where insert_user = '{$member['mb_id']}'
            and temp_yn = 'Y'
            and now() between start_date and end_date;
            ";
        $resultOne = sql_fetch($resultOneSql);
        $start_date = strtotime($resultOne['start_date']);
        $end_date = strtotime($resultOne['end_date']);
        
        //수정시
        if ($resultOne != NULL) {
            // 현재 날짜를 가져옵니다.
            $currentDate = new DateTime();
            // 현재 날짜에 7일을 더하고 23시 59분 59초로 설정합니다.
            $expirationDate = $currentDate->add(new DateInterval('P7D'))->setTime(23, 59, 59);
            // 포맷을 맞춰서 사용합니다.
            $toDate = $expirationDate->format('Y-m-d H:i:s');

            $upd_sql = "
            update {$g5['crm_whiteip']} set 
                white_ip = '{$inpWhiteIp}'
                ,start_date = now()
                ,end_date = '{$toDate}'
            where insert_user = '{$member['mb_id']}'
            and temp_yn = 'Y'
            and now() between start_date and end_date; 
            ";

            isSqlError(sql_query($upd_sql), $upd_sql);
            echo json_encode(-1);
        } 
        //입력시
        else {
            $to_date = "";

            $mb_id = $member['mb_id'];
            $mb_name = $member['mb_name'];

            // 현재 날짜를 가져옵니다.
            $currentDate = new DateTime();
            // 현재 날짜에 7일을 더하고 23시 59분 59초로 설정합니다.
            $expirationDate = $currentDate->add(new DateInterval('P1M'))->setTime(23, 59, 59);
            // 포맷을 맞춰서 사용합니다.
            $toDate = $expirationDate->format('Y-m-d H:i:s');

            $ins_sql = "
            insert into {$g5['crm_whiteip']} (white_ip,start_date,end_date,insert_user,insert_user_name,temp_yn) values
            ('{$inpWhiteIp}',now(),'{$toDate}','{$mb_id}','{$mb_name}','Y');
            ";

            isSqlError(sql_query($ins_sql), $ins_sql);
            $white_idx = sql_insert_id();
            echo json_encode($white_idx);
        }
    }
} 
else if ($act === "aft_design_all") {
    $page = isset($_POST['page']) ? strip_tags($_POST['page']) : '';

    if($member['mb_deptno'] != 9) {
        $cond_gubun = "and des_deptno = {$member['mb_deptno']}";
        $order_gubun = "order by field(a.des_deptno, {$member['mb_deptno']}) desc, a.design_idx desc";
    } else {
        $order_gubun = "order by a.design_idx desc";
    }

    $design_sql = "
    select design_idx 
            , design_name
            , f_getcode(des_cate_code) as des_cate_code_nm
            , a.des_deptno 
            , b.deptnm
        from {$g5['crm_design']} a
        left join {$g5['crm_depart']} b on a.des_deptno = b.deptno 
        where a.use_yn = 'Y'
        {$cond_gubun}
        {$order_gubun}
        LIMIT $page, 18446744073709551615
    ";
    $design_list = sql_query($design_sql);
    $result = array();
    for ($i = 0; $design = sql_fetch_array($design_list); $i++) {
        $response .= '<option value="'.$design['design_idx'].'">'.$design['deptnm'].":".$design['design_name'].'</option>';
    }
    array_push( $result, $response );
    echo json_encode($result);

}
// else if ($act === "scroll_prc_design") {
//     $page = isset($_POST['page']) ? strip_tags($_POST['page']) : '';
//     $itemsPerPage = 50; // 페이지당 아이템 수
//     $offset = ($page - 1) * $itemsPerPage;
//     $cond_gubun = "";
//     $order_gubun = "";
//     if($member['mb_deptno'] != 9) {
//         $cond_gubun = "and des_deptno = {$member['mb_deptno']}";
//         $order_gubun = "order by field(a.des_deptno, {$member['mb_deptno']}) desc, a.design_idx desc";
//     } else {
//         $order_gubun = "order by a.design_idx desc";
//     }
//     $design_sql = "
//     select design_idx 
//          , design_name
//          , f_getcode(des_cate_code) as des_cate_code_nm
//          , a.des_deptno 
//          , b.deptnm
//       from {$g5['crm_design']} a
//       left join {$g5['crm_depart']} b on a.des_deptno = b.deptno 
//      where a.use_yn = 'Y'
//      {$cond_gubun}
//      {$order_gubun}
//      LIMIT $offset, $itemsPerPage
//     ";
//     $design_list = sql_query($design_sql);
//     $result = array();
//     for ($i = 0; $design = sql_fetch_array($design_list); $i++) {
//         $response .= '<option value="'.$design['design_idx'].'">'.$design['deptnm'].":".$design['design_name'].'</option>';
//     }
//     array_push( $result, $response );
//     echo json_encode($result);
// }




