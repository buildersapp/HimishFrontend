<?php
include_once('utils/helpers.php');

$id =base64_decode($_GET['id']);

$apiData=[];
$community =[];
$query_data ='?user_id='.$id.'&page=1&limit=1';
$response = sendCurlRequest(BASE_URL.'/get-profile'.$query_data, 'GET', $apiData);
$decodedResponse = json_decode($response, true);

// Handle 401 Unauthorized
if (isset($decodedResponse['code']) && $decodedResponse['code'] == 401) {
    http_response_code(401);
    header($_SERVER['PHP_SELF']);
    exit;
}

$response1 = sendCurlRequest(BASE_URL.'/get-community?user_id='.$id, 'GET', $apiData);
$decodedResponse1 = json_decode($response1, true);
$community = ($decodedResponse1['body']) ? $decodedResponse1['body'] :[];

//dump($decodedResponse['body']);
$final = ($decodedResponse['body']) ? $decodedResponse['body'] :'';
$community_array = ($final['community']) ? explode(',', $final['community']) :[];

// /apis/get-community?user_id=123
//  dump($community);
// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Users', 'url' => 'users.php'],
    ['name' => $final['name'], 'url' => ''],
];

$profileGroups =[];
$responsePG= sendCurlRequest(BASE_URL.'/profile-group-list', 'GET', []);
$decodedResponsePG = json_decode($responsePG, true);
if($decodedResponsePG['success']){
    $profileGroups = $decodedResponsePG['body'];
}

// updateUser
if(isset($_POST['updateUser'])){
    $gender      =   cleanInputs($_POST['profile_group']);
    $email      =   cleanInputs($_POST['email']);
    $name       =   cleanInputs($_POST['name']);
    $apiData = [
        'email' => $email,
        'gender' => $gender,
        'name' => $name
    ];

    // Check if a file was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $imageFilePath = $_FILES['image']['tmp_name'];
        $apiData['image'] = new CURLFile($imageFilePath, $_FILES['image']['type'], $_FILES['image']['name']);
    }

    $response = sendCurlRequest(BASE_URL.'/edit-profile-admin?user_id='.$id, 'PUT', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'users.php'</script>";
    }
}

// addCredits
if(isset($_POST['addCredits'])){
    $credit_debit      =   cleanInputs($_POST['credit_debit']);
    $amount            =   cleanInputs($_POST['credits']);
    $apiData = [
        'type' => $credit_debit,
        'amount' => $amount,
        'user_id' => $id
    ];

    $response = sendCurlRequest(BASE_URL.'/addWalletAmount', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'view-user.php?id=".$_GET['id']."&redirect=wallet'</script>";
    }
}
// updateCommunity
if(isset($_POST['updateCommunity'])){
    $community      =   cleanInputs(@$_POST['community']);
    // dump($community);
        $apiData = [
            'community_id' => ($community) ? implode(',',$community) :'',
            'user_id' => $id
        ];

        $response = sendCurlRequest(BASE_URL.'/updateCommunityByAdmin','POST', $apiData, [], true);
        $decodedResponse = json_decode($response, true);
        //dump($decodedResponse);
        
        if($decodedResponse['success']){
         echo "<script>window.location.href = 'view-user.php?id=".base64_encode($id)."'</script>";
        }
        
}

$title = $final['name']. ' - User';
include('pages/users/details.html');