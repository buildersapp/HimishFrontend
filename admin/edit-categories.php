<?php
include_once('utils/helpers.php');

$id =base64_decode($_GET['id']);

$apiData=[];
$query_data ='?id='.$id.'&page=1&limit=1';
$response = sendCurlRequest(BASE_URL.'/admin-get-single-master-cat-sub'.$query_data, 'GET', $apiData);
$decodedResponse = json_decode($response, true);
//dump($decodedResponse);

// Handle 401 Unauthorized
if (isset($decodedResponse['code']) && $decodedResponse['code'] == 401) {
    http_response_code(401);
    header($_SERVER['PHP_SELF']);
    exit;
}

$final = ($decodedResponse['body']);
$final['industry'] = getIndustryCategoryHierarchy($final)['industry'];
$final['category'] = getIndustryCategoryHierarchy($final)['category'];
$final['subcategory'] = getIndustryCategoryHierarchy($final)['subcategory'];
$final['subsubcategory'] = getIndustryCategoryHierarchy($final)['subsubcategory'];

function getIndustryCategoryHierarchy($category) {
    $hierarchy = [
        'industry'        => null,
        'category'        => null,
        'subcategory'     => null,
        'subsubcategory'  => null
    ];

    $chain = [];
    $current = $category;

    // Traverse upwards until no parent
    while ($current && is_array($current)) {
        $chain[] = [
            'id'   => $current['id'] ?? null,
            'name' => $current['name'] ?? null
        ];
        $current = $current['parentCategory'] ?? null;
    }

    // Reverse so root comes first
    $chain = array_reverse($chain);

    // Map to hierarchy (root = industry)
    if (isset($chain[0])) $hierarchy['industry']       = $chain[0]; // Aviation
    if (isset($chain[1])) $hierarchy['category']       = $chain[1]; // Transportation
    if (isset($chain[2])) $hierarchy['subcategory']    = $chain[2]; // Air Travel
    if (isset($chain[3])) $hierarchy['subsubcategory'] = $chain[3]; // Charter Flights

    return $hierarchy;
}

// Breadcrumbs
$breadcrumb = [
    ['name' => 'Lists', 'url' => ''],
    ['name' => 'Master', 'url' => 'master.php'],
    ['name' => $final['name'], 'url' => ''],
];

$currentCategory = $final;
$breadcrumbCat = [];

while ($currentCategory) {
    array_unshift($breadcrumbCat, $currentCategory['name']);
    $currentCategory = $currentCategory['parentCategory'];
}

if (isset($_POST['cateSbt'])) {

    $data = $_POST;

    // Get category information
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
        $response = sendCurlRequest(BASE_URL.'/addMasterData', 'POST', $apiDataU, [], true);
        $decodedResponse = json_decode($response, true);
        //dump($decodedResponse);
        if($decodedResponse['success']){
            setcookie('successMsg', 'New Category Created Successfully', time() + 5, "/");
            echo "<script>window.location.href = 'master.php'</script>";
        }else{
            setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
            echo "<script>window.location.href = 'edit-categories.php?id=".base64_encode($id)."'</script>";
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
    $show_in_filter = cleanInputs($_POST['show_in_filter']);

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
        'show_in_filter' => $show_in_filter
    ];

    $response = sendCurlRequest(BASE_URL.'/admin-master-cat-sub-add?id='.$catId, 'POST', $apiData, [], true);
    $decodedResponse = json_decode($response, true);
    //dump($decodedResponse);
    if($decodedResponse['success']){
        setcookie('successMsg', 'Category Updated Successfully', time() + 5, "/");
        echo "<script>window.location.href = 'master.php'</script>";
    }else{
        setcookie('errorMsg', $decodedResponse['message'], time() + 5, "/");
        echo "<script>window.location.href = 'edit-categories.php?id=".base64_encode($id)."'</script>";
    }
}

$title = $final['name']. ' - Category';
include('pages/masterSubCat/edit.html');