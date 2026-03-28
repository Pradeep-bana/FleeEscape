<?php
session_start();
include("admin/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartId = intval($_POST['cart_id']);
    $newQty = intval($_POST['qty']);
    $sessionId = session_id();

    // 1. Fetch current per_guest_price for security
    $stmt = $pdo->prepare("SELECT per_guest_price FROM tbl_carts WHERE id = :id AND session_id = :sid");
    $stmt->execute([':id' => $cartId, ':sid' => $sessionId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $perGuestPrice = floatval($row['per_guest_price']);
        
        // 2. Calculate new total for additional guests
        $newTotalAdditional = $perGuestPrice * $newQty;

        // 3. Update the database
        $updateStmt = $pdo->prepare("
            UPDATE tbl_carts 
            SET additional_guest = :qty, 
                total_additional_price = :total 
            WHERE id = :id
        ");
        
        $updateStmt->execute([
            ':qty' => $newQty,
            ':total' => $newTotalAdditional,
            ':id' => $cartId
        ]);

        // Refresh Bookeo hold so totals (taxes, pricing) stay in sync when additional guests change.
        // We do this the same way update_qty_hold.php does: re-run apply_code.php using the current session code.
        $_POST['code'] = $_SESSION['giftCode'] ?? '';
        include("apply_code.php");
        exit;

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found']);
    }
}
?>