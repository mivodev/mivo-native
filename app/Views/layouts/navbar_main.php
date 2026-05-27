<?php

use App\Config\SiteConfig;
use App\Helpers\LanguageHelper;

// Determine active link state
$uri = $_SERVER['REQUEST_URI'] ?? '/';
?>
<!-- Modern Navbar (Tailwind) -->
<nav class="sticky top-0 z-50 w-full border-b border-accents-2 bg-background/80 backdrop-blur supports-[backdrop-filter]:bg-background/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <!-- Brand & Desktop Nav -->
            <div class="flex items-center gap-8">
                <a href="/" class="flex items-center gap-2 group">
                    <img src="/assets/img/logo-m.svg" alt="<?= SiteConfig::APP_NAME ?> Logo" class="h-6 w-auto block dark:hidden transition-transform group-hover:scale-110">
                    <img src="/assets/img/logo-m-dark.svg" alt="<?= SiteConfig::APP_NAME ?> Logo" class="h-6 w-auto hidden dark:block transition-transform group-hover:scale-110">
                    <span class="font-bold text-lg tracking-tight"><?= SiteConfig::APP_NAME ?></span>
                </a>

                <!-- Desktop Navigation Links (Hidden on Mobile) -->
                <?php if (isset($_SESSION['user_id'])) { ?>
                <div class="hidden md:flex items-center gap-6 text-sm font-medium">
                    <a href="/" class="relative py-1 <?= ($uri == '/' || $uri == '/home') ? 'text-foreground after:absolute after:bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-foreground' : 'text-accents-5 hover:text-foreground transition-colors' ?>">Home</a>
                    <a href="/settings" class="relative py-1 <?= (strpos($uri, '/settings') === 0) ? 'text-foreground after:absolute after:bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-foreground' : 'text-accents-5 hover:text-foreground transition-colors' ?>">Settings</a>
                </div>
                <?php } ?>
            </div>
            
            <!-- Right side controls -->
            <div class="flex items-center gap-3">
                <!-- Desktop Control Pill (Hidden on Mobile) -->
                <div class="hidden md:flex control-pill scale-95 hover:scale-100 transition-transform">
                    <!-- Notification Bell -->
                    <div class="relative group" onmouseleave="closeMenu('notification-dropdown')">
                        <button id="notification-bell" type="button" class="pill-lang-btn relative" onclick="toggleMenu('notification-dropdown', this)" title="Notifications">
                             <i data-lucide="bell" class="w-4 h-4"></i>
                             <span id="update-badge" class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full hidden animate-pulse"></span>
                        </button>
                        <div id="notification-dropdown" class="absolute right-0 top-full mt-3 w-64 bg-background/95 backdrop-blur-2xl border border-accents-2 rounded-xl shadow-xl overflow-hidden transition-all duration-200 ease-out origin-top-right opacity-0 scale-95 invisible pointer-events-none z-50 dropdown-bridge">
                            <div class="px-3 py-2 text-[10px] font-bold text-accents-4 uppercase tracking-widest border-b border-accents-2/50 bg-accents-1/50" data-i18n="notifications.title">Notifications</div>
                            <div id="notification-content" class="p-4 text-sm text-accents-5 text-center" data-i18n="notifications.empty">
                                No new notifications
                            </div>
                        </div>
                    </div>

                    <div class="pill-divider"></div>

                    <!-- Language Switcher -->
                    <div class="relative group" onmouseleave="closeMenu('lang-dropdown-desktop')">
                        <button type="button" class="pill-lang-btn" onclick="toggleMenu('lang-dropdown-desktop', this)" title="Change Language">
                             <i data-lucide="languages" class="w-4 h-4"></i>
                        </button>
                         <div id="lang-dropdown-desktop" class="absolute right-0 top-full mt-3 w-48 bg-background/95 backdrop-blur-2xl border border-accents-2 rounded-xl shadow-xl overflow-hidden transition-all duration-200 ease-out origin-top-right opacity-0 scale-95 invisible pointer-events-none z-50 dropdown-bridge">
                            <div class="px-3 py-2 text-[10px] font-bold text-accents-4 uppercase tracking-widest border-b border-accents-2/50 bg-accents-1/50" data-i18n="sidebar.switch_language">Select Language</div>
                            <?php
                            $languages = LanguageHelper::getAvailableLanguages();
foreach ($languages as $lang) {
    $pathArg = isset($lang['path']) ? "', '".$lang['path'] : '';
    ?>
                            <button onclick="Mivo.modules.I18n.loadLanguage('<?= $lang['code'] ?><?= $pathArg ?>')" class="w-full text-left flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-accents-1 transition-colors text-accents-6 hover:text-foreground group/lang">
                                <span class="fi fi-<?= $lang['flag'] ?> rounded-sm shadow-sm transition-transform group-hover/lang:scale-110"></span>
                                <span><?= $lang['name'] ?></span>
                            </button>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- Theme Toggle (Segmented) -->
                    <div class="segmented-switch theme-toggle" title="Toggle Theme">
                        <div class="segmented-switch-slider"></div>
                        <div class="segmented-switch-btn theme-toggle-light-icon">
                            <i data-lucide="sun" class="w-4 h-4" stroke-width="3.5"></i>
                        </div>
                        <div class="segmented-switch-btn theme-toggle-dark-icon">
                            <i data-lucide="moon" class="w-4 h-4" stroke-width="3.5"></i>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['user_id'])) { ?>
                        <div class="pill-divider"></div>
                        <a href="/logout" class="p-1.5 rounded-lg text-accents-5 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all ml-0.5" title="Logout">
                            <i data-lucide="log-out" class="w-4 h-4 !text-black dark:!text-white" stroke-width="2.5"></i>
                        </a>
                    <?php } ?>
                </div>

                <!-- Mobile Menu Toggles -->
                <div class="flex md:hidden items-center gap-2">
                    <!-- Mobile Mode Control Pill (Condensed) -->
                    <div class="control-pill py-1.5 px-2">
                         <div class="segmented-switch theme-toggle scale-75" title="Toggle Theme">
                            <div class="segmented-switch-slider"></div>
                            <div class="segmented-switch-btn theme-toggle-light-icon"><i data-lucide="sun" class="w-4 h-4" stroke-width="3.5"></i></div>
                            <div class="segmented-switch-btn theme-toggle-dark-icon"><i data-lucide="moon" class="w-4 h-4" stroke-width="3.5"></i></div>
                        </div>
                    </div>

                    <button type="button" class="p-2 rounded-lg bg-accents-1 text-accents-5 hover:text-foreground transition-colors group" onclick="toggleMenu('mobile-navbar-menu', this)">
                        <i data-lucide="menu" class="w-5 h-5 !text-black dark:!text-white transition-transform duration-300" stroke-width="2.5"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Drawer (Hidden by default) -->
    <div id="mobile-navbar-menu" class="md:hidden border-t border-accents-2 bg-background/95 backdrop-blur-xl transition-all duration-300 ease-in-out max-h-0 opacity-0 invisible overflow-hidden">
        <div class="px-4 pt-4 pb-6 space-y-4">
            <!-- Nav Links -->
            <?php if (isset($_SESSION['user_id'])) { ?>
            <div class="flex flex-col gap-1">
                <a href="/" class="flex items-center gap-3 px-4 py-3 rounded-xl <?= ($uri == '/' || $uri == '/home') ? 'bg-foreground/5 text-foreground font-bold' : 'text-accents-5 hover:bg-accents-1' ?>">
                    <i data-lucide="home" class="w-5 h-5 !text-black dark:!text-white" stroke-width="2.5"></i>
                    <span>Home</span>
                </a>
                <a href="/settings" class="flex items-center gap-3 px-4 py-3 rounded-xl <?= (strpos($uri, '/settings') === 0) ? 'bg-foreground/5 text-foreground font-bold' : 'text-accents-5 hover:bg-accents-1' ?>">
                    <i data-lucide="settings" class="w-5 h-5 !text-black dark:!text-white" stroke-width="2.5"></i>
                    <span>Settings</span>
                </a>
            </div>
            <?php } ?>

            <!-- Mobile Controls Overlay -->
            <div class="p-4 rounded-2xl bg-accents-1/50 border border-accents-2 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-accents-4 uppercase tracking-wider">Select Language</span>
                </div>
                <div class="flex items-center gap-2 overflow-x-auto pb-2 -mx-4 px-4 scrollbar-hide snap-x">
                    <?php foreach ($languages as $lang) {
                        $pathArg = isset($lang['path']) ? "', '".$lang['path'] : '';
                        ?>
                    <button onclick="changeLanguage('<?= $lang['code'] ?><?= $pathArg ?>')" class="flex-shrink-0 flex items-center gap-2 px-4 py-2 rounded-full border border-accents-2 bg-background hover:border-foreground transition-all text-sm font-medium snap-start shadow-sm">
                        <span class="fi fi-<?= $lang['flag'] ?> rounded-full shadow-sm"></span>
                        <span class="whitespace-nowrap"><?= $lang['name'] ?></span>
                    </button>
                    <?php } ?>
                </div>

                <?php if (isset($_SESSION['user_id'])) { ?>
                <div class="pt-2 border-t border-accents-2">
                    <a href="/logout" class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl bg-red-500/10 text-red-600 font-bold hover:bg-red-500/20 transition-all">
                        <i data-lucide="log-out" class="w-5 h-5 !text-black dark:!text-white" stroke-width="2.5"></i>
                        <span>Logout System</span>
                    </a>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</nav>
