<?php
/**
 * apply_code_func.php
 *
 * Contains the core Bookeo hold + promo/voucher logic as a plain function.
 *
 * HOW IT IS USED:
 *   - apply_code.php  → require_once this file, call run_apply_code(), echo the result.
 *                        apply_code.php itself is NOT changed at all — it still works for
 *                        any direct JS/AJAX calls exactly as before.
 *   - cart_session.php → require_once this file, call run_apply_code() directly.
 *                        No cURL, no HTTP round-trip, no auth/SSL issues.
 *
 * WHY A FUNCTION INSTEAD OF AN INCLUDE:
 *   apply_code.php uses `exit` and `echo` at the top level.  If we tried to
 *   `include 'apply_code.php'` from cart_session.php those calls would
 *   terminate the parent script immediately.  Wrapping in a function makes the
 *   logic fully re-entrant and returns a plain array instead.
 *
 * FUNCTION SIGNATURE:
 *   run_apply_code(string $inputCode, PDO $pdo) : array
 *     $inputCode  — comma-separated promo/voucher codes (pass '' for auto-promo only)
 *     $pdo        — active PDO connection (passed in so we share the same connection)
 *   Returns an associative array identical to what apply_code.php would echo as JSON.
 */

// Guard helper functions so this file is safe to require_once alongside
// apply_code.php (e.g. in unit tests) without "Cannot redeclare" fatals.
if (!function_exists('callBookeoHold')) {
    function callBookeoHold($payload, $apiKey, $secretKey) {
        // CART_TIMER_MINUTES is defined in config.php, which is loaded before this file.
        $url = "https://api.bookeo.com/v2/holds?holdDurationSeconds=" . (CART_TIMER_MINUTES * 60);
        $ch  = curl_init($url);
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
        curl_close($ch);
        return ['code' => $httpCode, 'data' => json_decode($response, true)];
    }
}

if (!function_exists('deleteBookeoHold')) {
    function deleteBookeoHold($holdId, $apiKey, $secretKey) {
        if (!$holdId) return;
        $url = "https://api.bookeo.com/v2/holds/{$holdId}?apiKey={$apiKey}&secretKey={$secretKey}";
        $ch  = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
}

/**
 * run_apply_code()
 *
 * Mirrors the full logic of apply_code.php exactly — same phases (A/B/C),
 * same promo detection, same voucher consumption, same DB writes.
 * Returns an array instead of echo-ing JSON so callers can decide what to do.
 *
 * @param  string $inputCode  Comma-separated promo/voucher code(s), or '' for none.
 * @param  PDO    $pdo        Active database connection.
 * @return array              ['status'=>'success'|'error', 'message'=>'...', ...]
 */
function run_apply_code(string $inputCode, PDO $pdo): array
{
    // ---- CONFIG (same as apply_code.php) ----
    $apiKey    = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
    $secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";
    $sid       = session_id();

    // ---- PREPARE INPUT POOL ----
    $userCodes   = array_values(array_filter(array_map('trim', explode(',', $inputCode))));
    $voucherPool = $userCodes;

    // ---- FETCH CART ----
    $stmt = $pdo->prepare("SELECT * FROM tbl_carts WHERE session_id = :sid");
    $stmt->execute([':sid' => $sid]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$cartItems) {
        return ['status' => 'error', 'message' => 'Cart is empty'];
    }

    // Sort: Highest Price First
    usort($cartItems, function ($a, $b) {
        return ($b['price'] * $b['guests']) <=> ($a['price'] * $a['guests']);
    });

    // ---- DETERMINE AUTO PROMO ----
    $escapeCount      = 0;
    $hasPromotionPage = false;
    foreach ($cartItems as $c) {
        if (strpos(strtolower($c['cat']), 'escape-room') !== false) $escapeCount++;
        if (!empty($c['pramotion_page']) && $c['pramotion_page'] !== 'false') $hasPromotionPage = true;
    }

    $activePromoCode    = null;
    $promoIsLocked      = false;

    if ($escapeCount >= 3)     { $activePromoCode = "BMSM_20"; $promoIsLocked = true; }
    elseif ($escapeCount == 2) { $activePromoCode = "BMSM_10"; $promoIsLocked = true; }
    elseif ($hasPromotionPage) { $promoIsLocked = true; }

    // Restore BMSM from DB only when escapeCount didn't already determine it
    if (!$activePromoCode) {
        foreach ($cartItems as $c) {
            if (!empty($c['promo_code']) && strpos($c['promo_code'], 'BMSM') !== false) {
                $activePromoCode = $c['promo_code'];
                $promoIsLocked   = true;
                break;
            }
        }
    }

    // ---- PROCESSING LOOP ----
    $itemsUpdated       = 0;
    $successType        = "";
    $validUserCodes     = [];
    $promoDiscoveryDone = false;

    foreach ($cartItems as $item) {
        $gameId          = $item['game_id'];
        $eventId         = $item['event_id'];
        $qty             = (int)$item['guests'];
        $approxItemPrice = (float)$item['price'] * $qty;

        if (empty($gameId) || empty($eventId)) continue;

        // 1. DELETE OLD HOLD
        $stmtHold = $pdo->prepare("SELECT id, response_json FROM tbl_bookeo_holds WHERE session_id=:sid AND event_id=:event_id ORDER BY id DESC LIMIT 1");
        $stmtHold->execute([':sid' => $sid, ':event_id' => $eventId]);
        $oldHoldRow = $stmtHold->fetch(PDO::FETCH_ASSOC);
        if ($oldHoldRow) {
            $oldJson = json_decode($oldHoldRow['response_json'], true);
            if (isset($oldJson['id'])) deleteBookeoHold($oldJson['id'], $apiKey, $secretKey);
            $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE id=:id")->execute([':id' => $oldHoldRow['id']]);
        }

        // 2. BASE PAYLOAD
        $basePayload = [
            "eventId"      => $eventId,
            "customer"     => ["firstName" => "Temp", "lastName" => "User", "emailAddress" => "temp@example.com"],
            "participants" => ["numbers"   => [["peopleCategoryId" => "Cadults", "number" => $qty]]],
            "productId"    => $gameId
        ];

        $options = [];

        if (!empty($item['escape_selection'])) {
            $options[] = ["name" => "Escape Room Choice", "value" => $item['escape_selection']];
        }

        if ($item['addon_qty'] > 0) {
            $val = (strtolower($item['cat']) === 'party-package') ? 'true' : $item['addon_qty'];
            if (!empty($item['addon_opt_id'])) {
                $options[] = ["id"   => $item['addon_opt_id'], "value" => $val];
            } elseif (!empty($item['addon_name'])) {
                $options[] = ["name" => $item['addon_name'],   "value" => $val];
            }
        }

        if ((float)($item['total_additional_price'] ?? 0) > 0) {
            $addGuests = max(1, (int)($item['additional_guest'] ?? 0));
            $options[] = ["name" => "Additional Guests", "value" => (string)$addGuests];
        }

        if (!empty($options)) $basePayload['options'] = $options;

        // ======================================================
        // PHASE A: PROMO DISCOVERY (run once across all items)
        // ======================================================
        if (!$promoIsLocked && !$promoDiscoveryDone) {
            $promoDiscoveryDone = true;
            foreach ($voucherPool as $key => $candidateCode) {
                $testPayload                    = $basePayload;
                $testPayload['promotionCodeInput'] = $candidateCode;

                usleep(250000);
                $res     = callBookeoHold($testPayload, $apiKey, $secretKey);
                $isPromo = false;

                if ($res['code'] === 201) {
                    if (isset($res['data']['promotionApplicable']) && $res['data']['promotionApplicable'] === true) {
                        $isPromo = true;
                    } elseif (floatval($res['data']['appliedPromotionDiscount']['amount'] ?? 0) > 0) {
                        $isPromo = true;
                    }
                    deleteBookeoHold($res['data']['id'], $apiKey, $secretKey);
                }

                if ($isPromo) {
                    $activePromoCode  = $candidateCode;
                    $validUserCodes[] = $candidateCode;
                    $successType      = "promotion";
                    unset($voucherPool[$key]);
                    break;
                }
            }
            $voucherPool = array_values($voucherPool);
        }

        // ======================================================
        // PHASE B: VOUCHER CONSUMPTION
        // ======================================================
        $appliedVouchersForThisItem = [];
        $currentCredit              = 0.0;

        $payload = $basePayload;
        if (!empty($activePromoCode)) $payload['promotionCodeInput'] = $activePromoCode;

        usleep(250000);
        $res      = callBookeoHold($payload, $apiKey, $secretKey);
        $netPrice = $approxItemPrice;

        if ($res['code'] === 201) {
            $data          = $res['data'];
            $currentCredit = $data['applicableGiftVoucherCredit']['amount']
                          ?? $data['price']['applicableGiftVoucherCredit']['amount']
                          ?? 0;
            $netPrice      = $data['price']['totalNet']['amount'] ?? $approxItemPrice;
            deleteBookeoHold($data['id'], $apiKey, $secretKey);

            foreach ($voucherPool as $key => $code) {
                if ($netPrice <= 0) break;

                $tempVouchers                  = $appliedVouchersForThisItem;
                $tempVouchers[]                = $code;
                $payload['giftVoucherCodeInput'] = implode(",", $tempVouchers);

                usleep(250000);
                $vRes = callBookeoHold($payload, $apiKey, $secretKey);

                if ($vRes['code'] === 201) {
                    $vData       = $vRes['data'];
                    $newCredit   = $vData['applicableGiftVoucherCredit']['amount']
                                ?? $vData['price']['applicableGiftVoucherCredit']['amount']
                                ?? 0;
                    $newNetPrice = $vData['price']['totalNet']['amount'];
                    $isSpecific  = isset($vData['specificVoucherCode']) && !empty($vData['specificVoucherCode']);

                    if ($newCredit > $currentCredit || $newNetPrice < $netPrice || $isSpecific) {
                        if ($isSpecific && count($tempVouchers) > 1) {
                            // Smart release: specific voucher found — return generic ones to pool
                            $voucherPool              = array_merge($voucherPool, $appliedVouchersForThisItem);
                            $appliedVouchersForThisItem = [$code];
                            $tempVouchers             = [$code];
                            $currentCredit            = 0;
                            $netPrice                 = $newNetPrice;
                            $validUserCodes[]         = $code;
                        } else {
                            $appliedVouchersForThisItem[] = $code;
                            $currentCredit                = $newCredit;
                            $netPrice                     = $newNetPrice;
                            $validUserCodes[]             = $code;
                        }
                        unset($voucherPool[$key]);
                        $successType = "voucher";
                    }
                    deleteBookeoHold($vData['id'], $apiKey, $secretKey);
                }
            }
        }

        // ======================================================
        // PHASE C: FINALIZATION & SAVING
        // ======================================================
        $payload = $basePayload;
        if (!empty($activePromoCode))            $payload['promotionCodeInput']   = $activePromoCode;
        if (!empty($appliedVouchersForThisItem)) $payload['giftVoucherCodeInput'] = implode(",", $appliedVouchersForThisItem);

        usleep(250000);
        $finalRes = callBookeoHold($payload, $apiKey, $secretKey);

        if ($finalRes['code'] === 201) {
            $finalData                        = $finalRes['data'];
            $finalData['_internal_promo']     = $activePromoCode;
            $finalData['_internal_vouchers']  = implode(",", $appliedVouchersForThisItem);

            $pdo->prepare("INSERT INTO tbl_bookeo_holds (session_id, event_id, game_id, response_json, created_at) VALUES (:sid, :eid, :gid, :json, NOW())")
                ->execute([':sid' => $sid, ':eid' => $eventId, ':gid' => $gameId, ':json' => json_encode($finalData)]);

            // Hard-delete ALL cache rows for this product+date so availability refreshes instantly.
            $slotDateForCache = isset($item['slot']) ? substr($item['slot'], 0, 10) : null;
            if ($gameId && $slotDateForCache) {
                $pdo->prepare("DELETE FROM bookeo_slots_cache WHERE product_id = :pid AND slot_date = :sdate")
                    ->execute([':pid' => $gameId, ':sdate' => $slotDateForCache]);
            }

            $itemsUpdated++;
        }
    } // end foreach $cartItems

    // ---- FINALISE SESSION GIFT CODE ----
    $validUserCodes  = array_unique($validUserCodes);
    $cleanCodeString = implode(",", $validUserCodes);

    if (!empty($cleanCodeString)) {
        $_SESSION['giftCode'] = $cleanCodeString;
    }

    // ---- RETURN RESULT (identical structure to what apply_code.php echoes) ----
    if ($itemsUpdated > 0) {
        return [
            'status'     => 'success',
            'message'    => !empty($cleanCodeString) ? 'Codes applied successfully' : 'Hold refreshed successfully',
            'valid_code' => $cleanCodeString
        ];
    }

    return ['status' => 'error', 'message' => 'Failed to refresh hold.'];
}