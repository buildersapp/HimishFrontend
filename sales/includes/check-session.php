<?php 
include_once('../admin/utils/helpers.php');
include_once('includes/web-helpers.php');

// Errors
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);

// Set no-cache headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

ini_set('session.gc_maxlifetime', 604800);
ini_set('session.cookie_lifetime', 604800);

if (isset($_SESSION['sr_timezone']) && !empty($_SESSION['sr_timezone'])) {
    date_default_timezone_set($_SESSION['sr_timezone']);
} else {
    date_default_timezone_set('UTC');
}

function clearAuthSessionAndRedirect($message = 'Unauthorized') {
    setcookie('wb_errorMsg', $message, time() + 5, "/");
    setcookie('pz_sr_user_auth', '', time() - 3600, "/", "", isset($_SERVER['HTTPS']), true);
    unset($_SESSION['hm_sr_auth_data'], $_SESSION['hm_sr_logged_in'], $_SESSION['sr_timezone']);
    echo "<script>window.location.href = 'index.php'</script>";
    exit;
}

if (!isset($_SESSION['hm_sr_auth_data']['id'])) {
    clearAuthSessionAndRedirect('Unauthorized access');
}

$query_data = '?user_id=' . $_SESSION['hm_sr_auth_data']['id'] . '&page=1&limit=1';
$response = sendCurlRequest(BASE_URL . '/get-profile' . $query_data, 'GET', []);
$decodedResponse = json_decode($response, true);

// Handle 401 Unauthorized
if (isset($decodedResponse['code']) && $decodedResponse['code'] == 401) {
    http_response_code(401);
    clearAuthSessionAndRedirect('Session expired');
}

if (empty($decodedResponse['success'])) {
    clearAuthSessionAndRedirect($decodedResponse['message'] ?? 'Unknown error');
}

$userDetails = $decodedResponse['body'] ?? [];
if (($userDetails['account_type'] ?? null) !== 3) {
    clearAuthSessionAndRedirect();
}


$fullUrl = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$current_page = basename($_SERVER['PHP_SELF']);

$menuItems = [
    [
        "tab_label" => "Overview",
        "sidebar_label" => "Dashboard",
        "icon" => "fa-home",
        "url" => "dashboard.php",
        "show_tab" => true,
        "show_sidebar" => true,
        "show_mobile" => true
    ],
    [
        "tab_label" => "Create Post",
        "sidebar_label" => "Create Post",
        "icon" => "fa-plus-circle",
        "url" => "create-post.php",
        "show_tab" => true,
        "show_sidebar" => true,
        "show_mobile" => true
    ],
    [
        "tab_label" => "Create Community",
        "sidebar_label" => "Create Community",
        "icon" => "fa-users",
        "url" => "create-community.php",
        "show_tab" => true,
        "show_sidebar" => true,
        "show_mobile" => true
    ],
    [
        "tab_label" => "Communities",
        "sidebar_label" => "Communities",
        "icon" => "fa-user-group",
        "url" => "communities.php",
        "show_tab" => true,
        "show_sidebar" => true,
        "show_mobile" => true
    ],
    [
        "tab_label" => "Posts",
        "sidebar_label" => "Posts",
        "icon" => "fa-file-text",
        "url" => "posts.php",
        "show_tab" => true,
        "show_sidebar" => true,
        "show_mobile" => true
    ],
    [
        "tab_label" => "Commissions",
        "sidebar_label" => "Commissions",
        "icon" => "fa-dollar-sign",
        "url" => "commissions.php",
        "show_tab" => true,
        "show_sidebar" => true,
        "show_mobile" => true
    ],
    [
        "tab_label" => "Message Templates",
        "sidebar_label" => "Message Templates",
        "icon" => "fa-message",
        "url" => "message-templates.php",
        "show_tab" => true,
        "show_sidebar" => true,
        "show_mobile" => true
    ],
];

// get settings
$responseSettings = sendCurlRequest(BASE_URL . '/get-sales-person-setting', 'GET', []);
$decodedResponseSettings = json_decode($responseSettings, true);
if($decodedResponseSettings['success'] == 1){
    $settings = $decodedResponseSettings['body'];
}

// get dashboard
$responseDashboard = sendCurlRequest(BASE_URL . '/get-sale-person-dashboard', 'GET', []);
$decodedResponseDashboard = json_decode($responseDashboard, true);
if($decodedResponseDashboard['success'] == 1){
    $dashboardData = $decodedResponseDashboard['body'];
    $totalTemplates = isset($dashboardData['templateCounts']) && is_array($dashboardData['templateCounts'])
        ? array_sum($dashboardData['templateCounts'])
        : 0;
}
?>