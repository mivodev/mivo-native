<?php

namespace App\Middleware;

use App\Core\Database;

class CorsMiddleware implements MiddlewareInterface
{
    public function handle($request, \Closure $next)
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Always allow if no origin (e.g. server-to-server or same-origin strict)
        // Check generic logic: if valid origin, try to match DB
        if (! empty($origin)) {
            $db = Database::getInstance();
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

        return $next($request);
    }
}
