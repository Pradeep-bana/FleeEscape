<?php
// api/bookeo_webhook.php

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../includes/bookeo_runtime.php');

$input = file_get_contents('php://input');
$payload = json_decode($input, true);

http_response_code(200);
if (function_exists('fastcgi_finish_request')) {
    echo "OK";
    fastcgi_finish_request();
} else {
    echo "OK";
}

if (!$payload) {
    flee_system_log_message('api_bookeo_webhook_invalid', 'Invalid JSON payload received', [
        'raw_payload' => $input,
    ]);
    exit;
}

require_once(__DIR__ . '/db.php');

$item = $payload['item'] ?? [];
$eventId = $item['eventId'] ?? ($payload['data']['eventId'] ?? null);
$productId = $item['productId'] ?? ($payload['data']['productId'] ?? null);
$rawDate = $item['startTime'] ?? ($payload['data']['startTime'] ?? null);
$reason = $item['reason'] ?? '';

$slotDate = null;
if ($rawDate) {
    try {
        $dt = new DateTime($rawDate);
        $dt->setTimezone(new DateTimeZone('America/Los_Angeles'));
        $slotDate = $dt->format('Y-m-d');
    } catch (Exception $e) {
        flee_system_log_message('api_bookeo_webhook_date_error', 'Unable to parse webhook start time', [
            'raw_date' => $rawDate,
            'error' => $e->getMessage(),
        ]);
    }
}

$statusLog = "UNKNOWN";
if (($payload['type'] ?? null) === 'deleted') {
    $statusLog = "RELEASE_HOLD_OR_DELETE";
} elseif (!empty($item['canceled'])) {
    $statusLog = "CANCELED";
} elseif (stripos($reason, 'in progress') !== false) {
    $statusLog = "HOLD_STARTED";
} elseif (isset($item['bookingNumber'])) {
    $statusLog = "BOOKED_OR_RESTORED";
} elseif (isset($item['numSeats'])) {
    $statusLog = "SEATBLOCK_UPDATE";
}

flee_system_log_message('api_bookeo_webhook_received', [
    'status' => $statusLog,
    'event_id' => $eventId,
    'product_id' => $productId,
    'slot_date' => $slotDate,
    'raw_payload' => $input,
]);

try {
    if (isset($pdo)) {
        $stmt = $pdo->prepare("
            INSERT INTO bookeo_webhook_log (event_type, product_id, event_id, slot_date, payload)
            VALUES (:type, :pid, :eid, :date, :payload)
        ");
        $stmt->execute([
            ':type' => $statusLog,
            ':pid' => $productId,
            ':eid' => $eventId,
            ':date' => $slotDate,
            ':payload' => $input,
        ]);
    }
} catch (PDOException $e) {
    flee_system_log_message('api_bookeo_webhook_db_error', 'Webhook DB log insert failed', [
        'error' => $e->getMessage(),
    ]);
}

if ($productId && $slotDate && isset($pdo)) {
    try {
        $stmt = $pdo->prepare("
            UPDATE bookeo_slots_cache
            SET expires_at = '2000-01-01 00:00:00'
            WHERE product_id = ? AND slot_date = ?
        ");
        $stmt->execute([$productId, $slotDate]);
        $affected = $stmt->rowCount();

        flee_bookeo_clear_day_cache($slotDate);

        flee_system_log_message('api_bookeo_webhook_cache_cleared', 'Expired cache rows for webhook event', [
            'product_id' => $productId,
            'slot_date' => $slotDate,
            'affected_rows' => $affected,
        ]);
    } catch (Exception $e) {
        flee_system_log_message('api_bookeo_webhook_cache_error', 'Cache invalidation failed', [
            'error' => $e->getMessage(),
            'product_id' => $productId,
            'slot_date' => $slotDate,
        ]);
    }
} else {
    flee_system_log_message('api_bookeo_webhook_cache_skipped', 'Cache not cleared because webhook lacked identifiers', [
        'product_id' => $productId,
        'slot_date' => $slotDate,
    ]);
}
