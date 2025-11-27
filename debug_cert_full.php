<?php
require 'vendor/autoload.php';
require 'config/database.php';

$pdo = new PDO('mysql:host=localhost;dbname=quan_ly_trung_tam', 'root', '');

// Kiểm tra cấu trúc bảng
echo "<h3>1. Cấu trúc bảng certificates:</h3>";
echo "<pre>";
$stmt = $pdo->query('DESCRIBE certificates');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    if (in_array($col['Field'], ['approved_at', 'received_at'])) {
        echo "Column: " . $col['Field'] . "\n";
        echo "Type: " . $col['Type'] . "\n";
        echo "Null: " . $col['Null'] . "\n";
        echo "Default: " . $col['Default'] . "\n\n";
    }
}
echo "</pre>";

// Kiểm tra dữ liệu thực tế
echo "<h3>2. Dữ liệu thực tế trong database:</h3>";
echo "<pre>";
$stmt = $pdo->query('SELECT id, student_name, approval_status, approved_at, receive_status, received_at FROM certificates LIMIT 5');
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $row) {
    echo "ID: " . $row['id'] . "\n";
    echo "Student: " . $row['student_name'] . "\n";
    echo "Approval Status: " . $row['approval_status'] . "\n";
    echo "Approved At RAW: [" . var_export($row['approved_at'], true) . "]\n";
    echo "Approved At is NULL: " . (is_null($row['approved_at']) ? 'YES' : 'NO') . "\n";
    echo "Approved At empty: " . (empty($row['approved_at']) ? 'YES' : 'NO') . "\n";
    echo "Receive Status: " . $row['receive_status'] . "\n";
    echo "Received At RAW: [" . var_export($row['received_at'], true) . "]\n";
    echo "Received At is NULL: " . (is_null($row['received_at']) ? 'YES' : 'NO') . "\n";
    echo "Received At empty: " . (empty($row['received_at']) ? 'YES' : 'NO') . "\n";
    echo "---\n\n";
}
echo "</pre>";

// Test điều kiện hiển thị
echo "<h3>3. Test điều kiện hiển thị:</h3>";
echo "<pre>";
foreach ($results as $row) {
    echo "Certificate ID: " . $row['id'] . "\n";
    
    $test1 = in_array($row['approval_status'], ['approved', 'cancelled']);
    $test2 = isset($row['approved_at']);
    $test3 = !empty($row['approved_at']);
    $test4 = $row['approved_at'] != '0000-00-00 00:00:00';
    
    echo "- in_array(['approved', 'cancelled']): " . ($test1 ? 'TRUE' : 'FALSE') . "\n";
    echo "- isset(approved_at): " . ($test2 ? 'TRUE' : 'FALSE') . "\n";
    echo "- !empty(approved_at): " . ($test3 ? 'TRUE' : 'FALSE') . "\n";
    echo "- != '0000-00-00 00:00:00': " . ($test4 ? 'TRUE' : 'FALSE') . "\n";
    echo "- ALL CONDITIONS: " . (($test1 && $test2 && $test3 && $test4) ? 'PASS - SẼ HIỆN' : 'FAIL - KHÔNG HIỆN') . "\n";
    echo "\n";
}
echo "</pre>";
