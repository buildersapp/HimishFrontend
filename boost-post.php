<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once('includes/check-session.php');

$apiData = [];
$adsData = [];
$defaultType = '';

$responseSettings = sendCurlRequest(BASE_URL.'/get-setting', 'GET', []);
$decodedResponseSettings = json_decode($responseSettings, true);
$generalSettings = $decodedResponseSettings['body'];
$post_Id = isset($_GET['post_id']) ? base64_decode($_GET['post_id']) : 0;

if($post_Id > 0){
    $responseAds = sendCurlRequest(BASE_URL.'/get-posts?post_id='.$post_Id, 'GET', []);
    $decodedResponseAds = json_decode($responseAds, true);
    $adsData = $decodedResponseAds['body'];
    $companyIds = array_column($decodedResponseAds['body'], 'company_id');
    $defaultType =  'image'; // Default to image for posts
    // if($adsData[0]['boost_expire'] > 0){
    //     setcookie('wb_errorMsg', 'Already Boosted', time() + 5, "/");
    //     echo "<script>window.location.href = 'home.php'</script>";
    // }
};

// Boost Post
if (isset($_POST['boostPost'])) {

    $adsData = [
        'post_id'       => cleanInputs($_POST['post_id']),
        'start_date'    => cleanInputs($_POST['start_date']),
        'end_date'      => cleanInputs($_POST['end_date']),
        'amount'        => cleanInputs($_POST['sub_total']),
        'tax'           => cleanInputs($_POST['tax_amount']),
        'total_amount'  => cleanInputs($_POST['total_amount']),
        'total_days'    => cleanInputs($_POST['total_days']),
        'payment_data'  => 0,
        'plan_id'       => cleanInputs($_POST['boost_plan']),
        'credit'            => isset($_POST['credit']) ? cleanInputs($_POST['credit']) : 0,
    ];

    if($adsData['credit'] > 0){
        $adsData['total_amount'] = $adsData['total_amount'] - $adsData['credit'];
        if($userDetails['wallet'] > $adsData['total_amount']){
            $adsData['total_amount'] = 0;
        }
    }

    $response = sendCurlRequest(BASE_URL.'/boost-post', 'POST', $adsData, [], true);
    $decodedResponse = json_decode($response, true);

    if($decodedResponse['success']){
        $paymentUrl = $decodedResponse['body']['url'];
        if($paymentUrl){
            echo "<script>window.location.href = '".$paymentUrl."'</script>";
        }else{
            setcookie('wb_successMsg', 'Successfully Boosted', time() + 5, "/");
            echo "<script>window.location.href = 'home.php'</script>";
        }
    }else{
        $err = 1;
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'boost-post.php?post_id=".base64_encode($adsData['post_id'])."'</script>";
    }
}

$responsePlansI = sendCurlRequest(BASE_URL.'/getPlans', 'GET', ['type' => 0]);
$decodedResponsePlansI = json_decode($responsePlansI, true);
$boostPlans = $decodedResponsePlansI['body'];

$title = "Boost Post";
include('pages/posts/boost-post.html');
?>
