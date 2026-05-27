<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\FlashHelper;
use App\Libraries\RouterOSAPI;
use App\Models\Config;

class DhcpController extends Controller
{
    public function index($session)
    {
        $configModel = new Config;
        $config = $configModel->getSession($session);
        if (! $config) {
            header('Location: /');
            exit;
        }

        $leases = [];
        $API = new RouterOSAPI;
        $API->attempts = 1;
        $API->timeout = 3;

        if ($API->connect($config['ip_address'], $config['username'], $config['password'])) {
            // Fetch DHCP Leases
            $leases = $API->comm('/ip/dhcp-server/lease/print');
        } else {
            FlashHelper::set('error', 'Connection Failed', 'Could not connect to router at '.$config['ip_address']);
            header('Location: '.($_SERVER['HTTP_REFERER'] ?? '/'.$session.'/dashboard'));
            exit;
        }

        // Add index for viewing
        return $this->view('network/dhcp', [
            'session' => $session,
            'leases' => $leases ?? [],
        ]);
    }
}
