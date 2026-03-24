<?php
require_once '../../../common.php';

ini_set("default_socket_timeout", 30);
require_once 'restapi.php';

$config = parse_ini_file("sample.ini");
print_r($config);

function debug($obj, $detail = false)
{
    if (is_array($obj)) {
        echo "size : " . count($obj) . "\n";
    }
    if ($detail) {
        print_r($obj);
    }
}

// #. detail log
$DEBUG = false;

$api = new RestApi($config['BASE_URL'], $config['API_KEY'], $config['SECRET_KEY'], $config['CUSTOMER_ID']);

echo "Test ManagedCustomerLink\n";
$customerlist = $api->GET('/customer-links', array('type' => 'MYCLIENTS'));
debug($customerlist, $DEBUG);


$truncate_sql = "
truncate gnp_kwd_customerlist;
";
$res = sql_query($truncate_sql);

$truncate_sql = "
truncate gnp_kwd_channels;
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

$cstm_into_sql = "
INSERT INTO gnp_kwd_customerlist (customerLinkId,managerCustomerId,clientCustomerId,roleId,linkStatus,description,regTm,editTm,managerLoginId,clientLoginId,managerName,managerEnable,managerPenaltySt,managerCustomerDelFlag,clientEnable,clientPenaltySt,clientCustomerDelFlag,delFlag) VALUES 
";

$cstm_data_sql = "";
foreach($customerlist as $key){
    //$cstm_data_sql .= "($customerlist[$i]['customerLinkId'], $customerlist[$i]['managerCustomerId'], $customerlist[$i]['clientCustomerId'], $customerlist[$i]['roleId'], $customerlist[$i]['linkStatus'], $customerlist[$i]['description'], $customerlist[$i]['regTm'], $customerlist[$i]['editTm'], $customerlist[$i]['clientLoginId'], $customerlist[$i]['managerName'], $customerlist[$i]['managerEnable'], $customerlist[$i]['managerPenaltySt'], $customerlist[$i]['managerCustomerDelFlag'], $customerlist[$i]['clientEnable'], $customerlist[$i]['clientPenaltySt'], $customerlist[$i]['clientCustomerDelFlag'], $customerlist[$i]['delFlag']  );";

    $customerLinkId         = $key['customerLinkId'];
    $managerCustomerId      = $key['managerCustomerId'];
    $clientCustomerId       = $key['clientCustomerId'];
    $roleId                 = $key['roleId'];
    $linkStatus             = $key['linkStatus'];
    $description            = $key['description'];
    $regTm                  = $key['regTm'];
    $editTm                 = $key['editTm'];
    $managerLoginId         = $key['managerLoginId'];
    $clientLoginId          = $key['clientLoginId'];
    $managerName            = $key['managerName'];
    $managerEnable          = $key['managerEnable'];
    $managerPenaltySt       = $key['managerPenaltySt'];
    $managerCustomerDelFlag = $key['managerCustomerDelFlag'];
    $clientEnable           = $key['clientEnable'];
    $clientPenaltySt        = $key['clientPenaltySt'];
    $clientCustomerDelFlag  = $key['clientCustomerDelFlag'];
    $delFlag                = $key['delFlag'];
    
    if(!next($customerlist)) {
        $appendComma = ';';
    } else {
        $appendComma = ',';
    }
    $cstm_data_sql .= "('{$customerLinkId}',{$managerCustomerId},{$clientCustomerId},{$roleId},{$linkStatus},'{$description}','{$regTm}','{$editTm}','{$managerLoginId}','{$clientLoginId}','{$managerName}',{$managerEnable},{$managerPenaltySt},{$managerCustomerDelFlag},{$clientEnable},{$clientPenaltySt},{$clientCustomerDelFlag},{$delFlag}){$appendComma}";

    $api = new RestApi($config['BASE_URL'], $config['API_KEY'], $config['SECRET_KEY'], (int)$clientCustomerId);    
    
    
    $adgrouplist = $api->GET('/ncc/adgroups');
    foreach($adgrouplist as $group){

        $nccCampaignId = $group['nccCampaignId'];
        $nccAdgroupId = $group['nccAdgroupId'];


        $statIds = "stat_ids"; // stat_ids를 실제 값으로 변경
        //$fields = ' ["impCnt","clkCnt","salesAmt","crto"]'; // fields를 실제 값으로 변경
        $fields = '["impCnt","clkCnt ","ctr ","cpc ","salesAmt ","ccnt ","crto ","convAmt ","ror ","cpConv ","avgRnk ","pcNxAvgRnk ","mblNxAvgRnk ","recentAvgRnk ","viewCnt "]';
        $timeRange = '{"since":"2023-04-01","until":"2023-04-07"}'; // timeRange를 실제 값으로 변경

        $param = array(
            "ids" => $nccCampaignId,
            "fields" => $fields,
            "datePreset" => 'yesterday'
        );

        $stat_list = $api->GET("/stats" , $param);
        debug($stat_list, $DEBUG);
        


    // }

   

    // $config['CUSTOMER_ID'] = $clientCustomerId;
    // $businesschannellist = $api->GET('/ncc/channels');

    // foreach($businesschannellist as $chn){
    //     $nccBusinessChannelId = $chn['nccBusinessChannelId'];
    //     $customerId = $chn['customerId'];
    //     $channelTp = $chn['channelTp'];
    //     $name = $chn['name'];
    //     $channelKey = $chn['channelKey'];
    //     $businessInfo = $chn['businessInfo'];
    //     $regTm = $chn['regTm'];
    //     $editTm = $chn['editTm'];
    //     $firstChargeTm = $chn['firstChargeTm'];
    //     $inspectTm = $chn['inspectTm'];
    //     $mobileInspectStatus = $chn['mobileInspectStatus'];
    //     $status = $chn['status'];
    //     $statusReason = $chn['statusReason'];
    //     $adultStatus = $chn['adultStatus'];
    //     $enabled = $chn['enabled'];
    //     $blackStatus = $chn['enabled'];

    //     $insert_bz = "
    //     insert into gnp_kwd_channels set 
    //         nccBusinessChannelId = '{$nccBusinessChannelId}'
    //     ,   customerId = '{$customerId}'
    //     ,   channelTp = '{$channelTp}'
    //     ,   name = '{$name}'
    //     ,   channelKey = '{$channelKey}'
    //     ,   businessInfo = '{$businessInfo}'
    //     ,   regTm = '{$regTm}'
    //     ,   editTm = '{$editTm}'
    //     ,   firstChargeTm = '{$firstChargeTm}'
    //     ,   inspectTm = '{$inspectTm}'
    //     ,   mobileInspectStatus = '{$mobileInspectStatus}'
    //     ,   status = '{$status}'
    //     ,   statusReason = '{$statusReason}'
    //     ,   adultStatus = '{$adultStatus}'
    //     ,   enabled = '{$enabled}'
    //     ,   blackStatus = '{$blackStatus}'
    //     ";
    //     $result = sql_query($insert_bz);
    // }

    // $campaignlist = $api->GET('/ncc/campaigns');

    // foreach($campaignlist as $cpn){
    //     $nccCampaignId   = $cpn['nccCampaignId'];
    //     $customerId      = $cpn['customerId'];
    //     $name            = $cpn['name'];
    //     $userLock        = $cpn['userLock'];
    //     $campaignTp      = $cpn['campaignTp'];
    //     $deliveryMethod  = $cpn['deliveryMethod'];
    //     $trackingUrl     = $cpn['trackingUrl'];
    //     $trackingMode    = $cpn['trackingMode'];
    //     $usePeriod       = $cpn['usePeriod'];
    //     $dailyBudget     = $cpn['dailyBudget'];
    //     $useDailyBudget  = $cpn['useDailyBudget'];
    //     $totalChargeCost = $cpn['totalChargeCost'];
    //     $status          = $cpn['status'];
    //     $statusReason    = $cpn['statusReason'];
    //     $expectCost      = $cpn['expectCost'];
    //     $migType         = $cpn['migType'];
    //     $delFlag         = $cpn['delFlag'];
    //     $regTm           = $cpn['regTm'];
    //     $editTm          = $cpn['editTm'];
        
    //     $insert_cpn = "
    //     insert into gnp_kwd_campaigns set 
    //         nccCampaignId = '{$nccCampaignId}'
    //     ,   customerId = '{$customerId}'
    //     ,   name = '{$name}'
    //     ,   userLock = '{$userLock}'
    //     ,   campaignTp = '{$campaignTp}'
    //     ,   deliveryMethod = '{$deliveryMethod}'
    //     ,   trackingUrl = '{$trackingUrl}'
    //     ,   trackingMode = '{$trackingMode}'
    //     ,   usePeriod = '{$usePeriod}'
    //     ,   dailyBudget = '{$dailyBudget}'
    //     ,   useDailyBudget = '{$useDailyBudget}'
    //     ,   totalChargeCost = '{$totalChargeCost}'
    //     ,   status = '{$status}'
    //     ,   statusReason = '{$statusReason}'
    //     ,   expectCost = '{$expectCost}'
    //     ,   migType = '{$migType}'
    //     ,   delFlag = '{$delFlag}'
    //     ,   regTm = '{$regTm}'
    //     ,   editTm = '{$editTm}'
    //     ";
    //     $result = sql_query($insert_cpn);
    // }

    // $adgrouplist = $api->GET('/ncc/adgroups');
    // $targetadgroup = $adgrouplist[0];
    
    // foreach($adgrouplist as $grp){

    //     $nccAdgroupId = $grp['nccAdgroupId'];
    //     $customerId = $grp['customerId'];
    //     $nccCampaignId = $grp['nccCampaignId'];
    //     $mobileChannelId = $grp['mobileChannelId'];
    //     $pcChannelId = $grp['pcChannelId'];
    //     $bidAmt = $grp['bidAmt'];
    //     $name = $grp['name'];
    //     $userLock = (int)$grp['userLock'];
    //     $useDailyBudget = (int)$grp['useDailyBudget'];
    //     $useKeywordPlus = (int)$grp['useKeywordPlus'];
    //     $keywordPlusWeight = $grp['keywordPlusWeight'];
    //     $contentsNetworkBidAmt = $grp['contentsNetworkBidAmt'];
    //     $useCntsNetworkBidAmt = (int)$grp['useCntsNetworkBidAmt'];
    //     $mobileNetworkBidWeight = $grp['mobileNetworkBidWeight'];
    //     $pcNetworkBidWeight = $grp['pcNetworkBidWeight'];
    //     $dailyBudget = $grp['dailyBudget'];
    //     $budgetLock = (int)$grp['budgetLock'];
    //     $delFlag = (int)$grp['delFlag'];
    //     $regTm = $grp['regTm'];
    //     $editTm = $grp['editTm'];
    //     $targetSummary = implode( '||', $grp['targetSummary'] );
    //     $pcChannelKey = $grp['pcChannelKey'];
    //     $mobileChannelKey = $grp['mobileChannelKey'];
    //     $status = $grp['status'];
    //     $statusReason = $grp['statusReason'];
    //     $expectCost = $grp['expectCost'];
    //     $migType = $grp['migType'];
    //     $adgroupAttrJson = implode( '||', $grp['adgroupAttrJson'] );
    //     $adRollingType = $grp['adRollingType'];
    //     $adgroupType = $grp['adgroupType'];
    //     $systemBiddingType = $grp['systemBiddingType'];
    //     $useCntsNetworkBidWeight = (int)$grp['useCntsNetworkBidWeight'];
    //     $contentsNetworkBidWeight = $grp['contentsNetworkBidWeight'];


    //     $insert_grp = "
    //     insert into gnp_kwd_group set 
    //         nccAdgroupId = '{$nccAdgroupId}'
    //     ,   customerId = '{$customerId}'
    //     ,   nccCampaignId = '{$nccCampaignId}'
    //     ,   mobileChannelId = '{$mobileChannelId}'
    //     ,   pcChannelId = '{$pcChannelId}'
    //     ,   bidAmt = '{$bidAmt}'
    //     ,   name = '{$name}'
    //     ,   userLock = '{$userLock}'
    //     ,   useDailyBudget = '{$useDailyBudget}'
    //     ,   useKeywordPlus = '{$useKeywordPlus}'
    //     ,   keywordPlusWeight = '{$keywordPlusWeight}'
    //     ,   contentsNetworkBidAmt = '{$contentsNetworkBidAmt}'
    //     ,   useCntsNetworkBidAmt = '{$useCntsNetworkBidAmt}'
    //     ,   mobileNetworkBidWeight = '{$mobileNetworkBidWeight}'
    //     ,   pcNetworkBidWeight = '{$pcNetworkBidWeight}'
    //     ,   dailyBudget = '{$dailyBudget}'
    //     ,   budgetLock = '{$budgetLock}'
    //     ,   delFlag = '{$delFlag}'
    //     ,   regTm = '{$regTm}'
    //     ,   editTm = '{$editTm}'
    //     ,   targetSummary = '{$targetSummary}'
    //     ,   pcChannelKey = '{$pcChannelKey}'
    //     ,   mobileChannelKey = '{$mobileChannelKey}'
    //     ,   status = '{$status}'
    //     ,   statusReason = '{$statusReason}'
    //     ,   expectCost = '{$expectCost}'
    //     ,   migType = '{$migType}'
    //     ,   adgroupAttrJson = '{$adgroupAttrJson}'
    //     ,   adRollingType = '{$adRollingType}'
    //     ,   adgroupType = '{$adgroupType}'
    //     ,   systemBiddingType = '{$systemBiddingType}'
    //     ,   useCntsNetworkBidWeight = '{$useCntsNetworkBidWeight}'
    //     ,   contentsNetworkBidWeight = '{$contentsNetworkBidWeight}'
    //     ";
    //     $result = sql_query($insert_grp);


        


    }

    
}

$cstm_sql = $cstm_into_sql.$cstm_data_sql;
$res = sql_query($cstm_sql);

debug($cstm_sql, $DEBUG);
error_log($cstm_sql ,3, G5_DATA_PATH."/log/tempSql.log");




// echo "  #2. CREATE Adgroup\n";
// $data = array(
//     "name" => 'TEST_#' . rand(),
//     "nccCampaignId" => $targetadgroup ["nccCampaignId"],
//     "pcChannelId" => $targetadgroup ["pcChannelId"],
//     "mobileChannelId" => $targetadgroup ["mobileChannelId"]
// );
// $createadgroup = $api->POST('/ncc/adgroups', $data);
// debug($createadgroup, $DEBUG);

// echo "  #3. UPDATE Adgroup\n";
// $createadgroup["userLock"] = 1;
// $updateadgroup = $api->PUT('/ncc/adgroups/' . $createadgroup["nccAdgroupId"], $createadgroup, array("fields" => 'userLock'));
// debug($updateadgroup, $DEBUG);

// echo "Test AdKeyword\n";
// echo "  #1. CREATE AdKeyword\n";
// $data = array(
//     array(
//         "keyword" => "hello2"
//     )
// );
// $createkeyword = $api->POST('/ncc/keywords', $data, array("nccAdgroupId" => $updateadgroup["nccAdgroupId"]));
// debug($createkeyword, $DEBUG);

// echo "  #2. GET AdKeyword\n";
// $adkeywordlist = $api->GET('/ncc/keywords', array("nccAdgroupId" => $updateadgroup["nccAdgroupId"]));
// debug($adkeywordlist, $DEBUG);

// echo "  #3. UPDATE AdKeyword\n";
// $adkeywordlist[0]["userLock"] = 1;
// $updatekeyword = $api->PUT('/ncc/keywords', $adkeywordlist, array("fields" => "userLock"));
// debug($updatekeyword, $DEBUG);

// echo "  #4. DELETE AdKeyword\n";
// $api->DELETE('/ncc/keywords/' . $createkeyword[0]["nccKeywordId"]);

// echo "  #4. DELETE Adgroup\n";
// $api->DELETE('/ncc/adgroups/' . $createadgroup["nccAdgroupId"]);

// echo "Test Estimate\n";
// echo "  #1. average-position-bid\n";
// $req_avg_pos = array(
//     "device" => "PC",
//     "items" => array(
//         array("key" => "제주여행", "position" => 1),
//         array("key" => "게스트하우스", "position" => 2),
//         array("key" => "자전거여행", "position" => 3),
//     )
// );
// $response = $api->POST('/estimate/average-position-bid/keyword', $req_avg_pos);
// debug($response, $DEBUG);

// echo "  #2. exposure-minimum-bid\n";
// $req_bid = array(
//     "device" => "PC",
//     "period" => "MONTH",
//     "items" => array(
//         "제주여행",
//         "게스트하우스",
//         "자전거여행",
//     )
// );
// $response = $api->POST('/estimate/exposure-minimum-bid/keyword', $req_bid);
// debug($response, $DEBUG);

// echo "  #3. median-bid\n";
// $response = $api->POST('/estimate/median-bid/keyword', $req_bid);
// debug($response, $DEBUG);

// echo "  #4. performance\n";
// $req_performance = array(
//     "device" => "PC",
//     "keywordplus" => true,
//     "key" => "중고차",
//     "bids" => array(
//         100,
//         500,
//         1000,
//         1500,
//         2000,
//         3000,
//         5000,
//     )
// );
// $response = $api->POST('/estimate/performance/keyword', $req_performance);
// debug($response, $DEBUG);

// echo "  #5. performance-bulk\n";
// $req_performance_bulk = array (
// 		"items" => array (
// 				0 => array (
// 						"device" => "PC",
// 						"keywordplus" => true,
// 						"keyword" => "제주여행",
// 						"bid" => 70 
// 				),
// 				1 => array (
// 						"device" => "PC",
// 						"keywordplus" => true,
// 						"keyword" => "제주도",
// 						"bid" => 80 
// 				),
// 				2 => array (
// 						"device" => "PC",
// 						"keywordplus" => true,
// 						"keyword" => "제주맛집",
// 						"bid" => 90 
// 				) 
// 		) 
// );
// $response = $api->POST('/estimate/performance-bulk', $req_performance_bulk);
// debug($response, $DEBUG);

echo "\nTest End\n";
?>