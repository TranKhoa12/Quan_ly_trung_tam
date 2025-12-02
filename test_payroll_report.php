<?php
// Simple test file
require_once __DIR__ . '/../../core/BaseController.php';
require_once __DIR__ . '/../../models/ShiftPayroll.php';

session_start();

// Check if user is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die('Access denied');
}

$payrollModel = new ShiftPayroll();

echo "<h1>Payroll Report Debug</h1>";
echo "<p>Testing database connection and data...</p>";

// Test getByPeriod
$testStart = '2025-12-01';
$testEnd = '2025-12-31';
echo "<h3>Testing getByPeriod($testStart, $testEnd, 'active'):</h3>";

try {
    $result = $payrollModel->getByPeriod($testStart, $testEnd, 'active');
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    echo "<p>Count: " . count($result) . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>ERROR: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Test statistics generation
echo "<h3>Generating 12 months statistics:</h3>";
$months = [];
for ($i = 11; $i >= 0; $i--) {
    $date = date('Y-m-01', strtotime("-$i months"));
    $months[] = [
        'key' => date('Y-m', strtotime($date)),
        'display' => date('m/Y', strtotime($date)),
        'start' => date('Y-m-01', strtotime($date)),
        'end' => date('Y-m-t', strtotime($date))
    ];
}

echo "<p>Months array:</p><pre>";
print_r($months);
echo "</pre>";

$statistics = [];
foreach ($months as $month) {
    try {
        $payrolls = $payrollModel->getByPeriod($month['start'], $month['end'], 'active');
        
        $totalStaff = count($payrolls);
        $totalAmount = array_sum(array_column($payrolls, 'total_amount'));
        $totalHours = array_sum(array_column($payrolls, 'total_hours'));
        
        $statistics[] = [
            'month' => $month['display'],
            'month_key' => $month['key'],
            'total_staff' => $totalStaff,
            'total_amount' => (float)$totalAmount,
            'total_hours' => (float)$totalHours,
            'avg_amount' => $totalStaff > 0 ? $totalAmount / $totalStaff : 0
        ];
    } catch (Exception $e) {
        echo "<p style='color:red'>ERROR for month {$month['display']}: " . $e->getMessage() . "</p>";
    }
}

echo "<h3>Statistics:</h3><pre>";
print_r($statistics);
echo "</pre>";

echo "<h3>Success! Now check actual report page.</h3>";
echo "<p><a href='/Quan_ly_trung_tam/public/teaching-shifts/payroll/report'>Go to Payroll Report</a></p>";
?>
