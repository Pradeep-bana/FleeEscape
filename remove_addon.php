<?php
session_start();
include("admin/db.php");
require_once("includes/booking_funnel.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartId = intval($_POST['cart_id']);
    $sessionId = session_id();

    // Fetch cart item first to get event_id
    $fetch_stmt = $pdo->prepare("SELECT event_id, addon_name FROM tbl_carts WHERE id = :id AND session_id = :sid");
    $fetch_stmt->execute([':id' => $cartId, ':sid' => $sessionId]);
    $cart_item = $fetch_stmt->fetch(PDO::FETCH_ASSOC);
    $event_id = $cart_item['event_id'] ?? null;
    $addon_name_removed = $cart_item['addon_name'] ?? null;

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