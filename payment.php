<?php
session_start();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

ob_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Require Composer autoload
require __DIR__ . '/vendor/autoload.php';
use Square\SquareClient;
use Square\Models\Money;
use Square\Models\CreatePaymentRequest;

// Include DB connection
include "admin/db.php";

// Logging helper
$logFile = __DIR__ . "/square_payment_debug.log";
function logMsg($msg) {
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] " . $msg . "\n", FILE_APPEND);
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $nonce = $input['sourceId'] ?? null;
    $currency = $input['currency'] ?? 'USD';

    if (!$nonce) {
        http_response_code(400);
        echo json_encode(["error" => "Missing card nonce"]);
        ob_end_flush();
        exit;
    }

    $sessionId = session_id();
    
    $bookings = [];
 foreach ($_SESSION['booking_numbers'] as $a => $bn) {
    // Fetch pending bookings
    $stmt = $pdo->prepare("SELECT * FROM tbl_bookings WHERE status = 0 AND bookingNumber = :bookingNumber");
    $stmt->execute([':bookingNumber' => $bn ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row){
        $bookings[] = $row;
    }
 }



    if (!$bookings) {
        logMsg("No pending bookings found for user_id {$_SESSION['user_id']}");
        echo json_encode(["status"=>"error","message"=>"No pending bookings found"]);
        ob_end_flush();
        exit;
    }

    // Calculate total amount in cents
    $totalAmountCents = 0;
    foreach ($bookings as $b) {
        $price = json_decode($b['priceJson'], true);
        if ($price && isset($price['totalGross']['amount'], $price['totalPaid']['amount'])) {
            $gross = (float)$price['totalGross']['amount'];
            $paid  = (float)$price['totalPaid']['amount'];
            $amountCents = (int)round(($gross - $paid) * 100);
            $totalAmountCents += $amountCents;
        }
    }

    if ($totalAmountCents <= 0) {
        logMsg("Total amount calculated is zero or negative.");
        echo json_encode(["status"=>"error","message"=>"No payment due"]);
        ob_end_flush();
        exit;
    }

    logMsg("Total payment amount: $" . number_format($totalAmountCents/100, 2) . " ($totalAmountCents cents)");

    // Square client
    $client = new SquareClient([
        'accessToken' => 'EAAAl8LlAZtvXu4na9gLonjCDfnJuZSxon16pkFR5bF89CDBduo9HJ-s1wvP5SpX',
        'environment' => 'sandbox'
    ]);
    $paymentsApi = $client->getPaymentsApi();

    $money = new Money();
    $money->setAmount($totalAmountCents);
    $money->setCurrency($currency);

    $request = new CreatePaymentRequest($nonce, uniqid(), $money);
    $request->setLocationId('L8XX876JN6ZSH');

    logMsg("Square Payment request: " . json_encode($input));

    $response = $paymentsApi->createPayment($request);

    if (!$response->isSuccess()) {
        $errors = $response->getErrors();
        logMsg("Square API errors: " . json_encode($errors));
        http_response_code(400);
        echo json_encode(["errors"=>$errors]);
        ob_end_flush();
        exit;
    }

    $paymentData = $response->getResult()->getPayment();
    $status = $paymentData->getStatus();
    logMsg("Square Payment status: " . $status);

    $res = ["payment" => $paymentData->jsonSerialize(), "bookeo_update" => []];

    if ($status === "COMPLETED") {
        $apiKey = 'AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC';
        $secretKey = 'RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4';

        foreach ($bookings as $index => $b) {
            $bookingNumber = $b['bookingNumber'];
            $price = json_decode($b['priceJson'], true);
            $amountCents = (int)round((floatval($price['totalGross']['amount']) - floatval($price['totalPaid']['amount'])) * 100);

            if ($amountCents <= 0) continue;

            // Bookeo payment
            $paymentPayload = [
                "receivedTime"       => $paymentData->getCreatedAt(),
                "reason"             => "Normal payment",
                "comment"            => "Square payment ID " . $paymentData->getId(),
                "amount"             => [
                    "amount"   => number_format($amountCents / 100, 2, '.', ''),
                    "currency" => $currency
                ],
                "paymentMethod"      => "creditCard",
                "paymentMethodOther" => $paymentData->getCardDetails()->getCard()->getCardBrand() . " **** " . $paymentData->getCardDetails()->getCard()->getLast4()
            ];

            $url = "https://api.bookeo.com/v2/bookings/" . urlencode($bookingNumber) . "/payments";
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($paymentPayload),
                CURLOPT_HTTPHEADER => [
                    "X-Bookeo-apiKey: $apiKey",
                    "X-Bookeo-secretKey: $secretKey",
                    "Content-Type: application/json",
                    "Accept: application/json"
                ]
            ]);

            $bookeoResp = curl_exec($ch);
            $err = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($err) {
                logMsg("Bookeo POST /payments Error for {$bookingNumber}: $err");
                $res["bookeo_update"][$bookingNumber] = ["error" => $err];
            } else {
                logMsg("Bookeo POST /payments Response [{$httpCode}] for {$bookingNumber}: " . $bookeoResp);
                $res["bookeo_update"][$bookingNumber] = [
                    "httpCode" => $httpCode,
                    "response" => json_decode($bookeoResp, true)
                ];

                // Update booking status
                $upd = $pdo->prepare("UPDATE tbl_bookings SET status = 1 WHERE bookingNumber = :booking");
                $upd->execute([':booking' => $bookingNumber]);
// Fetch addon values stored in DB
$addonTitle = $b['addon_title'] ?? '';
$addonQty   = (int)($b['addon_qty'] ?? 0);

// Build options array ONLY if addon exists
$options = [];
if (!empty($addonTitle) && $addonQty > 0) {
    $options[] = [
        "name"     => $addonTitle,
        "quantity" => $addonQty
    ];
}


$priceAdjustments = [];
if (!empty($b['priceAdjustments'])) {
    $decoded = json_decode($b['priceAdjustments'], true);
    if (is_array($decoded)) {
        $priceAdjustments = $decoded;
    }
}


                // Edit Bookeo booking to trigger confirmation
                $participants = json_decode($b['participantsJson'], true) ?: ["numbers" => []];
                $editPayload = [
                    "bookingNumber" => $bookingNumber,
                    "productId"     => $b['productId'],
                    "eventId"       => $b['eventId'],
                    "participants"  => $participants
                ];
                
                if (!empty($options)) {
    $editPayload["options"] = $options;
}

// Attach priceAdjustments to edit payload if exists
if (!empty($priceAdjustments)) {
    $editPayload["priceAdjustments"] = $priceAdjustments;
}
                $queryParams = http_build_query([
                    "notifyUsers"=>"true",
                    "notifyCustomer"=>"true"
                ]);
                $urlPut = "https://api.bookeo.com/v2/bookings/" . urlencode($bookingNumber) . "?$queryParams";
                $chPut = curl_init();
                curl_setopt_array($chPut, [
                    CURLOPT_URL => $urlPut,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => "PUT",
                    CURLOPT_POSTFIELDS => json_encode($editPayload),
                    CURLOPT_HTTPHEADER => [
                        "X-Bookeo-apiKey: $apiKey",
                        "X-Bookeo-secretKey: $secretKey",
                        "Content-Type: application/json",
                        "Accept: application/json"
                    ]
                ]);

                $bookeoPutResp = curl_exec($chPut);
                $errPut = curl_error($chPut);
                $httpPutCode = curl_getinfo($chPut, CURLINFO_HTTP_CODE);
                curl_close($chPut);

                if ($errPut) {
                    logMsg("Bookeo PUT /bookings Error for {$bookingNumber}: $errPut");
                    $res["bookeo_update"][$bookingNumber]['edit'] = ["error" => $errPut];
                } else {
                    logMsg("Bookeo PUT /bookings Response [{$httpPutCode}] for {$bookingNumber}: " . $bookeoPutResp);
                    $res["bookeo_update"][$bookingNumber]['edit'] = [
                        "httpCode" => $httpPutCode,
                        "response" => json_decode($bookeoPutResp, true)
                    ];
                }

                // --- Delete cart entry using productId ---
                $stmtDelCart = $pdo->prepare("DELETE FROM tbl_carts WHERE session_id = ? ");
                $stmtDelCart->execute([$sessionId]);
                logMsg("Cart cleanup - Rows affected: " . $stmtDelCart->rowCount());

                // --- Delete hold entry using gameId ---
                $stmtDelHold = $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE session_id = ? ");
                $stmtDelHold->execute([$sessionId]);
                logMsg("Hold cleanup - Rows affected: " . $stmtDelHold->rowCount());

                // --- Clean session cart ---
                if (isset($_SESSION['cart'][$index])) {
                    unset($_SESSION['cart'][$index]);
                    logMsg("Session cart item {$index} removed");
                }
                if (isset($_SESSION['giftCode'])) {
                    unset($_SESSION['giftCode']);
                    logMsg("GiftCode unset from session");
                    $res['giftCodeCleared'] = true; // tell JS that we cleared giftCode
                }
            }
        } // end foreach bookings

        // === POPULATE booking_summary SESSION ONCE (AFTER PROCESSING ALL BOOKINGS) ===
        $_SESSION['booking_summary'] = []; // reset old data if any

        foreach ($res["bookeo_update"] as $bookingNumber => $data) {
            // Accept 200 or 201 as success (Bookeo may return 201 Created)
            $httpCode = isset($data["httpCode"]) ? (int)$data["httpCode"] : 0;

            if ($httpCode === 200 || $httpCode === 201) {
                if (!empty($bookingNumber)) {
                    $_SESSION['booking_summary'][] = [
                        "bookingNumber" => $bookingNumber,
                        "status"        => "booked",
                        "time"          => date('Y-m-d H:i:s'),
                        "paymentId"     => $paymentData->getId(),
                        "amount"        => number_format($totalAmountCents / 100, 2, '.', ''),
                        "currency"      => $currency
                    ];
                }
            }
        }

        logMsg("✅ booking_summary session created: " . json_encode($_SESSION['booking_summary']));
    }

    echo json_encode($res);

} catch (Exception $e) {
    http_response_code(500);
    $error_message = "Server error: " . $e->getMessage();
    logMsg($error_message);
    echo json_encode(["error" => $error_message]);
}

ob_end_flush();
