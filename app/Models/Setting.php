<?php

namespace App\Models;

use App\Core\Database;

class Setting
{
    private $db;

    private $table = 'settings';

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->initTable();
    }

    private function initTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            key TEXT PRIMARY KEY,
            value TEXT
        )";
        $this->db->query($sql);
    }

    public function get($key, $default = null)
    {
        $stmt = $this->db->query("SELECT value FROM {$this->table} WHERE key = ?", [$key]);
        $row = $stmt->fetch();

        return $row ? $row['value'] : $default;
    }

    public function set($key, $value)
    {
        // SQLite Upsert
        $sql = "INSERT INTO {$this->table} (key, value) VALUES (:key, :value)
                ON CONFLICT(key) DO UPDATE SET value = excluded.value";

        return $this->db->query($sql, ['key' => $key, 'value' => $value]);
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        $results = $stmt->fetchAll();
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['key']] = $row['value'];
        }

        return $settings;
    }
}
