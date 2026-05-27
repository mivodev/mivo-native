<?php
$title = 'Resume Report';
require_once ROOT.'/app/Views/layouts/header_main.php';
?>

<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight" data-i18n="reports.resume_title">Resume Report</h1>
        <p class="text-accents-5" data-i18n="reports.resume_subtitle">Overview of aggregated income.</p>
    </div>
     <div class="flex items-center gap-2">
          <span class="text-sm font-medium bg-accents-1 px-3 py-1 rounded-full border border-accents-2">
            <span data-i18n="reports.total_income">Total Income</span>: <?= $currency ?> <?= number_format($totalIncome, 0, ',', '.') ?>
          </span>
    </div>
</div>

<!-- Tabs -->
<div class="mb-6 border-b border-accents-2">
    <nav class="flex space-x-4 overflow-x-auto no-scrollbar" aria-label="Tabs">
        <button onclick="switchTab('daily')" id="tab-daily" class="px-3 py-2 text-sm font-medium border-b-2 border-primary text-primary active-tab whitespace-nowrap" data-i18n="reports.daily">Daily</button>
        <button onclick="switchTab('monthly')" id="tab-monthly" class="px-3 py-2 text-sm font-medium border-b-2 border-transparent text-accents-5 hover:text-foreground whitespace-nowrap" data-i18n="reports.monthly">Monthly</button>
        <button onclick="switchTab('yearly')" id="tab-yearly" class="px-3 py-2 text-sm font-medium border-b-2 border-transparent text-accents-5 hover:text-foreground whitespace-nowrap" data-i18n="reports.yearly">Yearly</button>
    </nav>
</div>

<!-- Daily Tab -->
<div id="content-daily" class="tab-content">
    <table class="table-glass" id="table-daily">
        <thead>
            <tr>
                <th data-i18n="reports.date">Date</th>
                <th class="text-right" data-i18n="reports.total">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($daily as $date => $total) { ?>
            <tr>
                <td><?= $date ?></td>
                <td class="text-right font-mono"><?= $currency ?> <?= number_format($total, 0, ',', '.') ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Monthly Tab -->
<div id="content-monthly" class="tab-content hidden">
    <table class="table-glass" id="table-monthly">
            <thead>
            <tr>
                <th data-i18n="reports.month">Month</th>
                <th class="text-right" data-i18n="reports.total">Total</th>
            </tr>
        </thead>
            <tbody>
            <?php foreach ($monthly as $date => $total) { ?>
            <tr>
                <td><?= $date ?></td>
                <td class="text-right font-mono"><?= $currency ?> <?= number_format($total, 0, ',', '.') ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Yearly Tab -->
<div id="content-yearly" class="tab-content hidden">
    <table class="table-glass" id="table-yearly">
            <thead>
            <tr>
                <th data-i18n="reports.year">Year</th>
                <th class="text-right" data-i18n="reports.total">Total</th>
            </tr>
        </thead>
            <tbody>
            <?php foreach ($yearly as $date => $total) { ?>
            <tr>
                <td><?= $date ?></td>
                <td class="text-right font-mono"><?= $currency ?> <?= number_format($total, 0, ',', '.') ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="/assets/js/components/datatable.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Init Datatables
        if (typeof SimpleDataTable !== 'undefined') {
            new SimpleDataTable('#table-daily', { itemsPerPage: 10, searchable: true });
            new SimpleDataTable('#table-monthly', { itemsPerPage: 10, searchable: true });
            new SimpleDataTable('#table-yearly', { itemsPerPage: 10, searchable: true });
        }
    });

    function switchTab(tabName) {
        // Hide all contents
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        // Show selected
        document.getElementById('content-' + tabName).classList.remove('hidden');

        // Reset tab styles
        document.querySelectorAll('nav button').forEach(el => {
            el.classList.remove('border-primary', 'text-primary');
            el.classList.add('border-transparent', 'text-accents-5');
        });

        // Active tab style
        const btn = document.getElementById('tab-' + tabName);
        btn.classList.remove('border-transparent', 'text-accents-5');
        btn.classList.add('border-primary', 'text-primary');
    }
</script>

<?php require_once ROOT.'/app/Views/layouts/footer_main.php'; ?>
