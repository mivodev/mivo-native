<?php
$title = 'Walled Garden';
require_once ROOT.'/app/Views/layouts/header_main.php';
?>

<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight" data-i18n="security.walled_garden.title">Walled Garden</h1>
        <p class="text-accents-5" data-i18n="security.walled_garden.subtitle" data-i18n-params='{"name": "<?= htmlspecialchars($session) ?>"}'>Manage allowed destinations (bypass without login) for: <span class="text-foreground font-medium"><?= htmlspecialchars($session) ?></span></p>
    </div>
    <div class="flex gap-2">
        <a href="/<?= htmlspecialchars($session) ?>/dashboard" class="btn btn-secondary" data-i18n="common.dashboard">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Dashboard
        </a>
    </div>
</div>

<?php if ($error) { ?>
    <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 flex items-center shadow-sm">
        <i data-lucide="alert-circle" class="w-5 h-5 mr-3"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php } ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
    <!-- List (2/3) -->
    <div class="lg:col-span-2 space-y-4">
        <!-- Filter Bar -->
        <div class="flex flex-col md:flex-row gap-4 justify-between items-center bg-card p-4 rounded-lg border border-accents-2 shadow-sm">
            <!-- Search -->
            <div class="relative w-full">
                 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="h-4 w-4 text-accents-5"></i>
                </div>
                <input type="text" id="global-search" class="form-input pl-10 w-full" placeholder="Search host, ip, comment..." data-i18n="common.table.search_placeholder">
            </div>
             <!-- Dropdowns -->
            <div class="flex gap-2 w-full md:w-auto">
                <div class="w-40">
                    <select id="filter-action" class="custom-select" data-search="true">
                        <option value="" data-i18n="security.walled_garden.all_actions">All Actions</option>
                        <option value="allow" data-i18n="security.walled_garden.allow">Allow</option>
                        <option value="deny" data-i18n="security.walled_garden.deny">Deny</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="table-glass" id="walled-garden-table">
                <thead>
                    <tr>
                        <th data-i18n="security.walled_garden.table.host_ip">Dst. Host / IP</th>
                        <th data-i18n="security.walled_garden.table.proto_port">Protocol / Port</th>
                        <th data-sort="action" class="sortable cursor-pointer hover:text-primary select-none group">
                            <div class="flex items-center gap-1"><span data-i18n="security.walled_garden.table.action">Action</span> <i data-lucide="arrow-up-down" class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity"></i></div>
                        </th>
                        <th data-sort="comment" class="sortable cursor-pointer hover:text-primary select-none group">
                            <div class="flex items-center gap-1"><span data-i18n="security.walled_garden.table.comment">Comment</span> <i data-lucide="arrow-up-down" class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity"></i></div>
                        </th>
                        <th class="relative text-right" data-i18n="common.actions">Act</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php if (! empty($items)) { ?>
                        <?php foreach ($items as $item) { ?>
                        <tr class="table-row-item"
                            data-action="<?= htmlspecialchars($item['action'] ?? 'allow') ?>"
                            data-host="<?= strtolower($item['dst-host'] ?? '') ?>"
                            data-address="<?= htmlspecialchars($item['dst-address'] ?? '') ?>"
                            data-comment="<?= strtolower($item['comment'] ?? '') ?>">
                            
                            <td>
                                <div class="flex items-center">
                                    <div class="p-1.5 bg-accents-2 rounded mr-2 text-accents-6">
                                        <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="text-sm font-medium text-foreground"><?= htmlspecialchars($item['dst-host'] ?? $item['dst-address'] ?? 'Any') ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="text-sm text-foreground"><?= htmlspecialchars($item['protocol'] ?? 'Any') ?> : <?= htmlspecialchars($item['dst-port'] ?? 'Any') ?></div>
                            </td>
                            <td>
                                <?php
                                    $actionClass = 'bg-accents-2 text-accents-6 border border-accents-3';
                            if (($item['action'] ?? '') == 'allow') {
                                $actionClass = 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800';
                            }
                            if (($item['action'] ?? '') == 'deny') {
                                $actionClass = 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800';
                            }
                            ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?= $actionClass ?>">
                                    <?= htmlspecialchars($item['action'] ?? 'allow') ?>
                                </span>
                            </td>
                            <td class="text-sm text-accents-5 italic"><?= htmlspecialchars($item['comment'] ?? '-') ?></td>
                            <td class="text-right text-sm font-medium">
                                <div class="flex justify-end">
                                    <form action="/<?= htmlspecialchars($session) ?>/hotspot/walled-garden/remove" method="POST" onsubmit="event.preventDefault(); Mivo.confirm('Remove Entry?', 'Are you sure you want to remove this Walled Garden entry?', 'Remove', 'Cancel').then(res => { if(res) this.submit(); });" class="inline">
                                        <input type="hidden" name="session" value="<?= htmlspecialchars($session) ?>">
                                        <input type="hidden" name="id" value="<?= $item['.id'] ?>">
                                        <button type="submit" class="btn btn-icon-sm hover:bg-red-50 text-accents-5 hover:text-red-600 transition-colors" title="Remove">
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
                    Showing <span id="start-idx" class="font-medium text-foreground">0</span> to <span id="end-idx" class="font-medium text-foreground">0</span> of <span id="total-count" class="font-medium text-foreground">0</span>
                </div>
                <div class="flex gap-2">
                    <button id="prev-btn" class="btn btn-sm btn-secondary" disabled data-i18n="common.previous">Previous</button>
                    <div id="page-numbers" class="flex gap-1"></div>
                    <button id="next-btn" class="btn btn-sm btn-secondary" disabled data-i18n="common.next">Next</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Form (Sticky Side) -->
    <div class="lg:col-span-1">
        <div class="card p-0 border-accents-2 shadow-lg sticky top-6">
            <div class="p-4 border-b border-accents-2 bg-primary/5 flex items-center gap-2">
                 <div class="p-1.5 bg-primary/10 rounded text-primary">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                </div>
                <h3 class="font-bold text-sm uppercase tracking-wide text-primary" data-i18n="security.walled_garden.form.add_title">Add Entry</h3>
            </div>
            
            <form action="/<?= htmlspecialchars($session) ?>/hotspot/walled-garden/store" method="POST" class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-4">
                <input type="hidden" name="session" value="<?= htmlspecialchars($session) ?>">
                
                <div class="space-y-1.5 md:col-span-2 lg:col-span-1">
                    <label class="text-xs font-bold text-accents-5 uppercase" data-i18n="security.walled_garden.form.dst_host">Dst. Host (Domain)</label>
                     <div class="relative group">
                         <span class="absolute left-3 top-2.5 text-accents-4 group-focus-within:text-primary transition-colors pointer-events-none">
                            <i data-lucide="globe" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="dst_host" class="form-input pl-10" placeholder="example.com">
                    </div>
                     <p class="text-xs text-accents-5" data-i18n="security.walled_garden.form.host_help">Domain to allow (wildcard supported).</p>
                </div>
                <div class="space-y-1.5 md:col-span-2 lg:col-span-1">
                    <label class="text-xs font-bold text-accents-5 uppercase" data-i18n="security.walled_garden.form.dst_address">Dst. Address (IP)</label>
                    <input type="text" name="dst_address" class="form-input" placeholder="10.5.50.1">
                    <p class="text-xs text-accents-5" data-i18n="security.walled_garden.form.addr_help">Destination IP Address.</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4 md:col-span-2 lg:col-span-1">
                     <div class="space-y-1.5">
                        <label class="text-xs font-bold text-accents-5 uppercase" data-i18n="security.walled_garden.form.protocol">Protocol</label>
                         <select name="protocol" class="custom-select w-full">
                            <option value="(6) tcp">tcp</option>
                            <option value="(17) udp">udp</option>
                            <option value="" data-i18n="common.none">any</option>
                        </select>
                    </div>
                     <div class="space-y-1.5">
                        <label class="text-xs font-bold text-accents-5 uppercase" data-i18n="security.walled_garden.form.dst_port">Dst. Port</label>
                        <input type="text" name="dst_port" class="form-input" placeholder="80,443">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-accents-5 uppercase" data-i18n="security.walled_garden.form.action">Action</label>
                    <select name="action" class="custom-select w-full">
                        <option value="allow" data-i18n="security.walled_garden.allow">allow</option>
                        <option value="deny" data-i18n="security.walled_garden.deny">deny</option>
                    </select>
                     <p class="text-xs text-accents-5" data-i18n="security.walled_garden.form.action_help">Allow (bypass) or Deny access.</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-accents-5 uppercase" data-i18n="security.walled_garden.form.server">Server</label>
                    <select name="server" class="custom-select w-full" data-search="true">
                        <option value="all">all</option>
                        <!-- Ideally fetch servers -->
                    </select>
                    <p class="text-xs text-accents-5" data-i18n="security.walled_garden.form.server_help">Apply to specific Hotspot server.</p>
                </div>

                <div class="space-y-1.5 md:col-span-2 lg:col-span-1">
                    <label class="text-xs font-bold text-accents-5 uppercase" data-i18n="security.walled_garden.form.comment">Comment</label>
                    <input type="text" name="comment" class="form-input" placeholder="Optional notes" data-i18n-placeholder="security.walled_garden.form.comment_help">
                     <p class="text-xs text-accents-5" data-i18n="security.walled_garden.form.comment_help">Note for this rule.</p>
                </div>

                <div class="pt-2 md:col-span-2 lg:col-span-1">
                    <button type="submit" class="btn btn-primary w-full shadow-lg shadow-primary/20 hover:shadow-primary/40 transition-shadow">
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i> <span data-i18n="security.walled_garden.form.save">Save Entry</span>
                    </button>
                </div>

                <!-- Quick Tips -->
                <div class="pt-4 mt-4 border-t border-accents-2 md:col-span-2 lg:col-span-1">
                    <h4 class="text-xs font-bold text-accents-5 uppercase mb-2 flex items-center gap-1">
                        <i data-lucide="lightbulb" class="w-3 h-3 text-yellow-500"></i> <span data-i18n="common.tips">Tips</span>
                    </h4>
                    <ul class="text-xs text-accents-5 space-y-1.5 list-disc list-inside">
                        <li data-i18n="security.walled_garden.form.tip_host"><strong>Dst. Host:</strong> Domain name (e.g. <code>*.google.com</code>).</li>
                        <li data-i18n="security.walled_garden.form.tip_ip"><strong>Dst. IP:</strong> Specific IP address.</li>
                        <li data-i18n="security.walled_garden.form.tip_action"><strong>Action:</strong> Allow to bypass auth.</li>
                    </ul>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once ROOT.'/app/Views/layouts/footer_main.php'; ?>
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

            this.filters = { search: '', action: '' };
            this.init();
        }

        init() {
            document.getElementById('global-search').addEventListener('input', (e) => {
                this.filters.search = e.target.value.toLowerCase();
                this.currentPage = 1;
                this.update();
            });
            // Translate placeholder
            const searchInput = document.getElementById('global-search');
            if (searchInput && window.i18n) {
                searchInput.placeholder = window.i18n.t('common.table.search_placeholder');
            }
            document.getElementById('filter-action').addEventListener('change', (e) => {
                this.filters.action = e.target.value;
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
                const action = row.dataset.action || 'allow';
                const host = row.dataset.host || '';
                const addr = row.dataset.address || '';
                const cmt = row.dataset.comment || '';
                
                if (this.filters.action && action !== this.filters.action) return false;
                if (this.filters.search) {
                     if (!host.includes(this.filters.search) && !addr.includes(this.filters.search) && !cmt.includes(this.filters.search)) return false;
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
            
            this.elements.body.innerHTML = '';
            this.filteredRows.slice(start, end).forEach(row => this.elements.body.appendChild(row));
            
            // Update Text (Use Translation)
            if (window.i18n && document.getElementById('pagination-controls')) {
                 const text = window.i18n.t('common.table.showing', {
                    start: total === 0 ? 0 : start + 1,
                    end: end,
                    total: total
                });
                const container = document.getElementById('pagination-controls').querySelector('.text-accents-5');
                 if(container) {
                      container.innerHTML = text.replace('{start}', `<span class="font-medium text-foreground">${total === 0 ? 0 : start + 1}</span>`)
                                                .replace('{end}', `<span class="font-medium text-foreground">${end}</span>`)
                                                .replace('{total}', `<span class="font-medium text-foreground">${total}</span>`);
                 }
            } else {
                this.elements.startIdx.textContent = total === 0 ? 0 : start + 1;
                this.elements.endIdx.textContent = end;
                this.elements.totalCount.textContent = total;
            }
            
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
