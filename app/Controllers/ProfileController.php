<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\FlashHelper;
use App\Helpers\HotspotHelper;
use App\Libraries\RouterOSAPI;
use App\Models\Config;

class ProfileController extends Controller
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
        // Use default port 8728 if not specified
        if ($API->connect($creds['ip'], $creds['user'], $creds['password'])) {
            $profiles = $API->comm('/ip/hotspot/user/profile/print');

            // Fetch Pools & Queues for the Modal Form
            $pools = $API->comm('/ip/pool/print');
            $simple = $API->comm('/queue/simple/print');
            $tree = $API->comm('/queue/tree/print');

            $queues = [];
            foreach ($simple as $q) {
                if (isset($q['name'])) {
                    $queues[] = $q['name'];
                }
            }
            foreach ($tree as $q) {
                if (isset($q['name'])) {
                    $queues[] = $q['name'];
                }
            }
            sort($queues);

            $API->disconnect();

            // Process profiles to add metadata from on-login script
            foreach ($profiles as &$profile) {
                $meta = HotspotHelper::parseProfileMetadata($profile['on-login'] ?? '');
                $profile['meta'] = $meta;
                $profile['meta']['expired_mode_formatted'] = HotspotHelper::formatExpiredMode($meta['expired_mode'] ?? '');
            }

            $this->view('hotspot/profiles/index', [
                'session' => $session,
                'profiles' => $profiles,
                'pools' => $pools,
                'queues' => $queues,
                'title' => 'User Profiles',
            ]);
        } else {
            FlashHelper::set('error', 'Connection Failed', 'Could not connect to router at '.$creds['ip']);
            header('Location: '.($_SERVER['HTTP_REFERER'] ?? '/'.$session.'/dashboard'));
            exit;
        }
    }

    public function add($session)
    {
        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            $this->redirect('/');

            return;
        }

        $API = new RouterOSAPI;
        $pools = [];
        $queues = [];

        if ($API->connect($creds['ip'], $creds['user'], $creds['password'])) {
            $pools = $API->comm('/ip/pool/print');

            // Fetch Queues (Simple & Tree)
            $simple = $API->comm('/queue/simple/print');
            $tree = $API->comm('/queue/tree/print');

            // Extract just names for dropdown
            foreach ($simple as $q) {
                if (isset($q['name'])) {
                    $queues[] = $q['name'];
                }
            }
            foreach ($tree as $q) {
                if (isset($q['name'])) {
                    $queues[] = $q['name'];
                }
            }
            sort($queues);

            $API->disconnect();
        }

        $this->view('hotspot/profiles/add', [
            'session' => $session,
            'pools' => $pools,
            'queues' => $queues,
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');

            return;
        }

        $session = $_POST['session'] ?? '';
        $name = $_POST['name'] ?? '';
        $sharedUsers = $_POST['shared-users'] ?? '1';
        $rateLimit = $_POST['rate-limit'] ?? '';
        $addressPool = $_POST['address-pool'] ?? 'none';
        $parentQueue = $_POST['parent-queue'] ?? 'none';

        // Metadata fields
        $expiredMode = $_POST['expired_mode'] ?? 'none';

        // Validity Logic
        $val_d = $_POST['validity_d'] ?? '';
        $val_h = $_POST['validity_h'] ?? '';
        $val_m = $_POST['validity_m'] ?? '';
        $validity = '';
        if ($val_d) {
            $validity .= $val_d.'d';
        }
        if ($val_h) {
            $validity .= $val_h.'h';
        }
        if ($val_m) {
            $validity .= $val_m.'m';
        }

        $price = $_POST['price'] ?? '';
        $sellingPrice = $_POST['selling_price'] ?? '';
        $lockUser = $_POST['lock_user'] ?? 'Disable';

        // Construct on-login script
        // Construct on-login script
        $metaScript = sprintf(
            ':put (",%s,%s,%s,%s,,%s,")',
            $expiredMode,
            $price,
            $validity,
            $sellingPrice,
            $lockUser
        );

        // Logic Script (The "Enforcer") - Enforces Calendar Validity
        // Automates adding a scheduler to Disable user after "Validity" time passes from first login.
        // Update: Added Self-Cleaning logic (:do {} on-error={}) to ensure scheduler deletes itself
        // even if user was manually deleted from Winbox.
        $logicScript = '';
        if (! empty($validity)) {
            $logicScript = ' :local v "'.$validity.'"; :local u $user; :local c [/ip hotspot user get [find name=$u] comment]; :if ([:find $c "exp"] = -1) do={ /sys sch add name=$u interval=$v on-event=":do { /ip hotspot user set [find name=$u] disabled=yes } on-error={}; /sys sch remove [find name=$u]"; /ip hotspot user set [find name=$u] comment=("exp: " . $v . " " . $c); }';
        }

        $onLogin = $metaScript.$logicScript;

        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            return;
        }

        $API = new RouterOSAPI;
        if ($API->connect($creds['ip'], $creds['user'], $creds['password'])) {
            $profileData = [
                'name' => $name,
                'shared-users' => $sharedUsers,
                'on-login' => $onLogin,
                'address-pool' => $addressPool,
                'parent-queue' => $parentQueue,
            ];

            if ($parentQueue === 'none') {
                unset($profileData['parent-queue']); // Or handle appropriately if Mikrotik accepts 'none' or unset
            }

            if (! empty($rateLimit)) {
                $profileData['rate-limit'] = $rateLimit;
            }

            $API->comm('/ip/hotspot/user/profile/add', $profileData);
            $API->disconnect();
        }

        FlashHelper::set('success', 'toasts.profile_created', 'toasts.profile_created_desc', ['name' => $name], true);
        $this->redirect('/'.$session.'/hotspot/profiles');
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');

            return;
        }

        $session = $_POST['session'] ?? '';
        $id = $_POST['id'] ?? '';

        if (empty($session) || empty($id)) {
            $this->redirect('/');

            return;
        }

        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            return;
        }

        $API = new RouterOSAPI;
        if ($API->connect($creds['ip'], $creds['user'], $creds['password'])) {
            $API->comm('/ip/hotspot/user/profile/remove', [
                '.id' => $id,
            ]);
            $API->disconnect();
        }

        FlashHelper::set('success', 'toasts.profile_deleted', 'toasts.profile_deleted_desc', [], true);
        $this->redirect('/'.$session.'/hotspot/profiles');
    }

    public function edit($session, $id)
    {
        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            $this->redirect('/');

            return;
        }

        $API = new RouterOSAPI;
        $profile = null;
        $pools = [];
        $queues = [];

        if ($API->connect($creds['ip'], $creds['user'], $creds['password'])) {
            $pools = $API->comm('/ip/pool/print');

            // Fetch Queues (Simple & Tree)
            $simple = $API->comm('/queue/simple/print');
            $tree = $API->comm('/queue/tree/print');

            foreach ($simple as $q) {
                if (isset($q['name'])) {
                    $queues[] = $q['name'];
                }
            }
            foreach ($tree as $q) {
                if (isset($q['name'])) {
                    $queues[] = $q['name'];
                }
            }
            sort($queues);

            $profiles = $API->comm('/ip/hotspot/user/profile/print', [
                '?.id' => $id,
            ]);

            if (! empty($profiles)) {
                $profile = $profiles[0];
                // Parse metadata
                $meta = HotspotHelper::parseProfileMetadata($profile['on-login'] ?? '');
                $profile['meta'] = $meta;

                // Parse Validity
                $val_d = '';
                $val_h = '';
                $val_m = '';

                if (! empty($meta['validity'])) {
                    if (preg_match('/(\d+)d/', $meta['validity'], $m)) {
                        $val_d = $m[1];
                    }
                    if (preg_match('/(\d+)h/', $meta['validity'], $m)) {
                        $val_h = $m[1];
                    }
                    if (preg_match('/(\d+)m/', $meta['validity'], $m)) {
                        $val_m = $m[1];
                    }
                }

                $profile['val_d'] = $val_d;
                $profile['val_h'] = $val_h;
                $profile['val_m'] = $val_m;
            }

            $API->disconnect();
        }

        if (! $profile) {
            $this->redirect('/'.$session.'/hotspot/profiles');

            return;
        }

        $this->view('hotspot/profiles/edit', [
            'session' => $session,
            'profile' => $profile,
            'pools' => $pools,
            'queues' => $queues,
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');

            return;
        }

        $session = $_POST['session'] ?? '';
        $id = $_POST['id'] ?? '';
        $name = $_POST['name'] ?? '';
        $sharedUsers = $_POST['shared-users'] ?? '1';
        $rateLimit = $_POST['rate-limit'] ?? '';
        $addressPool = $_POST['address-pool'] ?? 'none';
        $parentQueue = $_POST['parent-queue'] ?? 'none';

        // Metadata fields
        $expiredMode = $_POST['expired_mode'] ?? 'none';

        // Validity Logic
        $val_d = $_POST['validity_d'] ?? '';
        $val_h = $_POST['validity_h'] ?? '';
        $val_m = $_POST['validity_m'] ?? '';
        $validity = '';
        if ($val_d) {
            $validity .= $val_d.'d';
        }
        if ($val_h) {
            $validity .= $val_h.'h';
        }
        if ($val_m) {
            $validity .= $val_m.'m';
        }

        $price = $_POST['price'] ?? '';
        $sellingPrice = $_POST['selling_price'] ?? '';
        $lockUser = $_POST['lock_user'] ?? 'Disable';

        $metaScript = sprintf(
            ':put (",%s,%s,%s,%s,,%s,")',
            $expiredMode,
            $price,
            $validity,
            $sellingPrice,
            $lockUser
        );

        // Logic Script (The "Enforcer")
        $logicScript = '';
        if (! empty($validity)) {
            $logicScript = ' :local v "'.$validity.'"; :local u $user; :local c [/ip hotspot user get [find name=$u] comment]; :if ([:find $c "exp"] = -1) do={ /sys sch add name=$u interval=$v on-event=":do { /ip hotspot user set [find name=$u] disabled=yes } on-error={}; /sys sch remove [find name=$u]"; /ip hotspot user set [find name=$u] comment=("exp: " . $v . " " . $c); }';
        }

        $onLogin = $metaScript.$logicScript;

        $configModel = new Config;
        $creds = $configModel->getSession($session);
        if (! $creds) {
            return;
        }

        $API = new RouterOSAPI;
        if ($API->connect($creds['ip'], $creds['user'], $creds['password'])) {
            $profileData = [
                '.id' => $id,
                'name' => $name,
                'shared-users' => $sharedUsers,
                'on-login' => $onLogin,
                'address-pool' => $addressPool,
                'parent-queue' => $parentQueue,
            ];

            if ($parentQueue === 'none') {
                unset($profileData['parent-queue']);
            }

            $profileData['rate-limit'] = $rateLimit;

            $API->comm('/ip/hotspot/user/profile/set', $profileData);
            $API->disconnect();
        }

        FlashHelper::set('success', 'toasts.profile_updated', 'toasts.profile_updated_desc', ['name' => $name], true);
        $this->redirect('/'.$session.'/hotspot/profiles');
    }
}
