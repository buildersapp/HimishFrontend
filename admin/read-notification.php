<?php
include_once('utils/helpers.php');

$id =base64_decode($_GET['id']);
$code =base64_decode($_GET['code']);
$request_id =base64_decode($_GET['request_id']);
$user_id =base64_decode($_GET['user_id']);

if ($id > 0) {

    $apiData=['type' => 1, 'notification_id' => $id];
    $response = sendCurlRequest(BASE_URL.'/read-notification', 'POST', $apiData);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        if($code == 9999 || $code == 54 || $code == 3){
            header('Location: post-details.php?scrollpos=bottom&id='.base64_encode($request_id));
        }
        else if($code ==21){
            header('Location: listing-details.php?scrollpos=bottom&id='.base64_encode($request_id));
        }
        else if($code ==644){
            header('Location: ads-details.php?scrollpos=bottom&id='.base64_encode($request_id));
        }
        else if($code ==22){
            header('Location: deal-details.php?scrollpos=bottom&id='.base64_encode($request_id));
        }
        else if($code ==23){
            header('Location: deal-share-details.php?scrollpos=bottom&id='.base64_encode($request_id));
        }
        else if($code == 24 || $code == 1234){
            header('Location: ananomousRequest.php');
        }
        else if($code == 6666){
            header('Location: dispute-requests.php');
        }else if($code == 8888){
            header('Location: community-requests.php');
        }else if($code == 7777){
            header('Location: claimed-business-requests.php?search='.base64_encode($request_id).'');
        }
        else if($code == 98999){
            header('Location: contact-us.php?user_id='.$_GET['user_id'].'');
        }else{
            header('Location: dashboard.php');
        }
    }
}