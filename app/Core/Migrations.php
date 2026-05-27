<?php

namespace App\Core;

class Migrations
{
    public static function up()
    {
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        // 1. Users Table (Admin Credentials)
        $pdo->exec('CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');

        // 2. Routers (Sessions) Table
        $pdo->exec("CREATE TABLE IF NOT EXISTS routers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            session_name TEXT NOT NULL UNIQUE,
            ip_address TEXT,
            username TEXT,
            password TEXT,
            hotspot_name TEXT,
            dns_name TEXT,
            currency TEXT DEFAULT 'RP',
            reload_interval INTEGER DEFAULT 60,
            interface TEXT,
            description TEXT,
            quick_access INTEGER DEFAULT 0
        )");

        // 3. Quick Access (Dashboard Shortcuts)
        $pdo->exec("CREATE TABLE IF NOT EXISTS quick_access (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            label TEXT NOT NULL,
            url TEXT NOT NULL,
            icon TEXT,
            category TEXT DEFAULT 'general',
            active INTEGER DEFAULT 1
        )");

        // 4. Settings (Key-Value Store)
        $pdo->exec('CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL
        )');

        // 5. Logos (Branding)
        $pdo->exec('CREATE TABLE IF NOT EXISTS logos (
            id TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            path TEXT NOT NULL,
            type TEXT,
            size INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');

        // 6. Quick Prints (Voucher Printing Profiles)
        $pdo->exec("CREATE TABLE IF NOT EXISTS quick_prints (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            router_id INTEGER,
            session_name TEXT NOT NULL,
            name TEXT NOT NULL,
            server TEXT NOT NULL,
            profile TEXT NOT NULL,
            prefix TEXT DEFAULT '',
            char_length INTEGER DEFAULT 4,
            price INTEGER DEFAULT 0,
            selling_price INTEGER DEFAULT 0,
            time_limit TEXT DEFAULT '',
            data_limit TEXT DEFAULT '',
            comment TEXT DEFAULT '',
            color TEXT DEFAULT 'bg-blue-500',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // 7. Voucher Templates
        $pdo->exec('CREATE TABLE IF NOT EXISTS voucher_templates (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            router_id INTEGER,
            session_name TEXT NOT NULL,
            name TEXT NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');

        // 8. API CORS Rules
        $pdo->exec("CREATE TABLE IF NOT EXISTS api_cors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            origin TEXT NOT NULL,
            methods TEXT DEFAULT '[\"GET\",\"POST\"]',
            headers TEXT DEFAULT '[\"*\"]',
            max_age INTEGER DEFAULT 3600,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        return true;
    }
}
