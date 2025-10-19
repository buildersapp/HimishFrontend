<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Requests', 'url' => ''],
    ['name' => 'Ads', 'url' => 'ads-requests.php']
];

if(isset($_GET['cbAct']) && isset($_GET['tp']) && $_GET['tp'] == 1){

    $type   =   cleanInputs($_GET['tp']);
    $id   =   cleanInputs(base64_decode($_GET['id']));
    $apiData = [
        'type' => $type,
        'id' => $id,
    ];

    $response = sendCurlRequest(BASE_URL.'/accept-reject-ads-request', 'POST', $apiData, [], true);
    //dump($response);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'ads-requests.php'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'ads-requests.php?id=".$_GET['id']."&tp=".$type."'</script>";
    }
}

if(isset($_POST['submitAdsWithRsn'])){

    $type       =   cleanInputs($_POST['tp']);
    $id         =   cleanInputs(base64_decode($_POST['id']));
    $reason     =   cleanInputs($_POST['reason']);
    $apiData = [
        'type' => $type,
        'id' => $id,
        'reason' => $reason
    ];

    $response = sendCurlRequest(BASE_URL.'/accept-reject-ads-request', 'POST', $apiData, [], true);
    //dump($response);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'ads-requests.php'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'ads-requests.php?id=".$_GET['id']."&tp=".$type."'</script>";
    }
}


$title = "Ads Requests";
include('pages/adsRequests/list.html');