<?php

namespace App\Middleware;

interface MiddlewareInterface
{
    public function handle($request, \Closure $next);
}
