<?php
require_once '../../common.php';

// header('Content-type: application/json'); 
// header('Access-Control-Allow-Origin: *');

$act = isset($_POST['act']) ? strip_tags($_POST['act']) : '';
$comm_pcd = isset($_POST['comm_pcd']) ? strip_tags($_POST['comm_pcd']) : '';

if ($act === "commonCode") {

    //공통코드리스트
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
    order by comm_pcd, comm_cd
    ";
    $code_list = sql_query($code_sql);
    $response = "";
    
    for ($i = 0; $code = sql_fetch_array($code_list); $i++) {
        $response .= '<option value="'.$code['comm_idx'].'">'.$code['comm_nm'].'</option>';
    }

    echo json_encode($response);

} else if ($act === "dup_partner") {

    $ptn_nm = isset($_POST['ptn_nm']) ? strip_tags($_POST['ptn_nm']) : '';
    
    $dup_result = 0; 

    $dup_sql = "
    select count(*) as partnerCnt
    from {$g5['crm_partner']} a 
    where a.ptn_nm = '{$ptn_nm}'
    ";
    $row = sql_fetch($dup_sql);
    $cnt = (int)$row['partnerCnt'];

    if($cnt > 0) {
        $dup_result = 1;  //partner명 중복
    } else {
        $dup_sql = "
        select count(*) as partnerCnt
        from {$g5['crm_signup']} a 
        where a.ptn_nm = '{$ptn_nm}'
        ";
        $row = sql_fetch($dup_sql);
        $cnt = (int)$row['partnerCnt'];
    
        if($cnt > 0) {
            $dup_result = 2;  //partner명 가입대기와 중복
        }
    }

    echo json_encode((int)$dup_result);

    //echo json_encode($dup_result);

} else if ($act === "dup_member") {

    $mb_id = isset($_POST['mb_id']) ? strip_tags($_POST['mb_id']) : '';

    $dup_sql = "
    select count(*) as partnerCnt
    from {$g5['member_table']}
    where mb_id = '{$mb_id}'
    ";
    $row = sql_fetch($dup_sql);
    
    echo json_encode((int)$row['partnerCnt']);

} else if ($act === "dup_member2") {

    $row1 =  0;
    $ptn_id = isset($_POST['ptn_id']) ? strip_tags($_POST['ptn_id']) : '';

    $dup_sql1 = "
    select count(*) as partnerCnt
    from {$g5['member_table']}
    where mb_id = '{$ptn_id}'
    ";
    $row1 = sql_fetch($dup_sql1);


    $dup_sql2 = "
    select count(*) as partnerCnt
    from {$g5['crm_signup']}
    where ptn_id = '{$ptn_id}'
    ";
    $row2 = sql_fetch($dup_sql2);


    $dup_sql3 = "
    select count(*) as partnerCnt
    from {$g5['crm_partner']}
    where ptn_id = '{$ptn_id}'
    ";
    $row3 = sql_fetch($dup_sql3);


    $count = $row1['partnerCnt'] + $row2['partnerCnt'] + $row3['partnerCnt'];

    echo json_encode((int)$count);

} 
else if ($act === "load_partner") {
    $ptn_nm = isset($_POST['ptn_nm']) ? strip_tags($_POST['ptn_nm']) : '';
    
    $load_ptn = "
    select sign_idx
        ,  ptn_id
        ,  ptn_nm
        ,  ptn_reprnm
        ,  ptn_phone
        ,  ptn_tel
        ,  ptn_email
        ,  insert_date
    from {$g5['crm_signup']}
    where ptn_nm = '{$ptn_nm}'
    ";
    $row = sql_fetch($load_ptn);
    echo json_encode($row);

}
else if ($act === "modalAddPtnEmp") {
    $deptno = isset($_POST['deptno']) ? strip_tags($_POST['deptno']) : '';
    
    //고객사코드
    $partner_sql = "
    select ptn_idx
         , ptn_nm
    from {$g5['crm_partner']} 
    where use_yn = 'Y'
      and ptn_status <= 3
    and ptn_deptno = {$deptno}
    order by ptn_idx desc
    ";
    $partner_list = sql_query($partner_sql);

    $response .= '<option value="">미선택</option>';
    for ($i = 0; $partner = sql_fetch_array($partner_list); $i++) {
        $response .= '<option value="'.$partner['ptn_idx'].'">'.$partner['ptn_nm'].'</option>';
    }
    
    echo json_encode($response);

}  
else if ($act === "modalListPtnEmp") {
    $ptn_idx = isset($_POST['ptn_idx']) ? strip_tags($_POST['ptn_idx']) : '';

    $ptnEmpSql = "
    select *
    from {$g5['member_table']} 
    where is_login = 'Y'
    and mb_ptnidx = {$ptn_idx}
    ";
    $ptnEmpList = sql_query($ptnEmpSql);

    for ($i = 0; $ptnemp = sql_fetch_array($ptnEmpList); $i++) {
        $mb_gubun = $ptnemp['mb_gubun'] == 'P' ? '대표' : '직원';

        $opt1 = "";
        $opt2 = "";

        if($ptnemp['mb_gubun'] == "P") {
            $mb_gubun = '대표';
            $opt1 = "selected";
        } else if($ptnemp['mb_gubun'] == "C") {
            $mb_gubun = '직원';
            $opt2 = "selected";
        }

        $response .= '
        <tr>
            <td>'.($i+1).'</td>
            <td>'.$ptnemp['mb_id'].'</td>
            <td><input type="text" id="upd_mb_name" name="mb_name" class="" value="'.$ptnemp['mb_name'].'" maxlength="5"/></td>
            <td>'.$ptnemp['mb_open_date'].'</td>
            <td><input type="text" id="upd_mb_hp" name="mb_hp" class="" value="'.$ptnemp['mb_hp'].'" oninput="telHyphen(this);" maxlength="13"/></td>
            <td>
            <select id="upd_mb_gubun" name="mb_gubun" class="custom_select">
                <option value="P" '.$opt1.'>대표</option>
                <option value="C" '.$opt2.'>직원</option>
            </select>
            </td>
            <td>
            <button type="button" class="btn btn-primary btn-xs listbtn" onclick="initPw(\''.$ptnemp['mb_id'].'\');">초기</button>
            <button type="button" class="btn btn-danger btn-xs listbtn" onclick="delPtnEmp('.$ptnemp['mb_no'].');">삭제</button>
            </td>
        </tr>
        ';
    }
    echo json_encode($response);
} else if ($act === "initPtnEmpPW") {
    
    $mb_id = isset($_POST['mb_id']) ? strip_tags($_POST['mb_id']) : '';
    $mb_password = isset($_POST['mb_password']) ? strip_tags($_POST['mb_password']) : '';
    $encode_pw = get_encrypt_string($mb_password); 

    $sql = "
    update {$g5['member_table']} set
        mb_password = '{$encode_pw}'
    where mb_id = '$mb_id'
    ";
    isSqlError(sql_query($sql), $sql);

    echo json_encode("초기화처리하였습니다.");
} else if ($act === "delPtnEmp") {
    
    $mb_no = isset($_POST['mb_no']) ? strip_tags($_POST['mb_no']) : '';

    $sql = "
    delete from {$g5['member_table']}
    where mb_no = '$mb_no'
    ";
    isSqlError(sql_query($sql), $sql);
    echo json_encode("삭제하였습니다.");
    
} else if ($act === "dup_email") {
    
    $mb_email = isset($_POST['mb_email']) ? strip_tags($_POST['mb_email']) : '';

    $dup_sql = "
    select count(*) as emailCnt
    from {$g5['member_table']}
    where mb_email = '{$mb_email}'
    ";
    $row = sql_fetch($dup_sql);
    
    echo json_encode((int)$row['emailCnt']);
    
} else if ($act === "upd_mb_hp") {
    
    $mb_id = isset($_POST['mb_id']) ? strip_tags($_POST['mb_id']) : '';
    $mb_hp = isset($_POST['mb_hp']) ? strip_tags($_POST['mb_hp']) : '';
    $mb_name = isset($_POST['mb_name']) ? strip_tags($_POST['mb_name']) : '';
    $mb_gubun = isset($_POST['mb_gubun']) ? strip_tags($_POST['mb_gubun']) : '';

    $sql = "
    update {$g5['member_table']} set
        mb_hp    = '{$mb_hp}'
       ,mb_name  = '{$mb_name}'
       ,mb_gubun = '{$mb_gubun}'
    where mb_id  = '$mb_id'
    ";
    isSqlError(sql_query($sql), $sql);

    echo json_encode("연락처가 저장되었습니다.");
    
} else if ($act === "chg_ptn_share") {
    
    $ptn_idx = isset($_POST['ptn_idx']) ? strip_tags($_POST['ptn_idx']) : '';
    
    $code_sql = "
    select page_idx 
        , pg_domain 
        , pg_uri 
    from {$g5['crm_page']} a
    where use_yn = 'Y'
    and pg_deptno = {$member['mb_deptno']}
    and pg_ptn_idx = {$ptn_idx}
    and not exists (select 1 from {$g5['crm_db_share']} b where a.page_idx = b.share_parent_page_idx) 
    order by page_idx desc
    ";
    $code_list = sql_query($code_sql);

    $response .= '<option value="" readonly>미선택</option>';  
    
    for ($i = 0; $code = sql_fetch_array($code_list); $i++) {
        
        $response .= '<option value="'.$code['page_idx'].'">'.$code['pg_uri'].'</option>';
    }
        
    echo json_encode($response);
} else if ($act === "init_db_share") {
    
    $ptn_idx = isset($_POST['ptn_idx']) ? strip_tags($_POST['ptn_idx']) : '';
    $page_idx = isset($_POST['hidden_page_idx']) ? strip_tags($_POST['hidden_page_idx']) : '';
  
    
    $share_find_sql = "
    select pg_domain
         , pg_uri
      from {$g5['crm_page']} a
      where page_idx = {$page_idx}
    ";
    $share_find = sql_fetch($share_find_sql);

  
    $delSql = "
    delete 
    from {$g5['crm_db_share']}
    where share_parent_ptn = {$ptn_idx}
    ";
    isSqlError(sql_query($delSql), $delSql);

    $pg_domain = $share_find['pg_domain'];
    $pg_uri = $share_find['pg_uri'];
    
    $find_html = DOCUMENT_ROOT . "landing/$pg_domain/$pg_uri/index.html";
    $html_content = file_get_contents($find_html);
    
    $pattern = '/(\s*var\s+input_share\s*=\s*document\.createElement\("input"\);\s*'
             . 'input_share\.type\s*=\s*"hidden";\s*'
             . 'input_share\.name\s*=\s*"share_token";\s*'
             . 'input_share\.value\s*=\s*"[^"]*";\s*'
             . 'form\.appendChild\(\s*input_share\s*\);)(\r?\n)?/s';
    
    $modified_content = preg_replace_callback($pattern, function($matches) {
        // 삭제한 코드 블록 뒤의 개행 문자를 유지
        return isset($matches[2]) ? $matches[2] : '';
    }, $html_content, 1);
    
    $bytesWritten = file_put_contents($find_html, $modified_content);
    
    if ($bytesWritten === false) {
        echo "Failed to write to file";
    } else {
        echo json_encode('삭제완료');
    }

} else if ($act === "delete_ptn_hist") {
    $hist_idx = isset($_POST['hist_idx']) ? strip_tags($_POST['hist_idx']) : '';

    $partner_del_sql = "
    delete from {$g5['crm_partner_hist']} where hist_idx = {$hist_idx}
    ";
    $partner_del = sql_query($partner_del_sql);
    echo json_encode("삭제완료");
} else if ($act === "show_all_page") {
    $ptn_idx = isset($_POST['ptn_idx']) ? strip_tags($_POST['ptn_idx']) : '';

    $page_sql = "
        SELECT 
            page_idx,
            pg_domain, 
            pg_uri, 
            DATE(a.insert_date) AS insert_date,
            b.mb_name 
        FROM {$g5['crm_page']} a
        LEFT JOIN {$g5['member_table']} b ON a.pg_mb_emp = b.mb_no  
        WHERE pg_ptn_idx = '{$ptn_idx}'
        ORDER BY a.insert_date desc
    ";

    $result = sql_query($page_sql);

    $table = '<table table id="dynamicTable" class="table table-striped table-bordered">';
    $table .= '<thead><tr><th>순번</th><th>도메인</th><th>코드명</th><th>담당자</th><th>생성일</th></tr></thead>';
    $table .= '<tbody>';
    $row_number = 1; 

    while ($row = sql_fetch_array($result)) {
        $goto_page_db_list = G5_BIZ_URL . '/landing/land_list?sfl=pg_uri&stx=' . htmlspecialchars($row['pg_uri']);
        $goto_landing_link = 'https://' . htmlspecialchars($row['pg_domain']) . '/' . htmlspecialchars($row['pg_uri']);
        $page_link = G5_BIZ_URL . '/page/page_form?w=u&page_idx=' . htmlspecialchars($row['page_idx']); // 추가된 링크

        $table .= '<tr>';
        $table .= '<td>' . $row_number . '</td>'; // 순번 추가
        $table .= '<td>' . htmlspecialchars($row['pg_domain']) . '</td>';
        $table .= '<td>';
        $table .= '<a href="' . $goto_page_db_list . '" target="_self">' . htmlspecialchars($row['pg_uri']) . '</a>';
        $table .= ' <a href="' . $page_link . '" target="_self"><i class="fas fa-link"></i></a>'; // 새 링크 추가
        $table .= ' <i class="fas fa-plane-departure text-success" style="cursor: pointer;" onclick="window.open(\'' . $goto_landing_link . '\', \'_blank\');"></i>';
        $table .= '</td>';
        $table .= '<td>' . htmlspecialchars($row['mb_name']) . '</td>';
        $table .= '<td>' . htmlspecialchars($row['insert_date']) . '</td>';
        $table .= '</tr>';

        $row_number++; 
    }

    $table .= '</tbody></table>';
    echo json_encode(['html' => $table]);


}




