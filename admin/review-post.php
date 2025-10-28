<?php
include_once('utils/helpers.php');

$id = $_GET['post_id'];

if(isset($_GET['op'])){
    $status = $_GET['op'] == 'accept' ? 1 : 2;
    $post_id    =  cleanInputs($id);
    $apiData = ['status' => (int)$status,'post_id'=> (int)$post_id];
    $response = sendCurlRequest(BASE_URL.'/admin-change-post-listing-type', 'POST', $apiData, [], false, "8fcd91ae5e316cb6611d");
    $responseUp = json_decode($response,true);
    if($responseUp['success']){
        echo "<script>window.location.href='review-post.php?post_id=".($id)."&res=success'</script>";
    }else{
        echo "<script>window.location.href='review-post.php?post_id=".($id)."&res=error'</script>";
    }
    exit;
}

if(isset($_POST['reject']) || isset($_POST['approve'])){

    if(isset($_POST['reject'])){
        $status = 2;
    }else{
        $status = 1;
    }

    $type = $_POST['type'];
    $post_id    =  cleanInputs($id);
    $gender    =  cleanInputs($_POST['gender']);
    $apiData = ['post_id'=> (int)$post_id, 'status' => $status];

    if (isset($type)) {
        $apiData['type'] = $type;
    }

    if(!empty($gender)){
        $apiData['gender'] = $gender;
    }else{
        echo "<script>alert('Please select profile group or gender.');</script>";
        exit;
    }

    $response = sendCurlRequest(BASE_URL.'/admin-change-post-listing-type', 'POST', $apiData, [], false, "8fcd91ae5e316cb6611d");
    $responseUp = json_decode($response,true);
    if($responseUp['success']){
        echo "<script>window.location.href='review-post.php?post_id=".($id)."&res=success'</script>";
        exit;
    }else{
        echo "<script>alert('Server Error ! Please try again later.');</script>";
    }
}

$profileGroups =[];
$responsePG= sendCurlRequest(BASE_URL.'/profile-group-list', 'GET', []);
$decodedResponsePG = json_decode($responsePG, true);
if($decodedResponsePG['success']){
    $profileGroups = $decodedResponsePG['body'];
}

$apiData=[];
$final = (object)[];
if($id > 0) {
    $query_data ='?post_id='.$id;
    $response = sendCurlRequest(BASE_URL.'/get-single-post'.$query_data, 'GET', $apiData, [], false, "8fcd91ae5e316cb6611d");
    $decodedResponse = json_decode($response, true);
    if(!$decodedResponse['success']){
        header('Location: dashboard.php');
    }
    $final = $decodedResponse['body'];
}

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Posts', 'url' => 'posts.php'],
    ['name' => $id > 0 ? $final['title'] : 'Invalid Post', 'url' => ''],
];

$title = ($id > 0 ? $final['title'] : 'Invalid Post'). ' - Post';
include('pages/posts/review.html');