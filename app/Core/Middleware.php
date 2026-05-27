<?php

namespace App\Core;

class Middleware
{
    public static function auth()
    {
        // Assume session is started in index.php
        if (! isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }

    public static function cors()
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if (empty($origin)) {
            return;
        }

        $db = Database::getInstance();
        // Check for specific origin or wildcard '*'
        $stmt = $db->query("SELECT * FROM api_cors WHERE origin = ? OR origin = '*' LIMIT 1", [$origin]);
        $rule = $stmt->fetch();

        if ($rule) {
            header('Access-Control-Allow-Origin: '.($rule['origin'] === '*' ? '*' : $origin));

            $methods = json_decode($rule['methods'], true) ?: ['GET', 'POST'];
            header('Access-Control-Allow-Methods: '.implode(', ', $methods));

            $headers = json_decode($rule['headers'], true) ?: ['*'];
            header('Access-Control-Allow-Headers: '.implode(', ', $headers));

            header('Access-Control-Max-Age: '.($rule['max_age'] ?? 3600));

            // Handle preflight requests
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }
        }
    }
}
