<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Deal Share', 'url' => 'deal-share.php']
];

## get All Users
$user_data = sendCurlRequest(BASE_URL.'/all-users', 'GET', []);
$userDataResponse = json_decode($user_data, true);
$users = $userDataResponse['body'];

$err = 0;
if(isset($_POST['addPost'])){

    $categoryArray = [];

    $apiDataU = [
        'title'         => cleanInputs($_POST['title']),
        'address'       => cleanInputs($_POST['address']),
        'latitude'      => cleanInputs($_POST['latitude']),
        'longitude'     => cleanInputs($_POST['longitude']),
        'city'     => cleanInputs($_POST['city']),
        'state'     => cleanInputs($_POST['state']),
        'type'          => 2,
        'loc_type'      => isset($_POST['loc_type']) ? 1 : 0
    ];

    //dump($apiDataU);

    if(isset($_POST['user_id']) && !empty($_POST['user_id'])){
        $apiDataU['user_id'] = cleanInputs($_POST['user_id']);
    }

    //dump($apiDataU);

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
    }

    $apiDataU['categoryArray'] = json_encode($categoryArray);

    $apiDataU['post_locations'] = [];
    if(!empty($apiDataU['latitude']) && !empty($apiDataU['longitude'])){
        $apiDataU['post_locations'][] = array('latitude' => $apiDataU['latitude'], 'longitude' => $apiDataU['longitude'], 'community_id' => 0);
        $apiDataU['post_locations'] = json_encode($apiDataU['post_locations']);
    }

    // Check if a file was uploaded
    $tempFilePath = "";
    if(isset($_POST['cropped_image']) && !empty($_POST['cropped_image'])) {
        $croppedImage = $_POST['cropped_image']; // Base64 string
    
        // Convert Base64 to an actual file
        $imageParts = explode(";base64,", $croppedImage);
        $imageBase64 = base64_decode($imageParts[1]);
        $tempDir = __DIR__ . '/uploads'; // Ensure this folder exists
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
        if(!empty($tempFilePath)){
            // Delete the temporary file
            unlink($tempFilePath);
        }
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'deal-share.php'</script>";
    }else{
        $err = 1;
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'deal-share.php'</script>";
    }
}

$title = "Deal Share";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}
include('pages/dealshare/list.html');