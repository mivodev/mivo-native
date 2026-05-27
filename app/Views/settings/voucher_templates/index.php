<?php
$title = 'Voucher Templates';
$no_main_container = true;
require_once ROOT.'/app/Views/layouts/header_main.php';
?>

<!-- Sub-Navbar Navigation -->
<?php include ROOT.'/app/Views/layouts/sidebar_settings.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow w-full flex flex-col">

    <div class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight" data-i18n="settings.templates_title">Voucher Templates</h1>
        <p class="text-accents-5 mt-2" data-i18n="settings.templates_subtitle">Manage and customize your voucher print designs.</p>
    </div>

    <!-- Content Area -->
    <div class="mt-8 flex-1 min-w-0" id="settings-content-area">
             <div class="space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-accents-2 pb-5 gap-4">
                    <div class="hidden md:block">
                        <!-- Spacer -->
                    </div>
                    <a href="/settings/voucher-templates/add" class="btn btn-primary w-full sm:w-auto justify-center">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        <span data-i18n="settings.new_template">New Template</span>
                    </a>
                </div>

                <!-- Template List -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Default Template Card (Read Only) -->
                    <div class="border border-accents-2 rounded-xl overflow-hidden bg-background flex flex-col h-full">
                        <div class="aspect-video bg-accents-1 border-b border-accents-2 w-full h-full relative overflow-hidden flex items-center justify-center group">
                            <!-- Loading Overlay -->    
                            <div class="absolute inset-0 flex items-center justify-center bg-accents-1 z-10 transition-opacity duration-500 pointer-events-none input-overlay">
                                <i data-lucide="loader-2" class="w-6 h-6 animate-spin text-accents-4"></i>
                            </div>
                            <iframe 
                                data-src="/settings/voucher-templates/preview/default" 
                                src="about:blank"
                                class="w-full h-full border-0 pointer-events-none opacity-0 transition-opacity duration-500"
                                scrolling="no"
                            ></iframe>
                        </div>
                        <div class="p-4 flex flex-col flex-grow">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-bold text-foreground" data-i18n="settings.default_template">Default Template</h3>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-accents-2 text-foreground" data-i18n="settings.system_label">System</span>
                            </div>
                            <p class="text-sm text-accents-5 mb-4" data-i18n="settings.default_template_desc">Standard thermal printer friendly template.</p>
                            <button disabled class="w-full py-2 border border-accents-2 rounded text-accents-4 text-sm cursor-not-allowed mt-auto" data-i18n="settings.built_in">
                                Built-in
                            </button>
                        </div>
                    </div>

                    <?php if (! empty($templates)) { ?>
                        <?php foreach ($templates as $tpl) { ?>
                        <div class="border border-accents-2 rounded-xl overflow-hidden bg-background hover:shadow-sm transition-shadow flex flex-col h-full">
                <div class="aspect-video bg-white relative group overflow-hidden">
                    <?php if (! empty($tpl['content'])) { ?>
                        <div class="w-full h-full bg-accents-1 relative overflow-hidden flex items-center justify-center group">
                            <!-- Loading Overlay -->
                            <div class="absolute inset-0 flex items-center justify-center bg-accents-1 z-10 transition-opacity duration-500 pointer-events-none input-overlay">
                                <i data-lucide="loader-2" class="w-6 h-6 animate-spin text-accents-4"></i>
                            </div>
                             <iframe 
                                data-src="/settings/voucher-templates/preview/<?= $tpl['id'] ?>" 
                                src="about:blank"
                                class="w-full h-full border-0 pointer-events-none opacity-0 transition-opacity duration-500"
                                scrolling="no"
                            ></iframe>
                        </div>
                    <?php } else { ?>
                        <!-- Placeholder for Preview Thumb -->
                         <div class="absolute inset-0 flex items-center justify-center text-accents-3 bg-accents-1">
                             <i data-lucide="file-code" class="w-8 h-8 opacity-50"></i>
                        </div>
                    <?php } ?>
                </div>
                <div class="p-4 flex flex-col flex-grow">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-bold text-foreground"><?= htmlspecialchars($tpl['name']) ?></h3>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400" data-i18n="settings.custom_label">Custom</span>
                                </div>
                                <p class="text-sm text-accents-5 mb-4 line-clamp-1">Created: <?= htmlspecialchars($tpl['created_at']) ?></p>
                                
                                <div class="flex items-center gap-2 mt-auto">
                                    <a href="/settings/voucher-templates/edit/<?= $tpl['id'] ?>" class="flex-1 btn btn-primary flex justify-center">
                                        <i data-lucide="edit-3" class="w-4 h-4 mr-2"></i> <span data-i18n="common.edit">Edit</span>
                                    </a>
                                    <form action="/settings/voucher-templates/delete" method="POST" class="delete-template-form">
                                        <input type="hidden" name="id" value="<?= $tpl['id'] ?>">
                                        <input type="hidden" name="template_name" value="<?= htmlspecialchars($tpl['name']) ?>">
                                        <button type="submit" class="p-2 btn btn-secondary hover:text-red-600 hover:bg-red-50 transition-colors h-9 w-9 flex items-center justify-center">
                                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div> <!-- End Space-y-6 -->
        </div> <!-- End Content Area -->


<?php require_once ROOT.'/app/Views/layouts/footer_main.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Intercept Template Deletion
        const deleteForms = document.querySelectorAll('.delete-template-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const templateName = this.querySelector('input[name="template_name"]').value;
                Mivo.confirm(
                    window.i18n ? window.i18n.t('settings.delete_template_title') : 'Delete Template?', 
                    window.i18n ? window.i18n.t('settings.delete_template_confirm', {name: templateName}) : `Are you sure you want to delete <strong>${templateName}</strong>?`,
                    window.i18n ? window.i18n.t('common.delete') : 'Yes, Delete',
                    window.i18n ? window.i18n.t('common.cancel') : 'Cancel'
                ).then((result) => {
                    if (result) {
                        this.submit();
                    }
                });
            });
        });

        const queue = [];
        let activeRequests = 0;
        const CONCURRENCY_LIMIT = 3; // "Threads"

        const processQueue = () => {
            // Fill up to the limit
            while (activeRequests < CONCURRENCY_LIMIT && queue.length > 0) {
                const iframe = queue.shift();
                activeRequests++;
                
                // Set src to trigger load
                iframe.src = iframe.dataset.src;
                
                // On load (or error), fade in and process next slot
                const onComplete = () => {
                    iframe.classList.remove('opacity-0');
                    if(iframe.previousElementSibling && iframe.previousElementSibling.classList.contains('input-overlay')) {
                         iframe.previousElementSibling.classList.add('opacity-0');
                    }
                    activeRequests--;
                    setTimeout(processQueue, 50); // Small delay
                };

                iframe.onload = onComplete;
                iframe.onerror = onComplete;
            }
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const iframe = entry.target;
                    // Only queue if it hasn't started loading (src is blank) and isn't already queued
                    if (iframe.getAttribute('src') === 'about:blank' && !iframe.classList.contains('queued')) {
                        iframe.classList.add('queued');
                        queue.push(iframe);
                        processQueue();
                    }
                }
            });
        }, { rootMargin: '200px' }); // Preload ahead slightly

        document.querySelectorAll('iframe[data-src]').forEach(iframe => {
            observer.observe(iframe);
        });
    });
</script>


