<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Define paths
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');

// Set test user data
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'test';
$_SESSION['full_name'] = 'Test User';
$_SESSION['role'] = 'staff';

// Set test data
$user = [
    'id' => 1,
    'username' => 'test',
    'full_name' => 'Test User',
    'role' => 'staff'
];

$stats = [
    'my_reports_today' => 5,
    'my_revenue_today' => 1000000,
    'my_visitors_today' => 10,
    'my_certificates_pending' => 3
];

// Include the view
include APP_PATH . '/views/dashboard/staff.php';
?>
