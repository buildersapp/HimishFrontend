<?php
include_once('admin/utils/helpers.php');

$responseSettings = sendCurlRequest(BASE_URL.'/get-faqs', 'GET', []);
$decodedResponseSettings = json_decode($responseSettings, true);
$generalSettings = $decodedResponseSettings['body'];

// Make the API request and get the response
$query_dataDS = '?page=1&limit=10000';
$responseDS = sendCurlRequest(BASE_URL . '/get-deals-share' . $query_dataDS, 'GET', []);
$responseDecodedDS = json_decode($responseDS,true);

// Decode the response (assuming it's JSON)
$dealShareData = $responseDecodedDS['body'];

$title = "FAQ";
include('pages/faqs.html');
?>