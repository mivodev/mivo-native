<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\FlashHelper;
use App\Libraries\RouterOSAPI;
use App\Models\Config;

class SchedulerController extends Controller
{
    public function index($session)
    {
        $configModel = new Config;
        $config = $configModel->getSession($session);

        if (! $config) {
            header('Location: /');
            exit;
        }

        $API = new RouterOSAPI;
        $schedulers = [];

        if ($API->connect($config['ip_address'], $config['username'], $config['password'])) {
            $schedulers = $API->comm('/system/scheduler/print');
            $API->disconnect();
        }

        return $this->view('system/scheduler', [
            'session' => $session,
            'schedulers' => $schedulers,
        ]);
    }

    public function store($session)
    {
        $configModel = new Config;
        $config = $configModel->getSession($session);
        if (! $config) {
            exit;
        }

        $API = new RouterOSAPI;
        if ($API->connect($config['ip_address'], $config['username'], $config['password'])) {
            $API->comm('/system/scheduler/add', [
                'name' => $_POST['name'],
                'on-event' => $_POST['on_event'],
                'start-date' => $_POST['start_date'],
                'start-time' => $_POST['start_time'],
                'interval' => $_POST['interval'],
                'comment' => $_POST['comment'] ?? '',
                'disabled' => 'no',
            ]);
            $API->disconnect();
        }
        FlashHelper::set('success', 'toasts.schedule_added', 'toasts.schedule_added_desc', [], true);
        header("Location: /$session/system/scheduler");
    }

    public function update($session)
    {
        $configModel = new Config;
        $config = $configModel->getSession($session);
        if (! $config) {
            exit;
        }

        $API = new RouterOSAPI;
        if ($API->connect($config['ip_address'], $config['username'], $config['password'])) {
            $API->comm('/system/scheduler/set', [
                '.id' => $_POST['id'],
                'name' => $_POST['name'],
                'on-event' => $_POST['on_event'],
                'start-date' => $_POST['start_date'],
                'start-time' => $_POST['start_time'],
                'interval' => $_POST['interval'],
                'comment' => $_POST['comment'] ?? '',
            ]);
            $API->disconnect();
        }
        FlashHelper::set('success', 'toasts.schedule_updated', 'toasts.schedule_updated_desc', [], true);
        header("Location: /$session/system/scheduler");
    }

    public function delete($session)
    {
        $configModel = new Config;
        $config = $configModel->getSession($session);
        if (! $config) {
            exit;
        }

        $API = new RouterOSAPI;
        if ($API->connect($config['ip_address'], $config['username'], $config['password'])) {
            $API->comm('/system/scheduler/remove', [
                '.id' => $_POST['id'],
            ]);
            $API->disconnect();
        }
        FlashHelper::set('success', 'toasts.schedule_deleted', 'toasts.schedule_deleted_desc', [], true);
        header("Location: /$session/system/scheduler");
    }
}
