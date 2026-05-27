<?php
$title = 'Scheduler';
require_once ROOT.'/app/Views/layouts/header_main.php';
?>

<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight" data-i18n="system_menu.scheduler">Scheduler</h1>
        <p class="text-accents-5"><span data-i18n="system_tools.scheduler_subtitle">Manage RouterOS automated tasks for:</span> <span class="text-foreground font-medium"><?= htmlspecialchars($session) ?></span></p>
    </div>
    <div class="flex gap-2">
         <button onclick="location.reload()" class="btn btn-secondary">
            <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i> <span data-i18n="reports.refresh">Refresh</span>
        </button>
        <button onclick="openSchedulerModal('add')" class="btn btn-primary">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> <span data-i18n="system_tools.add_task">Add Task</span>
        </button>
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
        <div class="input-group md:w-64 z-10">
             <div class="input-icon">
                <i data-lucide="search" class="h-4 w-4"></i>
            </div>
            <input type="text" id="global-search" class="form-input-search w-full" placeholder="Search task name..." data-i18n-placeholder="common.table.search_placeholder">
        </div>
    </div>

    <div class="table-container">
        <table class="table-glass" id="scheduler-table">
            <thead>
                <tr>
                    <th data-sort="name" class="sortable cursor-pointer hover:text-foreground select-none" data-i18n="system_tools.table_name">Name</th>
                    <th data-i18n="system_tools.interval">Interval</th>
                    <th data-i18n="system_tools.next_run">Next Run</th>
                    <th data-sort="status" class="sortable cursor-pointer hover:text-foreground select-none" data-i18n="system_tools.status">Status</th>
                    <th class="text-right" data-i18n="common.actions">Actions</th>
                </tr>
            </thead>
            <tbody id="table-body">
                <?php if (! empty($schedulers) && is_array($schedulers)) { ?>
                    <?php foreach ($schedulers as $task) {
                        $status = ($task['disabled'] === 'true') ? 'disabled' : 'enabled';
                        ?>
                    <tr class="table-row-item"
                            data-id="<?= $task['.id'] ?>"
                            data-name="<?= htmlspecialchars($task['name']) ?>"
                            data-interval="<?= htmlspecialchars($task['interval']) ?>"
                            data-start-date="<?= htmlspecialchars($task['start-date'] ?? '') ?>"
                            data-start-time="<?= htmlspecialchars($task['start-time'] ?? '') ?>"
                            data-on-event="<?= htmlspecialchars($task['on-event']) ?>"
                            data-comment="<?= htmlspecialchars($task['comment'] ?? '') ?>"
                            data-search-name="<?= strtolower($task['name']) ?>"
                            data-status="<?= $status ?>">
                        
                        <td>
                            <div class="text-sm font-medium text-foreground"><?= htmlspecialchars($task['name']) ?></div>
                            <div class="text-xs text-accents-5 truncate max-w-[200px]"><?= htmlspecialchars($task['on-event']) ?></div>
                        </td>
                        <td class="text-sm text-accents-5"><?= htmlspecialchars($task['interval']) ?></td>
                        <td class="text-sm text-accents-5"><?= htmlspecialchars($task['next-run'] ?? '-') ?></td>
                        <td>
                                <?php if ($task['disabled'] === 'true') { ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-accents-2 text-accents-5" data-i18n="system_tools.disabled">Disabled</span>
                            <?php } else { ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400" data-i18n="system_tools.enabled">Enabled</span>
                            <?php } ?>
                        </td>
                        <td class="text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2 table-actions-reveal">
                                <button onclick="openSchedulerModal('edit', this)" class="btn-icon" title="Edit">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </button>
                                <form action="/<?= $session ?>/system/scheduler/delete" method="POST" onsubmit="event.preventDefault(); Mivo.confirm(window.i18n ? window.i18n.t('system_tools.delete_task') : 'Delete Task?', window.i18n ? window.i18n.t('common.confirm_delete') : 'Are you sure you want to delete task <?= htmlspecialchars($task['name']) ?>?', window.i18n ? window.i18n.t('common.delete') : 'Delete', window.i18n ? window.i18n.t('common.cancel') : 'Cancel').then(res => { if(res) this.submit(); });" class="inline">
                                    <input type="hidden" name="id" value="<?= $task['.id'] ?>">
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
                Showing <span id="start-idx" class="font-medium text-foreground">0</span> to <span id="end-idx" class="font-medium text-foreground">0</span> of <span id="total-count" class="font-medium text-foreground">0</span> tasks
            </div>
            <div class="flex gap-2">
                <button id="prev-btn" class="btn btn-sm btn-secondary" disabled data-i18n="common.previous">Previous</button>
                <div id="page-numbers" class="flex gap-1"></div>
                <button id="next-btn" class="btn btn-sm btn-secondary" disabled data-i18n="common.next">Next</button>
            </div>
        </div>
    </div>
</div>



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
                const name = row.dataset.searchName || '';
                
                if (this.filters.search && !name.includes(this.filters.search)) return false;
                
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

function openSchedulerModal(mode, btn = null) {
    const template = document.getElementById('scheduler-form-template').innerHTML;
    
    let title = window.i18n ? window.i18n.t('system_tools.add_title') : 'Add Scheduler Task';
    let saveBtn = window.i18n ? window.i18n.t('common.save') : 'Save';
    
    if (mode === 'edit') {
        title = window.i18n ? window.i18n.t('system_tools.edit_title') : 'Edit Scheduler Task';
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
             form.action = "/<?= htmlspecialchars($session) ?>/system/scheduler/update";
             
             // Populate Hidden ID
             const idInput = form.querySelector('#form-id');
             idInput.disabled = false;
             idInput.value = row.dataset.id;

             // Populate Fields
             form.querySelector('[name="name"]').value = row.dataset.name || '';
             form.querySelector('[name="interval"]').value = row.dataset.interval || '';
             form.querySelector('[name="start_date"]').value = row.dataset.startDate || '';
             form.querySelector('[name="start_time"]').value = row.dataset.startTime || '';
             form.querySelector('[name="on_event"]').value = row.dataset.onEvent || '';
             form.querySelector('[name="comment"]').value = row.dataset.comment || '';
         }
    };

    Mivo.modal.form(title, template, saveBtn, preConfirmFn, onOpenedFn);
}

    document.addEventListener('DOMContentLoaded', () => {
        new TableManager(document.querySelectorAll('.table-row-item'), 10);
    });
</script>

<template id="scheduler-form-template">
    <div class="text-left">
        <form action="/<?= $session ?>/system/scheduler/store" method="POST" class="space-y-4">
            <input type="hidden" name="id" id="form-id" disabled>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="form-label" data-i18n="system_tools.name">Name</label>
                    <input type="text" name="name" class="w-full" required>
                </div>
                <div class="space-y-1">
                    <label class="form-label" data-i18n="system_tools.interval">Interval</label>
                    <input type="text" name="interval" class="w-full" value="1d 00:00:00" placeholder="1d 00:00:00">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="form-label" data-i18n="system_tools.start_date">Start Date</label>
                    <input type="text" name="start_date" class="w-full" value="Jan/01/1970">
                </div>
                <div class="space-y-1">
                    <label class="form-label" data-i18n="system_tools.start_time">Start Time</label>
                    <input type="text" name="start_time" class="w-full" value="00:00:00">
                </div>
            </div>
            <div class="space-y-1">
                <label class="form-label" data-i18n="system_tools.on_event">On Event (Script)</label>
                <textarea name="on_event" class="w-full font-mono text-xs h-32" placeholder="/system reboot"></textarea>
            </div>
            <div class="space-y-1">
                <label class="form-label" data-i18n="system_tools.comment">Comment</label>
                <input type="text" name="comment" class="w-full">
            </div>
        </form>
    </div>
</template>

<?php require_once ROOT.'/app/Views/layouts/footer_main.php'; ?>
