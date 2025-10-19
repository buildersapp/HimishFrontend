<?php
include_once('utils/helpers.php');


// Form submission handler
if (isset($_POST['createWebAd'])) {
    $errors = [];

    //dump($_POST);

    // Validations
    $companyId      = cleanInputs($_POST['company_id'] ?? '');
    $position       = cleanInputs($_POST['box'] ?? '');
    $showCompany    = isset($_POST['show_company']) ? 1 : 0;
    $url            = cleanInputs($_POST['url'] ?? '');
    $location       = cleanInputs($_POST['location'] ?? '');
    $latitude       = cleanInputs($_POST['latitude'] ?? '');
    $longitude      = cleanInputs($_POST['longitude'] ?? '');
    $city           = cleanInputs($_POST['city'] ?? '');
    $state          = cleanInputs($_POST['state'] ?? '');
    $radius          = cleanInputs($_POST['radius'] ?? '');
    $country_code   = cleanInputs($_POST['country_code'] ?? 'US');
    $range          = cleanInputs($_POST['range'] ?? '');
    $dateRange      = cleanInputs($_POST['datefilter'] ?? '');
    $amount         = cleanInputs($_POST['sub_total'] ?? 0);
    $tax            = cleanInputs($_POST['tax_amount'] ?? 0);
    $totalAmount    = cleanInputs($_POST['total_amount'] ?? 0);

    // Parse date range
    $dates = explode(' - ', $dateRange);
    $startDate = $dates[0] ?? '';
    $endDate = $dates[1] ?? '';

    if (!$companyId) $errors[] = 'Company is required';
    if (!$position) $errors[] = 'Position is required';
    if (!$url) $errors[] = 'URL is required';
    if (!$location) $errors[] = 'Location is required';
    if (!$dateRange || !$startDate || !$endDate) $errors[] = 'Date range is invalid';
    if (!$amount) $errors[] = 'Amount is required';

    // Handle file upload
    $imageData = $_FILES['image'] ?? null;
    if (!$imageData || $imageData['error'] !== 0) {
        $errors[] = 'Image/Video upload failed';
    }

    $fileFields = [];
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

        if (!file_exists($tempFilePath) || filesize($tempFilePath) <= 0) {
            // Optionally delete empty file
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            $errors[] = "Uploaded image is empty or invalid.";
        }

        $fileFields['image'] = new CURLFile($tempFilePath, 'image/jpeg', 'cropped_image.jpg');
    
    }else{
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $imageFilePath = $_FILES['image']['tmp_name'];
            $fileFields['image'] = new CURLFile($imageFilePath, $_FILES['image']['type'], $_FILES['image']['name']);
        }
    }

    //dump($fileFields);

    if (empty($errors)) {
        $postData = [
            [
                "company_id"    => $companyId,
                "position"      => $position,
                "show_company"  => $showCompany,
                "url"           => $url,
                "start_date"    => $startDate,
                "end_date"      => $endDate,
                "city"          => $city,
                "state"         => $state,
                "latitude"      => $latitude,
                "longitude"     => $longitude,
                "address"       => $location,
                "country_code"  => $country_code,
                "amount"        => $amount,
                "total_amount"  => $totalAmount,
                "tax"           => $tax,
                "miles"        => $radius,
            ]
        ];

        if($fileFields){
            $responseImg = sendCurlRequest(BASE_URL.'/upload-media', 'POST', $fileFields, [], true);
            $decodedResponseImg = json_decode($responseImg, true);
            if($decodedResponseImg['success']){
                $postData[0]['media'] = $decodedResponseImg['body']['image'];

                $extension = strtolower(pathinfo($postData[0]['media'], PATHINFO_EXTENSION));

                // Define image extensions
                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

                // Set media_type based on extension
                if (in_array($extension, $imageExtensions)) {
                    $postData[0]['media_type'] = 0; // Image
                } else {
                    $postData[0]['media_type'] = 1; // Video
                }
            }
        }

        //  dump($postData);

        $response = sendCurlRequest(BASE_URL.'/create-web-ad', 'POST', ['json_data' => json_encode($postData)], [], true);
        $decodedResponse = json_decode($response, true);

        if ($decodedResponse['success']) {
            setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
            echo "<script>window.location.href = 'web-feed-ads.php';</script>";
            exit;
        } else {
            $err = 1;
            $errorMsg = $decodedResponse['message'] ?? 'Something went wrong.';
            setcookie('errorMsg', $errorMsg, time() + 5, "/");
            echo "<script>window.location.href = 'web-feed-ads.php';</script>";
        }
    }
}

if (isset($_POST['updateWebAd'])) {
    $errors = [];

    // Validations
    $adId      = cleanInputs($_POST['ad_id'] ?? '');
    $companyId      = cleanInputs($_POST['company_id'] ?? '');
    $position       = cleanInputs($_POST['box'] ?? '');
    $showCompany    = isset($_POST['show_company']) ? 1 : 0;
    $url            = cleanInputs($_POST['url'] ?? '');
    $location       = cleanInputs($_POST['location'] ?? '');
    $latitude       = cleanInputs($_POST['latitude'] ?? '');
    $longitude      = cleanInputs($_POST['longitude'] ?? '');
    $city           = cleanInputs($_POST['city'] ?? '');
    $state          = cleanInputs($_POST['state'] ?? '');
    $radius          = cleanInputs($_POST['radius'] ?? '');
    $country_code   = cleanInputs($_POST['country_code'] ?? 'US');
    $range          = cleanInputs($_POST['range'] ?? '');
    $dateRange      = cleanInputs($_POST['datefilter'] ?? '');
    $amount         = cleanInputs($_POST['sub_total'] ?? 0);
    $tax            = cleanInputs($_POST['tax_amount'] ?? 0);
    $old_img_path    = cleanInputs($_POST['old_img_path'] ?? '');
    $totalAmount    = cleanInputs($_POST['total_amount'] ?? 0);

    // Parse date range
    $dates = explode(' - ', $dateRange);
    $startDate = $dates[0] ?? '';
    $endDate = $dates[1] ?? '';

    if (!$companyId) $errors[] = 'Company is required';
    if (!$position) $errors[] = 'Position is required';
    if (!$url) $errors[] = 'URL is required';
    if (!$location) $errors[] = 'Location is required';
    if (!$dateRange || !$startDate || !$endDate) $errors[] = 'Date range is invalid';
    if (!$amount) $errors[] = 'Amount is required';

    $fileFields = [];
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

        $fileFields['image'] = new CURLFile($tempFilePath, 'image/jpeg', 'cropped_image.jpg');
    
    }else{
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $imageFilePath = $_FILES['image']['tmp_name'];
            $fileFields['image'] = new CURLFile($imageFilePath, $_FILES['image']['type'], $_FILES['image']['name']);
        }
    }

    if (empty($errors)) {
        $postData = [
            [
                "ad_id"         => $adId,
                "company_id"    => $companyId,
                "position"      => $position,
                "show_company"  => $showCompany,
                "url"           => $url,
                "start_date"    => $startDate,
                "end_date"      => $endDate,
                "city"          => $city,
                "state"         => $state,
                "latitude"      => $latitude,
                "longitude"     => $longitude,
                "address"       => $location,
                "country_code"  => $country_code,
                "amount"        => $amount,
                "total_amount"  => $totalAmount,
                "tax"           => $tax,
                "miles"        => $radius,
            ]
        ];

        if($fileFields){
            $responseImg = sendCurlRequest(BASE_URL.'/upload-media', 'POST', $fileFields, [], true);
            $decodedResponseImg = json_decode($responseImg, true);
            if($decodedResponseImg['success']){
                $postData[0]['media'] = $decodedResponseImg['body']['image'];

                $extension = strtolower(pathinfo($postData[0]['media'], PATHINFO_EXTENSION));

                // Define image extensions
                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

                // Set media_type based on extension
                if (in_array($extension, $imageExtensions)) {
                    $postData[0]['media_type'] = 0; // Image
                } else {
                    $postData[0]['media_type'] = 1; // Video
                }
            }
        }else{
            $extension = strtolower(pathinfo($old_img_path, PATHINFO_EXTENSION));

            // Define image extensions
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

            // Set media_type based on extension
            if (in_array($extension, $imageExtensions)) {
                $postData[0]['media_type'] = 0; // Image
            } else {
                $postData[0]['media_type'] = 1; // Video
            }

            $postData[0]['old_img_path'] = $old_img_path;
        }

        $response = sendCurlRequest(BASE_URL.'/update-web-ad', 'PUT', $postData[0], [], true);
        $decodedResponse = json_decode($response, true);

        if ($decodedResponse['success']) {
            setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
            echo "<script>window.location.href = 'web-feed-ads.php';</script>";
            exit;
        } else {
            $err = 1;
            $errorMsg = $decodedResponse['message'] ?? 'Something went wrong.';
            setcookie('errorMsg', $errorMsg, time() + 5, "/");
            echo "<script>window.location.href = 'web-feed-ads.php';</script>";
        }
    }
}

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Feed Page Ads', 'url' => 'web-feed-ads.php']
];
$ad_id = isset($_GET['id']) ? base64_decode($_GET['id']) : 0;
$editMode = $ad_id > 0;

$webFeedAdData = [];
if($ad_id > 0) {
    $id = base64_decode($_GET['id']);
    $responseGetWebAd = sendCurlRequest(BASE_URL.'/get-web-ads?is_admin=1&page=1&limit=10&ad_id='.$id.'', 'GET', []);
    $decodedResponseGetWebAd = json_decode($responseGetWebAd, true);
    $webFeedAdData = $decodedResponseGetWebAd['body'];
}
$ad = $editMode ? $webFeedAdData[0] : null;

$response = sendCurlRequest(BASE_URL.'/get-company?page=1&limit=1000000', 'GET', []);
$decodedResponse = json_decode($response, true);
$companies = $decodedResponse['body'];

$responsePlans = sendCurlRequest(BASE_URL.'/getPlans', 'GET', ['type' => 4]);
$decodedResponsePlans = json_decode($responsePlans, true);
$plans = $decodedResponsePlans['body'];

$title = "Web Feed Ads";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}

include('pages/feedAds/list.html');