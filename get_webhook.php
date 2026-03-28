<?php
http_response_code(200);
header("Content-Type: text/plain");
echo "OK";

if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
} else {
    @ob_end_flush();
    @flush();
}

include('admin/db.php');
require_once(__DIR__ . '/includes/bookeo_runtime.php');

function flee_legacy_webhook_log($message, array $fields = [])
{
    $fields['message'] = $message;
    flee_bookeo_log('legacy_get_webhook', $fields);
}

$raw = file_get_contents("php://input");
flee_legacy_webhook_log('Webhook received', ['raw_payload' => $raw]);

$data = json_decode($raw, true);
if (!$data) {
    flee_legacy_webhook_log('Invalid JSON');
    return;
}

$item = $data["item"] ?? null;
if (!$item) {
    flee_legacy_webhook_log('No item object');
    return;
}

$eventType = "unknown";
if (isset($item["bookingNumber"])) {
    if (($data["type"] ?? "") === "bookings.updated" && isset($data["booking"]["status"]) && strtolower($data["booking"]["status"]) === "cancelled") {
        $eventType = "bookingCancelled";
        flee_legacy_webhook_log('Detected bookingCancelled via primary rule');
    }

    if ($eventType === "unknown") {
        if (($data["action"] ?? "") === "created") {
            $eventType = "bookingCreated";
        } elseif (($data["action"] ?? "") === "deleted") {
            $eventType = "bookingdeleted";
        } elseif (($data["action"] ?? "") === "updated") {
            $eventType = "bookingUpdated";
        } elseif (($item["status"] ?? "") === "cancelled" || ($data["action"] ?? "") === "statusChanged" || (($data["action"] ?? "") === "updated" && strtolower($item["status"] ?? "") === "cancelled")) {
            $eventType = "bookingCancelled";
        } else {
            $eventType = "bookingEvent";
        }
    }
} elseif (isset($item["numSeats"])) {
    if ($item["numSeats"] > 0) {
        $eventType = "seatBlockCreated";
    } elseif ($item["numSeats"] < 0) {
        $eventType = "seatBlockDeleted";
    } else {
        $eventType = "seatBlockEvent";
    }
} elseif (($data["type"] ?? "") === "event") {
    if (($data["action"] ?? "") === "created") {
        $eventType = "eventCreated";
    } elseif (($data["action"] ?? "") === "updated") {
        $eventType = "eventUpdated";
    } elseif (($data["action"] ?? "") === "deleted") {
        $eventType = "eventDeleted";
    } else {
        $eventType = "eventGeneric";
    }
}

flee_legacy_webhook_log('Detected event', ['event_type' => $eventType]);

$productId = $item["productId"] ?? null;
$eventId = $item["eventId"] ?? null;
$startTime = $item["startTime"] ?? null;
$numSeats = $item["numSeats"] ?? 0;

$itemId = $data["itemId"] ?? ($item["id"] ?? "");
if (!$itemId) {
    $itemId = $eventId . "|" . $startTime . "|" . ($item["bookingNumber"] ?? 'no-booking');
}

if (!$productId || !$eventId || !$startTime) {
    flee_legacy_webhook_log('Missing productId/eventId/startTime', [
        'product_id' => $productId,
        'event_id' => $eventId,
        'start_time' => $startTime,
    ]);
    return;
}

$processedFile = __DIR__ . "/bookeo_processed.json";
$dedupeWindow = 10 * 60;

function loadProcessed($file) {
    if (!file_exists($file)) {
        return [];
    }
    return json_decode(@file_get_contents($file), true) ?: [];
}

function saveProcessed($file, $arr) {
    file_put_contents($file . ".tmp", json_encode($arr), LOCK_EX);
    rename($file . ".tmp", $file);
}

$processed = loadProcessed($processedFile);
$now = time();

foreach ($processed as $key => $ts) {
    if ($now - $ts > $dedupeWindow * 5) {
        unset($processed[$key]);
    }
}

if (isset($processed[$itemId]) && ($now - $processed[$itemId]) <= $dedupeWindow) {
    flee_legacy_webhook_log('Skipped duplicate webhook item', ['item_id' => $itemId]);
    return;
}

$processed[$itemId] = $now;
saveProcessed($processedFile, $processed);
flee_legacy_webhook_log('Processing webhook item', ['item_id' => $itemId]);

$dt = new DateTime($startTime);
$slot_date = $dt->format("Y-m-d");

try {
    $pdo->beginTransaction();

    $find = $pdo->prepare("
        SELECT available_seats
        FROM bookeo_slots_cache
        WHERE product_id = :pid
          AND event_id   = :eid
          AND start_time_local = :stime
        LIMIT 1
    ");

    $find->execute([
        ":pid" => $productId,
        ":eid" => $eventId,
        ":stime" => $startTime
    ]);

    $row = $find->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $update = $pdo->prepare("
            UPDATE bookeo_slots_cache
            SET available_seats = :seats,
                updated_at = NOW()
            WHERE product_id = :pid 
              AND event_id   = :eid
              AND start_time_local = :stime
        ");

        $update->execute([
            ":seats" => 0,
            ":pid" => $productId,
            ":eid" => $eventId,
            ":stime" => $startTime
        ]);

        flee_legacy_webhook_log('DB slot updated', ['product_id' => $productId, 'event_id' => $eventId]);
    } else {
        $insert = $pdo->prepare("
            INSERT INTO bookeo_slots_cache
            (product_id, slot_date, start_time_utc, start_time_local,
             event_id, available_seats, max_seats, raw_slot_id,
             created_at, updated_at)
            VALUES
            (:pid, :sdate, UTC_TIMESTAMP(), :stime,
             :eid, :avail, 0, '',
             NOW(), NOW())
        ");

        $insert->execute([
            ":pid" => $productId,
            ":sdate" => $slot_date,
            ":stime" => $startTime,
            ":eid" => $eventId,
            ":avail" => $numSeats
        ]);

        flee_legacy_webhook_log('DB slot inserted', ['product_id' => $productId, 'event_id' => $eventId]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    flee_legacy_webhook_log('DB error during webhook sync', ['error' => $e->getMessage()]);
}
?>
