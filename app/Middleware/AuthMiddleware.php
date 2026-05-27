<?php

namespace App\Middleware;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle($request, \Closure $next)
    {
        // Assume session is started in index.php
        if (! isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        return $next($request);
    }
}
