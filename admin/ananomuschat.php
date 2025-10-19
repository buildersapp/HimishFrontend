<?php
include_once('utils/helpers.php');
// Breadcrumbs
$breadcrumb = [
    ['name' => 'chat', 'url' => 'ananomousRequest.php'],
    ['name' => 'Lists', 'url' => '']
];
$id =base64_decode($_GET['id']);

$apiData=[];
$query_data ='?id='.$id;
$response = sendCurlRequest(BASE_URL.'/get-ananomus-details'.$query_data, 'GET', $apiData);
$decodedResponse = json_decode($response, true);

if (isset($decodedResponse['code']) && $decodedResponse['code'] == 401) {
    http_response_code(401);
    header($_SERVER['PHP_SELF']);
    exit;
}
//   dump($decodedResponse['body']);
$final = ($decodedResponse['body']) ? $decodedResponse['body']: [];
// dump($final);

if(isset($_POST['sendMessage'])){
    $message   =   cleanInputs($_POST['message']);
    $apiData = [
        'message' => $message,
        'id' => $id
    ];

    $response = sendCurlRequest(BASE_URL.'/send-sms-reply', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'ananomuschat.php?id=".$_GET['id']."'</script>";
    }
}
$title = "AnanomusChat";
include('pages/contactUs/ananomusChat.html');