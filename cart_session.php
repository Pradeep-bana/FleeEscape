<?php
session_start();
include("admin/db.php");

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Remove expired holds (keeps slot management clean)
require('remove_expired_holds.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'add_to_cart') {

    $gameId        = trim($_POST['gameId']        ?? '');
    $gameName      = trim($_POST['gameName']       ?? '');
    $slot          = trim($_POST['slot']           ?? 'No slot');
    $eventId       = trim($_POST['eventId']        ?? 0);
    $guests        = (int)($_POST['guests']        ?? 0);
    $priceStr      = trim($_POST['price']          ?? '0');
    $dataAvailable = trim($_POST['dataAvailable']  ?? '0');
    $cat           = "escape-room"; // Fixed category

    // ------------------------------------------------------------------
    // STEP 1: DUPLICATE CHECK — only for escape-room + pramotion_page == true
    // (same game, same date already in cart)
    // ------------------------------------------------------------------
    $slotDate = $slot;
    if (preg_match('/^\d{4}-\d{2}-\d{2}T/', $slot)) {
        $slotDate = substr($slot, 0, 10);
    }

    foreach ($_SESSION['cart'] as $item) {
        if (($item['cat'] ?? '') !== 'escape-room' || $item['pramotion_page'] !== 'true') {
            continue;
        }
        $existingDate = $item['slot'];
        if (preg_match('/^\d{4}-\d{2}-\d{2}T/', $existingDate)) {
            $existingDate = substr($existingDate, 0, 10);
        }
        if ($item['gameId'] === $gameId && $existingDate === $slotDate) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'This game is already in your cart for the same date.',
                'cart'    => $_SESSION['cart']
            ]);
            exit;
        }
    }

    // ------------------------------------------------------------------
    // STEP 2: PRICE NORMALIZATION (tiered: 2 guests vs 3+ guests)
    // ------------------------------------------------------------------
    $normalized = str_replace(["â€“", "â€”", "–", "—"], "-", $priceStr);
    $normalized = preg_replace('/\s*-\s*/', '-', $normalized);
    preg_match_all('/\d+(?:\.\d+)?/', $normalized, $nums);
    $nums = $nums[0] ?? [];

    $priceUnit = 0.0;
    if (count($nums) >= 2) {
        $a = (float)$nums[0];
        $b = (float)$nums[1];
        $priceUnit = ($guests <= 2) ? max($a, $b) : min($a, $b);
    } elseif (count($nums) === 1) {
        $priceUnit = (float)$nums[0];
    }
    $total = $guests * $priceUnit;

    // ------------------------------------------------------------------
    // STEP 3: SAVE TO SESSION + DB
    // (No Bookeo API calls here — apply_code.php owns all holds)
    // ------------------------------------------------------------------
    $cartItem = [
        'gameId'        => $gameId,
        'eventId'       => $eventId,
        'gameName'      => $gameName,
        'slot'          => $slot,
        'guests'        => $guests,
        'price'         => $priceUnit,
        'total'         => $total,
        'dataAvailable' => $dataAvailable,
        'cat'           => $cat,
        'pramotion_page'=> 'false'
    ];

    $_SESSION['cart'][] = $cartItem;

    $stmt = $pdo->prepare("
        INSERT INTO tbl_carts
            (session_id, game_id, event_id, game_name, slot, guests, price, total, created_at, cat, dataAvailable, pramotion_page)
        VALUES
            (:sid, :game_id, :event_id, :game_name, :slot, :guests, :price, :total, NOW(), :cat, :dataAvailable, :pramotion_page)
    ");
    $stmt->execute([
        ':sid'           => session_id(),
        ':game_id'       => $gameId,
        ':event_id'      => $eventId,
        ':game_name'     => $gameName,
        ':slot'          => $slot,
        ':guests'        => $guests,
        ':price'         => $priceUnit,
        ':total'         => $total,
        ':cat'           => $cat,
        ':dataAvailable' => $dataAvailable,
        ':pramotion_page'=> 'false'
    ]);

    // ------------------------------------------------------------------
    // STEP 4: DETERMINE PROMO CODE — so JS knows what to pass to apply_code
    // (apply_code.php also calculates this itself, but returning it here
    //  lets the frontend pass it as the 'code' param immediately)
    // ------------------------------------------------------------------
    $escapeCount = 0;
    foreach ($_SESSION['cart'] as $ci) {
        if (
            ($ci['cat'] ?? '') === 'escape-room' &&
            ($ci['pramotion_page'] == 'false' || $ci['pramotion_page'] == 'save_more_play_more')
        ) {
            $escapeCount++;
        }
    }

    $promoCode = "";
    if ($escapeCount == 2)      $promoCode = "BMSM_10";
    elseif ($escapeCount >= 3)  $promoCode = "BMSM_20";

    // ------------------------------------------------------------------
    // SUCCESS — apply_code.php will be called by the frontend next
    // to create holds for ALL items correctly in one pass
    // ------------------------------------------------------------------
    echo json_encode([
        'status'    => 'success',
        'message'   => 'Item added to cart. Creating hold...',
        'cart'      => array_values($_SESSION['cart']),
        'promo'     => $promoCode,      // frontend passes this to apply_code
        'eventId'   => $eventId,        // so JS can do rollback if apply_code fails
        'gameId'    => $gameId
    ]);
    exit;
}
?>