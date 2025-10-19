<?php
include_once('includes/check-session.php');

$commID = isset($_GET['id']) ? base64_decode($_GET['id']) : 0;

if($commID > 0){
    $apiData=[];
    $response = sendCurlRequest(BASE_URL.'/get-community?id='.$commID, 'GET', $apiData);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success']){
        $commData = $decodedResponse['body'][0];
    }
}

// Add Community
if (isset($_POST['addCommunity']) || isset($_POST['updateCommunity'])) {

    // check allowed creations
    if($settings['max_community_per_rep'] > $userDetails['my_community']){

        $communityId = intval($_POST['id'] ?? 0);   // 0 = new
        $is_private  = $_POST['is_private'] ?? '0';

        $apiDataU = [
            'name'         => cleanInputs($_POST['community_name']),
            'description'  => cleanInputs($_POST['description'] ?? ''),
            'address'      => cleanInputs($_POST['address'] ?? ''),
            'latitude'     => cleanInputs($_POST['latitude'] ?? ''),
            'longitude'    => cleanInputs($_POST['longitude'] ?? ''),
            'city'         => cleanInputs($_POST['city'] ?? ''),
            'state'        => cleanInputs($_POST['state'] ?? ''),
            'country_code' => cleanInputs($_POST['country_code'] ?? ''),
            'is_private'   => cleanInputs($is_private),
        ];

         // Only add editId if editing

        if($communityId > 0){
            $apiDataU['editId'] = $communityId;
        }

        // (cropped or normal file)
        // Check if a file was uploaded
        $tempFilePath = "";
        if(isset($_POST['community_cropped']) && !empty($_POST['community_cropped'])) {
            $croppedImage = $_POST['community_cropped']; // Base64 string
        
            // Convert Base64 to an actual file
            $imageParts = explode(";base64,", $croppedImage);
            $imageBase64 = base64_decode($imageParts[1]);
            $tempDir = __DIR__ . '/assets/uploads'; // Ensure this folder exists
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true); // Create the directory if it doesn't exist
            }
            $tempFilePath = $tempDir . '/cropped_img_' . time() . '.jpg';
        
            // Save the decoded image
            file_put_contents($tempFilePath, $imageBase64);

            $apiDataU['image'] = new CURLFile($tempFilePath, 'image/jpeg', 'cropped_image.jpg');
        
        }else{
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $imageFilePath = $_FILES['image']['tmp_name'];
                $apiDataU['image'] = new CURLFile($imageFilePath, $_FILES['image']['type'], $_FILES['image']['name']);
            }
        }

        //dump($apiDataU);
        if($communityId > 0){
            $response        = sendCurlRequest(BASE_URL . '/admin-edit-community', 'PUT', $apiDataU, [], true);
        }else{
            $response        = sendCurlRequest(BASE_URL . '/add-community', 'POST', $apiDataU, [], true);
        }
        
        $decodedResponse = json_decode($response, true);
        //dump($decodedResponse);

        // -------- handle response ----------
        if ($decodedResponse['success']) {
            // unlink temp files if any ...
            setcookie('wb_successMsg', $decodedResponse['message'], time() + 5, "/");
            echo "<script>window.location.href='communities.php'</script>";
            exit;
        }

        // on error
        $commData = $apiDataU; // to refill form
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 5, "/");
        //echo "<script>window.location.href='create-community.php'</script>";
        //exit;
    }else{
        setcookie('wb_errorMsg', 'You’ve reached the maximum number of communities you can create. Please contact support if you need assistance.', time() + 5, "/");
        echo "<script>window.location.href='create-community.php'</script>";
        exit;
    }
}

// Title and page rendering (not changed)
$title = "Create Community | Sales Representative";

include('pages/community/create.html');
?>
