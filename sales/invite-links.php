<?php
include_once('includes/check-session.php');

$ID = isset($_GET['id']) ? base64_decode($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 0;

$old = $_SESSION['invite_old'] ?? [];
$errors = $_SESSION['invite_errors'] ?? [];

unset($_SESSION['invite_old'], $_SESSION['invite_errors']);

if($type == 1 && $ID > 0){
    $apiData=[];
    $response = sendCurlRequest(BASE_URL.'/get-community?id='.$ID, 'GET', $apiData);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        $ILData = $decodedResponse['body'][0];

        // Preparing Default Templates
        $replacements = [
            "[COMMUNITY_NAME]"     => $ILData['name'] ?? '',
            "[COMMUNITY_LOCATION]" => !empty($ILData['address']) ? '('.$ILData['address'].')' : '',
            "[INVITE_LINK]"        => $ILData['linkData'][0]['share_link'] ?? ''
        ];

        // List of template keys to process
        $templateKeys = [
            'community_default_template_email',
            'community_default_template_whatsapp',
            'community_default_template_copy_share',
            'community_default_template_sms'
        ];
    }
}else{
    setcookie('wb_errorMsg', 'Forbidden.', time() + 5, "/");
    echo "<script>window.location.href='communities.php'</script>";
    exit;
}

// Title and page rendering (not changed)
$title = "Invite Users | Sales Representative";
if($type == 1){
    $title = "Invite Users | Community | ".$ILData['name']." | Sales Representative";
}
include('pages/community/invite.html');

?>
