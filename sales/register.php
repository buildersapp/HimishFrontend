<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);
include_once('includes/web-helpers.php');

redirectIfLoggedIn();

$title = "Register | Sales Representative";
include('pages/auth/register.html');