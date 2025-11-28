<?php
session_start();

echo "=== THÔNG TIN ĐĂNG NHẬP ===\n\n";

if (isset($_SESSION['user'])) {
    echo "User đang đăng nhập: " . $_SESSION['user']['full_name'] . "\n";
    echo "ID: " . $_SESSION['user']['id'] . "\n";
    echo "Username: " . $_SESSION['user']['username'] . "\n";
    echo "Role: " . $_SESSION['user']['role'] . "\n\n";
    
    // Check completion slips
    require_once 'config/database.php';
    $config = require 'config/database.php';
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}", 
        $config['username'], 
        $config['password']
    );
    
    echo "=== PHIẾU BẠN CÓ THỂ SỬA ===\n\n";
    $stmt = $pdo->prepare("SELECT cs.id, cs.student_name, u.full_name as creator 
                           FROM completion_slips cs 
                           LEFT JOIN users u ON cs.created_by = u.id 
                           WHERE cs.created_by = ? OR ? = 'admin'");
    $stmt->execute([$_SESSION['user']['id'], $_SESSION['user']['role']]);
    
    $canEdit = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($canEdit) > 0) {
        foreach ($canEdit as $slip) {
            echo "- Phiếu #{$slip['id']}: {$slip['student_name']} (tạo bởi: {$slip['creator']})\n";
        }
    } else {
        echo "Không có phiếu nào bạn có thể sửa.\n";
    }
    
} else {
    echo "Chưa đăng nhập\n";
}
