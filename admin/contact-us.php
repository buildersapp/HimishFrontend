<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'General Settings', 'url' => ''],
    ['name' => 'Contact Us', 'url' => 'contact-us.php']
];

$userId = (isset($_GET['user_id'])) ? base64_decode($_GET['user_id']) : 0;

$title = "Contact Us";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}
include('pages/contactUs/list.html');