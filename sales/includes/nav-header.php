<header id="header" class="bg-white border-b border-gray-200 px-8 py-4">
    <div class="flex items-center justify-between">
        <div>
            <?php if($current_page == "dashboard.php"){ ?>
                <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
                <p class="text-gray-600">Welcome back! Here's your performance overview.</p>
            <?php }else if($current_page == "communities.php"){ ?>
                <h2 class="text-2xl font-bold text-gray-900">Communities</h2>
                <p class="text-gray-600">Manage your communities and generate invite links.</p>
            <?php }else if($current_page == "posts.php"){ ?>
                <h2 class="text-2xl font-bold text-gray-900">Posts</h2>
                <p class="text-gray-600">Manage your posts and generate invite links.</p>
            <?php }else if($current_page == "create-community.php"){ ?>
                <h2 class="text-2xl font-bold text-gray-900"><?= isset($_GET['id']) ? 'Update' : 'Create' ?> Community</h2>
                <p class="text-gray-600">Build your community and start earning commissions.</p>
            <?php }else if($current_page == "commissions.php"){ ?>
                <h2 class="text-2xl font-bold text-gray-900">Commission Tracker</h2>
                <p class="text-gray-600">Monitor your earnings from post referrals and community invites.</p>
            <?php }else if($current_page == "message-templates.php"){ ?>
                <h2 class="text-2xl font-bold text-gray-900">Message Templates</h2>
                <p class="text-gray-600">Manage your WhatsApp, SMS, and Email templates</p>
            <?php }else if($current_page == "create-template.php"){ ?>
                <h2 class="text-2xl font-bold text-gray-900">Create Message Template</h2>
                <p class="text-gray-600">Create message templates for WhatsApp, SMS, and Email to streamline your outreach process.</p>
            <?php }else if($current_page == "create-post.php"){ ?>
                <h2 class="text-2xl font-bold text-gray-900">Create Post</h2>
                <p class="text-gray-600">Create a new post and generate a referral link to earn commission.</p>
            <?php }else if($current_page == "invite-links.php"){ ?>
                <h2 class="text-2xl font-bold text-gray-900">Invite Users to Community</h2>
                <p class="text-gray-600">
                    <?= $ILData['name'] ?> 
                    <?php if (!empty($ILData['address'])): ?>
                        - <?= $ILData['address'] ?>
                    <?php endif; ?>
                </p>
            <?php }else if($current_page == "invite-posts.php"){ ?>
                <h2 class="text-2xl font-bold text-gray-900">Invite Users to Post</h2>
                <p class="text-gray-600">
                    <?= $ILData['post']['title'] ?> 
                    <?php if (!empty($ILData['post']['service'])): ?>
                        - <?= $ILData['post']['service'] ?>
                    <?php endif; ?>
                </p>
            <?php } ?>
        </div>
        <div class="flex items-center space-x-4">
            <?php if($current_page == "dashboard.php" || $current_page == "posts.php"){ ?>
                <a href="create-post.php">
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 flex items-center space-x-2">
                        <i class="fa-solid fa-plus"></i>
                        <span>Create New Post</span>
                    </button>
                </a>
            <?php }else if($current_page == "communities.php"){ ?>
                <a href="create-community.php">
                    <button class="bg-purple-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-purple-700 flex items-center space-x-2">
                        <i data-fa-i2svg=""><svg class="svg-inline--fa fa-plus" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="plus" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg=""><path fill="currentColor" d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"></path></svg></i>
                        <span class="">Create New Community</span>
                    </button>
                </a>
            <?php }else if($current_page == "create-community.php" || $current_page == "invite-links.php"){ ?>
                <a href="communities.php">
                    <button class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 flex items-center space-x-2">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Back to Communities</span>
                    </button>
                </a>
            <?php }else if($current_page == "create-post.php"){ ?>
                <a href="posts.php">
                    <button class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 flex items-center space-x-2">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Back to Posts</span>
                    </button>
                </a>
            <?php }else if($current_page == "commissions.php"){ ?>
                <div class="bg-green-50 px-4 py-2 rounded-lg">
                    <span class="text-green-700 font-semibold">Total Earned: $<?= displayWithFallback($dashboardData['user']['total_earning']) ?></span>
                </div>
            <?php }else if($current_page == "message-templates.php"){ ?>
                <a href="create-template.php">
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 flex items-center space-x-2">
                        <i class="fa-solid fa-plus"></i>
                        <span>New Template</span>
                    </button>
                </a>
            <?php } ?>
            <div class="w-10 h-10 rounded-full overflow-hidden">
                <?php
                    $imgData = "";

                    if (empty(@$userDetails['image'])) {
                        $imgData = '<img 
                            class="w-full h-full object-cover" 
                            src="' . generateBase64Image(@$userDetails['name'] ?? 'G U') . '" 
                            alt="User Placeholder" 
                        />';
                    } else {
                        $imgData = '<img 
                            class="w-full h-full object-cover" 
                            src="' . MEDIA_BASE_URL . @$userDetails['image'] . '" 
                            alt="User Image" 
                        />';
                    }
                    echo $imgData;
                ?>
            </div>
        </div>
    </div>
</header>