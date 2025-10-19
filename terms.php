<?php
include_once('admin/utils/helpers.php');

$responseSettings = sendCurlRequest(BASE_URL.'/get-setting', 'GET', []);
$decodedResponseSettings = json_decode($responseSettings, true);
$generalSettings = $decodedResponseSettings['body'];

$title = "Terms & Conditions";
include('pages/terms.html');
?>