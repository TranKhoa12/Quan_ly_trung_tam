<?php
session_start();

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    echo "NOT LOGGED IN - Redirecting to login...<br>";
    echo "Please login first at: <a href='/Quan_ly_trung_tam/public/login'>Login Page</a>";
    exit;
}

echo "LOGGED IN<br>";
echo "User ID: " . $_SESSION['user_id'] . "<br>";
echo "Username: " . $_SESSION['username'] . "<br>";
echo "Full Name: " . $_SESSION['full_name'] . "<br>";
echo "Role: " . $_SESSION['role'] . "<br>";
echo "<br>";
echo "<a href='/Quan_ly_trung_tam/public/dashboard'>Go to Dashboard</a>";
?>
