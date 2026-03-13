<?php
session_start();
include("admin/db.php");

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

//remove slots that is more than 3 minutes
require('remove_expired_holds.php');

$apiKey = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
$secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'add_to_cart') {

    $gameId = trim($_POST['gameId'] ?? '');
    $gameName = trim($_POST['gameName'] ?? '');
    $slot = trim($_POST['slot'] ?? 'No slot');
    $eventId = trim($_POST['eventId'] ?? 0);
    $guests = (int)($_POST['guests'] ?? 0);
    $priceStr = trim($_POST['price'] ?? '0');
    $dataAvailable = trim($_POST['dataAvailable'] ?? '0');
    $cat = "escape-room";  // FIXED category

    // --- Extract only date part from slot ---
    $slotDate = $slot;
    if (preg_match('/^\d{4}-\d{2}-\d{2}T/', $slot)) {
        $slotDate = substr($slot, 0, 10);
    }

    // ------------------------------------------------------------------
    // ✅ DUPLICATE CHECK – ONLY items where `cat = escape-room`
    // ------------------------------------------------------------------
    foreach ($_SESSION['cart'] as $item) {

        if (($item['cat'] ?? '') !== 'escape-room' || $item['pramotion_page'] !== 'true') {
            continue; // skip non escape-room items
        }

        $existingSlot = $item['slot'];
        $existingDate = $existingSlot;

        if (preg_match('/^\d{4}-\d{2}-\d{2}T/', $existingSlot)) {
            $existingDate = substr($existingSlot, 0, 10);
        }

        if ($item['gameId'] === $gameId && $existingDate === $slotDate) {
            echo json_encode([
                'status' => 'error',
                'message' => 'This game is already in your cart for the same date.',
                'cart' => $_SESSION['cart']
            ]);
            exit;
        }
    }
    // ------------------------------------------------------------------

    // --- Price normalization ---
    $normalized = str_replace(["â€“", "â€”", "–", "—"], "-", $priceStr);
    $normalized = preg_replace('/\s*-\s*/', '-', $normalized);
    preg_match_all('/\d+(?:\.d+)?/', $normalized, $nums);
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

    // --- Save in session & DB ---
    $cartItem = [
        'gameId' => $gameId,
        'eventId' => $eventId,
        'gameName' => $gameName,
        'slot' => $slot,
        'guests' => $guests,
        'price' => $priceUnit,
        'total' => $total,
        'dataAvailable' => $dataAvailable,
        'cat' => $cat,
        'pramotion_page' => 'false'
    ];

    $_SESSION['cart'][] = $cartItem;

    $stmt = $pdo->prepare("
        INSERT INTO tbl_carts
        (session_id, game_id, event_id, game_name, slot, guests, price, total, created_at, cat, dataAvailable, pramotion_page)
        VALUES (:sid, :game_id, :event_id, :game_name, :slot, :guests, :price, :total, NOW(), :cat, :dataAvailable, :pramotion_page)
    ");
    $stmt->execute([
        ':sid' => session_id(),
        ':game_id' => $gameId,
        ':event_id' => $eventId,
        ':game_name' => $gameName,
        ':slot' => $slot,
        ':guests' => $guests,
        ':price' => $priceUnit,
        ':total' => $total,
        ':cat' => $cat,
        ':dataAvailable' => $dataAvailable,
        ':pramotion_page' => 'false'
    ]);

    // Decide Promo Code based on cart count — ONLY escape-room
    $escapeCount = 0;
    foreach ($_SESSION['cart'] as $ci) {
        if (($ci['cat'] ?? '') === 'escape-room' && ($ci['pramotion_page'] == 'false' ||  $ci['pramotion_page'] == 'save_more_play_more')) {
            $escapeCount++;
        }
    }

    $promoCode = "";
    if ($escapeCount == 2) {
        $promoCode = "BMSM_10";
    } elseif ($escapeCount >= 3) {
        $promoCode = "BMSM_20";
    }

    // ... [Code above remains the same] ...

    $allResponses = [];
    $sid = session_id();
    $holdFailed = false;
    $failureMessage = '';

    // --- Process ALL cart items ---
    foreach ($_SESSION['cart'] as $index => $item) {
        
        // ONLY process escape rooms that need booking/re-booking
        if (($item['cat'] ?? '') === 'escape-room' && ($item['pramotion_page'] == 'false' || $item['pramotion_page'] == 'save_more_play_more')) {
            
            $currentEventId = $item['eventId'];
            $gameName = $item['gameName'];
            $foundHoldId = null;

            // --------------------------------------------------------
            // STEP 1: FIND EXISTING HOLD IN DB
            // --------------------------------------------------------
            $stmt = $pdo->prepare("
                SELECT response_json FROM tbl_bookeo_holds
                WHERE session_id = :sid AND event_id = :event_id
                ORDER BY id DESC LIMIT 1
            ");
            $stmt->execute([':sid' => $sid, ':event_id' => $currentEventId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Debug: Check if we found the hold
            $dbLogMsg = "DB LOOKUP for ($gameName): " . ($row ? "Found Record" : "NO RECORD FOUND");
            file_put_contents(__DIR__ . "/bookeo_hold_debug.log", date("c") . " " . $dbLogMsg . "\n", FILE_APPEND);

            if ($row) {
                $data = json_decode($row['response_json'], true);
                $holdId = $data['id'] ?? null;
                $foundHoldId = $holdId;
                
                if ($holdId) {
                    // --------------------------------------------------------
                    // STEP 2: DELETE EXISTING HOLD
                    // --------------------------------------------------------
                    $url = "https://api.bookeo.com/v2/holds/{$holdId}?apiKey={$apiKey}&secretKey={$secretKey}";
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_exec($ch);
                    $delHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    file_put_contents(__DIR__ . "/bookeo_hold_debug.log", 
                        date("c") . " DELETE HOLD {$holdId}: HTTP {$delHttpCode}\n", FILE_APPEND);
                    
                    // CRITICAL: Wait 1.0 second for Bookeo to release the seat
                    usleep(1000000); 

                    // Remove from DB immediately
                    $delStmt = $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE session_id = :sid AND event_id = :event_id");
                    $delStmt->execute([':sid' => $sid, ':event_id' => $currentEventId]);
                }
            }

            // --------------------------------------------------------
            // STEP 3: CREATE NEW HOLD (RE-BOOK)
            // --------------------------------------------------------
            $cartPayload = [
                "eventId" => $currentEventId,
                "customer" => [
                    "firstName" => "Test",
                    "lastName" => "User",
                    "emailAddress" => "test@example.com",
                    "phoneNumbers" => [["number" => "1234567890", "type" => "mobile"]]
                ],
                "participants" => [
                    "numbers" => [["peopleCategoryId" => "Cadults", "number" => $item['guests']]]
                ],
                "productId" => $item['gameId']
            ];

            if ($promoCode !== "") {
                $cartPayload["promotionCodeInput"] = $promoCode;
            }

            $ch = curl_init("https://api.bookeo.com/v2/holds?holdDurationSeconds=180");
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

            file_put_contents(__DIR__ . "/bookeo_hold_debug.log",
                "--------------------------------------------------\n" .
                date("c") . " RE-BOOKING ($gameName)\n" .
                "HTTP CODE: $httpCode\n" .
                "RESPONSE: " . json_encode($bookeoData) . "\n\n",
                FILE_APPEND
            );

            // --------------------------------------------------------
            // STEP 4: ERROR HANDLING
            // --------------------------------------------------------
            if ($httpCode != 200 && $httpCode != 201) {
                $holdFailed = true;
                $failureMessage = $bookeoData['message'] ?? "Failed to reserve slot for $gameName.";
                
                // IMPORTANT: If we failed to re-book, and we successfully deleted the previous hold,
                // we have lost the seat. We must remove this item.
                
                // Remove ONLY the failed item from session
                unset($_SESSION['cart'][$index]);
                
                // Clean up DB for THIS specific event
                $delCart = $pdo->prepare("DELETE FROM tbl_carts WHERE session_id = :sid AND event_id = :event_id");
                $delCart->execute([':sid' => $sid, ':event_id' => $currentEventId]);

                $delHold = $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE session_id = :sid AND event_id = :event_id");
                $delHold->execute([':sid' => $sid, ':event_id' => $currentEventId]);

                file_put_contents(__DIR__ . "/bookeo_hold_debug.log",
                    "FAILURE CLEANUP: Removed $gameName ($currentEventId)\n", FILE_APPEND);

                // Re-index array so JSON output is clean
                $_SESSION['cart'] = array_values($_SESSION['cart']);
                
                // Break immediately to return error
                break; 
            }

            // --------------------------------------------------------
            // STEP 5: SUCCESS - SAVE NEW HOLD
            // --------------------------------------------------------
            $stmt = $pdo->prepare("
                INSERT INTO tbl_bookeo_holds (session_id, event_id, game_id, response_json, created_at)
                VALUES (:sid, :event_id, :game_id, :response_json, NOW())
                ON DUPLICATE KEY UPDATE response_json = VALUES(response_json), created_at = NOW()
            ");
            $stmt->execute([
                ':sid' => $sid,
                ':event_id' => $currentEventId,
                ':game_id' => $item['gameId'],
                ':response_json' => json_encode($bookeoData)
            ]);

            // STEP 6: APPLY PROMO DISCOUNT IN SESSION/DB
            if (!empty($bookeoData['promotionApplicable']) && $bookeoData['promotionApplicable'] === true) {
                $discountAmt = (float)($bookeoData['appliedPromotionDiscount']['amount'] ?? 0);
                $discountedTotal = max(0, $item['total'] - $discountAmt);

                $updatePromo = $pdo->prepare("
                    UPDATE tbl_carts
                    SET pramotion_page = :page,
                        promo_code = :code,
                        discount_amt = :discount,
                        discounted_total = :discounted_total
                    WHERE session_id = :sid AND event_id = :event_id
                ");
                $updatePromo->execute([
                    ':page' => 'save_more_play_more',
                    ':code' => $promoCode,
                    ':discount' => $discountAmt,
                    ':discounted_total' => $discountedTotal,
                    ':sid' => $sid,
                    ':event_id' => $currentEventId
                ]);
                
                // Update Session Reference
                $_SESSION['cart'][$index]['promo_code_cart'] = $promoCode;
                $_SESSION['cart'][$index]['pramotion_page'] = 'save_more_play_more';
            }

            $allResponses[] = [
                "gameId" => $item['gameId'],
                "eventId" => $currentEventId,
                "httpCode" => $httpCode,
                "bookeo" => $bookeoData
            ];
        }
    }

    if ($holdFailed) {
        echo json_encode([
            'status' => 'bookeo_error',
            'message' => $failureMessage,
            'cart' => array_values($_SESSION['cart']) // Return remaining cart items
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'message' => 'Cart updated.',
            'cart' => array_values($_SESSION['cart']),
            'responses' => $allResponses,
            'promo' => $promoCode
        ]);
    }
    exit;
}
?>