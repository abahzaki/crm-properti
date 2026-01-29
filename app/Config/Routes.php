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
// DASHBOARD
// Pastikan string kedua adalah 'DashboardController::index' (bukan 'Dashboard::index')
$routes->get('dashboard', 'DashboardController::index', ['filter' => 'authGuard']);
$routes->get('/', 'DashboardController::index', ['filter' => 'authGuard']);

// 4. HALAMAN LEADS
$routes->get('/leads', 'LeadsController::index', ['filter' => 'authGuard']);
$routes->post('/leads/save', 'LeadsController::save', ['filter' => 'authGuard']);
$routes->post('/leads/update-status', 'LeadsController::updateStatus', ['filter' => 'authGuard']);
$routes->post('/leads/update', 'LeadsController::update', ['filter' => 'authGuard']);

// 5. USER MANAGEMENT (Admin/Owner Only)
$routes->get('/users', 'UsersController::index', ['filter' => 'authGuard']);
$routes->post('/users/save', 'UsersController::save', ['filter' => 'authGuard']);
$routes->post('/users/update', 'UsersController::update', ['filter' => 'authGuard']);

// 6. MODULE BOOKINGS & APPROVAL
// PENTING: Filter disamakan menjadi 'authGuard' (bukan 'auth') agar konsisten
$routes->group('bookings', ['filter' => 'authGuard'], function($routes) {
    
    // List Dashboard Booking
    $routes->get('/', 'BookingsController::index'); 
    
    // Form Input Booking Baru (Sales)
    $routes->get('new', 'BookingsController::new'); 
    
    // Proses Simpan Data Booking
    $routes->post('/', 'BookingsController::create'); 
    
    // Proses Approve (Admin)
    $routes->get('approve/(:num)', 'BookingsController::approve/$1');
    
    // Proses Reject (Admin)
    $routes->post('reject', 'BookingsController::reject');
});

// ... Group Bookings yang tadi ...

// MODULE STOK UNIT (MANAGEMENT)
$routes->group('units', ['filter' => 'authGuard'], function($routes) {
    // 1. List Unit (Semua Role)
    $routes->get('/', 'UnitsController::index');
    
    // 2. Form Tambah (Admin Only - diprotect di controller juga)
    $routes->get('new', 'UnitsController::new');
    
    // 3. Proses Simpan
    $routes->post('save', 'UnitsController::save');
    
    // 4. Form Edit
    $routes->get('edit/(:num)', 'UnitsController::edit/$1');
    
    // 5. Proses Hapus
    $routes->post('delete', 'UnitsController::delete');
});

// MODULE CLOSING / AKAD (Pipeline Akhir)
$routes->group('closings', ['filter' => 'authGuard'], function($routes) {
    
    // 1. Halaman List Approval (Khusus Owner melihat pengajuan)
    $routes->get('/', 'ClosingsController::index');
    
    // 2. Form Pengajuan Akad (Dari menu Booking)
    $routes->get('process/(:num)', 'ClosingsController::new/$1');
    
    // 3. Proses Simpan Pengajuan (Upload Berkas)
    $routes->post('save', 'ClosingsController::create');
    
    // 4. Action Owner (Approve / Reject)
    $routes->post('approve', 'ClosingsController::approve');
    $routes->post('reject', 'ClosingsController::reject');
});

// SALES ACTIVITY LOG
$routes->post('activity/save', 'ActivityController::save', ['filter' => 'authGuard']);

// LOG AKTIVITAS (Halaman Monitoring)
$routes->get('activity-logs', 'ActivityController::index', ['filter' => 'authGuard']);

$routes->get('ads-report', 'AdsController::index', ['filter' => 'authGuard']);

// Tambahkan ini biar Sistem kenal alamatnya
$routes->post('leads/update-status', 'LeadsController::updateStatus');