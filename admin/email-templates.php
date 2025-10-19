<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'General Settings', 'url' => ''],
    ['name' => 'Email Template Setup', 'url' => 'email-templates.php']
];

// get all modules

$apiData=[];
$response = sendCurlRequest(BASE_URL.'/getNotiModule', 'GET', $apiData);
$decodedResponse = json_decode($response, true);
$final = $decodedResponse['body'];

// get module 1
$defaultId = $final[0]['id'];
$defaultTab = $final[0]['title'];

if(isset($_POST['submitEM'])){

    $id        =   cleanInputs(base64_decode($_POST['Id']));
    $title     =   cleanInputs($_POST['title']);
    $subject   =   cleanInputs($_POST['subject']);
    $subject   =   cleanInputs($_POST['subject']);
    $template   =   cleanInputs($_POST['template']);
    $apiData = [
        'title' => $title,
        'subject' => $subject,
        'template' => $template,
        'template_id' => $id
    ];

    $response = sendCurlRequest(BASE_URL.'/update-email-template', 'POST', $apiData, [], true);
    //dump($response);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'email-templates.php'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'email-templates.php'</script>";
    }
}

$title = "Email Settings";
include('pages/emailTemplates/details.html');