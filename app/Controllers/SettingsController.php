<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Middleware;
use App\Core\PluginManager;
use App\Helpers\EncryptionHelper;
use App\Helpers\FlashHelper;
use App\Helpers\FormatHelper;
use App\Models\Config;
use App\Models\Logo;
use App\Models\Setting;
use App\Models\VoucherTemplateModel;

class SettingsController extends Controller
{
    public function __construct()
    {
        // Auth handled by Router Middleware
    }

    public function system()
    {
        // Systems Settings Tab (Admin, Global, Backup)
        $settingModel = new Setting;
        $settings = $settingModel->getAll();

        $username = $_SESSION['username'] ?? 'admin';

        return $this->view('settings/systems', [
            'settings' => $settings,
            'username' => $username,
        ]);
    }

    public function routers()
    {
        // Routers List Tab
        $configModel = new Config;
        $routers = $configModel->getAllSessions();

        return $this->view('settings/index', ['routers' => $routers]);
    }

    // ... (Existing Store methods) ...
    public function store()
    {
        // Sanitize Session Name (Duplicate Frontend Logic)
        $rawSess = $_POST['sessname'] ?? '';
        $sessName = preg_replace('/[^a-z0-9-]/', '', strtolower(str_replace(' ', '-', $rawSess)));

        $data = [
            'session_name' => $sessName,
            'ip_address' => $_POST['ipmik'],
            'username' => $_POST['usermik'],
            'password' => $_POST['passmik'],
            'hotspot_name' => $_POST['hotspotname'],
            'dns_name' => $_POST['dnsname'],
            'currency' => $_POST['currency'],
            'reload_interval' => $_POST['areload'],
            'interface' => $_POST['iface'],
            'description' => 'Added via Remake',
            'quick_access' => isset($_POST['quick_access']) ? 1 : 0,
        ];

        $configModel = new Config;
        try {
            $configModel->addSession($data);

            $redirect = '/settings/routers';
            if (isset($_POST['action']) && $_POST['action'] === 'connect') {
                $redirect = '/'.urlencode($data['session_name']).'/dashboard';
            }

            FlashHelper::set('success', 'toasts.router_added', 'toasts.router_added_desc', ['name' => $data['session_name']], true);
            header("Location: $redirect");
        } catch (\Exception $e) {
            echo 'Error adding session: '.$e->getMessage();
        }
    }

    // Update Admin Password
    public function updateAdmin()
    {
        $newPassword = $_POST['admin_password'] ?? '';

        if (! empty($newPassword)) {
            $db = Database::getInstance();
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            // Assuming we are updating the default 'admin' user or the currently logged in user
            // Original Mivo usually has one main user. Let's update 'admin' for now.
            $db->query("UPDATE users SET password = ? WHERE username = 'admin'", [$hash]);
            FlashHelper::set('success', 'toasts.password_updated', 'toasts.password_updated_desc', [], true);
        }

        header('Location: /settings/system');
    }

    // Update Global Settings
    public function updateGlobal()
    {
        $settingModel = new Setting;

        if (isset($_POST['quick_print_mode'])) {
            $settingModel->set('quick_print_mode', $_POST['quick_print_mode']);
            FlashHelper::set('success', 'toasts.settings_saved', 'toasts.settings_saved_desc', [], true);
        }

        header('Location: /settings/system');
    }

    public function update()
    {
        $id = $_POST['id'];

        // Sanitize Session Name
        $rawSess = $_POST['sessname'] ?? '';
        $sessName = preg_replace('/[^a-z0-9-]/', '', strtolower(str_replace(' ', '-', $rawSess)));

        $data = [
            'session_name' => $sessName,
            'ip_address' => $_POST['ipmik'],
            'username' => $_POST['usermik'],
            'password' => $_POST['passmik'], // Can be empty if not changing
            'hotspot_name' => $_POST['hotspotname'],
            'dns_name' => $_POST['dnsname'],
            'currency' => $_POST['currency'],
            'reload_interval' => $_POST['areload'],
            'interface' => $_POST['iface'],
            'description' => 'Updated via Remake',
            'quick_access' => isset($_POST['quick_access']) ? 1 : 0,
        ];

        $configModel = new Config;
        try {
            $configModel->updateSession($id, $data);

            $redirect = '/settings/routers';
            if (isset($_POST['action']) && $_POST['action'] === 'connect') {
                $redirect = '/'.urlencode($data['session_name']).'/dashboard';
            }

            FlashHelper::set('success', 'toasts.router_updated', 'toasts.router_updated_desc', ['name' => $data['session_name']], true);
            header("Location: $redirect");
        } catch (\Exception $e) {
            echo 'Error updating session: '.$e->getMessage();
        }
    }

    public function delete()
    {
        $id = $_POST['id'];
        $configModel = new Config;
        $configModel->deleteSession($id);
        FlashHelper::set('success', 'toasts.router_deleted', 'toasts.router_deleted_desc', [], true);
        header('Location: /settings/routers');
    }

    public function backup()
    {
        $backupName = 'mivo_backup_'.date('d-m-Y').'.mivo';
        $json = [];

        // Backup Settings
        $settingModel = new Setting;
        $settings = $settingModel->getAll();
        $json['settings'] = $settings;

        // Backup Sessions
        $configModel = new Config;
        $sessions = $configModel->getAllSessions();

        // Decrypt passwords for portability
        foreach ($sessions as &$session) {
            if (! empty($session['password'])) {
                $session['password'] = EncryptionHelper::decrypt($session['password']);
            }
        }
        $json['sessions'] = $sessions;

        // Backup Voucher Templates
        $templateModel = new VoucherTemplateModel;
        $json['voucher_templates'] = $templateModel->getAll();

        // Backup Logos
        $logoModel = new Logo;
        $logos = $logoModel->getAll();
        foreach ($logos as &$logo) {
            $filePath = ROOT.'/public'.$logo['path'];
            if (file_exists($filePath)) {
                $logo['data'] = base64_encode(file_get_contents($filePath));
            }
        }
        $json['logos'] = $logos;

        // Encode
        $jsonString = json_encode($json, JSON_PRETTY_PRINT);

        // Encrypt the entire file content for security
        // Decrypted data inside (like passwords) remain plaintext relative to the JSON structure
        // ensuring portability if decrypted successfully.
        $content = EncryptionHelper::encrypt($jsonString);

        // Force Download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($backupName));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: '.strlen($content));
        ob_clean();
        flush();
        echo $content;
        exit;
    }

    public function restore()
    {
        if (! isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            FlashHelper::set('error', 'toasts.restore_failed', 'toasts.no_file_selected', [], true);
            header('Location: /settings/system');
            exit;
        }

        $file = $_FILES['backup_file'];
        $filename = $file['name'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = $file['type'];

        // Validate Extension & MIME
        $allowedExtensions = ['mivo'];
        $allowedMimes = ['application/octet-stream', 'text/plain']; // text/plain fallback for some OS/Browsers

        if (! in_array($extension, $allowedExtensions) || (! empty($mime) && ! in_array($mime, $allowedMimes))) {
            FlashHelper::set('error', 'toasts.restore_failed', 'toasts.invalid_file_type_mivo', [], true);
            header('Location: /settings/system');
            exit;
        }

        $rawValue = file_get_contents($file['tmp_name']);
        if (empty($rawValue)) {
            FlashHelper::set('error', 'toasts.restore_failed', 'toasts.file_empty', [], true);
            header('Location: /settings/system');
            exit;
        }

        // Attempt to decrypt. If file is old (JSON plaintext), decrypt() returns it as-is.
        $content = EncryptionHelper::decrypt($rawValue);

        $json = json_decode($content, true);

        if (! $json || (! isset($json['settings']) && ! isset($json['sessions']))) {
            FlashHelper::set('error', 'toasts.restore_failed', 'toasts.file_corrupted', [], true);
            header('Location: /settings/system');
            exit;
        }

        // Restore Settings
        if (isset($json['settings'])) {
            $settingModel = new Setting;
            // Assuming we check if data exists
            // We might need to iterate and update
            foreach ($json['settings'] as $key => $val) {
                $settingModel->set($key, $val);
            }
        }

        // Restore Sessions
        if (isset($json['sessions'])) {
            $configModel = new Config;
            foreach ($json['sessions'] as $session) {
                unset($session['id']); // Let system generate new ID
                try {
                    $configModel->addSession($session);
                } catch (\Exception $e) {
                    error_log('Failed to restore session: '.($session['session_name'] ?? 'unknown'));
                }
            }
        }

        // Restore Voucher Templates
        if (isset($json['voucher_templates'])) {
            $templateModel = new VoucherTemplateModel;
            foreach ($json['voucher_templates'] as $tmpl) {
                // Check if template exists by name and session
                $db = Database::getInstance();
                $existing = $db->query('SELECT id FROM voucher_templates WHERE name = ? AND session_name = ?', [$tmpl['name'], $tmpl['session_name']])->fetch();

                if ($existing) {
                    $templateModel->update($existing['id'], $tmpl);
                } else {
                    $templateModel->add($tmpl);
                }
            }
        }

        // Restore Logos
        if (isset($json['logos'])) {
            $logoModel = new Logo;
            $uploadDir = ROOT.'/public/uploads/logos/';
            if (! file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($json['logos'] as $logo) {
                if (empty($logo['data'])) {
                    continue;
                }

                // Decode data
                $binaryData = base64_decode($logo['data']);
                if (! $binaryData) {
                    continue;
                }

                // Determine filename (try to keep original ID/name or generate new)
                $extension = $logo['type'] ?? 'png';
                $filename = $logo['id'].'.'.$extension;
                $targetPath = $uploadDir.$filename;

                // Save file
                if (file_put_contents($targetPath, $binaryData)) {
                    // Update DB
                    $db = Database::getInstance();
                    $db->query('INSERT INTO logos (id, name, path, type, size) VALUES (:id, :name, :path, :type, :size)
                                ON CONFLICT(id) DO UPDATE SET name=excluded.name, path=excluded.path, type=excluded.type, size=excluded.size', [
                        'id' => $logo['id'],
                        'name' => $logo['name'],
                        'path' => '/uploads/logos/'.$filename,
                        'type' => $extension,
                        'size' => $logo['size'],
                    ]);
                }
            }
        }

        FlashHelper::set('success', 'toasts.restore_success', 'toasts.restore_success_desc', [], true);
        header('Location: /settings/system');
    }

    // --- Logo Management ---

    public function logos()
    {
        $logoModel = new Logo; // Fully qualified to avoid import issues for now or add import
        $logoModel->syncFiles(); // Ensure FS and DB are in sync
        $logos = $logoModel->getAll();

        // Format size for display (since DB stores raw bytes or maybe we want helper there)
        // Actually model stored bytes, we format in View or here.
        // Let's format here for consistency with previous view.
        foreach ($logos as &$logo) {
            $logo['formatted_size'] = FormatHelper::formatBytes($logo['size']);
        }

        return $this->view('settings/logos', ['logos' => $logos]);
    }

    public function uploadLogo()
    {
        if (! isset($_FILES['logo_file']) || $_FILES['logo_file']['error'] !== UPLOAD_ERR_OK) {
            FlashHelper::set('error', 'toasts.upload_failed', 'toasts.no_file_selected', [], true);
            header('Location: /settings/logos');
            exit;
        }

        $logoModel = new Logo;
        try {
            $result = $logoModel->add($_FILES['logo_file']);
            if ($result) {
                FlashHelper::set('success', 'toasts.logo_uploaded', 'toasts.logo_uploaded_desc', [], true);
            } else {
                FlashHelper::set('error', 'toasts.upload_failed', 'Generic upload error', [], true);
            }
        } catch (\Exception $e) {
            FlashHelper::set('error', 'toasts.upload_failed', $e->getMessage(), [], true);
        }

        header('Location: /settings/logos');
    }

    public function deleteLogo()
    {
        $id = $_POST['id']; // Changed from filename to id

        $logoModel = new Logo;
        $logoModel->delete($id);

        FlashHelper::set('success', 'toasts.logo_deleted', 'toasts.logo_deleted_desc', [], true);
        header('Location: /settings/logos');
    }

    // --- API CORS Management ---

    public function apiCors()
    {
        $db = Database::getInstance();
        $rules = $db->query('SELECT * FROM api_cors ORDER BY created_at DESC')->fetchAll();

        // Decode JSON methods and headers for view
        foreach ($rules as &$rule) {
            $rule['methods_arr'] = json_decode($rule['methods'], true) ?: [];
            $rule['headers_arr'] = json_decode($rule['headers'], true) ?: [];
        }

        return $this->view('settings/api_cors', ['rules' => $rules]);
    }

    public function storeApiCors()
    {
        $origin = $_POST['origin'] ?? '';
        $methods = isset($_POST['methods']) ? json_encode($_POST['methods']) : '["GET","POST"]';
        $headers = isset($_POST['headers']) ? json_encode(array_map('trim', explode(',', $_POST['headers']))) : '["*"]';
        $maxAge = (int) ($_POST['max_age'] ?? 3600);

        if (! empty($origin)) {
            $db = Database::getInstance();
            $db->query('INSERT INTO api_cors (origin, methods, headers, max_age) VALUES (?, ?, ?, ?)', [
                $origin, $methods, $headers, $maxAge,
            ]);
            FlashHelper::set('success', 'toasts.cors_rule_added', 'toasts.cors_rule_added_desc', ['origin' => $origin], true);
        }

        header('Location: /settings/api-cors');
    }

    public function updateApiCors()
    {
        $id = $_POST['id'] ?? null;
        $origin = $_POST['origin'] ?? '';
        $methods = isset($_POST['methods']) ? json_encode($_POST['methods']) : '["GET","POST"]';
        $headers = isset($_POST['headers']) ? json_encode(array_map('trim', explode(',', $_POST['headers']))) : '["*"]';
        $maxAge = (int) ($_POST['max_age'] ?? 3600);

        if ($id && ! empty($origin)) {
            $db = Database::getInstance();
            $db->query('UPDATE api_cors SET origin = ?, methods = ?, headers = ?, max_age = ? WHERE id = ?', [
                $origin, $methods, $headers, $maxAge, $id,
            ]);
            FlashHelper::set('success', 'toasts.cors_rule_updated', 'toasts.cors_rule_updated_desc', ['origin' => $origin], true);
        }

        header('Location: /settings/api-cors');
    }

    public function deleteApiCors()
    {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $db = Database::getInstance();
            $db->query('DELETE FROM api_cors WHERE id = ?', [$id]);
            FlashHelper::set('success', 'toasts.cors_rule_deleted', 'toasts.cors_rule_deleted_desc', [], true);
        }
        header('Location: /settings/api-cors');
    }

    // --- Plugin Management ---

    public function plugins()
    {
        $pluginManager = new PluginManager;
        // Since PluginManager loads everything in constructor/loadPlugins,
        // we can just scan the directory to list them and check status (implied active for now)
        $pluginsDir = ROOT.'/plugins';
        $plugins = [];

        if (is_dir($pluginsDir)) {
            $folders = scandir($pluginsDir);
            foreach ($folders as $folder) {
                if ($folder === '.' || $folder === '..') {
                    continue;
                }

                $path = $pluginsDir.'/'.$folder;
                if (is_dir($path) && file_exists($path.'/plugin.php')) {
                    // Try to read header from plugin.php
                    $content = file_get_contents($path.'/plugin.php', false, null, 0, 1024); // Read first 1KB
                    preg_match('/Plugin Name:\s*(.*)$/mi', $content, $nameMatch);
                    preg_match('/Version:\s*(.*)$/mi', $content, $verMatch);
                    preg_match('/Description:\s*(.*)$/mi', $content, $descMatch);
                    preg_match('/Author:\s*(.*)$/mi', $content, $authMatch);

                    $plugins[] = [
                        'id' => $folder,
                        'name' => trim($nameMatch[1] ?? $folder),
                        'version' => trim($verMatch[1] ?? '1.0.0'),
                        'description' => trim($descMatch[1] ?? '-'),
                        'author' => trim($authMatch[1] ?? '-'),
                        'path' => $path,
                    ];
                }
            }
        }

        return $this->view('settings/plugins', ['plugins' => $plugins]);
    }

    public function uploadPlugin()
    {
        if (! isset($_FILES['plugin_file']) || $_FILES['plugin_file']['error'] !== UPLOAD_ERR_OK) {
            FlashHelper::set('error', 'toasts.upload_failed', 'toasts.no_file_selected', [], true);
            header('Location: /settings/plugins');
            exit;
        }

        $file = $_FILES['plugin_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($ext !== 'zip') {
            FlashHelper::set('error', 'toasts.upload_failed', 'Only .zip files are allowed', [], true);
            header('Location: /settings/plugins');
            exit;
        }

        $zip = new \ZipArchive;
        if ($zip->open($file['tmp_name']) === true) {
            $extractPath = ROOT.'/plugins/';
            if (! is_dir($extractPath)) {
                mkdir($extractPath, 0755, true);
            }

            // TODO: Better validation to prevent overwriting existing plugins without confirmation?
            // For now, extraction overwrites.

            // Validate content before extracting everything
            // Check if zip has a root folder or just files
            // Logic:
            // 1. Extract to temp.
            // 2. Find plugin.php
            // 3. Move to plugins dir.

            $tempExtract = sys_get_temp_dir().'/mivo_plugin_'.uniqid();
            if (! mkdir($tempExtract, 0755, true)) {
                FlashHelper::set('error', 'toasts.upload_failed', 'Failed to create temp dir', [], true);
                header('Location: /settings/plugins');
                exit;
            }

            $zip->extractTo($tempExtract);
            $zip->close();

            // Search for plugin.php
            $pluginFile = null;
            $pluginRoot = $tempExtract;

            // Recursive iterator to find plugin.php (max depth 2 to avoid deep scanning)
            $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tempExtract));
            foreach ($rii as $file) {
                if ($file->isDir()) {
                    continue;
                }
                if ($file->getFilename() === 'plugin.php') {
                    $pluginFile = $file->getPathname();
                    $pluginRoot = dirname($pluginFile);
                    break;
                }
            }

            if ($pluginFile) {
                // Determine destination name
                // If the immediate parent of plugin.php is NOT the temp dir, use that folder name.
                // Else use the zip name.
                $folderName = basename($pluginRoot);
                if ($pluginRoot === $tempExtract) {
                    $folderName = pathinfo($_FILES['plugin_file']['name'], PATHINFO_FILENAME);
                }

                $dest = $extractPath.$folderName;

                // Move/Copy
                // Using helper or rename. Rename might fail across volumes (temp to project).
                // Use custom recursive copy then delete temp.
                $this->recurseCopy($pluginRoot, $dest);

                FlashHelper::set('success', 'toasts.plugin_installed', 'toasts.plugin_installed_desc', ['name' => $folderName], true);
            } else {
                FlashHelper::set('error', 'toasts.install_failed', 'toasts.invalid_plugin_desc', [], true);
            }

            // Cleanup
            $this->recurseDelete($tempExtract);

        } else {
            FlashHelper::set('error', 'toasts.upload_failed', 'toasts.zip_open_failed_desc', [], true);
        }

        header('Location: /settings/plugins');
    }

    public function deletePlugin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /settings/plugins');
            exit;
        }

        $id = $_POST['plugin_id'] ?? '';
        if (empty($id)) {
            FlashHelper::set('error', 'common.error', 'Invalid plugin ID', [], true);
            header('Location: /settings/plugins');
            exit;
        }

        // Security check: validate id is just a folder name, no path traversal
        if (! preg_match('/^[a-zA-Z0-9_-]+$/', $id)) {
            FlashHelper::set('error', 'common.error', 'Invalid plugin ID format', [], true);
            header('Location: /settings/plugins');
            exit;
        }

        $pluginDir = ROOT.'/plugins/'.$id;

        if (is_dir($pluginDir)) {
            $this->recurseDelete($pluginDir);
            FlashHelper::set('success', 'toasts.plugin_deleted', 'toasts.plugin_deleted_desc', [], true);
        } else {
            FlashHelper::set('error', 'common.error', 'Plugin directory not found', [], true);
        }

        header('Location: /settings/plugins');
        exit;
    }

    // Helper for recursive copy (since rename/move_uploaded_file limit across partitions)
    private function recurseCopy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src.'/'.$file)) {
                    $this->recurseCopy($src.'/'.$file, $dst.'/'.$file);
                } else {
                    copy($src.'/'.$file, $dst.'/'.$file);
                }
            }
        }
        closedir($dir);
    }

    private function recurseDelete($dir)
    {
        if (! is_dir($dir)) {
            return;
        }
        $scan = scandir($dir);
        foreach ($scan as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (is_dir($dir.'/'.$file)) {
                $this->recurseDelete($dir.'/'.$file);
            } else {
                unlink($dir.'/'.$file);
            }
        }
        rmdir($dir);
    }
}
