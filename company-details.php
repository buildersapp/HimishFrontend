<?php
include_once('includes/custom-functions.php');
include_once('admin/utils/helpers.php');
$current_page = basename($_SERVER['PHP_SELF']);
$defaultContainer = "#posts-container";
$defaultStatus = 1;

// get user details
if(isset($_SESSION['hm_wb_auth_data']['id'])){
    $query_data ='?user_id='.$_SESSION['hm_wb_auth_data']['id'].'&page=1&limit=1';
    $response = sendCurlRequest(BASE_URL.'/get-profile'.$query_data, 'GET', []);
    $decodedResponse = json_decode($response, true);
    $userDetails = $decodedResponse['body'];
}else{
    $userDetails = [];
}

$apiData=[];
$companyId = isset($_GET['id']) ? (int) base64_decode($_GET['id']) : 0;
if($companyId>0){
    $query_data ='?company_id='.$companyId.'';
    $response = sendCurlRequest(BASE_URL.'/get-company'.$query_data, 'GET', $apiData);
    $decodedResponse = json_decode($response, true);
    $companyDetails = $decodedResponse['body'];
} 

if(count($companyDetails) == 0){
    header('Location: 500.php');
}

$members = $companyDetails[0]['company_members'];
$members_requests = array_values(array_filter($members, function($mem) {
    return $mem['status'] == 0;
}));

$active_members = array_values(array_filter($members, function($mem) {
    return $mem['status'] == 1;
}));

$chat_members = array_values(array_filter($members, function($mem) {
    return $mem['can_chat'] == 1;
}));
//dump($members);

$isMainAdmin = isAdminOrMainOwner(@$userDetails['id'], $members);
$isMainOwner = isMainOwner(@$userDetails['id'], $members);

// get recommends
$query_dataR ='?company_id='.$companyId.'';
$responseR = sendCurlRequest(BASE_URL.'/get-company-recommends'.$query_dataR, 'GET', $apiData);
$decodedResponseR = json_decode($responseR, true);
$recommends = $decodedResponseR['body'];
//dump($recommends);

// get showcase
$query_dataS ='?company_id='.$companyId.'';
$responseRS = sendCurlRequest(BASE_URL.'/get-showcase'.$query_dataS, 'GET', $apiData);
$decodedResponseRS = json_decode($responseRS, true);
$showcases = $decodedResponseRS['body'];
//dump($showcases);

// Title and page rendering (not changed)
$title = $companyDetails[0]['name']." | Company";

// check if login user is admin or owner
function isAdminOrMainOwner($login_id, $members) {
    foreach ($members as $member) {
        if ($member['user']['id'] == $login_id) {
            return ($member['is_admin'] == 1 || $member['main_owner'] == 1);
        }
    }
    return false; // Return false if user is not found
}

// check if login user is main owner owner
function isMainOwner($login_id, $members) {
    foreach ($members as $member) {
        if ($member['user']['id'] == $login_id) {
            return ($member['main_owner'] == 1);
        }
    }
    return false; // Return false if user is not found
}

include('pages/companies/details.html');
?>
