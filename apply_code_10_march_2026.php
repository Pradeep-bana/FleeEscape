<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include("admin/db.php");
require_once('config.php');

// --- CONFIG ---
$apiKey    = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
$secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";
$sid       = session_id();

// --- FUNCTIONS ---
function callBookeoHold($payload, $apiKey, $secretKey) {
    $url = "https://api.bookeo.com/v2/holds?holdDurationSeconds=".(CART_TIMER_MINUTES*60);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "X-Bookeo-apiKey: $apiKey",
        "X-Bookeo-secretKey: $secretKey"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpCode, 'data' => json_decode($response, true)];
}

function deleteBookeoHold($holdId, $apiKey, $secretKey) {
    if (!$holdId) return;
    $url = "https://api.bookeo.com/v2/holds/{$holdId}?apiKey={$apiKey}&secretKey={$secretKey}";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

// --- INPUT VALIDATION ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$inputCode = trim($_POST['code'] ?? '');
// if ($inputCode === '') {
//     echo json_encode(['status' => 'error', 'message' => 'Code is empty']);
//     // exit;
// }

// Prepare Input Pool
$userCodes = array_map('trim', explode(',', $inputCode));
$userCodes = array_filter($userCodes); 
$userCodes = array_values($userCodes);

$voucherPool = $userCodes;

// --- FETCH CART ---
$stmt = $pdo->prepare("SELECT * FROM tbl_carts WHERE session_id = :sid");
$stmt->execute([':sid' => $sid]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$cartItems) {
    echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
    exit;
}

// Sort: Highest Price First
usort($cartItems, function($a, $b) {
    $priceA = ($a['price'] * $a['guests']);
    $priceB = ($b['price'] * $b['guests']);
    return $priceB <=> $priceA; 
});

// --- DETERMINE AUTO PROMO ---
$escapeCount = 0;
$hasPromotionPage = false;
foreach ($cartItems as $c) {
    if (strpos(strtolower($c['cat']), 'escape-room') !== false) $escapeCount++;
    if (!empty($c['pramotion_page']) && $c['pramotion_page'] !== 'false') $hasPromotionPage = true;
}

$activePromoCode = null;
$promoIsLocked = false; 

if ($escapeCount >= 3) { $activePromoCode = "BMSM_20"; $promoIsLocked = true; } 
elseif ($escapeCount == 2) { $activePromoCode = "BMSM_10"; $promoIsLocked = true; } 
elseif ($hasPromotionPage) { $promoIsLocked = true; }

// Only restore BMSM from DB if escapeCount didn't already determine it
// (i.e. 0 or 1 escape rooms — user had manually applied something before)
if (!$activePromoCode) {
    foreach ($cartItems as $c) {
        if (!empty($c['promo_code']) && strpos($c['promo_code'], 'BMSM') !== false) {
            $activePromoCode = $c['promo_code'];
            $promoIsLocked = true;
            break;
        }
    }
}

// --- PROCESSING LOOP ---
$itemsUpdated = 0;
$successType = ""; 
$validUserCodes = [];
$promoDiscoveryDone = false;

foreach ($cartItems as $item) {
    $gameId  = $item['game_id'];
    $eventId = $item['event_id'];
    $qty     = (int)$item['guests'];
    $approxItemPrice = (float)$item['price'] * $qty;

    if(empty($gameId) || empty($eventId)) continue;

    // 1. DELETE OLD HOLD
    $stmtHold = $pdo->prepare("SELECT id, response_json FROM tbl_bookeo_holds WHERE session_id=:sid AND event_id=:event_id ORDER BY id DESC LIMIT 1");
    $stmtHold->execute([':sid' => $sid, ':event_id' => $eventId]);
    $oldHoldRow = $stmtHold->fetch(PDO::FETCH_ASSOC);
    if ($oldHoldRow) {
        $oldJson = json_decode($oldHoldRow['response_json'], true);
        if (isset($oldJson['id'])) deleteBookeoHold($oldJson['id'], $apiKey, $secretKey);
        $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE id=:id")->execute([':id' => $oldHoldRow['id']]);
    }

    // 2. BASE PAYLOAD
    // $basePayload = [
    //     "eventId" => $eventId,
    //     "customer" => [ "firstName" => "Temp", "lastName"  => "User", "emailAddress" => "temp@example.com" ],
    //     "participants" => [ "numbers" => [ ["peopleCategoryId" => "Cadults", "number" => $qty] ] ],
    //     "productId" => $gameId
    // ];

    // $options = [];
    // if (!empty($item['escape_selection'])) {
    //     $options[] = ["name" => "Escape Room Choice", "value" => $item['escape_selection']];
    // }
    // if (!empty($item['addon_name']) && $item['addon_qty'] > 0) {
    //     $val = (strtolower($item['cat']) === 'party-package') ? 'true' : $item['addon_qty'];
    //     $options[] = ["name" => $item['addon_name'], "value" => $val];
    // }
    
    $basePayload = [
        "eventId" => $eventId,
        "customer" => [ "firstName" => "Temp", "lastName"  => "User", "emailAddress" => "temp@example.com" ],
        "participants" => [ "numbers" => [ ["peopleCategoryId" => "Cadults", "number" => $qty] ] ],
        "productId" => $gameId
    ];

    $options = [];
    
    // 1. Handle Escape Selection (unchanged)
    if (!empty($item['escape_selection'])) {
        $options[] = ["name" => "Escape Room Choice", "value" => $item['escape_selection']];
    }

    // 2. Handle Add-ons (UPDATED)
    // We prioritize sending the ID so Bookeo calculates the price/tax
    if ($item['addon_qty'] > 0) {
        $val = (strtolower($item['cat']) === 'party-package') ? 'true' : $item['addon_qty'];

        if (!empty($item['addon_opt_id'])) {
            // SEND ID (This triggers Bookeo Pricing)
            $options[] = ["id" => $item['addon_opt_id'], "value" => $val];
        } 
        elseif (!empty($item['addon_name'])) {
            // Fallback to Name if ID is missing
            $options[] = ["name" => $item['addon_name'], "value" => $val];
        }
    }

    if ((float)($item['total_additional_price'] ?? 0) > 0) {
        $addGuests = (int)($item['additional_guest'] ?? 0);
        if ($addGuests <= 0) $addGuests = 1;
        // Send as Bookeo Option by name — Bookeo will apply $55/guest + Redmond Sales tax automatically
        $options[] = [
            "name"  => "Additional Guests",
            "value" => (string)$addGuests
        ];
    }
    if (!empty($options)) $basePayload['options'] = $options;
    
    // ======================================================
    // PHASE A: PROMO DISCOVERY (Run once)
    // ======================================================
    if (!$promoIsLocked && !$promoDiscoveryDone) {
        $promoDiscoveryDone = true;
        foreach ($voucherPool as $key => $candidateCode) {
            $testPayload = $basePayload;
            $testPayload['promotionCodeInput'] = $candidateCode;
            
            usleep(250000);
            $res = callBookeoHold($testPayload, $apiKey, $secretKey);
            
            $isPromo = false;
            if ($res['code'] === 201) {
                if (isset($res['data']['promotionApplicable']) && $res['data']['promotionApplicable'] === true) {
                    $isPromo = true;
                } elseif (floatval($res['data']['appliedPromotionDiscount']['amount'] ?? 0) > 0) {
                    $isPromo = true;
                }
                deleteBookeoHold($res['data']['id'], $apiKey, $secretKey);
            }

            if ($isPromo) {
                $activePromoCode = $candidateCode;
                $validUserCodes[] = $candidateCode;
                $successType = "promotion";
                unset($voucherPool[$key]);
                break; 
            }
        }
        $voucherPool = array_values($voucherPool);
    }

    // ======================================================
    // PHASE B: VOUCHER CONSUMPTION
    // ======================================================
    $appliedVouchersForThisItem = [];
    $currentCredit = 0.0;
    
    $payload = $basePayload;
    if (!empty($activePromoCode)) $payload['promotionCodeInput'] = $activePromoCode;
    
    // Get Base Price
    usleep(250000);
    $res = callBookeoHold($payload, $apiKey, $secretKey);
    $netPrice = $approxItemPrice; 

    if ($res['code'] === 201) {
        $data = $res['data'];
        $currentCredit = $data['applicableGiftVoucherCredit']['amount'] 
                         ?? $data['price']['applicableGiftVoucherCredit']['amount'] 
                         ?? 0;
        $netPrice = $data['price']['totalNet']['amount'] ?? $approxItemPrice;
        deleteBookeoHold($data['id'], $apiKey, $secretKey);
        
        // Loop Vouchers
        foreach ($voucherPool as $key => $code) {
            if ($netPrice <= 0) break; // Fully paid

            $tempVouchers = $appliedVouchersForThisItem;
            $tempVouchers[] = $code;
            
            $payload['giftVoucherCodeInput'] = implode(",", $tempVouchers);
            
            usleep(250000);
            $vRes = callBookeoHold($payload, $apiKey, $secretKey);
            
            if ($vRes['code'] === 201) {
                $vData = $vRes['data'];
                $newCredit = $vData['applicableGiftVoucherCredit']['amount'] 
                             ?? $vData['price']['applicableGiftVoucherCredit']['amount'] 
                             ?? 0;
                $newNetPrice = $vData['price']['totalNet']['amount'];
                
                // Detect Specific Voucher
                $isSpecific = isset($vData['specificVoucherCode']) && !empty($vData['specificVoucherCode']);
                
                // Effective Check
                if ($newCredit > $currentCredit || $newNetPrice < $netPrice || $isSpecific) {
                    
                    // --- SMART RELEASE LOGIC START ---
                    // If this is a Specific Voucher, and we have other "Generic" vouchers attached,
                    // release the generics back to the pool!
                    if ($isSpecific && count($tempVouchers) > 1) {
                        $vouchersToKeep = [$code];
                        $vouchersToRelease = [];
                        
                        foreach ($appliedVouchersForThisItem as $oldVoucher) {
                            $vouchersToRelease[] = $oldVoucher;
                        }
                        
                        // Release back to global pool
                        $voucherPool = array_merge($voucherPool, $vouchersToRelease);
                        
                        // Reset local tracking
                        $appliedVouchersForThisItem = $vouchersToKeep;
                        $tempVouchers = $vouchersToKeep; // Ensure current logic uses clean list
                        
                        // We need to 'refresh' the Bookeo Hold to ensure Generics aren't counted
                        // (Though we delete immediately below, logic requires correct stats)
                        // Actually, since $isSpecific covers the game, metrics update automatically:
                        $currentCredit = 0; // Specific vouchers usually don't show as 'credit', they drop 'Net'
                        $netPrice = $newNetPrice;
                        
                        $validUserCodes[] = $code; // Add specific
                        // Note: Released codes are NOT added to validUserCodes yet (they wait for next item)
                        
                    } else {
                        // Normal behavior
                        $appliedVouchersForThisItem[] = $code;
                        $currentCredit = $newCredit;
                        $netPrice = $newNetPrice;
                        $validUserCodes[] = $code;
                    }
                    // --- SMART RELEASE LOGIC END ---
                    
                    // Remove current code from pool (it's either used, or logic above handled re-pooling others)
                    unset($voucherPool[$key]);
                    
                    $successType = "voucher";
                }
                deleteBookeoHold($vData['id'], $apiKey, $secretKey);
            }
        }
    }

    // ======================================================
    // PHASE C: FINALIZATION & SAVING
    // ======================================================
    $payload = $basePayload;
    if (!empty($activePromoCode)) $payload['promotionCodeInput'] = $activePromoCode;
    if (!empty($appliedVouchersForThisItem)) $payload['giftVoucherCodeInput'] = implode(",", $appliedVouchersForThisItem);

    usleep(250000);
    $finalRes = callBookeoHold($payload, $apiKey, $secretKey);
    if ($finalRes['code'] === 201) {
        $finalData = $finalRes['data'];
        
        $finalData['_internal_promo'] = $activePromoCode;
        $finalData['_internal_vouchers'] = implode(",", $appliedVouchersForThisItem);

        $stmtInsert = $pdo->prepare("INSERT INTO tbl_bookeo_holds (session_id, event_id, game_id, response_json, created_at) VALUES (:sid, :eid, :gid, :json, NOW())");
        $stmtInsert->execute([':sid' => $sid, ':eid' => $eventId, ':gid' => $gameId, ':json' => json_encode($finalData)]);

        // Hard-delete ALL cache rows for this product+date (not just the event_id row).
        // Deleting only one event_id still leaves other slots in cache → slot_count > 0
        // → status 'fresh' → no re-fetch. Wiping product+date → slot_count = 0
        // → status 'missing' → blocking Bookeo re-fetch → slot shows correct instantly.
        $slotDateForCache = isset($item['slot']) ? substr($item['slot'], 0, 10) : null;
        if ($gameId && $slotDateForCache) {
            $pdo->prepare("DELETE FROM bookeo_slots_cache WHERE product_id = :pid AND slot_date = :sdate")
                ->execute([':pid' => $gameId, ':sdate' => $slotDateForCache]);
        }

        $itemsUpdated++;
    }
}

$validUserCodes = array_unique($validUserCodes);
$cleanCodeString = implode(",", $validUserCodes);

if (!empty($cleanCodeString)) {
    $_SESSION['giftCode'] = $cleanCodeString;
}

if ($itemsUpdated > 0) {
    echo json_encode([
        'status'     => 'success',
        'message'    => !empty($cleanCodeString) ? 'Codes applied successfully' : 'Hold refreshed successfully',
        'valid_code' => $cleanCodeString
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to refresh hold.']);
}
?>