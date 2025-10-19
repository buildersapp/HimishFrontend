<?php
// Get the current page file name
$modulesRoleBased = [
    "Users",
    "Sub Admins",
    "Sales Person",
    "Citilytics Data",
    "Companies",
    "Posts",
    "Ads",
    "Listings",
    "Deals",
    "Deal Share",
    "Communities",
    "Master",
    "Wallets",
    "Transactions",
    "Web Feed Ads",
    "Logs",
    "Countries",
    "Claimed Business Requests",
    "Community Requests",
    "Dispute Requests",
    "General Settings",
    "Plans",
    "Point Accumulation",
    "Notification & Email Setup",
    "FAQs",
    "Contact Us"
];

$permissions_json = [];
foreach ($modulesRoleBased as $module) {
    $permissions_json[$module] = [
        "add"    => false,
        "edit"   => false,
        "delete" => false,
        "notification" => false,
        "view"   => true // ✅ default checked
    ];
}

if (!empty($myProfile['permissions']['permissions'])) {
    $permissions_json = $myProfile['permissions']['permissions'];
    if($userRole == 2){
        $_SESSION['permissions'] = $permissions_json;
    }
}

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
                            <?php if (hasPermission('Users', 'view')) { ?>
                            <li><a href="users.php" class="<?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">Users</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Sub Admins', 'view')) { ?>
                            <li><a href="sub-admin.php" class="<?php echo ($current_page == 'sub-admin.php') ? 'active' : ''; ?>">Sub Admins</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Sales Person', 'view')) { ?>
                            <li><a href="sales-person.php" class="<?php echo ($current_page == 'sales-person.php') ? 'active' : ''; ?>">Sales Person</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Citilytics Data', 'view')) { ?>
                            <li><a href="citilytics-data.php" class="<?php echo ($current_page == 'citilytics-data.php') ? 'active' : ''; ?>">Citilytics Data</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Companies', 'view')) { ?>
                            <li><a  href="companies.php" class="<?php echo ($current_page == 'companies.php' || $current_page == 'company-details.php') ? 'active' : ''; ?>">Companies</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Posts', 'view')) { ?>
                            <li><a href="posts.php" class="<?php echo ($current_page == 'posts.php' || $current_page == 'post-details.php') ? 'active' : ''; ?>">Posts</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Ads', 'view')) { ?>
                            <li><a href="ads.php" class="<?php echo ($current_page == 'ads.php') ? 'active' : ''; ?>">Ads</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Listings', 'view')) { ?>
                            <li><a href="listings.php" class="<?php echo ($current_page == 'listings.php') ? 'active' : ''; ?>">Listings (Looking For)</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Deals', 'view')) { ?>
                            <li><a href="deals.php" class="<?php echo ($current_page == 'deals.php') ? 'active' : ''; ?>">Deals</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Deal Share', 'view')) { ?>
                            <li><a href="deal-share.php" class="<?php echo ($current_page == 'deal-share.php') ? 'active' : ''; ?>">Deal Share</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Communities', 'view')) { ?>
                            <li><a href="communities.php" class="<?php echo ($current_page == 'communities.php' || $current_page == 'community-details.php') ? 'active' : ''; ?>">Communities</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Master', 'view')) { ?>
                            <li><a href="master.php" class="<?php echo ($current_page == 'master.php' || $current_page == 'edit-categories.php') ? 'active' : ''; ?>">Master</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Wallets', 'view')) { ?>
                            <li><a href="wallets.php" class="<?php echo ($current_page == 'wallets.php') ? 'active' : ''; ?>">Wallets</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Transactions', 'view')) { ?>
                            <li><a href="transactions.php" class="<?php echo ($current_page == 'transactions.php') ? 'active' : ''; ?>">Transactions</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Web Feed Ads', 'view')) { ?>
                            <li><a href="web-feed-ads.php" class="<?php echo ($current_page == 'web-feed-ads.php') ? 'active' : ''; ?>">Web Feed Page Ads</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Logs', 'view')) { ?>
                            <li><a href="get-logs.php" class="<?php echo ($current_page == 'get-logs.php') ? 'active' : ''; ?>">Logs</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Countries', 'view')) { ?>
                            <li><a href="countries.php" class="<?php echo ($current_page == 'countries.php') ? 'active' : ''; ?>">Countries</a></li>
                            <?php } ?>

                            <?php if (hasPermission('General Settings', 'view')) { ?>
                            <li><a href="profile-groups.php" class="<?php echo ($current_page == 'profile-groups.php') ? 'active' : ''; ?>">Profile Groups</a></li>
                            <?php } ?>
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
                            <?php if (hasPermission('Claimed Business Requests', 'view')) { ?>
                            <li><a href="claimed-business-requests.php" class="<?php echo ($current_page == 'claimed-business-requests.php') ? 'active' : ''; ?>">Claimed Business</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Community Requests', 'view')) { ?>
                            <li><a href="community-requests.php" class="<?php echo ($current_page == 'community-requests.php') ? 'active' : ''; ?>">New Community Request</a></li>
                            <?php } ?>
                            
                            <?php if (hasPermission('Dispute Requests', 'view')) { ?>
                            <li><a href="dispute-requests.php" class="<?php echo ($current_page == 'dispute-requests.php') ? 'active' : ''; ?>">Disputes</a></li>
                            <?php } ?>
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
                            <?php if (hasPermission('General Settings', 'view')) { ?>
                            <li><a href="general-settings.php" class="<?php echo ($current_page == 'general-settings.php') ? 'active' : ''; ?>">General</a></li>
                            <?php } ?>

                            <?php if (hasPermission('General Settings', 'view')) { ?>
                            <li><a href="website-content.php" class="<?php echo ($current_page == 'website-content.php') ? 'active' : ''; ?>">Website Content</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Plans', 'view')) { ?>
                            <li><a href="plans.php" class="<?php echo ($current_page == 'plans.php') ? 'active' : ''; ?>">Plans</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Point Accumulation', 'view')) { ?>
                            <li><a href="point-accumulation.php" class="<?php echo ($current_page == 'point-accumulation.php') ? 'active' : ''; ?>">Point Accumulation</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Notification & Email Setup', 'view')) { ?>
                            <li><a href="notification-templates.php" class="<?php echo ($current_page == 'notification-templates.php') ? 'active' : ''; ?>">Notification & Email Setup</a></li>
                            <?php } ?>

                            <?php if (hasPermission('FAQs', 'view')) { ?>
                            <li><a href="faqs.php" class="<?php echo ($current_page == 'faqs.php') ? 'active' : ''; ?>">FAQs</a></li>
                            <?php } ?>

                            <?php if (hasPermission('Contact Us', 'view')) { ?>
                            <li><a href="contact-us.php" class="<?php echo ($current_page == 'contact-us.php') ? 'active' : ''; ?>">Contact Us</a></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <!-- nav link end  -->
                </nav>
            </aside>
        </div>
    </div>
</div>
