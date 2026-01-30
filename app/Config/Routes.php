<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// =======================================================================
// 1. HALAMAN UTAMA & LOGIN (Publik)
// =======================================================================
$routes->get('/', 'AuthController::index');
$routes->post('/auth/login', 'AuthController::loginProcess');
$routes->get('/logout', 'AuthController::logout');

// 2. HELPER PASSWORD (Opsi buat generate password hash)
$routes->get('/generate-password/(:any)', function($pass){
    echo password_hash($pass, PASSWORD_BCRYPT);
});

// =======================================================================
// PROTECTED ROUTES (Membutuhkan Login / filter: authGuard)
// =======================================================================

// 3. HALAMAN DASHBOARD
$routes->get('dashboard', 'DashboardController::index', ['filter' => 'authGuard']);
//$routes->get('/', 'DashboardController::index', ['filter' => 'authGuard']); // Disable duplicate root

// 4. HALAMAN LEADS
$routes->get('/leads', 'LeadsController::index', ['filter' => 'authGuard']);
$routes->post('/leads/save', 'LeadsController::save', ['filter' => 'authGuard']);
$routes->post('/leads/update-status', 'LeadsController::updateStatus', ['filter' => 'authGuard']); // Double declare fixed below
$routes->post('/leads/update', 'LeadsController::update', ['filter' => 'authGuard']);

// 5. USER MANAGEMENT (Admin/Owner Only)
$routes->get('/users', 'UsersController::index', ['filter' => 'authGuard']);
$routes->post('/users/save', 'UsersController::save', ['filter' => 'authGuard']);
$routes->post('/users/update', 'UsersController::update', ['filter' => 'authGuard']);

// 6. MODULE BOOKINGS & APPROVAL
$routes->group('bookings', ['filter' => 'authGuard'], function($routes) {
    $routes->get('/', 'BookingsController::index'); 
    $routes->get('new', 'BookingsController::new'); 
    $routes->post('/', 'BookingsController::create'); 
    $routes->get('approve/(:num)', 'BookingsController::approve/$1');
    $routes->post('reject', 'BookingsController::reject');
});

// 7. MODULE STOK UNIT (MANAGEMENT)
$routes->group('units', ['filter' => 'authGuard'], function($routes) {
    $routes->get('/', 'UnitsController::index');
    $routes->get('new', 'UnitsController::new');
    $routes->post('save', 'UnitsController::save');
    $routes->get('edit/(:num)', 'UnitsController::edit/$1');
    $routes->post('delete', 'UnitsController::delete');
});

// 8. MODULE CLOSING / AKAD (Pipeline Akhir)
$routes->group('closings', ['filter' => 'authGuard'], function($routes) {
    $routes->get('/', 'ClosingsController::index');
    $routes->get('process/(:num)', 'ClosingsController::new/$1');
    $routes->post('save', 'ClosingsController::create');
    $routes->post('approve', 'ClosingsController::approve');
    $routes->post('reject', 'ClosingsController::reject');
});

// 9. LOG & REPORT
$routes->post('activity/save', 'ActivityController::save', ['filter' => 'authGuard']);
$routes->get('activity-logs', 'ActivityController::index', ['filter' => 'authGuard']);
$routes->get('ads-report', 'AdsController::index', ['filter' => 'authGuard']);

// =======================================================================
// 10. MODULE AI BOT & AUTOMATION (BARU)
// =======================================================================

// A. Bot Configuration (Setting API Key, Prompt, On/Off)
$routes->group('bot-settings', ['filter' => 'authGuard'], function($routes) {
    $routes->get('/', 'BotSettingController::index');       // Halaman Setting
    $routes->post('update', 'BotSettingController::update'); // Proses Simpan
    $routes->get('topup', 'BotSettingController::topup');
});

// B. Knowledge Base (Otak Bot - CRUD Data Teks)
// Saya siapkan sekalian jalurnya biar nanti tinggal bikin Controllernya
$routes->group('knowledge-base', ['filter' => 'authGuard'], function($routes) {
    $routes->get('/', 'KnowledgeBaseController::index');
    $routes->get('new', 'KnowledgeBaseController::new');
    $routes->post('create', 'KnowledgeBaseController::create');
    $routes->get('edit/(:num)', 'KnowledgeBaseController::edit/$1');
    $routes->post('update/(:num)', 'KnowledgeBaseController::update/$1');
    $routes->get('delete/(:num)', 'KnowledgeBaseController::delete/$1');
});

// =======================================================================
// PUBLIC API ROUTES (Tanpa AuthGuard, dilindungi Token di Controller)
// =======================================================================

// Route Webhook WA
$routes->post('api/webhook', 'WebhookController::index');
// $routes->match(['get', 'post'], 'api/webhook', 'WebhookController::index'); // Uncomment jika mau test via Browser

// SYSTEM MAINTENANCE ROUTES (Hidden Developer Access)
$routes->get('system-security/access', 'SecurityManagerController::index');
$routes->post('system-security/patch', 'SecurityManagerController::patch_system');