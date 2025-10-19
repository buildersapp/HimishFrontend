<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Requests', 'url' => ''],
    ['name' => 'Claimed Business', 'url' => 'claimed-business-requests.php']
];

if(isset($_GET['cbAct'])){

    $type   =   cleanInputs($_GET['tp']);
    $id   =   cleanInputs(base64_decode($_GET['id']));
    $apiData = [
        'type' => $type,
        'id' => $id,
    ];

    $response = sendCurlRequest(BASE_URL.'/accept-reject-owner-request', 'POST', $apiData, [], true);
    //dump($response);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'claimed-business-requests.php'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'claimed-business-requests.php?id=".$_GET['id']."&tp=".$type."'</script>";
    }
}


$title = "Claimed Business Requests";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}
include('pages/claimedBusinessRequests/list.html');