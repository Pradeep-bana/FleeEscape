<?php session_start();

echo 'before session';

if (session_status() === PHP_SESSION_ACTIVE) {
    echo "Session is active<br>";
    echo "Session ID: " . session_id();
} else {
    echo "No active session";
}

echo '<pre>';
print_r($_SESSION);
echo '</pre>';
echo 'after session';
?>