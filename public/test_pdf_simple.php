<?php
/**
 * Test đơn giản chức năng tạo PDF
 * Upload file này lên hosting và truy cập để test
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
@ini_set('memory_limit', '256M');
@ini_set('max_execution_time', '300');

echo "<h2>🧪 TEST TẠO PDF ĐƠN GIẢN</h2>";

$vendorPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($vendorPath)) {
    die("❌ Vendor autoload không tồn tại. Chạy: composer install");
}

require_once $vendorPath;

try {
    echo "Loading FPDI library...<br>";
    
    if (!class_exists('\setasign\Fpdi\Tcpdf\Fpdi')) {
        die("❌ Class FPDI không tồn tại. Chạy: composer require setasign/fpdi-tcpdf");
    }
    
    echo "✅ FPDI loaded<br>";
    echo "Creating PDF...<br>";
    
    $pdf = new \setasign\Fpdi\Tcpdf\Fpdi('P', 'mm', 'A4', true, 'UTF-8');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->SetFont('dejavusans', '', 12);
    
    $pdf->AddPage();
    $pdf->Cell(0, 10, 'Test PDF Export - Phiếu hoàn thành học viên', 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->Cell(0, 10, 'Thời gian: ' . date('d/m/Y H:i:s'), 0, 1);
    $pdf->Ln(5);
    $pdf->MultiCell(0, 10, 'Nếu bạn thấy file PDF này được tạo ra thành công, nghĩa là thư viện FPDI hoạt động tốt.', 0, 'L');
    
    echo "✅ PDF created<br>";
    echo "Outputting PDF...<br>";
    
    if (ob_get_length()) {
        ob_end_clean();
    }
    
    $pdf->Output('test-completion-slip.pdf', 'D');
    exit;
    
} catch (Exception $e) {
    echo "❌ LỖI: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
