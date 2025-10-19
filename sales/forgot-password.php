<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0); 
session_start();
include_once('includes/web-helpers.php');
redirectIfLoggedIn();

$title = "Forgot Password | Sales Representative";
include('pages/auth/forgot-password.html');