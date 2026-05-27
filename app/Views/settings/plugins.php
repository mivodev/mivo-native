<?php
// Plugins View
$title = 'Plugins';
$no_main_container = true;
require_once ROOT.'/app/Views/layouts/header_main.php';
?>

<!-- Sub-Navbar Navigation -->
<?php include ROOT.'/app/Views/layouts/sidebar_settings.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow w-full flex flex-col">

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight" data-i18n="settings.plugins">Plugins</h1>
            <p class="text-accents-5 mt-2" data-i18n="settings.plugins_desc">Manage and extend functionality with plugins.</p>
        </div>
        <button onclick="openUploadModal()" class="btn btn-primary">
            <i data-lucide="upload" class="w-4 h-4 mr-2"></i>
            <span data-i18n="settings.upload_plugin">Upload Plugin</span>
        </button>
    </div>

    <!-- Content Area -->
    <div class="mt-8 flex-1 min-w-0" id="settings-content-area">
        
        <div class="card overflow-hidden p-0">
             <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-accents-5 uppercase bg-accents-1/50 border-b border-accents-2 font-semibold tracking-wider">
                        <tr>
                            <th class="px-6 py-4 w-[250px]" data-i18n="common.name">Name</th>
                            <th class="px-6 py-4" data-i18n="common.description">Description</th>
                            <th class="px-6 py-4 w-[100px]" data-i18n="common.version">Version</th>
                            <th class="px-6 py-4 w-[150px]" data-i18n="common.author">Author</th>
                            <th class="px-6 py-4 w-[100px] text-right" data-i18n="common.status">Status</th>
                            <th class="px-6 py-4 w-[100px] text-right" data-i18n="common.actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-accents-2">
                        <?php if (empty($plugins)) { ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-accents-5">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="p-3 rounded-full bg-accents-1">
                                        <i data-lucide="package-search" class="w-6 h-6 text-accents-4"></i>
                                    </div>
                                    <span class="font-medium" data-i18n="settings.no_plugins">No plugins installed</span>
                                    <span class="text-xs" data-i18n="settings.no_plugins_desc">Upload a .zip file to get started.</span>
                                </div>
                            </td>
                        </tr>
                        <?php } else { ?>
                            <?php foreach ($plugins as $plugin) { ?>
                            <tr class="group hover:bg-accents-1/30 transition-colors">
                                <td class="px-6 py-4 font-medium text-foreground">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded bg-primary/10 flex items-center justify-center text-primary">
                                            <i data-lucide="plug" class="w-4 h-4"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <span><?= htmlspecialchars($plugin['name']) ?></span>
                                            <span class="text-[10px] text-accents-4 font-normal font-mono"><?= htmlspecialchars($plugin['id']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-accents-6">
                                    <?= htmlspecialchars($plugin['description']) ?>
                                </td>
                                <td class="px-6 py-4 text-accents-6 font-mono text-xs">
                                    <?= htmlspecialchars($plugin['version']) ?>
                                </td>
                                <td class="px-6 py-4 text-accents-6">
                                    <?= htmlspecialchars($plugin['author']) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-500/10 text-green-600 dark:text-green-400">
                                        Active
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="/settings/plugins/delete" method="POST" class="inline" onsubmit="event.preventDefault(); 
                                        const title = window.i18n ? window.i18n.t('settings.delete_plugin') : 'Delete Plugin?';
                                        const msg = window.i18n ? window.i18n.t('settings.delete_plugin_confirm', {name: '<?= htmlspecialchars($plugin['name']) ?>'}) : 'Delete this plugin?';
                                        
                                        Mivo.confirm(title, msg, window.i18n ? window.i18n.t('common.delete') : 'Delete', window.i18n ? window.i18n.t('common.cancel') : 'Cancel').then(res => { 
                                            if(res) this.submit(); 
                                        });">
                                        <input type="hidden" name="plugin_id" value="<?= htmlspecialchars($plugin['id']) ?>">
                                        <button type="submit" class="btn-icon-danger" title="Delete">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
    function openUploadModal() {
        const title = window.i18n ? window.i18n.t('settings.upload_plugin') : 'Upload Plugin';
        const html = `
            <form id="upload-plugin-form" action="/settings/plugins/upload" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div class="text-sm text-accents-5">
                    <p class="mb-4" data-i18n="settings.upload_plugin_desc">Select a plugin .zip file to install.</p>
                    <input type="file" name="plugin_file" accept=".zip" required class="form-control-file w-full">
                </div>
            </form>
        `;

        Mivo.modal.form(title, html, window.i18n ? window.i18n.t('common.install') : 'Install', () => {
             const form = document.getElementById('upload-plugin-form');
             if (form.reportValidity()) {
                 form.submit();
                 return true;
             }
             return false;
        });
    }
</script>

<?php require_once ROOT.'/app/Views/layouts/footer_main.php'; ?>
