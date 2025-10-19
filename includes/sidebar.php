<?php
// Get the current page file name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="navbar-expand-lg">
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header bg-transparent">
            <button type="button" class="btn-close bg-transparent" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <aside class="position-fixed top-0 leftSide">
                <div class="logo">
                    <a href=""><img src="assets/img/logo.png" alt="logo" class="img-fluid" /></a>
                </div>
                <nav>
                    <!-- nav link start  -->
                    <div class="navWrapper">
                        <div class="navMain">
                            <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                                <span>Dashboard</span>
                            </a>
                        </div>
                    </div>
                    <!-- nav link end  -->

                    <!-- nav link start  -->
                    <div class="navWrapper">
                        <div class="navMain">
                            <a href="" class="<?php echo ($current_page == 'users.php' || $current_page == 'companies.php' || $current_page == 'view-user.php' || $current_page == 'company-details.php' || $current_page == 'listings.php' || $current_page == 'deals.php' || $current_page == 'deal-share.php' || $current_page == 'communities.php' || $current_page == 'community-details.php' || $current_page == 'post-details.php' || $current_page == 'posts.php' || $current_page == 'master.php' || $current_page == 'edit-categories.php') ? 'active' : ''; ?>">
                                <span>Lists</span>
                            </a>
                        </div>
                        <ul class="navItem">
                            <li><a href="users.php" class="<?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">Users</a></li>
                            <li><a href="sub-admin.php" class="<?php echo ($current_page == 'sub-admin.php') ? 'active' : ''; ?>">Sub Admins</a></li>
                            <li><a  href="companies.php" class="<?php echo ($current_page == 'companies.php' || $current_page == 'company-details.php') ? 'active' : ''; ?>">Companies</a></li>
                            <li><a href="posts.php" class="<?php echo ($current_page == 'posts.php' || $current_page == 'post-details.php') ? 'active' : ''; ?>">Posts</a></li>
                            <li><a href="ads.php" class="<?php echo ($current_page == 'ads.php') ? 'active' : ''; ?>">Ads</a></li>
                            <li><a href="listings.php" class="<?php echo ($current_page == 'listings.php') ? 'active' : ''; ?>">Listings (Looking For)</a></li>
                            <li><a href="deals.php" class="<?php echo ($current_page == 'deals.php') ? 'active' : ''; ?>">Deals</a></li>
                            <li><a href="deal-share.php" class="<?php echo ($current_page == 'deal-share.php') ? 'active' : ''; ?>">Deal Share</a></li>
                            <li><a href="communities.php" class="<?php echo ($current_page == 'communities.php' || $current_page == 'community-details.php') ? 'active' : ''; ?>">Communities</a></li>
                            <!-- <li><a href="comments.php" class="<?php echo ($current_page == 'comments.php') ? 'active' : ''; ?>">Comments</a></li> -->
                            <!-- <li><a href="" class="<?php echo ($current_page == 'products_services.php') ? 'active' : ''; ?>">Products & Services</a></li> -->
                            <li><a href="master.php" class="<?php echo ($current_page == 'master.php' || $current_page == 'edit-categories.php') ? 'active' : ''; ?>">Master</a></li>
                            <li><a href="wallets.php" class="<?php echo ($current_page == 'wallets.php') ? 'active' : ''; ?>">Wallets</a></li>
                            <li><a href="transactions.php" class="<?php echo ($current_page == 'transactions.php') ? 'active' : ''; ?>">Transactions</a></li>
                            <!-- <li><a href="ocr-scans.php" class="<?php echo ($current_page == 'ocr-scans.php') ? 'active' : ''; ?>">OCR Scans</a></li> -->
                            <!-- <li><a href="" class="<?php echo ($current_page == 'web_feed_ads.php') ? 'active' : ''; ?>">Web Feed Page Ads</a></li>
                            <li><a href="" class="<?php echo ($current_page == 'lcr.php') ? 'active' : ''; ?>">LCR</a></li> -->
                        </ul>
                    </div>
                    <!-- nav link end  -->

                    <!-- nav link start  -->
                    <div class="navWrapper">
                        <div class="navMain">
                            <a href="" class="<?php echo ($current_page == 'claimed-business-requests.php' || $current_page == 'ads-requests.php' || $current_page == 'dispute-requests.php' || $current_page == 'community-requests.php') ? 'active' : ''; ?>">
                                <span>Requests</span>
                            </a>
                        </div>
                        <ul class="navItem">
                            <li><a href="claimed-business-requests.php" class="<?php echo ($current_page == 'claimed-business-requests.php') ? 'active' : ''; ?>">Claimed Business</a></li>
                            <!-- <li><a href="ads-requests.php" class="<?php echo ($current_page == 'ads-requests.php') ? 'active' : ''; ?>">Ad Requests</a></li> -->
                            <li><a href="community-requests.php" class="<?php echo ($current_page == 'community-requests.php') ? 'active' : ''; ?>">New Community Request</a></li>
                            <li><a href="dispute-requests.php" class="<?php echo ($current_page == 'dispute-requests.php') ? 'active' : ''; ?>">Disputes</a></li>
                        </ul>
                    </div>
                    <!-- nav link end  -->

                    <!-- nav link start  -->
                    <div class="navWrapper">
                        <div class="navMain">
                            <a href="" class="<?php echo ($current_page == 'general_settings.php' || $current_page == 'faqs.php' || $current_page == 'plans.php' || $current_page == 'general-settings.php' || $current_page == 'email-templates.php' || $current_page == 'notification-templates.php' || $current_page == 'point-accumulation.php' || $current_page == 'contact-us.php') ? 'active' : ''; ?>">
                                <span>General Settings</span>
                            </a>
                        </div>
                        <ul class="navItem">
                            <li><a href="general-settings.php" class="<?php echo ($current_page == 'general-settings.php') ? 'active' : ''; ?>">General</a></li>
                            <li><a href="plans.php" class="<?php echo ($current_page == 'plans.php') ? 'active' : ''; ?>">Plans</a></li>
                            <li><a href="point-accumulation.php" class="<?php echo ($current_page == 'point-accumulation.php') ? 'active' : ''; ?>">Point Accumulation</a></li>
                            <!-- <li><a href="" class="<?php echo ($current_page == 'point_accumulation.php') ? 'active' : ''; ?>">Point Accumulation</a></li> -->
                            <li><a href="notification-templates.php" class="<?php echo ($current_page == 'notification-templates.php') ? 'active' : ''; ?>">Notification & Email Setup</a></li>
                            <!-- <li><a href="email-templates.php" class="<?php echo ($current_page == 'email-templates.php') ? 'active' : ''; ?>">Email Template Setup</a></li> -->
                            <li><a href="faqs.php" class="<?php echo ($current_page == 'faqs.php') ? 'active' : ''; ?>">FAQs</a></li>
                            <li><a href="contact-us.php" class="<?php echo ($current_page == 'contact-us.php') ? 'active' : ''; ?>">Contact Us</a></li>
                        </ul>
                    </div>
                    <!-- nav link end  -->
                </nav>
            </aside>
        </div>
    </div>
</div>
