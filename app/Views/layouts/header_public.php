<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php

use App\Core\Hooks;

?><?= $title ?? 'MIVO' ?></title>
    <!-- Tailwind CSS (Local) -->
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="/assets/js/lucide.min.js"></script>
    <script src="/assets/js/sweetalert2.all.min.js" defer></script>
    <script src="/assets/js/mivo.js" defer></script>
    <script src="/assets/js/modules/alert.js" defer></script>
    <script src="/assets/js/modules/i18n.js" defer></script>
    <style>
        /* Custom Keyframes */
        @keyframes fade-in-up {
            0% { opacity: 0; transform: translateY(10px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fade-in-up 0.4s ease-out forwards;
        }
    </style>
    <script>
        // Check local storage for theme
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <?php Hooks::doAction('mivo_head'); ?>
</head>
<body class="bg-background text-foreground antialiased min-h-screen relative overflow-hidden font-sans selection:bg-accents-2 selection:text-foreground flex flex-col">
    
    <!-- Background Elements (Common) -->
    <div class="absolute inset-0 z-0 pointer-events-none">
        <!-- Subtle Grid Pattern -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMCwwLDAsMC4zKSIvPjwvc3ZnPg==')] dark:bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')] [mask-image:linear-gradient(to_bottom,white,transparent)]"></div>
        <div class="absolute -top-[20%] -left-[10%] w-[70vw] h-[70vw] rounded-full bg-blue-500/20 dark:bg-blue-500/5 blur-[120px] animate-pulse" style="animation-duration: 4s;"></div>
        <div class="absolute top-[30%] -right-[15%] w-[60vw] h-[60vw] rounded-full bg-purple-500/20 dark:bg-purple-500/5 blur-[100px] animate-pulse" style="animation-duration: 6s; animation-delay: 1s;"></div>
    </div>

    <!-- Top Right Controls (Pill Theme Toggle & Lang Switcher) -->
    <div class="fixed top-4 right-4 z-50 flex items-center space-x-3">
         <!-- Language Switcher -->
         <div class="relative group">
            <button onclick="toggleMenu('lang-dropdown-public', this)" class="h-9 px-3 rounded-full bg-background/50 backdrop-blur-md border border-accents-2 hover:border-foreground/20 text-accents-5 hover:text-foreground transition-all flex items-center shadow-sm">
                <i data-lucide="globe" class="w-4 h-4 mr-2"></i>
                <span class="text-xs font-semibold uppercase tracking-wider" id="current-lang-label">EN</span>
                <i data-lucide="chevron-down" class="w-3 h-3 ml-2 opacity-50"></i>
            </button>
            <!-- Dropdown -->
            <div id="lang-dropdown-public" class="hidden absolute right-0 mt-2 w-32 bg-background/95 backdrop-blur-2xl border border-white/10 rounded-xl shadow-2xl py-1 z-50 transform origin-top-right transition-all duration-200" onmouseleave="closeMenu('lang-dropdown-public')">
                <button onclick="changeLanguage('en')" class="w-full text-left px-4 py-2 text-xs font-medium text-accents-5 hover:text-foreground hover:bg-white/5 flex items-center group">
                    <span class="mr-2 text-lg">🇺🇸</span> English
                </button>
                <button onclick="changeLanguage('id')" class="w-full text-left px-4 py-2 text-xs font-medium text-accents-5 hover:text-foreground hover:bg-white/5 flex items-center group">
                    <span class="mr-2 text-lg">🇮🇩</span> Indonesia
                </button>
            </div>
        </div>

        <!-- Theme Toggle Pill -->
        <div class="h-9 p-1 bg-accents-2/50 backdrop-blur-md border border-accents-2 rounded-full flex items-center relative" id="theme-pill">
            <!-- Gliding Background -->
            <div class="absolute top-1 bottom-1 w-[calc(50%-4px)] bg-background rounded-full shadow-sm transition-all duration-300 ease-spring" id="theme-glider" style="left: 4px;"></div>
            
            <button onclick="setTheme('light')" class="relative z-10 w-8 h-full flex items-center justify-center text-accents-5 hover:text-foreground transition-colors rounded-full" id="btn-light">
                <i data-lucide="sun" class="w-4 h-4"></i>
            </button>
            <button onclick="setTheme('dark')" class="relative z-10 w-8 h-full flex items-center justify-center text-accents-5 hover:text-foreground transition-colors rounded-full" id="btn-dark">
                <i data-lucide="moon" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <script>
        // Toggle Menu Helper (Reuse or define for public if main footer not loaded)
        // Public footer includes site config footer, but maybe not main JS.
        // Let's define simple toggle for public page to be safe and independent.
        function toggleMenu(id, btn) {
            const el = document.getElementById(id);
            if (!el) return;
            const isHidden = el.classList.contains('hidden');
            
            // Close others if needed (optional)
            
            if (isHidden) {
                el.classList.remove('hidden', 'scale-95', 'opacity-0');
                el.classList.add('scale-100', 'opacity-100');
            } else {
                closeMenu(id);
            }
        }

        function closeMenu(id) {
            const el = document.getElementById(id);
            if (el && !el.classList.contains('hidden')) {
                el.classList.remove('scale-100', 'opacity-100');
                el.classList.add('hidden', 'scale-95', 'opacity-0');
            }
        }
    
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();

            // Theme Logic
            const glider = document.getElementById('theme-glider');
            const btnLight = document.getElementById('btn-light');
            const btnDark = document.getElementById('btn-dark');
            const htmlElement = document.documentElement;

            window.setTheme = (theme) => {
                if (theme === 'dark') {
                    htmlElement.classList.add('dark');
                    localStorage.theme = 'dark';
                    glider.style.transform = 'translateX(100%)'; 
                    // adjustment: logic depends on width. 
                    // container is w-8+w-8+padding. 
                    // simplest is just left/right toggle classes or transform.
                    // using transform translateX(100%) works if width is exactly 50% parent minus padding.
                    // padding is 1 (4px). buttons are w-8 (32px).
                    // let's use explicit left style or class-based positioning if easier.
                    // Tailwind 'translate-x-full' moves 100% of own width.
                    // If glider is w-[calc(50%-4px)], moving 100% of itself is almost correct but includes gap.
                    // Let's rely on simple pixel math or percentage relative to parent?
                    // actually `left: 4px` vs `left: calc(100% - width - 4px)`.
                    glider.style.left = 'auto';
                    glider.style.right = '4px';
                } else {
                    htmlElement.classList.remove('dark');
                    localStorage.theme = 'light';
                    glider.style.right = 'auto';
                    glider.style.left = '4px';
                }
                
                // Update Active Colors
                if (theme === 'dark') {
                    btnDark.classList.add('text-foreground');
                    btnLight.classList.remove('text-foreground');
                } else {
                    btnLight.classList.add('text-foreground');
                    btnDark.classList.remove('text-foreground');
                }
            };

            // Init
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                setTheme('dark');
            } else {
                setTheme('light');
            }

            // Language Init (Mock)
            const currentLang = localStorage.getItem('mivo_lang') || 'en';
            const langLabel = document.getElementById('current-lang-label');
            if(langLabel) langLabel.innerText = currentLang.toUpperCase();
            
            window.changeLanguage = (lang) => {
                 localStorage.setItem('mivo_lang', lang);
                 // Reload or use i18n module to reload
                 location.reload(); 
            };
        });
    </script>
