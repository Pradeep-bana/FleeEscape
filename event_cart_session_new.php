<?php
session_start();
include("admin/db.php");

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$apiKey = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
$secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'add_party_cart') {

    $gameId   = trim($_POST['gameId'] ?? '');
    $gameName = trim($_POST['gameName'] ?? '');
    $slot     = trim($_POST['slot'] ?? 'No slot');
    $eventId  = trim($_POST['eventId'] ?? 0);
    $players  = 1;
    $priceStr = trim($_POST['price'] ?? '0');

    // Extract date for duplicate check
    $slotDate = $slot;
    if (preg_match('/^\d{4}-\d{2}-\d{2}T/', $slot)) {
        $slotDate = substr($slot, 0, 10);
    }

    // Prevent duplicate game + date
    foreach ($_SESSION['cart'] as $item) {
        $existingSlot = $item['slot'];
        $existingDate = $existingSlot;
        if (preg_match('/^\d{4}-\d{2}-\d{2}T/', $existingSlot)) {
            $existingDate = substr($existingSlot, 0, 10);
        }
        if ($item['eventId'] === $eventId && $existingDate === $slotDate) {
            echo json_encode([
                'status' => 'bookeo_error',
                'message' => 'This game is already in your cart for the same date.',
                'cart' => $_SESSION['cart']
            ]);
            exit;
        }
    }

    // ADD THIS ITEM TEMPORARY
    $cartItem = [
        'gameId' => $gameId,
        'eventId' => $eventId,
        'gameName' => $gameName,
        'slot' => $slot,
        'guests' => $players,
        'price' => $priceStr,
        'total' => $priceStr,
        'cat' => 'event-rooms'
    ];

    $_SESSION['cart'][] = $cartItem;

    $sid = session_id();

    // SAVE IN tbl_carts
    $stmt = $pdo->prepare("
        INSERT INTO tbl_carts
        (session_id, game_id, event_id, game_name, slot, guests, price, total, created_at, cat)
        VALUES (:sid, :game_id, :event_id, :game_name, :slot, :guests, :price, :total, NOW(), 'event-rooms')
    ");
    $stmt->execute([
        ':sid' => $sid,
        ':game_id' => $gameId,
        ':event_id' => $eventId,
        ':game_name' => $gameName,
        ':slot' => $slot,
        ':guests' => $players,
        ':price' => $priceStr,
        ':total' => $priceStr
    ]);

    /** -------------------------------------------------
     *  ONLY CURRENT ITEM HOLD API (NO FOREACH)
     *  ------------------------------------------------- */

  

    // CREATE NEW HOLD (NO PROMOCODE)
    $payload = [
        "eventId" => $eventId,
        "customer" => [
            "firstName" => "Test",
            "lastName" => "User",
            "emailAddress" => "test@example.com",
            "phoneNumbers" => [
                ["number" => "1234567890", "type" => "mobile"]
            ]
        ],
        "participants" => [
            "numbers" => [
                ["peopleCategoryId" => "Cadults", "number" => 1]
            ]
        ],
        "productId" => $gameId
    ];

    $ch = curl_init("https://api.bookeo.com/v2/holds");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "X-Bookeo-apiKey: $apiKey",
        "X-Bookeo-secretKey: $secretKey"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $bookeoData = json_decode($response, true) ?? [];

    // LOG
    file_put_contents(__DIR__."/bookeo_hold_debug.log",
        date("Y-m-d H:i:s") . " | GameID: {$gameId} | HTTP: $httpCode | Response: " . json_encode($bookeoData) . "\n",
        FILE_APPEND | LOCK_EX
    );

    // IF HOLD FAILED → ROLLBACK ONLY THIS ITEM
    if (!in_array($httpCode, [200, 201])) {

        // Remove only this item from session
        foreach ($_SESSION['cart'] as $k => $v) {
            if ($v['gameId'] === $gameId && $v['slot'] === $slot) {
                unset($_SESSION['cart'][$k]);
                break;
            }
        }
        $_SESSION['cart'] = array_values($_SESSION['cart']);

        // Remove from DB
        $pdo->prepare("DELETE FROM tbl_carts WHERE session_id = ? AND event_id = ?")
            ->execute([$sid, $eventId]);

        echo json_encode([
            'status' => 'bookeo_error',
            'message' => $bookeoData['message'] ?? "This time slot is no longer available or fully booked."
        ]);
        exit;
    }

    // SAVE HOLD RESPONSE IN DB
    $stmt = $pdo->prepare("
        INSERT INTO tbl_bookeo_holds (session_id, event_id, game_id, response_json, created_at)
        VALUES (?, ?, ?, ?, NOW())
       
    ");
    $stmt->execute([$sid, $eventId, $gameId, json_encode($bookeoData)]);

    // SUCCESS
    echo json_encode([
        'status' => 'success',
        'message' => 'Added to cart successfully!',
        'cart' => $_SESSION['cart']
    ]);
    exit;
}
?>
