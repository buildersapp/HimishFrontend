<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Transactions', 'url' => 'transactions.php']
];


$title = "Transactions";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}
include('pages/transactions/list.html');