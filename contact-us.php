<?php
include_once('admin/utils/helpers.php');
if (isset($_SESSION['hm_wb_logged_in']) && $_SESSION['hm_wb_logged_in'] === true) {
    header("Location: home.php");
    exit();
}

if(isset($_POST['contactUs'])){

    $apiDataU = [
        'name'         => cleanInputs($_POST['name']),
        'email'        => cleanInputs($_POST['email']),
        'subject'      => cleanInputs($_POST['subject']),
        'message'      => cleanInputs($_POST['message']),
    ];

    $response = sendCurlRequest(BASE_URL.'/contactUs', 'POST', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        setcookie('wb_successMsg', 'Thank you for reaching out. Our team will get back to you shortly.', time() + 5, "/");
        echo "<script>window.location.href = 'contact-us.php'</script>";
    }else{
        $err = 1;
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'contact-us.php'</script>";
    }
}

$title = "Contact Us";
include('pages/contact-us.html');