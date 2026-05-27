<?php
$title = 'Logo Management';
$no_main_container = true;
require_once ROOT.'/app/Views/layouts/header_main.php';
?>

<!-- Sub-Navbar Navigation -->
<?php include ROOT.'/app/Views/layouts/sidebar_settings.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow w-full flex flex-col">

    <div class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight" data-i18n="settings.logos_title">Logo Management</h1>
        <p class="text-accents-5 mt-2" data-i18n="settings.logos_subtitle">Upload and manage logos for your hotspots and vouchers.</p>
    </div>

    <!-- Content Area -->
    <div class="mt-8 flex-1 min-w-0" id="settings-content-area">
            <div class="space-y-8">
            <!-- Section Header (Removed redundant Logos) -->

            <!-- Upload Section -->
            <section>
                <div class="card p-8 border-dashed border-2 bg-accents-1 hover:bg-background transition-colors text-center relative group">
                    <form action="/settings/logos/upload" method="POST" enctype="multipart/form-data" class="absolute inset-0 w-full h-full cursor-pointer z-50">
                        <input type="file" name="logo_file" accept=".png,.jpg,.jpeg,.svg,.gif" onchange="this.form.submit()" class="block w-full h-full opacity-0 cursor-pointer">
                    </form>
        
        <div class="flex flex-col items-center justify-center pointer-events-none">
            <div class="h-12 w-12 rounded-full bg-accents-2 flex items-center justify-center mb-4 group-hover:bg-primary group-hover:text-white transition-colors">
                <i data-lucide="upload-cloud" class="w-6 h-6"></i>
            </div>
            <h3 class="text-lg font-medium mb-1" data-i18n="settings.upload_new_logo">Upload New Logo</h3>
            <p class="text-sm text-accents-5" data-i18n="settings.drag_drop">Drag and drop or click to select file</p>
            <p class="text-xs text-accents-4 mt-2" data-i18n="settings.supports_formats">Supports PNG, JPG, SVG, GIF</p>
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section>
    <?php if (empty($logos)) { ?>
        <div class="text-center py-12">
            <p class="text-accents-5" data-i18n="settings.no_logos">No logos uploaded yet.</p>
        </div>
    <?php } else { ?>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
            <?php foreach ($logos as $logo) { ?>
            <div class="group relative card !p-0 overflow-hidden border border-accents-2 bg-background hover:shadow-md transition-all">
                <!-- Image Preview -->
                <div class="aspect-square flex items-center justify-center p-4 bg-accents-1 relative" style="background-image: linear-gradient(45deg, #e5e5e5 25%, transparent 25%), linear-gradient(-45deg, #e5e5e5 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #e5e5e5 75%), linear-gradient(-45deg, transparent 75%, #e5e5e5 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px;">
                    <img src="<?= $logo['path'] ?>" alt="<?= htmlspecialchars($logo['name']) ?>" class="max-w-full max-h-full object-contain">
                    
                    <!-- Overlay Actions -->
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
                            <span class="text-white font-mono text-lg font-bold bg-black/50 px-2 py-1 rounded"><?= $logo['id'] ?></span>
                            <div class="flex gap-2">
                            <button onclick="copyToClipboard('<?= $logo['id'] ?>')" class="p-2 bg-white text-black rounded hover:bg-accents-2 transition-colors" title="Copy ID">
                                <i data-lucide="hash" class="w-4 h-4"></i>
                            </button>
                            <form action="/settings/logos/delete" method="POST" class="delete-logo-form">
                                <input type="hidden" name="id" value="<?= $logo['id'] ?>">
                                <button type="submit" class="p-2 bg-red-500 text-white rounded hover:bg-red-600 transition-colors" title="Delete">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                            </div>
                    </div>
                </div>
                
                <!-- Info -->
                <div class="p-3 border-t border-accents-2">
                    <div class="flex items-center justify-between">
                        <code class="text-xs font-bold bg-accents-2 px-1 rounded"><?= $logo['id'] ?></code>
                        <span class="text-xs text-accents-5 uppercase"><?= $logo['type'] ?></span>
                    </div>
                    <p class="text-xs text-accents-5 mt-1 truncate" title="<?= htmlspecialchars($logo['name']) ?>"><?= htmlspecialchars($logo['name']) ?></p>
                    <div class="flex items-center justify-between mt-1 text-xs text-accents-4">
                        <span><?= $logo['formatted_size'] ?></span>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    <?php } ?>
</section>
        </div> <!-- End Space-y-8 -->
    </div> <!-- End Content Area -->

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                const title = window.i18n ? window.i18n.t('settings.id_copied') : 'ID Copied';
                const desc = window.i18n ? window.i18n.t('settings.logo_id_copied_desc', {id: text}) : `Logo ID <strong>${text}</strong> copied to clipboard.`;
                Mivo.alert('success', title, desc);
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Intercept Logo Deletion
            const deleteForms = document.querySelectorAll('.delete-logo-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const logoId = this.querySelector('input[name="id"]').value;
                    Mivo.confirm(
                        window.i18n ? window.i18n.t('settings.delete_logo_title') : 'Delete Logo?', 
                        window.i18n ? window.i18n.t('settings.delete_logo_confirm', {id: logoId}) : `Are you sure you want to delete logo <strong>${logoId}</strong>?`,
                        window.i18n ? window.i18n.t('common.delete') : 'Yes, Delete',
                        window.i18n ? window.i18n.t('common.cancel') : 'Cancel'
                    ).then((result) => {
                        if (result) {
                            this.submit();
                        }
                    });
                });
            });
        });
    </script>

<?php require_once ROOT.'/app/Views/layouts/footer_main.php'; ?>
