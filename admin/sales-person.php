<?php
include_once('utils/helpers.php');
date_default_timezone_set('UTC');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Sales Person', 'url' => 'sales-person.php']
];
## get All Community
$community_data = sendCurlRequest(BASE_URL.'/get-community', 'GET', []);
$communityDataResponse = json_decode($community_data, true);
$communities = $communityDataResponse['body'];


## get All sales person
$salesData = sendCurlRequest(BASE_URL.'/all-users?account_type=3', 'GET', []);
$salesDataResponse = json_decode($salesData, true);
$salesPerson = $salesDataResponse['body'];


$apiData = ['type' => 0,  'page' => 1, 'limit' => 100];

$response = sendCurlRequest(BASE_URL.'/get-all-posts', 'GET', $apiData);
$PostResponse = json_decode($response, true);
$postData = $PostResponse['body'];

// dump($postData);

## get setting
$getSetting = sendCurlRequest(BASE_URL.'/get-setting-sales-person-admin', 'GET', []);
$getSettingResponse = json_decode($getSetting, true);
$final = $getSettingResponse['body'];
//  dump($final);

$err = 0;
if(isset($_POST['addUser'])){
    $name           =   cleanInputs($_POST['name']);
    $email          =   cleanInputs($_POST['email']);
    $password       =   cleanInputs($_POST['password']);
    $phone          =   cleanInputs($_POST['phone']);
    $status         =   cleanInputs($_POST['status']);
    $account_type        =   cleanInputs($_POST['account_type']);
    $apiDataU = [
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'phone' => $phone,
        'status' => $status,
        'account_type' => $account_type,
        
    ];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $apiDataU['image'] = new CURLFile($_FILES['image']['tmp_name'], $_FILES['image']['type'], $_FILES['image']['name']);
    }

    if (isset($_POST['community'])) {
        $apiDataU['community'] = implode(',', $_POST['community']);
        // Now $community contains the trimmed value
    }

    //   dump($apiDataU);

    $response = sendCurlRequest(BASE_URL.'/signup', 'POST', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'sales-person.php'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        $err = 1;
    }
}

if(isset($_POST['editUser'])){
    $name           =   cleanInputs($_POST['name']);
    $email          =   cleanInputs($_POST['email']);
    $id       =   cleanInputs($_POST['id']);
    $phone          =   cleanInputs($_POST['phone']);
    $status         =   cleanInputs($_POST['status']);
    $apiDataU = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'status' => $status,
        
    ];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $apiDataU['image'] = new CURLFile($_FILES['image']['tmp_name'], $_FILES['image']['type'], $_FILES['image']['name']);
    }

    if (isset($_POST['community'])) {
        $apiDataU['community'] = implode(',', $_POST['community']);
        // Now $community contains the trimmed value
    }

    // dump($apiDataU);

    $response = sendCurlRequest(BASE_URL.'/edit-profile-admin?user_id='.$id, 'PUT', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'sales-person.php'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        $err = 1;
    }
}
if(isset($_POST['updateCommission'])){
    // dump($_POST);
    $response = sendCurlRequest(BASE_URL.'/update-setting-sales-person-admin', 'PUT', $_POST, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'sales-person.php?redirect=commission-tab'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        $err = 1;
    }
}
## add  new community
if(isset($_POST['addSalesCommunity'])){
    // Check if a file was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $imageFilePath = $_FILES['image']['tmp_name'];
        $_POST['image'] = new CURLFile($imageFilePath, $_FILES['image']['type'], $_FILES['image']['name']);
    }

    $response = sendCurlRequest(BASE_URL.'/admin-add-community', 'POST', $_POST, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'sales-person.php?redirect=communities-tab'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        $err = 1;
    }
}

if(isset($_POST['editSaleCommunity'])){
     // Check if a file was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $imageFilePath = $_FILES['image']['tmp_name'];
        $_POST['image'] = new CURLFile($imageFilePath, $_FILES['image']['type'], $_FILES['image']['name']);
    }

    // dump($_POST);

    $response = sendCurlRequest(BASE_URL.'/admin-edit-community?editId='.$_POST['id'], 'PUT', $_POST, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'sales-person.php?redirect=communities-tab'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        $err = 1;
    }
}


## add link
if(isset($_POST['addLink'])){
    // dump($_POST);
    // die;
    $response = sendCurlRequest(BASE_URL.'/create-sale-person-link', 'POST', $_POST, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'sales-person.php?redirect=referral-tab'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        $err = 3;
    }
}
## add link
if(isset($_POST['__editLink'])){
    // dump($_POST);
    // die;
    $response = sendCurlRequest(BASE_URL.'/update-link?id='.$_POST['id'], 'PUT', $_POST, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'sales-person.php?redirect=referral-tab'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        $err = 4;
    }
}

$title = "Sales Person";

if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}

include('pages/salesPerson/list.html');