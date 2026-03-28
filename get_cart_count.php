<?php
include("admin/db.php");
session_start();
if (!defined('FLEE_CART_SESSION_LIBRARY')) {
    define('FLEE_CART_SESSION_LIBRARY', true);
}
require_once('cart_session.php');
flee_cart_cleanup_expired($pdo);

$sid = session_id();

// --- Fetch latest cart count ---
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_carts WHERE session_id = :sid");
$stmt->execute([':sid' => $sid]);
$cartCount = $stmt->fetchColumn();

echo json_encode(['count' => (int)$cartCount]);
