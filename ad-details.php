<?php
include_once('includes/custom-functions.php');
include_once('admin/utils/helpers.php');
$isGuestMode = isset($_SESSION['hm_wb_auth_data']['id']) ? 0 : 1;
$current_page = basename($_SERVER['PHP_SELF']);
$adId = isset($_GET['id']) ? (int) base64_decode($_GET['id']) : 0; // Default to 0 if 'type' is not set
$radius = $userDetails['radius'] ?? 200;
$type = 0;
// Initialize API data with required fields
$apiData = [
    'ads_id' => $adId
];

if($adId){
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/get-ads', 'GET', $apiData);
    $responseDecoded = json_decode($response,true);

    // Decode the response (assuming it's JSON)
    $posts = $responseDecoded['body'];

    $title = $posts[0]['title'] ?? 'Ad Detail';

    if($posts){

        // Define classes based on type
        $dashboardClass = ($type == 0) ? 'dashboard-pd' : 
        (($type == 1) ? 'dashboard-lt' : 
        (($type == 2) ? 'dashboard-de' : 
        (($type == 3) ? 'dashboard-ds' : 'dashboard-default')));

        $containerClass = ($type == 0) ? 'container-pd' : 
        (($type == 1) ? 'container-lt' : 
        (($type == 2) ? 'container-de' : 
        (($type == 3) ? 'container-ds' : 'container-default')));
    }
}



include('pages/ad-detail.html');
?>
