<?php

namespace App\Middleware;

use App\Helpers\ErrorHelper;
use App\Models\Config;

class RouterCheckMiddleware implements MiddlewareInterface
{
    public function handle($request, \Closure $next)
    {
        // We need to extract the session from the URI
        // Pattern: /{session}/...

        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if (strpos($path, $scriptName) === 0) {
            $path = substr($path, strlen($scriptName));
        }
        $path = '/'.trim($path, '/');

        // Regex to grab first segment
        if (preg_match('#^/([^/]+)#', $path, $matches)) {
            $session = $matches[1];

            // Exclude system routes that might mimic this pattern if any (like 'settings')
            // But 'settings' is usually top level.
            // If the user name their router "settings", it would conflict anyway.
            // Let's assume standard routing structure.

            if ($session === 'login' || $session === 'logout' || $session === 'settings' || $session === 'install' || $session === 'api') {
                return $next($request);
            }

            $configModel = new Config;
            if ($session !== 'demo' && ! $configModel->getSession($session)) {
                // Router NOT FOUND
                ErrorHelper::show(404, 'errors.router_not_found_title', 'errors.router_not_found_desc');
            }
        }

        return $next($request);
    }
}
