<?php

use App\Helpers\FormatHelper;
use App\Helpers\HotspotHelper;
use App\Helpers\ViewHelper;

$title = 'Hotspot Users';
require_once ROOT.'/app/Views/layouts/header_main.php';

// Prepare Filters Data
$uniqueProfiles = [];
$uniqueComments = [];
if (! empty($users)) {
    foreach ($users as $u) {
        $p = $u['profile'] ?? 'default';
        $c = $u['comment'] ?? '';

        $uniqueProfiles[$p] = $p; // Key-Value distinct
        if (! empty($c)) {
            $uniqueComments[$c] = $c;
        }
    }
}
sort($uniqueProfiles);

// $servers is passed from controller
if (! isset($servers)) {
    $servers = [];
}

sort($uniqueComments);
?>

<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight" data-i18n="hotspot_users.title">Hotspot Users</h1>
        <p class="text-accents-5"><span data-i18n="hotspot_users.subtitle">Manage vouchers and user accounts for session</span>: <span class="text-foreground font-medium"><?= htmlspecialchars($session) ?></span></p>
    </div>
    <div class="flex gap-2">
        <a href="/<?= htmlspecialchars($session) ?>/dashboard" class="btn btn-secondary">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> <span data-i18n="common.dashboard">Dashboard</span>
        </a>
        <button onclick="openUserModal('add')" class="btn btn-primary">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> <span data-i18n="hotspot_users.add_user">Add User</span>
        </button>
    </div>
</div>

<?php if ($error) { ?>
    <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 flex items-center">
        <i data-lucide="alert-circle" class="w-5 h-5 mr-3"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php } ?>

<!-- Batch Action Toolbar -->
<div id="batch-toolbar" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-foreground text-background px-6 py-3 rounded-full shadow-lg z-50 flex items-center gap-4 transition-all duration-300 translate-y-20 opacity-0">
    <span class="text-sm font-medium"><span id="selected-count">0</span> <span data-i18n="common.selected">Selected</span></span>
    <div class="h-4 w-px bg-background/20"></div>
    <button onclick="printSelected()" class="flex items-center gap-2 hover:text-accents-2 transition-colors font-bold text-sm">
        <i data-lucide="printer" class="w-4 h-4"></i> <span data-i18n="common.print">Print</span>
    </button>
    <button onclick="deleteSelected()" class="flex items-center gap-2 text-red-400 hover:text-red-300 transition-colors font-bold text-sm">
        <i data-lucide="trash-2" class="w-4 h-4"></i> <span data-i18n="common.delete">Delete</span>
    </button>
</div>

<!-- Filters & Table -->
<div class="space-y-4">
    <!-- Filter Bar -->
    <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
        <!-- Search -->
        <div class="input-group md:w-64 z-10">
            <div class="input-icon">
                <i data-lucide="search" class="h-4 w-4"></i>
            </div>
            <input type="text" id="global-search" class="form-input-search w-full" placeholder="Search user..." data-i18n="common.table.search_placeholder">
        </div>

        <!-- Dropdowns -->
        <div class="flex gap-2 w-full md:w-auto">
            <div class="w-40">
                <select id="filter-profile" class="custom-select form-filter" data-search="true">
                    <option value="" data-i18n="common.all_profiles">All Profiles</option>
                    <?php foreach ($uniqueProfiles as $p) { ?>
                        <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="w-40">
                <select id="filter-comment" class="custom-select form-filter" data-search="true">
                    <option value="" data-i18n="common.all_comments">All Comments</option>
                    <?php foreach ($uniqueComments as $c) { ?>
                        <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Table Container -->
    <!-- Table Container -->
    <div class="table-container">
        <table class="table-glass" id="users-table">
            <thead>
                <tr>
                    <th scope="col" class="px-4 py-3 w-10">
                        <input type="checkbox" id="select-all" class="checkbox">
                    </th>
                    <th data-sort="name" class="sortable cursor-pointer hover:text-foreground select-none" data-i18n="hotspot_users.name">Name</th>
                    <th data-sort="profile" class="sortable cursor-pointer hover:text-foreground select-none" data-i18n="hotspot_users.profile">Profile</th>
                    <th data-i18n="hotspot_users.uptime_limit">Uptime / Limit</th>
                    <th data-i18n="hotspot_users.bytes_in_out">Bytes In/Out</th>
                    <th data-sort="comment" class="sortable cursor-pointer hover:text-foreground select-none" data-i18n="hotspot_users.comment">Comment</th>
                    <th class="relative text-right" data-i18n="common.actions">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody id="table-body">
                <?php if (! empty($users)) { ?>
                    <?php foreach ($users as $user) { ?>
                    <?php
                        // Helper to split time limit for editing (Simple parsing or raw passing)
                        // Assuming time limit format from router is like 1d2h3m or just 1h
                        // We will pass the raw string if we can't easily split, OR rely on a JS parser.
                        // For now let's pass raw limit-uptime.

                        // Just prepare some safe values
                        $id = $user['.id'];
                        $name = $user['name'] ?? '';
                        $profile = $user['profile'] ?? 'default';
                        $comment = $user['comment'] ?? '';
                        $server = $user['server'] ?? 'all';
                        $password = $user['password'] ?? '';

                        // Limits
                        $limitUptime = $user['limit-uptime'] ?? '';
                        $limitBytes = $user['limit-bytes-total'] ?? '';
                        ?>
                    <tr class="table-row-item" 
                        data-id="<?= htmlspecialchars($id) ?>"
                        data-name="<?= strtolower($name) ?>" 
                        data-rawname="<?= htmlspecialchars($name) ?>"
                        data-profile="<?= htmlspecialchars($profile) ?>" 
                        data-comment="<?= htmlspecialchars($comment) ?>"
                        data-comment-raw="<?= htmlspecialchars($comment) ?>"
                        data-password="<?= htmlspecialchars($password) ?>"
                        data-server="<?= htmlspecialchars($server) ?>"
                        data-limit-uptime="<?= htmlspecialchars($limitUptime) ?>"
                        data-limit-bytes-total="<?= htmlspecialchars($limitBytes) ?>">
                        
                        <td class="px-4 py-4">
                            <input type="checkbox" name="selected_users[]" value="<?= htmlspecialchars($id) ?>" class="user-checkbox checkbox">
                        </td>
                        <td>
                            <div class="flex items-center w-full">
                                <div class="h-8 w-8 rounded bg-accents-2 flex items-center justify-center text-xs font-bold mr-3 text-accents-6 flex-shrink-0">
                                    <i data-lucide="user" class="w-4 h-4"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="text-sm font-medium text-foreground truncate"><?= htmlspecialchars($name) ?></div>
                                        <?php
                                                $status = HotspotHelper::getUserStatus($user);
                        echo ViewHelper::badge($status);
                        ?>
                                    </div>
                                    <div class="text-xs text-accents-5"><?= htmlspecialchars($password) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                <?= htmlspecialchars($profile) ?>
                            </span>
                        </td>
                        <td>
                            <div class="text-sm text-foreground"><?= FormatHelper::elapsedTime($user['uptime'] ?? '0s') ?></div>
                            <div class="text-xs text-accents-5">Limit: <?= FormatHelper::elapsedTime($user['limit-uptime'] ?? 'unlimited') ?></div>
                        </td>
                        <td>
                            <div class="text-xs text-accents-5 flex flex-col gap-1">
                                <span class="flex items-center"><i data-lucide="arrow-down" class="w-3 h-3 mr-1 text-green-500"></i> <?= FormatHelper::formatBytes($user['bytes-in'] ?? 0) ?></span>
                                <span class="flex items-center"><i data-lucide="arrow-up" class="w-3 h-3 mr-1 text-blue-500"></i> <?= FormatHelper::formatBytes($user['bytes-out'] ?? 0) ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="text-sm text-accents-5 italic"><?= htmlspecialchars($comment) ?></div>
                        </td>
                        <td class="text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2 table-actions-reveal">
                                <button onclick="printUser('<?= htmlspecialchars($id) ?>')" class="btn-icon" title="Print">
                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                </button>
                                <button onclick="openUserModal('edit', this)" class="btn-icon inline-flex items-center justify-center" title="Edit">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </button>
                                <form action="/<?= htmlspecialchars($session) ?>/hotspot/delete" method="POST" onsubmit="event.preventDefault(); Mivo.confirm('Delete User?', 'Are you sure you want to delete user <?= htmlspecialchars($name) ?>?', 'Delete', 'Cancel').then(res => { if(res) this.submit(); });" class="inline">
                                    <input type="hidden" name="session" value="<?= htmlspecialchars($session) ?>">
                                    <input type="hidden" name="id" value="<?= $id ?>">
                                    <button type="submit" class="btn-icon-danger" title="Delete">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-white/10 flex items-center justify-between" id="pagination-controls">
            <div class="text-sm text-accents-5">
                 <span id="pagination-text">Showing <span id="start-idx" class="font-medium text-foreground">0</span> to <span id="end-idx" class="font-medium text-foreground">0</span> of <span id="total-count" class="font-medium text-foreground">0</span> users</span>
            </div>
            <div class="flex gap-2">
                <button id="prev-btn" class="btn btn-sm btn-secondary" disabled data-i18n="common.previous">Previous</button>
                <div id="page-numbers" class="flex gap-1"></div>
                <button id="next-btn" class="btn btn-sm btn-secondary" disabled data-i18n="common.next">Next</button>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT.'/app/Views/layouts/footer_main.php'; ?>
<!-- Add/Edit User Template -->
<template id="user-form-template">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-left">
        <!-- Form Column -->
        <div class="lg:col-span-2">
            <form id="user-form" action="/<?= htmlspecialchars($session) ?>/hotspot/store" method="POST" class="space-y-4">
                <input type="hidden" name="session" value="<?= htmlspecialchars($session) ?>">
                <input type="hidden" name="id" id="form-id" disabled> <!-- Disabled for Add -->
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Name & Password -->
                    <div class="space-y-1">
                        <label class="form-label" data-i18n="hotspot_users.form.username">Username</label>
                        <div class="input-group">
                            <span class="input-icon"><i data-lucide="user" class="w-4 h-4"></i></span>
                            <input type="text" name="name" required class="pl-10 w-full" data-i18n-placeholder="hotspot_users.form.username_placeholder" placeholder="e.g. voucher123">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="form-label" data-i18n="hotspot_users.form.password">Password</label>
                        <div class="input-group">
                            <span class="input-icon"><i data-lucide="key" class="w-4 h-4"></i></span>
                            <input type="text" name="password" required class="pl-10 w-full" data-i18n-placeholder="hotspot_users.form.password_placeholder" placeholder="e.g. 123456">
                        </div>
                    </div>

                     <!-- Profile -->
                    <div class="space-y-1 col-span-1 md:col-span-2">
                        <label class="form-label" data-i18n="hotspot_users.form.profile">Profile</label>
                        <select name="profile" class="w-full" data-search="true">
                            <?php foreach ($uniqueProfiles as $p) { ?>
                                <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- Server -->
                     <div class="space-y-1">
                        <label class="form-label" data-i18n="hotspot_users.form.server">Server</label>
                        <select name="server" class="w-full">
                            <option value="all">all</option>
                            <?php
                            if (! empty($servers)) {
                                foreach ($servers as $s) {
                                    $sName = $s['name'] ?? '';
                                    if ($sName === 'all' || empty($sName)) {
                                        continue;
                                    }
                                    ?>
                                <option value="<?= htmlspecialchars($sName) ?>"><?= htmlspecialchars($sName) ?></option>
                            <?php
                                }
                            }
?>
                        </select>
                    </div>

                    <!-- Comment -->
                    <div class="space-y-1">
                        <label class="form-label" data-i18n="hotspot_users.form.comment">Comment</label>
                        <div class="input-group">
                            <span class="input-icon"><i data-lucide="message-square" class="w-4 h-4"></i></span>
                            <input type="text" name="comment" class="pl-10 w-full" placeholder="Optional note">
                        </div>
                    </div>
                    
                     <!-- Time Limit -->
                    <div class="space-y-1">
                        <label class="form-label" data-i18n="hotspot_users.form.time_limit">Time Limit</label>
                        <div class="flex w-full">
                            <div class="relative flex-1">
                                <span class="absolute right-2 top-2 text-xs font-bold text-accents-4 pointer-events-none">D</span>
                                <input type="number" name="timelimit_d" min="0" class="w-full pr-6 rounded-r-none border-r-0 text-center" placeholder="0">
                            </div>
                            <div class="relative flex-1">
                                <span class="absolute right-2 top-2 text-xs font-bold text-accents-4 pointer-events-none">H</span>
                                <input type="number" name="timelimit_h" min="0" max="23" class="w-full pr-6 rounded-none border-r-0 text-center" placeholder="0">
                            </div>
                            <div class="relative flex-1">
                                <span class="absolute right-2 top-2 text-xs font-bold text-accents-4 pointer-events-none">M</span>
                                <input type="number" name="timelimit_m" min="0" max="59" class="w-full pr-6 rounded-l-none text-center" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <!-- Data Limit -->
                    <div class="space-y-1">
                        <label class="form-label" data-i18n="hotspot_users.form.data_limit">Data Limit</label>
                        <div class="flex relative w-full">
                            <div class="relative flex-grow z-0">
                                <span class="input-icon"><i data-lucide="database" class="w-4 h-4"></i></span>
                                <input type="number" name="datalimit_val" min="0" class="form-input w-full pl-10 rounded-r-none" placeholder="0">
                            </div>
                            <div class="relative -ml-px w-20 z-0">
                                <select name="datalimit_unit" class="w-full rounded-l-none bg-accents-1 text-center font-medium">
                                    <option value="MB" selected>MB</option>
                                    <option value="GB">GB</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Spacer for dropdowns -->
                <div class="h-24"></div>
            </form>
        </div>

        <!-- Tips Column -->
        <div class="hidden lg:block space-y-4 border-l border-white/10 pl-6">
            <h3 class="text-sm font-bold text-foreground flex items-center gap-2">
                <i data-lucide="lightbulb" class="w-4 h-4 text-yellow-400"></i>
                <span data-i18n="hotspot_users.form.quick_tips">Quick Tips</span>
            </h3>
            <ul class="text-xs text-accents-5 space-y-3 list-disc pl-4">
                <li data-i18n="hotspot_users.form.tip_profiles">
                    <strong>Profiles</strong> define the default speed limits, session timeout, and shared users policy.
                </li>
                <li data-i18n="hotspot_users.form.tip_time_limit">
                    <strong>Time Limit</strong> is the total accumulated uptime allowed for this user.
                </li>
                <li data-i18n="hotspot_users.form.tip_data_limit">
                    <strong>Data Limit</strong> will override the profile's data limit settings if specified here. Set to 0 to use profile default.
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
    class TableManager {
        constructor(rows, itemsPerPage = 10) {
            this.allRows = Array.from(rows);
            this.filteredRows = this.allRows;
            this.itemsPerPage = itemsPerPage;
            this.currentPage = 1;

            this.elements = {
                body: document.getElementById('table-body'),
                startIdx: document.getElementById('start-idx'),
                endIdx: document.getElementById('end-idx'),
                totalCount: document.getElementById('total-count'),
                prevBtn: document.getElementById('prev-btn'),
                nextBtn: document.getElementById('next-btn'),
                pageNumbers: document.getElementById('page-numbers')
            };

            this.filters = {
                search: '',
                profile: '',
                comment: ''
            };

            this.init();
        }

        init() {
            this.setupListeners();
            this.update();
        }

        setupListeners() {
            // Search Input
            document.getElementById('global-search').addEventListener('input', (e) => {
                this.filters.search = e.target.value.toLowerCase();
                this.currentPage = 1;
                this.update();
            });

            // Prev/Next
            this.elements.prevBtn.addEventListener('click', () => {
                if (this.currentPage > 1) {
                    this.currentPage--;
                    this.render();
                }
            });

            this.elements.nextBtn.addEventListener('click', () => {
                const maxPage = Math.ceil(this.filteredRows.length / this.itemsPerPage);
                if (this.currentPage < maxPage) {
                    this.currentPage++;
                    this.render();
                }
            });
            
            // Filters
            document.getElementById('filter-profile').addEventListener('change', (e) => {
                this.filters.profile = e.target.value;
                this.currentPage = 1;
                this.update();
            });
            
            document.getElementById('filter-comment').addEventListener('change', (e) => {
                this.filters.comment = e.target.value;
                this.currentPage = 1;
                this.update();
            });

             // Listen for language change
             window.addEventListener('languageChanged', () => {
                this.render();
            });
        }

        update() {
            // Apply Filters
            this.filteredRows = this.allRows.filter(row => {
                const name = row.dataset.name || '';
                const comment = (row.dataset.comment || '').toLowerCase();
                const profile = row.dataset.profile || '';
                
                // 1. Search
                if (this.filters.search) {
                     const matchName = name.includes(this.filters.search);
                     const matchComment = comment.includes(this.filters.search);
                     if (!matchName && !matchComment) return false;
                }
                
                // 2. Profile
                if (this.filters.profile && profile !== this.filters.profile) return false;
                
                // 3. Comment
                if (this.filters.comment && row.dataset.comment !== this.filters.comment) return false;
                
                return true;
            });

            this.render();
        }

        render() {
            const total = this.filteredRows.length;
            const maxPage = Math.ceil(total / this.itemsPerPage) || 1;
            
            if (this.currentPage > maxPage) this.currentPage = maxPage;
            
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = Math.min(start + this.itemsPerPage, total);
            
            // Update Text
            if (window.i18n) {
                const text = window.i18n.t('common.table.showing', {
                    start: total === 0 ? 0 : start + 1,
                    end: end,
                    total: total
                });
                document.getElementById('pagination-text').textContent = text;
            } else {
                 // Fallback
                 const el = document.getElementById('pagination-text');
                 el.innerHTML = `Showing <span class="font-medium text-foreground">${total === 0 ? 0 : start + 1}</span> to <span class="font-medium text-foreground">${end}</span> of <span class="font-medium text-foreground">${total}</span> users`;
            }
            
            // Clear & Append Rows
            this.elements.body.innerHTML = '';
            
            const pageRows = this.filteredRows.slice(start, end);
            pageRows.forEach(row => this.elements.body.appendChild(row));
            
            // Update Buttons
            this.elements.prevBtn.disabled = this.currentPage === 1;
            this.elements.nextBtn.disabled = this.currentPage === maxPage || total === 0;

            if (this.elements.pageNumbers) {
                 const pageText = window.i18n ? window.i18n.t('common.page_of', {current: this.currentPage, total: maxPage}) : `Page ${this.currentPage} of ${maxPage}`;
                this.elements.pageNumbers.innerHTML = `<span class="px-3 py-1 text-sm font-medium bg-accents-2 rounded text-accents-6">${pageText}</span>`;
            }

            // Re-init Icons
            if (typeof lucide !== 'undefined') lucide.createIcons();
            
            // Reset "Select All"
            document.getElementById('select-all').checked = false;
        }
    }

    // --- Modal Logic ---
    function openUserModal(mode, btn = null) {
        const template = document.getElementById('user-form-template').innerHTML;
        
        let title = window.i18n ? window.i18n.t('hotspot_users.add_user') : 'Add User';
        let saveBtn = window.i18n ? window.i18n.t('common.save') : 'Save';
        
        if (mode === 'edit') {
            title = window.i18n ? window.i18n.t('hotspot_users.edit_user') : 'Edit User';
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
             
             if (mode === 'edit' && btn) {
                 const row = btn.closest('tr');
                 
                 form.action = "/<?= htmlspecialchars($session) ?>/hotspot/update";
                 
                 // Populate Hidden ID
                 const idInput = form.querySelector('#form-id');
                 idInput.disabled = false;
                 idInput.value = row.dataset.id; // Ensure data-id is set on TR!

                 // Populate Fields (Assuming data attributes or simple values)
                 // NOTE: For full data (limits, etc), we might need to fetch OR put all in data attributes
                 // Let's rely on data attributes for speed, but need to add them to TR first
                 form.querySelector('[name="name"]').value = row.dataset.rawname || '';
                 form.querySelector('[name="password"]').value = row.dataset.password || '';
                 form.querySelector('[name="comment"]').value = row.dataset.commentRaw || '';
                 
                 // Selects
                 const profileSel = form.querySelector('[name="profile"]');
                 if(profileSel) profileSel.value = row.dataset.profile;
                 
                 const serverSel = form.querySelector('[name="server"]');
                 if(serverSel) serverSel.value = row.dataset.server || 'all';

                 // Limits (Parsing from data attributes)
                 // Time Limit
                 const tLimit = row.dataset.limitUptime || '';
                 // Simple regex parsing for 1d2h3m (Mikrotik format)
                 // This is complex to parse perfectly from string back to split fields without a helper
                 // For now, let's just leave 0 or try best effort if available
                 // Ideally, we pass split values in data attributes from PHP
                 if (row.dataset.timeD) form.querySelector('[name="timelimit_d"]').value = row.dataset.timeD;
                 if (row.dataset.timeH) form.querySelector('[name="timelimit_h"]').value = row.dataset.timeH;
                 if (row.dataset.timeM) form.querySelector('[name="timelimit_m"]').value = row.dataset.timeM;

                 // Data Limit
                 if (row.dataset.limitBytesTotal) {
                     const bytes = parseInt(row.dataset.limitBytesTotal);
                     if (bytes > 0) {
                         if (bytes >= 1073741824) { // GB
                             form.querySelector('[name="datalimit_val"]').value = (bytes / 1073741824).toFixed(0); // integer prefer
                             form.querySelector('[name="datalimit_unit"]').value = 'GB';
                         } else { // MB
                             form.querySelector('[name="datalimit_val"]').value = (bytes / 1048576).toFixed(0);
                             form.querySelector('[name="datalimit_unit"]').value = 'MB';
                         }
                     }
                 }
             }
        };

        Mivo.modal.form(title, template, saveBtn, preConfirmFn, onOpenedFn, 'swal-wide');
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Init Checkboxes & Table methods
        const selectAll = document.getElementById('select-all');
        const toolbar = document.getElementById('batch-toolbar');
        const countSpan = document.getElementById('selected-count');
        const tableBody = document.getElementById('table-body');
        
        // Init Custom Selects on Filter Bar
        if (typeof CustomSelect !== 'undefined') {
            document.querySelectorAll('.custom-select.form-filter').forEach(s => new CustomSelect(s));
        }

        // Init Table
        const rows = document.querySelectorAll('.table-row-item');
        const manager = new TableManager(rows, 10);
        
        // Toolbar Logic
        function updateToolbar() {
            const checked = document.querySelectorAll('.user-checkbox:checked');
            countSpan.textContent = checked.length;
            if (checked.length > 0) toolbar.classList.remove('translate-y-20', 'opacity-0');
            else toolbar.classList.add('translate-y-20', 'opacity-0');
        }

        if(selectAll) {
            selectAll.addEventListener('change', (e) => {
                const isChecked = e.target.checked;
                // Only select visible rows
                const visibleCheckboxes = tableBody.querySelectorAll('.user-checkbox');
                visibleCheckboxes.forEach(cb => cb.checked = isChecked);
                updateToolbar();
            });
        }

        if(tableBody) {
             tableBody.addEventListener('change', (e) => {
                if (e.target.classList.contains('user-checkbox')) {
                    updateToolbar();
                    if (!e.target.checked) selectAll.checked = false;
                }
            });
        }
    });

    // Actions
    function printUser(id) {
        const width = 400; const height = 600;
        const left = (window.innerWidth - width) / 2;
        const top = (window.innerHeight - height) / 2;
        const session = '<?= htmlspecialchars($session) ?>';
        window.open(`/${session}/hotspot/print/${encodeURIComponent(id)}`, `PrintUser`, `width=${width},height=${height},top=${top},left=${left},scrollbars=yes`);
    }
    
    function printSelected() {
        const selected = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) return Mivo.alert('info', 'No selection', window.i18n ? window.i18n.t('hotspot_users.no_users_selected') : "No users selected.");
        
        const width = 800; const height = 600;
        const left = (window.innerWidth - width) / 2;
        const top = (window.innerHeight - height) / 2;
        const session = '<?= htmlspecialchars($session) ?>';
        const ids = selected.map(id => encodeURIComponent(id)).join(',');
        window.open(`/${session}/hotspot/print-batch?ids=${ids}`, `PrintBatch`, `width=${width},height=${height},top=${top},left=${left},scrollbars=yes`);
    }
    
    function deleteSelected() {
        const selected = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
        if (selected.length === 0) return Mivo.alert('info', 'No selection', window.i18n ? window.i18n.t('hotspot_users.no_users_selected') : "Please select at least one user.");
        
        const title = window.i18n ? window.i18n.t('common.delete') : 'Delete Users?';
        const msg = window.i18n ? window.i18n.t('common.confirm_delete') : `Are you sure you want to delete ${selected.length} users?`;
        
        Mivo.confirm(title, msg, window.i18n.t('common.delete'), window.i18n.t('common.cancel')).then(res => {
            if (!res) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/<?= htmlspecialchars($session) ?>/hotspot/delete'; 
            const sInput = document.createElement('input');
            sInput.type = 'hidden'; sInput.name = 'session'; sInput.value = '<?= htmlspecialchars($session) ?>';
            form.appendChild(sInput);
            const idInput = document.createElement('input');
            idInput.type = 'hidden'; idInput.name = 'id'; idInput.value = selected.join(',');
            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
        });
    }
</script>
