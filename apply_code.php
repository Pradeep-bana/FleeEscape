<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("admin/db.php");
require_once('config.php');
require_once(__DIR__ . '/includes/bookeo_runtime.php');

// ==================================================================
// NEW: Global Throttling Logic
// ==================================================================
if (!function_exists('flee_apply_is_throttled')) {
    function flee_apply_is_throttled()
    {
        return flee_bookeo_is_throttled();
    }
}

if (!function_exists('flee_apply_set_throttle')) {
    function flee_apply_set_throttle($retryAfterSeconds)
    {
        flee_bookeo_set_throttle($retryAfterSeconds, 'apply_code_throttle');
        flee_apply_write_log(
            "GLOBAL THROTTLE ENGAGED",
            "Bookeo API calls are suspended for " . ($retryAfterSeconds + 2) . " seconds."
        );
    }
}

// ==================================================================

if (!function_exists('flee_apply_write_log')) {
    // (Your existing function, unchanged)
    function flee_apply_write_log($context, $message)
    {
        flee_system_log_message('apply_code_' . strtolower(str_replace(' ', '_', $context)), $message);
    }
}

if (!function_exists('callBookeoHold')) {
    function callBookeoHold($payload, $apiKey, $secretKey)
    {
        // NEW/MODIFIED: Check for throttle before making the call
        if (flee_apply_is_throttled()) {
            $waitSeconds = max(1, flee_bookeo_retry_after_seconds());
            $errorMsg = "The booking system is currently busy. Please try again in {$waitSeconds} seconds.";
            flee_apply_write_log("callBookeoHold - SKIPPED", "API is globally throttled.");
            return ['code' => 429, 'data' => ['error' => $errorMsg]];
        }

        $url = "https://api.bookeo.com/v2/holds?holdDurationSeconds=" . (CART_TIMER_MINUTES * 60);

        $apiResponse = flee_bookeo_request('POST', $url, [
            'context' => 'apply_code_hold_create',
            'timeout' => 15,
            'headers' => [
                "Content-Type: application/json",
                "X-Bookeo-apiKey: $apiKey",
                "X-Bookeo-secretKey: $secretKey"
            ],
            'body' => json_encode($payload),
            'log_body' => true,
        ]);
        $response = $apiResponse['body'];
        $httpCode = $apiResponse['code'];
        $curlError = $apiResponse['error'];

        if ($response === false || $httpCode === 0) {
            flee_apply_write_log(
                "callBookeoHold - NETWORK ERROR",
                "cURL Error: $curlError | Payload: " . json_encode($payload)
            );
            return ['code' => 500, 'data' => ['error' => 'Network error connecting to Bookeo']];
        }

        // NEW/MODIFIED: React to 429 error
        if ($httpCode === 429) {
            $responseData = json_decode($response, true);
            $retryAfter = (int)($responseData['retryAfter'] ?? 60); // Default to 60s
            flee_apply_set_throttle($retryAfter);
            flee_apply_write_log(
                "callBookeoHold - THROTTLED",
                "HTTP 429 received. API locked for {$retryAfter} seconds. Response: $response"
            );
            $errorMsg = "The booking system is currently busy. Please try again in {$retryAfter} seconds.";
            return ['code' => 429, 'data' => ['error' => $errorMsg]];
        }

        // if ($httpCode !== 201) {
        //     flee_apply_write_log(
        //         "callBookeoHold - API ERROR",
        //         "HTTP $httpCode | Payload: " . json_encode($payload) . " | Response: $response"
        //     );
        // }

        return ['code' => $httpCode, 'data' => json_decode($response, true)];
    }
}


if (!function_exists('deleteBookeoHold')) {
    function deleteBookeoHold($holdId, $apiKey, $secretKey)
    {
        if (!$holdId) {
            return;
        }

        // NEW/MODIFIED: Check for throttle before making the call
        if (flee_apply_is_throttled()) {
            flee_apply_write_log("deleteBookeoHold - SKIPPED", "Hold ID: $holdId | API is globally throttled.");
            return; // Just stop, don't make the call.
        }

        $url = "https://api.bookeo.com/v2/holds/{$holdId}";
        $apiResponse = flee_bookeo_request('DELETE', $url, [
            'context' => 'apply_code_hold_delete',
            'timeout' => 15,
            'headers' => [
                "X-Bookeo-apiKey: $apiKey",
                "X-Bookeo-secretKey: $secretKey"
            ],
        ]);
        $response = $apiResponse['body'];
        $httpCode = $apiResponse['code'];
        $curlError = $apiResponse['error'];

        if ($response === false || $httpCode === 0) {
            flee_apply_write_log("deleteBookeoHold - NETWORK ERROR", "Hold ID: $holdId | cURL Error: $curlError");
            return;
        }

        // NEW/MODIFIED: React to 429 error
        if ($httpCode === 429) {
            $responseData = json_decode($response, true);
            $retryAfter = (int)($responseData['retryAfter'] ?? 60); // Default to 60s
            flee_apply_set_throttle($retryAfter);
            flee_apply_write_log(
                "deleteBookeoHold - THROTTLED",
                "Hold ID: $holdId | HTTP 429 received. API locked for {$retryAfter} seconds."
            );
        } elseif ($httpCode < 200 || $httpCode > 299) {
            flee_apply_write_log("deleteBookeoHold - API ERROR", "Hold ID: $holdId | HTTP $httpCode | Response: $response");
        }
    }
}

if (!function_exists('flee_apply_touch_session_cart_rows')) {
    function flee_apply_touch_session_cart_rows(PDO $pdo, $sid)
    {
        $stmt = $pdo->prepare("UPDATE tbl_carts SET created_at = NOW() WHERE session_id = :sid");
        $stmt->execute([':sid' => $sid]);
    }
}

if (!function_exists('flee_apply_slot_date')) {
    function flee_apply_slot_date($slot)
    {
        $slot = trim((string)$slot);
        return $slot === '' ? null : substr($slot, 0, 10);
    }
}

if (!function_exists('flee_apply_session_value')) {
    function flee_apply_session_value(array $row)
    {
        return [
            'id' => (int)($row['id'] ?? 0),
            'gameId' => (string)($row['game_id'] ?? ''),
            'eventId' => (string)($row['event_id'] ?? ''),
            'gameName' => (string)($row['game_name'] ?? ''),
            'slot' => (string)($row['slot'] ?? ''),
            'guests' => (int)($row['guests'] ?? 0),
            'price' => (float)($row['price'] ?? 0),
            'total' => (float)($row['total'] ?? 0),
            'cat' => (string)($row['cat'] ?? ''),
            'pramotion_page' => (string)($row['pramotion_page'] ?? 'false'),
            'promo_code' => (string)($row['promo_code'] ?? ''),
            'dataAvailable' => (string)($row['dataAvailable'] ?? '0'),
            'addon_name' => (string)($row['addon_name'] ?? ''),
            'addon_qty' => (int)($row['addon_qty'] ?? 0),
            'addon_price' => (float)($row['addon_price'] ?? 0),
            'addon_subtotal' => (float)($row['addon_subtotal'] ?? 0),
            'addon_opt_id' => (string)($row['addon_opt_id'] ?? ''),
            'escape_selection' => (string)($row['escape_selection'] ?? ''),
            'additional_guest' => (int)($row['additional_guest'] ?? 0),
            'per_guest_price' => (float)($row['per_guest_price'] ?? 0),
            'total_additional_price' => (float)($row['total_additional_price'] ?? 0),
            'discount_amt' => (float)($row['discount_amt'] ?? 0),
            'discounted_total' => (float)($row['discounted_total'] ?? 0),
        ];
    }
}

if (!function_exists('flee_apply_sync_session_cart')) {
    function flee_apply_sync_session_cart(PDO $pdo, $sid)
    {
        $stmt = $pdo->prepare("SELECT * FROM tbl_carts WHERE session_id = :sid ORDER BY id ASC");
        $stmt->execute([':sid' => $sid]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $_SESSION['cart'] = array_map('flee_apply_session_value', $rows);
        return $rows;
    }
}

if (!function_exists('flee_apply_is_promotion_page_flag')) {
    function flee_apply_is_promotion_page_flag($flag)
    {
        $flag = strtolower(trim((string)$flag));
        return in_array($flag, ['true', '1'], true);
    }
}

if (!function_exists('flee_apply_resolve_active_promo')) {
    function flee_apply_resolve_active_promo(array $cartItems)
    {
        // Skip auto-promo detection when user explicitly removes promo
        if (!empty($_SESSION['skip_auto_promo'])) {
            unset($_SESSION['skip_auto_promo']);
            return [null, false];
        }

        $escapeCount = 0;
        $hasPromotionPage = false;
        $promotionPagePromo = null;
        $activePromoCode = null;
        $promoIsLocked = false;

        foreach ($cartItems as $item) {
            if (strpos(strtolower((string)($item['cat'] ?? '')), 'escape-room') !== false) {
                $escapeCount++;
            }

            if (flee_apply_is_promotion_page_flag($item['pramotion_page'] ?? '')) {
                $hasPromotionPage = true;
                $itemPromo = trim((string)($item['promo_code'] ?? ''));
                if ($itemPromo !== '' && $promotionPagePromo === null) {
                    $promotionPagePromo = $itemPromo;
                }
            }
        }

        if ($promotionPagePromo !== null) {
            $activePromoCode = $promotionPagePromo;
            $promoIsLocked = true;
        } elseif ($escapeCount >= 3) {
            $activePromoCode = "BMSM_20";
            $promoIsLocked = true;
        } elseif ($escapeCount == 2) {
            $activePromoCode = "BMSM_10";
            $promoIsLocked = true;
        } elseif ($hasPromotionPage) {
            $promoIsLocked = true;
        }

        if (!$activePromoCode) {
            foreach ($cartItems as $item) {
                $itemPromo = trim((string)($item['promo_code'] ?? ''));
                if ($itemPromo !== '' && stripos($itemPromo, 'BMSM') !== false) {
                    $activePromoCode = $itemPromo;
                    $promoIsLocked = true;
                    break;
                }
            }
        }

        return [$activePromoCode, $promoIsLocked];
    }
}

if (!function_exists('flee_apply_build_options')) {
    function flee_apply_build_options(array $item)
    {
        $options = [];
        $gameId = (string)($item['game_id'] ?? '');

        if (!empty($item['escape_selection'])) {
            $skipEscapeChoiceGames = ['41551LAM3LY18570132661'];
            if (!in_array($gameId, $skipEscapeChoiceGames, true)) {
                $options[] = ["name" => "Escape Room Choices", "value" => $item['escape_selection']];
            }
        }

        if ((int)($item['addon_qty'] ?? 0) > 0) {
            $val = (strtolower((string)($item['cat'] ?? '')) === 'party-package')
                ? 'true'
                : (string)$item['addon_qty'];

            if (!empty($item['addon_opt_id'])) {
                $options[] = ["id" => $item['addon_opt_id'], "value" => $val];
            } elseif (!empty($item['addon_name'])) {
                $options[] = ["name" => $item['addon_name'], "value" => $val];
            }
        }

        if ((float)($item['total_additional_price'] ?? 0) > 0) {
            $addGuests = (int)($item['additional_guest'] ?? 0);
            if ($addGuests <= 0) {
                $addGuests = 1;
            }
            $options[] = ["name" => "Additional Guests", "value" => (string)$addGuests];
        }

        return $options;
    }
}

if (!function_exists('flee_apply_update_cart_row')) {
    function flee_apply_update_cart_row(PDO $pdo, array $item, $activePromoCode, array $finalData)
    {
        $eventId = (string)($item['event_id'] ?? '');
        $sid = session_id();
        $bookeoPromoAmount = (float)($finalData['appliedPromotionDiscount']['amount'] ?? 0);
        $promoApplicable = !empty($finalData['promotionApplicable']) || $bookeoPromoAmount > 0;
        $currentFlag = strtolower(trim((string)($item['pramotion_page'] ?? 'false')));

        $nextFlag = 'false';
        if ($promoApplicable && $activePromoCode) {
            if (stripos($activePromoCode, 'BMSM') !== false) {
                $nextFlag = 'save_more_play_more';
            } elseif (in_array($currentFlag, ['true', '1'], true)) {
                $nextFlag = 'true';
            } else {
                $nextFlag = 'user-input';
            }
        } elseif (in_array($currentFlag, ['true', '1'], true)) {
            $nextFlag = 'true';
        }

        $discountedTotal = (float)($finalData['price']['totalNet']['amount'] ?? ($item['total'] ?? 0));
        $promoToStore = $promoApplicable ? (string)$activePromoCode : null;

        $stmt = $pdo->prepare("
            UPDATE tbl_carts
            SET pramotion_page = :page,
                promo_code = :promo,
                discount_amt = :discount,
                discounted_total = :discounted_total,
                created_at = NOW()
            WHERE session_id = :sid AND event_id = :eid
        ");
        $stmt->execute([
            ':page' => $nextFlag,
            ':promo' => $promoToStore,
            ':discount' => $promoApplicable ? $bookeoPromoAmount : 0,
            ':discounted_total' => $discountedTotal,
            ':sid' => $sid,
            ':eid' => $eventId,
        ]);
    }
}

if (!function_exists('run_apply_code')) {
    function run_apply_code(string $inputCode, PDO $pdo): array
    {
        $apiKey = FLEE_BOOKEO_API_KEY;
        $secretKey = FLEE_BOOKEO_SECRET_KEY;
        $sid = session_id();

        // 1. Parse user codes & LIMIT TO 5 CODES MAX
        $rawCodes = array_values(array_filter(array_map('trim', explode(',', $inputCode))));
        $userCodes = array_slice($rawCodes, 0, 5); // STRICT: Ignore anything past 5 codes

        $cartItems = flee_apply_sync_session_cart($pdo, $sid);
        if (!$cartItems) {
            unset($_SESSION['giftCode']);
            return ['status' => 'error', 'message' => 'Cart is empty', 'valid_code' => '', 'isHoldRefreshed' => false];
        }

        flee_apply_touch_session_cart_rows($pdo, $sid);

        usort($cartItems, function ($a, $b) {
            $priceA = ((float)$a['price']) * ((int)$a['guests']);
            $priceB = ((float)$b['price']) * ((int)$b['guests']);
            return $priceB <=> $priceA;
        });

        // 2. Pre-flight: Separate Promos and Vouchers
        [$activePromoCode, $promoIsLocked] = flee_apply_resolve_active_promo($cartItems);
        
        $promoToApply = $activePromoCode;
        $voucherPool = []; 

        if ($activePromoCode) {
            foreach ($userCodes as $code) {
                if (strcasecmp($code, $activePromoCode) !== 0) {
                    $voucherPool[] = $code;
                }
            }
        } else {
            $voucherPool = $userCodes;
        }

        $itemsUpdated = 0;
        $holdsCreatedInThisRun = [];

        // 3. Process Cart Items
        foreach ($cartItems as $index => $item) {
            $gameId = (string)($item['game_id'] ?? '');
            $eventId = (string)($item['event_id'] ?? '');
            $qty = (int)($item['guests'] ?? 0);

            if ($gameId === '' || $eventId === '' || $qty <= 0) continue;

            // --- Call 1: Delete Old Hold from Database ---
            $excludeSql = "";
            if (!empty($holdsCreatedInThisRun)) {
                $excludeSql = " AND id NOT IN (" . implode(',', array_map('intval', $holdsCreatedInThisRun)) . ") ";
            }

            $stmtHold = $pdo->prepare("
                SELECT id, response_json FROM tbl_bookeo_holds
                WHERE session_id = :sid AND event_id = :event_id {$excludeSql}
                ORDER BY id DESC LIMIT 1
            ");
            $stmtHold->execute([':sid' => $sid, ':event_id' => $eventId]);
            $oldHoldRow = $stmtHold->fetch(PDO::FETCH_ASSOC);

            if ($oldHoldRow) {
                $oldJson = json_decode($oldHoldRow['response_json'], true);
                if (!empty($oldJson['id'])) {
                    deleteBookeoHold($oldJson['id'], $apiKey, $secretKey);
                }
                $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE id = :id")->execute([':id' => $oldHoldRow['id']]);
            }

            // --- Prepare Payload ---
            $payload = [
                "eventId" => $eventId,
                "customer" => ["firstName" => "Temp", "lastName" => "User", "emailAddress" => "temp@example.com"],
                "participants" => ["numbers" => [ ["peopleCategoryId" => "Cadults", "number" => $qty] ]],
                "productId" => $gameId
            ];

            $options = flee_apply_build_options($item);
            if (!empty($options)) $payload['options'] = $options;
            
            // Assume the first user code is a promo if we don't already have one
            $guessedPromo = null;
            if ($promoToApply) {
                $payload['promotionCodeInput'] = $promoToApply;
            } elseif (!empty($voucherPool)) {
                $guessedPromo = array_shift($voucherPool);
                $payload['promotionCodeInput'] = $guessedPromo;
            }

            if (!empty($voucherPool)) {
                $payload['giftVoucherCodeInput'] = implode(',', $voucherPool);
            }

            // --- Call 2: Test Codes ---
            usleep(250000); 
            $res = callBookeoHold($payload, $apiKey, $secretKey);

            // Handle 400 Error (Wrong Guess)
            if ($res['code'] === 400 && $guessedPromo) {
                // The first code wasn't a promo. Move it to the voucher list and try one more time.
                unset($payload['promotionCodeInput']);
                array_unshift($voucherPool, $guessedPromo);
                $payload['giftVoucherCodeInput'] = implode(',', $voucherPool);
                
                usleep(250000);
                $res = callBookeoHold($payload, $apiKey, $secretKey); // Call 3
            }

            // --- STRICT ABORT ---
            if ($res['code'] !== 201) {
                // If it fails again, the user typed a truly invalid code. Restore clean cart immediately.
                usleep(250000);
                $cleanPayload = [
                    "eventId" => $eventId,
                    "customer" => ["firstName" => "Temp", "lastName" => "User", "emailAddress" => "temp@example.com"],
                    "participants" => ["numbers" => [ ["peopleCategoryId" => "Cadults", "number" => $qty] ]],
                    "productId" => $gameId
                ];
                if (!empty($options)) $cleanPayload['options'] = $options;
                if ($activePromoCode) $cleanPayload['promotionCodeInput'] = $activePromoCode; // Keep auto-promos
                
                $cleanRes = callBookeoHold($cleanPayload, $apiKey, $secretKey); // Restore Call
                
                if ($cleanRes['code'] === 201) {
                    $cleanData = $cleanRes['data'];
                    $cleanData['_internal_promo'] = $activePromoCode;
                    $cleanData['_internal_vouchers'] = null;
                    
                    $stmtInsert = $pdo->prepare("INSERT INTO tbl_bookeo_holds (session_id, event_id, game_id, response_json, created_at) VALUES (:sid, :eid, :gid, :json, NOW())");
                    $stmtInsert->execute([':sid' => $sid, ':eid' => $eventId, ':gid' => $gameId, ':json' => json_encode($cleanData)]);
                    flee_apply_update_cart_row($pdo, $item, $activePromoCode, $cleanData);
                }
                
                unset($_SESSION['giftCode']);
                flee_apply_sync_session_cart($pdo, $sid);
                
                $errorMsg = ($res['code'] === 429) ? 'System Busy - Please wait a moment' : 'Invalid promo/voucher code';
                return [
                    'status' => 'error',
                    'message' => $errorMsg,
                    'valid_code' => '',
                    'isHoldRefreshed' => false
                ];
            }

            // --- SUCCESS ---
            $finalData = $res['data'];
            
            // Figure out what actually worked for our records
            $appliedPromo = $payload['promotionCodeInput'] ?? null;
            $appliedVouchers = $payload['giftVoucherCodeInput'] ?? null;
            
            $finalData['_internal_promo'] = $appliedPromo;
            $finalData['_internal_vouchers'] = $appliedVouchers;

            $stmtInsert = $pdo->prepare("
                INSERT INTO tbl_bookeo_holds (session_id, event_id, game_id, response_json, created_at)
                VALUES (:sid, :eid, :gid, :json, NOW())
            ");
            $stmtInsert->execute([
                ':sid' => $sid, ':eid' => $eventId, ':gid' => $gameId, ':json' => json_encode($finalData)
            ]);
            $holdsCreatedInThisRun[] = (int)$pdo->lastInsertId();

            flee_apply_update_cart_row($pdo, $item, $appliedPromo, $finalData);
            $itemsUpdated++;
            
            // ANTI-DOUBLE DIP LOGIC:
            // Since we applied all valid vouchers to the first game, Bookeo has logged the discount.
            // Empty the pool so we don't send the exact same vouchers to Game #2, which prevents the double-dip bug!
            $voucherPool = [];
            $promoToApply = $activePromoCode; // Reset promo guess
        }

        flee_apply_sync_session_cart($pdo, $sid);

        $cleanCodeString = implode(',', $userCodes); 
        if ($cleanCodeString !== '') {
            $_SESSION['giftCode'] = $cleanCodeString;
        } else {
            unset($_SESSION['giftCode']);
        }

        if ($itemsUpdated === count($cartItems)) {
            return [
                'status' => 'success',
                'message' => $cleanCodeString !== '' ? 'Codes applied successfully' : 'Hold refreshed successfully',
                'valid_code' => $cleanCodeString,
                'isHoldRefreshed' => true
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Could not complete holds for all items.',
            'valid_code' => '',
            'isHoldRefreshed' => false
        ];
    }
}

if (!defined('FLEE_APPLY_CODE_LIBRARY')) {
    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
        exit;
    }

    echo json_encode(run_apply_code(trim($_POST['code'] ?? ''), $pdo));
    exit;
}
