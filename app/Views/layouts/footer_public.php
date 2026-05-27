    <footer class="mt-auto py-8 text-center space-y-4">
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
            <?php

use App\Config\SiteConfig;
            use App\Core\Hooks;
            use App\Helpers\FlashHelper;

            ?><?= SiteConfig::getFooter() ?>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
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

                    // Use Custom Toasts for most notifications (Success, Info, Error)
                    // Only use Modal (Swal) for specific heavy warnings or questions if needed
                    // Use Toasts for standard notifications
                    if (['success', 'info', 'error', 'warning'].includes(type)) {
                        if (window.Mivo && window.Mivo.toast) {
                             Mivo.toast(type, title, message);
                        }
                    } else {
                        // For questions or other types, use Modal Alert
                        if (window.Mivo && window.Mivo.alert) {
                            Mivo.alert(type || 'info', title, message);
                        } else if (typeof Swal !== 'undefined') {
                            Swal.fire(title, message, type);
                        }
                    }
                };

                if (window.i18n && window.i18n.ready) {
                    window.i18n.ready.then(showFlash);
                } else {
                    showFlash();
                }
            });
        <?php } ?>
    </script>
    <?php Hooks::doAction('mivo_footer'); ?>
</body>
</html>
