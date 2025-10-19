<?php
include_once('utils/helpers.php');
date_default_timezone_set('Asia/Kolkata');
$id =base64_decode($_GET['id']);

$apiData=[];
$query_data ='?post_id='.$id;
$response = sendCurlRequest(BASE_URL.'/get-single-post'.$query_data, 'GET', $apiData);
$decodedResponse = json_decode($response, true);

// Handle 401 Unauthorized
if (isset($decodedResponse['code']) && $decodedResponse['code'] == 401) {
    http_response_code(401);
    header($_SERVER['PHP_SELF']);
    exit;
}

//dump($decodedResponse);

## get All Companies
$company_data = sendCurlRequest(BASE_URL.'/all-admin-company', 'GET', []);
$companyDataResponse = json_decode($company_data, true);
$company = $companyDataResponse['body'];

$defaultType = 0;
$defaultCategory = 0;
$masterCatSub = [];
if(count($decodedResponse['body']['post_categories'])>0){
    // get master cat based on gpt selected
    $defaultType =  $decodedResponse['body']['post_categories'][0]['parentCategory']['type'] ?? 1;
    $defaultCategory =  $decodedResponse['body']['post_categories'][0]['category_id'];
}
$responseM = sendCurlRequest(BASE_URL.'/admin-master-cat-subs?type='.$defaultType, 'GET', []);
$decodedResponseM = json_decode($responseM, true);
$masterCatSub = $decodedResponseM['body'];

//  dump($category);
$final = ($decodedResponse['body']) ? $decodedResponse['body']:'';
//dump($masterCatSub);
$defaultType = 0;
$defaultCategory = isset($_GET['newCatId']) ? base64_decode($_GET['newCatId']) : 0;
$masterCatSub = [];
$defaultTabLabels = [];
if(isset($_GET['newCatId'])){
    $apiDataNW=[];
    $query_dataNW ='?id='.($defaultCategory);
    $responseNW = sendCurlRequest(BASE_URL.'/admin-get-single-master-cat-sub'.$query_dataNW, 'GET', $apiDataNW);
    $decodedResponseNW = json_decode($responseNW, true);
    $newPostData = $decodedResponseNW['body'];
    //dump($newPostData);
    // get master cat based on gpt selected
    $defaultType =  $newPostData['type'];
    $defaultTabLabels = array_filter(explode(',',$newPostData['tab_label_option_posts']));
    $final['service'] = $newPostData['keywords1'];
    $responseM = sendCurlRequest(BASE_URL.'/admin-master-cat-subs?type='.$defaultType, 'GET', []);
    $decodedResponseM = json_decode($responseM, true);
    $masterCatSub = $decodedResponseM['body'];
}

// update post loc
if (isset($_POST['updateLoc'])) {

    $location_id    = $_POST['location_id'];
    $address          = $_POST['address'];
    $state          = $_POST['state'];
    $city           = $_POST['city'];
    $country_code   = $_POST['country_code'];
    $latitude       = $_POST['latitude'];
    $longitude      = $_POST['longitude'];

    $apiData = [
        'loc_id'        => $location_id,
        'address'         => $address,
        'state'         => $state,
        'city'          => $city,
        'country_code'  => $country_code,
        'latitude'      => $latitude,
        'longitude'     => $longitude,
    ];

    // If you are using an API to update location
    $response = sendCurlRequest(BASE_URL . '/update-post-location', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);

    if ($decodedResponse['success']) {
        echo "<script>alert('Location updated successfully!'); window.location.href = 'deal-details.php?id=" . $_GET['id'] . "&redirect=locations';</script>";
    } else {
        echo "<script>alert('Failed to update post location!');</script>";
    }
}
// add post loc
if (isset($_POST['addPostLoc'])) {

    $address          = $_POST['address'];
    $state          = $_POST['state'];
    $city           = $_POST['city'];
    $country_code   = $_POST['country_code'];
    $latitude       = $_POST['latitude'];
    $longitude      = $_POST['longitude'];

    $apiData = [
        'post_id'        => $id,
        'address'         => $address,
        'state'         => $state,
        'city'          => $city,
        'country_code'  => $country_code,
        'latitude'      => $latitude,
        'longitude'     => $longitude,
    ];

    // If you are using an API to update location
    $response = sendCurlRequest(BASE_URL . '/update-post-location', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);

    if ($decodedResponse['success']) {
        echo "<script>alert('location added successfully!'); window.location.href = 'deal-details.php?id=" . $_GET['id'] . "&redirect=locations';</script>";
    } else {
        echo "<script>alert('Failed to update post location!');</script>";
    }
}

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Deals', 'url' => 'deals.php'],
    ['name' => $final['title'], 'url' => ''],
];

$title = $final['title']. ' - Deal';
include('pages/deals/details.html');