<?php
error_reporting(0);
include_once('includes/custom-functions.php');
include_once('admin/utils/helpers.php');
//dump($_SESSION);
$current_page = basename($_SERVER['PHP_SELF']);
if (empty($_SESSION)) {
    echo "<script>window.location.href = 'login.php'</script>";
    exit();
}

// get user details
if(isset($_SESSION['hm_wb_auth_data']['id'])){
    $query_data ='?user_id='.$_SESSION['hm_wb_auth_data']['id'].'&page=1&limit=1';
    $response = sendCurlRequest(BASE_URL.'/get-profile'.$query_data, 'GET', []);
    $decodedResponse = json_decode($response, true);
    $userDetails = $decodedResponse['body'];
}else{
    $userDetails = [];
}

// get user companies
$responseUC = sendCurlRequest(BASE_URL.'/get-company', 'GET', ['user_id' => $userDetails['id']]);
$decodedResponseUC = json_decode($responseUC, true);
$userCompanies = $decodedResponseUC['body'];

// Handle 401 Unauthorized
if (empty($decodedResponse) || (isset($decodedResponse['code']) && $decodedResponse['code'] == 401)) {
    http_response_code(401);
    unset($_SESSION['hm_wb_auth_data']);
    unset($_SESSION['hm_wb_logged_in']);
    unset($_SESSION['hm_wb_timezone']);
    echo "<script>window.location.href = 'home.php'</script>";
}
?>
