<?php

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('CORE_PATH', BASE_PATH . '/core');

// Autoload function
spl_autoload_register(function ($className) {
    $directories = [
        CORE_PATH . '/',
        APP_PATH . '/controllers/',
        APP_PATH . '/models/',
        APP_PATH . '/middleware/'
    ];

    foreach ($directories as $directory) {
        $file = $directory . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Start session
session_start();

// Set timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Include config
$config = include CONFIG_PATH . '/app.php';

// Initialize router
$router = new Router();

// Web routes
$router->get('/', 'Home@index');
$router->get('/login', 'Auth@showLogin');
$router->post('/login', 'Auth@login');
$router->get('/logout', 'Auth@logout');
$router->get('/forgot-password', 'Auth@showForgotPassword');
$router->post('/forgot-password', 'Auth@forgotPassword');
$router->get('/reset-password/{token}', 'Auth@showResetPassword');
$router->post('/reset-password', 'Auth@resetPassword');
$router->get('/dashboard', 'Dashboard@index');

// Reports routes
$router->get('/reports', 'Report@index');
$router->get('/reports/create', 'Report@create');
$router->post('/reports', 'Report@store');
$router->get('/reports/{id}', 'Report@show');
$router->get('/reports/{id}/edit', 'Report@edit');
$router->post('/reports/{id}/update', 'Report@update');
$router->post('/reports/{id}/delete', 'Report@delete');

// Revenue routes
$router->get('/revenue', 'Revenue@index');
$router->get('/revenue/create', 'Revenue@create');
$router->post('/revenue', 'Revenue@store');
$router->get('/revenue/check-receipt-code', 'Revenue@checkReceiptCode');
$router->post('/revenue/processOCR', 'Revenue@processOCR');
$router->get('/revenue/{id}', 'Revenue@show');

// Students routes
$router->get('/students', 'Student@index');
$router->get('/students/create', 'Student@create');
$router->post('/students', 'Student@store');

// Certificates routes
$router->get('/certificates', 'Certificate@index');
$router->get('/certificates/create', 'Certificate@create');
$router->post('/certificates', 'Certificate@store');
$router->put('/certificates/{id}/approve', 'Certificate@approve');

// Staff management routes (Admin only)
$router->get('/staff', 'Staff@index');
$router->get('/staff/create', 'Staff@create');
$router->post('/staff', 'Staff@store');
$router->get('/staff/{id}', 'Staff@show');
$router->get('/staff/{id}/edit', 'Staff@edit');
$router->put('/staff/{id}/update', 'Staff@update');
$router->delete('/staff/{id}/delete', 'Staff@delete');

// Course management routes (Admin only)
$router->get('/courses', 'Course@index');
$router->get('/courses/create', 'Course@create');
$router->post('/courses', 'Course@store');
$router->get('/courses/import', 'Course@import');
$router->post('/courses/process-import', 'Course@processImport');
$router->get('/courses/download-template', 'Course@downloadTemplate');
$router->get('/courses/export', 'Course@export');
$router->get('/courses/{id}', 'Course@show');
$router->get('/courses/{id}/edit', 'Course@edit');
$router->put('/courses/{id}/update', 'Course@update');
$router->delete('/courses/{id}/delete', 'Course@delete');

// OCR route
$router->post('/ocr', 'OCR@processImage');

// API routes
$router->api('GET', '/reports', 'Report@apiIndex');
$router->api('POST', '/reports', 'Report@apiStore');
$router->api('GET', '/reports/{id}', 'Report@apiShow');
$router->api('PUT', '/reports/{id}', 'Report@apiUpdate');
$router->api('DELETE', '/reports/{id}', 'Report@apiDelete');

$router->api('GET', '/revenue', 'Revenue@apiIndex');
$router->api('POST', '/revenue', 'Revenue@apiStore');
$router->api('POST', '/revenue/from-report', 'Revenue@apiStoreFromReport');
$router->api('GET', '/revenue/{id}', 'Revenue@apiShow');

$router->api('GET', '/students', 'Student@apiIndex');
$router->api('POST', '/students', 'Student@apiStore');
$router->api('PUT', '/students/{id}', 'Student@apiUpdate');

$router->api('GET', '/certificates', 'Certificate@apiIndex');
$router->api('POST', '/certificates', 'Certificate@apiStore');
$router->api('PUT', '/certificates/{id}', 'Certificate@apiUpdate');

// Dispatch the request
$router->dispatch();