<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;
use App\Helpers\FlashHelper;
use App\Libraries\RouterOSAPI;
use App\Models\Config;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Auth handled by Router Middleware
    }

    public function index($session)
    {
        $configModel = new Config;
        $creds = $configModel->getSession($session);

        if (! $creds) {
            echo 'Session not found.';

            return;
        }

        // Mock Data for Demo (SQLite or Legacy)
        if ($session === 'demo') {
            $data = [
                'session' => $session,
                'clock' => ['time' => '12:00:00', 'date' => 'jan/01/2024'],
                'resource' => [
                    'board-name' => 'CHR (Demo SQLite)',
                    'version' => '7.12',
                    'uptime' => '1w 2d 3h',
                    'cpu-load' => '15',
                    'free-memory' => 1048576 * 512, // 512 MB
                    'free-hdd-space' => 1048576 * 1024, // 1 GB
                ],
                // ... rest of mock data
                'routerboard' => ['model' => 'x86_64'],
                'hotspot_active' => 25,
                'hotspot_users' => 150,
                'lang' => [
                    'system_date_time' => 'System Date & Time',
                    'uptime' => 'Uptime',
                    'board_name' => 'Board Name',
                    'model' => 'Model',
                    'cpu_load' => 'CPU Load',
                    'free_memory' => 'Free Memory',
                    'free_hdd' => 'Free HDD',
                    'hotspot_active' => 'Hotspot Active',
                    'hotspot_users' => 'Hotspot Users',
                ],
            ];

            return $this->view('dashboard', $data);
        }

        $API = new RouterOSAPI;

        // Determine password: if legacy, decrypt it. If SQLite (new), assume plain for now
        // (since we just seeded 'admin' plain in setup_database.php) or decrypt if you decide to encrypt in DB.
        // For this Demo, setup_database.php inserted plain 'admin'.
        // Existing v3 passwords are encrypted.

        $password = $creds['password'];
        if (isset($creds['source']) && $creds['source'] === 'legacy') {
            $password = RouterOSAPI::decrypt($password);
        }

        if ($API->connect($creds['ip'], $creds['user'], $password)) {
            // ... API calls
            $getclock = $API->comm('/system/clock/print');
            $clock = $getclock[0] ?? [];

            $getresource = $API->comm('/system/resource/print');
            $resource = $getresource[0] ?? [];

            $getrouterboard = $API->comm('/system/routerboard/print');
            $routerboard = $getrouterboard[0] ?? [];

            $counthotspotactive = $API->comm('/ip/hotspot/active/print', ['count-only' => '']);
            $countallusers = $API->comm('/ip/hotspot/user/print', ['count-only' => '']);

            $API->disconnect();

            $data = [
                'session' => $session,
                'clock' => $clock,
                'resource' => $resource,
                'routerboard' => $routerboard,
                'hotspot_active' => $counthotspotactive,
                'hotspot_users' => $countallusers,
                'lang' => [
                    'system_date_time' => 'System Date & Time',
                    'uptime' => 'Uptime',
                    'board_name' => 'Board Name',
                    'model' => 'Model',
                    'cpu_load' => 'CPU Load',
                    'free_memory' => 'Free Memory',
                    'free_hdd' => 'Free HDD',
                    'hotspot_active' => 'Hotspot Active',
                    'hotspot_users' => 'Hotspot Users',
                    'hotspot_users' => 'Hotspot Users',
                ],
                'reload_interval' => $creds['reload'] ?? 5, // Default 5s if not set
                'interface' => $creds['interface'] ?? 'ether1',
            ];

            // Pass Users Link (Optional: could be part of layout or card link)
            // Ideally, the "Hotspot Users" card on dashboard should be clickable.
            return $this->view('dashboard', $data);

        } else {
            FlashHelper::set('error', 'Connection Failed', 'Could not connect to router at '.$creds['ip']);
            header('Location: '.($_SERVER['HTTP_REFERER'] ?? '/'));
            exit;
        }
    }
}
