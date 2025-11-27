<?php
require 'vendor/autoload.php';
require 'config/database.php';
require 'core/Database.php';
require 'core/BaseModel.php';
require 'app/models/Certificate.php';

echo "<h3>Test query trực tiếp:</h3>";
$pdo = new PDO('mysql:host=localhost;dbname=quan_ly_trung_tam', 'root', '');

// Test 1: Query đơn giản
echo "<h4>Test 1: SELECT * FROM certificates LIMIT 1</h4><pre>";
$stmt = $pdo->query('SELECT * FROM certificates LIMIT 1');
$result = $stmt->fetch(PDO::FETCH_ASSOC);
print_r(array_keys($result));
echo "\n\nFull data:\n";
print_r($result);
echo "</pre>";

// Test 2: Query với JOIN như trong model
echo "<h4>Test 2: Query với JOIN (như trong Model)</h4><pre>";
$sql = "SELECT c.*, 
        u1.full_name as requested_by_name,
        u2.full_name as approved_by_name 
        FROM certificates c 
        LEFT JOIN users u1 ON c.requested_by = u1.id 
        LEFT JOIN users u2 ON c.approved_by = u2.id 
        LIMIT 1";
$stmt = $pdo->query($sql);
$result = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
echo "Keys in result:\n";
print_r(array_keys($result));
echo "\n\nFull data:\n";
print_r($result);
echo "</pre>";

// Test 3: Check nếu cột tồn tại
echo "<h4>Test 3: Kiểm tra cột approved_at và received_at</h4><pre>";
echo "approved_at exists: " . (array_key_exists('approved_at', $result) ? 'YES' : 'NO') . "\n";
echo "received_at exists: " . (array_key_exists('received_at', $result) ? 'YES' : 'NO') . "\n";
if (array_key_exists('approved_at', $result)) {
    echo "approved_at value: [" . var_export($result['approved_at'], true) . "]\n";
}
if (array_key_exists('received_at', $result)) {
    echo "received_at value: [" . var_export($result['received_at'], true) . "]\n";
}
echo "</pre>";
