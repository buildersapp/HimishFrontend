<?php
include_once('includes/check-session.php');

// Update Profile
if (isset($_POST['updateProfile'])) {

    $apiDataU = [
        'name'         => cleanInputs($_POST['name']),
        'email'        => cleanInputs($_POST['email'] ?? ''),
        'handle_name'  => cleanInputs($_POST['handle_name'] ?? ''),
        'phone'        => cleanInputs($_POST['phone'] ?? ''),
    ];

    // Check if a file was uploaded
    if (isset($_POST['croppedImages']) && is_array($_POST['croppedImages'])) {
        $tempDir = __DIR__ . '/assets/uploads'; // Ensure this folder exists
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
    
        foreach ($_POST['croppedImages'] as $index => $croppedImage) {
            if (!empty($croppedImage)) {
                $imageParts = explode(";base64,", $croppedImage);
                if (count($imageParts) == 2) {
                    $imageBase64 = base64_decode($imageParts[1]);
                    $tempFilePath = $tempDir . '/cropped_img_' . time() . "_$index.jpg";
                    file_put_contents($tempFilePath, $imageBase64);
    
                    $apiDataU["image"] = new CURLFile($tempFilePath, 'image/jpeg', "cropped_image_$index.jpg");
                    $tempFiles[] = $tempFilePath;
                }
            }
        }
    }

    $endpoint = BASE_URL. '/edit-profile';
    $method = 'PUT';

    $response        = sendCurlRequest($endpoint, $method, $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);

    // -------- handle response ----------
    if ($decodedResponse['success']) {
        // unlink temp files if any ...
        setcookie('wb_successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href='profile.php'</script>";
        exit;
    }

    // on error
    setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
    echo "<script>window.location.href='profile.php?md=open'</script>";
    exit;
}

// Change Password
if(isset($_POST['changePassword'])){

    $tempFiles = [];

    $apiDataU = [
        'oldPassword'       => cleanInputs($_POST['oldPassword']),
        'newPassword'       => cleanInputs($_POST['newPassword']),
    ];

    $response = sendCurlRequest(BASE_URL.'/change-password', 'PUT', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        setcookie('wb_successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'profile.php'</script>";
    }else{
        $err = 1;
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'profile.php'</script>";
    }
}

// Title and page rendering (not changed)
$title = "Profile | Sales Representative";

include('pages/user/profile.html');
?>
