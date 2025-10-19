<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Users', 'url' => 'users.php']
];

$err = 0;
if(isset($_POST['addUser'])){
    $name           =   cleanInputs($_POST['name']);
    $email          =   cleanInputs($_POST['email']);
    $password       =   cleanInputs($_POST['password']);
    $phone          =   cleanInputs($_POST['phone']);
    $facebook       =   cleanInputs($_POST['facebook']);
    $instagram      =   cleanInputs($_POST['instagram']);
    $twitter        =   cleanInputs($_POST['twitter']);
    $apiDataU = [
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'phone' => $phone,
        'facebook' => $facebook,
        'instagram' => $instagram,
        'twitter' => $twitter,
    ];

    $response = sendCurlRequest(BASE_URL.'/signup', 'POST', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'users.php'</script>";
    }else{
        $err = 1;
    }
}

if(isset($_POST['addUpdateCommunity'])){
    $users_id       =   cleanInputs($_POST['users_id']);
    $community      =   cleanInputs(implode(',',$_POST['community']));
    $apiDataU = [
        'users_id' => $users_id,
        'community' => $community,
    ];

    $response = sendCurlRequest(BASE_URL.'/add-community-to-users', 'POST', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'users.php'</script>";
    }else{
        $err = 1;
    }
}

// communities

$apiData=[];
$response = sendCurlRequest(BASE_URL.'/get-community', 'GET', $apiData);
$decodedResponse = json_decode($response, true);
$communities = [];
if(count($decodedResponse)){
    $communities = $decodedResponse['body'];
}


//dump($communities);

$title = "Users";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}

include('pages/users/list.html');