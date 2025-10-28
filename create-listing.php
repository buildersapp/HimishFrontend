<?php
include_once('includes/check-session.php');

// Initialize necessary arrays
$apiData = [];

$uniqueKeysMy = [];
$uniqueKeysAll = [];
$myCommunityLocations = [];
$allCommunityLocations = [];

$postDetail = [];
$selectedRadius = 5;
$postId = isset($_GET['id']) ? (int) base64_decode($_GET['id']) : 0;

/***
 * 
 * DELETE IMAGE
 * 
 * 
 */

 if(isset($_GET['img_id']) && !empty($_GET['img_id']) && isset($_GET['act'])){
    $img_id = base64_decode($_GET['img_id']);
    $query_data = '?id=' . urlencode($img_id);
    $responseDI = sendCurlRequest(BASE_URL . '/delete-post-image' . $query_data, 'DELETE', []);
    $decodedResponseDI = json_decode($responseDI, true);
    if($decodedResponseDI['success']){
        setcookie('wb_successMsg', $decodedResponseDI['message'], time() + 5, "/");
        echo "<script>window.location.href = 'create-listing.php?id=".base64_encode($postId)."'</script>";
    }else{
        setcookie('wb_errorMsg', $decodedResponseDI['message'], time() + 5, "/");
        echo "<script>window.location.href = 'create-listing.php?id=".base64_encode($postId)."'</script>";
    }
 }

$query_data = '?user_id=' . urlencode($userDetails['id']);
$response = sendCurlRequest(BASE_URL . '/get-community' . $query_data, 'GET', $apiData);
$decodedResponse = json_decode($response, true);

/***
 * 
 * UPDATE LISTING
 * 
 * 
 */

 if(isset($_POST['updatePost'])){

    $categoryArray = [];
    $post_locations = [];
    $tempFiles = [];

    $apiDataU = [
        'post_id'       => cleanInputs($postId),
        'title'         => cleanInputs($_POST['title']),
        'info'          => cleanInputs($_POST['info']),
        'address'          => cleanInputs($_POST['address']),
        'state'          => cleanInputs($_POST['state']),
        'country_code'          => cleanInputs($_POST['country_code']),
        'city'          => cleanInputs($_POST['city']),
        'latitude'          => cleanInputs($_POST['latitude']),
        'longitude'          => cleanInputs($_POST['longitude']),
        'type'          => 1
    ];

    // if($apiDataU['community_type'] != 0){
    //     if($apiDataU['community_type'] == 2){
    //         $selectedCommunities = json_decode($_POST['selected_communities'], true);
    //         $communityNames = array_map(function ($community) {
    //             return $community['name'];
    //         }, $selectedCommunities);
            
    //         $commaSeparatedNames = implode(',', $communityNames);
    //     }else{
    //         $commaSeparatedNames = $userDetails['community'];
    //     }
    //     $apiDataU['community'] = $commaSeparatedNames;
    // }

    // add post_locations array
    $postLocations = [];
    if (isset($_POST['selected_locations']) && !empty($_POST['selected_locations'])) {
        $post_locations = json_decode($_POST['selected_locations'],true);
        $apiDataU['address'] = $post_locations[0]['address'];
        $apiDataU['latitude'] = $post_locations[0]['latitude'];
        $apiDataU['longitude'] = $post_locations[0]['longitude'];
        $apiDataU['state'] = $post_locations[0]['state'];
        $apiDataU['city'] = $post_locations[0]['city'];
        $apiDataU['country_code'] = $post_locations[0]['country_code'];
        $apiDataU['post_locations'] = json_encode($post_locations);
    }else{
        $postLocations['address'] = $apiDataU['address'];
        $postLocations['latitude'] = $apiDataU['latitude'];
        $postLocations['longitude'] = $apiDataU['longitude'];
        $postLocations['state'] = $apiDataU['state'];
        $postLocations['city'] = $apiDataU['city'];
        $postLocations['country_code'] = $apiDataU['country_code'];
        $apiDataU['post_locations'] = json_encode($postLocations);
    }

    // get categorization
    // if(isset($apiDataU['info']) && !empty($apiDataU['info'])){
    //     $responseCat = sendCurlRequest(BASE_URL.'/searchPills', 'POST', ['description' => cleanInputs($apiDataU['info'])], [], true);
    //     $decodedResponseCat = json_decode($responseCat, true);
    //     //dump($decodedResponseCat);
    //     if (!empty($decodedResponseCat['body']) && is_array($decodedResponseCat['body'])) {
    //         foreach ($decodedResponseCat['body'] as $category) {
    //             $apiDataU['service'] = $category['keywords4'];
    //             $categoryArray[] = [
    //                 'id'  => $category['id'] ?? "",
    //                 'category'     => end($category['treeStructure']) ?? "",
    //                 'subcategory'  => $category['treeStructure'][2] ?? "",
    //                 'pills'  => explode(',',$category['keywords4']) ?? [],
    //             ];
    //         }
    //     }
    // }

    //$apiDataU['categoryArray'] = json_encode($categoryArray);
    
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

    $response = sendCurlRequest(BASE_URL.'/update-post-user', 'POST', $apiDataU, [], true);
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
        echo "<script>window.location.href = 'home.php?type=1'</script>";
    }else{
        $err = 1;
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'create-listing.php?id=".base64_encode($postId)."'</script>";
    }
}

/***
 * 
 * ADD LISTING
 * 
 * 
 */

 if(isset($_POST['addPost'])){

    $categoryArray = [];
    $post_locations = [];
    $tempFiles = [];

    $apiDataU = [
        'title'         => cleanInputs($_POST['title']),
        'info'          => cleanInputs($_POST['info']),
        'address'          => cleanInputs($_POST['address']),
        'state'          => cleanInputs($_POST['state']),
        'city'          => cleanInputs($_POST['city']),
        'latitude'          => cleanInputs($_POST['latitude']),
        'longitude'          => cleanInputs($_POST['longitude']),
        'country_code'          => cleanInputs($_POST['country_code']),
        'community_type'=> cleanInputs($_POST['community_type']),
        'radius'        => cleanInputs($_POST['radius']),
        'type'          => 1
    ];

    if($apiDataU['community_type'] != 0){
        if($apiDataU['community_type'] == 2){
            $selectedCommunities = json_decode($_POST['selected_communities'], true);
            $communityNames = array_map(function ($community) {
                return $community['name'];
            }, $selectedCommunities);
            
            $commaSeparatedNames = implode(',', $communityNames);
        }else{
            $commaSeparatedNames = $userDetails['community'];
        }
        $apiDataU['community'] = $commaSeparatedNames;
    }

    // add post_locations array
    if (isset($apiDataU['latitude']) && !empty($apiDataU['latitude']) && isset($apiDataU['longitude']) && !empty($apiDataU['longitude'])) {
        $apiDataU['post_locations'] = $_POST['selected_locations'];
    }

    // get categorization
    if(isset($apiDataU['info']) && !empty($apiDataU['info'])){
        $responseCat = sendCurlRequest(BASE_URL.'/searchPills', 'POST', ['description' => cleanInputs($apiDataU['info'])], [], true);
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
    }

    $apiDataU['categoryArray'] = json_encode($categoryArray);
    
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

    $response = sendCurlRequest(BASE_URL.'/create-post', 'POST', $apiDataU, [], true);
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
        echo "<script>window.location.href = 'home.php?type=1'</script>";
    }else{
        $err = 1;
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'create-listing.php'</script>";
    }
}

$query_data = '?user_id=' . urlencode($userDetails['id']);
$response = sendCurlRequest(BASE_URL . '/get-community' . $query_data, 'GET', $apiData);
$decodedResponse = json_decode($response, true);

// Ensure response is valid and communities exist
$communities = isset($decodedResponse['body']) && is_array($decodedResponse['body']) ? $decodedResponse['body'] : [];

$my_communities_locations = array_filter($communities, function ($data) {
    return isset($data['is_selected']) &&
           $data['is_selected'] == 1 &&
           !empty($data['latitude']) &&
           !empty($data['longitude']);
});

$all_communities_locations = array_filter($communities, function ($data) {
    return !empty($data['latitude']) && !empty($data['longitude']);
});

// Step 4: Process
addUniqueLocations($my_communities_locations, $uniqueKeysMy, $myCommunityLocations);
addUniqueLocations($all_communities_locations, $uniqueKeysAll, $allCommunityLocations);

// get edit listing data
if($postId > 0){
    $apiData = [
        'post_id' => $postId
    ];

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/get-posts', 'GET', $apiData);
    $responseDecoded = json_decode($response,true);

    // Decode the response (assuming it's JSON)
    $postDetail = $responseDecoded['body'];
    if(count($postDetail)){
        $apiDataU = $postDetail[0];
        $selectedRadius = isset($apiDataU['radius']) ? $apiDataU['radius'] : '5';
        $selectedCommunityType = isset($apiDataU['community_type']) ? $apiDataU['community_type'] : '1';
    }
}

//dump($postDetail);

$title = ($postId > 0) ? "Update Listing" : "Create Listing";
include('pages/posts/create-listing.html');
?>
