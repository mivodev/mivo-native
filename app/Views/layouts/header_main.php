<?php

use App\Config\SiteConfig;
use App\Core\Hooks;

// Initialize variables to avoid undefined notices if not set
$hotspotname = isset($hotspotname) ? $hotspotname : SiteConfig::APP_NAME;
$themecolor = isset($themecolor) ? $themecolor : '#000000';
$theme = 'light'; // Default theme
$title = isset($title) ? $title : SiteConfig::APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title; ?></title>
    <meta name="theme-color" content="<?= $themecolor ?>" />
    
    <!-- Icons -->
    <link rel="icon" href="/assets/img/favicon.png" />
    
    <!-- Tailwind CSS (Local) -->
    <link rel="stylesheet" href="/assets/css/styles.css">
    
    <!-- Flag Icons (Local) -->
    <link rel="stylesheet" href="/assets/vendor/flag-icons/css/flag-icons.min.css" />

    
    <style>
        @font-face {
            font-family: 'Geist';
            src: url('/assets/fonts/Geist-Regular.woff2') format('woff2');
            font-weight: 400;
            font-style: normal;
        }
        @font-face {
            font-family: 'Geist';
            src: url('/assets/fonts/Geist-Bold.woff2') format('woff2');
            font-weight: 700;
            font-style: normal;
        }
        @font-face {
            font-family: 'Geist Mono';
            src: url('/assets/fonts/GeistMono-Regular.woff2') format('woff2');
            font-weight: 400;
            font-style: normal;
        }
    </style>

    <script>
        // Check local storage or system preference on load
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/lucide.min.js"></script>
    <script>
        window.currentVersion = '<?= SiteConfig::APP_VERSION ?>';
    </script>
    <script src="/assets/js/mivo.js" defer></script>
    <script src="/assets/js/modules/updater.js" defer></script>
    <script src="/assets/js/components/select.js" defer></script>
    <script src="/assets/js/components/datatable.js" defer></script>
    <script src="/assets/js/sweetalert2.all.min.js" defer></script>
    <script src="/assets/js/modules/alert.js" defer></script>
    <script src="/assets/js/modules/i18n.js" defer></script>
    
    <style>
        /* Global Form Input Style - Matches Vercel Design System */
        .form-input, .form-control {
            display: flex;
            align-items: center;
            height: 2.5rem; /* 10px */
            width: 100%;
            border-radius: 0.375rem; /* 6px */
            border: 1px solid var(--accents-2, #eaeaea);
            background-color: var(--background, #ffffff);
            padding-left: 0.75rem;
            padding-right: 0.75rem;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
            font-size: 0.875rem; /* 14px */
            line-height: 1.25rem;
            color: var(--foreground, #000);
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }

        /* Input with left icon spacing */
        .form-input.pl-10, .form-control.pl-10 {
            padding-left: 2.5rem;
        }

        .dark .form-input {
             background-color: #000; /* or darkest gray */
             border-color: #333;
             color: #fff;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--foreground);
            box-shadow: 0 0 0 1px var(--foreground);
        }

        .form-input::placeholder {
            color: var(--accents-4);
        }
        
        /* Fix for DataTables or other inputs without Left Icon */
        input.form-input:not([class*="pl-"]) {
             padding-left: 0.75rem;
        }

    </style>
    
    <?php Hooks::doAction('mivo_head'); ?>
</head>
<body class="flex flex-col min-h-screen bg-background text-foreground anti-aliased relative">
    <!-- Background Elements (Global Sci-Fi Grid) -->
    <div class="fixed inset-0 z-0 pointer-events-none">
        <!-- Subtle Grid Pattern -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMCwwLDAsMC4zKSIvPjwvc3ZnPg==')] dark:bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')] [mask-image:linear-gradient(to_bottom,white,transparent)]"></div>
        <div class="absolute -top-[20%] -left-[10%] w-[70vw] h-[70vw] rounded-full bg-blue-500/20 dark:bg-blue-500/5 blur-[120px] animate-pulse" style="animation-duration: 4s;"></div>
        <div class="absolute top-[30%] -right-[15%] w-[60vw] h-[60vw] rounded-full bg-purple-500/20 dark:bg-purple-500/5 blur-[100px] animate-pulse" style="animation-duration: 6s; animation-delay: 1s;"></div>
    </div>
    <?php
    if (isset($session) && ! empty($session)) {
        // Session Layout (Sidebar)
        include ROOT.'/app/Views/layouts/sidebar_session.php';
    } else {
        // Global Layout (Navbar)
        include ROOT.'/app/Views/layouts/navbar_main.php';
        if (! isset($no_main_container) || ! $no_main_container) {
            echo '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow w-full flex flex-col">';
        }
    }
?>
    

