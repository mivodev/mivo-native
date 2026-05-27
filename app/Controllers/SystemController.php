<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Libraries\RouterOSAPI;
use App\Models\Config;

class SystemController extends Controller
{
    // Reboot Router
    public function reboot($session)
    {
        $this->executeCommand($session, '/system/reboot');
    }

    // Shutdown Router
    public function shutdown($session)
    {
        $this->executeCommand($session, '/system/shutdown');
    }

    private function executeCommand($session, $command)
    {
        $configModel = new Config;
        $config = $configModel->getSession($session);
        if (! $config) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Session not found']);

            return;
        }

        $API = new RouterOSAPI;
        if ($API->connect($config['ip_address'], $config['username'], $config['password'])) {
            $API->write($command);
            // Wait for command to be processed before cutting connection
            sleep(2);
            $API->disconnect();

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Connection failed']);
        }
    }
}
