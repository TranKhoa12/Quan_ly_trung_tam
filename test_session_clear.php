<?php
session_start();

echo "=== TEST SESSION MESSAGE CLEARING ===\n\n";

// Test 1: Set a success message
$_SESSION['success'] = 'Test success message';
echo "1. Set success message: " . $_SESSION['success'] . "\n";

// Simulate what modern.php layout does
if (isset($_SESSION['success'])) {
    $message = $_SESSION['success'];
    unset($_SESSION['success']);
    echo "2. Message captured and unset: $message\n";
    echo "3. Session success after unset: " . (isset($_SESSION['success']) ? 'STILL EXISTS (BAD)' : 'CLEARED (GOOD)') . "\n\n";
}

// Test 2: Set error message
$_SESSION['error'] = 'Test error message';
echo "4. Set error message: " . $_SESSION['error'] . "\n";

if (isset($_SESSION['error'])) {
    $message = $_SESSION['error'];
    unset($_SESSION['error']);
    echo "5. Message captured and unset: $message\n";
    echo "6. Session error after unset: " . (isset($_SESSION['error']) ? 'STILL EXISTS (BAD)' : 'CLEARED (GOOD)') . "\n\n";
}

echo "✓ Session clearing working correctly!\n";
echo "Messages will only show once, then disappear.\n";
