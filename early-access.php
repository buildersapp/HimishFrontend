<?php
session_start();
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
include_once('includes/web-helpers.php');

$apiData=[];
$response = sendCurlRequest(BASE_URL.'/get-setting', 'GET', $apiData);
$decodedResponse = json_decode($response, true);
$final = $decodedResponse['body'];
if(!empty($final['login_page']) && !empty($final['early_access_page'])){
    $final['login_page'] = json_decode($final['login_page'], true);
    $final['early_access_page'] = json_decode($final['early_access_page'], true);
}

include('pages/maintenance.html');