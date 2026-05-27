<?php
$uri = $_SERVER['REQUEST_URI'];
function isActive($path, $current)
{
    if ($path === '/settings') {
        // Routers is the new home. Active if exactly /settings or /settings/routers
        return $current === '/settings' || $current === '/settings/' || strpos($current, '/settings/routers') !== false;
    }

    return strpos($current, $path) !== false;
}

$menu = [
    ['label' => 'routers_title', 'url' => '/settings', 'namespace' => 'settings'],
    ['label' => 'system', 'url' => '/settings/system', 'namespace' => 'settings'],
    ['label' => 'templates_title', 'url' => '/settings/voucher-templates', 'namespace' => 'settings'],
    ['label' => 'logos_title', 'url' => '/settings/logos', 'namespace' => 'settings'],
    ['label' => 'api_cors_title', 'url' => '/settings/api-cors', 'namespace' => 'settings'],
    ['label' => 'plugins_title', 'url' => '/settings/plugins', 'namespace' => 'settings'],
];
?>
<nav id="settings-sidebar" class="w-full sticky top-[64px] z-40 bg-background/95 backdrop-blur border-b border-accents-2 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 md:px-8"> <!-- Aligned with header_main max-w-7xl -->
        <div class="relative py-2 flex items-start gap-2">
            
            <!-- Menu Container (Toggles between flex-row/scroll and grid) -->
            <div id="sub-navbar-menu" class="flex-1 flex flex-row items-center overflow-x-auto no-scrollbar mask-fade-right gap-2 transition-all duration-300">
                <?php foreach ($menu as $item) {
                    $active = isActive($item['url'], $uri);
                    ?>
                <a href="<?= $item['url'] ?>" 
                   class="sub-nav-item whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 border border-transparent 
                   <?= $active ? 'bg-foreground text-background shadow-sm' : 'text-accents-5 hover:text-foreground hover:bg-accents-1' ?>"
                   data-i18n="<?= ($item['namespace'] ?? 'settings').'.'.$item['label'] ?>">
                    <?= $item['label'] ?>
                </a>
                <?php } ?>
            </div>

            <!-- Toggle Button -->
            <button id="sub-navbar-toggle" class="flex-shrink-0 p-2 text-accents-5 hover:text-foreground hover:bg-accents-1 rounded-full transition-colors hidden sm:block" title="Expand Menu">
                <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300"></i>
            </button>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleBtn = document.getElementById('sub-navbar-toggle');
        const menu = document.getElementById('sub-navbar-menu');
        const icon = toggleBtn?.querySelector('i');
        let isExpanded = false;

        if (toggleBtn && menu) {
            // Check if content overflows to decide if we even show the toggle initially?
            // For now, always show it on sm+ screens if desired, or we can check scrollWidth > clientWidth.
            // Let's keep it simple: always available on desktop/tablet to see full grid.

            toggleBtn.addEventListener('click', () => {
                isExpanded = !isExpanded;
                
                if (isExpanded) {
                    // Expand: Grid Layout
                    menu.classList.remove('flex-row', 'overflow-x-auto', 'whitespace-nowrap', 'mask-fade-right', 'items-center');
                    menu.classList.add('grid', 'grid-cols-2', 'sm:grid-cols-3', 'md:grid-cols-4', 'lg:grid-cols-5', 'gap-2', 'pb-4');
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    // Collapse: Scroll Layout
                    menu.classList.add('flex-row', 'overflow-x-auto', 'whitespace-nowrap', 'mask-fade-right', 'items-center');
                    menu.classList.remove('grid', 'grid-cols-2', 'sm:grid-cols-3', 'md:grid-cols-4', 'lg:grid-cols-5', 'gap-2', 'pb-4');
                    icon.style.transform = 'rotate(0deg)';
                    
                    // Reset scroll position to start? or keep?
                    menu.scrollLeft = 0;
                }
            });
        }
    });

    // Re-run Lucide mainly for the chevron if this is loaded via PJAX (though sidebar is usually persistent in SPA layout? 
    // Wait, in PJAX we replace content, not the sidebar if it's outside. 
    // BUT sidebar_settings.php is INSIDE the view in the current PHP architecture.
    // So it gets re-rendered on every navigation if we don't change that.
    // The current SPA script replaces `#settings-content-area`.
    // We need to move the sidebar OUT of the `#settings-content-area` target in the PHP files if we want it to persist...
    // OR we just re-init the script. Since it's inline, it runs on content injection.
    
    if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
