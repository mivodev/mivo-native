<?php

namespace App\Models;

use App\Core\Database;
use Exception;
use PDO;

class Logo
{
    protected $db;

    protected $table = 'logos';

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->initTable();
    }

    // Connect method removed as we use shared instance
    private function initTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            path TEXT NOT NULL,
            type TEXT,
            size INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $this->db->query($query);
    }

    public function generateId($length = 6)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE id = :id", ['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function add($file)
    {
        // Security: Strict MIME Type Check
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/svg+xml' => 'svg',
            'image/webp' => 'webp',
        ];

        if (! array_key_exists($mimeType, $allowedMimes)) {
            throw new Exception('Invalid file type: '.$mimeType);
        }

        // Use extension mapped from MIME type or sanitize original
        // Better to trust MIME mapping for extensions to avoid double extension attacks
        $extension = $allowedMimes[$mimeType];

        // Generate Unique Short ID
        do {
            $id = $this->generateId();
            $exists = $this->getById($id);
        } while ($exists);

        $uploadDir = ROOT.'/public/uploads/logos/';
        if (! file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = $id.'.'.$extension;
        $targetPath = $uploadDir.$filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->db->query("INSERT INTO {$this->table} (id, name, path, type, size) VALUES (:id, :name, :path, :type, :size)", [
                'id' => $id,
                'name' => $file['name'],
                'path' => '/uploads/logos/'.$filename,
                'type' => $extension,
                'size' => $file['size'],
            ]);

            return $id;
        }

        return false;
    }

    public function syncFiles()
    {
        // One-time sync: scan folder, if file not in DB, add it.
        $logoDir = ROOT.'/public/uploads/logos/';
        if (! file_exists($logoDir)) {
            return;
        }

        $files = [];
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        foreach ($extensions as $ext) {
            $files = array_merge($files, glob($logoDir.'*.'.$ext));
        }

        foreach ($files as $file) {
            $filename = basename($file);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);

            // Check if file is registered (maybe by path match)
            $webPath = '/uploads/logos/'.$filename;
            $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE path = :path", ['path' => $webPath]);

            if ($stmt->fetchColumn() == 0) {
                // Not in DB, register it.
                // Ideally we'd rename it to a hashID, but since it's existing, let's generate an ID and map it.
                do {
                    $id = $this->generateId();
                    $exists = $this->getById($id);
                } while ($exists);

                $this->db->query("INSERT INTO {$this->table} (id, name, path, type, size) VALUES (:id, :name, :path, :type, :size)", [
                    'id' => $id,
                    'name' => $filename,
                    'path' => $webPath,
                    'type' => $extension,
                    'size' => filesize($file),
                ]);
            }
        }
    }

    public function delete($id)
    {
        $logo = $this->getById($id);
        if ($logo) {
            $filePath = ROOT.'/public'.$logo['path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $this->db->query("DELETE FROM {$this->table} WHERE id = :id", ['id' => $id]);

            return true;
        }

        return false;
    }
}
