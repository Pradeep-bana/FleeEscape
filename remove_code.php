<?php
session_status() === PHP_SESSION_NONE && session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

// Clear the gift code from session
unset($_SESSION['giftCode']);

// Set flag to skip auto-promo detection when removing codes
$_SESSION['skip_auto_promo'] = true;

// Re-run apply_code.php with empty code to refresh holds cleanly
$_POST['code'] = '';
include("apply_code.php");
exit;