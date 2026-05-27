<?php

namespace App\Models;

use App\Core\Database;
use App\Helpers\EncryptionHelper;

class Config
{
    protected $configPath;

    public function __construct()
    {
        // MIVO Standalone Config Path
        // Points to /include/config.php within the MIVO directory
        $this->configPath = ROOT.'/include/config.php';
    }

    public function getSession($sessionName)
    {
        // 1. Check SQLite Database First
        try {
            $db = Database::getInstance();
            $stmt = $db->query('SELECT * FROM routers WHERE session_name = ?', [$sessionName]);
            $router = $stmt->fetch();

            if ($router) {
                return [
                    'id' => $router['id'],
                    'ip' => $router['ip_address'],
                    'ip_address' => $router['ip_address'], // Alias
                    'user' => $router['username'],
                    'username' => $router['username'], // Alias
                    'password' => EncryptionHelper::decrypt($router['password']),
                    'hotspot_name' => $router['hotspot_name'],
                    'dns_name' => $router['dns_name'],
                    'currency' => $router['currency'],
                    'reload' => $router['reload_interval'],
                    'interface' => $router['interface'],
                    'info' => $router['description'],
                    'quick_access' => $router['quick_access'] ?? 0,
                    'source' => 'sqlite',
                ];
            }
        } catch (\Exception $e) {
            // Ignore DB error and fallback
        }

        // 2. Fallback to Legacy Config
        if (! file_exists($this->configPath)) {
            return null;
        }

        include $this->configPath;

        if (isset($data) && isset($data[$sessionName]) && is_array($data[$sessionName])) {
            $s = $data[$sessionName];

            return [
                'ip' => isset($s[1]) ? explode('!', $s[1])[1] : '',
                'ip_address' => isset($s[1]) ? explode('!', $s[1])[1] : '', // Alias
                'user' => isset($s[2]) ? explode('@|@', $s[2])[1] : '',
                'username' => isset($s[2]) ? explode('@|@', $s[2])[1] : '', // Alias
                'password' => isset($s[3]) ? explode('#|#', $s[3])[1] : '',
                'hotspot_name' => isset($s[4]) ? explode('%', $s[4])[1] : '',
                'dns_name' => isset($s[5]) ? explode('^', $s[5])[1] : '',
                'currency' => isset($s[6]) ? explode('&', $s[6])[1] : '',
                'reload' => isset($s[7]) ? explode('*', $s[7])[1] : '',
                'interface' => isset($s[8]) ? explode('(', $s[8])[1] : '',
                'info' => isset($s[9]) ? explode(')', $s[9])[1] : '',
                'source' => 'legacy',
            ];
        }

        return null;
    }

    public function getAllSessions()
    {
        // SQLite
        try {
            $db = Database::getInstance();
            $stmt = $db->query('SELECT * FROM routers');

            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getSessionById($id)
    {
        $db = Database::getInstance();
        $stmt = $db->query('SELECT * FROM routers WHERE id = ?', [$id]);
        $router = $stmt->fetch();

        if ($router) {
            return [
                'id' => $router['id'],
                'session_name' => $router['session_name'],
                'ip_address' => $router['ip_address'],
                'username' => $router['username'],
                'password' => EncryptionHelper::decrypt($router['password']),
                'hotspot_name' => $router['hotspot_name'],
                'dns_name' => $router['dns_name'],
                'currency' => $router['currency'],
                'reload_interval' => $router['reload_interval'],
                'interface' => $router['interface'],
                'interface' => $router['interface'],
                'description' => $router['description'],
                'quick_access' => $router['quick_access'] ?? 0,
            ];
        }

        return null;
    }

    public function addSession($data)
    {
        $db = Database::getInstance();
        $sql = 'INSERT INTO routers (session_name, ip_address, username, password, hotspot_name, dns_name, currency, reload_interval, interface, description, quick_access) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        return $db->query($sql, [
            $data['session_name'] ?? 'New Session',
            $data['ip_address'] ?? '',
            $data['username'] ?? '',
            EncryptionHelper::encrypt($data['password'] ?? ''),
            $data['hotspot_name'] ?? '',
            $data['dns_name'] ?? '',
            $data['currency'] ?? 'RP',
            $data['reload_interval'] ?? 60,
            $data['interface'] ?? 'ether1',
            $data['description'] ?? '',
            $data['quick_access'] ?? 0,
        ]);
    }

    public function updateSession($id, $data)
    {
        $db = Database::getInstance();

        // If password is provided, encrypt it. If empty, don't update it (keep existing).
        if (! empty($data['password'])) {
            $sql = 'UPDATE routers SET session_name=?, ip_address=?, username=?, password=?, hotspot_name=?, dns_name=?, currency=?, reload_interval=?, interface=?, description=?, quick_access=? WHERE id=?';
            $params = [
                $data['session_name'],
                $data['ip_address'],
                $data['username'],
                EncryptionHelper::encrypt($data['password']),
                $data['hotspot_name'],
                $data['dns_name'],
                $data['currency'],
                $data['reload_interval'],
                $data['interface'],
                $data['description'],
                $data['quick_access'] ?? 0,
                $id,
            ];
        } else {
            $sql = 'UPDATE routers SET session_name=?, ip_address=?, username=?, hotspot_name=?, dns_name=?, currency=?, reload_interval=?, interface=?, description=?, quick_access=? WHERE id=?';
            $params = [
                $data['session_name'],
                $data['ip_address'],
                $data['username'],
                $data['hotspot_name'],
                $data['dns_name'],
                $data['currency'],
                $data['reload_interval'],
                $data['interface'],
                $data['description'],
                $data['quick_access'] ?? 0,
                $id,
            ];
        }

        return $db->query($sql, $params);
    }

    public function deleteSession($id)
    {
        $db = Database::getInstance();

        return $db->query('DELETE FROM routers WHERE id = ?', [$id]);
    }
}
