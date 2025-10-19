<?php
include_once('includes/check-session.php');

if(isset($_POST['changePassword'])){

    $tempFiles = [];

    $apiDataU = [
        'oldPassword'       => cleanInputs($_POST['oldPassword']),
        'newPassword'       => cleanInputs($_POST['newPassword']),
    ];

    $response = sendCurlRequest(BASE_URL.'/change-password', 'PUT', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        setcookie('wb_successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'home.php'</script>";
    }else{
        $err = 1;
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'change-password.php'</script>";
    }
}

$title = "Change Password";
include('pages/user/change-password.html');
?>
