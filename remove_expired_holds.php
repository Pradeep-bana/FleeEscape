<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('FLEE_CART_SESSION_LIBRARY')) {
    define('FLEE_CART_SESSION_LIBRARY', true);
}

require_once('cart_session.php');
$fleeExpiredCleanup = flee_cart_cleanup_expired($pdo, $_REQUEST['reason'] ?? '');
?>
