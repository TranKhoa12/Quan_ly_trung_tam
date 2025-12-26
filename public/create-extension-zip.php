<?php
/**
 * Script tạo file ZIP cho extension
 * Chạy file này để đóng gói extension thành file ZIP để download
 */

$extensionDir = __DIR__ . '/../certificate-auto-fill/';
$zipFile = __DIR__ . '/downloads/certificate-auto-fill.zip';

// Tạo thư mục downloads nếu chưa có
if (!file_exists(dirname($zipFile))) {
    mkdir(dirname($zipFile), 0755, true);
    echo "✅ Đã tạo thư mục downloads\n";
}

// Xóa file ZIP cũ nếu có
if (file_exists($zipFile)) {
    unlink($zipFile);
    echo "🗑️ Đã xóa file ZIP cũ\n";
}

// Kiểm tra extension directory
if (!file_exists($extensionDir)) {
    die("❌ Lỗi: Không tìm thấy thư mục extension tại: $extensionDir\n");
}

$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("❌ Lỗi: Không thể tạo file ZIP\n");
}

echo "📦 Đang đóng gói extension...\n";

// Thêm tất cả file vào ZIP
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($extensionDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$fileCount = 0;
foreach ($files as $file) {
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = 'certificate-auto-fill/' . substr($filePath, strlen($extensionDir));
        
        // Bỏ qua các file không cần thiết
        if (strpos($relativePath, '.git') === false && 
            strpos($relativePath, 'node_modules') === false &&
            strpos($relativePath, '.DS_Store') === false) {
            
            $zip->addFile($filePath, $relativePath);
            $fileCount++;
            echo "  + $relativePath\n";
        }
    }
}

$zip->close();

$zipSize = filesize($zipFile);
$zipSizeKB = round($zipSize / 1024, 2);

echo "\n✅ Hoàn tất!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📊 Thống kê:\n";
echo "  • Số file: $fileCount\n";
echo "  • Kích thước: $zipSizeKB KB\n";
echo "  • Đường dẫn: $zipFile\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🌐 URL download: /downloads/certificate-auto-fill.zip\n";
echo "\n";
?>
