<?php
// Simulate controller logic
$dateFrom = '2026-01-02';
$dateTo = '2026-01-09';

echo "<h3>TEST 1: Chọn tất cả T2-T6 (1,2,3,4,5)</h3><pre>";
$weekdays = [1, 2, 3, 4, 5];
$dates = [];
$current = strtotime($dateFrom);
$end = strtotime($dateTo);

while ($current <= $end) {
    $dayOfWeek = date('w', $current);
    if ($dayOfWeek != 0 && in_array($dayOfWeek, $weekdays)) {
        $date = date('Y-m-d', $current);
        $dates[] = $date;
        echo date('d/m/Y', $current) . " - " . ['CN','T2','T3','T4','T5','T6','T7'][$dayOfWeek] . "\n";
    }
    $current = strtotime('+1 day', $current);
}
echo "Tổng: " . count($dates) . " ngày\n</pre>";

echo "<h3>TEST 2: Không chọn gì (mặc định T2-T7)</h3><pre>";
$weekdays = []; // Empty
if (empty($weekdays)) {
    $weekdays = [1, 2, 3, 4, 5, 6]; // Default T2-T7
}
$dates = [];
$current = strtotime($dateFrom);

while ($current <= $end) {
    $dayOfWeek = date('w', $current);
    if ($dayOfWeek != 0 && in_array($dayOfWeek, $weekdays)) {
        $date = date('Y-m-d', $current);
        $dates[] = $date;
        echo date('d/m/Y', $current) . " - " . ['CN','T2','T3','T4','T5','T6','T7'][$dayOfWeek] . "\n";
    }
    $current = strtotime('+1 day', $current);
}
echo "Tổng: " . count($dates) . " ngày\n</pre>";

echo "<h3>TEST 3: Chọn cả Chủ nhật + T2 (sẽ loại CN)</h3><pre>";
$weekdays = [0, 1]; // CN + T2
$weekdays = array_filter($weekdays, function($day) {
    return $day != 0;
});
$dates = [];
$current = strtotime($dateFrom);

while ($current <= $end) {
    $dayOfWeek = date('w', $current);
    if ($dayOfWeek != 0 && in_array($dayOfWeek, $weekdays)) {
        $date = date('Y-m-d', $current);
        $dates[] = $date;
        echo date('d/m/Y', $current) . " - " . ['CN','T2','T3','T4','T5','T6','T7'][$dayOfWeek] . "\n";
    }
    $current = strtotime('+1 day', $current);
}
echo "Tổng: " . count($dates) . " ngày (chỉ T2, CN đã bị loại)\n</pre>";
