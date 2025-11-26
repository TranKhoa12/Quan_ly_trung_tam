<?php
define('CONFIG_PATH', __DIR__ . '/config/');
define('BASE_PATH', '/Quan_ly_trung_tam/public');

require_once 'core/Database.php';

try {
    $db = Database::getInstance();
    
    echo "=== KIỂM TRA USERS ===\n\n";
    
    $users = $db->fetchAll("SELECT id, username, full_name, role FROM users ORDER BY id");
    foreach ($users as $user) {
        echo "ID: {$user['id']} | Username: {$user['username']} | Name: {$user['full_name']} | Role: {$user['role']}\n";
        
        // Đếm doanh thu của user này
        $count = $db->fetch("SELECT COUNT(*) as count FROM revenue_reports WHERE staff_id = ?", [$user['id']]);
        echo "  → Có {$count['count']} doanh thu\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
