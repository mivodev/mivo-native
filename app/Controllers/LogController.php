<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\FlashHelper;
use App\Libraries\RouterOSAPI;
use App\Models\Config;

class LogController extends Controller
{
    public function index($session)
    {
        $configModel = new Config;
        $config = $configModel->getSession($session);
        if (! $config) {
            return header('Location: /');
        }

        $logs = [];
        $API = new RouterOSAPI;
        $API->attempts = 1;
        $API->timeout = 3;

        if ($API->connect($config['ip_address'], $config['username'], $config['password'])) {
            // Fetch Hotspot Logs
            // /log/print where topics~hotspot
            // In API we can't always filter effectively by topic in all versions,
            // but we can try ?topics=hotspot,info or similar.
            // Safe bet: fetch last 100 logs and filter PHP side or use API filter if possible.
            // Using a limit to avoid timeout.

            // Getting generic logs for now, filtered by topic 'hotspot' if possible.
            // RouterOS API query for array search: ?topics=hotspot

            $logs = $API->comm('/log/print', [
                '?topics' => 'hotspot,info,debug', // Try detailed match
            ]);

            // Fallback if strict match fails, just get recent logs
            if (empty($logs) || isset($logs['!trap'])) {
                $logs = $API->comm('/log/print', []); // Get all (capped usually by buffer)
            }

            // Reverse to show newest first
            if (is_array($logs)) {
                $logs = array_reverse($logs);
            }

        } else {
            FlashHelper::set('error', 'Connection Failed', 'Could not connect to router at '.$config['ip_address']);
            header('Location: '.($_SERVER['HTTP_REFERER'] ?? '/'.$session.'/dashboard'));
            exit;
        }

        return $this->view('reports/user_log', [
            'session' => $session,
            'logs' => $logs,
        ]);
    }
}
