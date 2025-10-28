<?php
include_once('includes/custom-functions.php');
include_once('admin/utils/helpers.php');

// get geocoding api key
if (isset($_GET['action']) && $_GET['action'] === "gt_gcd_ky") {
    $apiKey = GEOCODING_API_KEY;
    // Return JSON response
    echo json_encode([
        'success' => true,
        'apiKey' => $apiKey
    ]);
    exit;
}

// adminLogin
if(isset($_GET['action']) && $_GET['action'] == "adminLogin"){

    $email      =   cleanInputs($_POST['email']);
    $password   =   cleanInputs($_POST['password']);
    $timezone   =   cleanInputs($_POST['timezone']);
    $randomString = bin2hex(random_bytes(10));
    $device_id   =   cleanInputs($randomString);
    $device_name   =   'web';
    $apiData = [
        'email' => $email,
        'password' => $password,
        'device_type' => 3, // Web
        'device_token' => 'Web',
        'device_name' => $device_name,
        'device_id' => $device_id,
    ];
    $response = sendCurlRequest(BASE_URL.'/adminLogin', 'POST', $apiData);
    $decodedResponse = json_decode($response, true);

    if($decodedResponse['success']){
        $_SESSION['sr_timezone'] = $timezone;
        $_SESSION['hm_sr_auth_data'] = $decodedResponse['body'];
        $_SESSION['hm_sr_logged_in'] = true;

        // ✅ Create a persistent login cookie (valid for 7 days)
        $userId = $decodedResponse['body']['id'] ?? null;
        if ($userId) {
            $cookiePayload = [
                'user_id' => $userId,
                'timezome' => $timezone,
                'email' => $email,
                'token' => bin2hex(random_bytes(32)), // Optional: save this in DB if validating
                'created_at' => time()
            ];

            setcookie(
                'pz_sr_user_auth',
                json_encode($cookiePayload),
                time() + (86400 * 7), // 7 days
                "/",
                "",
                isset($_SERVER['HTTPS']),
                true
            );
        }
    }

    echo $response;
}

// loginUser
if(isset($_GET['action']) && $_GET['action'] == "loginUser"){

    $email      =   cleanInputs($_POST['email']);
    $password   =   cleanInputs($_POST['password']);
    $timezone   =   cleanInputs($_POST['timezone']);
    $randomString = bin2hex(random_bytes(10));
    $device_id   =   cleanInputs($randomString);
    $device_name   =   'web';
    $apiData = [
        'email' => $email,
        'password' => $password,
        'device_type' => 3, // Web
        'device_token' => 'Web',
        'device_name' => $device_name,
        'device_id' => $device_id,
    ];
    
    $response = sendCurlRequest(BASE_URL.'/login', 'POST', $apiData);
    $decodedResponse = json_decode($response, true);

    if($decodedResponse['success']){
        $_SESSION['hm_wb_timezone'] = $timezone;
        $_SESSION['hm_wb_auth_data'] = $decodedResponse['body'];
        $_SESSION['hm_wb_logged_in'] = true;

        // ✅ Create a persistent login cookie (valid for 7 days)
        $userId = $decodedResponse['body']['id'] ?? null;
        if ($userId) {
            $cookiePayload = [
                'user_id' => $userId,
                'timezome' => $timezone,
                'email' => $email,
                'token' => bin2hex(random_bytes(32)), // Optional: save this in DB if validating
                'created_at' => time()
            ];

            setcookie(
                'pz_wb_user_auth',
                json_encode($cookiePayload),
                time() + (86400 * 7), // 7 days
                "/",
                "",
                isset($_SERVER['HTTPS']),
                true
            );
        }
    }

    echo $response;
}

// logoutUser
if(isset($_GET['action']) && $_GET['action'] == "logoutUser"){
    $apiData = [];
    $response = sendCurlRequest(BASE_URL.'/logout', 'PUT', $apiData);
    $decodedResponse = json_decode($response, true);

    if($decodedResponse['success']){
        // ✅ Remove persistent login cookie
        setcookie('pz_wb_user_auth', '', time() - 3600, "/", "", isset($_SERVER['HTTPS']), true);
        unset($_SESSION['hm_wb_auth_data']);
        unset($_SESSION['hm_wb_logged_in']);
        unset($_SESSION['hm_wb_timezone']);
    }

    echo $response;
}

// logoutSalesRep
if(isset($_GET['action']) && $_GET['action'] == "logoutSalesRep"){
    $apiData = [];
    $response = sendCurlRequest(BASE_URL.'/logout', 'PUT', $apiData);
    $decodedResponse = json_decode($response, true);

    if($decodedResponse['success']){
        // ✅ Remove persistent login cookie
        setcookie('pz_sr_user_auth', '', time() - 3600, "/", "", isset($_SERVER['HTTPS']), true);
        unset($_SESSION['hm_sr_auth_data']);
        unset($_SESSION['hm_sr_logged_in']);
        unset($_SESSION['sr_timezone']);
    }

    echo $response;
}

// emailVerification
if(isset($_GET['action']) && $_GET['action'] == "emailVerification"){
    $email          =   cleanInputs($_POST['email']);
    $type          =   cleanInputs($_POST['type'] ?? 0); // Default to 0 if not set
    $apiData = [
        'email' => $email,
        'type' => $type,
    ];
    $response = sendCurlRequest(BASE_URL.'/emailVerification', 'POST', $apiData);
    $decodedResponse = json_decode($response, true);
    echo $response;
}

// ownerVerification
if(isset($_GET['action']) && $_GET['action'] == "ownerVerification"){
    $method         =   cleanInputs($_POST['method']);
    $value          =   cleanInputs($_POST['value']); // Default to 0 if not set
    $apiData = [
        'method' => $method,
        'value' => $value,
    ];

    if($method == 'email'){
        $emailOtpData = ['email'   => $value, 'type' => 1];
        $response = sendCurlRequest(BASE_URL.'/emailVerification', 'POST', $emailOtpData);
    }else{
        $phoneOtpData = ['phone'   => $value];
        $response = sendCurlRequest(BASE_URL.'/verifyOwnerWithOtp', 'POST', $phoneOtpData);
    }
    $decodedResponse = json_decode($response, true);
    echo $response;
}

// registerUser
if(isset($_GET['action']) && $_GET['action'] == "registerUser"){

    $name           =   cleanInputs($_POST['name']);
    $email          =   cleanInputs($_POST['email']);
    $password       =   cleanInputs($_POST['password']);
    $timezone       =   cleanInputs($_POST['timezone']);
    $referral_code  =   cleanInputs($_POST['referral_code']);
    $account_type  =   isset($_POST['account_type']) ? cleanInputs($_POST['account_type']) : 0;
    $randomString = bin2hex(random_bytes(10));
    $device_id   =   cleanInputs($randomString);
    $device_name   =   'web';
    $apiData = [
        'name' => $name,
        'referral_code' => $referral_code,
        'email' => $email,
        'password' => $password,
        'device_type' => 3, // Web
        'device_token' => 'Web',
        'device_name' => $device_name,
        'device_id' => $device_id,
        'account_type' => $account_type
    ];

    if(isset($_SESSION['hm_at_post_id'])) {
        $apiData['post_id'] = $_SESSION['hm_at_post_id'];
    }

    $response = sendCurlRequest(BASE_URL.'/signup', 'POST', $apiData);
    $decodedResponse = json_decode($response, true);

    if($decodedResponse['success']){

        if($account_type == 3){
            $_SESSION['sr_timezone'] = $timezone;
            $_SESSION['hm_sr_auth_data'] = $decodedResponse['body'];
            $_SESSION['hm_sr_logged_in'] = true;
        }else{
            $_SESSION['hm_wb_timezone'] = $timezone;
            $_SESSION['hm_wb_auth_data'] = $decodedResponse['body'];
            $_SESSION['hm_wb_logged_in'] = true;
        }
    }

    echo $response;
}

// deleteAccount
if(isset($_GET['action']) && $_GET['action'] == "deleteAccount"){
    $apiData = [];
    $response = sendCurlRequest(BASE_URL.'/delete-account', 'DELETE', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        unset($_SESSION['hm_wb_auth_data']);
        unset($_SESSION['hm_wb_logged_in']);
        unset($_SESSION['hm_wb_timezone']);
        echo json_encode(["success" => 1, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => 0, 'message' => $responseArr['message']]);
    }
}

// updateUserProfile
if(isset($_GET['action']) && $_GET['action'] == "updateUserProfile"){
    $apiData = $_POST;
    $response = sendCurlRequest(BASE_URL.'/edit-profile', 'PUT', $apiData);
    echo $response;
}

// getNotiCount
if(isset($_GET['action']) && $_GET['action'] == "getNotiCount"){
    $query_data ='?user_id='.$_SESSION['hm_wb_auth_data']['id'].'&page=1&limit=100';
    $response = sendCurlRequest(BASE_URL.'/get-profile'.$query_data, 'GET', []);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['code'] == 401){
        http_response_code(401);
        echo json_encode(['noti_count' => 0]);
    } else {
        $notiCount = $decodedResponse['body']['noti_count'] ?? 0;
        echo json_encode(['noti_count' => $notiCount]);
    }
}

/*******
 * 
 * **************************************************************************
 * *************           U S E R           *******************
* ***************        M O D U L E            ****************
 * **************************************************************************
 * 
 * 
 *******/



/*******
 * 
 * **************************************************************************
 * *************          P O S T S           *******************
* ***************        M O D U L E            ****************
 * **************************************************************************
 * 
 * 
 *******/

// get_posts
// if(isset($_GET['action']) && $_GET['action'] == "get_posts") {
//     // Initialize API data with required fields
//     $apiData = [
//         'type' => cleanInputs($_POST['type'] ?? ''),
//         'page' => cleanInputs($_POST['page'] ?? ''),
//         'limit' => cleanInputs($_POST['limit'] ?? ''),
//         'radius' => cleanInputs($_POST['radius'] ?? ''),
//         'status' => cleanInputs($_POST['status'] ?? ''),
//         'search' => cleanInputs($_POST['search'] ?? '')
//     ];

//     // Conditionally append optional parameters if they exist
//     if (isset($_POST['user_id']) && $_POST['user_id'] > 0) {
//         $apiData['user_id'] = cleanInputs($_POST['user_id']);
//     }
//     if (isset($_POST['post_id']) && $_POST['post_id'] > 0) {
//         $apiData['post_id'] = cleanInputs($_POST['post_id']);
//     }
//     if (isset($_POST['sort']) && !empty($_POST['sort'])) {
//         $apiData['sort'] = cleanInputs($_POST['sort']);
//     }

//     // Make the API request and get the response
//     $response = sendCurlRequest(BASE_URL.'/get-posts', 'GET', $apiData);
//     $responseDecoded = json_decode($response,true);
    
//     // Decode the response (assuming it's JSON)
//     $posts = $responseDecoded['body']; // assuming the API returns a JSON response

//     //dump($posts);

//     // Check if there are posts
//     if (count($posts) > 0) {
//         $postCardsHtml = '';

//         // Loop through the posts and render each post using renderPostCard
//         foreach ($posts as $post) {
//             $postCardsHtml .= renderPostCard($post, $apiData['type']); // Append the HTML of each post card
//         }

//         // Return the combined HTML of all post cards
//         echo $postCardsHtml;
//     }else{
//         if($apiData['page'] == 1){
//             $title = "Nothing to Display Yet! Start an Activity to See Some Results.";
//             $button = "";
//             echo renderNoDataCard($title, $button);
//         }
//     }
// }

// get_fav_posts
if(isset($_GET['action']) && $_GET['action'] == "get_fav_posts") {
    // Initialize API data with required fields
    $apiData = [
        'type' => cleanInputs($_POST['type'] ?? ''),
    ];

    // Conditionally append optional parameters if they exist
    if (isset($_POST['user_id']) && $_POST['user_id'] > 0) {
        $apiData['user_id'] = cleanInputs($_POST['user_id']);
    }

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/get-fav-list', 'GET', $apiData);
    $responseDecoded = json_decode($response,true);
    
    // Decode the response (assuming it's JSON)
    $posts = $responseDecoded['body']; // assuming the API returns a JSON response

    // Check if there are posts
    if (count($posts) > 0) {
        $postCardsHtml = '';

        // Loop through the posts and render each post using renderPostCard
        foreach ($posts as $savedPost) {
            $postCardsHtml .= renderPostCard($savedPost['post'], $apiData['type']); // Append the HTML of each post card
        }

        // Return the combined HTML of all post cards
        echo $postCardsHtml;
    }else{
        $title = "Nothing to Display Yet! Start an Activity to See Some Results.";
        $button = "";
        echo renderNoDataCard($title, $button);
    }
}

// get_fav_listings
if(isset($_GET['action']) && $_GET['action'] == "get_fav_listings") {
    // Initialize API data with required fields
    $apiData = [
        'type' => cleanInputs($_POST['type'] ?? ''),
    ];

    // Conditionally append optional parameters if they exist
    if (isset($_POST['user_id']) && $_POST['user_id'] > 0) {
        $apiData['user_id'] = cleanInputs($_POST['user_id']);
    }

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/get-fav-new-listing', 'GET', $apiData);
    $responseDecoded = json_decode($response,true);
    
    // Decode the response (assuming it's JSON)
    $posts = $responseDecoded['body']; // assuming the API returns a JSON response

    // Check if there are posts
    if (count($posts) > 0) {
        $postCardsHtml = '';

        // Loop through the posts and render each post using renderPostCard
        foreach ($posts as $savedPost) {
            // print_r($savedPost); 
            if($savedPost['type'] == 'On Sale'){
                $postCardsHtml .= renderPostCard($savedPost['post'], 4, $savedPost['post']['show_image']); // Append the HTML of each post card
            } else {
                $postCardsHtml .= renderPostCard($savedPost['post'], $apiData['type'], $savedPost['post']['show_image']); // Append the HTML of each post card
            }
        }

        // Return the combined HTML of all post cards
        echo $postCardsHtml;
    }else{
        $title = "Nothing to Display Yet! Start an Activity to See Some Results.";
        $button = "";
        echo renderNoDataCard($title, $button);
    }
}

// like_post
if(isset($_GET['action']) && $_GET['action'] == "like_post") {
    // Initialize API data with required fields
    $apiData = [
        'post_id' => cleanInputs($_POST['post_id']),
        'type' => cleanInputs($_POST['type']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/like-post', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// fav_post
if(isset($_GET['action']) && $_GET['action'] == "fav_post") {
    // Initialize API data with required fields
    $apiData = [
        'post_id' => cleanInputs($_POST['post_id']),
        'post_type' => cleanInputs($_POST['post_type']),
        'type' => cleanInputs($_POST['type']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/fav-post', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// fav_listing
if(isset($_GET['action']) && $_GET['action'] == "fav_listing") {
    // Initialize API data with required fields
    $apiData = [
        'post_id' => cleanInputs($_POST['post_id']),
        'listing_id' => cleanInputs($_POST['listing_id']),
        'type' => cleanInputs($_POST['type']),
        'api_type' => cleanInputs($_POST['api_type']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/fav-listing', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// show_listing_interest
if(isset($_GET['action']) && $_GET['action'] == "show_listing_interest") {
    // Initialize API data with required fields
    $apiData = [
        'post_id' => cleanInputs($_POST['post_id']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/send-sms-listing-owner', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// report_post
if(isset($_GET['action']) && $_GET['action'] == "report_post") {
    // Initialize API data with required fields
    $apiData = [
        'post_id' => cleanInputs($_POST['post_id']),
        'type' => cleanInputs($_POST['type']),
        'message' => cleanInputs($_POST['message']),
    ];

    if($apiData['type'] == "delete_post"){
        // If the type is delete_post, we need to show phone and email fields
        $apiData['phone'] = cleanInputs($_POST['phone']);
        $apiData['email'] = cleanInputs($_POST['email']);
        $apiData['request_id'] = cleanInputs($_POST['post_id']);
        $apiData['request_type'] = cleanInputs($_POST['request_type']);

        // Make the API request and get the response
        $response = sendCurlRequest(BASE_URL.'/anaomous-post-delete', 'POST', $apiData);
        $responseArr = json_decode($response,true);
    }else{
        // Make the API request and get the response
        $response = sendCurlRequest(BASE_URL.'/report-post', 'POST', $apiData);
        $responseArr = json_decode($response,true);
    }

    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// report_post
if(isset($_GET['action']) && $_GET['action'] == "report_claim_post") {
    // Initialize API data with required fields
    $apiData = [
        'request_id' => (int)cleanInputs($_POST['post_id']),
        'communication' => cleanInputs($_POST['communication']),
        'phone' => cleanInputs($_POST['phone']),
        'sender' => cleanInputs($_POST['sender']),
        'email' => cleanInputs($_POST['email']),
        'message' => cleanInputs($_POST['message']),
        'request_type' => (int)cleanInputs($_POST['post_itype']),
    ];

    $response = sendCurlRequest(BASE_URL.'/anaomous-post-delete', 'POST', $apiData);
    $responseArr = json_decode($response,true);

    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// post_comment
if(isset($_GET['action']) && $_GET['action'] == "post_comment") {
    // Initialize API data with required fields
    $apiData = [
        'post_id' => cleanInputs(base64_decode($_POST['post_id'])),
        'comment' => cleanInputs($_POST['comment']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/add-comment', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// delete_post_comment
if(isset($_GET['action']) && $_GET['action'] == "delete_post_comment") {
    // Initialize API data with required fields
    $apiData = [
        'comment_id' => cleanInputs($_POST['comment_id']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/delete-comment?comment_id='.$apiData['comment_id'], 'DELETE', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// delete_ad_comment
if(isset($_GET['action']) && $_GET['action'] == "delete_ad_comment") {
    // Initialize API data with required fields
    $apiData = [
        'comment_id' => cleanInputs($_POST['comment_id']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/delete-ad-comment?comment_id='.$apiData['comment_id'], 'DELETE', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// delete_post
if(isset($_GET['action']) && $_GET['action'] == "delete_post") {
    // Initialize API data with required fields
    $apiData = [
        'post_id' => cleanInputs($_POST['post_id']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/delete-post?post_id='.$apiData['post_id'], 'DELETE', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// claim_post
if(isset($_GET['action']) && $_GET['action'] == "claim_post") {
    // Initialize API data with required fields
    $apiData = [
        'post_id' => cleanInputs($_POST['post_id']),
        'company_id' => cleanInputs($_POST['company_id']),
        'show_username' => cleanInputs($_POST['show_username'] ? 1 : 0),
        'type' => cleanInputs($_POST['type']) ?? 0,
        'email' => cleanInputs($_POST['email']),
    ];

    if($apiData['post_id']==0){
        unset($apiData['post_id']);
    }
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/send-owner-request', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// get_post_comments
if(isset($_GET['action']) && $_GET['action'] == "get_post_comments") {
    // Initialize API data with required fields
    $apiData = [
        'post_id' => cleanInputs(base64_decode($_POST['post_id']) ?? ''),
        'page' => cleanInputs($_POST['page'] ?? ''),
        'limit' => cleanInputs($_POST['limit'] ?? ''),
    ];

    $user_id = $_SESSION['hm_wb_auth_data']['id'];

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/get-comment', 'GET', $apiData);
    $responseDecoded = json_decode($response,true);
    
    // Decode the response (assuming it's JSON)
    $posts = $responseDecoded['body']; // assuming the API returns a JSON response

    //dump($posts);

    // Check if there are posts
    if (count($posts) > 0) {
        $postCardsHtml = '';

        // Loop through the posts and render each post using renderCompanyCard
        foreach ($posts as $post) {
            $postCardsHtml .= renderPostCommentCard($post, $user_id); // Append the HTML of each post card
        }

        // Return the combined HTML of all post cards
        echo $postCardsHtml;
    }
}

/*******
 * 
 * **************************************************************************
 * *************            A D S           *******************
* ***************        M O D U L E            ****************
 * **************************************************************************
 * 
 * 
 *******/

// get_ads
if(isset($_GET['action']) && $_GET['action'] == "get_ads") {
    // Initialize API data with required fields
    $apiData = [
        'type' => cleanInputs($_POST['type'] ?? ''),
        'radius' => cleanInputs($_POST['radius'] ?? 200),
    ];

    // Conditionally append optional parameters if they exist
    if (isset($_POST['ads_id']) && $_POST['ads_id'] > 0) {
        $apiData['ads_id'] = cleanInputs($_POST['ads_id']);
    }

    if (isset($_POST['user_id']) && $_POST['user_id'] > 0) {
        $apiData['user_id'] = cleanInputs($_POST['user_id']);
    }
    
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/get-ads', 'GET', $apiData);
    $responseDecoded = json_decode($response,true);
    
    // Decode the response (assuming it's JSON)
    $data = $responseDecoded['body']; // assuming the API returns a JSON response

    // Check if there are posts
    if (count($data) > 0) {
        $postCardsHtml = '';

        // Loop through the posts and render each post using renderPostCard
        foreach ($data as $post) {
            $postCardsHtml .= renderPostCard($post, 3); // Append the HTML of each post card
        }

        // Return the combined HTML of all post cards
        echo $postCardsHtml;
    }else{
        $text = $apiData['type'] == 4 ? 'running' : ($apiData['type'] == 2 ? 'past' : 'others');
        $title = "There is no ".$text." ad! Create your first ad today.";
        $button = '<a href="create-ad.php"><button class="upload-buttom-top sk-gradint-button-one">Create New Ad<img src="assets/img/Arrow Forward.svg" alt="Arrow" /></button></a>';
        echo renderNoDataCard($title, $button);
    }
}

// like_ads
if(isset($_GET['action']) && $_GET['action'] == "like_ads") {
    // Initialize API data with required fields
    $apiData = [
        'ads_id' => cleanInputs($_POST['ads_id']),
        'ads_type' => (int)cleanInputs($_POST['ads_type']),
        'type' => cleanInputs($_POST['type']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/like-ads', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// fav_ads
if(isset($_GET['action']) && $_GET['action'] == "fav_ads") {
    // Initialize API data with required fields
    $apiData = [
        'ad_id' => cleanInputs($_POST['ad_id']),
        'type' => cleanInputs($_POST['type']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/fav-ad', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// get_ads_comments
if(isset($_GET['action']) && $_GET['action'] == "get_ads_comments") {
    // Initialize API data with required fields
    $apiData = [
        'ads_id' => cleanInputs(base64_decode($_POST['post_id']) ?? ''),
        'page' => cleanInputs($_POST['page'] ?? ''),
        'limit' => cleanInputs($_POST['limit'] ?? ''),
        'type' => cleanInputs($_POST['ads_type'] ?? ''),
    ];

    $user_id = $_SESSION['hm_wb_auth_data']['id'];

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/get-ad-comment', 'GET', $apiData);
    $responseDecoded = json_decode($response,true);
    
    // Decode the response (assuming it's JSON)
    $posts = $responseDecoded['body']; // assuming the API returns a JSON response

    //dump($posts);

    // Check if there are posts
    if (count($posts) > 0) {
        $postCardsHtml = '';

        // Loop through the posts and render each post using renderCompanyCard
        foreach ($posts as $post) {
            $postCardsHtml .= renderAdCommentCard($post, $user_id); // Append the HTML of each post card
        }

        // Return the combined HTML of all post cards
        echo $postCardsHtml;
    }
}

// add_ads_comment
if(isset($_GET['action']) && $_GET['action'] == "add_ads_comment") {
    // Initialize API data with required fields
    $apiData = [
        'ads_id' => cleanInputs(base64_decode($_POST['post_id'])),
        'comment' => cleanInputs($_POST['comment']),
        'type' => cleanInputs($_POST['post_type'] ?? ''),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/add-ad-comment', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// deleteSingleAds
if(isset($_GET['action']) && $_GET['action'] == "deleteSingleAds"){
    $id    =  cleanInputs(base64_decode($_POST['ads_id']));
    $apiData = [];
    $response = sendCurlRequest(BASE_URL.'/delete-ads?ads_id='.$id, 'DELETE', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// deleteAdImage
if(isset($_GET['action']) && $_GET['action'] == "delete_ads_image"){
    $id    =  cleanInputs($_POST['id']);
    // dump($ids);
    $response = sendCurlRequest(BASE_URL.'/delete-ads-image?id='.$id.'', 'DELETE', []);
    echo $response;
}

/*******
 * 
 * **************************************************************************
 * *************      C O M P A N I E S           *******************
* ***************        M O D U L E            ****************
 * **************************************************************************
 * 
 * 
 *******/

// get_companies
if(isset($_GET['action']) && $_GET['action'] == "get_companies") {
    // Initialize API data with required fields
    $apiData = [
        'page' => cleanInputs($_POST['page'] ?? ''),
        'limit' => cleanInputs($_POST['limit'] ?? ''),
        'radius' => cleanInputs($_POST['radius'] ?? ''),
        'search' => cleanInputs($_POST['search'] ?? '')
    ];

    $isGuestMode = isset($_SESSION['hm_wb_auth_data']['id']) ? 0 : 1;

    // Conditionally append optional parameters if they exist
    if (isset($_POST['user_id']) && $_POST['user_id'] > 0) {
        $apiData['user_id'] = cleanInputs($_POST['user_id']);
    }
    if (isset($_POST['company_id']) && $_POST['company_id'] > 0) {
        $apiData['company_id'] = cleanInputs($_POST['company_id']);
    }
    if (isset($_POST['category_id']) && $_POST['category_id'] > 0) {
        $apiData['category_id'] = cleanInputs($_POST['category_id']);
    }
    if (isset($_POST['product_service_id']) && $_POST['product_service_id'] > 0) {
        $apiData['product_service_id'] = cleanInputs($_POST['product_service_id']);
    }

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/get-company', 'GET', $apiData);
    $responseDecoded = json_decode($response,true);
    
    // Decode the response (assuming it's JSON)
    $posts = $responseDecoded['body']; // assuming the API returns a JSON response

    // Check if there are posts
    if (count($posts) > 0) {
        $postCardsHtml = '';

        // Loop through the posts and render each post using renderCompanyCard
        foreach ($posts as $post) {
            $postCardsHtml .= renderCompanyCard($post, $isGuestMode); // Append the HTML of each post card
        }

        // Return the combined HTML of all post cards
        echo $postCardsHtml;
    }

    if ($apiData['page'] == 1 && count($posts) == 0) {
        $title = "No companies available. Click the `Create Company` button to add your first company.";
        $button = "";
        echo renderNoDataCardPosts($title, $button);
        return;
    }
}

// get_company_suggestion
if(isset($_GET['action']) && $_GET['action'] == "get_company_suggestion") {
    // Initialize API data with required fields
    $apiData = [
        'page' => 1,
        'limit' => 5000,
        'search' => cleanInputs($_POST['search'] ?? '')
    ];

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/get-company', 'GET', $apiData);
    $responseDecoded = json_decode($response,true);
    
    // Decode the response (assuming it's JSON)
    $data = $responseDecoded['body']; // assuming the API returns a JSON response

    echo  json_encode($data);
}

// search_company_fnc
if(isset($_GET['action']) && $_GET['action'] == "search_company_fnc") {
    // Initialize API data with required fields
    $apiData = [
        'search' => cleanInputs($_POST['search'] ?? '')
    ];

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/search-company', 'GET', $apiData);
    $responseDecoded = json_decode($response,true);
    
    // Decode the response (assuming it's JSON)
    $data = $responseDecoded['body']; // assuming the API returns a JSON response

    echo  json_encode($data);
}

// remove_company_member
if(isset($_GET['action']) && $_GET['action'] == "remove_company_member") {
    // Initialize API data with required fields
    $apiData = [
        'id' => cleanInputs($_POST['id']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/remove-company-member', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// make_company_admin
if(isset($_GET['action']) && $_GET['action'] == "make_company_admin") {
    // Initialize API data with required fields
    $apiData = [
        'user_id' => cleanInputs($_POST['user_id']),
        'company_id' => cleanInputs($_POST['company_id']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/make-company-admin', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// company_follow
if(isset($_GET['action']) && $_GET['action'] == "company_follow") {
    // Initialize API data with required fields
    $apiData = [
        'company_id' => cleanInputs($_POST['company_id']),
        'type' => cleanInputs($_POST['type']),
    ];

    if($apiData['type'] == 0){
        $apiData['user_id'] = cleanInputs($_SESSION['hm_wb_auth_data']['id']);
    }

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/company-follow', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// recommend_company
if(isset($_GET['action']) && $_GET['action'] == "recommend_company") {
    // Initialize API data with required fields
    $apiData = [
        'company_id' => cleanInputs($_POST['company_id']),
        'user2_id' => cleanInputs($_SESSION['hm_wb_auth_data']['id']),
    ];

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/recommends-company', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// accept_reject_association_request
if(isset($_GET['action']) && $_GET['action'] == "accept_reject_association_request") {
    // Initialize API data with required fields
    $apiData = [
        'id' => cleanInputs($_POST['id']),
        'status' => cleanInputs($_POST['status']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/accept-reject-association-request', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// accept_reject_post_request
if(isset($_GET['action']) && $_GET['action'] == "accept_reject_post_request") {
    // Initialize API data with required fields
    $apiData = [
        'post_id' => cleanInputs($_POST['request_id']),
        'type' => cleanInputs($_POST['type']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/accept-reject-post-request', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// send_association_request
if(isset($_GET['action']) && $_GET['action'] == "send_association_request") {
    // Initialize API data with required fields
    $apiData = [
        'company_id' => cleanInputs($_POST['company_id']),
        'role' => cleanInputs($_POST['role']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/send-association-request', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// recommend_company_thanks
if(isset($_GET['action']) && $_GET['action'] == "recommend_company_thanks") {
    // Initialize API data with required fields
    $apiData = [
        'id' => cleanInputs($_POST['id']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/recommend-company-thanks', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// get_company_posts
if(isset($_GET['action']) && $_GET['action'] == "get_company_posts") {
    // Initialize API data with required fields
    $apiData = [
        'company_id' => cleanInputs($_POST['company_id'] ?? ''),
    ];

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/get-company-posts', 'GET', $apiData);
    $responseDecoded = json_decode($response,true);
    
    // Decode the response (assuming it's JSON)
    $posts = $responseDecoded['body']; // assuming the API returns a JSON response

    // Check if there are posts
    if (count($posts) > 0) {
        $postCardsHtml = '';

        // Loop through the posts and render each post using renderPostCard
        foreach ($posts as $savedPost) {
            $postCardsHtml .= renderPostCard($savedPost,0); // Append the HTML of each post card
        }

        // Return the combined HTML of all post cards
        echo $postCardsHtml;
    }else{
        $title = "Nothing to Display Yet! Start an Activity to See Some Results.";
        $button = "";
        echo renderNoDataCard($title, $button);
    }
}

// company_recommends
if(isset($_GET['action']) && $_GET['action'] == "company_recommends") {
    // Initialize API data with required fields
    $apiData = [];

    if (isset($_POST['company_id']) && $_POST['company_id'] > 0) {
        $apiData['company_id'] = cleanInputs($_POST['company_id']);
    }

    if (isset($_POST['company_name']) && !empty($_POST['company_name'])) {
        $apiData['company_name'] = cleanInputs($_POST['company_name']);
    }

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/get-company-recommends', 'GET', $apiData);
    $responseDecoded = json_decode($response,true);
    
    // Decode the response (assuming it's JSON)
    $data = $responseDecoded['body']; // assuming the API returns a JSON response

    //dump($data);

    // Check if there is data
    if (count($data) > 0) {
        $postCardsHtml = '';

        // Loop through the posts and render each post using renderPostCard
        foreach ($data as $post) {
            $postCardsHtml .= renderCompanyRecommendCard($post,$_SESSION['hm_wb_auth_data']['id']); // Append the HTML of each post card
        }

        // Return the combined HTML of all post cards
        echo $postCardsHtml;
    }else{
        $title = $apiData['company_name']." is new on Himish and hasn't received any recommendations yet. Be the first to recommend them!";
        $button = '';
        echo renderNoDataCard($title, $button);
    }
}

// fav_company
if(isset($_GET['action']) && $_GET['action'] == "fav_company") {
    // Initialize API data with required fields
    $apiData = [
        'company_id' => cleanInputs($_POST['company_id']),
        'company_type' => cleanInputs($_POST['company_type']),
        'type' => cleanInputs($_POST['type']),
    ];
    
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/fav-company', 'POST', $apiData);
    $responseArr = json_decode($response,true);

    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

/*******
 * 
 * **************************************************************************
 * *************      N O T I F I C A T I O N           *******************
* ***************           M O D U L E            ****************
 * **************************************************************************
 * 
 * 
 *******/

// get_notifications
if(isset($_GET['action']) && $_GET['action'] == "get_notifications") {
    // Initialize API data with required fields
    $apiData = [
        'page' => cleanInputs($_POST['page'] ?? ''),
        'limit' => cleanInputs($_POST['limit'] ?? ''),
    ];

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/get-notifications', 'GET', $apiData);
    $responseDecoded = json_decode($response,true);
    
    // Decode the response (assuming it's JSON)
    $posts = $responseDecoded['body']; // assuming the API returns a JSON response

    //dump($posts);

    // Check if there are posts
    if (count($posts) > 0) {
        $postCardsHtml = '';

        // Loop through the posts and render each post using renderCompanyCard
        foreach ($posts as $post) {
            $postCardsHtml .= renderNotificationCard($post); // Append the HTML of each post card
        }

        // Return the combined HTML of all post cards
        echo $postCardsHtml;
    }else{
        if($apiData['page'] == 1){
            $title = "No notifications yet! Engage in activities to start receiving.";
            $button = "";
            echo renderNoDataCard($title, $button);
        }
    }
}

/*******
 * 
 * **************************************************************************
 * *************      C O M M O N           *******************
* ***************     M O D U L E            ****************
 * **************************************************************************
 * 
 * 
 *******/

 // get_search_suggestions
if(isset($_GET['action']) && $_GET['action'] == "get_search_suggestions") {
    // Initialize API data with required fields
    $apiData = [];

    $search = $_POST['search'];
    $radius = cleanInputs(20000);

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL."/get-search-suggession?search=".urlencode($search)."&radius=".urlencode($radius)."", 'GET', $apiData);
    $responseDecoded = json_decode($response,true);
    
    // Decode the response (assuming it's JSON)
    $data = $responseDecoded['body']; // assuming the API returns a JSON response
    echo  json_encode($data);
}

 // get-services-products
if(isset($_GET['action']) && $_GET['action'] == "get_services_products") {
    // Initialize API data with required fields
    $apiData = [];
    $data = [];

    $type = $_POST['type'];
    $selectedIds = $_POST['selectedIds'];
    $show_in_filter = $_POST['show_in_filter'];
    $radius = cleanInputs(20000);

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL."/get-services-products?type=".urlencode($type)."&selectedIds=".urlencode($selectedIds)."&show_in_filter=".urlencode($show_in_filter)."", 'GET', $apiData);
    $responseDecoded = json_decode($response,true);
    
    // Decode the response (assuming it's JSON)
    if($responseDecoded['success'] == 1){
        $data = $responseDecoded['body']; // assuming the API returns a JSON response
    }
    echo  json_encode($data);
}

/*******
 * 
 * **************************************************************************
 * *************      C O M M U N I T I E S           *******************
* ***************        M O D U L E            ****************
 * **************************************************************************
 * 
 * 
 *******/

// leave_community
if(isset($_GET['action']) && $_GET['action'] == "leave_community") {
    // Initialize API data with required fields
    $apiData = [
        'id' => cleanInputs($_POST['id']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/leave-community?community_id='.$apiData['id'], 'DELETE', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// delete_community
if(isset($_GET['action']) && $_GET['action'] == "delete_community") {
    // Initialize API data with required fields
    $apiData = [
        'id' => cleanInputs($_POST['id']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/admin-delete-community?id='.$apiData['id'], 'DELETE', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// accept_reject_community_request
if(isset($_GET['action']) && $_GET['action'] == "accept_reject_community_request") {
    // Initialize API data with required fields
    $apiData = [
        'request_id' => cleanInputs($_POST['id']),
        'type' => cleanInputs($_POST['status']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/accept-reject-community-request', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// join_community
if(isset($_GET['action']) && $_GET['action'] == "join_community") {
    // Initialize API data with required fields
    $apiData = [
        'community_id' => cleanInputs($_POST['community_id']),
    ];
    
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/join-community', 'POST', $apiData);
    $responseArr = json_decode($response,true);

    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// get_community_members
if(isset($_GET['action']) && $_GET['action']=="get_community_members"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0]['column'], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw'], 'status' => $_POST['status'], 'is_owner' => $_POST['is_owner']);

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['community_id' => base64_decode($_POST['Id']), 'search' => $input['search'], 'page' => $page, 'limit' => $input['length'], 'status' => $input['status'], 'is_owner' => $input['is_owner']];

    $response = sendCurlRequest(BASE_URL.'/getCommunityMember', 'GET', $apiData);

    $decodedResponse = json_decode($response, true);
    
    // Handle 401 Unauthorized
    if (isset($decodedResponse['code']) && $decodedResponse['code'] == 401) {
        http_response_code(401);
        echo json_encode([
            'redirect' => true,
            'url' => dirname($_SERVER['PHP_SELF']) // Redirect URL
        ]);
        exit;
    }

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {
            $user = $row['user'];
            $name = $user['name'] ?? 'N/A';
            $avatar = empty($user['image']) ? 'assets/img/fav-icon.png' : MEDIA_BASE_URL . $user['image'];
            $avatarImg = '<img src="'.$avatar.'" class="w-8 h-8 rounded-full mr-3" alt="'.$name.'" onerror="this.onerror=null;this.src=\'assets/img/fav-icon.png\';" />';

            $accountTypeBadge = $user['account_type'] == 3
                ? '<span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full flex items-center w-fit"><i class="fa-solid fa-user-tie text-blue-600 mr-1"></i>Sales Rep</span>'
                : '';

            $role = $row['is_owner']
                ? '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full flex items-center w-fit"><i class="fa-solid fa-shield-halved text-red-600 mr-1"></i>Admin</span>'
                : '<span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full flex items-center w-fit"><i class="fa-solid fa-users text-gray-600 mr-1"></i>Regular Member</span>';

            $status = $row['status'] == 1
                ? '<div class="flex items-center"><span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Active</span></div>'
                : '<div class="flex items-center"><span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Inactive</span></div>';

            $joinDate = '<div class="text-gray-500">'.formatToUSDate($row['created_at'], 1).'</div>';

            $actions = '<div class="flex items-center space-x-3">'
                    . '<a class="text-blue-500 hover:text-blue-700" href="javascript:void(0)" onclick="upcomingFeatureToggleModal(true)" title="Message"><i class="fa-solid fa-envelope"></i></a>'
                    . '<a class="text-green-500 hover:text-green-700" href="javascript:void(0)" onclick="upcomingFeatureToggleModal(true)" title="View Profile"><i class="fa-solid fa-user"></i></a>'
                    . '</div>';

            $nameHtml = '<div class="flex items-center">'.$avatarImg.'<div><div class="flex items-center gap-2"><span class="font-medium text-gray-800">'.$name.'</span>'.$accountTypeBadge.'</div></div></div>';

            $final[] = [
                "DT_RowId" => $row['id'],
                $nameHtml,
                $role,
                $status,
                $joinDate,
                $actions
            ];
        }
    }

    $json_data = array(
                    "draw"=> intval( $input['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                    "recordsTotal"    => intval(count($final)),  // total number of records
                    "recordsFiltered" => intval($decodedResponse['meta']['total']), // total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"            => $final   // total data array
                );

    echo  json_encode($json_data);
}

// sr_regenerate_link
if(isset($_GET['action']) && $_GET['action'] == "sr_regenerate_link") {
    // Initialize API data with required fields
    $apiData = [
        'link_id' => cleanInputs(base64_decode($_POST['id'])),
    ];
    
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/update-referral-link?link_id='.$apiData['link_id'], 'PUT', $apiData);
    $responseArr = json_decode($response,true);

    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

/*******
 * 
 * **************************************************************************
 * *************    C O N N E C T I O N S           *******************
* ***************        M O D U L E            ****************
 * **************************************************************************
 * 
 * 
 *******/

// get_connections
if(isset($_GET['action']) && $_GET['action'] == "get_connections") {
    // Initialize API data with required fields
    $apiData = [
        'type' => cleanInputs($_POST['type'] ?? ''),
    ];

    if (isset($_POST['user_id']) && $_POST['user_id'] > 0) {
        $apiData['user_id'] = cleanInputs($_POST['user_id']);
    }

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/get-followers', 'GET', $apiData);
    $responseDecoded = json_decode($response,true);
    
    // Decode the response (assuming it's JSON)
    $data = $responseDecoded['body']; // assuming the API returns a JSON response

    //dump($data);

    // Check if there is data
    if (count($data) > 0) {
        $postCardsHtml = '';

        // Loop through the posts and render each post using renderPostCard
        foreach ($data as $post) {
            $postCardsHtml .= renderConnectionCard($post,$_SESSION['hm_wb_auth_data']['id']); // Append the HTML of each post card
        }

        // Return the combined HTML of all post cards
        echo $postCardsHtml;
    }else{
        $title = "There are no connections! Follow companies or users to build your network.";
        $button = '';
        echo renderNoDataCard($title, $button);
    }
}

// accept_reject_connection_request
if(isset($_GET['action']) && $_GET['action'] == "accept_reject_connection_request") {
    // Initialize API data with required fields
    $apiData = [
        'request_id' => cleanInputs($_POST['request_id']),
        'type' => cleanInputs($_POST['type']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/accept-reject-company-follow', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

/*******
 * 
 * **************************************************************************
 * *************         S H A R E           *******************
* ***************       M O D U L E            ****************
 * **************************************************************************
 * 
 * 
 *******/

// Handle Deep Link Generation Request
if (isset($_GET['action']) && $_GET['action'] === "get_deep_link_wb") {
    $ID   = cleanInputs($_POST['id'] ?? '');
    $type = cleanInputs($_POST['type'] ?? '');
    $meta = cleanInputs($_POST['meta'] ?? '');

    $decodedID = base64_decode($ID);
    if (!$decodedID || !is_numeric($decodedID)) {
        echo json_encode(['error' => 'Invalid ID']);
        exit;
    }

    // Mapping type to internal values
    $typeMap = [
        'get_posts'       => ['value' => 0, 'string' => 'postPage'],
        'get_ads'         => ['value' => 1, 'string' => 'adsPage'],
        'get_looking_for' => ['value' => 2, 'string' => 'servicePage'],
        'get_deal_share'  => ['value' => 3, 'string' => 'dealShareDetail'],
        'get_deals'       => ['value' => 4, 'string' => 'dealDetail'],
        'get_companies'   => ['value' => 5, 'string' => 'companyDetail'],
        'get_on_sale'     => ['value' => 6, 'string' => 'onSalePage'],
        'referral_link'   => ['value' => 7, 'string' => 'referralLink'],
        'invite_community'=> ['value' => 8, 'string' => 'communityDetail'],
        'inviteUser'      => ['value' => 9, 'string' => 'inviteUser'],
    ];

    $typeValue = $typeMap[$type]['value'] ?? 0;
    $customString = $typeMap[$type]['string'] ?? 'default';

    // Fallback values
    $shareTitle = "Check this out on Himish!";
    $shareDescription = "Himish is a platform where users can post feeds, view nearby businesses, claim their business listings, and connect with other companies and users";
    $shareImage = "https://himish.com/assets/img/logo.png";
    $canonicalUrl = BRANCH_TEST_MODE ? "http://localhost/himish_admin/branch-redirect.php" : "https://himish.com/branch-redirect.php";

    // If it's a post, fetch dynamic content
    if ($type === 'get_posts' || $type === 'get_deals' || $type === 'get_looking_for' || $type === 'get_on_sale') {
        $apiData = ['post_id' => $decodedID];
        $response = sendCurlRequest(BASE_URL . '/get-posts', 'GET', $apiData);
        $posts = json_decode($response, true)['body'][0] ?? [];

        if (!empty($posts)) {
            $shareTitle = ucfirst($posts['title']) ?? $shareTitle;
            $shareDescription = $posts['info'] ?? $shareDescription;
            $shareImage = count($posts['post_images']) ? MEDIA_BASE_URL.$posts['post_images'][0]['image'] : $shareImage;
        }
    } else if ($type === 'get_deal_share') {
        $apiData = ['post_id' => $decodedID];
        $response = sendCurlRequest(BASE_URL . '/get-deals-share', 'GET', $apiData);
        $posts = json_decode($response, true)['body'][0] ?? [];

        if (!empty($posts)) {
            $shareTitle = ucfirst($posts['title']) ?? $shareTitle;
        }
    } else if ($type === 'get_ads') {
        $apiData = ['ads_id' => $decodedID];
        $response = sendCurlRequest(BASE_URL . '/get-ads', 'GET', $apiData);
        $posts = json_decode($response, true)['body'][0] ?? [];

        if (!empty($posts)) {
            $shareTitle = ucfirst($posts['title']) ?? $shareTitle;
            $shareDescription = $posts['location'] ?? $shareDescription;
            $shareImage = count($posts['sponser_ads_images']) ? MEDIA_BASE_URL.$posts['sponser_ads_images'][0]['media'] : $shareImage;
        }
    } else if ($type === 'get_companies') {

        $query_data ='?company_id='.$decodedID.'';
        $response = sendCurlRequest(BASE_URL . '/get-company'.$query_data, 'GET', []);
        $company = json_decode($response, true)['body'][0] ?? [];

        if (!empty($company)) {
            if (!empty($company['name'])) {
                $shareTitle = ucfirst($company['name']);
            } else {
                $shareTitle = $shareTitle;
            }
            if (!empty($company['company_branches']) && isset($company['company_branches'][0]['address'])) {
                $shareDescription = $company['company_branches'][0]['address'];
            } else {
                $shareDescription = $shareDescription; // keeps original value, you can omit this line actually
            }
            $shareImage = count($company['company_cover_images']) ? MEDIA_BASE_URL.$company['company_cover_images'][0]['image'] : $shareImage;
        }

        if($meta == "company_recommend"){
            $responseSettings = sendCurlRequest(BASE_URL . '/get-setting', 'GET', []);
            $generalSettings = json_decode($responseSettings, true)['body'] ?? [];

            $companyName = ucfirst($company['name'] ?? '');

            // Replace {Company} placeholder in the message
            $inviteMessage = str_replace('{Company}', $companyName, $generalSettings['invite_to_company_recommend'] ?? '');

            if (!empty($companyName)) {
                $shareTitle = $companyName . "\n\n" . $inviteMessage;
            } else {
                $shareTitle = $inviteMessage;
            }
        }
    } else if ($type === 'invite_community') { // invite community

        $query_data ='?id='.$decodedID.'';
        $response = sendCurlRequest(BASE_URL . '/get-community'.$query_data, 'GET', []);
        $community = json_decode($response, true)['body'][0] ?? [];

        if (!empty($community)) {
            if (!empty($community['name'])) {
                $shareTitle = ucfirst($community['name']);
            } else {
                $shareTitle = $shareTitle;
            }
            $shareImage = !empty($community['image']) ? MEDIA_BASE_URL.$community['image'] : $shareImage;
        }

        if($meta == "invite_community"){
            $responseSettings = sendCurlRequest(BASE_URL . '/get-setting', 'GET', []);
            $generalSettings = json_decode($responseSettings, true)['body'] ?? [];

            $communityName = ucfirst($community['name'] ?? '');

            // Replace {Community} placeholder in the message
            $inviteMessage = str_replace('{Community}', $communityName, $generalSettings['invite_to_community'] ?? '');

            if (!empty($communityName)) {
                $shareTitle = $communityName . "\n\n" . $inviteMessage;
            } else {
                $shareTitle = $inviteMessage;
            }
        }
    } else if ($type === 'inviteUser') {
        $responseSettings = sendCurlRequest(BASE_URL . '/get-setting', 'GET', []);
        $generalSettings = json_decode($responseSettings, true)['body'] ?? [];
        $shareTitle = $generalSettings['invite_to_app'];
    }

    // Prepare deep link parameters
    $params = [
        'custom_number'  => (string) $decodedID,
        'request_type'   => (string) $typeValue,
        'user_id'        => (string) ($_SESSION['hm_wb_auth_data']['id'] ?? '0'),
        'custom_string'  => (string) $customString,
        'meta'           => (string) $meta,

        // Open Graph (Facebook, LinkedIn)
        '$desktop_url'       => $canonicalUrl,
        '$og_title'          => 'Himish : ' . "\n\n" .$shareTitle . "\n\n",
        '$og_description'    => $shareDescription,
        '$og_image_url'      => $shareImage,
        '$og_url'            => $canonicalUrl,
        '$og_image_width'    => 1200,
        '$og_image_height'   => 630,
        '$og_type'           => 'website',
        '$og_image_alt'      => 'Himish preview',

        // Twitter
        '$twitter_card'        => 'summary_large_image',
        '$twitter_title'       => 'Himish : ' . "\n\n" .$shareTitle . "\n\n",
        '$twitter_description' => $shareDescription,
        '$twitter_image_url'   => $shareImage,
    ];

    if($type === "referral_link"){
        $params['alias'] = cleanInputs($_POST['meta'] ?? '');
    }

    //dump($params);

    // Create Branch.io short URL
    $shareURL = createBranchShortUrl($params);

    // Fetch Settings (Invite text)
    if(($type == "inviteUser")){
        
    }

    echo json_encode([
        'link' => $shareURL,
        'text' => $shareTitle
    ]);
}

// get_fav_companies
if(isset($_GET['action']) && $_GET['action'] == "get-fav-companies") {

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/get-fav-companies', 'GET');
    $responseDecoded = json_decode($response,true);

    $isGuestMode = isset($_SESSION['hm_wb_auth_data']['id']) ? 0 : 1;
    
    // Decode the response (assuming it's JSON)
    $companies = $responseDecoded['body']; // assuming the API returns a JSON response

    // Check if there are posts
    if (count($companies) > 0) {
        $postCardsHtml = '';

        // Loop through the posts and render each post using renderPostCard
        foreach ($companies as $savedCompanies) {
            if(!isset($savedCompanies['company']['total_recommend_web'])){
                $savedCompanies['company']['total_recommend_web'] = 0;
            }
            $postCardsHtml .= renderCompanyCard($savedCompanies['company'],$isGuestMode); // Append the HTML of each post card
        }

        // Return the combined HTML of all post cards
        echo $postCardsHtml;
    }else{
        $title = "Nothing to Display Yet! Start an Activity to See Some Results.";
        $button = "";
        echo renderNoDataCard($title, $button);
    }
}

// Add Community
if(isset($_GET['action']) && $_GET['action'] == "add-community") {
    // Initialize API data with required fields
    $apiData = [
        'name'=> cleanInputs($_POST['name'] ?? ''),
        'description' => cleanInputs($_POST['description'] ?? ''),
        'is_private'=> cleanInputs($_POST['is_private'] ?? ''),
        'address'=> cleanInputs($_POST['address'] ?? ''),
        'city' => cleanInputs($_POST['city'] ?? ''),
        'state'=> cleanInputs($_POST['state'] ?? ''),
        'latitude' => cleanInputs($_POST['latitude'] ?? ''),
        'longitude'=> cleanInputs($_POST['longitude'] ?? '')
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/add-community', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// View Post Eye Count
if(isset($_GET['action']) && $_GET['action'] == "view_post_eye_count") {
    // Initialize API data with required fields
    $apiData = [
        'post_id'=> cleanInputs($_POST['post_id'] ?? ''),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/eye-post-count', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// ad_view_count_fnc
if(isset($_GET['action']) && $_GET['action'] == "ad_view_count_fnc") {
    // Initialize API data with required fields
    $apiData = [
        'ad_id'=> cleanInputs($_POST['ad_id'] ?? ''),
        'type'=> cleanInputs($_POST['type'] ?? 0),
    ];

    if($apiData['type'] == 0){
        unset($apiData['type']);
    }

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/ad-view-count', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

if(isset($_GET['action']) && $_GET['action'] == "delete-company"){
    // Validate input
    $company_id      =   cleanInputs(base64_decode($_POST['id']));
    // dump($company_id);
    $apiData = [
        'id' => cleanInputs($company_id),
    ]; 
    // dump($apiData);   
    $response = sendCurlRequest(BASE_URL.'/delete-company?id='.$apiData['id'], 'DELETE');
    // dump($response);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => 1, 'message' => $responseArr['message'], 'id' => base64_decode($_SESSION['hm_wb_auth_data']['id'])]);
    }else{
        echo json_encode(["success" => 0, 'message' => $responseArr['message'], 'id' => $apiData['id']]);
    }
}

// // get_ads_and_posts
// if (isset($_GET['action']) && $_GET['action'] == "get_posts") {

//     $isGuestMode = isset($_SESSION['hm_wb_auth_data']['id']) ? 0 : 1;
//     // Fetch posts data with the provided type
//     $postsApiData = [
//         'type' => cleanInputs($_POST['type'] ?? ''), // Use user-provided type for posts
//         'page' => cleanInputs($_POST['page'] ?? ''),
//         'limit' => cleanInputs($_POST['limit'] ?? ''),
//         'radius' => cleanInputs($_POST['radius'] ?? ''),
//         'status' => cleanInputs($_POST['status'] ?? ''),
//         'search' => cleanInputs($_POST['search'] ?? '')
//     ];
//     if (isset($_POST['user_id']) && $_POST['user_id'] > 0) {
//         $postsApiData['user_id'] = cleanInputs($_POST['user_id']);
//     }
//     if (isset($_POST['post_id']) && $_POST['post_id'] > 0) {
//         $postsApiData['post_id'] = cleanInputs($_POST['post_id']);
//     }
//     if (isset($_POST['sort']) && !empty($_POST['sort'])) {
//         $postsApiData['sort'] = cleanInputs($_POST['sort']);
//     }
//     $postsResponse = sendCurlRequest(BASE_URL.'/get-posts', 'GET', $postsApiData);
//     $postsData = json_decode($postsResponse, true)['body'] ?? [];

//     // Mark posts data with "source" => "post"
//     foreach ($postsData as &$post) {
//         $post['source'] = 'post';
//     }
//     unset($post);

//     $mergedData = $postsData; // Initialize mergedData with posts

//     // **Only fetch and merge ads if type is 0**
//     if ($postsApiData['type'] == 0) {
//         // Fetch ads data with type = 3
//         $adsApiData = [
//             'type' => 3, // Set type to 3 for ads
//             'radius' => cleanInputs($_POST['radius'] ?? 200),
//         ];
//         if (isset($_POST['ads_id']) && $_POST['ads_id'] > 0) {
//             $adsApiData['ads_id'] = cleanInputs($_POST['ads_id']);
//         }
//         if (isset($_POST['user_id']) && $_POST['user_id'] > 0) {
//             $adsApiData['user_id'] = cleanInputs($_POST['user_id']);
//         }
//         $adsResponse = sendCurlRequest(BASE_URL.'/get-ads', 'GET', $adsApiData);
//         $adsData = json_decode($adsResponse, true)['body'] ?? [];

//         // Mark ads data with "source" => "ad"
//         foreach ($adsData as &$ad) {
//             $ad['source'] = 'ad';
//         }
//         unset($ad);

//         // Merge ads with posts
//         $mergedData = array_merge($postsData, $adsData);
//         shuffle($mergedData);
//     }

//     // Check if there is any data
//     if (count($mergedData) > 0) {
//         $postCardsHtml = '';
//         foreach ($mergedData as $item) {
//             // If it's an ad, pass type 3, otherwise use the original post type
//             $type = ($item['source'] == 'ad') ? 3 : $postsApiData['type'];
//             $postCardsHtml .= renderPostCard($item, $type, 0, $isGuestMode);
//         }
//         echo $postCardsHtml;
//     }
// }

if (isset($_GET['action']) && $_GET['action'] === "get_posts_old") {

    $isGuestMode = isset($_SESSION['hm_wb_auth_data']['id']) ? 0 : 1;

    // Input sanitization
    $postsApiData = [
        'type'   => cleanInputs($_POST['type'] ?? ''),
        'page'   => cleanInputs($_POST['page'] ?? ''),
        'limit'  => cleanInputs($_POST['limit'] ?? ''),
        'radius' => cleanInputs($_POST['radius'] ?? ''),
        'search' => cleanInputs($_POST['search'] ?? '')
    ];
    if (!empty($_POST['user_id'])) $postsApiData['user_id'] = cleanInputs($_POST['user_id']);
    if (!empty($_POST['post_id'])) $postsApiData['post_id'] = cleanInputs($_POST['post_id']);
    if (!empty($_POST['status'])) $postsApiData['status'] = cleanInputs($_POST['status']);
    if (!empty($_POST['sort'])) $postsApiData['sort'] = cleanInputs($_POST['sort']);

    if($postsApiData['type'] == 1){
        if (!empty($_POST['currentListingCategory'])) $postsApiData['ad_type'] = cleanInputs($_POST['currentListingCategory']);

        if(strtolower($postsApiData['ad_type']) == "all"){
            unset($postsApiData['ad_type']);
        }
    }

    // Settings
    $responseSettings = sendCurlRequest(BASE_URL . '/get-setting', 'GET', []);
    $settings = json_decode($responseSettings, true)['body'] ?? [];
    $fd_shown_after_x_feeds = (int)($settings['fd_shown_after_x_feeds'] ?? 0);
    $show_listing_images = (int)($settings['show_listing_images'] ?? 0);

    // Posts
    if($postsApiData['type'] == 1){
        $postsResponse = sendCurlRequest(BASE_URL.'/get-new-listing', 'GET', $postsApiData);
    }else{
        $postsResponse = sendCurlRequest(BASE_URL.'/get-posts', 'GET', $postsApiData);
    }
    $postsData = json_decode($postsResponse, true)['body'] ?? [];
    foreach ($postsData as &$post) $post['source'] = 'post'; unset($post);

    //dump($postsData);

    // Ads
    $adsData = [];
    if ($postsApiData['type'] == 0 && $postsApiData['page'] == 1) {
        $adsApiData = [
            'type' => 3,
        ];
        if (!empty($_POST['user_id'])) $adsApiData['user_id'] = cleanInputs($_POST['user_id']);
        if (!empty($_POST['ads_id'])) $adsApiData['ads_id'] = cleanInputs($_POST['ads_id']);

        $adsResponse = sendCurlRequest(BASE_URL.'/get-ads', 'GET', $adsApiData);
        $adsData = json_decode($adsResponse, true)['body'] ?? [];
        foreach ($adsData as &$ad) $ad['source'] = 'ad'; unset($ad);

        // Featured deals
        $featuredDeals = [];
        $fdApiData = ['featured_deals' => 1, 'type' => 2];
        $responseFD = sendCurlRequest(BASE_URL.'/get-posts', 'GET', $fdApiData);
        $featuredDeals = json_decode($responseFD, true)['body'] ?? [];
    }

    // Filter available ads
    $shownAdIds = $_SESSION['shown_ad_ids'] ?? [];
    $adsCount = count($adsData);
    //if (count($shownAdIds) >= $adsCount) $shownAdIds = [];
    $shownAdIds = [];

    $availableAds = array_values(array_filter($adsData, fn($ad) => !in_array($ad['id'], $shownAdIds)));

    $postCardsHtml = '';
    $totalFeedCounter = 0;
    $postCounter = 0;
    $adPointer = 0;
    $featuredDealsInserted = false;

    foreach ($postsData as $index => $post) {
        
        // Render post
        $postCardsHtml .= renderPostCard($post, $postsApiData['type'], 0, $isGuestMode, $show_listing_images,0,count($postsData));
        $totalFeedCounter++;
        $postCounter++;

        // Insert featured deals
        if ($postsApiData['page'] == 1 && !$featuredDealsInserted && $fd_shown_after_x_feeds > 0 && $totalFeedCounter == $fd_shown_after_x_feeds) {
            ob_start();
            include('includes/featured-deals.php');
            $featuredDealsInserted = true;
        }

        // Determine ad interval
        $interval = ($index % 2 == 0) ? 5 : 6;
        if ($postCounter % $interval === 0 && isset($availableAds[$adPointer])) {
            $ad = $availableAds[$adPointer];
            $postCardsHtml .= renderPostCard($ad, 3, 0, $isGuestMode);
            $shownAdIds[] = $ad['id'];
            $adPointer++;
            $totalFeedCounter++;

            // Insert featured deals after ad too
            if ($postsApiData['page'] == 1 && !$featuredDealsInserted && $fd_shown_after_x_feeds > 0 && $totalFeedCounter == $fd_shown_after_x_feeds) {
                ob_start();
                include('includes/featured-deals.php');
                $featuredDealsInserted = true;
            }
        }
    }

    // If very few posts, still show remaining ads
    if (empty($postsData) || $postCounter < 5) {
        foreach ($availableAds as $ad) {
            if (!in_array($ad['id'], $shownAdIds)) {
                $postCardsHtml .= renderPostCard($ad, 3, 0, $isGuestMode);
                $shownAdIds[] = $ad['id'];
                $totalFeedCounter++;

                if (!$featuredDealsInserted && $fd_shown_after_x_feeds > 0 && $totalFeedCounter == $fd_shown_after_x_feeds) {
                    ob_start();
                    include('includes/featured-deals.php');
                    $postCardsHtml .= ob_get_clean();
                    $featuredDealsInserted = true;
                }
            }
        }
    }

    // CASE 1: type == 0 → check both posts and ads, and page should be 1
    if ($postsApiData['type'] == 0 && $postsApiData['page'] == 1 && count($postsData) == 0 && count($adsData) == 0 && count($featuredDeals) == 0) {
        $title = "Nothing to Display Yet! Start an Activity to See Some Results.";
        $button = "";
        echo renderNoDataCardPosts($title, $button);
        return;
    }

    // CASE 2: type > 0 → only check posts, and page should be 1
    if ($postsApiData['type'] > 0 && $postsApiData['page'] == 1 && count($postsData) == 0) {
        $title = "Nothing to Display Yet! Start an Activity to See Some Results.";
        $button = "";
        echo renderNoDataCardPosts($title, $button);
        return;
    }

    $_SESSION['shown_ad_ids'] = $shownAdIds;

    echo $postCardsHtml;
}

if (isset($_GET['action']) && $_GET['action'] === "get_posts") {

    $isGuestMode = isset($_SESSION['hm_wb_auth_data']['id']) ? 0 : 1;

    // Input sanitization
    $postsApiData = [
        'type'   => cleanInputs($_POST['type'] ?? ''),
        'page'   => cleanInputs($_POST['page'] ?? ''),
        'limit'  => cleanInputs($_POST['limit'] ?? ''),
        'radius' => cleanInputs($_POST['radius'] ?? ''),
        'search' => cleanInputs($_POST['search'] ?? ''),
        'latitude' => cleanInputs($_POST['latitude'] ?? ''),
        'longitude' => cleanInputs($_POST['longitude'] ?? ''),
        'country_code' => cleanInputs($_POST['country_code'] ?? '')
    ];
    if (!empty($_POST['user_id'])) $postsApiData['user_id'] = cleanInputs($_POST['user_id']);
    if (!empty($_POST['post_id'])) $postsApiData['post_id'] = cleanInputs($_POST['post_id']);
    if (!empty($_POST['ads_id'])) $postsApiData['ads_id'] = cleanInputs($_POST['ads_id']);
    if (!empty($_POST['status'])) $postsApiData['status'] = cleanInputs($_POST['status']);
    if (!empty($_POST['sort'])) $postsApiData['sort'] = cleanInputs($_POST['sort']);

    if($postsApiData['type'] == 1){
        if (!empty($_POST['currentListingCategory'])) $postsApiData['ad_type'] = cleanInputs($_POST['currentListingCategory']);

        if(strtolower($postsApiData['ad_type']) == "all"){
            unset($postsApiData['ad_type']);
            $postsApiData['limit'] = 500000;
        }
    }

    // Settings
    $responseSettings = sendCurlRequest(BASE_URL . '/get-setting', 'GET', []);
    $settings = json_decode($responseSettings, true)['body'] ?? [];
    $fd_shown_after_x_feeds = (int)($settings['fd_shown_after_x_feeds'] ?? 0);
    $show_listing_images = (int)($settings['show_listing_images'] ?? 0);

    // Posts
    if($postsApiData['type'] == 1){
        $postsResponse = sendCurlRequest(BASE_URL.'/get-new-listing', 'GET', $postsApiData);
    }else if($postsApiData['type'] == 4){
        $postsResponse = sendCurlRequest(BASE_URL.'/get-on-sale-listing', 'GET', $postsApiData);
    }else{
        $postsResponse = sendCurlRequest(BASE_URL.'/get-posts', 'GET', $postsApiData);
    }

    $postsData = json_decode($postsResponse, true)['body'] ?? [];
    foreach ($postsData as &$post) $post['source'] = 'post'; unset($post);

    if(isset($postsApiData['ads_id'])){
        $postsData = [];
    }

    // dump($postsData);

    // Detect category grouping case
    $isGroupedByCategory = false;
    if ($postsApiData['type'] == 1 && (empty($_POST['currentListingCategory']) || strtolower($_POST['currentListingCategory']) == 'all')) {
        $isGroupedByCategory = true;
        $groupedPosts = [];
        foreach ($postsData as $post) {
            $category = $post['ad_type'] ?? 'Uncategorized';
            if($category !== 'Product'){
                $groupedPosts[$category][] = $post;
            }
        }
    }
    
    // Ads
    $adsData = [];
    $featuredDeals = [];
    if ($postsApiData['type'] == 0 && $postsApiData['page'] == 1 && !isset($postsApiData['post_id']) && !isset($postsApiData['user_id'])) {
        $adsApiData = [
            'type' => 3,
            'search' => $postsApiData['search'] ?? '',
        ];
        if (!empty($_POST['user_id'])) $adsApiData['user_id'] = cleanInputs($_POST['user_id']);
        if (!empty($_POST['ads_id'])) $adsApiData['ads_id'] = cleanInputs($_POST['ads_id']);

        $adsResponse = sendCurlRequest(BASE_URL.'/get-ads', 'GET', $adsApiData);
        $adsData = json_decode($adsResponse, true)['body'] ?? [];
        foreach ($adsData as &$ad) $ad['source'] = 'ad'; unset($ad);

        // Featured deals
        $fdApiData = ['featured_deals' => 1, 'type' => 2];
        $responseFD = sendCurlRequest(BASE_URL.'/get-posts', 'GET', $fdApiData);
        $featuredDeals = json_decode($responseFD, true)['body'] ?? [];
    }

    // Filter available ads
    $shownAdIds = $_SESSION['shown_ad_ids'] ?? [];
    $adsCount = count($adsData);
    //if (count($shownAdIds) >= $adsCount) $shownAdIds = [];
    $shownAdIds = [];

    $availableAds = array_values(array_filter($adsData, fn($ad) => !in_array($ad['id'], $shownAdIds)));

    $postCardsHtml = '';
    $totalFeedCounter = 0;
    $postCounter = 0;
    $adPointer = 0;
    $featuredDealsInserted = false;

    // ---- Grouped rendering if type == 1 and ad_type == all ----
    if ($isGroupedByCategory) {
        foreach ($groupedPosts as $categoryName => $posts) {
            ob_start();

            $postCardsHtml .= '<div class="d-flex mt-2 justify-content-between align-items-end">
                <h2 class="f-32-b m-0 text-dark">'. htmlspecialchars(ucwords($categoryName)) .'</h2>
                <div>
                  <a href="?type=1&listing_cat='.urlencode($categoryName).'" class="set-view-all">View All <img src="assets/images/arrow-left.png" alt="arrow left"></a>
                </div>
              </div>';

            $count = 0;
            foreach ($posts as $post) {
                if ($count >= 2) break;
                $postCardsHtml .= renderPostCard($post, $postsApiData['type'], 0, $isGuestMode, $post['show_image']);
                $totalFeedCounter++;
                $postCounter++;
                $count++;
            }
         
            $postCardsHtml .= ob_get_clean();
               if($count < 2){
                $postCardsHtml .= '<div class="col-lg-6 mt-12">
                    <div class="set-card-categories set-custom-add-listing-card-main position-relative"><a href="#" class="d-block"><div class="set-round-box-add mx-auto"><img src="assets/images/add.png" alt="img" /></div><h4 class="mt-3 f-20-g fw-semibold">Add New</h4><p class="f-14-grey fw-medium">Create and share your listing</p>
                        </a>
                    </div>
                </div>';
            }
        }
    } else {
        // ---- Default rendering ----
        foreach ($postsData as $index => $post) {
            $postCardsHtml .= renderPostCard($post, $postsApiData['type'], 0, $isGuestMode, $show_listing_images);
            $totalFeedCounter++;
            $postCounter++;

            if ($postsApiData['page'] == 1 && !$featuredDealsInserted && $fd_shown_after_x_feeds > 0 && $totalFeedCounter == $fd_shown_after_x_feeds) {
                $postCardsHtml .= getFeaturedDealsHtmlFn($featuredDeals);
                $featuredDealsInserted = true;
            }

            $interval = ($index % 2 == 0) ? 5 : 6;
            if ($postCounter % $interval === 0 && isset($availableAds[$adPointer])) {
                $ad = $availableAds[$adPointer];
                $postCardsHtml .= renderPostCard($ad, 3, 0, $isGuestMode);
                $shownAdIds[] = $ad['id'];
                $adPointer++;
                $totalFeedCounter++;

                if ($postsApiData['page'] == 1 && !$featuredDealsInserted && $fd_shown_after_x_feeds > 0 && $totalFeedCounter == $fd_shown_after_x_feeds) {
                    ob_start();
                    include('includes/featured-deals.php');
                    $postCardsHtml .= ob_get_clean();
                    $featuredDealsInserted = true;
                }
            }
        }
    }

    // If very few posts, still show remaining ads
    if (empty($postsData) || $postCounter < 5) {
        foreach ($availableAds as $ad) {
            if (!in_array($ad['id'], $shownAdIds)) {
                $postCardsHtml .= renderPostCard($ad, 3, 0, $isGuestMode);
                $shownAdIds[] = $ad['id'];
                $totalFeedCounter++;

                if (!$featuredDealsInserted && $fd_shown_after_x_feeds > 0 && $totalFeedCounter == $fd_shown_after_x_feeds) {
                    ob_start();
                    include('includes/featured-deals.php');
                    $postCardsHtml .= ob_get_clean();
                    $featuredDealsInserted = true;
                }
            }
        }
    }

    // CASE 1: type == 0 → check both posts and ads, and page should be 1
    if ($postsApiData['type'] == 0 && $postsApiData['page'] == 1 && count($postsData) == 0 && count($adsData) == 0 && count($featuredDeals) == 0) {
        $title = "Nothing to Display Yet! Start an Activity to See Some Results.";
        $button = "";
        echo renderNoDataCardPosts($title, $button);
        return;
    }

    // CASE 2: type > 0 → only check posts, and page should be 1
    if ($postsApiData['type'] > 0 && $postsApiData['page'] == 1 && count($postsData) == 0) {
        $title = "Nothing to Display Yet! Start an Activity to See Some Results.";
        $button = "";
        echo renderNoDataCardPosts($title, $button);
        return;
    }

    if(isset($adsApiData['ads_id']) &&  $adsApiData['ads_id'] > 0 && $postsApiData['page'] == 1){
        $postCardsHtml .= '<button onclick="removeIdAndReload()" class="mt-3 upload-buttom-top sk-gradint-button-one">
            Load All Feeds
            <img src="assets/img/Arrow Forward.svg" alt="Arrow" />
        </button>';
    }

    $_SESSION['shown_ad_ids'] = $shownAdIds;

    echo $postCardsHtml;
}

// Forgot Password
if(isset($_GET['action']) && $_GET['action'] == "forgot_password") {
    $apiData = [
        'email'=> cleanInputs($_POST['email'] ?? ''),
    ];
    $response = sendCurlRequest(BASE_URL.'/forgot_password', 'POST', $apiData);
    $responseArr = json_decode($response,true);
    // dump($responseArr);
    if($responseArr['success']){
        echo json_encode(["success" => 1, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => 0, 'message' => $responseArr['message']]);
    }
}



/**********
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 *  S A L E S
 * 
 * 
 *  P O R T A L
 * 
 * 
 * 
 * 
 */


// Get commissions
if (isset($_GET['action']) && $_GET['action'] === "get_commissions_sales") {

    // Input sanitization
    $postsApiData = [
        'page'   => cleanInputs($_POST['page'] ?? ''),
        'limit'  => cleanInputs($_POST['limit'] ?? ''),
        'type' => cleanInputs($_POST['type'] ?? ''),
    ];

    // Posts
    $postsResponse = sendCurlRequest(BASE_URL.'/get-sales-person-activity', 'GET', $postsApiData);
    
    $postsData = json_decode($postsResponse, true)['body'] ?? [];
    foreach ($postsData as &$post) $post['source'] = 'post'; unset($post);
    //dump($postsData);

    $postCardsHtml = '';
    $totalFeedCounter = 0;
    $postCounter = 0;
    $featuredDealsInserted = false;

    // ---- Default rendering ----
    foreach ($postsData as $index => $post) {
        $postCardsHtml .= renderCommissionCardSales($post,$postsApiData['type']);
        $totalFeedCounter++;
        $postCounter++;
    }

    if ($postsApiData['page'] == 1 && count($postsData) == 0) {

        echo '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500"><section id="no-commissions-empty" class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 mb-8"><div class="text-center max-w-md mx-auto"><div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6"><i class="fa-solid fa-dollar-sign text-green-600 text-2xl"></i></div><h3 class="text-xl font-semibold text-gray-900 mb-3">No commissions earned</h3><p class="text-gray-600 mb-6">Send more links and invite users to communities to start earning commissions on successful conversions.</p><div class="flex space-x-3 justify-center"><a href="create-post.php"><button class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-medium hover:bg-blue-700 flex items-center space-x-2"><i class="fa-solid fa-link"></i><span>Create Post</span></button></a><a href="create-community.php"><button class="bg-purple-600 text-white px-5 py-2.5 rounded-lg font-medium hover:bg-purple-700 flex items-center space-x-2"><i class="fa-solid fa-users"></i><span>Create Community</span></button></a></div></div></section></td></tr>';
        return;
    }

    echo $postCardsHtml;
}

// Get link data posts / community
if (isset($_GET['action']) && $_GET['action'] === "get_posts_sales") {

    $isGuestMode = isset($_SESSION['hm_wb_auth_data']['id']) ? 0 : 1;

    // Input sanitization
    $postsApiData = [
        'page'   => cleanInputs($_POST['page'] ?? ''),
        'limit'  => cleanInputs($_POST['limit'] ?? ''),
        'search' => cleanInputs($_POST['search'] ?? ''),
        'type' => cleanInputs($_POST['type'] ?? ''),
        'link_type' => cleanInputs($_POST['link_type'] ?? ''),
    ];

    if($postsApiData['type'] == 'high' || $postsApiData['type'] == 'recent'){
        $postsApiData['sort_type'] = $postsApiData['type'];
    }

    if (!empty($_POST['user_id'])) $postsApiData['user_id'] = cleanInputs($_POST['user_id']);
    if (!empty($_POST['link_id'])) $postsApiData['link_id'] = cleanInputs($_POST['post_id']);
    if (!empty($_POST['status'])) $postsApiData['status'] = cleanInputs($_POST['status']);

    unset($postsApiData['type']);
    $postsApiData['type'] = $postsApiData['link_type'];
    unset($postsApiData['link_type']);

    // Posts
    //dump($postsApiData);
    $postsResponse = sendCurlRequest(BASE_URL.'/get-sale-person-link', 'GET', $postsApiData);
    
    $postsData = json_decode($postsResponse, true)['body'] ?? [];
    foreach ($postsData as &$post) $post['source'] = 'post'; unset($post);

    if(isset($postsApiData['ads_id'])){
        $postsData = [];
    }

    $postCardsHtml = '';
    $totalFeedCounter = 0;
    $postCounter = 0;
    $featuredDealsInserted = false;

    // ---- Default rendering ----
    foreach ($postsData as $index => $post) {
        $postCardsHtml .= renderPostCardSales($post,$postsApiData['type']);
        $totalFeedCounter++;
        $postCounter++;
    }

    if ($postsApiData['page'] == 1 && count($postsData) == 0) {

        echo '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500"><section class="bg-white rounded-xl shadow-sm border border-gray-100 p-2"><div class="text-center max-w-md mx-auto"><div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6"><i class="fa-solid fa-search text-gray-400 text-2xl"></i></div><h3 class="text-xl font-semibold text-gray-900 mb-3">No results found</h3><p class="text-gray-600 mb-6">We couldn\'t find any matches for your search. Try adjusting your search terms or filters.</p><button class="bg-gray-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-gray-700 flex items-center space-x-2 mx-auto" onclick="location.reload()"><i class="fa-solid fa-times"></i><span>Clear Search</span></button></div></section></td></tr>';
        return;
    }

    echo $postCardsHtml;
}

// get user_posts for existing post ( link generation )
if (isset($_GET['action']) && $_GET['action'] === "sp_get_user_posts_existing_dropdown") {

    // Input sanitization
    $postsApiData = [
        'type'  =>  0,
        'page'   => cleanInputs($_POST['page'] ?? ''),
        'limit'  => cleanInputs($_POST['limit'] ?? ''),
        'search' => cleanInputs($_POST['search'] ?? ''),
        'user_id' => cleanInputs($_POST['user_id'] ?? ''),
    ];

    // Posts
    //dump($postsApiData);
    $postsResponse = sendCurlRequest(BASE_URL.'/get-posts', 'GET', $postsApiData);
    $postsData = json_decode($postsResponse, true)['body'] ?? [];
    foreach ($postsData as &$post) $post['source'] = 'post'; unset($post);

    $postCardsHtml = '';
    $totalFeedCounter = 0;
    $postCounter = 0;
    $featuredDealsInserted = false;

    // ---- Default rendering ----
    foreach ($postsData as $index => $post) {
        $postCardsHtml .= renderExistingPostDropdownSales($post);
        $totalFeedCounter++;
        $postCounter++;
    }

    if ($postsApiData['page'] == 1 && count($postsData) == 0) {
        if(!empty($postsData['search'])){
            $title = 'We couldn\'t find any matches for your search. Try adjusting your search terms or filters';
        }else{
            $title = 'No posts yet. Click the ‘Create Post’ button to share your first one!';
        }
        echo '<section class="bg-white rounded-xl shadow-sm border border-gray-100 p-2 w-full"><div class="text-center max-w-md mx-auto"><p class="text-gray-600 mb-2">'.$title.'</p></div></section>';
        return;
    }

    echo $postCardsHtml;
}

// get message templates
if (isset($_GET['action']) && $_GET['action'] === "get_referral_template_sp") {

    // Input sanitization
    $postsApiData = [
        'page'      => cleanInputs($_POST['page'] ?? 1),
        'limit'      => cleanInputs($_POST['limit'] ?? 3000),
        'type'      => cleanInputs($_POST['type'] ?? ''),
        'search'    => cleanInputs($_POST['search'] ?? ''),
        'filter'    => cleanInputs($_POST['filter'] ?? ''),
        'isMt'      => $_POST['isMT'],
    ];

    $postsResponse = sendCurlRequest(BASE_URL.'/get-referral-template', 'GET', $postsApiData);
    $postsData = json_decode($postsResponse, true)['body'] ?? [];

    if($postsApiData['isMt'] == 1){
        $postCardsHtml = '';
        foreach ($postsData as $index => $post) {
            $postCardsHtml .= renderMessageTemplateDropdownSales($post);
        }

        if ($postsApiData['page'] == 1 && count($postsData) == 0) {

            if($postsApiData['search'] != ''){
                echo '<section class="w-full bg-white rounded-xl shadow-sm border border-gray-100 p-2"><div class="text-center max-w-md mx-auto"><div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6"><i class="fa-solid fa-search text-gray-400 text-2xl"></i></div><h3 class="text-xl font-semibold text-gray-900 mb-3">No results found</h3><p class="text-gray-600 mb-6">We couldn\'t find any matches for your search. Try adjusting your search terms or filters.</p><button class="bg-gray-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-gray-700 flex items-center space-x-2 mx-auto" onclick="location.reload()"><i class="fa-solid fa-times"></i><span>Clear Search</span></button></div></section>';
            }else{
                echo '
                <section id="no-templates-empty" class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 mb-8">
                    <div class="text-center max-w-md mx-auto">
                        <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fa-solid fa-message text-orange-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">No '.ucwords(str_replace('_', ' ', $postsApiData['type'])).' templates</h3>
                        <p class="text-gray-600 mb-6">Create reusable message templates for WhatsApp, SMS, and Email to streamline your outreach process.</p>
                    </div>
                </section>';
            }
            return;
        }

        echo $postCardsHtml;
        return;
    }else{
        echo json_encode($postsData);
    }
}

// Save Message Template
if(isset($_GET['action']) && $_GET['action'] == "save_message_template_sp") {
    $save_template          =   cleanInputs($_POST['save_template']);
    $apiData = [
        'save_template' => $save_template,
    ];
    $response = sendCurlRequest(BASE_URL.'/edit-profile', 'PUT', $apiData);
    $decodedResponse = json_decode($response, true);
    echo $response;
}

// Save FCM Token
if(isset($_GET['action']) && $_GET['action'] == "save_user_fcm_token") {
    $device_token          =   cleanInputs($_POST['token']);
    $apiData = [
        'device_token' => $device_token,
    ];
    $response = sendCurlRequest(BASE_URL.'/edit-profile', 'PUT', $apiData);
    $decodedResponse = json_decode($response, true);
    echo $response;
}

// delete_community
if(isset($_GET['action']) && $_GET['action'] == "delete_template_sp") {
    // Initialize API data with required fields
    $apiData = [
        'id' => cleanInputs($_POST['id']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/delete-user-template/'.$apiData['id'], 'DELETE', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// send email invite
if (isset($_GET['action']) && $_GET['action'] === "sendEmailInvite") {

    $emails        = $_POST['emails'] ?? [];
    $message       = ($_POST['messageFn'] ?? '');

    // Prepare API payload
    $apiData = [
        'toRecipients'   => implode(',',$emails),
        'message'        => $message,
        'type'           => '1'
    ];

    // Call external API
    $response = sendCurlRequest(BASE_URL . '/send-invitation', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);

    // Handle API response
    if ($decodedResponse['success']) {
        echo json_encode([
            "success" => true,
            "message" => $decodedResponse['message'] ?? "Invitations sent successfully!"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => $decodedResponse['message'] ?? "Failed to send invites."
        ]);
    }

    exit;
}

// send phone invite
if (isset($_GET['action']) && $_GET['action'] === "sendPhoneInvite") {

    $phones        = $_POST['phones'] ?? [];
    //$message       = cleanInputs($_POST['messageFn'] ?? '');
    $message       = cleanInputs($_POST['plainMessage'] ?? '');

    $errors = [];
    // Prepare API payload
    $apiData = [
        'toRecipients'   => implode(',',$phones),
        'message'        => $message,
        'type'           => '0'
    ];

    // Call external API
    $response = sendCurlRequest(BASE_URL . '/send-invitation', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);

    // Handle API response
    if ($decodedResponse['success']) {
        echo json_encode([
            "success" => true,
            "message" => $decodedResponse['message'] ?? "Invitations sent successfully!"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => $decodedResponse['message'] ?? "Failed to send invites."
        ]);
    }

    exit;
}

// send whatsapp invite
if (isset($_GET['action']) && $_GET['action'] === "sendWhatsAppInvite") {

    $phone   = $_POST['whtsph'] ?? '';
    $message = $_POST['plainMessage'] ?? '';

    // Make sure message is properly URL encoded (UTF-8 safe)
    $encodedMessage = rawurlencode($message);

    // Build WhatsApp URL
    $waUrl = "https://wa.me/{$phone}?text={$encodedMessage}";

    echo json_encode([
        "success" => true,
        "whatsappUrl" => $waUrl
    ]);

    exit;
}

// delete_post_referral
if(isset($_GET['action']) && $_GET['action'] == "delete_post_referral_sales") {
    // Initialize API data with required fields
    $apiData = [
        'link_id' => cleanInputs($_POST['link_id']),
    ];
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/delete-referral-link?link_id='.$apiData['link_id'], 'DELETE', $apiData);
    $responseArr = json_decode($response,true);
    if($responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    }else{
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// early_access_form
if(isset($_GET['action']) && $_GET['action'] == "early_access_form") {
    // Initialize API data with required fields
    $company = cleanInputs(ucwords($_POST['company'] ?? ''));
    $type = cleanInputs($_POST['audience_type'] ?? '');
    $email = cleanInputs($_POST['email'] ?? '');
    $phone = cleanInputs($_POST['phone'] ?? '');

    $recaptchaSecret = RECAPTCHA_SECRET_KEY;
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}&response={$recaptchaResponse}");
    $responseData = json_decode($verify);

    if (empty($responseData->success) || !$responseData->success) {
        echo json_encode([
            "success" => false, 
            "message" => "reCAPTCHA verification failed."
        ]);
        exit;
    }

    // Backend validation: email and phone should not be blank
    if(empty($email) || empty($phone)) {
        echo json_encode([
            "success" => false, 
            "message" => "Email and phone are required."
        ]);
        exit; // Stop further execution
    }

    $apiData = [
        'company_name' => $company,
        'type' => $type,
        'email' => $email,
        'phone' => $phone,
    ];

    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/early-access-user', 'POST', $apiData);
    $responseArr = json_decode($response, true);

    if(!empty($responseArr['success']) && $responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    } else {
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}

// report_server_crash
if(isset($_GET['action']) && $_GET['action'] == "report_server_crash") {
    // Make the API request and get the response
    $response = sendCurlRequest(BASE_URL.'/server-down', 'POST', []);
    $responseArr = json_decode($response, true);

    if(!empty($responseArr['success']) && $responseArr['success']){
        echo json_encode(["success" => true, 'message' => $responseArr['message']]);
    } else {
        echo json_encode(["success" => false, 'message' => $responseArr['message']]);
    }
}