<?php
include_once('utils/helpers.php');

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'OCR Scans', 'url' => 'ocr-scans.php']
];

if(isset($_POST['uploadOCR'])){
    $apiDataU = [];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $imageFilePath = $_FILES['image']['tmp_name'];
        $apiDataU['image'] = new CURLFile($imageFilePath, $_FILES['image']['type'], $_FILES['image']['name']);
    }

    $response = sendCurlRequest(BASE_URL.'/imageClassificationGemini', 'POST', $apiDataU, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if(isset($decodedResponse['title'])){
        echo "<script>window.location.href = 'ocr-scans.php'</script>";
    }else{
        $err = 1;
        echo "<script>window.location.href = 'ocr-scans.php'</script>";
    }
}

$title = "OCR Scans";
include('pages/ocrScans/list.html');