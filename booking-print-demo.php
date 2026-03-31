<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your booking with 'FLEE Escape Rooms and Zero Latency VR Seattle' - Bookeo</title>

<style>
body {
    font-family: Arial, Helvetica, sans-serif;
    margin: 0;
    background: #fff;
    color: #000;
    font-size: 11px;
}

@page {
    size: A4;
    margin: 8mm;
}

@media print {
    body { -webkit-print-color-adjust: exact; }

    .booking-row {
        flex-direction: row !important;
        flex-wrap: nowrap !important;
    }

    .booking-left,
    .booking-right {
        width: 50% !important;
    }
}

.page {
    width: 210mm;
    margin: auto;
    padding: 10mm;
    background: white;
    border-bottom: 2px solid #000;
}

.header-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-left img {
    width: 150px;
}

.header-right {
    text-align: right;
    font-size: 11px;
    line-height: 16px;
    min-width: 230px;
}

.section-title {
    font-size: 17px;
    margin: 10px 0 8px;
    font-weight: bold;
}

.divider {
    border-top: 1px solid #ccc;
    margin: 10px 0;
}

.booking-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    flex-wrap: wrap;
}

.booking-left {
    width: 50%;
    min-width: 240px;
}

.booking-right {
    width: 40%;
    text-align: right;
    min-width: 180px;
}

.booking-right img {
    width: 160px;
    height: 110px;
    object-fit: cover;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.small-text {
    font-size: 11px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 5px;
    margin-bottom: 15px;
    font-size: 11px;
}

table th {
    background: #f7f7f7;
    border: 1px solid #ccc;
    padding: 6px;
    font-weight: bold;
}

table td {
    border: 1px solid #ccc;
    padding: 6px;
}

.right { text-align: right; }
.bold { font-weight: bold; }
</style>

<script>
window.onload = function () {
    setTimeout(function () {
        window.print();
    }, 500);
};
</script>
</head>

<body>

<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL);
include "admin/db.php";

function bp_normalize_code_list($codes) {
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

function bp_promo_label(array $summaryItem) {
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

function bp_voucher_label(array $summaryItem) {
    $codes = bp_normalize_code_list($summaryItem['voucher_codes'] ?? []);
    if (empty($codes)) {
        return 'Gift Voucher Applied';
    }

    $prefix = count($codes) > 1 ? 'Gift Vouchers Applied' : 'Gift Voucher Applied';
    return $prefix . ' (' . implode(', ', array_map('htmlspecialchars', $codes)) . ')';
}

if (empty($_POST['booking_summary'])) {
    echo 'booking summary not passed in post';
    exit();
}

$customerData = null;
$allBookings = [];
$grandTotal = 0;
$grandTaxes = 0;
$grandVoucherAmount = 0;
$grandBalanceDue = 0;

$bookingSummary = $_POST['booking_summary'];
if (is_string($bookingSummary)) {
    $bookingSummary = json_decode($bookingSummary, true);
}

if (!is_array($bookingSummary)) {
    echo 'invalid booking summary';
    exit();
}

foreach ($bookingSummary as $b) {
    $bookingNumber = $b['bookingNumber'] ?? "";
    if ($bookingNumber === "") {
        continue;
    }

    $q = $pdo->prepare("
        SELECT b.*, u.firstName, u.lastName, u.email, u.phone
        FROM tbl_bookings b
        LEFT JOIN tbl_users u ON b.user_id = u.id
        WHERE b.bookingNumber = :bn
    ");
    $q->execute([':bn' => $bookingNumber]);
    $row = $q->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        continue;
    }

    if (!$customerData) {
        $customerData = [
            "name"  => trim(($row["firstName"] ?? '') . " " . ($row["lastName"] ?? '')),
            "email" => $row["email"] ?? '',
            "phone" => $row["phone"] ?? ''
        ];
    }

    $price = json_decode($row['priceJson'], true) ?? [];
    $adjustments = json_decode($row['priceAdjustments'], true) ?? [];
    $parts = json_decode($row['participantsJson'], true) ?? [];
    $taxes = json_decode($row['taxesJson'], true) ?? [];

    $participantCount = 0;
    foreach (($parts['numbers'] ?? []) as $participant) {
        $participantCount += (int)($participant['number'] ?? 0);
    }

    $escapeRoomUnitPrice = (float)($b['price'] ?? 0);
    $escapeRoomSubtotal = (float)($b['escape_room_subtotal'] ?? 0);
    if ($escapeRoomSubtotal <= 0 && $escapeRoomUnitPrice > 0 && $participantCount > 0) {
        $escapeRoomSubtotal = $escapeRoomUnitPrice * $participantCount;
    }

    $additionalGuestCount = (int)($b['additional_guest'] ?? 0);
    $additionalGuestUnitPrice = (float)($b['per_guest_price'] ?? 0);
    $additionalGuestSubtotal = (float)($b['total_additional_price'] ?? 0);
    if ($additionalGuestUnitPrice <= 0 && $additionalGuestCount > 0 && $additionalGuestSubtotal > 0) {
        $additionalGuestUnitPrice = $additionalGuestSubtotal / $additionalGuestCount;
    }

    $addonTitle = trim((string)($b['addon_title'] ?? ($row['addon_title'] ?? '')));
    $addonQty = (int)($b['addon_qty'] ?? ($row['addon_qty'] ?? 0));
    $addonPrice = (float)($b['addon_price'] ?? ($row['addon_price'] ?? 0));
    $addonSubtotal = (float)($b['addon_subtotal'] ?? ($row['addon_subtotal'] ?? 0));
    if ($addonPrice <= 0 && $addonQty > 0 && $addonSubtotal > 0) {
        $addonPrice = $addonSubtotal / $addonQty;
    }

    $subtotal = $escapeRoomSubtotal + $additionalGuestSubtotal + $addonSubtotal;
    if ($subtotal <= 0) {
        $subtotal = (float)($price['totalNet']['amount'] ?? ($row["totalGross"] - $row["totalTaxes"]));
    }

    $displayTaxes = [];
    if (!empty($b['display_taxes']) && is_array($b['display_taxes'])) {
        foreach ($b['display_taxes'] as $taxRow) {
            $displayTaxes[] = [
                'label' => (string)($taxRow['label'] ?? 'Tax'),
                'amount' => (float)($taxRow['amount'] ?? 0)
            ];
        }
    } else {
        $taxCount = !empty($taxes) ? count($taxes) : 0;
        if ($taxCount > 0) {
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
    }

    $displayTaxTotal = 0.0;
    foreach ($displayTaxes as $taxRow) {
        $displayTaxTotal += (float)($taxRow['amount'] ?? 0);
    }

    $bookingTotalDisplay = isset($b['display_booking_total'])
        ? (float)$b['display_booking_total']
        : (float)$row["totalGross"];
    if ($bookingTotalDisplay <= 0 && $subtotal > 0) {
        $bookingTotalDisplay = $subtotal + $displayTaxTotal;
    }

    $promoDiscountDisplay = isset($b['promo_discount'])
        ? (float)$b['promo_discount']
        : 0.0;
    $voucherAmountDisplay = isset($b['voucher_amount'])
        ? (float)$b['voucher_amount']
        : 0.0;
    $totalPaidDisplay = isset($b['display_total_paid'])
        ? (float)$b['display_total_paid']
        : 0.0;
    $balanceDueDisplay = isset($b['display_balance_due'])
        ? (float)$b['display_balance_due']
        : max(0, $bookingTotalDisplay - $voucherAmountDisplay - $totalPaidDisplay);

    if ($balanceDueDisplay < 0.01) {
        $balanceDueDisplay = 0.0;
    }

    $productImage = "";
    $currentProductId = $row["productId"] ?? "";
    if ($currentProductId !== "") {
        $q2 = $pdo->query("SELECT product_data FROM bookeo_products_cache");
        $allRows = $q2->fetchAll(PDO::FETCH_ASSOC);

        foreach ($allRows as $cacheRow) {
            $json = json_decode($cacheRow['product_data'], true);
            if (empty($json['data']) || !is_array($json['data'])) {
                continue;
            }

            foreach ($json['data'] as $prod) {
                if (($prod['productId'] ?? '') === $currentProductId) {
                    if (!empty($prod['images'][0]['url'])) {
                        $productImage = $prod['images'][0]['url'];
                    }
                    break 2;
                }
            }
        }
    }

    $allBookings[] = [
        "productName" => $row["productName"],
        "startTime" => $row["startTime"],
        "endTime" => $row["endTime"],
        "bookingNumber" => $row["bookingNumber"],
        "participants" => $parts['numbers'] ?? [],
        "participantCount" => $participantCount,
        "escapeRoomUnitPrice" => $escapeRoomUnitPrice,
        "escapeRoomSubtotal" => $escapeRoomSubtotal,
        "additionalGuestCount" => $additionalGuestCount,
        "additionalGuestUnitPrice" => $additionalGuestUnitPrice,
        "additionalGuestSubtotal" => $additionalGuestSubtotal,
        "addonTitle" => $addonTitle,
        "addonQty" => $addonQty,
        "addonPrice" => $addonPrice,
        "addonSubtotal" => $addonSubtotal,
        "adjustments" => $adjustments,
        "subtotal" => $subtotal,
        "taxes" => $displayTaxes,
        "totalGross" => $bookingTotalDisplay,
        "totalTaxes" => $displayTaxTotal > 0 ? $displayTaxTotal : (float)$row["totalTaxes"],
        "promo_discount" => $promoDiscountDisplay,
        "promo_label" => bp_promo_label($b),
        "voucher_amount" => $voucherAmountDisplay,
        "voucher_label" => bp_voucher_label($b),
        "total_paid" => $totalPaidDisplay,
        "balance_due" => $balanceDueDisplay,
        "productId" => $row["productId"],
        "productImage" => $productImage
    ];

    $grandTotal += $bookingTotalDisplay;
    $grandTaxes += ($displayTaxTotal > 0 ? $displayTaxTotal : (float)$row["totalTaxes"]);
    $grandVoucherAmount += $voucherAmountDisplay;
    $grandBalanceDue += $balanceDueDisplay;
}
?>

<div class="page">
    <div class="header-flex">
        <div class="header-left">
            <img src="https://indiawebsoft.co.in/fleeescape-new/assets/images/logo.png">
        </div>
        <div class="header-right">
            <b>FLEE Escape Rooms and Zero Latency VR Seattle</b><br>
            #112-2222 152nd AVE NE, Redmond, Washington 98052<br>
            (425)287-1426<br>
            info@fleeescape.com<br>
            www.fleeescape.com
        </div>
    </div>

    <div class="divider"></div>

    <h3 class="section-title">Customer</h3>
    <div class="small-text">
        <strong><?= htmlspecialchars($customerData["name"] ?? '') ?></strong><br>
        Email: <?= htmlspecialchars($customerData["email"] ?? '') ?><br>
        Phone: <?= htmlspecialchars($customerData["phone"] ?? '') ?>
    </div>

    <div class="divider"></div>

    <h3 class="section-title">Booking details</h3>

    <?php foreach ($allBookings as $bk): ?>
        <div class="booking-row">
            <div class="booking-left">
                <div class="small-text"><strong><?= htmlspecialchars($bk["productName"]) ?></strong></div>
                <div class="small-text"><?= date("l, F j, Y", strtotime($bk["startTime"])) ?></div>
                <div class="small-text">
                    <?= date("g:i A", strtotime($bk["startTime"])) ?> -
                    <?= date("g:i A", strtotime($bk["endTime"])) ?>
                </div>

                <?php if (!empty($bk["participants"])): ?>
                    <?php foreach ($bk["participants"] as $p): ?>
                        <div class="small-text">
                            Participants (<?= htmlspecialchars($p["peopleCategoryId"] ?? '') ?>):
                            <?= htmlspecialchars($p["number"] ?? 0) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="small-text">Booking Number: <?= htmlspecialchars($bk["bookingNumber"]) ?></div>
                <br>

                <div class="small-text">Total price: <strong>$<?= number_format($bk["totalGross"], 2) ?></strong></div>
                <?php if ($bk["voucher_amount"] > 0): ?>
                    <div class="small-text"><?= $bk["voucher_label"] ?>: <strong>- $<?= number_format($bk["voucher_amount"], 2) ?></strong></div>
                <?php endif; ?>
                <?php if ($bk["balance_due"] > 0): ?>
                    <div class="small-text">Amount due: <strong>$<?= number_format($bk["balance_due"], 2) ?></strong></div>
                <?php endif; ?>
            </div>

            <div class="booking-right">
                <?php if ($bk["productImage"] !== ""): ?>
                    <img src="<?= htmlspecialchars($bk["productImage"]) ?>" />
                <?php endif; ?>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Unit price</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($bk["participants"]) && $bk["escapeRoomSubtotal"] > 0): ?>
                    <tr>
                        <td>
                            <strong><?= date("l, F j, Y g:i A", strtotime($bk["startTime"])) ?></strong><br>
                            <?= htmlspecialchars($bk["productName"]) ?> - participants
                        </td>
                        <td>$<?= number_format($bk["escapeRoomUnitPrice"], 2) ?></td>
                        <td><?= htmlspecialchars($bk["participantCount"]) ?></td>
                        <td>$<?= number_format($bk["escapeRoomSubtotal"], 2) ?></td>
                    </tr>
                <?php endif; ?>

                <?php if ($bk["additionalGuestCount"] > 0 || $bk["additionalGuestSubtotal"] > 0): ?>
                    <tr>
                        <td>Additional Guests</td>
                        <td>$<?= number_format($bk["additionalGuestUnitPrice"], 2) ?></td>
                        <td><?= htmlspecialchars($bk["additionalGuestCount"]) ?></td>
                        <td>$<?= number_format($bk["additionalGuestSubtotal"], 2) ?></td>
                    </tr>
                <?php endif; ?>

                <?php if ($bk["addonTitle"] !== '' && ($bk["addonQty"] > 0 || $bk["addonSubtotal"] > 0)): ?>
                    <tr>
                        <td><?= htmlspecialchars($bk["addonTitle"]) ?></td>
                        <td>$<?= number_format($bk["addonPrice"], 2) ?></td>
                        <td><?= htmlspecialchars($bk["addonQty"]) ?></td>
                        <td>$<?= number_format($bk["addonSubtotal"], 2) ?></td>
                    </tr>
                <?php endif; ?>

                <?php if (!empty($bk["adjustments"])): ?>
                    <?php foreach ($bk["adjustments"] as $a): ?>
                        <?php
                        $description = (string)($a["description"] ?? '');
                        $isAdditionalGuest = stripos($description, 'additional guest') !== false;
                        if (($bk["additionalGuestCount"] > 0 || $bk["additionalGuestSubtotal"] > 0) && $isAdditionalGuest) {
                            continue;
                        }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($description) ?></td>
                            <td>$<?= number_format((float)($a["unitPrice"]["amount"] ?? 0), 2) ?></td>
                            <td><?= htmlspecialchars($a["quantity"] ?? 0) ?></td>
                            <td>$<?= number_format((float)($a["totalPrice"]["amount"] ?? 0), 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <tr>
                    <td><strong>Subtotal</strong></td>
                    <td></td>
                    <td></td>
                    <td><strong>$<?= number_format($bk["subtotal"], 2) ?></strong></td>
                </tr>

                <?php if (!empty($bk["taxes"])): ?>
                    <?php foreach ($bk["taxes"] as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)($t["label"] ?? 'Tax')) ?></td>
                            <td></td>
                            <td></td>
                            <td>$<?= number_format((float)($t["amount"] ?? 0), 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($bk["promo_discount"] > 0): ?>
                    <tr>
                        <td><?= htmlspecialchars($bk["promo_label"]) ?></td>
                        <td></td>
                        <td></td>
                        <td>- $<?= number_format($bk["promo_discount"], 2) ?></td>
                    </tr>
                <?php endif; ?>

                <tr>
                    <td></td>
                    <td></td>
                    <td class="right bold">Total</td>
                    <td class="bold">$<?= number_format($bk["totalGross"], 2) ?></td>
                </tr>

                <?php if ($bk["voucher_amount"] > 0): ?>
                    <tr>
                        <td><?= $bk["voucher_label"] ?></td>
                        <td></td>
                        <td></td>
                        <td>- $<?= number_format($bk["voucher_amount"], 2) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ($bk["balance_due"] > 0): ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td class="right bold">Balance Due</td>
                        <td class="bold">$<?= number_format($bk["balance_due"], 2) ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endforeach; ?>

    <h3 class="section-title">Grand Total</h3>
    <table>
        <tbody>
            <tr>
                <td class="right bold">Grand Total</td>
                <td class="bold">$<?= number_format($grandTotal, 2) ?></td>
            </tr>
            <tr>
                <td class="right bold">Total Taxes</td>
                <td class="bold">$<?= number_format($grandTaxes, 2) ?></td>
            </tr>
            <?php if ($grandVoucherAmount > 0): ?>
                <tr>
                    <td class="right bold">Total Voucher Applied</td>
                    <td class="bold">- $<?= number_format($grandVoucherAmount, 2) ?></td>
                </tr>
                <tr>
                    <td class="right bold">Balance Due</td>
                    <td class="bold">$<?= number_format($grandBalanceDue, 2) ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h3 class="section-title">Cancellation policy</h3>
    <div class="small-text">
       <ol>
           <li>No refund will be provided for cancellations made less than 3 days in advance, or in case of no-show</li>
           <li>A cancellation fee of 50% applies for cancellations made less than 7 days in advance.</li>
           <li>A cancellation fee of 10% applies for cancellations made 7 or more days in advance.</li>
       </ol>
    </div>

    <br>

    <h3 class="section-title">Terms & Conditions</h3>
    <div class="small-text">
        <p>Tickets are sold subject to the following conditions:</p>
        <ol>
            <li>You have expressly agreed to participate, join, enter, use, play and/or access to FLEE Escape Games at your sole risk. FLEE does not warrant the reliability, accuracy, completeness, current or error-free of the product, content and materials included.</li>
            <li>FLEE shall not be responsible for any risk, hazard, danger, security, threat, safety and/or protection from FLEE Escape Game.</li>
            <li>You shall not be allowed to record, capture or snap any photograph, video, film, tape, audio recording whatsoever before, during and after the FLEE Escape Game.</li>
            <li>FLEE Escape Game reserve the right to update or modify terms and conditions at any time without prior notice.</li>
            <li>5% Admission tax is a mandatory requirement from the city for all the escape game venues. FLEE Escape Game only collects the tax on behalf of the city that our escape rooms located.</li>
            <li>FLEE reserves the right to refuse the service if participants are under the influence of alcohol or drugs.</li>
        </ol>
    </div>

    <br>

    <div class="small-text" style="text-align:center;">
        Please always quote your booking number when contacting us about this booking
    </div>
</div>

</body>
</html>
