<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once('includes/check-session.php');

$myCommunityLocations = [];
$uniqueKeysMy = [];
$allCommunityLocations = [];
$uniqueKeysAll = [];
$apiData = [];
$adsData = [];
$defaultType = '';
$hasLink = 0;
$linkData_Meta = (object)[];
$selectedLocation_Val = "";
$selectedCommunity_Val = "";

$responseSettings = sendCurlRequest(BASE_URL.'/get-setting', 'GET', []);
$decodedResponseSettings = json_decode($responseSettings, true);
$generalSettings = $decodedResponseSettings['body'];
$ads_Id = isset($_GET['ad_id']) ? base64_decode($_GET['ad_id']) : 0;
$post_Id = isset($_GET['post_id']) ? base64_decode($_GET['post_id']) : 0;
$link_Id = isset($_GET['link_id']) ? base64_decode($_GET['link_id']) : 0;
$editMode = isset($_GET['edit']) ? 1 : 0;
$numberOfFreeCommunity = !empty($generalSettings['free_community']) ? $generalSettings['free_community'] : 0;
$defaultCommunityPrice = !empty($generalSettings['default_community_price']) ? $generalSettings['default_community_price'] : 0;
$discountMyCommunity = !empty($generalSettings['discount_community']) ? $generalSettings['discount_community'] : 0;
$defaultAllCommunityPrice = !empty($generalSettings['all_community_selection_price']) ? $generalSettings['all_community_selection_price'] : 0;;

if($ads_Id > 0){
    $responseAds = sendCurlRequest(BASE_URL.'/get-ads?ads_id='.$ads_Id, 'GET', []);
    $decodedResponseAds = json_decode($responseAds, true);
    $adsData = $decodedResponseAds['body'];
    $companyIds = array_column($decodedResponseAds['body'], 'company_id');
    $defaultType =  $adsData[0]['sponser_ads_images'][0]['type'] == 0 ? 'image' : 'video';
};

if($post_Id > 0){
    $responseAds = sendCurlRequest(BASE_URL.'/get-posts?post_id='.$post_Id, 'GET', []);
    $decodedResponseAds = json_decode($responseAds, true);
    $adsData = $decodedResponseAds['body'];
    $companyIds = array_column($decodedResponseAds['body'], 'company_id');
    $defaultType =  'image'; // Default to image for posts
};

if($link_Id != ''){
    $apiDataLD = ['link_id' => $link_Id];
    $responseLD = sendCurlRequest(BASE_URL . '/get-link-by-linkId', 'GET', $apiDataLD);
    $linkData = json_decode($responseLD, true);
    if($linkData['success'] == '1'){
        $hasLink = 1;
        $linkData_Meta = $linkData['body'];
        $linkType = $linkData_Meta['type'];

        $selectedLocation_ValArr = [[
            'community_id' => $linkData_Meta['community']['id'],
            'address' => $linkData_Meta['community']['address'],
            'latitude' => $linkData_Meta['community']['latitude'],
            'country_code' => $linkData_Meta['community']['country_code'],
            'longitude' => $linkData_Meta['community']['longitude'],
            'name' => $linkData_Meta['community']['name'],
            'description' => $linkData_Meta['community']['description'],
            'state' => $linkData_Meta['community']['state'],
            'city' => $linkData_Meta['community']['city'],
        ]];

        $selectedCommunity_ValArr = [[
            'id' => $linkData_Meta['community']['id'],
            'address' => $linkData_Meta['community']['address'],
            'latitude' => $linkData_Meta['community']['latitude'],
            'country_code' => $linkData_Meta['community']['country_code'],
            'longitude' => $linkData_Meta['community']['longitude'],
            'name' => $linkData_Meta['community']['name'],
            'description' => $linkData_Meta['community']['description'],
            'state' => $linkData_Meta['community']['state'],
            'city' => $linkData_Meta['community']['city'],
        ]];

        $selectedLocation_Val = htmlspecialchars(json_encode($selectedLocation_ValArr));
        $selectedCommunity_Val = htmlspecialchars(json_encode($selectedCommunity_ValArr));
    }
};

//dump($selectedCommunity_Val);

// Create Ad
if (isset($_POST['createAd']) || isset($_POST['saveForLater'])) {
    $status = isset($_POST['createAd']) ? 1 : 2;

    $categoryArray = [];
    $ad_locations = [];

    $adsData = [
        'status'        => $status,
        'ad_type'       => cleanInputs($_POST['ad_type']),
        'company_id'    => cleanInputs($_POST['company_id']),
        'company_name'  => cleanInputs($_POST['display-company-name-hd']),
        'title'         => cleanInputs($_POST['title']),
        'start_date'    => cleanInputs($_POST['start_date']),
        'end_date'      => cleanInputs($_POST['end_date']),
        'amount'        => cleanInputs($_POST['sub_total']),
        'tax'           => cleanInputs($_POST['tax_amount']),
        'total_amount'  => cleanInputs($_POST['total_amount']),
        'total_days'    => cleanInputs($_POST['total_days']),
        'plan_id'       => cleanInputs($_POST['plan_id']),
        'post_id'       => cleanInputs($_POST['post_id']),
        'payment'       => cleanInputs($_POST['payment']),
        'is_manual'     => cleanInputs($_POST['is_manual']),
        'service'       => cleanInputs($_POST['keywords']),
        'community_type'=> cleanInputs($_POST['community_type']),
        'info_link'     => cleanInputs($_POST['info_link']),
        'button_type'   => cleanInputs($_POST['button_type_val']),
        'whatsapp_message'  => cleanInputs($_POST['whatsapp_message']),
        'whatsapp_phone'    => cleanInputs($_POST['whatsapp_phone']),
        'quote_link'        => cleanInputs($_POST['quote_link']),
        'email_sec'         => cleanInputs($_POST['email_sec']),
        'subject_sec'       => cleanInputs($_POST['subject_sec']),
        'message_sec'       => cleanInputs($_POST['message_sec']),
        'miles'             => isset($_POST['miles']) ? cleanInputs($_POST['miles']) : 30,
        'credit'            => isset($_POST['credit']) ? cleanInputs($_POST['credit']) : 0,
    ];

    if($hasLink == 1){
        $adsData['link_id'] = $_POST['link_id'];
    }

    if($adsData['credit'] > 0){
        $adsData['total_amount'] = $adsData['total_amount'] - $adsData['credit'];
        if($userDetails['wallet'] > $adsData['total_amount']){
            $adsData['total_amount'] = 0;
        }
    }

    // add ad_locations array
    if (isset($_POST['selected_locations']) && !empty($_POST['selected_locations'])) {
        $ads_locations = json_decode($_POST['selected_locations'],true);
        $adsData['location'] = $ads_locations[0]['city'] . ', ' . $ads_locations[0]['state'];
        $adsData['latitude'] = $ads_locations[0]['latitude'];
        $adsData['longitude'] = $ads_locations[0]['longitude'];
        $adsData['country_code'] = $ads_locations[0]['country_code'];
        $adsData['ads_locations'] = json_encode($ads_locations);
    }

    // Image & Video fields
    if($adsData['ad_type'] == 'image'){
        $imageFields = [
            'listing_cropped_image1',
            'listing_cropped_image2',
            'listing_cropped_image3'
        ];

        foreach ($imageFields as $imageField) {
            if (!empty($_POST[$imageField])) {
                $imageParts = explode(";base64,", $_POST[$imageField]);
                $imageBase64 = base64_decode($imageParts[1]);
                $tempDir = __DIR__ . '/assets/uploads';

                if (!is_dir($tempDir)) {
                    mkdir($tempDir, 0777, true);
                }

                $filename = $imageField . '_' . time() . '.jpg';
                $filePath = $tempDir . '/' . $filename;
                file_put_contents($filePath, $imageBase64);

                $tempFiles[] = $filePath;

                if($imageField == 'listing_cropped_image1') {
                    $adsData['image1'] = new CURLFile($filePath, 'image/jpeg', $filename);
                    unset($adsData['listing_cropped_image1']); // Remove the original field
                } else if($imageField == 'listing_cropped_image2') {
                    $adsData['image2'] = new CURLFile($filePath, 'image/jpeg', $filename);
                    unset($adsData['listing_cropped_image2']); // Remove the original field
                } else if($imageField == 'listing_cropped_image3') {
                    $adsData['image3'] = new CURLFile($filePath, 'image/jpeg', $filename);
                    unset($adsData['listing_cropped_image3']); // Remove the original field
                } else {
                    $adsData[$imageField] = $filename; // Store other image filenames
                }
            }
        }
    } else if($adsData['ad_type'] == 'video'){
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $imageFilePath = $_FILES['image']['tmp_name'];
            $adsData['images'] = new CURLFile($imageFilePath, $_FILES['image']['type'], $_FILES['image']['name']);
        }
    }

    // button type
    if($adsData['button_type'] == 'none'){
        $adsData['button_type'] = 'sms';
    } else if($adsData['button_type'] == 'link_info'){
        $adsData['button_type'] = 'info';
        $adsData['link'] = cleanInputs($_POST['info_link']);
        unset($adsData['info_link']); // Remove the original field
    } else if($adsData['button_type'] == 'whatsapp'){
        $adsData['button_type'] = 'whatsapp';
        $adsData['whatsapp_message'] = cleanInputs($_POST['whatsapp_message']);
        $adsData['whatsapp_phone'] = cleanInputs($_POST['whatsapp_phone']);
        $adsData['style_type'] = json_encode(['to' => $adsData['whatsapp_phone'], 'message' => $adsData['whatsapp_message']]);
        unset($adsData['whatsapp_message'], $adsData['whatsapp_phone']); // Remove the original fields
    } else if($adsData['button_type'] == 'quote'){
        $adsData['button_type'] = 'quote';
        $adsData['link'] = cleanInputs($_POST['quote_link']);
        unset($adsData['quote_link']); // Remove the original field
    } else if($adsData['button_type'] == 'emailSec'){
        $adsData['button_type'] = 'email';
        $adsData['email_sec'] = cleanInputs($_POST['email_sec']);
        $adsData['subject_sec'] = cleanInputs($_POST['subject_sec']);
        $adsData['message_sec'] = cleanInputs($_POST['message_sec']);
        $adsData['style_type'] = json_encode(['to' => $adsData['email_sec'], 'message' => $adsData['message_sec'], 'subject' => $adsData['subject_sec']]);
        unset($adsData['email_sec'], $adsData['subject_sec'], $adsData['message_sec']); // Remove the original fields
    }

    $response = sendCurlRequest(BASE_URL.'/create-ads', 'POST', $adsData, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        // Unlink temporary files after request
        foreach ($tempFiles as $temp) {
            if (file_exists($temp)) {
                unlink($temp);
            }
        }
        $paymentUrl = $decodedResponse['body']['url'];
        $adId = $decodedResponse['body']['id'];
        $productId = $decodedResponse['body']['product_id'];
        if($paymentUrl && $status == 1){
            echo "<script>window.location.href = '".$paymentUrl."'</script>";
        }else{
            echo "<script>window.location.href = 'home.php?ad_id=".base64_encode($adId)."&type=0&scrollTo=".$productId."'</script>";
        }
    }else{
        $err = 1;
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'create-ad.php'</script>";
    }
}

// Update Ad
if(isset($_POST['updateAd'])){

    $categoryArray = [];
    $ad_locations = [];

    $adsData = [
        'ads_id'         => cleanInputs($_POST['ad_id']),
        'ad_type'       => cleanInputs($_POST['ad_type']),
        'company_id'    => cleanInputs($_POST['company_id']),
        'company_name'  => cleanInputs($_POST['display-company-name-hd']),
        'title'         => cleanInputs($_POST['title']),
        'start_date'    => cleanInputs($_POST['start_date']),
        'end_date'      => cleanInputs($_POST['end_date']),
        'amount'        => cleanInputs($_POST['sub_total']),
        'tax'           => cleanInputs($_POST['tax_amount']),
        'total_amount'  => cleanInputs($_POST['total_amount']),
        'total_days'    => cleanInputs($_POST['total_days']),
        'plan_id'       => cleanInputs($_POST['plan_id']),
        'post_id'       => cleanInputs($_POST['post_id']),
        'status'        => 1,
        'payment'       => cleanInputs($_POST['payment']),
        'is_manual'     => cleanInputs($_POST['is_manual']),
        'service'       => cleanInputs($_POST['keywords']),
        'community_type'=> cleanInputs($_POST['community_type']),
        'info_link'     => cleanInputs($_POST['info_link']),
        'button_type'   => cleanInputs($_POST['button_type_val']),
        'whatsapp_message'  => cleanInputs($_POST['whatsapp_message']),
        'whatsapp_phone'    => cleanInputs($_POST['whatsapp_phone']),
        'quote_link'        => cleanInputs($_POST['quote_link']),
        'email_sec'         => cleanInputs($_POST['email_sec']),
        'subject_sec'       => cleanInputs($_POST['subject_sec']),
        'message_sec'       => cleanInputs($_POST['message_sec']),
        'miles'             => isset($_POST['miles']) ? cleanInputs($_POST['miles']) : 30,
        'credit'            => isset($_POST['credit']) ? cleanInputs($_POST['credit']) : 0,
    ];

    if($adsData['credit'] > 0){
        $adsData['total_amount'] = $adsData['total_amount'] - $adsData['credit'];
        if($userDetails['wallet'] > $adsData['total_amount']){
            $adsData['total_amount'] = 0;
        }
    }

    // add ad_locations array
    if (isset($_POST['selected_locations']) && !empty($_POST['selected_locations'])) {
        $ads_locations = json_decode($_POST['selected_locations'],true);
        $adsData['location'] = $ads_locations[0]['city'] . ', ' . $ads_locations[0]['state'];
        $adsData['latitude'] = $ads_locations[0]['latitude'];
        $adsData['longitude'] = $ads_locations[0]['longitude'];
        $adsData['ads_locations'] = json_encode($ads_locations);
    }

    // Image & Video fields
    if($adsData['ad_type'] == 'image'){
        $imageFields = [
            'listing_cropped_image1',
            'listing_cropped_image2',
            'listing_cropped_image3'
        ];

        foreach ($imageFields as $imageField) {
            if (!empty($_POST[$imageField])) {
                $imageParts = explode(";base64,", $_POST[$imageField]);
                $imageBase64 = base64_decode($imageParts[1]);
                $tempDir = __DIR__ . '/assets/uploads';

                if (!is_dir($tempDir)) {
                    mkdir($tempDir, 0777, true);
                }

                $filename = $imageField . '_' . time() . '.jpg';
                $filePath = $tempDir . '/' . $filename;
                file_put_contents($filePath, $imageBase64);

                $tempFiles[] = $filePath;

                if($imageField == 'listing_cropped_image1') {
                    $adsData['image1'] = new CURLFile($filePath, 'image/jpeg', $filename);
                    unset($adsData['listing_cropped_image1']); // Remove the original field
                } else if($imageField == 'listing_cropped_image2') {
                    $adsData['image2'] = new CURLFile($filePath, 'image/jpeg', $filename);
                    unset($adsData['listing_cropped_image2']); // Remove the original field
                } else if($imageField == 'listing_cropped_image3') {
                    $adsData['image3'] = new CURLFile($filePath, 'image/jpeg', $filename);
                    unset($adsData['listing_cropped_image3']); // Remove the original field
                } else {
                    $adsData[$imageField] = $filename; // Store other image filenames
                }
            }
        }
    } else if($adsData['ad_type'] == 'video'){
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $imageFilePath = $_FILES['image']['tmp_name'];
            $adsData['images'] = new CURLFile($imageFilePath, $_FILES['image']['type'], $_FILES['image']['name']);
        }
    }

    // button type
    if($adsData['button_type'] == 'none'){
        $adsData['button_type'] = 'sms';
    } else if($adsData['button_type'] == 'link_info'){
        $adsData['button_type'] = 'link';
        $adsData['link'] = cleanInputs($_POST['info_link']);
        unset($adsData['info_link']); // Remove the original field
    } else if($adsData['button_type'] == 'whatsapp'){
        $adsData['button_type'] = 'whatsapp';
        $adsData['whatsapp_message'] = cleanInputs($_POST['whatsapp_message']);
        $adsData['whatsapp_phone'] = cleanInputs($_POST['whatsapp_phone']);
        $adsData['style_type'] = json_encode(['to' => $adsData['whatsapp_phone'], 'message' => $adsData['whatsapp_message']]);
        unset($adsData['whatsapp_message'], $adsData['whatsapp_phone']); // Remove the original fields
    } else if($adsData['button_type'] == 'quote'){
        $adsData['button_type'] = 'quote';
        $adsData['link'] = cleanInputs($_POST['quote_link']);
        unset($adsData['quote_link']); // Remove the original field
    } else if($adsData['button_type'] == 'emailSec'){
        $adsData['button_type'] = 'email';
        $adsData['email_sec'] = cleanInputs($_POST['email_sec']);
        $adsData['subject_sec'] = cleanInputs($_POST['subject_sec']);
        $adsData['message_sec'] = cleanInputs($_POST['message_sec']);
        $adsData['style_type'] = json_encode(['to' => $adsData['email_sec'], 'message' => $adsData['message_sec'], 'subject' => $adsData['subject_sec']]);
        unset($adsData['email_sec'], $adsData['subject_sec'], $adsData['message_sec']); // Remove the original fields
    }

    if($adsData['total_amount'] ==  0){
        $adsData['payment'] = 1;
    }

    $response = sendCurlRequest(BASE_URL.'/update-ads', 'PUT', $adsData, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        // Unlink temporary files after request
        foreach ($tempFiles as $temp) {
            if (file_exists($temp)) {
                unlink($temp);
            }
        }

        if($adsData['total_amount'] > 0){
            // get payment link
            $responsePaymentLink = sendCurlRequest(BASE_URL.'/paymentUrlGet?ads_id='.$adsData['ads_id'], 'GET');
            $decodedResponsePaymentLink = json_decode($responsePaymentLink, true);

            if($decodedResponsePaymentLink['success']){
                $paymentUrl = $decodedResponsePaymentLink['body']['url'];
                if($paymentUrl && !$editMode){
                    echo "<script>window.location.href = '".$paymentUrl."'</script>";
                }else{
                    echo "<script>window.location.href = 'home.php'</script>";
                }
            }
        }else{
            echo "<script>window.location.href = 'home.php'</script>";
        }
    }else{
        $err = 1;
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'create-ad.php'</script>";
    }
}

$responsePlansI = sendCurlRequest(BASE_URL.'/getPlans', 'GET', ['type' => 1]);
$decodedResponsePlansI = json_decode($responsePlansI, true);
$imagePlans = $decodedResponsePlansI['body'];

$responsePlans = sendCurlRequest(BASE_URL.'/getPlans', 'GET', ['type' => 2]);
$decodedResponsePlans = json_decode($responsePlans, true);
$videoPlans = $decodedResponsePlans['body'];

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

/***
 * 
 *  START : Calculate My Community Price
 */
$myCommunities = array_filter($communities, function ($data) {
    return isset($data['is_selected']) &&
           $data['is_selected'] == 1;
});

usort($myCommunities, function ($a, $b) {
    return $a['price'] <=> $b['price'];
});

// Loop and apply free slots
$totalMyCommunityPrice = 0;
foreach ($myCommunities as $index => $community) {
    if ($community['price'] > 0) {
        $price = (float) $community['price'];
    } else {
        $price = $defaultCommunityPrice;
    }
    $totalMyCommunityPrice += $price;
}
/***
 * 
 *  END My Community Price
 */

// Step 4: Process
addUniqueLocations($my_communities_locations, $uniqueKeysMy, $myCommunityLocations, $defaultCommunityPrice);
addUniqueLocations($all_communities_locations, $uniqueKeysAll, $allCommunityLocations, $defaultCommunityPrice);

$title = ($ads_Id > 0) ? "Update Ad" : "Create Ad";
include('pages/create-ad.html');
?>
