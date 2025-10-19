<?php
include_once('includes/check-session.php');

$apiData=[];
$response = sendCurlRequest(BASE_URL.'/get-sale-person-community-dashboard', 'GET', $apiData);
$decodedResponse = json_decode($response, true);
if($decodedResponse['success']){
    $commData = $decodedResponse['body'];
}

// Title and page rendering (not changed)
$title = "Communities | Sales Representative";

include('pages/community/index.html');
?>
