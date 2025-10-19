<?php
session_start();
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);

include_once('includes/web-helpers.php');
redirectIfLoggedIn();

$profileGroups =[];
$responsePG= sendCurlRequest(BASE_URL.'/profile-group-list', 'GET', []);
$decodedResponsePG = json_decode($responsePG, true);
if($decodedResponsePG['success']){
    $profileGroups = $decodedResponsePG['body'];
}

if(isset($_GET['email'])){
    $_SESSION['hm_at_email'] = $_GET['email'];
}

if(isset($_GET['post_id'])){
    $_SESSION['hm_at_post_id'] = $_GET['post_id'];
}

if(isset($_GET['id'])){
    $id = base64_decode($_GET['id']);
    $query_data ='?id='.$id.'&page=1&limit=1';
    $responseIUser = sendCurlRequest(BASE_URL.'/profile-group-list'.$query_data, 'GET', []);
    $decodedResponseIUser = json_decode($responseIUser, true);
    $apiDataU = $decodedResponseIUser['body'][0];
}

$title = "Register";
include('pages/register.html');