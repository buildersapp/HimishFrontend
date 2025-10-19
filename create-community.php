<?php
include_once('includes/check-session.php');

if(isset($_POST['addCommunity'])){
    // dump($_POST);
    $post_locations = [];
    $is_private = $_POST['is_private'] ?? '0';
    $apiDataU = [
        'name'         => cleanInputs($_POST['title']),
        'description'          => cleanInputs($_POST['description']),
        'address'       => cleanInputs($_POST['address']),
        'latitude'      => cleanInputs($_POST['latitude']),
        'longitude'     => cleanInputs($_POST['longitude']),
        'city'          => cleanInputs($_POST['city']),
        'state'         => cleanInputs($_POST['state']),
        'country_code'  => cleanInputs($_POST['country_code']),
        'loc_type'      => cleanInputs($_POST['loc_type']),
        'is_private'    => cleanInputs($is_private),
    ];

    // add post_locations array
    // if(isset($apiDataU['latitude']) && isset($apiDataU['longitude'])){
    //     $post_locations[] = [
    //         'latitude'  => $apiDataU['latitude'],
    //         'longitude'     => $apiDataU['longitude'],
    //         'community_id'  => 0,
    //     ];
    //     $apiDataU['post_locations'] = json_encode($post_locations);
    // }

    // Check if a file was uploaded
    $tempFilePath = "";
    if(isset($_POST['cropped_image']) && !empty($_POST['cropped_image'])) {
        $croppedImage = $_POST['cropped_image']; // Base64 string
    
        // Convert Base64 to an actual file
        $imageParts = explode(";base64,", $croppedImage);
        $imageBase64 = base64_decode($imageParts[1]);
        $tempDir = __DIR__ . '/assets/uploads'; // Ensure this folder exists
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true); // Create the directory if it doesn't exist
        }
        $tempFilePath = $tempDir . '/cropped_img_' . time() . '.jpg';
    
        // Save the decoded image
        file_put_contents($tempFilePath, $imageBase64);

        $apiDataU['image'] = new CURLFile($tempFilePath, 'image/jpeg', 'cropped_image.jpg');
    
    }else{
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $imageFilePath = $_FILES['image']['tmp_name'];
            $apiDataU['image'] = new CURLFile($imageFilePath, $_FILES['image']['type'], $_FILES['image']['name']);
        }
    }

    //dump($apiDataU);

    $response = sendCurlRequest(BASE_URL.'/add-community', 'POST', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        // Unlink temporary files after request
        if (!empty($tempFilePath) && file_exists($tempFilePath)) {
            unlink($tempFilePath);
        }

        if (isset($imageFilePath) && file_exists($imageFilePath)) {
            unlink($imageFilePath);
        }
        setcookie('wb_successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'communities.php'</script>";
    }else{
        $err = 1;
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'communities.php'</script>";
    }
}
$title = "Create Community";
include('pages/communities/create-community.html');
?>
