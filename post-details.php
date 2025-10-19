<?php
include_once('includes/custom-functions.php');
include_once('admin/utils/helpers.php');
$current_page = basename($_SERVER['PHP_SELF']);
$postId = isset($_GET['id']) ? (int) base64_decode($_GET['id']) : 0;
$type = isset($_GET['type']) ? (int) $_GET['type'] : 0; 
$radius = $userDetails['radius'] ?? 200;
// Initialize API data with required fields
$apiData = [
    'post_id' => $postId
];

if($postId){
    
    // Make the API request and get the response
    if($type == 1){
        $response = sendCurlRequest(BASE_URL.'/get-new-listing', 'GET', $apiData);
    }else{
        $response = sendCurlRequest(BASE_URL.'/get-posts', 'GET', $apiData);
    }
    $responseDecoded = json_decode($response,true);
    //dump($responseDecoded);

    // Decode the response (assuming it's JSON)
    $posts = $responseDecoded['body'];

    $title = $posts[0]['title'] ?? 'Post Detail';

    if($posts){

        $responseSettings = sendCurlRequest(BASE_URL . '/get-setting', 'GET', []);
        $settings = json_decode($responseSettings, true)['body'] ?? [];
        $show_listing_images = (int)($settings['show_listing_images'] ?? 0);

        $type = $posts[0]['type'];
        if($type == 2 && empty($post[0]['info'])){
            $type = 3;
        }
        
        // Define classes based on type
        $dashboardClass = ($type == 0) ? 'dashboard-pd' : 
        (($type == 1) ? 'set-container-1000' : 
        (($type == 2) ? 'dashboard-de' : 
        (($type == 3) ? 'dashboard-ds' : 'dashboard-default set-custom-post-ad-style')));

        $containerClass = ($type == 0) ? 'container-pd' : 
        (($type == 1) ? 'set-main-sec container mb-5' : 
        (($type == 2) ? 'container-de' : 
        (($type == 3) ? 'container-ds' : 'container-default')));
    }
}



include('pages/posts/detail.html');
?>
