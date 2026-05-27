<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Middleware;
use App\Helpers\FlashHelper;
use App\Helpers\HotspotHelper;
use App\Libraries\RouterOSAPI;
use App\Models\Config;
use App\Models\Logo;
use App\Models\QuickPrintModel;
use App\Models\VoucherTemplateModel;

class QuickPrintController extends Controller
{
    public function __construct()
    {
        Middleware::auth();
    }

    // Dashboard: List Cards
    public function index($session)
    {
        $qpModel = new QuickPrintModel;

        $configModel = new Config;
        $creds = $configModel->getSession($session);
        $routerId = $creds['id'] ?? null;

        // If no ID (Legacy), fallback to empty list or handle gracefully.
        // For now, we assume ID exists as per migration plan.
        $packages = $routerId ? $qpModel->getAllByRouterId($routerId) : [];

        $data = [
            'session' => $session,
            'packages' => $packages,
        ];

        // Note: View will be 'quick_print/index'
        return $this->view('quick_print/index', $data);
    }

    // List/Manage Packages (CRUD)
    public function manage($session)
    {
        $qpModel = new QuickPrintModel;

        $configModel = new Config;
        $creds = $configModel->getSession($session);
        $routerId = $creds['id'] ?? null;

        $packages = $routerId ? $qpModel->getAllByRouterId($routerId) : [];
        $profiles = [];
        if ($creds) {
            $API = new RouterOSAPI;
            $password = $creds['password'];
            if (isset($creds['source']) && $creds['source'] === 'legacy') {
                $password = RouterOSAPI::decrypt($password);
            }
            if ($API->connect($creds['ip'], $creds['user'], $password)) {
                $profiles = $API->comm('/ip/hotspot/user/profile/print');
                $API->disconnect();
            }
        }

        $data = [
            'session' => $session,
            'packages' => $packages,
            'profiles' => $profiles,
        ];

        return $this->view('quick_print/list', $data);
    }

    // CRUD: Store
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $session = $_POST['session'] ?? '';

        $configModel = new Config;
        $creds = $configModel->getSession($session);
        $routerId = $creds['id'] ?? 0;

        $data = [
            'router_id' => $routerId,
            'session_name' => $session,
            'name' => $_POST['name'] ?? 'Package',
            'server' => $_POST['server'] ?? 'all',
            'profile' => $_POST['profile'] ?? 'default',
            'prefix' => $_POST['prefix'] ?? '',
            'char_length' => $_POST['char_length'] ?? 4,
            'price' => $_POST['price'] ?? 0,
            'selling_price' => $_POST['selling_price'] ?? ($_POST['price'] ?? 0),
            'time_limit' => $_POST['time_limit'] ?? '',
            'data_limit' => $_POST['data_limit'] ?? '',
            'comment' => $_POST['comment'] ?? '',
            'color' => $_POST['color'] ?? 'bg-blue-500',
        ];

        $qpModel = new QuickPrintModel;
        $qpModel->add($data);

        FlashHelper::set('success', 'toasts.package_saved', 'toasts.package_saved_desc', [], true);
        header('Location: /'.$session.'/quick-print/manage');
        exit;
    }

    // CRUD: Update
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $session = $_POST['session'] ?? '';
        $id = $_POST['id'] ?? '';

        if (empty($id)) {
            FlashHelper::set('error', 'common.error', 'toasts.error_missing_id', [], true);
            header('Location: /'.$session.'/quick-print/manage');
            exit;
        }

        $data = [
            'name' => $_POST['name'] ?? 'Package',
            'profile' => $_POST['profile'] ?? 'default',
            'prefix' => $_POST['prefix'] ?? '',
            'char_length' => $_POST['char_length'] ?? 4,
            'price' => $_POST['price'] ?? 0,
            'selling_price' => $_POST['selling_price'] ?? ($_POST['price'] ?? 0),
            'time_limit' => $_POST['time_limit'] ?? '',
            'data_limit' => $_POST['data_limit'] ?? '',
            'comment' => $_POST['comment'] ?? '',
            'color' => $_POST['color'] ?? 'bg-blue-500',
        ];

        $qpModel = new QuickPrintModel;
        $qpModel->update($id, $data); // Assuming update method exists in simple JSON model

        FlashHelper::set('success', 'toasts.package_updated', 'toasts.package_updated_desc', [], true);
        header('Location: /'.$session.'/quick-print/manage');
        exit;
    }

    // CRUD: Delete
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        $session = $_POST['session'] ?? '';
        $id = $_POST['id'] ?? '';

        $qpModel = new QuickPrintModel;
        $qpModel->delete($id);

        FlashHelper::set('success', 'toasts.package_deleted', 'toasts.package_deleted_desc', [], true);
        header('Location: /'.$session.'/quick-print/manage');
        exit;
    }

    // ACTION: Generate User & Print
    public function printPacket($session, $id)
    {
        // 1. Get Package Details
        $qpModel = new QuickPrintModel;
        $package = $qpModel->getById($id);

        if (! $package) {
            exit('Package not found');
        }

        // 2. Generate Credentials
        $prefix = $package['prefix'];
        $length = $package['char_length'];
        $charSet = '1234567890abcdefghijklmnopqrstuvwxyz'; // Simple lowercase + num
        $rand = substr(str_shuffle($charSet), 0, $length);
        $username = $prefix.$rand;
        $password = $username; // Default: user=pass (User Mode) - Can be improved later

        // 3. Connect to Mikrotik & Add User
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

        if ($API->connect($creds['ip'], $creds['user'], $password_router)) {
            $userData = [
                'name' => $username,
                'password' => $password,
                'profile' => $package['profile'],
                'comment' => $package['comment'].' [QP]', // Mark as QuickPrint
            ];

            // Limits
            if (! empty($package['time_limit'])) {
                $userData['limit-uptime'] = $package['time_limit'];
            }
            if (! empty($package['data_limit'])) {
                // Check if M or G
                // Simple logic for now, assuming raw if number, or passing string if Mikrotik accepts it (usually requires bytes)
                // Let's assume user inputs "100M" or "1G" which usually needs parsing.
                // For now, let's assume input is NUMBER in MB as per standard Mivo practice, OR generic string.
                // We'll pass as is for strings, or multiply if strictly numeric?
                // Let's rely on standard Mikrotik parsing if string passed, or convert.
                // Mivo usually uses dropdown "MB/GB".
                // Implementing simple conversion:
                $val = intval($package['data_limit']);
                if (strpos(strtolower($package['data_limit']), 'g') !== false) {
                    $userData['limit-bytes-total'] = $val * 1024 * 1024 * 1024;
                } else {
                    $userData['limit-bytes-total'] = $val * 1024 * 1024; // Default MB
                }
            }

            $API->comm('/ip/hotspot/user/add', $userData);
            $API->disconnect();
        } else {
            FlashHelper::set('error', 'Connection Failed', 'Could not connect to router at '.$creds['ip']);
            header('Location: '.($_SERVER['HTTP_REFERER'] ?? '/'.$session.'/quick-print/manage'));
            exit;
        }

        // 4. Render Template
        $tplModel = new VoucherTemplateModel;
        $templates = $tplModel->getAll();

        $currentTemplate = $_GET['template'] ?? 'default';
        $templateContent = '';
        $viewName = 'print/default';

        if ($currentTemplate !== 'default') {
            $tpl = $tplModel->getById($currentTemplate);
            if ($tpl) {
                $templateContent = $tpl['content'];
                $viewName = 'print/custom';
            } else {
                $currentTemplate = 'default';
            }
        }

        // Calculate bytes for display
        $dlVal = intval($package['data_limit']);
        $bytes = (strpos(strtolower($package['data_limit']), 'g') !== false) ? $dlVal * 1024 * 1024 * 1024 : $dlVal * 1024 * 1024;

        $userDataValues = [
            'username' => $username,
            'password' => $password,
            'price' => $package['price'],
            'validity' => $package['time_limit'],
            'timelimit' => HotspotHelper::formatValidity($package['time_limit']),
            'datalimit' => HotspotHelper::formatBytes($bytes),
            'profile' => $package['profile'],
            'comment' => 'Quick Print',
            'hotspotname' => $creds['hotspot_name'],
            'dns_name' => $creds['dns_name'],
            'login_url' => (preg_match('~^(?:f|ht)tps?://~i', $creds['dns_name']) ? $creds['dns_name'] : 'http://'.$creds['dns_name']).'/login',
        ];

        // --- Logo Handling ---
        $logoModel = new Logo;
        $logos = $logoModel->getAll();
        $logoMap = [];
        foreach ($logos as $l) {
            $logoMap[$l['id']] = $l['path'];
        }

        $data = [
            'users' => [$userDataValues],
            'templates' => $templates,
            'currentTemplate' => $currentTemplate,
            'templateContent' => $templateContent,
            'session' => $session,
            'logoMap' => $logoMap,
        ];

        return $this->view($viewName, $data);
    }
}
