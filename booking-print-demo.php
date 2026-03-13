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

/* HEADER SECTION */
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

/* BOOKING SECTION */
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

/* TABLE STYLING */
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
        window.print();   // print popup open hoga
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
if (empty($_POST['booking_summary'])) {
    echo 'booking summary not passed in post';
    exit();
}

$customerData = null;
$allBookings = [];
$grandTotal = 0;
$grandTaxes = 0;
$grandPaid  = 0;

// ------------------------------------------------------------
// FETCH ALL BOOKINGS USING SESSION BOOKING NUMBERS
// ------------------------------------------------------------
$bookingSummary = $_POST['booking_summary'];
if (is_string($bookingSummary)) {
    $bookingSummary = json_decode($bookingSummary, true);
}
foreach ($bookingSummary as $b) {

    $bookingNumber = $b['bookingNumber'] ?? "";
    if (!$bookingNumber) continue;

    // Fetch booking with user info
    $q = $pdo->prepare("
        SELECT b.*, u.firstName, u.lastName, u.email, u.phone
        FROM tbl_bookings b
        LEFT JOIN tbl_users u ON b.user_id = u.id
        WHERE b.bookingNumber = :bn
    ");
    $q->execute([':bn' => $bookingNumber]);
    $row = $q->fetch(PDO::FETCH_ASSOC);

    if (!$row) continue;

    // Customer info only once
    if (!$customerData) {
        $customerData = [
            "name"  => $row["firstName"] . " " . $row["lastName"],
            "email" => $row["email"],
            "phone" => $row["phone"]
        ];
    }

    // Decode JSON fields
   $price     = json_decode($row['priceJson'], true) ?? [];
$adj       = json_decode($row['priceAdjustments'], true) ?? [];
$parts     = json_decode($row['participantsJson'], true) ?? [];



    $book = [];
// Use taxes from priceJson
$book["taxes"] = $price['taxes'] ?? [];

    $book["productName"]  = $row["productName"];
    $book["startTime"]    = $row["startTime"];
    $book["endTime"]      = $row["endTime"];
    $book["bookingNumber"]= $row["bookingNumber"];
    $book["participants"] = $parts['numbers'] ?? [];
    $book["adjustments"]  = $adj;
   
    $book["totalGross"] = $row["totalGross"];
    $book["totalPaid"]  = $row["totalPaid"];
    $book["totalTaxes"] = $row["totalTaxes"];
     $book["productId"] = $row["productId"];

    // Add to grand totals
    $grandTotal += floatval($row["totalGross"]);
    $grandTaxes += floatval($row["totalTaxes"]);
    $grandPaid  += floatval($row["totalPaid"]);

    $allBookings[] = $book;
}
?>

<!-- ======================= -->
<!-- INVOICE HTML STRUCTURE  -->
<!-- ======================= -->

<div class="page">

    <!-- HEADER -->
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

    <!-- CUSTOMER INFO -->
    <h3 class="section-title">Customer</h3>
    <div class="small-text">
        <strong><?= htmlspecialchars($customerData["name"]) ?></strong><br>
        Email: <?= htmlspecialchars($customerData["email"]) ?><br>
        Phone: <?= htmlspecialchars($customerData["phone"]) ?>
    </div>

    <div class="divider"></div>


    <!-- ==================================== -->
    <!--  BOOKING DETAILS (MULTIPLE BOOKINGS) -->
    <!-- ==================================== -->

    <h3 class="section-title">Booking details</h3>

    <?php foreach ($allBookings as $bk): ?>

        <div class="booking-row">

            <!-- LEFT -->
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
                            Participants (<?= htmlspecialchars($p["peopleCategoryId"]) ?>):
                            <?= htmlspecialchars($p["number"]) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="small-text">Booking Number: <?= htmlspecialchars($bk["bookingNumber"]) ?></div>
                <br>

                <div class="small-text">Total price: <strong>$<?= number_format($bk["totalGross"], 2) ?></strong></div>
                <div class="small-text">Amount paid: <strong>$<?= number_format($bk["totalGross"], 2) ?></strong></div>
                <div class="small-text">Amount due: <strong>$0</strong></div>
            </div>
<?php
$productImage = "";
$currentProductId = $bk["productId"]; // ← USE BK, NOT ROW

$q2 = $pdo->query("SELECT product_data FROM bookeo_products_cache");
$allRows = $q2->fetchAll(PDO::FETCH_ASSOC);

foreach ($allRows as $r) {

    $json = json_decode($r['product_data'], true);

    if (!empty($json['data'])) {

        foreach ($json['data'] as $prod) {

            if (!empty($prod['productId']) && $prod['productId'] === $currentProductId) {

                if (!empty($prod['images'][0]['url'])) {
                    $productImage = $prod['images'][0]['url'];
                }

                break 2;
            }
        }
    }
}
?>
<div class="booking-right">
    <img src="<?= $productImage ?>" />
</div>

        </div>


        <!-- PRICE TABLE FOR EACH BOOKING -->
       
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

                <!-- MAIN Participant Rows -->
                <?php if (!empty($bk["participants"])): ?>
                    <?php foreach ($bk["participants"] as $p): ?>
                        <tr>
                            <td>
                                <strong><?= date("l, F j, Y g:i A", strtotime($bk["startTime"])) ?></strong><br>
                                <?= htmlspecialchars($bk["productName"]) ?> - participants
                            </td>
                           <?php
// Sample JSON (you will already have these as PHP arrays)
$priceJson = [
    "totalNet" => ["amount" => "90", "currency" => "USD"]
];

$participantsJson = [
    "numbers" => [
        ["peopleCategoryId" => "Cadults", "number" => 2]
    ]
];

// 1️⃣ Total Net Price
$totalNet = floatval($priceJson["totalNet"]["amount"]);

// 2️⃣ Total Participants
$totalParticipants = 0;
foreach ($participantsJson["numbers"] as $p) {
    $totalParticipants += intval($p["number"]);
}

// 3️⃣ Unit Price Calculation
$unitPrice = ($totalParticipants > 0) ? ($totalNet / $totalParticipants) : 0;
?>


    <td>$<?= number_format($unitPrice, 2) ?></td>
    <td><?= htmlspecialchars($totalParticipants) ?></td>
    <td>$<?= number_format($unitPrice * $totalParticipants, 2) ?></td>


                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- ADDONS -->
                <?php if (!empty($bk["adjustments"])): ?>
                    <?php foreach ($bk["adjustments"] as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a["description"]) ?></td>
                            <td>$<?= number_format($a["unitPrice"]["amount"], 2) ?></td>
                            <td><?= htmlspecialchars($a["quantity"]) ?></td>
                            <td>$<?= number_format($a["totalPrice"]["amount"], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- SUBTOTAL -->
                <tr>
                    <td><strong>Subtotal</strong></td>
                    <td></td><td></td>
                    <td><strong>$<?= number_format($bk["totalGross"] - $bk["totalTaxes"], 2) ?></strong></td>
                </tr>

                <!-- TAXES -->
             <?php
$taxCount = !empty($bk["taxes"]) ? count($bk["taxes"]) : 0;
?>

<?php if ($taxCount > 0): ?>
    <?php foreach ($bk["taxes"] as $i => $t): ?>

        <?php
        // Apply custom names based on conditions
        if ($taxCount == 1) {
            // Only one tax → Redmond Sales Tax
            $taxName = "Redmond Sales Tax";
        } else {
            // Two taxes → first Admission, second Redmond
            $taxName = ($i == 0) ? "Admission Tax" : "Redmond Sales Tax";
        }
        ?>

        <tr>
            <td><?= htmlspecialchars($taxName) ?></td>
            <td></td><td></td>
            <td>$<?= number_format($t["amount"]["amount"], 2) ?></td>
        </tr>

    <?php endforeach; ?>
<?php endif; ?>


                <!-- TOTAL -->
                <tr>
                    <td></td><td></td>
                    <td class="right bold">Total</td>
                    <td class="bold">$<?= number_format($bk["totalGross"], 2) ?></td>
                </tr>

            </tbody>
        </table>

    <?php endforeach; ?>
  <!-- PAYMENTS -->
    <!--<h3 class="section-title">Payments</h3>-->
    <!--<table>-->
    <!--    <thead>-->
    <!--        <tr>-->
    <!--            <th>When</th>-->
    <!--            <th>Reason</th>-->
    <!--            <th>Payment method</th>-->
    <!--            <th>Amount</th>-->
    <!--        </tr>-->
    <!--    </thead>-->
    <!--    <tbody>-->
    <!--        <tr>-->
    <!--            <td>12/3/2025 9:11 PM</td>-->
    <!--            <td>Credit from gift voucher 33NKMNN</td>-->
    <!--            <td>Generic gift voucher</td>-->
    <!--            <td>$103.77</td>-->
    <!--        </tr>-->
    <!--    </tbody>-->
    <!--</table>-->


    <!-- GRAND TOTAL -->
    <h3 class="section-title">Grand Total</h3>
    <table>
        <tbody>
            <tr>
                <td class="right bold">Grand Total</td>
                <td class="bold">$<?= number_format($grandTotal, 2) ?></td>
            </tr>
            <!--<tr>-->
            <!--    <td class="right bold">Promotion / Voucher	</td>-->
            <!--    <td class="bold">Applied</td>-->
            <!--</tr>-->
            
        </tbody>
    </table>


    <!-- ============================ -->
    <!-- STATIC SECTIONS – AS IS      -->
    <!-- ============================ -->

    <!-- CANCELLATION POLICY -->
    <h3 class="section-title">Cancellation policy</h3>
    <div class="small-text">
       <ol>
           <li>No refund will be provided for cancellations made less than 3 days in advance, or in case of no-show</li>
           <li>A cancellation fee of 50% applies for cancellations made less than 7 days in advance.</li>
            <li>A cancellation fee of 10% applies for cancellations made 7 or more days in advance.</li>
       </ol>
    </div>

    <br>

    <!-- TERMS -->
    <h3 class="section-title">Terms & Conditions</h3>
    <div class="small-text">
        <p>Tickets are sold subject to the following conditions:</p>
        <ol>
            <li> You have expressly agreed to participate, join, enter, use, play and/or access to FLEE Escape Games at
your sole risk. FLEE does not warrant the reliability, accuracy, completeness, current or error-free of the
product, content and materials included.
</li>
            <li>FLEE shall not be responsible for any risk, hazard, danger, security, thread, safety and/or protection from
FLEE Escape Game.</li>
            <li> You shall not be allowed to record, capture or snap any photograph, video, film, tape, audio recording
whatsoever before, during and after the FLEE Escape Game.</li>
            <li> FLEE Escape Game reserve the right to update or modify terms and conditions at any time without prior
notice.
</li>
            <li>5% Admission tax is a mandatory requirement from the city for all the escape game venues. FLEE
Escape Game only collects the tax on behalf of the city that our escape rooms located.
</li>
            <li> FLEE reserves the right to refuse the service if participants are under the influence of alcohol or drugs</li>
        </ol>
    </div>

    <br>



  <div class="small-text" style="text-align:center;">
      Please always quote your booking number when contacting us about this booking
    </div>

</div>


</body>
</html>
