<?php
require_once 'config/database.php';
require_once 'core/Database.php';
require_once 'core/BaseModel.php';
require_once 'app/models/RevenueReport.php';

echo "=== TEST REVENUE MULTIPLE IMAGES ===\n\n";

try {
    $revenue = new RevenueReport();
    
    // Get latest 3 records
    $records = $revenue->getRevenueWithDetails([], 'payment_date DESC', 3);
    
    echo "Latest 3 revenue records:\n";
    echo "========================\n\n";
    
    foreach ($records as $record) {
        echo "ID: {$record['id']}\n";
        echo "Student: {$record['student_name']}\n";
        echo "Amount: " . number_format($record['amount'], 0, ',', '.') . " VNĐ\n";
        echo "Old single image: " . ($record['confirmation_image'] ?? 'null') . "\n";
        echo "New images array: " . ($record['confirmation_images'] ?? 'null') . "\n";
        
        if (!empty($record['confirmation_images'])) {
            $images = json_decode($record['confirmation_images'], true);
            if (is_array($images)) {
                echo "  → Decoded: " . count($images) . " image(s)\n";
                foreach ($images as $idx => $img) {
                    echo "    [" . ($idx + 1) . "] $img\n";
                }
            }
        }
        
        echo "---\n\n";
    }
    
    echo "✓ Test completed!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
