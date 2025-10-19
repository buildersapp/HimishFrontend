<?php
include_once('includes/check-session.php');

$linkID = isset($_GET['id']) ? base64_decode($_GET['id']) : 0;
if($linkID){
    $postsResponse = sendCurlRequest(BASE_URL.'/get-sale-person-link', 'GET', ['link_id' => $linkID]);
    $postsResponseDecoded = json_decode($postsResponse,true);
    if($postsResponseDecoded['success']){
        $postData = $postsResponseDecoded['body'];
    }
}
// Title and page rendering (not changed)
$title = $postData[0]['post']['title']." | Post Detail | Sales Representative";

include('pages/posts/detail.html');
?>
