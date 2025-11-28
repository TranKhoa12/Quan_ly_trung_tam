<?php
require_once 'config/database.php';

$config = require 'config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}", 
    $config['username'], 
    $config['password']
);

echo "=== DANH SÁCH TẤT CẢ USERS ===\n\n";
echo str_pad('ID', 5) . str_pad('Username', 20) . str_pad('Full Name', 30) . str_pad('Role', 10) . 'Status' . "\n";
echo str_repeat('-', 80) . "\n";

$stmt = $pdo->query('SELECT id, username, full_name, role, status FROM users ORDER BY role, full_name');
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo str_pad($row['id'], 5) . 
         str_pad($row['username'], 20) . 
         str_pad($row['full_name'], 30) . 
         str_pad($row['role'], 10) . 
         $row['status'] . "\n";
}

echo "\n=== DANH SÁCH CHỈ STAFF (role='staff' AND status='active') ===\n\n";
$stmt = $pdo->query("SELECT id, username, full_name FROM users WHERE role = 'staff' AND status = 'active' ORDER BY full_name");
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "ID: {$row['id']} - {$row['full_name']} ({$row['username']})\n";
}
