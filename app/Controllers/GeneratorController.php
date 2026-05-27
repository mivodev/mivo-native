<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\FlashHelper;
use App\Libraries\RouterOSAPI;
use App\Models\Config;

class GeneratorController extends Controller
{
    public function index($session)
    {
        $configModel = new Config;
        $creds = $configModel->getSession($session);

        if (! $creds) {
            $this->redirect('/');

            return;
        }

        $API = new RouterOSAPI;
        if ($API->connect($creds['ip'], $creds['user'], $creds['password'])) {
            // Fetch Profiles for Dropdown
            $profiles = $API->comm('/ip/hotspot/user/profile/print');
            // Fetch Hotspot Servers
            $servers = $API->comm('/ip/hotspot/print');
            $API->disconnect();

            $data = [
                'session' => $session,
                'title' => 'Generate Vouchers - '.$session,
                'profiles' => $profiles,
                'servers' => $servers,
            ];

            $this->view('hotspot/generate', $data);
        } else {
            FlashHelper::set('error', 'Connection Failed', 'Could not connect to router at '.$creds['ip']);
            header('Location: '.($_SERVER['HTTP_REFERER'] ?? '/'.$session.'/dashboard'));
            exit;
        }
    }

    public function process()
    {
        $session = $_POST['session'] ?? '';
        $qty = intval($_POST['qty'] ?? 1);
        $server = $_POST['server'] ?? 'all';
        $userMode = $_POST['userModel'] ?? 'up';
        $userLength = intval($_POST['userLength'] ?? 4);
        $prefix = $_POST['prefix'] ?? '';
        $char = $_POST['char'] ?? 'mix';
        $profile = $_POST['profile'] ?? '';
        $comment = $_POST['comment'] ?? '';

        // Time Limit Logic (d, h, m)
        $timelimit_d = $_POST['timelimit_d'] ?? '';
        $timelimit_h = $_POST['timelimit_h'] ?? '';
        $timelimit_m = $_POST['timelimit_m'] ?? '';

        $timeLimit = '';
        if ($timelimit_d != '') {
            $timeLimit .= $timelimit_d.'d';
        }
        if ($timelimit_h != '') {
            $timeLimit .= $timelimit_h.'h';
        }
        if ($timelimit_m != '') {
            $timeLimit .= $timelimit_m.'m';
        }

        // Data Limit Logic (Value, Unit)
        $datalimit_val = $_POST['datalimit_val'] ?? '';
        $datalimit_unit = $_POST['datalimit_unit'] ?? 'MB';

        $dataLimit = '';
        if (! empty($datalimit_val) && is_numeric($datalimit_val)) {
            $bytes = (float) $datalimit_val;
            if ($datalimit_unit === 'GB') {
                $bytes = $bytes * 1073741824;
            } else {
                // MB
                $bytes = $bytes * 1048576;
            }
            $dataLimit = (string) round($bytes);
        }

        if (! $session || $qty < 1 || ! $profile) {
            $this->back($session);

            return;
        }

        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            $this->redirect('/');

            return;
        }

        $API = new RouterOSAPI;
        if ($API->connect($creds['ip'], $creds['user'], $creds['password'])) {

            // Format Comment: prefix-rand-date- comment
            // Example: up-123-12.01.26- premium
            $commentPrefix = ($userMode === 'vc') ? 'vc-' : 'up-';
            $batchId = rand(100, 999);
            $date = date('m.d.y');
            $commentBody = $comment ?: $profile;
            $finalComment = "{$commentPrefix}{$batchId}-{$date}- {$commentBody}";

            for ($i = 0; $i < $qty; $i++) {
                $username = $prefix.$this->generateRandomString($userLength, $char);
                $password = $username;

                if ($userMode === 'up') {
                    $password = $this->generateRandomString($userLength, $char);
                }

                $user = [
                    'server' => $server,
                    'profile' => $profile,
                    'name' => $username,
                    'password' => $password,
                    'comment' => $finalComment,
                ];

                if (! empty($timeLimit)) {
                    $user['limit-uptime'] = $timeLimit;
                }
                if (! empty($dataLimit)) {
                    $user['limit-bytes-total'] = $dataLimit;
                }

                $API->comm('/ip/hotspot/user/add', $user);
            }

            $API->disconnect();
        }

        FlashHelper::set('success', 'toasts.vouchers_generated', 'toasts.vouchers_generated_desc', ['qty' => $qty], true);
        $this->redirect('/'.$session.'/hotspot/users');
    }

    private function generateRandomString($length, $charType)
    {
        $characters = '';
        switch ($charType) {
            case 'lower':
                $characters = 'abcdefghijklmnopqrstuvwxyz';
                break;
            case 'upper':
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'number':
                $characters = '0123456789';
                break;
            case 'uppernumber':
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                break;
            case 'lowernumber':
                $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
                break;
            case 'mix':
            default:
                $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                break;
        }

        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    private function back($session)
    {
        $this->redirect('/'.$session.'/hotspot/generate');
    }
}
