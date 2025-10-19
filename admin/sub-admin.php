<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Sub Admins', 'url' => 'sub-admin.php']
];

if(isset($_GET['id'])){
    $id = base64_decode($_GET['id']);
    $query_data ='?user_id='.$id.'&page=1&limit=1';
    $responseIUser = sendCurlRequest(BASE_URL.'/get-profile'.$query_data, 'GET', []);
    $decodedResponseIUser = json_decode($responseIUser, true);
    $apiDataU = $decodedResponseIUser['body'];
}

$err = 0;
if(isset($_POST['addUser'])){
    $name           =   cleanInputs($_POST['name']);
    $email          =   cleanInputs($_POST['email']);
    $password       =   cleanInputs($_POST['password']);
    $phone          =   cleanInputs($_POST['phone']);
    $facebook       =   cleanInputs($_POST['facebook'] ?? '');
    $instagram      =   cleanInputs($_POST['instagram'] ?? '');
    $twitter        =   cleanInputs($_POST['twitter'] ?? '');
    $account_type        =   cleanInputs($_POST['account_type']);
    $permissions = $_POST['permissions_json'];
    $apiDataU = [
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'phone' => $phone,
        'facebook' => $facebook,
        'instagram' => $instagram,
        'twitter' => $twitter,
        'account_type' => $account_type
    ];

    $response = sendCurlRequest(BASE_URL.'/signup', 'POST', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){

        $userId = $decodedResponse['body']['id'];
        $apiDataUP = ['user_id' => $userId, 'permissions' => json_encode([$permissions])];

        //dump($apiDataUP);
        $responsePermission = sendCurlRequest(BASE_URL.'/permission-add', 'POST', $apiDataUP, [], true);
        $decodedResponsePermission = json_decode($responsePermission, true);
        if($decodedResponsePermission['success']){
            setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
            echo "<script>window.location.href = 'sub-admin.php'</script>";
        }else{
            setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
            $err = 1;
        }
    }else{
        $err = 1;
    }
}

if(isset($_POST['updateUser'])){
    $name           =   cleanInputs($_POST['name']);
    $email          =   cleanInputs($_POST['email']);
    $phone          =   cleanInputs($_POST['phone']);
    $has_permissions  =   cleanInputs($_POST['has_permissions']);
    $permissions = $_POST['permissions_json'];
    $apiDataU = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
    ];

    $response = sendCurlRequest(BASE_URL.'/edit-profile-admin?user_id='.$id, 'PUT', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){

        $userId = $decodedResponse['body']['id'];
        $apiDataUP = ['user_id' => $userId, 'permissions' => json_encode([$permissions])];

        if($has_permissions){
            $responsePermission = sendCurlRequest(BASE_URL.'/permission-update', 'PUT', $apiDataUP, [], true);
        }else{
            $responsePermission = sendCurlRequest(BASE_URL.'/permission-add', 'POST', $apiDataUP, [], true);
        }
        $decodedResponsePermission = json_decode($responsePermission, true);
        if($decodedResponsePermission['success']){
            setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
            echo "<script>window.location.href = 'sub-admin.php'</script>";
        }else{
            setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
            $err = 1;
        }
    }else{
        $err = 1;
    }
}

$title = "Sub Admins";

if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}

include('pages/subAdmin/list.html');