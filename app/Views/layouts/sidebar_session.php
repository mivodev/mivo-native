<?php

use App\Helpers\LanguageHelper;
use App\Models\Config;

// Determine active link state
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$isDashboard = strpos($uri, '/dashboard') !== false;
$isGenerate = strpos($uri, '/hotspot/generate') !== false;
$isTemplates = strpos($uri, '/settings/voucher-templates') !== false;
$isSettings = ($uri === '/settings' || strpos($uri, '/settings/') !== false) && ! $isTemplates;

// Hotspot Group Active Check
$hotspotPages = ['/hotspot/users', '/hotspot/profiles', '/hotspot/generate', '/hotspot/cookies'];
$isHotspotActive = false;
foreach ($hotspotPages as $page) {
    if (strpos($uri, $page) !== false) {
        $isHotspotActive = true;
        break;
    }
}

// Status Group Active Check
$statusPages = ['/hotspot/active', '/hotspot/hosts'];
$isStatusActive = false;
foreach ($statusPages as $page) {
    if (strpos($uri, $page) !== false) {
        $isStatusActive = true;
        break;
    }
}

// Security Group Active Check (Existing)
$securityPages = ['/hotspot/bindings', '/hotspot/walled-garden'];
$isSecurityActive = false;
foreach ($securityPages as $page) {
    if (strpos($uri, $page) !== false) {
        $isSecurityActive = true;
        break;
    }
}

// Reports Group Active Check
$reportsPages = ['/reports/resume', '/reports/selling', '/reports/user-log'];
$isReportsActive = false;
foreach ($reportsPages as $page) {
    if (strpos($uri, $page) !== false) {
        $isReportsActive = true;
        break;
    }
}

// Network Group Active Check
$networkPages = ['/network/dhcp'];
$isNetworkActive = false;
foreach ($networkPages as $page) {
    if (strpos($uri, $page) !== false) {
        $isNetworkActive = true;
        break;
    }
}

// System Group Active Check
$systemPages = ['/system/scheduler'];
$isSystemActive = false;
foreach ($systemPages as $page) {
    if (strpos($uri, $page) !== false) {
        $isSystemActive = true;
        break;
    }
}

// Fetch all sessions for the switcher
$configModel = new Config;
$allSessions = $configModel->getAllSessions();

// Find current session details to get Hotspot Name / IP
$currentSessionDetails = [];
foreach ($allSessions as $s) {
    if (isset($session) && $s['session_name'] === $session) {
        $currentSessionDetails = $s;
        break;
    }
}
// Determine label: Hotspot Name > IP Address > 'MIVO'
$sessionLabel = $currentSessionDetails['hotspot_name'] ?? $currentSessionDetails['ip_address'] ?? 'MIVO';
if (empty($sessionLabel)) {
    $sessionLabel = $currentSessionDetails['ip_address'] ?? 'MIVO';
}

// Helper for Session Initials (Kebab-friendly)
$getInitials = function ($name) {
    if (empty($name)) {
        return 'UN';
    }
    if (strpos($name, '-') !== false) {
        $parts = explode('-', $name);
        $initials = '';
        foreach ($parts as $part) {
            if (! empty($part)) {
                $initials .= substr($part, 0, 1);
            }
        }

        return strtoupper(substr($initials, 0, 2));
    }

    return strtoupper(substr($name, 0, 2));
};
?>
<div class="flex h-screen overflow-hidden">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden transition-opacity opacity-0"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="w-64 flex-shrink-0 border-r border-white/20 dark:border-white/10 bg-white/40 dark:bg-black/40 backdrop-blur-[40px] fixed md:static inset-y-0 left-0 z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-200 flex flex-col h-full">
        <!-- Sidebar Header -->
        <!-- Sidebar Header -->
        <div id="sidebar-header" class="group flex flex-col items-center py-5 border-b border-accents-2 flex-shrink-0 relative cursor-default overflow-hidden">
            <div class="relative w-full h-10 flex items-center justify-center">
                <!-- Brand (Slides out to the Left) -->
                <div class="flex items-center gap-2 font-bold text-2xl tracking-tighter transition-all duration-500 ease-in-out group-hover:-translate-x-full group-hover:opacity-0">
                    <img src="/assets/img/logo-m.svg" alt="MIVO Logo" class="h-10 w-auto block dark:hidden">
                    <img src="/assets/img/logo-m-dark.svg" alt="MIVO Logo" class="h-10 w-auto hidden dark:block">
                    <span>MIVO</span>
                </div>

                <!-- Premium Control Pill (Slides in from the Right to replace Brand) -->
                <div class="absolute inset-0 hidden md:flex items-center justify-center transition-all duration-500 ease-in-out translate-x-full opacity-0 group-hover:translate-x-0 group-hover:opacity-100 pointer-events-none group-hover:pointer-events-auto z-10">
                    <div class="control-pill scale-90 transition-transform hover:scale-100 shadow-lg bg-white/10 dark:bg-black/20 backdrop-blur-md">
                        <!-- Language Switcher -->
                        <!-- Language Switcher (Mivo Component) -->
                        <!-- Language Switcher -->
                        <div class="relative group/lang" onmouseleave="closeMenu('lang-dropdown-sidebar')">
                            <button type="button" class="pill-lang-btn" onclick="toggleMenu('lang-dropdown-sidebar', this)" title="Change Language">
                                <i data-lucide="languages" class="w-4 h-4 !text-black dark:!text-white" stroke-width="2.5"></i>
                            </button>
                            <div id="lang-dropdown-sidebar" class="absolute left-1/2 -translate-x-1/2 top-full mt-3 w-48 bg-background/95 backdrop-blur-2xl border border-accents-2 rounded-xl shadow-xl overflow-hidden transition-all duration-200 ease-out origin-top opacity-0 scale-95 invisible pointer-events-none z-50 dropdown-bridge" onmouseenter="if(typeof menuTimeout !== 'undefined') clearTimeout(menuTimeout)">
                                <div class="px-3 py-2 text-[10px] font-bold text-accents-4 uppercase tracking-widest border-b border-accents-2/50 bg-accents-1/50" data-i18n="sidebar.switch_language">Select Language</div>
                                <?php
                                $languages = LanguageHelper::getAvailableLanguages();
foreach ($languages as $lang) {
    ?>
                                <button onclick="Mivo.modules.I18n.loadLanguage('<?= $lang['code'] ?>')" class="w-full text-left flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-accents-1 transition-colors text-accents-6 hover:text-foreground group/lang-item">
                                    <span class="fi fi-<?= $lang['flag'] ?> rounded-sm shadow-sm transition-transform group-hover/lang-item:scale-110"></span>
                                    <span><?= $lang['name'] ?></span>
                                </button>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="pill-divider"></div>

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
                    </div>
                </div>
            </div>

            <!-- Mobile Close Button -->
            <button id="sidebar-close" class="md:hidden absolute top-4 right-4 text-accents-5 hover:text-foreground">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <!-- Sidebar Content -->
        <!-- Sidebar Content (RTL for left scrollbar) -->
        <div class="flex-1 overflow-y-auto" style="direction: rtl;">
            <div class="py-4 px-3 space-y-1" style="direction: ltr;">
            <!-- Session Switcher -->
            <div class="px-3 mb-6 relative" onmouseleave="closeMenu('session-dropdown')">
                <button type="button" class="w-full group grid grid-cols-[auto_1fr_auto] items-center gap-3 px-4 py-2.5 rounded-xl bg-white/50 dark:bg-white/5 border border-accents-2 dark:border-white/10 hover:bg-white/80 dark:hover:bg-white/10 transition-all decoration-0 overflow-hidden shadow-sm" onclick="toggleMenu('session-dropdown', this)">
                    <!-- Initials -->
                    <div class="h-8 w-8 rounded-lg bg-accents-2/50 group-hover:bg-accents-2 flex items-center justify-center text-xs font-bold text-accents-6 group-hover:text-foreground transition-colors flex-shrink-0">
                        <?= $getInitials($session ?? '') ?>
                    </div>

                    <!-- Text Info -->
                    <div class="flex flex-col text-left min-w-0">
                        <span class="text-xs font-bold text-accents-6 group-hover:text-foreground transition-colors leading-none truncate"><?= htmlspecialchars($session ?? 'Select Session') ?></span>
                        <span class="text-[10px] text-accents-4 leading-none mt-1 truncate" title="<?= htmlspecialchars($sessionLabel) ?>">
                            <?= htmlspecialchars($sessionLabel) ?>
                        </span>
                    </div>

                    <!-- Chevron Icon -->
                    <div class="h-8 w-8 flex-shrink-0 flex items-center justify-center rounded-lg bg-accents-2/50 group-hover:bg-accents-2 transition-colors">
                        <i data-lucide="chevrons-up-down" class="!w-4 !h-4 !text-accents-6 dark:!text-accents-6 transition-colors"></i>
                    </div>
                </button>

                <!-- Dropdown -->
                <div id="session-dropdown" class="absolute top-full left-3 w-[calc(100%-1.5rem)] z-50 mt-1 bg-background border border-accents-2 rounded-lg shadow-lg overflow-hidden transition-all duration-200 ease-out origin-top opacity-0 scale-95 invisible pointer-events-none dropdown-bridge" onmouseenter="if(typeof menuTimeout !== 'undefined') clearTimeout(menuTimeout)">
                    <div class="py-1 max-h-60 overflow-y-auto">
                        <div class="px-3 py-2 text-xs font-semibold text-accents-5 uppercase tracking-wider bg-accents-1/50 border-b border-accents-2" data-i18n="sidebar.switch_session">
                            Switch Session
                        </div>
                        <?php foreach ($allSessions as $s) { ?>
                        <a href="/<?= htmlspecialchars($s['session_name']) ?>/dashboard" class="flex items-center gap-3 px-3 py-2 text-sm hover:bg-accents-1 transition-colors group/item">
                            <div class="h-6 w-6 rounded flex-shrink-0 bg-accents-2 flex items-center justify-center text-[10px] font-bold">
                                 <?= $getInitials($s['session_name']) ?>
                            </div>
                            <div class="flex flex-col overflow-hidden">
                                <span class="truncate <?= ($session === $s['session_name']) ? 'font-medium text-foreground' : 'text-accents-5 group-hover/item:text-foreground' ?>">
                                    <?= htmlspecialchars($s['session_name']) ?>
                                </span>
                                <span class="text-[10px] text-accents-4 truncate">
                                    <?= htmlspecialchars($s['hotspot_name'] ?: $s['ip_address']) ?>
                                </span>
                            </div>
                             <?php if ($session === $s['session_name']) { ?>
                                <i data-lucide="check" class="w-3 h-3 ml-auto text-primary"></i>
                            <?php } ?>
                        </a>
                        <?php } ?>
                    </div>
                    <div class="border-t border-accents-2 p-1 bg-accents-1/30">
                         <a href="/settings/add" class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-accents-2 rounded-md transition-colors text-accents-5 hover:text-foreground">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            <span data-i18n="settings.add_router">Connect Router</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Dashboard -->
            <a href="/<?= htmlspecialchars($session) ?>/dashboard" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors <?= $isDashboard ? 'bg-white/40 dark:bg-white/5 shadow-sm text-foreground ring-1 ring-white/10' : 'text-accents-6 hover:text-foreground hover:bg-white/5' ?>">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                <span data-i18n="sidebar.dashboard">Dashboard</span>
            </a>

            <!-- Quick Print -->
             <a href="/<?= htmlspecialchars($session) ?>/quick-print" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors <?= (strpos($uri, '/quick-print') !== false) ? 'bg-white/40 dark:bg-white/5 shadow-sm text-foreground ring-1 ring-white/10' : 'text-accents-6 hover:text-foreground hover:bg-white/5' ?>">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span data-i18n="sidebar.quick_print">Quick Print</span>
            </a>

            <!-- Hotspots Separator -->
            <div class="pt-4 pb-1 px-3">
                <div class="text-xs font-semibold text-accents-5 uppercase tracking-wider" data-i18n="sidebar.hotspot">Hotspots</div>
            </div>

            <!-- Hotspot Group (Collapsible) -->
            <div class="space-y-1">
                <button type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium transition-colors text-accents-5 hover:text-foreground hover:bg-accents-2/50 group" onclick="toggleMenu('hotspot-menu', this)">
                    <div class="flex items-center gap-3">
                        <i data-lucide="wifi" class="w-4 h-4"></i>
                        <span data-i18n="sidebar.hotspot">Hotspot</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300 <?= $isHotspotActive ? 'rotate-180' : '' ?>"></i>
                </button>
                
                <div id="hotspot-menu" class="space-y-1 pl-9 overflow-hidden transition-[max-height] duration-300 ease-in-out" style="max-height: <?= $isHotspotActive ? '500px' : '0px' ?>">
                    <a href="/<?= htmlspecialchars($session) ?>/hotspot/users" class="block px-3 py-2 rounded-md text-sm transition-colors <?= (strpos($uri, '/hotspot/users') !== false) ? 'bg-white/40 dark:bg-white/5 text-foreground ring-1 ring-white/10 font-medium' : 'text-accents-6 hover:text-foreground' ?>">
                        <span data-i18n="hotspot_menu.users">Users</span>
                    </a>
                    <a href="/<?= htmlspecialchars($session) ?>/hotspot/profiles" class="block px-3 py-2 rounded-md text-sm transition-colors <?= (strpos($uri, '/hotspot/profile') !== false) ? 'bg-white/40 dark:bg-white/5 text-foreground ring-1 ring-white/10 font-medium' : 'text-accents-6 hover:text-foreground' ?>">
                        <span data-i18n="hotspot_menu.profiles">User Profiles</span>
                    </a>
                    <a href="/<?= htmlspecialchars($session) ?>/hotspot/generate" class="block px-3 py-2 rounded-md text-sm transition-colors <?= (strpos($uri, '/hotspot/generate') !== false) ? 'bg-white/40 dark:bg-white/5 text-foreground ring-1 ring-white/10 font-medium' : 'text-accents-6 hover:text-foreground' ?>">
                        <span data-i18n="hotspot_menu.generate">Generate</span>
                    </a>
                    <a href="/<?= htmlspecialchars($session) ?>/hotspot/cookies" class="block px-3 py-2 rounded-md text-sm transition-colors <?= (strpos($uri, '/hotspot/cookies') !== false) ? 'bg-white/40 dark:bg-white/5 text-foreground ring-1 ring-white/10 font-medium' : 'text-accents-6 hover:text-foreground' ?>">
                        <span data-i18n="hotspot_menu.cookies">Cookies</span>
                    </a>
                </div>
            </div>

            <!-- Status Group (Collapsible) -->
             <div class="space-y-1">
                <button type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium transition-colors text-accents-5 hover:text-foreground hover:bg-accents-2/50 group" onclick="toggleMenu('status-menu', this)">
                    <div class="flex items-center gap-3">
                        <i data-lucide="activity" class="w-4 h-4"></i>
                        <span data-i18n="sidebar.status">Status</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300 <?= $isStatusActive ? 'rotate-180' : '' ?>"></i>
                </button>
                
                <div id="status-menu" class="space-y-1 pl-9 overflow-hidden transition-[max-height] duration-300 ease-in-out" style="max-height: <?= $isStatusActive ? '500px' : '0px' ?>">
                    <a href="/<?= htmlspecialchars($session) ?>/hotspot/active" class="block px-3 py-2 rounded-md text-sm transition-colors <?= (strpos($uri, '/hotspot/active') !== false) ? 'bg-white/40 dark:bg-white/5 text-foreground ring-1 ring-white/10 font-medium' : 'text-accents-6 hover:text-foreground' ?>">
                        <span data-i18n="hotspot_menu.active">Active</span>
                    </a>
                    <a href="/<?= htmlspecialchars($session) ?>/hotspot/hosts" class="block px-3 py-2 rounded-md text-sm transition-colors <?= (strpos($uri, '/hotspot/hosts') !== false) ? 'bg-white/40 dark:bg-white/5 text-foreground ring-1 ring-white/10 font-medium' : 'text-accents-6 hover:text-foreground' ?>">
                        <span data-i18n="hotspot_menu.hosts">Hosts</span>
                    </a>
                </div>
            </div>

             <!-- Security Group (Collapsible) -->
             <div class="space-y-1">
                <button type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium transition-colors text-accents-5 hover:text-foreground hover:bg-accents-2/50 group" onclick="toggleMenu('security-menu', this)">
                    <div class="flex items-center gap-3">
                        <i data-lucide="shield" class="w-4 h-4"></i>
                        <span data-i18n="sidebar.security">Security</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300 <?= $isSecurityActive ? 'rotate-180' : '' ?>"></i>
                </button>
                
                <div id="security-menu" class="space-y-1 pl-9 overflow-hidden transition-[max-height] duration-300 ease-in-out" style="max-height: <?= $isSecurityActive ? '500px' : '0px' ?>">
                    <a href="/<?= htmlspecialchars($session) ?>/hotspot/bindings" class="block px-3 py-2 rounded-md text-sm transition-colors <?= (strpos($uri, '/hotspot/bindings') !== false) ? 'bg-white/40 dark:bg-white/5 text-foreground ring-1 ring-white/10 font-medium' : 'text-accents-6 hover:text-foreground' ?>">
                        <span data-i18n="hotspot_menu.bindings">IP Bindings</span>
                    </a>
                    <a href="/<?= htmlspecialchars($session) ?>/hotspot/walled-garden" class="block px-3 py-2 rounded-md text-sm transition-colors <?= (strpos($uri, '/hotspot/walled-garden') !== false) ? 'bg-white/40 dark:bg-white/5 text-foreground ring-1 ring-white/10 font-medium' : 'text-accents-6 hover:text-foreground' ?>">
                        <span data-i18n="hotspot_menu.walled_garden">Walled Garden</span>
                    </a>
                </div>
            </div>


            <!-- Reports Group -->
             <div class="space-y-1">
                <button type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium transition-colors text-accents-5 hover:text-foreground hover:bg-accents-2/50 group" onclick="toggleMenu('reports-menu', this)">
                    <div class="flex items-center gap-3">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        <span data-i18n="sidebar.reports">Reports</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300 <?= $isReportsActive ? 'rotate-180' : '' ?>"></i>
                </button>
                
                <div id="reports-menu" class="space-y-1 pl-9 overflow-hidden transition-[max-height] duration-300 ease-in-out" style="max-height: <?= $isReportsActive ? '500px' : '0px' ?>">
                    <a href="/<?= htmlspecialchars($session) ?>/reports/resume" class="block px-3 py-2 rounded-md text-sm transition-colors <?= (strpos($uri, '/reports/resume') !== false) ? 'bg-white/40 dark:bg-white/5 text-foreground ring-1 ring-white/10 font-medium' : 'text-accents-6 hover:text-foreground' ?>">
                        <span data-i18n="reports_menu.resume">Resume</span>
                    </a>
                    <a href="/<?= htmlspecialchars($session) ?>/reports/selling" class="block px-3 py-2 rounded-md text-sm transition-colors <?= (strpos($uri, '/reports/selling') !== false) ? 'bg-white/40 dark:bg-white/5 text-foreground ring-1 ring-white/10 font-medium' : 'text-accents-6 hover:text-foreground' ?>">
                        <span data-i18n="reports_menu.selling">Selling Report</span>
                    </a>
                    <a href="/<?= htmlspecialchars($session) ?>/reports/user-log" class="block px-3 py-2 rounded-md text-sm transition-colors <?= (strpos($uri, '/reports/user-log') !== false) ? 'bg-white/40 dark:bg-white/5 text-foreground ring-1 ring-white/10 font-medium' : 'text-accents-6 hover:text-foreground' ?>">
                        <span data-i18n="reports_menu.user_log">User Log</span>
                    </a>
                </div>
            </div>

            <!-- Network Group -->
             <div class="space-y-1">
                <button type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium transition-colors text-accents-5 hover:text-foreground hover:bg-accents-2/50 group" onclick="toggleMenu('network-menu', this)">
                    <div class="flex items-center gap-3">
                        <i data-lucide="network" class="w-4 h-4"></i>
                        <span data-i18n="sidebar.network">Network</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300 <?= $isNetworkActive ? 'rotate-180' : '' ?>"></i>
                </button>
                
                <div id="network-menu" class="space-y-1 pl-9 overflow-hidden transition-[max-height] duration-300 ease-in-out" style="max-height: <?= $isNetworkActive ? '500px' : '0px' ?>">
                    <a href="/<?= htmlspecialchars($session) ?>/network/dhcp" class="block px-3 py-2 rounded-md text-sm transition-colors <?= (strpos($uri, '/network/dhcp') !== false) ? 'bg-white/40 dark:bg-white/5 text-foreground ring-1 ring-white/10 font-medium' : 'text-accents-6 hover:text-foreground' ?>">
                        <span data-i18n="network_menu.dhcp">DHCP Leases</span>
                    </a>
                </div>
            </div>
            
            <!-- System Group -->
             <div class="space-y-1">
                <button type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium transition-colors text-accents-5 hover:text-foreground hover:bg-accents-2/50 group" onclick="toggleMenu('system-menu', this)">
                    <div class="flex items-center gap-3">
                        <i data-lucide="cpu" class="w-4 h-4"></i>
                        <span data-i18n="sidebar.system">System</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300 <?= $isSystemActive ? 'rotate-180' : '' ?>"></i>
                </button>
                
                <div id="system-menu" class="space-y-1 pl-9 overflow-hidden transition-[max-height] duration-300 ease-in-out" style="max-height: <?= $isSystemActive ? '500px' : '0px' ?>">
                     <a href="/<?= htmlspecialchars($session) ?>/system/scheduler" class="block px-3 py-2 rounded-md text-sm transition-colors <?= (strpos($uri, '/system/scheduler') !== false) ? 'bg-white/40 dark:bg-white/5 text-foreground ring-1 ring-white/10 font-medium' : 'text-accents-6 hover:text-foreground' ?>">
                        <span data-i18n="system_menu.scheduler">Scheduler</span>
                    </a>
                    <button onclick="confirmAction('/<?= htmlspecialchars($session) ?>/system/reboot', 'Reboot Router?')" class="w-full text-left block px-3 py-2 rounded-md text-sm text-accents-5 hover:text-red-500 transition-colors">
                        <span data-i18n="system_menu.reboot">Reboot</span>
                    </button>
                    <button onclick="confirmAction('/<?= htmlspecialchars($session) ?>/system/shutdown', 'Shutdown Router?')" class="w-full text-left block px-3 py-2 rounded-md text-sm text-accents-5 hover:text-red-500 transition-colors">
                        <span data-i18n="system_menu.shutdown">Shutdown</span>
                    </button>
                </div>
            </div>

            <!-- Systems Separator -->
            <div class="pt-4 pb-1 px-3">
                <div class="text-xs font-semibold text-accents-5 uppercase tracking-wider" data-i18n="sidebar.system">Systems</div>
            </div>

            <!-- Settings -->
            <a href="/settings" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors <?= $isSettings ? 'bg-white/40 dark:bg-white/5 shadow-sm text-foreground ring-1 ring-white/10' : 'text-accents-6 hover:text-foreground hover:bg-white/5' ?>">
                 <i data-lucide="settings" class="w-4 h-4"></i>
                 <span data-i18n="sidebar.settings">Settings</span>
            </a>

            <!-- Voucher Templates -->
            <a href="/settings/voucher-templates" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors <?= $isTemplates ? 'bg-white/40 dark:bg-white/5 shadow-sm text-foreground ring-1 ring-white/10' : 'text-accents-6 hover:text-foreground hover:bg-white/5' ?>">
                 <i data-lucide="file-code" class="w-4 h-4"></i>
                 <span data-i18n="sidebar.templates">Templates</span>
            </a>

            <!-- Support Separator -->
            <div class="pt-4 pb-1 px-3">
                <div class="text-xs font-semibold text-accents-5 uppercase tracking-wider" data-i18n="sidebar.support">Support</div>
            </div>

            <!-- Docs -->
            <a href="https://mivodev.github.io" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors text-accents-6 hover:text-foreground hover:bg-white/5">
                 <i data-lucide="book-open" class="w-4 h-4"></i>
                 <span data-i18n="sidebar.docs">Documentation</span>
                 <i data-lucide="external-link" class="w-3 h-3 ml-auto opacity-50"></i>
            </a>

            <!-- Community -->
            <a href="https://github.com/mivodev/mivo/discussions" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors text-accents-6 hover:text-foreground hover:bg-white/5">
                 <i data-lucide="message-circle" class="w-4 h-4"></i>
                 <span data-i18n="sidebar.community">Community</span>
                 <i data-lucide="external-link" class="w-3 h-3 ml-auto opacity-50"></i>
            </a>

            <!-- Repo -->
            <a href="https://github.com/mivodev/mivo" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition-colors text-accents-6 hover:text-foreground hover:bg-white/5">
                 <i data-lucide="github" class="w-4 h-4"></i>
                 <span data-i18n="sidebar.repo">Repository</span>
                 <i data-lucide="external-link" class="w-3 h-3 ml-auto opacity-50"></i>
            </a>

        </div>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-white/10 space-y-3">
             <!-- Disconnect (Session) -->
             <a href="/" class="group flex items-center justify-between px-3 py-2.5 rounded-xl bg-white/50 dark:bg-white/5 border border-accents-2 dark:border-white/10 hover:bg-white/80 dark:hover:bg-white/10 transition-all decoration-0 shadow-sm" title="Disconnect Session">
                <div class="flex items-center gap-3">
                    <div class="p-1.5 rounded-lg bg-accents-2/50 group-hover:bg-accents-2 transition-colors">
                         <i data-lucide="cast" class="!w-4 !h-4 !text-black dark:!text-white !flex-shrink-0 transition-colors"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-accents-6 group-hover:text-foreground transition-colors leading-none" data-i18n="sidebar.disconnect">Disconnect</span>
                        <span class="text-[10px] text-accents-4 leading-none mt-1">Exit Session</span>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="!w-4 !h-4 !text-black dark:!text-white !flex-shrink-0 transition-colors"></i>
            </a>
            
            <?php if (isset($_SESSION['user_id'])) { ?>
            <!-- Logout (System) -->
             <a href="/logout" class="group flex items-center justify-between px-3 py-2.5 rounded-xl bg-white/50 dark:bg-white/5 border border-accents-2 dark:border-white/10 hover:bg-red-500/10 hover:border-red-500/20 transition-all decoration-0 shadow-sm" title="Logout from Mivo">
                <div class="flex items-center gap-3">
                    <div class="p-1.5 rounded-lg bg-red-500/10 text-red-500 group-hover:bg-red-500/20 transition-colors">
                         <i data-lucide="log-out" class="w-4 h-4"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-accents-6 group-hover:text-red-500 transition-colors leading-none" data-i18n="sidebar.logout">Logout</span>
                        <span class="text-[10px] text-accents-4 group-hover:text-red-400/80 leading-none mt-1">Sign Out</span>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="!w-4 !h-4 !text-black dark:!text-white !flex-shrink-0 group-hover:!text-red-500 transition-colors"></i>
            </a>
            <?php } ?>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col overflow-hidden w-full">
        <!-- Mobile Header (Visible only on small screens) -->
        <header class="h-16 flex items-center justify-between px-4 border-b border-accents-2 bg-background/80 backdrop-blur-md md:hidden z-20 sticky top-0">
             <div class="flex items-center gap-2">
                <img src="/assets/img/logo-m.svg" class="h-6 w-auto block dark:hidden">
                <img src="/assets/img/logo-m-dark.svg" class="h-6 w-auto hidden dark:block">
                <span class="font-bold">MIVO</span>
            </div>
            <div class="flex items-center gap-4">
                <!-- Mobile Premium Control Pill -->
                <div class="control-pill scale-90 origin-right transition-transform hover:scale-95">
                    <!-- Language Switcher -->
                    <div class="relative group">
                        <button type="button" class="pill-lang-btn" onclick="toggleMenu('lang-dropdown-mobile', this)" title="Change Language">
                             <i data-lucide="languages" class="w-4 h-4"></i>
                        </button>
                         <div id="lang-dropdown-mobile" class="absolute right-0 top-full mt-3 w-48 bg-background/90 backdrop-blur-xl border border-accents-2 rounded-xl shadow-xl overflow-hidden transition-all duration-200 ease-out origin-top-right opacity-0 scale-95 invisible pointer-events-none z-50 dropdown-bridge" onmouseenter="if(typeof menuTimeout !== 'undefined') clearTimeout(menuTimeout)">
                            <div class="px-3 py-2 text-[10px] font-bold text-accents-4 uppercase tracking-widest border-b border-accents-2/50 bg-accents-1/50" data-i18n="sidebar.switch_language">Select Language</div>
                            <?php
                            $languages = LanguageHelper::getAvailableLanguages();
foreach ($languages as $lang) {
    ?>
                            <button onclick="changeLanguage('<?= $lang['code'] ?>')" class="w-full text-left flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-accents-1 transition-colors text-accents-6 hover:text-foreground group/lang">
                                <span class="fi fi-<?= $lang['flag'] ?> rounded-sm shadow-sm transition-transform group-hover/lang:scale-110"></span>
                                <span><?= $lang['name'] ?></span>
                            </button>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="pill-divider"></div>

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
                </div>
                 <button id="mobile-menu-toggle" class="text-accents-5 hover:text-foreground">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                 </button>
            </div>
        </header>

        <!-- Scrollable Page Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-background p-4 md:p-8">
            <div class="max-w-7xl mx-auto">


