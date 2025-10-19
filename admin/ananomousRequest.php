<?php
include_once('utils/helpers.php');
// Breadcrumbs
$breadcrumb = [
    ['name' => 'Contact Us', 'url' => 'contact-us.php'],
    ['name' => 'Lists', 'url' => '']
];

if(isset($_POST['mailSend'])){
    $message   =   cleanInputs($_POST['message']);
    $id   =   cleanInputs($_POST['id']);
    $apiData = [
        'message' => $message,
        'id' => $id
    ];

    $response = sendCurlRequest(BASE_URL.'/send-mail-reply', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'ananomousRequest.php'</script>";
    }
}
$title = "AnanomusClaimRequest";
include('pages/contactUs/ananomusList.html');