<?php
use App\Helpers\FormatHelper;

$title = 'Selling Report';
require_once ROOT.'/app/Views/layouts/header_main.php';
?>

<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight" data-i18n="reports.selling_title">Selling Report</h1>
        <p class="text-accents-5"><span data-i18n="reports.selling_subtitle">Sales summary and details for:</span> <span class="text-foreground font-medium"><?= htmlspecialchars($session) ?></span></p>
    </div>
    <div class="flex gap-2">
        <div class="dropdown dropdown-end relative" id="export-dropdown">
            <button class="btn btn-secondary dropdown-toggle" onclick="document.getElementById('export-menu').classList.toggle('hidden')">
                <i data-lucide="download" class="w-4 h-4 mr-2"></i> <span data-i18n="reports.export">Export</span>
                <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i>
            </button>
            <div id="export-menu" class="dropdown-menu hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-black border border-accents-2 z-50 p-1">
                <button onclick="exportReport('csv')" class="block w-full text-left px-4 py-2 text-sm text-foreground hover:bg-accents-1 rounded flex items-center">
                    <i data-lucide="file-text" class="w-4 h-4 mr-2 text-green-600"></i> Export CSV
                </button>
                <button onclick="exportReport('xlsx')" class="block w-full text-left px-4 py-2 text-sm text-foreground hover:bg-accents-1 rounded flex items-center">
                    <i data-lucide="sheet" class="w-4 h-4 mr-2 text-green-600"></i> Export Excel
                </button>
            </div>
        </div>
        <button onclick="location.reload()" class="btn btn-secondary">
            <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i> <span data-i18n="reports.refresh">Refresh</span>
        </button>
        <button onclick="window.print()" class="btn btn-primary">
            <i data-lucide="printer" class="w-4 h-4 mr-2"></i> <span data-i18n="reports.print_report">Print Report</span>
        </button>
    </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- Stock / Potential -->
    <div class="card">
        <div class="text-sm text-accents-5 uppercase font-bold tracking-wide" data-i18n="reports.generated_stock">Generated Stock</div>
        <div class="text-3xl font-bold text-accents-6 mt-2">
            <?= FormatHelper::formatCurrency($totalIncome, $currency) ?>
        </div>
        <div class="text-xs text-accents-5 mt-1">
            <?= number_format($totalVouchers) ?> vouchers
        </div>
    </div>
    
    <!-- Realized / Actual -->
    <div class="card !bg-green-500/10 !border-green-500/20">
        <div class="text-sm text-green-600 dark:text-green-400 uppercase font-bold tracking-wide" data-i18n="reports.realized_income">Realized Income</div>
        <div class="text-3xl font-bold text-green-600 dark:text-green-400 mt-2">
            <?= FormatHelper::formatCurrency($totalRealizedIncome ?? 0, $currency) ?>
        </div>
        <div class="text-xs text-green-600/70 dark:text-green-400/70 mt-1">
            <?= number_format($totalUsedVouchers ?? 0) ?> used
        </div>
    </div>
</div>

<div class="space-y-4">
    <!-- Detailed Table -->
    <table class="table-glass" id="report-table">
        <thead>
            <tr>
                <th data-sort="date" data-i18n="reports.date_batch">Date / Batch (Comment)</th>
                <th data-i18n="reports.status">Status</th>
                <th class="text-right" data-i18n="reports.qty">Qty (Stock)</th>
                <th class="text-right text-green-500" data-i18n="reports.used">Used</th>
                <th data-sort="total" class="text-right" data-i18n="reports.total_stock">Total Stock</th>
            </tr>
        </thead>
        <tbody id="table-body">
            <?php if (empty($report)) { ?>
                <tr>
                    <td colspan="5" class="p-8 text-center text-accents-5" data-i18n="reports.no_data">No sales data found.</td>
                </tr>
            <?php } else { ?>
                <?php foreach ($report as $row) { ?>
                    <tr class="table-row-item">
                        <td class="font-medium">
                            <?= htmlspecialchars($row['date']) ?>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'New') { ?>
                                <span class="px-2 py-1 text-xs font-bold rounded-md bg-accents-2 text-accents-6">NEW</span>
                            <?php } elseif ($row['status'] === 'Selling') { ?>
                                <span class="px-2 py-1 text-xs font-bold rounded-md bg-blue-500/10 text-blue-500 border border-blue-500/20">SELLING</span>
                            <?php } elseif ($row['status'] === 'Sold Out') { ?>
                                <span class="px-2 py-1 text-xs font-bold rounded-md bg-green-500/10 text-green-500 border border-green-500/20">SOLD OUT</span>
                            <?php } ?>
                        </td>
                        <td class="text-right font-mono text-accents-6">
                            <?= number_format($row['count']) ?>
                        </td>
                        <td class="text-right font-mono text-green-500 font-medium">
                            <?= number_format($row['realized_count']) ?>
                            <span class="text-xs opacity-70 block">
                                <?= FormatHelper::formatCurrency($row['realized_total'], $currency) ?>
                            </span>
                        </td>
                        <td class="text-right font-mono font-bold text-foreground">
                            <?= FormatHelper::formatCurrency($row['total'], $currency) ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
    </table>
</div>


<script src="/assets/js/components/datatable.js"></script>
<!-- Local SheetJS Library -->
<script src="/assets/vendor/xlsx/xlsx.full.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof SimpleDataTable !== 'undefined') {
            new SimpleDataTable('#report-table', { 
                itemsPerPage: 15, 
                searchable: true,
                pagination: true,
                // Add Filter for Status Column (Index 1)
                filters: [
                    { index: 1, label: 'Status: All' }
                ]
            });
        }
    });

    async function exportReport(type) {
        const url = '/<?= $session ?>/reports/selling/export/' + type;
        const btn = document.querySelector('.dropdown-toggle');
        const originalText = btn.innerHTML;
        
        // Show Loading State
        btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 mr-2 animate-spin"></i> Processing...`;
        lucide.createIcons();

        try {
            const response = await fetch(url);
            const data = await response.json();

            if (data.error) {
                alert('Export Failed: ' + data.error);
                return;
            }

            const filename = `selling-report-<?= date('Y-m-d') ?>-${type}.` + (type === 'csv' ? 'csv' : 'xlsx');

            if (type === 'csv') {
                // Convert JSON to CSV
                const header = Object.keys(data[0]);
                const csv = [
                    header.join(','), // header row first
                    ...data.map(row => header.map(fieldName => JSON.stringify(row[fieldName])).join(','))
                ].join('\r\n');

                const blob = new Blob([csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.setAttribute('hidden', '');
                a.setAttribute('href', url);
                a.setAttribute('download', filename);
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            } 
            else if (type === 'xlsx') {
                // Use SheetJS for Real Excel
                const ws = XLSX.utils.json_to_sheet(data);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Selling Report");
                XLSX.writeFile(wb, filename);
            }

        } catch (error) {
            console.error('Export Error:', error);
            alert('Failed to export data. Check console for details.');
        } finally {
            // Restore Button
            btn.innerHTML = originalText;
            lucide.createIcons();
            document.getElementById('export-menu').classList.add('hidden');
        }
    }
</script>

<?php require_once ROOT.'/app/Views/layouts/footer_main.php'; ?>
