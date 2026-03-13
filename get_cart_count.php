<?php
include("admin/db.php");
session_start();
require_once('remove_expired_holds.php');

$sid = session_id();

// --- Fetch latest cart count ---
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_carts WHERE session_id = :sid");
$stmt->execute([':sid' => $sid]);
$cartCount = $stmt->fetchColumn();

echo json_encode(['count' => (int)$cartCount]);
