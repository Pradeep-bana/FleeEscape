<?php
session_start();
include("admin/db.php");
require_once('config.php');

// --- DEBUG FUNCTION ---
function log_msg($message, $data = null) {
    $file = __DIR__ . '/debug_party_cart.log'; // Log file in current directory
    $timestamp = date("Y-m-d H:i:s");
    $logEntry = "[$timestamp] $message";
    
    if ($data !== null) {
        $logEntry .= "\nDATA: " . (is_array($data) || is_object($data) ? print_r($data, true) : $data);
    }
    
    $logEntry .= "\n-------------------------\n";
    file_put_contents($file, $logEntry, FILE_APPEND);
}
// ----------------------

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$apiKey    = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
$secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'add_party_cart') {

    // 1. Log Incoming Request
    log_msg("New 'add_party_cart' Request Initiated", $_POST);

    $gameId   = trim($_POST['gameId'] ?? '');
    $gameName = trim($_POST['gameName'] ?? '');
    $slot     = trim($_POST['slot'] ?? 'No slot');
    $eventId  = trim($_POST['eventId'] ?? 0);
    $guests   = 1;
    $priceStr = trim($_POST['price'] ?? '0');

    $additionalGuest      = intval($_POST['additional_guest'] ?? 0);
    $perGuestPrice        = floatval($_POST['per_guest_price'] ?? 0);
    $totalAdditionalPrice = floatval($_POST['total_additional_price'] ?? 0);

    // Slot date normalize
    $slotDate = $slot;
    if (preg_match('/^\d{4}-\d{2}-\d{2}T/', $slot)) {
        $slotDate = substr($slot, 0, 10);
    }

    // Prevent duplicate entry for same date & game
    foreach ($_SESSION['cart'] as $item) {
        $existingSlot = $item['slot'];
        $existingDate = $existingSlot;

        if (preg_match('/^\d{4}-\d{2}-\d{2}T/', $existingSlot)) {
            $existingDate = substr($existingSlot, 0, 10);
        }

        if ($item['eventId'] === $eventId && $existingDate === $slotDate) {
            log_msg("Duplicate check failed: Item already in cart", ['eventId' => $eventId, 'slotDate' => $slotDate]);
            echo json_encode([
                'status'  => 'error',
                'message' => 'This game is already in your cart for the same date.'
            ]);
            exit;
        }
    }

    // Add item to session cart
    $cartItem = [
        'gameId'   => $gameId,
        'eventId'  => $eventId,
        'gameName' => $gameName,
        'slot'     => $slot,
        'guests'   => $guests,
        'price'    => $priceStr,
        'total'    => $priceStr,
        'cat'      => 'party-package',
        'additional_guest'      => $additionalGuest,
        'per_guest_price'       => $perGuestPrice,
        'total_additional_price'=> $totalAdditionalPrice
    ];
    $_SESSION['cart'][] = $cartItem;

    // Insert into tbl_carts
    try {
        $stmt = $pdo->prepare("
            INSERT INTO tbl_carts 
            (session_id, game_id, event_id, game_name, slot, guests, price, total, created_at, cat, additional_guest, per_guest_price, total_additional_price) 
            VALUES (:sid, :game_id, :event_id, :game_name, :slot, :guests, :price, :total, NOW(), :cat, :additional_guest, :per_guest_price, :total_additional_price)
        ");
        $stmt->execute([
            ':sid'       => session_id(),
            ':game_id'   => $gameId,
            ':event_id'  => $eventId,
            ':game_name' => $gameName,
            ':slot'      => $slot,
            ':guests'    => $guests,
            ':price'     => $priceStr,
            ':total'     => $priceStr,
            ':cat'       => 'party-package',
            ':additional_guest'      => $additionalGuest,
            ':per_guest_price'       => $perGuestPrice,
            ':total_additional_price'=> $totalAdditionalPrice
        ]);
        log_msg("Database insert into tbl_carts successful");
    } catch (Exception $e) {
        log_msg("Database Error (tbl_carts)", $e->getMessage());
    }

    /* ----------------------------------------------------------
        HOLD API MUST RUN ONLY FOR CURRENT POSTED GAME
       ---------------------------------------------------------- */

    $sid = session_id();

    // Step 1: Delete previous hold for this same game
    $stmt = $pdo->prepare("
        SELECT response_json FROM tbl_bookeo_holds
        WHERE session_id = :sid AND event_id = :event_id
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([':sid' => $sid, ':event_id' => $eventId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $old = json_decode($row['response_json'], true);
        $holdId = $old['id'] ?? "";

        if ($holdId != "") {
            log_msg("Attempting to DELETE previous hold: $holdId");
            $url = "https://api.bookeo.com/v2/holds/{$holdId}?apiKey={$apiKey}&secretKey={$secretKey}";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $delResp = curl_exec($ch);
            $delHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            log_msg("DELETE Hold Response [$delHttp]", $delResp);

            $delStmt = $pdo->prepare("
                DELETE FROM tbl_bookeo_holds
                WHERE session_id = :sid AND event_id = :event_id
            ");
            $delStmt->execute([':sid' => $sid, ':event_id' => $eventId]);
        }
    }

    // Step 2: NEW Hold Payload
    $payload = [
        "eventId" => $eventId,
        "customer" => [
            "firstName" => "Test",
            "lastName"  => "User",
            "emailAddress" => "test@example.com",
            "phoneNumbers" => [["number" => "1234567890", "type" => "mobile"]]
        ],
        "participants" => [
            "numbers" => [
                ["peopleCategoryId" => "Cadults", "number" => 1]
            ]
        ],
        "productId" => $gameId
    ];

    log_msg("Preparing NEW Hold Payload", $payload);

    // Step 3: CURL → Create new hold
    $ch = curl_init("https://api.bookeo.com/v2/holds?holdDurationSeconds=".(CART_TIMER_MINUTES*60));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "X-Bookeo-apiKey: $apiKey",
        "X-Bookeo-secretKey: $secretKey"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch); // Capture connection errors
    curl_close($ch);

    log_msg("Bookeo API Response Received", [
        'HTTP_CODE' => $httpCode,
        'CURL_ERROR' => $curlErr,
        'RAW_RESPONSE' => $response
    ]);

    $res = json_decode($response, true);

    // Step 4: Check if hold failed
    if (!in_array($httpCode, [200, 201])) {
        
        log_msg("HOLD FAILED. Rolling back cart.", $res);

        // Remove from SESSION
        foreach ($_SESSION['cart'] as $k => $c) {
            if ($c['gameId'] == $gameId) unset($_SESSION['cart'][$k]);
        }

        // Remove from DB
        $pdo->prepare("DELETE FROM tbl_carts WHERE session_id=? AND event_id=?")
            ->execute([$sid, $eventId]);

        echo json_encode([
            'status' => 'slot_error',
            'message' => 'Failed to reserve slot. Please try again.',
            'http' => $httpCode,
            'bookeo' => $res,
            'debug_error' => $curlErr ? $curlErr : 'API Error'
        ]);
        exit;
    }

    // Step 5: INSERT/UPDATE tbl_bookeo_holds
    try {
        $stmtChk = $pdo->prepare("
            SELECT id FROM tbl_bookeo_holds
            WHERE session_id=:sid AND event_id=:eid
            LIMIT 1
        ");
        $stmtChk->execute([':sid'=>$sid, ':eid'=>$eventId]);
        $existing = $stmtChk->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $pdo->prepare("
                UPDATE tbl_bookeo_holds 
                SET response_json=:json, updated_at=NOW()
                WHERE id=:id
            ")->execute([
                ':json' => json_encode($res),
                ':id'   => $existing['id']
            ]);
        } else {
            $pdo->prepare("
                INSERT INTO tbl_bookeo_holds (session_id,event_id,game_id,response_json,created_at)
                VALUES (:sid,:eid,:gid,:json,NOW())
            ")->execute([
                ':sid'=>$sid,
                ':eid'=>$eventId,
                ':gid'=>$gameId,
                ':json'=>json_encode($res)
            ]);
        }
        log_msg("Hold saved to DB successfully.");
    } catch (Exception $e) {
        log_msg("DB Error saving hold", $e->getMessage());
    }

    // FINAL RESPONSE
    echo json_encode([
        'status' => 'success',
        'message' => 'Cart updated and hold created.',
        'cart' => array_values($_SESSION['cart']),
        'bookeo' => $res
    ]);
    exit;
}
?>