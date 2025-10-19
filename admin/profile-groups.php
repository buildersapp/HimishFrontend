<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Profile Groups', 'url' => 'profile-groups.php']
];

if(isset($_GET['id'])){
    $id = base64_decode($_GET['id']);
    $query_data ='?id='.$id.'&page=1&limit=1';
    $responseIUser = sendCurlRequest(BASE_URL.'/profile-group-list'.$query_data, 'GET', []);
    $decodedResponseIUser = json_decode($responseIUser, true);
    $apiDataU = $decodedResponseIUser['body'][0];
}

$err = 0;
if(isset($_POST['addProfileGroups'])){
    $name           =   cleanInputs($_POST['name']);
    $apiDataU = [
        'name' => $name
    ];

    $response = sendCurlRequest(BASE_URL.'/profile-group-add', 'POST', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'profile-groups.php'</script>";
    }else{
        $err = 1;
    }
}

if(isset($_POST['updateProfileGroups'])){
    $name           =   cleanInputs($_POST['name']);
    $apiDataU = [
        'name' => $name,
        'id' => $id
    ];

    $response = sendCurlRequest(BASE_URL.'/profile-group-edit', 'POST', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'profile-groups.php'</script>";
    }else{
        $err = 1;
    }
}

$title = "Profile Groups";

if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}

include('pages/profileGroups/list.html');