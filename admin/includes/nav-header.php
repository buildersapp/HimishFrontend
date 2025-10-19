<?php
if (empty($_SESSION)) {
    echo "<script>window.location.href = 'index.php'</script>";
    exit();
}

$apiData=[];

$response = sendCurlRequest(BASE_URL.'/get-notifications?web=ad&page=1&limit=10000', 'GET', $apiData);
//dump($response);
$decodedResponse = json_decode($response, true);

// Handle 401 Unauthorized
if ((isset($decodedResponse['code']) && $decodedResponse['code'] == 401) || !isset($_SESSION['hm_auth_data'])) {
    http_response_code(401);
    // $_SESSION['hm_auth_data'] = (object)[];
    // $_SESSION['logged_in'] = false;

    unset($_SESSION['hm_auth_data']);
    unset($_SESSION['logged_in']);
    unset($_SESSION['hm_timezone']);
    header('Location: index.php');
}else{
    $notifications = $decodedResponse['body'];
    //dump($notifications);
}
?>
<!-- header start  -->
<header class="position-sticky top-0 mainHeader">
    <div class="d-flex w-100 justify-content-between align-self-center align-items-center">
        <div class="headerLeft d-lg-block d-none">
            <?php
            if($userRole == 3){ ?>
                <div class="logo ms-3">
                    <a href=""><img src="assets/img/logo.png" width="150" alt="logo" class="img-fluid" /></a>
                </div>
            <?php }else{
            echo '<ul class="d-flex flex-row ps-0">';

            foreach ($breadcrumb as $index => $item) {
                echo '<li class="d-flex align-items-center">';

                if ($index < count($breadcrumb) - 1) {
                    echo '<a href="' . $item['url'] . '">' . $item['name'] . '</a>';
                    
                    echo '&emsp;<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                            d="M17.0607 13.0607C17.6464 12.4749 17.6464 11.5251 17.0607 10.9393L7.51472 1.3934C6.92893 0.807611 5.97919 0.807611 5.3934 1.3934C4.80761 1.97919 4.80761 2.92893 5.3934 3.51472L13.8787 12L5.3934 20.4853C4.80761 21.0711 4.80761 22.0208 5.3934 22.6066C5.97919 23.1924 6.92893 23.1924 7.51472 22.6066L17.0607 13.0607ZM14 13.5H16V10.5H14V13.5Z"
                            fill="#000000" />
                        </svg>';
                } else {
                    echo $item['name'];
                }

                echo '</li>';
            }

            echo '</ul>';
        } ?>
        </div>
        <div class="d-lg-none d-block">
            <button class="btn border" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5" />
                </svg>
            </button>
        </div>
        <div class="headerRight d-flex align-self-center align-items-center">
            <?php if($myProfile['unread_contact_us_count'] > 0): ?>
            <!-- Messaging Dropdown Start -->
            <div class="dropdown notifications me-3 position-relative">
                <a href="contact-us.php">
                    <button class="btn dropdown-toggle" type="button">
                        <i class="fa fa-comment-dots"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $myProfile['unread_contact_us_count'] ?>
                        </span>
                    </button>
                </a>
            </div>
            <!-- Messaging Dropdown End -->
            <?php endif; ?>

            <!-- Notifications Dropdown Start -->
            <div class="dropdown notifications me-3 position-relative">
                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="assets/img/notifications.svg" alt="img" class="img-fluid" />
                    <?php if($myProfile['noti_count'] > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $myProfile['noti_count'] ?>
                        </span>
                    <?php endif; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width: 300px;">
                    <li class="dropdown-header fw-bold"> <?= (count($notifications)) ? 'Notifications' : 'No New Notifications' ?></li>
                    <div style="max-height: 200px; overflow-y: auto;">
                        <?php foreach ($notifications as $noti): ?>
                            <li><a class="dropdown-item <?= $noti['is_seen']==1 ? 'text-t': 'text-danger' ?>" href="read-notification.php?id=<?= base64_encode($noti['id']) ?>&code=<?= base64_encode($noti['code']) ?>&request_id=<?= base64_encode($noti['request_id']) ?>&user_id=<?= base64_encode($noti['user_id']) ?>"><?= $noti['message'] ?></a></li>
                            <li><hr class="dropdown-divider"></li>
                        <?php endforeach; ?>
                    </div>
                </ul>
            </div>
            <!-- Notifications Dropdown End -->

            <!-- Notifications Dropdown End -->

            <!-- User Dropdown Start -->
            <div class="dropdown user">
                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php if($userRole != 3){ ?>
                        <i class="fa fa-user-tie fa-2x mt-2 text-primary"></i>
                    <?php }else{ ?>
                        <i class="fa fa-user fa-2x mt-2 text-primary"></i>
                    <?php } ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="javascript:void(0)" id="logoutUser">Logout</a></li>
                </ul>
            </div>
            <!-- User Dropdown End -->
        </div>
    </div>
</header>
<!-- header end  -->
