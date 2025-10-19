<div id="mobile-header" class="md:hidden bg-dark text-white p-4 flex justify-between items-center">
    <div class="flex items-center">
        <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center mr-2">
            <i class="fa-solid fa-p text-white"></i>
        </div>
        <div class="font-medium">Himish</div>
    </div>
    <div class="flex items-center">
        <button class="mr-4 relative">
            <i class="fa-solid fa-bell"></i>
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">3</span>
        </button>
        <button id="mobile-menu-toggle">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>
</div>

<div id="mobile-menu" class="md:hidden bg-dark text-white fixed inset-0 z-50 hidden">
    <div class="p-4 flex justify-between items-center border-b border-gray-700">
        <div class="font-medium">Menu</div>
        <button id="mobile-menu-close">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>

    <div class="p-4">
        <?php foreach ($menuItems as $item): ?>
            <?php if (!empty($item['show_mobile']) && $item['show_mobile']): ?>
                <div class="mb-4">
                    <a href="<?= $item['url']; ?>">
                        <div class="flex items-center py-2 
                            <?= ($current_page == $item['url']) ? 'bg-primary text-white' : 'text-gray-300'; ?> 
                            bg-opacity-20 rounded-md px-3">
                            <i class="fa-solid <?= $item['icon']; ?> mr-3"></i>
                            <?= $item['sidebar_label']; ?>
                        </div>
                    </a>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="p-4 border-t border-gray-700 mt-auto">
        <div class="flex items-center mb-4">
            <img src="https://storage.googleapis.com/uxpilot-auth.appspot.com/avatars/avatar-2.jpg" class="w-10 h-10 rounded-full mr-3" alt="User avatar">
            <div>
                <div class="font-medium"><?= $userDetails['name'] ?></div>
                <div class="text-gray-400 text-sm">Sales Representative</div>
            </div>
        </div>
        <button class="w-full bg-gray-700 text-white py-2 rounded font-medium flex items-center justify-center" id="logoutSalesRep">
            <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Logout
        </button>
    </div>
</div>
