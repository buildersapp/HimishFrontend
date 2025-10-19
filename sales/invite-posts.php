<?php
include_once('includes/check-session.php');

$ID = isset($_GET['id']) ? base64_decode($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 0;

$old = $_SESSION['invite_old'] ?? [];
$errors = $_SESSION['invite_errors'] ?? [];

unset($_SESSION['invite_old'], $_SESSION['invite_errors']);

if($type == 0 && !empty($ID)){
    $apiData=[];
    $response = sendCurlRequest(BASE_URL.'/get-sale-person-link?link_id='.$ID, 'GET', $apiData);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        $ILData = $decodedResponse['body'][0];

        // Preparing Default Templates
        $replacements = [
            "[POST_NAME]"     => $ILData['post']['title'] ?? '',
            "[INVITE_LINK]"        => $ILData['share_link'] ?? ''
        ];

        // List of template keys to process
        $templateKeys = [
            'post_default_template_email',
            'post_default_template_whatsapp',
            'post_default_template_copy_share',
            'post_default_template_sms'
        ];
    }
}else{
    setcookie('wb_errorMsg', 'Forbidden.', time() + 5, "/");
    echo "<script>window.location.href='posts.php'</script>";
    exit;
}

// Title and page rendering (not changed)
$title = "Invite Users | Sales Representative";
if($type == 1){
    $title = "Invite Users | Post | ".$ILData['post']['title']." | Sales Representative";
}
include('pages/posts/invite.html');

?>
