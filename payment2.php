<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include "admin/db.php";
require __DIR__ . '/vendor/autoload.php';

use Square\SquareClient;
use Square\Models\Money;
use Square\Models\CreatePaymentRequest;

// --- Logging ---
$logFile = __DIR__ . "/square_booking_debug.log";
function logMsg($msg) {
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] " . $msg . "\n", FILE_APPEND);
}

header("Content-Type: application/json");

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $nonce = $input['sourceId'] ?? null;
    $currency = $input['currency'] ?? 'USD';

    if (!$nonce) {
        http_response_code(400);
        echo json_encode(["error" => "Missing card nonce"]);
        exit;
    }

    $sessionId = session_id();

    // --- fetch holds ---
    $stmtHold = $pdo->prepare("SELECT * FROM tbl_bookeo_holds WHERE session_id = ? ORDER BY id ASC");
    $stmtHold->execute([$sessionId]);
    $holdRows = $stmtHold->fetchAll(PDO::FETCH_ASSOC);

    if (!$holdRows) {
        logMsg("No holds for session {$sessionId}");
        echo json_encode(["status" => "error", "message" => "No pending holds found"]);
        exit;
    }

    // --- map holds by gameId ---
    $holdMap = [];
    foreach ($holdRows as $row) {
        $resp = json_decode($row['response_json'], true);
        if ($resp && isset($resp['id'])) {
            $holdMap[$row['game_id']][] = [
                "id"    => $resp['id'],
                "price" => $resp['price'] ?? null,
                "raw"   => $resp
            ];
        }
    }

    // --- calculate total payable (from holds) ---
    $totalAmountCents = 0;
    $priceInfo = ['amount' => 0, 'currency' => $currency];
    foreach ($holdRows as $h) {
        $respJson = json_decode($h['response_json'], true);
        if (isset($respJson['totalPayable']['amount'])) {
            $amt = (float)$respJson['totalPayable']['amount'];
            $totalAmountCents += (int)round($amt * 100);
            $priceInfo = [
                'amount'   => number_format($amt, 2, '.', ''),
                'currency' => $respJson['totalPayable']['currency'] ?? $currency
            ];
        }
    }

    // --- include additional guest total from cart ---
    $addonTotalCents = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $cat = strtolower(trim($item['cat'] ?? ''));
            $totalAddPrice = (float)($item['total_additional_price'] ?? 0);
            if ($cat === 'party-package' && $totalAddPrice > 0) {
                $addonTotalCents += (int)round($totalAddPrice * 100);
            }
        }
    }

    if ($addonTotalCents > 0) {
        logMsg("Additional guest total added to payment: " . ($addonTotalCents / 100) . " {$currency}");
    }

    // --- updated final total (includes addon) ---
 $finalAmountCents = $totalAmountCents + $addonTotalCents;

    if ($finalAmountCents <= 0) {
        echo json_encode(["status" => "error", "message" => "No payment due"]);
        exit;
    }

    // --- Square Payment ---
    $client = new SquareClient([
        'accessToken' => 'EAAAl8LlAZtvXu4na9gLonjCDfnJuZSxon16pkFR5bF89CDBduo9HJ-s1wvP5SpX',
        'environment' => 'sandbox'
    ]);
    $paymentsApi = $client->getPaymentsApi();

    $money = new Money();
    $money->setAmount($finalAmountCents);
    $money->setCurrency($currency);

    $request = new CreatePaymentRequest($nonce, uniqid(), $money);
    $request->setLocationId('L8XX876JN6ZSH');
    $response = $paymentsApi->createPayment($request);

    if (!$response->isSuccess()) {
        echo json_encode(["errors" => $response->getErrors()]);
        exit;
    }

    $paymentData = $response->getResult()->getPayment();
    if ($paymentData->getStatus() !== "COMPLETED") {
        echo json_encode(["status" => "error", "message" => "Payment not completed"]);
        exit;
    }

    // --- Bookeo Booking ---
    $apiKey    = 'AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC';
    $secretKey = 'RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4';

    $firstName = $_SESSION['firstName'] ?? $input['firstName'] ?? '';
    $lastName  = $_SESSION['lastName'] ?? $input['lastName'] ?? '';
    $email     = $_SESSION['email'] ?? $input['email'] ?? '';
    $phone     = $_SESSION['phone'] ?? $input['phone'] ?? '';
    $type      = $_SESSION['type'] ?? $input['type'] ?? '';

    $customer = [
        "firstName"    => $firstName,
        "lastName"     => $lastName,
        "emailAddress" => $email,
        "phoneNumbers" => [["number" => $phone, "type" => "mobile"]]
    ];

    $responses = [];

$stmtCart = $pdo->prepare("SELECT * FROM `tbl_carts` WHERE session_id = ?");
$stmtCart->execute([$sessionId]);
$cartItems = $stmtCart->fetchAll(PDO::FETCH_ASSOC);

if (!$cartItems) {
    logMsg("No cart items found for session {$sessionId}");
    echo json_encode(["status" => "error", "message" => "No cart items found"]);
    exit;
}

// --- loop through cart items ---
$responses = [];

foreach ($cartItems as $item) {
    $gameId  = $item['game_id'] ?? '';
    $eventId = $item['event_id'] ?? '';
    $guests  = (int)($item['guests'] ?? 0);
    $cat     = strtolower(trim($item['cat'] ?? ''));
    $addGuests     = (int)($item['additional_guest'] ?? 0);
    $perGuestPrice = (float)($item['per_guest_price'] ?? 0);
    $totalAddPrice = (float)($item['total_additional_price'] ?? 0);
    $escape_selection = $item['escape_selection'] ?? 0;

    logMsg("------ CART ITEM DETAILS ------");
    logMsg("GameID: {$gameId} | EventID: {$eventId} | Category: {$cat} | Guests: {$guests} | AddGuests: {$addGuests}");
    logMsg("PerGuestPrice: {$perGuestPrice} | TotalAddPrice: {$totalAddPrice}");
    logMsg("------------------------------");

    if (!$gameId || !$eventId || $guests <= 0) {
        $responses[] = ["gameId" => $gameId, "status" => "skipped", "message" => "Invalid cart item"];
        continue;
    }

    $holdsForGame = $holdMap[$gameId] ?? [];
    if (empty($holdsForGame)) {
        $responses[] = ["gameId" => $gameId, "status" => "skipped", "message" => "No hold found"];
        logMsg("No hold found for GameID {$gameId}");
        continue;
    }

    // --- consume the first available hold for this game (prevents reusing the same hold) ---
    $holdItem = array_shift($holdMap[$gameId]); // removes the hold from array
    $holdId = $holdItem['id'] ?? null;
    $holdRaw = $holdItem['raw'] ?? [];

    // Determine per-item amount (use hold's totalPayable if present, else fall back to derived value)
    $itemAmount = 0.00;
    if (!empty($holdRaw['totalPayable']['amount'])) {
        $itemAmount = (float)$holdRaw['totalPayable']['amount'];
    } else {
        // Fallback: use cart values (guests * perGuestPrice + totalAddPrice) if available
        $base = max(0, (float)($item['base_price'] ?? 0));
        $calc = ($guests * $perGuestPrice) + $totalAddPrice + $base;
        $itemAmount = round($calc, 2);
    }
    $itemAmountCents = (int)round($itemAmount * 100);

    logMsg("Using holdId {$holdId} for GameID {$gameId} with itemAmount {$itemAmount} {$currency}");

    // Build booking payload for THIS item
    $bookingData = [
        "productId" => $gameId,
        "eventId"   => $eventId,
        "holdId"    => $holdId,
        "customer"  => $customer,
        "participants" => [
            "numbers" => [
                // Ensure this peopleCategoryId is valid for your Bookeo product (replace if needed)
                ["peopleCategoryId" => "Cadults", "number" => $guests]
            ]
        ],
        "status" => "booked",
        "initialPayments" => [[
            "receivedTime" => date('c'),
            "reason"       => "Full payment",
            "comment"      => "Credit card payment",
            "amount"       => [
                "amount"   => number_format($itemAmount, 2, '.', ''),
                "currency" => $currency
            ],
            "paymentMethod" => "creditCard",
            "paymentMethodOther" => $paymentData->getCardDetails()->getCard()->getCardBrand()
                . " **** " . $paymentData->getCardDetails()->getCard()->getLast4()
        ]]
    ];

    if ($cat === 'party-package' && !empty($escape_selection)) {
        $bookingData["options"] = [
            ["name" => "Escape Room Choice", "value" => $escape_selection]
        ];
    }

    if ($cat === 'party-package' && $addGuests > 0 && $perGuestPrice > 0 && $totalAddPrice > 0) {
        $bookingData["priceAdjustments"] = [[
            "unitPrice" => [
                "amount"   => number_format($perGuestPrice, 2, '.', ''),
                "currency" => $currency
            ],
            "quantity"   => $addGuests,
            "description"=> "Additional Guests",
            "totalPrice" => [
                "amount"   => number_format($totalAddPrice, 2, '.', ''),
                "currency" => $currency
            ],
            "taxIds" => []
        ]];
    }

    // --- Send booking request to Bookeo for THIS item ---
    $queryParams = http_build_query([
        "previousHoldId" => $holdId,
        "notifyUsers"    => "true",
        "notifyCustomer" => "true"
    ]);
    $bookingUrl = "https://api.bookeo.com/v2/bookings?$queryParams";

    logMsg("----- BOOKEO API CALL START for GameID {$gameId} -----");
    logMsg("Booking URL: " . $bookingUrl);
    logMsg("Booking Payload: " . json_encode($bookingData, JSON_PRETTY_PRINT));

    $ch = curl_init();
    curl_setopt_array($ch, [
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
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    logMsg("HTTP Code: {$httpCode}");
    if ($err) {
        logMsg("cURL Error: " . $err);
    }
    logMsg("Bookeo Raw Response: " . $resp);
    logMsg("----- BOOKEO API CALL END -----\n");

    $decodedResp = json_decode($resp, true);

    // --- immediately insert this booking into DB (so each booking is recorded) ---
    try {
        $stmtInsert = $pdo->prepare("
            INSERT INTO tbl_bookings 
            (bookingNumber, eventId, startTime, endTime, customerId, title, productId, productName, privateEvent, noShow, canceled, accepted, creationTime, creationAgent, totalGross, totalNet, totalTaxes, totalPaid, priceJson, priceAdjustments, participantsJson, taxesJson, user_id)
            VALUES
            (:bookingNumber, :eventId, :startTime, :endTime, :customerId, :title, :productId, :productName, :privateEvent, :noShow, :canceled, :accepted, :creationTime, :creationAgent, :totalGross, :totalNet, :totalTaxes, :totalPaid, :priceJson, :priceAdjustments, :participantsJson, :taxesJson, :user_id)
        ");

        // ensure user exists or create
        $stmtUser = $pdo->prepare("SELECT id FROM tbl_users WHERE email=? LIMIT 1");
        $stmtUser->execute([$email]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $userId = $user['id'];
        } else {
            $stmtInsertUser = $pdo->prepare("INSERT INTO tbl_users (firstName,lastName,email,phone,type,created_at) VALUES (?,?,?,?,?,NOW())");
            $stmtInsertUser->execute([$firstName,$lastName,$email,$phone,$type]);
            $userId = $pdo->lastInsertId();
        }
        $_SESSION['user_id'] = $userId;

        $stmtInsert->execute([
            ":bookingNumber"    => $decodedResp['bookingNumber'] ?? null,
            ":eventId"          => $decodedResp['eventId'] ?? null,
            ":startTime"        => $decodedResp['startTime'] ?? null,
            ":endTime"          => $decodedResp['endTime'] ?? null,
            ":customerId"       => $decodedResp['customerId'] ?? null,
            ":title"            => $decodedResp['title'] ?? null,
            ":productId"        => $decodedResp['productId'] ?? null,
            ":productName"      => $decodedResp['productName'] ?? null,
            ":privateEvent"     => !empty($decodedResp['privateEvent']) ? 1 : 0,
            ":noShow"           => !empty($decodedResp['noShow']) ? 1 : 0,
            ":canceled"         => !empty($decodedResp['canceled']) ? 1 : 0,
            ":accepted"         => !empty($decodedResp['accepted']) ? 1 : 0,
            ":creationTime"     => $decodedResp['creationTime'] ?? null,
            ":creationAgent"    => $decodedResp['creationAgent'] ?? null,
            ":totalGross"       => $decodedResp['price']['totalGross']['amount'] ?? 0,
            ":totalNet"         => $decodedResp['price']['totalNet']['amount'] ?? 0,
            ":totalTaxes"       => $decodedResp['price']['totalTaxes']['amount'] ?? 0,
            ":totalPaid"        => $decodedResp['price']['totalPaid']['amount'] ?? 0,
            ":priceJson"        => json_encode($decodedResp['price'] ?? []),
            ":priceAdjustments" => json_encode($decodedResp['priceAdjustments'] ?? []),
            ":participantsJson" => json_encode($decodedResp['participants'] ?? []),
            ":taxesJson"        => json_encode($decodedResp['price']['taxes'] ?? []),
            ":user_id"          => $userId
        ]);

        // store booking number in session summary immediately
        if (!isset($_SESSION['booking_summary'])) $_SESSION['booking_summary'] = [];
        if (!empty($decodedResp['bookingNumber'])) {
            $_SESSION['booking_summary'][] = [
                "bookingNumber" => $decodedResp['bookingNumber'],
                "eventId"       => $decodedResp['eventId'] ?? '',
                "productId"     => $decodedResp['productId'] ?? '',
                "productName"   => $decodedResp['productName'] ?? '',
                "status"        => $decodedResp['status'] ?? 'booked',
                "startTime"     => $decodedResp['startTime'] ?? '',
                "endTime"       => $decodedResp['endTime'] ?? ''
            ];
        }

    } catch (Exception $ex) {
        logMsg("DB insert error for GameID {$gameId}: " . $ex->getMessage());
    }

    // push response for this item
    $responses[] = [
        "gameId"   => $gameId,
        "eventId"  => $eventId,
        "httpCode" => $httpCode,
        "response" => $decodedResp,
        "error"    => $err
    ];
}

// end foreach cart items




  $stmtUser = $pdo->prepare("SELECT id FROM tbl_users WHERE email=? LIMIT 1");
        $stmtUser->execute([$email]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $userId = $user['id'];
        } else {
            $stmtInsertUser = $pdo->prepare("INSERT INTO tbl_users (firstName,lastName,email,phone,type,created_at) VALUES (?,?,?,?,?,NOW())");
            $stmtInsertUser->execute([$firstName,$lastName,$email,$phone,$type]);
            $userId = $pdo->lastInsertId();
        }
        $_SESSION['user_id'] = $userId;

        if (!isset($_SESSION['booking_numbers'])) $_SESSION['booking_numbers'] = [];
        if (!empty($decodedResp['bookingNumber'])) {
            $_SESSION['booking_numbers'][] = $decodedResp['bookingNumber'];
        }

        $stmtInsert = $pdo->prepare("
            INSERT INTO tbl_bookings 
            (bookingNumber, eventId, startTime, endTime, customerId, title, productId, productName, privateEvent, noShow, canceled, accepted, creationTime, creationAgent, totalGross, totalNet, totalTaxes, totalPaid, priceJson, priceAdjustments, participantsJson, taxesJson, user_id)
            VALUES
            (:bookingNumber, :eventId, :startTime, :endTime, :customerId, :title, :productId, :productName, :privateEvent, :noShow, :canceled, :accepted, :creationTime, :creationAgent, :totalGross, :totalNet, :totalTaxes, :totalPaid, :priceJson, :priceAdjustments, :participantsJson, :taxesJson, :user_id)
        ");

        $stmtInsert->execute([
            ":bookingNumber"    => $decodedResp['bookingNumber'] ?? null,
            ":eventId"          => $decodedResp['eventId'] ?? null,
            ":startTime"        => $decodedResp['startTime'],
            ":endTime"          => $decodedResp['endTime'],
            ":customerId"       => $decodedResp['customerId'] ?? null,
            ":title"            => $decodedResp['title'] ?? null,
            ":productId"        => $decodedResp['productId'] ?? null,
            ":productName"      => $decodedResp['productName'] ?? null,
            ":privateEvent"     => !empty($decodedResp['privateEvent']) ? 1 : 0,
            ":noShow"           => !empty($decodedResp['noShow']) ? 1 : 0,
            ":canceled"         => !empty($decodedResp['canceled']) ? 1 : 0,
            ":accepted"         => !empty($decodedResp['accepted']) ? 1 : 0,
            ":creationTime"     => $decodedResp['creationTime'],
            ":creationAgent"    => $decodedResp['creationAgent'] ?? null,
            ":totalGross"       => $decodedResp['price']['totalGross']['amount'] ?? 0,
            ":totalNet"         => $decodedResp['price']['totalNet']['amount'] ?? 0,
            ":totalTaxes"       => $decodedResp['price']['totalTaxes']['amount'] ?? 0,
            ":totalPaid"        => $decodedResp['price']['totalPaid']['amount'] ?? 0,
            ":priceJson"        => json_encode($decodedResp['price'] ?? []),
             ":priceAdjustments"        => json_encode($decodedResp['priceAdjustments'] ?? []),
            ":participantsJson" => json_encode($decodedResp['participants'] ?? []),
            ":taxesJson"        => json_encode($decodedResp['price']['taxes'] ?? []),
            ":user_id"          => $userId
        ]);



    // --- cleanup ---
    $pdo->prepare("DELETE FROM tbl_carts WHERE session_id=?")->execute([$sessionId]);
    $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE session_id=?")->execute([$sessionId]);
    unset($_SESSION['cart']);
    if (isset($_SESSION['giftCode'])) {
        unset($_SESSION['giftCode']);
        logMsg("GiftCode unset from session");
    }
  // --- create new session to store all booking IDs ---
    $_SESSION['booking_summary'] = []; // reset old summary if any

    foreach ($responses as $resp) {
        if (!empty($resp['response']['bookingNumber'])) {
            $_SESSION['booking_summary'][] = [
                "bookingNumber" => $resp['response']['bookingNumber'],
                "eventId"       => $resp['eventId'] ?? '',
                "productId"     => $resp['response']['productId'] ?? '',
                "productName"   => $resp['response']['productName'] ?? '',
                "status"        => $resp['response']['status'] ?? 'booked',
                "startTime"     => $resp['response']['startTime'] ?? '',
                "endTime"       => $resp['response']['endTime'] ?? ''
            ];
        }
    }
      
      
    logMsg("New booking_summary session created: " . json_encode($_SESSION['booking_summary']));
      
      
    echo json_encode([
        "status" => "success",
        "finalPayment" => number_format($finalAmountCents / 100, 2),
        "currency" => $currency,
        "bookings" => $responses
    ]);

} catch (Exception $e) {
    logMsg("Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
