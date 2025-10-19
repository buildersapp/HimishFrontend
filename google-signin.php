<?php
require_once 'vendor/autoload.php'; // if using composer
include_once('admin/utils/helpers.php');

use Google\Client as GoogleClient;

session_start();

// Google oAuth credentials

// Read incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['credential'])) {
    echo json_encode(['success' => false, 'error' => 'No token provided']);
    exit;
}

// Verify token using Google Client
$client = new GoogleClient(['client_id' => GOOGLE_OAUTH_CLIENT_ID]);
$payload = $client->verifyIdToken($data['credential']);

if ($payload) {
    $email      = cleanInputs($payload['email']);
    $name       = cleanInputs($payload['name']);
    $social_id  = cleanInputs($payload['sub']); // Google's unique user ID
    $timezone = isset($data['timezone']) ? cleanInputs($data['timezone']) : 'UTC';

    $randomString = bin2hex(random_bytes(10));
    $device_id   = cleanInputs($randomString);
    $device_name = 'web';

    $apiData = [
        'name'         => $name,
        'email'         => $email,
        'social_id'     => $social_id,
        'social_type'   => 1, // 1 = Google
        'device_type'   => 3, // Web
        'device_token'  => 'Web',
        'device_name'   => $device_name,
        'device_id'     => $device_id,
    ];

    $response = sendCurlRequest(BASE_URL.'/social-login', 'POST', $apiData);
    $decodedResponse = json_decode($response, true);

    if ($decodedResponse['success']) {
        $_SESSION['hm_wb_timezone']   = $timezone;
        $_SESSION['hm_wb_auth_data']  = $decodedResponse['body'];
        $_SESSION['hm_wb_logged_in']  = true;

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Social login failed']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid token']);
}
?>
