<?php
// Test Revenue API endpoint
session_start();

// Set test session
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/BaseModel.php';
require_once __DIR__ . '/../app/models/RevenueReport.php';

echo "<h2>Test Revenue API</h2>";

// Test data
$testData = [
    'payment_date' => '2025-11-14',
    'transfer_type' => 'cash',
    'receipt_code' => 'TEST001',
    'amount' => 2300000,
    'student_name' => 'Cao Văn Hai',
    'course_id' => null, // Test without course
    'payment_content' => 'deposit',
    'staff_id' => 1,
    'notes' => 'Test revenue entry'
];

echo "<h3>Test Data:</h3>";
echo "<pre>";
print_r($testData);
echo "</pre>";

try {
    $revenueModel = new RevenueReport();
    
    echo "<h3>Attempting to create revenue record...</h3>";
    $revenueId = $revenueModel->create($testData);
    
    echo "<div style='color: green; font-weight: bold;'>✓ SUCCESS! Revenue ID: {$revenueId}</div>";
    
    // Retrieve and display
    echo "<h3>Retrieved Record:</h3>";
    $record = $revenueModel->find($revenueId);
    echo "<pre>";
    print_r($record);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold;'>✗ ERROR: " . $e->getMessage() . "</div>";
    echo "<pre>";
    echo $e->getTraceAsString();
    echo "</pre>";
}
