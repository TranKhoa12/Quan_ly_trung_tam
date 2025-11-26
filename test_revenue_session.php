<?php
session_start();

// Fake login as staff
$_SESSION['user_id'] = 10;
$_SESSION['username'] = 'demo';
$_SESSION['full_name'] = 'Demo User';
$_SESSION['role'] = 'staff';

echo "Session set. Now try accessing: http://localhost:8081/Quan_ly_trung_tam/public/revenue/19";
?>
