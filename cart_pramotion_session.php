<?php
session_start();

ini_set('display_errors',1);
ini_set('system_startup_errors',1);
error_reporting(E_ALL);
include("admin/db.php");

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$apiKey = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
$secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {

    $gameId = trim($_POST['gameId'] ?? '');
    $gameName = trim($_POST['gameName'] ?? '');
    $slot = trim($_POST['slot'] ?? 'No slot');
    $eventId = trim($_POST['eventId'] ?? 0);
    $guests = (int)($_POST['guests'] ?? 0);
    $priceStr = trim($_POST['price'] ?? '0');
    $dataAvailable = trim($_POST['dataAvailable'] ?? '0');

    // Duplicate check by date
    $slotDate = $slot;
    if (preg_match('/^\d{4}-\d{2}-\d{2}T/', $slot)) {
        $slotDate = substr($slot, 0, 10);
    }
    foreach ($_SESSION['cart'] as $item) {
        $existingDate = $item['slot'];
        if (preg_match('/^\d{4}-\d{2}-\d{2}T/', $existingDate)) {
            $existingDate = substr($existingDate, 0, 10);
        }
        if ($item['gameId'] === $gameId && $existingDate === $slotDate) {
            echo json_response('error', 'This game is already in your cart for the same date.');
        }
    }

    // Price parsing
    $normalized = str_replace(["–", "—"], "-", $priceStr);
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

    $newCartItem = [
        'gameId' => $gameId,
        'eventId' => $eventId,
        'gameName' => $gameName,
        'slot' => $slot,
        'guests' => $guests,
        'price' => $priceUnit,
        'total' => $total
    ];

    // Promo code logic (exactly same as yours)
    $promoCode = "";
    $durationHours = 0;
    $promoStmt = $pdo->prepare("SELECT coupon_code, deal_hours FROM tbl_flash_deal WHERE is_active = 1 LIMIT 1");
    $promoStmt->execute();
    $promo = $promoStmt->fetch(PDO::FETCH_ASSOC);
    if ($promo) {
        $promoCode = trim($promo['coupon_code']);
        $durationHours = (float)$promo['deal_hours'];
    }
    if ($promoCode !== "" && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $slot)) {
        $slotTime = strtotime($slot);
        $now = time();
        $diffHours = ($slotTime - $now) / 3600;
        if (!($diffHours > 0 && $diffHours <= $durationHours)) {
            $promoCode = "";
        }
    }

    $sid = session_id();

    // 1) Refresh holds for existing cart items (your original logic - untouched)
    foreach ($_SESSION['cart'] as $item) {
        // delete old hold
        $stmt = $pdo->prepare("SELECT response_json FROM tbl_bookeo_holds WHERE session_id = :sid AND game_id = :game_id ORDER BY id DESC LIMIT 1");
        $stmt->execute([':sid' => $sid, ':game_id' => $item['gameId']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $data = json_decode($row['response_json'], true);
            $holdId = $data['id'] ?? null;
            if ($holdId) {
                $url = "https://api.bookeo.com/v2/holds/{$holdId}?apiKey={$apiKey}&secretKey={$secretKey}";
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_exec($ch);
                curl_close($ch);

                $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE session_id = :sid AND game_id = :game_id")
                    ->execute([':sid' => $sid, ':game_id' => $item['gameId']]);
            }
        }

        // create new hold for existing item
        $payload = [
            "eventId" => $item['eventId'],
            "customer" => [
                "firstName" => "Test", "lastName" => "User",
                "emailAddress" => "test@example.com",
                "phoneNumbers" => [["number" => "1234567890", "type" => "mobile"]]
            ],
            "participants" => [
                "numbers" => [["peopleCategoryId" => "Cadults", "number" => $item['guests']]
            ],
            "productId" => $item['gameId']
        ]];
        if ($promoCode !== "") {
            $payload["promotionCodeInput"] = $promoCode;  // Promo bheja ja raha hai
        }

        $ch = curl_init("https://api.bookeo.com/v2/holds");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "X-Bookeo-apiKey: $apiKey",
                "X-Bookeo-secretKey: $secretKey"
            ],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $bookeoData = $response ? json_decode($response, true) : [];

        // Save hold
        if ($bookeoData) {
            $pdo->prepare("
                INSERT INTO tbl_bookeo_holds (session_id, event_id, game_id, response_json, created_at)
                VALUES (:sid, :eid, :gid, :json, NOW())
                ON DUPLICATE KEY UPDATE response_json = VALUES(response_json), created_at = NOW()
            ")->execute([
                ':sid' => $sid,
                ':eid' => $item['eventId'],
                ':gid' => $item['gameId'],
                ':json' => json_encode($bookeoData)
            ]);
        }
    }

    // 2) NOW CREATE HOLD FOR NEW ITEM — YEH HAI ASLI CHANGE
    $newPayload = [
        "eventId" => $newCartItem['eventId'],
        "customer" => [
            "firstName" => "Test", "lastName" => "User",
            "emailAddress" => "test@example.com",
            "phoneNumbers" => [["number" => "1234567890", "type" => "mobile"]]
        ],
        "participants" => [
            "numbers" => [["peopleCategoryId" => "Cadults", "number" => $newCartItem['guests']]]
        ],
        "productId" => $newCartItem['gameId']
    ];

    // Promo code bilkul bhej rahe hain
    if ($promoCode !== "") {
        $newPayload["promotionCodeInput"] = $promoCode;
    }

    $ch = curl_init("https://api.bookeo.com/v2/holds");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "X-Bookeo-apiKey: $apiKey",
            "X-Bookeo-secretKey: $secretKey"
        ],
        CURLOPT_POSTFIELDS => json_encode($newPayload)
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $bookeoData = $response ? json_decode($response, true) : [];

    // LOG FOR DEBUG
    file_put_contents(__DIR__."/bookeo_hold_debug.log", 
        "=== NEW ITEM HOLD ===\n".date("c")."\nHTTP: $httpCode\nPAYLOAD: ".json_encode($newPayload, JSON_PRETTY_PRINT)."\nRESPONSE: ".json_encode($bookeoData, JSON_PRETTY_PRINT)."\n\n",
        FILE_APPEND
    );

    // AGAR HOLD NAHI BANA (slot unavailable ya error)
    if ($httpCode !== 200 && $httpCode !== 201 || empty($bookeoData['id'])) {
        json_response('slot_error', 'This time slot is no longer available. Please select another time slot.');
    }

    // SUCCESS: Ab safe hai save karna
    // Save hold in DB
    $pdo->prepare("
        INSERT INTO tbl_bookeo_holds (session_id, event_id, game_id, response_json, created_at)
        VALUES (:sid, :eid, :gid, :json, NOW())
        ON DUPLICATE KEY UPDATE response_json = VALUES(response_json), created_at = NOW()
    ")->execute([
        ':sid' => $sid,
        ':eid' => $eventId,
        ':gid' => $gameId,
        ':json' => json_encode($bookeoData)
    ]);

    // Calculate discount from Bookeo response
    $discountAmt = 0;
    $discountedTotal = $total;
    if (!empty($bookeoData['promotionApplicable']) && $bookeoData['promotionApplicable'] === true) {
        $discountAmt = $bookeoData['appliedPromotionDiscount']['amount'] ?? 0;
        $discountedTotal = $bookeoData['price']['totalNet']['amount'] ?? $total;
    }

    // Insert into tbl_carts
    $pdo->prepare("
        INSERT INTO tbl_carts
        (session_id, game_id, event_id, game_name, slot, guests, price, total, promo_code, discount_amt, discounted_total, created_at, cat, pramotion_page, dataAvailable)
        VALUES (:sid, :gid, :eid, :gname, :slot, :guests, :price, :total, :promo, :discount, :dtotal, NOW(), 'escape-room', 'true', :dataAvailable)
    ")->execute([
        ':sid' => $sid,
        ':gid' => $gameId,
        ':eid' => $eventId,
        ':gname' => $gameName,
        ':slot' => $slot,
        ':guests' => $guests,
        ':price' => $priceUnit,
        ':total' => $total,
        ':promo' => $promoCode ?: null,
        ':discount' => $discountAmt,
        ':dtotal' => $discountedTotal,
        ':dataAvailable' => $dataAvailable
    ]);

    // Add to session
    $newCartItem['promo_code'] = $discountAmt;
    $newCartItem['discounted_total'] = $discountedTotal;
    $newCartItem['cat'] = 'escape-room';
    $newCartItem['pramotion_page'] = 'true';
    $newCartItem['dataAvailable'] = $dataAvailable; 

    $_SESSION['cart'][] = $newCartItem;

    json_response('success', 'Added to cart successfully!', ['cart' => $_SESSION['cart']]);
}

// Helper function
function json_response($status, $message, $extra = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $extra));
    exit;
}
?>