<?php
include_once('includes/custom-functions.php');
include_once('admin/utils/helpers.php');

$showcaseId = isset($_GET['id']) ? (int) base64_decode($_GET['id']) : 0;
$companyId = isset($_GET['company_id']) ? (int) base64_decode($_GET['company_id']) : 0;

if($showcaseId > 0){

    $response = sendCurlRequest(BASE_URL.'/delete-showcase?id='.$showcaseId, 'DELETE', [], [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        setcookie('wb_successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'company-details.php?id=".base64_encode($companyId)."'</script>";
    }else{
        $err = 1;
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'company-details.php?id=".base64_encode($companyId)."'</script>";
    }
}
?>
