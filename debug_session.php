<?php
session_start();
echo "<h3>Session Debug</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['user'])) {
    echo "<h3>User Info:</h3>";
    echo "<pre>";
    print_r($_SESSION['user']);
    echo "</pre>";
}
