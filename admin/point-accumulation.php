<?php
include_once('utils/helpers.php');

$response = sendCurlRequest(BASE_URL.'/get-point-accumulation-admin', 'GET', []);
$decodedResponse = json_decode($response, true);
$pointsArray = $decodedResponse['body'];

$responseR = sendCurlRequest(BASE_URL.'/get-company-rating-points-admin', 'GET', []);
$decodedResponseR = json_decode($responseR, true);
$ratingArray = $decodedResponseR['body'];

// Breadcrumbs
$breadcrumb = [
    ['name' => 'General Settings', 'url' => ''],
    ['name' => 'Point Accumulation', 'url' => 'point-accumulation.php']
];

if(isset($_POST['updatePoints'])){
    $updatedRows = $_POST['rows'];
    $apiData = [
        'updatedRows' => json_encode($updatedRows),
    ];

    $response = sendCurlRequest(BASE_URL.'/update-point-accumulation-admin', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'point-accumulation.php'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'point-accumulation.php?id=".$_GET['id']."'</script>";
    }    
}

if(isset($_POST['updateRatings'])){
    $updatedRows = $_POST['rowsR'];

    $checkValid = validateUpdatedRows($updatedRows);

    if(!$checkValid['status']){
        setcookie('errorMsg', $checkValid['message'], time() + 5, "/");
        echo "<script>window.location.href = 'point-accumulation.php'</script>";
        exit;
    }

    $apiData = [
        'updatedRows' => json_encode($updatedRows),
    ];

    $response = sendCurlRequest(BASE_URL.'/update-company-rating-points-admin', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'point-accumulation.php'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'point-accumulation.php'</script>";
    }    
}

function validateUpdatedRows($updatedRows) {
    $previousMax = 0;
    $expectedType = 0;

    foreach ($updatedRows as $index => $row) {
        // Check if all required keys are present
        if (!isset($row['min'], $row['max'], $row['type'])) {
            return [
                'status' => false,
                'message' => "Missing required keys at index $index."
            ];
        }

        // Check if min, max, and type are numeric
        if (!is_numeric($row['min']) || !is_numeric($row['max']) || !is_numeric($row['type'])) {
            return [
                'status' => false,
                'message' => "Non-numeric values found at index $index."
            ];
        }

        $min = (int) $row['min'];
        $max = (int) $row['max'];
        $type = (int) $row['type'];

        // Check if min is greater than the previous max
        if ($min <= $previousMax) {
            return [
                'status' => false,
                'message' => "Invalid range: 'min' ($min) is less than or equal to the previous 'max' ($previousMax) at index $index."
            ];
        }

        // Check if type is sequential
        if ($type !== $expectedType) {
            return [
                'status' => false,
                'message' => "Type mismatch: Expected $expectedType but found $type at index $index."
            ];
        }

        // Ensure min is less than max
        if ($min >= $max) {
            return [
                'status' => false,
                'message' => "Invalid range: 'min' ($min) is greater than or equal to 'max' ($max) at index $index."
            ];
        }

        // Update previousMax and increment expectedType
        $previousMax = $max;
        $expectedType++;
    }

    return [
        'status' => true,
        'message' => "Validation successful."
    ];
}

$title = "Point Accumulation";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}
include('pages/pointAccumulation/list.html');