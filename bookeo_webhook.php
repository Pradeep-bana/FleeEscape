<?php
// bookeo_webhook.php

// Ensure we don't output errors to the browser/webhook response, log them instead
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

include('admin/db.php');

// 1. Get Payload
$input = file_get_contents('php://input');
$payload = json_decode($input, true);

// If no payload, stop
if (!$payload) {
    http_response_code(400);
    exit('Invalid JSON');
}

// 2. Extract Key Variables
// Bookeo sends types like: 'created', 'updated', 'deleted'
// Bookeo sends domains like: 'bookings', 'seatblocks'
$eventType = $payload['type'] ?? 'unknown';
$domain    = $payload['domain'] ?? 'unknown';
$data      = $payload['data'] ?? [];

$productId = $data['productId'] ?? null;
$eventId   = $data['eventId'] ?? null;

// Calculate Date for Log
$slotDate = null;
if (isset($data['startTime'])) {
    try {
        $dt = new DateTime($data['startTime'], new DateTimeZone('UTC'));
        $dt->setTimezone(new DateTimeZone('America/Los_Angeles'));
        $slotDate = $dt->format('Y-m-d');
    } catch (Exception $e) {
        // invalid date format
    }
}

// 3. Log to Database
try {
    $stmt = $pdo->prepare("
        INSERT INTO bookeo_webhook_log (event_type, product_id, event_id, slot_date, payload)
        VALUES (:type, :pid, :eid, :date, :payload)
    ");
    
    $stmt->execute([
        ':type'    => $eventType, // Will store 'created', 'updated', etc.
        ':pid'     => $productId,
        ':eid'     => $eventId,
        ':date'    => $slotDate,
        ':payload' => $input, // Store raw JSON string
    ]);
} catch (PDOException $e) {
    // Log DB error quietly if needed
    error_log("Webhook DB Log Error: " . $e->getMessage());
}

// 4. Process Logic (Cache Invalidation)
// We check if the event is created/updated/deleted
if ($eventId && in_array($eventType, ['created', 'updated', 'deleted'])) {
    
    $newSeats = $data['numSeatsAvailable'] ?? null;
    $isCanceled = $data['canceled'] ?? false; // Check if it is a cancellation

    // If canceled, we might want to treat it like a deletion (free up seats)
    // Bookeo usually sends 'numSeatsAvailable' automatically, but let's be safe.
    
    if ($newSeats !== null) {
        // Update specific seats
        $stmt = $pdo->prepare("
            UPDATE bookeo_slots_cache 
            SET available_seats = :seats,
                cached_at = NOW(),
                expires_at = DATE_ADD(NOW(), INTERVAL 2 MINUTE)
            WHERE event_id = :eid
        ");
        $stmt->execute([':seats' => (int)$newSeats, ':eid' => $eventId]);
    } else {
        // If we don't have seat count (e.g. strict delete), force expire the cache
        $stmt = $pdo->prepare("
            UPDATE bookeo_slots_cache 
            SET expires_at = '2000-01-01 00:00:00'
            WHERE event_id = :eid
        ");
        $stmt->execute([':eid' => $eventId]);
    }
    
    // Clear Fetch Registry
    if ($productId && $slotDate) {
        $stmt = $pdo->prepare("DELETE FROM bookeo_fetch_registry WHERE product_id=? AND slot_date=?");
        $stmt->execute([$productId, $slotDate]);
    }
}

// 5. Respond 200 OK
http_response_code(200);
echo 'OK';
?>