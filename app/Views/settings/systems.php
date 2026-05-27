<?php
$title = 'Settings';
$no_main_container = true;
require_once ROOT.'/app/Views/layouts/header_main.php';
?>

<!-- Sub-Navbar Navigation -->
<?php include ROOT.'/app/Views/layouts/sidebar_settings.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow w-full flex flex-col">

    <div class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight" data-i18n="settings.system">General Settings</h1>
        <p class="text-accents-5 mt-2" data-i18n="settings.system_desc">System-wide configurations and security.</p>
    </div>

    <!-- Content Area -->
    <div class="mt-8 flex-1 min-w-0" id="settings-content-area">
        <div class="space-y-8">
            
            <!-- Section Header (Removed redundant General) -->
            <div class="pb-5">
                    <h3 class="text-lg font-medium leading-6 text-foreground" data-i18n="settings.security">Security & Access</h3>
            </div>

            <!-- Admin Password -->
            <div class="card">
                    <form action="/settings/admin/update" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 w-full">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-foreground" data-i18n="settings.admin_username">Admin Username</label>
                            <div class="relative">
                                <input type="text" class="form-control w-full bg-accents-1 text-accents-5 cursor-not-allowed pl-10" value="<?= htmlspecialchars($username) ?>" readonly disabled>
                                <i data-lucide="lock" class="absolute left-3 top-2.5 h-4 w-4 text-accents-4"></i>
                            </div>
                                <p class="text-xs text-accents-4" data-i18n="settings.admin_username_desc">
                                <i class="inline-block w-3 h-3 mr-1 align-middle" data-lucide="info"></i>
                                For security reasons, the administrator username cannot be changed.
                            </p>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-foreground" data-i18n="settings.change_password">Change Password</label>
                                <div class="relative">
                                <input type="password" name="admin_password" class="form-control w-full pl-10" placeholder="Enter new password" data-i18n-placeholder="settings.new_password_placeholder">
                                <i data-lucide="key" class="absolute left-3 top-2.5 h-4 w-4 text-accents-4"></i>
                            </div>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-accents-2">
                        <button type="submit" class="btn btn-primary">
                            <span data-i18n="settings.update_password">Update Password</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Global Configuration -->
            <div class="card">
                    <form action="/settings/global/update" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 w-full">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-foreground" data-i18n="settings.quick_print_mode">Quick Print Mode</label>
                                <div class="relative">
                                    <select name="quick_print_mode" class="custom-select w-full">
                                    <option value="0" <?= ($settings['quick_print_mode'] ?? '0') == '0' ? 'selected' : '' ?> data-i18n="common.forms.disabled">Disabled</option>
                                    <option value="1" <?= ($settings['quick_print_mode'] ?? '0') == '1' ? 'selected' : '' ?> data-i18n="common.forms.enabled">Enabled</option>
                                    </select>
                                </div>
                                <p class="text-xs text-accents-4" data-i18n="settings.quick_print_mode_desc">Enable direct printing for voucher generation.</p>
                        </div>
                    </div>
        
                    <div class="pt-4 border-t border-accents-2 mt-6">
                        <button type="submit" class="btn btn-primary" data-i18n="settings.save_global">
                            Save Global Settings
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Data Management -->
            <div class="card">
                <div class="mb-6">
                    <h4 class="text-lg font-medium" data-i18n="settings.data_management">Data Management</h4>
                    <p class="text-sm text-accents-5" data-i18n="settings.data_management_desc">Backup or restore your application data.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Backup -->
                    <div class="p-4 rounded-lg bg-accents-1 border border-accents-2 flex flex-col h-full">
                            <div class="flex-1">
                                <h4 class="font-medium mb-2 text-sm" data-i18n="settings.backup_data">Backup Data</h4>
                                <p class="text-xs text-accents-5 mb-4" data-i18n="settings.backup_data_desc">Download a configuration file (.mivo) containing your database and settings.</p>
                            </div>
                            <a href="/settings/backup" class="btn btn-primary w-full justify-center text-sm mt-auto">
                            <i data-lucide="download" class="w-4 h-4 mr-2"></i> <span data-i18n="settings.download_backup">Download Backup</span>
                            </a>
                    </div>
                    
                    <!-- Restore -->
                        <div class="p-4 rounded-lg bg-accents-1 border border-accents-2 flex flex-col h-full">
                            <div class="flex-1">
                                <h4 class="font-medium mb-2 text-sm" data-i18n="settings.restore_data">Restore Data</h4>
                                <p class="text-xs text-accents-5 mb-4" data-i18n="settings.restore_data_desc">Upload a previously backup file (.mivo). <strong>Overwrites or adds to existing data.</strong></p>
                            </div>
                            <form action="/settings/restore" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-2 mt-auto">
                            <div class="w-full">
                                <input type="file" name="backup_file" accept=".mivo" class="form-control-file" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-full sm:w-auto mt-2 sm:mt-0" onclick="event.preventDefault(); Mivo.confirm(window.i18n ? window.i18n.t('settings.restore_data') : 'Restore Data?', window.i18n ? window.i18n.t('settings.warning_restore') : 'WARNING: This will restore settings from the file and may overwrite existing data. Continue?', window.i18n ? window.i18n.t('settings.restore') : 'Restore', window.i18n ? window.i18n.t('common.cancel') : 'Cancel').then(res => { if(res) this.closest('form').submit(); });">
                                <span data-i18n="settings.restore">Restore</span>
                            </button>
                            </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

<?php require_once ROOT.'/app/Views/layouts/footer_main.php'; ?>
