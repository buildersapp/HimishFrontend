<?php
// Set no-cache headers
if (isset($_SESSION['hm_wb_timezone']) && !empty($_SESSION['hm_wb_timezone'])) {
    date_default_timezone_set($_SESSION['hm_wb_timezone']);
} else {
    date_default_timezone_set('UTC');
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
include_once('includes/web-helpers.php');
// Always try auto-login from cookie
optionalLogin();

$userCompanies = [];
$connectToExisting = 0;

// get user details
if(isset($_SESSION['hm_wb_auth_data']['id'])){
    $query_data ='?user_id='.$_SESSION['hm_wb_auth_data']['id'].'&page=1&limit=1';
    $response = sendCurlRequest(BASE_URL.'/get-profile'.$query_data, 'GET', []);
    $decodedResponse = json_decode($response, true);
    if($decodedResponse['success'] == false){
        unset($_SESSION['hm_wb_auth_data']);
        unset($_SESSION['hm_wb_logged_in']);
        unset($_SESSION['hm_wb_timezone']);
        setcookie('pz_wb_user_auth', '', time() - 3600, "/", "", isset($_SERVER['HTTPS']), true);
        setcookie('wb_errorMsg', $decodedResponse['message'], time() + 2, "/");
        echo "<script>window.location.href = 'login.php'</script>";
        exit;
    }
    $userDetails = $decodedResponse['body'];

    $_SESSION['currency']['code']       = $userDetails['country']['currency_code'] ?? 'USD';
    $_SESSION['currency']['multiplier'] = $userDetails['country']['currency_multiplier'] ?? 1;
    $_SESSION['currency']['symbol'] = $userDetails['country']['currency_symbol'] ?? '';

    //dump($decodedResponse);

    // user companies
    $responseUC = sendCurlRequest(BASE_URL.'/get-company', 'GET', ['user_id' => $userDetails['id']]);
    $decodedResponseUC = json_decode($responseUC, true);
    $userCompanies = $decodedResponseUC['body'];

    $connectToExisting = count($userCompanies) == 0 ? 1 : 0;

    if($connectToExisting){
        $response = sendCurlRequest(BASE_URL.'/get-company', 'GET', ['page' => 1, 'limit' => 1000]);
        $decodedResponse = json_decode($response, true);
        $userCompanies = $decodedResponse['body'];
    }
}else{
    $userDetails = [];
    $gu_country_code = getCountryCodeFromIP();
    $responseCurrencyMulti = sendCurlRequest(BASE_URL.'/countries/currency-multiplier?country_code='.$gu_country_code, 'GET', []);
    $responseCurrencyMultiDec = json_decode($responseCurrencyMulti, true);
    if($responseCurrencyMultiDec && $responseCurrencyMultiDec['success']){
        $currencyMulti = $responseCurrencyMultiDec['body'];
        $currencyMultiplier_x = !empty($currencyMulti['currency_multiplier']) ? $currencyMulti['currency_multiplier'] : 1;
        $currencyCode_x = !empty($currencyMulti['currency_code']) ? $currencyMulti['currency_code'] : 'USD';
        $currencySymbol_x = !empty($currencyMulti['currency_symbol']) ? $currencyMulti['currency_symbol'] : '';
        $_SESSION['currency']['multiplier'] = $currencyMultiplier_x ?? 1;
        $_SESSION['currency']['code']       = $currencyCode_x ?? 'USD';
        $_SESSION['currency']['symbol']     = $currencySymbol_x ?? '';
    }
}

// get web ads
$webAds = [];
$responseWebAds = sendCurlRequest(BASE_URL.'/get-web-ads?page=1&limit=1000', 'GET', []);
$decodedResponseWebAds = json_decode($responseWebAds, true);
if($decodedResponseWebAds['success']){
    $webAds = $decodedResponseWebAds['body'];
}

$left_ads = array_values(array_filter($webAds, function($mem) {
    return $mem['position'] == 'left';
}));

$top_right_ads = array_values(array_filter($webAds, function($mem) {
    return $mem['position'] == 'top-right';
}));

$middle_right_ads = array_values(array_filter($webAds, function($mem) {
    return $mem['position'] == 'middle-right';
}));

$bottom_right_ads = array_values(array_filter($webAds, function($mem) {
    return $mem['position'] == 'bottom-right';
}));

?>

<script>
<?php if($userDetails){ ?>
    localStorage.setItem('userLatitude', "<?= $userDetails['latitude'] ?>");
    localStorage.setItem('userLongitude', "<?= $userDetails['longitude'] ?>");
    localStorage.setItem('userCity', "<?= $userDetails['city'] ?>");
    localStorage.setItem('userState', "<?= $userDetails['state'] ?>");
    localStorage.setItem('userAddress', "<?= $userDetails['location'] ?>");
    localStorage.setItem('userCountryCode', "<?= $userDetails['country_code'] ?>");
<?php } ?>
</script>

<!-- header start  -->
<header class="feed-header" id="nav-bar">
    <input type="hidden" id="guestLogin" value="<?= (@$userDetails) ? 0 : 1 ?>" />
    <div class="destop-nav d-none d-lg-flex justify-content-between align-items-center">
        <div class="header-left">
            <div class="logo">
                <a href="home.php"><img src="assets/images/logo-light-org.png" class="img-fluid" alt="Logo" /></a>
            </div>
            <div class="ms-lg-4 ms-2">
                <img src="assets/images/pin.png" class="img-fluid" alt="Menu" />
                <?php if(empty(@$userDetails['location'])){ ?>
                    <a href="change-location.php" class="text-white">
                        <span>Change Location...</span>
                    </a>
                <?php }else{ ?>
                    <a href="change-location.php" class="text-white">
                    <span>
                        <?= (!empty(@$userDetails['city']) || !empty(@$userDetails['state']))
                            ? (!empty(@$userDetails['city']) ? @$userDetails['city'] . ', ' . @$userDetails['state'] : @$userDetails['state'])
                            : 'Change Location' ?>
                    </span>
                    </a>
                <?php } ?>
            </div>
        </div>
        <div class="header-right">
            <!-- <div class="search">
                <div action="" class="navSearch" style="position: relative;">
                    <input type="text" class="form-control global-search" placeholder="Search..." data-radius="<?= @$userDetails['radius'] ?>" value="<?= @$_GET['search'] ?>" />

                    
                    <button type="button" class="search-icon <?= (isset($_GET['search'])) ? 'clear-global-search' : 'clear-withoutSrc' ?> mx-4" style="display: <?= (isset($_GET['search'])) ? 'block' : 'none' ?>;">
                        <i class="fa fa-times"></i>
                    </button>
                    <button type="button" class="search-icon manual-global-search">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
                <div class="global-search-dropdown" style="display: none;"></div>
            </div> -->
            <div>
                <button class="nav-btn set-btn-transparent-navigation <?php echo ($current_page == 'home.php') ? 'active-btn' : 'dashboard-btn'; ?>">
                    <a href="home.php"><img src="assets/images/<?php echo ($current_page == 'home.php') ? 'home-nav' : 'house-nav'; ?>.png" class="img-fluid" alt="Home" /></a>
                </button>
            </div>
            <div>
                <button class="nav-btn set-btn-transparent-navigation <?php echo ($current_page == 'companies.php') ? 'active-btn' : 'dashboard-btn'; ?>">
                    <a href="companies.php"><img src="assets/images/<?php echo ($current_page == 'companies.php') ? 'visualization-nav' : 'menu-nav'; ?>.png" class="img-fluid" alt="Dashboard" /></a>
                </button>
            </div>
            <?php if(@$userDetails){ ?>
            <div>
            <button class="nav-btn notification-btn position-relative set-btn-transparent-navigation" type="button" data-bs-toggle="offcanvas" data-bs-target="#notificationOffcanvas" aria-controls="notificationOffcanvas">
                <img src="assets/images/notification-white.png" class="img-fluid" alt="notification" />
                <span id="noti-badge-container">
                    <?php if($userDetails['noti_count']>0){ ?>
                    <span class="notification-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $userDetails['noti_count'] ?>
                        <span class="visually-hidden">unread messages</span>
                    </span>
                    <?php } ?>
                </span>
            </button>
            </div>
            <?php } ?>
            <!-- nav profile img  -->
            <div class="nav-profile position-relative">
                <button class="profile-btn" id="profileOptions" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php
                        $imgData = "";
                        if(empty(@$userDetails['image'])){
                            $imgData = '<img class="img-placeholder profile-imimg-nh rounded-circle" src="'.generateBase64Image(@$userDetails['name'] ?? 'G U').'" alt="" width="20" />';
                        }else{
                            $imgData = '<img src="'. MEDIA_BASE_URL .''.@$userDetails['image'].'" alt="" class="profile-imimg-nh rounded-circle" />';
                        }
                        echo $imgData;
                    ?>
                </button>
                <ul class="dropdown-menu thin-scrollbar" aria-labelledby="profileOptions">
                    <?php if(@$userDetails){ ?>
                    <!-- item 0 -->
                    <li>
                        <a class="dropdown-item profile-single d-flex gap-3 align-items-center set-padding-item" href="edit-profile.php">
                            <div class="set-user-round">
                                <img src="assets/images/user.png" alt="user">
                            </div>
                            <p class="f-14-gb mb-0 set-flex-grow">Edit Profile</p>
                            <span class="profile-single-right">
                                <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                            </span>
                        </a>
                    </li>
                    <!-- item 1 -->
                    <li>
                        <a class="dropdown-item profile-single d-flex gap-3 align-items-center set-padding-item" href="companies-profile.php?id=<?= base64_encode(@$userDetails['id']) ?>">
                            <div class="set-user-round">
                                <img src="assets/images/bank.png" alt="company porfile">
                            </div>
                            <p class="f-14-gb mb-0 set-flex-grow">Company Profile</p>    
                        
                            <span class="profile-single-right">
                                <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                            </span>
                        </a>
                    </li>
                    <!-- item 2 -->
                    <li>
                        <a class="dropdown-item profile-single openAdsFromMenu d-flex gap-3 align-items-center set-padding-item" data-bs-toggle="offcanvas"
                            href="#adsOffcanvas"
                            role="button"
                            aria-controls="adsOffcanvasLabel" id="openAdsCanvas">
                            <div class="set-user-round">
                                <img src="assets/images/ad.png" alt="ads">
                            </div>
                            <p class="f-14-gb mb-0 set-flex-grow">My Ads</p>
                            <span class="profile-single-right">
                                <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                            </span>
                        </a>
                    </li>
                    <!-- item 3 -->
                    <li>
                        <a class="dropdown-item profile-single d-flex gap-3 align-items-center set-padding-item" href="user-details.php?id=<?= base64_encode(@$userDetails['id']) ?>&type=0">
                            <div class="set-user-round">
                                <img src="assets/img/camera.png" alt="" />
                            </div>
                            <p class="f-14-gb mb-0 set-flex-grow">My Posts</p>
                            <span class="profile-single-right">
                                <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                            </span>
                        </a>
                    </li>
                    <!-- item 4 -->
                    <li>
                        <a class="dropdown-item profile-single d-flex gap-3 align-items-center set-padding-item" href="communities.php">
                            <div class="set-user-round">
                                <img src="assets/img/i3.png" alt="" />
                            </div>
                            <p class="f-14-gb mb-0 set-flex-grow">My Communities</p>
                            <span class="profile-single-right">
                                <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                            </span>
                        </a>
                    </li>
                    <!-- item 5 -->
                    <li>
                        <a class="dropdown-item profile-single d-flex gap-3 align-items-center set-padding-item" href="inbox.php">
                            <div class="set-user-round position-relative">
                                <img src="assets/img/i5.png" alt="" />
                                <?php if($userDetails['unread_chat_count']>0){ ?>
                                <span class="set-overlay-top-count-badge"><?= $userDetails['unread_chat_count'] ?></span>
                                <?php } ?>
                            </div>
                            <p class="f-14-gb mb-0 set-flex-grow">Chats </p>
                            <span class="profile-single-right">
                                <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                            </span>
                        </a>
                    </li>
                    <!-- item 6 -->
                    <li>
                        <a class="dropdown-item profile-single d-flex gap-3 align-items-center set-padding-item" href="#">
                            <div class="set-user-round">
                                <img src="assets/img/i6.png" alt="" />
                            </div>
                            <p class="f-14-gb mb-0 set-flex-grow">Wallet <span class="text-blue">$<?= formatNumberUS(@$userDetails['wallet']) ?></span></p>
                            <span class="profile-single-right">
                                <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                            </span>
                        </a>
                    </li>
                    <!-- item 7 -->
                    <li>
                        <a class="dropdown-item profile-single share-fn d-flex gap-3 align-items-center set-padding-item" data-id="<?php echo base64_encode(@$userDetails['id']); ?>" data-title="with your friends and invite them to join!" data-type="inviteUser" href="javascript:void(0)">
                            <div class="set-user-round">
                                <img src="assets/img/i7.png" alt="" />
                            </div>
                            <p class="f-14-gb mb-0 set-flex-grow">Invite Friends</p>
                            <span class="profile-single-right">
                                <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                            </span>
                        </a>
                    </li>
                    <?php } ?>
                    <!-- item 8 -->
                    <li>
                        <a class="dropdown-item profile-single d-flex gap-3 align-items-center set-padding-item" href="faqs.php">
                            <div class="set-user-round">
                                <img src="assets/img/i8.png" alt="" />
                            </div>
                            <p class="f-14-gb mb-0 set-flex-grow">Faqs</p>
                            <span class="profile-single-right">
                                <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                            </span>
                        </a>
                    </li>
                    <!-- item 9 -->
                    <li>
                        <a class="dropdown-item profile-single d-flex gap-3 align-items-center set-padding-item" href="privacy.php">
                            <div class="set-user-round">
                                <img src="assets/img/i9.png" alt="" />
                            </div>
                            <p class="f-14-gb mb-0 set-flex-grow">Privacy Policy</p>
                            <span class="profile-single-right">
                                <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                            </span>
                        </a>
                    </li>
                    <!-- item 10 -->
                    <li>
                        <a class="dropdown-item profile-single d-flex gap-3 align-items-center set-padding-item" href="terms.php">
                            <div class="set-user-round">
                                <img src="assets/img/i10.png" alt="" />
                            </div>
                            <p class="f-14-gb mb-0 set-flex-grow">Terms & Condition</p>
                            <span class="profile-single-right">
                                <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                            </span>
                        </a>
                    </li>
                    <!-- item 11 -->
                    <li>
                        <?php if (!isset($userDetails) || empty($userDetails)): ?>
                            <!-- Guest user: show Claim & Remove Post -->
                        <a class="dropdown-item profile-single d-flex gap-3 align-items-center set-padding-item"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#reportClaimPostOffcanvas"
                            aria-controls="reportClaimPostOffcanvas"
                            onclick="$('#report_claim_post_id').val(0); $('#report_claim_post_type').val(3);$('#titlereportClaimPost').text('Contact Us'); $('#descreportClaimPost').text('Please enter your details, and our support team will get in touch with you shortly.');">
                                <div class="set-user-round">
                                    <img src="assets/img/i11.png" alt="" />
                                </div>
                                <p class="f-14-gb mb-0 set-flex-grow">Contact Us</p>
                                <span class="profile-single-right">
                                    <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                                </span>
                        </a>
                        <?php else: ?>
                            <!-- Logged-in user: show Contact Us -->
                            <a class="dropdown-item profile-single d-flex gap-3 align-items-center set-padding-item" href="single-chat.php?id=<?= base64_encode(12) ?>&type=<?= base64_encode(0) ?>">
                                <div class="set-user-round">
                                    <img src="assets/img/i11.png" alt="" />
                                </div>
                                <p class="f-14-gb mb-0 set-flex-grow">Contact Us</p>
                                <span class="profile-single-right">
                                    <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                                </span>
                            </a>
                        <?php endif; ?>
                    </li>
                    <?php if(@$userDetails){ ?>
                    <!-- item 12.0 -->
                    <li>
                        <a class="dropdown-item profile-single d-flex gap-3 align-items-center set-padding-item" href="change-password.php">
                            <div class="set-user-round">
                                <i class="fa fa-lock"></i>
                            </div>
                            <p class="f-14-gb mb-0 set-flex-grow">Change Password</p>
                            <span class="profile-single-right">
                                <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                            </span>
                        </a>
                    </li>
                    <!-- item 12 -->
                    <li>
                        <a class="dropdown-item profile-single d-flex gap-3 align-items-center set-padding-item" href="javascript:void(0)" id="logoutUser">
                            <div class="set-user-round">
                                <img src="assets/img/i13.png" alt="" />
                            </div>
                            <p class="f-14-gb mb-0 set-flex-grow">Logout</p>
                            <span class="profile-single-right">
                                <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                            </span>
                        </a>
                    </li>
                    <!-- item 13 -->
                    <li>
                        <a class="dropdown-item profile-single d-flex gap-3 align-items-center set-padding-item"  href="javascript:void(0)" id="deleteAccountFn">
                            <div class="set-user-round">
                                <img src="assets/img/i14.png" alt="" />
                            </div>
                            <p class="f-14-gb mb-0 set-flex-grow">Delete Account</p>
                            <span class="profile-single-right">
                                <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                            </span>
                        </a>
                    </li>
                    <?php }else{ ?>
                        <li>
                            <a class="dropdown-item profile-single d-flex gap-3 align-items-center set-padding-item" href="login.php">
                                <div class="set-user-round">
                                    <img src="assets/img/i13.png" alt="" />
                                </div>
                                <p class="f-14-gb mb-0 set-flex-grow">Login</p>
                                <span class="profile-single-right">
                                    <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                                </span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="mobile-nav d-flex d-lg-none justify-content-between align-items-center">
        <a href="home.php" class="d-block mobile-logo">
            <img src="assets/images/fav-icon.png" alt="logo" class="img-fluid" />
        </a>
        <a href="home.php" class="d-block mobile-logo2">
            <img src="assets/images/logo-light-name.png" alt="logo" class="img-fluid" />
        </a>
        <div class="dropdown set-dropdown-mobile-all">
            <button class="d-block mobile-search dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="assets/img/search2.png" alt="search" class="img-fluid" />
            </button>
            <ul class="dropdown-menu">
                <li>
                    <div class="search">
                        <div action="" class="navSearch" style="position: relative;">
                            <input type="text" class="form-control global-search" placeholder="Search..." data-radius="<?= @$userDetails['radius'] ?>" value="<?= @$_GET['search'] ?>" />

                            <!-- Clear icon inside the form -->
                            <button type="button" class="search-icon <?= (isset($_GET['search'])) ? 'clear-global-search' : 'clear-withoutSrc' ?> mx-4" style="display: <?= (isset($_GET['search'])) ? 'block' : 'none' ?>;">
                                <i class="fa fa-times"></i>
                            </button>
                            <button type="button" class="search-icon manual-global-search">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                        <div class="global-search-dropdown" style="display: none;"></div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>
<!-- header end  -->

<!-- OFFCANVAS FOR MULTIPLE COMPANIES  -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="multipleCompaniesOffcanvas"
        aria-labelledby="notificationOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 id="notificationOffcanvasLabel" class="fw-bold">My Companies <span>2</span></h5>
            <button type="button" class="modal-close" data-bs-dismiss="offcanvas" aria-label="Close">
                <img src="assets/img/cross.png" alt="">
            </button>
        </div>
        <div class="notification-bar"></div>
        <div class="offcanvas-body">
            <div class="company-listings">
                <!-- Single Company Item -->
                <div class="company-item">
                                    <!-- Company Image Carousel -->
                                    <div class="company-carousel">
                                        <div id="companyCarouselIndicators" class="carousel slide" data-bs-ride="carousel">
                                            <!-- Carousel Indicators -->
                                            <div class="carousel-indicators">
                                                <button type="button" data-bs-target="#companyCarouselIndicators" data-bs-slide-to="0"
                                                    class="active" aria-current="true" aria-label="Slide 1"></button>
                                                <button type="button" data-bs-target="#companyCarouselIndicators" data-bs-slide-to="1"
                                                    aria-label="Slide 2"></button>
                                                <button type="button" data-bs-target="#companyCarouselIndicators" data-bs-slide-to="2"
                                                    aria-label="Slide 3"></button>
                                            </div>
                                            <!-- Carousel Inner Content -->
                                            <div class="carousel-inner">
                                                <div class="carousel-item active">
                                                    <img src="assets/img/company1.png" class="img-fluid" alt="Company Image 1">
                                                </div>
                                                <div class="carousel-item">
                                                    <img src="assets/img/company1.png" class="img-fluid" alt="Company Image 2">
                                                </div>
                                                <div class="carousel-item">
                                                    <img src="assets/img/company1.png" class="img-fluid" alt="Company Image 3">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            
                                    <!-- Company Details -->
                                    <div class="company-details">
                                        <!-- Company Logo -->
                                        <div class="company-logo">
                                            <img src="assets/img/podcast.png" alt="Company Logo" class="img-fluid">
                                        </div>
                                        <!-- Company Info Section -->
                                        <div class="company-info">
                                            <!-- Company Title -->                                           
                                            <h3 class="company-title">
                                                <a href="">Sharp Security Systems</a> <a href="#"><img src="assets/img/chat1.png" alt=""></a>
                                            </h3>
                                                                                     
                                            <!-- Company Rating -->
                                            <div class="company-rating">
                                                <p>0.0</p>
                                                <img src="assets/img/star.png" alt="Rating Stars">
                                                
                                            </div>
                                        
                                            <!-- Company Location -->
                                            <div class="company-location">
                                                <img src="assets/img/sale-loc.png" alt="Location Icon">
                                                <span class="loc-name">Brooklyn, NY</span>
                                                <span class="other-loc" data-bs-toggle="dropdown" aria-expanded="true">+2 Locations</span>
                                                <ul class="dropdown-menu" aria-labelledby="postActionsFilter" data-popper-placement="bottom-start">
                                                    <li>New York, NY</li>
                                                    <li>Long Branch, NJ</li>
                                                </ul>

                                            </div>
                                        
                                            <!-- Company Category -->
                                            <div class="company-category">
                                                <h4>Contractor</h4>
                                                <p>Paint & Finishing</p>
                                                <img src="assets/img/arr-com.png" alt="">
                                            </div>
                                        
                                            <!-- Company Stats -->
                                            <div class="company-stats">
                                                <div class="company-posts">
                                                    <p>Posts</p>
                                                    <h3>65</h3> <!-- Switched: Number is now h3, title is now p -->
                                                </div>
                                                <div class="company-posts">
                                                    <p>Connections</p>
                                                    <h3>65</h3> <!-- Switched -->
                                                </div>
                                                <div class="company-posts">
                                                    <p>Showcase</p>
                                                    <h3>32</h3> <!-- Switched -->
                                                </div>
                                                <div class="company-posts">
                                                    <p>Recommends</p>
                                                    <h3>03</h3> <!-- Switched -->
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Company Share Icons -->
                                        <div class="company-share-icons">
                                            <!-- Like Button -->
                                            <button class="share-item share-like">
                                                <img src="assets/img/like.png" alt="Like Icon">
                                                <span class="like-count">874</span>
                                            </button>
                                            <!-- Share Button -->
                                            <button class="share-item share-button">
                                                <img src="assets/img/share.png" alt="Share Icon">
                                            </button>
                                            <!-- Bookmark Button -->
                                            <button class="share-item share-bookmark">
                                                <img src="assets/img/bookmarkBlack.png" alt="Bookmark Icon">
                                            </button>
                                        </div>


                                    </div>
                                    <!-- ribbone  -->
                                     <p class="company-ribbon">
                                        Wholesale
                                     </p>
                                </div>
                </div>
                <!-- Single Company Item -->
                <div class="company-item">
                                    <!-- Company Image Carousel -->
                                    <div class="company-carousel">
                                        <div id="companyCarouselIndicators" class="carousel slide" data-bs-ride="carousel">
                                            <!-- Carousel Indicators -->
                                            <div class="carousel-indicators">
                                                <button type="button" data-bs-target="#companyCarouselIndicators" data-bs-slide-to="0"
                                                    class="active" aria-current="true" aria-label="Slide 1"></button>
                                                <button type="button" data-bs-target="#companyCarouselIndicators" data-bs-slide-to="1"
                                                    aria-label="Slide 2"></button>
                                                <button type="button" data-bs-target="#companyCarouselIndicators" data-bs-slide-to="2"
                                                    aria-label="Slide 3"></button>
                                            </div>
                                            <!-- Carousel Inner Content -->
                                            <div class="carousel-inner">
                                                <div class="carousel-item active">
                                                    <img src="assets/img/company1.png" class="img-fluid" alt="Company Image 1">
                                                </div>
                                                <div class="carousel-item">
                                                    <img src="assets/img/company1.png" class="img-fluid" alt="Company Image 2">
                                                </div>
                                                <div class="carousel-item">
                                                    <img src="assets/img/company1.png" class="img-fluid" alt="Company Image 3">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            
                                    <!-- Company Details -->
                                    <div class="company-details">
                                        <!-- Company Logo -->
                                        <div class="company-logo">
                                            <img src="assets/img/podcast.png" alt="Company Logo" class="img-fluid">
                                        </div>
                                        <!-- Company Info Section -->
                                        <div class="company-info">
                                            <!-- Company Title -->                                           
                                            <h3 class="company-title">
                                                <a href="">Sharp Security Systems</a> <a href="#"><img src="assets/img/chat1.png" alt=""></a>
                                            </h3>
                                                                                     
                                            <!-- Company Rating -->
                                            <div class="company-rating">
                                                <p>0.0</p>
                                                <img src="assets/img/star.png" alt="Rating Stars">
                                                
                                            </div>
                                        
                                            <!-- Company Location -->
                                            <div class="company-location">
                                                <img src="assets/img/sale-loc.png" alt="Location Icon">
                                                <span class="loc-name">Brooklyn, NY</span>
                                                <span class="other-loc" data-bs-toggle="dropdown" aria-expanded="true">+2 Locations</span>
                                                <ul class="dropdown-menu" aria-labelledby="postActionsFilter" data-popper-placement="bottom-start">
                                                    <li>New York, NY</li>
                                                    <li>Long Branch, NJ</li>
                                                </ul>

                                            </div>
                                        
                                            <!-- Company Category -->
                                            <div class="company-category">
                                                <h4>Contractor</h4>
                                                <p>Paint & Finishing</p>
                                                <img src="assets/img/arr-com.png" alt="">
                                            </div>
                                        
                                            <!-- Company Stats -->
                                            <div class="company-stats">
                                                <div class="company-posts">
                                                    <p>Posts</p>
                                                    <h3>65</h3> <!-- Switched: Number is now h3, title is now p -->
                                                </div>
                                                <div class="company-posts">
                                                    <p>Connections</p>
                                                    <h3>65</h3> <!-- Switched -->
                                                </div>
                                                <div class="company-posts">
                                                    <p>Showcase</p>
                                                    <h3>32</h3> <!-- Switched -->
                                                </div>
                                                <div class="company-posts">
                                                    <p>Recommends</p>
                                                    <h3>03</h3> <!-- Switched -->
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Company Share Icons -->
                                        <div class="company-share-icons">
                                            <!-- Like Button -->
                                            <button class="share-item share-like">
                                                <img src="assets/img/like.png" alt="Like Icon">
                                                <span class="like-count">874</span>
                                            </button>
                                            <!-- Share Button -->
                                            <button class="share-item share-button">
                                                <img src="assets/img/share.png" alt="Share Icon">
                                            </button>
                                            <!-- Bookmark Button -->
                                            <button class="share-item share-bookmark">
                                                <img src="assets/img/bookmarkBlack.png" alt="Bookmark Icon">
                                            </button>
                                        </div>


                                    </div>
                                    <!-- ribbone  -->
                                     <p class="company-ribbon">
                                        Wholesale
                                     </p>
                                </div>
                </div>
        </div>
    </div>


<!-- offcanvas notification  -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="notificationOffcanvas" aria-labelledby="notificationOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 id="notificationOffcanvasLabel">
            Notifications
            <?php if(@$userDetails['noti_count']>0){ ?>
            <span>
                <?= @$userDetails['noti_count'] ?>
                New
            </span>
            <?php } ?>
        </h5>
        <button type="button" class="modal-close" data-bs-dismiss="offcanvas" aria-label="Close">
            <img src="assets/img/cross.png" alt="" />
        </button>
    </div>
    <div class="notification-bar"></div>
    <div class="offcanvas-body" id="notifications-container-scroll">
        <div class="notification-lists" id="notifications-container"></div>
    </div>
</div>

<!-- offcanvas create post  -->
<div class="offcanvas offcanvas-end " tabindex="-1" id="createPostDetailOffcanvas" aria-labelledby="createPostDetailOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 id="notificationOffcanvasLabel" class="fw-bold">
            Post As
        </h5>
        <button type="button" class="modal-close" data-bs-dismiss="offcanvas" aria-label="Close">
            <img src="assets/img/cross.png" alt="" />
        </button>
    </div>
    <div class="notification-bar"></div>
    <div class="offcanvas-body" id="notifications-container-scroll">
        <form class="py-4" action="" method="">
            <div class="form-group">
                <div id="">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="set-checkbox-wrapper">
                                <input type="checkbox" name="visibility" value="named">
                                <div class="set-checkbox-post">
                                    <strong class="d-inline-block mt-2">Oscar Novelli</strong>
                                    <p>Your name will be shown with the post, owner may reward you.</p>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="set-checkbox-wrapper">
                                <input type="checkbox" name="visibility" value="anonymous">
                                <div class="set-checkbox-post">
                                    <strong class="d-inline-block mt-2">Anonymous</strong>
                                    <p>Your name will not be shown to the public.</p>
                                    <p>Posterzs will however see your info.</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="notification-bar mt-2"></div>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="set-check-text d-flex gap-3 mt-4">
                        <input type="checkbox" class="" name="agree" >
                        <p class="m-0">I understand Himish will notify the business via one-time SMS or E-mail</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <button class="register-submit" type="button" id="">
                    <span class="saveBtnTxt">Create & Post </span>
                    <span class="saveBtnImg"><img src="assets/img/forward.png" class="img-fluid w-100" alt="" /></span>
                </button>
            </div>
        </form>
    </div>
</div>


<!-- offcanvas ads  -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="adsOffcanvas" aria-labelledby="adsOffcanvasLabel">
    <div class="offcanvas-header d-flex justify-content-between align-items-center">
        <h5 id="adsOffcanvasLabel">My Ads</h5>
        
        <div class="d-flex align-items-center gap-2">
            <!-- Plus Icon Button -->
            <a href="create-ad.php" class="text-decoration-none">
                <button type="button" class="modal-add btn p-0 border-0 bg-transparent" aria-label="Add">
                    <i class="fa fa-plus-circle" style="font-size: 30px; color: #1f448a;"></i>
                </button>
            </a>

            <!-- Close Button -->
            <button type="button" class="modal-close clsoe-cross btn p-0 border-0 bg-transparent" 
                    data-bs-dismiss="offcanvas" aria-label="Close">
                <img src="assets/img/iconamoon_close-light-two.svg" alt="Close" />
            </button>
        </div>
    </div>

    <div class="notification-bar"></div>
    <div class="offcanvas-body">
        <!-- tab button  -->
        <ul class="nav nav-area mb-3" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-linkk active" id="ad-running-pills-posts-tabk" data-bs-toggle="pill" data-bs-target="#pills-postsk" type="button" role="tab" aria-controls="pills-postsk" aria-selected="true">
                    Running Ads
                </button>
            </li>
            <li class="nav-item extra-nav" role="presentation">
                <button class="nav-linkk" id="ad-past-pills-listing-tabk" data-bs-toggle="pill" data-bs-target="#pills-listingk" type="button" role="tab" aria-controls="pills-listingk" aria-selected="false">
                    Past Ads
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-linkk" id="ad-others-pills-deals-tabk" data-bs-toggle="pill" data-bs-target="#pills-dealsk" type="button" role="tab" aria-controls="pills-dealsk" aria-selected="false">
                    Others
                </button>
            </li>
        </ul>
        <!-- tab content  -->
        <div class="tab-content" id="pills-tabContent">
            <!-- running tab content  -->
            <div id="ad-running-pills-postsk">

            </div>
        </div>
    </div>
</div>

<!-- offcanvas report  -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="reportPostOffcanvas" aria-labelledby="createComunityLabel">
    <div class="offcanvas-header reportPostOffcanvas-header">
        <div class="createComunityLabel">
            <h5>Report</h5>
            <p style="font-style: italic;">Why you want to report this?</p>
        </div>
        <button type="button" class="modal-close" data-bs-dismiss="offcanvas" aria-label="Close">
            <img src="assets/img/cross.png" alt="" />
        </button>
    </div>
    <div class="offcanvas-body addComunityOffcanvas-body">
        <div class="createComunityForm">
            <div class="notification-bar"></div>
            <form action="" data-parsley-validate>
                <input type="hidden" name="report_post_id" id="report_post_id" value="0" />
                <!-- pick an option -->
                <div class="description-input-wrapper">
                    <select class="form-input" name="type" id="report-type" required>
                        <option value="" disabled selected>Pick an Option</option>
                        <option value="spam">It's spam</option>
                        <option value="inappropriate">It's inappropriate</option>
                    </select>
                </div>

                <!-- Description -->
                <div class="description-input-wrapper">
                    <textarea name="message" id="report-message" placeholder="Please describe you reason." class="form-input" required></textarea>
                </div>

                <!-- Phone and Email (Initially Hidden) -->
                <div class="description-input-wrapper delete-post-extra" style="display: none;">
                    <input type="number" name="phone" autocomplete="off" id="report-phone" placeholder="Your Phone Number" class="form-input" value="<?= @$userDetails['phone'] ?>" />
                </div>
                <div class="description-input-wrapper delete-post-extra" style="display: none;">
                    <input type="email" name="email" autocomplete="off" id="report-email" placeholder="Your Email Address" class="form-input" value="<?= @$userDetails['email'] ?>" />
                </div>

                <!-- submit  -->
                <div class="mt-3">
                    <button class="register-submit" type="button" id="reportPostFn">
                        <span class="saveBtnTxt">Save</span>
                        <span class="saveBtnImg"><img src="assets/img/forward.png" class="img-fluid w-100" alt="" /></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- OFFCANVAS CLAIM & REPORT POST -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="reportClaimPostOffcanvas" aria-labelledby="createComunityLabel">
    <div class="offcanvas-header reportClaimPostOffcanvas-header">
        <div class="createComunityLabel">
            <h5 id="titlereportClaimPost">Claim and Remove Post</h5>
            <p id="descreportClaimPost" style="font-style: italic;">Why you want to report this?</p>
        </div>
        <button type="button" class="modal-close" data-bs-dismiss="offcanvas" aria-label="Close">
            <img src="assets/img/cross.png" alt="" />
        </button>
    </div>

    <div class="offcanvas-body addComunityOffcanvas-body">
        <div class="createComunityForm">
            <div class="notification-bar"></div>
            <form id="claimPostForm" data-parsley-validate>
                <input type="hidden" name="report_claim_post_id" id="report_claim_post_id" value="0" />
                <input type="hidden" name="report_claim_post_type" id="report_claim_post_type" value="0" />

                <!-- Preferred Communication -->
                <div class="description-input-wrapper">
                    <select class="form-input" id="preferred-communication" required>
                        <option value="" disabled selected>Select Preferred Communication</option>
                        <option value="email">Email</option>
                        <option value="phone">Phone Number</option>
                    </select>
                </div>

                <!-- Phone Field -->
                <div class="description-input-wrapper delete-post-extra phone-field" style="display: none;">
                    <input type="text" name="phone" autocomplete="off" id="report-claim-phone" placeholder="Your Phone Number" class="form-input" oninput="this.value = formatPhoneNumberUSRC(this.value);" />
                </div>

                <!-- Email Field -->
                <div class="description-input-wrapper delete-post-extra email-field" style="display: none;">
                    <input type="email" name="email" autocomplete="off" id="report-claim-email" placeholder="Your Email Address" class="form-input" />
                </div>

                <!-- Description -->
                <div class="description-input-wrapper">
                    <textarea name="message" id="report-claim-message" placeholder="Please describe your reason." class="form-input" required></textarea>
                </div>

                <!-- Submit -->
                <div class="mt-3">
                    <button class="register-submit" type="button" id="reportClaimPostFn">
                        <span class="saveBtnTxt">Submit</span>
                        <span class="saveBtnImg"><img src="assets/img/forward.png" class="img-fluid w-100" alt="" /></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- offcanvas post comments  -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="postCommentsOffcanvas" aria-labelledby="createComunityLabel">
    <div class="offcanvas-header postCommentsOffcanvas-header">
        <div class="createComunityLabel">
            <h5 id="pc_title">Comments</h5>
            <p style="font-style: italic;" id="pc_desc">Post</p>
        </div>
        <button type="button" class="modal-close" data-bs-dismiss="offcanvas" aria-label="Close">
            <img src="assets/img/cross.png" alt="" />
        </button>
    </div>
    <div class="offcanvas-body" id="pc-comments-container-scroll">
        <div id="previewContComm" class="card commentListPreview"></div>
        <div class="notification-lists" id="pc-comments-container"></div>
    </div>
    <!-- Fixed comment input at the bottom -->
    <div class="comment-input-container">
        <form action="" class="cmnt-form" id="postCommentForm">
            <input type="hidden" id="pc_post_id" value="0" />
            <input type="text" class="form-control" placeholder="Comment here..." name="comment" id="commentInput">
            <button type="submit" class="cmnt-submit">
                <img src="assets/img/carbon_send-filled.png" alt="">
            </button>
        </form>
    </div>
</div>

<!-- offcanvas ads comments  -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="adsCommentsOffcanvas" aria-labelledby="createComunityLabel">
    <div class="offcanvas-header adsCommentsOffcanvas-header">
        <div class="createComunityLabel">
            <h5 id="pc_title">Comments</h5>
            <p style="font-style: italic;" id="ac_desc">Post</p>
        </div>
        <button type="button" class="modal-close" data-bs-dismiss="offcanvas" aria-label="Close">
            <img src="assets/img/cross.png" alt="" />
        </button>
    </div>
    <div class="offcanvas-body" id="ac-comments-container-scroll">
        <div id="previewContCommAds" class="card commentListPreview"></div>
        <div class="notification-lists" id="ac-comments-container"></div>
    </div>
    <!-- Fixed comment input at the bottom -->
    <div class="comment-input-container">
        <form action="" class="cmnt-form" id="adsCommentForm">
            <input type="hidden" id="ac_post_id" value="0" />
            <input type="hidden" id="ac_post_type" value="0" />
            <input type="text" class="form-control" placeholder="Comment here..." name="comment" id="commentInputAds" >
            <button type="submit" class="cmnt-submit">
                <img src="assets/img/carbon_send-filled.png" alt="">
            </button>
        </form>
    </div>
</div>

<!-- offcanvas Claim  -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="createClaimOffcanvas" aria-labelledby="createComunityLabel">
    <div class="offcanvas-header createComunityoffcanvas-header">
        <div class="createComunityLabel">
            <h5>Do you own this business?</h5>
            <p style="font-style: italic;">Kindly, help us to verify you as owner.</p>
        </div>
        <button type="button" class="modal-close" data-bs-dismiss="offcanvas" aria-label="Close">
            <img src="assets/img/cross.png" alt="">
        </button>
    </div>
    <div class="offcanvas-body addComunityOffcanvas-body">
        <div class="createComunityForm">
            <div class="notification-bar"></div>
            <form action="" data-parsley-validate>
                <input type="hidden" name="type" id="ad_cc_type" value="0" />
                <input type="hidden" name="post_id" id="post_id" value="0" />
                <input type="hidden" name="company_id" id="company_id" value="0" />
                <div class="input-wrapper mb-3">
                    <input type="checkbox" name="show_username" id="show_username" />
                    <label for="title-input" class="input-icon">
                        Display Poster Name
                    </label>
                </div>
                <div class="input-wrapper">
                    <input type="email" name="email" id="email" placeholder="Enter your email address" id="title-input" class="form-input" required />
                    <label for="title-input" class="input-icon">
                        <img src="assets/img/comunity.png" alt="Title Icon" />
                    </label>
                    <label for="title-input" class="postdetails-form-bar"> </label>
                </div>
                <!-- submit  -->
                <div class="mt-3">
                    <button class="register-submit" type="button" id="claimPostFn">
                        <span class="saveBtnTxt">Submit</span>
                        <span class="saveBtnImg"><img src="assets/img/forward.png" class="img-fluid w-100" alt=""></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- offcanvas Connection  -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="connectionOffcanvas" aria-labelledby="connectionOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 id="connectionOffcanvasLabel">Connections</h5>
        <button type="button" class="modal-close" data-bs-dismiss="offcanvas" aria-label="Close">
            <img src="assets/img/cross.png" alt="">
        </button>
    </div>
    <div class="notification-bar"></div>
    <div class="offcanvas-body thin-scrollbar">
        <div class="connection-container">
            <!-- Tab Buttons -->
            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link connectxn active" id="all-connections-pills-tabk" data-bs-toggle="pill" data-bs-target="#pills-allConnection" type="button" role="tab" aria-controls="pills-allConnection" aria-selected="true">All (<?= @$userDetails['following'] + @$userDetails['followers'] ?>)</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link connectxn" id="in-connections-pills-tabk" data-bs-toggle="pill"
                        data-bs-target="#pills-ConnectionIn" type="button" role="tab"
                        aria-controls="pills-ConnectionIn" aria-selected="false" tabindex="-1">Connects In (<?= @$userDetails['followers'] ?>)</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link connectxn" id="out-connections-pills-tabk" data-bs-toggle="pill"
                        data-bs-target="#pills-connectionOut" type="button" role="tab"
                        aria-controls="pills-connectionOut" aria-selected="false" tabindex="-1">Connects Out (<?= @$userDetails['following'] ?>)</button>
                </li>
            </ul>
            <!-- Tab Content -->
            <div class="tab-content" id="pills-tabContent">
                <div id="my-connections-postsk">

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Share Modal  -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body" id="shareLoader">
                <div class="shareModal-header">
                    <h5>Share this <span id="shareName">post</span></h5>
                    <button class="shareModal-close" data-bs-dismiss="modal" aria-label="Close">
                        <img src="assets/img/closeblack.png" alt="">
                    </button>
                </div>
                <div class="px-3">
                    <div class="notification-bar"></div>
                </div>
                <div class="sharePost-container">

                    <div class="boostPost-Cont mb-3" style="display: none;">
                        <p class="txt-desc-small mx-3">Share this post with atleast 10 people to boost this post and get credits.</p>

                        <br/>
                            
                        <h5 class="fs-16 text-primary mx-3">1 Share = $1 Credit</h5>
                        
                        <p class="txt-desc-small mx-3">You will see earned credits in your wallet, credits reflect only when the person has clicked the shared link.</p>
                    </div>
                    <div class="location-wraper">
                        <div class="input-wrapper">
                            <input type="text" id="linkInput" value="https://www.posterzs.com/test/postshare" readonly class="form-input">
                            <label for="linkInput" class="input-icon">
                                <img src="assets/img/linkform.png" alt="">
                            </label>
                            <label for="linkInput" class="postdetails-form-bar"> </label>
                        </div>
                        <div class="addPhone-button-wrapper">
                            <button type="button" id="copyButton">
                                <img src="assets/img/copy.png" alt="">
                            </button>
                        </div>
                    </div>
                    <!-- share buttons  -->
                    <div class="share-btns-wraper">
                        <a href="#" id="sharePinterest" target="_blank">
                            <span class="shareOption-img"><img src="assets/img/pinterest.png" class="img-fluid"
                                    alt=""></span>
                            <span class="shareOption-text">Pinterest</span>
                        </a>
                        <a href="#" id="shareFacebook" target="_blank">
                            <span class="shareOption-img"><img src="assets/img/facebook1.png" class="img-fluid"
                                    alt=""></span>
                            <span class="shareOption-text">Facebook</span>
                        </a>
                        <a href="#" id="shareReddit" target="_blank">
                            <span class="shareOption-img"><img src="assets/img/reddit.png" class="img-fluid"
                                    alt=""></span>
                            <span class="shareOption-text">Reddit</span>
                        </a>
                        <a href="#" id="shareTwitter" target="_blank">
                            <span class="shareOption-img"><img src="assets/img/twitter.png" class="img-fluid"
                                    alt=""></span>
                            <span class="shareOption-text">Twitter</span>
                        </a>
                        <a href="#" id="shareWhatsApp" target="_blank">
                            <span class="shareOption-img"><img src="assets/img/whatsapp-fill.png" class="img-fluid"
                                    alt=""></span>
                            <span class="shareOption-text">WhatsApp</span>
                        </a>
                        <a href="#" id="shareLinkedIn" target="_blank">
                            <span class="shareOption-img"><img src="assets/img/linkedin.png" class="img-fluid"
                                    alt=""></span>
                            <span class="shareOption-text">Linkedin</span>
                        </a>
                        <a href="#" id="shareEmail" target="_blank">
                            <span class="shareOption-img"><img src="assets/img/mail1.png" class="img-fluid"
                                    alt=""></span>
                            <span class="shareOption-text">Email</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recommend Modal  -->
<div class="offcanvas offcanvas-end p-lg-4" tabindex="-1" id="recommendationsOffcanvas" aria-labelledby="recommendationsOffcanvasLabel">
    <div class="offcanvas-header d-block">
        <div class="shareModal-header p-0">
            <input type="hidden" id="rc_company_id" />
            <h5>
                <span id="rm_company_txt">Mail On Broadway</span> has
                <span class="text-like"><span id="rm_company_count">0</span> Recommendations</span>
            </h5>
            <button class="shareModal-close" data-bs-dismiss="offcanvas" aria-label="Close">
                <img src="assets/img/closeblack.png" alt="">
            </button>
        </div>
    </div>
    <div class="notification-bar"></div>
    <div class="offcanvas-body">
        <div class="notification-lists">
            <button class="register-submit px-4" id="recomdCmpy">
                <span class="saveBtnTxt"><span id="rm_company_btn_name">Recommend</span> <strong id="rm_company_txt_n">Mail On Broadway</strong></span>
                <span class="saveBtnImg"><img src="assets/img/forward.png" class="img-fluid w-100" alt=""></span>
            </button>
            <div class="offcanvas-body" id="pc-recommends-container-scroll">
                <div class="notification-lists" id="pc-recommends-container"></div>
            </div>
        </div>
    </div>
</div>

<!-- Sticky Bottom Bar -->
<div class="stb-container stb-bar fixed-bottom">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <a href="home.php" class="btn cst-mobile-menus<?php echo ($current_page == 'home.php') ? '-active' : ''; ?>">
                <img src="assets/img/<?php echo ($current_page == 'home.php') ? 'home' : 'home-outline'; ?>.png" alt="Home" class="cst-mobile-menus-img">
            </a>
            <a href="companies.php" class="btn cst-mobile-menus<?php echo ($current_page == 'companies.php') ? '-active' : ''; ?>">
                <img src="assets/img/<?php echo ($current_page == 'companies.php') ? 'dashboardfill' : 'dashboard'; ?>.png" alt="Companies" class="cst-mobile-menus-img">
            </a>
            <button class="btn cst-mobile-menus">
                <img src="assets/img/added-mobile.svg" alt="Added" class="cst-mobile-menus-img">
            </button>
            <button class="btn cst-mobile-menus" type="button" data-bs-toggle="offcanvas" data-bs-target="#notificationOffcanvas" aria-controls="notificationOffcanvas">
                <img src="assets/img/notification-mobile.svg" alt="Notification" class="cst-mobile-menus-img">
            </button>
            <button class="btn cst-mobile-menus" data-bs-toggle="offcanvas" data-bs-target="#stbOffcanvas">
                <img src="assets/img/profile-circle-mobile.svg" alt="Profile" class="cst-mobile-menus-img">
            </button>
        </div>
    </div>
</div>

<!-- Custom Menu Offcanvas Mobile view -->
<div class="offcanvas offcanvas-bottom stb-offcanvas" tabindex="-1" id="stbOffcanvas"
    aria-labelledby="stbOffcanvasLabel">
    <div class="offcanvas-header justify-content-end align-items-end">

        <button type="button" class="btn-closex" data-bs-dismiss="offcanvas" aria-label="Close">
            <img src="assets/img/iconamoon_close-light.svg" alt="">
        </button>
    </div>
    <div class="offcanvas-body">
        <div>
            <div class="sideBar-wraper hide-scrollbar">
                <?php if(@$userDetails){ ?>
                <div class="user-profile">
                    <!-- profile card  -->
                    <div class="profile-card">
                        <div class="profile-img">
                            <span class="set-badge-online-user-himish set-badge-online-mobile-himish"></span>
                            <?php
                                $imgData = "";
                                if(empty(@$userDetails['image'])){
                                    $imgData = '<img class="img-placeholder profile-imimg-nh rounded-circle" src="'.generateBase64Image(@$userDetails['name'] ?? 'G U').'" alt="" width="20" />';
                                }else{
                                    $imgData = '<img src="'. MEDIA_BASE_URL .''.@$userDetails['image'].'" alt="" class="profile-imimg-nh rounded-circle" />';
                                }
                                echo $imgData;
                            ?>
                            <a href="#" class="edit-icon">
                                <img src="assets/img/edit.png" alt="">
                            </a>
                        </div>
                        <!-- profile details -->
                        <h2><?= @$userDetails['name'] ?></h2>
                        <a href="#" class="profile-usernamer">@<?= @$userDetails['handle_name'] ?></a>
                        <a href="user-details.php?id=<?= base64_encode(@$userDetails['id']) ?>" class="profile-link" target="_blank">
                            <img src="assets/img/link.png" alt="">
                        </a>
                    </div>
            
                    <!-- profile-stats  -->
                    <div class="profile-stats mb-4">
                        <a data-bs-toggle="offcanvas" data-bs-target="#connectionOffcanvas" aria-controls="connectionOffcanvas"
                            class="left-stats-box" data-tab="all">
                            <p>Connections</p>
                            <h3><?= @$userDetails['following'] + @$userDetails['followers'] ?></h3>
                        </a>
                        <div class="right-stats-box">
                            <a data-bs-toggle="offcanvas" data-bs-target="#connectionOffcanvas" aria-controls="connectionOffcanvas"
                                class="stats-info" data-tab="in">
                                <img src="assets/img/connect-right.png" alt="">
                                <span class="connect-num"><?= @$userDetails['following'] ?></span>
                            </a>
                            <a data-bs-toggle="offcanvas" data-bs-target="#connectionOffcanvas" aria-controls="connectionOffcanvas"
                                class="stats-info" data-tab="out">
                                <img src="assets/img/connect.png" alt="">
                                <span class="connect-num"><?= @$userDetails['followers'] ?></span>
                            </a>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <!-- Scrollable Menu List -->
            <ul class="dropdown-menux" aria-labelledby="profileOptions">
                <?php if(@$userDetails){ ?>
                <!-- item 0 -->
                <li>
                    <a class="dropdown-item profile-single" href="edit-profile.php">
                        <span class="profile-single-left">
                            <i class="fa fa-user"></i>
                            <span>Edit Profile</span>
                        </span>
                        <span class="profile-single-right">
                            <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                        </span>
                    </a>
                </li>
                <!-- item 1 -->
                <li>
                    <a class="dropdown-item profile-single" href="companies-profile.php?id=<?= base64_encode(@$userDetails['id']) ?>">
                        <span class="profile-single-left">
                            <img src="assets/img/clarity_building-line.png" alt="" />
                            <span>Company Profile</span>
                        </span>
                        <span class="profile-single-right">
                            <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                        </span>
                    </a>
                </li>
                <!-- item 2 -->
                <li>
                    <a class="dropdown-item profile-single openAdsFromMenu" data-bs-toggle="offcanvas"
                        href="#adsOffcanvas"
                        role="button"
                        aria-controls="adsOffcanvasLabel" id="openAdsCanvas">
                        <span class="profile-single-left">
                            <img src="assets/img/i2.png" alt="" />
                            <span>My Ads</span>
                        </span>
                        <span class="profile-single-right">
                            <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                        </span>
                    </a>
                </li>
                <!-- item 3 -->
                <li>
                    <a class="dropdown-item profile-single" href="user-details.php?id=<?= base64_encode(@$userDetails['id']) ?>">
                        <span class="profile-single-left">
                            <img src="assets/img/camera.png" alt="" />
                            <span>My Posts</span>
                        </span>
                        <span class="profile-single-right">
                            <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                        </span>
                    </a>
                </li>
                <!-- item 4 -->
                <li>
                    <a class="dropdown-item profile-single" href="communities.php">
                        <span class="profile-single-left">
                            <img src="assets/img/i3.png" alt="" />
                            <span>My Communities</span>
                        </span>
                        <span class="profile-single-right">
                            <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                        </span>
                    </a>
                </li>
                <!-- item 5 -->
                <li>
                    <a class="dropdown-item profile-single" href="#">
                        <span class="profile-single-left">
                            <div class="position-relative">
                                <img src="assets/img/i5.png" alt="" />
                                <?php if($userDetails['unread_chat_count'] > 0){ ?>
                                <span class="set-overlay-top-count-badge set-badge-mobile-org-count"><?= $userDetails['unread_chat_count'] ?></span>
                                <?php } ?>
                            </div>
                            <span>Chats </span>
                        </span>
                        <span class="profile-single-right">
                            <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                        </span>
                    </a>
                </li>
                <!-- item 6 -->
                <li>
                    <a class="dropdown-item profile-single" href="#">
                        <span class="profile-single-left">
                            <img src="assets/img/i6.png" alt="" />
                            <span>Wallet <span class="text-blue">$89.00</span></span>
                        </span>
                        <span class="profile-single-right">
                            <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                        </span>
                    </a>
                </li>
                <!-- item 7 -->
                <li>
                    <a class="dropdown-item profile-single" href="#">
                        <span class="profile-single-left">
                            <img src="assets/img/i7.png" alt="" />
                            <span>Invite Friends</span>
                        </span>
                        <span class="profile-single-right">
                            <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                        </span>
                    </a>
                </li>
                <?php } ?>
                <!-- item 8 -->
                <li>
                    <a class="dropdown-item profile-single" href="#">
                        <span class="profile-single-left">
                            <img src="assets/img/i8.png" alt="" />
                            <span>Faqs</span>
                        </span>
                        <span class="profile-single-right">
                            <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                        </span>
                    </a>
                </li>
                <!-- item 9 -->
                <li>
                    <a class="dropdown-item profile-single" href="privacy.php">
                        <span class="profile-single-left">
                            <img src="assets/img/i9.png" alt="" />
                            <span>Privacy Policy</span>
                        </span>
                        <span class="profile-single-right">
                            <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                        </span>
                    </a>
                </li>
                <!-- item 10 -->
                <li>
                    <a class="dropdown-item profile-single" href="terms.php">
                        <span class="profile-single-left">
                            <img src="assets/img/i10.png" alt="" />
                            <span>Terms & Condition</span>
                        </span>
                        <span class="profile-single-right">
                            <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                        </span>
                    </a>
                </li>
                <!-- item 12 -->
                <li>
                    <a class="dropdown-item profile-single" href="#">
                        <span class="profile-single-left">
                            <img src="assets/img/i11.png" alt="" />
                            <span>Contact Us</span>
                        </span>
                        <span class="profile-single-right">
                            <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                        </span>
                    </a>
                </li>
                <?php if(@$userDetails){ ?>
                <!-- item 12.0 -->
                <li>
                    <a class="dropdown-item profile-single" href="change-password.php">
                        <span class="profile-single-left">
                            <i class="fa fa-lock"></i>
                            <span>Change Password</span>
                        </span>
                        <span class="profile-single-right">
                            <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                        </span>
                    </a>
                </li>
                <!-- item 11 -->
                <li>
                    <a class="dropdown-item profile-single" href="javascript:void(0)" id="logoutUser">
                        <span class="profile-single-left">
                            <img src="assets/img/i13.png" alt="" />
                            <span>Logout</span>
                        </span>
                        <span class="profile-single-right">
                            <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                        </span>
                    </a>
                </li>
                <!-- item 13 -->
                <li>
                    <a class="dropdown-item profile-single"  href="javascript:void(0)" id="deleteAccountFn">
                        <span class="profile-single-left">
                            <img src="assets/img/i14.png" alt="" />
                            <span class="text-reprt">Delete Account</span>
                        </span>
                        <span class="profile-single-right">
                            <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                        </span>
                    </a>
                </li>
                <?php }else{ ?>
                    <li>
                        <a class="dropdown-item profile-single" href="login.php">
                            <span class="profile-single-left">
                                <img src="assets/img/i13.png" alt="" />
                                <span>Login</span>
                            </span>
                            <span class="profile-single-right">
                                <img src="assets/img/right-arr.png" class="img-fluid" alt="" />
                            </span>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>

    <script>
        let lastScrollTop = 0;
        const navBar = document.getElementById('nav-bar');

        window.addEventListener('scroll', function () {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const screenWidth = window.innerWidth;

            let topValue;
            if (screenWidth <= 767) {
            topValue = "0px"; // mobile
            } else if (screenWidth <= 991) {
            topValue = "0px"; // tablet
            } else {
            topValue = "0px"; // desktop
            }

            if (scrollTop > lastScrollTop) {
            navBar.style.top = "-100px"; // hide on scroll down
            } else {
            navBar.style.top = topValue; // show on scroll up
            }

            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        });
    </script>
<script>
function updateNotiCount() {
    fetch('ajax.php?action=getNotiCount')
        .then(response => response.json())
        .then(data => {
            const badgeContainer = document.getElementById('noti-badge-container');
            if (data.noti_count > 0) {
                badgeContainer.innerHTML = `
                    <span class="notification-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        ${data.noti_count}
                        <span class="visually-hidden">unread messages</span>
                    </span>
                `;
            } else {
                badgeContainer.innerHTML = '';
            }
        })
        .catch(error => {
            console.error('Error fetching notification count:', error);
        });
}

<?php if($userDetails){ ?>
// Run on load and every 5–10 seconds
updateNotiCount();
setInterval(updateNotiCount, Math.floor(Math.random() * 5000) + 5000); // 5 to 10 sec
<?php } ?>
</script>