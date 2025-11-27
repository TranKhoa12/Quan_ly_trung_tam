<?php
$pdo = new PDO('mysql:host=localhost;dbname=quan_ly_trung_tam', 'root', '');
$stmt = $pdo->query('DESCRIBE certificates');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Columns in certificates table:</h3><pre>";
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
echo "</pre>";
