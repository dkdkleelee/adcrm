<?php
require_once '../../common.php';


$act = isset($_POST['act']) ? strip_tags($_POST['act']) : '';

if ($act === "dup_uri") {
    
    $pg_uri = isset($_POST['pg_uri']) ? strip_tags($_POST['pg_uri']) : '';
    
    $cnt = 0; 

    $dup_sql = "
    select count(*) as uriCnt
    from {$g5['crm_page']} a 
    where a.pg_uri = '{$pg_uri}'
    ";
    $row = sql_fetch($dup_sql);
    $cnt = (int)$row['uriCnt'];

    echo json_encode((int)$cnt);

} else if ($act === "getScript") {
    $script_sql = "
    select script_name
         , script_code
    from {$g5['crm_page_script']} 
    where script_idx = '{$script_idx}'
    ";
    $row = sql_fetch($script_sql);
    echo json_encode($row);
} else if ($act === "page_delete") {
    $script_idx = isset($_POST['script_idx']) ? strip_tags($_POST['script_idx']) : '';

    $delete_sql = "
    delete from {$g5['crm_page_script']} where script_idx = {$script_idx}
    ";

    $delete_result = sql_query($delete_sql);

    if($delete_result)
        echo "삭제완료";
    else 
        echo "에러발생";
} else if ($act === "desByCate") {
    $design_idx = isset($_POST['design_idx']) ? strip_tags($_POST['design_idx']) : '';

    $ptn_lastest_sql = "
    select design_idx
         , f_getcode(des_cate_code) as des_cate_code_nm
         , des_html
    from {$g5['crm_design']} a
    where use_yn = 'Y'
    and design_idx = {$design_idx}
    limit 0,1
    ";
    $row = sql_fetch($ptn_lastest_sql);

    $des_html = $row['des_html'];

    $isOption1 = false;
    $isOption2 = false;
    $isOption3 = false;
    $isOption4 = false;
    $isOption5 = false;
    $isOption6 = false;
    $isOption7 = false;
    $isOption8 = false;
    $isOption9 = false;

    $linkByDes = G5_BIZ_URL.'/design/design_form?w=u&design_idx='.$row['design_idx'];
    $row['linkByDes'] = $linkByDes;

    $isOption = []; // 이 배열에 각 isOption 값을 저장합니다.

    for ($i = 9; $i >= 1; $i--) {
        $key = 'option' . $i . '"';
        $isOption[$i] = strpos($des_html, $key) !== false; // true 또는 false로 저장됩니다.
    }

    if (strpos($des_html, 'name') !== false) {
        $row['name'] = true;
    } else {
        $row['name'] = false;
    }

    // des_html 삭제
    //unset($row['des_html']);

    // isOption 값을 $row에 추가
    foreach ($isOption as $i => $value) {
        $row['isOption' . $i] = $value;
    }

    echo json_encode($row);
} else if ($act === "initAddParam") {
    $page_idx = isset($_POST['page_idx']) ? strip_tags($_POST['page_idx']) : '';
    $sql = "
    update {$g5['crm_page']} set
    pg_api_add_param = '' 
    where page_idx = {$page_idx}
    ";
    isSqlError(sql_query($sql), $sql);
    echo json_encode('success');
} else if ($act === "checkForSms") {

    $result = array();

    $ptn_idx = isset($_POST['ptn_idx']) ? strip_tags($_POST['ptn_idx']) : '';

    $ptn_info_sql = "
    select ptn_idx
         , ptn_nm  
         , ptn_bznm
         , ptn_reprnm
         , ptn_bznum
         , ptn_addr
         , ptn_tel
         , ptn_email
         , ptn_send_sms_yn
         , ptn_send_sms_idx
         , ptn_send_sms_msg
    from {$g5['crm_partner']} 
    where ptn_idx = $ptn_idx
    ";
    $row = sql_fetch($ptn_info_sql);

    if($row['ptn_send_sms_yn'] == "Y") {
        
        $row = sql_fetch($ptn_info_sql);
        array_push( $result, $row );

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
        $response .= '<option value="" disabled>[미선택시]직원전체발송</option>';

        for ($i = 0; $emp = sql_fetch_array($emp_list); $i++) {
            
            $mb_gubun = $emp['mb_gubun'] == "P" ? "대표" : "직원";

            $response .= '<option value="'.$emp['mb_no'].'">'.$emp['mb_name'].'('.$mb_gubun.')</option>';
        }
        array_push( $result, $response );

        echo json_encode($result);
    } else {
        
    }
    

    


   
} else if ($act === "onChgPtn") {

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
         , pg_db_sms_yn
         , pg_db_sms_msg
    from {$g5['crm_partner']} 
    where ptn_idx = $ptn_idx
    ";
    $row = sql_fetch($ptn_info_sql);
    array_push( $result, $row );

    echo json_encode($result);

}  else if ($act === "page_copy") {

    $my_emp_no = $member['mb_no'];

    $deptno = $member['mb_deptno'];
    $result = array();
    $emp_sql = "
        select mb_no 
            , mb_id 
            , mb_name
            , mb_ptnidx 
        from {$g5['member_table']}
        where mb_gubun = 'E'
        and is_login = 'Y'
        and mb_deptno = {$deptno}
        order by mb_no
        ";
    $emp_list = sql_query($emp_sql);
    $response = "";
    
    for ($i = 0; $emp = sql_fetch_array($emp_list); $i++) {
        $selectedOpt = "";
        if($emp['mb_no'] == $my_emp_no) {
            $selectedOpt = " selected";
        }
        $response .= '<option value="'.$emp['mb_no'].'"'. $selectedOpt .'>'.$emp['mb_name'].'</option>';
    }
    array_push( $result, $response );

    echo json_encode($result);
} else if ($act === "adapt_ptn_page") {

    $pg_ptn_idx = isset($_POST['pg_ptn_idx']) ? strip_tags($_POST['pg_ptn_idx']) : '';

    $pg_chk_data1 = isset($_POST['pg_chk_data1']) ? strip_tags($_POST['pg_chk_data1']) : '';
    $pg_chk_data2 = isset($_POST['pg_chk_data2']) ? strip_tags($_POST['pg_chk_data2']) : '';
    $pg_chk_data3 = isset($_POST['pg_chk_data3']) ? strip_tags($_POST['pg_chk_data3']) : '';
    $pg_chk_data4 = isset($_POST['pg_chk_data4']) ? strip_tags($_POST['pg_chk_data4']) : '';
    $pg_chk_data5 = isset($_POST['pg_chk_data5']) ? strip_tags($_POST['pg_chk_data5']) : '';
    $pg_chk_data6 = isset($_POST['pg_chk_data6']) ? strip_tags($_POST['pg_chk_data6']) : '';
    $pg_chk_data7 = isset($_POST['pg_chk_data7']) ? strip_tags($_POST['pg_chk_data7']) : '';
    $pg_chk_data8 = isset($_POST['pg_chk_data8']) ? strip_tags($_POST['pg_chk_data8']) : '';
    $pg_chk_data9 = isset($_POST['pg_chk_data9']) ? strip_tags($_POST['pg_chk_data9']) : '';

    $pg_chk_code = isset($_POST['pg_chk_code']) ? strip_tags($_POST['pg_chk_code']) : '';
    $pg_chk_utm = isset($_POST['pg_chk_utm']) ? strip_tags($_POST['pg_chk_utm']) : '';
    $pg_chk_ip = isset($_POST['pg_chk_ip']) ? strip_tags($_POST['pg_chk_ip']) : '';

    $upd_sql = "
    update {$g5['crm_page']} set  
      pg_chk_data1 = '{$pg_chk_data1}'
    , pg_chk_data2 = '{$pg_chk_data2}'
    , pg_chk_data3 = '{$pg_chk_data3}'
    , pg_chk_data4 = '{$pg_chk_data4}'
    , pg_chk_data5 = '{$pg_chk_data5}'
    , pg_chk_data6 = '{$pg_chk_data6}'
    , pg_chk_data7 = '{$pg_chk_data7}'
    , pg_chk_data8 = '{$pg_chk_data8}'
    , pg_chk_data9 = '{$pg_chk_data9}'
    , pg_chk_code  = '{$pg_chk_code}'
    , pg_chk_utm  = '{$pg_chk_utm}'
    , pg_chk_ip    = '{$pg_chk_ip}'
    where pg_ptn_idx = {$pg_ptn_idx}
    ";
    isSqlError(sql_query($upd_sql), $upd_sql);
    echo json_encode("success");

   
} else if ($act === "chgPtn") {

    $page_idx_list = $_POST['page_idx_list']; // 배열
    $selected_partner = $_POST['selected_partner'];
    $orginal_partner = $_POST['orginal_partner'];

    foreach ($page_idx_list as $idx) {
        $upd_sql = "
            update {$g5['crm_page']} set
              pg_ptn_idx = {$selected_partner}
            , update_date = now()
            , update_user = '{$member['mb_id']}'
            , update_user_name = '{$member['mb_name']}'
            where page_idx = {$idx}
        ";
        isSqlError(sql_query($upd_sql), $upd_sql);
    }
    echo json_encode("success");
} 


else if ($act === "delete_aft_pop_img") {

    $page_idx = (int)$_POST['page_idx'];
    $file_idx = (int)$_POST['file_idx'];

    $sql = " select file_name from {$g5['crm_aft_ad_file']} where file_idx = '$file_idx' and page_idx = '$page_idx' ";
    $row = sql_fetch($sql);

    if ($row['file_name']) {
        // 물리 파일 삭제
        $path = G5_DATA_PATH.'/file/aftpop/'.$page_idx.'/'.$row['file_name'];
        @unlink($path);

        // DB 삭제
        sql_query(" delete from {$g5['crm_aft_ad_file']} where file_idx = '$file_idx' ");
        // 메인 테이블 인덱스 업데이트
        sql_query(" update {$g5['crm_page']} set pg_aft_ad_file_idx = 0 where page_idx = '$page_idx' ");

        echo json_encode(array("status" => "success"));
    } else {
        echo json_encode(array("status" => "fail", "msg" => "파일 정보를 찾을 수 없습니다."));
    }
    echo json_encode("success");
} else if ($act == 'ajax_aft_pop_img_upload') {
    $page_idx = isset($_POST['page_idx']) ? (int)$_POST['page_idx'] : 0;
    
    // 1. 페이지 인덱스 검증
    if (!$page_idx) {
        echo json_encode(array("status" => "error", "msg" => "페이지 정보가 없습니다. (먼저 페이지를 저장해주세요)"));
        exit;
    }

    // 2. 파일 전송 여부 확인
    if (!isset($_FILES['aft_file']) || !$_FILES['aft_file']['name']) {
        echo json_encode(array("status" => "error", "msg" => "업로드된 파일이 없습니다."));
        exit;
    }

    // 3. 물리적 저장 경로 설정 (요청하신 경로 반영)
    $dest_path = G5_DATA_PATH . '/file/aftpop/' . $page_idx;
    $dest_url  = G5_DATA_URL . '/file/aftpop/' . $page_idx; // 프론트엔드 이미지 표시용 URL

    // 폴더가 없으면 생성 (하위 폴더까지)
    if (!is_dir($dest_path)) {
        @mkdir($dest_path, G5_DIR_PERMISSION, true); 
        @chmod($dest_path, G5_DIR_PERMISSION);
    }

    // 4. 파일명 처리
    $filename = $_FILES['aft_file']['name'];
    $file_size = $_FILES['aft_file']['size'];
    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    // 확장자 보안 검사
    if (!in_array($file_ext, array('jpg', 'jpeg', 'png', 'gif', 'webp'))) {
        echo json_encode(array("status" => "error", "msg" => "이미지 파일(jpg, png, gif, webp)만 업로드 가능합니다."));
        exit;
    }

    // 파일명 난수화 (덮어쓰기 방지 및 브라우저 캐시 이슈 회피)
    $new_filename = 'pop_' . date('YmdHis') . '_' . substr(md5(microtime()), 0, 4) . '.' . $file_ext;
    $upload_file_path = $dest_path . '/' . $new_filename;

    // 5. 파일 업로드 실행
    if (move_uploaded_file($_FILES['aft_file']['tmp_name'], $upload_file_path)) {
        
        // 5-1. 기존 이미지가 있다면 DB 삭제 및 물리 파일 삭제 (해당 페이지는 이미지 1개만 허용)
        $old_file = sql_fetch("SELECT * FROM gnp_crm_aft_pop_file WHERE page_idx = '{$page_idx}'");
        if ($old_file) {
            $old_real_path = $dest_path . '/' . $old_file['file_name'];
            @unlink($old_real_path);
            sql_query("DELETE FROM gnp_crm_aft_pop_file WHERE file_idx = '{$old_file['file_idx']}'");
        }

        // 5-2. 신규 이미지 DB 저장
        $insert_sql = " INSERT INTO gnp_crm_aft_pop_file
                        SET page_idx    = '{$page_idx}',
                            file_source = '{$filename}',
                            file_name   = '{$new_filename}',
                            file_path   = '{$dest_url}',
                            file_size   = '{$file_size}',
                            insert_date = NOW() ";
        sql_query($insert_sql);
        $new_idx = sql_insert_id();

        // 5-3. 성공 응답 리턴 (JSON)
        echo json_encode(array(
            "status"   => "success",
            "url"      => $dest_url . '/' . $new_filename,
            "file_idx" => $new_idx
        ));
    } else {
        echo json_encode(array("status" => "error", "msg" => "서버 폴더에 파일을 저장하는 데 실패했습니다."));
    }
    exit;
}

else if ($act == 'ajax_aft_pop_img_delete') {
    $file_idx = isset($_POST['file_idx']) ? (int)$_POST['file_idx'] : 0;

    if (!$file_idx) {
        echo json_encode(array("status" => "error", "msg" => "잘못된 접근입니다. (파일 번호 없음)"));
        exit;
    }

    // 1. 삭제할 파일 정보 조회
    $row = sql_fetch("SELECT * FROM gnp_crm_aft_pop_file WHERE file_idx = '{$file_idx}'");
    if ($row) {
        
        // 2. 물리 파일 삭제 (file_path가 URL 형태일 수 있으므로 PATH로 변환)
        $dest_path = G5_DATA_PATH . '/file/aftpop/' . $row['page_idx'];
        $real_file_path = $dest_path . '/' . $row['file_name'];
        @unlink($real_file_path);

        // 3. DB 데이터 삭제
        sql_query("DELETE FROM gnp_crm_aft_pop_file WHERE file_idx = '{$file_idx}'");
        
        echo json_encode(array("status" => "success"));
    } else {
        echo json_encode(array("status" => "error", "msg" => "삭제할 파일 정보를 DB에서 찾을 수 없습니다."));
    }
    exit;
}

