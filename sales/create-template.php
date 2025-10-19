<?php
include_once('includes/check-session.php');

$tmpID = isset($_GET['id']) ? base64_decode($_GET['id']) : 0;

if($tmpID > 0){
    $apiData=[];
    $response = sendCurlRequest(BASE_URL.'/get-referral-template?id='.$tmpID, 'GET', $apiData);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        $tmpData = $decodedResponse['body'][0];
    }
}

// Add / Update Template
if (isset($_POST['addTemplate']) || isset($_POST['updateTemplate'])) {

    $templateId = intval($_POST['id'] ?? 0);   // 0 = new

    $apiDataU = [
        'type'         => cleanInputs($_POST['type']),
        'title'        => cleanInputs($_POST['title']),
        'message'      => $_POST['message'] ?? ''
    ];

    // Only add editId if editing

    if($templateId > 0){
        $apiDataU['id'] = $templateId;
    }

    //dump($apiDataU);
    if($templateId > 0){
        $response        = sendCurlRequest(BASE_URL . '/edit-user-template/'.$templateId, 'PUT', $apiDataU, [], true);
    }else{
        $response        = sendCurlRequest(BASE_URL . '/add-user-template', 'POST', $apiDataU, [], true);
    }
    
    $decodedResponse = json_decode($response, true);

    // -------- handle response ----------
    if ($decodedResponse['success']) {
        // unlink temp files if any ...
        setcookie('wb_successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href='message-templates.php?type=".$apiDataU['type']."'</script>";
        exit;
    }

    // on error
    setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
    echo "<script>window.location.href='create-template.php'</script>";
    exit;
}

// Title and page rendering (not changed)
$title = "Create Template | Sales Representative";

include('pages/messageTemplates/create.html');
?>
