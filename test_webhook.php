<?php
// test_webhook.php

// The URL of your webhook listener
$url = 'https://fleeescapes.com/bookeo_webhook.php';

// Dummy data that looks like a real Bookeo event
$data = [
    'type' => 'created',
    'domain' => 'bookings',
    'data' => [
        'eventId' => 'TEST_EVENT_123',
        'productId' => 'TEST_PRODUCT_ABC',
        'startTime' => '2026-05-20T14:00:00Z',
        'numSeatsAvailable' => 4
    ]
];

$payload = json_encode($data);

// Send this data using cURL (mimicking Bookeo)
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h3>Webhook Test Result:</h3>";
echo "HTTP Status: <b>$httpCode</b> (Should be 200)<br>";
echo "Response: <b>$response</b> (Should be 'OK')";
?>