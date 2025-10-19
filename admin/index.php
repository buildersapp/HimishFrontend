<?php
session_start();

if (isset($_SESSION['hm_logged_in']) && $_SESSION['hm_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$title = "Login";
include('pages/index.html');