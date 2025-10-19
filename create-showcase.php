<?php
include_once('includes/check-session.php');

$companyId = isset($_GET['id']) ? (int) base64_decode($_GET['id']) : 0;
if($companyId == 0){
    header('Location: home.php');
}

if(isset($_POST['addShowcase'])){

    $tempFiles = [];

    $apiDataU = [
        'company_id'    => cleanInputs($_POST['company_id']),
        'info'          => cleanInputs($_POST['info']),
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
    
                    $apiDataU["image$index"] = new CURLFile($tempFilePath, 'image/jpeg', "cropped_image_$index.jpg");
                    $tempFiles[] = $tempFilePath;
                }
            }
        }
    }
    //dump($apiDataU);

    $response = sendCurlRequest(BASE_URL.'/create-showcase', 'POST', $apiDataU, [], true);
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
        echo "<script>window.location.href = 'company-details.php?id=".base64_encode($apiDataU['company_id'])."'</script>";
    }else{
        $err = 1;
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'create-showcase.php'</script>";
    }
}

$title = "Create Showcase";
include('pages/companies/create-showcase.html');
?>
