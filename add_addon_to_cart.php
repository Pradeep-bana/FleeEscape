<?php
// add_addon_to_cart.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include("admin/db.php");

header('Content-Type: application/json');

$sid             = session_id();
$addon_opt_id    = $_POST['addon_opt_id'] ?? '';
$game_id         = $_POST['product_code'] ?? '';
$addon_name      = $_POST['addon_name'] ?? '';
$addon_price     = floatval($_POST['addon_price'] ?? 0);
$qty             = intval($_POST['qty'] ?? 0);

if ($game_id == '' || $addon_name == '' || $qty <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

$subtotal   = $addon_price * $qty;

// ------------------------------
// TAX CALCULATION 10.3%
// ------------------------------
$addon_tax = round($subtotal * 0.103, 2);

$created_at = date("Y-m-d H:i:s");

// 1) Check main game exists for this session + game
$parent = $pdo->prepare("
    SELECT id FROM tbl_carts 
    WHERE session_id = :sid 
      AND game_id    = :gid
    ORDER BY id DESC LIMIT 1
");
$parent->execute([
    ":sid" => $sid,
    ":gid" => $game_id
]);

$row = $parent->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode(["success" => false, "message" => "Main game not found in cart"]);
    exit;
}

// 2) UPDATE addon columns in database
$update = $pdo->prepare("
    UPDATE tbl_carts 
    SET addon_name     = :name,
        addon_price    = :price,
        addon_opt_id   = :addon_opt_id,
        addon_qty      = :qty,
        addon_subtotal = :subtotal,
        addon_tax      = :addon_tax,
        created_at     = :created_at
    WHERE id = :id
");

$update->execute([
    ":name"         => $addon_name,
    ":price"        => $addon_price,
    ":addon_opt_id" => $addon_opt_id,
    ":qty"          => $qty,
    ":subtotal"     => $subtotal,
    ":addon_tax"    => $addon_tax,
    ":created_at"   => $created_at,
    ":id"           => $row["id"]
]);

$touchCart = $pdo->prepare("
    UPDATE tbl_carts
    SET created_at = :created_at
    WHERE session_id = :sid
");
$touchCart->execute([
    ":created_at" => $created_at,
    ":sid"        => $sid
]);


// ----------------------------------------------------------
// 3) UPDATE ALSO THE SESSION CART
// ----------------------------------------------------------

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Find matching gameId inside session cart
foreach ($_SESSION['cart'] as &$cartItem) {
    $cartGameId = $cartItem['gameId'] ?? ($cartItem['game_id'] ?? '');
    if ($cartGameId == $game_id) {

        // Create addon detail array
        $addonArray = [
            "addon_opt_id"   => $addon_opt_id,
            "addon_name"     => $addon_name,
            "addon_price"    => $addon_price,
            "addon_qty"      => $qty,
            "addon_subtotal" => $subtotal,
            "addon_tax"      => $addon_tax
        ];

        $cartItem['addons'] = [$addonArray];
    }
}
unset($cartItem);

echo json_encode(["success" => true, "message" => "Addon updated with tax"]);
exit;
?>
