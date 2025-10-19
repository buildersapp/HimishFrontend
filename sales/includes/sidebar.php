<!-- Sidebar -->
<aside id="sidebar" class="w-64 bg-white shadow-lg border-r border-gray-200">
    <!-- Logo / Header -->
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-chart-line text-white text-sm"></i>
            </div>
            <h1 class="text-xl font-bold text-gray-900">Sales Portal</h1>
        </div>
    </div>

    <!-- Menu Items -->
    <nav id="navigation" class="flex-1 px-4 pb-4 overflow-y-auto">
        <ul class="space-y-2 mt-4">
            <?php foreach ($menuItems as $item): ?>
                <?php if ($item['show_sidebar']): ?>
                    <li>
                        <a href="<?= $item['url']; ?>">
                            <span class="flex items-center space-x-3 px-4 py-3 rounded-lg font-medium cursor-pointer
                                <?= ($current_page == $item['url']) 
                                    ? 'text-blue-600 bg-blue-50' 
                                    : 'text-gray-600 hover:bg-gray-50'; ?>">
                                <i class="fa-solid <?= $item['icon']; ?>"></i>
                                <span><?= $item['sidebar_label']; ?></span>
                            </span>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>

        <!-- Divider -->
        <div class="mt-8 pt-4 border-t border-gray-200">
            <!-- Logout -->
            <span class="space-x-3 px-4 py-3 text-sm font-medium text-gray-900"><?= $userDetails['name'] ?></span>
            <button id="logoutSalesRep"
                class="w-full flex items-center space-x-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg font-medium">
                <i class="fa-solid fa-sign-out-alt"></i>
                <span>Log Out</span>
            </button>
        </div>
    </nav>
</aside>