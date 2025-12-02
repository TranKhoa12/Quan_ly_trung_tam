<?php
$pdo = new PDO('mysql:host=localhost;dbname=quan_ly_trung_tam', 'root', '');

echo "<h3>Test query như trong getForStaff (lấy 5 bản ghi mới nhất của staff_id = 14):</h3><pre>";
$sql = "SELECT sr.*, ts.name AS shift_name, ts.start_time AS preset_start, ts.end_time AS preset_end,
               ts.hourly_rate AS shift_hourly_rate, approver.full_name AS approver_name
        FROM shift_registrations sr
        LEFT JOIN teaching_shifts ts ON sr.shift_id = ts.id
        LEFT JOIN users approver ON sr.approved_by = approver.id
        WHERE sr.staff_id = 14
        ORDER BY sr.shift_date DESC, COALESCE(sr.custom_start, ts.start_time)
        LIMIT 5";

$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $row) {
    echo "ID: {$row['id']}\n";
    echo "Date: {$row['shift_date']}\n";
    echo "Shift: {$row['shift_name']}\n";
    echo "preset_start: {$row['preset_start']}\n";
    echo "preset_end: {$row['preset_end']}\n";
    echo "custom_start: {$row['custom_start']}\n";
    echo "custom_end: {$row['custom_end']}\n";
    echo "Status: {$row['status']}\n";
    echo "---\n";
}
echo "</pre>";
