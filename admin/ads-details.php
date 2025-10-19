<?php
include_once('utils/helpers.php');

$id =base64_decode($_GET['id']);

// Test Ad Expiry
if (isset($_POST['testAd'])) {

    $expiry_time    = cleanInputs($_POST['expiry_time']);

    $apiData = [
        'ad_id'     => $id,
        'expiry_time'        => $expiry_time,
    ];

    // If you are using an API to update location
    $response = sendCurlRequest(BASE_URL . '/send-ad-expire-reminder', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);

    if ($decodedResponse['success']) {
        echo "<script>alert('Test Notification Sent!'); window.location.href = 'ads-details.php?id=" . $_GET['id'] . "';</script>";
    } else {
        echo "<script>alert('Failed to send notification!');</script>";
    }
}

$apiData=[];
$query_data ='?ads_id='.$id.'&admin=all';
$response = sendCurlRequest(BASE_URL.'/get-ads'.$query_data, 'GET', $apiData);
$decodedResponse = json_decode($response, true);
// dump($decodedResponse);

if (isset($decodedResponse['code']) && $decodedResponse['code'] == 401) {
    http_response_code(401);
    header($_SERVER['PHP_SELF']);
    exit;
}
//   dump($decodedResponse['body']);
$final = ($decodedResponse['body']) ? $decodedResponse['body'][0]: [];

if (count($final) == 0) {
    setcookie('errorMsg', 'Ad not Found', time() + 2, "/");
    echo "<script>window.location.href='ads.php'</script>";
}

## get All Community
$community_data = sendCurlRequest(BASE_URL.'/get-community', 'GET', []);
$communityDataResponse = json_decode($community_data, true);
$community = $communityDataResponse['body'];

## get All Comapnies
$user_data = sendCurlRequest(BASE_URL.'/all-users', 'GET', []);
$userDataResponse = json_decode($user_data, true);
$users = $userDataResponse['body'];

## get All Companies
$company_data = sendCurlRequest(BASE_URL.'/all-admin-company', 'GET', []);
$companyDataResponse = json_decode($company_data, true);
$company = $companyDataResponse['body'];
usort($company, function ($a, $b) {
    return strcmp($a['name'], $b['name']);
});
//  dump($final);
$community_array = explode(',', $final['community']);

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Ads', 'url' => 'ads.php'],
    ['name' => $final['title'], 'url' => ''],
];

$title = $final['title']. ' - Ad';


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
    $response = sendCurlRequest(BASE_URL . '/updateADLocation', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);

    if ($decodedResponse['success']) {
        echo "<script>alert('Ad Location updated successfully!'); window.location.href = 'ads-details.php?id=" . $_GET['id'] . "&redirect=locations';</script>";
    } else {
        echo "<script>alert('Failed to update post location!');</script>";
    }
}
// add post loc
if (isset($_POST['addADLoc'])) {

    $address          = $_POST['address'];
    $state          = $_POST['state'];
    $city           = $_POST['city'];
    $country_code   = $_POST['country_code'];
    $latitude       = $_POST['latitude'];
    $longitude      = $_POST['longitude'];

    $apiData = [
        'ad_id'        => $id,
        'address'         => $address,
        'state'         => $state,
        'city'          => $city,
        'country_code'  => $country_code,
        'latitude'      => $latitude,
        'longitude'     => $longitude,
    ];

    // If you are using an API to update location
    $response = sendCurlRequest(BASE_URL . '/updateADLocation', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);

    if ($decodedResponse['success']) {
        echo "<script>alert('AD location added successfully!'); window.location.href = 'ads-details.php?id=" . $_GET['id'] . "&redirect=locations';</script>";
    } else {
        echo "<script>alert('Failed to update post location!');</script>";
    }
}

if (isset($_POST['updateBranch'])) {

    $location_id    = $_POST['branch_id'];
    $address        = $_POST['address'];
    $state          = $_POST['state'];
    $city           = $_POST['city'];
    $country_code   = $_POST['country_code'];
    $latitude       = $_POST['latitude'];
    $longitude      = $_POST['longitude'];
    $type           = $_POST['type'];

    $apiData = [
        'loc_id'        => $location_id,
        'address'       => $address,
        'state'         => $state,
        'city'          => $city,
        'country_code'  => $country_code,
        'latitude'      => $latitude,
        'longitude'     => $longitude,
        'type'          => 0
    ];

    // If you are using an API to update location
    $response = sendCurlRequest(BASE_URL . '/updateCompanyLocation', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);

    if ($decodedResponse['success']) {
        echo "<script>alert('Company Branch updated successfully!'); window.location.href = 'ads-details.php?id=" . $_GET['id'] . "&redirect=locations';</script>";
    } else {
        echo "<script>alert('Failed to update company branch!');</script>";
    }
}
// add branch
if (isset($_POST['addBranch'])) {

    $address        = $_POST['address'];
    $state          = $_POST['state'];
    $city           = $_POST['city'];
    $country_code   = $_POST['country_code'];
    $latitude       = $_POST['latitude'];
    $longitude      = $_POST['longitude'];
    $type           = $_POST['type'];

    $apiData = [
        'company_id'    => $final['company_id'],
        'address'       => $address,
        'state'         => $state,
        'city'          => $city,
        'country_code'  => $country_code,
        'latitude'      => $latitude,
        'longitude'     => $longitude,
        'type'          => 0
    ];

    // If you are using an API to update location
    $response = sendCurlRequest(BASE_URL . '/updateCompanyLocation', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);

    if ($decodedResponse['success']) {
        echo "<script>alert('Company branch added successfully!'); window.location.href = 'ads-details.php?id=" . $_GET['id'] . "&redirect=locations';</script>";
    } else {
        echo "<script>alert('Failed to update company branch!');</script>";
    }
}
include('pages/ads/details.html');