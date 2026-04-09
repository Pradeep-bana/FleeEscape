<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);

include "admin/db.php";
require_once('config.php');
require __DIR__ . '/vendor/autoload.php';
require_once(__DIR__ . '/includes/bookeo_runtime.php');

use Square\SquareClient;
use Square\Models\Money;
use Square\Models\CreatePaymentRequest;

function logMsg($msg) {
  flee_system_log_message('process_booking', $msg);
}

function normalizeCodeList($codes) {
  if (is_array($codes)) {
    $list = $codes;
  } else {
    $list = explode(',', (string)$codes);
  }

  $list = array_map('trim', $list);
  $list = array_values(array_unique(array_filter($list, static function ($code) {
    return $code !== '';
  })));

  return $list;
}

function buildPromoLabel($promoName, $promoCode) {
  $promoName = trim((string)$promoName);
  if ($promoName !== '') {
    return $promoName;
  }

  $promoCode = trim((string)$promoCode);
  if ($promoCode === '') {
    return 'Applied Promotion';
  }

  $upperCode = strtoupper($promoCode);
  if (strpos($upperCode, 'BMSM') !== false) {
    $label = 'Play More Save More';
    if ($upperCode === 'BMSM_20') {
      $label .= ' - 20% OFF';
    } elseif ($upperCode === 'BMSM_10') {
      $label .= ' - 10% OFF';
    }
    return $label;
  }

  return ucwords(strtolower(str_replace(['_', '-'], ' ', $promoCode)));
}

function buildDisplayBreakdown(array $item, array $holdData, array $bookingData) {
  $taxLabels = [
    "41551F4AA9416930C3600E" => "Admission Tax",
    "415514PR6RC14F9231736E" => "Redmond Sales Tax"
  ];

  $guestCount = (int)($item['guests'] ?? 0);
  $unitPrice = (float)($item['price'] ?? 0);
  $baseItemPrice = $unitPrice * $guestCount;
  $additionalGuestSubtotal = (float)($item['total_additional_price'] ?? 0);
  $addonSubtotal = (float)($item['addon_subtotal'] ?? 0);
  $addonTax = (float)($item['addon_tax'] ?? 0);
  $localOriginalNet = $baseItemPrice + $additionalGuestSubtotal + $addonSubtotal;

  $holdPrice = $holdData['price'] ?? [];
  $bookingPrice = $bookingData['price'] ?? [];
  $holdGross = (float)($holdPrice['totalGross']['amount'] ?? 0);
  $bookingGross = (float)($bookingPrice['totalGross']['amount'] ?? $holdGross);

  $promoCode = trim((string)($holdData['_internal_promo'] ?? ($item['promo_code'] ?? '')));
  $promoName = trim((string)($bookingData['promotionName'] ?? ($holdData['promotionName'] ?? '')));
  $promoDiscountApi = (float)($holdData['appliedPromotionDiscount']['amount'] ?? ($bookingData['appliedPromotionDiscount']['amount'] ?? 0));
  $bookeoNetAfterPromo = (float)($holdPrice['totalNet']['amount'] ?? ($bookingPrice['totalNet']['amount'] ?? 0));
  $promoDiscount = 0.0;

  if ($localOriginalNet > 0 && $bookeoNetAfterPromo > 0 && $localOriginalNet > $bookeoNetAfterPromo) {
    $promoDiscount = $localOriginalNet - $bookeoNetAfterPromo;
  } elseif ($promoDiscountApi > 0) {
    $promoDiscount = $promoDiscountApi;
  }

  $voucherCodes = normalizeCodeList($holdData['_internal_vouchers'] ?? '');
  $voucherAmount = 0.0;
  if (isset($holdData['applicableGiftVoucherCredit']['amount'])) {
    $voucherAmount = (float)$holdData['applicableGiftVoucherCredit']['amount'];
  } elseif (isset($holdPrice['applicableGiftVoucherCredit']['amount'])) {
    $voucherAmount = (float)$holdPrice['applicableGiftVoucherCredit']['amount'];
  } elseif (isset($bookingData['applicableGiftVoucherCredit']['amount'])) {
    $voucherAmount = (float)$bookingData['applicableGiftVoucherCredit']['amount'];
  } elseif (isset($bookingPrice['applicableGiftVoucherCredit']['amount'])) {
    $voucherAmount = (float)$bookingPrice['applicableGiftVoucherCredit']['amount'];
  }

  $isSpecificVoucher = ($holdGross == 0.0 && $localOriginalNet > 0 && !empty($voucherCodes));
  $displayTaxes = [];

  if ($isSpecificVoucher) {
    $admissionTax = $baseItemPrice * 0.05;
    $redmondTax = ($baseItemPrice * 0.103) + $addonTax;

    $displayTaxes[] = ['label' => 'Admission Tax', 'amount' => $admissionTax];
    $displayTaxes[] = ['label' => 'Redmond Sales Tax', 'amount' => $redmondTax];

    $displayBookingTotal = $localOriginalNet + $admissionTax + $redmondTax;
    $voucherAmount = $displayBookingTotal;
  } else {
    $taxRows = [];
    if (!empty($holdPrice['taxes']) && is_array($holdPrice['taxes'])) {
      $taxRows = $holdPrice['taxes'];
    } elseif (!empty($bookingData['taxes']) && is_array($bookingData['taxes'])) {
      $taxRows = $bookingData['taxes'];
    } elseif (!empty($bookingPrice['taxes']) && is_array($bookingPrice['taxes'])) {
      $taxRows = $bookingPrice['taxes'];
    }

    foreach ($taxRows as $tax) {
      $taxId = $tax['taxId'] ?? '';
      $displayTaxes[] = [
        'label' => $taxLabels[$taxId] ?? ($tax['name'] ?? $taxId ?: 'Tax'),
        'amount' => (float)($tax['amount']['amount'] ?? 0)
      ];
    }

    $displayBookingTotal = $bookingGross;
    if ($displayBookingTotal <= 0 && $localOriginalNet > 0) {
      $displayBookingTotal = $localOriginalNet;
      foreach ($displayTaxes as $tax) {
        $displayBookingTotal += (float)($tax['amount'] ?? 0);
      }
    }
  }

  $displayTaxTotal = 0.0;
  foreach ($displayTaxes as $tax) {
    $displayTaxTotal += (float)($tax['amount'] ?? 0);
  }

  $displayTotalPaid = (float)($bookingPrice['totalPaid']['amount'] ?? 0);
  $balanceDue = max(0, $displayBookingTotal - $voucherAmount - $displayTotalPaid);
  if ($balanceDue < 0.01) {
    $balanceDue = 0.0;
  }

  return [
    'promo_code' => $promoCode,
    'promo_name' => $promoName,
    'promo_label' => buildPromoLabel($promoName, $promoCode),
    'promo_discount' => round($promoDiscount, 2),
    'voucher_codes' => $voucherCodes,
    'voucher_amount' => round($voucherAmount, 2),
    'is_specific_voucher' => $isSpecificVoucher,
    'display_subtotal' => round($localOriginalNet, 2),
    'display_taxes' => $displayTaxes,
    'display_tax_total' => round($displayTaxTotal, 2),
    'display_booking_total' => round($displayBookingTotal, 2),
    'display_total_paid' => round($displayTotalPaid, 2),
    'display_balance_due' => round($balanceDue, 2)
  ];
}

// ---------------------------------------------------------
// REMOVED: sendBookingEmail() function is no longer needed
// ---------------------------------------------------------

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
      $eventKey = trim((string)($h['event_id'] ?? ''));
      $gameKey = trim((string)($h['game_id'] ?? ''));

      if ($eventKey !== '') {
        $holdMap['event:' . $eventKey] = $hData;
      }
      if ($gameKey !== '') {
        $holdMap['game:' . $gameKey] = $hData;
      }
    }
  }

  // 3. Calculate Final Payable Amount
  $totalAmountCents = 0;
  foreach ($cartItems as $item) {
    $gid = $item['game_id'];
    $eventId = trim((string)($item['event_id'] ?? ''));
    $holdData = $holdMap['event:' . $eventId] ?? $holdMap['game:' . $gid] ?? [];
    $itemPayable = 0.0;
    
    if (isset($holdData['totalPayable']['amount'])) {
      $itemPayable = (float)$holdData['totalPayable']['amount'];
    } else {
      $base = (float)$item['price'];
      $guests = (int)$item['guests'];
      $extras = (float)($item['total_additional_price'] ?? 0);
      $addon = (float)($item['addon_subtotal'] ?? 0);
      $sub = ($base * $guests) + $extras + $addon;
      $taxEst = $sub * 0.153; 
      $itemPayable = $sub + $taxEst;
    }
    $totalAmountCents += (int)round($itemPayable * 100);
  }

  // 4. Process Payment (Square)
  $paymentId = null;
  $paymentDetails = "Voucher/Promo Covered";

  if ($totalAmountCents > 0) {
    if (!$nonce) throw new Exception("Payment required but no card token received.");

    $client = new SquareClient([
      'accessToken' => 'EAAAl8LlAZtvXu4na9gLonjCDfnJuZSxon16pkFR5bF89CDBduo9HJ-s1wvP5SpX', // Use ENV in production
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
  }

  // 5. Finalize Bookeo Bookings
  $apiKey  = FLEE_BOOKEO_API_KEY;
  $secretKey = FLEE_BOOKEO_SECRET_KEY;
  
  $customerFn = $_SESSION['firstName'] ?? 'Guest';
  $customerLn = $_SESSION['lastName'] ?? '';
  $customerEmail = $_SESSION['email'] ?? '';
  
  $customer = [
    "firstName"  => $customerFn,
    "lastName"   => $customerLn,
    "emailAddress" => $customerEmail,
    "phoneNumbers" => [["number" => $_SESSION['phone'] ?? '', "type" => $_SESSION['type'] ?? 'mobile']]
  ];

  $bookedList = [];
  $bookedSummary = [];
  $errors = [];

  // --- MODIFIED CALL BOOKEO FUNCTION ---
  function callBookeo($payload, $key, $secret) {
    // Build base URL
    $baseUrl = "https://api.bookeo.com/v2/bookings";

    // Define Query Parameters to trigger Bookeo Email
    $queryParams = [
      "notifyUsers"  => "true", // Notify Admin
      "notifyCustomer" => "true" // Notify Customer (This sends the Bookeo email)
    ];

    // Add Hold ID if present
    if (isset($payload['holdId'])) {
      $queryParams['previousHoldId'] = $payload['holdId'];
      // Remove holdId from body payload as it goes in URL
      unset($payload['holdId']); 
    }

    // Create Query String
    $url = $baseUrl . "?" . http_build_query($queryParams);
    
    $apiResponse = flee_bookeo_request('POST', $url, [
      'context' => 'process_booking_booking_create',
      'timeout' => 20,
      'headers' => ["X-Bookeo-apiKey: $key", "X-Bookeo-secretKey: $secret", "Content-Type: application/json"],
      'body' => json_encode($payload),
      'log_body' => true,
    ]);
    return ['code' => $apiResponse['code'], 'body' => $apiResponse['body']];
  }

  foreach ($cartItems as $item) {
    $gid = $item['game_id'];
    $eventId = trim((string)($item['event_id'] ?? ''));
    $holdData = $holdMap['event:' . $eventId] ?? $holdMap['game:' . $gid] ?? [];
    $holdId = $holdData['id'] ?? null;
    
    $bookingPayload = [
      "productId" => $gid,
      "eventId"  => $item['event_id'],
      "customer" => $customer,
      "participants" => [ "numbers" => [["peopleCategoryId" => "Cadults", "number" => (int)$item['guests']]] ]
    ];
    
    
    if ($holdId) $bookingPayload['holdId'] = $holdId;

    // 1. Get the specific codes saved for THIS game during the Hold phase 
    $specificPromo = $holdData['_internal_promo'] ?? '';
    $specificVouchers = $holdData['_internal_vouchers'] ?? '';

    // 2. ALWAYS apply the Promo Code (e.g., BMSM_10) if it exists.
    // This fixes the issue where the 10% discount disappears.
    if (!empty($specificPromo)) {
      $bookingPayload['promotionCodeInput'] = $specificPromo;
    }

    // 3. ALWAYS apply the Voucher Code, but ONLY the one assigned to this game.
    // This fixes the "Amount Due" issue by reapplying the discount.
    // Since we are using '$specificVouchers' (not the global session), 
    // it won't crash the 2nd game.
    if (!empty($specificVouchers)) {
      $bookingPayload['giftVoucherCodeInput'] = $specificVouchers;
    }
    // Fallback: Only use global session codes if we completely lack Hold data
    elseif (!$holdId && !empty($_SESSION['giftCode'])) {
      $bookingPayload['giftVoucherCodeInput'] = $_SESSION['giftCode'];
    }

    // if ($holdId) $bookingPayload['holdId'] = $holdId;
    // if (!$holdId) {
    //   // Only use fallback logic if we DO NOT have a hold
    //   $specificPromo = $holdData['_internal_promo'] ?? '';
    //   $specificVouchers = $holdData['_internal_vouchers'] ?? '';
    //   $itemPayable = (float)($holdData['totalPayable']['amount'] ?? $item['price']);

    //   // Fallback to session if local hold data is empty
    //   if ($itemPayable > 0 && empty($specificVouchers) && !empty($_SESSION['giftCode'])) {
    //     $specificVouchers = $_SESSION['giftCode'];
    //   }

    //   if (!empty($specificPromo)) $bookingPayload['promotionCodeInput'] = $specificPromo;
    //   if (!empty($specificVouchers)) $bookingPayload['giftVoucherCodeInput'] = $specificVouchers;
    // }

    // --- PAYMENT RECORDING ---
    $amountToRecord = $holdData['totalPayable']['amount'] ?? "0.00";
    if ((float)$amountToRecord > 0 && $totalAmountCents > 0) {
      $bookingPayload['initialPayments'] = [[
        "receivedTime" => date('c'),
        "reason"    => "Online Booking",
        "comment"   => "Square ID: $paymentId",
        "amount"    => ["amount" => $amountToRecord, "currency" => $currency],
        "paymentMethod" => "creditCard",
        "paymentMethodOther" => $paymentDetails
      ]];
    }
    
    // --- PAYMENT RECORDING ---
// --- PAYMENT RECORDING ---
// Use the amount from the hold/cart item, NOT $bData (which is empty before booking)
// --- PAYMENT RECORDING PER ITEM ---
// Get exact amount payable for this item (hold first, else compute)



    // --- OPTIONS / ADJUSTMENTS --- 
    $options = [];
    if (!empty($item['escape_selection'])) {
        $skipEscapeChoiceGames = [
            '41551LAM3LY18570132661'
        ];
        if (!in_array($gid, $skipEscapeChoiceGames)) {
            $options[] = ["name" => "Escape Room Choices", "value" => $item['escape_selection']];
        }
    }
    if ($item['addon_qty'] > 0) {
      $val = (strtolower($item['cat']) === 'party-package') ? 'true' : $item['addon_qty'];
      
      if (!empty($item['addon_opt_id'])) {
        // Pass 'id' directly. DB has "4155..._4C9PK6FE", which matches the API definition.
        $options[] = ["id" => $item['addon_opt_id'], "value" => $val];
      } 
      elseif (!empty($item['addon_name'])) {
        // Fallback to name if ID is somehow missing
        $options[] = ["name" => $item['addon_name'], "value" => $val];
      }
    }
    
    if ((float)($item['total_additional_price'] ?? 0) > 0) {
      $addGuests = (int)($item['additional_guest'] ?? 0);
      if ($addGuests <= 0) $addGuests = 1;
    
      // Send as Bookeo Option by name — Bookeo will apply $55/guest + Redmond Sales tax automatically
      $options[] = [
        "name" => "Additional Guests",
        "value" => (string)$addGuests
      ];
      // NO priceAdjustments needed — Bookeo handles price + tax
    }
    
    if (!empty($options)) $bookingPayload['options'] = $options;

    // --- EXECUTE ---
    $res = callBookeo($bookingPayload, $apiKey, $secretKey);

    if ($res['code'] == 201) {
      $bData = json_decode($res['body'], true);
      $bookingNum = $bData['bookingNumber'];
      $bookedList[] = $bookingNum;
    
      // Variables for DB
      $addonName   = $item['addon_name'] ?? null;
      $addonPrice  = $item['addon_price'] ?? 0;
      $addonQty   = $item['addon_qty'] ?? 0;
      $addonSubtotal = $item['addon_subtotal'] ?? 0;
      $guestCount = (int)($item['guests'] ?? 0);
      $baseUnitPrice = (float)($item['price'] ?? 0);
      $additionalGuestCount = (int)($item['additional_guest'] ?? 0);
      $additionalGuestUnitPrice = (float)($item['per_guest_price'] ?? 0);
      $additionalGuestSubtotal = (float)($item['total_additional_price'] ?? 0);
      $escapeRoomSubtotal = $baseUnitPrice * $guestCount;
      $startTime = $bData['startTime'] ?? null;
      $endTime  = $bData['endTime'] ?? null;
      $prodName = $bData['productName'] ?? ($item['game_name'] ?? null); 
      $priceJson    = json_encode($bData['price'] ?? []);
      $priceAdjustments = json_encode($bData['priceAdjustments'] ?? []);
      $participantsJson = json_encode($bData['participants'] ?? []);
      $taxesJson    = json_encode($bData['taxes'] ?? ($bData['price']['taxes'] ?? [])); 
      $displayBreakdown = buildDisplayBreakdown($item, $holdData, $bData);

      $bookedSummary[] = [
        "bookingNumber" => $bookingNum,
        "status" => "booked",
        "time" => date('Y-m-d'),
        "guests" => $guestCount,
        "price" => $baseUnitPrice,
        "escape_room_subtotal" => $escapeRoomSubtotal,
        "additional_guest" => $additionalGuestCount,
        "per_guest_price" => $additionalGuestUnitPrice,
        "total_additional_price" => $additionalGuestSubtotal,
        "addon_title" => $addonName,
        "addon_price" => (float)$addonPrice,
        "addon_qty" => (int)$addonQty,
        "addon_subtotal" => (float)$addonSubtotal,
        "promo_code" => $displayBreakdown['promo_code'],
        "promo_name" => $displayBreakdown['promo_name'],
        "promo_label" => $displayBreakdown['promo_label'],
        "promo_discount" => $displayBreakdown['promo_discount'],
        "voucher_codes" => $displayBreakdown['voucher_codes'],
        "voucher_amount" => $displayBreakdown['voucher_amount'],
        "is_specific_voucher" => $displayBreakdown['is_specific_voucher'],
        "display_subtotal" => $displayBreakdown['display_subtotal'],
        "display_taxes" => $displayBreakdown['display_taxes'],
        "display_tax_total" => $displayBreakdown['display_tax_total'],
        "display_booking_total" => $displayBreakdown['display_booking_total'],
        "display_total_paid" => $displayBreakdown['display_total_paid'],
        "display_balance_due" => $displayBreakdown['display_balance_due']
      ];
    
      // --- INSERT INTO DB ---
      $stmtIns = $pdo->prepare("
        INSERT INTO tbl_bookings 
        (
          bookingNumber, eventId, startTime, endTime, customerId, title, 
          productId, productName, privateEvent, noShow, canceled, accepted, 
          creationTime, creationAgent, totalGross, totalNet, totalTaxes, totalPaid, 
          priceJson, priceAdjustments, participantsJson, taxesJson, 
          user_id, addon_title, addon_price, addon_qty, addon_subtotal,
          status, created_at
        ) 
        VALUES 
        (
          :bookingNumber, :eventId, :startTime, :endTime, :customerId, :title, 
          :productId, :productName, :privateEvent, :noShow, :canceled, :accepted, 
          :creationTime, :creationAgent, :totalGross, :totalNet, :totalTaxes, :totalPaid, 
          :priceJson, :priceAdjustments, :participantsJson, :taxesJson, 
          :user_id, :addon_title, :addon_price, :addon_qty, :addon_subtotal,
          :status, NOW()
        )
      ");
    
      $stmtIns->execute([
        ":bookingNumber"  => $bookingNum,
        ":eventId"     => $bData['eventId'] ?? ($item['event_id'] ?? null),
        ":startTime"    => $startTime,
        ":endTime"     => $endTime,
        ":customerId"    => $bData['customerId'] ?? null,
        ":title"      => $bData['title'] ?? null,
        ":productId"    => $bData['productId'] ?? ($gid ?? null),
        ":productName"   => $prodName,
        ":privateEvent"   => !empty($bData['privateEvent']) ? 1 : 0,
        ":noShow"      => !empty($bData['noShow']) ? 1 : 0,
        ":canceled"     => !empty($bData['canceled']) ? 1 : 0,
        ":accepted"     => !empty($bData['accepted']) ? 1 : 0,
        ":creationTime"   => $bData['creationTime'] ?? null,
        ":creationAgent"  => $bData['creationAgent'] ?? null,
        ":totalGross"    => $bData['price']['totalGross']['amount'] ?? 0,
        ":totalNet"     => $bData['price']['totalNet']['amount'] ?? 0,
        ":totalTaxes"    => $bData['price']['totalTaxes']['amount'] ?? 0,
        ":totalPaid"    => $bData['price']['totalPaid']['amount'] ?? 0,
        ":priceJson"    => $priceJson,
        ":priceAdjustments" => $priceAdjustments,
        ":participantsJson" => $participantsJson,
        ":taxesJson"    => $taxesJson,
        ":user_id"     => $_SESSION['user_id'] ?? 0,
        ":status"      => 1, 
        ":addon_title"   => $addonName,
        ":addon_price"   => $addonPrice,
        ":addon_qty"    => $addonQty,
        ":addon_subtotal"  => $addonSubtotal
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

  // Clear Cart
  $pdo->prepare("DELETE FROM tbl_carts WHERE session_id=?")->execute([$sessionId]);
  $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE session_id=?")->execute([$sessionId]);
  
  $_SESSION['booking_summary'] = $bookedSummary;

  // ------------------------------------------------------------------
  // REMOVED: Manual email sending logic is gone. 
  // Bookeo sends it now because we added notifyCustomer=true above.
  // ------------------------------------------------------------------

  flee_system_log_message('booking_success', 'Booking finalized successfully', [
      'booking_number' => $bookingNumber
  ]);

  echo json_encode(["status" => "success", "redirectUrl" => "booking-confirmation.php"]);

} catch (Exception $e) {
  // logMsg("Critical Error: " . $e->getMessage());
  flee_system_log_message('booking_failed', 'Failed to process booking', [
      'error_reason' => $e->getMessage()
  ]);
  echo json_encode(["status" => "error", "message" => $e->getMessage()]);
} 
?>
