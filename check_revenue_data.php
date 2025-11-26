<?php
define('CONFIG_PATH', __DIR__ . '/config/');
define('BASE_PATH', '/Quan_ly_trung_tam/public');

require_once 'core/Database.php';

try {
    $db = Database::getInstance();
    
    echo "=== KIỂM TRA DỮ LIỆU REVENUE_REPORTS ===\n\n";
    
    // Đếm tổng số doanh thu
    $total = $db->fetch("SELECT COUNT(*) as count FROM revenue_reports");
    echo "Tổng số doanh thu: " . $total['count'] . "\n\n";
    
    // Lấy danh sách staff_id
    $staffs = $db->fetchAll("SELECT DISTINCT staff_id, COUNT(*) as count FROM revenue_reports GROUP BY staff_id");
    echo "Doanh thu theo nhân viên:\n";
    foreach ($staffs as $staff) {
        echo "  - Staff ID {$staff['staff_id']}: {$staff['count']} doanh thu\n";
    }
    echo "\n";
    
    // Lấy 5 doanh thu gần nhất
    $recent = $db->fetchAll("SELECT id, student_name, amount, payment_date, staff_id FROM revenue_reports ORDER BY created_at DESC LIMIT 5");
    echo "5 doanh thu gần nhất:\n";
    foreach ($recent as $r) {
        echo "  - ID {$r['id']}: {$r['student_name']} - " . number_format($r['amount']) . "đ - Ngày: {$r['payment_date']} - Staff: {$r['staff_id']}\n";
    }
    echo "\n";
    
    // Kiểm tra user demo
    $demoUser = $db->fetch("SELECT id, username, role FROM users WHERE username = 'demo' OR role = 'staff' LIMIT 1");
    if ($demoUser) {
        echo "Thông tin user demo/staff:\n";
        echo "  - ID: {$demoUser['id']}\n";
        echo "  - Username: {$demoUser['username']}\n";
        echo "  - Role: {$demoUser['role']}\n\n";
        
        $staffRevenue = $db->fetchAll("SELECT id, student_name, amount, payment_date FROM revenue_reports WHERE staff_id = ? ORDER BY payment_date DESC LIMIT 5", [$demoUser['id']]);
        echo "Doanh thu của user này:\n";
        if (empty($staffRevenue)) {
            echo "  - Không có doanh thu nào!\n";
        } else {
            foreach ($staffRevenue as $r) {
                echo "  - ID {$r['id']}: {$r['student_name']} - " . number_format($r['amount']) . "đ - Ngày: {$r['payment_date']}\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
