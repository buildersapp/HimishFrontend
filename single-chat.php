<?php
include_once('includes/check-session.php');
$title = "Chat";
$data=[];
$login_id = $userDetails['id'];
$id = base64_decode($_GET['id']);
$user2_id = $id;
$name="";

// $type = isset($_GET['type']) ? (int) base64_decode($_GET['type']) : 0;
$type = base64_decode($_GET['type']); // 0 =normal ,2 = looking,3=company



if($type == 3){
    $query_data ='?company_id='.$id.'';
    $response = sendCurlRequest(BASE_URL.'/get-company'.$query_data, 'GET', []);
    $decodedResponse = json_decode($response, true);
    $data = count($decodedResponse['body']) > 0 ? $decodedResponse['body'][0] : [];
    $user2_id = $data['owner_id'];
}else if($type==2){
    $apiData = [
        'post_id' => $id
    ];
    
    $response = sendCurlRequest(BASE_URL.'/get-posts', 'GET', $apiData);
    $responseDecoded = json_decode($response,true);
    // Decode the response (assuming it's JSON)
    $data = count($responseDecoded['body']) > 0 ? $responseDecoded['body'][0] : [];
    $user2_id = $data['user_id'];
}else{
    $query_data ='?user_id='.$id.'';
    $response = sendCurlRequest(BASE_URL.'/get-profile'.$query_data, 'GET', $apiData);
    $decodedResponse = json_decode($response, true);
    $userD = $decodedResponse['body'];
    $name = $userD['name'];
    $id = 0;
}

include('pages/inbox/single.html');