<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$reason = trim((string)($_GET['reason'] ?? ''));
if ($reason !== '') {
    $_REQUEST['reason'] = $reason;
}

require_once 'remove_expired_holds.php';

echo json_encode([
    'status' => 'success',
    'expired_count' => (int)($fleeExpiredCleanup['expired_count'] ?? 0),
    'expired_event_ids' => array_values($fleeExpiredCleanup['expired_event_ids'] ?? []),
    'cart_count' => (int)($fleeExpiredCleanup['cart_count'] ?? 0),
]);
exit;
?>
