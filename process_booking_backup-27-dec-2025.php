<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
$logFile = __DIR__ . "/final_transaction.log";
ini_set('error_log', $logFile);

include "admin/db.php";
require __DIR__ . '/vendor/autoload.php';

use Square\SquareClient;
use Square\Models\Money;
use Square\Models\CreatePaymentRequest;

function logMsg($msg) {
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] " . $msg . "\n", FILE_APPEND);
}

header("Content-Type: application/json");

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $nonce = $input['sourceId'] ?? null; 
    $currency = 'USD';
    $sessionId = session_id();

    // 1. Fetch Cart
    $stmt = $pdo->prepare("SELECT * FROM tbl_carts WHERE session_id = :sid");
    $stmt->execute([':sid' => $sessionId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$cartItems) throw new Exception("Cart is empty.");

    // 2. Fetch Holds
    $stmtHold = $pdo->prepare("SELECT * FROM tbl_bookeo_holds WHERE session_id = ?");
    $stmtHold->execute([$sessionId]);
    $holdRows = $stmtHold->fetchAll(PDO::FETCH_ASSOC);

    $holdMap = [];
    foreach ($holdRows as $h) {
        $hData = json_decode($h['response_json'], true);
        if (isset($hData['id'])) {
            $holdMap[$h['game_id']] = $hData;
        }
    }

    // 3. Calculate Final Payable Amount (Trust the Holds)
    $totalAmountCents = 0;
    
    foreach ($cartItems as $item) {
        $gid = $item['game_id'];
        $itemPayable = 0.0;
        
        if (isset($holdMap[$gid]) && isset($holdMap[$gid]['totalPayable']['amount'])) {
            $itemPayable = (float)$holdMap[$gid]['totalPayable']['amount'];
        } else {
            // Fallback
            $base = (float)$item['price'];
            $guests = (int)$item['guests'];
            $extras = (float)($item['total_additional_price'] ?? 0);
            $addon  = (float)($item['addon_subtotal'] ?? 0);
            $sub = ($base * $guests) + $extras + $addon;
            $taxEst = $sub * 0.153; 
            $itemPayable = $sub + $taxEst;
        }
        $totalAmountCents += (int)round($itemPayable * 100);
    }

    logMsg("Calculated Total Cents to Charge: " . $totalAmountCents);

    // 4. Process Payment (Square)
    $paymentId = null;
    $paymentDetails = "Voucher/Promo Covered";

    if ($totalAmountCents > 0) {
        if (!$nonce) throw new Exception("Payment required but no card token received.");

        $client = new SquareClient([
            'accessToken' => 'EAAAl8LlAZtvXu4na9gLonjCDfnJuZSxon16pkFR5bF89CDBduo9HJ-s1wvP5SpX',
            'environment' => 'sandbox'
        ]);

        $money = new Money();
        $money->setAmount($totalAmountCents);
        $money->setCurrency($currency);

        $req = new CreatePaymentRequest($nonce, uniqid(), $money);
        $req->setLocationId('L8XX876JN6ZSH'); 

        $apiResponse = $client->getPaymentsApi()->createPayment($req);

        if (!$apiResponse->isSuccess()) {
            $errs = $apiResponse->getErrors();
            $msg = $errs[0]->getDetail() ?? "Payment Failed";
            throw new Exception("Square Error: " . $msg);
        }

        $paymentObj = $apiResponse->getResult()->getPayment();
        if ($paymentObj->getStatus() !== 'COMPLETED') {
            throw new Exception("Payment status: " . $paymentObj->getStatus());
        }

        $paymentId = $paymentObj->getId();
        $cardInfo = $paymentObj->getCardDetails()->getCard();
        $paymentDetails = $cardInfo->getCardBrand() . " **** " . $cardInfo->getLast4();
        logMsg("Payment Success. ID: " . $paymentId);
    }

    // 5. Finalize Bookeo Bookings
    $apiKey    = 'AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC';
    $secretKey = 'RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4';
    
    $customer = [
        "firstName"    => $_SESSION['firstName'] ?? 'Guest',
        "lastName"     => $_SESSION['lastName'] ?? '',
        "emailAddress" => $_SESSION['email'] ?? '',
        "phoneNumbers" => [["number" => $_SESSION['phone'] ?? '', "type" => $_SESSION['type'] ?? 'mobile']]
    ];

    $bookedList = [];
    $errors = [];

    function callBookeo($payload, $key, $secret) {
        $url = "https://api.bookeo.com/v2/bookings";
        if (isset($payload['holdId'])) $url .= "?previousHoldId=" . $payload['holdId'];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ["X-Bookeo-apiKey: $key", "X-Bookeo-secretKey: $secret", "Content-Type: application/json"]
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['code' => $code, 'body' => $resp];
    }

    foreach ($cartItems as $item) {
        $gid = $item['game_id'];
        $holdData = $holdMap[$gid] ?? [];
        $holdId = $holdData['id'] ?? null;
        
        $bookingPayload = [
            "productId" => $gid,
            "eventId"   => $item['event_id'],
            "customer"  => $customer,
            "participants" => [ "numbers" => [["peopleCategoryId" => "Cadults", "number" => (int)$item['guests']]] ]
        ];

        if ($holdId) $bookingPayload['holdId'] = $holdId;

        // --- RETRIEVE SPECIFIC CODES FROM HOLD ---
        $specificPromo = $holdData['_internal_promo'] ?? '';
        $specificVouchers = $holdData['_internal_vouchers'] ?? '';
        
        // Safety Fallback: If Item still has Price > 0, and we have global codes not used
        $itemPayable = (float)($holdData['totalPayable']['amount'] ?? $item['price']);
        if ($itemPayable > 0 && empty($specificVouchers) && !empty($_SESSION['giftCode'])) {
            // Try injecting session codes if hold failed to capture them
            $specificVouchers = $_SESSION['giftCode'];
        }

        if (!empty($specificPromo)) {
            $bookingPayload['promotionCodeInput'] = $specificPromo;
        }
        if (!empty($specificVouchers)) {
            $bookingPayload['giftVoucherCodeInput'] = $specificVouchers;
        }

        // --- PAYMENT RECORDING ---
        $amountToRecord = $holdData['totalPayable']['amount'] ?? "0.00";
        if ((float)$amountToRecord > 0 && $totalAmountCents > 0) {
            $bookingPayload['initialPayments'] = [[
                "receivedTime" => date('c'),
                "reason"       => "Online Booking",
                "comment"      => "Square ID: $paymentId",
                "amount"       => ["amount" => $amountToRecord, "currency" => $currency],
                "paymentMethod" => "creditCard",
                "paymentMethodOther" => $paymentDetails
            ]];
        }

        // --- OPTIONS ---
        $options = [];
        if (!empty($item['escape_selection'])) $options[] = ["name" => "Escape Room Choice", "value" => $item['escape_selection']];
        if (!empty($item['addon_name']) && $item['addon_qty'] > 0) {
            $val = (strtolower($item['cat']) === 'party-package') ? 'true' : $item['addon_qty'];
            $options[] = ["name" => $item['addon_name'], "value" => $val];
        }
        if (!empty($options)) $bookingPayload['options'] = $options;
        
        if ((float)($item['total_additional_price'] ?? 0) > 0) {
            $bookingPayload['priceAdjustments'] = [[
                "description" => "Additional Guests",
                "totalPrice" => ["amount" => $item['total_additional_price'], "currency" => $currency]
            ]];
        }

        // --- EXECUTE ---
        $res = callBookeo($bookingPayload, $apiKey, $secretKey);

        if ($res['code'] == 201) {
            $bData = json_decode($res['body'], true);
            $bookingNum = $bData['bookingNumber'];
            $bookedList[] = $bookingNum;

            $stmtIns = $pdo->prepare("INSERT INTO tbl_bookings (bookingNumber, eventId, productId, customerId, totalGross, totalNet, user_id, status, created_at) VALUES (?,?,?,?,?,?,?,1,NOW())");
            $stmtIns->execute([
                $bookingNum,
                $item['event_id'],
                $gid,
                $bData['customerId'],
                $bData['price']['totalGross']['amount'],
                $bData['price']['totalNet']['amount'], 
                ($_SESSION['user_id'] ?? 0)
            ]);
        } else {
            $errDetails = "Bookeo Error for Game $gid: " . $res['body'];
            logMsg($errDetails);
            $errors[] = $errDetails;
        }
    }

    if (empty($bookedList) && !empty($errors)) {
        throw new Exception("Booking creation failed. " . implode("; ", $errors));
    }

    $pdo->prepare("DELETE FROM tbl_carts WHERE session_id=?")->execute([$sessionId]);
    $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE session_id=?")->execute([$sessionId]);
    
    $_SESSION['booking_summary'] = array_map(function($bn) {
        return ["bookingNumber" => $bn, "status" => "booked", "time" => date('Y-m-d')];
    }, $bookedList);

    echo json_encode(["status" => "success", "redirectUrl" => "booking-confirmation.php"]);

} catch (Exception $e) {
    logMsg("Critical Error: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>