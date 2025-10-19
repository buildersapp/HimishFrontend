<?php
include_once('utils/helpers.php');

$id =base64_decode($_GET['id']);

$apiData=[];
$users=[];
$query_data ='?id='.$id.'&page=1&limit=1';
$response = sendCurlRequest(BASE_URL.'/admin-community-list'.$query_data, 'GET', $apiData);
$decodedResponse = json_decode($response, true);
//dump($decodedResponse);

$profileGroups =[];
$responsePG= sendCurlRequest(BASE_URL.'/profile-group-list', 'GET', []);
$decodedResponsePG = json_decode($responsePG, true);
if($decodedResponsePG['success']){
    $profileGroups = $decodedResponsePG['body'];
}

// Handle 401 Unauthorized
if (isset($decodedResponse['code']) && $decodedResponse['code'] == 401) {
    http_response_code(401);
    header($_SERVER['PHP_SELF']);
    exit;
}

// users get 

$response1 = sendCurlRequest(BASE_URL.'/all-users?community_id='.$id, 'GET', $apiData);
$decodedResponse1 = json_decode($response1, true);

//  dump($decodedResponse['body']);
$final = ($decodedResponse['body']) ? $decodedResponse['body'][0] :'';
$users = ($decodedResponse1['body']) ? $decodedResponse1['body'] :[];

// dump($users);
// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Communities', 'url' => 'communities.php'],
    ['name' => $final['name'], 'url' => ''],
];

if(isset($_POST['updateCommunity'])){
    $description  =   cleanInputs($_POST['description']);
    $is_private   =   cleanInputs($_POST['is_private']);
    $name         =   cleanInputs($_POST['name']);
    $price         =   cleanInputs($_POST['price']);
    $gender         =   cleanInputs($_POST['gender']);
    $apiData = [
        'description' => $description,
        'is_private' => $is_private,
        'name' => $name,
        'price' => $price,
        'gender' => $gender
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

    $response = sendCurlRequest(BASE_URL.'/admin-edit-community?editId='.$id, 'PUT', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'communities.php'</script>";
    }
}
// add community users
if(isset($_POST['addCommunityUsers'])){
    $users  =   cleanInputs($_POST['user']);
    if(count($users) > 0){
        $apiData = [
            'user_id' => implode(',',$users),
            'community_id' => $id,
            
        ];

         $response = sendCurlRequest(BASE_URL.'/addAdminCommunityMember', 'POST', $apiData, [], true);
        $decodedResponse = json_decode($response, true);
        //dump($decodedResponse);
        if($decodedResponse['success']){
            echo "<script>window.location.href = 'community-details.php?id=".base64_encode($id)."'</script>";
        }
    }else{
            echo "<script>window.location.href = 'community-details.php?id=".base64_encode($id)."'</script>";
    }
   





   
}

$title = $final['name']. ' - Community';
include('pages/communities/details.html');