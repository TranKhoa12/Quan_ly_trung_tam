<?php
// Define constants
define('CONFIG_PATH', __DIR__ . '/config/');
define('BASE_PATH', '/Quan_ly_trung_tam/public');

require_once 'core/Database.php';

try {
    $db = Database::getInstance();
    $sql = file_get_contents('database/update_payment_method_column.sql');
    $db->query($sql);
    echo "✅ Migration completed successfully!\n";
    echo "✅ payment_method column updated to VARCHAR(50)\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
