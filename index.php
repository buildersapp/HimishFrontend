<?php
ini_set('session.gc_maxlifetime', 604800);
ini_set('session.cookie_lifetime', 604800);
session_start();
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);

include('includes/custom-functions.php');
include_once('includes/web-helpers.php');

redirectIfLoggedIn();

// Google oAuth
$localhost_servers = ['127.0.0.1', '::1', 'localhost'];

// if (isset($_SESSION['hm_wb_logged_in']) && $_SESSION['hm_wb_logged_in'] === true) {
//     header("Location: home.php");
//     exit();
// }

$apiData=[];
$response = sendCurlRequest(BASE_URL.'/get-setting', 'GET', $apiData);
$decodedResponse = json_decode($response, true);
$final = $decodedResponse['body'];
if(!empty($final['login_page']) && !empty($final['early_access_page'])){
    $final['login_page'] = json_decode($final['login_page'], true);
    $final['early_access_page'] = json_decode($final['early_access_page'], true);
}

$title = "Himish";


//include('pages/index.html');
include('pages/maintenance.html');