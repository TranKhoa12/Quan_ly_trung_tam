<?php
// Test script để kiểm tra transfer form

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/core/Database.php';

// Define constants
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('CORE_PATH', BASE_PATH . '/core');

session_start();

// Mock user session
$_SESSION['user'] = [
    'id' => 14,
    'full_name' => 'Hồ Linh Cảnh',
    'email' => 'canh@gmail.com',
    'role' => 'staff'
];

require_once APP_PATH . '/models/ShiftRegistration.php';
require_once APP_PATH . '/models/ShiftTransfer.php';
require_once APP_PATH . '/models/User.php';

try {
    $db = Database::getInstance();
    
    echo "=== TEST TRANSFER FORM ===\n\n";
    
    // Test query từ controller
    $registrationId = 551;
    $sql = "SELECT sr.*, 
                   COALESCE(sr.custom_start, ts.start_time) as custom_start,
                   COALESCE(sr.custom_end, ts.end_time) as custom_end,
                   ts.name as shift_name
            FROM shift_registrations sr
            LEFT JOIN teaching_shifts ts ON sr.shift_id = ts.id
            WHERE sr.id = :id";
    
    echo "1. Testing SQL query...\n";
    $registration = $db->query($sql, ['id' => $registrationId])->fetch();
    
    if (!$registration) {
        echo "   ✗ Không tìm thấy ca dạy ID: $registrationId\n\n";
        exit(1);
    }
    
    echo "   ✓ Tìm thấy ca dạy!\n";
    echo "   - ID: {$registration['id']}\n";
    echo "   - Ngày: {$registration['shift_date']}\n";
    echo "   - Giờ: {$registration['custom_start']} - {$registration['custom_end']}\n";
    echo "   - Ca: " . ($registration['shift_name'] ?? 'N/A') . "\n";
    echo "   - Staff ID: {$registration['staff_id']}\n";
    echo "   - Status: {$registration['status']}\n\n";
    
    // Check if approved
    if ($registration['status'] !== 'approved') {
        echo "   ⚠️ Ca chưa được duyệt (status: {$registration['status']})\n";
        echo "   → Chỉ có thể chuyển ca đã duyệt!\n\n";
    }
    
    // Test get staff list
    echo "2. Testing get staff list...\n";
    $userModel = new User();
    $allStaff = $userModel->getStaffList();
    echo "   ✓ Tìm thấy " . count($allStaff) . " nhân viên\n\n";
    
    // Test has pending transfer
    echo "3. Testing has pending transfer...\n";
    $transferModel = new ShiftTransfer();
    $hasPending = $transferModel->hasPendingTransfer($registrationId);
    echo "   " . ($hasPending ? "⚠️ Ca đã có yêu cầu pending" : "✓ Ca chưa có yêu cầu pending") . "\n\n";
    
    echo "=== KẾT QUẢ ===\n";
    echo "✅ Tất cả test PASS! View nên load được.\n\n";
    
    echo "Dữ liệu sẽ truyền vào view:\n";
    echo "- registration: " . json_encode($registration, JSON_UNESCAPED_UNICODE) . "\n";
    echo "- otherStaff: " . count($allStaff) . " nhân viên\n\n";
    
} catch (Exception $e) {
    echo "❌ LỖI: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
