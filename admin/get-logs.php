<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Get Logs', 'url' => 'get-logs.php']
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
    $account_type        =   cleanInputs($_POST['account_type']);
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
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'sub-admin.php'</script>";
    }else{
        $err = 1;
    }
}

$title = "Logs";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}
include('pages/getLogs/list.html');