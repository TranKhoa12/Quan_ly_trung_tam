<?php
/**
 * Tạo bảng completion_slips trong database
 */

require_once __DIR__ . '/config/database.php';

try {
    $config = require __DIR__ . '/config/database.php';
    
    $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    
    echo "Đang kết nối tới database '{$config['database']}'...\n";
    
    // Đọc file SQL
    $sqlFile = __DIR__ . '/database/add_completion_slips.sql';
    if (!file_exists($sqlFile)) {
        die("Lỗi: Không tìm thấy file {$sqlFile}\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    echo "Đang tạo bảng completion_slips...\n";
    $pdo->exec($sql);
    
    echo "✓ Tạo bảng completion_slips thành công!\n";
    
    // Kiểm tra bảng đã tạo
    $stmt = $pdo->query("SHOW TABLES LIKE 'completion_slips'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Xác nhận: Bảng completion_slips đã tồn tại trong database\n";
        
        // Hiển thị cấu trúc bảng
        echo "\nCấu trúc bảng completion_slips:\n";
        echo "--------------------------------\n";
        $columns = $pdo->query("DESCRIBE completion_slips")->fetchAll();
        foreach ($columns as $col) {
            echo sprintf("  %-20s %-20s %s\n", 
                $col['Field'], 
                $col['Type'], 
                $col['Null'] === 'YES' ? 'NULL' : 'NOT NULL'
            );
        }
    }
    
    echo "\n✓ Hoàn tất! Bây giờ bạn có thể lưu phiếu hoàn thành học viên.\n";
    
} catch (PDOException $e) {
    echo "✗ Lỗi: " . $e->getMessage() . "\n";
    exit(1);
}
