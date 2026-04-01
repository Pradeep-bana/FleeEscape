<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("admin/db.php");
require_once('config.php');

if (!defined('FLEE_APPLY_CODE_LIBRARY')) {
    define('FLEE_APPLY_CODE_LIBRARY', true);
}
require_once('apply_code.php');

if (!function_exists('flee_cart_json_response')) {
    function flee_cart_json_response(array $payload)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }
}

if (!function_exists('flee_cart_slot_date')) {
    function flee_cart_slot_date($slot)
    {
        $slot = trim((string)$slot);
        return $slot === '' ? null : substr($slot, 0, 10);
    }
}

if (!function_exists('flee_cart_delete_bookeo_hold')) {
    function flee_cart_delete_bookeo_hold($holdId, $apiKey, $secretKey)
    {
        if (!$holdId) {
            return;
        }
        if (function_exists('deleteBookeoHold')) {
            deleteBookeoHold($holdId, $apiKey, $secretKey);
            return;
        }
        flee_bookeo_log_message('cart_session_hold_delete_skipped', 'deleteBookeoHold helper was not available', [
            'hold_id' => $holdId,
        ]);
    }
}

if (!function_exists('flee_cart_collect_cart_rows')) {
    function flee_cart_collect_cart_rows(PDO $pdo, $sid)
    {
        $stmt = $pdo->prepare("SELECT * FROM tbl_carts WHERE session_id = :sid ORDER BY id ASC");
        $stmt->execute([':sid' => $sid]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('flee_cart_collect_hold_rows')) {
    function flee_cart_collect_hold_rows(PDO $pdo, $sid)
    {
        $stmt = $pdo->prepare("SELECT id, event_id, game_id, response_json FROM tbl_bookeo_holds WHERE session_id = :sid");
        $stmt->execute([':sid' => $sid]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('flee_cart_delete_cache_for_rows')) {
    function flee_cart_delete_cache_for_rows(PDO $pdo, array $cartRows)
    {
        if (empty($cartRows)) {
            return;
        }

        $stmt = $pdo->prepare("
            DELETE FROM bookeo_slots_cache
            WHERE product_id = :pid AND slot_date = :sdate
        ");

        foreach ($cartRows as $row) {
            $gameId = trim((string)($row['game_id'] ?? ''));
            $slotDate = flee_cart_slot_date($row['slot'] ?? '');
            if ($gameId !== '' && $slotDate) {
                $stmt->execute([':pid' => $gameId, ':sdate' => $slotDate]);
                flee_bookeo_clear_day_cache($slotDate);
            }
        }
    }
}

if (!function_exists('flee_cart_sync_auto_promo')) {
    function flee_cart_sync_auto_promo(PDO $pdo, $sid) {
        $stmt = $pdo->prepare("SELECT id, cat FROM tbl_carts WHERE session_id = :sid");
        $stmt->execute([':sid' => $sid]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $escapeCount = 0;
        foreach ($rows as $r) {
            if (strtolower(trim((string)$r['cat'])) === 'escape-room') {
                $escapeCount++;
            }
        }
        
        $currentCode = strtoupper(trim((string)($_SESSION['giftCode'] ?? '')));
        
        // Only apply/modify if user has no manual code, or already has an auto-code
        $isBMSM = ($currentCode === '' || strpos($currentCode, 'BMSM_') === 0);
        
        if ($isBMSM) {
            $targetCode = '';
            $promoPage = 'false';
            
            if ($escapeCount >= 3) {
                $targetCode = 'BMSM_20';
                $promoPage = 'save_more_play_more';
            } elseif ($escapeCount == 2) {
                $targetCode = 'BMSM_10';
                $promoPage = 'save_more_play_more';
            }
            
            if ($currentCode !== $targetCode) {
                if ($targetCode === '') {
                    // Dropped below 2 games: Completely scrub promo code data from session AND DB
                    unset($_SESSION['giftCode']);
                    $pdo->prepare("
                        UPDATE tbl_carts 
                        SET promo_code = '', 
                            pramotion_page = 'false', 
                            discount_amt = 0, 
                            discounted_total = total 
                        WHERE session_id = :sid
                    ")->execute([':sid' => $sid]);
                } else {
                    // Upgraded/Downgraded
                    $_SESSION['giftCode'] = $targetCode;
                    $pdo->prepare("
                        UPDATE tbl_carts 
                        SET promo_code = :code, 
                            pramotion_page = :page 
                        WHERE session_id = :sid 
                          AND cat = 'escape-room'
                    ")->execute([
                        ':code' => $targetCode, 
                        ':page' => $promoPage, 
                        ':sid' => $sid
                    ]);
                }
                return true; // Indicates promo code changed and holds need refresh
            }
        }
        
        // Ultimate fallback: If cart drops below 2 games but DB mysteriously still has BMSM
        if ($escapeCount < 2) {
            $stmtCheck = $pdo->prepare("SELECT id FROM tbl_carts WHERE session_id = :sid AND promo_code LIKE 'BMSM_%' LIMIT 1");
            $stmtCheck->execute([':sid' => $sid]);
            if ($stmtCheck->fetch()) {
                $pdo->prepare("
                    UPDATE tbl_carts 
                    SET promo_code = '', 
                        pramotion_page = 'false', 
                        discount_amt = 0, 
                        discounted_total = total 
                    WHERE session_id = :sid 
                      AND promo_code LIKE 'BMSM_%'
                ")->execute([':sid' => $sid]);
                return true;
            }
        }
        
        return false;
    }
}

if (!function_exists('flee_cart_cleanup_expired')) {
    function flee_cart_cleanup_expired(PDO $pdo, $reason = '')
    {
        $sid = session_id();
        $apiKey = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
        $secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";

        $forceExpireReasons = [
            'persistent_timer',
            'header_watcher',
            'slot_timer_countdown_end',
            'expired_flag_detected',
        ];

        $shouldForceExpire = in_array(trim((string)$reason), $forceExpireReasons, true);
        $result = [
            'expired_count' => 0,
            'expired_event_ids' => [],
            'cart_count' => 0,
        ];

        $addEventId = function ($eventId) use (&$result) {
            $eventId = trim((string)$eventId);
            if ($eventId !== '' && !in_array($eventId, $result['expired_event_ids'], true)) {
                $result['expired_event_ids'][] = $eventId;
            }
        };

        try {
            if ($shouldForceExpire) {
                $cartRows = flee_cart_collect_cart_rows($pdo, $sid);
                $holdRows = flee_cart_collect_hold_rows($pdo, $sid);

                foreach ($cartRows as $row) {
                    $addEventId($row['event_id'] ?? '');
                }

                foreach ($holdRows as $row) {
                    $addEventId($row['event_id'] ?? '');
                    $json = json_decode($row['response_json'] ?? '', true);
                    $holdId = $json['id'] ?? null;
                    flee_cart_delete_bookeo_hold($holdId, $apiKey, $secretKey);
                }

                flee_cart_delete_cache_for_rows($pdo, $cartRows);

                $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE session_id = :sid")->execute([':sid' => $sid]);
                $pdo->prepare("DELETE FROM tbl_carts WHERE session_id = :sid")->execute([':sid' => $sid]);

                $_SESSION['cart'] = [];
                unset($_SESSION['giftCode']);

                $result['expired_count'] = max(count($cartRows), count($holdRows), count($result['expired_event_ids']));
            } else {
                $stmtExpire = $pdo->prepare("
                    SELECT id, game_id, event_id, response_json
                    FROM tbl_bookeo_holds
                    WHERE session_id = :sid
                      AND TIMESTAMPDIFF(SECOND, created_at, NOW()) > " . (CART_TIMER_MINUTES * 60)
                );
                $stmtExpire->execute([':sid' => $sid]);
                $expiredHolds = $stmtExpire->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($expiredHolds)) {
                    $stmtSlot = $pdo->prepare("
                        SELECT slot
                        FROM tbl_carts
                        WHERE session_id = :sid AND event_id = :eid
                        ORDER BY id DESC
                        LIMIT 1
                    ");
                    $stmtDeleteCart = $pdo->prepare("DELETE FROM tbl_carts WHERE session_id = :sid AND event_id = :eid");
                    $stmtDeleteHold = $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE id = :id");
                    $stmtDeleteCache = $pdo->prepare("
                        DELETE FROM bookeo_slots_cache
                        WHERE product_id = :pid AND slot_date = :sdate
                    ");

                    foreach ($expiredHolds as $hold) {
                        $eventId = (string)$hold['event_id'];
                        $gameId = trim((string)($hold['game_id'] ?? ''));
                        $slotDate = null;

                        $stmtSlot->execute([':sid' => $sid, ':eid' => $eventId]);
                        $cartRow = $stmtSlot->fetch(PDO::FETCH_ASSOC);
                        if (!empty($cartRow['slot'])) {
                            $slotDate = flee_cart_slot_date($cartRow['slot']);
                        }

                        $json = json_decode($hold['response_json'] ?? '', true);
                        $holdId = $json['id'] ?? null;
                        flee_cart_delete_bookeo_hold($holdId, $apiKey, $secretKey);

                        $stmtDeleteCart->execute([':sid' => $sid, ':eid' => $eventId]);
                        $stmtDeleteHold->execute([':id' => $hold['id']]);

                        if ($gameId !== '' && $slotDate) {
                            $stmtDeleteCache->execute([':pid' => $gameId, ':sdate' => $slotDate]);
                            flee_bookeo_clear_day_cache($slotDate);
                        }

                        $result['expired_count']++;
                        $addEventId($eventId);
                    }
                }

                $stmtExpiredCartRows = $pdo->prepare("
                    SELECT COUNT(*)
                    FROM tbl_carts
                    WHERE session_id = :sid
                      AND TIMESTAMPDIFF(SECOND, created_at, NOW()) > " . (CART_TIMER_MINUTES * 60)
                );
                $stmtExpiredCartRows->execute([':sid' => $sid]);
                $hasExpiredCartRows = ((int)$stmtExpiredCartRows->fetchColumn()) > 0;

                if ($hasExpiredCartRows) {
                    $cartRows = flee_cart_collect_cart_rows($pdo, $sid);
                    $holdRows = flee_cart_collect_hold_rows($pdo, $sid);

                    foreach ($holdRows as $row) {
                        $json = json_decode($row['response_json'] ?? '', true);
                        $holdId = $json['id'] ?? null;
                        flee_cart_delete_bookeo_hold($holdId, $apiKey, $secretKey);
                        $addEventId($row['event_id'] ?? '');
                    }

                    foreach ($cartRows as $row) {
                        $addEventId($row['event_id'] ?? '');
                    }

                    flee_cart_delete_cache_for_rows($pdo, $cartRows);

                    $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE session_id = :sid")->execute([':sid' => $sid]);
                    $pdo->prepare("DELETE FROM tbl_carts WHERE session_id = :sid")->execute([':sid' => $sid]);

                    $_SESSION['cart'] = [];
                    unset($_SESSION['giftCode']);

                    $result['expired_count'] += max(count($cartRows), count($holdRows), count($result['expired_event_ids']));
                }
            }
        } catch (Exception $e) {
            error_log("Expiration Sync Error: " . $e->getMessage());
        }

        $rows = flee_apply_sync_session_cart($pdo, $sid);
        $result['cart_count'] = count($rows);

        if ($result['cart_count'] === 0) {
            $_SESSION['cart'] = [];
            unset($_SESSION['giftCode']);
        } elseif ($result['expired_count'] > 0) {
            // Apply Auto-Promo check if an item expired from the cart
            if (flee_cart_sync_auto_promo($pdo, $sid)) {
                flee_cart_refresh_holds($pdo, flee_cart_current_code());
            }
        }

        return $result;
    }
}

if (!function_exists('flee_cart_normalize_price')) {
    function flee_cart_normalize_price($priceStr, $guests)
    {
        $normalized = str_replace(["Ã¢â‚¬â€œ", "Ã¢â‚¬â€ ", "â€“", "â€”"], "-", (string)$priceStr);
        $normalized = preg_replace('/\s*-\s*/', '-', $normalized);
        preg_match_all('/\d+(?:\.\d+)?/', $normalized, $matches);
        $numbers = $matches[0] ?? [];

        $unitPrice = 0.0;
        if (count($numbers) >= 2) {
            $a = (float)$numbers[0];
            $b = (float)$numbers[1];
            $unitPrice = ((int)$guests <= 2) ? max($a, $b) : min($a, $b);
        } elseif (count($numbers) === 1) {
            $unitPrice = (float)$numbers[0];
        }

        return [$unitPrice, $unitPrice * (int)$guests];
    }
}

if (!function_exists('flee_cart_has_duplicate')) {
    function flee_cart_has_duplicate(array $sessionCart, array $candidate)
    {
        $candidateCat = strtolower(trim((string)($candidate['cat'] ?? '')));
        $candidateDate = flee_cart_slot_date($candidate['slot'] ?? '');
        $candidateGameId = trim((string)($candidate['gameId'] ?? ''));
        $candidateEventId = trim((string)($candidate['eventId'] ?? ''));

        foreach ($sessionCart as $item) {
            $itemCat = strtolower(trim((string)($item['cat'] ?? '')));
            $itemDate = flee_cart_slot_date($item['slot'] ?? '');
            $itemGameId = trim((string)($item['gameId'] ?? ($item['game_id'] ?? '')));
            $itemEventId = trim((string)($item['eventId'] ?? ($item['event_id'] ?? '')));

            if ($candidateEventId !== '' && $itemEventId !== '' && $itemEventId === $candidateEventId) {
                return true;
            }

            if ($candidateEventId === '') {
                if ($candidateCat === 'escape-room') {
                    if ($itemCat === 'escape-room' && $itemGameId === $candidateGameId && $itemDate === $candidateDate) {
                        return true;
                    }
                } elseif ($itemEventId === '' && $itemDate === $candidateDate && $itemGameId === $candidateGameId) {
                    return true;
                }
            }
        }

        return false;
    }
}

if (!function_exists('flee_cart_current_code')) {
    function flee_cart_current_code()
    {
        return trim((string)($_SESSION['giftCode'] ?? ''));
    }
}

if (!function_exists('flee_cart_count_rows')) {
    function flee_cart_count_rows(PDO $pdo, $sid)
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_carts WHERE session_id = :sid");
        $stmt->execute([':sid' => $sid]);
        return (int)$stmt->fetchColumn();
    }
}

if (!function_exists('flee_cart_delete_item_by_event')) {
    function flee_cart_delete_item_by_event(PDO $pdo, $sid, $eventId)
    {
        $eventId = trim((string)$eventId);
        if ($eventId === '') {
            return;
        }

        $apiKey = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
        $secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";

        $cartRows = flee_cart_collect_cart_rows($pdo, $sid);
        $targetRows = array_values(array_filter($cartRows, function ($row) use ($eventId) {
            return (string)($row['event_id'] ?? '') === $eventId;
        }));

        $stmtHold = $pdo->prepare("
            SELECT id, response_json
            FROM tbl_bookeo_holds
            WHERE session_id = :sid AND event_id = :eid
            ORDER BY id DESC
        ");
        $stmtHold->execute([':sid' => $sid, ':eid' => $eventId]);
        $holdRows = $stmtHold->fetchAll(PDO::FETCH_ASSOC);

        foreach ($holdRows as $holdRow) {
            $json = json_decode($holdRow['response_json'] ?? '', true);
            $holdId = $json['id'] ?? null;
            flee_cart_delete_bookeo_hold($holdId, $apiKey, $secretKey);
        }

        flee_cart_delete_cache_for_rows($pdo, $targetRows);

        $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE session_id = :sid AND event_id = :eid")
            ->execute([':sid' => $sid, ':eid' => $eventId]);
        $pdo->prepare("DELETE FROM tbl_carts WHERE session_id = :sid AND event_id = :eid")
            ->execute([':sid' => $sid, ':eid' => $eventId]);

        flee_apply_sync_session_cart($pdo, $sid);
    }
}

if (!function_exists('flee_cart_refresh_holds')) {
    function flee_cart_refresh_holds(PDO $pdo, $preferredCode = '')
    {
        $preferredCode = trim((string)$preferredCode);
        $result = run_apply_code($preferredCode, $pdo);

        if (
            $result['status'] !== 'success'
            && $preferredCode !== ''
            && stripos((string)($result['message'] ?? ''), 'Invalid promo/voucher code') !== false
        ) {
            unset($_SESSION['giftCode']);
            $result = run_apply_code('', $pdo);
        }

        return $result;
    }
}

if (!function_exists('flee_cart_flash_promo_code')) {
    function flee_cart_flash_promo_code(PDO $pdo, $slot)
    {
        $promoStmt = $pdo->prepare("SELECT coupon_code, deal_hours FROM tbl_flash_deal WHERE is_active = 1 LIMIT 1");
        $promoStmt->execute();
        $promo = $promoStmt->fetch(PDO::FETCH_ASSOC);

        if (!$promo) {
            return '';
        }

        $promoCode = trim((string)($promo['coupon_code'] ?? ''));
        $durationHours = (float)($promo['deal_hours'] ?? 0);
        $slot = trim((string)$slot);

        if ($promoCode === '') {
            return '';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $slot)) {
            $slotTime = strtotime($slot);
            $now = time();
            $diffHours = ($slotTime - $now) / 3600;

            if (!($diffHours > 0 && $diffHours <= $durationHours)) {
                return '';
            }
        }

        return $promoCode;
    }
}

if (!function_exists('flee_cart_insert_row')) {
    function flee_cart_insert_row(PDO $pdo, array $data)
    {
        $stmt = $pdo->prepare("
            INSERT INTO tbl_carts (
                session_id, game_id, event_id, game_name, slot, guests, price, total, created_at,
                cat, dataAvailable, pramotion_page, promo_code, discount_amt, discounted_total,
                additional_guest, per_guest_price, total_additional_price
            ) VALUES (
                :sid, :game_id, :event_id, :game_name, :slot, :guests, :price, :total, NOW(),
                :cat, :data_available, :pramotion_page, :promo_code, :discount_amt, :discounted_total,
                :additional_guest, :per_guest_price, :total_additional_price
            )
        ");

        $stmt->execute([
            ':sid' => session_id(),
            ':game_id' => $data['game_id'],
            ':event_id' => $data['event_id'],
            ':game_name' => $data['game_name'],
            ':slot' => $data['slot'],
            ':guests' => $data['guests'],
            ':price' => $data['price'],
            ':total' => $data['total'],
            ':cat' => $data['cat'],
            ':data_available' => $data['dataAvailable'],
            ':pramotion_page' => (string)($data['pramotion_page'] ?? 'false'),
            ':promo_code' => (string)($data['promo_code'] ?? ''),
            ':discount_amt' => $data['discount_amt'],
            ':discounted_total' => $data['discounted_total'],
            ':additional_guest' => $data['additional_guest'],
            ':per_guest_price' => $data['per_guest_price'],
            ':total_additional_price' => $data['total_additional_price'],
        ]);
    }
}

if (!function_exists('flee_cart_handle_add')) {
    function flee_cart_handle_add(PDO $pdo, $mode)
    {
        flee_cart_cleanup_expired($pdo);

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $gameId = trim((string)($_POST['gameId'] ?? ''));
        $gameName = trim((string)($_POST['gameName'] ?? ''));
        $slot = trim((string)($_POST['slot'] ?? ''));
        $eventId = trim((string)($_POST['eventId'] ?? ''));

        if ($gameId === '' || $eventId === '' || $slot === '') {
            return ['status' => 'error', 'message' => 'Missing required cart fields.', 'cart' => $_SESSION['cart']];
        }

        $row = [
            'game_id' => $gameId,
            'event_id' => $eventId,
            'game_name' => $gameName,
            'slot' => $slot,
            'guests' => 1,
            'price' => 0,
            'total' => 0,
            'cat' => 'escape-room',
            'dataAvailable' => trim((string)($_POST['dataAvailable'] ?? '0')),
            'pramotion_page' => 'false',
            'promo_code' => '',
            'discount_amt' => 0,
            'discounted_total' => 0,
            'additional_guest' => 0,
            'per_guest_price' => 0,
            'total_additional_price' => 0,
        ];

        if ($mode === 'escape' || $mode === 'promotion') {
            $row['cat'] = 'escape-room';
            $row['guests'] = max(1, (int)($_POST['guests'] ?? 0));
            [$priceUnit, $total] = flee_cart_normalize_price($_POST['price'] ?? '0', $row['guests']);
            $row['price'] = $priceUnit;
            $row['total'] = $total;
            $row['discounted_total'] = $total;

            if ($mode === 'promotion') {
                $row['pramotion_page'] = 'true';
                $row['promo_code'] = flee_cart_flash_promo_code($pdo, $slot) ?: '';
            }
        } elseif ($mode === 'party') {
            $row['cat'] = 'party-package';
            $row['guests'] = 1;
            $row['price'] = (float)($_POST['price'] ?? 0);
            $row['total'] = $row['price'];
            $row['discounted_total'] = $row['total'];
            $row['additional_guest'] = max(0, (int)($_POST['additional_guest'] ?? 0));
            $row['per_guest_price'] = (float)($_POST['per_guest_price'] ?? 0);
            $row['total_additional_price'] = (float)($_POST['total_additional_price'] ?? 0);
        } elseif ($mode === 'event') {
            $row['cat'] = 'event-rooms';
            $row['guests'] = 1;
            $row['price'] = (float)($_POST['price'] ?? 0);
            $row['total'] = $row['price'];
            $row['discounted_total'] = $row['total'];
        } else {
            return ['status' => 'error', 'message' => 'Unsupported cart mode.', 'cart' => $_SESSION['cart']];
        }

        $sessionCandidate = [
            'cat' => $row['cat'],
            'slot' => $row['slot'],
            'gameId' => $gameId,
            'eventId' => $eventId,
        ];

        if (flee_cart_has_duplicate($_SESSION['cart'], $sessionCandidate)) {
            $message = ($row['cat'] === 'escape-room')
                ? 'This exact slot is already in your cart.'
                : 'This exact slot is already in your cart.';

            return ['status' => 'error', 'message' => $message, 'cart' => $_SESSION['cart']];
        }

        flee_cart_insert_row($pdo, $row);
        flee_apply_sync_session_cart($pdo, session_id());

        // Call auto-promo handler to add BMSM logic if needed before sending to bookeo
        flee_cart_sync_auto_promo($pdo, session_id());

        $preferredCode = flee_cart_current_code();
        $refreshResult = flee_cart_refresh_holds($pdo, $preferredCode);

        if (($refreshResult['status'] ?? 'error') !== 'success') {
            flee_cart_delete_item_by_event($pdo, session_id(), $eventId);

            if (flee_cart_count_rows($pdo, session_id()) > 0) {
                flee_cart_refresh_holds($pdo, flee_cart_current_code());
            } else {
                unset($_SESSION['giftCode']);
            }

            return [
                'status' => 'error',
                'message' => $refreshResult['message'] ?? 'Failed to reserve slot. Please try another time.',
                'cart' => $_SESSION['cart'],
            ];
        }

        $cartRows = flee_apply_sync_session_cart($pdo, session_id());

        return [
            'status' => 'success',
            'message' => 'Added to cart successfully!',
            'cart' => $_SESSION['cart'],
            'cartCount' => count($cartRows),
            'eventId' => $eventId,
            'gameId' => $gameId,
            'promo' => $refreshResult['valid_code'] ?? '',
            'apply_code' => $refreshResult,
        ];
    }
}

if (!function_exists('flee_cart_handle_remove')) {
    function flee_cart_handle_remove(PDO $pdo)
    {
        flee_cart_cleanup_expired($pdo);
        flee_apply_sync_session_cart($pdo, session_id());

        if (!isset($_POST['index']) || !is_numeric($_POST['index'])) {
            return ['status' => 'error', 'message' => 'Invalid index'];
        }

        $index = (int)$_POST['index'];
        if (!isset($_SESSION['cart'][$index])) {
            flee_apply_sync_session_cart($pdo, session_id());
            return ['status' => 'error', 'message' => 'Cart item not found', 'cartCount' => count($_SESSION['cart'])];
        }

        $item = $_SESSION['cart'][$index];
        $eventId = trim((string)($item['eventId'] ?? ($item['event_id'] ?? '')));

        if ($eventId === '') {
            return ['status' => 'error', 'message' => 'Event ID missing'];
        }

        flee_cart_delete_item_by_event($pdo, session_id(), $eventId);

        if (flee_cart_count_rows($pdo, session_id()) > 0) {
            
            // Check count, scrub database if we dropped below 2 items
            flee_cart_sync_auto_promo($pdo, session_id());
            
            $refreshResult = flee_cart_refresh_holds($pdo, flee_cart_current_code());
            
            if (($refreshResult['status'] ?? 'error') !== 'success') {
                return [
                    'status' => 'error',
                    'message' => $refreshResult['message'] ?? 'Item removed, but failed to refresh the remaining holds.',
                    'cartCount' => flee_cart_count_rows($pdo, session_id()),
                ];
            }
        } else {
            $_SESSION['cart'] = [];
            unset($_SESSION['giftCode']);
        }

        // Must sync back the fully scrubbed data back to $_SESSION for final response
        $cartRows = flee_apply_sync_session_cart($pdo, session_id());

        return [
            'status' => 'success',
            'message' => 'Item removed successfully.',
            'cartCount' => count($cartRows),
            'cart' => $_SESSION['cart'],
        ];
    }
}

if (!defined('FLEE_CART_SESSION_LIBRARY')) {
    $action = trim((string)($_REQUEST['action'] ?? ''));

    if ($action === '') {
        flee_cart_json_response(['status' => 'error', 'message' => 'Missing cart action.']);
    }

    if ($action === 'add_to_cart') {
        flee_cart_json_response(flee_cart_handle_add($pdo, 'escape'));
    }

    if ($action === 'add_promotion_cart') {
        flee_cart_json_response(flee_cart_handle_add($pdo, 'promotion'));
    }

    if ($action === 'add_party_cart') {
        flee_cart_json_response(flee_cart_handle_add($pdo, 'party'));
    }

    if ($action === 'add_event_cart') {
        flee_cart_json_response(flee_cart_handle_add($pdo, 'event'));
    }

    if ($action === 'remove_from_cart') {
        flee_cart_json_response(flee_cart_handle_remove($pdo));
    }

    if ($action === 'cleanup_expired') {
        flee_cart_json_response(array_merge(['status' => 'success'], flee_cart_cleanup_expired($pdo, $_REQUEST['reason'] ?? '')));
    }

    flee_cart_json_response(['status' => 'error', 'message' => 'Unsupported cart action.']);
}