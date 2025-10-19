<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Ad', 'url' => 'ads.php']
];

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

if(isset($_POST['addAd'])){
    // dump($_SESSION);
    // dump($_POST);
    $today = date("d/m/Y"); // Get today's date in the same format

    
    $apiDataU = [
        'title'         => cleanInputs($_POST['title']),
        'address'       => cleanInputs($_POST['address']),
        'latitude'      => cleanInputs($_POST['latitude']),
        'longitude'     => cleanInputs($_POST['longitude']),
        'company_id'     => cleanInputs($_POST['company_id']),
        'service'     => cleanInputs($_POST['service']),
        'community_type'=> cleanInputs($_POST['community_type']),
        'start_date' => date("m/d/Y", strtotime(cleanInputs($_POST['start_date']))),
        'end_date' => date("m/d/Y", strtotime(cleanInputs($_POST['end_date']))),
        'payment'        => 1,
        'button_type'        => 'email',
    ];

    if($_POST['community_type'] != 0){
        $apiDataU['community'] = implode(',',$_POST['community']);
    }

    $status = ($apiDataU['start_date'] === $today) ? 1 : 0;
    $expire_date = strtotime(cleanInputs($_POST['end_date'])); // Convert to Unix timestamp (10-digit)

    $apiDataU['status'] = $status;
    $apiDataU['expire_date'] = $expire_date;

    // if(isset($_POST['user_id']) && !empty($_POST['user_id'])){
    //     $apiDataU['user_id'] = cleanInputs($_POST['user_id']);
    // }else{
    //     $apiDataU['user_id'] = ($_SESSION['hm_auth_data']) ? $_SESSION['hm_auth_data']['id'] : 12;
    // }

    $apiDataU['user_id'] = ($_SESSION['hm_auth_data']) ? $_SESSION['hm_auth_data']['id'] : 12;

    if (!empty($_SESSION['hm_auth_data']) && isset($_SESSION['hm_auth_data']['email'])) {
        $apiDataU['style_type'] = json_encode([
            "to" => $_SESSION['hm_auth_data']['email'],
            "subject" => "Test",
            "message" => "Test"
        ]);
    } else {
        $apiDataU['style_type'] = '';
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

        $apiDataU['images'] = new CURLFile($tempFilePath, 'image/jpeg', 'cropped_image.jpg');
    
    }else{
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $imageFilePath = $_FILES['image']['tmp_name'];
            $apiDataU['images'] = new CURLFile($imageFilePath, $_FILES['image']['type'], $_FILES['image']['name']);
        }
    }

    

    $apiDataU['community'] = ltrim($apiDataU['community'], ',');
    // dump($apiDataU);
    $response = sendCurlRequest(BASE_URL.'/create-ads-admin', 'POST', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'ads.php'</script>";
    }else{
        $err = 1;
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'ads.php'</script>";
    }

}

$title = "Ads";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}

include('pages/ads/list.html');