<?php
include_once('includes/check-session.php');

if(isset($_POST['editProfile'])){

    $tempFiles = [];

    $apiDataU = [
        'name'          => cleanInputs($_POST['name']),
        'handle_name'   => cleanInputs($_POST['handle_name']),
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
    //dump($apiDataU);

    $response = sendCurlRequest(BASE_URL.'/edit-profile', 'PUT', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        // Unlink temporary files after request
        foreach ($tempFiles as $tempFilePath) {
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
        }
        setcookie('wb_successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'home.php'</script>";
    }else{
        $err = 1;
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'edit-profile.php'</script>";
    }
}

$title = "Edit Profile";
include('pages/user/edit-profile.html');
?>
