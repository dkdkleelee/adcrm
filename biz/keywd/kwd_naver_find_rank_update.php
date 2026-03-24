<?php

require_once '../../common.php';



$post_count_chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;
$chk            = (isset($_POST['chk']) && is_array($_POST['chk'])) ? $_POST['chk'] : array();
$act_button     = isset($_POST['act_button']) ? strip_tags($_POST['act_button']) : '';

if ($act_button === "순위체크") {
    $ch = curl_init('https://naver.gonplan.co.kr');
    
    // SSL 인증서 검증 비활성화 (보안상의 이유로 생산 환경에서는 권장되지 않음)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // HTTP 요청 실행 및 응답 받기
    $response = curl_exec($ch);
    
    // cURL 오류가 있는 경우 출력
    if ($response === false) {
        $error = curl_error($ch);
        echo "cURL Error: $error";
    } else {
        // 응답 출력
        echo $response;
    }
    
    // cURL 세션 종료
    curl_close($ch);
} else if ($act_button === "저장") {

    // 데이터베이스 설정
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

    // POST 데이터 받기 및 태그 제거


    $mbNo = $member['mb_no'];
    $mbId = $member['mb_id'];
    $mbName = $member['mb_name'];

    $nvMid = isset($_POST['nvMid']) ? strip_tags($_POST['nvMid']) : '';
    
    $mallName = isset($_POST['mallName']) ? strip_tags($_POST['mallName']) : '';
    $productName = isset($_POST['productName']) ? strip_tags($_POST['productName']) : '';

    $mallProductId = isset($_POST['mallProductId']) ? strip_tags($_POST['mallProductId']) : '';
    $id = isset($_POST['id']) ? strip_tags($_POST['id']) : '';
    $shopNNo = isset($_POST['shopNNo']) ? strip_tags($_POST['shopNNo']) : '';
    $seq = isset($_POST['seq']) ? strip_tags($_POST['seq']) : '';
    $sel_keyword = isset($_POST['selKeyword']) ? strip_tags($_POST['selKeyword']) : '';
    $selGubun = isset($_POST['selGubun']) ? strip_tags($_POST['selGubun']) : '';
    $selValue = isset($_POST['selValue']) ? strip_tags($_POST['selValue']) : '';
    $rank = isset($_POST['rank']) ? strip_tags($_POST['rank']) : '';

    $price = isset($_POST['price']) ? strip_tags($_POST['price']) : '';

    $reviewCount = isset($_POST['reviewCount']) ? strip_tags($_POST['reviewCount']) : '';
    $purchaseCnt = isset($_POST['purchaseCnt']) ? strip_tags($_POST['purchaseCnt']) : '';
    $scoreInfo = isset($_POST['scoreInfo']) ? strip_tags($_POST['scoreInfo']) : '';

    $startDate = isset($_POST['startDate']) ? strip_tags($_POST['startDate']) : '';
    $endDate = isset($_POST['endDate']) ? strip_tags($_POST['endDate']) : '';

    // SQL 쿼리 준비
    $sql = "INSERT INTO gnp_naver_rank_master (mbNo, mbId, mbName, nvMid, mallName, productName, mallproductid, id, shopnno, seq, selKeyword, selGubun, selValue, init_rank, startDate, endDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    // 쿼리 준비
    $stmt = $mysqli->prepare($sql);

    if ($stmt === false) {
        die('MySQL prepare error: ' . $mysqli->error);
    }

    // 파라미터 바인딩
    $stmt->bind_param('isssssssssssssss', $mbNo, $mbId, $mbName, $nvMid, $mallName, $productName, $mallProductId, $id, $shopNNo, $seq, $selKeyword, $selGubun, $selValue, $rank, $startDate, $endDate);


    
    // 쿼리 실행
    if ($stmt->execute()) {

        $inserted_id = $mysqli->insert_id;
        
        // SQL 쿼리 준비
        $sql2 = "INSERT INTO gnp_naver_rank_daily (mstIdx, dailyRank, dailyPrice, dailyReview, dailyPurchase, dailyGrade) VALUES (?,?,?,?,?,?)";
        $stmt = $mysqli->prepare($sql2);
        if ($stmt === false) {
            die('MySQL prepare error: ' . $mysqli->error);
        }
        // 파라미터 바인딩
        $stmt->bind_param('iiiiid', $inserted_id, $rank, $price, $reviewCount, $purchaseCnt, $scoreInfo);
        if ($stmt->execute()) {
            // 자원 정리
            $stmt->close();
            $mysqli->close();
            header('location:'.$_SERVER['HTTP_REFERER']);  
        }
        
    } else {
        // 오류 코드가 1062인 경우, 중복 키 오류로 판단
        if ($mysqli->errno == 1062) {
            // 자원 정리
            $stmt->close();
            $mysqli->close();
            alert("이미등록된 키워드+상품이므로 종료합니다.");
        } else {
            // 자원 정리
            $stmt->close();
            $mysqli->close();
            alert("에러발생 개발자에게 문의해주세요.");
        }
    }

    

} else if ($act_button === "선택연장") {


    for ($i = 0; $i < $post_count_chk; $i++) {
    
        $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;
        $mstIdx     = isset($_POST['mstIdx'][$k]) ? strip_tags(clean_xss_attributes($_POST['mstIdx'][$k])) : '';

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

    }

    header('location:'.$_SERVER['HTTP_REFERER']);
} else if ($act_button === "엑셀다운") {

    $mbNo = $member['mb_no'];
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
    $query = "SELECT a.mstIdx,
                    a.mbNo,
                    a.mbId,
                    a.mbName,
                    a.nvMid,
                    a.mallName,
                    a.productName,
                    a.mallProductId,
                    a.id,
                    a.shopnno,
                    a.seq,
                    a.selKeyword,
                    a.selGubun,
                    a.selValue,
                    a.init_rank,
                    a.startDate,
                    a.endDate,
                    a.mstMemo,
                    a.useYn,
                    a.insertDate,
                    (SELECT c.dailyRank FROM gnp_naver_rank_daily c WHERE c.mstIdx = a.mstIdx ORDER BY c.insertDate DESC LIMIT 1) AS curran,
                    (SELECT ROUND(AVG(c.dailyRank), 2) FROM gnp_naver_rank_daily c WHERE c.mstIdx = a.mstIdx AND c.dailyRank IS NOT NULL) AS avgrank
            FROM gnp_naver_rank_master a
            WHERE a.useYn = 'Y'
            AND a.mbNo = ?
            ORDER BY endDate DESC
            ";
            

    if (!empty($stx)) {
        // Append to the existing query to filter by productName or mallName
        $query .= " AND (a.productName LIKE CONCAT('%', ?, '%') OR a.mallName LIKE CONCAT('%', ?, '%'))";
    }

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $mbNo); 
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $mysqli->close();



    $EXCEL_STR = "
    <table border='1'>
    <tr>
        <td style='white-space:nowrap;'>mstIdx</td>
        <td style='white-space:nowrap;'>nvMid</td>
        <td style='white-space:nowrap;'>스토어명</td>
        <td style='white-space:nowrap;'>상품명</td>
        <td style='white-space:nowrap;'>키워드</td>
        <td style='white-space:nowrap;'>시작순위</td>
        <td style='white-space:nowrap;'>평균순위</td>
        <td style='white-space:nowrap;'>시작-종료일</td>
    </tr>";
    
    $i = 1;
    while ($res = sql_fetch_array( $result )) {
        $EXCEL_STR .= "  
       <tr>  
           <td style='white-space:nowrap;'>".$res['mstIdx']."</td>
           <td style='white-space:nowrap;'>".$res['nvMid']."</td>
           <td style='white-space:nowrap;'>".$res['mallName']."</td>  
           <td style='white-space:nowrap;'>".$res['productName']."</td>  
           <td style='white-space:nowrap;'>".$res['selKeyword']."</td>  
           <td style='white-space:nowrap;'>".$res['curran']."</td>  
           <td style='white-space:nowrap;'>".$res['avgrank']."</td>  
           <td style='white-space:nowrap;'>".$res['startDate']."~".$res['endDate']."</td>  
       </tr>  
       ";  
    
       $i = $i + 1;
     }

    $EXCEL_STR .= "</table>";

    header("Content-type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename={$partner_name}_{$append_title}엑셀다운로드_".date("Ymd_Hms").".xls" );
    header("Content-Description: PHP4 Generated Data");
    header("Pragma: no-cache");
    header("Expires: 0");
    print("<meta http-equiv=\"Content-Type\" content=\"application/vnd.ms-excel; charset=utf-8\">");

    //echo "<meta http-equiv='Content-Type' content='text/html; charset=euc-kr'> ";
    echo $EXCEL_STR;


}