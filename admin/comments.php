<?php
include_once('utils/helpers.php');

$faqData = [];
$id = 0;
if(isset($_GET['id'])){
    $id =base64_decode($_GET['id']);
    $apiData=[];
    $query_data ='?comment_id='.$id.'&page=1&limit=1';
    $response = sendCurlRequest(BASE_URL.'/get-suggession-comments'.$query_data, 'GET', $apiData);
    $decodedResponse = json_decode($response, true);
    $faqData = $decodedResponse['body'];
}

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Comments(suggestion)', 'url' => 'comments.php']
];

if(isset($_POST['addComment'])){
    $name   =   cleanInputs($_POST['name']);
    $apiData = [
        'name' => $name,
        'id' => 0
    ];

    $response = sendCurlRequest(BASE_URL.'/add-suggession-comments-admin', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'comments.php'</script>";
    }
}

if(isset($_POST['editComment'])){
    $name   =   cleanInputs($_POST['name']);
    $apiData = [
        'name' => $name,
        'id' => $id
    ];

    $response = sendCurlRequest(BASE_URL.'/add-suggession-comments-admin', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'comments.php'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'comments.php?id=".$_GET['id']."'</script>";
    }
}


$title = "comments";
include('pages/comments/list.html');