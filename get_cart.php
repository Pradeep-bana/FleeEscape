<?php
session_start();
header('Content-Type: application/json');
if (!defined('FLEE_CART_SESSION_LIBRARY')) {
    define('FLEE_CART_SESSION_LIBRARY', true);
}
require_once('cart_session.php');
flee_cart_cleanup_expired($pdo);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

echo json_encode(['cart' => $_SESSION['cart']]);
