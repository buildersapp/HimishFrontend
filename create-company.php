<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$current_page = basename($_SERVER['PHP_SELF']);
$apiData = [];
$companyId = isset($_GET['id']) ? (int) base64_decode($_GET['id']) : 0;

$redirectPath = isset($_GET['redirect_path']) ? $_GET['redirect_path'] : '';

if(isset($_GET['act']) && $_GET['act']=="nauth"){
    include_once('includes/custom-functions.php');
    include_once('includes/web-helpers.php');
    include_once('admin/utils/helpers.php');
    if (!isset($_SESSION['hm_wb_auth_data']) && !isset($_SESSION['hm_wb_logged_in'])) {
        $query_data ='?user_id=12';
        $response = sendCurlRequest(BASE_URL.'/get-profile'.$query_data, 'GET', []);
        $decodedResponse = json_decode($response, true);
        $userDetails = $decodedResponse['body'];

        if (!empty($userDetails['python_api_url'])) {
            echo '<script>';
            echo 'localStorage.setItem("scanApiUrl", "' . htmlspecialchars($userDetails['python_api_url'], ENT_QUOTES) . '");';
            echo '</script>';
        }
    }else{
        echo "<script>alert('You are already logged In.')</script>";
        echo "<script>window.location.href = 'home.php'</script>";
    }
}else{
    include_once('includes/check-session.php');
}

// Add Company
if (isset($_POST['addCompany'])) {

    $companyData = [];
    $tempFiles = [];

    // Clean inputs
    $companyData = [
        'name' => cleanInputs($_POST['name']),
        'email' => cleanInputs($_POST['email']),
        'info' => cleanInputs($_POST['info']),
        'website' => cleanInputs($_POST['website']),
        'phone' => cleanInputs($_POST['phone']),
        'business_type' => cleanInputs($_POST['business_type']),
        'address' => cleanInputs($_POST['address']),
        'latitude' => cleanInputs($_POST['latitude']),
        'longitude' => cleanInputs($_POST['longitude']),
        'city' => cleanInputs($_POST['city']),
        'state' => cleanInputs($_POST['state']),
        'country_code' => cleanInputs($_POST['country_code']),
        'business_location_type' => cleanInputs($_POST['business_location_type'] ?? 'Local'),
        'is_worldwide' => cleanInputs($_POST['is_worldwide'] ?? 0),
        'selected_company_id' => cleanInputs($_POST['selected_company_id'] ?? 0),
        'keywords' => cleanInputs($_POST['keywords']),
        'role' => cleanInputs($_POST['role']),
        'verify_method' => cleanInputs($_POST['verify-method']),
        'verification_input' => cleanInputs($_POST['verification-input']),
        'hide_name' => cleanInputs($_POST['hide_name'] ?? 0),
        'company_id' => cleanInputs($_POST['selected_company_id'] ?? 0),
        'selectedServiceIdsHidden' => cleanInputs($_POST['selectedServiceIdsHidden'] ?? ''),
        'selectedProductIdsHidden' => cleanInputs($_POST['selectedProductIdsHidden'] ?? ''),
    ];

    // Image fields
    $imageFields = [
        'logo_cropped_image',
        'cover_cropped_image1',
        'cover_cropped_image2',
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

            if($imageField == 'logo_cropped_image') {
                $companyData['logo'] = new CURLFile($filePath, 'image/jpeg', $filename);
                unset($companyData['logo_cropped_image']); // Remove the original field
            } else if($imageField == 'cover_cropped_image1') {
                $companyData['company_cover_images1'] = new CURLFile($filePath, 'image/jpeg', $filename);
                unset($companyData['cover_cropped_image1']); // Remove the original field
            } else if($imageField == 'cover_cropped_image2') {
                $companyData['company_cover_images2'] = new CURLFile($filePath, 'image/jpeg', $filename);
                unset($companyData['cover_cropped_image2']); // Remove the original field
            } else if($imageField == 'listing_cropped_image1') {
                $companyData['company_listing_images1'] = new CURLFile($filePath, 'image/jpeg', $filename);
                unset($companyData['listing_cropped_image1']); // Remove the original field
            } else if($imageField == 'listing_cropped_image2') {
                $companyData['company_listing_images2'] = new CURLFile($filePath, 'image/jpeg', $filename);
                unset($companyData['listing_cropped_image2']); // Remove the original field
            } else if($imageField == 'listing_cropped_image3') {
                $companyData['company_listing_images3'] = new CURLFile($filePath, 'image/jpeg', $filename);
                unset($companyData['listing_cropped_image3']); // Remove the original field
            } else {
                $companyData[$imageField] = $filename; // Store other image filenames
            }
        }
    }

    // Decode company branches
    if (!empty($_POST['company_branches_array'])) {
        $companyBranchesJson = json_decode($_POST['company_branches_array'], true);
        if(count($companyBranchesJson) > 0){
            $companyData['company_branches'] = $_POST['company_branches_array']; // JSON string
        }else{
            $companyData['company_branches'] = json_encode([[
                'address' => $companyData['address'],
                'latitude' => $companyData['latitude'],
                'longitude' => $companyData['longitude'],
                'city' => $companyData['city'],
                'state' => $companyData['state'],
                'country_code' => $companyData['country_code'],
                'phone_numbers' => $companyData['phone']
            ]], JSON_UNESCAPED_UNICODE);
        }
    }

    // Decode service areas
    if (!empty($_POST['company_service_areas_array'])) {
        $companyServiceJson = json_decode($_POST['company_service_areas_array'], true);
        if(count($companyServiceJson)>0){
            $companyData['company_service_areas'] = $_POST['company_service_areas_array']; // JSON string
        }
        
        if(count($companyBranchesJson) == 0){
            $companyData['company_branches'] = $_POST['company_service_areas_array'];
        }
    }

    // Products and Services
    $serviceIds = $companyData['selectedServiceIdsHidden'] ?? '';
    $productIds = $companyData['selectedProductIdsHidden'] ?? '';

    if (!empty($serviceIds) || !empty($productIds)) {
        $serviceArray = array_filter(array_map('trim', explode(',', $serviceIds)));
        $productArray = array_filter(array_map('trim', explode(',', $productIds)));

        $categoryArray = array_unique(array_merge($serviceArray, $productArray));
        $companyData['category_ids'] = implode(',', $categoryArray);
    }
    unset($companyData['selectedServiceIdsHidden'], $companyData['selectedProductIdsHidden']);

    // Send to API
    $response = sendCurlRequest(BASE_URL . '/create-company', 'POST', $companyData, [], true, $userDetails['authorization']);
    $decodedResponse = json_decode($response, true);

    // Clean up temp files
    foreach ($tempFiles as $temp) {
        if (file_exists($temp)) {
            unlink($temp);
        }
    }

    if ($decodedResponse['success']) {
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        if ($redirectPath) {
            echo "<script>window.location.href = 'create-ad.php'</script>";
        }
        else {
            if (isset($decodedResponse['body']['id']) && !empty($decodedResponse['body']['id'])) {
                echo "<script>window.location.href = 'company-details.php?id=" . base64_encode($decodedResponse['body']['id']) . "';</script>";
            } else {
                echo "<script>window.location.href = 'companies.php';</script>";
            }
        }
    } else {
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'create-company.php'</script>";
    }
}

// Update Company
if (isset($_POST['updateCompany'])) {

    $companyData = [];
    $tempFiles = [];

    // Clean inputs
    $companyData = [
        'company_id' => cleanInputs($_POST['company_id']),
        'name' => cleanInputs($_POST['name']),
        'email' => cleanInputs($_POST['email']),
        'info' => cleanInputs($_POST['info']),
        'website' => cleanInputs($_POST['website']),
        'phone' => cleanInputs($_POST['phone']),
        'business_type' => cleanInputs($_POST['business_type']),
        'address' => cleanInputs($_POST['address']),
        'latitude' => cleanInputs($_POST['latitude']),
        'longitude' => cleanInputs($_POST['longitude']),
        'city' => cleanInputs($_POST['city']),
        'state' => cleanInputs($_POST['state']),
        'country_code' => cleanInputs($_POST['country_code']),
        'business_location_type' => cleanInputs($_POST['business_location_type'] ?? 'Local'),
        'is_worldwide' => cleanInputs($_POST['is_worldwide'] ?? 0),
        'keywords' => cleanInputs($_POST['keywords']),
        'role' => cleanInputs($_POST['role']),
        'verify_method' => cleanInputs($_POST['verify-method']),
        'verification_input' => cleanInputs($_POST['verification-input']),
        'hide_name' => cleanInputs($_POST['hide_name'] ?? 0),
        'selectedServiceIdsHidden' => cleanInputs($_POST['selectedServiceIdsHidden'] ?? ''),
        'selectedProductIdsHidden' => cleanInputs($_POST['selectedProductIdsHidden'] ?? ''),
    ];

    // Image fields
    $imageFields = [
        'logo_cropped_image',
        'cover_cropped_image1',
        'cover_cropped_image2',
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

            if($imageField == 'logo_cropped_image') {
                $companyData['logo'] = new CURLFile($filePath, 'image/jpeg', $filename);
                unset($companyData['logo_cropped_image']); // Remove the original field
            } else if($imageField == 'cover_cropped_image1') {
                $companyData['company_cover_images1'] = new CURLFile($filePath, 'image/jpeg', $filename);
                unset($companyData['cover_cropped_image1']); // Remove the original field
            } else if($imageField == 'cover_cropped_image2') {
                $companyData['company_cover_images2'] = new CURLFile($filePath, 'image/jpeg', $filename);
                unset($companyData['cover_cropped_image2']); // Remove the original field
            } else if($imageField == 'listing_cropped_image1') {
                $companyData['company_listing_images1'] = new CURLFile($filePath, 'image/jpeg', $filename);
                unset($companyData['listing_cropped_image1']); // Remove the original field
            } else if($imageField == 'listing_cropped_image2') {
                $companyData['company_listing_images2'] = new CURLFile($filePath, 'image/jpeg', $filename);
                unset($companyData['listing_cropped_image2']); // Remove the original field
            } else if($imageField == 'listing_cropped_image3') {
                $companyData['company_listing_images3'] = new CURLFile($filePath, 'image/jpeg', $filename);
                unset($companyData['listing_cropped_image3']); // Remove the original field
            } else {
                $companyData[$imageField] = $filename; // Store other image filenames
            }
        }
    }

    // Decode company branches
    if (!empty($_POST['company_branches_array'])) {
        $companyData['company_branches'] = json_decode($_POST['company_branches_array'],true); // JSON string
        if(count($companyData['company_branches']) == 0){
            $companyData['company_branches'][0]['address'] = $companyData['address'];
            $companyData['company_branches'][0]['latitude'] = $companyData['latitude'];
            $companyData['company_branches'][0]['longitude'] = $companyData['longitude'];
            $companyData['company_branches'][0]['city'] = $companyData['city'];
            $companyData['company_branches'][0]['state'] = $companyData['state'];
            $companyData['company_branches'][0]['country_code'] = $companyData['country_code'];
            $companyData['company_branches'][0]['phone_numbers'] = $companyData['phone'];
        }

        $companyData['company_branches'] = json_encode($companyData['company_branches'], JSON_UNESCAPED_UNICODE);
    }

    // Decode service areas
    if (!empty($_POST['company_service_areas_array'])) {
        $companyData['company_service_areas'] = json_decode($_POST['company_service_areas_array'],true); // JSON string
        $companyData['company_service_areas'] = json_encode($companyData['company_service_areas'], JSON_UNESCAPED_UNICODE);
    }

    // Products and Services
    $serviceIds = $companyData['selectedServiceIdsHidden'] ?? '';
    $productIds = $companyData['selectedProductIdsHidden'] ?? '';

    if (!empty($serviceIds) || !empty($productIds)) {
        $serviceArray = array_filter(array_map('trim', explode(',', $serviceIds)));
        $productArray = array_filter(array_map('trim', explode(',', $productIds)));

        $categoryArray = array_unique(array_merge($serviceArray, $productArray));
        $companyData['category_ids'] = implode(',', $categoryArray);
    }
    unset($companyData['selectedServiceIdsHidden'], $companyData['selectedProductIdsHidden']);

    // Send to API
    $response = sendCurlRequest(BASE_URL . '/update-company', 'PUT', $companyData, [], true, $userDetails['authorization']);
    $decodedResponse = json_decode($response, true);

    // Clean up temp files
    foreach ($tempFiles as $temp) {
        if (file_exists($temp)) {
            unlink($temp);
        }
    }

    if ($decodedResponse['success']) {
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'companies-profile.php?id=".base64_encode($userDetails['id'])."'</script>";
    } else {
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'create-company.php?id=".base64_encode($companyData['company_id'])."'</script>";
    }
}

// get edit company data
$selectedPAndS = [];
$selectedServiceIds = [];
$selectedServiceIdsHd = [];
$selectedProductIds = [];
$selectedProductIdsHd = [];
$coverImages = [];
$listingImages = [];

if($companyId > 0){
    $apiData = [
        'company_id' => $companyId
    ];

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/get-company', 'GET', $apiData);
    $responseDecoded = json_decode($response,true);

    // Decode the response (assuming it's JSON)
    $companyDataEd = $responseDecoded['body'];

    if (count($companyDataEd)) {
        $companyDataEd = $companyDataEd[0];

        if (!empty($companyDataEd['company_cover_images'])) {
            foreach ($companyDataEd['company_cover_images'] as $img) {
                if ($img['img_type'] == 0) {
                    $coverImages[] = $img;
                } elseif ($img['img_type'] == 1) {
                    $listingImages[] = $img;
                }
            }
        }

        if (!empty($companyDataEd['company_services_and_products'])) {
            foreach ($companyDataEd['company_services_and_products'] as $item) {
                if (!empty($item['parentCategory'])) {
                    $mainParentId = getRootParentCategoryId($item['parentCategory']);
                    $currentCategoryId = $item['parentCategory']['parent_id'] ?? null;
                    $type = $item['parentCategory']['type'] ?? null;

                    if($type == 1){
                        $selectedServiceIds[] = $item['parentCategory']['parent_id'];
                        $selectedServiceIdsHd[] = $item['category_id'];
                    } elseif($type == 2){
                        $selectedProductIds[] = $item['parentCategory']['parent_id'];
                        $selectedProductIdsHd[] = $item['category_id'];
                    }

                    $selectedPAndS[] = [
                        'type' => $type,
                        'category_id' => $currentCategoryId,
                        'main_parent' => $mainParentId
                    ];
                }
            }
        }
    }
}

function getRootParentCategoryId($category) {
    while (!empty($category['parentCategory'])) {
        $category = $category['parentCategory'];
    }
    return $category['id'] ?? null;
}

//dump($companyDataEd);
$title = ($companyId > 0) ? "Update Company" : "Create Company";
include('pages/companies/create.html');
?>
