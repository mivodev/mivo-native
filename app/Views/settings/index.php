<?php
$title = 'Settings';
$no_main_container = true;
require_once ROOT.'/app/Views/layouts/header_main.php';
?>

<!-- Sub-Navbar Navigation -->
<?php include ROOT.'/app/Views/layouts/sidebar_settings.php'; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow w-full flex flex-col">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Router Sessions</h1>
            <p class="text-accents-5 mt-2">Manage your stored MikroTik connections.</p>
        </div>
    </div>

    <!-- Content Area -->
    <div class="mt-8 flex-1 min-w-0" id="settings-content-area">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
                <div class="hidden md:block">
                     <!-- Spacer or Breadcrumbs if needed -->
                </div>
                <button onclick="openRouterModal('add')" class="btn btn-primary w-full md:w-auto">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i> <span data-i18n="routers.add_router_title">Add Router</span>
                </button>
            </div>

            <?php if (empty($routers)) { ?>
                <div class="card flex flex-col items-center justify-center py-16 text-center border-dashed">
                    <div class="rounded-full bg-accents-1 p-4 mb-4">
                        <i data-lucide="server-off" class="w-8 h-8 text-accents-4"></i>
                    </div>
                    <h3 class="text-lg font-medium mb-2">No routers configured</h3>
                    <p class="text-accents-5 mb-6 max-w-sm mx-auto">Connect your first MikroTik router to start managing hotspots and vouchers.</p>
                    <button onclick="openRouterModal('add')" class="btn btn-primary">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> <span data-i18n="routers.add_router_title">Connect Router</span>
                    </button>
                </div>
            <?php } else { ?>
                <div class="table-container">
                    <table class="table-glass">
                        <thead>
                            <tr>
                                <th scope="col">Session Name</th>
                                <th scope="col">Hotspot Name</th>
                                <th scope="col">IP Address</th>
                                <th scope="col" class="relative text-right">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($routers as $router) { ?>
                            <tr class="router-row"
                                data-id="<?= $router['id'] ?>"
                                data-sessname="<?= htmlspecialchars($router['session_name']) ?>"
                                data-ipmik="<?= htmlspecialchars($router['ip_address']) ?>"
                                data-usermik="<?= htmlspecialchars($router['username']) ?>"
                                data-hotspotname="<?= htmlspecialchars($router['hotspot_name']) ?>"
                                data-dnsname="<?= htmlspecialchars($router['dns_name']) ?>"
                                data-iface="<?= htmlspecialchars($router['interface'] ?? 'ether1') ?>"
                                data-currency="<?= htmlspecialchars($router['currency'] ?? 'Rp') ?>"
                                data-areload="<?= htmlspecialchars($router['reload_interval'] ?? '10') ?>"
                                data-quick-access="<?= $router['quick_access'] ?? 0 ?>">
                                <td>
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded bg-accents-2 flex items-center justify-center text-xs font-bold mr-3">
                                            <?= strtoupper(substr($router['session_name'], 0, 2)) ?>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-foreground flex items-center gap-2">
                                                <?= htmlspecialchars($router['session_name']) ?>
                                                <?php if (isset($router['quick_access']) && $router['quick_access'] == 1) { ?>
                                                    <i data-lucide="star" class="w-3 h-3 text-yellow-500 fill-current" title="Quick Access Enabled"></i>
                                                <?php } ?>
                                            </div>
                                            <div class="text-xs text-accents-5">ID: <?= $router['id'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-sm text-foreground"><?= htmlspecialchars($router['hotspot_name']) ?></div>
                                </td>
                                <td>
                                    <div class="text-sm text-accents-5 font-mono"><?= htmlspecialchars($router['ip_address']) ?></div>
                                </td>
                                <td class="text-right text-sm font-medium flex justify-end gap-2">
                                    <a href="/<?= htmlspecialchars($router['session_name']) ?>/dashboard" class="btn btn-secondary btn-sm h-8 px-3">
                                        Open
                                    </a>
                                     <button onclick="openRouterModal('edit', this)" class="btn btn-secondary btn-sm h-8 px-3" title="Edit">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </button>
                                    <form action="/settings/delete" method="POST" onsubmit="event.preventDefault(); Mivo.confirm('Disconnect Router?', 'Are you sure you want to disconnect <?= htmlspecialchars($router['session_name']) ?>?', 'Disconnect', 'Cancel').then(res => { if(res) this.submit(); });" class="inline">
                                        <input type="hidden" name="id" value="<?= $router['id'] ?>">
                                        <button type="submit" class="btn hover:bg-red-100 dark:hover:bg-red-900/30 text-red-600 border border-transparent h-8 px-2" title="Delete">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <div class="bg-accents-1 px-4 py-3 border-t border-accents-2 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sm:gap-0 sm:px-6">
                         <div class="text-sm text-accents-5">
                            Showing all <?= count($routers) ?> stored sessions
                         </div>
                          <button onclick="openRouterModal('add')" class="btn btn-primary btn-sm w-full sm:w-auto justify-center">
                            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> <span data-i18n="routers.add_router_title">Add New</span>
                          </button>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

<template id="router-form-template">
    <div class="text-left">
        <form id="router-form" action="/settings/store" method="POST" class="space-y-6">
            <input type="hidden" name="id" id="form-id">
            
            <!-- Session Settings -->
            <div>
                <h2 class="text-base font-semibold mb-3 flex items-center gap-2" data-i18n="routers.session_settings">
                    <i data-lucide="settings" class="w-4 h-4"></i> Session Settings
                </h2>
                <div class="max-w-md space-y-4">
                    <div class="space-y-1">
                        <label class="form-label" data-i18n="home.session_name">Session Name</label>
                        <input class="w-full" type="text" name="sessname" id="sessname" placeholder="e.g. router-jakarta-1" required/>
                        <p class="text-[10px] text-accents-4 uppercase tracking-tighter mt-1">
                            <span data-i18n="routers.unique_id">Unique ID:</span> <span id="sessname-preview" class="font-mono text-primary font-bold">...</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="quick_access" name="quick_access" class="checkbox flex-shrink-0" value="1">
                        <label for="quick_access" class="text-xs font-bold cursor-pointer select-none whitespace-nowrap uppercase tracking-wider" data-i18n="routers.show_quick_access">Show in Quick Access</label>
                    </div>
                </div>
            </div>

            <!-- Connection Details -->
            <div class="border-t border-white/5 pt-6">
                <h2 class="text-base font-semibold mb-3 flex items-center gap-2" data-i18n="routers.connection_details">
                    <i data-lucide="zap" class="w-4 h-4"></i> Connection Details
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1 md:col-span-1">
                        <label class="form-label" data-i18n="home.ip_address">IP Address</label>
                        <input class="w-full" type="text" name="ipmik" placeholder="192.168.88.1" required/>
                    </div>
                    <div class="space-y-1">
                        <label class="form-label" data-i18n="login.username">Username</label>
                        <input class="w-full" type="text" name="usermik" placeholder="admin" required/>
                    </div>
                    <div class="space-y-1">
                        <label class="form-label" data-i18n="login.password">Password</label>
                        <input class="w-full" type="password" name="passmik" id="passmik" placeholder="••••••••"/>
                    </div>
                </div>
            </div>

            <!-- Hotspot Information -->
            <div class="border-t border-white/5 pt-6">
                <h2 class="text-base font-semibold mb-3 flex items-center gap-2" data-i18n="routers.hotspot_info">
                    <i data-lucide="globe" class="w-4 h-4"></i> Hotspot Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="form-label" data-i18n="home.hotspot_name">Hotspot Name</label>
                        <input class="w-full" type="text" name="hotspotname" placeholder="My Hotspot ID" required/>
                    </div>
                    <div class="space-y-1">
                        <label class="form-label" data-i18n="routers.dns_name">DNS Name</label>
                        <input class="w-full" type="text" name="dnsname" placeholder="hotspot.net" required/>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div class="space-y-1">
                        <label class="form-label" data-i18n="routers.traffic_interface">Traffic Interface</label>
                        <div class="flex w-full gap-2">
                            <div class="flex-grow">
                                <select class="w-full" name="iface" id="iface" data-search="true" required>
                                    <option value="ether1">ether1</option>
                                </select>
                            </div>
                            <button type="button" id="check-interface-btn" class="btn btn-secondary whitespace-nowrap px-3" title="Check connection">
                                <i data-lucide="refresh-cw" class="w-4 h-4 mr-1"></i>
                                <span class="text-xs font-bold uppercase tracking-tight" data-i18n="routers.check_connection">Check Connection</span>
                            </button>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="form-label" data-i18n="routers.currency">Currency</label>
                            <input class="w-full" type="text" name="currency" value="Rp" required/>
                        </div>
                        <div class="space-y-1">
                            <label class="form-label" data-i18n="routers.auto_reload">Reload (s)</label>
                            <input class="w-full" type="number" min="2" name="areload" value="10" required/>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    function openRouterModal(mode, btn = null) {
        const template = document.getElementById('router-form-template').innerHTML;
        
        let title = window.i18n ? window.i18n.t('routers.add_router_title') : 'Add Router';
        let saveBtn = window.i18n ? window.i18n.t('common.save') : 'Save';
        
        if (mode === 'edit') {
            title = window.i18n ? window.i18n.t('routers.edit_router_title') : 'Edit Router';
            saveBtn = window.i18n ? window.i18n.t('common.forms.save_changes') : 'Save Changes';
        }

        const preConfirmFn = () => {
             const form = Swal.getHtmlContainer().querySelector('form');
             if(form.reportValidity()) {
                 form.submit();
                 return true;
             }
             return false;
        };

        const onOpenedFn = (popup) => {
             const form = popup.querySelector('form');
             
             // --- Interface Check Logic ---
             const checkBtn = form.querySelector('#check-interface-btn');
             const ifaceSelect = form.querySelector('#iface');
             
             if (checkBtn && ifaceSelect) {
                 checkBtn.addEventListener('click', async () => {
                    const originalHTML = checkBtn.innerHTML;
                    checkBtn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 mr-1 animate-spin"></i><span class="text-xs font-bold uppercase tracking-tight">Checking...</span>';
                    checkBtn.disabled = true;
                    if (typeof lucide !== 'undefined') lucide.createIcons();

                    const ip = form.querySelector('[name="ipmik"]').value;
                    const user = form.querySelector('[name="usermik"]').value;
                    const pass = form.querySelector('[name="passmik"]').value;
                    const id = form.querySelector('[name="id"]').value || null;

                    if (!ip || !user) {
                        Mivo.toast('warning', 'Missing Details', 'IP Address and Username are required');
                        checkBtn.innerHTML = originalHTML;
                        checkBtn.disabled = false;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        return;
                    }

                    try {
                        const response = await fetch('/api/router/interfaces', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ ip, user, password: pass, id })
                        });
                        const data = await response.json();

                        if (!data.success || !data.interfaces) {
                            Mivo.toast('error', 'Fetch Failed', data.error || 'Check credentials');
                        } else {
                            ifaceSelect.innerHTML = '';
                            data.interfaces.forEach(iface => {
                                const opt = document.createElement('option');
                                opt.value = iface;
                                opt.textContent = iface;
                                ifaceSelect.appendChild(opt);
                            });
                            
                            if (window.Mivo && window.Mivo.components.Select) {
                                const instance = window.Mivo.components.Select.get(ifaceSelect);
                                if (instance) instance.refresh();
                            }
                            Mivo.toast('success', 'Success', 'Interfaces loaded');
                        }
                    } catch (err) {
                        Mivo.toast('error', 'Error', 'Connection failed');
                    } finally {
                        checkBtn.innerHTML = originalHTML;
                        checkBtn.disabled = false;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                 });
             }

             // --- Session Name Formatting ---
             const sessInput = form.querySelector('[name="sessname"]');
             const sessPreview = form.querySelector('#sessname-preview');
             if (sessInput && sessPreview) {
                 sessInput.addEventListener('input', (e) => {
                    let val = e.target.value.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '').replace(/-+/g, '-');
                    e.target.value = val;
                    sessPreview.textContent = val || '...';
                 });
             }

             if (mode === 'edit' && btn) {
                 const row = btn.closest('tr');
                 form.action = "/settings/update";
                 
                 const idInput = form.querySelector('#form-id');
                 idInput.disabled = false;
                 idInput.value = row.dataset.id;

                 form.querySelector('[name="sessname"]').value = row.dataset.sessname || '';
                 if(sessPreview) sessPreview.textContent = row.dataset.sessname || '';
                 
                 form.querySelector('[name="ipmik"]').value = row.dataset.ipmik || '';
                 form.querySelector('[name="usermik"]').value = row.dataset.usermik || '';
                 form.querySelector('[name="hotspotname"]').value = row.dataset.hotspotname || '';
                 form.querySelector('[name="dnsname"]').value = row.dataset.dnsname || '';
                 form.querySelector('[name="currency"]').value = row.dataset.currency || 'Rp';
                 form.querySelector('[name="areload"]').value = row.dataset.areload || '10';
                 
                 const quickCheck = form.querySelector('#quick_access');
                 if(quickCheck) quickCheck.checked = row.dataset.quickAccess == '1';

                 // Handle Interface Select
                 const currentIface = row.dataset.iface || 'ether1';
                 ifaceSelect.innerHTML = `<option value="${currentIface}" selected>${currentIface}</option>`;
                 if (window.Mivo && window.Mivo.components.Select) {
                    const instance = window.Mivo.components.Select.get(ifaceSelect);
                    if (instance) instance.refresh();
                 }

                 // Password is not populated for security, hint is in placeholder
                 form.querySelector('[name="passmik"]').placeholder = '•••••••• (unchanged)';
                 form.querySelector('[name="passmik"]').required = false;
             }
        };

        Mivo.modal.form(title, template, saveBtn, preConfirmFn, onOpenedFn, 'swal-wide');
    }
</script>

<?php require_once ROOT.'/app/Views/layouts/footer_main.php'; ?>
