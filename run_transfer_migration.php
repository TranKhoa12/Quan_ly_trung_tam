<?php
// Script để chạy migrations cho chức năng chuyển ca

$dbConfig = require __DIR__ . '/config/database.php';

try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $db = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
    
    echo "=== BẮT ĐẦU MIGRATION CHỨC NĂNG CHUYỂN CA ===\n\n";
    
    // 1. Tạo bảng shift_transfers
    echo "1. Đang tạo bảng shift_transfers...\n";
    $sql1 = file_get_contents(__DIR__ . '/database/migrations/create_shift_transfers_table.sql');
    $db->exec($sql1);
    echo "   ✓ Đã tạo bảng shift_transfers thành công!\n\n";
    
    // 2. Tạo bảng shift_transfer_logs
    echo "2. Đang tạo bảng shift_transfer_logs...\n";
    $sql2 = file_get_contents(__DIR__ . '/database/migrations/create_shift_transfer_logs_table.sql');
    $db->exec($sql2);
    echo "   ✓ Đã tạo bảng shift_transfer_logs thành công!\n\n";
    
    echo "=== HOÀN THÀNH MIGRATION ===\n\n";
    echo "Các bảng đã được tạo:\n";
    echo "  - shift_transfers (lưu yêu cầu chuyển ca)\n";
    echo "  - shift_transfer_logs (lưu lịch sử log)\n\n";
    
    echo "Chức năng chuyển ca đã sẵn sàng sử dụng!\n";
    echo "\nCác tính năng:\n";
    echo "  - Nhân viên: Tạo yêu cầu chuyển ca tại /teaching-shifts/transfer/{registrationId}\n";
    echo "  - Nhân viên: Xem yêu cầu của mình tại /teaching-shifts/transfers/my\n";
    echo "  - Admin: Quản lý yêu cầu tại /teaching-shifts/transfers/list\n";
    echo "  - Admin: Duyệt/Từ chối yêu cầu\n";
    echo "  - Hệ thống: Tự động log mọi thay đổi\n\n";
    
} catch (PDOException $e) {
    echo "LỖI: " . $e->getMessage() . "\n";
    echo "\nChi tiết lỗi:\n";
    echo "  - Code: " . $e->getCode() . "\n";
    echo "  - File: " . $e->getFile() . "\n";
    echo "  - Line: " . $e->getLine() . "\n\n";
    
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "Lưu ý: Bảng có thể đã tồn tại. Nếu muốn tạo lại, hãy xóa bảng cũ trước:\n";
        echo "  DROP TABLE IF EXISTS shift_transfer_logs;\n";
        echo "  DROP TABLE IF EXISTS shift_transfers;\n\n";
    }
    
    exit(1);
}
