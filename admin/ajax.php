<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once('utils/helpers.php');
date_default_timezone_set('UTC');

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
    $response = sendCurlRequest(BASE_URL.'/adminLogin', 'POST', $apiData);
    $decodedResponse = json_decode($response, true);

    if($decodedResponse['success']){
        $_SESSION['hm_timezone'] = $timezone;
        $_SESSION['hm_auth_data'] = $decodedResponse['body'];
        $_SESSION['hm_logged_in'] = true;
    }

    echo $response;
}

// logoutUser
if(isset($_GET['action']) && $_GET['action'] == "logoutUser"){
    $apiData = [];
    $response = sendCurlRequest(BASE_URL.'/logout', 'PUT', $apiData);
    $decodedResponse = json_decode($response, true);

    if($decodedResponse['success']){
        unset($_SESSION['hm_auth_data']);
        unset($_SESSION['hm_logged_in']);
        unset($_SESSION['hm_timezone']);
    }

    echo $response;
}

/*******
 * 
 * **************************************************************************
 * *************     C O U N T R I E S           *******************
* ***************        M O D U L E            ****************
 * **************************************************************************
 * 
 * 
 *******/

// changeCountryStatus
if(isset($_GET['action']) && $_GET['action'] == "changeCountryStatus"){
    $code     =  cleanInputs($_POST['code']);
    $apiData = ['code' => $code];
    $response = sendCurlRequest(BASE_URL.'/countries/'.$code.'/status', 'PATCH', $apiData);
    echo $response;
}

// deleteCountry
if(isset($_GET['action']) && $_GET['action'] == "deleteCountry"){
    $code     =  cleanInputs(base64_decode($_POST['id']));
    $apiData = [];
    $response = sendCurlRequest(BASE_URL.'/countries/'.$code, 'DELETE', $apiData);
    echo $response;
}

// get_countries
if(isset($_GET['action']) && $_GET['action']=="get_countries"){
    // dump($_POST);

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw'], 'status' => @$_POST['status']);

    $page = floor($input['start'] / $input['length']) + 1;

    // set sorting
    $sortColumnMap = [
        1 => 'country_code',
        2 => 'name',
        3 => 'currency_code',
        5 => 'timezone',
        6 => 'currency_multiplier',
        7 => 'status',
        8 => 'created_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $apiData = ['search' => $input['search'], 'status' => $input['status'], 'account_type' => 0, 'page' => $page, 'limit' => $input['length'],'sortColumn' => $sortColumn,'sortType' => $sortType];
    //dump($apiData);

    $response = sendCurlRequest(BASE_URL.'/countries', 'GET', $apiData);

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
    //dump($decodedResponse);

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {

            $userStatus = "";
            if($row['is_active'] == 1){
                $userStatus = '<div class="content userActive" status="0" code="'.$row['country_code'].'" onclick="changeCountryStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to deactivate"> Active</div>';
            }else{
                $userStatus = '<div class="content userInactive" status="1" code="'.$row['country_code'].'" onclick="changeCountryStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to activate"> Inactive</div>';
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="content userHandle"> '.($row['id'] ? $row['id'] : '-').'</div>',
                '<div class="content userHandle"> '.($row['country_code'] ? $row['country_code'] : '-').'</div>',
                '<div class="content d-flex flex-row align-items-center userName">
                '.$row['name'].'</div>',
                '<div class="content userHandle text-"> '.($row['currency_code'] ? $row['currency_code'] : 'N/A').'</div>',
                '<div class="content userHandle"> '.$row['currency_symbol'].'</div>',
                '<div class="content userHandle text-"> '.$row['timezone'].'</div>',
                '<div class="content userHandle text-primary"> '.($row['currency_code'] ? $row['currency_multiplier'] : 'N/A').'</div>',
                ''.$userStatus.'',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                '' . renderActionButtons(base64_encode($row['country_code']), 'countries', 'countries.php?id=', 'view-user.php?id=', false, true, true) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
            );
        }
    }

    $json_data = array(
                    "draw"=> intval( $input['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                    "recordsTotal"    => intval(count($final)),  // total number of records
                    "recordsFiltered" => intval($decodedResponse['meta']['pagination']['total_records']), // total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"            => $final   // total data array
                );

    echo  json_encode($json_data);
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

// changeUserStatus
if(isset($_GET['action']) && $_GET['action'] == "changeUserStatus"){
    $status     =  cleanInputs($_POST['status']);
    $user_id    =  cleanInputs($_POST['userId']);
    $apiData = ['status' => $status];
    $response = sendCurlRequest(BASE_URL.'/edit-profile-admin?user_id='.$user_id, 'PUT', $apiData);
    echo $response;
}

// changeProfileGroupStatus
if(isset($_GET['action']) && $_GET['action'] == "changeProfileGroupStatus"){
    $status     =  cleanInputs($_POST['status']);
    $id    =  cleanInputs($_POST['id']);
    $apiData = ['status' => $status, 'id' => $id];
    $response = sendCurlRequest(BASE_URL.'/profile-group-edit', 'POST', $apiData);
    echo $response;
}

// deleteUser
if(isset($_GET['action']) && $_GET['action'] == "deleteUser"){
    $id     =  cleanInputs(base64_decode($_POST['id']));
    $apiData = [];
    $response = sendCurlRequest(BASE_URL.'/delete-account-admin?id='.$id, 'DELETE', $apiData);
    echo $response;
}

// deleteProfileGroup
if(isset($_GET['action']) && $_GET['action'] == "deleteProfileGroup"){
    $id     =  cleanInputs(base64_decode($_POST['id']));
    $apiData = [];
    $response = sendCurlRequest(BASE_URL.'/profile-group-delete?id='.$id, 'GET', $apiData);
    echo $response;
}

// deleteMultiUser
if(isset($_GET['action']) && $_GET['action'] == "deleteMultiUser"){
    $ids    =  cleanInputs($_POST['ids']);
    $apiData = ['ids' => $ids];
    $response = sendCurlRequest(BASE_URL.'/delete-multi-users', 'POST', $apiData);
    echo $response;
}

// get_users
if(isset($_GET['action']) && $_GET['action']=="get_users"){
    // dump($_POST);

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw'], 'status' => @$_POST['status']);

    $page = floor($input['start'] / $input['length']) + 1;

    // set sorting
    if($input['status'] == 2){
        $sortColumnMap = [
            0 => 'type',
            1 => 'company_name',
            2 => 'email',
            3 => 'phone',
            4 => 'created_at',
        ];
    }else{
        $sortColumnMap = [
            2 => 'name',
            3 => 'handle_name',
            4 => 'email',
            5 => 'total_posts',
            6 => 'total_ads',
            7 => 'last_posted_date',
            8 => 'status',
            9 => 'created_at',
        ];
    }
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $apiData = ['search' => $input['search'], 'status' => $input['status'], 'account_type' => 0, 'page' => $page, 'limit' => $input['length'],'sortColumn' => $sortColumn,'sortType' => $sortType];
    //dump($apiData);

    if($apiData['status'] == 2){
        $response = sendCurlRequest(BASE_URL.'/admin-get-early-access-user-listing', 'GET', $apiData);
    }else{
        $response = sendCurlRequest(BASE_URL.'/all-users', 'GET', $apiData);
    }

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
    //dump($decodedResponse);

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {

            if($apiData['status'] == '2'){

                $final[] = array(
                    "DT_RowId" => $row['id'],
                    '<div class="content userHandle"> #'.($row['id'] ? $row['id'] : 'N/A').'</div>',
                    '<div class="content userHandle"> '.($row['type'] ? $row['type'] : 'N/A').'</div>',
                    '<div class="content userHandle"> '.($row['company_name'] ? $row['company_name'] : 'N/A').'</div>',
                    '<div class="content userHandle"> '.($row['email'] ? $row['email'] : 'N/A').'</div>',
                    '<div class="content userHandle"> '.($row['phone'] ? $row['phone'] : 'N/A').'</div>',
                    '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                );
            }else{

                $userStatus = "";
                if($row['status'] == 1){
                    $userStatus = '<div class="content userActive" status="0" user-id="'.$row['id'].'" onclick="changeUserStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to deactivate"> Active</div>';
                }else{
                    $userStatus = '<div class="content userInactive" status="1" user-id="'.$row['id'].'" onclick="changeUserStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to activate"> Inactive</div>';
                }

                $imgData = "";
                if(empty($row['image'])){
                    $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" alt="" />';
                }else{
                    $imgData = '<img src="'. MEDIA_BASE_URL .''.$row['image'].'" alt="" onerror="this.onerror=null;this.src=\'assets/img/fav-icon.png\';" />';
                }

                $viewPermission = true;
                if (!hasPermission('Users', 'view')) {
                    $viewPermission = false;
                }

                $editPermission = true;
                if (!hasPermission('Users', 'edit')) {
                    $editPermission = false;
                }

                $deletePermission = true;
                if (!hasPermission('Users', 'delete')) {
                    $deletePermission = false;
                }

                $final[] = array(
                    "DT_RowId" => $row['id'],
                    '<input type="checkbox" class="chk-box" value="'.$row['id'].'" />',
                    '<div class="content userHandle"> '.($row['id'] ? $row['id'] : '0').'</div>',
                    '<div class="content d-flex flex-row align-items-center userName">
                    '.$imgData.' '.$row['name'].'</div>',
                    '<div class="content userHandle text-primary"> @'.($row['handle_name'] ? $row['handle_name'] : 'N/A').'</div>',
                    '<div class="content userHandle"> '.$row['email'].'</div>',
                    '<div class="content userHandle text-success"> '.$row['total_posts'].'</div>',
                    '<div class="content userHandle text-info"> '.$row['total_ads'].'</div>',
                    '<div class="content userHandle text-info"> '.($row['last_posted_date'] == '0' ? 'N/A' : formatToUSDate($row['last_posted_date'], 1)).'</div>',
                    ''.$userStatus.'',
                    '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                    '' . renderActionButtons(base64_encode($row['id']), 'users', 'view-user.php?id=', 'view-user.php?id=', $viewPermission, $editPermission, $deletePermission) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
                );
            }
        }
    }

    $json_data = array(
                    "draw"=> intval( $input['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                    "recordsTotal"    => intval(count($final)),  // total number of records
                    "recordsFiltered" => intval($apiData['status'] == 2 ?  $decodedResponse['meta']['totalItems'] : $decodedResponse['meta']['total']), // total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"            => $final   // total data array
                );

    echo  json_encode($json_data);
}

// get_profile_groups
if(isset($_GET['action']) && $_GET['action']=="get_profile_groups"){
    // dump($_POST);

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw'], 'status' => @$_POST['status']);

    $page = floor($input['start'] / $input['length']) + 1;

    // set sorting
    $sortColumnMap = [
        1 => 'name',
        2 => 'status',
        3 => 'created_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $apiData = ['search' => $input['search'], 'status' => $input['status'], 'page' => $page, 'limit' => $input['length'],'sortColumn' => $sortColumn,'sortType' => $sortType];
    //dump($apiData);

    $response = sendCurlRequest(BASE_URL.'/profile-group-list', 'GET', $apiData);

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

            $userStatus = "";
            if($row['status'] == 1){
                $userStatus = '<div class="content userActive" status="0" pg-id="'.$row['id'].'" onclick="changeProfileGroupStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to deactivate"> Active</div>';
            }else{
                $userStatus = '<div class="content userInactive" status="1" pg-id="'.$row['id'].'" onclick="changeProfileGroupStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to activate"> Inactive</div>';
            }

            $viewPermission = true;
            if (!hasPermission('Sub Admins', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Sub Admins', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Sub Admins', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                $key + 1,
                '<div class="content d-flex flex-row align-items-center userName">
                '.$row['name'].'</div>',
                ''.$userStatus.'',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                '' . renderActionButtons(base64_encode($row['id']), 'profileGroups', 'profile-groups.php?id=', '', false, $editPermission, $deletePermission) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
            );
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

// get_sub_admins
if(isset($_GET['action']) && $_GET['action']=="get_sub_admins"){
    // dump($_POST);

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw'], 'status' => @$_POST['status']);

    $page = floor($input['start'] / $input['length']) + 1;

    // set sorting
    $sortColumnMap = [
        1 => 'name',
        2 => 'handle_name',
        3 => 'email',
        7 => 'status',
        8 => 'created_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $apiData = ['search' => $input['search'], 'status' => $input['status'], 'account_type' => 2, 'page' => $page, 'limit' => $input['length'],'sortColumn' => $sortColumn,'sortType' => $sortType];
    //dump($apiData);

    $response = sendCurlRequest(BASE_URL.'/all-users', 'GET', $apiData);

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

            $userStatus = "";
            if($row['status'] == 1){
                $userStatus = '<div class="content userActive" status="0" user-id="'.$row['id'].'" onclick="changeUserStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to deactivate"> Active</div>';
            }else{
                $userStatus = '<div class="content userInactive" status="1" user-id="'.$row['id'].'" onclick="changeUserStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to activate"> Inactive</div>';
            }

            $imgData = "";
            if(empty($row['image'])){
                $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" alt="" />';
            }else{
                $imgData = '<img src="'. MEDIA_BASE_URL .''.$row['image'].'" alt="" onerror="this.onerror=null;this.src=\'assets/img/fav-icon.png\';" />';
            }

            $viewPermission = true;
            if (!hasPermission('Sub Admins', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Sub Admins', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Sub Admins', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="content d-flex flex-row align-items-center userName">
                '.$imgData.' '.$row['name'].'</div>',
                '<div class="content userHandle text-primary"> @'.($row['handle_name'] ? $row['handle_name'] : 'N/A').'</div>',
                '<div class="content userHandle"> '.$row['email'].'</div>',
                '<div class="content userHandle"> '.$row['plan_password'].'</div>',
                ''.$userStatus.'',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                '' . renderActionButtons(base64_encode($row['id']), 'users', 'sub-admin.php?id=', 'view-user.php?id=', false, $editPermission, $deletePermission) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
            );
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

// get_user_companies
if(isset($_GET['action']) && $_GET['action']=="get_user_companies"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0]['column'], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['user_id' => base64_decode($_POST['userId']), 'search' => $input['search'], 'page' => $page, 'limit' => $input['length']];

    $response = sendCurlRequest(BASE_URL.'/get-company', 'GET', $apiData);

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
    //dump($decodedResponse);

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {

            $dStatus = "";
            if($row['status'] == 1){
                $dStatus = '<div class="content userActive" status="0" user-id="'.$row['id'].'" onclick="changeUserStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to deactivate"> Active</div>';
            }else{
                $dStatus = '<div class="content userInactive" status="1" user-id="'.$row['id'].'" onclick="changeUserStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to activate"> Inactive</div>';
            }

            $imgData = "";
            if(empty($row['logo'])){
                $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" alt="" />';
            }else{
                $imgData = '<img src="'. MEDIA_BASE_URL .''.$row['logo'].'" alt="" onerror="this.onerror=null;this.src=\'assets/img/fav-icon.png\';" />';
            }

            $viewPermission = true;
            if (!hasPermission('Companies', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Companies', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Companies', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="content d-flex flex-row align-items-center userName text-primary">
                '.$imgData.' '.$row['name'].'</div>',
                '<div class="content userHandle"> '.($row['role'] ? $row['role'] : 'N/A').'</div>',
                '<div class="content userHandle"> '.$row['email'].'</div>',
                ''.$dStatus.'',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                '' . renderActionButtons(base64_encode($row['id']), 'users', 'edit-user.php?id=', 'company-details.php?id=', $viewPermission, false, false) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
            );
        }
    }

    $json_data = array(
                    "draw"=> intval( $input['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                    "recordsTotal"    => intval(count($final)),  // total number of records
                    "recordsFiltered" => intval($decodedResponse['meta']['totalItems']), // total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"            => $final   // total data array
                );

    echo  json_encode($json_data);
}

// get_user_posts
if(isset($_GET['action']) && $_GET['action']=="get_user_posts"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0]['column'], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['user_id' => base64_decode($_POST['userId']), 'type' => 0, 'search' => $input['search'], 'page' => $page, 'limit' => $input['length']];

    $response = sendCurlRequest(BASE_URL.'/get-posts', 'GET', $apiData);

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

    //dump($decodedResponse);

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {

            $userStatus = "";
            if($row['status'] == 1){
                $userStatus = '<div class="content userActive" status="0" user-id="'.$row['id'].'"> Active</div>';
            }else{
                $userStatus = '<div class="content userInactive" status="1" user-id="'.$row['id'].'"> Inactive</div>';
            }

            $viewPermission = true;
            if (!hasPermission('Posts', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Posts', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Posts', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="content d-flex flex-row align-items-center justify-content-between userNms">
                '.$row['user']['name'].'</div>',
                '<div class="content userNo"> '.($row['user_type'] ==0  ? 'No' : 'Yes').'</div>',
                '<div class="content userNo"> '.$row['company_name'].'</div>',
                '<div class="content userVw d-flex justify-content-center align-items-center"> '.$row['total_spotted'].'</div>',
                '<div class="content userCt d-flex justify-content-center align-items-center"> '.$row['category'].'</div>',
                ''.$userStatus.'',
                '<div class="content content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                '' . renderActionButtons(base64_encode($row['id']), 'companies', 'company-details.php?id=', 'company-details.php?id=', $viewPermission, false, false) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
            );
        }
    }

    $json_data = array(
                    "draw"=> intval( $input['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                    "recordsTotal"    => intval(count($final)),  // total number of records
                    "recordsFiltered" => intval($decodedResponse['meta']['totalItems']), // total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"            => $final   // total data array
                );

    echo  json_encode($json_data);
}

// get_user_wallet_txns
if(isset($_GET['action']) && $_GET['action']=="get_user_wallet_txns"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0]['column'], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['user_id' => base64_decode($_POST['userId']), 'search' => $input['search'], 'page' => $page, 'limit' => $input['length']];

    $response = sendCurlRequest(BASE_URL.'/wallet-history', 'GET', $apiData);

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

            $msg = "";
            if($row['from'] == 0){
                $msg = "Admin Added";
            }else{
                if($row['type'] == 0){
                    $msg = "Credits used in Ads";
                }else{
                    $msg = "Credits used in Boost Post";
                }
            }

            $credits = "";
            if($row['from'] == 0){
                $credits = "<span class='text-success'> <i class'fa fa-arrow-up'></i> " . round($row['credit'],2) . "</span>";
            }else{
                $credits = "<span class='text-danger'> <i class'fa fa-arrow-down'></i> " . round($row['credit'],2) . "</span>";
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="content d-flex flex-row align-items-center userName">
                '.$msg.'</div>',
                '<div class="content userHandle"> '.$credits.'</div>',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>'
            );
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

// get_wallet_history
if(isset($_GET['action']) && $_GET['action']=="get_wallet_history"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0]['column'], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length']];

    $response = sendCurlRequest(BASE_URL.'/all-users', 'GET', $apiData);

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
    //dump($decodedResponse);

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {
            $imgData = "";
            if(empty($row['image'])){
                $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" alt="" />';
            }else{
                $imgData = '<img src="'. MEDIA_BASE_URL .''.$row['image'].'" alt="" onerror="this.onerror=null;this.src=\'assets/img/fav-icon.png\';" />';
            }

            $viewPermission = true;
            if (!hasPermission('Transactions', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Transactions', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Transactions', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<input type="checkbox" class="chk-box" value="'.$row['id'].'" />',
                '<div class="content d-flex flex-row align-items-center userName">
                '.$imgData.' '.$row['name'].'</div>',
                '<div class="content text-gray">+$'.($row['total_credit'] ? $row['total_credit'] : '0').'</div>',
                '<div class="content text-gray">-$'.($row['total_debit'] ? $row['total_debit'] : '0').'</div>',
                '<div class="content text-gray">$'.($row['wallet'] ? $row['wallet'] : '0').'</div>',
                '' . renderActionButtons(base64_encode($row['id']), 'users', '', 'view-user.php?redirect=wallet&id=', $viewPermission, false, false) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
            );
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

// deletecompay
if(isset($_GET['action']) && $_GET['action'] == "delete_wallet_history"){
    $ids    =  cleanInputs($_POST['ids']);
    // dump($ids);
    $apiData = ['ids' => $ids];
    $response = sendCurlRequest(BASE_URL.'/deleteWalletHistory', 'POST', $apiData);
    echo $response;
}


/*******
 * 
 * **************************************************************************
 * *************        C O M P A N Y           *******************
* ***************        M O D U L E            ****************
 * **************************************************************************
 * 
 * 
 *******/

// changeCompanyStatus
if(isset($_GET['action']) && $_GET['action'] == "changeCompanyStatus"){
    $status     =  cleanInputs($_POST['status']);
    $company_id    =  cleanInputs($_POST['company_id']);
    $apiData = ['status' => $status,'company_id'=>$company_id];
    $response = sendCurlRequest(BASE_URL.'/update-company', 'PUT', $apiData);
    echo $response;
}

// get_companies
if(isset($_GET['action']) && $_GET['action']=="get_companies"){
    // dump(@$_POST);

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw'],'user_id' => @$_POST['user_id'],'status' => @$_POST['status'],'address' => @$_POST['address']);

    $page = floor($input['start'] / $input['length']) + 1;

    // set sorting
    $sortColumnMap = [
        1 => 'name',
        2 => 'short_name',
        3 => 'owner_name',
        4 => 'email',
        5 => 'country_code',
        6 => 'total_posts',
        7 => 'total_ads',
        8 => 'status',
        9 => 'last_posted_date',
        11 => 'created_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length'],'user_id' => @$_POST['user_id'],'status' => @$_POST['status'],'address' => @$_POST['address'], 'sortColumn'=> $sortColumn, 'sortType'=> $sortType];

    $response = sendCurlRequest(BASE_URL.'/all-admin-company', 'GET', $apiData);

    // Decode the JSON response
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
            //  dump($row);
            $userStatus = "";
            if($row['status'] == 1){
                $userStatus = '<div class="content userActive" status="0" user-id="'.$row['id'].'" onclick="changeCompanyStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to deactivate"> Active</div>';
            }else{
                $userStatus = '<div class="content userInactive" status="1" user-id="'.$row['id'].'" onclick="changeCompanyStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to activate"> Inactive</div>';
            }
            $categorize_button='<div class="content categorized-btn">Categorized</div>';
            if(count($row['company_categories']) === 0) {
                $categorize_button='<div class="content categorized-btn uncategorized-btn showPP" rel="'.$row['id'].'" data-set="'.$row['keywords'].'" style="cursor:pointer">Uncategorized</div>';
            }

            $imgData = "";
            if(empty($row['logo'])){
                $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" alt="" />';
            }else{
                $imgData = '<img src="'. MEDIA_BASE_URL .''.$row['logo'].'" alt="" />';
            }

            // country check
            $__country ='<spam class="text-success"> Yes</div>';
            if($row['country_code']=='' || $row['country_code'] =='N/A'){
                $__country ='<spam class="text-danger" title ="The country code not available">No</div>';
            }

            $viewPermission = true;
            if (!hasPermission('Companies', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Companies', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Companies', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<input type="checkbox" class="chk-box" value="'.$row['id'].'" rel="'.$row['name'].'" />',
                '<div class="content d-flex flex-row align-items-center userName">
                '.$imgData.' <a href="company-details.php?id='.base64_encode($row['id']).'">'.ucwords(strtolower($row['name'])).'</a></div>',
                '<div class="content userHandle"> '.($row['short_name'] ? $row['short_name'] : 'N/A').'</div>',
                '<div class="content userHandle"> '.($row['owner_name'] ? $row['owner_name'] : 'N/A').'</div>',
                '<div class="content userHandle"> '.$row['email'].'</div>',
                ''.$__country.'',
                '<div class="content userHandle text-success"> '.$row['total_posts'].'</div>',
                '<div class="content userHandle text-info"> '.$row['total_ads'].'</div>',
                ''.$userStatus.'',
                '<div class="content userHandle text-info"> '.($row['last_posted_date'] == '0' ? 'N/A' : formatToUSDate($row['last_posted_date'], 1)).'</div>',

                ''.$categorize_button.'',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                '<div class="content userVw d-flex justify-content-center align-items-center increaseShareBtn" data-company-id="'.$row['id'].'"> '.$row['total_share'].'</div>',
                '' . renderActionButtons(base64_encode($row['id']), 'companies', 'company-details.php?id=', 'company-details.php?id=', $viewPermission, $editPermission, $deletePermission) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
            );
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

// get_companies_posts
if(isset($_GET['action'])  && $_GET['action']=="get_company_posts"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0]['column'], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length'],'company_id'=>$_GET['company_id']];

    $response = sendCurlRequest(BASE_URL.'/get-company-posts', 'GET', $apiData);

    // Decode the JSON response
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
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
            $userStatus = "";
            if($row['status'] == 1){
                $userStatus = '<div class="content userActive" status="0" user-id="'.$row['id'].'" onclick="changePostStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to deactivate"> Active</div>';
            }else{
                $userStatus = '<div class="content userInactive" status="1" user-id="'.$row['id'].'" onclick="changePostStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to activate"> Inactive</div>';
            }

            $viewPermission = true;
            if (!hasPermission('Companies', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Companies', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Companies', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="content d-flex flex-row align-items-center justify-content-between userNms">
                '.$row['user']['name'].'</div>',
                '<div class="content userNo"> '.($row['user_type'] ==0  ? 'No' : 'Yes').'</div>',
                '<div class="content userNo"> '.$row['company_name'].'</div>',
                '<div class="content userVw d-flex justify-content-center align-items-center"> '.$row['total_spotted'].'</div>',
                '<div class="content userCt d-flex justify-content-center align-items-center">'.((!empty($row['category'])) ? $row['category'] : 'N/A').'</div>',
                ''.$userStatus.'',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                '' . renderActionButtons(base64_encode($row['id']), 'posts', 'post-details.php?id=', 'post-details.php?id=', $viewPermission, false, $deletePermission) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
            );
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

// company_showcase
if(isset($_GET['action'])  && $_GET['action']=="company_showcase"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0]['column'], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length'],'company_id'=>$_GET['company_id']];

    $response = sendCurlRequest(BASE_URL.'/get-showcase', 'GET', $apiData);

    // Decode the JSON response
    $decodedResponse = json_decode($response, true);
      //dump($decodedResponse);
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
        foreach ($decodedResponse['body'] as $key1 => $row1) {
            foreach ($row1['showcases'] as $key => $row) {

                $imgData = "";
                if(count($row['showcase_images']) == 0){
                    $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" alt="" />';
                }else{
                    $imgData = '<img class="listImageBox" src="'. MEDIA_BASE_URL .''.$row['showcase_images'][0]['image'].'" alt="" />';
                }
    
                $userStatus = "";
                if($row['status'] == 1){
                    $userStatus = '<div class="content userActive" status="0" user-id="'.$row['id'].'"  data-bs-toggle="tooltip" data-bs-placement="top" > Active</div>';
                }else{
                    $userStatus = '<div class="content userInactive" status="1" user-id="'.$row['id'].'"  data-bs-toggle="tooltip" data-bs-placement="top" > Inactive</div>';
                }

                $viewPermission = true;
                if (!hasPermission('Companies', 'view')) {
                    $viewPermission = false;
                }

                $editPermission = true;
                if (!hasPermission('Companies', 'edit')) {
                    $editPermission = false;
                }

                $deletePermission = true;
                if (!hasPermission('Companies', 'delete')) {
                    $deletePermission = false;
                }
    
                $final[] = array(
                    "DT_RowId" => $row['id'],
                    '<div class="listImageBox">'.$imgData.'</div>',
                    '<div class="content userNo">'.$row['name'].'</div>',
                    '<div class="content userNo">'.((!empty($row1['category'])) ? $row1['category'] : 'N/A').'</div>',
                    '<div class="content">'.((!empty($row['price'])) ? $row['price'] : 'N/A').'</div>',
                    '<div class="w-162">'.
                    (( !empty($row['info']) ) 
                        ? implode(' ', array_slice(explode(' ', $row['info']), 0, 15)).((str_word_count($row['info']) > 15) ? '...' : '') 
                        : 'N/A'
                    ).
                '</div>',                    
                ''.$userStatus.'',
                    '<div class="content userHandle"> '.formatToUSDate(@$row['created_at']).'</div>',
                    '' . renderActionButtons(base64_encode($row['id']), 'companies', 'company-details.php?id=', 'company-details.php?id=', false, $editPermission, $deletePermission) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
                );
                
            }
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

// company_recommends
if(isset($_GET['action'])  && $_GET['action']=="company_recommends"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0]['column'], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length'],'company_id'=>$_GET['company_id']];

    $response = sendCurlRequest(BASE_URL.'/get-company-recommends', 'GET', $apiData);

    // Decode the JSON response
    $decodedResponse = json_decode($response, true);
    // dump($decodedResponse);
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

                $imgData = "";
                if($row['user']['image']){
                    $imgData = '<img class="listImageBox" src="'. MEDIA_BASE_URL .''.$row['user']['image'].'" alt="" />';

                }else{
                    $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" alt="" />';

                }
    
                $viewPermission = true;
                if (!hasPermission('Companies', 'view')) {
                    $viewPermission = false;
                }

                $editPermission = true;
                if (!hasPermission('Companies', 'edit')) {
                    $editPermission = false;
                }

                $deletePermission = true;
                if (!hasPermission('Companies', 'delete')) {
                    $deletePermission = false;
                }
    
                $final[] = array(
                    "DT_RowId" => $row['id'],
                    '<div class="content d-flex flex-row align-items-center userName">'.$imgData.' '.$row['user']['name'].'</div>',
                    '<div class="content userNo">Received</div>',
                    '<div class="content userActive">'.(($row['thanked'] == 0) ? 'No' : 'Yes').'</div>',
                
                    '<div class="content userHandle"> '.formatToUSDate(@$row['created_at']).'</div>',
                    '' . renderActionButtons(base64_encode($row['id']), 'company_recommends', 'company-details.php?id=', 'company-details.php?id=', false, false, $deletePermission) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
                );
                
            }
        
    }

    $json_data = array(
                    "draw"=> intval( $input['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                    "recordsTotal"    => intval(count($final) ),  // total number of records
                    "recordsFiltered" => intval($decodedResponse['meta']['total']), // total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"            => $final   // total data array
                );

    echo  json_encode($json_data);
}

// deleteCompanyRecommend
if(isset($_GET['action']) && $_GET['action'] == "deleteCompanyRecommend"){
    $id     =  cleanInputs(base64_decode($_POST['id']));
    $apiData = [];
    $response = sendCurlRequest(BASE_URL.'/deleteCompanyRecommend?id='.$id, 'DELETE', $apiData);
    echo $response;
}
// deleteCompanyMember
if(isset($_GET['action']) && $_GET['action'] == "deleteCompanyMember"){
    $id     =  cleanInputs(base64_decode($_POST['id']));
    $apiData = ['id'=> $id];
    $response = sendCurlRequest(BASE_URL.'/remove-company-member', 'POST', $apiData);
    echo $response;
}
// deleteCompanybranch
if(isset($_GET['action']) && $_GET['action'] == "deleteCompanyBranch"){
    $id     =  cleanInputs(base64_decode($_POST['id']));
    $apiData = ['id'=> $id];
    $response = sendCurlRequest(BASE_URL.'/remove-company-branch', 'POST', $apiData);
    echo $response;
}

// deletecompay
if(isset($_GET['action']) && $_GET['action'] == "delete_company"){
    $ids    =  cleanInputs($_POST['ids']);
    // dump($ids);
    $apiData = ['ids' => $ids];
    $response = sendCurlRequest(BASE_URL.'/deleteMultiCompany', 'POST', $apiData);
    echo $response;
}
// deleteSingleCompany
if(isset($_GET['action']) && $_GET['action'] == "deleteSingleCompany"){
    $id    =  cleanInputs(base64_decode($_POST['id']));
    // dump($ids);
    $apiData = [];
    $response = sendCurlRequest(BASE_URL.'/delete-company?id='.$id, 'DELETE', $apiData);
    echo $response;
}

// get company by id 
if(isset($_GET['action']) && $_GET['action'] == "getCompanyBYId"){
    $ids    =  cleanInputs($_POST['ids']);
    // dump($ids);
    $apiData = ['ids' => $ids];
    header('Content-Type: application/json'); // Ensure the correct header
    $response = sendCurlRequest(BASE_URL.'/get-company-by-id', 'POST', $apiData);
    echo json_encode($response);
}

// update_company
if(isset($_GET['action']) && $_GET['action'] == "update_company"){

    // Create POST fields
    // dump($_POST);
    $logo ='';
    $company_cover_images ='';
    $company_listing_images ='';
    if(isset($_FILES) && !empty($_FILES['logo']['name'])){
        $file =$_FILES['logo'];
        $curl = curl_init();
        $filePath = $file['tmp_name'];
        $fileName = $file['name'];
        $fileMimeType = mime_content_type($filePath);

        // Prepare the CURLFile object
        $logo = new CURLFile($filePath, $fileMimeType, $fileName);

    }
    if(isset($_FILES) && !empty($_FILES['company_cover_images']['name'])){
        $file =$_FILES['company_cover_images'];
        $curl = curl_init();
        $filePath = $file['tmp_name'];
        $fileName = $file['name'];
        $fileMimeType = mime_content_type($filePath);

        // Prepare the CURLFile object
        $company_cover_images = new CURLFile($filePath, $fileMimeType, $fileName);

    }
    if(isset($_FILES) && !empty($_FILES['company_listing_images']['name'])){
        $file =$_FILES['company_listing_images'];
        $curl = curl_init();
        $filePath = $file['tmp_name'];
        $fileName = $file['name'];
        $fileMimeType = mime_content_type($filePath);

        // Prepare the CURLFile object
        $company_listing_images = new CURLFile($filePath, $fileMimeType, $fileName);

    }
    $postFields = $_POST;
    // dump($postFields['company_branches']);

    if (isset($postFields['company_branches']) && count($postFields['company_branches']) > 0) {
        // Transforming data
        $transformedBranches = array_map(function($branch) {
            return [
                "name" => $branch["name"],
                "phone_numbers" => implode(',', $branch["phone_numbers"]), // Convert array to comma-separated string
                "address" => $branch["address"],
                "state" => $branch["state"],
                "city" => $branch["city"],
                "zipcode" => $branch["zipcode"],
                "latitude" => $branch["latitude"],
                "longitude" => $branch["longitude"]
            ];
        }, $postFields['company_branches']);
        $jsonBranches = json_encode($transformedBranches, JSON_PRETTY_PRINT);

    
        $postFields['company_branches'] = $jsonBranches;
    
        // Debugging transformed data
        // echo "<pre>";
        // print_r($postFields['company_branches']);
        // echo "</pre>";
    }
    

    // dump($postFields);

    if(!empty($logo)){
        $postFields['logo']= $logo;
    }
    if(!empty($company_cover_images)){
        $postFields['company_cover_images']= $company_cover_images;
    }
    if(!empty($company_listing_images)){
        $postFields['company_listing_images']= $company_listing_images;
    }
    // dump($postFields );
    $response = sendCurlRequest(BASE_URL.'/update-company', 'PUT', $postFields,[],true);
    echo $response;
}

// updateTeamRole
if(isset($_GET['action']) && $_GET['action'] == "updateTeamRole"){
    $Id     =  cleanInputs($_POST['Id']);
    $role    =  cleanInputs($_POST['role']);
    $apiData = ['id'=>$Id, 'role' => $role];
    $response = sendCurlRequest(BASE_URL.'/update-company-member', 'POST', $apiData);
    echo $response;
}



/*******
 * 
 * **************************************************************************
 * *************         P O S T S              *******************
* ***************        M O D U L E            ****************
 * **************************************************************************
 * 
 * 
 *******/


// get_posts
if(isset($_GET['action'])  && $_GET['action']=="get_posts"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    $page = floor($input['start'] / $input['length']) + 1;

    // set sorting
    $sortColumnMap = [
        2 => 'title',
        5 => 'address',
        6 => 'country_code',
        7 => 'company_name',
        8 => 'total_view',
        10 => 'status',
        11 => 'boost_expire',
        12 => 'created_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length'], 'sortColumn'=> $sortColumn, 'sortType'=> $sortType];

    $response = sendCurlRequest(BASE_URL.'/get-all-posts', 'GET', $apiData);

    // Decode the JSON response
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
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
      // Dynamic row number

    $responseUsers = sendCurlRequest(BASE_URL.'/all-users', 'GET', []);
    $decodedResponseUsers = json_decode($responseUsers, true);
    if (!empty($decodedResponse['body'])) {
        $startRow = $input['start']; // Starting row index
        foreach ($decodedResponse['body'] as $key => $row) {
            $rowNumber = sprintf('%02d', $startRow + $key + 1);
            $userDropdown = '<select class="user-dropdown" onchange="updatePostUser(this, \'' . $row['id'] . '\')">';
            if (!empty($decodedResponseUsers['body'])) {
                foreach ($decodedResponseUsers['body'] as $user) {
                    $selected = (@$row['user']['id'] == $user['id']) ? 'selected' : '';
                    $userDropdown .= '<option value="' . $user['id'] . '" ' . $selected . '>' . htmlspecialchars($user['name']) . '</option>';
                }
            }
            $userDropdown .= '</select>';

            $userStatus = "";
            if($row['status'] == 1){
                $userStatus = '<div class="content userActive" status="0" user-id="'.$row['id'].'" onclick="changePostStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to deactivate"> Active</div>';
            }else{
                $userStatus = '<div class="content userInactive" status="1" user-id="'.$row['id'].'" onclick="changePostStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to activate"> Inactive</div>';
            }

            $postStatus = "";
            if($row['boost_expire'] > 0){
                $postStatus = '<div class="content" data-bs-toggle="tooltip"> Boosted</div>';
            }else if($row['is_ad'] > 0){
                $postStatus = '<div class="content" data-bs-toggle="tooltip"> Ad</div>';
            }else{
                $postStatus = '<div class="content">Post</div>';
            }
            
            // country check
            $missingFields = [];

            if ($row['country_code'] == '' || $row['country_code'] == 'N/A') {
                $missingFields[] = 'Country Code Not Available';
            }
            if ($row['latitude'] == '' || $row['latitude'] == 'N/A') {
                $missingFields[] = 'Latitude Not Available';
            }
            if ($row['longitude'] == '' || $row['longitude'] == 'N/A') {
                $missingFields[] = 'Longitude Not Available';
            }

            // approval status
            if (!$row['admin_approved'] && !$row['owner_approved']) {
                $missingFields[] = 'Waiting for admin and owner approval';
            } elseif ($row['admin_approved'] && !$row['owner_approved']) {
                $missingFields[] = 'Waiting for owner approval';
            } elseif (!$row['admin_approved'] && $row['owner_approved']) {
                $missingFields[] = 'Waiting for admin approval';
            }

            // combine both in $__country
            if (!empty($missingFields)) {
                $fieldsText = implode(' / ', $missingFields);
                $__country = '<span class="text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="' . $fieldsText . '">No</span>'
                            . '';
            } else {
                $__country = '<span class="text-success">Yes</span>';
            }

            $postCat = "";
            if(!empty($row['service']) || count($row['post_categories']) > 0){
                $postCat = '<div class="content categorized" data-bs-toggle="tooltip"> Categorized</div>';
            }else if(!empty($row['service']) &&  count($row['post_categories']) == 0){
                $postCat = '<div class="content gptcategorized"> GPT Categorized</div>';
            }else{
                $postCat = '<div class="content uncategorized">UnCategorized</div>';
            }

            $triggerEmailButton = '<button class="btn btn-crud" user-id="'.$row['id'].'" onclick="triggerPostEmail(this)" data-action="trigger email" data-bs-toggle="tooltip" data-bs-placement="top" title="Trigger email & text to owner">
                <i class="fa fa-envelope text-danger"></i>
            </button>';

            $buttonJson = '';
            if (!empty($row['listing_json_data_v2'])) {
                $jsonData = json_decode($row['listing_json_data_v2'], true);
                $prettyJson = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                $escapedJson = htmlspecialchars($prettyJson, ENT_QUOTES); // for safe JS usage
                $buttonJson = ' <i style="cursor: pointer; color:green" class="fa fa-code" onclick="showJsonModal('.$row['id'].',`'.$escapedJson.'`, 0)"></i>';
            } else {
                $escapedJson = ''; // or use '{}' if you prefer to default to an empty object
            }

            $txt ='No';
            if(count($row['post_locations']) > 0 && $row['post_locations'][0]['country_code'] == 'WW'){
                $txt='Yes';
            }

            $ww = '<div class="content userNo"> ' . $txt . '</div>';

            $viewPermission = true;
            if (!hasPermission('Posts', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Posts', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Posts', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<input type="checkbox" class="chk-box" value="'.$row['id'].'" />',
                '<div class="content userNo">'.$rowNumber.'</div>',
                '<div class="content userNo text-primary"><a href="post-details.php?id='.base64_encode($row['id']).'" target="_blank">'.$row['title'].'</a></div>',
                '<div class="content d-flex flex-row align-items-center justify-content-between">' . $userDropdown . '</div>',
                '<div class="content userNo"> '.($row['user_type'] ==0  ? 'No' : 'Yes').'</div>',
                ''.$ww.'',
                '<div class="content userNo"> '.$__country.'</div>',
                '<div class="content userNo bold"> '.ucwords(strtolower($row['company_name'])).'</div>',
                '<div class="content userVw d-flex justify-content-center align-items-center increaseViewBtn" data-post-id="'.$row['id'].'"> '.$row['total_view'].'</div>',
                ''. $postCat .'',
                ''.$userStatus.'',
                ''.$postStatus.'',
                '<div class="userHandle fs10"> '.formatToUSDate($row['created_at'],1).'</div>',
                '<div class="content userVw d-flex justify-content-center align-items-center increaseShareBtn" data-post-id="'.$row['id'].'"> '.$row['total_share'].'</div>',
                ' ' . renderActionButtons(base64_encode($row['id']), 'posts', 'post-details.php?id=', 'post-details.php?id=', false, $editPermission, $deletePermission, true, 'get_posts') . ' ' . $triggerEmailButton .''. $buttonJson . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true, $hasShare = true, $shareType = '')
            );
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

// updatePostUser
if(isset($_GET['action']) && $_GET['action'] == "updatePostUser"){
    $user_id    =  cleanInputs($_POST['user_id']);
    $post_id    =  cleanInputs($_POST['post_id']);
    $apiData = ['user_id' => (int)$user_id,'post_id'=> (int)$post_id];
    $response = sendCurlRequest(BASE_URL.'/update-post-admin', 'POST', $apiData);
    echo $response;
}
// update post json
if(isset($_GET['action']) && $_GET['action'] == "updatePostJson"){
    $listing_json_data_v2 = json_decode(stripslashes($_POST['listing_json_data_v2']), true);
    $post_id    =  cleanInputs($_POST['id']);
    $apiData = ['listing_json_data_v2' => $listing_json_data_v2,'post_id'=> (int)$post_id];
    $response = sendCurlRequest(BASE_URL.'/update-post-admin', 'POST', $apiData);
    echo $response;
}
// update post and live json
if(isset($_GET['action']) && $_GET['action'] == "updatePostAndLiveJson"){
    $listing_json_data_v2 = json_decode(stripslashes($_POST['listing_json_data_v2']), true);
    $post_id    =  cleanInputs($_POST['id']);
    $apiData = ['listing_json_data_v2' => $listing_json_data_v2,'post_id'=> (int)$post_id];
    $response = sendCurlRequest(BASE_URL.'/admin-post-live-listing', 'POST', $apiData);
    echo $response;
}

// changePostStatus
if(isset($_GET['action']) && $_GET['action'] == "changePostStatus"){
    $status     =  cleanInputs($_POST['status']);
    $post_id    =  cleanInputs($_POST['post_id']);
    $apiData = ['status' => $status,'post_id'=>$post_id];
    $response = sendCurlRequest(BASE_URL.'/update-post-admin', 'POST', $apiData);
    echo $response;
}

// changePostImageStatus
if(isset($_GET['action']) && $_GET['action'] == "changePostImageStatus"){
    $show_image     =  cleanInputs($_POST['show_image']);
    $post_id    =  cleanInputs($_POST['post_id']);
    $apiData = ['show_image' => $show_image,'post_id'=>$post_id];
    $response = sendCurlRequest(BASE_URL.'/update-post-admin', 'POST', $apiData);
    echo $response;
}

// changeDealFeaturedStatus
if(isset($_GET['action']) && $_GET['action'] == "changeDealFeaturedStatus"){
    $status     =  cleanInputs($_POST['status']);
    $post_id    =  cleanInputs($_POST['post_id']);
    $apiData = ['payment' => $status,'post_id'=>$post_id];
    $response = sendCurlRequest(BASE_URL.'/update-post-admin', 'POST', $apiData);
    echo $response;
}

// triggerPostEmail
if(isset($_GET['action']) && $_GET['action'] == "triggerPostEmail"){
    $post_id    =  cleanInputs($_POST['post_id']);
    $apiData = ['post_id'=>$post_id];
    $response = sendCurlRequest(BASE_URL.'/trigger-post-email-owner', 'POST', $apiData);
    echo $response;
}

// deletePost
if(isset($_GET['action']) && $_GET['action'] == "delete_posts"){
    $ids    =  cleanInputs($_POST['ids']);
    $apiData = ['ids' => $ids];
    $response = sendCurlRequest(BASE_URL.'/delete-post-admin', 'POST', $apiData);
    echo $response;
}

// Increase View
if (isset($_GET['action']) && $_GET['action'] == "increase_view") {
    $increaseAmount = intval($_POST['increaseAmount'] ?? 0);

    // Collect post IDs (multi or single)
    $postIds = explode(',',$_POST['postIds']);

    if ($increaseAmount > 0 && !empty($postIds)) {
        $responses = [];

        foreach ($postIds as $id) {
            $apiData = [
                'increaseAmount' => $increaseAmount,
                'postId' => $id
            ];
            $responses[$id] = json_decode(
                sendCurlRequest(BASE_URL . '/increase-view', 'POST', $apiData),
                true
            );
        }

        echo json_encode([
            'success' => true,
            'results' => $responses
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request: missing amount or postIds'
        ]);
    }
    exit;
}

// Increase Shares
if(isset($_GET['action']) && $_GET['action'] == "increase_share_count"){
    $companyId = cleanInputs($_POST['companyId'] ?? 0);
    $postId = cleanInputs($_POST['postId'] ?? 0);
    $apiData = ['increaseAmount' => $_POST['increaseAmount'], 'postId' => $postId, 'companyId' => $companyId];
    $response = sendCurlRequest(BASE_URL.'/increase-share-count', 'POST', $apiData);
    echo $response;
}

// add status 
if(isset($_GET['action']) && $_GET['action'] == "add_post_status"){
    $file = $_FILES['media'];
    // dump($_FILES);
    $post_id    =  cleanInputs($_GET['id']);
    $fileType = mime_content_type($_FILES['media']['tmp_name']); // Reliable way to get MIME type
    if (str_starts_with($fileType, 'image/')) {
        $mediaType = 0;
    } elseif (str_starts_with($fileType, 'video/')) {
        $mediaType = 1;
    }

    $curl = curl_init();

    $filePath = $file['tmp_name'];
    $fileName = $file['name'];
    $fileMimeType = mime_content_type($filePath);

    // Prepare the CURLFile object
    $fileData = new CURLFile($filePath, $fileMimeType, $fileName);

    // Create POST fields
    $postFields = [
        'media' => $fileData,
        'post_id' => $post_id,
        'media_type'=>$mediaType
    ];

    // dump($ids);
    // dump($apiData );
    $response = sendCurlRequest(BASE_URL.'/create-post-status', 'POST', $postFields,[],true);
    echo $response;
}

// update_post
if(isset($_GET['action']) && $_GET['action'] == "update_post"){

    // Create POST fields
    //dump($_POST);
    $image ='';
    if(isset($_FILES) && !empty($_FILES['image']['name'])){
        echo 1;
        $file =$_FILES['image'];
        $curl = curl_init();
        $filePath = $file['tmp_name'];
        $fileName = $file['name'];
        $fileMimeType = mime_content_type($filePath);

        // Prepare the CURLFile object
        $image = new CURLFile($filePath, $fileMimeType, $fileName);

    }
    $postFields = $_POST;
    if (isset($postFields['community_type']) && $postFields['community_type'] == 0) {
        // All Community selected → no specific community should be stored
        $postFields['community'] = null;
    } else if (!empty($postFields['community'])) {
        // If communities are selected, convert array to comma-separated string
        $postFields['community'] = implode(',', $postFields['community']);
    }
    

    if(!empty($image)){
        $postFields['image']= $image;
    }
    // dump($postFields );
    $response = sendCurlRequest(BASE_URL.'/update-post-admin', 'POST', $postFields,[],true);
    echo $response;
}

// Handle Deep Link Generation Request
if (isset($_GET['action']) && $_GET['action'] === "get_deep_link") {
    $ID   = cleanInputs($_POST['id'] ?? '');
    $type = cleanInputs($_POST['type'] ?? '');

    $decodedID = base64_decode($ID);
    if (!$decodedID || !is_numeric($decodedID)) {
        echo json_encode(['error' => 'Invalid ID']);
        exit;
    }

    // Mapping type to internal values
    $typeMap = [
        'get_posts'       => ['value' => 0, 'string' => 'postPage'],
        'get_ads'         => ['value' => 1, 'string' => 'adsPage'],
        'get_deals'       => ['value' => 4, 'string' => 'dealDetail'],
        'get_companies'   => ['value' => 5, 'string' => 'companyDetail'],
        'inviteUser'      => ['value' => 9, 'string' => 'inviteUser'],
        'get_looking_for' => ['value' => 2, 'string' => 'servicePage'],
    ];

    $typeValue = $typeMap[$type]['value'] ?? 0;
    $customString = $typeMap[$type]['string'] ?? 'default';

    // Fallback values
    $shareTitle = "Check this out on Himish!";
    $shareDescription = "Himish is a platform where users can post feeds, view nearby businesses, claim their business listings, and connect with other companies and users";
    $shareImage = "https://postersz.com/assets/img/logo.png";
    $canonicalUrl = BRANCH_TEST_MODE ? "http://localhost/Himish_Web_Frontend-/branch-redirect.php" : "https://postersz.com/branch-redirect.php";

    // If it's a post, fetch dynamic content
    if ($type === 'get_posts' || $type === 'get_deals' || $type === 'get_looking_for') {
        $apiData = ['post_id' => $decodedID];
        $response = sendCurlRequest(BASE_URL . '/get-posts', 'GET', $apiData);
        $posts = json_decode($response, true)['body'][0] ?? [];

        if (!empty($posts)) {
            $shareTitle = ucfirst($posts['title']) ?? $shareTitle;
            $shareDescription = $posts['info'] ?? $shareDescription;
            $shareImage = count($posts['post_images']) ? MEDIA_BASE_URL.$posts['post_images'][0]['image'] : $shareImage;
        }
    }else if ($type === 'get_companies') {
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
    }else if ($type === 'inviteUser') {
        $responseSettings = sendCurlRequest(BASE_URL . '/get-setting', 'GET', []);
        $generalSettings = json_decode($responseSettings, true)['body'] ?? [];
        $shareTitle = $generalSettings['invite_to_app'];
    }

    // Prepare deep link parameters
    $params = [
        'custom_number'  => (string) $decodedID,
        'request_type'   => (string) $typeValue,
        'user_id'        => (string) ($_SESSION['wb_auth_data']['id'] ?? '0'),
        'custom_string'  => (string) $customString,

        // Open Graph (Facebook, LinkedIn)
        '$desktop_url'       => $canonicalUrl,
        '$og_title'          => 'Himish : '.$shareTitle,
        '$og_description'    => $shareDescription,
        '$og_image_url'      => $shareImage,
        '$og_url'            => $canonicalUrl,
        '$og_image_width'    => 1200,
        '$og_image_height'   => 630,
        '$og_type'           => 'website',
        '$og_image_alt'      => 'Himish preview',

        // Twitter
        '$twitter_card'        => 'summary_large_image',
        '$twitter_title'       => $shareTitle,
        '$twitter_description' => $shareDescription,
        '$twitter_image_url'   => $shareImage,
    ];

    // Create Branch.io short URL
    $shareURL = createBranchShortUrl($params);

    // Fetch Settings (Invite text)
    if(($type == "inviteUser")){
        
    }

    echo json_encode([
        'link' => $shareURL,
    ]);
}

// update post keywords
if(isset($_GET['action']) && $_GET['action'] == "update_post_keywords"){
    $postFields['keyword']    =  cleanInputs($_POST['keyword']);
    $postFields['category']    =  cleanInputs($_POST['category']);

    // dump($postFields );
    $response = sendCurlRequest(BASE_URL.'/update-post-keywords', 'POST', $postFields,[],true);
    echo $response;
}


/*******
 * 
 * **************************************************************************
 * *************        W E B / F E E D         *******************
* ***************           A D S              ****************
 * **************************************************************************
 * 
 * 
 *******/

// get_web_feed_ads
if(isset($_GET['action'])  && $_GET['action']=="get_web_feed_ads"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw'], 'position' => @$_POST['position']);

     // set sorting
     $sortColumnMap = [
        1 => 'position',
        3 => 'company_id',
        4 => 'url',
        5 => 'address',
        6 => 'total_spotted',
        7 => 'created_at',
        8 => 'start_date',
        9 => 'status',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['position' => $input['position'], 'search' => $input['search'], 'page' => $page, 'limit' => $input['length'],'sortColumn' => $sortColumn,'sortType' => $sortType, 'is_admin' => 1];

    $response = sendCurlRequest(BASE_URL.'/get-web-ads', 'GET', $apiData);

    // Decode the JSON response
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
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
        $startRow = $input['start']; // Starting row index
        foreach ($decodedResponse['body'] as $key => $row) {

            $rowNumber = sprintf('%02d', $startRow + $key + 1);

            $imgData = "";
            if(empty($row['media'])){
                $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" alt="" />';
            }else{
                $imgData = '<img src="'. MEDIA_BASE_URL .''.$row['media'].'" width="50" alt="" onerror="this.onerror=null;this.src=\'assets/img/fav-icon.png\';" />';
            }

            $userStatus = "";
            if($row['status'] == 1){
                $userStatus = '<div class="content userActive" status="0" id="'.$row['id'].'" onclick="changeWebAdStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to deactivate"> Active</div>';
            }else{
                $userStatus = '<div class="content userInactive" status="1" id="'.$row['id'].'" onclick="changeWebAdStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to activate"> Inactive</div>';
            }

            $paymentStatus = "";

            if ($row['payment'] == 1) {
                $paymentStatus = '
                    <div class="content userActive" data-bs-toggle="tooltip" data-bs-placement="top" title="Payment Completed">
                        Paid
                    </div>';
            } else {
                $paymentStatus = '<a href="' . $row['payment_url'] . '" target="_blank" class="btn btn-sm btn-danger text-white mt-1">
                        Pay Now
                    </a>';
            }

            $viewPermission = true;
            if (!hasPermission('Web Feed Ads', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Web Feed Ads', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Web Feed Ads', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<input type="checkbox" class="chk-box" value="'.$row['id'].'" />',
                '<div class="content userNo">'.$row['position'].'</div>',
                '<div class="content d">
                '.$imgData.'</div>',
                '<div class="content d-flex">
                <a href="company-details.php?id='.base64_encode($row['company']['id']).'" target="_blank">'.$row['company']['name'].'</a></div>',
                '<a href="'.$row['url'].'" target="_blank">'.$row['url'].'</a></div>',
                '<div class="content userNo"> '.($row['address']).'</div>',
                '<div class="content userVw d-flex justify-content-center align-items-center increaseShareBtn" data-post-id="'.$row['id'].'"> '.$row['total_spotted'].'</div>',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                '<div class="userNo text-primary">'.$row['start_date'].' - '.$row['end_date'].'</div>',
                $userStatus,
                $paymentStatus,
                '' . renderActionButtons(base64_encode($row['id']), 'posts', 'web-feed-ads.php?id=', 'web-feed-ads.php?id=', false, $editPermission, false, false, 'get_posts') . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
            );
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

// changeWebAdStatus
if(isset($_GET['action']) && $_GET['action'] == "changeWebAdStatus"){
    $status     =  cleanInputs($_POST['status']);
    $ad_id    =  cleanInputs($_POST['Id']);
    $apiData = ['status' => $status,'ad_id'=>$ad_id];
    $response = sendCurlRequest(BASE_URL.'/update-web-ad', 'PUT', $apiData);
    echo $response;
}

// Increase increase_share_count_web_Ad
if(isset($_GET['action']) && $_GET['action'] == "increase_share_count_web_Ad"){
    $ad_id = cleanInputs($_POST['ad_id'] ?? 0);
    $apiData = ['increaseAmount' => $_POST['increaseAmount'], 'ad_id' => $ad_id];
    $response = sendCurlRequest(BASE_URL.'/increase-webAd-count', 'PUT', $apiData);
    echo $response;
}

// delete_web_ads
if(isset($_GET['action']) && $_GET['action'] == "delete_web_ads"){
    $ids    =  cleanInputs(implode(',',$_POST['ids']));
    $apiData = ['id' => $ids];
    $response = sendCurlRequest(BASE_URL.'/delete-web-ad?ad_id='.$ids, 'DELETE', $apiData);
    echo $response;
}


/*******
 * 
 * **************************************************************************
 * *************        L O O K I N G         *******************
* ***************           F O R              ****************
 * **************************************************************************
 * 
 * 
 *******/

// get_looking_for
if(isset($_GET['action'])  && $_GET['action']=="get_looking_for"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw'], 'listingType' => @$_POST['listingType']);

     // set sorting
     $sortColumnMap = [
        2 => 'title',
        3 => 'info',
        5 => 'address',
        4 => 'country_code',
        8 => 'status',
        10 => 'created_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['type' => ($input['listingType']==1 ? 4 : 1), 'search' => $input['search'], 'page' => $page, 'limit' => $input['length'],'sortColumn' => $sortColumn,'sortType' => $sortType];

    $response = sendCurlRequest(BASE_URL.'/get-all-posts', 'GET', $apiData);

    // Decode the JSON response
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
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
      // Dynamic row number

    if($input['listingType']==0){
        $responseUsers = sendCurlRequest(BASE_URL.'/all-users', 'GET', []);
        $decodedResponseUsers = json_decode($responseUsers, true);
    }

    if (!empty($decodedResponse['body'])) {
        $startRow = $input['start']; // Starting row index
        foreach ($decodedResponse['body'] as $key => $row) {

            $rowNumber = sprintf('%02d', $startRow + $key + 1);

            if($input['listingType']==0){
                $userDropdown = '<select class="user-dropdown" onchange="updatePostUser(this, \'' . $row['id'] . '\')">';
                if (!empty($decodedResponseUsers['body'])) {
                    foreach ($decodedResponseUsers['body'] as $user) {
                        
                        $selected = (@$row['user']['id'] == @$user['id']) ? 'selected' : '';
                        $userDropdown .= '<option value="' . $user['id'] . '" ' . $selected . '>' . htmlspecialchars($user['name']) . '</option>';
                    }
                }
                $userDropdown .= '</select>';
            }

            if($input['listingType']==0){

                $visible = "";
                if($row['status'] == 1){
                    $visible = '<div class="content userActive mb-3" status="0" user-id="'.$row['id'].'" onclick="changePostStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to non-visible"> Y</div>';
                }else{
                    $visible = '<div class="content userInactive mb-3" status="1" user-id="'.$row['id'].'" onclick="changePostStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="Waiting for approval ! Click to visible"> N</div>';
                }
                if($row['expire_date'] > 0){
                    $userStatus = '<div class="content userActive mb-3"> Active</div>';
                }else{
                    $userStatus = '<div class="content userInactive mb-3" > Expired</div>';
                }

                $imageStatus = "";
                if($row['show_image'] == 0){
                    $imageStatus = '<div class="content alert alert-info mb-3" show_image="1" user-id="'.$row['id'].'" onclick="changePostImageStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to hide image"> Hide Image</div>';
                }else{
                    $imageStatus = '<div class="content alert alert-warning mb-3" show_image="0" user-id="'.$row['id'].'" onclick="changePostImageStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to show image"> Show Image</div>';
                }
            }

            if($input['listingType']==0){
                $postCat = "";
                if(!empty($row['service']) &&  count($row['post_categories']) > 0){
                    $postCat = '<div class="categorized" data-bs-toggle="tooltip"> Categorized</div>';
                }else if(!empty($row['service']) &&  count($row['post_categories']) == 0){
                    $postCat = '<div class="gptcategorized> GPT Categorized</div>';
                }else{
                    $postCat = '<div class="uncategorized">UnCategorized</div>';
                }
            }

            $viewPermission = true;
            if (!hasPermission('Listings', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Listings', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Listings', 'delete')) {
                $deletePermission = false;
            }

            $buttonJson = '';
            if($input['listingType']==0){

                if (!empty($row['listing_json_data_v2'])) {
                    $jsonData = json_decode($row['listing_json_data_v2'], true);
                    $prettyJson = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                    $escapedJson = htmlspecialchars($prettyJson, ENT_QUOTES); // for safe JS usage
                    $buttonJson = ' <i style="cursor: pointer; color:green" class="fa fa-code" onclick="showJsonModal('.$row['id'].',`'.$escapedJson.'`, 0)"></i>';
                } else {
                    $escapedJson = ''; // or use '{}' if you prefer to default to an empty object
                }

                // country check
                $missingFields = [];

                if ($row['country_code'] == '' || $row['country_code'] == 'N/A') {
                    $missingFields[] = 'Country Code';
                }
                if ($row['latitude'] == '' || $row['latitude'] == 'N/A') {
                    $missingFields[] = 'Latitude';
                }
                if ($row['longitude'] == '' || $row['longitude'] == 'N/A') {
                    $missingFields[] = 'Longitude';
                }

                if (!empty($missingFields)) {
                    $fieldsText = implode(' / ', $missingFields);
                    $__country = '<span class="text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="' . $fieldsText . ' Not Available">No</span>';
                } else {
                    $__country = '<spam class="text-success"> Yes</div>'; // or whatever you want to show when all are available
                }

                $final[] = array(
                    "DT_RowId" => $row['id'],
                    '<input type="checkbox" class="chk-box" value="'.$row['id'].'" />',
                    '<div class="content userNo">'.$rowNumber.'</div>',
                    '<div class="content d">
                    <a href="listing-details.php?id='.base64_encode($row['id']).'">'.$row['title'].'</a></div>',
                    '<div class="content d-flex">
                    '.$row['info'].'</div>',
                    ''.$__country.'',
                    '<div class="content userNo"> '.($row['address']).'</div>',
                    '<div class="userNo text-primary">'.$userDropdown.'</div>',
                    ''.$postCat.'',
                    ''.$userStatus.' <br/> '.$imageStatus.'',
                    ''.$visible.'',
                    '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                    '<div class="content userVw d-flex justify-content-center align-items-center increaseShareBtn" data-post-id="'.$row['id'].'"> '.$row['total_share'].'</div>',
                    '' . renderActionButtons(base64_encode($row['id']), 'posts', 'listing-details.php?id=', 'listing-details.php?id=', $viewPermission, $editPermission, false, true, 'get_posts') . '' . $buttonJson // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
                );
            }else{
                $jsonData = json_decode($row['listing_json_data_v2'],true);
                $prettyJson = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                $escapedJson = htmlspecialchars($prettyJson, ENT_QUOTES); // for safe JS usage

                $final[] = array(
                    "DT_RowId" => $row['id'],
                    '<div class="content userNo">'.$rowNumber.'</div>',
                    '<div class="content d-flex">'.$jsonData['title'].'</div>',
                    '<div class="content d-flex">'.$jsonData['description'].'</div>',
                    '<div class="content userNo">'.($jsonData['category']).'</div>',
                    '<div class="userNo text-primary">'.$jsonData['ad_purpose'].'</div>',
                    '<div class="content userHandle">'.formatToUSDate($row['created_at'],1).'</div>',
                    '<button class="btn btn-sm btn-warning" onclick="showJsonModal('.$row['id'].',`'.$escapedJson.'`, 1)">View & Update JSON</button>'
                );
            }
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

// getListingPreviewForConversion
if(isset($_GET['action'])  && $_GET['action']=="getListingPreviewForConversion"){
    $apiDataU = [];
    $response = sendCurlRequest(BASE_URL.'/convert-to-main-listing-v2', 'POST', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    echo json_encode($decodedResponse);
}

/*******
 * 
 * **************************************************************************
 * *************        D E A L S         *******************
 * **************************************************************************
 * 
 * 
 *******/

// get_deals
if(isset($_GET['action'])  && $_GET['action']=="get_deals"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

     // set sorting
     $sortColumnMap = [
        2 => 'title',
        3 => 'company',
        4 => 'country_code',
        // 5 => 'address',
        5 => 'regular_price',
        6 => 'price',
        9 => 'status',
        10 => 'created_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['type' => 2, 'search' => $input['search'], 'page' => $page, 'limit' => $input['length'],'sortColumn' => $sortColumn,'sortType' => $sortType];

    $response = sendCurlRequest(BASE_URL.'/get-all-posts', 'GET', $apiData);

    // Decode the JSON response
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
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
      // Dynamic row number
    
    $responseUsers = sendCurlRequest(BASE_URL.'/all-users', 'GET', []);
    $decodedResponseUsers = json_decode($responseUsers, true);
    if (!empty($decodedResponse['body'])) {
        $startRow = $input['start']; // Starting row index
        foreach ($decodedResponse['body'] as $key => $row) {

            if (!is_null($row['info'])) {

                $rowNumber = sprintf('%02d', $startRow + $key + 1);
                $userDropdown = '<select class="user-dropdown" onchange="updatePostUser(this, \'' . $row['id'] . '\')">';
                if (!empty($decodedResponseUsers['body'])) {
                    foreach ($decodedResponseUsers['body'] as $user) {
                        $selected = ($row['user']['id'] == $user['id']) ? 'selected' : '';
                        $userDropdown .= '<option value="' . $user['id'] . '" ' . $selected . '>' . htmlspecialchars($user['name']) . '</option>';
                    }
                }
                $userDropdown .= '</select>';

                $userStatus = "";
                if($row['status'] == 1){
                    $userStatus = '<div class="content userActive" status="0" user-id="'.$row['id'].'" onclick="changePostStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to deactivate"> Active</div>';
                }else{
                    $userStatus = '<div class="content userInactive" status="1" user-id="'.$row['id'].'" onclick="changePostStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to activate"> Inactive</div>';
                }

                $dealFeatured = "";
                if($row['payment'] == 1){
                    $dealFeatured = '<div class="content userActive" status="0" user-id="'.$row['id'].'" onclick="changeDealFeaturedStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to remove featured"> Featured</div>';
                }else{
                    $dealFeatured = '<div class="content userInactive" status="1" user-id="'.$row['id'].'" onclick="changeDealFeaturedStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to make featured"> Non-Featured</div>';
                }

                $imgData = "";
                if(empty($row['post_images'][0]['image'])){
                    $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" alt="" />';
                }else{
                    $imgData = '<img src="'. MEDIA_BASE_URL .''.$row['post_images'][0]['image'].'" alt="" onerror="this.onerror=null;this.src=\'assets/img/fav-icon.png\';" />';
                }

                // country check
                $__country ='<spam class="text-success"> Yes</div>';
                if ($row['country_code'] == '' || $row['country_code'] == 'N/A' || ($row['expire_date'] == 0 && $row['payment']==1)) {
                    $___title = ($row['expire_date'] == 0) ? 'The expire date not added' : 'The country code not available';
                    $__country = '<span class="text-danger" title="' . $___title . '">No</span>';
                }
            

                $postCat = "";
                if(!empty($row['service']) && count($row['post_categories']) > 0){
                    $postCat = '<div class="content categorized" data-bs-toggle="tooltip"> Categorized</div>';
                }else if(!empty($row['service']) && count($row['post_categories']) == 0){
                    $postCat = '<div class="content gptcategorized> GPT Categorized</div>';
                }else{
                    $postCat = '<div class="content uncategorized">UnCategorized</div>';
                }

                $viewPermission = true;
                if (!hasPermission('Deals', 'view')) {
                    $viewPermission = false;
                }

                $editPermission = true;
                if (!hasPermission('Deals', 'edit')) {
                    $editPermission = false;
                }

                $deletePermission = true;
                if (!hasPermission('Deals', 'delete')) {
                    $deletePermission = false;
                }

                $final[] = array(
                    "DT_RowId" => $row['id'],
                    '<input type="checkbox" class="chk-box" value="'.$row['id'].'" />',
                    '<div class="content userNo">'.$rowNumber.'</div>',
                    '<div class="content d-flex flex-row align-items-center userName">
                    '.$imgData.' <a href="deal-details.php?id='.base64_encode($row['id']).'">'.$row['title'].'</a></div>',
                    '<div class="content d-flex">'.($row['company'] ? ''.$row['company'] : 'N/A').'</div>',
                    ''.$__country.'',
                    // '<div class="content userNo"> '.($row['address'] ? ''.$row['address'] : 'N/A').' </div>',
                    '<div class="content userNo"> '.($row['regular_price'] ? '$'.$row['regular_price'] : 'N/A').' </div>',
                    '<div class="content userNo"> '.($row['price'] ? '$'.$row['price'] : 'N/A').' </div>',
                    ''.$postCat.'',
                    '<div class="content userNo text-primary">'.$userDropdown.'</div>',
                    ''.$userStatus.'',
                    '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                    '<div class="content userHandle"> '.$dealFeatured.'</div>',
                    '<div class="content userVw d-flex justify-content-center align-items-center increaseShareBtn" data-post-id="'.$row['id'].'"> '.$row['total_share'].'</div>',
                    '' . renderActionButtons(base64_encode($row['id']), 'posts', 'deal-details.php?id=', 'deal-details.php?id=', $viewPermission, $editPermission, false, true, 'get_deals') . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
                );
            }
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

/*******
 * 
 * **************************************************************************
 * *************        D E A L S         *******************
 * * ***************    S H A R E             ****************
 * **************************************************************************
 * 
 * 
 *******/

// get_deal_share
if(isset($_GET['action'])  && $_GET['action']=="get_deal_share"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    // set sorting
    $sortColumnMap = [
        2 => 'title',
        3 => 'country_code',
        4 => 'address',
        5 => 'user_id',
        6 => 'status',
        8 => 'created_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }


    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['type' => 3, 'search' => $input['search'], 'page' => $page, 'limit' => $input['length'],'sortColumn' => $sortColumn,'sortType' => $sortType, 'admin' => 'wb'];

    $response = sendCurlRequest(BASE_URL.'/get-deals-share', 'GET', $apiData);

    // Decode the JSON response
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
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
      // Dynamic row number

    $responseUsers = sendCurlRequest(BASE_URL.'/all-users', 'GET', []);
    $decodedResponseUsers = json_decode($responseUsers, true);
    if (!empty($decodedResponse['body'])) {
    
        $startRow = $input['start']; // Starting row index
        foreach ($decodedResponse['body'] as $key => $row) {

            if(empty($row['info'])){

                $rowNumber = sprintf('%02d', $startRow + $key + 1);
                $userDropdown = '<select class="user-dropdown" onchange="updatePostUser(this, \'' . $row['id'] . '\')">';
                if (!empty($decodedResponseUsers['body'])) {
                    foreach ($decodedResponseUsers['body'] as $user) {
                        $selected = ($row['user']['id'] == $user['id']) ? 'selected' : '';
                        $userDropdown .= '<option value="' . $user['id'] . '" ' . $selected . '>' . htmlspecialchars($user['name']) . '</option>';
                    }
                }
                $userDropdown .= '</select>';

                $userStatus = "";
                if($row['status'] == 1){
                    $userStatus = '<div class="content userActive" status="0" user-id="'.$row['id'].'" onclick="changePostStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to deactivate"> Active</div>';
                }else{
                    $userStatus = '<div class="content userInactive" status="1" user-id="'.$row['id'].'" onclick="changePostStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to activate"> Inactive</div>';
                }

                $imgData = "";
                $mediaUrl = !empty($row['post_images'][0]['image']) ? MEDIA_BASE_URL . $row['post_images'][0]['image'] : "";

                if (empty($mediaUrl)) {
                    $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" alt="" />';
                } else {
                    $fileExtension = strtolower(pathinfo($mediaUrl, PATHINFO_EXTENSION));
                    $videoExtensions = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv']; // Add more if needed

                    if (in_array($fileExtension, $videoExtensions)) {
                        // Show video
                        $imgData = '<video width="150" height="80" controls autoplay muted loop playsinline>
                            <source src="' . $mediaUrl . '">
                        </video>';
                    } else {
                        // Show image
                        $imgData = '<a href="' . $mediaUrl . '" target="_blank">
                        <img src="' . $mediaUrl . '" alt="" onerror="this.onerror=null;this.src=\'assets/img/fav-icon.png\';" width="150" height="80" style="object-fit: cover;" /></a>';
                    }
                }

                 // country check
            $__country ='<spam class="text-success"> Yes</div>';
            if($row['country_code']=='' || $row['country_code'] =='N/A'){
                $__country ='<spam class="text-danger" title ="The country code not available">No</div>';
            }

                $postCat = "";
                if (!empty($row['service'])) {
                    if (isset($row['post_categories']) && count($row['post_categories']) > 0) {
                        $postCat = '<div class="content categorized" data-bs-toggle="tooltip">Categorized</div>';
                    } else if (isset($row['post_categories']) && count($row['post_categories']) == 0) {
                        $postCat = '<div class="content gptcategorized">GPT Categorized</div>';
                    } else {
                        $postCat = '<div class="content uncategorized">UnCategorized</div>';
                    }
                } else {
                    $postCat = '<div class="content uncategorized">UnCategorized</div>';
                }

                $viewPermission = true;
                if (!hasPermission('Deal Share', 'view')) {
                    $viewPermission = false;
                }

                $editPermission = true;
                if (!hasPermission('Deal Share', 'edit')) {
                    $editPermission = false;
                }

                $deletePermission = true;
                if (!hasPermission('Deal Share', 'delete')) {
                    $deletePermission = false;
                }

                $final[] = array(
                    "DT_RowId" => $row['id'],
                    '<input type="checkbox" class="chk-box" value="'.$row['id'].'" />',
                    '<div class="content userNo">'.$rowNumber.'</div>',
                    '<div class="content d-flex flex-row align-items-center">
                    '.$imgData.' </div><br/><a href="deal-share-details.php?id='.base64_encode($row['id']).'">'.$row['title'].'</a>',
                    ''.$__country.'',
                    '<div class="content userNo"> '.($row['address'] ? ''.$row['address'] : 'N/A').' </div>',
                    '<div class="content userNo text-primary">'.$userDropdown.'</div>',
                    ''.$userStatus.'',
                    ''.$postCat.'',
                    '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                    '<div class="content userVw d-flex justify-content-center align-items-center increaseShareBtn" data-post-id="'.$row['id'].'"> '.$row['total_share'].'</div>',
                    '' . renderActionButtons(base64_encode($row['id']), 'posts', 'deal-share-details.php?id=', 'deal-share-details.php?id=', $viewPermission, $editPermission, false, true, 'get_deals') . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
                );
            }
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

/*******
 * 
 * **************************************************************************
 * *************        C O M M U N I T Y         *******************
 * **************************************************************************
 * 
 * 
 *******/

// get_communities
if(isset($_GET['action'])  && $_GET['action']=="get_communities"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    $page = floor($input['start'] / $input['length']) + 1;

    // set sorting
    $sortColumnMap = [
        1 => 'id',
        3 => 'name',
        4 => 'address',
        6 => 'total_member',
        7 => 'total_posts',
        8 => 'total_LF',
        9 => 'is_private',
        10 => 'status',
        11 => 'created_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length'], 'sortColumn'=> $sortColumn, 'sortType'=> $sortType];

    $response = sendCurlRequest(BASE_URL.'/admin-community-list', 'GET', $apiData);
    
    // Decode the JSON response
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
      // Dynamic row number
    if (!empty($decodedResponse['body'])) {
        $startRow = $input['start']; // Starting row index
        foreach ($decodedResponse['body'] as $key => $row) {

            $rowNumber = sprintf('%02d', $startRow + $key + 1);
            $userStatus = "";
            if($row['status'] == 1){
                $userStatus = '<div class="content userActive" status="0" id="'.$row['id'].'" onclick="changeCommunityStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to deactivate"> Active</div>';
            }else{
                $userStatus = '<div class="content userInactive" status="1" id="'.$row['id'].'" onclick="changeCommunityStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to activate"> Inactive</div>';
            }

            $comStatus = "";
            if($row['is_private'] == 1){
                $comStatus = '<div class="userPublic userPrivate">Private</div>';
            }else{
                $comStatus = '<div class="userPublic">Public</div>';
            }

            $imgData = "";
            if(empty($row['image'])){
                $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" alt="" />';
            }else{
                $imgData = '<img src="'. MEDIA_BASE_URL .''.$row['image'].'" alt="" onerror="this.onerror=null;this.src=\'assets/img/fav-icon.png\';" />';
            }

            $viewPermission = true;
            if (!hasPermission('Communities', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Communities', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Communities', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<input type="checkbox" class="chk-box" value="'.$row['id'].'" />',
                '<div class="content userNo">'.$row['id'].'</div>',
                '<div class="content d-flex flex-row align-items-center userName">
                '.$imgData.' </div>',
                '<div class="userNo content"> '.($row['name'] ? ''.$row['name'] : 'N/A').' </div>',
                '<div class="userNo content"> '.($row['address'] ? ''.$row['address'] : 'N/A').' </div>',
                '<div class="userNo content"> <a href="view-user.php?id='.base64_encode($row['user_id']).'" target="_blank"> '.($row['username'] ? ''.$row['username'] : 'N/A').' </a> </div>',
                '<div class="userNo content"> <a href="community-details.php?redirect=members&id='.base64_encode($row['id']).'" target="_blank"> ' . $row['total_member'] . '</a> </div>',
                '<div class="userNo content"> <a href="javascript:void(0)"> ' . $row['total_posts'] . '</a> </div>',
                '<div class="userNo content"> <a href="javascript:void(0)"> ' . $row['total_LF'] . '</a> </div>',
                '<div class="userNo content"> <a href="javascript:void(0)"> ' . ($row['price'] ?? 0) . '</a> </div>',
                // '<div class="userNo content"> <a href="javascript:void(0)"> ' . $row['total_DS'] . '</a> </div>',
                ' ' . $comStatus. ' ',
                ''.$userStatus.'',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                '<div class="content userHandle"><a href="javascript:void(0)" rel="'.$row['id'].'" class="inviteUser"> Invite Users </a></div>',
                '' . renderActionButtons(base64_encode($row['id']), 'communities', 'community-details.php?id=', 'community-details.php?id=', $viewPermission, $editPermission, $deletePermission, false, '', true) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true, hasEditPopUp = false)
            );
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

// changeCommunityStatus
if(isset($_GET['action']) && $_GET['action'] == "changeCommunityStatus"){
    $status     =  cleanInputs($_POST['status']);
    $Id    =  cleanInputs($_POST['Id']);
    $apiData = ['status' => $status];
    $response = sendCurlRequest(BASE_URL.'/admin-edit-community?editId='.$Id, 'PUT', $apiData);
    echo $response;
}

// deleteCommunity
if(isset($_GET['action']) && $_GET['action'] == "deleteCommunity"){
    $id     =  cleanInputs(base64_decode($_POST['id']));
    $apiData = [];
    $response = sendCurlRequest(BASE_URL.'/admin-delete-community?id='.$id, 'DELETE', $apiData);
    echo $response;
}

// get_community_members
if(isset($_GET['action']) && $_GET['action']=="get_community_members"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0]['column'], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['community_id' => base64_decode($_POST['Id']), 'search' => $input['search'], 'page' => $page, 'limit' => $input['length']];

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
    //dump($decodedResponse);

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {

            $dStatus = "";
            if($row['status'] == 1){
                $dStatus = '<div class="content userActive" status="0" user-id="'.$row['id'].'"> Active</div>';
            }else{
                $dStatus = '<div class="content userInactive" status="1" user-id="'.$row['id'].'"> Inactive</div>';
            }

            $imgData = "";
            if(empty($row['user']['image'])){
                $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" alt="" />';
            }else{
                $imgData = '<img src="'. MEDIA_BASE_URL .''.$row['user']['image'].'" alt="" onerror="this.onerror=null;this.src=\'assets/img/fav-icon.png\';" />';
            }

            $viewPermission = true;
            if (!hasPermission('Communities', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Communities', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Communities', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="content d-flex flex-row align-items-center userName text-primary">
                '.$imgData.' <a href="view-user.php?id='.base64_encode($row['user']['id']).'" target="_blank">'.$row['user']['name'].'</a></div>',
                '<div class="content userHandle"> '.($row['is_owner'] ? 'Owner' : 'Member').'</div>',
                ''.$dStatus.'',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                '' . renderActionButtons(base64_encode($row['id']), 'community_members', 'edit-user.php?id=', 'view-user.php?id=', false, false, ($row['is_owner'] ? false : $deletePermission)) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
            );
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

/*******
 * 
 * **************************************************************************
 * *************          M A S T E R          *******************
 * *************        C A T E G O R I E S          *******************
 * **************************************************************************
 * 
 * 
 *******/

// get_master_sub_cat

if(isset($_GET['action'])  && $_GET['action']=="get_master_sub_cat"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw'], 'type' => @$_POST['type']);

     // set sorting
     $sortColumnMap = [
        9 => 'created_at',
    ];
    
    $sortColumn = 'created_at'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length'], 'type' => $input['type'],'sortType' => $sortType, 'sortColumn' => $sortColumn];

    $response = sendCurlRequest(BASE_URL.'/admin-master-cat-subs', 'GET', $apiData);
    
    // Decode the JSON response
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
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
      // Dynamic row number
    if (!empty($decodedResponse['body'])) {
        $startRow = $input['start']; // Starting row index
        foreach ($decodedResponse['body'] as $key => $row) {

            $rowNumber = sprintf('%02d', $startRow + $key + 1);

            $showStar = "";
            if($row['show_in_filter']){
                $showStar = '<i class="fa fa-star text-warning"></i>';
            }

            $viewPermission = true;
            if (!hasPermission('Master', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Master', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Master', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="content me">
                <p class="fw-900 content"> '.$showStar.' '.($row['names'][0]).' > </p> <small class="grey"> ' . implode(' > ', array_slice($row['names'], 1, 3)) . ' </small> </div>',
                '<div class="userNo content"> '.($row['keywords1'] ? ''.$row['keywords1'] : 'N/A').' </div>',
                '<div class="userNo content"> '.($row['keywords2'] ? ''.$row['keywords2'] : 'N/A').' </div>',
                '<div class="userNo content"> '.($row['keywords3'] ? ''.$row['keywords3'] : 'N/A').' </div>',
                '<div class="userNo content"> '.($row['keywords4'] ? ''.$row['keywords4'] : 'N/A').' </div>',
                '<div class="userNo content"> '.($row['keywords5'] ? ''.$row['keywords5'] : 'N/A').' </div>',
                '<div class="userNo content"> '.($row['tabLabelOptionPosts'] ? ''.$row['tabLabelOptionPosts'] : 'N/A').' </div>',
                '<div class="userNo content"> '.($row['tabLabelOptionCompany'] ? ''.$row['tabLabelOptionCompany'] : 'N/A').' </div>',
                '<div class="userNo content"> '.($row['tabLabelOptionLookingFor'] ? ''.$row['tabLabelOptionLookingFor'] : 'N/A').' </div>',
                '<div class="userNo content"> '.formatToUSDate($row['created_at'],1).' </div>',
                '' . renderActionButtons(base64_encode($row['id']), 'master_cat_sub', 'edit-categories.php?id=', '', false, $editPermission, false) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
            );
        }
    }

    $json_data = array(
                    "draw"=> intval( $input['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                    "recordsTotal"    => intval(count($final)),  // total number of records
                    "recordsFiltered" => intval(5000), // total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"            => $final   // total data array
                );

    echo  json_encode($json_data);
}

// get_master_sub_cat__by_type

if(isset($_GET['action'])  && $_GET['action']=="get_master_sub_cat__by_type"){

    $input = array('type' => @$_POST['type']);

    $response = sendCurlRequest(BASE_URL.'/admin-master-cat-subs?type='.$input['type'], 'GET', []);
    
    // Decode the JSON response
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

    echo  json_encode($decodedResponse['body']);
}

if(isset($_GET['action'])  && $_GET['action']=="get_master_sub_cat__by_search"){

    $input = array('search' => @$_POST['search'], 'categoryType' => @$_POST['categoryType']);

    $response = sendCurlRequest(BASE_URL.'/admin-master-cat-subs-search?search='.urlencode($input['search']).'&categoryType='.$input['categoryType'], 'GET', []);
    
    // Decode the JSON response
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

    echo  json_encode($decodedResponse['body']);
}

// make_category_star_unstar
if(isset($_GET['action']) && $_GET['action'] == "make_category_star_unstar"){
    $id     =  cleanInputs($_POST['id']);
    $show_in_filter    =  cleanInputs($_POST['show_in_filter']);
    $apiData = ['show_in_filter' => $show_in_filter];
    $response = sendCurlRequest(BASE_URL.'/admin-master-cat-sub-add?id='.$id, 'POST', $apiData);
    echo $response;
}

/*******
 * 
 * **************************************************************************
 * *************        T R A N S A T I O N S        *******************
 * **************************************************************************
 * 
 *******/


// get_transactions

if(isset($_GET['action']) && $_GET['action']=="get_transactions"){
    // dump(@$_POST);

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0]['column'], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw'],'user_id' => @$_POST['user_id'],'status' => @$_POST['status'],'address' => @$_POST['address']);

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length'],'user_id' => @$_POST['user_id'],'status' => @$_POST['status'],'address' => @$_POST['address']];

    $response = sendCurlRequest(BASE_URL.'/getTransaction', 'GET', $apiData);

    // Decode the JSON response
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

    //dump($decodedResponse);

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {
            $link='post-details.php?id=';
            if($row['request_type'] ==1){
                $link='ads-details.php?id=';
            }
            $userStatus = "";
            $paymentMethod = "Wallet Points";

            //$row['json_data'] = json_decode($row['json_data'],true);

            if (!empty($row['json_data']) && is_array($row['json_data'])) {
                $jsonData = $row['json_data'];

                // Check for payment_status
                if (
                    isset($jsonData['object']) &&
                    is_array($jsonData['object']) &&
                    isset($jsonData['object']['payment_status'])
                ) {
                    if ($jsonData['object']['payment_status'] === 'paid') {
                        $userStatus = '<div class="userActive">PAID</div>';
                    } else {
                        $userStatus = '<div class="userInactiveRed">Failed</div>';
                    }
                }

                // Check for payment_method_types and hosted_invoice_url
                if (
                    isset($jsonData['object']['payment_method_types']) &&
                    is_array($jsonData['object']['payment_method_types']) &&
                    !empty($jsonData['object']['payment_method_types'][0])
                ) {
                    $methodType = ucfirst($jsonData['object']['payment_method_types'][0]);

                    $invoiceUrl = '';
                    if (
                        isset($jsonData['paymentIntentDetails']) &&
                        is_array($jsonData['paymentIntentDetails']) &&
                        !empty($jsonData['paymentIntentDetails']['hosted_invoice_url'])
                    ) {
                        $invoiceUrl = '<br><a href="' . htmlspecialchars($jsonData['paymentIntentDetails']['hosted_invoice_url']) . '" target="_blank">View Invoice</a>';
                    }

                    $paymentMethod = $methodType . $invoiceUrl;
                }
            }


            $imgData = "";
            if(($row['request_type'] == 2)){
                if(empty($row['post_data']['logo'])){
                    $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" alt="" />';
                }else{
                    $imgData = '<img src="'. MEDIA_BASE_URL .''.$row['post_data']['logo'].'" alt="" />';
                }
            }else{
                if(empty($row['post_data']['company_image'])){
                    $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" alt="" />';
                }else{
                    $imgData = '<img src="'. MEDIA_BASE_URL .''.$row['post_data']['company_image'].'" alt="" />';
                }
            }

            $viewPermission = true;
            if (!hasPermission('Transactions', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Transactions', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Transactions', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<input type="checkbox" class="chk-box" value="'.$row['id'].'" />',
                '<div class="content d-flex flex-row align-items-center userName">
                '.$imgData.' '.(($row['request_type'] == 2) ? $row['post_data']['name'] : @$row['post_data']['company_name']).'</div>',
                '<div class="userHandle">'.$paymentMethod.'</div>',
                '<div class="d-flex flex-row align-items-center userNo text-gray">
                ' . (($row['request_type'] == 1) ? 'Ads Run' : (($row['request_type'] == 2) ? 'NFC Card' : 'Boost Post')) . '</div>',                
                '<div class="text-gray"> '.formatToUSDate($row['created_at'],1).'</div>',
                '<div class="text-gray">$'.$row['credit'].'</div>',
                '<div class="text-gray">$'.$row['tax'].'</div>',
                '<div class="text-gray">$'.$row['total_amount'].'</div>',
                $userStatus,
                '' . renderActionButtons(base64_encode($row['request_id']), 'transactions', $link , $link, $viewPermission, $editPermission, false) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
            );
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

// deletePost
if(isset($_GET['action']) && $_GET['action'] == "delete_transactions"){
    $ids    =  cleanInputs($_POST['ids']);
    // dump($ids);
    $apiData = ['ids' => $ids];
    $response = sendCurlRequest(BASE_URL.'/delete-transactions', 'POST', $apiData);
    echo $response;
}

/*******
 * 
 * **************************************************************************
 * *************        F A Q S        *******************
 * **************************************************************************
 * 
 *******/

// get_faqs

if(isset($_GET['action']) && $_GET['action']=="get_faqs"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    // set sorting
    $sortColumnMap = [
        0 => 'question',
        1 => 'answer',
        2 => 'status',
        3 => 'created_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length'], 'sortType' => $sortType, 'sortColumn' => $sortColumn];

    $response = sendCurlRequest(BASE_URL.'/get-faqs-admin', 'GET', $apiData);

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
    //dump($decodedResponse);

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {

            $userStatus = "";
            if($row['status'] == 1){
                $userStatus = '<div class="content userActive" status="0" user-id="'.$row['id'].'" onclick="changeFaqStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to deactivate"> Active</div>';
            }else{
                $userStatus = '<div class="content userInactive" status="1" user-id="'.$row['id'].'" onclick="changeFaqStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to activate"> Inactive</div>';
            }

            $viewPermission = true;
            if (!hasPermission('FAQs', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('FAQs', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('FAQs', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="content userName">
                '.$row['question'].'</div>',
                '<div class="content userHandle answer"> '.($row['answer'] ? $row['answer'] : 'N/A').'</div>',
                ''.$userStatus.'',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                '' . renderActionButtons(base64_encode($row['id']), 'faqs', 'faqs.php?id=', '', false, $editPermission, $deletePermission) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
            );
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

// deleteFaq
if(isset($_GET['action']) && $_GET['action'] == "deleteFaq"){
    $id     =  cleanInputs(base64_decode($_POST['id']));
    $apiData = [];
    $response = sendCurlRequest(BASE_URL.'/delete-faq?id='.$id, 'DELETE', $apiData);
    echo $response;
}

// deleteCommunityMember
if(isset($_GET['action']) && $_GET['action'] == "deleteCommunityMember"){
    $id     =  cleanInputs(base64_decode($_POST['id']));
    $apiData = [];
    $response = sendCurlRequest(BASE_URL.'/remove-from-community?request_id='.$id, 'DELETE', $apiData);
    echo $response;
}

// changeFaqStatus
if(isset($_GET['action']) && $_GET['action'] == "changeFaqStatus"){
    $status     =  cleanInputs($_POST['status']);
    $Id         =  cleanInputs($_POST['Id']);
    $apiData = ['status' => $status];
    $response = sendCurlRequest(BASE_URL.'/add-edit-faq?faq_id='.$Id, 'POST', $apiData);
    echo $response;
}


/*******
 * 
 * **************************************************************************
 * *************        C O M M E N T S        *******************
 * **************************************************************************
 * 
 *******/


 if(isset($_GET['action']) && $_GET['action']=="get_comments"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0]['column'], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length']];

    $response = sendCurlRequest(BASE_URL.'/get-suggession-comments-admin', 'GET', $apiData);

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
    //dump($decodedResponse);

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {

            $userStatus = "";
            if($row['status'] == 1){
                $userStatus = '<div class="content userActive" status="0" user-id="'.$row['id'].'" onclick="changeCommentStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to deactivate"> Active</div>';
            }else{
                $userStatus = '<div class="content userInactive" status="1" user-id="'.$row['id'].'" onclick="changeCommentStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to activate"> Inactive</div>';
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div >
                '.($key + 1).'</div>',
                '<div class="content userHandle"> '.($row['name'] ? $row['name'] : 'N/A').'</div>',
                ''.$userStatus.'',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                '' . renderActionButtons(base64_encode($row['id']), 'comment_sugessions', 'comments.php?id=', '', false, true, true) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
            );
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

// changeFaqStatus
if(isset($_GET['action']) && $_GET['action'] == "changeCommentStatus"){
    $status     =  cleanInputs($_POST['status']);
    $Id         =  cleanInputs($_POST['Id']);
    $apiData = ['status' => $status,'id'=>$Id];
    $response = sendCurlRequest(BASE_URL.'/add-suggession-comments-admin', 'POST', $apiData);
    echo $response;
}

// deleteFaq
if(isset($_GET['action']) && $_GET['action'] == "deleteComment"){
    $id     =  cleanInputs(base64_decode($_POST['id']));
    $apiData = [];
    $response = sendCurlRequest(BASE_URL.'/delete-suggession-comment-admin?id='.$id, 'DELETE', $apiData);
    echo $response;
}



/*******
 * 
 * **************************************************************************
 * *************        P L A N S       *******************
 * **************************************************************************
 * 
 *******/

// get_plans
if(isset($_GET['action']) && $_GET['action']=="get_plans"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    $page = floor($input['start'] / $input['length']) + 1;

    // set sorting
    $sortColumnMap = [
        0 => 'type',
        1 => 'title',
        3 => 'price',
        4 => 'tax',
        5 => 'created_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
        if ($sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length'], 'sortType' => $sortType, 'sortColumn' => $sortColumn];

    $response = sendCurlRequest(BASE_URL.'/getPlans', 'GET', $apiData);

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
    //dump($decodedResponse);

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {

            $viewPermission = true;
            if (!hasPermission('Plans', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Plans', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Plans', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="content d-flex flex-row align-items-center userName">
                '.($row['type']==0 ? '<span class="text-primary">Boost Post Plan</span>' : '<span class="text-danger">Ads Plan</span>').'</div>',
                '<div class="content userHandle"> '.($row['title'] ? $row['title'] : 'N/A').'</div>',
                '<div class="content userHandle"> '.($row['price'] ? '$'.$row['price'] : 'N/A').'</div>',
                '<div class="content userHandle"> '.($row['tax'] ? $row['tax'].'%' : 'N/A').'</div>',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                '' . renderActionButtons(base64_encode($row['id']), 'plans', 'plans.php?id=', '', false, $editPermission, false) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
            );
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

/*******
 * 
 * **************************************************************************
 * *************         C L A I M E D        *******************
 * *************        B U S I N E S S        *******************
 * *************        R E Q U E S T S        *******************
 * **************************************************************************
 * 
 *******/

// get_claimed_business_requests
if(isset($_GET['action']) && $_GET['action']=="get_claimed_business_requests"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    if(isset($_POST['extra_search'])){
        $input['search'] = base64_decode($_POST['extra_search']);
    }

    // set sorting
    $sortColumnMap = [
        0 => 'id',
        1 => 'company_id',
        2 => 'user_id',
        3 => 'email',
        4 => 'post_id',
        5 => 'phone',
        6 => 'post_owner_request.created_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length'], 'sortType' => $sortType, 'sortColumn' => $sortColumn];

    $response = sendCurlRequest(BASE_URL.'/all-owner-request', 'GET', $apiData);

    $decodedResponse = json_decode($response, true);

    //dump($decodedResponse);
    
    // Handle 401 Unauthorized
    if (isset($decodedResponse['code']) && $decodedResponse['code'] == 401) {
        http_response_code(401);
        echo json_encode([
            'redirect' => true,
            'url' => dirname($_SERVER['PHP_SELF']) // Redirect URL
        ]);
        exit;
    }
    // dump($decodedResponse);

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {
           // Set default
            $from = '<span class="text-info">AD</span>';
            $bt = '';

            switch ((int)$row['type']) {
                case 0: // post
                    $postId = $row['post']['id'] ?? null;
                    $companyId = $row['company']['id'] ?? null;
                    $companyName = $row['company']['name'] ?? 'N/A';

                    $from = $postId ? '<a href="post-details.php?id=' . base64_encode((string)$postId) . '" class="text-success" target="_blank">POST</a>' : 'POST';
                    $bt = $companyId ? ' <a href="company-details.php?id=' . base64_encode((string)$companyId) . '" target="_blank">' . htmlspecialchars($companyName) . '</a></div>' : $companyName;
                    break;

                case 1: // listing
                    $postId = $row['post']['id'] ?? null;
                    $postTitle = $row['post']['title'] ?? 'N/A';

                    $from = $postId ? '<a href="listing-details.php?id=' . base64_encode((string)$postId) . '" class="text-primary" target="_blank">LISTING</a>' : 'LISTING';
                    $bt = $postId ? ' <a href="listing-details.php?id=' . base64_encode((string)$postId) . '" target="_blank">' . htmlspecialchars($postTitle) . '</a></div>' : $postTitle;
                    break;

                case 2: // company
                case 3: // ad
                    $companyId = $row['company']['id'] ?? null;
                    $companyName = $row['company']['name'] ?? 'N/A';
                    $colorClass = $row['type'] == 2 ? 'text-warning' : 'text-info';

                    $from = $companyId ? '<a href="company-details.php?id=' . base64_encode((string)$companyId) . '" class="' . $colorClass . '" target="_blank">' . strtoupper($row['type'] == 2 ? 'COMPANY' : 'AD') . '</a>' : strtoupper($row['type'] == 2 ? 'COMPANY' : 'AD');
                    $bt = $companyId ? ' <a href="company-details.php?id=' . base64_encode((string)$companyId) . '" target="_blank">' . htmlspecialchars($companyName) . '</a></div>' : $companyName;
                    break;
            }

            $username = ' <a href="view-user.php?id='.base64_encode($row['user']['id']).'" target="_blank">'.($row['user']['name']).'</a>';

            $viewPermission = true;
            if (!hasPermission('Claimed Business Requests', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Claimed Business Requests', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Claimed Business Requests', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="content d-flex flex-row align-items-center userName">'.$row['id'].'</div>',
                '<div class="content d-flex flex-row align-items-center userName">'.$bt.'</div>',
                '<div class="content userHandle"> '.$username.'</div>',
                '<div class="content userHandle"> '.($row['email'] ? $row['email'] : 'N/A').'</div>',
                '<div class="content userHandle"> '.$from.'</div>',
                '<div class="content userHandle"> '.($row['phone'] ? $row['phone'] : 'N/A').'</div>',
                '<div class="content userHandle"> '.formatToUSDate($row['createdAt'],1).'</div>',
                ($editPermission) ? '<a class="btn btn-success btn-sm text-white" href="claimed-business-requests.php?cbAct=1&rqt='.uniqid().'&sy='.time().'&id='.base64_encode($row['id']).'&tp=1"><i class="fa fa-check"></i></a> &nbsp; 
                <a class="btn btn-danger btn-sm text-white" href="claimed-business-requests.php?cbAct=1&rqt='.uniqid().'&sy='.time().'&id='.base64_encode($row['id']).'&tp=2"><i class="fa fa-times"></i></a>' : '-'
            );
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

/*******
 * 
 * **************************************************************************
 * *************            A D S        *******************
 * *************        R E Q U E S T S        *******************
 * **************************************************************************
 * 
 *******/

 // deletecompay
if(isset($_GET['action']) && $_GET['action'] == "delete_ads_image"){
    $id    =  cleanInputs($_POST['id']);
    // dump($ids);
    $response = sendCurlRequest(BASE_URL.'/delete-ads-image?id='.$id.'', 'DELETE', []);
    echo $response;
}

// get_ads_requests

if(isset($_GET['action']) && $_GET['action']=="get_ads_requests"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0]['column'], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['admin'=>'all','run'=>'0', 'search' => $input['search'], 'page' => $page, 'limit' => $input['length']];

    $response = sendCurlRequest(BASE_URL.'/get-ads', 'GET', $apiData);

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
    //dump($decodedResponse);

    function getStatusText($status) {
        switch ($status) {
          case 0:
            return 'Pending';
          case 1:
            return 'Approved';
          case 2:
            return 'Rejected';
          case 3:
            return 'Past';
          default:
            return 'Unknown';
        }
    };

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {

            $status = $row['status'];
            $statusClass = $status == 0 ? 'text-warning' : ($status == 1 ? 'text-success' : 'text-danger');

            $actBtn = '<a class="btn btn-default text-white" href="javascript:void(0)">--</a>';
            if($row['status'] == 0){
                $actBtn = '<a class="btn btn-sm btn-success text-white" href="ads-requests.php?cbAct=1&rqt='.uniqid().'&sy='.time().'&id='.base64_encode($row['id']).'&tp=1"><i class="fa fa-check"></i></a> &nbsp; 
                <a class="btn btn-danger btn-sm text-white" href="ads-requests.php?cbAct=1&rqt='.uniqid().'&sy='.time().'&id='.base64_encode($row['id']).'&tp=2"><i class="fa fa-times"></i></a>';
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="content userHandle"> '.($row['title'] ? $row['title'] : 'N/A').'</div>',
                '<div class="content userHandle"> <a href="company-details.php?id='.base64_encode($row['company_id']).'" target="_blank">'.($row['company_name'] ? $row['company_name'] : 'N/A').'</a></div>',
                '<div class="content userHandle"> <a href="view-user.php?id='.base64_encode($row['user_id']).'" target="_blank">'.($row['user'] ? $row['user']['name'] : 'N/A').'</a></div>',
                '<div class="content userHandle '.$statusClass.'"> '.getStatusText($row['status']).'</div>',
                '<div class="content userHandle"> '.($row['payment'] ==0 ? '<span class="text-warning">Pending</span>' : 'Succeeded').'</div>',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                $actBtn
            );
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
## get ads
if(isset($_GET['action']) && $_GET['action']=="getAds"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

     // set sorting
     $sortColumnMap = [
        1 => 'country_code',
        8 => 'end_date',
        6 => 'status',
        9 => 'payment',
        7 => 'created_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['admin'=>'all','run'=>'1','search' => $input['search'], 'page' => $page, 'limit' => $input['length'],'sortColumn' => $sortColumn,'sortType' => $sortType];

    $response = sendCurlRequest(BASE_URL.'/get-ads', 'GET', $apiData);

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
    //dump($decodedResponse);

    function getStatusText($status) {
        switch ($status) {
          case 0:
            return 'Pending';
          case 1:
            return 'Active';
          case 2:
            return 'Rejected';
          case 3:
            return 'Expired';
          default:
            return 'Unknown';
        }
    };

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {

            $status = $row['status'];
            $statusClass = $status == 0 ? 'text-warning' : ($status == 1 ? 'userActive' : 'userInactive userExpired');

            // country check
            $__country ='<spam class="text-success"> Yes</div>';
            if($row['country_code']=='' || $row['country_code'] =='N/A'){
                $__country ='<spam class="text-danger" title ="The country code not available">No</div>';
            }

            $viewPermission = true;
            if (!hasPermission('Ads', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Ads', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Ads', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<input type="checkbox" class="chk-box" value="'.$row['id'].'" />',
                ''.$__country.'',
                '<span class="content">'.$row['company_name'].'</span>',
                '<a href="view-user.php?id='.base64_encode($row['user_id']).'" target="_blank">'.($row['user'] ? $row['user']['name'] : 'N/A').'</a>',
                $row['title'],
                '<span class="content">'.$row['start_date'].'</span>',
                '<div class="'.$statusClass.'"> '.getStatusText($row['status']).'</div>',
                formatToUSDate($row['created_at'],1),
                '<div class="content userNo">'.$row['end_date'].'</div>',
                ($row['payment'] ==0 ? '<div class="text-Declined">Pending</div>' : '<div class="textSucceeded">Succeeded</div>'),
                '' . renderActionButtons(base64_encode($row['id']), 'sponser_ads', 'ads-details.php?id=', 'ads-details.php?id=', $viewPermission, $editPermission, $deletePermission, true, 'get_ads') . '' 
            );
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

// deleteMultiAds
if(isset($_GET['action']) && $_GET['action'] == "deleteMultiAds"){
    $ids    =  cleanInputs($_POST['ids']);
    $apiData = ['ids' => $ids];
    $response = sendCurlRequest(BASE_URL.'/delete-multi-ads', 'POST', $apiData);
    echo $response;
}
// deleteSingleAds
if(isset($_GET['action']) && $_GET['action'] == "deleteSingleAds"){
    $id    =  cleanInputs(base64_decode($_POST['id']));
    $apiData = [];
    $response = sendCurlRequest(BASE_URL.'/delete-ads?ads_id='.$id, 'DELETE', $apiData);
    echo $response;
}

// update_ads
if(isset($_GET['action']) && $_GET['action'] == "update_ads"){

    // Create POST fields
    // dump($_POST);
    $images ='';
    if(isset($_FILES) && !empty($_FILES['images']['name'])){
        $file =$_FILES['images'];
        $curl = curl_init();
        $filePath = $file['tmp_name'];
        $fileName = $file['name'];
        $fileMimeType = mime_content_type($filePath);

        // Prepare the CURLFile object
        $images = new CURLFile($filePath, $fileMimeType, $fileName);

    }
    $postFields = $_POST;
    if($postFields['community']){
        $postFields['community'] = implode(',',$postFields['community']);
    }
    // if($postFields['end_date']){
    //     $postFields['end_date'] = DateTime::createFromFormat('Y-m-d', $postFields['end_date'])->format('m/d/Y');
    // }


    // dump($postFields);

    if(!empty($images)){
        $postFields['images']= $images;
    }
    //  dump($postFields );
    $response = sendCurlRequest(BASE_URL.'/update-ads', 'PUT', $postFields,[],true);
    echo $response;
}

/*******
 * 
 * **************************************************************************
 * *************         D I S P U T E       *******************
 * *************        R E Q U E S T S        *******************
 * **************************************************************************
 * 
 *******/

// get_dispute_requests
if(isset($_GET['action']) && $_GET['action']=="get_dispute_requests"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0]['column'], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length']];

    $response = sendCurlRequest(BASE_URL.'/all-company-dispute', 'GET', $apiData);

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
    //dump($decodedResponse);

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="content userHandle"> <a href="company-details.php?id='.base64_encode($row['company_id']).'" target="_blank">'.($row['company_name'] ? $row['company_name'] : 'N/A').'</a></div>',
                '<div class="content userHandle"> <a href="view-user.php?id='.base64_encode($row['user_id']).'" target="_blank">'.($row['username'] ? $row['username'] : 'N/A').'</a></div>',
                '<div class="content userHandle"> '.$row['message'].'</div>',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                // '<a class="btn btn-success btn-sm text-white" href="dispute-requests.php?cbAct=1&rqt='.uniqid().'&sy='.time().'&id='.base64_encode($row['id']).'&tp=1"><i class="fa fa-check"></i></a> &nbsp; 
                // <a class="btn btn-danger btn-sm text-white" href="dispute-requests.php?cbAct=1&rqt='.uniqid().'&sy='.time().'&id='.base64_encode($row['id']).'&tp=2"><i class="fa fa-times"></i></a>'
                'N/A'
            );
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

/*******
 * 
 * **************************************************************************
 * *************        C O M M U N I T Y       *******************
 * *************         R E Q U E S T S        *******************
 * **************************************************************************
 * 
 *******/

// get_community_requests
if(isset($_GET['action'])  && $_GET['action']=="get_community_requests"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    $page = floor($input['start'] / $input['length']) + 1;

    // set sorting
    $sortColumnMap = [
        1 => 'name',
        2 => 'username',
        3 => 'created_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length'], 'sortColumn'=> $sortColumn, 'sortType'=> $sortType, 'status'=> 0];

    $response = sendCurlRequest(BASE_URL.'/admin-community-list', 'GET', $apiData);
    
    // Decode the JSON response
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
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
    $rowNumber = 0;
    // Dynamic row number
    if (!empty($decodedResponse['body'])) {
        $startRow = $input['start']; // Starting row index
        foreach ($decodedResponse['body'] as $key => $row) {

            $rowNumber = ($key + 1);
            $userStatus = "";
            if($row['status'] == 0){
                $userStatus = '<div class="content userInactive" status="1" id="'.$row['id'].'" onclick="changeCommunityStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to approve"> Click to Approve</div>';
            }

            $comStatus = "";
            if($row['is_private'] == 1){
                $comStatus = '<div class="userPublic userPrivate">Private</div>';
            }else{
                $comStatus = '<div class="userPublic">Public</div>';
            }

            $imgData = "";
            if(empty($row['image'])){
                $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" alt="" />';
            }else{
                $imgData = '<img src="'. MEDIA_BASE_URL .''.$row['image'].'" alt="" onerror="this.onerror=null;this.src=\'assets/img/fav-icon.png\';" />';
            }

            $viewPermission = true;
            if (!hasPermission('Community Requests', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Community Requests', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Community Requests', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="content d-flex flex-row align-items-center userName">
                '.$imgData.' </div>',
                '<div class="userNo content"> '.($row['name'] ? ''.$row['name'] : 'N/A').' </div>',
                '<div class="userNo content"> <a href="view-user.php?id='.base64_encode($row['user_id']).'" target="_blank"> '.($row['username'] ? ''.$row['username'] : 'N/A').' </a> </div>',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                ($editPermission) ? $userStatus : '-',
            );
        }
    }

    $json_data = array(
                    "draw"=> intval( $input['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                    "recordsTotal"    => intval(count($final)),  // total number of records
                    "recordsFiltered" => intval($rowNumber), // total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"            => $final   // total data array
                );

    echo  json_encode($json_data);
}

/*******
 * 
 * **************************************************************************
 * *************   N O T I F I C A T I O N    *******************
 * *************     T E M P L A T E S        *******************
 * **************************************************************************
 * 
 *******/

// get_noti_templates

if(isset($_GET['action'])  && $_GET['action']=="get_noti_templates"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    // set sorting
    $sortColumnMap = [
        0 => 'title',
        2 => 'name',
        1 => 'code',
        3 => 'push_message',
        4 => 'notification_message',
        5 => 'email_text',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
        if ($sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length'], 'sortColumn' => $sortColumn, 'sortType' => $sortType];

    $response = sendCurlRequest(BASE_URL.'/getNotiModuleData', 'GET', $apiData);
    
    // Decode the JSON response
    $decodedResponse = json_decode($response, true);
    //  dump($decodedResponse);
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
    $rowNumber = 0;
    // Dynamic row number
    if (!empty($decodedResponse['body'])) {
        $startRow = $input['start']; // Starting row index
        foreach ($decodedResponse['body'] as $key => $row) {

            $rowNumber = ($key + 1);

            $viewPermission = true;
            if (!hasPermission('Notification & Email Setup', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Notification & Email Setup', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Notification & Email Setup', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="userNo content"> '.($row['title'] ? ''.$row['title'] : 'N/A').' </div>',
                '<div class="userNo content"> '.($row['code'] ? ''.$row['code'] : 'N/A').' </div>',
                '<div class="userNo content"> '.($row['name'] ? ''.$row['name'] : 'N/A').' </div>',
                '<div class="userNo content"> '.($row['after_expire_time'] ? ''.$row['after_expire_time'] : 'N/A').' </div>',
                '<div class="userNo content"> '.($row['push_message'] ? ''.$row['push_message'] : 'N/A').' </div>',
                '<div class="userNo content"> '.($row['notification_message'] ? ''.$row['notification_message'] : 'N/A').' </div>',
                '<div class="userNo content"> '.($row['email_text'] ? ''.$row['email_text'] : 'N/A').' </div>',
                (($editPermission) ? '<button 
                    class="btn text-white btn-sm editNM" 
                    data-id="'.base64_encode($row['id']).'" 
                    data-push_message="'.htmlspecialchars($row['push_message'], ENT_QUOTES).'" 
                    data-notification_message="'.htmlspecialchars($row['notification_message'], ENT_QUOTES).'" 
                    data-code="'.htmlspecialchars($row['code'], ENT_QUOTES).'" 
                    data-title="'.htmlspecialchars($row['title'], ENT_QUOTES).'"                    data-after_expire_time="'.htmlspecialchars($row['after_expire_time'], ENT_QUOTES).'" 
                    data-name="'.$row['name'].'" 
                    data-after_expire_time="'.htmlspecialchars($row['after_expire_time'], ENT_QUOTES).'" 
                    data-email_text="'.htmlspecialchars($row['email_text'], ENT_QUOTES).'" 
                    data-checkbox_notification_message="'.htmlspecialchars($row['noti_enable'], ENT_QUOTES).'" 
                    data-checkbox_push_message="'.htmlspecialchars($row['push_enable'], ENT_QUOTES).'" 
                    data-checkbox_email_text="'.htmlspecialchars($row['email_enable'], ENT_QUOTES).'">
                    <img src="assets/img/list/edit.svg" alt="edit">
                </button>' : '').'
                <button class="btn btn-primary btn-small test-noti-fn" data-id="'.$row['id'].'">Test</button>'
            );
        }
    }

    $json_data = array(
                    "draw"=> intval( $input['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                    "recordsTotal"    => intval(count($final)),  // total number of records
                    "recordsFiltered" => intval($rowNumber), // total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"            => $final   // total data array
                );

    echo  json_encode($json_data);
}

/*******
 * 
 * **************************************************************************
 * *************        E M A I L    *******************
 * *************     T E M P L A T E S        *******************
 * **************************************************************************
 * 
 *******/

// get_email_templates
if(isset($_GET['action'])  && $_GET['action']=="get_email_templates"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0]['column'], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw']);

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length']];

    $response = sendCurlRequest(BASE_URL.'/get-email-templates', 'GET', $apiData);
    
    // Decode the JSON response
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
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
    $rowNumber = 0;
    // Dynamic row number
    if (!empty($decodedResponse['body'])) {
        $startRow = $input['start']; // Starting row index
        foreach ($decodedResponse['body'] as $key => $row) {

            $rowNumber = ($key + 1);

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="userNo content"> '.($row['title'] ? ''.$row['title'] : 'N/A').' </div>',
                '<div class="userNo content"> '.($row['subject'] ? ''.$row['subject'] : 'N/A').' </div>',
                '<button class="btn text-white btn-sm editEM" data-id="'.base64_encode($row['id']).'" data-title="'.($row['title']).'" data-subject="'.($row['subject']).'" data-code="'.($row['code']).'" data-template="'.($row['template']).'"><img src="assets/img/list/edit.svg" alt="edit"></button>',
            );
        }
    }

    $json_data = array(
                    "draw"=> intval( $input['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                    "recordsTotal"    => intval(count($final)),  // total number of records
                    "recordsFiltered" => intval($rowNumber), // total number of records after searching, if there is no searching then totalFiltered = totalData
                    "data"            => $final   // total data array
                );

    echo  json_encode($json_data);
}


// get user by community
if(isset($_GET['action']) && $_GET['action'] == "getUserByCommunity"){
  
    $apiData = ['community_id' => @$_POST['community_id']];
    $response = sendCurlRequest(BASE_URL.'/get-user-by-community', 'POST', $apiData);
    echo $response;
}
// server restart
if(isset($_GET['action']) && $_GET['action'] == "restartServer"){
  
    $apiData = [];
    $response = sendCurlRequest(BASE_URL.'/restart-server', 'POST', $apiData);
    echo $response;
}

// get_ocr_data
if(isset($_GET['action']) && $_GET['action']=="get_ocr_data"){
    // dump($_POST);

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw'], 'status' => @$_POST['status']);

    $page = floor($input['start'] / $input['length']) + 1;

    // set sorting
    $sortColumnMap = [
        1 => 'json_data',
        2 => 'created_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $apiData = ['search' => $input['search'], 'status' => $input['status'], 'page' => $page, 'limit' => $input['length'],'sortColumn' => $sortColumn,'sortType' => $sortType];
    //dump($apiData);

    $response = sendCurlRequest(BASE_URL.'/get-ocr-data', 'GET', $apiData);

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
    //dump($decodedResponse);

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {

            $imgData = "";
            if (empty($row['image'])) {
                $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" alt="" />';
            } else {
                // Creating the image tag wrapped in an anchor to open in a new tab
                $imgData = '<img src="'. MEDIA_BASE_URL .''.$row['image'].'" width="50" height="50" alt="" onerror="this.onerror=null;this.src=\'assets/img/fav-icon.png\';" /></a>';
            }

            $jsonData = json_decode($row['json_data'],true);

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="content d-flex flex-row align-items-center" data-toggle="modal" data-target="#jsonModal" data-image="'. MEDIA_BASE_URL .''.$row['image'].'" data-json=\'' . htmlspecialchars(json_encode($jsonData, JSON_PRETTY_PRINT), ENT_QUOTES, 'UTF-8') . '\'>
                '.$imgData.'</div>',
                '<div class="content userHandle">'.$jsonData['title'].'</div>',
                '<div class="content userHandle text-">'.$jsonData['company_name'].'</div>',
                '<div class="content userHandle text-">'.implode(',',$jsonData['keywords']).'</div>',
                '<div class="content userHandle text-">'.implode(',',$jsonData['phone_numbers']).'</div>',
                '<div class="content userHandle text-">'.implode(',',$jsonData['email_addresses']).'</div>',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
            );
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

// delete_listing_image
if(isset($_GET['action']) && $_GET['action'] == "delete_post_image"){
    $ids    =  cleanInputs($_POST['ids']);
    // dump($ids);
    $response = sendCurlRequest(BASE_URL.'/delete-post-image?id='.$ids, 'DELETE', []);
    echo $response;
}

// scan image 
if(isset($_GET['action']) && $_GET['action'] == "scan_image"){

    if(isset($_FILES) && !empty($_FILES['image']['name'])){
        $file =$_FILES['image'];
        $curl = curl_init();
        $filePath = $file['tmp_name'];
        $fileName = $file['name'];
        $fileMimeType = mime_content_type($filePath);

        // Prepare the CURLFile object
        $image = new CURLFile($filePath, $fileMimeType, $fileName);

    }
  
    $apiData = ['image'=>$image];
    $response = sendCurlRequest(BASE_URL.'/imageClassificationGemini', 'POST', $apiData,[],true);
    echo $response; 
}



/*******
 * 
 * **************************************************************************
 * *************     S A L E S    P E R S O N           *******************
* ***************        M O D U L E            ****************
 * **************************************************************************
 * 
 * 
 *******/

// get_sales_person
if(isset($_GET['action']) && $_GET['action']=="get_sales_person"){
    //  dump($_POST);

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw'], 'status' => @$_POST['status']);

    $page = floor($input['start'] / $input['length']) + 1;

    // set sorting
    $sortColumnMap = [
        1 => 'name',
        2 => 'email',
        6 => 'status',
        5 => 'updated_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $apiData = ['search' => $input['search'], 'status' => $input['status'], 'account_type' => 3, 'page' => $page, 'limit' => $input['length'],'sortColumn' => $sortColumn,'sortType' => $sortType];
    //dump($apiData);

    $response = sendCurlRequest(BASE_URL.'/all-users', 'GET', $apiData);

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
    //dump($decodedResponse);

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {

            $viewPermission = true;
            if (!hasPermission('Sales Person', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Sales Person', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Sales Person', 'delete')) {
                $deletePermission = false;
            }
            
            $buttons = [];

            // ✅ View Button
            if (hasPermission('Sales Person', 'view')) {
                $buttons[] = '<button class="p-1.5 text-gray-400 hover:text-primary rounded viewReps" 
                    data-json="' . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . '"  
                    data-bs-toggle="modal" data-bs-target="#viewRepDetailsModal" 
                    title="View Details">
                    <i class="fa-solid fa-eye"></i>
                </button>';
            }

            // ✅ Edit Button
            if (hasPermission('Sales Person', 'edit')) {
                $buttons[] = '<button class="p-1.5 text-gray-400 hover:text-warning rounded editReps"  
                    data-bs-toggle="modal" data-bs-target="#editRepInfoModal" 
                    data-json="' . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . '" 
                    title="Reassign Links">
                    <i class="fa-solid fa-edit"></i>
                </button>';
            }

            // ✅ Delete Button
            if (hasPermission('Sales Person', 'delete')) {
                $buttons[] = '<button class="p-1.5 text-danger-400 hover:text-danger rounded"  
                    data-action="delete" data-table="users" 
                    data-id="' . base64_encode($row['id']) . '" 
                    onclick="handleActionButtons(this)" 
                    aria-label="delete" title="Delete">
                    <i class="fa-solid fa-trash"></i>
                </button>';
            }

            // Combine all available buttons into wrapper
            $button = '<div class="flex items-center space-x-2">' . implode('', $buttons) . '</div>';

            $userStatus = "";
            if($row['status'] == 1){
                $userStatus = '<div class="content userActive" status="0" user-id="'.$row['id'].'" onclick="changeUserStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to deactivate"> Active</div>';
            }else{
                $userStatus = '<div class="content userInactive" status="1" user-id="'.$row['id'].'" onclick="changeUserStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to activate"> Flagged</div>';
            }

            $imgData = "";
            if(empty($row['image'])){
                $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" alt="" />';
            }else{
                $imgData = '<img src="'. MEDIA_BASE_URL .''.$row['image'].'" alt="" class="w-8 h-8 rounded-full mr-3" onerror="this.onerror=null;this.src=\'assets/img/fav-icon.png\';" />';
            }
            $name ='<div class="flex items-center">'.$imgData.'<div><div class="font-medium text-gray-900">'.$row['name'].'</div><div class="text-sm text-gray-500">ID: SR'.$row['id'].'</div></div></div>';
            $contact ='<div class="text-sm text-gray-900">'.$row['email'].'</div><div class="text-sm text-gray-500">'.$row['phone'].'</div>';

            $final[] = array(
                "DT_RowId" => $row['id'],
                $name,
                $contact,
                '<div class="text-sm text-gray-900">'.$row['total_converted'].'</div><div class="text-xs text-gray-500">'.$row['total_click_count'].' Click / '.$row['total_community_count'].' Community</div>',
                ' <div class="font-medium text-gray-900">$'.$row['total_amount'].'</div><div class="text-xs text-gray-500">This month: $'.$row['this_month_amount'].'</div>',
                '<div class="content userHandle"> '.formatToUSDate($row['updated_at'],1).'</div>',
                ''.$userStatus.'',
                '' . $button . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
            );
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

// get_communities
if(isset($_GET['action'])  && $_GET['action']=="get_sales_communities"){

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw'],'status' => @$_POST['status'],'is_private' => @$_POST['is_private']);

    $page = floor($input['start'] / $input['length']) + 1;

    // set sorting
    $sortColumnMap = [
        0 => 'name',
        1 => 'username',
        3 => 'is_private',
        4 => 'total_member',
        6 => 'created_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length'], 'sortColumn'=> $sortColumn, 'sortType'=> $sortType,'account_type' =>3,'status' =>$input['status'],'is_private'=>$input['is_private']];

    $response = sendCurlRequest(BASE_URL.'/admin-community-list', 'GET', $apiData);
    
    // Decode the JSON response
    $decodedResponse = json_decode($response, true);
    // dump($decodedResponse);
    // Handle 401 Unauthorized
    if (isset($decodedResponse['code']) && $decodedResponse['code'] == 401) {
        http_response_code(401);
        echo json_encode([
            'redirect' => true,
            'url' => dirname($_SERVER['PHP_SELF']) // Redirect URL
        ]);
        exit;
    }
    // viewCommunityDetailsModal
    // Initialize variables
    $final = [];
      // Dynamic row number
    if (!empty($decodedResponse['body'])) {
        $startRow = $input['start']; // Starting row index
        foreach ($decodedResponse['body'] as $key => $row) {

            $rowNumber = sprintf('%02d', $startRow + $key + 1);
            $userStatus = "";
            if($row['status'] == 1){
                $userStatus = '<div class="content userActive" status="0" id="'.$row['id'].'" onclick="changeCommunityStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to deactivate"> Active</div>';
            }else{
                $userStatus = '<div class="content userInactive" status="1" id="'.$row['id'].'" onclick="changeCommunityStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to activate"> Flagged</div>';
            }
            $shareLink = isset($row['linkData'][0]['share_link']) && !empty($row['linkData'][0]['share_link'])
            ? htmlspecialchars($row['linkData'][0]['share_link'], ENT_QUOTES, 'UTF-8')
            : ''; // fallback if not available

            $button='<div class="flex items-center space-x-2"><button class="p-1.5 text-gray-400 hover:text-primary rounded viewComs" data-json="'.htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8').'"  data-bs-toggle="modal" data-bs-target="#viewCommunityDetailsModal" title="View Details"><i class="fa-solid fa-eye"></i></button><button class="p-1.5 text-gray-400 hover:text-warning rounded editCom"  data-bs-toggle="modal" data-bs-target="#editCommunityModal" data-json="'.htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8').'" title="Reassign Links"><i class="fa-solid fa-edit"></i></button><button class="p-1.5 text-danger-400 hover:text-danger rounded"  data-action="delete" data-table="communities" data-id="'.base64_encode($row['id']).'" data-bs-toggle="tooltip" data-bs-placement="top" onclick="handleActionButtons(this)" aria-label="delete" data-bs-original-title="delete"><i class="fa-solid fa-trash"></i></button> ';

              if($shareLink){
                $button.=' <button type="button" class="ml-2 p-1 text-gray-400 hover:text-primary copy-btn" data-link="'.$shareLink.'" title="Copy Link">
                <i class="fa-solid fa-copy text-xs"></i>
              </button></div>';
              }else{
                $button.='</div>';
              }
          

            $comStatus = "";
            if($row['is_private'] == 1){
                $comStatus = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"><i class="fa-solid fa-lock mr-1"></i>Private</span>';
            }else{
                $comStatus = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fa-solid fa-globe mr-1"></i>Public</span>';
            }

            $imgData = "";
            if(empty($row['image'])){
                $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" style="width:50px" alt="" />';
            }else{
                $imgData = '<img src="'. MEDIA_BASE_URL .''.$row['image'].'"  style="width:50px" alt="" onerror="this.onerror=null;this.src=\'assets/img/fav-icon.png\';" />';
            }

            

            $final[] = array(
                "DT_RowId" => $row['id'],
                ' <div class="flex items-center">'.$imgData.'<div><div class="font-medium text-gray-900">'.$row['name'].'</div><div class="text-sm text-gray-500">ID: COM'.$row['id'].'</div></div></div>',
                '<div class="flex items-center"><div><div class="text-sm font-medium text-gray-900">'.$row['username'].'</div><div class="text-xs text-gray-500">SR'.$row['user_id'].'</div></div></div>',
                ' ' . $comStatus. ' ',
                '<div class=" items-center text-sm text-gray-900 font-medium">' . $row['total_member'] . '</div>',
                ''.$userStatus.'',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                $button

              // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true, hasEditPopUp = false)
            );
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

// get_links
if(isset($_GET['action'])  && $_GET['action']=="get_referral_inks"){
    //  dump($_POST);

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw'],'status' => @$_POST['status'],'user_id' => @$_POST['user_id']);

    $page = floor($input['start'] / $input['length']) + 1;

    // set sorting
    $sortColumnMap = [
        
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length'], 'sortColumn'=> $sortColumn, 'sortType'=> $sortType,'account_type' =>3,'status' =>$input['status'],'user_id'=>$input['user_id']];

    $response = sendCurlRequest(BASE_URL.'/get-sale-person-link', 'GET', $apiData);
    
    // Decode the JSON response
    $decodedResponse = json_decode($response, true);
    //   dump($decodedResponse);
    // Handle 401 Unauthorized
    if (isset($decodedResponse['code']) && $decodedResponse['code'] == 401) {
        http_response_code(401);
        echo json_encode([
            'redirect' => true,
            'url' => dirname($_SERVER['PHP_SELF']) // Redirect URL
        ]);
        exit;
    }
    // viewCommunityDetailsModal
    // Initialize variables
    $final = [];
    
      // Dynamic row number
    if (!empty($decodedResponse['body'])) {
        $startRow = $input['start']; // Starting row index
        foreach ($decodedResponse['body'] as $key => $row) {

            $rowNumber = sprintf('%02d', $startRow + $key + 1);
            $userStatus = "";
            if($row['status'] == 1){
                $userStatus = '<div class="content userActive" status="0" id="'.$row['id'].'" onclick="changeLinkStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to deactivate"> Active</div>';
            }else{
                $userStatus = '<div class="content userInactive" status="1" id="'.$row['id'].'" onclick="changeLinkStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to activate"> Flagged</div>';
            }


            $button='<div class="flex items-center space-x-2"><button class="p-1.5 text-gray-400 hover:text-primary rounded viewLink" data-json="'.htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8').'"  data-bs-toggle="modal" data-bs-target="#referralLinkDetailModal" title="View Details"><i class="fa-solid fa-eye"></i></button><button class="p-1.5 text-gray-400 hover:text-warning rounded editLink"  data-bs-toggle="modal" data-bs-target="#editReferralLinkModal" data-json="'.htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8').'" title="Reassign Links"><i class="fa-solid fa-edit"></i></button><button class="p-1.5 text-danger-400 hover:text-danger rounded"  data-action="delete" data-table="referral_links" data-id="'.base64_encode($row['id']).'" data-bs-toggle="tooltip" data-bs-placement="top" onclick="handleActionButtons(this)" aria-label="delete" data-bs-original-title="delete"><i class="fa-solid fa-trash"></i></button></div>';

           

            $imgData = "";
            if(empty($row['user']['image'])){
                $imgData = '<img class="w-6 h-6 rounded-full mr-2" src="assets/img/fav-icon.png" alt="" />';
            }else{
                $imgData = '<img src="'. MEDIA_BASE_URL .''.$row['user']['image'].'" alt="" class="w-6 h-6 rounded-full mr-2"  onerror="this.onerror=null;this.src=\'assets/img/fav-icon.png\';" />';
            }
            $__user = '<div class="flex items-center">'. $imgData .'<span class="text-sm text-gray-900">'.$row['user']['name'].'</span></div>';

            $__link = '
            <div class="flex items-center">
              <code class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">'.$row['link_id'].'</code>
              <button type="button" class="ml-2 p-1 text-gray-400 hover:text-primary copy-btn" data-link="'.$row['share_link'].'" title="Copy Link">
                <i class="fa-solid fa-copy text-xs"></i>
              </button>
            </div>';
            

            $formatted_date = date('M d, Y', $row['expire_time']);            

            // Calculate days left
            $now = time();
            $diff_seconds = $row['expire_time'] - $now;
            $days_left = ceil($diff_seconds / (60 * 60 * 24));

            // Ensure it doesn't show negative days
            $days_left_text = $days_left > 0 ? "$days_left days left" : "Expired";

            

            $final[] = array(
                "DT_RowId" => $row['id'],
                $__link,
                $__user,
                '<div class="text-sm text-gray-900">'.$row['post']['company'].'</div><div class="text-xs text-gray-500">'.$row['post']['title'].'</div>',
                '<div class="text-sm text-gray-900"> '.formatToUSDate($row['created_at'],1).'</div>',
                '<div class="text-sm text-gray-900">'.$formatted_date.'</div><div class="text-xs text-gray-500">'.$days_left_text.'</div>',
                '<div class="text-sm text-gray-900">'.$row['total_click'].' / '.$row['total_convert'].'</div><div class="text-xs text-gray-500">Clicks / Conversions</div>',
                ' ' . $userStatus. ' ',
                $button
            );
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






// getAdsInvitation
if(isset($_GET['action']) && $_GET['action']=="getAdsInvitation"){
    // dump($_POST);

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw'], 'status' => @$_POST['status']);

    $page = floor($input['start'] / $input['length']) + 1;

    // set sorting
    $sortColumnMap = [
        0 => 'custom_number',
        1 => 'share_option',
        2 => 'emails',
        5 => 'clicks',
        6 => 'conversions',
        7 => 'created_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $apiData = ['search' => $input['search'], 'sales_person_id' => $_SESSION['hm_auth_data']['id'], 'page' => $page, 'limit' => $input['length'],'sortColumn' => $sortColumn,'sortType' => $sortType];

    $response = sendCurlRequest(BASE_URL.'/get-ads-invitation', 'GET', $apiData);

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
    //dump($decodedResponse);

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="content d-flex flex-row align-items-center userName">
                '.$row['custom_number'].'</div>',
                '<div class="content userHandle text-primary"> '.($row['share_option'] ? $row['share_option'] : 'N/A').'</div>',
                '<div class="content userHandle"> '.($row['emails'] ? $row['emails'] : 'N/A') .'</div>',
                '<div class="content userHandle"> '.($row['include_coupon'] == 'yes' ? $row['coupon_type'].' ( $'.$row['coupon_value'].' )' : $row['include_coupon']).'</div>',
                '<div class="content userHandle text-primary" onclick="copyToClipboard(\'' . addslashes($row['deep_link_url']) . '\')">Copy Link</div>',
                '<div class="content userHandle"> '.$row['clicks'].'</div>',
                '<div class="content userHandle"> '.$row['conversions'].'</div>',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
            );
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

// getLogsTable
if(isset($_GET['action']) && $_GET['action']=="get_logs"){
    // dump($_POST);

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw'], 'status' => @$_POST['status']);

    $page = floor($input['start'] / $input['length']) + 1;

    // set sorting
    $sortColumnMap = [
        1 => 'name',
        2 => 'message',
        3 => 'level',
        7 => 'user_agent',
        8 => 'created_at',
    ];
    
    $sortColumn = 'id'; // Default sort column
    $sortType = 'desc'; // Default sort type
    
    if (!empty($input['order'])) {
        $columnIndex = $input['order']['column'] ?? null;
        $sortDir = $input['order']['dir'] ?? null;
    
        if ($columnIndex && $sortDir && isset($sortColumnMap[$columnIndex])) {
            $sortColumn = $sortColumnMap[$columnIndex];
            $sortType = $sortDir;
        }
    }

    $apiData = ['search' => $input['search'], 'status' => $input['status'], 'account_type' => 2, 'page' => $page, 'limit' => $input['length'],'sortColumn' => $sortColumn,'sortType' => $sortType];
    //dump($apiData);

    $response = sendCurlRequest(BASE_URL.'/get-logs', 'GET', $apiData);

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
    //dump($decodedResponse);

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {

            // $userStatus = "";
            // if($row['status'] == 1){
            //     $userStatus = '<div class="content userActive" status="0" user-id="'.$row['id'].'" onclick="changeUserStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to deactivate"> Active</div>';
            // }else{
            //     $userStatus = '<div class="content userInactive" status="1" user-id="'.$row['id'].'" onclick="changeUserStatus(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="click to activate"> Inactive</div>';
            // }

            $imgData = "";
            if(empty($row['user']['image'])){
                $imgData = '<img class="img-placeholder" src="assets/img/fav-icon.png" alt="" />';
            }else{
                $imgData = '<img src="'. MEDIA_BASE_URL .''.$row['user']['image'].'" alt="" onerror="this.onerror=null;this.src=\'assets/img/fav-icon.png\';" />';
            }

            $viewPermission = true;
            if (!hasPermission('Logs', 'view')) {
                $viewPermission = false;
            }

            $editPermission = true;
            if (!hasPermission('Logs', 'edit')) {
                $editPermission = false;
            }

            $deletePermission = true;
            if (!hasPermission('Logs', 'delete')) {
                $deletePermission = false;
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                '<div class="content d-flex flex-row align-items-center userName">
                '.$imgData.' '.$row['user']['name'].'</div>',
                '<div class="content userHandle text-primary">'.($row['message'] ? $row['message'] : 'N/A').'</div>',
                '<div class="content userHandle"> '.$row['level'].'</div>',
                '<div class="content userHandle"> '.$row['user_agent'].'</div>',
                '<div class="content userHandle"> '.formatToUSDate($row['created_at'],1).'</div>',
                '' . renderActionButtons(base64_encode($row['id']), 'users', '', 'view-user.php?id=', false, false, $deletePermission) . '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
            );
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

/*******
 * 
 * **************************************************************************
 * *************        A N A N O M O U S  R E Q U E S T       *******************
 * **************************************************************************
 * 
 *******/


// ananomousRequest

if(isset($_GET['action']) && $_GET['action']=="ananomousRequest"){
    // dump(@$_POST);

    $input = array('search' => @$_POST['search']['value'], 'order' => @$_POST['order'][0]['column'], 'start' => @$_POST['start'], 'length' => @$_POST['length'], 'draw' => @$_POST['draw'],'user_id' => @$_POST['user_id'],'status' => @$_POST['status'],'address' => @$_POST['address']);

    $page = floor($input['start'] / $input['length']) + 1;

    $apiData = ['search' => $input['search'], 'page' => $page, 'limit' => $input['length'],'user_id' => @$_POST['user_id'],'status' => @$_POST['status'],'address' => @$_POST['address']];

    $response = sendCurlRequest(BASE_URL.'/get-anaomous-post-request', 'GET', $apiData);

    // Decode the JSON response
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

    // dump($decodedResponse);

    // Initialize variables
    $final = [];
    if (!empty($decodedResponse['body'])) {
        foreach ($decodedResponse['body'] as $key => $row) {
            $link = 'post-details.php?id=';
            $text = 'Post';
            $textColor = 'text-success'; // default green
            $showLink = true;

            if ($row['request_type'] == 1) {
                $link = 'listing-details.php?id=';
                $text = 'Listing';
                $textColor = 'text-info'; // default
                $showLink = false;
            }
            if ($row['request_type'] == 2) {
                $link = '';
                $text = 'Contact us';
                $textColor = 'text-primary'; // default
                $showLink = false;
            }

            $idEncoded = base64_encode($row['request_id']);
            $id = base64_encode($row['id']);
            $viewLink = $link . $idEncoded;

            $textHtml =  "<a href='$viewLink' class='$textColor'>$text</a>";
               

            $badgeCount = $row['unread_count'] ?? 0; // replace with your actual badge count column

            if (!empty($row['email'])) {
                $mailButton = '<button class="btn btn-sm" onclick="openMailPopup(' . $row['id'] . ')">
                                <i class="fas fa-envelope"></i>
                            </button>';
            } else {
                $badgeHtml = $badgeCount > 0 ? '<span class="badge badge-danger badge-pill" style="position:absolute; top:-5px; right:-5px;background-color: #dc3545; color: white;">' . $badgeCount . '</span>' : '';

                $mailButton = '<a class="btn btn-sm position-relative" href="ananomuschat.php?id=' . $id . '">
                                <i class="fas fa-comment-dots"></i>
                                ' . $badgeHtml . '
                            </a>';
            }

            $final[] = array(
                "DT_RowId" => $row['id'],
                $key + 1,
               '<div class="content d-flex flex-row align-items-center userName">'
                . ( !empty($row['email']) ? $row['email'] : $row['phone'] )
                . '</div>',
                '<div class="userHandle">'.$textHtml.'</div>',
                '<div class="d-flex flex-row align-items-center">
                ' .$row['message'].'</div>',                
                '<div class="text-gray"> '.formatToUSDate($row['created_at'],1).'</div>',
            
                '' .$mailButton.''. renderActionButtons(base64_encode($row['id']), 'anonymous_user_report_posts', $link , $link, false, false, true) .  '' // renderActionButtons($id, $table, $editLink = '', $viewLink = '', $hasView = true, $hasEdit = true, $hasDelete = true)
            );
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

// deleteAnanomousRequest
if(isset($_GET['action']) && $_GET['action'] == "deleteAnanomousRequest"){
    $id     =  cleanInputs(base64_decode($_POST['id']));
    $apiData = [];
    $response = sendCurlRequest(BASE_URL.'/deleteAnanomousRequest?id='.$id, 'DELETE', $apiData);
    echo $response;
}
// deleteLink
if(isset($_GET['action']) && $_GET['action'] == "deleteLink"){
    $id     =  cleanInputs(base64_decode($_POST['id']));
    $apiData = [];
    $response = sendCurlRequest(BASE_URL.'/delete-link?id='.$id, 'DELETE', $apiData);
    echo $response;
}

// test_notification_fn
if(isset($_GET['action']) && $_GET['action'] == "test_notification_fn"){
    $notification_id     =  cleanInputs($_POST['noti_id']);
    $apiData = [
        'notification_id' => $notification_id,
    ];
    $response = sendCurlRequest(BASE_URL.'/send-notification-by-id', 'POST', $apiData);
    echo $response;
}

// changeLinkStatus
if(isset($_GET['action']) && $_GET['action'] == "changeLinkStatus"){
    $status     =  cleanInputs($_POST['status']);
    $id    =  cleanInputs($_POST['id']);
    $apiData = ['status' => $status];
    // dump($_POST);
    $response = sendCurlRequest(BASE_URL.'/update-link?id='.$id, 'PUT', $apiData);
    echo $response;
}