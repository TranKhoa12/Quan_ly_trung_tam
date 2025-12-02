<?php
echo "<h3>Test date range logic:</h3><pre>";

echo "Hôm nay: " . date('Y-m-d') . " (" . date('d/m/Y') . ")\n\n";

echo "=== LOGIC CŨ (SAI) ===\n";
$fromDateOld = date('Y-m-01', strtotime('-15 days'));
$toDateOld = date('Y-m-t', strtotime('+30 days'));
echo "fromDate: $fromDateOld\n";
echo "toDate: $toDateOld\n\n";

echo "=== LOGIC MỚI (ĐÚNG) ===\n";
$fromDateNew = date('Y-m-01', strtotime('first day of -1 month'));
$toDateNew = date('Y-m-t', strtotime('last day of +1 month'));
echo "fromDate: $fromDateNew (Đầu tháng trước)\n";
echo "toDate: $toDateNew (Cuối tháng sau)\n\n";

echo "Kết quả: Lấy data từ $fromDateNew đến $toDateNew\n";
echo "Bao gồm:\n";
echo "  - Tháng 11/2025\n";
echo "  - Tháng 12/2025 (hiện tại)\n";
echo "  - Tháng 1/2026\n";
echo "</pre>";
