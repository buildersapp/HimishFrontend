<?php
include_once('admin/utils/helpers.php');

// Get raw request body from Stripe
$input = @file_get_contents("php://input");
$data = json_decode($input, true);
$logFile = __DIR__ . '/webhook_log.txt';

//file_put_contents($logFile, '');

// Log full payload for debugging
file_put_contents($logFile, date("Y-m-d H:i:s") . " - Full Payload: " . json_encode($data) . PHP_EOL, FILE_APPEND);

// Validate payload
if (!$data) {
    http_response_code(400); // Bad request
    exit('Invalid payload');
}

// Prepare API data
$apiDataU = [
    'payload' => json_encode($data)
];

// Log API request data
file_put_contents($logFile, date("Y-m-d H:i:s") . " - Sending Data: " . json_encode($apiDataU) . PHP_EOL, FILE_APPEND);

// Send data to another API
$response = sendCurlRequest(BASE_URL . '/webhook-sms', 'POST', $apiDataU);
$decodedResponse = json_decode($response, true);

// Log response
file_put_contents($logFile, date("Y-m-d H:i:s") . " - API Response: " . $response . PHP_EOL, FILE_APPEND);

// Respond to Stripe
http_response_code(200);
exit('Webhook processed successfully');

?>
