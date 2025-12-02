<?php
$pdo = new PDO('mysql:host=localhost;dbname=quan_ly_trung_tam', 'root', '');

try {
    // Add hourly_rate column to shift_registrations table
    $sql = "ALTER TABLE shift_registrations 
            ADD COLUMN hourly_rate DECIMAL(10,2) DEFAULT 50000.00 AFTER hours";
    
    $pdo->exec($sql);
    
    echo "✅ Đã thêm cột hourly_rate vào bảng shift_registrations\n";
    
    // Update existing records to use the shift's hourly_rate
    $updateSql = "UPDATE shift_registrations sr
                  JOIN teaching_shifts ts ON sr.shift_id = ts.id
                  SET sr.hourly_rate = ts.hourly_rate
                  WHERE sr.shift_id IS NOT NULL";
    
    $affected = $pdo->exec($updateSql);
    echo "✅ Đã cập nhật hourly_rate cho $affected bản ghi hiện có\n";
    
    echo "\n✅ Migration hoàn tất!\n";
    
} catch (PDOException $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}
