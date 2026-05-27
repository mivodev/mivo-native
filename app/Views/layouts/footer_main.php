    <?php
use App\Config\SiteConfig;
use App\Core\Hooks;
use App\Helpers\FlashHelper;

if (isset($session) && ! empty($session)) { ?>
            </div> <!-- /.max-w-7xl (Sidebar content) -->
        </main>
    </div> <!-- /.flex-col (Main Content Wrapper) -->
</div> <!-- /.flex h-screen (Sidebar Layout Root) -->
    <?php } else { ?>
    </div> <!-- /.container (Navbar Global) -->
    
    <footer class="border-t border-accents-2 bg-background mt-auto transition-colors duration-200 py-8 text-center space-y-4">
        <!-- Links Row -->
        <div class="flex justify-center items-center gap-6 text-sm font-medium text-accents-5">
            <a href="https://mivodev.github.io" target="_blank" class="hover:text-foreground transition-colors flex items-center gap-2">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                <span>Docs</span>
            </a>
            <a href="https://github.com/mivodev/mivo/discussions" target="_blank" class="hover:text-foreground transition-colors flex items-center gap-2">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                <span>Community</span>
            </a>
            <a href="https://github.com/mivodev/mivo" target="_blank" class="hover:text-foreground transition-colors flex items-center gap-2">
                <i data-lucide="github" class="w-4 h-4"></i>
                <span>Repo</span>
            </a>
        </div>

        <!-- Copyright Row -->
        <div class="text-xs text-accents-4 opacity-50">
            <?= SiteConfig::getFooter() ?>
        </div>
    </footer>
    <?php } ?>

    <script>
        window.MIVO_VERSION = "<?= SiteConfig::APP_VERSION ?>";
    </script>
    <script src="/assets/js/modules/update-checker.js"></script>
    <script>
        // Global Theme Toggle Logic (Class-based for multiple instances)
        document.addEventListener('DOMContentLoaded', () => {
            const toggleButtons = document.querySelectorAll('.theme-toggle');
            
            // Function to update all icons based on current mode
            const updateIcons = (isDark) => {
                const darkIcons = document.querySelectorAll('.theme-toggle-dark-icon');
                const lightIcons = document.querySelectorAll('.theme-toggle-light-icon');
                
                if (isDark) {
                    darkIcons.forEach(el => el.classList.add('hidden'));
                    lightIcons.forEach(el => el.classList.remove('hidden'));
                } else {
                    darkIcons.forEach(el => el.classList.remove('hidden'));
                    lightIcons.forEach(el => el.classList.add('hidden'));
                }
            };

            // Initial Check
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                updateIcons(true);
            } else {
                updateIcons(false);
            }

            // Click Handlers
            toggleButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Update LocalStorage & HTML Class
                    if (localStorage.theme === 'dark') {
                        document.documentElement.classList.remove('dark');
                        localStorage.theme = 'light';
                        updateIcons(false);
                    } else {
                        document.documentElement.classList.add('dark');
                        localStorage.theme = 'dark';
                        updateIcons(true);
                    }
                });
            });

            // Sidebar Toggle Logic
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebar-overlay');
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const sidebarClose = document.getElementById('sidebar-close');

            if (sidebar && mobileMenuToggle) {
                const toggleSidebar = () => {
                   const isClosed = sidebar.classList.contains('-translate-x-full');
                   if (isClosed) {
                       // Open
                       sidebar.classList.remove('-translate-x-full');
                       sidebarOverlay.classList.remove('hidden');
                       // Small delay to allow display:block to apply before opacity transition
                       setTimeout(() => sidebarOverlay.classList.remove('opacity-0'), 10);
                   } else {
                       // Close
                       sidebar.classList.add('-translate-x-full');
                       sidebarOverlay.classList.add('opacity-0');
                       setTimeout(() => sidebarOverlay.classList.add('hidden'), 200);
                   }
                };

                mobileMenuToggle.addEventListener('click', toggleSidebar);
                if (sidebarClose) sidebarClose.addEventListener('click', toggleSidebar);
                if (sidebarOverlay) sidebarOverlay.addEventListener('click', toggleSidebar);
            }
            
            // Initialize Lucide Icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
        
        <?php if (FlashHelper::has()) { ?>
            <?php $flash = FlashHelper::get(); ?>
            document.addEventListener('DOMContentLoaded', () => {
                // Map Flash Type to Lucide Icon & Color Class
                const typeMap = {
                    'success': { icon: 'check-circle-2', color: 'text-success' },
                    'error':   { icon: 'x-circle', color: 'text-error' },
                    'warning': { icon: 'alert-triangle', color: 'text-warning' },
                    'info':    { icon: 'info', color: 'text-info' },
                    'question':{ icon: 'help-circle', color: 'text-question' }
                };

                const type = '<?= $flash['type'] ?>';
                const config = typeMap[type] || typeMap['info'];
                
                let title = '<?= addslashes($flash['title']) ?>';
                let message = '<?= addslashes($flash['message'] ?? '') ?>';
                const params = <?= json_encode($flash['params'] ?? []) ?>;
                const isTranslated = <?= $flash['isTranslated'] ? 'true' : 'false' ?>;
                
                const showFlash = () => {
                    if (isTranslated && window.i18n) {
                        title = window.i18n.t(title, params);
                        message = window.i18n.t(message, params);
                    }

                    // Use Toasts for all flash notifications
                    Mivo.toast(type, title, message);
                };

                if (window.i18n && window.i18n.ready) {
                    window.i18n.ready.then(showFlash);
                } else {
                    showFlash();
                }
            });
        <?php } ?>
    </script>
    <script>
        // Global Dropdown & Sidebar Logic
        let menuTimeout;

        function toggleMenu(menuId, button) {
            if (menuTimeout) clearTimeout(menuTimeout);
            
            const menu = document.getElementById(menuId);
            if (!menu) return;
            
            // Handle Dropdowns (IDs start with 'lang-', 'session-', or is 'notification-')
            if (menuId.startsWith('lang-') || menuId === 'session-dropdown' || menuId === 'notification-dropdown') {
                const sidebarHeader = document.getElementById('sidebar-header');
                const isOpening = menu.classList.contains('invisible');

                if (isOpening) {
                    // Smart Positioning Logic
                    // 1. Reset to base state (remove specific overrides to measure natural/preferred state)
                    // But we want to preserve 'absolute' etc. The HTML has 'left-1/2 -translate-x-1/2' by default for sidebar.
                    // We'll calculate based on button rect and assumed menu width (w-48 = 12rem = 192px approx, or measure)
                    
                    const btnRect = button.getBoundingClientRect();
                    const menuWidth = 192; // Approx w-48 standard. Better to measure if possible, but invisible elements have width.
                    // Actually, if we make it visible but opacity-0 first, we can measure.
                    // But simpler math:
                    const centerX = btnRect.left + (btnRect.width / 2);
                    const leftEdge = centerX - (menuWidth / 2);
                    const rightEdge = centerX + (menuWidth / 2);
                    
                    // Remove conflicting positioning classes first to ensure a clean slate if we need to override
                    menu.classList.remove('left-0', 'right-0', 'left-1/2', '-translate-x-1/2', 'origin-top-left', 'origin-top-right', 'origin-top', 'left-3');

                    // Decision Tree
                    if (leftEdge < 10) { 
                        // overflow left -> Align Left
                        menu.classList.add('left-0', 'origin-top-left');
                    } else if (rightEdge > window.innerWidth - 10) { 
                        // overflow right -> Align Right
                        menu.classList.add('right-0', 'origin-top-right');
                    } else {
                        // Safe to Center
                        menu.classList.add('left-1/2', '-translate-x-1/2', 'origin-top');
                    }
                    
                    // Open
                    menu.classList.remove('opacity-0', 'scale-95', 'invisible', 'pointer-events-none');
                    menu.classList.add('opacity-100', 'scale-100', 'visible', 'pointer-events-auto');
                    
                    // Special Case: Sidebar Lang Dropdown needs overflow visible on header
                    if (menuId === 'lang-dropdown-sidebar' && sidebarHeader) {
                        sidebarHeader.classList.remove('overflow-hidden');
                        sidebarHeader.classList.add('overflow-visible');
                    }
                } else {
                    // Close
                    menu.classList.add('opacity-0', 'scale-95', 'invisible', 'pointer-events-none');
                    menu.classList.remove('opacity-100', 'scale-100', 'visible', 'pointer-events-auto');
                    
                    // Revert Overflow
                    if (menuId === 'lang-dropdown-sidebar' && sidebarHeader) {
                        sidebarHeader.classList.add('overflow-hidden');
                        sidebarHeader.classList.remove('overflow-visible');
                    }
                }
                return;
            }

            // Handle Collapsible (Max-Height + Fade for Navbar)
            const isOpening = menu.style.maxHeight === '0px' || menu.style.maxHeight === '';
            const chevron = button.querySelector('[data-lucide="chevron-down"]');
            const burger = button.querySelector('[data-lucide="menu"]');
            
            if (isOpening) {
                menu.style.maxHeight = menu.scrollHeight + "px";
                if (chevron) chevron.classList.add('rotate-180');
                if (burger) burger.classList.add('rotate-90');
                
                if (menuId === 'mobile-navbar-menu') {
                    menu.classList.remove('opacity-0', 'invisible');
                    menu.classList.add('opacity-100', 'visible');
                }
            } else {
                menu.style.maxHeight = "0px";
                if (chevron) chevron.classList.remove('rotate-180');
                if (burger) burger.classList.remove('rotate-90');
                
                if (menuId === 'mobile-navbar-menu') {
                    menu.classList.add('opacity-0', 'invisible');
                    menu.classList.remove('opacity-100', 'visible');
                }
            }
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const dropdowns = document.querySelectorAll('[id^="lang-dropdown"], #session-dropdown, #notification-dropdown');
            dropdowns.forEach(dropdown => {
                const sidebarHeader = document.getElementById('sidebar-header');

                if (!dropdown.classList.contains('invisible')) {
                    const button = document.querySelector(`button[onclick*="'${dropdown.id}'"]`);
                    
                    if (!dropdown.contains(event.target) && (!button || !button.contains(event.target))) {
                         dropdown.classList.add('opacity-0', 'scale-95', 'invisible', 'pointer-events-none');
                         dropdown.classList.remove('opacity-100', 'scale-100', 'visible', 'pointer-events-auto');

                         // Revert Sidebar Overflow if needed
                         if (dropdown.id === 'lang-dropdown-sidebar' && sidebarHeader) {
                             sidebarHeader.classList.add('overflow-hidden');
                             sidebarHeader.classList.remove('overflow-visible');
                         }
                    }
                }
            });
        });

        // Helper for confirm actions
        async function confirmAction(url, message) {
            const title = message.includes('Reboot') ? 'Reboot Router?' : 'Shutdown Router?';
            const okText = message.includes('Reboot') ? 'Reboot' : 'Shutdown';
            
            const confirmed = await Mivo.confirm(title, message, okText, 'Cancel');
            if (!confirmed) return;

            try {
                const res = await fetch(url, { method: 'POST' });
                const data = await res.json();
                
                if (data.success) {
                    Mivo.toast('success', title.replace('?', ''), 'The command has been sent to the router.');
                } else {
                    Mivo.alert('error', 'Action Failed', data.error || 'Unknown error occurred.');
                }
            } catch (err) {
                Mivo.toast('error', 'Connection Error', 'Failed to reach the server.');
            }
        }

        // Auto-Close Helper with Debounce
        function closeMenu(menuId) {
            if (menuTimeout) clearTimeout(menuTimeout);
            
            // Notification dropdown is more "sticky" (800ms vs 300ms elsewhere)
            const delay = (menuId === 'notification-dropdown') ? 800 : 300;
            
            menuTimeout = setTimeout(() => {
                const menu = document.getElementById(menuId);
                const sidebarHeader = document.getElementById('sidebar-header');
                
                if (menu && !menu.classList.contains('invisible')) {
                    menu.classList.add('opacity-0', 'scale-95', 'invisible', 'pointer-events-none');
                    menu.classList.remove('opacity-100', 'scale-100', 'visible', 'pointer-events-auto');
                    
                    // Revert Overflow if needed
                    if (menuId === 'lang-dropdown-sidebar' && sidebarHeader) {
                        sidebarHeader.classList.add('overflow-hidden');
                        sidebarHeader.classList.remove('overflow-visible');
                    }
                }
            }, 300); // 300ms delay to prevent accidental closure
        }
    </script>
    <?php Hooks::doAction('mivo_footer'); ?>
</body>
</html>
