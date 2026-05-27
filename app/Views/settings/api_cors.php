<?php
$title = 'API CORS';
$no_main_container = true;
require_once ROOT.'/app/Views/layouts/header_main.php';
?>

<!-- Sub-Navbar Navigation -->
<?php include ROOT.'/app/Views/layouts/sidebar_settings.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow w-full flex flex-col">

    <div class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight" data-i18n="settings.api_cors_title">API CORS</h1>
        <p class="text-accents-5 mt-2" data-i18n="settings.api_cors_subtitle">Manage Cross-Origin Resource Sharing for API access.</p>
    </div>

    <!-- Content Area -->
    <div class="mt-8 flex-1 min-w-0" id="settings-content-area">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div class="hidden md:block">
                 <!-- Spacer -->
            </div>
            <div class="flex gap-2 w-full md:w-auto">
                <button onclick="openCorsModal()" class="btn btn-primary w-full md:w-auto">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i> <span data-i18n="settings.add_rule">Add CORS Rule</span>
                </button>
            </div>
        </div>

        <div class="table-container">
            <table class="table-glass" id="cors-table">
                <thead>
                    <tr>
                        <th data-i18n="settings.origin">Origin</th>
                        <th data-i18n="settings.methods">Allowed Methods</th>
                        <th data-i18n="settings.headers">Allowed Headers</th>
                        <th class="text-right" data-i18n="common.actions">Actions</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php if (! empty($rules)) { ?>
                        <?php foreach ($rules as $rule) { ?>
                        <tr class="table-row-item" 
                            data-rule-id="<?= $rule['id'] ?>"
                            data-origin="<?= htmlspecialchars($rule['origin']) ?>"
                            data-headers="<?= htmlspecialchars(implode(', ', $rule['headers_arr'])) ?>"
                            data-max-age="<?= $rule['max_age'] ?>"
                            data-methods='<?= json_encode($rule['methods_arr']) ?>'>
                            <td>
                                <div class="text-sm font-medium text-foreground"><?= htmlspecialchars($rule['origin']) ?></div>
                                <div class="text-xs text-accents-4">Max Age: <?= $rule['max_age'] ?>s</div>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    <?php foreach ($rule['methods_arr'] as $method) { ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400"><?= htmlspecialchars($method) ?></span>
                                    <?php } ?>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm text-accents-5 truncate max-w-[200px]"><?= htmlspecialchars(implode(', ', $rule['headers_arr'])) ?></div>
                            </td>
                            <td class="text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2 table-actions-reveal">
                                    <button onclick="openCorsModal(this.closest('tr'))" class="btn-icon" title="Edit">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </button>
                                    <form action="/settings/api-cors/delete" method="POST" onsubmit="event.preventDefault(); Mivo.confirm(window.i18n ? window.i18n.t('settings.cors_rule_deleted') : 'Delete CORS Rule?', 'Are you sure you want to delete the CORS rule for <?= htmlspecialchars($rule['origin']) ?>?', 'Delete', 'Cancel').then(res => { if(res) this.submit(); });" class="inline">
                                        <input type="hidden" name="id" value="<?= $rule['id'] ?>">
                                        <button type="submit" class="btn-icon-danger" title="Delete">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i data-lucide="shield" class="w-12 h-12 text-accents-2 mb-4"></i>
                                    <p class="text-accents-5">No CORS rules found. Add your first origin to allow external API access.</p>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

<script>
    async function openCorsModal(row = null) {
        const isEdit = !!row;
        const title = isEdit ? (window.i18n ? window.i18n.t('settings.edit_rule') : 'Edit CORS Rule') : (window.i18n ? window.i18n.t('settings.add_rule') : 'Add CORS Rule');
        const template = document.getElementById('cors-form-template').innerHTML;
        const saveBtn = window.i18n ? window.i18n.t('common.save') : 'Save';

        const preConfirmFn = () => {
            const form = document.getElementById('cors-form');
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }
            form.submit();
            return true;
        };

        const onOpenedFn = (popup) => {
            const form = popup.querySelector('#cors-form');
            if (isEdit) {
                form.action = '/settings/api-cors/update';
                form.querySelector('[name="id"]').value = row.dataset.ruleId;
                form.querySelector('[name="origin"]').value = row.dataset.origin;
                form.querySelector('[name="headers"]').value = row.dataset.headers;
                form.querySelector('[name="max_age"]').value = row.dataset.maxAge;
                
                const methods = JSON.parse(row.dataset.methods || '[]');
                form.querySelectorAll('[name="methods[]"]').forEach(cb => {
                    cb.checked = methods.includes(cb.value);
                });
            }
        };

        Mivo.modal.form(title, template, saveBtn, preConfirmFn, onOpenedFn);
    }
</script>

<template id="cors-form-template">
    <form action="/settings/api-cors/store" method="POST" id="cors-form" class="space-y-4 text-left">
        <input type="hidden" name="id">
        <div>
            <label class="form-label" data-i18n="settings.origin">Origin</label>
            <input type="text" name="origin" class="w-full" placeholder="https://example.com or *" required>
            <p class="text-[10px] text-orange-500 dark:text-orange-400 mt-1 font-medium">Use * for all origins (not recommended for production).</p>
        </div>
        <div>
            <label class="form-label" data-i18n="settings.methods">Allowed Methods</label>
            <div class="grid grid-cols-3 gap-2">
                <?php foreach (['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD'] as $m) { ?>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="methods[]" value="<?= $m ?>" class="checkbox" <?= in_array($m, ['GET', 'POST']) ? 'checked' : '' ?>>
                    <span class="text-sm font-medium"><?= $m ?></span>
                </label>
                <?php } ?>
            </div>
        </div>
        <div>
            <label class="form-label" data-i18n="settings.headers">Allowed Headers</label>
            <input type="text" name="headers" class="w-full" value="*" placeholder="Content-Type, Authorization, *">
        </div>
        <div>
            <label class="form-label" data-i18n="settings.max_age">Max Age (seconds)</label>
            <input type="number" name="max_age" class="w-full" value="3600">
        </div>
    </form>
</template>

<?php require_once ROOT.'/app/Views/layouts/footer_main.php'; ?>
