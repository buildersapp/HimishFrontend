<div class="md:hidden px-4 py-2 bg-white border-b overflow-x-auto whitespace-nowrap">
    <div class="flex">
        <?php foreach ($menuItems as $item): ?>
            <?php if (!empty($item['show_tab']) && $item['show_tab']): ?>
                <a href="<?= $item['url']; ?>">
                    <button class="py-2 px-3 text-sm 
                        <?= ($current_page == $item['url']) 
                            ? 'border-b-2 border-primary text-primary font-medium' 
                            : 'text-gray-500'; ?>">
                        <?= $item['tab_label']; ?>
                    </button>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
