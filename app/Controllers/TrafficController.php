<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Libraries\RouterOSAPI;
use App\Models\Config;

class TrafficController extends Controller
{
    public function monitor($session)
    {
        // 1. Get Session Config
        $configModel = new Config;
        $config = $configModel->getSession($session);

        if (! $config) {
            http_response_code(404);
            echo json_encode(['error' => 'Session not found']);

            return;
        }

        // 2. Connect to RouterOS
        $API = new RouterOSAPI;
        // $API->debug = true;

        // Fast Fail for Traffic Monitor to prevent blocking PHP server
        $API->attempts = 1;
        $API->timeout = 2;

        if ($API->connect($config['ip_address'], $config['username'], $config['password'])) {
            // 3. Get Interface Name from GET param > Config > default 'ether1'
            $interface = $_GET['interface'] ?? $config['interface'] ?? 'ether1';

            // 4. Fetch Traffic
            // /interface/monitor-traffic interface=ether1 once
            $traffic = $API->comm('/interface/monitor-traffic', [
                'interface' => $interface,
                'once' => '',
            ]);

            $API->disconnect();

            // 5. Return JSON
            if (! empty($traffic) && ! isset($traffic['!trap'])) {
                header('Content-Type: application/json');
                echo json_encode($traffic[0]);
            } else {
                echo json_encode(['error' => 'No data']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Connection failed']);
        }
    }

    public function getInterfaces($session)
    {
        // 1. Get Session Config
        $configModel = new Config;
        $config = $configModel->getSession($session);

        if (! $config) {
            http_response_code(404);
            echo json_encode(['error' => 'Session not found']);

            return;
        }

        // 2. Connect
        $API = new RouterOSAPI;
        if ($API->connect($config['ip_address'], $config['username'], $config['password'])) {
            // 3. Fetch Interfaces
            // Use comm() to safely handle response parsing and filtering
            $interfaces = $API->comm('/interface/print', [
                '.proplist' => 'name,type',
            ]);
            $API->disconnect();

            // 4. Return
            header('Content-Type: application/json');
            echo json_encode($interfaces);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Connection failed']);
        }
    }
}
