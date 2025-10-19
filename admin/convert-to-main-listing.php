<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => 'listings.php'],
    ['name' => 'Preview Listing For Data Import (Looking For)', 'url' => '']
];

$title = "Preview Listing";
include('pages/listings/preview-convert-to-main-listing.html');