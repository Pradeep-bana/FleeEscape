<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

// Clear the gift code from session
unset($_SESSION['giftCode']);

// Re-run apply_code.php with empty code to refresh holds cleanly
$_POST['code'] = '';
include("apply_code.php");
exit;