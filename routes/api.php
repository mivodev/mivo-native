<?php

use App\Controllers\ApiController;
use App\Controllers\PublicStatusController;

// API Routes
// These routes do not use the session in the URL prefix by default,
// but might require session/id in the POST body for authentication context.

// Apply Global CORS to all API routes
$router->group(['middleware' => 'cors'], function ($router) {

    $router->post('/api/router/interfaces', [ApiController::class, 'getInterfaces']);

    // Public Status API (No Auth Check in Controller)
    $router->post('/api/status/check', [PublicStatusController::class, 'check']);
    $router->options('/api/status/check', function () {});

    // Voucher Check (Code/Username in URL) - Support GET (Status Page) and POST (Login Page Check)
    $router->post('/api/voucher/check/{code}', [PublicStatusController::class, 'check']);
    $router->get('/api/voucher/check/{code}', [PublicStatusController::class, 'check']);
    $router->options('/api/voucher/check/{code}', function () {}); // CORS Middleware handles this

});
