<?php
require_once '../../common.php';

$act = isset($_POST['act']) ? strip_tags($_POST['act']) : '';

if ($act === "add_comp") {

    //부서별직원코드
    $member_sql = "
    select mb_no 
        , mb_id 
        , mb_name
        , mb_deptno 
    from {$g5['member_table']}
    where mb_gubun = 'E'
    and mb_deptno = 11
    and is_login = 'Y'
    order by mb_name
    ";

    $member_list = sql_query($member_sql);
    
    $options = '';  
    for ($i = 0; $member = sql_fetch_array($member_list); $i++) {
        $options .= '<option value="'.$member['mb_no'].'">'.$member['mb_name'].'</option>';
    }

    echo json_encode($options);

} else if ($act === "add_acc") {

    //부서별직원코드
    $member_sql = "
    select mb_no 
        , mb_id 
        , mb_name
        , mb_deptno 
    from {$g5['member_table']}
    where mb_gubun = 'E'
    and mb_deptno = 11
    and is_login = 'Y'
    order by mb_name
    ";

    $member_list = sql_query($member_sql);
    
    $options = '';  
    for ($i = 0; $member = sql_fetch_array($member_list); $i++) {
        $options .= '<option value="'.$member['mb_no'].'">'.$member['mb_name'].'</option>';
    }

    echo json_encode($options);

} else if ($act === "upd_comp") {

    $customerLinkId = isset($_POST['customerLinkId']) ? strip_tags($_POST['customerLinkId']) : '';
    $mb_no = isset($_POST['mb_no']) ? strip_tags($_POST['mb_no']) : 'NULL';

    $result = array();

     //부서별직원코드
     $member_sql = "
     select b.mb_no
          , b.comp_name
          , b.rpt_term
          , b.rpt_type
          , is_sms_bizmoney
          , cond_bizmoney
     from {$g5['member_table']} a
     left join gnp_kwd_customers b on a.mb_no = b.mb_no 
     where b.customerLinkId = '{$customerLinkId}'
     ";
 
     $resultOne = sql_fetch($member_sql);
     $stack = array();
     $stack['comp_name'] = $resultOne['comp_name'];
     $stack['mb_no'] = $resultOne['mb_no'];
     $stack['rpt_term'] = $resultOne['rpt_term'];
     $stack['rpt_type'] = $resultOne['rpt_type'];

     $stack['is_sms_bizmoney'] = $resultOne['is_sms_bizmoney'];
     $stack['cond_bizmoney'] = $resultOne['cond_bizmoney'];
     array_push( $result, $stack );


    $member_sql = "
    select mb_no 
        , mb_id 
        , mb_name
        , mb_deptno 
    from {$g5['member_table']}
    where mb_gubun = 'E'
    and mb_deptno = 11
    and is_login = 'Y'
    order by mb_name
    ";
    $member_list = sql_query($member_sql);

    $options = '';  
    $mb_no = $resultOne['mb_no'];

    for ($i = 0; $member = sql_fetch_array($member_list); $i++) {
        if($member['mb_no'] == $mb_no) {
            $options .= '<option value="'.$member['mb_no'].'" selected>'.$member['mb_name'].'</option>';
        } else {
            $options .= '<option value="'.$member['mb_no'].'">'.$member['mb_name'].'</option>';
        }
        
    }
    array_push( $result, $options );

   
    echo json_encode($result);

} else if ($act === "lst-naver") {

    $lstSql = "
    select *
         , ifnull (f_get_mb_name(mb_no), mb_no) as mb_emp_name
    from gnp_kwd_naver_acct
    where use_yn = 'Y'
    ";
    $lst = sql_query($lstSql);

    for ($i = 0; $row = sql_fetch_array($lst); $i++) {
        $response .= '
        <tr id="tr_'.$row['naver_idx'].'">
            <td>'.($i+1).'</td>
            <td style="display:none">'.$row['naver_idx'].'</td>
            <td>'.$row['naver_id'].'</td>
            <td>'.$row['naver_pw'].'</td>
            <td>'.$row['customer_id'].'</td>
            <td style="display:none">'.$row['access_license'].'</td>
            <td style="display:none">'.$row['access_secretkey'].'</td>
            <td>'.$row['mb_emp_name'].'</td>
            <td>'.view_dateformat($row['insert_date']).'</td>
            <td>'.view_dateformat($row['update_date']).'</td>
            <td>
            <button type="button" class="btn btn-primary btn-xs listbtn" onclick="click_modal_upd(this);">수정</button>
            <button type="button" class="btn btn-danger btn-xs listbtn" onclick="click_modal_del(this);">삭제</button>
            </td>
        </tr>
        ';
    }
    echo json_encode($response);
} else if ($act === "ajax_add_naver") {

    $naver_id = isset($_POST['naver_id']) ? strip_tags(clean_xss_attributes($_POST['naver_id'])) : '';
    $naver_pw = isset($_POST['naver_pw']) ? strip_tags(clean_xss_attributes($_POST['naver_pw'])) : '';
    $customer_id = isset($_POST['customer_id']) ? strip_tags(clean_xss_attributes($_POST['customer_id'])) : '';
    $access_license = isset($_POST['access_license']) ? strip_tags(clean_xss_attributes($_POST['access_license'])) : '';
    $access_secretkey = isset($_POST['access_secretkey']) ? strip_tags(clean_xss_attributes($_POST['access_secretkey'])) : '';
    $mb_no = isset($_POST['mb_no']) ? strip_tags(clean_xss_attributes($_POST['mb_no'])) : '';
        
    $naver_add_sql = "
    insert into gnp_kwd_naver_acct set  
      naver_id = '{$naver_id}'
    , naver_pw = '{$naver_pw}'
    , customer_id = {$customer_id}
    , access_license = '{$access_license}'
    , access_secretkey = '{$access_secretkey}'
    , mb_no = {$mb_no}
    , use_yn = 'Y'
    , insert_date = now()
    , insert_user = '{$member['mb_id']}'
    , insert_user_name = '{$member['mb_name']}'
    , update_date = now()
    , update_user = '{$member['mb_id']}'
    , update_user_name = '{$member['mb_name']}'
    ";
    isSqlError(sql_query($naver_add_sql), $naver_add_sql);
    goto_url('./kwd_customers_list?' . $qstr);

} else if ($act === "ajax_upd_naver") {

    $naver_idx = isset($_POST['naver_idx']) ? strip_tags(clean_xss_attributes($_POST['naver_idx'])) : '';
    $naver_id = isset($_POST['naver_id']) ? strip_tags(clean_xss_attributes($_POST['naver_id'])) : '';
    $naver_pw = isset($_POST['naver_pw']) ? strip_tags(clean_xss_attributes($_POST['naver_pw'])) : '';
    $customer_id = isset($_POST['customer_id']) ? strip_tags(clean_xss_attributes($_POST['customer_id'])) : '';
    $access_license = isset($_POST['access_license']) ? strip_tags(clean_xss_attributes($_POST['access_license'])) : '';
    $access_secretkey = isset($_POST['access_secretkey']) ? strip_tags(clean_xss_attributes($_POST['access_secretkey'])) : '';
    
    $naver_upd_sql = "
    update gnp_kwd_naver_acct set  
      naver_id = '{$naver_id}'
    , naver_pw = '{$naver_pw}'
    , customer_id = {$customer_id}
    , access_license = '{$access_license}'
    , access_secretkey = '{$access_secretkey}'
    where naver_idx = {$naver_idx}
    ";
    isSqlError(sql_query($naver_upd_sql), $naver_upd_sql);
    goto_url('./kwd_customers_list?' . $qstr);


} else if ($act === "ajax_del_naver") {

    $naver_idx = isset($_POST['naver_idx']) ? strip_tags($_POST['naver_idx']) : '';

    $delSql = "
    delete from gnp_kwd_naver_acct
    where naver_idx = $naver_idx
    ";
    isSqlError(sql_query($delSql), $delSql);
    echo json_encode("success");

} else if ($act === "del_comp") {

    $customerLinkId = isset($_POST['customerLinkId']) ? strip_tags($_POST['customerLinkId']) : '';

    $delSql = "
    delete from gnp_kwd_customers
    where customerLinkId = $customerLinkId
    ";
    isSqlError(sql_query($delSql), $delSql);
    echo json_encode("deleted");
} else if ($act === "get_calc_emp") {

    $charge_sql = "
    select chg_naver_id, chg_emp_no, chg_emp_name
    from gnp_kwd_calc_charge 
    where use_yn = 'Y'
    order by chg_emp_name asc
    ";
    $charge_list = sql_query($charge_sql);

    $result = array();
    while($row = sql_fetch_array($charge_list)) {
        $result[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($result);

   
} else if ($act === "del_calc_emp") {

    $naver_id = isset($_POST['naver_id']) ? strip_tags($_POST['naver_id']) : '';
    $emp_no = isset($_POST['emp_no']) ? strip_tags($_POST['emp_no']) : '';

    $update_sql = "
    update gnp_kwd_calc_charge set
    use_yn = 'N'
    where chg_naver_id = '{$naver_id}'
    and chg_emp_no = '{$emp_no}'
    ";
    $updated = sql_query($update_sql);

    echo json_encode('updated');
   
} else if ($act === 'get_emp_list') {

    $result = array();

    $emp_sql = "
    select mb_no 
         , mb_id 
         , mb_name
    from {$g5['member_table']}
    where mb_gubun = 'E'
    and is_login = 'Y'
    and mb_deptno = 11
    order by mb_name asc
    ";
    $emp_list = sql_query($emp_sql);

    $response = "";
    $response .= '<option value="" disabled>미선택</option>';

    for ($i = 0; $emp = sql_fetch_array($emp_list); $i++) {
        $response .= '<option value="'.$emp['mb_no'].'||'.$emp['mb_name'].'">'.$emp['mb_name'].'</option>';
    }

    array_push( $result, $response );
    echo json_encode($result);
    
    
} else if ($act === 'pick_ownerless') {
    $mb_no = isset($_POST['my_emp']) ? strip_tags($_POST['my_emp']) : '';
    $naver_id = isset($_POST['naver_id']) ? strip_tags($_POST['naver_id']) : '';

    $mb_id = $member['mb_id'];
    $mb_name = $member['mb_name'];


    $insert_sql = "
    insert into gnp_kwd_calc_charge (chg_naver_id,chg_emp_no,chg_emp_name,use_yn, insert_user, update_user, insert_user_name, update_user_name) values
    ('{$naver_id}',$mb_no,'{$mb_name}','Y','{$mb_id}','{$mb_id}','{$mb_name}','{$mb_name}');
    ";

    $inserted = sql_query($insert_sql);

    echo json_encode("success");
} else if ($act === 'confirm') {
    $conf_yyyymm = isset($_POST['param']) ? strip_tags($_POST['param']) : '';

    $mb_no = $member['mb_no'];
    $mb_id = $member['mb_id'];
    $mb_name = $member['mb_name'];

    $insert_sql = "
    INSERT INTO gnp_kwd_calc_confirm (conf_yyyymm,conf_mb_no,insert_user,update_user,insert_user_name,update_user_name) VALUES
	 ('{$conf_yyyymm}',{$mb_no},'{$mb_id}','{$mb_id}','{$mb_name}','{$mb_name}');
    ";
    $inserted = sql_query($insert_sql);

    if($inserted == true) {
        echo json_encode("success");
    } else {
        echo json_encode("already");
    }
    
    
} else if ($act === 'conf_stat') {

    $cur_month = isset($_POST['cur_month']) ? strip_tags($_POST['cur_month']) : '';

    $lstSql = "
    select m.mb_name, c.conf_yyyymm , c.insert_date 
    from {$g5['member_table']} m
    left join gnp_kwd_calc_confirm c on m.mb_no = c.conf_mb_no and c.conf_yyyymm = '{$cur_month}'
    where m.mb_deptno = 11 and c.conf_mb_no is not null
    union all
    select m2.mb_name, null, null
    from {$g5['member_table']} m2
    where m2.mb_deptno = 11
    and not exists (select 1 from gnp_kwd_calc_confirm c2 where c2.conf_mb_no = m2.mb_no and c2.conf_yyyymm = '{$cur_month}')
    order by mb_name asc;
    ";
    $lst = sql_query($lstSql);

    for ($i = 0; $row = sql_fetch_array($lst); $i++) {
        $response .= '
        <tr>
            <td>'.($i+1).'</td>
            <td>'.$row['mb_name'].'</td>
            <td>'.$row['conf_yyyymm'].'</td>
            <td>'.$row['insert_date'].'</td>
        </tr>
        ';
    }
    echo json_encode($response);
} else if ($act === 'find_naver_rank') {

    $sel_keyword = isset($_POST['sel_keyword']) ? strip_tags($_POST['sel_keyword']) : '';
    $sel_gubun = isset($_POST['sel_gubun']) ? strip_tags($_POST['sel_gubun']) : '';
    $sel_value = isset($_POST['sel_value']) ? strip_tags($_POST['sel_value']) : '';
    
    // if($sel_gubun == "1") {
    //     if (preg_match("/products\/(\d+)/", $sel_value, $matches)) {
    //         // 추출된 값(상품 ID)을 출력합니다.
    //         $sel_value = $matches[1];
    //     } 
    // }

    $ch = curl_init('https://naver.gonplan.co.kr');
    
    $postData = array(
        'sel_keyword' => $sel_keyword,
        'sel_gubun' => $sel_gubun,
        'sel_value' => $sel_value,
        'mb_name' => $member['mb_name'],
        'client_ip' => $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER['REMOTE_ADDR']
    );
    
    // SSL 인증서 검증 비활성화 (보안상의 이유로 생산 환경에서는 권장되지 않음)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true); // POST 메소드 사용
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData)); // POST 데이터 설정
    
    // HTTP 요청 실행 및 응답 받기
    $response = curl_exec($ch);
    $decodedResponse = json_decode($response, true);

    // cURL 오류가 있는 경우 출력
    if ($response === false) {
        $error = curl_error($ch);
        echo "cURL Error: $error";
    } else {
        // 응답 출력
        //echo $response;
        //echo json_encode($decodedResponse);

        if($decodedResponse == null || $decodedResponse == "") {
            echo json_encode("");
        } else {

            $total_price = number_format($decodedResponse['data']['price']);

            if($decodedResponse['data']['mallName'] == "") {
                $decodedResponse['data']['mallName'] = "catalog:".$decodedResponse['data']['productName'];
                $decodedResponse['data']['mallProductId'] = $decodedResponse['data']['nvmid'];
            }

            $div_html = "
            <form id='modal_form' name='modal_form' action='./kwd_naver_find_rank_update' method='post' onSubmit='return validateForm()'>

                <input type='hidden' id='nvMid' name='nvMid' value='{$decodedResponse['data']['nvmid']}'>

                <input type='hidden' id='mallName' name='mallName' value='{$decodedResponse['data']['mallName']}'>
                <input type='hidden' id='productName' name='productName' value='{$decodedResponse['data']['productName']}'>
                <input type='hidden' id='mallProductId' name='mallProductId' value='{$decodedResponse['data']['mallProductId']}'>

                <input type='hidden' id='id' name='id' value='{$decodedResponse['data']['id']}'>
                <input type='hidden' id='shopNNo' name='shopNNo' value='{$decodedResponse['data']['shopNNo']}'>
                <input type='hidden' id='seq' name='seq' value='{$decodedResponse['data']['seq']}'>
                <input type='hidden' id='selKeyword' name='selKeyword' value='{$sel_keyword}'>
                <input type='hidden' id='selGubun' name='selGubun' value='{$sel_gubun}'>
                <input type='hidden' id='selValue' name='selValue' value='{$sel_value}'>
                <input type='hidden' id='rank' name='rank' value='{$decodedResponse['data']['rank']}'>

                <input type='hidden' id='price' name='price' value='{$decodedResponse['data']['price']}'>

                <input type='hidden' id='reviewCount' name='reviewCount' value='{$decodedResponse['data']['reviewCount']}'>
                <input type='hidden' id='purchaseCnt' name='purchaseCnt' value='{$decodedResponse['data']['purchaseCnt']}'>
                <input type='hidden' id='scoreInfo' name='scoreInfo' value='{$decodedResponse['data']['scoreInfo']}'>

                <div class='modal-body'>
                    <div class='card-body'>
                        <div class='row'>
                            <div class='col-lg-3'>
                                <img src='{$decodedResponse['data']['imageUrl']}' alt='Product Image' class='img-fluid'>
                            </div>
                            <div class='col-lg-9'>
                               

                                <h5 class='card-title bg-indigo color-palette'>
                                    {$decodedResponse['data']['rank']}위
                                    <span class='text-white'> [{$decodedResponse['data']['mallName']}]</span>
                                </h5>

                                <p class='card-text'>{$total_price}원</p>
                                <p class='card-text'>평점 {$decodedResponse['data']['scoreInfo']}점 | 리뷰 {$decodedResponse['data']['reviewCount']}개 | 판매 {$decodedResponse['data']['purchaseCnt']}개</p>
                                <p class='card-text'>{$decodedResponse['data']['productName']}</p>
                                <a href='{$decodedResponse['data']['mallProductUrl']}' target='_blank' class='btn btn-primary'>상품 페이지</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class='modal-footer justify-content-between'>
                    <button type='button' class='btn btn-danger' data-dismiss='modal'>닫기</button>

                    <div class='mx-auto'>
                        <div class='form-row'>
                            <div class='col-md-6'>
                                <input type='date' id='startDate' name='startDate' class='form-control' value='".date('Y-m-d')."'>
                            </div>
                            <div class='col-md-6'>
                                <input type='date' id='endDate' name='endDate' class='form-control' value='".date('Y-m-d', strtotime('+1 month')) . "'>
                            </div>
                        </div>
                    </div>

                    <button type='submit' class='btn btn-primary' name='act_button' value='저장'>저장</button>
                </div>
            </form>
            ";
            echo json_encode($div_html);
        }





    }
    
    // cURL 세션 종료
    curl_close($ch);

} else if ($act === 'get_chart') {

    $mstIdx = isset($_POST['mstIdx']) ? strip_tags($_POST['mstIdx']) : '';
    $startDate = isset($_POST['startDate']) ? strip_tags($_POST['startDate']) : '';
    $endDate = isset($_POST['endDate']) ? strip_tags($_POST['endDate']) : '';

    $host = '115.71.19.114';
    $dbname = 'naver_gonplan';
    $username = 'gonplan'; // 데이터베이스 사용자 이름
    $password = 'gon!@34qwer@@'; // 데이터베이스 비밀번호

    // 데이터베이스 연결
    $mysqli = new mysqli($host, $username, $password, $dbname);

    // 연결 오류 확인
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // SQL 쿼리 준비
    $query = "
    select a.mallName 
         , a.productName 
         , a.selKeyword
         , b.dailyRank 
         , DATE_FORMAT(b.insertdate, '%m-%d') AS insertDate
         , b.dailyReview
         , b.dailyPurchase
    from gnp_naver_rank_master a
    left join gnp_naver_rank_daily b on a.mstIdx = b.mstIdx 
    where a.mstIdx = ?
    and b.dateRank between ? and ?
    order by b.insertDate asc
    limit 0, 31
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("iss", $mstIdx, $startDate, $endDate); // Assuming $mbNo is an integer
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $mysqli->close(); 
    
    $i = 0;
    $response = array();
    $chart = "";

    $response = array();

    while ($row = $result->fetch_assoc()) {

        if($i == 0) {
            $info_data = [
                'mallName' => $row['mallName'],
                'productName' => $row['productName'],
                'selKeyword' => $row['selKeyword'],
            ];

            array_push( $response, $info_data );
            $i = $i + 1;
        }
        $chart_data[] = [
            'mallName' => $row['mallName'],
            'productName' => $row['productName'],
            'selKeyword' => $row['selKeyword'],
            'dailyRank' => $row['dailyRank'],
            'insertDate' => $row['insertDate'],
            'dailyReview' => $row['dailyReview'] == NULL ? 0 : number_format($row['dailyReview']),
            'dailyPurchase' => $row['dailyPurchase'] == NULL ? 0 : number_format($row['dailyPurchase'])
        ];
    }
    
    array_push( $response, $chart_data );
    header('Content-Type: application/json');
    echo json_encode($response);
    
} else if ($act === 'del_master_rank') {

    $host = '115.71.19.114';
    $dbname = 'naver_gonplan';
    $username = 'gonplan'; // 데이터베이스 사용자 이름
    $password = 'gon!@34qwer@@'; // 데이터베이스 비밀번호

    // 데이터베이스 연결
    $mysqli = new mysqli($host, $username, $password, $dbname);

    // 연결 오류 확인
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // SQL 쿼리 준비
    $query = "
    delete from gnp_naver_rank_master 
    where mstIdx = ?
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $mstIdx); // Assuming $mbNo is an integer
    $stmt->execute();
    $stmt->close();
    $mysqli->close(); 
    echo json_encode("success");
} else if ($act === 'continue_history') {

    $host = '115.71.19.114';
    $dbname = 'naver_gonplan';
    $username = 'gonplan'; // 데이터베이스 사용자 이름
    $password = 'gon!@34qwer@@'; // 데이터베이스 비밀번호

    // 데이터베이스 연결
    $mysqli = new mysqli($host, $username, $password, $dbname);

    // 연결 오류 확인
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // SQL 쿼리 준비
    $query = "
    update gnp_naver_rank_master set
    endDate = DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
    where mstIdx = ?
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $mstIdx); // Assuming $mbNo is an integer
    $stmt->execute();
    $stmt->close();
    $mysqli->close(); 
    echo json_encode("success");
} else if ($act === 'research_rank') {

    $mstIdx = isset($_POST['mstIdx']) ? strip_tags($_POST['mstIdx']) : '';

    $ch = curl_init('https://naver.gonplan.co.kr/research_rank.php');
    
    $postData = array(
        'mstIdx' => $mstIdx
    );
    
    // SSL 인증서 검증 비활성화 (보안상의 이유로 생산 환경에서는 권장되지 않음)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true); // POST 메소드 사용
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData)); // POST 데이터 설정
    
    // HTTP 요청 실행 및 응답 받기
    $response = curl_exec($ch);
    $decodedResponse = json_decode($response, true);
    echo json_encode("success");
} else if ($act === 'open_memo_modal') {

    $mstIdx = isset($_POST['mstIdx']) ? strip_tags($_POST['mstIdx']) : '';

    $host = '115.71.19.114';
    $dbname = 'naver_gonplan';
    $username = 'gonplan'; // 데이터베이스 사용자 이름
    $password = 'gon!@34qwer@@'; // 데이터베이스 비밀번호

    // 데이터베이스 연결
    $mysqli = new mysqli($host, $username, $password, $dbname);

    // 연결 오류 확인
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // SQL 쿼리 준비
    $query = "
    select *
    from gnp_naver_rank_master a
    where a.mstIdx = ?
    limit 0,1
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $mstIdx); // Assuming $mbNo is an integer
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $mysqli->close(); 

    if ($row = $result->fetch_assoc()) {
        $result_memo = htmlspecialchars($row['mstMemo'], ENT_QUOTES, 'UTF-8');
        // 결과를 객체로 만들어 반환
        $response = ['memoText' => $result_memo];
    } else {
        $response = ['memoText' => ''];
    }

    echo json_encode($response);
} else if ($act === 'save_memo_modal') {

    $mstIdx = isset($_POST['mstIdx']) ? strip_tags($_POST['mstIdx']) : '';
    $mstMemo = isset($_POST['mstMemo']) ? strip_tags($_POST['mstMemo']) : '';

    $host = '115.71.19.114';
    $dbname = 'naver_gonplan';
    $username = 'gonplan';
    $password = 'gon!@34qwer@@'; // 데이터베이스 비밀번호를 안전한 곳에 저장하고 이를 여기에 포함시켜야 함

    // 데이터베이스 연결
    $mysqli = new mysqli($host, $username, $password, $dbname);

    // 연결 오류 확인
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // SQL 쿼리 준비
    $query = "UPDATE gnp_naver_rank_master SET mstMemo = ? WHERE mstIdx = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ss", $mstMemo, $mstIdx);
        $stmt->execute();

        // 응답을 JSON 형식으로 전송
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => '메모가 저장되었습니다.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => '업데이트에 실패했습니다.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => '쿼리 준비 실패']);
    }
    

    $mysqli->close();
}