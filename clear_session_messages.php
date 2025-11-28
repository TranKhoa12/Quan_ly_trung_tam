<?php
// Clear all session data and test
session_start();

echo "=== CLEARING ALL SESSION MESSAGES ===\n\n";

// Check current session
echo "Current session state:\n";
echo "- success: " . (isset($_SESSION['success']) ? $_SESSION['success'] : 'NOT SET') . "\n";
echo "- error: " . (isset($_SESSION['error']) ? $_SESSION['error'] : 'NOT SET') . "\n";
echo "- warning: " . (isset($_SESSION['warning']) ? $_SESSION['warning'] : 'NOT SET') . "\n\n";

// Clear all message types
$cleared = 0;
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
    $cleared++;
    echo "✓ Cleared success message\n";
}
if (isset($_SESSION['error'])) {
    unset($_SESSION['error']);
    $cleared++;
    echo "✓ Cleared error message\n";
}
if (isset($_SESSION['warning'])) {
    unset($_SESSION['warning']);
    $cleared++;
    echo "✓ Cleared warning message\n";
}

if ($cleared === 0) {
    echo "✓ No messages to clear\n";
}

echo "\n=== DONE ===\n";
echo "Please refresh your browser and navigate to any page.\n";
echo "You should NOT see any lingering messages.\n";
