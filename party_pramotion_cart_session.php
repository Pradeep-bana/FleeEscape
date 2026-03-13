<?php
session_start();
include("admin/db.php"); // <-- db.php must set $pdo = new PDO(...)

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$apiKey    = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
$secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'add_party_cart') {

    $gameId   = trim($_POST['gameId'] ?? '');
    $gameName = trim($_POST['gameName'] ?? '');
    $slot     = trim($_POST['slot'] ?? 'No slot');
    $eventId  = trim($_POST['eventId'] ?? '');
    $guests   = 1;
    $priceStr = trim($_POST['price'] ?? '0');
    $additionalGuest     = intval($_POST['additional_guest'] ?? 0);
    $perGuestPrice       = floatval($_POST['per_guest_price'] ?? 0);
    $totalAdditionalPrice = floatval($_POST['total_additional_price'] ?? 0);

    // --- Prevent duplicate entry for same gameId + same date ---
    $slotDate = $slot;
    if (preg_match('/^\d{4}-\d{2}-\d{2}T/', $slot)) {
        $slotDate = substr($slot, 0, 10);
    }

    foreach ($_SESSION['cart'] as $item) {
        $existingSlot = $item['slot'];
        $existingDate = preg_match('/^\d{4}-\d{2}-\d{2}T/', $existingSlot)
            ? substr($existingSlot, 0, 10)
            : $existingSlot;

        if ($item['gameId'] === $gameId && $existingDate === $slotDate) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'This game is already in your cart for the same date.',
                'cart'    => $_SESSION['cart']
            ]);
            exit;
        }
    }

    // -------------------------------------------------------------------
    // Read promo from tbl_coupon as per your reference & check window
    // -------------------------------------------------------------------
    $promoCode = '';
    $durationHours = 0;
    $promoApplies = false;

    $promoStmt = $pdo->prepare("
        SELECT coupon_code, duration 
        FROM tbl_coupon 
        WHERE type = 'pramotion' AND  game_type = 'party-package' AND status = 0 
        LIMIT 1
    ");
    $promoStmt->execute();
    $promo = $promoStmt->fetch(PDO::FETCH_ASSOC);

    if ($promo) {
        $promoCode = trim($promo['coupon_code'] ?? '');
        $durationHours = floatval($promo['duration'] ?? 0);
    }

    // Check if promo applies for this slot using strtotime and duration
    if ($promoCode !== "" && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $slot)) {
        $slotTime = strtotime($slot);
        $now      = time();
        $diffHours = ($slotTime - $now) / 3600; // hours difference

        // promo applies only if slot is in the future and within durationHours
        if ($diffHours > 0 && $diffHours <= $durationHours) {
            $promoApplies = true;
        } else {
            // outside promo window
            $promoCode = "";
            $promoApplies = false;
        }
    } else {
        // no valid promo or no valid slot time
        $promoCode = "";
        $promoApplies = false;
    }

    // -------------------------------------------------------------------
    // Prepare Bookeo payload
    // -------------------------------------------------------------------
    $cartPayload = [
        "productId" => $gameId,
        "customer" => [
            "firstName" => "Test",
            "lastName"  => "User",
            "emailAddress" => "test@example.com",
            "phoneNumbers" => [["number" => "1234567890", "type" => "mobile"]]
        ],
        "participants" => [
            "numbers" => [["peopleCategoryId" => "Cadults", "number" => 1]]
        ]
    ];

    if (!empty($eventId)) {
        $cartPayload["eventId"] = $eventId;
    }

    // Only attach promotionCodeInput if promoApplies (inside window)
    if ($promoApplies && $promoCode !== "") {
        $cartPayload["promotionCodeInput"] = $promoCode;
    }

    // -------------------------------------------------------------------
    // Call Bookeo API to create hold
    // -------------------------------------------------------------------
    $ch = curl_init("https://api.bookeo.com/v2/holds");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "X-Bookeo-apiKey: $apiKey",
        "X-Bookeo-secretKey: $secretKey"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($cartPayload));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $bookeoData = $response ? json_decode($response, true) : [];

    // Debug log
    file_put_contents(__DIR__ . "/bookeo_hold_debug.log",
        "=============================\n" .
        date("c") . "\n" .
        "REQUEST:\n" . json_encode($cartPayload, JSON_PRETTY_PRINT) . "\n\n" .
        "HTTP CODE: $httpCode\n" .
        "RESPONSE:\n" . json_encode($bookeoData, JSON_PRETTY_PRINT) . "\n\n",
        FILE_APPEND
    );

    // -------------------------------------------------------------------
    // Only store in DB + session if Bookeo success (200 or 201)
    // -------------------------------------------------------------------
    if ($httpCode == 200 || $httpCode == 201) {

        $sid = session_id();

        // Since tbl_coupon didn't provide discount amount, set discount_amt = 0
        // and keep discounted_total equal to priceStr (no auto discount applied).
        $discountAmt = 0.00;
        $discountedTotal = floatval($priceStr);

        // Insert into tbl_carts
        $insertCart = $pdo->prepare("
            INSERT INTO tbl_carts 
            (
                session_id, game_id, event_id, game_name, slot, guests, price, total, created_at, cat,
                pramotion_page, promo_code, discount_amt, discounted_total, escape_selection,
                additional_guest, per_guest_price, total_additional_price
            ) VALUES (
                :sid, :game_id, :event_id, :game_name, :slot, :guests, :price, :total, NOW(), :cat,
                :pramotion_page, :promo_code, :discount_amt, :discounted_total, :escape_selection,
                :additional_guest, :per_guest_price, :total_additional_price
            )
        ");

        $insertCart->execute([
            ':sid'                  => $sid,
            ':game_id'              => $gameId,
            ':event_id'             => $eventId,
            ':game_name'            => $gameName,
            ':slot'                 => $slot,
            ':guests'               => $guests,
            ':price'                => $priceStr,
            ':total'                => $priceStr,
            ':cat'                  => 'party-package',
            ':pramotion_page'       => ($promoApplies ? 1 : 0),
            ':promo_code'           => ($promoApplies ? $promoCode : ''),
            ':discount_amt'         => $discountAmt,
            ':discounted_total'     => $discountedTotal,
            ':escape_selection'     => '', // keep empty as default; change if you have selection data
            ':additional_guest'     => $additionalGuest,
            ':per_guest_price'      => $perGuestPrice,
            ':total_additional_price' => $totalAdditionalPrice
        ]);

        // Save to session (same condition)
        $_SESSION['cart'][] = [
            'gameId'   => $gameId,
            'eventId'  => $eventId,
            'gameName' => $gameName,
            'slot'     => $slot,
            'guests'   => $guests,
            'price'    => $priceStr,
            'total'    => $priceStr,
            'cat'      => 'party-package',
            'pramotion_page'     => ($promoApplies ? 1 : 0),
            'promo_code'         => ($promoApplies ? $promoCode : ''),
            'discount_amt'       => $discountAmt,
            'discounted_total'   => $discountedTotal,
            'escape_selection'   => '',
            'additional_guest'   => $additionalGuest,
            'per_guest_price'    => $perGuestPrice,
            'total_additional_price' => $totalAdditionalPrice
        ];

        // Save Bookeo hold response
        $holdStmt = $pdo->prepare("
            INSERT INTO tbl_bookeo_holds (session_id, event_id, game_id, response_json, created_at)
            VALUES (:sid, :event_id, :game_id, :response_json, NOW())
            ON DUPLICATE KEY UPDATE response_json = VALUES(response_json), created_at = NOW()
        ");
        $holdStmt->execute([
            ':sid'          => $sid,
            ':event_id'     => $eventId,
            ':game_id'      => $gameId,
            ':response_json'=> json_encode($bookeoData)
        ]);

        echo json_encode([
            'status'       => 'success',
            'message'      => 'Game added successfully (Bookeo hold created).',
            'cart'         => $_SESSION['cart'],
            'promoApplied' => $promoApplies ? true : false,
            'promoCode'    => $promoApplies ? $promoCode : '',
            'httpCode'     => $httpCode
        ]);
    } else {
        // Bookeo hold failed — do not save cart item
        echo json_encode([
            'status'  => 'error',
            'message' => 'Failed to create hold on Bookeo. Game not added to cart.',
            'response'=> $bookeoData,
            'httpCode'=> $httpCode
        ]);
    }

    exit;
}
?>
