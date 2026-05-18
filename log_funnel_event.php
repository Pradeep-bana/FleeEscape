<?php
/**
 * Booking Funnel JavaScript Logging Endpoint
 * Receives funnel events from JavaScript and logs them
 */

session_start();
require_once('includes/booking_funnel.php');

header('Content-Type: application/json');

$event = $_POST['event'] ?? '';
$data = $_POST['data'] ?? [];

if (!$event) {
    echo json_encode(['success' => false, 'message' => 'No event provided']);
    exit;
}

// Log the event
flee_funnel_log($event, $data, session_id());

echo json_encode(['success' => true, 'message' => 'Event logged']);
?>
