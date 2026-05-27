<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\DhcpController;
use App\Controllers\GeneratorController;
use App\Controllers\HomeController;
use App\Controllers\HotspotController;
use App\Controllers\InstallController;
use App\Controllers\LogController;
use App\Controllers\ProfileController;
use App\Controllers\PublicStatusController;
use App\Controllers\QuickPrintController;
use App\Controllers\ReportController;
use App\Controllers\SchedulerController;
use App\Controllers\SettingsController;
use App\Controllers\SystemController;
use App\Controllers\TrafficController;
use App\Controllers\VoucherTemplateController;

// -----------------------------------------------------------------------------
// Public Routes (No Auth Required)
// -----------------------------------------------------------------------------

// Installer
$router->get('/install', [InstallController::class, 'index']);
$router->post('/install', [InstallController::class, 'process']);

// Authentication
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// Public Status Check (Requires Valid Router Session, but NO Auth)
$router->group(['middleware' => 'router.valid'], function ($router) {
    $router->get('/{session}/status', [PublicStatusController::class, 'index']);
});

// Temporary Test Route
$router->get('/test-alert', [HomeController::class, 'testAlert']);

// Plugin Language Route - DEPRECATED
// Plugins now handle their own routing via Hooks::addAction('router_init')

// -----------------------------------------------------------------------------
// Protected Admin Routes (Requires Auth)
// -----------------------------------------------------------------------------

$router->group(['middleware' => 'auth'], function ($router) {

    // Global Home / Design System
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/design-system', [HomeController::class, 'designSystem']);

    // Global Settings (Admin Level)
    $router->get('/settings', [SettingsController::class, 'routers']);
    $router->get('/settings/system', [SettingsController::class, 'system']);
    $router->get('/settings/routers', [SettingsController::class, 'routers']);
    $router->get('/settings/add', [SettingsController::class, 'add']);
    $router->post('/settings/store', [SettingsController::class, 'store']);
    $router->get('/settings/edit/{id}', [SettingsController::class, 'edit']);
    $router->post('/settings/update', [SettingsController::class, 'update']);
    $router->post('/settings/delete', [SettingsController::class, 'delete']);
    $router->post('/settings/admin/update', [SettingsController::class, 'updateAdmin']);
    $router->post('/settings/global/update', [SettingsController::class, 'updateGlobal']);
    $router->get('/settings/backup', [SettingsController::class, 'backup']);
    $router->post('/settings/restore', [SettingsController::class, 'restore']);

    // Voucher Templates
    $router->get('/settings/voucher-templates', [VoucherTemplateController::class, 'index']);
    $router->get('/settings/voucher-templates/preview/{id}', [VoucherTemplateController::class, 'preview']);
    $router->get('/settings/voucher-templates/add', [VoucherTemplateController::class, 'add']);
    $router->post('/settings/voucher-templates/store', [VoucherTemplateController::class, 'store']);
    $router->get('/settings/voucher-templates/edit/{id}', [VoucherTemplateController::class, 'edit']);
    $router->post('/settings/voucher-templates/update', [VoucherTemplateController::class, 'update']);
    $router->post('/settings/voucher-templates/delete', [VoucherTemplateController::class, 'delete']);

    // Logo Management
    $router->get('/settings/logos', [SettingsController::class, 'logos']);
    $router->post('/settings/logos/upload', [SettingsController::class, 'uploadLogo']);
    $router->post('/settings/logos/delete', [SettingsController::class, 'deleteLogo']);

    // API CORS Settings
    $router->get('/settings/api-cors', [SettingsController::class, 'apiCors']);
    $router->post('/settings/api-cors/store', [SettingsController::class, 'storeApiCors']);
    $router->post('/settings/api-cors/update', [SettingsController::class, 'updateApiCors']);
    $router->post('/settings/api-cors/delete', [SettingsController::class, 'deleteApiCors']);

    // Plugins Management
    $router->get('/settings/plugins', [SettingsController::class, 'plugins']);
    $router->post('/settings/plugins/upload', [SettingsController::class, 'uploadPlugin']);
    $router->post('/settings/plugins/delete', [SettingsController::class, 'deletePlugin']);

    // -------------------------------------------------------------------------
    // Router Context Routes (Requires Auth AND Valid Router Session)
    // -------------------------------------------------------------------------
    // These routes rely on {session} parameter and middleware checks if it exists.

    $router->group(['middleware' => 'router.valid'], function ($router) {

        // Dashboard
        $router->get('/{session}/dashboard', [DashboardController::class, 'index']);

        // Hotspot - Profiles
        $router->get('/{session}/hotspot/profiles', [ProfileController::class, 'index']);
        $router->get('/{session}/hotspot/profile/add', [ProfileController::class, 'add']);
        $router->post('/{session}/hotspot/profile/store', [ProfileController::class, 'store']);
        $router->post('/{session}/hotspot/profile/delete', [ProfileController::class, 'delete']);
        $router->get('/{session}/hotspot/profile/edit/{id}', [ProfileController::class, 'edit']);
        $router->post('/{session}/hotspot/profile/update', [ProfileController::class, 'update']);

        // Hotspot - Users
        $router->get('/{session}/hotspot/users', [HotspotController::class, 'index']);
        $router->get('/{session}/hotspot/add', [HotspotController::class, 'add']);
        $router->post('/{session}/hotspot/store', [HotspotController::class, 'store']);
        $router->post('/{session}/hotspot/delete', [HotspotController::class, 'delete']);
        $router->get('/{session}/hotspot/user/edit/{id}', [HotspotController::class, 'edit']);
        $router->post('/{session}/hotspot/update', [HotspotController::class, 'update']);
        $router->get('/{session}/hotspot/print-batch', [HotspotController::class, 'printBatchActions']);
        $router->get('/{session}/hotspot/print/([a-zA-Z0-9*]+)', [HotspotController::class, 'printUser']);

        // Hotspot - Active & Hosts
        $router->get('/{session}/hotspot/active', [HotspotController::class, 'active']);
        $router->post('/{session}/hotspot/active/remove', [HotspotController::class, 'removeActive']);
        $router->get('/{session}/hotspot/hosts', [HotspotController::class, 'hosts']);
        $router->get('/{session}/hotspot/bindings', [HotspotController::class, 'bindings']);
        $router->post('/{session}/hotspot/bindings/store', [HotspotController::class, 'storeBinding']);
        $router->post('/{session}/hotspot/bindings/remove', [HotspotController::class, 'removeBinding']);
        $router->get('/{session}/hotspot/walled-garden', [HotspotController::class, 'walledGarden']);
        $router->post('/{session}/hotspot/walled-garden/store', [HotspotController::class, 'storeWalledGarden']);
        $router->post('/{session}/hotspot/walled-garden/remove', [HotspotController::class, 'removeWalledGarden']);

        // Hotspot - Generate
        $router->get('/{session}/hotspot/generate', [GeneratorController::class, 'index']);
        $router->post('/{session}/hotspot/generate/process', [GeneratorController::class, 'process']);

        // Traffic Monitor
        $router->get('/{session}/traffic/monitor', [TrafficController::class, 'monitor']);
        $router->get('/{session}/traffic/interfaces', [TrafficController::class, 'getInterfaces']);

        // Reports
        $router->get('/{session}/reports/selling', [ReportController::class, 'index']);
        $router->get('/{session}/reports/selling/export/{type}', [ReportController::class, 'sellingExport']);
        $router->get('/{session}/reports/resume', [ReportController::class, 'resume']);
        $router->get('/{session}/reports/user-log', [LogController::class, 'index']);

        // System Tools
        $router->post('/{session}/system/reboot', [SystemController::class, 'reboot']);
        $router->post('/{session}/system/shutdown', [SystemController::class, 'shutdown']);
        $router->get('/{session}/system/scheduler', [SchedulerController::class, 'index']);
        $router->post('/{session}/system/scheduler/store', [SchedulerController::class, 'store']);
        $router->post('/{session}/system/scheduler/update', [SchedulerController::class, 'update']);
        $router->post('/{session}/system/scheduler/delete', [SchedulerController::class, 'delete']);

        // Network & Cookies
        $router->get('/{session}/network/dhcp', [DhcpController::class, 'index']);
        $router->get('/{session}/hotspot/cookies', [HotspotController::class, 'cookies']);
        $router->post('/{session}/hotspot/cookies/remove', [HotspotController::class, 'removeCookie']);

        // Quick Print
        $router->get('/{session}/quick-print', [QuickPrintController::class, 'index']);
        $router->get('/{session}/quick-print/manage', [QuickPrintController::class, 'manage']);
        $router->post('/{session}/quick-print/store', [QuickPrintController::class, 'store']);
        $router->post('/{session}/quick-print/update', [QuickPrintController::class, 'update']);
        $router->post('/{session}/quick-print/delete', [QuickPrintController::class, 'delete']);
        $router->get('/{session}/quick-print/print/([a-zA-Z0-9_-]+)', [QuickPrintController::class, 'printPacket']);

    }); // End Router Context Group
}); // End Auth Group
