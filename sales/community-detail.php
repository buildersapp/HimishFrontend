<?php
include_once('includes/check-session.php');

$community_Id = isset($_GET['id']) ? base64_decode($_GET['id']) : 0;

if($community_Id > 0){
    $apiData=[];
    $query_data ='?id='.$community_Id;
    $response = sendCurlRequest(BASE_URL.'/get-community'.$query_data, 'GET', $apiData);
    $decodedResponse = json_decode($response, true);
    if(count($decodedResponse)){
        $communities = $decodedResponse['body'];
    }

    $communityMembers = $communities[0]['members'];

    //dump($communities);

    // Title and page rendering (not changed)
    $title = $communities[0]['name']. " | Community | Sales Representative";

    include('pages/community/detail.html');
}else{
    setcookie('wb_errorMsg', 'Invalid Community', time() + 5, "/");
    echo "<script>window.location.href='communities.php?md=open'</script>";
    exit;
}
?>
