<?php
$title = 'User Log';
require_once ROOT.'/app/Views/layouts/header_main.php';

// Prepare unique topics for filter
$uniqueTopics = [];
if (! empty($logs) && is_array($logs)) {
    foreach ($logs as $log) {
        $t = $log['topics'] ?? '';
        // Split comma separated topics if needed, but usually it's one string or comma sep string
        // Simple approach: Use full string or main topic
        if (! empty($t)) {
            $uniqueTopics[$t] = $t;
        }
    }
}
sort($uniqueTopics);
?>

<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight" data-i18n="reports.user_log_title">User Log</h1>
        <p class="text-accents-5"><span data-i18n="reports.user_log_subtitle">Login and logout history for:</span> <span class="text-foreground font-medium"><?= htmlspecialchars($session) ?></span></p>
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
            <input type="text" id="global-search" class="form-input pl-10 w-full" placeholder="Search message..." data-i18n-placeholder="common.table.search_placeholder">
        </div>
         <!-- Dropdowns -->
        <div class="flex gap-2 w-full md:w-auto">
            <div class="w-48">
                <select id="filter-topic" class="custom-select" data-search="true">
                    <option value="" data-i18n="common.all_topics">All Topics</option>
                    <option value="hotspot,info,debug">hotspot,info,debug</option>
                    <option value="hotspot,account,info,debug">hotspot,account,info,debug</option>
                    <option value="system,info,account">system,info,account</option>
                    <!-- Fallback to generated if diverse -->
                    <?php foreach ($uniqueTopics as $t) { ?>
                        <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="table-glass" id="log-table">
            <thead>
                <tr>
                    <th class="w-40" data-i18n="reports.time">Time</th>
                    <th data-sort="topics" class="sortable cursor-pointer hover:text-foreground select-none w-48" data-i18n="reports.topics">Topics</th>
                    <th data-sort="message" class="sortable cursor-pointer hover:text-foreground select-none" data-i18n="reports.message">Message</th>
                </tr>
            </thead>
            <tbody id="table-body">
                <?php if (! empty($logs) && is_array($logs)) { ?>
                    <?php foreach ($logs as $log) {
                        $topics = $log['topics'] ?? '';
                        $isError = strpos($topics, 'error') !== false;
                        $rowClass = $isError ? 'text-red-500 hover:bg-red-50 dark:hover:bg-red-900/10' : '';
                        ?>
                    <tr class="table-row-item <?= $rowClass ?>"
                            data-topics="<?= htmlspecialchars($topics) ?>"
                            data-message="<?= strtolower($log['message'] ?? '') ?>">
                        
                        <td class="font-mono text-sm text-accents-5">
                            <?= htmlspecialchars($log['time'] ?? '-') ?>
                        </td>
                        <td>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-accents-2 text-accents-6 border border-accents-3">
                                <?= htmlspecialchars($topics) ?>
                            </span>
                        </td>
                        <td class="text-sm whitespace-normal break-words">
                            <?= htmlspecialchars($log['message'] ?? '-') ?>
                        </td>
                    </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-white/10 flex items-center justify-between" id="pagination-controls">
            <div class="text-sm text-accents-5">
                Showing <span id="start-idx" class="font-medium text-foreground">0</span> to <span id="end-idx" class="font-medium text-foreground">0</span> of <span id="total-count" class="font-medium text-foreground">0</span> logs
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
        constructor(rows, itemsPerPage = 15) {
            this.allRows = Array.from(rows);
            // Hide duplicates in unique topics select options (hacky fix for double output)
            const seen = new Set();
            document.querySelectorAll('#filter-topic option').forEach(o => {
                if(seen.has(o.value) || o.value === '') { 
                    if(o.value !== '') o.remove(); 
                } else seen.add(o.value);
            });

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

            this.filters = { search: '', topics: '' };
            this.init();
        }

        init() {
            // Translate placeholder
            const searchInput = document.getElementById('global-search');
            if (searchInput && window.i18n) {
                searchInput.placeholder = window.i18n.t('common.table.search_placeholder');
            }
            document.getElementById('global-search').addEventListener('input', (e) => {
                this.filters.search = e.target.value.toLowerCase();
                this.currentPage = 1;
                this.update();
            });
            document.getElementById('filter-topic').addEventListener('change', (e) => {
                this.filters.topics = e.target.value;
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
                const topics = row.dataset.topics || '';
                const msg = row.dataset.message || '';
                
                if (this.filters.topics && !topics.includes(this.filters.topics)) return false;
                if (this.filters.search && !msg.includes(this.filters.search)) return false;
                
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
                // Find and update the text node if possible
                const container = document.getElementById('pagination-controls').querySelector('.text-accents-5');
                 if(container) {
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
        new TableManager(document.querySelectorAll('.table-row-item'), 15);
    });
</script>
