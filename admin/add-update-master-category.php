<?php
include_once('utils/helpers.php');

$id =base64_decode($_GET['post_id']);
$page = isset($_GET['page']) ? $_GET['page'] : 'posts';

if (isset($_POST['cateSbt'])) {

    $data = $_POST;

    // Get category information
    //$type = ($_POST['type']==1) ? 'Service' : 'Product';
    $data['type'] = ($_POST['type']==1) ? 'Service' : 'Product';

    // Process child fields
    if (isset($_POST['child'])) {

        $data['industry'] = $_POST['child'][0];
        $data['category'] = $_POST['child'][1];
        $data['subcategory'] = $_POST['child'][2];
        $data['subsubcategory'] = $_POST['child'][3];

        unset($data['child'], $data['cateSbt']);

        // Display the structured data
        $apiDataU = [
            'json_data' => json_encode($data),
        ];
        //dump($apiDataU);
        $response = sendCurlRequest(BASE_URL.'/addMasterData', 'POST', $apiDataU, [], true);
        $decodedResponse = json_decode($response, true);
        //dump($decodedResponse);
        if($decodedResponse['success']){
            setcookie('successMsg', 'New Category Created Successfully', time() + 5, "/");
            if($page == "listing"){
                echo "<script>window.location.href = 'listing-details.php?id=".base64_encode($id)."&newCatId=".base64_encode($decodedResponse['body']['__parent_id'])."&redirect=categories'</script>";
            }elseif($page == "deals"){
                echo "<script>window.location.href = 'deal-details.php?id=".base64_encode($id)."&newCatId=".base64_encode($decodedResponse['body']['__parent_id'])."&redirect=categories'</script>";
            }elseif($page == "dealshare"){
                echo "<script>window.location.href = 'deal-share-details.php?id=".base64_encode($id)."&newCatId=".base64_encode($decodedResponse['body']['__parent_id'])."&redirect=categories'</script>";
            }else{
                echo "<script>window.location.href = 'post-details.php?id=".base64_encode($id)."&newCatId=".base64_encode($decodedResponse['body']['__parent_id'])."&redirect=categories'</script>";
            }
        }else{
            setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
            echo "<script>window.location.href = 'add-update-master-category.php?post_id=".base64_encode($id)."'</script>";
        }
    }
}

if(isset($_POST['cateSbtOld'])){
    $name           =   cleanInputs($_POST['child'][3]);
    $catId          = cleanInputs($_POST['id']);
    $keywords1      = cleanInputs($_POST['keywords1']);
    $keywords2      = cleanInputs($_POST['keywords2']);
    $keywords3      = cleanInputs($_POST['keywords3']);
    $keywords4      = cleanInputs($_POST['keywords4']);
    $keywords5      = cleanInputs($_POST['keywords5']);
    $tab_label_option_posts  = cleanInputs($_POST['tab_label_option_posts']);
    $tab_label_option_company = cleanInputs($_POST['tab_label_option_company']);
    $tab_label_option_looking_for = cleanInputs($_POST['tab_label_option_looking_for']);

    $apiData = [
        'name'                         => $name,
        'keywords1'                    => $keywords1,
        'keywords2'                    => $keywords2,
        'keywords3'                    => $keywords3,
        'keywords4'                    => $keywords4,
        'keywords5'                    => $keywords5,
        'tab_label_option_posts'       => $tab_label_option_posts,
        'tab_label_option_company'     => $tab_label_option_company,
        'tab_label_option_looking_for' => $tab_label_option_looking_for,
        'show_in_filter' => 0
    ];

    $response = sendCurlRequest(BASE_URL.'/admin-master-cat-sub-add?id='.$catId, 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        setcookie('successMsg', 'Existing Category Updated Successfully', time() + 5, "/");
        echo "<script>window.location.href = 'post-details.php?id=".base64_encode($id)."&newCatId=".base64_encode($catId)."&redirect=categories'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'add-update-master-category.php?post_id=".base64_encode($id)."'</script>";
    }
}

$apiData=[];
$query_data ='?post_id='.$id;
$response = sendCurlRequest(BASE_URL.'/get-single-post'.$query_data, 'GET', $apiData);
$decodedResponse = json_decode($response, true);
//dump($decodedResponse);

// Handle 401 Unauthorized
if (isset($decodedResponse['code']) && $decodedResponse['code'] == 401) {
    http_response_code(401);
    header($_SERVER['PHP_SELF']);
    exit;
}

$final = ($decodedResponse['body']);

$gptCategory = (object)[];
if(count($final['post_images'])> 0){
    $image  =   cleanInputs($final['post_images'][0]['image']);
    $apiData = [
        'image' => $image
    ];

    $responseGptClassi = sendCurlRequest(BASE_URL.'/imageCategorizationGpt', 'POST', $apiData, [], true);
    $decodedResponseGptClassi = json_decode($responseGptClassi, true);
    $gptCategory = $decodedResponseGptClassi['body'];
}

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Master', 'url' => 'master.php'],
    ['name' => 'Add / Update Category', 'url' => ''],
];

$currentCategory = $final;
$breadcrumbCat = [];

$title = 'Add / Update Category';
include('pages/masterSubCat/add-update.html');