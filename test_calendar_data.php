<?php
$pdo = new PDO('mysql:host=localhost;dbname=quan_ly_trung_tam', 'root', '');

$sql = "SELECT sr.*, ts.name AS shift_name, ts.start_time AS preset_start, ts.end_time AS preset_end,
               ts.hourly_rate, approver.full_name AS approver_name
        FROM shift_registrations sr
        LEFT JOIN teaching_shifts ts ON sr.shift_id = ts.id
        LEFT JOIN users approver ON sr.approved_by = approver.id
        WHERE sr.staff_id = 14
          AND sr.shift_date >= '2026-01-01'
          AND sr.shift_date <= '2026-01-31'
        ORDER BY sr.shift_date DESC, COALESCE(sr.custom_start, ts.start_time)";

$stmt = $pdo->query($sql);
$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Registrations data được truyền vào calendar:</h3>";
echo "<pre>";
echo "Total: " . count($registrations) . " records\n\n";
foreach (array_slice($registrations, 0, 3) as $reg) {
    echo "shift_date: '{$reg['shift_date']}'\n";
    echo "shift_name: '{$reg['shift_name']}'\n";
    echo "preset_start: '{$reg['preset_start']}'\n";
    echo "preset_end: '{$reg['preset_end']}'\n";
    echo "custom_start: '{$reg['custom_start']}'\n";
    echo "custom_end: '{$reg['custom_end']}'\n";
    echo "hours: '{$reg['hours']}'\n";
    echo "status: '{$reg['status']}'\n";
    echo "---\n";
}
echo "</pre>";

echo "<h3>JSON encode (như trong view):</h3>";
echo "<textarea style='width:100%; height:300px'>";
echo json_encode($registrations, JSON_PRETTY_PRINT);
echo "</textarea>";

