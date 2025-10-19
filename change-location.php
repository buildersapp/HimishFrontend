<?php
include_once('includes/custom-functions.php');
include_once('includes/web-helpers.php');
include_once('admin/utils/helpers.php');

$isLoggedIn = isset($_SESSION['hm_wb_logged_in']) ? 1 : 0;

if (isset($_POST['save_location'])) {
    $apiData = $_POST;

    // Trim and validate city and state
    $city = trim($apiData['city'] ?? '');
    $state = trim($apiData['state'] ?? '');

    if (empty($city) && empty($state)) {
        setcookie('wb_errorMsg', 'Please select valid address.', time() + 5, "/");
        echo "<script>window.location.href = 'change-location.php'</script>";
    }

    $response = sendCurlRequest(BASE_URL . '/edit-profile', 'PUT', $apiData);
    $responseDmp = json_decode($response,true);
    if($responseDmp['success']){
        echo "<script>localStorage.setItem('locationManuallySet', 1);</script>";
        echo "<script>window.location.href = 'home.php'</script>";
    }else{
        setcookie('wb_errorMsg', $responseDmp['message'], time() + 5, "/");
        echo "<script>window.location.href = 'change-location.php'</script>";
    }
}

// Title and page rendering (not changed)
$title = "Change Location";
include('pages/change-location/index.html');
?>
