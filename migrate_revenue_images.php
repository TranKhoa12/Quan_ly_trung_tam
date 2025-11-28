<?php
require_once 'config/database.php';

echo "=== MIGRATE REVENUE IMAGES TO SUPPORT MULTIPLE FILES ===\n\n";

try {
    $config = require 'config/database.php';
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}", 
        $config['username'], 
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, 2); // PDO::ERRMODE_EXCEPTION
    
    echo "Connected to database: {$config['database']}\n\n";
    
    // Step 1: Add new column (check if exists first)
    echo "Step 1: Checking if confirmation_images column exists...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM revenue_reports LIKE 'confirmation_images'");
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        echo "Adding confirmation_images column...\n";
        $pdo->exec("ALTER TABLE revenue_reports 
                    ADD COLUMN confirmation_images TEXT AFTER confirmation_image");
        echo "✓ Column added\n\n";
    } else {
        echo "✓ Column already exists\n\n";
    }
    
    // Step 2: Migrate existing data
    echo "Step 2: Migrating existing single image data...\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM revenue_reports WHERE confirmation_image IS NOT NULL AND confirmation_image != ''");
    $count = $stmt->fetchColumn();
    echo "Found $count records with images\n";
    
    $pdo->exec("UPDATE revenue_reports 
                SET confirmation_images = CONCAT('[\"', confirmation_image, '\"]')
                WHERE confirmation_image IS NOT NULL AND confirmation_image != ''");
    echo "✓ Migrated $count records\n\n";
    
    // Step 3: Set empty arrays for null images
    echo "Step 3: Setting empty arrays for records without images...\n";
    $pdo->exec("UPDATE revenue_reports 
                SET confirmation_images = '[]'
                WHERE confirmation_images IS NULL OR confirmation_images = ''");
    echo "✓ Done\n\n";
    
    // Verification
    echo "=== VERIFICATION ===\n";
    $stmt = $pdo->query("SELECT id, confirmation_image, confirmation_images FROM revenue_reports LIMIT 5");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        echo "ID: {$row['id']}\n";
        echo "  Old: {$row['confirmation_image']}\n";
        echo "  New: {$row['confirmation_images']}\n";
        echo "---\n";
    }
    
    echo "\n✓ Migration completed successfully!\n";
    echo "\nNOTE: Old 'confirmation_image' column is kept for backward compatibility.\n";
    echo "You can manually drop it later if needed.\n";
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
