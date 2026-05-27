<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Error - MIVO</title>
    <!-- Tailwind CSS (Local) -->
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script src="/assets/js/lucide.min.js"></script>
    <style>
        /* Force dark mode if needed */
        @media (prefers-color-scheme: dark) {
            :root { color-scheme: dark; }
            body { background-color: #000; color: #fff; }
        }
        
        /* Critical: Reset potential global tag styles that might break layout */
        .dev-layout-header, .dev-layout-footer {
            position: relative !important;
            width: 100% !important;
            left: auto !important;
            right: auto !important;
            top: auto !important;
            bottom: auto !important;
            transform: none !important;
        }
        
        /* Ensure code block scrolls nicely */
        .custom-scrollbar::-webkit-scrollbar { height: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #0d1117; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #30363d; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #6e7681; }

        /* Manual Utilities (Polyfill for missing Tailwind classes) */
        .py-16 { padding-top: 4rem !important; padding-bottom: 4rem !important; }
        .space-y-16 > :not([hidden]) ~ :not([hidden]) { margin-top: 4rem !important; }
        .space-y-8 > :not([hidden]) ~ :not([hidden]) { margin-top: 2rem !important; }
    </style>
</head>
<body class="bg-background text-foreground antialiased min-h-screen flex flex-col font-sans selection:bg-red-500/30 overflow-x-hidden">
    
    <!-- Isolated Header -->
    <div class="dev-layout-header border-b border-accents-2 bg-background py-4 px-6 flex-none z-50">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-4">
                <!-- Branding -->
                <div class="flex items-center gap-2">
                    <div class="bg-foreground text-background font-black text-lg px-2 py-0.5 rounded leading-tight">
                        MIVO
                    </div>
                </div>
                
                <div class="h-5 w-px bg-accents-2"></div>
                
                <span class="text-sm font-bold text-red-600 dark:text-red-500 uppercase tracking-widest">
                    System Error
                </span>
            </div>
            
            <div class="flex items-center gap-2">
                <a href="/" class="p-2 rounded-full text-accents-5 hover:bg-accents-1 hover:text-foreground transition-colors focus:outline-none focus:ring-2 focus:ring-accents-2" aria-label="Return to Dashboard" title="Return to Dashboard">
                    <i data-lucide="home" class="w-4 h-4"></i>
                </a>
                <button onclick="location.reload()" class="p-2 rounded-full text-accents-5 hover:bg-accents-1 hover:text-foreground transition-colors focus:outline-none focus:ring-2 focus:ring-accents-2" aria-label="Reload Application" title="Reload Application">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                </button>
                <div class="w-px h-4 bg-accents-2 mx-1"></div>
                <button id="theme-toggle" class="p-2 rounded-full text-accents-5 hover:bg-accents-1 hover:text-foreground transition-colors focus:outline-none focus:ring-2 focus:ring-accents-2" aria-label="Toggle Dark Mode">
                    <i data-lucide="moon" class="w-4 h-4 hidden dark:block"></i>
                    <i data-lucide="sun" class="w-4 h-4 block dark:hidden"></i>
                </button>
                <div class="hidden sm:flex items-center gap-2 text-xs font-mono text-accents-5 bg-accents-1 px-3 py-1.5 rounded-full border border-accents-2">
                    <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                    DEV MODE
                </div>
            </div>
        </div>
    </div>

    <?php
    $className = get_class($exception);
    $message = $exception->getMessage();
    $file = $exception->getFile();
    $line = $exception->getLine();
    $trace = $exception->getTraceAsString();

    // Code Snippet Logic
    $snippet = [];
    if (file_exists($file)) {
        $lines = file($file);
        $start = max(0, $line - 6);
        $end = min(count($lines), $line + 5);

        for ($i = $start; $i < $end; $i++) {
            $snippet[$i + 1] = $lines[$i];
        }
    }
    ?>

    <!-- Main Content -->
    <div class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 flex flex-col justify-center">
        
        <div class="max-w-4xl mx-auto w-full space-y-16">
            
            <!-- Error Card -->
            <div class="card !border-red-500/30 !bg-red-50/50 dark:!bg-red-900/10 p-6 md:p-8 shadow-lg transition-all">
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center bg-red-100 dark:bg-red-900/40 rounded-xl text-red-600 dark:text-red-400 ring-1 ring-red-200 dark:ring-red-800/50 shadow-sm">
                            <i data-lucide="bomb" class="w-6 h-6 md:w-8 md:h-8"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-600 text-white uppercase tracking-widest shadow-sm">
                                FATAL EXCEPTION
                            </span>
                            <span class="text-xs font-mono text-accents-5">
                                <?= date('H:i:s') ?>
                            </span>
                        </div>
                        
                        <h2 class="text-sm font-bold text-red-600 dark:text-red-400 break-all font-mono mb-2">
                            <?= htmlspecialchars($className) ?>
                        </h2>
                        
                        <h1 class="text-2xl md:text-3xl font-extrabold text-foreground mb-6 leading-tight">
                            <?= htmlspecialchars($message) ?>
                        </h1>
                        
                        <div class="bg-background border border-accents-2 rounded-lg font-mono text-sm shadow-sm overflow-hidden mt-6">
                            <div class="bg-accents-1 px-4 py-2 border-b border-accents-2 flex justify-between items-center">
                                <span class="break-all text-accents-7 text-xs"><?= htmlspecialchars($file) ?></span>
                                <span class="text-xs font-bold bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 px-2 py-0.5 rounded">Line <?= $line ?></span>
                            </div>
                            <div class="p-0 overflow-x-auto bg-[#0d1117] text-gray-300">
                                <table class="w-full text-xs md:text-sm">
                                    <?php foreach ($snippet as $num => $code) { ?>
                                    <?php $isErrorLine = ($num == $line); ?>
                                    <tr class="<?= $isErrorLine ? 'bg-red-500/20' : '' ?>">
                                        <td class="text-right px-4 py-1 select-none text-gray-600 border-r border-[#30363d] w-12 bg-[#0d1117]"><?= $num ?></td>
                                        <td class="px-4 py-1 whitespace-pre break-normal font-mono <?= $isErrorLine ? 'text-white font-bold' : 'text-gray-300' ?>"><?= htmlspecialchars($code) ?></td>
                                    </tr>
                                    <?php } ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stack Trace -->
            <div class="space-y-8">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-sm font-semibold text-accents-5 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="activity" class="w-4 h-4"></i>
                        Stack Trace
                    </h3>
                    <button onclick="navigator.clipboard.writeText(document.getElementById('stacktrace').innerText); this.innerHTML = 'Copied!';" class="text-xs btn btn-sm btn-secondary h-8 px-4 transition-all">
                        Copy Trace
                    </button>
                </div>
                
                <div class="rounded-xl overflow-hidden border border-accents-2 shadow-inner bg-[#0d1117] relative group">
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                         <span class="text-[10px] text-gray-500 font-mono">PHP Stack Trace</span>
                    </div>
                    <pre id="stacktrace" class="p-4 text-xs font-mono leading-relaxed whitespace-pre-wrap text-gray-300 overflow-x-auto custom-scrollbar max-h-[500px]"><?= htmlspecialchars($trace) ?></pre>
                </div>
            </div>



        </div>

    </div>

    <!-- Isolated Footer -->
    <div class="dev-layout-footer border-t border-accents-2 bg-background py-6 text-center flex-none">
        <p class="text-sm text-accents-4 font-medium flex items-center justify-center gap-2">
            MIVO Debugger <span class="w-1 h-1 rounded-full bg-accents-3"></span> Environment: <span class="text-foreground font-semibold">Development</span>
        </p>
    </div>

    <script>
        lucide.createIcons();

        // Theme Toggle Logic
        const themeToggleBtn = document.getElementById('theme-toggle');
        const html = document.documentElement;

        // Check local storage or system preference
        const storedTheme = localStorage.getItem('theme');
        const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (storedTheme === 'dark' || (!storedTheme && systemDark)) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }

        // Toggle Event
        themeToggleBtn.addEventListener('click', () => {
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        });
    </script>
</body>
</html>
