<?php
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

session_start();
include("admin/db.php");
require_once("addon_pricing_helper.php");
$products = flee_get_cached_products_data($pdo);

$sid = session_id();

if (!defined('FLEE_CART_SESSION_LIBRARY')) {
    define('FLEE_CART_SESSION_LIBRARY', true);
}
require_once('cart_session.php');
flee_cart_cleanup_expired($pdo);

// --- Fetch cart items ---
try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_carts WHERE session_id = :sid ORDER BY id ASC");
    $stmt->execute([':sid' => $sid]);
    $cart = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("DB error: " . $e->getMessage());
}

if (empty($cart)) {
    // Ensure this matches what your JS expects
    unset($_SESSION['giftCode']);
    echo json_encode(['redirect' => true]);
    exit; 
}

// if (empty($cart)) {
//     echo "<p>Your cart is empty</p>";
//     echo '<div id="bookeo-totals" data-totals=\'' . json_encode([
//         'subtotal'      => 0.0,
//         'discount'      => 0.0,
//         'taxes'         => [],
//         'grandTotal'    => 0.0,
//         'voucherAmount' => 0.0,
//         'totalPayable'  => 0.0
//     ], JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP) . '\'></div>';
//     exit;
// }

// --- Initialize Totals ---
$subtotal      = 0.0;
$totalDiscount = 0.0;
$grandTotal    = 0.0;
$voucherAmountTotal = 0.0;

// --- Discount Logic (Auto Promo) ---
$totalEscapeGames = 0;
foreach ($cart as $item) {
    if (!empty($item['cat']) && strtolower($item['cat']) === 'escape-room') {
        $totalEscapeGames++;
    }
}
$discountPercent = 0;
if ($totalEscapeGames == 2) $discountPercent = 10;
elseif ($totalEscapeGames >= 3) $discountPercent = 20;

$holdStmt = $pdo->prepare("SELECT response_json FROM tbl_bookeo_holds WHERE session_id = :sid AND event_id = :event_id ORDER BY id DESC LIMIT 1");

$taxLabels = [
    "41551F4AA9416930C3600E" => "Admission Tax",
    "415514PR6RC14F9231736E" => "Redmond Sales Tax"
];

// --- LOOP THROUGH CART ---
foreach ($cart as $index => $item) {

    $unitPrice = (float)($item['price'] ?? 0);
    $qty       = (int)($item['guests'] ?? 0);
    
    // 1. Calculate Local Base Price
    $baseItemPrice = $unitPrice * $qty;
    $subtotal += $baseItemPrice;

    // Party Package / Addons
    $totalAdditionalPrice = (float)($item['total_additional_price'] ?? 0);
    $addonTotal = 0.0;
    if (!empty($item['addon_subtotal'])) {
        $addonTotal = floatval($item['addon_subtotal']);
    }
    // Add these to subtotal tracker
    $subtotal += $totalAdditionalPrice + $addonTotal;

    // Variables for Display
    $itemDiscount = 0;
    $itemTaxes = [];
    $thisItemTotal = 0.0;
    $isSpecificVoucher = false;
    $promoCodeApplied  = null;
    $promoLabelText    = null;

    // Check Bookeo Data
    $eventId = $item['event_id'] ?? '';
    if (!empty($eventId)) {
        $holdStmt->execute([':sid' => $sid, ':event_id' => $eventId]);
        $row = $holdStmt->fetch(PDO::FETCH_ASSOC);

        if ($row && !empty($row['response_json'])) {
            $bookeo = json_decode($row['response_json'], true);

            $bookeoGross = floatval($bookeo['price']['totalGross']['amount'] ?? 0);
            
            // --- DETECTION LOGIC ---
            // If Bookeo says price is 0, but our local DB says it costs money ($baseItemPrice > 0),
            // then a Specific Voucher (or 100% discount) masked the price.
            if ($bookeoGross == 0 && $baseItemPrice > 0) {
                $isSpecificVoucher = true;
            }

            if ($isSpecificVoucher) {
                // [CASE A] SPECIFIC VOUCHER: Manually Reconstruct Price
                
                // 1. Taxes (Calculated based on your Generic Voucher JSON rates)
                // Admission: 5%
                $taxAdm = $baseItemPrice * 0.05; 
                // Redmond: 10.3%
                $taxRed = ($baseItemPrice * 0.103) + floatval($item['addon_tax'] ?? 0);

                $itemTaxes[] = ['label' => "Admission Tax", 'amount' => $taxAdm];
                $itemTaxes[] = ['label' => "Redmond Sales Tax", 'amount' => $taxRed];

                // 2. Total
                // The item total is Base + Taxes + Addons/Extras
                $thisItemTotal = $baseItemPrice + $taxAdm + $taxRed + $totalAdditionalPrice + $addonTotal;
                
                // 3. Voucher Coverage
                // Since specific voucher covers the game, we add this WHOLE amount to the Voucher Total
                $voucherAmountTotal += $thisItemTotal;

            } else {
                // [CASE B] STANDARD / GENERIC VOUCHER
                
                // 1. Taxes from API
                if (isset($bookeo['price']['taxes']) && is_array($bookeo['price']['taxes'])) {
                    foreach ($bookeo['price']['taxes'] as $tax) {
                        $taxId  = $tax['taxId'] ?? '';
                        $label  = $taxLabels[$taxId] ?? $taxId;
                        $amount = floatval($tax['amount']['amount'] ?? 0);
                        $itemTaxes[] = ['label' => $label, 'amount' => $amount];
                    }
                }

                // 2. Monetary Voucher Credit
                $voucherAmt = 0.0;
                if (isset($bookeo['applicableGiftVoucherCredit']['amount'])) {
                    $voucherAmt = floatval($bookeo['applicableGiftVoucherCredit']['amount']);
                } elseif (isset($bookeo['price']['applicableGiftVoucherCredit']['amount'])) {
                    $voucherAmt = floatval($bookeo['price']['applicableGiftVoucherCredit']['amount']);
                }
                $voucherAmountTotal += $voucherAmt;

                // 3. Promo Discount
                $bookeoPromoAmount = floatval($bookeo['appliedPromotionDiscount']['amount'] ?? 0);
                $promoIsActive = (isset($bookeo['promotionApplicable']) && $bookeo['promotionApplicable'] === true)
                                 || $bookeoPromoAmount > 0;

                if ($promoIsActive) {
                    $promoCodeApplied = $bookeo['_internal_promo'] ?? ($item['promo_code'] ?? null);
                    $promoLabelText   = $bookeo['promotionName'] ?? null;

                    $bookeoNetAfterPromo = floatval($bookeo['price']['totalNet']['amount'] ?? 0);
                    $localOriginalNet    = $baseItemPrice + $totalAdditionalPrice + $addonTotal;

                    if ($bookeoNetAfterPromo > 0 && $localOriginalNet > $bookeoNetAfterPromo) {
                        $effectiveDiscount = $localOriginalNet - $bookeoNetAfterPromo;
                    } else {
                        $effectiveDiscount = $bookeoPromoAmount;
                    }

                    if ($effectiveDiscount > 0) {
                        $itemDiscount  += $effectiveDiscount;
                        $totalDiscount += $effectiveDiscount;
                    }
                }

                // 4. Item Total (Gross from API)
                $thisItemTotal = $bookeoGross;

                // Note: Bookeo Gross usually doesn't include custom Addons added in PHP if they aren't in Bookeo payload
                // If 'addon_subtotal' is local only, add it here:
                //  if (!empty($item['addon_subtotal'])) {
                //      $thisItemTotal += floatval($item['addon_subtotal'] + ($item['addon_tax']??0));
                //  }
                 if ($totalAdditionalPrice > 0) {
                    //  $thisItemTotal += $totalAdditionalPrice;
                    $thisItemTotal = $bookeoGross; 
                 }
            }
        } else {
             // Fallback if no API response found (assume base price)
             $thisItemTotal = $baseItemPrice;
        }
    }

    // Add to Grand Total
    $grandTotal += $thisItemTotal;


    // --- RENDER HTML ---
    $displayName = htmlspecialchars($item['game_name']);
    if (strlen($displayName) > 30) $displayName = substr($displayName, 0, 30) . '...';
    
    try { $fDate = (new DateTime($item['slot']))->format('l, F j, Y g:i A'); } 
    catch (Exception $e) { $fDate = htmlspecialchars($item['slot']); }

    echo '<div class="summary-row-group">';
    echo '  <div class="d-flex justify-content-between align-items-center">';
    echo '      <div class="summary-date"><p>' . $fDate . '</p></div>';
    echo '  </div>';

    // Add position:relative to align the delete icon correctly
    echo '  <div class="summary-row" style="position: relative;">';
    echo '      <div>' . $displayName . '</div>';
    echo '      <div>$' . number_format($unitPrice, 2) . '</div>';

    // QTY SELECTOR (This part is unchanged)
    echo '<div class="checkout_QUNT_select">';
    if (!empty($item['cat']) && (strtolower($item['cat']) === 'party-package' || strtolower($item['cat']) === 'event-rooms')) {
        echo '<span>1</span>';
    } else {
        echo '<select name="qty"
            id="guest-' . $item['game_id'] . '"
            data-event="' . $item['event_id'] . '"
            data-game="' . $item['game_id'] . '"
            class="QNT_SELECT_drop qty-input"
            style="width:80px;">';
        for ($i = 2; $i <= $item['dataAvailable']; $i++) {
            $sel = ($i == $qty) ? 'selected' : '';
            echo '<option value="' . $i . '" ' . $sel . '>' . $i . '</option>';
        }
        echo '</select>';
    }
    echo '</div>';

    echo '      <div>$' . number_format($unitPrice * $qty, 2) . '</div>';

    // --- NEW: Add the consistent delete button with data-index ---
    echo '      <span class="remove-game-btn" data-index="' . (int)$index . '" style="cursor:pointer; color:#ff4d4d; margin-right:8px;" title="Remove Game">';
    echo '          <i class="fa-solid fa-trash-can"></i>';
    echo '      </span>';
    // --- End of new button ---

    echo '  </div>';
    
    // --- [NEW] ADDITIONAL GUESTS ROW ---
    $addGuests = (int)($item['additional_guest'] ?? 0);
    $addGuestTotal = (float)($item['total_additional_price'] ?? 0);
    $perGuestPrice = (float)($item['per_guest_price'] ?? 0);

    // Fallback: If per_guest_price is missing but total exists, calculate it
    if ($perGuestPrice == 0 && $addGuests > 0) {
        $perGuestPrice = $addGuestTotal / $addGuests;
    }

    if ($addGuests > 0) {
        echo '<div class="summary-row" style="color: #00d4ff; font-size: 0.95em;">';
        echo '  <div style="padding-left: 20px;">';
        echo '      <i class="fa-solid fa-user-plus" style="margin-right:5px;"></i> Additional Guests';
        echo '  </div>';
        echo '  <div>$' . number_format($perGuestPrice, 2) . '</div>';
        echo '  <div class="checkout_QUNT_select">';
        echo '      <select class="QNT_SELECT_drop qty-input QNT_SELECT_drop update-additional-guest" data-cart-id="' . $item['id'] . '" style="width:60px;">';
                    // Allow 0 up to 20 additional guests (or adjust loop as needed)
                    for ($i = 1; $i <= 10; $i++) {
                    // for ($i = 2; $i <= 6; $i++) {
                        $selected = ($i == $addGuests) ? 'selected' : '';
                        echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
                    }
        echo '      </select>';
        echo '  </div>';
        echo '  <div>$' . number_format($addGuestTotal, 2) . '</div>';
        echo '          <span class="remove-additional-guest-btn" data-cart-id="' . $item['id'] . '" style="cursor:pointer; color:#ff4d4d; margin-right:8px;" title="Remove Addon">';
        echo '              <i class="fa-solid fa-trash-can"></i>';
        echo '          </span>';
        echo '</div>';
    }
    // -----------------------------------

    // ADDONS
    if (!empty($item['addon_name']) && $item['addon_qty'] > 0) {
        echo '  <div class="summary-row" style="position: relative;">';
        echo '      <div class="d-flex align-items-center">' . htmlspecialchars($item['addon_name']) . '</div>';
        echo '      <div>$' . number_format($item['addon_price'], 2) . '</div>';
        echo '      <div class="checkout_QUNT_select">';
    
        // --- DYNAMIC ADDON QTY LOGIC ---
        $min = 1;
        $max = 10; // Default max
        $isOnOffOption = false;
    
        // Find the product in our cache to determine the addon type
        $product = flee_find_cached_product($products, $item['game_id']);
    
        if ($product) {
            // Check OnOffOptions first (for party packages, typically has no dropdown)
            if (!empty($product['onOffOptions'])) {
                foreach ($product['onOffOptions'] as $opt) {
                    if ($opt['id'] === $item['addon_opt_id']) {
                        $isOnOffOption = true;
                        break;
                    }
                }
            }
            
            // If it wasn't an OnOffOption, check NumberOptions for min/max values
            if (!$isOnOffOption && !empty($product['numberOptions'])) {
                foreach ($product['numberOptions'] as $opt) {
                    if ($opt['id'] === $item['addon_opt_id']) {
                        $min = (int)($opt['minValue'] ?? 1);
                        $max = (int)($opt['maxValue'] ?? 10);
                        break;
                    }
                }
            }
        }
    
        // Render the correct control based on the addon type
        if ($isOnOffOption) {
            // For on/off addons, just show a static "1" as the quantity is not changeable.
            echo '<span>1</span>';
        } else {
            // For number-based addons, show the dynamic dropdown
            echo '<select class="QNT_SELECT_drop qty-input update-addon-qty" data-cart-id="' . $item['id'] . '" style="width:60px;">';
            for ($i = $min; $i <= $max; $i++) {
                $selected = ($i == $item['addon_qty']) ? 'selected' : '';
                echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
            }
            echo '</select>';
        }
        // --- END DYNAMIC LOGIC ---
    
        echo '      </div>';
        echo '      <div>$' . number_format($item['addon_subtotal'], 2) . '</div>';
        echo '      <span class="remove-addon-btn" data-cart-id="' . $item['id'] . '" style="cursor:pointer; color:#ff4d4d; margin-right:8px;" title="Remove Addon">';
        echo '          <i class="fa-solid fa-trash-can"></i>';
        echo '      </span>';
        echo '  </div>';
    }

    // TAXES
    foreach ($itemTaxes as $tax) {
        $taxAmt = $tax['amount'];
        echo '<div class="summary-row">';
        echo '  <div style="padding-left:20px;">' . htmlspecialchars($tax['label']) . '</div>';
        echo '<div></div><div></div>';
        echo '  <div>$' . number_format($taxAmt, 2) . '</div>';
        echo '</div>';
    }

    // DISCOUNT ROW
    if ($itemDiscount > 0) {
        if (!empty($promoLabelText)) {
            $label = $promoLabelText;
        } elseif (!empty($promoCodeApplied)) {
            $code = strtoupper($promoCodeApplied);
            if (strpos($code, 'BMSM') !== false) {
                $label = "Play More Save More";
                if ($code === 'BMSM_20') $label .= " - 20% OFF";
                elseif ($code === 'BMSM_10') $label .= " - 10% OFF";
            } else {
                $label = ucwords(strtolower(str_replace(['_', '-'], ' ', $promoCodeApplied)));
            }
        } else {
            $label = "Applied Promotion";
        }

        echo '<div class="summary-row discount-row">';
        echo '  <div style="padding-left:20px;">' . htmlspecialchars($label) . '</div>';
        echo '<div></div><div></div>';
        echo '  <div>- $' . number_format($itemDiscount, 2) . '</div>';
        echo '</div>';
    }

    // ROW TOTAL
    echo '<div class="summary-row">';
    echo '  <div style="padding-left:20px;font-weight:bold;">Total</div>';
    echo '<div></div><div></div>';
    echo '  <div style="font-weight:bold;">$' . number_format($thisItemTotal, 2) . '</div>';
    echo '</div>';
    
    // SPECIFIC VOUCHER TEXT
    // if ($isSpecificVoucher) {
    //     echo '<div class="summary-row" style="color:#00d4ff; font-size:12px;">';
    //     echo '  <div style="padding-left:20px;">Generic Voucher Applied</div>';
    //     echo '</div>';
    // }
    
    echo '</div>'; // End Group
}

// --- TOTALS SECTION ---

// 1. GRAND TOTAL
echo '<div class="summary-row grand-total" style="margin-top:15px;font-size:16px;font-weight:bold;">';
echo '  <div>Grand Total</div><div></div><div></div>';
echo '  <div>$' . number_format($grandTotal, 2) . '</div>';
echo '</div>';

// 2. VOUCHER AMOUNT
if ($voucherAmountTotal > 0) {
    echo '<div class="summary-row" style="color: #00d4ff; font-weight:bold;">';
    echo '  <div>Gift Voucher Applied</div><div></div><div></div>';
    echo '  <div>- $' . number_format($voucherAmountTotal, 2) . '</div>';
    echo '</div>';
}

// 3. BALANCE DUE
$balanceDue = max(0, $grandTotal - $voucherAmountTotal);
if($balanceDue < 0.01) $balanceDue = 0.00;

echo '<div class="summary-row" style="border-top: 1px solid #333; margin-top:5px; padding-top:5px; font-size:18px; color:#fff;">';
echo '  <div>Balance Due</div><div></div><div></div>';
echo '  <div>$' . number_format($balanceDue, 2) . '</div>';
echo '</div>';


// JSON for Frontend
echo '<div id="bookeo-totals" data-totals=\'' . json_encode([
    'subtotal'      => $subtotal,
    'discount'      => $totalDiscount,
    'grandTotal'    => $grandTotal,
    'voucherAmount' => $voucherAmountTotal,
    'totalPayable'  => $balanceDue
], JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP) . '\'></div>';
?>
