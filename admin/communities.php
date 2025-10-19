<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Communities', 'url' => 'communities.php']
];

## get All Comapnies
$user_data = sendCurlRequest(BASE_URL.'/all-users', 'GET', []);
$userDataResponse = json_decode($user_data, true);
$users = $userDataResponse['body'];

$profileGroups =[];
$responsePG= sendCurlRequest(BASE_URL.'/profile-group-list', 'GET', []);
$decodedResponsePG = json_decode($responsePG, true);
if($decodedResponsePG['success']){
    $profileGroups = $decodedResponsePG['body'];
}

if(isset($_POST['addCommunity'])){
    $description  =   cleanInputs($_POST['description']);
    $is_private   =   cleanInputs($_POST['is_private']);
    $gender   =   cleanInputs($_POST['gender']);
    $name         =   cleanInputs($_POST['name']);
    $price         =   cleanInputs($_POST['price']);
    $apiData = [
        'description' => $description,
        'is_private' => $is_private,
        'gender' => $gender,
        'name' => $name,
        'price' => $price
    ];

    if(isset($_POST['address'])){
        $apiData['address']    =   cleanInputs($_POST['address']);
        $apiData['city']       =   cleanInputs($_POST['city']);
        $apiData['state']      =   cleanInputs($_POST['state']);
        $apiData['latitude']   =   cleanInputs($_POST['latitude']);
        $apiData['longitude']  =   cleanInputs($_POST['longitude']);
    }

    // Check if a file was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $imageFilePath = $_FILES['image']['tmp_name'];
        $apiData['image'] = new CURLFile($imageFilePath, $_FILES['image']['type'], $_FILES['image']['name']);
    }

    $response = sendCurlRequest(BASE_URL.'/admin-add-community', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'communities.php'</script>";
    }
}

## invite users
if(isset($_POST['inviteUsersButton'])){
    $communityId  =   cleanInputs($_POST['communityId']);
    $users   =   cleanInputs($_POST['users']);
    $apiData = [
        'users' => $users,
        'communityId' => $communityId,
    ];

    // dump($apiData);

    $response = sendCurlRequest(BASE_URL.'/invite-community', 'POST', $apiData, [], false);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'communities.php'</script>";
    }
}


$title = "Communities";
include('pages/communities/list.html');