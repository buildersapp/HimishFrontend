<?php
include_once('includes/custom-functions.php');
include_once('admin/utils/helpers.php');
$communityId = isset($_GET['id']) ? (int) base64_decode($_GET['id']) : 0;

$apiData=[];
$query_data ='?community_id='.$communityId;
$response = sendCurlRequest(BASE_URL.'/getCommunityMember'.$query_data, 'GET', $apiData);
$decodedResponse = json_decode($response, true);
$communityMembers = [];
$communityDetails = (object)[];
$isMyCommunity = 0;
if(count($decodedResponse)){
    $communityMembers = $decodedResponse['body'];
    $communityDetails = $decodedResponse['meta']['community'];
}

if(@$userDetails['id'] == $communityDetails['user_id']){
    $isMyCommunity = 1;
}

$active_community_members = array_values(array_filter($communityMembers, function($mem) {
    return $mem['status'] == 1;
}));

$community_member_requests = array_values(array_filter($communityMembers, function($mem) {
    return $mem['status'] == 0;
}));

// Title and page rendering (not changed)
$title = $communityDetails['name']." | Community";
include('pages/communities/detail.html');
?>
