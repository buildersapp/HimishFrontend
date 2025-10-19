<?php
include_once('includes/custom-functions.php');
include_once('admin/utils/helpers.php');
$current_page = basename($_SERVER['PHP_SELF']);
$type = isset($_GET['type']) ? (int) $_GET['type'] : 0;
$companyId = isset($_GET['id']) ? (int) base64_decode($_GET['id']) : 0;
$product_service_id = isset($_GET['product_service_id']) ? (int) $_GET['product_service_id'] : 0;
$radius = $userDetails['radius'] ?? 200;
$search = isset($_GET['search']) ? $_GET['search'] : '';

$apiData=[];
$query_data ='?show_in_filter=1';
$response = sendCurlRequest(BASE_URL.'/get-services-products'.$query_data, 'GET', $apiData);
$decodedResponse = json_decode($response, true);
$filters = [];
if(count($decodedResponse)){
    $filters = $decodedResponse['meta']['forFilters'];
}
//dump($filters);

// Title and page rendering (not changed)
$title = "Companies";
include('pages/companies/index.html');
?>
