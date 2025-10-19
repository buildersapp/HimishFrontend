<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Wallet', 'url' => 'wallet.php']
];


$title = "Wallets";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}
include('pages/wallet/list.html');