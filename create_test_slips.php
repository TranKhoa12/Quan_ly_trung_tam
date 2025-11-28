<?php
require_once 'config/database.php';

$config = require 'config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}", 
    $config['username'], 
    $config['password']
);

// Get a staff user ID
$stmt = $pdo->query("SELECT id FROM users WHERE role = 'staff' LIMIT 1");
$staffId = $stmt->fetchColumn();

// Get a course ID
$stmt = $pdo->query("SELECT id FROM courses LIMIT 1");
$courseId = $stmt->fetchColumn();

if (!$staffId || !$courseId) {
    die("Cần có ít nhất 1 staff và 1 course trong database\n");
}

echo "Đang tạo 15 phiếu test để kiểm tra phân trang...\n\n";

for ($i = 1; $i <= 15; $i++) {
    $sql = "INSERT INTO completion_slips (student_name, course_id, teacher_name, notes, created_by, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        "Học viên test " . $i,
        $courseId,
        "Giáo viên test",
        "Phiếu test số " . $i,
        $staffId
    ]);
    
    echo "✓ Đã tạo phiếu test #$i\n";
}

echo "\n✓ Hoàn tất! Đã tạo 15 phiếu test.\n";
echo "Bây giờ reload trang danh sách sẽ thấy phân trang (tổng 20 phiếu = 2 trang)\n";
