<?php
//----------------------------------------------------------
// 1) QUICK RESPONSE TO BOOKEO (VERY IMPORTANT)
//----------------------------------------------------------
http_response_code(200);
header("Content-Type: text/plain");
echo "OK";

if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
} else {
    @ob_end_flush();
    @flush();
}

//----------------------------------------------------------
// 2) BACKGROUND PROCESSING STARTS
//----------------------------------------------------------
include('admin/db.php');

$logFile = __DIR__ . '/bookeo_webhook_log.txt';
$raw     = file_get_contents("php://input");

file_put_contents(
    $logFile,
    "\n\n==== NEW WEBHOOK @ " . date("Y-m-d H:i:s") . " ====\n$raw\n",
    FILE_APPEND
);

$data = json_decode($raw, true);
if (!$data) { file_put_contents($logFile, "Invalid JSON\n", FILE_APPEND); return; }

// MAIN ITEM
$item = $data["item"] ?? null;
if (!$item) { file_put_contents($logFile, "No item object\n", FILE_APPEND); return; }


//----------------------------------------------------------
// 3) DETECT EVENT TYPE (UPDATED WITH YOUR LOGIC)
//----------------------------------------------------------
$eventType = "unknown";

// Bookings
if (isset($item["bookingNumber"])) {

    // Standard Bookeo Cancellation Logic
    if (($data["type"] ?? "") === "bookings.updated") {
        if (isset($data["booking"]["status"]) && strtolower($data["booking"]["status"]) === "cancelled") {

            $eventType = "bookingCancelled";

            file_put_contents($logFile, "Detected: bookingCancelled (primary rule)\n", FILE_APPEND);
        }
    }

    // Existing logic (works as fallback)
    if ($eventType === "unknown") {
        if (($data["action"] ?? "") === "created") {
            $eventType = "bookingCreated";
        }
        elseif (($data["action"] ?? "") === "deleted") {
            $eventType = "bookingdeleted";
        }
        elseif (($data["action"] ?? "") === "updated") {
            $eventType = "bookingUpdated";
        }
        elseif (
            ($item["status"] ?? "") === "cancelled" ||
            ($data["action"] ?? "") === "statusChanged" ||
            (
                ($data["action"] ?? "") === "updated" &&
                strtolower($item["status"] ?? "") === "cancelled"
            )
        ) {
            $eventType = "bookingCancelled";
        }
        else {
            $eventType = "bookingEvent";
        }
    }

}
// Seatblocks
elseif (isset($item["numSeats"])) {

    if ($item["numSeats"] > 0) {
        $eventType = "seatBlockCreated";
    } elseif ($item["numSeats"] < 0) {
        $eventType = "seatBlockDeleted";
    } else {
        $eventType = "seatBlockEvent";
    }

// Events
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

file_put_contents($logFile, "Detected Event: $eventType\n", FILE_APPEND);


//----------------------------------------------------------
// 4) COMMON FIELDS
//----------------------------------------------------------
$productId = $item["productId"] ?? null;
$eventId   = $item["eventId"] ?? null;
$startTime = $item["startTime"] ?? null;
$numSeats  = $item["numSeats"] ?? 0;

$itemId = $data["itemId"] ?? ($item["id"] ?? "");
if (!$itemId) {
    $itemId = $eventId . "|" . $startTime . "|" . ($item["bookingNumber"] ?? 'no-booking');
}

if (!$productId || !$eventId || !$startTime) {
    file_put_contents($logFile, "Missing productId/eventId/startTime\n", FILE_APPEND);
    return;
}


//----------------------------------------------------------
// 5) DEDUPE PROTECTION
//----------------------------------------------------------
$processedFile = __DIR__ . "/bookeo_processed.json";
$dedupeWindow  = 10 * 60;

function loadProcessed($file) {
    if (!file_exists($file)) return [];
    return json_decode(@file_get_contents($file), true) ?: [];
}

function saveProcessed($file, $arr) {
    file_put_contents($file . ".tmp", json_encode($arr), LOCK_EX);
    rename($file . ".tmp", $file);
}

$processed = loadProcessed($processedFile);
$now = time();

foreach ($processed as $key => $ts) {
    if ($now - $ts > $dedupeWindow * 5) unset($processed[$key]);
}

if (isset($processed[$itemId]) && ($now - $processed[$itemId]) <= $dedupeWindow) {
    file_put_contents($logFile, "SKIPPED DUPLICATE: $itemId\n", FILE_APPEND);
    return;
}

$processed[$itemId] = $now;
saveProcessed($processedFile, $processed);

file_put_contents($logFile, "PROCESSING itemId $itemId\n", FILE_APPEND);


//----------------------------------------------------------
// 6) FORMAT DATE
//----------------------------------------------------------
$dt          = new DateTime($startTime);
$slot_date   = $dt->format("Y-m-d");
$local_start = $dt->format("Y-m-d\TH:i:sP");


//----------------------------------------------------------
// 7) DB UPDATE
//----------------------------------------------------------
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
        ":pid"   => $productId,
        ":eid"   => $eventId,
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
            ":pid"   => $productId,
            ":eid"   => $eventId,
            ":stime" => $startTime
        ]);

        file_put_contents($logFile, "DB: Slot updated\n", FILE_APPEND);

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
            ":pid"   => $productId,
            ":sdate" => $slot_date,
            ":stime" => $startTime,
            ":eid"   => $eventId,
            ":avail" => $numSeats
        ]);

        file_put_contents($logFile, "DB: Slot inserted\n", FILE_APPEND);
    }

    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
    file_put_contents($logFile, "DB ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
}
?>
