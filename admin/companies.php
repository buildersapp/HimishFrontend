<?php
include_once('utils/helpers.php');
$err = 0;
// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Companies', 'url' => 'companies.php']
];

## get All Comapnies
$user_data = sendCurlRequest(BASE_URL.'/all-users', 'GET', []);
$userDataResponse = json_decode($user_data, true);
$users = $userDataResponse['body'];

## get Category
$category_data = sendCurlRequest(BASE_URL.'/get-all-servies', 'GET', []);
$categoryDataResponse = json_decode($category_data, true);
$category = $categoryDataResponse['body'];

## Add New Company
if(isset($_POST['addCompany'])){
    $name           =   cleanInputs($_POST['name']);
    $short_name           =   cleanInputs($_POST['short_name']);
    $email          =   cleanInputs($_POST['email']);
    $user_id       =   cleanInputs($_POST['user_id']);
    $business_type          =   cleanInputs($_POST['business_type']);
    $location       =   cleanInputs($_POST['location']);
    $latitude      =   cleanInputs($_POST['latitude']);
    $longitude        =   cleanInputs($_POST['longitude']);
    $role        =   cleanInputs($_POST['role']);
    $info        =   cleanInputs($_POST['info']);
    $keywords        =   cleanInputs($_POST['keywords']);
    $apiDataU = [
        'name' => $name,
        'short_name' => $short_name,
        'email' => $email,
        'user_id' => $user_id,
        'business_type' => $business_type,
        'location' => $location,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'role' => $role,
        'info' => $info,
        'keywords' => $keywords,
    ];

    $response = sendCurlRequest(BASE_URL.'/create-company-admin', 'POST', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    // dump( $decodedResponse);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'companies.php'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'companies.php'</script>";

    }
}

if(isset($_POST['categorize'])){
    $post_category_master  =   cleanInputs($_POST['post_category_master']);
    $keywords2          =   cleanInputs($_POST['keywords2']);
    $company_id       =   cleanInputs($_POST['company_id']);
    $apiDataU = [
        'id' => $post_category_master,
        'keywords2' => $keywords2,
        'company_id' => $company_id,
    ];
    //dump($apiDataU);
    $response = sendCurlRequest(BASE_URL.'/update-keywords', 'POST', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    // dump( $decodedResponse);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'companies.php'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'companies.php'</script>";

    }
};

if(isset($_POST['masterClick'])){
    $selectIds = cleanInputs($_POST['selectIds']);
    $master_id = cleanInputs($_POST['master_id']);
     // Explode `selectIds` into an array
    $selectIdsArray = explode(',', $selectIds);
    if(empty($master_id)){
        setcookie('errorMsg', 'Please select master company', time() + 5, "/");

        // echo "<script>window.location.href = 'companies.php'</script>";
        exit;

    }
   
    // Remove `master_id`, `0`, empty values, and `undefined` from the array
    $filteredIds = array_filter($selectIdsArray, function ($id) use ($master_id) {
        return trim($id) !== '' && $id !== '0' && $id !== 'undefined' && $id != $master_id;
    });
    $selectIds = implode(',', $filteredIds);

    $apiDataU = [
        'master_id' => $master_id,
        'other_id' => $selectIds,
    ];
    //  dump($apiDataU);
    
    $response = sendCurlRequest(BASE_URL.'/merge-company', 'POST', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'companies.php'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'companies.php'</script>";

    }


}

$title = "Companies";

if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}

include('pages/companies/list.html');