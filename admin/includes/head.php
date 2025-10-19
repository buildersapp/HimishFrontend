<?php
$current_page = basename($_SERVER['PHP_SELF']);
$userRole = 0;
if(isset($_SESSION['hm_auth_data'])){
    $file = __DIR__ . '/utils/helpers.php';

    if (is_readable($file)) {
        require_once $file;
    }
    $userSession = $_SESSION['hm_auth_data'];
    $userRole = $userSession['account_type'];
    $responseP = sendCurlRequest(BASE_URL.'/get-profile', 'GET', ['user_id' => $_SESSION['hm_auth_data']['id']]);
    $decodedResponseP = json_decode($responseP, true);
    $myProfile = $decodedResponseP['body'];
}

if($userRole == 3 && $current_page !== 'sp-dashboard.php'){
    header('Location: 500.php');
} else if ($userRole !== 3 && $current_page == 'sp-dashboard.php'){
    header('Location: 500.php');
}
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | Himish Admin</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Himish is a platform where users can post feeds, view nearby businesses, claim their business listings, and connect with other companies and users. Users can also post buy/sell/share listings and discover nearby deals and products.">
    <meta name="keywords" content="Himish, business connections, local businesses, buy and sell, nearby deals, claim business, product discovery, local feed, community connections">
    <meta name="author" content="Himish">
    <meta name="robots" content="index, follow">
    <meta name="language" content="en">
    
    <!-- Open Graph Meta Tags (For Social Media Sharing) -->
    <meta property="og:title" content="<?= $title ?>" />
    <meta property="og:description" content="Himish is a platform where users can post feeds, claim businesses, create connections with companies and other users, and post buy/sell/share listings to find nearby deals." />
    <meta property="og:image" content="https://yourdomain.com/path-to-your-image.jpg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta property="og:url" content="<?= $_SERVER['REQUEST_URI'] ?>" />
    <meta property="og:type" content="website" />

    <!-- Additional Open Graph Meta Tags for Facebook -->
    <meta property="og:image:secure_url" content="assets/img/logo.png" />
    <meta property="og:image:alt" content="Himish" /> <!-- Alt text for the image -->

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title ?>">
    <meta name="twitter:description" content="Himish is a platform where users can post feeds, claim businesses, create connections with companies and other users, and post buy/sell/share listings to find nearby deals.">
    <meta name="twitter:image" content="https://yourdomain.com/path-to-your-image.jpg">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/plugins/parsley/parsley.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/plugins/cropper/pixelarity.css">
    <link rel="stylesheet" href="assets/plugins/toaster/css/toastr.min.css?v1.0" />
    <link href="assets/plugins/holdon/css/HoldOn.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" />

    <!-- Favicon -->
    <link rel="icon" href="assets/img/fav-icon.png" type="image/x-icon" />
    <link rel="icon" href="assets/img/fav-icon.png" type="image/png" />

    <!-- Custom CSS -->
    
    <link rel="stylesheet" href="assets/css/custom.css?v=<?= time() ?>" />
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>" />
    <link rel="stylesheet" href="assets/css/admin-responsive.css?v=<?= time() ?>" />

    <!-- Load jQuery -->
    <script type="application/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js?v=<?= time() ?>"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA8-xD4gQvyPqth_tvkgSuKwf7-p0cmSvc&libraries=geometry,places"></script>
    <link href="assets/css/select2.css" rel="stylesheet" />

    <!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

</head>