<?php
include_once('utils/helpers.php');

$type = isset($_GET['type']) ? $_GET['type'] : 3;
$apiData = [ 'type' => $type ];
$response = sendCurlRequest(BASE_URL.'/admin-master-cat-subs', 'GET', $apiData);
// Decode the JSON response
$decodedResponse = json_decode($response, true);
$masterSubCats = $decodedResponse['body'] ?? [];
//dump($masterSubCats);

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Master', 'url' => 'master.php']
];

// IMPORT MASTER FILE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel'])) {
    $file = $_FILES['excel'];
    
    // Validate the file (check for allowed file types, size, etc.)
    $allowedTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    
    if (!in_array($file['type'], $allowedTypes)) {
        echo 'Invalid file type.';
        exit;
    }
    
    $apiData    = [];

    // Check if a file was uploaded
    if (isset($_FILES['excel']) && $_FILES['excel']['error'] == UPLOAD_ERR_OK) {
        $imageFilePath = $_FILES['excel']['tmp_name'];
        $apiData['excel'] = new CURLFile($imageFilePath, $_FILES['excel']['type'], $_FILES['excel']['name']);
    }

    $response = sendCurlRequest(BASE_URL.'/admin-import-master-cat-sub', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'master.php'</script>";
    }
}

// EXPORT MASTER FILE
if (isset($_GET['tp']) && $_GET['tp'] == "export") {
    try {
        // Mock API URL and authorization
        $apiData = [];
        $query_data = '?page=1&limit=1';
        $response = sendCurlRequest(BASE_URL . '/admin-export-master-cat-sub' . $query_data, 'POST', $apiData);
        $responseData = json_decode($response, true);

        if (!isset($responseData['data'])) {
            throw new Exception('Invalid response data.');
        }

        $data = $responseData['data'];

        // Disable any additional output buffering
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers for CSV download
        $fileName = "MasterCategories" . date('YmdHis') . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        // Open output stream
        $output = fopen('php://output', 'w');

        // Loop through each data type
        foreach ($data as $type => $items) {
            if (count($items) > 0) {
                // Add section header
                // fputcsv($output, [$type]);

                // Add rows
                foreach ($items as $item) {
                    fputcsv($output, $item);
                }

                // Add empty line for separation
                fputcsv($output, []);
            }
        }

        fclose($output);
        exit; // Ensure no further code is executed

    } catch (Exception $e) {
        // Handle errors
        error_log("Error in exporting data: " . $e->getMessage());
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Failed to export data.']);
        exit;
    }
}

// MERGE MASTE IDS
if(isset($_POST['mergeMaster'])){
    $selectIds = cleanInputs($_POST['other_ids']);
    $master_id = cleanInputs($_POST['master_id']);

    $apiDataU = [
        'master_id' => $master_id,
        'other_id' => $selectIds,
    ];
    
    $response = sendCurlRequest(BASE_URL.'/merge-category', 'POST', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'master.php'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'master.php'</script>";
    }
}


$title = "Master";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}
include('pages/masterSubCat/list2.html');