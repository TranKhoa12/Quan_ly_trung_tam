<?php
// Test case: Từ 2/1/2026 đến 9/1/2026
$dateFrom = '2026-01-02';
$dateTo = '2026-01-09';

echo "<h3>Kiểm tra các ngày từ $dateFrom đến $dateTo:</h3>";
echo "<pre>";

$current = strtotime($dateFrom);
$end = strtotime($dateTo);
$count = 0;

while ($current <= $end) {
    $dayOfWeek = date('w', $current); // 0=Sun, 1=Mon, ..., 6=Sat
    $dayName = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'][$dayOfWeek];
    $date = date('d/m/Y', $current);
    
    echo "$date - $dayName (dayOfWeek=$dayOfWeek)";
    
    // Loại trừ Chủ nhật
    if ($dayOfWeek != 0) {
        echo " ✓ Tính";
        $count++;
    } else {
        echo " ✗ Bỏ qua (Chủ nhật)";
    }
    echo "\n";
    
    $current = strtotime('+1 day', $current);
}

echo "\nTổng số ngày (không tính CN): $count ngày\n";
echo "</pre>";

echo "<h3>Giải pháp:</h3>";
echo "<ul>";
echo "<li>Nếu không tick checkbox nào → Mặc định chọn T2-T7 (1-6)</li>";
echo "<li>Hoặc bắt buộc phải tick ít nhất 1 checkbox</li>";
echo "<li>Trong controller, luôn kiểm tra và loại trừ ngày 0 (Chủ nhật)</li>";
echo "</ul>";
