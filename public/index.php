<?php

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('CORE_PATH', BASE_PATH . '/core');

// Load Composer autoload
require_once BASE_PATH . '/vendor/autoload.php';

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
$router->get('/profile', 'Profile@index');
$router->post('/profile/update', 'Profile@update');

// Reports routes
$router->get('/reports', 'Report@index');
$router->get('/reports/create', 'Report@create');
$router->post('/reports', 'Report@store');
$router->post('/reports/delete-multiple', 'Report@deleteMultiple');
$router->get('/reports/{id}', 'Report@show');
$router->get('/reports/{id}/edit', 'Report@edit');
$router->post('/reports/{id}/update', 'Report@update');
$router->post('/reports/{id}/delete', 'Report@delete');

// Revenue routes
$router->get('/revenue', 'Revenue@index');
$router->get('/revenue/create', 'Revenue@create');
$router->post('/revenue', 'Revenue@store');
$router->post('/revenue/delete-multiple', 'Revenue@deleteMultiple');
$router->get('/revenue/check-receipt-code', 'Revenue@checkReceiptCode');
$router->post('/revenue/processOCR', 'Revenue@processOCR');
$router->get('/revenue/{id}', 'Revenue@show');

// Transfer Batch routes (Quản lý đợt chuyển tiền)
$router->get('/transfer-batch', 'TransferBatch@index');
$router->post('/transfer-batch/push-to-sheet', 'TransferBatch@pushToGoogleSheet');
$router->get('/transfer-batch/create', 'TransferBatch@create');
$router->post('/transfer-batch/store', 'TransferBatch@store');
$router->get('/transfer-batch/show/{id}', 'TransferBatch@show');
$router->get('/transfer-batch/edit/{id}', 'TransferBatch@edit');
$router->post('/transfer-batch/update/{id}', 'TransferBatch@update');
$router->get('/transfer-batch/delete/{id}', 'TransferBatch@delete');
$router->post('/transfer-batch/recalculate', 'TransferBatch@recalculate');

// Students routes
$router->get('/students', 'Student@index');
$router->get('/students/create', 'Student@create');
$router->post('/students', 'Student@store');

// Certificates routes
$router->get('/certificates', 'Certificate@index');
$router->get('/certificates/create', 'Certificate@create');
$router->post('/certificates', 'Certificate@store');
$router->post('/certificates/delete-multiple', 'Certificate@deleteMultiple');
$router->get('/certificates/{id}', 'Certificate@show');
$router->get('/certificates/{id}/edit', 'Certificate@edit');
$router->post('/certificates/{id}/update', 'Certificate@update');
$router->post('/certificates/{id}/status', 'Certificate@updateStatus');
$router->post('/certificates/{id}/receive', 'Certificate@updateReceiveStatus');
$router->put('/certificates/{id}/approve', 'Certificate@approve');
$router->get('/certificate-request', 'PublicCertificate@showForm');
$router->post('/certificate-request', 'PublicCertificate@submit');

// Completion slips routes
$router->get('/completion-slips', 'CompletionSlip@index');
$router->get('/completion-slips/export/pdf', 'CompletionSlip@exportPdf');
$router->get('/completion-slips/create', 'CompletionSlip@create');
$router->post('/completion-slips', 'CompletionSlip@store');
$router->get('/completion-slips/{id}/edit', 'CompletionSlip@edit');
$router->post('/completion-slips/{id}/update', 'CompletionSlip@update');
$router->post('/completion-slips/{id}/delete', 'CompletionSlip@delete');
$router->post('/completion-slips/delete-multiple', 'CompletionSlip@deleteMultiple');

// Teaching shift routes
$router->get('/teaching-shifts', 'TeachingShift@index');
$router->post('/teaching-shifts/register', 'TeachingShift@store');
$router->get('/teaching-shifts/admin', 'TeachingShift@admin');
$router->get('/teaching-shifts/admin/create', 'TeachingShift@adminCreate');
$router->post('/teaching-shifts/admin/create', 'TeachingShift@adminStore');
$router->post('/teaching-shifts/admin/create-multiple', 'TeachingShift@adminCreateMultiple');
$router->get('/teaching-shifts/payroll', 'TeachingShift@payroll');
$router->get('/teaching-shifts/payroll/report', 'TeachingShift@payrollReport');
$router->post('/teaching-shifts/payroll/finalize', 'TeachingShift@finalizePayroll');
$router->post('/teaching-shifts/payroll/cancel', 'TeachingShift@cancelPayroll');
$router->post('/teaching-shifts/payroll/save-staff', 'TeachingShift@saveStaffPayroll');
$router->post('/teaching-shifts/payroll/cancel-staff', 'TeachingShift@cancelStaffPayroll');
$router->get('/teaching-shifts/payroll/print', 'TeachingShift@printPayslip');
$router->get('/teaching-shifts/transfer/{registrationId}', 'TeachingShift@transferForm');
$router->post('/teaching-shifts/transfer/store', 'TeachingShift@transferStore');
$router->get('/teaching-shifts/transfers/my', 'TeachingShift@myTransfers');
$router->get('/teaching-shifts/transfers/list', 'TeachingShift@transferList');
$router->post('/teaching-shifts/transfer/approve/{transferId}', 'TeachingShift@transferApprove');
$router->post('/teaching-shifts/transfer/reject/{transferId}', 'TeachingShift@transferReject');
$router->post('/teaching-shifts/transfer/delete/{transferId}', 'TeachingShift@transferDelete');
$router->get('/teaching-shifts/transfer/detail/{transferId}', 'TeachingShift@transferDetail');
$router->post('/teaching-shifts/bulk-action', 'TeachingShift@bulkAction');
$router->post('/teaching-shifts/quick-approve', 'TeachingShift@quickApprove');
$router->post('/teaching-shifts/quick-reject', 'TeachingShift@quickReject');
$router->post('/teaching-shifts/{id}/cancel', 'TeachingShift@cancel');
$router->post('/teaching-shifts/{id}/status', 'TeachingShift@updateStatus');
$router->post('/teaching-shifts/{id}/delete', 'TeachingShift@delete');

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