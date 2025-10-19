<?php
include_once('utils/helpers.php');

$id =base64_decode($_GET['id']);

$apiData=[];
$query_data ='?post_id='.$id;
$response = sendCurlRequest(BASE_URL.'/get-single-post'.$query_data, 'GET', $apiData);
$decodedResponse = json_decode($response, true);

$profileGroups =[];
$responsePG= sendCurlRequest(BASE_URL.'/profile-group-list', 'GET', []);
$decodedResponsePG = json_decode($responsePG, true);
if($decodedResponsePG['success']){
    $profileGroups = $decodedResponsePG['body'];
}

$query_data ='?post_id='.$id;
$responseRP = sendCurlRequest(BASE_URL.'/get-post-reports'.$query_data, 'GET', $apiData);
$decodedResponseRP = json_decode($responseRP, true);

// Handle 401 Unauthorized
if (isset($decodedResponse['code']) && $decodedResponse['code'] == 401) {
    http_response_code(401);
    header($_SERVER['PHP_SELF']);
    exit;
}

$defaultType = 0;
$defaultCategory = isset($_GET['newCatId']) ? base64_decode($_GET['newCatId']) : 0;
$masterCatSub = [];
$defaultTabLabels = [];
if(count($decodedResponse['body']['post_categories'])>0){
    // get master cat based on gpt selected
    $defaultType =  end($decodedResponse['body']['post_categories'])['parentCategory']['type'];
    if(!isset($_GET['newCatId'])){
        //$defaultCategory =  $decodedResponse['body']['post_categories'][0]['category_id'];
        $defaultCategory = end($decodedResponse['body']['post_categories'])['category_id'];
    }
    $defaultTabLabels = array_filter(explode(',',$decodedResponse['body']['post_categories'][0]['parentCategory']['tab_label_option_posts']));
    $responseM = sendCurlRequest(BASE_URL.'/admin-master-cat-subs?type='.$defaultType, 'GET', []);
    $decodedResponseM = json_decode($responseM, true);
    $masterCatSub = $decodedResponseM['body'];
}

//dump($masterCatSub);

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
## get All Community
$community_data = sendCurlRequest(BASE_URL.'/get-community', 'GET', []);
$communityDataResponse = json_decode($community_data, true);
$community = $communityDataResponse['body'];
// dump($community);
## get Category
$category_data = sendCurlRequest(BASE_URL.'/get-all-servies', 'GET', []);
$categoryDataResponse = json_decode($category_data, true);
$category = $categoryDataResponse['body'];

//  dump($category);
$final = ($decodedResponse['body']) ? $decodedResponse['body']:'';
$community_array = explode(',', $final['community']);

if(!empty($final['company_id']) && $final['company_id'] > 0){
    $postCompany = sendCurlRequest(BASE_URL.'/get-company', 'GET', ['company_id' => $final['company_id']]);
    $postCompanyDataResponse = json_decode($postCompany, true);
    $final['company_data'] = $postCompanyDataResponse['body'][0];
}

//dump($final['company_data']['company_branches']);


if (count($final['post_categories']) > 0) {
    foreach ($final['post_categories'] as $key => $category) {
        // Get the master category based on the selected type
        $defaultType = $category['parentCategory']['type'];
        $responseM = sendCurlRequest(BASE_URL . '/admin-master-cat-subs?type=' . $defaultType, 'GET', []);
        $decodedResponseM = json_decode($responseM, true);

        // Add masterCatSub as a new key to the category array
        $final['post_categories'][$key]['masterCatSub'] = $decodedResponseM['body'] ?? [];
    }
}

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
    $decodedResponse['body']['service'] = $newPostData['keywords1'];
    $responseM = sendCurlRequest(BASE_URL.'/admin-master-cat-subs?type='.$defaultType, 'GET', []);
    $decodedResponseM = json_decode($responseM, true);
    $masterCatSub = $decodedResponseM['body'];
}

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Posts', 'url' => 'posts.php'],
    ['name' => $final['title'], 'url' => ''],
];

if (isset($_POST['updateCat'])) {

    $post_category_master_c     = $_POST['post_category_master_c'];
    $request_id_c               = $_POST['request_id_c'];

    $apiData = [
        'id' => $request_id_c,
        'category_id' => $post_category_master_c,
        'post_id' => $id
    ];

    $response = sendCurlRequest(BASE_URL.'/update-post-category', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'post-details.php?id=".$_GET['id']."&redirect=categories'</script>";
    }
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
        echo "<script>alert('Post Location updated successfully!'); window.location.href = 'post-details.php?id=" . $_GET['id'] . "&redirect=locations';</script>";
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
        echo "<script>alert('Post location added successfully!'); window.location.href = 'post-details.php?id=" . $_GET['id'] . "&redirect=locations';</script>";
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
        echo "<script>alert('Company Branch updated successfully!'); window.location.href = 'post-details.php?id=" . $_GET['id'] . "&redirect=locations';</script>";
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
        echo "<script>alert('Company branch added successfully!'); window.location.href = 'post-details.php?id=" . $_GET['id'] . "&redirect=locations';</script>";
    } else {
        echo "<script>alert('Failed to update company branch!');</script>";
    }
}

if (isset($_POST['updateServiceLoc'])) {

    $location_id    = $_POST['service_id'];
    $address        = $_POST['address'];
    $state          = $_POST['state'];
    $city           = $_POST['city'];
    $country_code   = $_POST['country_code'];
    $latitude       = $_POST['latitude'];
    $longitude      = $_POST['longitude'];
    $miles          = $_POST['miles'];
    $type           = $_POST['type'];

    $apiData = [
        'loc_id'        => $location_id,
        'address'       => $address,
        'state'         => $state,
        'city'          => $city,
        'country_code'  => $country_code,
        'latitude'      => $latitude,
        'longitude'     => $longitude,
        'miles'         => $miles,
        'type'          => 1
    ];

    // If you are using an API to update location
    $response = sendCurlRequest(BASE_URL . '/updateCompanyLocation', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);

    if ($decodedResponse['success']) {
        echo "<script>alert('Company Service Area updated successfully!'); window.location.href = 'post-details.php?id=" . $_GET['id'] . "&redirect=locations';</script>";
    } else {
        echo "<script>alert('Failed to update company service area!');</script>";
    }
}

if (isset($_POST['addCat'])) {

    $post_category_master_c     = $_POST['post_category_master'];
    $apiData = [
        'category_id' => $post_category_master_c,
        'post_id' => $id
    ];

    if(!$post_category_master_c){
        setcookie('errorMsg', 'Select Category', time() + 5, "/");
        echo "<script>window.location.href = 'post-details.php?id=".base64_encode($id)."&redirect=categories'</script>";exit;
    }

    $response = sendCurlRequest(BASE_URL.'/add-post-category', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'post-details.php?id=".$_GET['id']."&redirect=categories'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'post-details.php?id=".base64_encode($id)."&redirect=categories'</script>";
    }
}

if (isset($_GET['del']) && isset($_GET['request_id'])) {
    $request_id_c               = $_GET['request_id'];
    //dump($request_id_c);
    $response = sendCurlRequest(BASE_URL.'/delete-post-category?id='.$request_id_c.'', 'DELETE', [], [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'post-details.php?id=".$_GET['id']."&redirect=categories'</script>";
    }
}

if (isset($_POST['cateJsonButton'])) {

    $post_category_master_c     = $_POST['catJson'];
    $apiData = [
        'categoryArray' => $post_category_master_c,
        'post_id' => $id
    ];

    // dump($apiData);


    $response = sendCurlRequest(BASE_URL.'/add-post-category-json', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    // dump($decodedResponse);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'post-details.php?id=".$_GET['id']."&redirect=categories'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'post-details.php?id=".base64_encode($id)."&redirect=categories'</script>";
    }
}


// dump($company);
$title = $final['title']. ' - Post';
include('pages/posts/details.html');