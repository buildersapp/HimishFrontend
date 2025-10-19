<?php
include_once('utils/helpers.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Citilytics Data', 'url' => 'citilytics-data.php']
];

## get All Comapnies
$user_data = sendCurlRequest(BASE_URL.'/get-new-listing-admin', 'GET', []);
$userDataResponse = json_decode($user_data, true);
$data = $userDataResponse['body'];
// dump($data);
$adTypeMapping = [
    "Job" => ["icon" => "💼", "label" => "Job Listings", "class" => "bg-primary"],
    "Product" => ["icon" => "📦", "label" => "Product Listings", "class" => "bg-success"],
    "On Sale" => ["icon" => "📦", "label" => "On Sale Listings", "class" => "bg-success"],
    "Service" => ["icon" => "🛠️", "label" => "Service Listings", "class" => "bg-info"],
    "Real Estate" => ["icon" => "🏡", "label" => "Real Estate Listings", "class" => "bg-warning text-dark"],
    "Car Sales" => ["icon" => "🚗", "label" => "Car Sales Listings", "class" => "bg-danger"],
    "Event" => ["icon" => "🎉", "label" => "Event Listings", "class" => "bg-secondary"],
    "General Item" => ["icon" => "📌", "label" => "General Items", "class" => "bg-dark"],
    "Lost & Found" => ["icon" => "🔍", "label" => "Lost & Found", "class" => "bg-light text-dark"],
    "Dress" => ["icon" => "👗", "label" => "Dress Listings", "class" => "bg-pink text-dark"],
];
$adTypeToMapping = [
    "Job" => "job_listings",
    "Product" => "product_listings",
    "On Sale" => "on_sale_listings",
    "Service" => "service_listings",
    "Real Estate" => "real_estate_listings",
    "Car Sales" => "car_listings",
    "Event" => "events",
    "General Item" => "general_items",
    "Lost & Found" => "found_lost_listings",
    "Dress" => "dress_listings",
];
$tableHeaders = [
    'real_estate_listings' => [
        'Type', 'Price', 'Property Location', 'Status', 'Listing Type',
        'Sq Footage', 'Lot Size', 'Bedrooms', 'Bathrooms', 'Floor Level',
        'Furnished', 'Amenities', 'Condition'
    ],
    'events' => [
        'Name', 'Highlights', 'Purpose', 'Event Address', 'Time', 'Date'
    ],
  'product_listings' => [
        'Name', 'Price', 'Brand', 'Department', 'Category', 'Condition',
        'Availability', 'Ad Purpose', 'Special Notes', 'Unit of Measure',
        'Sale Price', 'Regular Price', 'Color', 'Size', 'Age'
    ],
  'on_sale_listings' => [
        'Name', 'Price', 'Brand', 'Department', 'Category', 'Condition',
        'Availability', 'Ad Purpose', 'Special Notes', 'Unit of Measure',
        'Sale Price', 'Regular Price', 'Color', 'Size', 'Age'
    ],
    'service_listings' => [
        'Title', 'Description', 'Service', 'Ad Purpose'
    ],
    'general_items' => [
        'Name', 'Description', 'Price', 'Category',  'Item Color',
        'Item Size', 'Item Age'
    ],
    'car_listings' => [
        'Year', 'Make', 'Model', 'Mileage', 'Condition', 'Price'
    ],
    'found_lost_listings' => [
        'Description', 'Datetime', 'Location'
    ],
    'job_listings' => [
        'Job Type', 'Employment Type', 'Schedule', 'Salary', 'Posting Type',
        'Application Method', 'Application Link', 'Experience', 'Skills',
        'Perks', 'Availability'
    ],
    'dress_listings' => [
        'Listing Type', 'Status', 'Price', 'Color', 'Size', 'Age', 'Occasion',
        'Condition', 'Fabric Type', 'Rental Policy', 'Designer Brand',
        'Suggested For'
    ]
];

if(isset($_GET['deleteThis'])){
    $id   =   cleanInputs($_GET['deleteThis']);
    $post_id = json_decode($id);
    // echo $post_id;die;
    $apiData = [
      
    ];

    $response = sendCurlRequest(BASE_URL.'/delete-post?post_id='.$post_id, 'DELETE', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        echo "<script>location.reload();'</script>";
    }
}
$title = "Citilytics Data";

if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}

include('pages/citilyticsData/list.html');