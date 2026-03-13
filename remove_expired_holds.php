<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("admin/db.php");
require_once('config.php');

$sid = session_id();
$reason = trim((string)($_REQUEST['reason'] ?? ''));
$forceExpireReasons = [
    'persistent_timer',
    'header_watcher',
    'slot_timer_countdown_end',
    'expired_flag_detected',
];
$shouldForceExpire = in_array($reason, $forceExpireReasons, true);
$fleeExpiredCleanup = [
    'expired_count' => 0,
    'expired_event_ids' => [],
    'cart_count' => 0,
];

function flee_delete_bookeo_hold($holdId, $apiKey, $secretKey)
{
    if (!$holdId) {
        return;
    }
    $url = "https://api.bookeo.com/v2/holds/{$holdId}?apiKey={$apiKey}&secretKey={$secretKey}";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    curl_close($ch);
}

function flee_collect_cart_rows($pdo, $sid)
{
    $stmt = $pdo->prepare("
        SELECT event_id, game_id, slot
        FROM tbl_carts
        WHERE session_id = :sid
    ");
    $stmt->execute([':sid' => $sid]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function flee_collect_hold_rows($pdo, $sid)
{
    $stmt = $pdo->prepare("
        SELECT id, event_id, game_id, response_json
        FROM tbl_bookeo_holds
        WHERE session_id = :sid
    ");
    $stmt->execute([':sid' => $sid]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function flee_add_event_id(&$collector, $eventId)
{
    $eid = trim((string)$eventId);
    if ($eid === '') {
        return;
    }
    if (!in_array($eid, $collector, true)) {
        $collector[] = $eid;
    }
}

function flee_delete_cache_for_cart_rows($pdo, $cartRows)
{
    if (empty($cartRows)) {
        return;
    }
    $stmtDeleteCache = $pdo->prepare("
        DELETE FROM bookeo_slots_cache
        WHERE product_id = :pid AND slot_date = :sdate
    ");
    foreach ($cartRows as $row) {
        $gameId = $row['game_id'] ?? null;
        $slotDateOnly = !empty($row['slot']) ? substr((string)$row['slot'], 0, 10) : null;
        if ($gameId && $slotDateOnly) {
            $stmtDeleteCache->execute([
                ':pid' => $gameId,
                ':sdate' => $slotDateOnly,
            ]);
        }
    }
}

function flee_sync_session_cart_with_db($pdo, $sid)
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $stmt = $pdo->prepare("
        SELECT event_id
        FROM tbl_carts
        WHERE session_id = :sid
    ");
    $stmt->execute([':sid' => $sid]);
    $eventIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $dbEvents = [];
    foreach ($eventIds as $eid) {
        $eid = trim((string)$eid);
        if ($eid !== '') {
            $dbEvents[$eid] = true;
        }
    }

    if (empty($dbEvents)) {
        $_SESSION['cart'] = [];
        return;
    }

    $filtered = [];
    foreach ($_SESSION['cart'] as $item) {
        $itemEventId = trim((string)($item['eventId'] ?? ($item['event_id'] ?? '')));
        if ($itemEventId !== '' && isset($dbEvents[$itemEventId])) {
            $filtered[] = $item;
        }
    }
    $_SESSION['cart'] = array_values($filtered);
}

try {
    $apiKey = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
    $secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";

    if ($shouldForceExpire) {
        $cartRows = flee_collect_cart_rows($pdo, $sid);
        $allHolds = flee_collect_hold_rows($pdo, $sid);

        foreach ($cartRows as $r) {
            flee_add_event_id($fleeExpiredCleanup['expired_event_ids'], $r['event_id'] ?? '');
        }
        foreach ($allHolds as $h) {
            flee_add_event_id($fleeExpiredCleanup['expired_event_ids'], $h['event_id'] ?? '');
            $json = json_decode($h['response_json'] ?? '', true);
            $holdId = $json['id'] ?? null;
            flee_delete_bookeo_hold($holdId, $apiKey, $secretKey);
        }

        flee_delete_cache_for_cart_rows($pdo, $cartRows);

        $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE session_id = :sid")
            ->execute([':sid' => $sid]);
        $pdo->prepare("DELETE FROM tbl_carts WHERE session_id = :sid")
            ->execute([':sid' => $sid]);

        $_SESSION['cart'] = [];
        unset($_SESSION['giftCode']);

        $fleeExpiredCleanup['expired_count'] = max(
            count($cartRows),
            count($allHolds),
            count($fleeExpiredCleanup['expired_event_ids'])
        );
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
            $stmtCartSlot = $pdo->prepare("
                SELECT slot
                FROM tbl_carts
                WHERE session_id = :sid AND event_id = :eid
                ORDER BY id DESC
                LIMIT 1
            ");
            $stmtDeleteCart = $pdo->prepare("
                DELETE FROM tbl_carts
                WHERE session_id = :sid AND event_id = :eid
            ");
            $stmtDeleteHold = $pdo->prepare("
                DELETE FROM tbl_bookeo_holds
                WHERE id = :id
            ");
            $stmtDeleteCache = $pdo->prepare("
                DELETE FROM bookeo_slots_cache
                WHERE product_id = :pid AND slot_date = :sdate
            ");

            foreach ($expiredHolds as $exHold) {
                $expiredEventId = $exHold['event_id'];
                $gameId = $exHold['game_id'];
                $slotDateOnly = null;

                $stmtCartSlot->execute([':sid' => $sid, ':eid' => $expiredEventId]);
                $cartRow = $stmtCartSlot->fetch(PDO::FETCH_ASSOC);
                if (!empty($cartRow['slot'])) {
                    $slotDateOnly = substr((string)$cartRow['slot'], 0, 10);
                }

                $json = json_decode($exHold['response_json'], true);
                $holdId = $json['id'] ?? null;
                flee_delete_bookeo_hold($holdId, $apiKey, $secretKey);

                $stmtDeleteCart->execute([':sid' => $sid, ':eid' => $expiredEventId]);

                if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $key => $item) {
                        $itemEventId = $item['eventId'] ?? ($item['event_id'] ?? null);
                        if ($itemEventId && (string)$itemEventId === (string)$expiredEventId) {
                            unset($_SESSION['cart'][$key]);
                        }
                    }
                    $_SESSION['cart'] = array_values($_SESSION['cart']);
                }

                if ($gameId && $slotDateOnly) {
                    $stmtDeleteCache->execute([
                        ':pid' => $gameId,
                        ':sdate' => $slotDateOnly
                    ]);
                }

                $stmtDeleteHold->execute([':id' => $exHold['id']]);

                $fleeExpiredCleanup['expired_count']++;
                flee_add_event_id($fleeExpiredCleanup['expired_event_ids'], $expiredEventId);
            }

            if ($fleeExpiredCleanup['expired_count'] > 0) {
                unset($_SESSION['giftCode']);
            }
        }

        // Fallback: if cart rows themselves are older than timer,
        // clear whole cart/session even when hold rows are missing/inconsistent.
        $stmtExpiredCartRows = $pdo->prepare("
            SELECT COUNT(*)
            FROM tbl_carts
            WHERE session_id = :sid
              AND TIMESTAMPDIFF(SECOND, created_at, NOW()) > " . (CART_TIMER_MINUTES * 60)
        );
        $stmtExpiredCartRows->execute([':sid' => $sid]);
        $hasExpiredCartRows = ((int)$stmtExpiredCartRows->fetchColumn()) > 0;

        if ($hasExpiredCartRows) {
            $cartRows = flee_collect_cart_rows($pdo, $sid);
            $allHolds = flee_collect_hold_rows($pdo, $sid);

            foreach ($allHolds as $holdRow) {
                $json = json_decode($holdRow['response_json'], true);
                $holdId = $json['id'] ?? null;
                flee_delete_bookeo_hold($holdId, $apiKey, $secretKey);
                flee_add_event_id($fleeExpiredCleanup['expired_event_ids'], $holdRow['event_id'] ?? '');
            }

            foreach ($cartRows as $r) {
                flee_add_event_id($fleeExpiredCleanup['expired_event_ids'], $r['event_id'] ?? '');
            }
            flee_delete_cache_for_cart_rows($pdo, $cartRows);

            $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE session_id = :sid")
                ->execute([':sid' => $sid]);
            $pdo->prepare("DELETE FROM tbl_carts WHERE session_id = :sid")
                ->execute([':sid' => $sid]);

            $_SESSION['cart'] = [];
            unset($_SESSION['giftCode']);

            $fleeExpiredCleanup['expired_count'] += max(
                count($cartRows),
                count($allHolds),
                count($fleeExpiredCleanup['expired_event_ids'])
            );
        }
    }

    flee_sync_session_cart_with_db($pdo, $sid);

    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM tbl_carts WHERE session_id = :sid");
    $stmtCount->execute([':sid' => $sid]);
    $fleeExpiredCleanup['cart_count'] = (int)$stmtCount->fetchColumn();

    if ($fleeExpiredCleanup['cart_count'] === 0) {
        $_SESSION['cart'] = [];
        unset($_SESSION['giftCode']);
    }
} catch (Exception $e) {
    error_log("Expiration Sync Error: " . $e->getMessage());
}
?>
