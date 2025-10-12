<?php
// Test create empty report
session_start();

// Simulate POST data for empty report
$_POST = [
    'report_date' => date('Y-m-d'),
    'report_time' => date('H:i:s'),
    'staff_id' => 1,
    'total_visitors' => 0,
    'total_registered' => 0,
    'empty_report' => 1,
    'notes' => 'Báo cáo rỗng - Test'
];

// Simulate user session
$_SESSION['user'] = [
    'id' => 1,
    'username' => 'admin',
    'role' => 'admin'
];

echo "Testing empty report creation...\n";
echo "POST data: " . print_r($_POST, true) . "\n";

// Include required files
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/BaseModel.php';
require_once __DIR__ . '/app/models/Report.php';
require_once __DIR__ . '/app/models/ReportCustomer.php';
require_once __DIR__ . '/app/models/Course.php';
require_once __DIR__ . '/app/models/User.php';
require_once __DIR__ . '/core/BaseController.php';
require_once __DIR__ . '/app/controllers/ReportController.php';

try {
    $controller = new ReportController();
    echo "Controller created successfully\n";
    
    // Call store method
    ob_start();
    $controller->store();
    $output = ob_get_clean();
    
    echo "Store method completed\n";
    echo "Output: " . $output . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Check if record was created
try {
    $db = new Database();
    $pdo = $db->getConnection();
    $stmt = $pdo->query('SELECT * FROM reports ORDER BY id DESC LIMIT 1');
    $report = $stmt->fetch();
    
    if ($report) {
        echo "Report created successfully:\n";
        echo "ID: " . $report['id'] . "\n";
        echo "Date: " . $report['report_date'] . "\n";
        echo "Visitors: " . $report['total_visitors'] . "\n";
        echo "Registered: " . $report['total_registered'] . "\n";
        echo "Notes: " . $report['notes'] . "\n";
    } else {
        echo "No report found in database\n";
    }
} catch (Exception $e) {
    echo "Database check error: " . $e->getMessage() . "\n";
}
?>