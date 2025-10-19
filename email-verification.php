<?php
session_start();
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
include_once('admin/utils/helpers.php');
include_once('includes/web-helpers.php');
redirectIfLoggedIn();

$email = '';
$originalEmail = '';
if(isset($_GET['em'])){
    $email = maskEmail(base64_decode($_GET['em']));
    $originalEmail = base64_decode($_GET['em']);
}

$title = "Email Verification";
include('pages/email-verification.html');