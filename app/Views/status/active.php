<?php

use App\Helpers\FormatHelper;

$title = 'Active Users';
require_once ROOT.'/app/Views/layouts/header_main.php';

// Filter Data
$uniqueServers = [];
if (! empty($items)) {
    foreach ($items as $item) {
        $s = $item['server'] ?? '';
        if (! empty($s)) {
            $uniqueServers[$s] = $s;
        }
    }
}
sort($uniqueServers);
?>

<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight" data-i18n="hotspot_active.title">Active Users</h1>
        <p class="text-accents-5"><span data-i18n="hotspot_active.subtitle">Monitor currently active hotspot sessions</span> <span class="text-foreground font-medium"><?= htmlspecialchars($session) ?></span></p>
    </div>
    <div class="flex gap-2">
        <a href="/<?= htmlspecialchars($session) ?>/dashboard" class="btn btn-secondary">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> <span data-i18n="common.dashboard">Dashboard</span>
        </a>
         <a href="/<?= htmlspecialchars($session) ?>/hotspot/users" class="btn btn-secondary">
            <i data-lucide="users" class="w-4 h-4 mr-2"></i> <span data-i18n="hotspot_menu.users">Users List</span>
        </a>
    </div>
</div>

<?php if ($error) { ?>
    <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 flex items-center">
        <i data-lucide="alert-circle" class="w-5 h-5 mr-3"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php } ?>

<div class="space-y-4">
    <!-- Filter Bar -->
    <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
        <!-- Search -->
        <div class="relative w-full md:w-64">
             <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i data-lucide="search" class="h-4 w-4 text-accents-5"></i>
            </div>
            <input type="text" id="global-search" class="form-input pl-10 w-full" placeholder="Search user, mac, ip...">
        </div>
         <!-- Dropdowns -->
        <div class="flex gap-2 w-full md:w-auto">
            <div class="w-40">
                <select id="filter-server" class="custom-select" data-search="true">
                    <option value="" data-i18n="hotspot_active.filter_server">All Servers</option>
                    <?php foreach ($uniqueServers as $s) { ?>
                        <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <table class="table-glass" id="active-table">
            <thead>
                <tr>
                    <th data-sort="server" class="sortable cursor-pointer hover:text-foreground select-none" data-i18n="hotspot_active.server">Server</th>
                    <th data-sort="user" class="sortable cursor-pointer hover:text-foreground select-none" data-i18n="hotspot_active.user">User</th>
                    <th data-i18n="hotspot_active.address">Address / MAC</th>
                    <th data-i18n="hotspot_active.uptime">Uptime / Left</th>
                    <th data-i18n="hotspot_active.bytes_in_out">Bytes In/Out</th>
                    <th class="relative text-right" data-i18n="common.actions">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody id="table-body">
                <?php if (! empty($items)) { ?>
                    <?php foreach ($items as $item) { ?>
                    <tr class="table-row-item"
                        data-server="<?= htmlspecialchars($item['server'] ?? '') ?>"
                        data-user="<?= strtolower($item['user'] ?? '') ?>"
                        data-address="<?= htmlspecialchars($item['address'] ?? '') ?>"
                        data-mac="<?= strtolower($item['mac-address'] ?? '') ?>">
                        
                        <td>
                            <span class="text-sm font-medium text-foreground"><?= htmlspecialchars($item['server'] ?? '-') ?></span>
                        </td>
                        <td>
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-xs font-bold mr-3 text-green-700 dark:text-green-400">
                                    <i data-lucide="wifi" class="w-4 h-4"></i>
                                </div>
                                <div class="text-sm font-medium text-foreground"><?= htmlspecialchars($item['user'] ?? '-') ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="text-sm text-foreground"><?= htmlspecialchars($item['address'] ?? '-') ?></div>
                            <div class="text-xs text-accents-5 font-mono"><?= htmlspecialchars($item['mac-address'] ?? '-') ?></div>
                        </td>
                        <td>
                             <div class="text-sm text-foreground"><?= FormatHelper::elapsedTime($item['uptime'] ?? '0s') ?></div>
                             <?php if (isset($item['session-time-left'])) { ?>
                                <div class="text-xs text-accents-5"><span data-i18n="hotspot_active.time_left">Left</span>: <?= FormatHelper::elapsedTime($item['session-time-left']) ?></div>
                             <?php } ?>
                        </td>
                        <td>
                            <div class="text-xs text-accents-5 flex flex-col gap-1">
                                <span class="flex items-center"><i data-lucide="arrow-down" class="w-3 h-3 mr-1 text-green-500"></i> <?= FormatHelper::formatBytes($item['bytes-in'] ?? 0) ?></span>
                                <span class="flex items-center"><i data-lucide="arrow-up" class="w-3 h-3 mr-1 text-blue-500"></i> <?= FormatHelper::formatBytes($item['bytes-out'] ?? 0) ?></span>
                            </div>
                        </td>
                        <td class="text-right text-sm font-medium">
                            <div class="flex items-center justify-end">
                                <form action="/<?= htmlspecialchars($session) ?>/hotspot/active/remove" method="POST" onsubmit="event.preventDefault(); Mivo.confirm(window.i18n ? window.i18n.t('hotspot_active.remove') : 'Disconnect User?', window.i18n ? window.i18n.t('common.confirm_delete') : 'Are you sure you want to disconnect user <?= htmlspecialchars($item['user'] ?? '') ?>?', window.i18n ? window.i18n.t('hotspot_active.remove') : 'Disconnect', window.i18n ? window.i18n.t('common.cancel') : 'Cancel').then(res => { if(res) this.submit(); });" class="inline">
                                    <input type="hidden" name="session" value="<?= htmlspecialchars($session) ?>">
                                    <input type="hidden" name="id" value="<?= $item['.id'] ?>">
                                    <button type="submit" class="btn bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/20 dark:hover:bg-red-900/40 border-transparent h-8 px-2 rounded transition-colors" title="Disconnect">
                                        <i data-lucide="x-circle" class="w-4 h-4"></i>
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
                Showing <span id="start-idx" class="font-medium text-foreground">0</span> to <span id="end-idx" class="font-medium text-foreground">0</span> of <span id="total-count" class="font-medium text-foreground">0</span> active
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
<script>
    class TableManager {
        constructor(rows, itemsPerPage = 10) {
            this.allRows = Array.from(rows);
            // Translate placeholder
            const searchInput = document.getElementById('global-search');
            if (searchInput && window.i18n) {
                searchInput.placeholder = window.i18n.t('common.table.search_placeholder');
            }
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

            this.filters = { search: '', server: '' };
            this.init();
        }

        init() {
            document.getElementById('global-search').addEventListener('input', (e) => {
                this.filters.search = e.target.value.toLowerCase();
                this.currentPage = 1;
                this.update();
            });
            document.getElementById('filter-server').addEventListener('change', (e) => {
                this.filters.server = e.target.value;
                this.currentPage = 1;
                this.update();
            });
            
            this.elements.prevBtn.addEventListener('click', () => { if(this.currentPage > 1) { this.currentPage--; this.render(); } });
            this.elements.nextBtn.addEventListener('click', () => { 
                const max = Math.ceil(this.filteredRows.length / this.itemsPerPage);
                if(this.currentPage < max) { this.currentPage++; this.render(); } 
            });

            this.update();

            // Listen for language change
            window.addEventListener('languageChanged', () => {
                const searchInput = document.getElementById('global-search');
                if (searchInput && window.i18n) {
                    searchInput.placeholder = window.i18n.t('common.table.search_placeholder');
                }
                this.render();
            });
        }

        update() {
            this.filteredRows = this.allRows.filter(row => {
                const svr = row.dataset.server || '';
                const user = row.dataset.user || '';
                const mac = row.dataset.mac || '';
                const addr = row.dataset.address || '';
                
                if (this.filters.server && svr !== this.filters.server) return false;
                if (this.filters.search) {
                    if (!user.includes(this.filters.search) && !mac.includes(this.filters.search) && !addr.includes(this.filters.search)) return false;
                }
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
            
            this.elements.startIdx.textContent = total === 0 ? 0 : start + 1;
            this.elements.endIdx.textContent = end;
            this.elements.totalCount.textContent = total;
            
            // Update Text (Use Translation)
            if (window.i18n && document.getElementById('pagination-controls')) {
                 const text = window.i18n.t('common.table.showing', {
                    start: total === 0 ? 0 : start + 1,
                    end: end,
                    total: total
                });
                // Find and update the text node if possible, or reconstruct
                const container = document.getElementById('pagination-controls').querySelector('.text-accents-5');
                if(container) container.innerHTML = text; // This replaces the span structure, need to be careful
                // Actually, the structure is "Showing <span>..</span> to <span>..</span>".
                // Our translation string is "Showing {start} to {end} of {total} active"
                // So we can just replace the whole innerHTML of the container
                 if(container) {
                     // Re-render with spans for consistent styling if needed, or just text
                      container.innerHTML = text.replace('{start}', `<span class="font-medium text-foreground">${total === 0 ? 0 : start + 1}</span>`)
                                                .replace('{end}', `<span class="font-medium text-foreground">${end}</span>`)
                                                .replace('{total}', `<span class="font-medium text-foreground">${total}</span>`);
                 }
            }
            
            this.elements.body.innerHTML = '';
            this.filteredRows.slice(start, end).forEach(row => this.elements.body.appendChild(row));
            
            this.elements.prevBtn.disabled = this.currentPage === 1;
            this.elements.nextBtn.disabled = this.currentPage === maxPage || total === 0;

            if (this.elements.pageNumbers) {
                const pageText = window.i18n ? window.i18n.t('common.page_of', {current: this.currentPage, total: maxPage}) : `Page ${this.currentPage} of ${maxPage}`;
                this.elements.pageNumbers.innerHTML = `<span class="px-3 py-1 text-sm font-medium bg-accents-2 rounded text-accents-6">${pageText}</span>`;
            }

            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof CustomSelect !== 'undefined') {
            document.querySelectorAll('.custom-select').forEach(s => new CustomSelect(s));
        }
        new TableManager(document.querySelectorAll('.table-row-item'), 10);
    });
</script>
