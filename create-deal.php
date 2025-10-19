<?php
include_once('includes/check-session.php');

$postId = isset($_GET['id']) ? (int) base64_decode($_GET['id']) : 0;

$responseSettings = sendCurlRequest(BASE_URL.'/get-setting', 'GET', []);
$decodedResponseSettings = json_decode($responseSettings, true);
$generalSettings = $decodedResponseSettings['body'];


// get Plans
$responsePlans = sendCurlRequest(BASE_URL.'/getPlans', 'GET', []);
$decodedResponsePlans = json_decode($responsePlans, true);
$generalPlans = $decodedResponsePlans['body'];
$featuredDealsPlan = array_values(array_filter($generalPlans, function($mem) {
    return $mem['type'] == 3;
}));
//dump($featuredDealsPlan);

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
        echo "<script>window.location.href = 'create-deal.php?id=".base64_encode($postId)."'</script>";
    }else{
        setcookie('wb_errorMsg', $decodedResponseDI['message'], time() + 5, "/");
        echo "<script>window.location.href = 'create-deal.php?id=".base64_encode($postId)."'</script>";
    }
 }

/***
 * 
 * UPDATE DEAL
 * 
 * 
 */

 if(isset($_POST['updatePost'])){

    $categoryArray = [];
    $post_locations = [];

    $apiDataU = [
        'post_id'       => cleanInputs($postId),
        'title'         => cleanInputs($_POST['title']),
        'info'          => cleanInputs($_POST['description']),
        'regular_price' => cleanInputs($_POST['regular_price']),
        'price'         => cleanInputs($_POST['sale_price']),
        'company'       => cleanInputs($_POST['store_name']),
        'address'       => cleanInputs($_POST['address']),
        'latitude'      => cleanInputs($_POST['latitude']),
        'longitude'     => cleanInputs($_POST['longitude']),
        'city'          => cleanInputs($_POST['city']),
        'state'         => cleanInputs($_POST['state']),
        'url'           => cleanInputs($_POST['url']),
        'type'          => 2,
        'loc_type'      => cleanInputs($_POST['loc_type']),
        'is_featured_deal'      => isset($_POST['is_featured_deal']) ? 1 : 0,
        'credit'      => isset($_POST['credit']) ? cleanInputs($_POST['credit']) : 0,
        'country_code'         => cleanInputs($_POST['country_code']),
    ];


    if($apiDataU['is_featured_deal'] > 0){
        $apiDataU['amount'] = $generalSettings['fd_cost'];
        $apiDataU['total_amount'] = number_format(($generalSettings['fd_cost'] * $generalSettings['nfc_card_tax']) / 100 + $generalSettings['fd_cost'], 2);
        $apiDataU['tax'] = number_format(($generalSettings['fd_cost'] * $generalSettings['nfc_card_tax']) / 100,2);

        if($apiDataU['credit'] > 0){
            $apiDataU['total_amount'] = $apiDataU['total_amount'] - $apiDataU['credit'];

            if($userDetails['wallet'] > $apiDataU['total_amount']){
                $apiDataU['is_featured_deal'] = 0;
                $apiDataU['total_amount'] = 0;
            }
        }
    }

    //dump($apiDataU);

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

    $response = sendCurlRequest(BASE_URL.'/update-post-admin', 'POST', $apiDataU, [], true);
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

        if($apiDataU['is_featured_deal']){
            $paymentUrl = $decodedResponse['meta']['payment_url'];
            echo "<script>window.location.href = '".$paymentUrl."'</script>";
        }else{
            echo "<script>window.location.href = 'home.php?type=2'</script>";
        }
    }else{
        $err = 1;
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'create-deal.php?id=".base64_encode($postId)."'</script>";
    }
}

/***
 * 
 * ADD DEAL
 * 
 * 
 */

if(isset($_POST['addPost'])){

    $categoryArray = [];
    $post_locations = [];

    $apiDataU = [
        'title'         => cleanInputs($_POST['title']),
        'info'          => cleanInputs($_POST['description']),
        'regular_price' => cleanInputs($_POST['regular_price']),
        'price'         => cleanInputs($_POST['sale_price']),
        'company'       => cleanInputs($_POST['store_name']),
        'address'       => cleanInputs($_POST['address']),
        'latitude'      => cleanInputs($_POST['latitude']),
        'longitude'     => cleanInputs($_POST['longitude']),
        'city'          => cleanInputs($_POST['city']),
        'state'         => cleanInputs($_POST['state']),
        'url'           => cleanInputs($_POST['url']),
        'type'          => 2,
        'loc_type'      => cleanInputs($_POST['loc_type']),
        'is_featured_deal'      => isset($_POST['is_featured_deal']) ? 1 : 0,
        'credit'      => isset($_POST['credit']) ? cleanInputs($_POST['credit']) : 0,
        'country_code'         => cleanInputs($_POST['country_code']),
    ];


    if ($apiDataU['is_featured_deal']) {
        $num_days = isset($_POST['num_days']) ? intval($_POST['num_days']) : 1;

        $pricePerDay = $featuredDealsPlan[0]['price'];
        $taxRate = $featuredDealsPlan[0]['tax'];

        $totalPrice = $pricePerDay * $num_days;
        $taxAmount = ($totalPrice * $taxRate) / 100;
        $totalAmount = $totalPrice + $taxAmount;

        $apiDataU['amount'] = $totalPrice;
        $apiDataU['tax'] = number_format($taxAmount, 2);
        $apiDataU['total_amount'] = number_format($totalAmount, 2);

        if (!empty($apiDataU['credit']) && $apiDataU['credit'] > 0) {
            $apiDataU['total_amount'] = $totalAmount - $apiDataU['credit'];

            // Optional: reset if wallet > total
            if ($userDetails['wallet'] > $apiDataU['total_amount']) {
                $apiDataU['is_featured_deal'] = 0;
                $apiDataU['total_amount'] = 0;
            }
        }

        // Calculate expire date (current time + days) in timestamp format
        $expireTime = time() + ($num_days * 86400); // 86400 = seconds per day
        $apiDataU['expire_date'] = $expireTime;
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
        $apiDataU['categoryArray'] = json_encode($categoryArray);
    }

    // add post_locations array
    if (isset($apiDataU['latitude']) && !empty($apiDataU['latitude']) && isset($apiDataU['longitude']) && !empty($apiDataU['longitude'])) {
        $post_locations[] = [
            'latitude'  => $apiDataU['latitude'],
            'longitude'     => $apiDataU['longitude'],
            'country_code'     => $apiDataU['country_code'],
            'community_id'  => 0,
        ];
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

        if($apiDataU['is_featured_deal']){
            $paymentUrl = $decodedResponse['meta']['payment_url'];
            echo "<script>window.location.href = '".$paymentUrl."'</script>";
        }else{
            echo "<script>window.location.href = 'home.php?type=2'</script>";
        }
    }else{
        $err = 1;
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'create-deal.php'</script>";
    }
}


// get edit deal data
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
    }
}

$title = ($postId > 0) ? "Update Deal" : "Create Deal";
include('pages/posts/create-deal.html');
?>
