<?php
include_once('utils/helpers.php');

$faqData = [];
$id = 0;
if(isset($_GET['id'])){
    $id =base64_decode($_GET['id']);
    $apiData=[];
    $query_data ='?faq_id='.$id.'&page=1&limit=1';
    $response = sendCurlRequest(BASE_URL.'/get-faqs-admin'.$query_data, 'GET', $apiData);
    $decodedResponse = json_decode($response, true);
    $faqData = $decodedResponse['body'];
}

// Breadcrumbs
$breadcrumb = [
    ['name' => 'General Settings', 'url' => ''],
    ['name' => 'Faqs', 'url' => 'faqs.php']
];

if(isset($_POST['addFaq'])){
    $question   =   cleanInputs($_POST['question']);
    $answer     =   cleanInputs($_POST['answer']);
    $apiData = [
        'question' => $question,
        'answer' => $answer,
    ];

    $response = sendCurlRequest(BASE_URL.'/add-edit-faq', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'faqs.php'</script>";
    }
}

if(isset($_POST['editFaq'])){
    $question   =   cleanInputs($_POST['question']);
    $answer     =   cleanInputs($_POST['answer']);
    $apiData = [
        'question' => $question,
        'answer' => $answer,
    ];

    $response = sendCurlRequest(BASE_URL.'/add-edit-faq?faq_id='.$id, 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'faqs.php'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'faqs.php?id=".$_GET['id']."'</script>";
    }
}


$title = "FAQs";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}
include('pages/faqs/list.html');