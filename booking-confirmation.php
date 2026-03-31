<?php

$pageTitle = 'Booking Confirmation';
include('./includes/header.php');

if (empty($_SESSION['booking_summary'])) {
    // echo '<meta http-equiv="refresh" content="0;URL=https://indiawebsoft.co.in/fleeescape-new">';
    echo '<meta http-equiv="refresh" content="0;URL=' . BASE_URL . '">';
    exit;
}

function bc_normalize_code_list($codes) {
    if (is_array($codes)) {
        $list = $codes;
    } else {
        $list = explode(',', (string)$codes);
    }

    $list = array_map('trim', $list);
    return array_values(array_unique(array_filter($list, static function ($code) {
        return $code !== '';
    })));
}

function bc_promo_label(array $summaryItem) {
    $label = trim((string)($summaryItem['promo_label'] ?? ''));
    if ($label !== '') {
        return $label;
    }

    $promoName = trim((string)($summaryItem['promo_name'] ?? ''));
    if ($promoName !== '') {
        return $promoName;
    }

    $promoCode = trim((string)($summaryItem['promo_code'] ?? ''));
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

function bc_voucher_label(array $summaryItem) {
    $codes = bc_normalize_code_list($summaryItem['voucher_codes'] ?? []);
    if (empty($codes)) {
        return 'Gift Voucher Applied';
    }

    $prefix = count($codes) > 1 ? 'Gift Vouchers Applied' : 'Gift Voucher Applied';
    return $prefix . ' (' . implode(', ', array_map('htmlspecialchars', $codes)) . ')';
}
?>

<div class="booking_banner booking_success_banner">
    <img src="./assets/images/fleeescape_img/banner/Booking_baneer_img.png" loading="lazy"  decoding="async"  alt="booking banner" />

    <div class="booking_banner_content booking_success_content">

        <div class="success_icon_3d">
    🎉
</div>


        <h1 class="success_title">
            <span class="blue_text">Congratulations!</span> Booking Confirmed
        </h1>

        <p class="success_subtitle">
            Your adventure is booked! Scroll down to view full booking details.
        </p>

    </div>
</div>
<style>
    .booking_success_banner {
    position: relative;
    text-align: center;
    overflow: hidden;
}

.booking_success_content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #ffffff;
    text-align: center;
    width: 100%;
}

.success_icon_3d {
    font-size: 85px;
    animation: float3D 3s ease-in-out infinite;
    filter: drop-shadow(0px 0px 15px rgba(0,255,234,0.8));
    margin-bottom: 15px;
    transform-style: preserve-3d;
        margin-bottom: 50px;
}

@keyframes float3D {
    0%   { transform: translateY(0px) rotateX(0deg) rotateY(0deg) scale(1); }
    50%  { transform: translateY(-15px) rotateX(10deg) rotateY(-10deg) scale(1.1); }
    100% { transform: translateY(0px) rotateX(0deg) rotateY(0deg) scale(1); }
}

.success_title {
    font-size: 42px;
    font-weight: 800;
    text-shadow: 0 0 15px rgba(0,255,255,0.6);
}

.blue_text {
    color: #00d4ff;
}

.success_subtitle {
    font-size: 18px;
    margin-top: 10px;
    opacity: 0.9;
}

</style>

<div class="Booking_Confirmation_new_page" id="custom_scroll">
    <div class="container">
             <div class="row " id="printableArea">
                <div class="col-xl-7">
                    <div class="payment_left_tab"
                        style="background-image: url(./assets/images/fleeescape_img/party_pack/Most_Value.jpg);">
                      <div class="payment_images_done">
    <div class="booking_done_img_main">
        <div class="booking_done_img_main_img">
            <img src="./assets/images/fleeescape_img/CHOOSE/4.jpg" loading="lazy" decoding="async" alt="Payment BG img" />
        </div>
        <div class="booking_done_img_main_gallery">
            <img src="./assets/images/fleeescape_img/party_pack/Corporate.jpg" loading="lazy" decoding="async">
            <img src="./assets/images/fleeescape_img/party_pack/Kids.png" loading="lazy" decoding="async">
            <img src="./assets/images/fleeescape_img/party_pack/Portable.jpg" loading="lazy" decoding="async">
        </div>
    </div>

    <!-- âœ… Dynamic Product Name -->
   <h1>
<?php
if (!empty($_SESSION['booking_summary'])) {
    include_once "admin/db.php";

    foreach ($_SESSION['booking_summary'] as $b) {
        $bn = $b['bookingNumber'] ?? '';
        if (!$bn) continue;

        $stmt = $pdo->prepare("SELECT productName FROM tbl_bookings WHERE bookingNumber = :bn LIMIT 1");
        $stmt->execute([':bn' => $bn]);
        $productName = $stmt->fetchColumn();
        if ($productName) {
            echo htmlspecialchars($productName) . "<br>"; // <br> se ek ke neeche ek ayega
        }
    }
}
?>
</h1>


    <div class="pay_done_add_all_aeax" style="display: none;">
        <div class="payment_address_tab">
            <h1>South Lake Union</h1>
            526 WESTLAKE AVE N.<br />
            SEATTLE, WA 98109
        </div>
        <div class="payment_maplink_tab">Open in Maps</div>
        <div class="payment_detailgrid_tab">
            <div class="payment_column_tab">
                <div class="payment_maintext_tab">4</div>
                <div class="payment_labeltext_tab">GUESTS</div>
            </div>
            <div class="payment_column_tab">
                <div class="payment_maintext_tab">10:00 AM</div>
                <div class="payment_labeltext_tab">JULY 28TH,<br />2025</div>
            </div>
        </div>
        <div class="payment_session_tag">ðŸ”’ PUBLIC SESSION</div>
    </div>
</div>

 
                    </div>
                </div>
                <div class="col-xl-5 payment_confirmation">
                    <h2 class="payment_confirmation_heading" data-aos="fade-right">Payment Confirmation</h2>
<div id="payment_confirmation" style="background-size: cover; background-repeat: no-repeat;">
                    
        <?php

if (!empty($_SESSION['booking_summary'])) {
  
include "admin/db.php"; // adjust path if needed

// Turn on error reporting for debugging
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

try {
    if (empty($_SESSION['booking_summary'])) {
        echo "<p style='color:red;'>No booking found in session.</p>";
        exit;
    }

    $html = "";
    $customerShown = false;
    $grandTotal = 0;
    $grandTaxes = 0;
    $grandVoucherAmount = 0;
    $grandBalanceDue = 0;
    $confirmationEmail = '';
    $summaryMap = [];

    foreach ($_SESSION['booking_summary'] as $summaryItem) {
        $summaryBookingNumber = $summaryItem['bookingNumber'] ?? '';
        if ($summaryBookingNumber !== '') {
            $summaryMap[$summaryBookingNumber] = $summaryItem;
        }
    }

    foreach ($_SESSION['booking_summary'] as $b) {
        $bookingNumber = $b['bookingNumber'] ?? '';

        if (empty($bookingNumber)) {
            continue;
        }

        $query = "SELECT b.*, u.firstName, u.lastName, u.email, u.phone
                  FROM tbl_bookings b
                  LEFT JOIN tbl_users u ON b.user_id = u.id
                  WHERE b.bookingNumber = :bookingNumber";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':bookingNumber' => $bookingNumber]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) continue;

        // Show customer info only once
        if (!$customerShown) {
            $confirmationEmail = $row["email"] ?? '';
            $html .= '
            <div class="booking_com_customer_info">
                <h3>Customer Info</h3>
                <p>Name: <strong>' . htmlspecialchars($row["firstName"] . " " . $row["lastName"]) . '</strong></p>
                <p>Email: <strong>' . htmlspecialchars($row["email"]) . '</strong></p>
                <p>Phone: <strong>' . htmlspecialchars($row["phone"]) . '</strong></p>
            </div>';
            $customerShown = true;
        }

        // Decode JSON fields
        $price = json_decode($row['priceJson'], true) ?? [];
        $participants = json_decode($row['participantsJson'], true) ?? [];
        $priceAdjustments = json_decode($row['priceAdjustments'], true) ?? [];
        $taxes = json_decode($row['taxesJson'], true) ?? [];
        $summaryItem = $summaryMap[$bookingNumber] ?? [];

        $totalGross = isset($price['totalGross']['amount']) ? (float)$price['totalGross']['amount'] : 0;
        $totalTaxes = isset($price['totalTaxes']['amount']) ? (float)$price['totalTaxes']['amount'] : 0;

        $participantCount = 0;
        if (!empty($participants['numbers'])) {
            foreach ($participants['numbers'] as $participant) {
                $participantCount += (int)($participant['number'] ?? 0);
            }
        }

        $escapeRoomUnitPrice = (float)($summaryItem['price'] ?? 0);
        $escapeRoomSubtotal = (float)($summaryItem['escape_room_subtotal'] ?? 0);
        if ($escapeRoomSubtotal <= 0 && $escapeRoomUnitPrice > 0 && $participantCount > 0) {
            $escapeRoomSubtotal = $escapeRoomUnitPrice * $participantCount;
        }

        $additionalGuestCount = (int)($summaryItem['additional_guest'] ?? 0);
        $additionalGuestUnitPrice = (float)($summaryItem['per_guest_price'] ?? 0);
        $additionalGuestSubtotal = (float)($summaryItem['total_additional_price'] ?? 0);
        if ($additionalGuestUnitPrice <= 0 && $additionalGuestCount > 0 && $additionalGuestSubtotal > 0) {
            $additionalGuestUnitPrice = $additionalGuestSubtotal / $additionalGuestCount;
        }

        $addonTitle = trim((string)($summaryItem['addon_title'] ?? ($row['addon_title'] ?? '')));
        $addonQty = (int)($summaryItem['addon_qty'] ?? ($row['addon_qty'] ?? 0));
        $addonPrice = (float)($summaryItem['addon_price'] ?? ($row['addon_price'] ?? 0));
        $addonSubtotal = (float)($summaryItem['addon_subtotal'] ?? ($row['addon_subtotal'] ?? 0));
        if ($addonPrice <= 0 && $addonQty > 0 && $addonSubtotal > 0) {
            $addonPrice = $addonSubtotal / $addonQty;
        }

        $bookingSubtotal = $escapeRoomSubtotal + $additionalGuestSubtotal + $addonSubtotal;
        if ($bookingSubtotal <= 0 && isset($price['totalNet']['amount'])) {
            $bookingSubtotal = (float)$price['totalNet']['amount'];
        }

        $displayTaxes = [];
        if (!empty($summaryItem['display_taxes']) && is_array($summaryItem['display_taxes'])) {
            foreach ($summaryItem['display_taxes'] as $taxRow) {
                $displayTaxes[] = [
                    'label' => (string)($taxRow['label'] ?? 'Tax'),
                    'amount' => (float)($taxRow['amount'] ?? 0)
                ];
            }
        } elseif (!empty($taxes)) {
            $taxCount = count($taxes);
            foreach ($taxes as $i => $tax) {
                $taxAmount = isset($tax['amount']['amount']) ? (float)$tax['amount']['amount'] : 0;
                if ($taxCount == 1) {
                    $taxName = "Redmond Sales Tax";
                } else {
                    $taxName = ($i == 0) ? "Admission Tax" : "Redmond Sales Tax";
                }

                $displayTaxes[] = [
                    'label' => $taxName,
                    'amount' => $taxAmount
                ];
            }
        }

        $displayTaxTotal = 0.0;
        foreach ($displayTaxes as $taxRow) {
            $displayTaxTotal += (float)($taxRow['amount'] ?? 0);
        }

        $bookingTotalDisplay = isset($summaryItem['display_booking_total'])
            ? (float)$summaryItem['display_booking_total']
            : $totalGross;
        if ($bookingTotalDisplay <= 0 && $bookingSubtotal > 0) {
            $bookingTotalDisplay = $bookingSubtotal + $displayTaxTotal;
        }

        $voucherAmountDisplay = isset($summaryItem['voucher_amount'])
            ? (float)$summaryItem['voucher_amount']
            : 0.0;
        $promoDiscountDisplay = isset($summaryItem['promo_discount'])
            ? (float)$summaryItem['promo_discount']
            : 0.0;
        $totalPaidDisplay = isset($summaryItem['display_total_paid'])
            ? (float)$summaryItem['display_total_paid']
            : 0.0;
        $balanceDueDisplay = isset($summaryItem['display_balance_due'])
            ? (float)$summaryItem['display_balance_due']
            : max(0, $bookingTotalDisplay - $voucherAmountDisplay - $totalPaidDisplay);

        if ($balanceDueDisplay < 0.01) {
            $balanceDueDisplay = 0.0;
        }

        $grandTotal += $bookingTotalDisplay;
        $grandTaxes += ($displayTaxTotal > 0 ? $displayTaxTotal : $totalTaxes);
        $grandVoucherAmount += $voucherAmountDisplay;
        $grandBalanceDue += $balanceDueDisplay;

        $html .= '
        <div class="booking_com_customer_info">
            <h3 class="booking_com_customer_info_ID">Booking #' . htmlspecialchars($row["bookingNumber"]) . ' - ' . htmlspecialchars($row["productName"]) . '</h3>
            <div class="">
                <p>Event Start: 
                    <span>' . date("d M Y", strtotime($row["startTime"])) . ' ' . date("h:i A", strtotime($row["startTime"])) . '</span>
                </p>
                <p>Event End: 
                    <span>' . date("d M Y", strtotime($row["endTime"])) . ' ' . date("h:i A", strtotime($row["endTime"])) . '</span>
                </p>';

        // Participants
        if (!empty($participants['numbers'])) {
            foreach ($participants['numbers'] as $p) {
                $html .= '<p>Participants (' . htmlspecialchars($p['peopleCategoryId']) . '): <span>' . htmlspecialchars($p['number']) . '</span></p>';
            }
        }

        if ($escapeRoomSubtotal > 0) {
            $escapeLabel = 'Escape Room';
            if ($participantCount > 0 && $escapeRoomUnitPrice > 0) {
                $escapeLabel .= ': ' . $participantCount . ' x $' . number_format($escapeRoomUnitPrice, 2);
            }
            $html .= '<p>' . htmlspecialchars($escapeLabel) . ' = <span>$' . number_format($escapeRoomSubtotal, 2) . '</span></p>';
        }

        $renderedAdditionalGuests = false;
        if ($additionalGuestCount > 0 || $additionalGuestSubtotal > 0) {
            $additionalGuestLabel = 'Additional Guests';
            if ($additionalGuestCount > 0 && $additionalGuestUnitPrice > 0) {
                $additionalGuestLabel .= ': ' . $additionalGuestCount . ' x $' . number_format($additionalGuestUnitPrice, 2);
            } elseif ($additionalGuestCount > 0) {
                $additionalGuestLabel .= ': ' . $additionalGuestCount;
            }

            $html .= '<p>' . htmlspecialchars($additionalGuestLabel) . ' = <span>$' . number_format($additionalGuestSubtotal, 2) . '</span></p>';
            $renderedAdditionalGuests = true;
        }

        if ($addonTitle !== '' && ($addonQty > 0 || $addonSubtotal > 0)) {
            $addonLabel = $addonTitle;
            if ($addonQty > 0 && $addonPrice > 0) {
                $addonLabel .= ': ' . $addonQty . ' x $' . number_format($addonPrice, 2);
            } elseif ($addonQty > 0) {
                $addonLabel .= ': ' . $addonQty;
            }

            $html .= '<p>' . htmlspecialchars($addonLabel) . ' = <span>$' . number_format($addonSubtotal, 2) . '</span></p>';
        }

        // Fallback adjustments from Bookeo for items not preserved locally.
        if (!empty($priceAdjustments)) {
            foreach ($priceAdjustments as $adj) {
                $qty = $adj['quantity'] ?? 0;
                $desc = $adj['description'] ?? '';
                $unitPrice = isset($adj['unitPrice']['amount']) ? (float)$adj['unitPrice']['amount'] : 0;
                $totalPrice = isset($adj['totalPrice']['amount']) ? (float)$adj['totalPrice']['amount'] : 0;
                $isAdditionalGuest = stripos($desc, 'additional guest') !== false;

                if ($renderedAdditionalGuests && $isAdditionalGuest) {
                    continue;
                }

                $html .= '<p>' . htmlspecialchars($desc) . ': ' . $qty . ' x $' . number_format($unitPrice,2) . ' = <span>$' . number_format($totalPrice,2) . '</span></p>';
            }
        }

        if ($bookingSubtotal > 0) {
            $html .= '<p>Subtotal: <span>$' . number_format($bookingSubtotal, 2) . '</span></p>';
        }

        foreach ($displayTaxes as $taxRow) {
            $html .= '<p>' . htmlspecialchars($taxRow['label']) . ': <span>$' . number_format((float)$taxRow['amount'], 2) . '</span></p>';
        }

        if ($promoDiscountDisplay > 0) {
            $html .= '<p>' . htmlspecialchars(bc_promo_label($summaryItem)) . ': <span>- $' . number_format($promoDiscountDisplay, 2) . '</span></p>';
        }

        $html .= '<p class="total">Booking Total: <span>$' . number_format($bookingTotalDisplay,2) . '</span></p>';

        if ($voucherAmountDisplay > 0) {
            $html .= '<p>' . bc_voucher_label($summaryItem) . ': <span>- $' . number_format($voucherAmountDisplay, 2) . '</span></p>';
        }

        if ($balanceDueDisplay > 0) {
            $html .= '<p class="total">Balance Due: <span>$' . number_format($balanceDueDisplay, 2) . '</span></p>';
        }

        $html .= '</div></div>';
    }

    // Grand total
    $html .= '
    <div class="booking_com_customer_info">
        <h3 style="color:#00d4ff">Grand Total</h3>
        <p>Total Amount: <span>$' . number_format($grandTotal,2) . '</span></p>
        <p>Total Taxes: <span>$' . number_format($grandTaxes,2) . '</span></p>';

    if ($grandVoucherAmount > 0) {
        $html .= '<p>Total Voucher Applied: <span>- $' . number_format($grandVoucherAmount, 2) . '</span></p>';
    }

    if ($grandBalanceDue > 0) {
        $html .= '<p class="total">Balance Due: <span>$' . number_format($grandBalanceDue, 2) . '</span></p>';
    }

    $html .= '
    </div>';

    echo $html ?: "<p style='color:red;'>No booking summary available.</p>";

} catch (PDOException $e) {
    echo "<div style='color:red; background:#fee; padding:10px; border:1px solid #f99;'>
            <strong>Database Error:</strong> " . htmlspecialchars($e->getMessage()) . "
          </div>";
} catch (Exception $e) {
    echo "<div style='color:red; background:#fee; padding:10px; border:1px solid #f99;'>
            <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "
          </div>";
}

} else {
    echo "<p>No booking found yet.</p>";
    $confirmationEmail = '';
}
?>
    
    
    
    </div>
                </div>
            </div>
            <div class="Confirmation_so_butn">
             <div class="email-message">
                    <p>An email has been sent to <?php echo htmlspecialchars($confirmationEmail); ?> with all the booking details.</p>
                    <p>If you do not find it in your inbox shortly, please check the spam/junk folder.</p>
                    <a class="bg_bnt_custom " onclick="openPrintPage()">Print</a>


                </div>
           <div class="share-section Confirmation_so_butn_flex_box">
                    <h2 class="share-heading">Share with your friends</h2>
                    <p class="share-description">
                        Share your bookings with your friends and you both get $10 off on your next booking!
                    </p>
                   <div class="button-group">
                        <a href="https://www.facebook.com/"
                           target="_blank"
                           class="bg_bnt_custom bg_bnt_custom_tran">
                            Post on Facebook
                        </a>
                    
                        <a href="https://x.com/"
                           target="_blank"
                           class="bg_bnt_custom">
                            Post on X
                        </a>
                    </div>

                </div>
        </div> 
    </div>
</div>
            
            <script>
function printBooking() {
    var printContents = document.getElementById("printableArea").innerHTML;
    var originalContents = document.body.innerHTML;

    // Temporary print page
    document.body.innerHTML = `
        <html>
            <head>
                <title>Booking Confirmation</title>
                <style>
                    body {  padding: 20px; }
                </style>
            </head>
            <body>${printContents}</body>
        </html>
    `;

    window.print();

    // Restore original page
    document.body.innerHTML = originalContents;
    location.reload();   // restore scripts
}
</script>

<script>
    const bookingData = <?php echo json_encode($_SESSION['booking_summary']); ?>;
function openPrintPage() {
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'booking-print-demo.php';
    form.target = '_blank'; 

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'booking_summary';
    input.value = JSON.stringify(bookingData);

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>

<?php 
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy(); 
include('./includes/footer.php'); ?>
