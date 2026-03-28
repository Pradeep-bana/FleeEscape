<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$reason = trim((string)($_GET['reason'] ?? ''));
if ($reason !== '') {
    $_REQUEST['reason'] = $reason;
}

if (!defined('FLEE_CART_SESSION_LIBRARY')) {
    define('FLEE_CART_SESSION_LIBRARY', true);
}
require_once 'cart_session.php';
$fleeExpiredCleanup = flee_cart_cleanup_expired($pdo, $reason);

echo json_encode([
    'status' => 'success',
    'expired_count' => (int)($fleeExpiredCleanup['expired_count'] ?? 0),
    'expired_event_ids' => array_values($fleeExpiredCleanup['expired_event_ids'] ?? []),
    'cart_count' => (int)($fleeExpiredCleanup['cart_count'] ?? 0),
]);
exit;
?>
