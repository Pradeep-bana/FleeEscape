<?php session_start();

$pageTitle = 'Booking Confirmation';
include('./includes/header.php');

if (empty($_SESSION['booking_summary'])) {
    // echo '<meta http-equiv="refresh" content="0;URL=https://indiawebsoft.co.in/fleeescape-new">';
    echo '<meta http-equiv="refresh" content="0;URL=' . BASE_URL . '">';
    exit;
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

    <!-- ✅ Dynamic Product Name -->
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
        <div class="payment_session_tag">🔒 PUBLIC SESSION</div>
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

        $totalGross = isset($price['totalGross']['amount']) ? (float)$price['totalGross']['amount'] : 0;
        $totalTaxes = isset($price['totalTaxes']['amount']) ? (float)$price['totalTaxes']['amount'] : 0;
        $grandTotal += $totalGross;
        $grandTaxes += $totalTaxes;

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

        // Additional Guests (priceAdjustments)
        if (!empty($priceAdjustments)) {
            foreach ($priceAdjustments as $adj) {
                $qty = $adj['quantity'] ?? 0;
                $desc = $adj['description'] ?? '';
                $unitPrice = isset($adj['unitPrice']['amount']) ? (float)$adj['unitPrice']['amount'] : 0;
                $totalPrice = isset($adj['totalPrice']['amount']) ? (float)$adj['totalPrice']['amount'] : 0;

                $html .= '<p>' . htmlspecialchars($desc) . ': ' . $qty . ' x $' . number_format($unitPrice,2) . ' = <span>$' . number_format($totalPrice,2) . '</span></p>';
            }
        }

        // Taxes per booking|
        $taxCount = !empty($taxes) ? count($taxes) : 0;

if ($taxCount > 0) {
    foreach ($taxes as $i => $tax) {
        $taxAmount = isset($tax['amount']['amount']) ? (float)$tax['amount']['amount'] : 0;

        // Dynamic Tax Name
        if ($taxCount == 1) {
            $taxName = "Redmond Sales Tax";
        } else {
            $taxName = ($i == 0) ? "Admission Tax" : "Redmond Sales Tax";
        }

        $html .= '<p>' . htmlspecialchars($taxName) . ': <span>$' . number_format($taxAmount,2) . '</span></p>';
    }
}

        // if (!empty($taxes)) {
        //     foreach ($taxes as $tax) {
        //         $taxAmount = isset($tax['amount']['amount']) ? (float)$tax['amount']['amount'] : 0;
        //         $html .= '<p>Admission Tax: <span>$' . number_format($taxAmount,2) . '</span></p>';
        //     }
        // }

        $html .= '<p class="total">Booking Total: <span>$' . number_format($totalGross,2) . '</span></p>';
        $html .= '</div></div>';
    }

    // Grand total
    $html .= '
    <div class="booking_com_customer_info">
        <h3 style="color:#00d4ff">Grand Total</h3>
        <p>Total Amount: <span>$' . number_format($grandTotal,2) . '</span></p>
        <p>Total Taxes: <span>$' . number_format($grandTaxes,2) . '</span></p>
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
}
?>
    
    
    
    </div>
                </div>
            </div>
            <div class="Confirmation_so_butn">
             <div class="email-message">
                    <p>An email has been sent to <?php echo htmlspecialchars($row["email"]); ?> with all the booking details.</p>
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
