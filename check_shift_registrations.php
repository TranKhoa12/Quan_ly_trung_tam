<?php
$pdo = new PDO('mysql:host=localhost;dbname=quan_ly_trung_tam', 'root', '');

echo "<h3>Cấu trúc bảng shift_registrations:</h3><pre>";
$stmt = $pdo->query('DESCRIBE shift_registrations');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ") " . ($col['Null'] == 'YES' ? 'NULL' : 'NOT NULL') . "\n";
}
echo "</pre>";

echo "<h3>Dữ liệu mới nhất trong shift_registrations (10 bản ghi):</h3><pre>";
$stmt = $pdo->query('SELECT * FROM shift_registrations ORDER BY created_at DESC LIMIT 10');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
echo "</pre>";
