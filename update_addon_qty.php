<?php
// update_addon_qty.php
ini_set('display_errors', 0);
session_start();
include("admin/db.php");

header('Content-Type: application/json');

$sid = session_id();
$cart_id = isset($_POST['cart_id']) ? intval($_POST['cart_id']) : 0;
$qty = isset($_POST['qty']) ? intval($_POST['qty']) : 0;

if ($cart_id <= 0 || $qty <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
    exit;
}

// 1) Fetch the existing addon data from the cart
$stmt = $pdo->prepare("SELECT id, game_id, addon_opt_id, addon_price FROM tbl_carts WHERE id = :id AND session_id = :sid");
$stmt->execute([':id' => $cart_id, ':sid' => $sid]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode(["status" => "error", "message" => "Cart item not found"]);
    exit;
}

// 2) Calculate new math
$addon_price = floatval($row['addon_price']);
$subtotal = $addon_price * $qty;
$addon_tax = round($subtotal * 0.103, 2);

// 3) Update the database using MySQL NOW() to prevent timezone expiration issues
$update = $pdo->prepare("
    UPDATE tbl_carts 
    SET addon_qty = :qty, 
        addon_subtotal = :subtotal, 
        addon_tax = :tax,
        created_at = NOW()
    WHERE id = :id
");

$update->execute([
    ":qty" => $qty,
    ":subtotal" => $subtotal,
    ":tax" => $addon_tax,
    ":id" => $cart_id
]);

// Keep all session cart items alive
$touchCart = $pdo->prepare("UPDATE tbl_carts SET created_at = NOW() WHERE session_id = :sid");
$touchCart->execute([":sid" => $sid]);

// 4) Update the Session Array (keeps Bookeo payload synced)
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as &$cartItem) {
        $cartGameId = $cartItem['gameId'] ?? ($cartItem['game_id'] ?? '');
        if ($cartGameId == $row['game_id'] && isset($cartItem['addons'])) {
            foreach ($cartItem['addons'] as &$addon) {
                if ($addon['addon_opt_id'] == $row['addon_opt_id']) {
                    $addon['addon_qty']      = $qty;
                    $addon['addon_subtotal'] = $subtotal;
                    $addon['addon_tax']      = $addon_tax;
                }
            }
        }
    }
    unset($cartItem);
}

echo json_encode(["status" => "success", "message" => "Addon quantity updated"]);
exit;
?>