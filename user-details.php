<?php
include_once('includes/check-session.php');
$isSelf = 0;
$defaultContainer = "#posts-container";
$defaultStatus = 1;
$type = isset($_GET['type']) ? $_GET['type'] : 0;
$tp = isset($_GET['tp']) ? $_GET['tp'] : 'posts';
if($tp == "draft"){
    $defaultStatus = 2;
    $defaultContainer = "#posts-draft-container";
}

$apiData=[];
$userId = isset($_GET['id']) ? (int) base64_decode($_GET['id']) : 0;
if($userId>0){
    $query_data ='?user_id='.$userId.'';
    $response = sendCurlRequest(BASE_URL.'/get-profile'.$query_data, 'GET', $apiData);
    $decodedResponse = json_decode($response, true);
    $userD = $decodedResponse['body'];
}

if(!$userD){
    header('Location: 500.php');
}

if($_SESSION['hm_wb_auth_data']['id'] == $userId){
    $isSelf = 1;
}

//dump($userDetails);

// get recommends
$query_dataR ='?user_id='.$userId.'';
$responseR = sendCurlRequest(BASE_URL.'/get-company-recommends'.$query_dataR, 'GET', $apiData);
$decodedResponseR = json_decode($responseR, true);
$recommends = $decodedResponseR['body'];

// Title and page rendering (not changed)
$title = $userD['name']."'s Profile";
include('pages/user/details.html');
?>
