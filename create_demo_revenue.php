<?php
define('CONFIG_PATH', __DIR__ . '/config/');
define('BASE_PATH', '/Quan_ly_trung_tam/public');

require_once 'core/Database.php';

try {
    $db = Database::getInstance();
    
    echo "=== TẠO DỮ LIỆU TEST CHO USER DEMO ===\n\n";
    
    // Lấy course_id thực tế từ database
    $courses = $db->fetchAll("SELECT id FROM courses LIMIT 3");
    $courseIds = !empty($courses) ? array_column($courses, 'id') : [null, null, null];
    
    // Tạo 3 doanh thu test cho user demo (ID=10)
    $testData = [
        [
            'payment_date' => '2025-11-14',
            'transfer_type' => 'cash',
            'receipt_code' => 'BL001',
            'amount' => 2000000,
            'student_name' => 'Nguyễn Văn A',
            'course_id' => $courseIds[0],
            'payment_content' => 'full_payment',
            'staff_id' => 10,
            'notes' => 'Học phí khóa test'
        ],
        [
            'payment_date' => '2025-11-13',
            'transfer_type' => 'account_co_nhi',
            'receipt_code' => 'BL002',
            'amount' => 1500000,
            'student_name' => 'Trần Thị B',
            'course_id' => $courseIds[1],
            'payment_content' => 'deposit',
            'staff_id' => 10,
            'notes' => 'Đặt cọc khóa học'
        ],
        [
            'payment_date' => '2025-11-12',
            'transfer_type' => 'account_company',
            'receipt_code' => 'BL003',
            'amount' => 3000000,
            'student_name' => 'Lê Văn C',
            'course_id' => $courseIds[2],
            'payment_content' => 'full_payment',
            'staff_id' => 10,
            'notes' => 'Thanh toán đầy đủ'
        ]
    ];
    
    foreach ($testData as $data) {
        $sql = "INSERT INTO revenue_reports (payment_date, transfer_type, receipt_code, amount, student_name, course_id, payment_content, staff_id, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $data['payment_date'],
            $data['transfer_type'],
            $data['receipt_code'],
            $data['amount'],
            $data['student_name'],
            $data['course_id'],
            $data['payment_content'],
            $data['staff_id'],
            $data['notes']
        ];
        $db->query($sql, $params);
        echo "✅ Đã tạo doanh thu: {$data['student_name']} - " . number_format($data['amount']) . "đ\n";
    }
    
    echo "\n✅ Hoàn tất! User demo giờ có 3 doanh thu.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
