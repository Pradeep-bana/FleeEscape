<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);

include "admin/db.php";
require_once('config.php');
require_once(__DIR__ . '/includes/bookeo_runtime.php');

// --- Global error deduplication tracker ---
$seenErrors = []; 

function logMsg($msg) {
    flee_system_log_message('save_session', $msg);
}

// --- Unique error handler ---
function getUniqueErrorMessage($apiResp) {
    global $seenErrors;
    $decoded = json_decode($apiResp['response'], true);
    $errorMsg = "Booking failed";

    if (isset($decoded['message'])) $errorMsg = $decoded['message'];
    elseif (!empty($apiResp['error'])) $errorMsg = $apiResp['error'];
    elseif (isset($decoded['error'])) $errorMsg = $decoded['error'];
    elseif (isset($decoded['errors'][0]['message'])) $errorMsg = $decoded['errors'][0]['message'];

    $errorMsg = trim(preg_replace('/^\[.*?\]\s*/', '', $errorMsg));
    $errorMsg = trim($errorMsg);

    if (!in_array($errorMsg, $seenErrors)) {
        $seenErrors[] = $errorMsg;
        logMsg("ERROR: " . $errorMsg);
    }

    return $errorMsg;
}

// --- helpers ---
function makeBookingAPI($bookingData, $previousHoldId) {
    global $apiKey, $secretKey;
    $queryParams = http_build_query([
        "previousHoldId" => $previousHoldId,
        "notifyUsers" => "false",
        "notifyCustomer" => "false"
    ]);
    $bookingUrl = "https://api.bookeo.com/v2/bookings?$queryParams";
    $apiResponse = flee_bookeo_request('POST', $bookingUrl, [
        'context' => 'save_session_booking_create',
        'timeout' => 30,
        'headers' => [
            "X-Bookeo-apiKey: $apiKey",
            "X-Bookeo-secretKey: $secretKey",
            "Content-Type: application/json",
            "Accept: application/json"
        ],
        'body' => json_encode($bookingData),
        'log_body' => true,
    ]);
    return ['httpCode' => $apiResponse['code'], 'response' => $apiResponse['body'], 'error' => $apiResponse['error']];
}

function isSuccess($apiResp) {
    return isset($apiResp['httpCode']) && in_array($apiResp['httpCode'], [200, 201]);
}

// --- extract customer data ---
$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$type = trim($_POST['type'] ?? '');
$giftCodeInput = trim($_POST['giftCode'] ?? ''); // Check POST first
$currency = 'USD';

// --- API keys ---
$apiKey = FLEE_BOOKEO_API_KEY;
$secretKey = FLEE_BOOKEO_SECRET_KEY;
$sessionId = session_id();
logMsg("Session ID: {$sessionId} | Starting booking process");

// --- fetch cart items ---
$stmt = $pdo->prepare("SELECT * FROM tbl_carts WHERE session_id = ? ORDER BY id ASC");
$stmt->execute([$sessionId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$cartItems) {
    logMsg("No cart items found for session {$sessionId}");
    die(json_encode(["status" => "error", "message" => "No cart items found"]));
}

// --- auto promo logic (BMSM) ---
$cartCount = count($cartItems);

 $stmt = $pdo->prepare("SELECT * FROM tbl_carts WHERE session_id=:sid");
    $stmt->execute([':sid' => $sessionId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $escapeItems = [];
    foreach ($rows as $r) {
        if (
            str_contains(strtolower($r['cat']), 'escape-room')
            && (str_contains($r['pramotion_page'], 'false')
                || str_contains($r['pramotion_page'], 'save_more_play_more'))
        ) {
            $escapeItems[] = $r;
        }
    }

    $escapeCount = count($escapeItems);



$autoPromo = "";
if ($escapeCount == 2) $autoPromo = "BMSM_10";
elseif ($escapeCount >= 3) $autoPromo = "BMSM_20";
 $autoPromo;
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
            'id' => $resp['id'],
            'price' => $resp['price'] ?? null,
            'raw' => $resp
        ];
    }
}

// --- user entered code ---
// Priority: POST data > SESSION data
$userEnteredCode = !empty($giftCodeInput) ? $giftCodeInput : trim($_SESSION['giftCode'] ?? "");

// --- Utility: build promotion string ---
function buildPromoListForItem($item, $includeAuto = true) {
    $promoList = [];
    global $autoPromo;
    // Add Auto Promo (BMSM_10/20)
    if ($includeAuto && $autoPromo) $promoList[] = $autoPromo;
    // Add Game Specific Promo (stored in cart)
    if (!empty($item['promo_code'])) $promoList[] = $item['promo_code'];
    return array_values(array_unique(array_filter($promoList)));
}

// --- attempt booking wrapper ---
function attemptBooking($bookingData, $previousHoldId) {
    $respAPI = makeBookingAPI($bookingData, $previousHoldId);
    $decoded = json_decode($respAPI['response'], true);
    return ['api' => $respAPI, 'decoded' => $decoded];
}

// --- Save booking to DB ---
function saveBookingToDB($pdo, $decodedResp, $item, $userId, $addonName, $addonPrice, $addonQty, $addonSubtotal) {
    $stmtInsert = $pdo->prepare("
        INSERT INTO tbl_bookings
        (bookingNumber, eventId, startTime, endTime, customerId, title, productId, productName, privateEvent, noShow, canceled, accepted, creationTime, creationAgent, totalGross, totalNet, totalTaxes, totalPaid, priceJson, priceAdjustments, participantsJson, taxesJson, user_id, addon_title, addon_price, addon_qty, addon_subtotal)
        VALUES
        (:bookingNumber, :eventId, :startTime, :endTime, :customerId, :title, :productId, :productName, :privateEvent, :noShow, :canceled, :accepted, :creationTime, :creationAgent, :totalGross, :totalNet, :totalTaxes, :totalPaid, :priceJson, :priceAdjustments, :participantsJson, :taxesJson, :user_id, :addon_title, :addon_price, :addon_qty, :addon_subtotal)
    ");
    $stmtInsert->execute([
        ":bookingNumber" => $decodedResp['bookingNumber'] ?? null,
        ":eventId" => $decodedResp['eventId'] ?? null,
        ":startTime" => $decodedResp['startTime'] ?? null,
        ":endTime" => $decodedResp['endTime'] ?? null,
        ":customerId" => $decodedResp['customerId'] ?? null,
        ":title" => $decodedResp['title'] ?? null,
        ":productId" => $decodedResp['productId'] ?? null,
        ":productName" => $decodedResp['productName'] ?? null,
        ":privateEvent" => !empty($decodedResp['privateEvent']) ? 1 : 0,
        ":noShow" => !empty($decodedResp['noShow']) ? 1 : 0,
        ":canceled" => !empty($decodedResp['canceled']) ? 1 : 0,
        ":accepted" => !empty($decodedResp['accepted']) ? 1 : 0,
        ":creationTime" => $decodedResp['creationTime'] ?? null,
        ":creationAgent" => $decodedResp['creationAgent'] ?? null,
        ":totalGross" => $decodedResp['price']['totalGross']['amount'] ?? null,
        ":totalNet" => $decodedResp['price']['totalNet']['amount'] ?? null,
        ":totalTaxes" => $decodedResp['price']['totalTaxes']['amount'] ?? null,
        ":totalPaid" => $decodedResp['price']['totalPaid']['amount'] ?? null,
        ":priceJson" => json_encode($decodedResp['price'] ?? []),
        ":priceAdjustments" => json_encode($decodedResp['priceAdjustments'] ?? []),
        ":participantsJson" => json_encode($decodedResp['participants'] ?? []),
        ":taxesJson" => json_encode($decodedResp['taxes'] ?? []),
        ":user_id" => $userId,
        ":addon_title" => $addonName,
        ":addon_price" => $addonPrice,
        ":addon_qty" => $addonQty,
        ":addon_subtotal" => $addonSubtotal
    ]);
}

// --- Prepare item templates ---
$itemTemplates = [];
foreach ($cartItems as $item) {
    $gameId = $item['game_id'] ?? '';
    $eventId = $item['event_id'] ?? '';
    $guests = (int)($item['guests'] ?? 0);
    $cat = strtolower(trim($item['cat'] ?? ''));
    $addGuests = (int)($item['additional_guest'] ?? 0);
    $perGuestPrice = (float)($item['per_guest_price'] ?? 0);
    $totalAddPrice = (float)($item['total_additional_price'] ?? 0);
    $escape_selection = $item['escape_selection'] ?? 0;
    $addonName = $item['addon_name'] ?? '';
    $addonQty = $item['addon_qty'] ?? 0;
    $addonPrice = $item['addon_price'] ?? 0;
    $addonSubtotal = $item['addon_subtotal'] ?? ($addonQty * $addonPrice);

    $holdsForGame = $holdMap[$gameId] ?? [];
    $previousHoldId = !empty($holdsForGame) ? end($holdsForGame)['id'] : null;

    $customer = [
        "firstName" => $firstName,
        "lastName" => $lastName,
        "emailAddress" => $email,
        "phoneNumbers" => [["number" => $phone, "type" => "mobile"]]
    ];

    $bookingData = [
        "productId" => $gameId,
        "eventId" => $eventId,
        "holdId" => $previousHoldId,
        "customer" => $customer,
        "participants" => ["numbers" => [["peopleCategoryId" => "Cadults", "number" => $guests]]],
        "status" => "booked"
    ];

    if ($cat === 'party-package' && $addGuests > 0) {
        $bookingData["priceAdjustments"] = [[
            "unitPrice" => ["amount" => number_format($perGuestPrice, 2, '.', ''), "currency" => $currency],
            "quantity" => $addGuests,
            "description" => "Additional Guests",
            "totalPrice" => ["amount" => number_format($totalAddPrice, 2, '.', ''), "currency" => $currency],
            "taxIds" => []
        ]];
    }
    if ($cat === 'party-package' && !empty($escape_selection)) {
        $bookingData["options"] = [["name" => "Escape Room Choices", "value" => $escape_selection]];
    }
    if ($addonName && $addonQty > 0) {
        if (!isset($bookingData["options"])) $bookingData["options"] = [];
        $opt_value = ($cat === 'party-package') ? 'true' : $addonQty;
        $bookingData["options"][] = ["name" => $addonName, "value" => $opt_value];
    }

    $itemTemplates[] = [
        "rawItem" => $item,
        "bookingData" => $bookingData,
        "previousHoldId" => $previousHoldId
    ];
}

// ================================================================
// MAIN BOOKING LOGIC (STRICT PROMO RULES)
// ================================================================

$responses = [];
$anySuccessfulBookings = false;
$paymentRequiredOverall = false;
$userId = null;

// Track strategy used for the first successful game ('gift', 'promo', 'auto')
$firstGameStrategy = null; 

$perItemPromoLists = array_map(fn($tpl) => buildPromoListForItem($tpl['rawItem'], true), $itemTemplates);

foreach ($itemTemplates as $idx => $tpl) {
    $item = $tpl['rawItem'];
    $previousHoldId = $tpl['previousHoldId'];
    $promoList = $perItemPromoLists[$idx]; // e.g. ['BMSM_10']

    $resp = ["gameId"=>$item['game_id'],"eventId"=>$item['event_id'],"status"=>"error","messages"=>[]];
    $attempts = [];

    /* -----------------------------------------------------
       STRATEGY DEFINITION
    ----------------------------------------------------- */

    // Is this the first game in the cart?
    $isFirstGame = ($idx === 0);

    if ($isFirstGame) {
        // === GAME 1 LOGIC ===
        if (!empty($userEnteredCode)) {
            // Rule: "User Input Code" exists. MUST validate it. NO fallback to Auto only if this fails.
            
            // Attempt 1: As Gift Code + Auto Promos
            $attemptGift = $tpl['bookingData'];
            $attemptGift["giftVoucherCodeInput"] = $userEnteredCode;
            if (!empty($promoList)) $attemptGift["promotionCodeInput"] = implode(",", $promoList);
            $attempts[] = ['type' => 'gift', 'data' => $attemptGift];

            // Attempt 2: As Promo Code (User Code combined with Auto Promos)
            $attemptPromo = $tpl['bookingData'];
            $combinedPromos = array_merge([$userEnteredCode], $promoList); // User Code First
            $attemptPromo["promotionCodeInput"] = implode(",", $combinedPromos);
            $attempts[] = ['type' => 'promo', 'data' => $attemptPromo];

            // NOTE: We do NOT add a 3rd attempt for Auto-Promo only here. 
            // If the user entered a code and it fails both Gift and Promo checks, 
            // the instructions say "generate error... booking nahi hogi".
        } else {
            // Rule: No User Input. Use Auto Promos.
            $attemptAuto = $tpl['bookingData'];
            if (!empty($promoList)) $attemptAuto["promotionCodeInput"] = implode(",", $promoList);
            $attempts[] = ['type' => 'auto', 'data' => $attemptAuto];
        }

    } else {
        // === GAME 2+ LOGIC ===
        // Dependent on what happened in Game 1
        
        if (!empty($userEnteredCode)) {
            // Strategy based on Game 1 success
            if ($firstGameStrategy === 'gift') {
                // Game 1 used Gift Code successfully. Try reusing it.
                $attemptGift = $tpl['bookingData'];
                $attemptGift["giftVoucherCodeInput"] = $userEnteredCode;
                if (!empty($promoList)) $attemptGift["promotionCodeInput"] = implode(",", $promoList);
                $attempts[] = ['type' => 'gift', 'data' => $attemptGift];
                
                // Fallback allowed for Game 2+ ("if error do booking without gift code")
                $attemptAuto = $tpl['bookingData'];
                if (!empty($promoList)) $attemptAuto["promotionCodeInput"] = implode(",", $promoList);
                $attempts[] = ['type' => 'auto', 'data' => $attemptAuto];

            } elseif ($firstGameStrategy === 'promo') {
                // Game 1 used User Code as Promo. Try reusing it.
                $attemptPromo = $tpl['bookingData'];
                $combinedPromos = array_merge([$userEnteredCode], $promoList);
                $attemptPromo["promotionCodeInput"] = implode(",", $combinedPromos);
                $attempts[] = ['type' => 'promo', 'data' => $attemptPromo];

                // Fallback allowed for Game 2+
                $attemptAuto = $tpl['bookingData'];
                if (!empty($promoList)) $attemptAuto["promotionCodeInput"] = implode(",", $promoList);
                $attempts[] = ['type' => 'auto', 'data' => $attemptAuto];
                
            } else {
                // Should not happen if userCode exists but logic falls here (e.g. Game 1 failed?)
                // Just try standard
                $attemptAuto = $tpl['bookingData'];
                if (!empty($promoList)) $attemptAuto["promotionCodeInput"] = implode(",", $promoList);
                $attempts[] = ['type' => 'auto', 'data' => $attemptAuto];
            }
        } else {
            // No user code
            $attemptAuto = $tpl['bookingData'];
            if (!empty($promoList)) $attemptAuto["promotionCodeInput"] = implode(",", $promoList);
            $attempts[] = ['type' => 'auto', 'data' => $attemptAuto];
        }
    }

    /* -----------------------------------------------------
       EXECUTE BOOKING ATTEMPTS
    ----------------------------------------------------- */
    $success = false;

    foreach ($attempts as $at) {
        $r = attemptBooking($at['data'], $previousHoldId);

        if (isSuccess($r['api'])) {
            $decodedResp = $r['decoded'];
            $resp['status'] = 'booked';
            $resp['messages'][] = "Booking confirmed successfully";
            $success = true;


$appliedPromoCode = null;
if (isset($at['data']['promotionCodeInput']) && !empty($at['data']['promotionCodeInput'])) {
    // multiple promo codes separated by commas → we only store the user input code
    $parts = explode(",", $at['data']['promotionCodeInput']);
    $appliedPromoCode = trim($parts[0]); // The user-input coupon is always first
}

// If user entered code and it was accepted either as gift or promo
if (!empty($userEnteredCode) && $appliedPromoCode === $userEnteredCode) {

    // Calculate discount from Bookeo API response
  $discountAmt = 0;

if (!empty($decodedResp['price']['totalNet']['amount'])) {
    $discountAmt = (float)$decodedResp['price']['totalNet']['amount'];
}
$promoTaxes = [];
if (!empty($decodedResp['price']['taxes'])) {
    $promoTaxes = $decodedResp['price']['taxes'];
}
    // Update tbl_carts
 $stmtUpdatePromo = $pdo->prepare("
    UPDATE tbl_carts
    SET pramotion_page = 'user-input',
        promo_code = :promo,
        promocode_dis = :discount,
        promocode_tax = :taxes
    WHERE session_id = :sid AND event_id = :eid
    LIMIT 1
");

$stmtUpdatePromo->execute([
    ":promo"    => $userEnteredCode,
    ":discount" => $discountAmt,
    ":taxes"    => json_encode($promoTaxes),
    ":sid"      => $sessionId,
    ":eid"      => $item['event_id']
]);
   
}



            // Set strategy if it's the first game
            if ($isFirstGame) {
                $firstGameStrategy = $at['type'];
            }

            // Save user
            $stmtUser = $pdo->prepare("SELECT id FROM tbl_users WHERE email=? LIMIT 1");
            $stmtUser->execute([$email]);
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
            if ($user) $userId = $user['id'];
            else {
                $stmtInsertUser = $pdo->prepare("INSERT INTO tbl_users (firstName,lastName,email,phone,type,created_at) VALUES (?,?,?,?,?,NOW())");
                $stmtInsertUser->execute([$firstName,$lastName,$email,$phone,$type]);
                $userId = $pdo->lastInsertId();
            }
            $_SESSION['user_id'] = $userId;
            if (!isset($_SESSION['booking_numbers'])) $_SESSION['booking_numbers'] = [];
            $_SESSION['booking_numbers'][] = $decodedResp['bookingNumber'] ?? '';

            $totalGross = $decodedResp['price']['totalGross']['amount'] ?? 0;
            $totalPaid = $decodedResp['price']['totalPaid']['amount'] ?? 0;
            if ($totalGross != $totalPaid) $paymentRequiredOverall = true;

            $addonName     = $item['addon_name'] ?? '';
            $addonQty      = $item['addon_qty'] ?? 0;
            $addonPrice    = $item['addon_price'] ?? 0;
            $addonSubtotal = $item['addon_subtotal'] ?? ($addonQty * $addonPrice);

            saveBookingToDB($pdo, $decodedResp, $item, $userId, $addonName, $addonPrice, $addonQty, $addonSubtotal);

            break; // Stop attempts for this item on success
        }
    }

    if (!$success) {
        // Collect error from the last failed attempt (usually most relevant) or first one
        if (empty($seenErrors)) $resp['messages'][] = getUniqueErrorMessage($r['api']);
        
        // CRITICAL: If Game 1 fails, we must stop the entire process.
        // We cannot book Game 2 if Game 1 failed in a sequential cart.
        $responses[] = $resp;
        break; // Break main loop
    }

    $responses[] = $resp;
    if ($success) $anySuccessfulBookings = true;
}

// ================================================================
// POST-BOOKING CLEANUP & REDIRECT
// ================================================================

$payment_required = true;
// Check if ALL attempted items were booked (simple check: response count vs cart count, and last status)
$allSuccess = (count($responses) == count($cartItems)) && ($responses[count($responses)-1]['status'] == 'booked');

if ($allSuccess && $anySuccessfulBookings && !$paymentRequiredOverall) {
    $payment_required = false;

    $_SESSION['booking_summary'] = [];
    if (!empty($_SESSION['booking_numbers'])) {
        $stmtUpdate = $pdo->prepare("UPDATE tbl_bookings SET status = 1 WHERE bookingNumber = ?");
        $stmtSelect = $pdo->prepare("SELECT totalGross FROM tbl_bookings WHERE bookingNumber = ? LIMIT 1");

        foreach ($_SESSION['booking_numbers'] as $bn) {
            if (empty($bn)) continue;
            $stmtUpdate->execute([$bn]);

            $amountVal = 0.00;
            $currencyVal = $currency;
            $stmtSelect->execute([$bn]);
            $row = $stmtSelect->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $totalGrossFromDb = $row['totalGross'] ?? 0;
                if (is_numeric($totalGrossFromDb)) $amountVal = number_format($totalGrossFromDb / 100, 2, '.', '');
            }

            $_SESSION['booking_summary'][] = [
                "bookingNumber" => $bn,
                "status" => "booked",
                "time" => date('Y-m-d H:i:s'),
                "amount" => $amountVal,
                "currency" => $currencyVal
            ];
        }
    }

    $stmtDelCart = $pdo->prepare("DELETE FROM tbl_carts WHERE session_id = ?");
    $stmtDelCart->execute([$sessionId]);

    $stmtDelHold = $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE session_id = ?");
    $stmtDelHold->execute([$sessionId]);

    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $k => $v) unset($_SESSION['cart'][$k]);
    }

    if (isset($_SESSION['giftCode'])) unset($_SESSION['giftCode']);

    $redirectUrl = BASE_URL."booking-confirmation.php";
} else {
    // If partial failure or payment required
    $payment_required = true;
    $redirectUrl = null;
    
    // If we had a partial failure (e.g. Game 1 success, Game 2 fail), we might need to rollback Game 1
    // But Bookeo API logic usually implies manual cancellation if not fully integrated transaction.
    // For this scope, we just return error.
}

$uniqueErrorMessage = !empty($seenErrors) ? $seenErrors[0] : "Booking failed";

$output = [
    "status" => $allSuccess ? "success" : "error", // Status is error if not ALL items booked
    "paymentRequired" => $paymentRequiredOverall,
    "bookings" => $responses,
    "payment_required" => $payment_required,
    "redirectUrl" => $redirectUrl
];

if (!$allSuccess && !empty($seenErrors)) $output["message"] = $uniqueErrorMessage;

header('Content-Type: application/json');
echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
