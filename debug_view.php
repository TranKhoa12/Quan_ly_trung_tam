<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing dashboard view...<br>";

$viewPath = __DIR__ . '/app/views/dashboard/staff.php';
echo "View path: $viewPath<br>";
echo "File exists: " . (file_exists($viewPath) ? "YES" : "NO") . "<br>";

if (file_exists($viewPath)) {
    echo "<hr>File content preview (first 500 chars):<br><pre>";
    echo htmlspecialchars(substr(file_get_contents($viewPath), 0, 500));
    echo "</pre>";
}
?>
