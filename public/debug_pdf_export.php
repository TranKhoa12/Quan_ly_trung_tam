<?php
/**
 * Debug script cho chức năng xuất PDF trên hosting
 * Upload file này lên hosting và truy cập để kiểm tra
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 KIỂM TRA CẤU HÌNH PDF EXPORT</h2>";

// 1. Kiểm tra PHP version
echo "<h3>1. PHP Version:</h3>";
echo "Version: " . PHP_VERSION . "<br>";
if (version_compare(PHP_VERSION, '7.4.0') >= 0) {
    echo "✅ PHP version đủ yêu cầu (>= 7.4)<br>";
} else {
    echo "❌ PHP version quá thấp (cần >= 7.4)<br>";
}

// 2. Kiểm tra memory limit
echo "<h3>2. Memory & Timeout:</h3>";
echo "Memory limit: " . ini_get('memory_limit') . "<br>";
echo "Max execution time: " . ini_get('max_execution_time') . "s<br>";

// 3. Kiểm tra allow_url_fopen
echo "<h3>3. Network Access:</h3>";
echo "allow_url_fopen: " . (ini_get('allow_url_fopen') ? '✅ Enabled' : '❌ Disabled') . "<br>";
echo "cURL available: " . (function_exists('curl_init') ? '✅ Yes' : '❌ No') . "<br>";

// 4. Kiểm tra vendor/autoload
echo "<h3>4. Composer Packages:</h3>";
$vendorPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendorPath)) {
    echo "✅ Vendor autoload exists<br>";
    require_once $vendorPath;
    
    // Kiểm tra FPDI
    if (class_exists('\setasign\Fpdi\Tcpdf\Fpdi')) {
        echo "✅ FPDI/TCPDF library loaded<br>";
    } else {
        echo "❌ FPDI/TCPDF library NOT loaded<br>";
        echo "👉 Cần chạy: composer require setasign/fpdi-tcpdf<br>";
    }
} else {
    echo "❌ Vendor autoload NOT found: $vendorPath<br>";
    echo "👉 Cần chạy: composer install<br>";
}

// 5. Kiểm tra GD library (cho xử lý ảnh)
echo "<h3>5. Image Libraries:</h3>";
if (extension_loaded('gd')) {
    echo "✅ GD library available<br>";
} else {
    echo "❌ GD library NOT available<br>";
}

// 6. Test download từ Cloudinary
echo "<h3>6. Test Download from Cloudinary:</h3>";
$testUrl = "https://res.cloudinary.com/demo/image/upload/sample.jpg";

if (function_exists('curl_init')) {
    echo "Testing with cURL...<br>";
    $ch = curl_init($testUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($content !== false && $httpCode === 200) {
        echo "✅ cURL download successful (HTTP $httpCode)<br>";
        echo "Downloaded " . strlen($content) . " bytes<br>";
    } else {
        echo "❌ cURL download failed (HTTP $httpCode)<br>";
        if ($error) {
            echo "Error: $error<br>";
        }
    }
} elseif (ini_get('allow_url_fopen')) {
    echo "Testing with file_get_contents...<br>";
    $content = @file_get_contents($testUrl);
    if ($content !== false) {
        echo "✅ file_get_contents successful<br>";
        echo "Downloaded " . strlen($content) . " bytes<br>";
    } else {
        echo "❌ file_get_contents failed<br>";
    }
} else {
    echo "❌ Không có cách nào để download file từ URL<br>";
    echo "👉 Cần bật allow_url_fopen hoặc cài cURL extension<br>";
}

// 7. Kiểm tra temp directory
echo "<h3>7. Temp Directory:</h3>";
$tempDir = sys_get_temp_dir();
echo "Temp dir: $tempDir<br>";
if (is_writable($tempDir)) {
    echo "✅ Temp directory is writable<br>";
    
    // Test tạo file temp
    $testFile = $tempDir . '/test_' . uniqid() . '.txt';
    if (file_put_contents($testFile, 'test') !== false) {
        echo "✅ Can create temp files<br>";
        @unlink($testFile);
    } else {
        echo "❌ Cannot create temp files<br>";
    }
} else {
    echo "❌ Temp directory NOT writable<br>";
}

// 8. Kiểm tra error log
echo "<h3>8. Error Logging:</h3>";
$errorLog = ini_get('error_log');
echo "Error log: " . ($errorLog ?: 'default') . "<br>";
echo "Log errors: " . (ini_get('log_errors') ? 'Yes' : 'No') . "<br>";

echo "<hr>";
echo "<h3>📋 CHECKLIST:</h3>";
echo "<ul>";
echo "<li>PHP >= 7.4: " . (version_compare(PHP_VERSION, '7.4.0') >= 0 ? '✅' : '❌') . "</li>";
echo "<li>Composer installed: " . (file_exists($vendorPath) ? '✅' : '❌') . "</li>";
echo "<li>FPDI library: " . (class_exists('\setasign\Fpdi\Tcpdf\Fpdi') ? '✅' : '❌') . "</li>";
echo "<li>Network access (cURL or allow_url_fopen): " . (function_exists('curl_init') || ini_get('allow_url_fopen') ? '✅' : '❌') . "</li>";
echo "<li>Temp dir writable: " . (is_writable($tempDir) ? '✅' : '❌') . "</li>";
echo "</ul>";

echo "<h3>🔧 Nếu có lỗi:</h3>";
echo "<ol>";
echo "<li>Nếu thiếu vendor: chạy <code>composer install</code> trên hosting</li>";
echo "<li>Nếu thiếu FPDI: chạy <code>composer require setasign/fpdi-tcpdf</code></li>";
echo "<li>Nếu không download được: liên hệ hosting bật cURL hoặc allow_url_fopen</li>";
echo "<li>Kiểm tra error log của hosting để xem lỗi cụ thể</li>";
echo "</ol>";
