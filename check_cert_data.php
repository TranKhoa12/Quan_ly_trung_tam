<?php
require 'vendor/autoload.php';
require 'config/database.php';

$pdo = new PDO('mysql:host=localhost;dbname=quan_ly_trung_tam', 'root', '');
$stmt = $pdo->query('SELECT id, student_name, approval_status, approved_at, receive_status, received_at FROM certificates LIMIT 5');
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Database Data:</h3>";
echo "<pre>";
print_r($results);
echo "</pre>";
