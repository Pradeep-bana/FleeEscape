<?php
session_start();
include("admin/db.php");
header("Content-Type: application/json");

$apiKey = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
$secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";
$logFile = __DIR__ . "/bookeo_hold_debug.log";

function log_debug($msg) {
    global $logFile;
    file_put_contents($logFile, date("c") . " | " . $msg . "\n", FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'POST required']);
    exit;
}

if (!isset($_POST['index']) || !is_numeric($_POST['index'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid index']);
    exit;
}

$index = (int)$_POST['index'];

if (!isset($_SESSION['cart'][$index])) {
    $sid = session_id();
    $stmt = $pdo->prepare("DELETE FROM tbl_carts WHERE session_id = :sid");
    $stmt->execute([':sid' => $sid]);
    echo json_encode(['status' => 'error', 'message' => 'Cart item not found']);
    exit;
}

$item = $_SESSION['cart'][$index];
$eventIdToRemove = $item['eventId'] ?? $item['event_id'] ?? null;
$gameIdToRemove  = $item['gameId']  ?? $item['game_id']  ?? null;

// Extract just the date portion from the slot string (e.g. "2025-03-15 17:00:00" → "2025-03-15")
$rawSlot      = $item['slot'] ?? '';
$slotDateOnly = substr($rawSlot, 0, 10); // always "YYYY-MM-DD"

if (!$eventIdToRemove) {
    echo json_encode(['status' => 'error', 'message' => 'Event ID missing']);
    exit;
}

$sid = session_id();

try {

    $pdo->beginTransaction();

    /*--------------------------------------------------------------
     STEP 1: DELETE PREVIOUS HOLD FOR THE ITEM BEING REMOVED
    --------------------------------------------------------------*/
    $stmt = $pdo->prepare("
        SELECT id, response_json 
        FROM tbl_bookeo_holds 
        WHERE session_id = :sid AND event_id = :event 
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([':sid' => $sid, ':event' => $eventIdToRemove]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $oldHold = json_decode($row['response_json'], true);
        $oldHoldId = $oldHold['id'] ?? null;

        if ($oldHoldId) {
            $ch = curl_init("https://api.bookeo.com/v2/holds/" . urlencode($oldHoldId));
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST => "DELETE",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "X-Bookeo-apiKey: $apiKey",
                    "X-Bookeo-secretKey: $secretKey"
                ]
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            log_debug("DELETE (remove-item) HOLD: $oldHoldId HTTP=$code");

            /*--------------------------------------------------------------
             HARD-DELETE ALL CACHE ROWS for this product_id + slot_date.
             Deleting only the event_id row leaves other slots in cache,
             keeping slot_count > 0 → status stays 'fresh' → no re-fetch.
             Wiping the whole product+date forces slot_count = 0 → 'missing'
             → blocking Bookeo re-fetch → instant correct availability.
            --------------------------------------------------------------*/
            if ($gameIdToRemove && $slotDateOnly) {
                $pdo->prepare("
                    DELETE FROM bookeo_slots_cache
                    WHERE product_id = :pid AND slot_date = :sdate
                ")->execute([':pid' => $gameIdToRemove, ':sdate' => $slotDateOnly]);
                log_debug("CACHE HARD-DELETED product_id=$gameIdToRemove slot_date=$slotDateOnly");
            }
        }
    }

    /*--------------------------------------------------------------
     STEP 2: REMOVE ITEM FROM SESSION + DB
    --------------------------------------------------------------*/
    unset($_SESSION['cart'][$index]);
    unset($_SESSION['giftCode']);
    $_SESSION['cart'] = array_values($_SESSION['cart']);

    $pdo->prepare("DELETE FROM tbl_carts WHERE session_id=:sid AND event_id=:eid")
        ->execute([':sid' => $sid, ':eid' => $eventIdToRemove]);

    $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE session_id=:sid AND event_id=:eid")
        ->execute([':sid' => $sid, ':eid' => $eventIdToRemove]);

    /*--------------------------------------------------------------
     STEP 3: FETCH REMAINING ESCAPE ROOM ITEMS
    --------------------------------------------------------------*/
    $stmt = $pdo->prepare("SELECT * FROM tbl_carts WHERE session_id=:sid");
    $stmt->execute([':sid' => $sid]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $escapeItems = [];
   foreach ($rows as $r) {
    if (
        str_contains(strtolower($r['cat']), 'escape-room') 
        && (str_contains($r['pramotion_page'], 'false') 
        || str_contains($r['pramotion_page'], 'save_more_play_more'))
    ) {
        $escapeItems[] = $r;
    }
}

    $escapeCount = count($escapeItems);

    /*------------------------------------------
     STEP 4: Decide promo
    -------------------------------------------*/
    if ($escapeCount == 0) {
        unset($_SESSION['giftCode']);
        $pdo->commit();
        echo json_encode([
            'status'=>'success',
            'message'=>'Removed. No escape items left.',
            'promo'=>'',
            'cartCount'=>count($rows),
            'escapeCount'=>0
        ]);
        exit;
    }

    $promoCode = "";
    if ($escapeCount == 2) $promoCode = "BMSM_10";
    else if ($escapeCount >= 3) $promoCode = "BMSM_20";

    /*--------------------------------------------------------------
     STEP 5: RE-HOLD EACH ITEM → BUT FIRST DELETE ITS OLD HOLD
    --------------------------------------------------------------*/
    $allResponses = [];
    $holdFailed = false;
    $failureMessage = "";

    foreach ($escapeItems as $e) {

        $eventId = $e['event_id'];
        $gameId  = $e['game_id'];
        $guests  = (int)$e['guests'];
        $total   = (float)$e['total'];

        /*------------------------------------------
         DELETE OLD HOLD ⬅️ (important new change)
        -------------------------------------------*/
        $stmt = $pdo->prepare("
            SELECT response_json 
            FROM tbl_bookeo_holds 
            WHERE session_id=:sid AND event_id=:eid 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([':sid'=>$sid, ':eid'=>$eventId]);
        $h = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($h) {
            $hJson = json_decode($h['response_json'], true);
            $oldHoldId = $hJson['id'] ?? null;

            if ($oldHoldId) {
                $ch = curl_init("https://api.bookeo.com/v2/holds/" . urlencode($oldHoldId));
                curl_setopt_array($ch, [
                    CURLOPT_CUSTOMREQUEST => "DELETE",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        "X-Bookeo-apiKey: $apiKey",
                        "X-Bookeo-secretKey: $secretKey"
                    ]
                ]);
                $resp = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                log_debug("DELETE (re-hold) OLD HOLD: $oldHoldId HTTP=$code");
            }
        }

        /*------------------------------------------
         CREATE NEW HOLD
        -------------------------------------------*/
        $payload = [
            "eventId" => $eventId,
            "productId" => $gameId,
            "customer" => [
                "firstName" => "Test",
                "lastName" => "User",
                "emailAddress" => "test@example.com",
                "phoneNumbers" => [["number"=>"1234567890","type"=>"mobile"]]
            ],
            "participants" => [
                "numbers" => [
                    ["peopleCategoryId"=>"Cadults","number"=>$guests]
                ]
            ]
        ];

        if ($promoCode) {
            $payload["promotionCodeInput"] = $promoCode;
        }

        $ch = curl_init("https://api.bookeo.com/v2/holds");
        curl_setopt_array($ch, [
            CURLOPT_POST=>true,
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_HTTPHEADER=>[
                "Content-Type: application/json",
                "X-Bookeo-apiKey: $apiKey",
                "X-Bookeo-secretKey: $secretKey"
            ],
            CURLOPT_POSTFIELDS=>json_encode($payload)
        ]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $rData = json_decode($resp,true);

        log_debug("RECREATE HOLD new HTTP=$httpCode, event=$eventId, resp=".substr($resp,0,500));

        if ($httpCode != 200 && $httpCode != 201) {
            $holdFailed = true;
            $failureMessage = $rData['message'] ?? "Hold failed";
            break;
        }

        /*------------------------------------------
         SAVE NEW HOLD RECORD
        -------------------------------------------*/
     $pdo->prepare("
                UPDATE tbl_bookeo_holds
                SET response_json=:response_json
                WHERE session_id=:sid AND event_id=:eid
            ")->execute([
                ':response_json'=>json_encode($rData), ':sid'=>$sid,':eid'=>$eventId
            ]);
        

        /*------------------------------------------
         APPLY PROMO IF VALID
        -------------------------------------------*/
        if (!empty($rData['promotionApplicable'])) {
            $discount = (float)($rData['appliedPromotionDiscount']['amount'] ?? 0);
            $newTotal = max(0, ($total - $discount));

            $pdo->prepare("
                UPDATE tbl_carts
                SET pramotion_page=:pramotion_page,promo_code=:p, discount_amt=:d, discounted_total=:t
                WHERE session_id=:sid AND event_id=:eid
            ")->execute([
                 ':pramotion_page' => 'save_more_play_more',':p'=>$promoCode,':d'=>$discount,':t'=>$newTotal,
                ':sid'=>$sid,':eid'=>$eventId
            ]);
        } else {
            $pdo->prepare("
                UPDATE tbl_carts
                SET  pramotion_page = NULL,promo_code=NULL, discount_amt=0, discounted_total=total
                WHERE session_id=:sid AND event_id=:eid
            ")->execute([':sid'=>$sid,':eid'=>$eventId]);
        }

        $allResponses[] = [
            "eventId"=>$eventId,
            "http"=>$httpCode
        ];
    }

    if ($holdFailed) {
        $pdo->commit();
        echo json_encode([
            'status'=>'error',
            'message'=>$failureMessage,
            'cartCount'=>count($rows)
        ]);
        exit;
    }

    $pdo->commit();
    echo json_encode([
        'status'=>'success',
        'message'=>'Item removed + all re-holds created successfully',
        'promo'=>$promoCode,
        'cartCount'=>count($rows),
        'escapeCount'=>$escapeCount,
        'responses'=>$allResponses
    ]);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    log_debug("EXCEPTION: ".$e->getMessage());
    echo json_encode(['status'=>'error','message'=>'Server error','detail'=>$e->getMessage()]);
    exit;
}
?>