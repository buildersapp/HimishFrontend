<?php
include_once('includes/check-session.php');

if(isset($_POST['addPost'])){

    $categoryArray = [];
    $post_locations = [];

    $apiDataU = [
        'title'         => cleanInputs($_POST['title']),
        'address'       => cleanInputs($_POST['address']),
        'latitude'      => cleanInputs($_POST['latitude']),
        'longitude'     => cleanInputs($_POST['longitude']),
        'city'          => cleanInputs($_POST['city']),
        'state'         => cleanInputs($_POST['state']),
        'type'          => 2,
        'loc_type'      => cleanInputs($_POST['loc_type']),
        'country_code'         => cleanInputs($_POST['country_code']),
    ];

    // get categorization
    if(isset($apiDataU['title']) && !empty($apiDataU['title'])){
        $responseCat = sendCurlRequest(BASE_URL.'/searchPills', 'POST', ['description' => cleanInputs($apiDataU['title'])], [], true);
        $decodedResponseCat = json_decode($responseCat, true);
        //dump($decodedResponseCat);
        if (!empty($decodedResponseCat['body']) && is_array($decodedResponseCat['body'])) {
            foreach ($decodedResponseCat['body'] as $category) {
                $apiDataU['service'] = $category['keywords4'];
                $categoryArray[] = [
                    'id'  => $category['id'] ?? "",
                    'category'     => end($category['treeStructure']) ?? "",
                    'subcategory'  => $category['treeStructure'][2] ?? "",
                    'pills'  => explode(',',$category['keywords4']) ?? [],
                ];
            }
        }
        $apiDataU['categoryArray'] = json_encode($categoryArray);
    }

    // Set Locations
    if (isset($apiDataU['loc_type'])) {
        if ($apiDataU['loc_type'] == "2") { // NationWide
            $post_locations[] = [
                'latitude'     => null,
                'longitude'    => null,
                'community_id' => 0,
                'country_code' => isset($userDetails['country_code']) ? $userDetails['country_code'] : null,
            ];
        } elseif ($apiDataU['loc_type'] == "1") { // WorldWide
            $post_locations[] = [
                'latitude'     => null,
                'longitude'    => null,
                'community_id' => 0,
                'country_code' => 'WW', // All countries (worldwide)
            ];
        } else { // Default location (manual input)
            $post_locations[] = [
                'latitude'     => !empty($apiDataU['latitude']) ? $apiDataU['latitude'] : null,
                'longitude'    => !empty($apiDataU['longitude']) ? $apiDataU['longitude'] : null,
                'community_id' => 0,
                'country_code' => isset($apiDataU['country_code']) ? $apiDataU['country_code'] : null,
            ];
        }

        $apiDataU['post_locations'] = json_encode($post_locations);
    }


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

    $response = sendCurlRequest(BASE_URL.'/create-post', 'POST', $apiDataU, [], true);
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
        echo "<script>window.location.href = 'home.php?type=2'</script>";
    }else{
        $err = 1;
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'create-deal-share.php'</script>";
    }
}

$title = "Create Deal Share";
include('pages/posts/create-deal-share.html');
?>
