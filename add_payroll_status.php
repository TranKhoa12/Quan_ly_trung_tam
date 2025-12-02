<?php
// Migration: Add status column to shift_payrolls table

$host = 'localhost';
$dbname = 'quan_ly_trung_tam';
$username = 'root';
$password = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Adding status column to shift_payrolls table...\n";
    
    // Add status column
    $sql = "ALTER TABLE shift_payrolls 
            ADD COLUMN status ENUM('active', 'cancelled') NOT NULL DEFAULT 'active' AFTER generated_by";
    $db->exec($sql);
    echo "✓ Added status column\n";
    
    // Add indexes
    $sql = "CREATE INDEX idx_payroll_status ON shift_payrolls(status)";
    $db->exec($sql);
    echo "✓ Added idx_payroll_status index\n";
    
    $sql = "CREATE INDEX idx_payroll_period_status ON shift_payrolls(period_start, period_end, status)";
    $db->exec($sql);
    echo "✓ Added idx_payroll_period_status index\n";
    
    echo "\n✓ Migration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    
    // Check if column already exists
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Note: Column 'status' already exists. Skipping...\n";
    }
}
