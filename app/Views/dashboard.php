<?php

use App\Helpers\FormatHelper;

require_once ROOT.'/app/Views/layouts/header_main.php';
?>

<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight" data-i18n="common.dashboard">Dashboard</h1>
        <p class="text-accents-5"><span data-i18n="common.session">Session</span>: <strong class="text-foreground"><?= $session ?></strong></p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- System Info Card -->
    <div class="card space-y-5">
        <div class="flex items-center gap-2">
             <i data-lucide="cpu" class="w-5 h-5"></i>
            <h3 class="font-semibold text-lg" data-i18n="dashboard.system_info">System Info</h3>
        </div>
        <div class="text-sm space-y-2">
            <div class="flex justify-between border-b border-accents-2 pb-2">
                <span class="text-accents-5" data-i18n="dashboard.model">Model</span>
                <span class="font-medium"><?= $routerboard['model'] ?? '-' ?></span>
            </div>
            <div class="flex justify-between border-b border-accents-2 pb-2">
                <span class="text-accents-5" data-i18n="dashboard.board_name">Board Name</span>
                <span class="font-medium"><?= $resource['board-name'] ?? '-' ?></span>
            </div>
             <div class="flex justify-between border-b border-accents-2 pb-2">
                <span class="text-accents-5" data-i18n="dashboard.router_os">RouterOS</span>
                <span class="font-medium"><?= $resource['version'] ?? '-' ?></span>
            </div>
            <div class="flex justify-between border-b border-accents-2 pb-2">
                <span class="text-accents-5" data-i18n="dashboard.architecture">Architecture</span>
                <span class="font-medium"><?= $resource['architecture-name'] ?? '-' ?></span>
            </div>
             <div class="flex justify-between">
                <span class="text-accents-5" data-i18n="dashboard.uptime">Uptime</span>
                <span class="font-medium"><?= FormatHelper::elapsedTime($resource['uptime'] ?? '-') ?></span>
            </div>
        </div>
    </div>

    <!-- Resources Card -->
    <div class="card space-y-5">
        <div class="flex items-center gap-2">
            <i data-lucide="hard-drive" class="w-5 h-5"></i>
            <h3 class="font-semibold text-lg" data-i18n="dashboard.resources">Resources</h3>
        </div>
        
        <!-- CPU Config (simple progress not calculated here for cpu-load as it fluctuates, just text) -->
        <div class="space-y-1">
             <div class="flex justify-between text-sm">
                <span data-i18n="dashboard.cpu_load">CPU Load</span>
                <span class="font-bold"><?= $resource['cpu-load'] ?? 0 ?>%</span>
            </div>
            <div class="h-2 w-full bg-accents-2 rounded-full overflow-hidden">
                <div class="h-full bg-foreground" style="width: <?= $resource['cpu-load'] ?? 0 ?>%"></div>
            </div>
        </div>

        <div class="space-y-1">
            <div class="flex justify-between text-sm">
                <span data-i18n="dashboard.memory">Memory</span>
                <span class="text-accents-5"><?= FormatHelper::formatBytes($resource['free-memory'] ?? 0, 1) ?> <span data-i18n="dashboard.free">Free</span></span>
            </div>
            <div class="h-2 w-full bg-accents-2 rounded-full overflow-hidden">
                <?php
                    $totalMem = ($resource['total-memory'] ?? 1);
$freeMem = ($resource['free-memory'] ?? 0);
$usedMemP = (($totalMem - $freeMem) / $totalMem) * 100;
?>
                <div class="h-full bg-blue-600 dark:bg-blue-500" style="width:<?= $usedMemP ?>%"></div>
            </div>
        </div>

        <div class="space-y-1">
            <div class="flex justify-between text-sm">
                <span data-i18n="dashboard.hdd">HDD</span>
                <span class="text-accents-5"><?= FormatHelper::formatBytes($resource['free-hdd-space'] ?? 0, 1) ?> <span data-i18n="dashboard.free">Free</span></span>
            </div>
             <div class="h-2 w-full bg-accents-2 rounded-full overflow-hidden">
                <?php
    $totalHdd = ($resource['total-hdd-space'] ?? 1);
$freeHdd = ($resource['free-hdd-space'] ?? 0);
$usedHddP = (($totalHdd - $freeHdd) / $totalHdd) * 100;
?>
                <div class="h-full bg-foreground" style="width:<?= $usedHddP ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Hotspot Stats -->
    <div class="col-span-full md:col-span-1 lg:col-span-1 card flex flex-col justify-center space-y-5">
         <div class="flex items-center gap-2">
            <i data-lucide="wifi" class="w-5 h-5"></i>
            <h3 class="font-semibold text-lg" data-i18n="hotspot_menu.hotspot">Hotspot</h3>
         </div>
         
        <div class="grid grid-cols-2 md:grid-cols-1 xl:grid-cols-2 gap-4">
            <!-- Active Hotspot -->
            <div class="sub-card text-center group relative aspect-square flex flex-col justify-center items-center w-full max-w-[140px] mx-auto">
                 <a href="/<?= htmlspecialchars($session) ?>/hotspot/active" class="absolute inset-0 z-10" title="View Active Users"></a>
                <div class="flex justify-center mb-2 text-blue-500 dark:text-blue-400 group-hover:scale-110 transition-transform">
                     <i data-lucide="activity" class="w-6 h-6"></i>
                </div>
                <div class="text-2xl font-bold text-foreground"><?= $hotspot_active ?></div>
                <div class="text-xs text-accents-5 uppercase tracking-wide font-semibold mt-1" data-i18n="status_menu.active">Active</div>
            </div>

            <!-- Users -->
            <div class="sub-card text-center group relative aspect-square flex flex-col justify-center items-center w-full max-w-[140px] mx-auto">
                 <a href="/<?= htmlspecialchars($session) ?>/hotspot/users" class="absolute inset-0 z-10" title="Manage Users"></a>
                <div class="flex justify-center mb-2 text-purple-500 dark:text-purple-400 group-hover:scale-110 transition-transform">
                     <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <div class="text-2xl font-bold text-foreground"><?= htmlspecialchars($hotspot_users['count'] ?? 0) ?></div>
                <div class="text-xs text-accents-5 uppercase tracking-wide font-semibold mt-1" data-i18n="hotspot_menu.users">Users</div>
            </div>

            <!-- Income -->
            <div class="sub-card text-center col-span-2 group">
                 <div class="flex justify-center mb-2 text-yellow-500 dark:text-yellow-400 group-hover:scale-110 transition-transform">
                     <i data-lucide="dollar-sign" class="w-6 h-6"></i>
                </div>
                 <div class="text-2xl font-bold text-foreground">0</div>
                <div class="text-xs text-accents-5 uppercase tracking-wide font-semibold mt-1" data-i18n="dashboard.income_today">Income Today</div>
            </div>
        </div>
    </div>
    
    <!-- Traffic Monitor -->
    <div class="col-span-full card space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 w-full sm:w-auto">
                <div class="flex items-center gap-2">
                    <i data-lucide="activity" class="w-5 h-5 text-blue-500"></i>
                    <h3 class="font-semibold text-lg" data-i18n="dashboard.traffic_monitor">Traffic Monitor</h3>
                </div>
                <div class="relative w-full sm:w-auto">
                    <select id="traffic-interface" class="custom-select w-full sm:w-48">
                        <option value="" disabled selected>Loading...</option>
                    </select>
                </div>
            </div>
             <div class="flex items-center gap-2 text-xs text-accents-5 self-end sm:self-auto">
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span> <span data-i18n="dashboard.rx_download">Rx (Download)</span></span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500"></span> <span data-i18n="dashboard.tx_upload">Tx (Upload)</span></span>
            </div>
        </div>
        <div class="relative h-64 w-full">
            <canvas id="trafficChart"></canvas>
        </div>
    </div>
</div>

<script src="/assets/js/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('trafficChart').getContext('2d');
        const labels = Array(20).fill(''); 
        const rxData = Array(20).fill(0);
        const txData = Array(20).fill(0);

        // Chart Configuration
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: window.i18n ? window.i18n.t('dashboard.rx_download') : 'Rx (Download)',
                        data: rxData,
                        borderColor: '#3b82f6', // blue-500
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 0
                    },
                    {
                        label: window.i18n ? window.i18n.t('dashboard.tx_upload') : 'Tx (Upload)',
                        data: txData,
                        borderColor: '#22c55e', // green-500
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false, // Disable animation for smoother realtime updates
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatBits(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: false,
                        grid: { display: false }
                    },
                    y: {
                        border: { display: false },
                        grid: { color: 'rgba(128, 128, 128, 0.1)' },
                        ticks: {
                            callback: function(value) {
                                return formatBits(value);
                            }
                        },
                        beginAtZero: true
                    }
                }
            }
        });

        // Helper: Format Bits
        function formatBits(bits) {
            if (bits === 0) return '0 bps';
            const units = ['bps', 'Kbps', 'Mbps', 'Gbps'];
            const i = Math.floor(Math.log(bits) / Math.log(1024));
            return parseFloat((bits / Math.pow(1024, i)).toFixed(1)) + ' ' + units[i];
        }

        // Fetch Data
        const session = '<?= htmlspecialchars($session) ?>';
        let currentInterface = null; // Will be set after fetching interfaces

        async function fetchInterfaces() {
            try {
                const response = await fetch(`/${session}/traffic/interfaces`);
                if (!response.ok) return;

                const interfaces = await response.json();
                const select = document.getElementById('traffic-interface');
                select.innerHTML = ''; // access clean

                if (Array.isArray(interfaces)) {
                    interfaces.forEach(iface => {
                        const option = document.createElement('option');
                        option.value = iface.name;
                        option.textContent = iface.name; // Simple name, can add type if needed
                        select.appendChild(option);
                    });

                    // Set default (ether1 or first one)
                    // Priority: Configured Interface > ether1 > First available
                    const configInterface = '<?= $interface ?>'; // From Controller
                    let defaultIface = null;

                    if (configInterface && interfaces.find(i => i.name === configInterface)) {
                        defaultIface = configInterface;
                    } else if (interfaces.find(i => i.name === 'ether1')) {
                         defaultIface = 'ether1';
                    } else {
                        defaultIface = interfaces[0]?.name;
                    }

                    if (defaultIface) {
                        select.value = defaultIface;
                        currentInterface = defaultIface;
                    }
                    
                    // Refresh Custom Select UI
                    if (typeof CustomSelect !== 'undefined' && CustomSelect.instances) {
                        const instance = CustomSelect.instances.find(i => i.originalSelect.id === 'traffic-interface');
                        if (instance) instance.refresh();
                    }
                }
            } catch (err) {
                console.error("Interfaces fetch error:", err);
                document.getElementById('traffic-interface').innerHTML = '<option>Error</option>';
            }
        }
        
        // Handle Change
        document.getElementById('traffic-interface').addEventListener('change', (e) => {
            currentInterface = e.target.value;
            // Clear chart for visual feedback? Or just let it transition
             rxData.fill(0);
             txData.fill(0);
             chart.update();
        });

        async function fetchTraffic() {
            if (!currentInterface) return;

            try {
                // Encode interface name to handle special chars / spaces
                const response = await fetch(`/${session}/traffic/monitor?interface=${encodeURIComponent(currentInterface)}`);
                if (!response.ok) return; // Silent fail
                
                const data = await response.json();
                
                if (data && !data.error) {
                    // Update Data (Shift and Push)
                    chart.data.datasets[0].data.push(parseInt(data['rx-bits-per-second']));
                    chart.data.datasets[0].data.shift();
                    
                    chart.data.datasets[1].data.push(parseInt(data['tx-bits-per-second']));
                    chart.data.datasets[1].data.shift();
                    
                    chart.update('none'); // Update without animation
                }
            } catch (err) {
                console.error("Traffic fetch error:", err);
            }
        }

        // Init
        fetchInterfaces().then(() => {
            // Start Polling after interfaces loaded
            const reloadInterval = <?= ($reload_interval ?? 5) * 1000 ?>; // Convert sec to ms
            setInterval(fetchTraffic, reloadInterval); 
            fetchTraffic();
        });

        // Localization Support
        const updateChartLabels = () => {
            if (window.i18n && window.i18n.isLoaded) {
                const rxLabel = window.i18n.t('dashboard.rx_download');
                const txLabel = window.i18n.t('dashboard.tx_upload');
                
                // Only update if changed
                if (chart.data.datasets[0].label !== rxLabel || chart.data.datasets[1].label !== txLabel) {
                    chart.data.datasets[0].label = rxLabel;
                    chart.data.datasets[1].label = txLabel;
                    chart.update('none');
                }
            }
        };

        // Listen for language changes
        if (window.Mivo) {
            window.Mivo.on('languageChanged', updateChartLabels);
        }
        window.addEventListener('languageChanged', updateChartLabels);
        
        // Try initial update after a short delay to ensure i18n is ready if race condition
        setTimeout(updateChartLabels, 500); 
    });
</script>

<?php require_once ROOT.'/app/Views/layouts/footer_main.php'; ?>
