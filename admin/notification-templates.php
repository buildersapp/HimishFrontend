<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'General Settings', 'url' => ''],
    ['name' => 'Notification Setup', 'url' => 'notification-templates.php']
];

// get all modules

$apiData=[];
$response = sendCurlRequest(BASE_URL.'/getNotiModule', 'GET', $apiData);
$decodedResponse = json_decode($response, true);
$final = $decodedResponse['body'];

// get module 1
$defaultId = $final[0]['id'];
$defaultTab = $final[0]['title'];

if(isset($_POST['submitNM'])){

    // dump($_POST);

    $id                     =   cleanInputs(base64_decode($_POST['Id']));
    $push_message           =   cleanInputs($_POST['push_message']);
    $notification_message   =   cleanInputs($_POST['notification_message']);
    $template_code   =   cleanInputs($_POST['template_code']);
    $email_text   =   ($_POST['email_text']);
    $title   =   cleanInputs($_POST['title']);
    $name   =   cleanInputs($_POST['name']);
    $after_expire_time   =   cleanInputs($_POST['after_expire_time']);
    $noti_enable   = isset($_POST['noti_enable']) ? cleanInputs($_POST['noti_enable']) : 0;
    $push_enable   = isset($_POST['push_enable']) ? cleanInputs($_POST['push_enable']) : 0;
    $email_enable  = isset($_POST['email_enable']) ? cleanInputs($_POST['email_enable']) : 0;    
    $apiData = [
        'id' => $id,
        'title' => $title,
        'name' => $name,
        'noti_enable' => $noti_enable,
        'push_enable' => $push_enable,
        'email_enable' => $email_enable,
        'after_expire_time' => $after_expire_time,
        'template_code' => $template_code
    ];

    if(isset($_POST['email_enable'])){
        $apiData['email_text'] = $email_text;
    }
    if(isset($_POST['noti_enable'])){
        $apiData['notification_message'] = $notification_message;
    }
    if(isset($_POST['push_enable'])){
        $apiData['push_message'] = $push_message;
    }
    //dump($apiData);


    $response = sendCurlRequest(BASE_URL.'/edit-notification?notiId='.$id, 'POST', $apiData, [], true);
    //dump($response);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'notification-templates.php'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'notification-templates.php'</script>";
    }
}

$title = "Notification & Email Setup";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}
include('pages/notificationTemplates/details.html');