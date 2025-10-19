<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
include_once('includes/web-helpers.php');

redirectIfLoggedIn();

// Google oAuth
$localhost_servers = ['127.0.0.1', '::1', 'localhost'];

$title = "Login | Sales Representative";

include('pages/auth/login.html');