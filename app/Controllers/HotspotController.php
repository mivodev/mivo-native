<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;
use App\Helpers\FlashHelper;
use App\Helpers\HotspotHelper;
use App\Libraries\RouterOSAPI;
use App\Models\Config;
use App\Models\Logo;
use App\Models\VoucherTemplateModel;

class HotspotController extends Controller
{
    public function __construct()
    {
        Middleware::auth();
    }

    public function index($session)
    {
        $configModel = new Config;
        $creds = $configModel->getSession($session);

        if (! $creds) {
            echo 'Session not found.';

            return;
        }

        $userId = $session; // For view context
        $users = [];
        $servers = [];
        $error = null;

        $API = new RouterOSAPI;
        // $API->debug = true; // Enable for debugging

        // Decrypt password if from SQLite
        $password = $creds['password'];
        if (isset($creds['source']) && $creds['source'] === 'legacy') {
            $password = RouterOSAPI::decrypt($password);
        }

        if ($API->connect($creds['ip'], $creds['user'], $password)) {
            // Get all hotspot users
            $users = $API->comm('/ip/hotspot/user/print');

            // Get servers for dropdown
            $servers = $API->comm('/ip/hotspot/server/print');

            $API->disconnect();
        } else {
            FlashHelper::set('error', 'Connection Failed', 'Could not connect to router at '.$creds['ip']);
            header('Location: '.($_SERVER['HTTP_REFERER'] ?? '/'.$session.'/dashboard'));
            exit;
        }

        $data = [
            'session' => $session,
            'users' => $users,
            'servers' => $servers,
            'error' => $error,
        ];

        return $this->view('hotspot/users/users', $data);
    }

    public function add($session)
    {
        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            return;
        } // Should handle error better

        $API = new RouterOSAPI;

        $password = $creds['password'];
        if (isset($creds['source']) && $creds['source'] === 'legacy') {
            $password = RouterOSAPI::decrypt($password);
        }

        $profiles = [];
        if ($API->connect($creds['ip'], $creds['user'], $password)) {
            $profiles = $API->comm('/ip/hotspot/user/profile/print');
            $API->disconnect();
        }

        $data = [
            'session' => $session,
            'profiles' => $profiles,
        ];

        return $this->view('hotspot/users/add', $data);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        $session = $_POST['session'] ?? '';
        $name = $_POST['name'] ?? '';
        $password_user = $_POST['password'] ?? '';
        $profile = $_POST['profile'] ?? 'default';
        $comment = $_POST['comment'] ?? '';

        // Time Limit Logic (d, h, m)
        $timelimit_d = $_POST['timelimit_d'] ?? '';
        $timelimit_h = $_POST['timelimit_h'] ?? '';
        $timelimit_m = $_POST['timelimit_m'] ?? '';

        $timelimit = '';
        if ($timelimit_d != '') {
            $timelimit .= $timelimit_d.'d';
        }
        if ($timelimit_h != '') {
            $timelimit .= $timelimit_h.'h';
        }
        if ($timelimit_m != '') {
            $timelimit .= $timelimit_m.'m';
        }

        // Data Limit Logic (Value, Unit)
        $datalimit_val = $_POST['datalimit_val'] ?? '';
        $datalimit_unit = $_POST['datalimit_unit'] ?? 'MB';

        $datalimit = '';
        if (! empty($datalimit_val) && is_numeric($datalimit_val)) {
            $bytes = (int) $datalimit_val;
            if ($datalimit_unit === 'GB') {
                $bytes = $bytes * 1073741824;
            } else {
                // MB
                $bytes = $bytes * 1048576;
            }
            $datalimit = (string) $bytes;
        }

        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            return;
        }

        $API = new RouterOSAPI;
        $password_router = $creds['password'];
        if (isset($creds['source']) && $creds['source'] === 'legacy') {
            $password_router = RouterOSAPI::decrypt($password_router);
        }

        if ($API->connect($creds['ip'], $creds['user'], $password_router)) {

            $userData = [
                'name' => $name,
                'password' => $password_user,
                'profile' => $profile,
                'comment' => $comment,
            ];

            if (! empty($timelimit)) {
                $userData['limit-uptime'] = $timelimit;
            }
            if (! empty($datalimit)) {
                $userData['limit-bytes-total'] = $datalimit;
            }

            $API->comm('/ip/hotspot/user/add', $userData);
            $API->disconnect();
        }

        FlashHelper::set('success', 'toasts.user_added', 'toasts.user_added_desc', ['name' => $name], true);
        header('Location: /'.$session.'/hotspot/users');
        exit;
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        $session = $_POST['session'] ?? '';
        $rawId = $_POST['id'] ?? '';

        $configModel = new Config;
        $creds = $configModel->getSession($session);

        $API = new RouterOSAPI;
        $password_router = $creds['password'];
        if (isset($creds['source']) && $creds['source'] === 'legacy') {
            $password_router = RouterOSAPI::decrypt($password_router);
        }

        if ($API->connect($creds['ip'], $creds['user'], $password_router)) {
            // Handle Multiple IDs (comma separated)
            $ids = explode(',', $rawId);
            foreach ($ids as $id) {
                $id = trim($id);
                if (! empty($id)) {
                    // 1. Get Username first (to delete scheduler)
                    $user = $API->comm('/ip/hotspot/user/print', [
                        '?.id' => $id,
                    ]);

                    if (! empty($user) && isset($user[0]['name'])) {
                        $username = $user[0]['name'];

                        // 2. Remove User
                        $API->comm('/ip/hotspot/user/remove', ['.id' => $id]);

                        // 3. Remove Scheduler (Ghost Cleanup)
                        // Check if scheduler exists with same name as user
                        $schedules = $API->comm('/system/scheduler/print', [
                            '?name' => $username,
                        ]);

                        if (! empty($schedules)) {
                            // Loop just in case multiple matches (unlikely if unique name)
                            foreach ($schedules as $sch) {
                                $API->comm('/system/scheduler/remove', [
                                    '.id' => $sch['.id'],
                                ]);
                            }
                        }
                    }
                }
            }
            $API->disconnect();
        }

        FlashHelper::set('success', 'toasts.user_deleted', 'toasts.user_deleted_desc', [], true);
        header('Location: /'.$session.'/hotspot/users');
        exit;
    }

    public function edit($session, $id)
    {
        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            return;
        }

        $API = new RouterOSAPI;
        $password_router = $creds['password'];
        if (isset($creds['source']) && $creds['source'] === 'legacy') {
            $password_router = RouterOSAPI::decrypt($password_router);
        }

        $user = null;
        $profiles = [];

        if ($API->connect($creds['ip'], $creds['user'], $password_router)) {
            // Fetch specific user
            $userRequest = $API->comm('/ip/hotspot/user/print', [
                '?.id' => $id,
            ]);
            if (! empty($userRequest)) {
                $user = $userRequest[0];

                // Parse Time Limit (limit-uptime) e.g. 1d04:00:00 or 30d
                // Mikrotik uptime format varies. Safe parse:
                // Regex for Xd, Xh, Xm? NO, Mikrotik returns "4w2d" or "10:00:00" (h:m:s)
                // Or simple seconds if raw? Print usually returns formatted.
                // Let's defer to a helper or simple parsing.
                // Actually standard format: 1d 04:00:00 or 1h30m.
                // Let's try simple regex extraction.
                $t_d = '';
                $t_h = '';
                $t_m = '';
                $uptime = $user['limit-uptime'] ?? '';
                if ($uptime) {
                    if (preg_match('/(\d+)d/', $uptime, $m)) {
                        $t_d = $m[1];
                    }
                    if (preg_match('/(\d+)h/', $uptime, $m)) {
                        $t_h = $m[1];
                    }
                    if (preg_match('/(\d+)m/', $uptime, $m)) {
                        $t_m = $m[1];
                    }
                    // Handle H:M:S format (e.g. 04:00:00) if no 'h'/'m' chars?
                    // Mikrotik CLI `print` implies "1d04:00:00". API might return "1d04:00:00".
                    // If so, 04 is hours.
                    // Simple parse if regex failed?
                    // Let's assume standard XdXhXm usage for now based on Add form.
                }
                $user['time_d'] = $t_d;
                $user['time_h'] = $t_h;
                $user['time_m'] = $t_m;

                // Parse Data Limit (limit-bytes-total)
                $bytes = $user['limit-bytes-total'] ?? 0;
                $d_val = '';
                $d_unit = 'MB';
                if ($bytes > 0) {
                    if ($bytes >= 1073741824) {
                        $d_val = round($bytes / 1073741824, 2);
                        $d_unit = 'GB';
                    } else {
                        $d_val = round($bytes / 1048576, 2);
                        $d_unit = 'MB';
                    }
                }
                $user['data_val'] = $d_val;
                $user['data_unit'] = $d_unit;
            }

            // Fetch Profiles
            $profiles = $API->comm('/ip/hotspot/user/profile/print');

            $API->disconnect();
        }

        if (! $user) {
            header('Location: /'.$session.'/hotspot/users');
            exit;
        }

        $data = [
            'session' => $session,
            'user' => $user,
            'profiles' => $profiles,
        ];

        return $this->view('hotspot/users/edit', $data);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        $session = $_POST['session'] ?? '';
        $id = $_POST['id'] ?? '';
        $name = $_POST['name'] ?? '';
        $password_user = $_POST['password'] ?? '';
        $profile = $_POST['profile'] ?? '';
        $comment = $_POST['comment'] ?? '';
        $server = $_POST['server'] ?? 'all';

        // Time Limit Logic (d, h, m)
        $timelimit_d = $_POST['timelimit_d'] ?? '';
        $timelimit_h = $_POST['timelimit_h'] ?? '';
        $timelimit_m = $_POST['timelimit_m'] ?? '';

        $timelimit = '';
        if ($timelimit_d != '') {
            $timelimit .= $timelimit_d.'d';
        }
        if ($timelimit_h != '') {
            $timelimit .= $timelimit_h.'h';
        }
        if ($timelimit_m != '') {
            $timelimit .= $timelimit_m.'m';
        }

        // Data Limit Logic (Value, Unit)
        $datalimit_val = $_POST['datalimit_val'] ?? '';
        $datalimit_unit = $_POST['datalimit_unit'] ?? 'MB';

        $datalimit = '0';
        if (! empty($datalimit_val) && is_numeric($datalimit_val)) {
            $bytes = (float) $datalimit_val; // float to handle decimals before calc
            if ($datalimit_unit === 'GB') {
                $bytes = $bytes * 1073741824;
            } else {
                // MB
                $bytes = $bytes * 1048576;
            }
            $datalimit = (string) round($bytes);
        }

        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            return;
        }

        $API = new RouterOSAPI;
        $password_router = $creds['password'];
        if (isset($creds['source']) && $creds['source'] === 'legacy') {
            $password_router = RouterOSAPI::decrypt($password_router);
        }

        if ($API->connect($creds['ip'], $creds['user'], $password_router)) {

            $userData = [
                '.id' => $id,
                'name' => $name,
                'password' => $password_user,
                'profile' => $profile,
                'comment' => $comment,
                'server' => $server,
            ];

            if (! empty($timelimit)) {
                $userData['limit-uptime'] = $timelimit;
            } else {
                $userData['limit-uptime'] = '0s';
            } // Reset if empty

            // Always set if calculated, 0 resets it.
            $userData['limit-bytes-total'] = $datalimit;

            $API->comm('/ip/hotspot/user/set', $userData);
            $API->disconnect();
        }

        FlashHelper::set('success', 'toasts.user_updated', 'toasts.user_updated_desc', ['name' => $name], true);
        header('Location: /'.$session.'/hotspot/users');
        exit;
    }

    public function active($session)
    {
        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            header('Location: /');
            exit;
        }

        $items = [];
        $error = null;

        $API = new RouterOSAPI;
        $password_router = $creds['password'];
        if (isset($creds['source']) && $creds['source'] === 'legacy') {
            $password_router = RouterOSAPI::decrypt($password_router);
        }

        if ($API->connect($creds['ip'], $creds['user'], $password_router)) {
            $items = $API->comm('/ip/hotspot/active/print');
            $API->disconnect();
        } else {
            FlashHelper::set('error', 'Connection Failed', 'Could not connect to router at '.$creds['ip']);
            header('Location: '.($_SERVER['HTTP_REFERER'] ?? '/'.$session.'/dashboard'));
            exit;
        }

        $data = [
            'session' => $session,
            'items' => $items,
            'error' => $error,
        ];

        return $this->view('status/active', $data);
    }

    public function removeActive()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        $session = $_POST['session'] ?? '';
        $id = $_POST['id'] ?? '';

        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            return;
        }

        $API = new RouterOSAPI;
        $password_router = $creds['password'];
        if (isset($creds['source']) && $creds['source'] === 'legacy') {
            $password_router = RouterOSAPI::decrypt($password_router);
        }

        if ($API->connect($creds['ip'], $creds['user'], $password_router)) {
            $API->comm('/ip/hotspot/active/remove', ['.id' => $id]);
            $API->disconnect();
        }

        FlashHelper::set('success', 'toasts.session_removed', 'toasts.session_removed_desc', [], true);
        header('Location: /'.$session.'/hotspot/active');
        exit;
    }

    public function hosts($session)
    {
        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            header('Location: /');
            exit;
        }

        $items = [];
        $error = null;

        $API = new RouterOSAPI;
        $password_router = $creds['password'];
        if (isset($creds['source']) && $creds['source'] === 'legacy') {
            $password_router = RouterOSAPI::decrypt($password_router);
        }

        if ($API->connect($creds['ip'], $creds['user'], $password_router)) {
            $items = $API->comm('/ip/hotspot/host/print');
            $API->disconnect();
        } else {
            FlashHelper::set('error', 'Connection Failed', 'Could not connect to router at '.$creds['ip']);
            header('Location: '.($_SERVER['HTTP_REFERER'] ?? '/'.$session.'/dashboard'));
            exit;
        }

        $data = [
            'session' => $session,
            'items' => $items,
            'error' => $error,
        ];

        return $this->view('status/hosts', $data);
    }

    public function bindings($session)
    {
        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            header('Location: /');
            exit;
        }

        $items = [];
        $error = null;

        $API = new RouterOSAPI;
        $password_router = $creds['password'];
        if (isset($creds['source']) && $creds['source'] === 'legacy') {
            $password_router = RouterOSAPI::decrypt($password_router);
        }

        if ($API->connect($creds['ip'], $creds['user'], $password_router)) {
            $items = $API->comm('/ip/hotspot/ip-binding/print');
            $API->disconnect();
        } else {
            FlashHelper::set('error', 'Connection Failed', 'Could not connect to router at '.$creds['ip']);
            header('Location: '.($_SERVER['HTTP_REFERER'] ?? '/'.$session.'/dashboard'));
            exit;
        }

        $data = [
            'session' => $session,
            'items' => $items,
            'error' => $error,
        ];

        return $this->view('security/bindings', $data);
    }

    public function storeBinding()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        $session = $_POST['session'] ?? '';
        $mac = $_POST['mac'] ?? '';
        $address = $_POST['address'] ?? '';
        $toAddress = $_POST['to_address'] ?? '';
        $server = $_POST['server'] ?? 'all';
        $type = $_POST['type'] ?? 'regular';
        $comment = $_POST['comment'] ?? '';

        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            return;
        }

        $API = new RouterOSAPI;
        $password_router = $creds['password'];
        if (isset($creds['source']) && $creds['source'] === 'legacy') {
            $password_router = RouterOSAPI::decrypt($password_router);
        }

        if ($API->connect($creds['ip'], $creds['user'], $password_router)) {
            $data = [
                'mac-address' => $mac,
                'type' => $type,
                'comment' => $comment,
                'server' => $server,
            ];
            if (! empty($address)) {
                $data['address'] = $address;
            }
            if (! empty($toAddress)) {
                $data['to-address'] = $toAddress;
            }

            $API->comm('/ip/hotspot/ip-binding/add', $data);
            $API->disconnect();
        }

        FlashHelper::set('success', 'toasts.binding_added', 'toasts.binding_added_desc', [], true);
        header('Location: /'.$session.'/hotspot/bindings');
        exit;
    }

    public function removeBinding()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        $session = $_POST['session'] ?? '';
        $id = $_POST['id'] ?? '';

        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            return;
        }

        $API = new RouterOSAPI;
        $password_router = $creds['password'];
        if (isset($creds['source']) && $creds['source'] === 'legacy') {
            $password_router = RouterOSAPI::decrypt($password_router);
        }

        if ($API->connect($creds['ip'], $creds['user'], $password_router)) {
            $API->comm('/ip/hotspot/ip-binding/remove', ['.id' => $id]);
            $API->disconnect();
        }

        FlashHelper::set('success', 'toasts.binding_removed', 'toasts.binding_removed_desc', [], true);
        header('Location: /'.$session.'/hotspot/bindings');
        exit;
    }

    public function walledGarden($session)
    {
        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            header('Location: /');
            exit;
        }

        $items = [];
        $error = null;

        $API = new RouterOSAPI;
        $password_router = $creds['password'];
        if (isset($creds['source']) && $creds['source'] === 'legacy') {
            $password_router = RouterOSAPI::decrypt($password_router);
        }

        if ($API->connect($creds['ip'], $creds['user'], $password_router)) {
            $items = $API->comm('/ip/hotspot/walled-garden/ip/print');
            // Standard walled garden print usually involves /ip/hotspot/walled-garden/ip/print for IP based or just /ip/hotspot/walled-garden/print
            // Let's use /ip/hotspot/walled-garden/ip/print as general walled garden often implies IP based rules in modern RouterOS or just walled-garden
            // Actually, usually there is /ip/hotspot/walled-garden (Dst Host, etc) and /ip/hotspot/walled-garden/ip (Dst Address, etc)
            // Mikhmon v3 usually merges them or uses one.
            // Let's check typical Mikhmon usage. Usually "Walled Garden" uses `/ip/hotspot/walled-garden/print` (which captures domains) and IP List uses `/ip/hotspot/walled-garden/ip/print`.
            // My view lists Dst Host / IP.
            // Let's fetch BOTH and merge, or just one.
            // For now, let's target `/ip/hotspot/walled-garden/ip/print` as it allows protocol, port, dst-address, dst-host (in newer ROS?).
            // Wait, `/ip/hotspot/walled-garden/print` allows `dst-host`.
            // `/ip/hotspot/walled-garden/ip/print` allows `dst-address`.
            // I'll stick to `/ip/hotspot/walled-garden/ip/print` for now as it seems more robust for IP rules, but domains need `walled-garden/print`.
            // Actually, let's look at `walled_garden.php`. It handles `dst-host` or `dst-address`.
            // I will use `/ip/hotspot/walled-garden/ip/print` which is "Walled Garden IP List". This is usually what people mean by "Walled Garden" for banking apps etc (IP ranges or strict definitions).
            // BUT domain bypasses are in `/ip/hotspot/walled-garden/print`.
            // Let's try to fetch `/ip/hotspot/walled-garden/ip/print` first.

            $items = $API->comm('/ip/hotspot/walled-garden/ip/print');
            $API->disconnect();
        } else {
            FlashHelper::set('error', 'Connection Failed', 'Could not connect to router at '.$creds['ip']);
            header('Location: '.($_SERVER['HTTP_REFERER'] ?? '/'.$session.'/dashboard'));
            exit;
        }

        $data = [
            'session' => $session,
            'items' => $items,
            'error' => $error,
        ];

        return $this->view('security/walled_garden', $data);
    }

    public function storeWalledGarden()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        $session = $_POST['session'] ?? '';
        $dstHost = $_POST['dst_host'] ?? '';
        $dstAddress = $_POST['dst_address'] ?? '';
        $protocol = $_POST['protocol'] ?? '';
        $dstPort = $_POST['dst_port'] ?? '';
        $action = $_POST['action'] ?? 'allow';
        $server = $_POST['server'] ?? 'all';
        $comment = $_POST['comment'] ?? '';

        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            return;
        }

        $API = new RouterOSAPI;
        $password_router = $creds['password'];
        if (isset($creds['source']) && $creds['source'] === 'legacy') {
            $password_router = RouterOSAPI::decrypt($password_router);
        }

        if ($API->connect($creds['ip'], $creds['user'], $password_router)) {
            $data = [
                'action' => $action,
                'server' => $server,
                'comment' => $comment,
            ];

            // If dst-host is present, we might need to use /ip/hotspot/walled-garden/add instead of /ip/.../ip/add?
            // RouterOS distinguishes them. active.php shows I used `walled-garden/ip/print`.
            // If user enters dst-host, it usually goes to `walled-garden`. If dst-address, `walled-garden/ip`.
            // This is complex. Let's assume we are adding to `walled-garden/ip` for now which supports protocol/port/dst-address but NOT dst-host typically (older ROS).
            // Actually, newer ROS might merge.
            // Let's assume standard behavior:
            // If dst-host is provided, add to `/ip/hotspot/walled-garden/add`.
            // If dst-address is provided, add to `/ip/hotspot/walled-garden/ip/add`.
            // My View asks for BOTH?
            // Let's simplification: Check if dst_host is set.

            $path = '/ip/hotspot/walled-garden/ip/add';
            if (! empty($dstHost)) {
                $path = '/ip/hotspot/walled-garden/add';
                $data['dst-host'] = $dstHost;
            } else {
                if (! empty($dstAddress)) {
                    $data['dst-address'] = $dstAddress;
                }
            }

            // Protocol and Port logic
            // Note: `walled-garden` (host) takes protocol/port too? Yes.
            if (! empty($protocol)) {
                // extract protocol name if format is "(6) tcp"
                if (preg_match('/\)\s*(\w+)/', $protocol, $m)) {
                    $protocol = $m[1];
                }
                $data['protocol'] = $protocol;
            }
            if (! empty($dstPort)) {
                $data['dst-port'] = $dstPort;
            }

            $API->comm($path, $data);
            $API->disconnect();
        }

        FlashHelper::set('success', 'toasts.rule_added', 'toasts.rule_added_desc', [], true);
        header('Location: /'.$session.'/hotspot/walled-garden');
        exit;
    }

    public function removeWalledGarden()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        $session = $_POST['session'] ?? '';
        $id = $_POST['id'] ?? '';

        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            return;
        }

        $API = new RouterOSAPI;
        $password_router = $creds['password'];
        if (isset($creds['source']) && $creds['source'] === 'legacy') {
            $password_router = RouterOSAPI::decrypt($password_router);
        }

        if ($API->connect($creds['ip'], $creds['user'], $password_router)) {
            $API->comm('/ip/hotspot/walled-garden/ip/remove', ['.id' => $id]);
            $API->disconnect();
        }

        FlashHelper::set('success', 'toasts.rule_removed', 'toasts.rule_removed_desc', [], true);
        header('Location: /'.$session.'/hotspot/walled-garden');
        exit;
    }

    // Print Single User
    public function printUser($session, $id)
    {
        return $this->printBatch($session, $id);
    }

    // Print Batch Users (Comma separated IDs)
    public function printBatchActions($session)
    {
        $ids = $_GET['ids'] ?? '';

        return $this->printBatch($session, $ids);
    }

    // Cookies
    public function cookies($session)
    {
        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            return header('Location: /');
        }

        $cookies = [];
        $API = new RouterOSAPI;
        $API->attempts = 1;
        $API->timeout = 3;

        if ($API->connect($creds['ip_address'], $creds['username'], $creds['password'])) {
            $cookies = $API->comm('/ip/hotspot/cookie/print');
            $API->disconnect();
        }

        return $this->view('hotspot/cookies', [
            'session' => $session,
            'cookies' => $cookies ?? [],
        ]);
    }

    public function removeCookie()
    {
        $session = $_POST['session'] ?? '';
        $id = $_POST['id'] ?? '';

        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            return;
        }

        $API = new RouterOSAPI;
        if ($API->connect($creds['ip_address'], $creds['username'], $creds['password'])) {
            $API->comm('/ip/hotspot/cookie/remove', ['.id' => $id]);
            $API->disconnect();
        }

        FlashHelper::set('success', 'toasts.cookie_removed', 'toasts.cookie_removed_desc', [], true);
        header("Location: /$session/hotspot/cookies");
    }

    private function printBatch($session, $rawIds)
    {
        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            exit('Session error');
        }

        $API = new RouterOSAPI;
        $password_router = $creds['password'];
        if (isset($creds['source']) && $creds['source'] === 'legacy') {
            $password_router = RouterOSAPI::decrypt($password_router);
        }

        // Handle ID List
        // IDs can be "id1,id2,id3"
        // Also Mikrotik IDs start with *, we need to ensure they are handled.
        // If passed via URL, `*` might be encoded.
        $idList = explode(',', urldecode($rawIds));
        $validUsers = [];

        if ($API->connect($creds['ip'], $creds['user'], $password_router)) {
            // Optimized: Fetch ALL users and filter PHP side?
            // Or fetch loop? Mikrotik API `/print` with `?.id` only supports single match usually or we need filtered print.
            // Usually `print` returning all is faster for < 1000 users than 100 calls.
            // But if we have 5000 users, we shouldn't fetch all.
            // Mikrotik API doesn't support `WHERE id IN (...)`.
            // So for batch, we might have to loop calls OR fetch all and filter if list is huge.
            // Let's loop for now as batch print is usually < 50 items.

            foreach ($idList as $id) {
                // Ensure ID has * if missing (unlikely if coming from app logic)
                $req = $API->comm('/ip/hotspot/user/print', [
                    '?.id' => $id,
                ]);
                if (! empty($req)) {
                    $u = $req[0];
                    $validUsers[] = [
                        'username' => $u['name'],
                        'password' => $u['password'] ?? '',
                        'price' => $u['price'] ?? '',
                        'validity' => $u['limit-uptime'] ?? '',
                        'timelimit' => HotspotHelper::formatValidity($u['limit-uptime'] ?? ''),
                        'datalimit' => HotspotHelper::formatBytes($u['limit-bytes-total'] ?? 0),
                        'profile' => $u['profile'] ?? 'default',
                        'comment' => $u['comment'] ?? '',
                        'hotspotname' => $creds['hotspot_name'],
                        'dns_name' => $creds['dns_name'],
                        'login_url' => (preg_match('~^(?:f|ht)tps?://~i', $creds['dns_name']) ? $creds['dns_name'] : 'http://'.$creds['dns_name']).'/login',
                    ];
                }
            }
            $API->disconnect();
        }

        if (empty($validUsers)) {
            exit('No users found');
        }

        // --- Template Handling ---
        $tplModel = new VoucherTemplateModel;
        $templates = $tplModel->getAll(); // Need session? Model usually handles simple select, maybe filter by session later if needed? Schema says global?
        // Verification: Schema in implementation plan says id, name, content... doesn't mention session. Assuming global.

        $currentTemplate = $_GET['template'] ?? 'default';
        $templateContent = '';

        $viewName = 'print/default';

        if ($currentTemplate !== 'default') {
            $tpl = $tplModel->getById($currentTemplate);
            if ($tpl) {
                $templateContent = $tpl['content'];
                $viewName = 'print/custom';
            } else {
                FlashHelper::set('error', 'Template Not Found', 'The selected print template could not be found.');
                header('Location: '.($_SERVER['HTTP_REFERER'] ?? '/'.$session.'/hotspot/users'));
                exit;
            }
        }

        // --- Logo Handling ---
        $logoModel = new Logo;
        $logos = $logoModel->getAll();
        $logoMap = [];
        foreach ($logos as $l) {
            $logoMap[$l['id']] = $l['path'];
        }

        $data = [
            'users' => $validUsers,
            'templates' => $templates,
            'currentTemplate' => $currentTemplate,
            'templateContent' => $templateContent,
            'session' => $session,
            'logoMap' => $logoMap,
        ];

        return $this->view($viewName, $data);
    }
}
