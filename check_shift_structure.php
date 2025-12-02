<?php
// Script kiểm tra cấu trúc bảng shift_registrations

$dbConfig = require __DIR__ . '/config/database.php';

try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $db = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
    
    echo "=== KIỂM TRA CẤU TRÚC BẢNG shift_registrations ===\n\n";
    
    // Lấy cấu trúc bảng
    $stmt = $db->query("DESCRIBE shift_registrations");
    $columns = $stmt->fetchAll();
    
    echo "Các cột trong bảng:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']}) | Null: {$col['Null']} | Default: {$col['Default']}\n";
    }
    
    echo "\n=== KIỂM TRA DỮ LIỆU MẪU ===\n\n";
    
    // Lấy 1 record để xem đầy đủ
    $stmt = $db->query("SELECT * FROM shift_registrations WHERE status = 'approved' LIMIT 1");
    $sample = $stmt->fetch();
    
    if ($sample) {
        echo "Record mẫu (ID: {$sample['id']}):\n";
        foreach ($sample as $key => $value) {
            if (!is_numeric($key)) {
                echo "  - {$key}: " . ($value ?? 'NULL') . "\n";
            }
        }
    }
    
    echo "\n=== PHÂN TÍCH ===\n\n";
    
    // Kiểm tra các trường quan trọng
    $hasCustomStart = false;
    $hasCustomEnd = false;
    $hasPresetStart = false;
    $hasPresetEnd = false;
    
    foreach ($columns as $col) {
        if ($col['Field'] === 'custom_start') $hasCustomStart = true;
        if ($col['Field'] === 'custom_end') $hasCustomEnd = true;
        if ($col['Field'] === 'preset_start') $hasPresetStart = true;
        if ($col['Field'] === 'preset_end') $hasPresetEnd = true;
    }
    
    echo "Kiểm tra cột thời gian:\n";
    echo "  - custom_start: " . ($hasCustomStart ? "✓" : "✗") . "\n";
    echo "  - custom_end: " . ($hasCustomEnd ? "✓" : "✗") . "\n";
    echo "  - preset_start: " . ($hasPresetStart ? "✓" : "✗") . "\n";
    echo "  - preset_end: " . ($hasPresetEnd ? "✓" : "✗") . "\n\n";
    
    if ($sample) {
        $startTime = $sample['custom_start'] ?? $sample['preset_start'] ?? null;
        $endTime = $sample['custom_end'] ?? $sample['preset_end'] ?? null;
        
        if (!$startTime || !$endTime) {
            echo "⚠️ PHÁT HIỆN VẤN ĐỀ: Ca dạy không có thời gian bắt đầu/kết thúc!\n";
            echo "→ Có thể do:\n";
            echo "   1. Dữ liệu cũ từ phiên bản trước (chưa có custom_start/end)\n";
            echo "   2. Ca được đăng ký trước khi thêm chức năng này\n\n";
            echo "→ Giải pháp:\n";
            echo "   1. Xóa ca cũ và đăng ký lại\n";
            echo "   2. Hoặc update thủ công thêm thời gian\n\n";
        } else {
            echo "✓ Thời gian ca dạy OK: {$startTime} - {$endTime}\n\n";
        }
    }
    
} catch (PDOException $e) {
    echo "LỖI: " . $e->getMessage() . "\n";
    exit(1);
}
