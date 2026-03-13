<?php
session_start();
include("admin/db.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cartId   = intval($_POST['cart_id']);
    $sessionId = session_id();

    // Reset all additional guest related columns
    $stmt = $pdo->prepare("
        UPDATE tbl_carts 
        SET 
            additional_guest = 0,
            total_additional_price = 0.00,
            per_guest_price = 0.00
        WHERE id = :id AND session_id = :sid
    ");

    $result = $stmt->execute([
        ':id' => $cartId,
        ':sid' => $sessionId
    ]);

    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
}
?>
