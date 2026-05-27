<?php
$title = 'Hotspot Cookies';
require_once ROOT.'/app/Views/layouts/header_main.php';
?>

<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight" data-i18n="cookies.title">Hotspot Cookies</h1>
        <p class="text-accents-5"><span data-i18n="cookies.subtitle">Active authentication cookies for:</span> <span class="text-foreground font-medium"><?= htmlspecialchars($session) ?></span></p>
    </div>
    <div class="flex gap-2">
         <button onclick="location.reload()" class="btn btn-secondary">
            <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i> <span data-i18n="reports.refresh">Refresh</span>
        </button>
        <a href="/<?= htmlspecialchars($session) ?>/dashboard" class="btn btn-secondary">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> <span data-i18n="common.dashboard">Dashboard</span>
        </a>
    </div>
</div>

<?php if (isset($error) && $error) { ?>
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
            <input type="text" id="global-search" class="form-input pl-10 w-full" placeholder="Search user, mac..." data-i18n="common.table.search_placeholder">
        </div>
    </div>

    <div class="table-container">
        <table class="table-glass" id="cookies-table">
            <thead>
                <tr>
                    <th data-sort="user" class="sortable cursor-pointer hover:text-foreground select-none" data-i18n="cookies.user">User</th>
                    <th data-i18n="cookies.mac">MAC Address</th>
                    <th data-i18n="cookies.ip">IP Address</th>
                    <th data-sort="expires" class="sortable cursor-pointer hover:text-foreground select-none" data-i18n="cookies.expires">Expires In</th>
                    <th class="relative text-right" data-i18n="common.actions">Action</th>
                </tr>
            </thead>
            <tbody id="table-body">
                <?php if (! empty($cookies) && is_array($cookies)) { ?>
                    <?php foreach ($cookies as $cookie) { ?>
                    <tr class="table-row-item"
                         data-user="<?= strtolower($cookie['user'] ?? '') ?>"
                         data-mac="<?= strtolower($cookie['mac-address'] ?? '') ?>"
                         data-expires="<?= htmlspecialchars($cookie['expires-in'] ?? '') ?>">
                        
                        <td>
                            <span class="text-sm font-medium text-foreground"><?= htmlspecialchars($cookie['user'] ?? '-') ?></span>
                        </td>
                        <td>
                            <span class="font-mono text-sm text-accents-5 uppercase"><?= htmlspecialchars($cookie['mac-address'] ?? '-') ?></span>
                        </td>
                        <td>
                            <span class="font-mono text-sm text-foreground"><?= htmlspecialchars($cookie['ip'] ?? '-') ?></span>
                        </td>
                        <td>
                            <span class="text-sm text-accents-5"><?= htmlspecialchars($cookie['expires-in'] ?? '-') ?></span>
                        </td>
                        <td class="text-right text-sm font-medium">
                            <div class="flex justify-end table-actions-reveal">
                                <form action="/<?= htmlspecialchars($session) ?>/hotspot/cookies/remove" method="POST" onsubmit="event.preventDefault(); Mivo.confirm(window.i18n ? window.i18n.t('cookies.remove_cookie') : 'Remove Cookie?', window.i18n ? window.i18n.t('cookies.remove_confirm', {user: '<?= htmlspecialchars($cookie['user'] ?? '') ?>'}) : 'Are you sure you want to remove the cookie for <?= htmlspecialchars($cookie['user'] ?? '') ?>?', window.i18n ? window.i18n.t('common.delete') : 'Remove', window.i18n ? window.i18n.t('common.cancel') : 'Cancel').then(res => { if(res) this.submit(); });">
                                    <input type="hidden" name="session" value="<?= htmlspecialchars($session) ?>">
                                    <input type="hidden" name="id" value="<?= $cookie['.id'] ?>">
                                    <button type="submit" class="p-1.5 text-red-500 hover:bg-red-500/10 rounded transition-colors" title="Remove">
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
            <div class="text-sm text-accents-5" data-i18n="common.table.showing" data-i18n-params='{"start": "0", "end": "0", "total": "0"}'>
                Showing <span id="start-idx" class="font-medium text-foreground">0</span> to <span id="end-idx" class="font-medium text-foreground">0</span> of <span id="total-count" class="font-medium text-foreground">0</span> cookies
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

            this.filters = { search: '' };
            this.init();
        }

        init() {
            document.getElementById('global-search').addEventListener('input', (e) => {
                this.filters.search = e.target.value.toLowerCase();
                this.currentPage = 1;
                this.update();
            });
            // Placeholder translation handled via data-i18n-placeholder
            
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
                const user = row.dataset.user || '';
                const mac = row.dataset.mac || '';
                
                if (this.filters.search) {
                     if (!user.includes(this.filters.search) && !mac.includes(this.filters.search)) return false;
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
        new TableManager(document.querySelectorAll('.table-row-item'), 10);
    });
</script>
