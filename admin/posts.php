<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Posts', 'url' => 'posts.php']
];

$python_api_url = (!empty($_SESSION['hm_auth_data'])) ? $_SESSION['hm_auth_data']['python_api_url'] :'https://citilytics.net/api/ocr/';

## get All Users
$user_data = sendCurlRequest(BASE_URL.'/all-users', 'GET', []);
$userDataResponse = json_decode($user_data, true);
$users = $userDataResponse['body'];

## get All Community
$community_data = sendCurlRequest(BASE_URL.'/get-community', 'GET', []);
$communityDataResponse = json_decode($community_data, true);
$communities = $communityDataResponse['body'];
## get All Company
$company_data = sendCurlRequest(BASE_URL.'/all-admin-company', 'GET', []);
$companyDataResponse = json_decode($company_data, true);
$company = $companyDataResponse['body'];
usort($company, function ($a, $b) {
    return strcmp($a['name'], $b['name']);
});

if(isset($_POST['addPost'])){
    // dump($_SESSION);
    //  dump($_POST);
    $today = date("d/m/Y"); // Get today's date in the same format

    
    $apiDataU = [
        'title'         => cleanInputs($_POST['title']),
        'phone'       => cleanInputs($_POST['phone']),
        'email'       => cleanInputs($_POST['email']),
        'address'       => cleanInputs($_POST['address']),
        'latitude'      => cleanInputs($_POST['latitude']),
        'longitude'     => cleanInputs($_POST['longitude']),
        'company'     => cleanInputs($_POST['company']),
        'service'     => cleanInputs($_POST['service']),
        'community_type'=> cleanInputs($_POST['community_type']),
        'state'=> cleanInputs($_POST['state']),
        'city'=> cleanInputs($_POST['location_name']),
        'isAdmin'=> 1,
        'business_location_type'=> cleanInputs($_POST['business_location_type']),
    ];

    if($_POST['community_type'] != 0){
        $apiDataU['community'] = implode(',',$_POST['community']);
    }

    
    $apiDataU['status'] = 1;
    $apiDataU['type'] = 0;

    if(isset($_POST['user_id']) && !empty($_POST['user_id'])){
        $apiDataU['user_id'] = cleanInputs($_POST['user_id']);
    }else{
        $apiDataU['user_id'] = ($_SESSION['hm_auth_data']) ? $_SESSION['hm_auth_data']['id'] : 12;
    }
    // category
    if(isset($_POST['post_category_master']) && !empty($_POST['post_category_master'])){
      // Create new category data
        $newCategory = [
            "id" => $_POST['post_category_master'],
            "category" => '',
            "subcategory" => ''
        ];
        // Append new category
        $existingCategories[] = $newCategory;

        // Re-encode the array as JSON
        $apiDataU['categoryArray'] = json_encode($existingCategories);
    }
    
    // company branch
    $newBranch = [
        "name" => $apiDataU['city'],
        "phone_numbers" => $apiDataU['phone'],
        "state" => $apiDataU['state'],
        "city" => $apiDataU['city'],
        "zipcode" => "",
        "latitude" => $apiDataU['latitude'],
        "longitude" => $apiDataU['longitude'],
        "address" => $apiDataU['address']
    ];
    $existingBranches[] = $newBranch;
    $apiDataU['company_branches'] = json_encode($existingBranches);

 
    $apiDataU['post_locations'] = [];
    if(!empty($apiDataU['latitude']) && !empty($apiDataU['longitude'])){
        $apiDataU['post_locations'][] = array('latitude' => $apiDataU['latitude'], 'longitude' => $apiDataU['longitude'], 'community_id' => 0);
        $apiDataU['post_locations'] = json_encode($apiDataU['post_locations']);
    }

    // Check if a file was uploaded
    $tempFilePath = "";
    if(isset($_POST['cropped_image'])) {
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

    

    $apiDataU['community'] = ltrim($apiDataU['community'], ',');
    //   dump($apiDataU);
    $response = sendCurlRequest(BASE_URL.'/create-post', 'POST', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'posts.php'</script>";
    }else{
        $err = 1;
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'posts.php'</script>";
    }

}

$title = "Posts";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}

include('pages/posts/list.html');