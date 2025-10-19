<?php
include_once('includes/check-session.php');

$apiData=[];
$query_data ='?user_id='.$userDetails['id'];
$response = sendCurlRequest(BASE_URL.'/get-community'.$query_data, 'GET', $apiData);
$decodedResponse = json_decode($response, true);
$communities = [];
if(count($decodedResponse)){
    $communities = $decodedResponse['body'];
}

$other_communities = array_values(array_filter($communities, function($data) {
    return $data['is_selected'] == 0;
}));

// Title and page rendering (not changed)
$title = "Join Community | Sales Representative";

include('pages/community/join.html');
?>
