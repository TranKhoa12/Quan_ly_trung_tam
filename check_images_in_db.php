<?php
require_once 'config/database.php';

$config = require 'config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}", 
    $config['username'], 
    $config['password']
);

echo "=== KIỂM TRA ẢNH TRONG DATABASE ===\n\n";

// Check completion_slips
echo "1. COMPLETION SLIPS (Phiếu hoàn thành):\n";
echo "==========================================\n";
$stmt = $pdo->query("SELECT id, student_name, image_files FROM completion_slips WHERE image_files IS NOT NULL AND image_files != '' AND image_files != '[]' ORDER BY id DESC LIMIT 5");
$slips = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($slips) > 0) {
    foreach ($slips as $slip) {
        $images = json_decode($slip['image_files'], true);
        $count = is_array($images) ? count($images) : 0;
        echo "ID: {$slip['id']} | Student: {$slip['student_name']}\n";
        echo "  → {$count} ảnh: " . $slip['image_files'] . "\n\n";
    }
} else {
    echo "  ✗ Không có phiếu nào có ảnh\n\n";
}

// Check revenue_reports
echo "2. REVENUE REPORTS (Báo cáo doanh thu):\n";
echo "==========================================\n";
$stmt = $pdo->query("SELECT id, student_name, confirmation_image, confirmation_images FROM revenue_reports WHERE (confirmation_image IS NOT NULL OR confirmation_images IS NOT NULL) ORDER BY id DESC LIMIT 5");
$revenues = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($revenues) > 0) {
    foreach ($revenues as $rev) {
        $oldImg = $rev['confirmation_image'] ?? 'null';
        $newImgs = $rev['confirmation_images'] ?? 'null';
        
        $images = json_decode($newImgs, true);
        $count = is_array($images) ? count($images) : 0;
        
        echo "ID: {$rev['id']} | Student: {$rev['student_name']}\n";
        echo "  → Old: $oldImg\n";
        echo "  → New ({$count} ảnh): $newImgs\n\n";
    }
} else {
    echo "  ✗ Không có revenue nào có ảnh\n\n";
}

// Check certificates
echo "3. CERTIFICATES (Chứng nhận):\n";
echo "==========================================\n";
$stmt = $pdo->query("SELECT COUNT(*) FROM certificates");
$count = $stmt->fetchColumn();
echo "  → Certificates không có cột lưu ảnh trong bảng chính\n";
echo "  → Tổng số yêu cầu chứng nhận: $count\n\n";

echo "✓ Kiểm tra hoàn tất!\n";
