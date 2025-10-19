<?php
include_once('includes/check-session.php');

// Title and page rendering (not changed)
$title = "Create Post | Sales Representative";

// get my posts
$apiData = ['user_id' => $userDetails['id'], 'type' => 0, 'search' => $input['search'], 'page' => $page, 'limit' => $input['length']];
$response = sendCurlRequest(BASE_URL.'/get-posts', 'GET', $apiData);
$decodedResponse = json_decode($response, true);


// Create Link From Existing Post
if (isset($_POST['draftPost']) || isset($_POST['generateLink'])) {

    $status = isset($_POST['draftPost']) ? '0' : '1';
    $linkID = intval($_POST['linkID'] ?? 0);   // 0 = new

    $apiDataU = [
        'post_id'          => cleanInputs($_POST['selectedPostId']),
        'user_id'          => cleanInputs($userDetails['id'] ?? 0),
        'link_type'        => 1,
        'message_template' => ($_POST['templates_json'] ?? ''),
        'is_saved'         => isset($_POST['save_template']) ? '1' : '0',
    ];

    // Only add referral_discount if provided
    if (!empty($_POST['discount_amount'])) {
        $apiDataU['referral_discount'] = cleanInputs($_POST['discount_amount']);
    }

    // Only add expire_time if provided
    if (!empty($_POST['discount_expiry'])) {
        $apiDataU['expire_time'] = cleanInputs(strtotime($_POST['discount_expiry']));
    }

    // Only add selected_template_ids if provided
    if (!empty($_POST['selected_template_ids'])) {
        $apiDataU['load_template_ids'] = implode(',',$_POST['selected_template_ids']);
    }

    if($linkID > 0){
        $apiDataU['linkID'] = $linkID;
    }

    if($linkID > 0){
        $response        = sendCurlRequest(BASE_URL . '/edit-link', 'PUT', $apiDataU, [], true);
    }else{
        $response        = sendCurlRequest(BASE_URL . '/create-sale-person-link', 'POST', $apiDataU, [], true);
    }
    
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);

    // -------- handle response ----------
    if ($decodedResponse['success']) {
        // unlink temp files if any ...
        setcookie('wb_successMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href='posts.php'</script>";
        exit;
    }

    // on error
    setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
    echo "<script>window.location.href='create-post.php'</script>";
    exit;
}
    

include('pages/posts/create.html');
?>
