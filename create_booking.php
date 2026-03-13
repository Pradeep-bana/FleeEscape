<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

include "admin/db.php"; // $pdo = new PDO(...)

// Bookeo API credentials
$apiKey    = 'AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC';
$secretKey = 'RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4';

// --- get current session id ---
$sessionId = session_id();

// --- fetch cart items ---
$stmt = $pdo->prepare("SELECT * FROM tbl_carts WHERE session_id = ?");
$stmt->execute([$sessionId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$cartItems) {
    die("<p>No cart items found for this session.</p>");
}

// --- fetch hold details ---
$stmtHold = $pdo->prepare("SELECT * FROM tbl_bookeo_holds WHERE session_id = ?");
$stmtHold->execute([$sessionId]);
$holdRows = $stmtHold->fetchAll(PDO::FETCH_ASSOC);

$holdMap = [];
foreach ($holdRows as $row) {
    $resp = json_decode($row['response_json'], true);
    if ($resp && isset($resp['id'])) {
        $holdMap[$row['event_id']] = $resp; // map by event_id
    }
}

// --- create bookings for each cart item ---
foreach ($_SESSION['cart'] as $item) {
    $gameId  = $item['gameId'] ?? '';
    $eventId = $item['eventId'] ?? '';
    $guests  = (int)($item['guests'] ?? 0);

    if (!$gameId || !$eventId || $guests <= 0) {
        echo "<p style='color:red;'>Skipping invalid cart item: " . htmlspecialchars(json_encode($item)) . "</p>";
        continue;
    }

    // get hold for this event
    $holdData = $holdMap[$eventId] ?? null;
    $holdId   = $holdData['id'] ?? null;

    // sample customer info (replace with actual user data)
    $customer = [
        "firstName" => "Jane",
        "lastName"  => "Smith",
        "emailAddress" => "jane.smith@example.com",
        "phoneNumbers" => [
            [
                "number" => "1234567890",
                "type" => "mobile"
            ]
        ]
    ];

    // Build booking payload with giftVoucherCodeInput
    $bookingData = [
        "productId" => $gameId,
        "eventId"   => $eventId,
        "holdId"    => $holdId, // link with hold
        "customer"  => $customer,
        "participants" => [
            "numbers" => [
                [
                    "peopleCategoryId" => "Cadults", // adjust dynamically if needed
                    "number" => $guests
                ]
            ]
        ],
        "status" => "booked",
     
        "giftVoucherCodeInput" => "C3UYYUJ" // <-- added gift voucher code
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.bookeo.com/v2/bookings",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($bookingData),
        CURLOPT_HTTPHEADER => [
            "X-Bookeo-apiKey: $apiKey",
            "X-Bookeo-secretKey: $secretKey",
            "Content-Type: application/json",
            "Accept: application/json"
        ]
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    echo "<h3>Booking Response for Game: {$eventId}</h3>";
    if ($err) {
        echo "<pre>cURL Error: $err</pre>";
    } else {
        echo "<p>HTTP Code: {$httpCode}</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }

    if ($holdData) {
        echo "<h4>Price Details from Hold</h4>";
        echo "<pre>" . htmlspecialchars(json_encode($holdData['price'], JSON_PRETTY_PRINT)) . "</pre>";
    }
    echo "<hr>";
}
