<?php
include_once('utils/helpers.php');

$editData = [];
$id = 0;
if(isset($_GET['id'])){
    $id =base64_decode($_GET['id']);
    $apiData=[];
    $query_data ='?id='.$id.'&page=1&limit=1';
    $response = sendCurlRequest(BASE_URL.'/getPlans'.$query_data, 'GET', $apiData);
    $decodedResponse = json_decode($response, true);
    $editData = $decodedResponse['body'];
}

// Breadcrumbs
$breadcrumb = [
    ['name' => 'General Settings', 'url' => ''],
    ['name' => 'Plans', 'url' => 'plans.php']
];

if(isset($_POST['editPlan'])){
    $title   =   cleanInputs($_POST['title']);
    $price   =   cleanInputs($_POST['price']);
    $tax     =   cleanInputs($_POST['tax']);
    $apiData = [
        'title' => $title,
        'tax' => $tax,
        'price' => $price,
    ];

    $response = sendCurlRequest(BASE_URL.'/edit-plan?id='.$id, 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'plans.php'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'plans.php?id=".$_GET['id']."'</script>";
    }
}


$title = "Plans";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}
include('pages/plans/list.html');