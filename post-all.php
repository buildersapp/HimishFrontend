<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$current_page = basename($_SERVER['PHP_SELF']);

$myCommunityLocations = [];
$uniqueKeysMy = [];
$allCommunityLocations = [];
$uniqueKeysAll = [];
$apiData = [];
$from = isset($_GET['from']) ? 1 : 0; 
$rd_srep = isset($_GET['rd_srep']) ? 1 : 0; 

include_once('includes/check-session.php');

if(isset($_POST['post_data']) && !empty($_POST['listing_data'])) {

    if(!empty($_POST['post_data'])){
        $postData = $_POST['post_data'];
        $postWithJson = ['json_data' => $postData, 'is_from' => 1];
        $responsePJ = sendCurlRequest(BASE_URL.'/create-post-with-json', 'POST', $postWithJson, [], true);
    }

    if(!empty($_POST['listing_data'])){
        $listingData = $_POST['listing_data'];
        $postML = ['json_data' => $listingData, 'is_from' => 1];
        $responseML = sendCurlRequest(BASE_URL.'/multi-listing-with-json', 'POST', $postML, [], true);
    }

    echo "<script>window.location.href = 'home.php'</script>";
}

$title = "Post All";
include('pages/posts/post-all.html');
?>
