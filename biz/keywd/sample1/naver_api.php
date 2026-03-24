<?php
require_once '../../../common.php';

ini_set("default_socket_timeout", 30);
require_once 'restapi.php';

$config = parse_ini_file("sample.ini");


$api = new RestApi($config['BASE_URL'], $config['API_KEY'], $config['SECRET_KEY'], $config['CUSTOMER_ID']);


$truncate_sql = "
truncate gnp_kwd_customers;
";
$res = sql_query($truncate_sql);


$truncate_sql = "
truncate gnp_kwd_campaigns;
";
$res = sql_query($truncate_sql);

$truncate_sql = "
truncate gnp_kwd_campaigns;
";
$res = sql_query($truncate_sql);

$truncate_sql = "
truncate gnp_kwd_group;
";
$res = sql_query($truncate_sql);

$truncate_sql = "
truncate gnp_kwd_keyword;
";
$res = sql_query($truncate_sql);

$truncate_sql = "
truncate gnp_kwd_ad;
";
$res = sql_query($truncate_sql);

$truncate_sql = "
truncate gnp_kwd_stat;
";
$res = sql_query($truncate_sql);

//$stat_report = $api->GET("/stat-reports");

$customers = $api->GET('/customer-links', array('type' => 'MYCLIENTS'));

// $statIds = "stat_ids"; // stat_ids를 실제 값으로 변경
// //$fields = ' ["impCnt","clkCnt","salesAmt","crto"]'; // fields를 실제 값으로 변경
// $fields = '["impCnt","clkCnt","ctr","cpc","salesAmt","ccnt","crto","convAmt","cpConv","ror","avgRnk"]';
// $timeRange = '{"since":"2023-04-10","until":"2023-04-10"}'; // timeRange를 실제 값으로 변경


// $param = array(
//     "id" => "grp-a001-02-000000031276450",
//     "fields" => $fields,
//     "timeRange" => $timeRange
// );

// $stat_list = $api->GET("/stats" , $param);



sleep(2);
foreach($customers as $cstm){
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

    $api = new RestApi($config['BASE_URL'], $config['API_KEY'], $config['SECRET_KEY'], (int)$clientCustomerId);
    $remain_money = $api->GET('/billing/bizmoney');



    $insert_customer = "
    insert into ignore gnp_kwd_customers set  
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
    , bizmoney = '{$remain_money['bizmoney']}'
    , api_ins_date = now()
    ";

    $result = sql_query($insert_customer);


    continue;
    
    $bzlist = $api->GET('/ncc/channels');
    foreach($bzlist as $bz){
        $nccBusinessChannelId = $bz['nccBusinessChannelId'];
        $customerId = $bz['customerId'];
        $channelTp = $bz['channelTp'];
        $name = $bz['name'];
        $channelKey = $bz['channelKey'];
        $businessInfo = $bz['businessInfo'];
        $regTm = $bz['regTm'];
        $editTm = $bz['editTm'];
        $firstChargeTm = $bz['firstChargeTm'];
        $inspectTm = $bz['inspectTm'];
        $mobileInspectStatus = $bz['mobileInspectStatus'];
        $status = $bz['status'];
        $statusReason = $bz['statusReason'];
        $adultStatus = $bz['adultStatus'];
        $enabled = $bz['enabled'];
        $blackStatus = $bz['enabled'];

        $insert_bz = "
        insert into gnp_kwd_channels set 
            nccBusinessChannelId = '{$nccBusinessChannelId}'
        ,   customerId = '{$customerId}'
        ,   channelTp = '{$channelTp}'
        ,   name = '{$name}'
        ,   channelKey = '{$channelKey}'
        ,   businessInfo = '{$businessInfo}'
        ,   regTm = '{$regTm}'
        ,   editTm = '{$editTm}'
        ,   firstChargeTm = '{$firstChargeTm}'
        ,   inspectTm = '{$inspectTm}'
        ,   mobileInspectStatus = '{$mobileInspectStatus}'
        ,   status = '{$status}'
        ,   statusReason = '{$statusReason}'
        ,   adultStatus = '{$adultStatus}'
        ,   enabled = '{$enabled}'
        ,   blackStatus = '{$blackStatus}'
        ";
        $result = sql_query($insert_bz);
    }
    

    $campaignlist = $api->GET('/ncc/campaigns');
    foreach($campaignlist as $campaign) {
        $nccCampaignId   = $campaign['nccCampaignId'];
        $customerId      = $campaign['customerId'];
        $name            = $campaign['name'];
        $userLock        = $campaign['userLock'];
        $campaignTp      = $campaign['campaignTp'];
        $deliveryMethod  = $campaign['deliveryMethod'];
        $trackingUrl     = $campaign['trackingUrl'];
        $trackingMode    = $campaign['trackingMode'];
        $usePeriod       = $campaign['usePeriod'];
        $dailyBudget     = $campaign['dailyBudget'];
        $useDailyBudget  = $campaign['useDailyBudget'];
        $totalChargeCost = $campaign['totalChargeCost'];
        $status          = $campaign['status'];
        $statusReason    = $campaign['statusReason'];
        $expectCost      = $campaign['expectCost'];
        $migType         = $campaign['migType'];
        $delFlag         = $campaign['delFlag'];
        $regTm           = $campaign['regTm'];
        $editTm          = $campaign['editTm'];
        
        if($status != "ELIGIBLE") {
            continue;
        }
        $insert_campaign = "
        insert into gnp_kwd_campaigns set 
            nccCampaignId = '{$nccCampaignId}'
        ,   customerId = '{$customerId}'
        ,   name = '{$name}'
        ,   userLock = '{$userLock}'
        ,   campaignTp = '{$campaignTp}'
        ,   deliveryMethod = '{$deliveryMethod}'
        ,   trackingUrl = '{$trackingUrl}'
        ,   trackingMode = '{$trackingMode}'
        ,   usePeriod = '{$usePeriod}'
        ,   dailyBudget = '{$dailyBudget}'
        ,   useDailyBudget = '{$useDailyBudget}'
        ,   totalChargeCost = '{$totalChargeCost}'
        ,   status = '{$status}'
        ,   statusReason = '{$statusReason}'
        ,   expectCost = '{$expectCost}'
        ,   migType = '{$migType}'
        ,   delFlag = '{$delFlag}'
        ,   regTm = '{$regTm}'
        ,   editTm = '{$editTm}'
        ";
        $result = sql_query($insert_campaign);

    }

    sleep(2);
    $adgrouplist = $api->GET('/ncc/adgroups');
    foreach($adgrouplist as $group){

        $nccAdgroupId = $group['nccAdgroupId'];
        $customerId = $group['customerId'];
        $nccCampaignId = $group['nccCampaignId'];
        $mobileChannelId = $group['mobileChannelId'];
        $userLock = $group['userLock'];
        $useDailyBudget = $group['useDailyBudget'];
        $useKeywordPlus = $group['useKeywordPlus'];
        $keywordPlusWeight = $group['keywordPlusWeight'];
        $contentsNetworkBidAmt = $group['contentsNetworkBidAmt'];
        $useCntsNetworkBidAmt = $group['useCntsNetworkBidAmt'];
        $mobileNetworkBidWeight = $group['mobileNetworkBidWeight'];
        $pcNetworkBidWeight = $group['pcNetworkBidWeight'];
        $dailyBudget = $group['dailyBudget'];
        $budgetLock = $group['budgetLock'];
        $delFlag = $group['delFlag'];
        $regTm = $group['regTm'];
        $editTm = $group['editTm'];
        $targetSummary = $group['targetSummary'];
        $pcChannelKey = $group['pcChannelKey'];
        $mobileChannelKey = $group['mobileChannelKey'];
        $status = $group['status'];
        $statusReason = $group['statusReason'];
        $expectCost = $group['expectCost'];
        $migType = $group['migType'];
        $adgroupAttrJson = $group['adgroupAttrJson'];
        $adRollingType = $group['adRollingType'];
        $adgroupType = $group['adgroupType'];
        $systemBiddingType = $group['systemBiddingType'];
        $useCntsNetworkBidWeight = $group['useCntsNetworkBidWeight'];
        $contentsNetworkBidWeight = $group['contentsNetworkBidWeight'];

        if($status != "ELIGIBLE") {
            continue;
        }

        $insert_group = "
        insert into gnp_kwd_group set 
        nccAdgroupId = '{$nccAdgroupId}'
        , customerId = '{$customerId}'
        , nccCampaignId = '{$nccCampaignId}'
        , mobileChannelId = '{$mobileChannelId}'
        , userLock = '{$userLock}'
        , useDailyBudget = '{$useDailyBudget}'
        , useKeywordPlus = '{$useKeywordPlus}'
        , keywordPlusWeight = '{$keywordPlusWeight}'
        , contentsNetworkBidAmt = '{$contentsNetworkBidAmt}'
        , useCntsNetworkBidAmt = '{$useCntsNetworkBidAmt}'
        , mobileNetworkBidWeight = '{$mobileNetworkBidWeight}'
        , pcNetworkBidWeight = '{$pcNetworkBidWeight}'
        , dailyBudget = '{$dailyBudget}'
        , budgetLock = '{$budgetLock}'
        , delFlag = '{$delFlag}'
        , regTm = '{$regTm}'
        , editTm = '{$editTm}'
        , targetSummary = '".json_encode($targetSummary, JSON_UNESCAPED_UNICODE)."'
        , pcChannelKey = '{$pcChannelKey}'
        , mobileChannelKey = '{$mobileChannelKey}'
        , status = '{$status}'
        , statusReason = '{$statusReason}'
        , expectCost = '{$expectCost}'
        , migType = '{$migType}'
        , adgroupAttrJson = '".json_encode($adgroupAttrJson, JSON_UNESCAPED_UNICODE)."'
        , adRollingType = '{$adRollingType}'
        , adgroupType = '{$adgroupType}'
        , systemBiddingType = '{$systemBiddingType}'
        , useCntsNetworkBidWeight = '{$useCntsNetworkBidWeight}'
        , contentsNetworkBidWeight = '{$contentsNetworkBidWeight}'        
        ";
        $result = sql_query($insert_group);

        $param = array(
            "nccAdgroupId" => $nccAdgroupId
        );
        $adkeywordlist = $api->GET('/ncc/keywords', array("nccAdgroupId" => $nccAdgroupId));
        foreach($adkeywordlist as $kwd){

            $nccKeywordId = $kwd['nccKeywordId'];
            $keyword = $kwd['keyword'];
            $customerId = $kwd['customerId'];
            $nccAdgroupId = $kwd['nccAdgroupId'];
            $nccCampaignId = $kwd['nccCampaignId'];
            $userLock = $kwd['userLock'];
            $inspectStatus = $kwd['inspectStatus'];
            $bidAmt = $kwd['bidAmt'];
            $useGroupBidAmt = $kwd['useGroupBidAmt'];
            $delFlag = $kwd['delFlag'];
            $regTm = $kwd['regTm'];
            $editTm = $kwd['editTm'];
            $status = $kwd['status'];
            $statusReason = $kwd['statusReason'];
            $nccQi = $kwd['nccQi'];

            $insert_keyword = "
            insert into gnp_kwd_keyword set 
              nccKeywordId = '{$nccKeywordId}'
            , keyword = '{$keyword}'
            , customerId = '{$customerId}'
            , nccAdgroupId = '{$nccAdgroupId}'
            , nccCampaignId = '{$nccCampaignId}'
            , userLock = '{$userLock}'
            , inspectStatus = '{$inspectStatus}'
            , bidAmt = '{$bidAmt}'
            , useGroupBidAmt = '{$useGroupBidAmt}'
            , delFlag = '{$delFlag}'
            , regTm = '{$regTm}'
            , editTm = '{$editTm}'
            , status = '{$status}'
            , statusReason = '{$statusReason}'
            , nccQi = '".json_encode($nccQi, JSON_UNESCAPED_UNICODE)."'
            ";
            $result = sql_query($insert_keyword);
        }
       

        $adlist = $api->GET('/ncc/ads', $param);
        foreach($adlist as $ad){

            $nccAdId = $ad['nccAdId'];
            $nccAdgroupId = $ad['nccAdgroupId'];
            $customerId = $ad['customerId'];
            $inspectStatus = $ad['inspectStatus'];
            $type = $ad['type'];
            $ad1 = json_encode($ad['ad'], JSON_UNESCAPED_UNICODE) ;
            $adAttr = json_encode($ad['adAttr'], JSON_UNESCAPED_UNICODE) ;
            $userLock = $ad['userLock'];
            $enable = $ad['enable'];
            $delFlag = $ad['delFlag'];
            $regTm = $ad['regTm'];
            $editTm = $ad['editTm'];
            $status = $ad['status'];
            $statusReason = $ad['statusReason'];

            if($status != "ELIGIBLE") {
                continue;
            }

            $insert_ad = "
            insert into gnp_kwd_ad set 
              nccAdId = '{$nccAdId}'
            , nccAdgroupId = '{$nccAdgroupId}'
            , customerId = '{$customerId}'
            , inspectStatus = '{$inspectStatus}'
            , type = '{$type}'
            , ad = '{$ad1}'
            , adAttr = '{$adAttr}'
            , userLock = '{$userLock}'
            , enable = '{$enable}'
            , delFlag = '{$delFlag}'
            , regTm = '{$regTm}'
            , editTm = '{$editTm}'
            , status = '{$status}'
            , statusReason = '{$statusReason}'         
            ";
            $result = sql_query($insert_ad);


            $statIds = "stat_ids"; // stat_ids를 실제 값으로 변경
            //$fields = ' ["impCnt","clkCnt","salesAmt","crto"]'; // fields를 실제 값으로 변경
            $fields = '["impCnt","clkCnt","salesAmt","ctr","cpc","avgRnk","ccnt","recentAvgRnk","recentAvgCpc","pcNxAvgRnk","mblNxAvgRnk","crto","convAmt","ror","cpConv","viewCnt"]';
            $timeRange = '{"since":"2023-04-10","until":"2023-04-10"}'; // timeRange를 실제 값으로 변경
           
            $param = array(
                "id" => $nccAdId,
                "fields" => $fields,
                "timeRange" => $timeRange
            );

            sleep(1);
            $stat_list = $api->GET("/stats" , $param);
            foreach($stat_list['data'] as $stat2){

                $dateStart = $stat2['dateStart'];
                $dateEnd = $stat2['dateEnd'];
                $impCnt = $stat2['impCnt'];
                $clkCnt = $stat2['clkCnt'];
                $salesAmt = $stat2['salesAmt'];
                $ctr = $stat2['ctr'];
                $cpc = $stat2['cpc'];
                $avgRnk = $stat2['avgRnk'];
                $ccnt = $stat2['ccnt'];
                $recentAvgRnk = $stat2['recentAvgRnk'];
                $recentAvgCpc = $stat2['recentAvgCpc'];
                $pcNxAvgRnk = $stat2['pcNxAvgRnk'];
                $mblNxAvgRnk = $stat2['mblNxAvgRnk'];
                $crto = $stat2['crto'];
                $convAmt = $stat2['convAmt'];
                $ror = $stat2['ror'];
                $cpConv = $stat2['cpConv'];
                $viewCnt = $stat2['viewCnt'];
                


                $insert_stat = "
                insert into gnp_kwd_stat set 
                  nccCampaignId = '{$nccCampaignId}'
                , nccAdgroupId = '{$nccAdgroupId}'
                , nccAdId = '{$nccAdId}'
                , dateStart = '{$dateStart}'
                , dateEnd = '{$dateEnd}'
                , impCnt = '{$impCnt}'
                , clkCnt = '{$clkCnt}'
                , salesAmt = '{$salesAmt}'
                , ctr = '{$ctr}'
                , cpc = '{$cpc}'
                , avgRnk = '{$avgRnk}'
                , ccnt = '{$ccnt}'
                , recentAvgRnk = '{$recentAvgRnk}'
                , recentAvgCpc = '{$recentAvgCpc}'
                , pcNxAvgRnk = '{$pcNxAvgRnk}'
                , mblNxAvgRnk = '{$mblNxAvgRnk}'
                , crto = '{$crto}'
                , convAmt = '{$convAmt}'
                , ror = '{$ror}'
                , cpConv = '{$cpConv}'
                , viewCnt = '{$viewCnt}'
                ";
                $result = sql_query($insert_stat);


                
            }
            
        }

    }

    exit;

}


?>