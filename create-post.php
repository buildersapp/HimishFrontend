<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$current_page = basename($_SERVER['PHP_SELF']);

$myCommunityLocations = [];
$uniqueKeysMy = [];
$allCommunityLocations = [];
$uniqueKeysAll = [];
$apiData = [];
$from = isset($_GET['from']) ? 1 : 0; 
$rd_srep = isset($_GET['rd_srep']) ? 1 : 0; 
$sp_auth = "";

if(isset($_GET['act']) && $_GET['act']=="nauth"){
    include_once('includes/custom-functions.php');
    include_once('includes/web-helpers.php');
    include_once('admin/utils/helpers.php');
    if (
    (!isset($_SESSION['hm_wb_auth_data']) && !isset($_SESSION['hm_wb_logged_in']))
    || (isset($_SESSION['hm_auth_data']) && isset($_SESSION['hm_wb_logged_in']))
    ) {

        $repId = 12;
        if(isset($_GET['repID'])){
            $repId = base64_decode($_GET['repID']);
        }

        $query_data = '?user_id='.$repId.'';
        $response = sendCurlRequest(BASE_URL.'/get-profile'.$query_data, 'GET', []);
        $decodedResponse = json_decode($response, true);
        $userDetails = $decodedResponse['body'];
        $sp_auth = $userDetails['authorization'];
        $posterName = $userDetails['name'];

        if (!empty($userDetails['python_api_url'])) {
            echo '<script>';
            echo 'localStorage.setItem("scanApiUrl", "' . htmlspecialchars($userDetails['python_api_url'], ENT_QUOTES) . '");';
            echo '</script>';
        }
    } else {
        echo "<script>alert('You are already logged In.')</script>";
        echo "<script>window.location.href = 'home.php'</script>";
    }
}else{
    include_once('includes/check-session.php');
    $posterName = $userDetails['name'];
}

if(isset($_POST['addPost'])){

    $categoryArray = [];
    $post_locations = [];
    $tempFiles = [];

    $companyEmails = (extractEmailsFromJson($_POST['scanResults']));

    $apiDataU = [
        'company'       => cleanInputs($_POST['company']),
        'title'         => cleanInputs($_POST['title']),
        'service'       => cleanInputs($_POST['service']),
        'keywords'      => cleanInputs($_POST['keywords']),
        'phone'         => cleanInputs($_POST['phone']),
        'address'       => cleanInputs($_POST['address']),
        'latitude'      => cleanInputs($_POST['latitude']),
        'longitude'     => cleanInputs($_POST['longitude']),
        'city'          => cleanInputs($_POST['city']),
        'state'         => cleanInputs($_POST['state']),
        'expire_date'   => cleanInputs($_POST['expire_date'] ? strtotime(date('Y-m-d',strtotime($_POST['expire_date']))) : 0),
        //'community_type'=> cleanInputs($_POST['community_type'] ?? 0),
        'community_type'=> 0,
        'type'          => 0,
        'community'          => '',
        'is_worldwide'=> cleanInputs($_POST['is_worldwide'] ?? 0),
        'company_id'=> cleanInputs($_POST['selected_company_id'] ?? 0),
        'scanImage'=> cleanInputs($_POST['scanImage'] ?? ''),
        'business_location_type'=> cleanInputs($_POST['business_location_type'] ?? 'Local'),
        'new_community'=> cleanInputs($_POST['new_community'] ?? 0),
        'is_owner'=> cleanInputs($_POST['is_owner'] ?? 0),
        'user_type'=> cleanInputs($_POST['user_type'] ?? 0),
        'company_branches_array'=> cleanInputs($_POST['company_branches_array'] ?? 0),
        'company_service_areas_array'=> cleanInputs($_POST['company_service_areas_array'] ?? 0),
        'post_id' => 0,
        'sp_auth' => $_POST['sp_auth']
    ];

    if(!empty($apiData['sp_auth'])){
        $userDetails['authorization'] = $apiDataU['sp_auth'];
    }

    if(count($companyEmails) > 0){
        $apiDataU['email'] = implode(',',$companyEmails);
    }

    // Compare Post Fn.

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

        $apiDataCp['image'] = new CURLFile($tempFilePath, 'image/jpeg', 'cropped_image.jpg');
        $apiDataCp['jsonData'] = json_encode(array('company_name' => $apiDataU['company']));

        $responseComparePost = sendCurlRequest(BASE_URL.'/compare-post', 'POST', $apiDataCp, [], true, $userDetails['authorization']);
        $decodedResponseComparePost = json_decode($responseComparePost, true);
        if($decodedResponseComparePost['status']){
            $apiDataU['scanImage'] = $decodedResponseComparePost['_meta']['image'];
            $apiDataU['did_match'] = count($decodedResponseComparePost['data']);
            if(count($decodedResponseComparePost['data'])){
                //$apiDataU['post_id'] = $decodedResponseComparePost['data'][0]['id'];
            }
        }
    }

    if($apiDataU['post_id'] > 0){ // add post member
        $apiDataPM = array('post_id' => $apiDataU['post_id'], 'image' => $apiDataCp['image']);
        $responsePM = sendCurlRequest(BASE_URL.'/add-post-member', 'POST', $apiDataPM, [], true, $userDetails['authorization']);
        $decodedResponsePM = json_decode($responsePM, true);
        if($decodedResponsePM['success']){
            // Unlink temporary files after request
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");

            if(isset($_GET['act']) && $_GET['act']=="nauth"){
                echo "<script>alert('Post Created Successfully')</script>";
                if($from){
                    echo "<script>window.location.href = 'admin/posts.php'</script>";
                }else if($rd_srep){
                    echo "<script>window.location.href = 'sales/posts.php'</script>";
                }else{
                    echo "<script>window.location.href = 'create-post.php?act=nauth'</script>";
                }
            }else{
                echo "<script>window.location.href = 'home.php?type=0'</script>";
            }
        }else{
            $err = 1;
            setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
            if(isset($_GET['act']) && $_GET['act']=="nauth"){
                if($from){
                    echo "<script>window.location.href = 'create-post.php?act=nauth'</script>";
                }else if($rd_srep){
                    echo "<script>window.location.href = 'create-post.php?act=nauth&rd_srep=sales&repID='".base64_encode($userDetails['id'])."'</script>";
                }else{
                    echo "<script>window.location.href = 'create-post.php?act=nauth'</script>";
                }
            }else{
                echo "<script>window.location.href = 'create-post.php'</script>";
            }
        }
    }else{
        // add post_locations array
        if (isset($_POST['selected_locations']) && !empty($_POST['selected_locations'])) {
            $post_locations = json_decode($_POST['selected_locations'],true);

            // If business is worldwide, update all country_code to 'WW'
            if ($apiDataU['business_location_type'] == "Worldwide") {
                foreach ($post_locations as &$location) {
                    $location['country_code'] = 'WW';
                }
                unset($location); // good practice to avoid reference bugs
            }

            if(empty($apiDataU['city']) && empty($apiDataU['state'])){
                $apiDataU['address'] = $post_locations[0]['address'];
                $apiDataU['latitude'] = $post_locations[0]['latitude'];
                $apiDataU['longitude'] = $post_locations[0]['longitude'];
                $apiDataU['state'] = $post_locations[0]['state'];
                $apiDataU['city'] = $post_locations[0]['city'];
                $apiDataU['country_code'] = ($apiDataU['business_location_type'] == "Worldwide") ? 'WW' : $post_locations[0]['country_code'];
            }
            $apiDataU['post_locations'] = json_encode($post_locations);
        }

        // add company_branches array
        if (isset($_POST['company_branches_array']) && !empty($_POST['company_branches_array'])) {
            $company_branches = json_decode($_POST['company_branches_array'],true);
            $cb_index = count($company_branches);
            if(count($company_branches)){
                $company_branches[$cb_index]['name'] = 'Primary';
                $company_branches[$cb_index]['phone_numbers'] = null;
                $company_branches[$cb_index]['address'] = $apiDataU['address'];
                $company_branches[$cb_index]['latitude'] = $apiDataU['latitude'];
                $company_branches[$cb_index]['longitude'] = $apiDataU['longitude'];
                $company_branches[$cb_index]['state'] = $apiDataU['state'];
                $company_branches[$cb_index]['city'] = $apiDataU['city'];
                $company_branches[$cb_index]['country_code'] = ($apiDataU['business_location_type'] == "Worldwide") ? 'WW' : $apiDataU['country_code'];
                $company_branches[$cb_index]['zipcode'] = null;
            }
            $apiDataU['company_branches'] = json_encode($company_branches);
        }

        // add company_service_areas array
        if (isset($_POST['company_service_areas_array']) && !empty($_POST['company_service_areas_array'])) {
            $company_service_areas = json_decode($_POST['company_service_areas_array'], true);

            // If Worldwide, change all country codes to 'WW'
            if ($apiDataU['business_location_type'] == "Worldwide") {
                foreach ($company_service_areas as &$area) {
                    $area['country_code'] = 'WW';
                }
                unset($area); // avoid reference issues
            }

            $apiDataU['company_service_areas'] = json_encode($company_service_areas);
        }

        //dump($company_branches);

        // get param from scanResults array
        if (isset($_POST['scanResults']) && !empty($_POST['scanResults'])) {
            $scanResults = json_decode($_POST['scanResults'],true);
            if($scanResults){
                $apiDataU['website'] = $scanResults['website_link'];
                $apiDataU['info'] = strtolower(implode(' ',$scanResults['keywords']));
            }
        }

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

        // get categorization
        if(isset($apiDataU['keywords']) && !empty($apiDataU['keywords'])){
            $responseCat = sendCurlRequest(BASE_URL.'/searchPills', 'POST', ['description' => cleanInputs($apiDataU['keywords'])], [], true, $userDetails['authorization']);
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

        $response = sendCurlRequest(BASE_URL.'/create-post', 'POST', $apiDataU, [], true, $userDetails['authorization']);
        $decodedResponse = json_decode($response, true);
        //dump($decodedResponse);
        if($decodedResponse['success']){
            // Unlink temporary files after request
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            setcookie('wb_successMsg', $decodedResponse['message'], time() + 5, "/");
            if(isset($_GET['act']) && $_GET['act']=="nauth"){
                echo "<script>alert('Post Created Successfully')</script>";
                if($from){
                    echo "<script>window.location.href = 'admin/posts.php'</script>";
                }else if($rd_srep){
                    echo "<script>window.location.href = 'sales/posts.php'</script>";
                }else{
                    echo "<script>window.location.href = 'create-post.php?act=nauth'</script>";
                }
            }else{
                echo "<script>window.location.href = 'home.php?type=0'</script>";
            }
        }else{
            $err = 1;
            setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
            if(isset($_GET['act']) && $_GET['act']=="nauth"){
                if($from){
                    echo "<script>window.location.href = 'create-post.php?act=nauth'</script>";
                }else if($rd_srep){
                    echo "<script>window.location.href = 'create-post.php?act=nauth&rd_srep=sales&repID='".base64_encode($userDetails['id'])."'</script>";
                }else{
                    echo "<script>window.location.href = 'create-post.php?act=nauth'</script>";
                }
            }else{
                echo "<script>window.location.href = 'create-post.php'</script>";
            }
        }
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

$title = "Create Post";
include('pages/posts/create-post.html');
?>
