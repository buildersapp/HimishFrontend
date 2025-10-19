<?php
include_once('utils/helpers.php');

$id =base64_decode($_GET['id']);

$apiData=[];
$query_data ='?company_id='.$id.'&page=1&limit=1';
$response = sendCurlRequest(BASE_URL.'/get-company'.$query_data, 'GET', $apiData);
$decodedResponse = json_decode($response, true);

// Handle 401 Unauthorized
if (isset($decodedResponse['code']) && $decodedResponse['code'] == 401) {
    http_response_code(401);
    header($_SERVER['PHP_SELF']);
    exit;
}


$final = ($decodedResponse['body']) ? $decodedResponse['body'][0] :'';

$defaultType = 0;
$defaultCategory = 0;
$masterCatSub = [];
$tabLabel =[];
if (count($final['company_categories']) > 0) {
    foreach ($final['company_categories'] as $key => $category) {
        // Get the master category based on the selected type
        $defaultType = $category['parentCategory']['type'];
        $responseM = sendCurlRequest(BASE_URL . '/admin-master-cat-subs?type=' . $defaultType, 'GET', []);
        $decodedResponseM = json_decode($responseM, true);
        
        if (count($decodedResponseM['body']) > 0) {
            foreach ($decodedResponseM['body'] as $c) {
                if (!empty($c['tabLabelOptionCompany'])) {
                    $labels = explode(',', $c['tabLabelOptionCompany']);
                    foreach ($labels as $label) {
                        $label = trim($label);
                        if (!in_array($label, $tabLabel)) {
                            $tabLabel[] = $label;
                        }
                    }
                }
            }
        }

        // Add masterCatSub as a new key to the category array
        $final['company_categories'][$key]['masterCatSub'] = $decodedResponseM['body'] ?? [];
    }
}

// dump($final);

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Companies', 'url' => 'companies.php'],
    ['name' => $final['name'], 'url' => ''],
];

function displayCategoryHierarchyHTML($category) {
    $hierarchy = [];
    
    // Traverse hierarchy and collect category names
    while (!empty($category)) {
        $hierarchy[] = htmlspecialchars($category['name']);
        $category = $category['parentCategory'] ?? null;
    }
    
    // Reverse the order to show the root category first
    $hierarchy = array_reverse($hierarchy);
    
    // Join with ' > ' and return as a string
    return implode(' > ', $hierarchy);
}

if (isset($_POST['updateCat'])) {

    $post_category_master_c     = $_POST['post_category_master_c'];
    $request_id_c               = $_POST['request_id_c'];

    $apiData = [
        'id' => $request_id_c,
        'category_id' => $post_category_master_c,
        'company_id' => $id
    ];

    $response = sendCurlRequest(BASE_URL.'/update-company-category', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'company-details.php?id=".$_GET['id']."'</script>";
    }
}

if (isset($_POST['addCat'])) {

    $post_category_master_c     = $_POST['post_category_master'];
    $apiData = [
        'category_id' => $post_category_master_c,
        'company_id' => $id
    ];

    if(!$post_category_master_c){
        setcookie('errorMsg', 'Select Category', time() + 5, "/");
        echo "<script>window.location.href = 'company-details.php?id=".base64_encode($id)."'</script>";exit;
    }

    $response = sendCurlRequest(BASE_URL.'/add-company-category', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'company-details.php?id=".$_GET['id']."'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'company-details.php?id=".base64_encode($id)."'</script>";
    }
}

if (isset($_GET['del']) && isset($_GET['request_id'])) {
    $request_id_c               = $_GET['request_id'];
    //dump($request_id_c);
    $response = sendCurlRequest(BASE_URL.'/delete-company-category?id='.$request_id_c.'', 'DELETE', [], [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'company-details.php?id=".$_GET['id']."'</script>";
    }
}

if (isset($_POST['updateTabLabel'])) {

    $tab_label_option     = $_POST['tab_label_option'];

    $apiData = [
        'tab_label_option' => $tab_label_option,
        'company_id' => $id
    ];

    $response = sendCurlRequest(BASE_URL.'/update-company', 'PUT', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'company-details.php?id=".$_GET['id']."'</script>";
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
        'company_id'    => $id,
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
        echo "<script>alert('Company branch added successfully!'); window.location.href = 'company-details.php?id=" . $_GET['id'] . "&redirect=company_branches';</script>";
    } else {
        echo "<script>alert('Failed to update company branch!');</script>";
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
        echo "<script>alert('Company Branch updated successfully!'); window.location.href = 'company-details.php?id=" . $_GET['id'] . "&redirect=company_branches';</script>";
    } else {
        echo "<script>alert('Failed to update company branch!');</script>";
    }
}

$title = $final['name']. ' - Company';
include('pages/companies/details.html');