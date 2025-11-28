<?php
require_once 'config/database.php';

echo "=== MIGRATE OLD REVENUE IMAGES ===\n\n";

try {
    $config = require 'config/database.php';
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}", 
        $config['username'], 
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, 2);
    
    echo "Connected to database: {$config['database']}\n\n";
    
    // Find records with old image but no new images array
    echo "Finding records to migrate...\n";
    $stmt = $pdo->query("
        SELECT id, student_name, confirmation_image, confirmation_images 
        FROM revenue_reports 
        WHERE confirmation_image IS NOT NULL 
        AND confirmation_image != ''
        AND (confirmation_images IS NULL OR confirmation_images = '' OR confirmation_images = '[]')
        ORDER BY id DESC
    ");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($records) . " records to migrate\n\n";
    
    if (count($records) === 0) {
        echo "✓ No records need migration!\n";
        exit(0);
    }
    
    // Migrate each record
    $updateStmt = $pdo->prepare("
        UPDATE revenue_reports 
        SET confirmation_images = ? 
        WHERE id = ?
    ");
    
    $migrated = 0;
    foreach ($records as $record) {
        $oldImage = $record['confirmation_image'];
        $jsonArray = json_encode([$oldImage]);
        
        $updateStmt->execute([$jsonArray, $record['id']]);
        
        echo "✓ ID {$record['id']}: {$record['student_name']}\n";
        echo "  Old: $oldImage\n";
        echo "  New: $jsonArray\n\n";
        
        $migrated++;
    }
    
    echo "========================================\n";
    echo "✓ Migration completed!\n";
    echo "✓ Migrated $migrated records\n";
    echo "\nBây giờ reload trang danh sách doanh thu để xem kết quả.\n";
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
