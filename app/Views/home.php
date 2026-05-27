<?php
use App\Config\SiteConfig;

require_once ROOT.'/app/Views/layouts/header_main.php'; ?>

<div class="w-full max-w-4xl mx-auto py-8 md:py-16 px-4 sm:px-6 text-center">
    <div class="mb-8 flex justify-center">
        <div class="h-16 w-16 bg-transparent rounded-full flex items-center justify-center">
            <img src="/assets/img/logo-m.svg" alt="Mivo Logo" class="h-16 w-auto block dark:hidden">
            <img src="/assets/img/logo-m-dark.svg" alt="Mivo Logo" class="h-16 w-auto hidden dark:block">
        </div>
    </div>
    
    <h1 class="text-4xl font-extrabold tracking-tight mb-4"><?= SiteConfig::APP_FULL_NAME ?></h1>
    <p class="text-xl text-accents-5 mb-12 max-w-2xl mx-auto" data-i18n="home.subtitle">
        A modern, lightweight MikroTik Hotspot Manager built for performance and simplicity.
    </p>

    <!-- Action Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-2xl mx-auto mb-16">
        <a href="/settings" class="group card hover:border-foreground transition-all duration-200 text-left">
            <div class="h-10 w-10 bg-accents-1 rounded-lg flex items-center justify-center mb-4 group-hover:bg-foreground group-hover:text-background transition-colors">
                <i data-lucide="server" class="w-5 h-5"></i>
            </div>
            <h3 class="font-semibold text-lg mb-1" data-i18n="home.manage_routers">Manage Routers</h3>
            <p class="text-sm text-accents-5" data-i18n="home.manage_routers_desc">Configure RouterOS connections and view status.</p>
        </a>

        <a href="<?= SiteConfig::REPO_URL ?>" target="_blank" class="group card hover:border-foreground transition-all duration-200 text-left">
             <div class="h-10 w-10 bg-accents-1 rounded-lg flex items-center justify-center mb-4 group-hover:bg-foreground group-hover:text-background transition-colors">
                <i data-lucide="github" class="w-5 h-5"></i>
            </div>
            <h3 class="font-semibold text-lg mb-1" data-i18n="home.source_code">Source Code</h3>
            <p class="text-sm text-accents-5" data-i18n="home.source_code_desc">View the project repository and contribute.</p>
        </a>
    </div>

    <!-- Quick Router List if available -->
    <?php
    $quickRouters = array_filter($routers, function ($r) {
        return isset($r['quick_access']) && $r['quick_access'] == 1;
    });
?>
    <?php if (! empty($quickRouters)) { ?>
        <div class="text-left max-w-4xl mx-auto">
            <h2 class="text-sm font-semibold text-accents-5 uppercase tracking-wider mb-4" data-i18n="home.quick_access">Quick Access</h2>
            <div class="table-container">
                <table class="table-glass">
                    <thead>
                        <tr>
                            <th scope="col" data-i18n="home.session_name">Session Name</th>
                            <th scope="col" data-i18n="home.hotspot_name">Hotspot Name</th>
                            <th scope="col" data-i18n="home.ip_address">IP Address</th>
                            <th scope="col" class="relative text-right">
                                <span class="sr-only" data-i18n="common.actions">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quickRouters as $router) { ?>
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded bg-accents-2 flex items-center justify-center text-xs font-bold mr-3">
                                        <?= strtoupper(substr($router['session_name'], 0, 2)) ?>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-foreground"><?= htmlspecialchars($router['session_name']) ?></div>
                                        <div class="text-xs text-accents-5">ID: <?= $router['id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm text-foreground"><?= htmlspecialchars($router['hotspot_name']) ?></div>
                            </td>
                            <td>
                                <div class="text-sm text-accents-5 font-mono"><?= htmlspecialchars($router['ip_address']) ?></div>
                            </td>
                            <td class="text-right text-sm font-medium">
                                <a href="/<?= htmlspecialchars($router['session_name']) ?>/dashboard" class="btn btn-secondary btn-sm h-8 px-3" data-i18n="common.open">
                                    Open
                                </a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php } ?>
</div>

<?php require_once ROOT.'/app/Views/layouts/footer_main.php'; ?>
