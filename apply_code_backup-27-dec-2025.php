<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include("admin/db.php");

// --- CONFIG ---
$apiKey    = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
$secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";
$sid       = session_id();

// --- FUNCTIONS ---
function callBookeoHold($payload, $apiKey, $secretKey) {
    $url = "https://api.bookeo.com/v2/holds";
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
if ($inputCode === '') {
    echo json_encode(['status' => 'error', 'message' => 'Code is empty']);
    exit;
}

// Prepare Input Pool
$userCodes = array_map('trim', explode(',', $inputCode));
$userCodes = array_filter($userCodes); 
$userCodes = array_values($userCodes);

// This pool contains everything initially. We will extract the Promo from it if found.
$voucherPool = $userCodes;

// --- FETCH CART ---
$stmt = $pdo->prepare("SELECT * FROM tbl_carts WHERE session_id = :sid");
$stmt->execute([':sid' => $sid]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$cartItems) {
    echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
    exit;
}

// Sort: Highest Price First (Best for single-use vouchers)
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

// Check Logic for Auto Promo
if ($escapeCount >= 3) { $activePromoCode = "BMSM_20"; $promoIsLocked = true; } 
elseif ($escapeCount == 2) { $activePromoCode = "BMSM_10"; $promoIsLocked = true; } 
elseif ($hasPromotionPage) { $promoIsLocked = true; }

foreach ($cartItems as $c) {
    if (!empty($c['promo_code']) && strpos($c['promo_code'], 'BMSM') !== false) {
        $activePromoCode = $c['promo_code'];
        $promoIsLocked = true;
        break;
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
    $stmtHold = $pdo->prepare("SELECT id, response_json FROM tbl_bookeo_holds WHERE session_id=:sid AND game_id=:game_id ORDER BY id DESC LIMIT 1");
    $stmtHold->execute([':sid' => $sid, ':game_id' => $gameId]);
    $oldHoldRow = $stmtHold->fetch(PDO::FETCH_ASSOC);
    if ($oldHoldRow) {
        $oldJson = json_decode($oldHoldRow['response_json'], true);
        if (isset($oldJson['id'])) deleteBookeoHold($oldJson['id'], $apiKey, $secretKey);
        $pdo->prepare("DELETE FROM tbl_bookeo_holds WHERE id=:id")->execute([':id' => $oldHoldRow['id']]);
    }

    // 2. BASE PAYLOAD
    $basePayload = [
        "eventId" => $eventId,
        "customer" => [ "firstName" => "Temp", "lastName"  => "User", "emailAddress" => "temp@example.com" ],
        "participants" => [ "numbers" => [ ["peopleCategoryId" => "Cadults", "number" => $qty] ] ],
        "productId" => $gameId
    ];

    // ======================================================
    // PHASE A: INTELLIGENT PROMO DISCOVERY (Run once)
    // ======================================================
    // If we aren't locked to Auto-Promo, and haven't found a user promo yet,
    // scan the input pool to see if one of them is a promo code.
    if (!$promoIsLocked && !$promoDiscoveryDone) {
        $promoDiscoveryDone = true; // Don't run this block for subsequent items
        
        foreach ($voucherPool as $key => $candidateCode) {
            // Test this code as a Promotion
            $testPayload = $basePayload;
            $testPayload['promotionCodeInput'] = $candidateCode;
            unset($testPayload['giftVoucherCodeInput']); // Ensure clean test

            $res = callBookeoHold($testPayload, $apiKey, $secretKey);
            
            $isPromo = false;
            if ($res['code'] === 201) {
                if (isset($res['data']['promotionApplicable']) && $res['data']['promotionApplicable'] === true) {
                    $isPromo = true;
                }
                // Cleanup the test hold immediately
                deleteBookeoHold($res['data']['id'], $apiKey, $secretKey);
            }

            if ($isPromo) {
                // FOUND IT! 
                $activePromoCode = $candidateCode; // Set the global promo
                $validUserCodes[] = $candidateCode; // Mark as valid
                $successType = "promotion";
                
                // Remove from Voucher Pool so we don't try to use it as a gift card
                unset($voucherPool[$key]);
                break; // Stop searching, Bookeo usually allows only 1 promo
            }
        }
        $voucherPool = array_values($voucherPool); // Re-index array
    }

// ======================================================
    // PHASE B: VOUCHER CONSUMPTION
    // ======================================================
    
    $appliedVouchersForThisItem = [];
    $currentCredit = 0.0;
    
    // Initial Setup: Payload with just the Promo (if any)
    $payload = $basePayload;
    if (!empty($activePromoCode)) $payload['promotionCodeInput'] = $activePromoCode;
    
    // Create Base Hold (Promo Only) to get the starting Price
    $res = callBookeoHold($payload, $apiKey, $secretKey);
    
    // Initialize Net Price tracker
    $netPrice = $approxItemPrice; 

    if ($res['code'] === 201) {
        $data = $res['data'];
        $currentCredit = $data['applicableGiftVoucherCredit']['amount'] 
                         ?? $data['price']['applicableGiftVoucherCredit']['amount'] 
                         ?? 0;
        
        // IMPORTANT: Capture the starting Net Price from the API
        $netPrice = $data['price']['totalNet']['amount'] ?? $approxItemPrice;
        
        // Delete immediately to start fresh for stacking
        deleteBookeoHold($data['id'], $apiKey, $secretKey);
        
        // Loop through Voucher Pool (Greedy Consumption)
        foreach ($voucherPool as $key => $code) {
            // If fully paid, we might still need to apply a Specific Voucher if it wasn't used yet,
            // but usually we stop if price is 0. However, for specific vouchers, let's try anyway if netPrice > 0.
            if ($netPrice <= 0) break; 

            // Try adding this voucher
            $tempVouchers = $appliedVouchersForThisItem;
            $tempVouchers[] = $code;
            
            $payload['giftVoucherCodeInput'] = implode(",", $tempVouchers);
            
            $vRes = callBookeoHold($payload, $apiKey, $secretKey);
            
            if ($vRes['code'] === 201) {
                $vData = $vRes['data'];
                
                $newCredit = $vData['applicableGiftVoucherCredit']['amount'] 
                             ?? $vData['price']['applicableGiftVoucherCredit']['amount'] 
                             ?? 0;
                
                $newNetPrice = $vData['price']['totalNet']['amount'];

                // Check if a specific voucher was accepted by Bookeo
                $isSpecificAccepted = isset($vData['specificVoucherCode']) && !empty($vData['specificVoucherCode']);
                
                // --- FIX: UPDATED SUCCESS CONDITION ---
                // 1. Credit Increased
                // 2. OR Price Decreased (Common for Specific Vouchers)
                // 3. OR Specific Voucher Field is present (Definitive proof for Specific Vouchers)
                if ($newCredit > $currentCredit || $newNetPrice < $netPrice || $isSpecificAccepted) {
                    
                    // It worked! Keep it.
                    $appliedVouchersForThisItem[] = $code;
                    $currentCredit = $newCredit;
                    $netPrice = $newNetPrice; // Update the tracking price
                    
                    // Remove from pool (Consumed!)
                    unset($voucherPool[$key]);
                    
                    // Track valid code
                    $validUserCodes[] = $code;
                    
                    if ($successType == "promotion" || $promoIsLocked || !empty($activePromoCode)) {
                        $successType = "both";
                    } else {
                        $successType = "voucher";
                    }
                }
                
                // Cleanup this test hold
                deleteBookeoHold($vData['id'], $apiKey, $secretKey);
            }
        }
    }

    // ======================================================
    // PHASE C: FINALIZATION
    // ======================================================
    // Create the final persistent hold with all valid codes found
    $payload = $basePayload;
    if (!empty($activePromoCode)) $payload['promotionCodeInput'] = $activePromoCode;
    if (!empty($appliedVouchersForThisItem)) $payload['giftVoucherCodeInput'] = implode(",", $appliedVouchersForThisItem);

    $finalRes = callBookeoHold($payload, $apiKey, $secretKey);
    if ($finalRes['code'] === 201) {
        $finalData = $finalRes['data'];
        
        $stmtInsert = $pdo->prepare("INSERT INTO tbl_bookeo_holds (session_id, event_id, game_id, response_json, created_at) VALUES (:sid, :eid, :gid, :json, NOW())");
        $stmtInsert->execute([':sid' => $sid, ':eid' => $eventId, ':gid' => $gameId, ':json' => json_encode($finalData)]);
        
        $itemsUpdated++;
    }
}

// Clean session codes
$validUserCodes = array_unique($validUserCodes);
$cleanCodeString = implode(",", $validUserCodes);

if (!empty($cleanCodeString)) {
    $_SESSION['giftCode'] = $cleanCodeString;
}

// --- RESPONSE ---
if ($itemsUpdated > 0) {
    if ($successType == "voucher_auto") {
        $msg = "Voucher applied! (Promotion is built-in).";
    } elseif ($successType == "both") {
        $msg = "Promotion and Voucher applied successfully!";
    } elseif ($successType == "voucher") {
        $msg = "Voucher applied successfully!";
    } elseif ($successType == "promotion") {
        $msg = "Promotion applied successfully!";
    } elseif ($successType == "auto_only") {
         $msg = "Code invalid (Default promotion kept).";
    } else {
        $msg = "Code applied.";
    }
    
    // Edge case: if only auto promo worked but user codes failed
    if (empty($validUserCodes) && $promoIsLocked) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Voucher code (Promotion is locked).']);
    } else {
        echo json_encode(['status' => 'success', 'message' => $msg, 'valid_code' => $cleanCodeString]);
    }

} else {
    $err = "Invalid code.";
    if ($promoIsLocked) $err = "Invalid Voucher code (Promotion is locked).";
    echo json_encode(['status' => 'error', 'message' => $err]);
}
?>