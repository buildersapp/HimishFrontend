<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'General Settings', 'url' => ''],
    ['name' => 'Website Content', 'url' => 'website-content.php']
];

$apiData=[];
$response = sendCurlRequest(BASE_URL.'/get-setting', 'GET', $apiData);
$decodedResponse = json_decode($response, true);
$final = $decodedResponse['body'];
if(!empty($final['login_page']) && !empty($final['early_access_page'])){
    $final['login_page'] = json_decode($final['login_page'], true);
    $final['early_access_page'] = json_decode($final['early_access_page'], true);
}

if (isset($_POST['updateSettings'])) {

    // Extract files
    $loginFile  = getUploadedFile($_FILES, 'login_page');
    $earlyFile  = getUploadedFile($_FILES, 'early_access_page');

    // Upload if present
    $login_image = $loginFile && $loginFile['error'] === 0 
        ? uploadImageToApi($loginFile) 
        : ($final['login_page']['image'] ?? '');

    $early_image = $earlyFile && $earlyFile['error'] === 0 
        ? uploadImageToApi($earlyFile) 
        : ($final['early_access_page']['image'] ?? '');

    // Build page data
    $login_page = [
        'title'       => cleanInputs($_POST['login_page']['title']),
        'description' => cleanInputs($_POST['login_page']['description']),
        'right_title'       => cleanInputs($_POST['login_page']['right_title']),
        'right_description' => cleanInputs($_POST['login_page']['right_description']),
        'image'       => $login_image
    ];

    $early_access_page = [
        'title'       => cleanInputs($_POST['early_access_page']['title']),
        'description' => cleanInputs($_POST['early_access_page']['description']),
        'right_title'       => cleanInputs($_POST['early_access_page']['right_title']),
        'right_description' => cleanInputs($_POST['early_access_page']['right_description']),
        'image'       => $early_image
    ];

    $id                 =   cleanInputs($_POST['id']);

    // Send API
    $apiData = [
        'tag_id'                       => $id,
        'login_page'        => json_encode($login_page),
        'early_access_page' => json_encode($early_access_page)
    ];

    $response = sendCurlRequest(BASE_URL.'/update-setting', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);

    if ($decodedResponse['success']) {
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'website-content.php'</script>";
    } else {
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'website-content.php'</script>";
    }
}

// --- Helper function for image upload API ---
function uploadImageToApi($file, $folder = 'pages') {
    if (!isset($file) || $file['error'] !== 0) {
        return '';
    }

    $uploadApiUrl = BASE_URL . "/upload-media"; // adjust if needed
    
    $fileData = [
        'image' => new CURLFile($file['tmp_name'], 'image/jpeg', $file['name'])
    ];

    $response = sendCurlRequest($uploadApiUrl, 'POST', $fileData, [], true);
    $decoded = json_decode($response, true);

    return $decoded['success'] ? $decoded['body']['image'] : '';
}

// Helper to extract nested file input
function getUploadedFile($filesArray, $key) {
    if (!isset($filesArray[$key])) {
        return null;
    }
    return [
        'name'     => $filesArray[$key]['name']['image'] ?? null,
        'type'     => $filesArray[$key]['type']['image'] ?? null,
        'tmp_name' => $filesArray[$key]['tmp_name']['image'] ?? null,
        'error'    => $filesArray[$key]['error']['image'] ?? 4,
        'size'     => $filesArray[$key]['size']['image'] ?? 0,
    ];
}


$title = "Website Content";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}
include('pages/generalSettings/websiteContent.html');