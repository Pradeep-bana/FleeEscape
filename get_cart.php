<?php
session_start();
header('Content-Type: application/json');
require_once('remove_expired_holds.php');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

echo json_encode(['cart' => $_SESSION['cart']]);
