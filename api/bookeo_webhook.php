<?php
// api/bookeo_webhook.php

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

$logFile = __DIR__ . '/webhook_debug.txt';

$input   = file_get_contents('php://input');
$payload = json_decode($input, true);

// Respond to Bookeo immediately
http_response_code(200);
if (function_exists('fastcgi_finish_request')) {
    echo "OK";
    fastcgi_finish_request();
} else {
    echo "OK";
}

$timestamp = date('Y-m-d H:i:s');
$logMsg    = "\n[$timestamp] WEBHOOK RECEIVED:\n$input\n";

if (!$payload) {
    file_put_contents($logFile, $logMsg . "ERROR: Invalid JSON\n", FILE_APPEND);
    exit;
}

require_once('db.php');

$item      = $payload['item'] ?? [];
$eventId   = $item['eventId']   ?? $payload['data']['eventId']   ?? null;
$productId = $item['productId'] ?? $payload['data']['productId'] ?? null;
$rawDate   = $item['startTime'] ?? $payload['data']['startTime'] ?? null;
$reason    = $item['reason'] ?? '';

// Parse slot date in LA timezone (critical — offsets like -07:00 must be respected)
$slotDate = null;
if ($rawDate) {
    try {
        $dt = new DateTime($rawDate); // PHP respects the timezone offset in the string
        $dt->setTimezone(new DateTimeZone('America/Los_Angeles'));
        $slotDate = $dt->format('Y-m-d');
    } catch (Exception $e) {}
}

// Detect event type
$statusLog = "UNKNOWN";
if (isset($payload['type']) && $payload['type'] == 'deleted') {
    $statusLog = "RELEASE_HOLD_OR_DELETE";
} elseif (!empty($item['canceled']) && $item['canceled'] === true) {
    $statusLog = "CANCELED";
} elseif (stripos($reason, 'in progress') !== false) {
    $statusLog = "HOLD_STARTED";
} elseif (isset($item['bookingNumber'])) {
    $statusLog = "BOOKED_OR_RESTORED";
} elseif (isset($item['numSeats'])) {
    $statusLog = "SEATBLOCK_UPDATE";
}

$logMsg .= "DETECTED STATUS: $statusLog\n";
$logMsg .= "Event ID: $eventId\n";
$logMsg .= "Product ID: $productId\n";
$logMsg .= "Slot Date: $slotDate\n";
file_put_contents($logFile, $logMsg, FILE_APPEND);

// Log to DB
try {
    if (isset($pdo)) {
        $stmt = $pdo->prepare("
            INSERT INTO bookeo_webhook_log (event_type, product_id, event_id, slot_date, payload)
            VALUES (:type, :pid, :eid, :date, :payload)
        ");
        $stmt->execute([
            ':type'    => $statusLog,
            ':pid'     => $productId,
            ':eid'     => $eventId,
            ':date'    => $slotDate,
            ':payload' => $input,
        ]);
    }
} catch (PDOException $e) {
    file_put_contents($logFile, "DB Log Error: " . $e->getMessage() . "\n", FILE_APPEND);
}

// CACHE INVALIDATION
if ($productId && $slotDate && isset($pdo)) {
    try {
        // Expire ALL slots for this product on this date
        $stmt = $pdo->prepare("
            UPDATE bookeo_slots_cache
            SET expires_at = '2000-01-01 00:00:00'
            WHERE product_id = ? AND slot_date = ?
        ");
        $stmt->execute([$productId, $slotDate]);
        $affected = $stmt->rowCount();

        // Also clear fetch registry to force fresh download
        $stmt = $pdo->prepare("
            DELETE FROM bookeo_fetch_registry
            WHERE product_id = ? AND slot_date = ?
        ");
        $stmt->execute([$productId, $slotDate]);

        file_put_contents(
            $logFile,
            "CACHE CLEARED. $affected slot rows expired for product=$productId date=$slotDate. Next page load will fetch fresh data from Bookeo.\n",
            FILE_APPEND
        );

    } catch (Exception $e) {
        file_put_contents($logFile, "Cache Error: " . $e->getMessage() . "\n", FILE_APPEND);
    }
} else {
    file_put_contents(
        $logFile,
        "CACHE NOT CLEARED — missing productId or slotDate (productId=$productId, slotDate=$slotDate)\n",
        FILE_APPEND
    );
}