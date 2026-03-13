<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
include "admin/db.php"; // $pdo = new PDO(...)

$apiKey    = 'AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC';
$secretKey = 'RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4';

$sessionId = session_id();
echo "<p>Session ID: {$sessionId}</p>";

$stmt = $pdo->prepare("SELECT * FROM tbl_carts WHERE session_id = ?");
$stmt->execute([$sessionId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$cartItems) {
    die("<p>No cart items found for this session.</p>");
}

// --- decide promo code ---
$cartCount = count($cartItems);
$promoCode = "";
if ($cartCount == 2) {
    $promoCode = "BMSM_10";
} elseif ($cartCount >= 3) {
    $promoCode = "BMSM_20";
}

// --- fetch hold details ---
$stmtHold = $pdo->prepare("SELECT * FROM tbl_bookeo_holds WHERE session_id = ? ORDER BY id ASC");
$stmtHold->execute([$sessionId]);
$holdRows = $stmtHold->fetchAll(PDO::FETCH_ASSOC);

$holdMap = [];
foreach ($holdRows as $row) {
    $resp = json_decode($row['response_json'], true);
    $gameId = $row['game_id'] ?? null;
    if ($resp && isset($resp['id']) && $gameId) {
        $holdMap[$gameId][] = [
            'id'    => $resp['id'],
            'price' => $resp['price'] ?? null,
            'raw'   => $resp
        ];
    }
}

// helper: safely extract amount & currency from hold price structure
function extractAmountCurrencyFromPrice($price) {
    if (!is_array($price)) {
        return null;
    }

    // prefer totalPayable, then totalGross, then totalNet, then totalPaid, then direct amount
    if (isset($price['totalPayable']['amount'])) {
        $amount = $price['totalPayable']['amount'];
        $currency = $price['totalPayable']['currency'] ?? null;
        if ($amount !== null && $currency !== null) {
            return ['amount' => (string)$amount, 'currency' => (string)$currency];
        }
    }

    if (isset($price['totalGross']['amount'])) {
        $amount = $price['totalGross']['amount'];
        $currency = $price['totalGross']['currency'] ?? null;
        if ($amount !== null && $currency !== null) {
            return ['amount' => (string)$amount, 'currency' => (string)$currency];
        }
    }

    if (isset($price['totalNet']['amount'])) {
        $amount = $price['totalNet']['amount'];
        $currency = $price['totalNet']['currency'] ?? null;
        if ($amount !== null && $currency !== null) {
            return ['amount' => (string)$amount, 'currency' => (string)$currency];
        }
    }

    if (isset($price['totalPaid']['amount'])) {
        $amount = $price['totalPaid']['amount'];
        $currency = $price['totalPaid']['currency'] ?? null;
        if ($amount !== null && $currency !== null) {
            return ['amount' => (string)$amount, 'currency' => (string)$currency];
        }
    }

    // fallback to direct keys if present
    if (isset($price['amount']) && isset($price['currency'])) {
        return ['amount' => (string)$price['amount'], 'currency' => (string)$price['currency']];
    }

    // nothing useful found
    return null;
}

// --- multiple vouchers in comma-separated string ---
$voucherCodes = ["PARNMXH", "MT9Y73J"];
$voucherString = implode(",", $voucherCodes);
$vouchersUsed = false; // ✅ flag to mark vouchers already consumed

// --- create bookings ---
foreach ($_SESSION['cart'] as $index => $item) {
    $gameId  = $item['gameId'] ?? '';
    $eventId = $item['eventId'] ?? '';
    $guests  = (int)($item['guests'] ?? 0);

    if (!$gameId || !$eventId || $guests <= 0) {
        echo "<p style='color:red;'>Skipping invalid cart item: " . htmlspecialchars(json_encode($item)) . "</p>";
        continue;
    }

    $holdsForGame = $holdMap[$gameId] ?? [];
    $holdCount = count($holdsForGame);

    $holdId = $holdCount > 0 ? $holdsForGame[$holdCount - 1]['id'] : null;
    $previousHoldId = $holdId;

    $customer = [
        "firstName" => "Megha",
        "lastName"  => "Barve",
        "emailAddress" => "megha.barve04@gmail.com",
        "phoneNumbers" => [
            ["number" => "9424894738", "type" => "mobile"]
        ]
    ];

    $bookingData = [
        "productId" => $gameId,
        "eventId"   => $eventId,
        "holdId"    => $holdId,
        "customer"  => $customer,
        "participants" => [
            "numbers" => [
                ["peopleCategoryId" => "Cadults", "number" => $guests]
            ]
        ],
        "status" => "booked"
    ];

    if ($promoCode !== "") {
        $bookingData["promotionCodeInput"] = $promoCode;
    }

    // Apply vouchers only if not already used
    if (!$vouchersUsed) {
        $bookingData["giftVoucherCodeInput"] = $voucherString;
    }

    // --- Add credit card payment details based on hold price (if available) ---
    $priceInfo = null;
    if ($holdCount > 0 && isset($holdsForGame[$holdCount - 1]['price'])) {
        $priceInfo = extractAmountCurrencyFromPrice($holdsForGame[$holdCount - 1]['price']);
    }

    if ($priceInfo) {
        // Use the price from hold (amount & currency)
        $bookingData["initialPayments"] = [
            [
                "receivedTime" => date('c'), // current ISO 8601 time
                "reason"       => "Full payment",
                "comment"      => "Credit card payment",
                "amount" => [
                    "amount"   => $priceInfo['amount'],
                    "currency" => $priceInfo['currency']
                ],
                "paymentMethod"      => "creditCard",
                "paymentMethodOther" => "VISA **** 4242"
                
               
      ]
            
        ];
    } else {
        // If no price found, do not include initialPayments (avoid sending incorrect data)
        // You can log or show a warning if needed:
        echo "<p style='color:orange;'>Warning: Could not determine amount/currency from hold for game {$gameId}. No initialPayments sent.</p>";
    }

    $queryParams = http_build_query([
        "previousHoldId"  => $previousHoldId,
        "notifyUsers"     => "true",
        "notifyCustomer"  => "true"
    ]);
    $bookingUrl = "https://api.bookeo.com/v2/bookings?" . $queryParams;

    // --- send booking request ---
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $bookingUrl,
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

    $decodedResp = json_decode($response, true);

    // --- check for voucher error ---
    if ($httpCode == 400 && isset($decodedResp['message']) && stripos($decodedResp['message'], 'voucher') !== false) {
        echo "<p style='color:orange;'>Voucher error for Game {$gameId}. Retrying without vouchers...</p>";

        // retry booking without vouchers
        unset($bookingData["giftVoucherCodeInput"]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $bookingUrl,
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

        $decodedResp = json_decode($response, true);

        // mark vouchers as consumed, so they won’t be sent in future games
        $vouchersUsed = true;
    }

    echo "<h3>Booking Response for Game: {$gameId}</h3>";
    echo "<p><b>holdId (used):</b> {$holdId}</p>";
    echo "<p><b>previousHoldId:</b> {$previousHoldId}</p>";
    echo "<p><b>Promotion Code Applied:</b> " . ($promoCode ?: "None") . "</p>";
    echo "<p><b>Gift Vouchers Used:</b> " . ((!$vouchersUsed) ? $voucherString : "None / Already Consumed") . "</p>";

    if ($priceInfo) {
        echo "<p><b>Initial Payment Amount (used for CC):</b> " . htmlspecialchars($priceInfo['amount']) . " " . htmlspecialchars($priceInfo['currency']) . "</p>";
    }

    if ($err) {
        echo "<pre>cURL Error: $err</pre>";
    } else {
        echo "<p>HTTP Code: {$httpCode}</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }

    if ($holdCount > 0 && isset($holdsForGame[$holdCount - 1]['price'])) {
        echo "<h4>Price Details from Hold</h4>";
        echo "<pre>" . htmlspecialchars(json_encode($holdsForGame[$holdCount - 1]['price'], JSON_PRETTY_PRINT)) . "</pre>";
    }

    if (in_array($httpCode, [200, 201])) {
        $stmtDelCart = $pdo->prepare("DELETE FROM tbl_carts WHERE session_id = ? AND game_id = ?");
        $stmtDelCart->execute([$sessionId, $gameId]);

        $stmtDelHold = $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE session_id = ? AND game_id = ?");
        $stmtDelHold->execute([$sessionId, $gameId]);

        unset($_SESSION['cart'][$index]);

        echo "<p style='color:green;'>Cart and hold data for game {$gameId} removed from DB and session.</p>";
    }

    echo "<hr>";
}
