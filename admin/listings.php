<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => 'listings.php'],
    ['name' => 'Listings (Looking For)', 'url' => '']
];

$listingType = isset($_GET['type']) ? 1 : 0;

$err = 0;
if(isset($_POST['addPost'])){

    $categoryArray = [];

    $apiDataU = [
        'title'         => cleanInputs($_POST['title']),
        'info'          => cleanInputs($_POST['description']),
        'address'       => cleanInputs($_POST['address']),
        'latitude'      => cleanInputs($_POST['latitude']),
        'longitude'     => cleanInputs($_POST['longitude']),
        'city'     => cleanInputs($_POST['city']),
        'state'     => cleanInputs($_POST['state']),
        'community_type'=> cleanInputs($_POST['community_type']),
        'radius'        => cleanInputs($_POST['radius']),
        'service'        => $_POST['service'],
        'type'          => 1,
    ];

    if($_POST['community_type'] != 0){
        $apiDataU['community'] = implode(',',$_POST['community']);
    }

    $apiDataU['post_locations'] = [];
    if(!empty($apiDataU['latitude']) && !empty($apiDataU['longitude'])){
        $apiDataU['post_locations'][] = array('latitude' => $apiDataU['latitude'], 'longitude' => $apiDataU['longitude'], 'community_id' => 0);
        $apiDataU['post_locations'] = json_encode($apiDataU['post_locations']);
    }

    if(isset($_POST['user_id']) && !empty($_POST['user_id'])){
        $apiDataU['user_id'] = cleanInputs($_POST['user_id']);
    }

    // get categorization
    if(isset($apiDataU['info']) && !empty($apiDataU['info'])){
        $responseCat = sendCurlRequest(BASE_URL.'/searchPills', 'POST', ['description' => cleanInputs($apiDataU['info'])], [], true);
        $decodedResponseCat = json_decode($responseCat, true);
        //dump($decodedResponseCat);
        if (!empty($decodedResponseCat['body']) && is_array($decodedResponseCat['body'])) {
            foreach ($decodedResponseCat['body'] as $category) {
                // $apiDataU['service'] = $category['keywords4'];
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
    
    // Check if a file was uploaded
    $tempFilePath = "";
    if(isset($_POST['cropped_image']) && !empty($_POST['cropped_image'])) {
        $croppedImage = $_POST['cropped_image']; // Base64 string
    
        // Convert Base64 to an actual file
        $imageParts = explode(";base64,", $croppedImage);
        $imageBase64 = base64_decode($imageParts[1]);
        $tempDir = __DIR__ . '/uploads'; // Ensure this folder exists
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true); // Create the directory if it doesn't exist
        }
        $tempFilePath = $tempDir . '/cropped_img_' . time() . '.jpg';
    
        // Save the decoded image
        file_put_contents($tempFilePath, $imageBase64);

        $apiDataU['image'] = new CURLFile($tempFilePath, 'image/jpeg', 'cropped_image.jpg');
    
    }else{
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $imageFilePath = $_FILES['image']['tmp_name'];
            $apiDataU['image'] = new CURLFile($imageFilePath, $_FILES['image']['type'], $_FILES['image']['name']);
        }
    }

    // Check if a file was uploaded
    // $uploadedFiles = [];
    // if (isset($_FILES['image']) && $_FILES['image']['error'][0] == UPLOAD_ERR_OK) {
    //     // Loop through all uploaded files
    //     foreach ($_FILES['image']['tmp_name'] as $key => $tmpName) {
    //         if ($_FILES['image']['error'][$key] == UPLOAD_ERR_OK) {
    //             // Get file path and type
    //             $filePath = $_FILES['image']['tmp_name'][$key];
    //             $fileType = $_FILES['image']['type'][$key];
    //             $fileName = $_FILES['image']['name'][$key];
                
    //             // Create CURLFile instance for each file
    //             $uploadedFiles[] = new CURLFile($filePath, $fileType, $fileName);
    //         }
    //     }
    // }

    // Example: If you want to send the files in an API request
    //$apiDataU['image'] = $uploadedFiles; // This contains all the uploaded files as CURLFile objects

    $response = sendCurlRequest(BASE_URL.'/create-post', 'POST', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        setcookie('successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'listings.php'</script>";
    }else{
        $err = 1;
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'listings.php'</script>";
    }
}

// IMPORT LOOKING FOR FILE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel'])) {
    $file = $_FILES['excel'];
    
    // Allowed MIME types for Excel and CSV
    $allowedTypes = [
        'application/vnd.ms-excel', // .xls
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
        'text/csv', // .csv (generic CSV MIME type)
        'application/csv', // Some browsers use this
        'text/plain' // Some CSV files may be detected as plain text
    ];

    // Validate the file type
    if (!in_array($file['type'], $allowedTypes)) {
        echo 'Invalid file type. Only Excel (.xls, .xlsx) and CSV (.csv) are allowed.';
        exit;
    }
    
    $apiData    = [];

    // Check if a file was uploaded
    if (isset($_FILES['excel']) && $_FILES['excel']['error'] == UPLOAD_ERR_OK) {
        $imageFilePath = $_FILES['excel']['tmp_name'];
        $apiData['excel'] = new CURLFile($imageFilePath, $_FILES['excel']['type'], $_FILES['excel']['name']);
    }

    $response = sendCurlRequest(BASE_URL.'/admin-import-looking-for', 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        echo "<script>window.location.href = 'listings.php'</script>";
    }
}

// EXPORT LOOKING FOR FILE
if (isset($_GET['tp']) && $_GET['tp'] == "export") {
    try {
        // Mock API URL and authorization
        $apiData = [];
        $query_data = '?page=1&limit=1';
        $response = sendCurlRequest(BASE_URL . '/admin-export-looking-for' . $query_data, 'POST', $apiData);
        $responseData = json_decode($response, true);

        if (!isset($responseData['data']) || !is_array($responseData['data'])) {
            throw new Exception('Invalid response data.');
        }

        $rawData = $responseData['data'];
        //dump($rawData);
        $data = [];

        // Convert flat indexed arrays to associative format
        foreach ($rawData as $item) {
            if (is_array($item)) {
                $data[] = [
                    "ID"             => $item[0] ?? '',              // Unique identifier for each looking-for entry.
                    "User ID"        => $item[1] ?? '',              // ID of the user who created the post.
                    "Title"          => $item[2] ?? '',              // Title of the post or looking-for item.
                    "Description"    => $item[3] ?? '',              // Detailed description of the post.
                    "Community"      => $item[4] ?? '',              // The community the post belongs to.
                    "Community Type" => $item[5] ?? '',              // The type of community, either Public or Private.
                    "Radius"         => $item[6] ?? '',              // The radius for the location associated with the post.
                    "Address"        => $item[7] ?? '',              // Address associated with the post.
                    "City"           => $item[8] ?? '',              // City for the location of the post.
                    "State"          => $item[9] ?? '',              // State for the location of the post.
                    "Latitude"       => $item[10] ?? '',             // Latitude coordinate for the post.
                    "Longitude"      => $item[11] ?? '',             // Longitude coordinate for the post.
                    "Image"          => $item[13] ?? ''              // URL for the image related to the post.
                ];
            }
        }

        // Disable output buffering
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers for CSV download
        $fileName = "LookingFor" . date('YmdHis') . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        // Open output stream
        $output = fopen('php://output', 'w');

        // Write headers to CSV
        if (!empty($data)) {
            // Adding comments as headers
            fputcsv($output, [
                "ID",
                "User ID",
                "Title",
                "Description",
                "Community",
                "Community Type (If 0 then all community)",
                "Radius",
                "Address",
                "City",
                "State",
                "Latitude",
                "Longitude",
                "Image"
            ]);
        }

        // Write data rows to CSV
        foreach ($data as $row) {
            fputcsv($output, $row);
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

## get All Users
$user_data = sendCurlRequest(BASE_URL.'/all-users', 'GET', []);
$userDataResponse = json_decode($user_data, true);
$users = $userDataResponse['body'];

## get All Community
$community_data = sendCurlRequest(BASE_URL.'/get-community', 'GET', []);
$communityDataResponse = json_decode($community_data, true);
$communities = $communityDataResponse['body'];

$title = "Listings";
if (!hasPermission($title, 'view')) {
    include('pages/no-permission.html');
    exit;
}

include('pages/listings/list.html');