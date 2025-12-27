<?php
/**
 * Test Admin Logs - Kiểm tra hệ thống logs
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 KIỂM TRA HỆ THỐNG ADMIN LOGS</h2>";
echo "<hr>";

// 1. Kiểm tra Database Connection
echo "<h3>1. Kiểm tra kết nối Database</h3>";
try {
    require_once __DIR__ . '/../config/database.php';
    echo "✅ Kết nối database thành công<br>";
} catch (Exception $e) {
    die("❌ Lỗi kết nối database: " . $e->getMessage());
}

// 2. Kiểm tra bảng admin_logs
echo "<h3>2. Kiểm tra bảng admin_logs</h3>";
$result = $conn->query("SHOW TABLES LIKE 'admin_logs'");
if ($result->num_rows > 0) {
    echo "✅ Bảng admin_logs đã tồn tại<br>";
    
    // Kiểm tra cấu trúc
    $columns = $conn->query("DESCRIBE admin_logs");
    echo "<details><summary>Xem cấu trúc bảng</summary>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($col = $columns->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "</tr>";
    }
    echo "</table></details>";
} else {
    echo "❌ Bảng admin_logs chưa tồn tại<br>";
    echo "👉 <strong>Hãy chạy file: database/admin_logs.sql</strong><br>";
    echo "👉 Hoặc chạy lệnh: <code>mysql -u root -p your_database < database/admin_logs.sql</code><br>";
}

// 3. Kiểm tra AdminLogger class
echo "<h3>3. Kiểm tra AdminLogger class</h3>";
$loggerPath = __DIR__ . '/../app/helpers/AdminLogger.php';
if (file_exists($loggerPath)) {
    echo "✅ File AdminLogger.php tồn tại<br>";
    require_once $loggerPath;
    if (class_exists('App\Helpers\AdminLogger')) {
        echo "✅ Class AdminLogger có thể load được<br>";
    } else {
        echo "❌ Class AdminLogger không load được<br>";
    }
} else {
    echo "❌ File AdminLogger.php không tồn tại<br>";
}

// 4. Kiểm tra AdminLogController
echo "<h3>4. Kiểm tra AdminLogController</h3>";
$controllerPath = __DIR__ . '/../app/controllers/AdminLogController.php';
if (file_exists($controllerPath)) {
    echo "✅ File AdminLogController.php tồn tại<br>";
} else {
    echo "❌ File AdminLogController.php không tồn tại<br>";
}

// 5. Kiểm tra Views
echo "<h3>5. Kiểm tra Views</h3>";
$indexView = __DIR__ . '/../app/views/admin-logs/index.php';
$detailView = __DIR__ . '/../app/views/admin-logs/detail.php';

if (file_exists($indexView)) {
    echo "✅ View index.php tồn tại<br>";
} else {
    echo "❌ View index.php không tồn tại<br>";
}

if (file_exists($detailView)) {
    echo "✅ View detail.php tồn tại<br>";
} else {
    echo "❌ View detail.php không tồn tại<br>";
}

// 6. Test ghi log (nếu bảng tồn tại)
if ($result->num_rows > 0) {
    echo "<h3>6. Test ghi log thử nghiệm</h3>";
    try {
        use App\Helpers\AdminLogger;
        
        // Giả lập session
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $testUserId = 1;
        $testUsername = 'test_user';
        
        $logger = new AdminLogger($conn, $testUserId, $testUsername);
        $result = $logger->log(
            'other',
            'system_test',
            'Test hệ thống admin logs',
            ['test' => true, 'timestamp' => date('Y-m-d H:i:s')],
            null,
            null
        );
        
        if ($result) {
            echo "✅ Ghi log test thành công<br>";
            
            // Đọc log vừa ghi
            $lastLog = $conn->query("SELECT * FROM admin_logs ORDER BY id DESC LIMIT 1")->fetch_assoc();
            echo "<details><summary>Xem log vừa ghi</summary>";
            echo "<pre>" . print_r($lastLog, true) . "</pre>";
            echo "</details>";
        } else {
            echo "❌ Ghi log test thất bại<br>";
        }
    } catch (Exception $e) {
        echo "❌ Lỗi test: " . $e->getMessage() . "<br>";
    }
}

// 7. Kiểm tra Routes
echo "<h3>7. Kiểm tra Routes</h3>";
$indexPath = __DIR__ . '/index.php';
$content = file_get_contents($indexPath);
if (strpos($content, '/admin-logs') !== false) {
    echo "✅ Routes đã được thêm vào index.php<br>";
} else {
    echo "❌ Routes chưa được thêm vào index.php<br>";
}

// 8. Tổng kết
echo "<hr>";
echo "<h3>📊 Tổng kết</h3>";
echo "<p><strong>Hệ thống Admin Logs:</strong> ";
if ($result->num_rows > 0 && file_exists($loggerPath) && file_exists($controllerPath)) {
    echo "<span style='color: green; font-weight: bold;'>✅ SẴN SÀNG SỬ DỤNG</span></p>";
    echo "<p>👉 Truy cập: <a href='/Quan_ly_trung_tam/public/admin-logs' target='_blank'>/Quan_ly_trung_tam/public/admin-logs</a></p>";
} else {
    echo "<span style='color: red; font-weight: bold;'>❌ CHƯA SẴN SÀNG</span></p>";
    echo "<p>👉 Hãy thực hiện các bước bị lỗi ở trên</p>";
}
