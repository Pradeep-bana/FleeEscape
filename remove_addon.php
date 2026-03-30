<?php
session_start();
include("admin/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartId = intval($_POST['cart_id']);
    $sessionId = session_id();

    // Reset all addon related columns to NULL or 0
    $stmt = $pdo->prepare("
        UPDATE tbl_carts 
        SET 
            addon_opt_id = NULL,
            addon_name = NULL,
            addon_price = 0.00,
            addon_qty = 0,
            addon_subtotal = 0.00,
            addon_tax = 0.00
        WHERE id = :id AND session_id = :sid
    ");

    $result = $stmt->execute([
        ':id' => $cartId,
        ':sid' => $sessionId
    ]);

    if ($result) {
        // Refresh Bookeo hold to recalculate prices with the new cart totals
        $_POST['code'] = $_SESSION['giftCode'] ?? '';
        include("apply_code.php");
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
}
?>