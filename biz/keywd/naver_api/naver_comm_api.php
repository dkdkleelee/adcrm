<?php

function call_api_customer(){

    // 두 달 전 데이터 삭제
    $twoMonthsAgo = date('Y-m-d', strtotime('-2 months'));
    $deleteSql = "delete from gnp_kwd_customers_backup where backup_date <= '$twoMonthsAgo'";
    sql_query($deleteSql);

    // 현재 날짜
    $backupDate = date('Y-m-d');

    //테이블 생성 후 백업 OR 백업
    $query = "SHOW TABLES LIKE 'gnp_kwd_customers_backup'";
    $result = sql_query($query);
    $tableExists = mysqli_num_rows($result) > 0;

    if ($tableExists) {
        // 백업 테이블이 이미 존재하는 경우, 데이터 추가
        $backupSql = "insert into gnp_kwd_customers_backup select *, '$backupDate' as backup_date from gnp_kwd_customers";
        sql_query($backupSql);
    } else {
        // 백업 테이블이 존재하지 않는 경우, 테이블 생성 후 데이터 추가
        $createTableSql = "create table gnp_kwd_customers_backup select *, '$backupDate' as backup_date from gnp_kwd_customers";
        sql_query($createTableSql);

        // 백업 테이블이 이미 존재하는 경우, 데이터 추가
        $backupSql = "insert into gnp_kwd_customers_backup select *, '$backupDate' as backup_date from gnp_kwd_customers";
        sql_query($backupSql);
    }


    $acc_sql = "
    select * from gnp_kwd_naver_acct where use_yn = 'Y'
    ";
    $acc_list = sql_query($acc_sql);
    for ($i = 0; $row = sql_fetch_array($acc_list); $i++) {

        $naver_idx = $row['naver_idx'];
        $naver_id = $row['naver_id'];
        $naver_pw = $row['naver_pw'];

        $customer_id = $row['customer_id'];
        $access_license = trim($row['access_license']);
        $access_secretkey = trim($row['access_secretkey']);
        

        #api 호출
        ini_set("default_socket_timeout", 30);
        require_once 'restapi.php';
        //$config = parse_ini_file("sample.ini");
        //$api = new RestApi($config['BASE_URL'], $config['API_KEY'], $config['SECRET_KEY'], $config['CUSTOMER_ID']);

        $api = new RestApi("https://api.searchad.naver.com", $access_license, $access_secretkey, $customer_id);
        $customers = $api->GET('/customer-links', array('type' => 'MYCLIENTS'));


        //API데이터와 지난주와 비교하여 해지한 고객은 update 처리
        //$lastWeek = date('Y-m-d', strtotime('-1 week'));
        //$query = "SELECT customerLinkId FROM gnp_kwd_customers WHERE api_ins_date < '$lastWeek'";
        $query = "SELECT customerLinkId FROM gnp_kwd_customers WHERE managerLoginId = '{$naver_id}'";
        $result = sql_query($query);

        $lastWeekCustomerIds = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $lastWeekCustomerIds[] = $row['customerLinkId'];
        }

        $api_cnt = count($customers);
        $database_cnt = count($lastWeekCustomerIds);

        file_put_contents(G5_DATA_PATH.'/log/naver_api.log', "api_cnt : [" . $api_cnt . "건 | database_cnt : " .$database_cnt. "건] -----" . PHP_EOL, FILE_APPEND | LOCK_EX);

        $customerIdsToDelete = array();
        $customerIdsToDelete = array_diff($lastWeekCustomerIds, array_column($customers, 'customerLinkId'));
        if (!empty($customerIdsToDelete)) {
            $quotedCustomerIdsToDelete = array_map(function($value) {
                return "'" . $value . "'";
            }, $customerIdsToDelete);
        
            //$query = "delete from gnp_kwd_customers where customerlinkid in (" . implode(",", $quotedCustomerIdsToDelete) . ")";
            //$result2 = sql_query($query);

            $upd_query = "
            update gnp_kwd_customers set 
            delFlag = 1
            where customerlinkid in (" . implode(",", $quotedCustomerIdsToDelete) . ")
            and managerLoginId = '{$naver_id}'
            ";
            $upd_result = sql_query($upd_query);

            file_put_contents(G5_DATA_PATH.'/log/naver_api.log', "[" . date("Y-m-d h:i:s") . "] -----  [".$naver_id." update : " .  implode( '/', $quotedCustomerIdsToDelete ) . "] -----" . PHP_EOL, FILE_APPEND | LOCK_EX);
        }

        $insert_cnt = 0;
        $update_cnt = 0;

        foreach($customers as $cstm) {
            //$cstm_data_sql .= "($customerlist[$i]['customerLinkId'], $customerlist[$i]['managerCustomerId'], $customerlist[$i]['clientCustomerId'], $customerlist[$i]['roleId'], $customerlist[$i]['linkStatus'], $customerlist[$i]['description'], $customerlist[$i]['regTm'], $customerlist[$i]['editTm'], $customerlist[$i]['clientLoginId'], $customerlist[$i]['managerName'], $customerlist[$i]['managerEnable'], $customerlist[$i]['managerPenaltySt'], $customerlist[$i]['managerCustomerDelFlag'], $customerlist[$i]['clientEnable'], $customerlist[$i]['clientPenaltySt'], $customerlist[$i]['clientCustomerDelFlag'], $customerlist[$i]['delFlag']  );";
        
            $customerLinkId         = $cstm['customerLinkId'];
            $managerCustomerId      = $cstm['managerCustomerId'];
            $clientCustomerId       = $cstm['clientCustomerId'];
            $roleId                 = $cstm['roleId'];
            $linkStatus             = $cstm['linkStatus'];
            $description            = $cstm['description'];
            $regTm                  = $cstm['regTm'];
            $editTm                 = $cstm['editTm'];
            $managerLoginId         = $cstm['managerLoginId'];
            $clientLoginId          = $cstm['clientLoginId'];
            $managerName            = $cstm['managerName'];
            $managerEnable          = $cstm['managerEnable'];
            $managerPenaltySt       = $cstm['managerPenaltySt'];
            $managerCustomerDelFlag = $cstm['managerCustomerDelFlag'];
            $clientEnable           = $cstm['clientEnable'];
            $clientPenaltySt        = $cstm['clientPenaltySt'];
            $clientCustomerDelFlag  = $cstm['clientCustomerDelFlag'];
            $delFlag                = $cstm['delFlag'];
        
            //$api = new RestApi($config['BASE_URL'], $config['API_KEY'], $config['SECRET_KEY'], (int)$clientCustomerId);
            $api = new RestApi("https://api.searchad.naver.com", $access_license, $access_secretkey, $clientCustomerId);

            $remain_money = $api->GET('/billing/bizmoney');

            $exist_sql = "
            select count(*) as cnt
            from gnp_kwd_customers
            where customerLinkId = '{$customerLinkId}'
            and managerLoginId = '{$managerLoginId}'
            ";
            $exist = sql_fetch($exist_sql);

            $cnt = (int) $exist['cnt'];

            //UPDATE
            if($cnt == 0) {
                $insert_customer = "
                insert into gnp_kwd_customers set  
                customerLinkId = '{$customerLinkId}'
                , managerCustomerId = '{$managerCustomerId}'
                , clientCustomerId = '{$clientCustomerId}'
                , roleId = '{$roleId}'
                , linkStatus = '{$linkStatus}'
                , description = '{$description}'
                , regTm = '{$regTm}'
                , editTm = '{$editTm}'
                , managerLoginId = '{$managerLoginId}'
                , clientLoginId = '{$clientLoginId}'
                , managerName = '{$managerName}'
                , managerEnable = '{$managerEnable}'
                , managerPenaltySt = '{$managerPenaltySt}'
                , managerCustomerDelFlag = '{$managerCustomerDelFlag}'
                , clientEnable = '{$clientEnable}'
                , clientPenaltySt = '{$clientPenaltySt}'
                , clientCustomerDelFlag = '{$clientCustomerDelFlag}'
                , delFlag = '{$delFlag}'
                , bizmoney = {$remain_money['bizmoney']}
                , api_ins_date = now()
                , api_upd_date = now()
                , use_yn = 'Y'
                ";
                $result = sql_query($insert_customer);
                $insert_cnt = $insert_cnt + 1;

            } 
            //INSERT
            else {
                $update_customer = "
                update gnp_kwd_customers set
                    managerCustomerId = '{$managerCustomerId}',
                    clientCustomerId = '{$clientCustomerId}',
                    roleId = '{$roleId}',
                    linkStatus = '{$linkStatus}',
                    description = '{$description}',
                    regTm = '{$regTm}',
                    editTm = '{$editTm}',
                    managerName = '{$managerName}',
                    managerEnable = '{$managerEnable}',
                    managerPenaltySt = '{$managerPenaltySt}',
                    managerCustomerDelFlag = '{$managerCustomerDelFlag}',
                    clientEnable = '{$clientEnable}',
                    clientPenaltySt = '{$clientPenaltySt}',
                    clientCustomerDelFlag = '{$clientCustomerDelFlag}',
                    delFlag = '{$delFlag}',
                    bizmoney = '{$remain_money['bizmoney']}',
                    api_upd_date = now()
                where customerLinkId = '{$customerLinkId}' 
                and managerLoginId = '{$managerLoginId}'";

                $result = sql_query($update_customer);
                $update_cnt = $update_cnt + 1;
            }
        }

        file_put_contents(G5_DATA_PATH.'/log/naver_api.log', "insert_cnt : [" . $insert_cnt . "건 | update_cnt : " .$update_cnt. "건] -----" . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

   
}



?>