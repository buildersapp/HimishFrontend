<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Dashboard', 'url' => ''],
];

$response = sendCurlRequest(BASE_URL.'/admin-dashboard', 'GET', []);
$decodedResponse = json_decode($response, true);
$final = $decodedResponse['body'];
//dump($decodedResponse);

$title = "Dashboard";
include('pages/dashboard/index.html');