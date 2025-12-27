<?php
/**
 * Script cài đặt tự động bảng admin_logs
 * Truy cập: http://localhost:81/Quan_ly_trung_tam/public/install_admin_logs.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='vi'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Cài đặt Admin Logs</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        .box { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>";

echo "<h1>🚀 Cài đặt hệ thống Admin Logs</h1>";
echo "<hr>";

// Kết nối database
try {
    $host = 'localhost';
    $database = 'quan_ly_trung_tam';
    $username = 'root';
    $password = '';
    
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
    echo "<div class='box success'>✅ Kết nối database thành công: <strong>$database</strong></div>";
    
} catch (Exception $e) {
    echo "<div class='box error'>❌ Lỗi kết nối database: " . $e->getMessage() . "</div>";
    echo "<p>Hãy kiểm tra:</p>";
    echo "<ul>";
    echo "<li>MySQL đã chạy chưa?</li>";
    echo "<li>Database 'quan_ly_trung_tam' đã tồn tại chưa?</li>";
    echo "<li>Username/password có đúng không?</li>";
    echo "</ul>";
    die();
}

// Kiểm tra bảng có tồn tại không
$tableExists = false;
$result = $conn->query("SHOW TABLES LIKE 'admin_logs'");
if ($result->num_rows > 0) {
    $tableExists = true;
    echo "<div class='box info'>ℹ️ Bảng 'admin_logs' đã tồn tại</div>";
}

// Tạo bảng nếu chưa có
if (!$tableExists) {
    echo "<h2>📋 Tạo bảng admin_logs...</h2>";
    
    $sql = "CREATE TABLE IF NOT EXISTS admin_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL COMMENT 'ID của user thực hiện hành động',
        username VARCHAR(100) COMMENT 'Tên đăng nhập của user',
        action_type ENUM('login', 'logout', 'create', 'update', 'delete', 'view', 'export', 'other') NOT NULL COMMENT 'Loại hành động',
        module VARCHAR(100) NOT NULL COMMENT 'Module bị tác động: students, teachers, courses, etc.',
        description TEXT COMMENT 'Mô tả chi tiết về hành động',
        ip_address VARCHAR(45) COMMENT 'IP address của user (hỗ trợ IPv6)',
        user_agent TEXT COMMENT 'Browser và thiết bị của user',
        request_data JSON COMMENT 'Dữ liệu request (tham số, form data)',
        old_data JSON COMMENT 'Dữ liệu cũ trước khi thay đổi (cho update/delete)',
        new_data JSON COMMENT 'Dữ liệu mới sau khi thay đổi (cho create/update)',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_action_type (action_type),
        INDEX idx_module (module),
        INDEX idx_created_at (created_at),
        INDEX idx_user_action (user_id, action_type),
        INDEX idx_user_created (user_id, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ghi log các hoạt động của nhân viên trong hệ thống'";
    
    try {
        if ($conn->query($sql)) {
            echo "<div class='box success'>✅ Tạo bảng admin_logs thành công!</div>";
            $tableExists = true;
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        echo "<div class='box error'>❌ Lỗi tạo bảng: " . $e->getMessage() . "</div>";
        die();
    }
}

// Hiển thị cấu trúc bảng
if ($tableExists) {
    echo "<h2>📊 Cấu trúc bảng admin_logs</h2>";
    $columns = $conn->query("DESCRIBE admin_logs");
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<thead style='background: #007bff; color: white;'>";
    echo "<tr><th style='padding: 10px;'>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    echo "</thead><tbody>";
    
    while ($col = $columns->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='padding: 8px;'><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>" . ($col['Key'] ?: '-') . "</td>";
        echo "<td>" . ($col['Default'] ?: '-') . "</td>";
        echo "<td>" . ($col['Extra'] ?: '-') . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    
    // Đếm số lượng logs hiện có
    $count = $conn->query("SELECT COUNT(*) as total FROM admin_logs")->fetch_assoc();
    echo "<div class='box info'>📈 Hiện có <strong>{$count['total']}</strong> logs trong hệ thống</div>";
}

// Test ghi log
echo "<h2>🧪 Test ghi log</h2>";
try {
    require_once __DIR__ . '/../app/helpers/AdminLogger.php';
    use App\Helpers\AdminLogger;
    
    $testUserId = 999;
    $testUsername = 'system_test';
    
    $logger = new AdminLogger($conn, $testUserId, $testUsername);
    $result = $logger->log(
        'other',
        'system',
        'Test cài đặt hệ thống admin logs',
        [
            'test_time' => date('Y-m-d H:i:s'),
            'test_from' => 'install_admin_logs.php'
        ],
        null,
        null
    );
    
    if ($result) {
        echo "<div class='box success'>✅ Test ghi log thành công!</div>";
        
        // Lấy log vừa ghi
        $lastLog = $conn->query("SELECT * FROM admin_logs ORDER BY id DESC LIMIT 1")->fetch_assoc();
        echo "<div class='box'>";
        echo "<strong>Log vừa tạo:</strong>";
        echo "<pre>" . json_encode($lastLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        echo "</div>";
    } else {
        echo "<div class='box error'>❌ Test ghi log thất bại</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='box error'>❌ Lỗi test: " . $e->getMessage() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Kiểm tra các files
echo "<h2>📁 Kiểm tra files</h2>";
$files = [
    'AdminLogger.php' => __DIR__ . '/../app/helpers/AdminLogger.php',
    'AdminLogController.php' => __DIR__ . '/../app/controllers/AdminLogController.php',
    'index.php (view)' => __DIR__ . '/../app/views/admin-logs/index.php',
    'detail.php (view)' => __DIR__ . '/../app/views/admin-logs/detail.php',
];

echo "<ul>";
foreach ($files as $name => $path) {
    if (file_exists($path)) {
        echo "<li class='success'>✅ $name</li>";
    } else {
        echo "<li class='error'>❌ $name - Không tìm thấy</li>";
    }
}
echo "</ul>";

// Kiểm tra routes
echo "<h2>🛣️ Kiểm tra Routes</h2>";
$indexContent = file_get_contents(__DIR__ . '/index.php');
if (strpos($indexContent, "admin-logs") !== false) {
    echo "<div class='box success'>✅ Routes đã được cấu hình</div>";
} else {
    echo "<div class='box error'>❌ Routes chưa được cấu hình trong index.php</div>";
}

// Tổng kết
echo "<hr>";
echo "<h2>🎉 Tổng kết</h2>";

if ($tableExists) {
    echo "<div class='box success'>";
    echo "<h3>✅ Cài đặt hoàn tất!</h3>";
    echo "<p>Hệ thống Admin Logs đã sẵn sàng sử dụng.</p>";
    echo "</div>";
    
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='/Quan_ly_trung_tam/public/admin-logs' class='btn'>🚀 Truy cập Admin Logs</a>";
    echo "<a href='/Quan_ly_trung_tam/public/dashboard' class='btn' style='background: #28a745;'>📊 Về Dashboard</a>";
    echo "</div>";
    
    echo "<div class='box info'>";
    echo "<h4>📚 Hướng dẫn sử dụng:</h4>";
    echo "<ol>";
    echo "<li>Đăng nhập với tài khoản admin</li>";
    echo "<li>Vào menu 'Nhật ký hoạt động' ở sidebar</li>";
    echo "<li>Xem, lọc, và xuất logs</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div class='box error'>";
    echo "<h3>❌ Cài đặt chưa hoàn tất</h3>";
    echo "<p>Vui lòng kiểm tra lại các lỗi ở trên.</p>";
    echo "</div>";
}

$conn->close();

echo "</body></html>";
