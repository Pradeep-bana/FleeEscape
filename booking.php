<?php session_start();
include('link.php');

include('admin/db.php'); 
// include('config.php');

// Get current session ID
$sid = session_id();

// Fetch total cart items for this session
$stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM tbl_carts WHERE session_id = :sid");
$stmt->execute([':sid' => $sid]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$cartCount = (int)$row['count'];

// Determine redirect URL
//if ($cartCount === 0) {
   
//echo '<meta http-equiv="refresh" content="0;URL=https://indiawebsoft.co.in/fleeescape-new/">';
   // exit;
//} 

$pageTitle = 'Book Your Escape Room and Virtual Reality Game Adventure Now in Redmond, Bellevue, and Greater Seattle';
$metaKeywords = 'Booking';
$metaDescription = 'Book immersive escape rooms and Zero Latency VR experiences at Flee Escape Rooms in Redmond. Perfect for team building, kids and teens birthday parties, fun date nights, competitions, and celebrations. Serving Bellevue, Kirkland, Issaquah, Sammamish, and all of Greater Seattle.';
$canonicalURL = $link."booking";
include('includes/header.php');

$userData = null;
if (!empty($_SESSION['user_id'])) {
    include "admin/db.php";
    $stmt = $pdo->prepare("SELECT firstName, lastName, email, phone FROM tbl_users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<script src="https://sandbox.web.squarecdn.com/v1/square.js"></script>
<style>
button.custom-date_arrow {
    background: transparent;
    border: none;
    color: #fff;

}

.sq-card-wrapper {

    min-width: 700px;

}
.respnsive_booknow_bnt {
    display: none!important;
}

/* Style the dropdown list options so they are readable when opened */
.flatpickr-year-dropdown option {
    background: #fff; /* White background */
    font-weight: normal;
}

/* Hide the original number input wrapper */
.numInputWrapper.hidden-year {
    display: none !important;
}

@media (max-width: 768px) {
    .flatpickr-current-month{
        padding: 0px;
    }
}

.flatpickr-months{
    /*margin-bottom: 10px;*/
}
</style>
<div class="booking_banner">
    <img src="./assets/images/fleeescape_img/banner//Booking_baneer_img.png" loading="lazy"  decoding="async"  alt="booking baneer" />
    <div class="booking_banner_content">
        <h1> <span style="color: #00d4ff;"> Book Your Ultimate </span> Adventure Now</h1>
        <p>Escape Rooms, VR Games, Party Packages, Gift cards and more...</p>
    </div>
</div>

<div class="page_heading_main_top" id="to_book_scroll">
    <h1>Book Now</h1>
</div>

<style>
/* ✅ Highlight selected slot */
.slot-selected {
    background: #e0f3ff;
    border: 2px solid #007bff;
    border-radius: 6px;
    padding: 4px 8px;
}
</style>

<div class="container">
    <div class="progress-container">
        <div class="progress-line">
            <div class="progress-line-fill" id="progressFill"></div>
        </div>
        <div class="progress-step" data-title="Choose Experience">
            <div class="step-circle">1</div>
        </div>
        <div class="progress-step" data-title="Add Ons ">
            <div class="step-circle">2</div>
        </div>
        <div class="progress-step" data-title="Customer Details">
            <div class="step-circle">3</div>
        </div>
        <div class="progress-step" id="payment_details" data-title="Payment Details">
            <div class="step-circle">4</div>
        </div>
        <div class="progress-step" data-title="Booking Confirmation">
            <div class="step-circle">5</div>
        </div>
    </div>
    <!-- Timer Section -->
    <div class="timer_wrapper" style="display: none;">
        <h3 class="timer_header">You have <span class="timer_display">3:00</span>
            Minutes to complete your booking</h3>
    </div>

    <!-- Step Pages -->
    <div id="stepContents">
        <div class="step-content active" id="mainStepContent">
            <div class="booking_date_multi_bnt">
                <div class="all_button_main_header text-end" style="position: relative!important;">
                    <!-- Hidden input for Flatpickr -->
                    <label style="position: relative!important;" for="custom-datepicke2" class=" bg_bnt_custom " ><i class="fa-solid fa-calendar-days"></i> <span>Pick Date</span> 
                        <input 
                          type="text" 
                          id="custom-datepicke2" 
                          value="Pick Date" 
                          class="custom-datepicker_input bg_bnt_custom d-none" 
                          data-product="custom-datepicker_input" 
                           style="
                               display: block !important;
                                visibility: hidden;
                                opacity: 0;
                                width: 0;
                                height: 0;
                                margin: 0;
                                padding: 0!importan; " > 
                    </label>
                </div>

                <div class="all_button_main_header text-end"
                    style="background-size: cover; background-repeat: no-repeat;">
                    <button id="prev-all-btn" style="border-radius: 30px" class="bg_bnt_custom disabled-btn">
                        <i class="fa-solid fa-chevron-left"></i> <span>Previous Day</span>
                    </button>

                </div>
                <div class=" all_button_main_header text-end"
                    style="background-size: cover; background-repeat: no-repeat; ">
                    <button style="border-radius: 30px" class=" bg_bnt_custom " id="next-all-btn">
                        <span>NEXT Day</span> <i class="fa-solid fa-angle-right"></i></button>
                </div>
                <style>
                #prev-all-btn.disabled {
                    opacity: 0.6;
                    cursor: not-allowed;
                }
                </style>
            </div>
            <div class=" booking_tab_wrapper">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="booking-sidebar p-3 rounded">
                            <div class="nav flex-column custom-tab-nav  d-none d-md-flex" id="v-pills-tab"
                                role="tablist">
                                <div class="tab-item active" data-hash="escape-room" data-bs-toggle="pill"
                                    data-bs-target="#tab-escape" role="tab">
                                    ESCAPE ROOMS
                                </div>
                                <div class="tab-item" data-hash="vr-game" data-bs-toggle="pill" data-bs-target="#tab-vr"
                                    role="tab">
                                    VR GAMES
                                </div>
                                <div class="tab-item" data-hash="party-package" data-bs-toggle="pill"
                                    data-bs-target="#tab-party" role="tab">
                                    PARTY PACKAGES
                                </div>
                                <div class="tab-item" data-hash="Facility-Rentals" data-bs-toggle="pill"
                                    data-bs-target="#tab-Facility" role="tab">
                                    FACILITY RENTALS
                                </div>
                                <div class="tab-item" data-hash="gift-card" data-bs-toggle="pill"
                                    data-bs-target="#tab-gift" role="tab">
                                    GIFT CARDS
                                </div>
                                <div class="tab-item" data-hash="event-room" data-bs-toggle="pill"
                                    data-bs-target="#tab-event" role="tab">
                                    EVENT ROOMS
                                </div>

                            </div>
                            <div class="d-md-none gaming_dropdwon_select mb-3">
                                <select id="mobile-tab-dropdown">
                                    <option value="#tab-escape" selected>ESCAPE ROOMS</option>
                                    <option value="#tab-vr">VR GAMES</option>
                                    <option value="#tab-party">PARTY PACKAGES</option>
                                    <option value="#tab-Facility">FACILITY RENTALS</option>
                                    <option value="#tab-gift">GIFT CARDS</option>
                                    <option value="#tab-event">EVENT ROOMS</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-9">
                        <div class="tab-content booking_tab_content">
                            <!-- ESCAPE ROOMS TAB -->
                            <div class="tab-pane fade show active" id="tab-escape">
                                <div class="row">



                                </div>
                            </div>

                            <!-- VR GAMES TAB -->
                            <div class="tab-pane fade" id="tab-vr">
                                <h1 class="text-center mb-4">VR GAMES</h1>

                            </div>

                            <!-- PARTY PACKAGES TAB -->
                            <div class="tab-pane fade" id="tab-party">

                            </div>

                            <!-- EVENT ROOMS TAB -->
                            <div class="tab-pane fade" id="tab-Facility">
                                <h1 class="text-center mb-4"> Facility Rentals</h1>
                            </div>

                            <!-- GIFT CARDS TAB -->
                            <div class="tab-pane fade" id="tab-gift">

                            </div>

                            <!-- EVENT ROOMS TAB -->
                            <div class="tab-pane fade" id="tab-event">
                                <h1 class="text-center mb-4">Event Rooms</h1>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add-on Section -->
     <div class="step-content" id="mainStepContent add_on_data">
           <div class="add_on_section">
               <?php include("load_addons.php"); ?>
               
           </div>
        </div>

        <!-- customer details tabs -->
        <div class="step-content" id="mainStepContent">
            <div class="add_on_section">
                <h2 class="add_on_heading" data-aos="fade-right">Customer Details</h2>
                <div class="customer-details">
                    <div class="form-group">
                        <label for="firstName">First name</label>
                        <input type="text" id="firstName" placeholder="Enter first name" />
                    </div>

                    <div class="form-group">
                        <label for="lastName">Last name</label>
                        <input type="text" id="lastName" placeholder="Enter last name" />
                    </div>

                    <h3 class="sub-heading">Contact details</h3>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" placeholder="Enter email" />
                    </div>

                    <div class="form-group">
                        <label for="confirmEmail">Email (confirm)</label>
                        <input type="email" id="confirmEmail" placeholder="Re-enter email" />
                    </div>

                    <div class="form-group phone-group">
                       <div>
                            <label for="phone">Phone</label>
                            <input 
                                type="tel" 
                                id="phone" 
                                placeholder="Enter phone number"
                                maxlength="10"
                                oninput="this.value = this.value.replace(/[^0-9]/g,'')"
                            />
                        </div>

                        <div>
                            <label for="type" class="invisible-label">Type</label>
                            <select id="type">
                                <option value="mobile">mobile</option>
                                <option value="home">home</option>
                                <option value="work">work</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="step-content" id="mainStepContent payment_details">
            <div class="add_on_section">
                <h2 class="add_on_heading" data-aos="fade-right">Payment Details</h2>
                <div class="worning_payment_details">
                    <h5>Your bookings are not confirmed yet.</h5>
                    <p>Please review all the booking details above before proceeding.</p>
                </div>
                <div class="payment_right_tab">
                    <div class="Customer_">
                        <h3 class=" " data-aos="fade-right" style="font-size:20px;">
                            Customer
                            Details</h3>
                        <div class="customar_details_wrapper">
                            <div class="customar_details_info">
                                <!-- Inside Step 4: Payment Details -->
                                <div class="customer_details_tab">
                                    <p>
                                        <span class="label">Name:</span> 
                                        <span class="value" id="display_name">
                                            <?= $userData ? htmlspecialchars($userData['firstName'] . ' ' . $userData['lastName']) : '' ?>
                                        </span>
                                    </p>
                                    <p>
                                        <span class="label">Email:</span> 
                                        <span class="value" id="display_email">
                                            <?= $userData ? htmlspecialchars($userData['email']) : '' ?>
                                        </span>
                                    </p>
                                    <p>
                                        <span class="label">Phone:</span> 
                                        <span class="value" id="display_phone">
                                            <?= $userData ? htmlspecialchars($userData['phone']) : '' ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="Customer_details_edit">
                                    <button onclick="changeStep(-1)" class="bg_bnt_custom"><i
                                            class="fa-solid fa-pencil"></i> Change</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="payment_section_tab">
                        <h2>PAYMENT</h2>
                        <div class="payment_cardinputs_tab">
                            <form id="payment-form">
                                <div id="card-container"></div>
                                <!-- Removed Pay Now button -->
                            </form>
                            <!--<div id="payment-status-container" style="margin-top:20px;"></div>-->
                        </div>
                        <div class="payment_btnrow_tab">
                            <button>CREDIT / DEBIT CARD</button>
                            <!--<button><strong>G</strong> Pay</button>-->
                             <button class="payment_voucher_tab" 
                                    onclick="window.open('booking#gift-card', '_blank');">
                                GIFT VOUCHER
                            </button>

                        </div>
                       
                        <!-- 💡 Cancellation Policy + Terms & Conditions Section -->
                        <div class="policy-section">
                            <div class="policy-box">
                                <h3 class="section-title">Cancellation policy</h3>
                                <ul>
                                    <li>No refund will be provided for cancellations made less than 3 days in
                                        advance,
                                        or in case of no-show.</li>
                                    <li>A cancellation fee of 50% applies for cancellations made less than 7 days in
                                        advance.</li>
                                    <li>A cancellation fee of 10% applies for cancellations made 7 or more days in
                                        advance.</li>
                                </ul>
                            </div>

                            <div class="policy-box">
                                <h3 class="section-title">Terms and conditions</h3>
                                <div class="policy-box">
                                    <p><strong>Tickets are sold subject to the following conditions:</strong></p>
                                    <ol>
                                        <li>You have expressly agreed to participate, join, enter, use, play and/or
                                            access to FLEE Escape Games at your sole risk. FLEE does not warrant the
                                            reliability, accuracy, completeness, current or error-free of the
                                            product,
                                            content and materials included.</li>
                                        <li>FLEE shall not be responsible for any risk, hazard, danger, security,
                                            threat, safety and/or protection from FLEE Escape Game.</li>
                                        <li>You shall not be allowed to record, capture or snap any photograph,
                                            video,
                                            film, tape, audio recording whatsoever before, during and after the FLEE
                                            Escape Game.</li>
                                        <li>FLEE Escape Game is a live event so once the booking is confirmed;
                                            refunds,
                                            cancellations are not accepted within 4 days prior to the game.</li>
                                        <li>FLEE Escape Game reserves the right to update or modify terms and
                                            conditions
                                            at any time without prior notice.</li>
                                        <li>5% Admission tax is a mandatory requirement from the city for all the
                                            escape
                                            game venues. FLEE Escape Game only collects the tax on behalf of the
                                            city
                                            that our escape rooms are located.</li>
                                        <li>FLEE reserves the right to refuse the service if participants are under
                                            the
                                            influence of alcohol or drugs.</li>
                                    </ol>
                                </div>
                            </div>

                            <style>
                            .policy-section {
                                color: #fff;
                                margin-top: 20px;
                            }

                            .policy-box {
                                margin-bottom: 24px;
                            }


                            .agree-box {
                                margin: 16px 0;
                                font-size: 16px;
                            }

                            .agree-box input[type="checkbox"] {
                                margin-right: 8px;
                                transform: scale(1.2);
                            }
                            </style>
                            <div class="agree-box">
                                <label>
                                    <input type="checkbox" id="agreeTerms" />
                                    <span>I agree to the conditions and policies above</span>
                                </label>
                            </div>

                            <!-- <button class="bg_bnt_custom">
                                Pay now and confirm your booking!
                            </button> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Confirmation -->
        <div class="step-content" id="mainStepContent">
            <div class="row " id="printableArea">
                <div class="col-xl-7">
                    <div class="payment_left_tab"
                        style="background-image: url(./assets/images/fleeescape_img/party_pack/Most_Value.jpg);">
                        <div class="payment_images_done">
                            <div class="booking_done_img_main">
                                <div class="booking_done_img_main_img">
                                    <img src="./assets/images/fleeescape_img/CHOOSE/4.jpg" loading="lazy"  decoding="async"  alt="Payment BG img" />
                                </div>
                                <div class="booking_done_img_main_gallery">
                                    <img src="./assets/images/fleeescape_img/party_pack/Corporate.jpg" alt="" loading="lazy"  decoding="async" >
                                    <img src="./assets/images/fleeescape_img/party_pack/Kids.png" alt="" loading="lazy"  decoding="async" >
                                    <img src="./assets/images/fleeescape_img/party_pack/Portable.jpg" alt="" loading="lazy"  decoding="async" >
                                </div>
                            </div>
                            <h1>Deadwood PHOBIA</h1>
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
<div id="payment_confirmation">
                    <?php

if (!empty($_SESSION['booking_summary'])) {
    include("get_booking_summary.php");
} else {
    echo "<p>No booking found yet.</p>";
}
?>
</div>
                </div>
            </div>
        <div class="Confirmation_so_butn">
             <div class="email-message">
                    <p>An email has been sent to gmail@gmail.com with all the booking details.</p>
                    <p>If you do not find it in your inbox shortly, please check the spam/junk folder.</p>
                </div>
            <div class="Confirmation_so_butn_flex">    
                <div class="Confirmation_so_butn_flex_box">
                     <h2 class="share-heading">Quick Actions</h2>
                     <p class="share-description">
                        Share your bookings with your friends and you both get $10 off on your next booking!
                    </p>
                    <div class="button-group">
                        <a class="bg_bnt_custom bg_bnt_custom_tran">Print</a>
                        <a href="index" class="bg_bnt_custom bg_bnt_custom_tran" >Go to the main site</a>
                    </div>
                </div>
                <div class="share-section Confirmation_so_butn_flex_box">
                    <h2 class="share-heading">Share with your friends</h2>
                    <p class="share-description">
                        Share your bookings with your friends and you both get $10 off on your next booking!
                    </p>
                    <div class="button-group">
                        <a href="https://www.facebook.com/" target="b" class="bg_bnt_custom bg_bnt_custom_tran" >Post on Facebook</a>
                        <a href="https://x.com/" target-"b" class="bg_bnt_custom bg_bnt_custom_tran" >Post on X</a>
                    </div>
                </div>
            </div>
        </div>         
<style>

</style>
    <script>
        function printBooking() {
            // Sirf row class wala content print hoga
            window.print();
        }
    </script>
        </div>
        <div class="booking_right_sitebar" id="sidebar">
            <!-- Booking Summary -->
            <div class="booking-summary-box" data-aos="fade-left">
                <h3>BOOKING SUMMARY</h3>
                <!-- <h3 class="summary-heading">Price</h3> -->
                <div class="summary-table">
                    <!-- Header Row -->
                    <div class="summary-row summary-header">
                        <div>Description</div>
                        <div>Unit price</div>
                        <div>Qty</div>
                        <div>Price</div>
                    </div>

                    <!-- Dynamic summary rows will be injected here -->
                    <div id="summary-output"></div>

                    <!--  <div class="payment_code_section">-->
                    <!--    <div class="applied_code_box">-->
                    <!--        <div class="applied_code_box_header">-->
                    <!--            <h3 class="label">Promotion/voucher</h3>-->
                    <!--        </div>-->
                    <!--        <div class="applied_code">-->
                    <!--            <span class="code">FIRSTRESPONDERS2025</span>-->
                    <!--            <a data-bs-toggle="collapse" href="#giftCodeCollapse" class="change">Change</a>-->
                    <!--        </div>-->
                    <!--    </div>-->

                    <!--    <div class="collapse" id="giftCodeCollapse">-->
                    <!--        <div class="payment_code_input">-->
                    <!--            <input type="text" id="giftCodeInput" placeholder="Enter gift or bonus code">-->
                    <!--            <button class="btn_apply">Apply</button>-->
                    <!--        </div>-->
                    <!--    </div>-->
                    <!--</div>-->

                    <!-- Voucher Promo Section -->
                    <div class="voucher_code_new_design vr-waiver-dark">
                        <!--<h3 class="voucher-label">Promotion/voucher</h3>-->
                        <div class="voucher_code__flex">
                            <div class="voucher_code__flex_code">
                                <!--<div class="applied_code">-->
                                <!--    <span class="code">FIRSTRESPONDERS2025</span>-->
                                <!--</div>-->
                                <!--<div class="applied_code_remove">-->
                                <!--    <button><i class="fa-solid fa-delete-left"></i></button>-->
                                <!--</div>-->
                                
                                <!--<p>Have a promotion or voucher code?</p>-->
                                <?php
                                $savedCode = !empty($_SESSION['giftCode']) ? $_SESSION['giftCode'] : '';
                                ?>
                                <p class="applied_code">
                                  <span class="code"><?= $savedCode ? 'Promotion: ' . htmlspecialchars($savedCode) : 'Have a promotion or voucher code?' ?></span>
                                </p>
                                <div class="applied_code_remove">
                                    <button><i class="fa-solid fa-delete-left"></i></button>
                                </div>
                            </div>
                            <div class="voucher-btn-wrap ">
                                <a class="bg_bnt_custom bg_bnt_custom_tran text-end" data-bs-toggle="modal"
                                    data-bs-target="#input_voucher_code_new">
                                    <!--<i class="fa-solid fa-ticket-simple"></i> -->
                                    <span>Enter code</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    


                    
               <div id="booking-response" ></div>     
                    
                    
                    <style>
                    .step-1 .step-2-bnt_continue,
                    .step-1 .step-3-bnt_continue,
                    .step-1 .step-4-bnt_continue {
                        display: none;
                    }

                    .step-2 .step-1-bnt_continue,
                    .step-2 .step-3-bnt_continue,
                    .step-2 .step-4-bnt_continue {
                        display: none;
                    }

                    .step-3 .step-2-bnt_continue,
                    .step-3 .step-1-bnt_continue,
                    .step-3 .step-4-bnt_continue {
                        display: none;
                    }
                    </style>

                    <!-- Buttons -->
                    <div class="all_button_main_header order_summart_main_button mt-4">
                        <a href="javascript:void(0)" onclick="changeStep(-1)" class="bg_bnt_custom custom_scroll">Back</a>
                        <!-- all tab buttons here -->
                        <a onclick="changeStep(1)" class="bg_bnt_custom step-1-bnt_continue custom_scroll">Continue </a>
                        <a onclick="" class="bg_bnt_custom step-2-bnt_continue custom_scroll">Continue </a>
                        <a  class="bg_bnt_custom step-3-bnt_continue">Place Your Order
                        </a>

                        <!-- payment deatils button  -->

                    </div>
                    <p>The slot will be held for 10 minutes.</p> 
                </div>
            </div>
        </div>
    </div>

</div>

<!--===================Review Section======================-->

<section class="review-section" style="margin-top: 50px;">
    <h2 style="color: #00d4ff; text-align:center;">What Our Customers Say</h2>
    <p class="subtitle1" style="color: #ccc; border:none; text-align:center;">Don't just take our word for it - hear
        from families who've celebrated with us</p>
    <div class="d-flex flex-wrap justify-content-center gap-3">
        <div class="review-card">
            <div class="rating" style="color: #ffd700;">★★★★★</div>
            <p style="color: #fff;">"My son's 12th birthday party was a huge hit! The kids loved the VR games and the
                staff was amazing with them. Will definitely be back!"</p>
            <p class="author" style="color: #ccc;">- Sarah M., Parent</p>
        </div>
        <div class="review-card">
            <div class="rating" style="color: #ffd700;">★★★★★</div>
            <p style="color: #fff;">"We did the Ultimate Combo Party for my daughter's 16th birthday. The escape room
                was challenging and the VR experience blew everyone away. Worth every penny!"</p>
            <p class="author" style="color: #ccc;">- Michael E., Parent</p>
        </div>
        <div class="review-card">
            <div class="rating" style="color: #ffd700;">★★★★★</div>
            <p style="color: #fff;">"Had my 30th birthday here with friends and it was incredible! The Space Station
                escape room was challenging and so much fun. The party room was perfect for our celebration after."</p>
            <p class="author" style="color: #ccc;">- Jessica K., Customer</p>
        </div>
    </div>
</section>


<!--=========================================-->

<div class="container">
    <div class="faq-section my-5">
        <h2 class="text-center mb-4">Frequently Asked Questions</h2>
      <?php
include('admin/db.php');

// Fetch FAQs where category_id = 1 and status = 1 (active)
$stmt = $pdo->prepare("SELECT id, question, answer FROM tbl_faq WHERE category_id = 6 AND status = 1 ORDER BY id ASC");
$stmt->execute();
$faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="accordion" id="faqAccordion">
    <?php if (!empty($faqs)) : ?>
        <?php foreach ($faqs as $index => $faq) : 
            $collapseId = 'faqCollapse' . $index;
            $headingId = 'faqHeading' . $index;
        ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="<?php echo $headingId; ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="false"
                        aria-controls="<?php echo $collapseId; ?>">
                        <?php echo htmlspecialchars($faq['question']); ?>
                        <span class="faq-toggle-icon ms-auto">
                            <span class="plus">+</span>
                            <span class="minus" style="display:none;">âˆ’</span>
                        </span>
                    </button>
                </h2>
                <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse"
                    aria-labelledby="<?php echo $headingId; ?>" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>No FAQs available at the moment.</p>
    <?php endif; ?>
</div>
    </div>
</div>


<!--========================================================-->

<!--  Delete Confirmation Modal -->

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="custom-modal-overlay">
    <div class="custom-modal p-4 text-center" id="deleteModalBox">
        <h3 class="custom-modal-title" id="deleteModalTitle">Remove Game</h3>

        <!-- Confirmation text -->
        <p class="custom-modal-text" id="deleteConfirmText">
            Are you sure you want to remove this game <br /> from your cart?
        </p>

        <!-- Normal actions -->
        <div class="all_button_main_header" id="deleteActions">
            <button id="confirmDeleteBtn" class="bg_bnt_custom bg_bnt_custom_tran">Yes, Remove</button>
            <button id="cancelDeleteBtn" class="bg_bnt_custom">Cancel</button>
        </div>

        <!-- Loading state -->
        <div id="deleteLoading">
            <div class="spinner"></div>
            <p class="loading-text">Deleting Please wait</p>
        </div>
    </div>
</div>
<!-- Delete Addon Modal -->
<!-- Delete Addon Modal -->
<div id="deleteAddonModal" class="custom-modal-overlay">
    <div class="custom-modal p-4 text-center" id="deleteAddonBox">
        <h3 class="custom-modal-title">Remove Addon</h3>

        <p class="custom-modal-text">
            Are you sure you want to remove this addon <br /> from your cart?
        </p>

        <!-- Normal actions -->
        <div class="all_button_main_header" id="deleteAddonActions">
            <button id="confirmDeleteAddonBtn" class="bg_bnt_custom bg_bnt_custom_tran">Yes, Remove</button>
            <button id="cancelDeleteAddonBtn" class="bg_bnt_custom">Cancel</button>
        </div>

        <!-- Loading state -->
        <div id="deleteAddonLoading">
            <div class="spinner"></div>
            <p class="loading-text">Deleting Please wait</p>
        </div>
    </div>
</div>



 Modal for Removing Additional Guests 
<div id="deleteAdditionalGuestModal" class="custom-modal-overlay">
    <div class="custom-modal p-4 text-center" id="deleteAdditionalGuestBox">
        <h3 class="custom-modal-title">Remove Additional Guest</h3>

        <p class="custom-modal-text">
            Are you sure you want to remove this additional guest <br /> from your cart?
        </p>

        <div class="all_button_main_header" id="deleteAdditionalGuestActions">
            <button id="confirmDeleteAdditionalGuestBtn" class="bg_bnt_custom bg_bnt_custom_tran">Yes, Remove</button>
            <button id="cancelDeleteAdditionalGuestBtn" class="bg_bnt_custom">Cancel</button>
        </div>

        <div id="deleteAdditionalGuestLoading">
            <div class="spinner"></div>
            <p class="loading-text">Deleting Please wait</p>
        </div>
    </div>
</div>




<style>
.custom-modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

/* Modal Box */
.custom-modal-box {
    background: #000;
    padding: 20px;
    border-radius: 12px;
    max-width: 400px;
    width: 90%;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    animation: fadeIn 0.3s ease-in-out;
}

/* Actions */
.custom-modal-actions {
    margin-top: 20px;
    display: flex;
    justify-content: space-around;
}

/* Buttons */
.custom-btn {
    padding: 8px 16px;
    border-radius: 8px;
    cursor: pointer;
    border: none;
    font-size: 14px;
    transition: 0.2s;
}

.custom-btn-danger {
    background-color: #00d4ff;
    color: white;
}

.custom-btn-danger:hover {
    background-color: #00d4ff;
}

.custom-btn-secondary {
    background-color: #bdc3c7;
    color: black;
}

.custom-btn-secondary:hover {
    background-color: #95a5a6;
}

/* Spinner */
.spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #e74c3c;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

.loading-text {
    margin-top: 12px;
    font-size: 14px;
    color: #333;
    font-weight: 500;
}

#deleteLoading {
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    /* space between spinner & text */
    /* compact height */
}

/* Loading mode = remove padding + center content */
.custom-modal-box.loading-mode {
    padding: 20px 20px 60px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    max-height: 150px;
}

/* Fade in */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

.step-1 .payment_code_section {
    display: none;
}
/* Addon modal styling exactly like Game modal */



#deleteAddonModal .spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #00d4ff; /* Game modal ke jaise color */
    border-radius: 50%;
    width: 28px;
    height: 28px;
    animation: spin 1s linear infinite;
}


#deleteAdditionalGuestModal .spinner {
    border: 3px solid #f3f3f3;
    border-top: 3px solid #00d4ff; /* Game modal ke jaise color */
    border-radius: 50%;
    width: 28px;
    height: 28px;
    animation: spin 1s linear infinite;
}


#deleteAddonModal #deleteAddonLoading {
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

#deleteAdditionalGuestModal #deleteAdditionalGuestLoading {
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

/* Addon modal styling exactly like Game modal */
#deleteAddonModal .custom-modal {
    background: #000;
    padding: 20px;
    border-radius: 12px;
    max-width: 400px;
    width: 90%;
    text-align: center;
    box-shadow: 0 0 20px rgba(0, 212, 255, 0.6) !important;
    animation: fadeIn 0.3s ease-in-out;
}


#deleteAdditionalGuestModal .custom-modal {
    background: #000;
    padding: 20px;
    border-radius: 12px;
    max-width: 400px;
    width: 90%;
    text-align: center;
    box-shadow: 0 0 20px rgba(0, 212, 255, 0.6) !important;
    animation: fadeIn 0.3s ease-in-out;
}




</style>

<div class="modal fade" id="holdErrorModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content" style="border-radius:12px;">
      <div class="modal-body text-center" style="padding:25px;">
        <p id="holdErrorText" style="color:red; font-weight:600; margin:0;"></p>
      </div>
      <div class="modal-footer p-2 justify-content-center">
        <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
          OK
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="holdErrorModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-4 text-center">
      <h5 class="mb-3">Hold Failed</h5>
      <p>Sorry! We cannot hold this slot right now. Please try again later.</p>
      <button type="button" class="btn btn-primary mt-2" data-bs-dismiss="modal">OK</button>
    </div>
  </div>
</div>
<!-- ======== site scroll ======= -->

<script>
function adjustSidebarHeight() {
    const mainContent = document.getElementById('mainStepContent');
    const sidebar = document.getElementById('sidebar');

    const mainHeight = mainContent.scrollHeight;

    // Apply scroll only if content is longer than viewport
    if (mainHeight > window.innerHeight) {
        sidebar.style.maxHeight = mainHeight + "px";
        sidebar.style.overflowY = "auto";
        sidebar.style.position = "sticky"; // optional for top fix
        sidebar.style.top = "0"; // sticky from top
    } else {
        sidebar.style.maxHeight = "unset";
        sidebar.style.overflowY = "unset";
        sidebar.style.position = "relative";
    }
}

// Run on page load
window.addEventListener('load', adjustSidebarHeight);

// Run on window resize or step change
window.addEventListener('resize', adjustSidebarHeight);
</script>


<!-- ============  progress bar js and tabs  ============ -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ========== TAB HANDLING (Party Package) ==========
    let hash = window.location.hash;

    if (hash) {
        hash = hash.substring(1); // Remove #

        // Find the tab with matching data-hash
        const tabItem = document.querySelector(`.tab-item[data-hash="${hash}"]`);

        if (tabItem) {
            // Remove active from all tabs
            document.querySelectorAll('.tab-item').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });

            // Activate the target tab
            tabItem.classList.add('active');

            // Get target from data-bs-target
            const targetPane = tabItem.getAttribute('data-bs-target');
            if (targetPane) {
                document.querySelector(targetPane).classList.add('show', 'active');
            }

            // Update mobile dropdown
            const mobileDropdown = document.getElementById('mobile-tab-dropdown');
            if (mobileDropdown && targetPane) {
                mobileDropdown.value = targetPane;
            }
        }
    }

    // ========== TAB CLICK EVENT - Remove hash when switching tabs ==========
    document.querySelectorAll('.tab-item').forEach(tabItem => {
        tabItem.addEventListener('click', function() {
            // Remove hash from URL when tab is clicked
            const newUrl = window.location.pathname + window.location.search;
            window.history.replaceState(null, null, "#" + hash);
        });
    });

    // ========== PROGRESS STEPS HANDLING ==========
    const steps = document.querySelectorAll('.progress-step');
    const contents = document.querySelectorAll('.step-content');
    const progressFill = document.getElementById('progressFill');
    let currentStep = 0;
    let maxStepReached = 0;

    function makeSlug(str) {
        return str.toLowerCase().replace(/\s+/g, '-').replace(/[^\w-]/g, '');
    }

    const stepMap = {};
    steps.forEach((step, index) => {
        const title = step.getAttribute("data-title");
        const slug = makeSlug(title);
        stepMap[slug] = index;
    });

    const urlParams = new URLSearchParams(window.location.search);
    let foundSlug = null;
    for (const [key] of urlParams.entries()) {
        if (stepMap[key] !== undefined) {
            foundSlug = key;
            break;
        }
    }

    if (foundSlug) {
        currentStep = stepMap[foundSlug];
        maxStepReached = currentStep;
    }

    function updateSteps() {
        steps.forEach((step, index) => {
            const circle = step.querySelector('.step-circle');
            circle.classList.remove('active', 'visited');
            if (index === currentStep) {
                circle.classList.add('active');
            } else if (index < maxStepReached) {
                circle.classList.add('visited');
            }
            contents[index].classList.toggle('active', index === currentStep);
        });

        const fillWidth = (currentStep) / (steps.length - 1) * 100;
        progressFill.style.width = fillWidth + '%';

        const stepContentsWrapper = document.getElementById('stepContents');
        stepContentsWrapper.className = '';
        stepContentsWrapper.classList.add('step-' + currentStep);

        const stepTitle = steps[currentStep].getAttribute("data-title");
        const stepSlug = makeSlug(stepTitle);
        // const newUrl = window.location.pathname + "?" + stepSlug;
        const newUrl = window.location.pathname + "?" + stepSlug;
        window.history.pushState({
            step: stepSlug
        }, "", newUrl);
    }

     steps.forEach((step, index) => {
        step.addEventListener('click', () => {
            if (step.classList.contains('step-disabled')) return; // ← NEW
            if (index <= maxStepReached) {
                currentStep = index;
                updateSteps();
            }
        });
    });

    window.goToStep = function(targetIndex) {
        if (targetIndex >= 0 && targetIndex < steps.length) {
            currentStep = targetIndex;
            if (maxStepReached < currentStep) maxStepReached = currentStep;
            updateSteps();
        }
    }

    // Keep changeStep for backward compat (processStepContinue uses it)
    window.changeStep = function(direction) {
        const nextStep = currentStep + direction;
        if (nextStep >= 0 && nextStep < steps.length) {
            currentStep = nextStep;
            if (maxStepReached < currentStep) maxStepReached = currentStep;
            updateSteps();
        }
    }

    // If page loaded directly on add-ons step, check if addons exist
    if (foundSlug === 'add-ons-') {
        fetch('check_addons.php')
            .then(r => r.json())
            .then(data => {
                if (!data.has_addons) {
                    if (steps[1]) steps[1].classList.add('step-disabled');
                    const custIdx = stepMap['customer-details'];
                    if (custIdx !== undefined) { currentStep = custIdx; maxStepReached = custIdx; }
                }
                updateSteps();
            })
            .catch(() => updateSteps());
    } else {
        updateSteps();
    }
});
</script>
<style>
.progress-step.step-disabled { cursor: not-allowed; pointer-events: none; }
.progress-step.step-disabled .step-circle {
    opacity: 0.35 !important;
    background: #555 !important;
    color: #999 !important;
    box-shadow: none !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/luxon@3/build/global/luxon.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- date picker script -->
<script>
const {
    DateTime
} = luxon;

// Get current date/time in America/Los_Angeles timezone
const laNow = DateTime.now().setZone("America/Los_Angeles");

// Format to YYYY-MM-DD for flatpickr
const laDate = laNow.toFormat("yyyy-MM-dd");

flatpickr("#pickDateBtn", {
    dateFormat: "D, F j, Y",
    defaultDate: laDate,
    minDate: laDate, // also respect LA time for minimum
    prevArrow: "←",
    nextArrow: "→",
    disableMobile: true
});
</script>

<style>
/* highlight the selected slot */
.time_slot_group input[type="radio"]:checked+label {
    background-color: #00d4ff;
    color: #000;
    box-shadow: 0 0 10px rgba(0, 212, 255, 0.6);
    border: 2px solid #77777700;
    border-radius: 3px;
}

/* make full slots visibly disabled & not clickable */
.time_slot_group input[type="radio"]:disabled+label {
    opacity: 0.5;
    pointer-events: none;
}
</style>


<!-- // Time Remaining 10 minutes countdown -->


<!-- =========== add on count detail show script ============= -->


<script>
let soloCount = 0;

function increase(type) {
    if (type === 'solo') {
        soloCount++;
        document.getElementById('count-solo').textContent = soloCount;
        toggleSummaryBtn();
    }
}

function decrease(type) {
    if (type === 'solo' && soloCount > 0) {
        soloCount--;
        document.getElementById('count-solo').textContent = soloCount;
        toggleSummaryBtn();
    }
}

function toggleSummaryBtn() {
    const summaryBtn = document.getElementById('add-summary-btn');
    if (soloCount >= 1) {
        summaryBtn.style.display = 'inline-block';
    } else {
        summaryBtn.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('add-summary-btn').addEventListener('click', function() {
        const title = document.querySelector('.add_on_booking_card_title').textContent.trim();
        const price = document.querySelector('.discounted-price').textContent.trim().replace('$', '');
        const quantity = soloCount;
        const total = (parseFloat(price) * quantity).toFixed(2);

        const outputDiv = document.getElementById('summary-output');
        outputDiv.innerHTML = `
            <div class="booking-summary-box_add_on">
                <div class="summary-date">Add Ons Summary</div>
                <div class="summary_ADD_on">
                    <div>${title}</div>
                  
                    <div class="summary-qty-price d-flex justify-content-between align-items-center">
                        <div class="summary-qty-controls">
                            <button class="summary-minus">−</button>
                            <input type="number" id="summary-qty" value="${quantity}" min="0" >
                            <button class="summary-plus">+</button>
                        </div>
                         <div class="summary-price d-flex align-items-end">
                             <div style="margin-right:10px">$${price}</div>
                            <div id="summary-total">$${total}</div>
                         </div>
                    </div>
                </div>
            </div>
        `;

        // Add event listeners for plus/minus in summary
        const qtyInput = document.getElementById('summary-qty');
        const totalEl = document.getElementById('summary-total');
        const bookingBox = document.querySelector('.booking-summary-box_add_on');

        document.querySelector('.summary-plus').addEventListener('click', function() {
            qtyInput.value = parseInt(qtyInput.value) + 1;
            updateSummary(price, qtyInput, totalEl, bookingBox);
        });

        document.querySelector('.summary-minus').addEventListener('click', function() {
            qtyInput.value = Math.max(0, parseInt(qtyInput.value) - 1);
            updateSummary(price, qtyInput, totalEl, bookingBox);
        });

        qtyInput.addEventListener('input', function() {
            if (qtyInput.value < 0) qtyInput.value = 0;
            updateSummary(price, qtyInput, totalEl, bookingBox);
        });

        // Toast
        Toastify({
            text: "Add On Summary Added ✅",
            duration: 3000,
            gravity: "top",
            position: "left",
            backgroundColor: "#00d4ff",
            close: true
        }).showToast();
    });
});

// Function to update summary
function updateSummary(price, qtyInput, totalEl, bookingBox) {
    const qty = parseInt(qtyInput.value);
    if (qty <= 0) {
        bookingBox.remove(); // remove block if 0
        soloCount = 0; // reset original count
        toggleSummaryBtn();
    } else {
        totalEl.textContent = `$${(qty * parseFloat(price)).toFixed(2)}`;
        soloCount = qty; // sync with main counter
        document.getElementById('count-solo').textContent = soloCount;
    }
}
</script>
<style>
#globalLoader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgb(0 0 0 / 64%);
    z-index: 9999;
    display: none;
    /* hidden by default */
    display: flex;
    /* flexbox for centering */
    align-items: center;
    /* vertical center */
    justify-content: center;
    /* horizontal center */
}

#globalLoader .loader-content {
    display: flex;
    flex-direction: column;
    /* stack circle and text vertically */
    align-items: center;
    /* center horizontally */
    text-align: center;
}

/* centered circle */
#globalLoader .loader-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 6px solid #f3f3f3;
    border-top: 6px solid #3498db;
    animation: spin 1s linear infinite;
    margin-bottom: 12px;
    /* space between circle and text */
}

#globalLoader p {
    color: #fff;
    font-size: 16px;
    font-weight: 500;
    margin: 0;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

.disabled-btn {
    opacity: 0.6;
    pointer-events: none;
}
</style>
<div class="modal fade" id="bookeoErrorModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Booking Error</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p id="bookeoErrorMessage" class="fw-bold text-danger"></p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>
<div id="globalLoader" aria-hidden="true">
    <div class="loader-content">
        <div class="loader-circle" role="status" aria-label="Loading"></div>
        <p>Data Loading Please Wait</p>
    </div>
</div>

<div id="stepLoader" aria-hidden="true">
    <div class="step-loader-content">
        <div class="step-loader-circle" role="status" aria-label="Loading"></div>
        <p>Please wait...</p>
    </div>
</div>



<style>
#stepLoader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.45);
    display: none;
    /* hidden by default */
    justify-content: center;
    align-items: center;
    z-index: 10000;
}

#stepLoader .step-loader-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

#stepLoader .step-loader-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 6px solid #f3f3f3;
    border-top: 6px solid #3498db;
    animation: spin 1s linear infinite;
    margin-bottom: 12px;
}

#stepLoader p {
    color: #fff;
    font-size: 16px;
    font-weight: 500;
    margin: 0;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>




<script>
$(document).ready(function() {

    $(document).on("click", ".step-2-bnt_continue", function(e) {
        e.preventDefault();
        let $btn = $(this);
        
        processStepContinue();

        // FETCH LATEST SESSION DATA
        // $.ajax({
        //     url: "get_session_data.php",
        //     type: "GET",
        //     dataType: "json",
        //     success: function(s) {

        //         let sessionPromoCode     = s.promo_code || "";
        //          let sessionPromoCodeCart     = s.promo_code_cart || "";
        //         let sessionPromotionPage = s.promotion_page || "";
        //         let sessionAddonname     = "";
        //         let sessionAddonqty      = "";

        //         if (Array.isArray(s.addons) && s.addons.length > 0) {
        //             sessionAddonname = s.addons[0].addon_name || "";
        //             sessionAddonqty  = s.addons[0].addon_qty  || "";
        //         }

        //         // processStepContinue(
        //         //     $btn,
        //         //     sessionPromoCode,
        //         //     sessionPromoCodeCart,
        //         //     sessionPromotionPage,
        //         //     sessionAddonname,
        //         //     sessionAddonqty
        //         // );
        //     },
        //     error: function() {
        //         $("#booking-response").html(
        //             `<div class="error-box">Unable to load session.</div>`
        //         );
        //     }
        // });
    });

});

/* ------------------------------------------
   UTILITY FUNCTIONS
-------------------------------------------*/

function showFieldError(fieldId, message) {
    let field = $("#" + fieldId);
    field.css("border-color", "red");
    field.next(".input-error").remove();
    field.after(
        `<span class="input-error" style="color:red;font-size:13px;">${message}</span>`
    );
}

function clearFieldError(fieldId) {
    let field = $("#" + fieldId);
    field.css("border-color", "#ccc");
    field.next(".input-error").remove();
}

/* ------------------------------------------
   MAIN CONTINUE HANDLER
-------------------------------------------*/

function processStepContinue(
) {
    $("#booking-response").html("");

    // Get values from Step 3 inputs
    let firstName = $("#firstName").val().trim();
    let lastName = $("#lastName").val().trim();
    let email = $("#email").val().trim();
    let confirmEmail = $("#confirmEmail").val().trim();
    let phone = $("#phone").val().trim();
    let type = $("#type").val().trim();
    let giftCode = $("#giftCodeInput").val().trim();

    // VALIDATION
    ["firstName", "lastName", "email", "confirmEmail", "phone"].forEach(id => clearFieldError(id));

    if (!firstName) return showFieldError("firstName", "First name is required.");
    if (!lastName) return showFieldError("lastName", "Last name is required.");
    if (!email) return showFieldError("email", "Email is required.");
    if (!confirmEmail) return showFieldError("confirmEmail", "Please confirm your email.");
    if (!phone) return showFieldError("phone", "Phone number is required.");

    let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) return showFieldError("email", "Enter a valid email.");
    if (email !== confirmEmail) return showFieldError("confirmEmail", "Emails do not match.");
    if (!/^[0-9]{10}$/.test(phone)) return showFieldError("phone", "Enter a valid 10-digit phone.");
    
     $.ajax({
        url: "save_customer_details.php", // NEW FILE
        type: "POST",
        data: { firstName, lastName, email, phone, type, giftCode },
        dataType: "json",
        success: function(res) {
            // $btn.prop("disabled", false).text("Place Your Order"); // Reset text for next step
            
            if (res.status === "success") {
                // Update Step 4 Display
                $("#display_name").text(firstName + " " + lastName);
                $("#display_email").text(email);
                $("#display_phone").text(phone);
                
                // Move to Payment Step
                changeStep(1); 
            } else {
                $("#booking-response").html('<div class="error-box">'+res.message+'</div>');
            }
        },
        error: function() {
            $btn.prop("disabled", false).text("Continue");
            $("#booking-response").html('<div class="error-box">Server error.</div>');
        }
    });
}
</script>





<style>
.error-box {
    background: #300;
    border: 2px solid red;
    color: #fff;
    padding: 12px;
    border-radius: 6px;
    margin-top: 10px;
    font-size: 15px;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // --- CONFIG & LIBS ---
    const { DateTime } = luxon;
    const LA_ZONE = "America/Los_Angeles";
    const today = DateTime.now().setZone(LA_ZONE).startOf('day');
    const fmtDisplay = dt => dt.toFormat("ccc, LLLL dd, yyyy");

    // --- REQUEST QUEUE / DEBOUNCE (to avoid Bookeo 429) ---
    let activeRequests = 0;
    let fetchTimers = {}; // per product/date debounce
    let maxParallel = 3;
    let runningRequests = 0;
    let requestQueue = [];
    let loadedTabs = {}; // cache tab html (optional)

    function showLoader() {
        $("#globalLoader").fadeIn(160);
    }

    function hideLoader() {
        if (activeRequests <= 0) $("#globalLoader").fadeOut(160);
    }

    function trackRequestStart() {
        activeRequests++;
        showLoader();
    }

    function trackRequestEnd() {
        activeRequests = Math.max(0, activeRequests - 1);
        if (activeRequests === 0) hideLoader();
    }

    function queueRequest(fn) {
        requestQueue.push(fn);
        processQueue();
    }

    function processQueue() {
        if (runningRequests >= maxParallel || requestQueue.length === 0) return;
        runningRequests++;
        const fn = requestQueue.shift();
        fn().always(() => {
            runningRequests--;
            processQueue();
        });
    }

    // ---------- Utility: safe extraction of productIds ----------
    function extractProductIdsFromCollection($collection) {
        // Read attr('data-product') explicitly, skip flatpickr-mobile and invalid values
        const ids = $collection.map(function() {
            const $el = $(this);
            if ($el.hasClass('flatpickr-mobile')) return null;
            // prefer attr to avoid jQuery caching quirks
            const raw = $el.attr('data-product');
            if (!raw || typeof raw !== 'string') return null;
            const trimmed = raw.trim();
            // Very small sanity check (IDs in your examples are long hex-like strings)
            if (trimmed === '' || trimmed.length < 4) return null;
            // Avoid accidental class-name or input-name being captured
            if (trimmed.toLowerCase().includes('custom-datepicker_input')) return null;
            return trimmed;
        }).get().filter(Boolean);
        return ids;
    }

    // --- SLOT FETCHING FOR MULTIPLE PRODUCTS ---
    function fetchSlotsForProducts(productIds, rawDate, allowAutoNextDay = true) {
        // Defensive sanitize
        productIds = (productIds || []).map(p => (typeof p === 'string' ? p.trim() : '')).filter(Boolean);
    
        if (!productIds.length || !rawDate) {
            console.log(`fetchSlotsForProducts: Invalid input`);
            return;
        }
    
        const ajaxFn = () => {
            const deferred = $.Deferred();
            trackRequestStart();
    
            // Show loading
            productIds.forEach(id => {
                const $container = $('#timeSlots-' + id);
                if ($container.length) $container.html('<div class="time_slots_loader">Loading slots...</div>');
            });
    
            $.ajax({
                url: 'fetch_slots.php',
                type: 'GET',
                data: {
                    date: rawDate,
                    productIds: JSON.stringify(productIds)
                },
                dataType: 'json',
                success: function(response) {
                    if (typeof response !== 'object' || response === null) {
                        productIds.forEach(id => $('#timeSlots-' + id).html('<p>No slots available</p>'));
                        return;
                    }
    
                    // 1. TRACK IF ANY GAME HAS A SLOT
                    let atLeastOneGameHasSlots = false;
    
                    productIds.forEach(productId => {
                        const res = response[productId];
                        let html = (res && typeof res === 'object' && res.html) ? res.html : (res || '<p>No slots available</p>');
                        
                        // Update UI
                        const $container = $('#timeSlots-' + productId);
                        if ($container.length) $container.html(html);
    
                        // Check availability for this specific game
                        if (!html.includes("No slots available") && !html.includes("Error loading slots")) {
                            atLeastOneGameHasSlots = true;
                        }
                    });
    
                    // 2. CHECK TIME (8:30 PM Cutoff)
                    const laNow = luxon.DateTime.now().setZone("America/Los_Angeles");
    
                    // Logic: Is it after 8 PM? OR Is it 8 PM and minutes >= 30?
                    const isPastCutoff = (laNow.hour > 20) || (laNow.hour === 20 && laNow.minute >= 30);
    
                    // 3. DECIDE TO REDIRECT
                    if (allowAutoNextDay && isPastCutoff && !atLeastOneGameHasSlots) {
                        
                        const currentRequestedDate = luxon.DateTime.fromISO(rawDate, { zone: "America/Los_Angeles" });
                        const nextDay = currentRequestedDate.plus({ days: 1 });
                        
                        // Prevent infinite jumps (limit to 1 day from today)
                        const todayRef = luxon.DateTime.now().setZone("America/Los_Angeles").startOf('day');
                        const diffFromToday = nextDay.diff(todayRef, 'days').days;
    
                        if (diffFromToday <= 1) {
                            // 1. Raw format for logic/flatpickr (YYYY-MM-DD)
                            const nextRaw = nextDay.toFormat('yyyy-MM-dd');
                            
                            // 2. Pretty format for Display (Fri, March 06, 2026)
                            const nextVisual = nextDay.toFormat("ccc, LLLL dd, yyyy");
    
                            console.log(`Auto-advance: Switching ALL datepickers to ${nextVisual} (${nextRaw})`);
    
                            // Update ALL datepickers on the page
                            $('.custom-datepicker_input').each(function() {
                                const $this = $(this);
                                
                                // A. Update internal data attribute (logic)
                                $this.data('rawdate', nextRaw);
                                $this.attr('data-rawdate', nextRaw); 
    
                                // B. Update Flatpickr instance FIRST
                                if (this._flatpickr) {
                                    this._flatpickr.setDate(nextRaw, false); 
                                    
                                    // Update year dropdown if present
                                    const yearSelect = this._flatpickr.calendarContainer.querySelector(".flatpickr-year-dropdown");
                                    if (yearSelect) yearSelect.value = nextDay.year;
                                }
    
                                // C. FORCE OVERWRITE THE DISPLAY TEXT LAST
                                // This fixes the issue where it showed "2026-03-06"
                                $this.val(nextVisual);
                            });
    
                            // Recursively fetch for the next day
                            fetchSlotsForProducts(productIds, nextRaw, false); 
                        }
                    } else {
                        console.log(`Auto-advance skipped.`);
                    }
                },
                error: function(xhr, status, error) {
                    productIds.forEach(id => {
                        $('#timeSlots-' + id).html('<p style="color:red;">Error loading slots</p>');
                    });
                },
                complete: function() {
                    trackRequestEnd();
                    deferred.resolve();
                }
            });
    
            return deferred.promise();
        };
    
        queueRequest(ajaxFn);
    }

    function updatePrevButtons() {
        let anyFuture = false;
        $('.custom-datepicker_input').each(function() {
            const $input = $(this);
            const raw = $input.data('rawdate');
            const current = raw && DateTime.fromISO(raw, { zone: LA_ZONE }).isValid ?
                DateTime.fromISO(raw, { zone: LA_ZONE }).startOf('day') :
                today;
            const diffDays = current.diff(today, 'days').days;
            console.log(`updatePrevButtons: Checking datepicker: product=${$input.attr('data-product') || $input.data('product')}, rawdate=${raw}, current=${current.toISODate()}, today=${today.toISODate()}, diffDays=${diffDays}`);
            if (diffDays > 0) anyFuture = true;
        });
        console.log(`updatePrevButtons: anyFuture=${anyFuture}`);
        const $prevAllBtn = $("#prev-all-btn");
        if (anyFuture) {
            $prevAllBtn.prop("disabled", false)
                .removeClass("disabled disabled-btn");
            console.log("updatePrevButtons: Enabling #prev-all-btn, removing disabled and disabled-btn classes");
        } else {
            $prevAllBtn.prop("disabled", true)
                .addClass("disabled disabled-btn");
            console.log("updatePrevButtons: Disabling #prev-all-btn, adding disabled and disabled-btn classes");
        }
        $(".prev-date").prop("disabled", !anyFuture)
            .css("visibility", anyFuture ? "visible" : "hidden");
    }

    function setDateAll(dt) {
        const normalized = dt.setZone(LA_ZONE).startOf('day');
        const rawDate = normalized.toFormat('yyyy-MM-dd');
        const targetYear = normalized.year; // Extract the year (e.g., 2029)

        console.log(`setDateAll: Setting date to ${rawDate}`);

        $('.custom-datepicker_input').each(function() {
            const $input = $(this);
            
            if ($input.hasClass('flatpickr-mobile')) return;
            if (this._flatpickr) {
                this._flatpickr.setDate(rawDate, false);
                const yearSelect = this._flatpickr.calendarContainer.querySelector(".flatpickr-year-dropdown");
                if (yearSelect) {
                    yearSelect.value = targetYear;
                }
            }
            $input.val(fmtDisplay(normalized));
            $input.data('rawdate', rawDate);
            console.log(`setDateAll: Updated datepicker: product=${$input.attr('data-product') || $input.data('product')}, rawdate=${$input.data('rawdate')}`);
        });
        const productIds = extractProductIdsFromCollection($('.custom-datepicker_input'));
        console.log(`setDateAll: Fetching slots for productIds: ${JSON.stringify(productIds)}`);
        fetchSlotsForProducts(productIds, rawDate);
        updatePrevButtons();
    }

    function loadTabContent($tab) {
        const label = $.trim($tab.text());
        const file = label.toLowerCase().replace(/\s+/g, '-') + '.php';
        const target = $tab.data('bs-target');

        if (loadedTabs[target]) {
            $(target).html(loadedTabs[target]);
            initTabContent($(target));
            return;
        }

        $(target).html('<p>Loading...</p>');
        $.get(file, function(data) {
            $(target).html(data);
            loadedTabs[target] = data;
            initTabContent($(target));
        });
    }

    // --- Datepickers + init (for tab content) ---
    function initDatePickers(container = $(document)) {
        let initialDate = today.toFormat('yyyy-MM-dd');
        const $globalPicker = $('#custom-datepicke2');
        if ($globalPicker.length) {
            const globalRaw = $globalPicker.data('rawdate');
            // If global picker has a date (and it's not the initialization of the page), use it
            if (globalRaw && DateTime.fromISO(globalRaw).isValid) {
                initialDate = globalRaw;
                console.log(`initDatePickers: Inheriting global date ${initialDate}`);
            }
        }
        container.find('.custom-datepicker_input').each(function(index) {
            const $input = $(this);
            if ($input.hasClass('flatpickr-mobile')) return;
            const productId = $input.attr('data-product') || $input.data('product');
            if (!productId && $input.attr('id') !== 'custom-datepicke2') {
                return;
            }
            if ($input[0]._flatpickr) $input[0]._flatpickr.destroy();
            $input.data('rawdate', initialDate);
            $input.val(fmtDisplay(DateTime.fromISO(initialDate)));

            flatpickr($input[0], {
                dateFormat: "Y-m-d",
                defaultDate: initialDate,
                minDate: today.toFormat('yyyy-MM-dd'),
                allowInput: false,
                clickOpens: true,
                disableMobile: true,
                monthSelectorType: "dropdown",
                
                onReady: (selectedDates, dateStr, instance) => {
                    const calendar = instance.calendarContainer;
                    const numInputWrapper = calendar.querySelector(".numInputWrapper");
                    
                    if (numInputWrapper && !calendar.querySelector(".flatpickr-year-dropdown")) {
                        numInputWrapper.classList.add("hidden-year");
                        const yearSelect = document.createElement("select");
                        yearSelect.className = "flatpickr-year-dropdown flatpickr-monthDropdown-months";
                        
                        const currentYear = new Date().getFullYear();
                        const maxYear = currentYear + 3; 

                        for (let i = currentYear; i <= maxYear; i++) {
                            const option = document.createElement("option");
                            option.value = i;
                            option.text = i;
                            yearSelect.appendChild(option);
                        }
                        yearSelect.value = instance.currentYear;

                        yearSelect.addEventListener("change", function (e) {
                            instance.changeYear(parseInt(e.target.value));
                        });

                        const currentMonthContainer = calendar.querySelector(".flatpickr-current-month");
                        if (currentMonthContainer) {
                            currentMonthContainer.appendChild(yearSelect);
                        }
                    }
                    const duplicates = document.querySelectorAll('.flatpickr-mobile');
                    duplicates.forEach(inp => inp.remove());

                    $input.attr('type', 'text')
                          .removeClass('d-none')
                          .css({ display: 'block', visibility: 'visible', opacity: '1' });

                    // Ensure visual text matches the inherited date
                    $input.val(fmtDisplay(DateTime.fromISO(initialDate)));
                },
                
                onYearChange: function(selectedDates, dateStr, instance) {
                    const yearSelect = instance.calendarContainer.querySelector(".flatpickr-year-dropdown");
                    if (yearSelect) yearSelect.value = instance.currentYear;
                },

                onChange: (selectedDates) => {
                    if (!selectedDates || !selectedDates[0]) return;
                    
                    const jsDate = selectedDates[0];
                    const picked = DateTime.fromObject({
                        year: jsDate.getFullYear(),
                        month: jsDate.getMonth() + 1,
                        day: jsDate.getDate()
                    }, { zone: LA_ZONE }).startOf('day');
                    
                    // Call the main function to update ALL pickers
                    setDateAll(picked);
                }
            });
        });

        // Fetch slots for products in this container using the INHERITED date
        const productIds = extractProductIdsFromCollection(container.find('.custom-datepicker_input'));
        console.log(`initDatePickers: Initial fetch for productIds: ${JSON.stringify(productIds)} on ${initialDate}`);
        fetchSlotsForProducts(productIds, initialDate);

        updatePrevButtons();
    }

    // --- GUEST / CONTINUE / TIMER LOGIC ---
    let guestCounts = {}; // productCode => count
    let slotAvailableSeats = {}; // productCode => seats available
    let timers = {}; // productCode => intervalId
    let wasGuestZero = {}; // productCode => boolean
    let expiryTriggeredFromSlotTimer = false;

    function getProductPrices(productCode) {
        const priceEl = document.querySelector(`#price-${productCode}`);
        if (!priceEl) return { min: 0, max: 0 };
        const text = priceEl.textContent;
        const matches = text.match(/\d+(?:\.\d+)?/g) || [];
        const uniquePrices = [...new Set(matches.map(Number))].sort((a, b) => a - b);
        return { min: uniquePrices[0] || 0, max: uniquePrices[uniquePrices.length - 1] || 0 };
    }

    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${minutes < 10 ? '0' : ''}${minutes}:${secs < 10 ? '0' : ''}${secs}`;
    }

    function expireCartAndRefreshSlots(reason = "slot_timer") {
        if (expiryTriggeredFromSlotTimer) return;
        expiryTriggeredFromSlotTimer = true;

        localStorage.removeItem('cartTimerEnd');
        localStorage.setItem('cartTimerExpired', 'true');

        fetch("expire_cart.php?reason=" + encodeURIComponent(reason), { cache: "no-store" })
            .catch(() => {})
            .finally(() => {
                if (typeof loadCart === "function") loadCart();

                const productIds = extractProductIdsFromCollection($('.custom-datepicker_input'));
                const rawDate = ($('#custom-datepicke2').attr('data-rawdate') || $('#custom-datepicke2').data('rawdate'));
                if (productIds.length && rawDate) {
                    fetchSlotsForProducts(productIds, rawDate);
                }

                expiryTriggeredFromSlotTimer = false;
            });
    }

    function startTimerForProduct(productCode) {
        clearInterval(timers[productCode]);
        let totalSeconds = (Number(window.CART_TIMER_MINUTES || 3) * 60);
        const timerWrapper = document.getElementById("timer-wrapper-" + productCode);
        if (!timerWrapper) return;
        const timerEls = timerWrapper.querySelectorAll('.timer_display');
        timerEls.forEach(el => el.innerText = formatTime(totalSeconds));
        timers[productCode] = setInterval(() => {
            totalSeconds--;
            timerEls.forEach(el => el.innerText = formatTime(totalSeconds));
            if (totalSeconds <= 0) {
                clearInterval(timers[productCode]);
                expireCartAndRefreshSlots("slot_timer_countdown_end");
            }
        }, 1000);
    }

    function stopTimerForProduct(productCode) {
        clearInterval(timers[productCode]);
        const timerWrapper = document.getElementById("timer-wrapper-" + productCode);
        if (timerWrapper) timerWrapper.style.display = "none";
    }

    function disableAllContinueButtons() {
        document.querySelectorAll(".continue_nex_step").forEach(btn => {
            btn.classList.add("disabled");
            btn.setAttribute("disabled", "true");
            btn.removeAttribute("data-bs-toggle");
            btn.removeAttribute("data-bs-target");
        });
    }

    function getPricePerGuest(productCode, guestCount) {
        const { min, max } = getProductPrices(productCode);
        if (min === max) return min;
        return guestCount === 2 ? max : min;
    }

    function updateContinueStateForProduct(productCode) {
        const btn = document.querySelector(`.continue_nex_step[data-game-id="${productCode}"]`);
        const priceEl = document.getElementById("total-price-" + productCode);
        const timerWrapper = document.getElementById("timer-wrapper-" + productCode);
        const timerEls = timerWrapper ? timerWrapper.querySelectorAll('.timer_display') : [];
        const timeSelected = !!document.querySelector(`input[name="lift-time-${productCode}"]:checked`);
        const count = guestCounts[productCode] || 0;
        const pricePerGuest = getPricePerGuest(productCode, count);
        const isEnabled = timeSelected && count > 0;

        if (btn) {
            if (isEnabled) {
                disableAllContinueButtons();
                btn.classList.remove("disabled");
                btn.removeAttribute("disabled");
            } else {
                btn.classList.add("disabled");
                btn.setAttribute("disabled", "true");
                btn.removeAttribute("data-bs-toggle");
                btn.removeAttribute("data-bs-target");
            }
        }

        if (priceEl) {
            priceEl.textContent = (count * pricePerGuest).toFixed(2);
        }

        if (timerWrapper) {
            if (count > 0) {
                timerWrapper.style.display = "block";
                if (!wasGuestZero[productCode]) {
                    // timer already started
                } else {
                    startTimerForProduct(productCode);
                    wasGuestZero[productCode] = false;
                }
            } else {
                stopTimerForProduct(productCode);
                wasGuestZero[productCode] = true;
            }
        }
    }

    function initGuestStateInContainer(container = $(document)) {
        container.find('.guest-count-wrapper').each(function() {
            const wrapper = this;
            const productCode = wrapper.querySelector(".guest-value").id.replace("guest-count-", "");
            guestCounts[productCode] = 0;
            wasGuestZero[productCode] = true;
            slotAvailableSeats[productCode] = slotAvailableSeats[productCode] || 0;
            const guestValueEl = wrapper.querySelector(".guest-value");
            if (guestValueEl) guestValueEl.textContent = 0;
            const priceEl = document.getElementById("total-price-" + productCode);
            if (priceEl) priceEl.textContent = "0";
        });

        container.find(".time_slot_group.time_slot_full input[type='radio']").each(function() {
            this.disabled = true;
        });
    }

    // --- DELEGATED EVENT HANDLERS ---
    $(document).on('click', '.guest-count-wrapper .plus-btn', function(e) {
        const wrapper = this.closest('.guest-count-wrapper');
        if (!wrapper) return;
        const productCode = wrapper.querySelector(".guest-value").id.replace("guest-count-", "");
        const guestCountEl = wrapper.querySelector(".guest-value");
        const maxSeats = slotAvailableSeats[productCode] || 99;

        if (maxSeats <= 0) return;

        if (!guestCounts[productCode]) guestCounts[productCode] = 0;

        if (guestCounts[productCode] === 0) {
            guestCounts[productCode] = Math.min(2, maxSeats);
        } else if (guestCounts[productCode] < maxSeats) {
            guestCounts[productCode]++;
        }
        guestCountEl.textContent = guestCounts[productCode];

        updateContinueStateForProduct(productCode);
    });

    $(document).on('click', '.guest-count-wrapper .minus-btn', function(e) {
        const wrapper = this.closest('.guest-count-wrapper');
        if (!wrapper) return;
        const productCode = wrapper.querySelector(".guest-value").id.replace("guest-count-", "");
        const guestCountEl = wrapper.querySelector(".guest-value");

        if (!guestCounts[productCode]) guestCounts[productCode] = 0;

        if (guestCounts[productCode] > 2) {
            guestCounts[productCode]--;
        } else if (guestCounts[productCode] === 2) {
            guestCounts[productCode] = 0;
        }
        guestCountEl.textContent = guestCounts[productCode];

        updateContinueStateForProduct(productCode);
    });

    $(document).on('change', ".time_slot_group input[type='radio'][name^='lift-time-']", function(e) {
        const current = this;
        if (!current.checked) return;

        const name = current.name;
        document.querySelectorAll(`input[name="${name}"]`).forEach(inp => {
            if (inp !== current && inp.checked) {
                inp.checked = false;
                $(inp).trigger('change');
            }
        });

        for (const product in guestCounts) {
            guestCounts[product] = 0;
            const guestEl = document.getElementById("guest-count-" + product);
            if (guestEl) guestEl.textContent = 0;
            updateContinueStateForProduct(product);
        }
        
        if(window.latestCart){
            updateBookedButtons(window.latestCart);
        }

        const productCode = current.name.replace("lift-time-", "");
        const available = parseInt(current.getAttribute("data-available"), 10) || 0;
        slotAvailableSeats[productCode] = available;
    });

    $(document).on('click', '.continue_nex_step', function(e) {
        const btn = this;
        if (btn.classList.contains('disabled')) {
            e.preventDefault();
            return;
        }
    });

    function initContinueButtonsInContainer(container = $(document)) {
        container.find('.continue_nex_step').each(function() {
            const btn = this;
            const productCode = btn.getAttribute('data-game-id');
            updateContinueStateForProduct(productCode);
        });
    }

    function initTabContent($container) {
        initDatePickers($container);
        initGuestStateInContainer($container);
        initContinueButtonsInContainer($container);
    }

    $(".tab-item").on("click", function() {
        loadTabContent($(this));
    });

    const $defaultTab = $(".tab-item.active");
    if ($defaultTab.length) loadTabContent($defaultTab);

    $(document).on("click", ".prev-date, #prev-all-btn", function(e) {
        e.preventDefault();
        const first = $('.custom-datepicker_input').first();
        if (!first.length) {
            console.log('Prev button clicked: No datepickers found');
            return;
        }
        const raw = first.data('rawdate') || today.toFormat('yyyy-MM-dd');
        const current = DateTime.fromISO(raw, { zone: LA_ZONE }).isValid ?
            DateTime.fromISO(raw, { zone: LA_ZONE }).startOf('day') :
            today;
        const diffDays = current.diff(today, 'days').days;
        console.log(`Prev button clicked: rawdate=${raw}, current=${current.toISODate()}, today=${today.toISODate()}, diffDays=${diffDays}`);
        if (diffDays > 0) {
            const newDate = current.minus({ days: 1 });
            console.log(`Prev button: Decreasing date to ${newDate.toISODate()}`);
            setDateAll(newDate);
        } else {
            console.log('Prev button: Cannot go before today');
        }
    });

    $(document).on("click", ".next-date, #next-all-btn", function(e) {
        e.preventDefault();
        const first = $('.custom-datepicker_input').first();
        if (!first.length) {
            console.log('Next button clicked: No datepickers found');
            return;
        }
        const raw = first.data('rawdate') || today.toFormat('yyyy-MM-dd');
        const current = DateTime.fromISO(raw, { zone: LA_ZONE }).isValid ?
            DateTime.fromISO(raw, { zone: LA_ZONE }).startOf('day') :
            today;
        const newDate = current.plus({ days: 1 });
        console.log(`Next button clicked: rawdate=${raw}, current=${current.toISODate()}, setting to ${newDate.toISODate()}`);
        setDateAll(newDate);
    });

    // --- GLOBAL PICK DATE BUTTON FUNCTIONALITY ---
    initTabContent($(document));

    // --- FIX: Remove Flatpickr duplicate input (keep only first one) ---
    setTimeout(() => {
      const duplicateInputs = document.querySelectorAll('.flatpickr-mobile');
      duplicateInputs.forEach(input => input.remove());
      const mainInputs = document.querySelectorAll('.custom-datepicker_input');
      mainInputs.forEach(input => {
        input.classList.remove('d-none');
        input.style.display = 'block';
        input.style.visibility = 'visible';
        input.style.opacity = '1';
        input.type = 'text';
      });
      console.log("✅ Removed Flatpickr duplicate inputs — only main input kept.");
    }, 1200);
});
</script>



<script>
const appId = "sandbox-sq0idb-VwqgN_zOnEPVQGzbPNMKDQ";
const locationId = "L8XX876JN6ZSH";

let sessionPromoCode = "";
let sessionPromotionPage = "";
let sessionAddonName = "";
let sessionAddonQty = "";
let sessionAddonPrice = "";
let sessionAddonSubtotal = "";
let sessionAddonTax = "";


/* ---------------------------------------------------------
   FETCH LIVE SESSION VALUES (Promo + Promotion + Addons)
----------------------------------------------------------*/
async function refreshSessionValues() {
    try {
        const res = await fetch("get_session_data.php");
        const s = await res.json();

        // Base session values
        sessionPromoCode     = s.promo_code || "";
          sessionPromoCodeCart     = s.promo_code_cart || "";
        sessionPromotionPage = s.promotion_page || "";

        // Add-ons (dynamic)
        if (Array.isArray(s.addons) && s.addons.length > 0) {
            let addon = s.addons[0];

            sessionAddonName     = addon.addon_name     || "";
            sessionAddonQty      = addon.addon_qty      || "";
            sessionAddonPrice    = addon.addon_price    || "";
            sessionAddonSubtotal = addon.addon_subtotal || "";
            sessionAddonTax      = addon.addon_tax      || "";
        } else {
            sessionAddonName     = "";
            sessionAddonQty      = "";
            sessionAddonPrice    = "";
            sessionAddonSubtotal = "";
            sessionAddonTax      = "";
        }

        console.log("🔄 Live Promo:", sessionPromoCode);
        console.log("🔄 Live Promotion:", sessionPromotionPage);
        console.log("🔄 Live Addon:", sessionAddonName, sessionAddonQty, sessionAddonSubtotal);

    } catch (err) {
        console.log("Session fetch failed:", err);
    }
}


/* ---------------------------------------------------------
   Square Payment Initialization
----------------------------------------------------------*/
async function initializeCard(payments) {
    try {
        const card = await payments.card();
        await card.attach('#card-container');
        return card;
    } catch (err) {
        throw new Error(`Card initialization failed: ${err.message}`);
    }
}


/* ---------------------------------------------------------
   CREATE PAYMENT — USE SESSION VALUES
----------------------------------------------------------*/
async function createPayment(token, useGiftCode) {

    const endpoint =
        sessionPromoCodeCart || useGiftCode ||
        (sessionPromoCode && sessionPromotionPage === "true") ||
        (sessionAddonName && sessionAddonQty)
            ? "payment.php"
            : "payment2.php";

    console.log("➡️ Using endpoint:", endpoint);

    try {
        const res = await fetch(endpoint, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                sourceId: token,
                amount: 100,
                currency: "USD",

                // OPTIONAL: send addons to backend
                addonName: sessionAddonName,
                addonQty: sessionAddonQty,
                addonPrice: sessionAddonPrice,
                addonSubtotal: sessionAddonSubtotal,
                addonTax: sessionAddonTax
            })
        });

        const text = await res.text();
        console.log("Raw response:", text);

        if (!res.ok) {
            throw new Error(`HTTP error ${res.status}: ${text}`);
        }

        return JSON.parse(text);
    } catch (err) {
        throw new Error(`Fetch error: ${err.message}`);
    }
}


/* ---------------------------------------------------------
   TOKENIZATION
----------------------------------------------------------*/
async function tokenize(paymentMethod) {
    try {
        const result = await paymentMethod.tokenize();

        if (result.status === "OK") return result.token;

        // ⛔ Tokenization failed → friendly message
        showError("Invalid card number. Please use a valid card.");
        return null; // process stop

    } catch (err) {
        // ⛔ Catch network or other errors
        showError("Payment processing failed. Please check your card details.");
        return null; // process stop
    }
}


// async function tokenize(paymentMethod) {
//     try {
//         const result = await paymentMethod.tokenize();
//         if (result.status === "OK") return result.token;
//         throw new Error(JSON.stringify(result.errors));
//     } catch (err) {
//         throw new Error(`Tokenization failed: ${err.message}`);
//     }
// }


/* ---------------------------------------------------------
   SHOW ERROR
----------------------------------------------------------*/
function showError(message) {
    document.getElementById("booking-response").innerHTML = `
        <div class="error-box" style="
            border:2px solid red;
            padding:10px;
            color:red;
            margin:10px 0;
            border-radius:6px;">
            • ${message}
        </div>`;
}


async function processFinalPayment(token) {
    const endpoint = "process_booking.php"; // NEW UNIFIED FILE

    try {
        const res = await fetch(endpoint, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                sourceId: token, // Card nonce
                // currency is handled in PHP
            })
        });

        const json = await res.json();

        if (json.status === "success") {
            window.location.href = json.redirectUrl;
        } else {
             // Handle Square or API errors
            document.getElementById("booking-response").innerHTML = 
                `<div class="error-box">${json.message || "Payment Failed"}</div>`;
        }
    } catch (err) {
        console.error(err);
        document.getElementById("booking-response").innerHTML = 
            `<div class="error-box">Network Error</div>`;
    }
}

/* ---------------------------------------------------------
   MAIN PAYMENT HANDLER
----------------------------------------------------------*/
document.addEventListener("DOMContentLoaded", async () => {

    const statusContainer = document.getElementById("booking-response");

    // Helper: show error inside booking-response div
    function showError(msg) {
        statusContainer.innerHTML = `
            <div style="color:red; padding:10px; font-weight:600;">
                ${msg}
            </div>
        `;
    }

    // Helper: clear error box
    function clearError() {
        statusContainer.innerHTML = "";
    }

    try {
        const payments = window.Square.payments(appId, locationId);
        const card = await initializeCard(payments);

      $(document).on("click", ".step-3-bnt_continue", async function(e) {
    e.preventDefault();
    let $btn = $(this);

    // CLEAR OLD MESSAGE
    $("#booking-response").html("");

    // ✅ CHECKBOX VALIDATION
    if (!$("#agreeTerms").is(":checked")) {

        $("#booking-response").html(`
            <div style="color:red; padding:10px; font-weight:600;">
                Please agree to the terms before continuing.
            </div>
        `);

        return; // Stop process here
    }

    clearError();

    // FIRST ⇒ Load updated session (Including Add-ons)
    await refreshSessionValues();

$btn.prop("disabled", true).text("Processing...");

try {
    const codeSpan = document.querySelector(".code");
    const codeValue = codeSpan ? codeSpan.textContent.trim() : "";
    const hasGiftCode = (codeValue !== "FIRSTRESPONDERS2025" && codeValue !== "");

    // TOKENIZE CARD
    const token = await tokenize(card);

    // Agar token fail ho gaya
    if (!token) {
        showError("Invalid card. Please use a valid card number."); // friendly message
        return; // ⛔ Stop process here
    }

    // ✅ Sirf valid token par call ho
    await processFinalPayment(token);

} catch (err) {
    // Catch any other errors
    showError(err.message || "Payment error occurred."); 
    return; // ⛔ Stop here
} finally {
    // Reset button state
    $btn.prop("disabled", false).text("Continue");
}

    // await refreshSessionValues();

    // $btn.prop("disabled", true).text("Processing...");

    // try {
    //     const codeSpan = document.querySelector(".code");
    //     const codeValue = codeSpan ? codeSpan.textContent.trim() : "";
    //     const hasGiftCode = (codeValue !== "FIRSTRESPONDERS2025" && codeValue !== "");

    //     const token = await tokenize(card);

    //     if (!token) {
    //         showError("Card token failed. Try again.");
    //         return;
    //     }

    //     processFinalPayment(token)

    // } catch (err) {
    //     showError(err.message || "Payment error occurred.");
    // } finally {
    //     $btn.prop("disabled", false).text("Continue");
    // }

});


    } catch (err) {
        showError("Initialization Failed: " + err.message);
    }
});

</script>





<script>
const BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="assets/js/booking-js.js"></script>
<div class="modal fade" id="timeslotModal" tabindex="-1" aria-labelledby="timeslotModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">

    <div class="modal-dialog modal-dialog-centered" style="max-width: 800px;">
        <div class="modal-content custom-modal text-white" style="background-color: #0e0e0e; border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h4 class="modal-title fw-bold text-uppercase text-center w-100" id="timeslotModalLabel"
                    style="color: #00e6f6;">
                    🎉 Bundle and Save
                </h4>
                <button onclick="goToAddonsOrCustomer()" type="button" class="btn-close position-absolute end-0 me-3 mt-3"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-3 pt-0 pb-3 d-flex flex-column justify-content-between">
                <div class="text-center">
                    <p class="fs-6" style="color: #fff;">Unlock more savings by adding more rooms to your booking!</p>

                    <!-- Offer Cards -->
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <div class="offer-card" data-offer="1" style="text-align:left;">
                            <div class="d-flex align-items-center mb-1">
                                <div class="offer-number">2+</div>
                                <div>
                                    <div class="fw-bold text-white">Add 1 More Escape Room</div>
                                    <small class="text-muted">The Smart Choice</small>
                                </div>
                            </div>
                            <div class="text-info fw-bold mt-2">Save 10% OFF TOTAL PRICE</div>
                            <small class="text-light d-block text-wrap">
                                Perfect for trying different themes and challenges.
                                Most popular choice among first-time visitors!
                            </small>
                        </div>

                        <div class="offer-card" data-offer="2" style="text-align:left;">
                            <div class="d-flex align-items-center mb-1">
                                <div class="offer-number">3+</div>
                                <div>
                                    <div class="fw-bold text-white">Add 2 or more Escape Rooms</div>
                                    <small class="text-muted">The Ultimate Experience</small>
                                </div>
                            </div>
                            <div class="text-info fw-bold mt-2">Save 20% OFF TOTAL PRICE</div>
                            <small class="text-light d-block text-wrap">
                                Perfect for special occasions and serious puzzle enthusiasts.
                            </small>
                        </div>
                    </div>
                </div>

                <div style="background-color: #1a1a1a; border: 1px solid #00e6f67d; border-radius: 10px; padding: 15px;"
                    class="text-center mt-3">
                    <div class="fw-bold" style="color: #00e6f6;">⏳ Limited Time Opportunity</div>
                    <small class="text-light d-block mb-1">
                        Offer only available during this session. After you leave, rooms will be full price.
                    </small>
                    <small style="color: #00e6f6;">🔥 87% of users who see this add at least one more room!</small>
                </div>

                <p class="pro-tip">
                    💡 <strong>Pro Tip:</strong> You can always book more rooms later,
                    but you won't get these special savings! Make the most of your visit today.
                </p>

                <div class="all_button_main_header">
                    <a href="#to_book_scroll" onclick="goToAddonsOrCustomer()" data-bs-dismiss="modal"
                        class="bg_bnt_custom bg_bnt_custom_tran custom_scroll">Skip</a>
                    <a href="#" data-bs-dismiss="modal" class="bg_bnt_custom">Add More Rooms</a>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.call-slot-btn {
    display: inline-block;
        margin: 5px;
    padding: 8px;
    border: 2px solid #777;
    color: #00d4ff;
    cursor: pointer;
    font-size: 15px;
    white-space: nowrap;
    transition: 0.3s ease;
    display: grid;
    text-align: center;
    line-height: normal;
    font-weight: 600;
    border-radius: 3px;
}
.Available_play_time{
        color: #fff;
    line-height: initial;
    padding-top: 4px;
    font-weight: 300;
    font-size: 14px;
}


.call-popup-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
.call-popup-box {
    background: #fff;
    padding: 20px 30px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
.call-popup-box button {
    margin-top: 10px;
    padding: 6px 14px;
    background: #1976d2;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
.call-popup-box button:hover { background: #1565c0; }
</style>
<div id="callPopup" class="popup-overlay" style="display:none;">
  <div class="popup-box">
    <p id="callText">To make a booking, call us on (425)287-1426</p>
    <div class="popup-buttons">
      <button id="cancelBtn">Cancel</button>
      <a id="callNowBtn" href="tel:(425)287-1426">Call now</a>
    </div>
  </div>
</div>
<script> 
function showCallPopup(time) {
    const popup = document.createElement("div");
    popup.className = "call-popup-overlay";
    popup.innerHTML = `
        <div class="call-popup-box">
            <p>To book for ${time}, please call our support team.</p>
             <button > <a id="callNowBtn" href="tel:(425)287-1426">Call now</a></button>
            <button onclick="this.closest(\'.call-popup-overlay\').remove()">Close</button>
        </div>`;
    document.body.appendChild(popup);
}
</script>
<!-- Voucher Code Modal  -->
<div class="modal fade voucher-modal" id="input_voucher_code_new" tabindex="-1" aria-labelledby="voucherModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: max-content; padding:12px">
        <div class="modal-content custom-modal voucher-modal-content">
            <div class="modal-header voucher-modal-header">
                <h5 class="modal-title" id="voucherModalLabel">
                    <i class="fa-solid fa-ticket-simple"></i>
                    Promotion/voucher code
                </h5>
                <button type="button" class="btn-close btn-close-voucher" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body voucher-modal-body ">
                <form class="voucher-form" >
                    <!--<label for="giftCodeInput" class="voucher-form-label">Code</label>-->
                    <input type="text" id="giftCodeInput" class="voucher-form-input" 
                    placeholder="Enter your code"
                    value="<?= htmlspecialchars($_SESSION['giftCode'] ?? '') ?>">
                </form>
                <div>
                    <p>To use multiple coupons, separate them with a comma. <br> Ex. ABC, DEF</p>
                </div>
            </div>
            <div class="all_button_main_header text-end" style="background-size: cover; background-repeat: no-repeat;">
               
                
                 <button class="btn_apply bg_bnt_custom bg_bnt_custom_tran">Apply</button>
                <a href="#" class="bg_bnt_custom" data-bs-dismiss="modal">Cancel</a>
            </div>


        </div>
    </div>
</div>

<!-- Modal Code -->
<div class="modal fade" id="partymodalform" tabindex="-1" aria-labelledby="partymodalformLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 900px;">
        <div class="modal-content custom-modal text-white">
            <div class="modal-header custom-modal-header">
                <h4 class="modal-title" id="partymodalformLabel">🗝️ Escape Room Choice</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body custom-modal-body">
                <p class="intro-text">
                    Please select your preferred escape rooms (multiple allowed).<br>
                    Your choices will appear on the right.
                </p>

                <div class="row g-4">
                    <!-- Left side -->
                    <div class="col-md-6">
                        <div class="modal-box">
                            <h5>Available Escape Rooms</h5>
                            <div class="room-list">
                                <label class="room-item"><input type="checkbox" class="room-checkbox" value="The Lift"> <i class="fa-solid fa-door-open"></i> The Lift</label>
                                <label class="room-item"><input type="checkbox" class="room-checkbox" value="Ice Walker - GOT"> <i class="fa-solid fa-snowflake"></i> Ice Walker - GOT</label>
                                <label class="room-item"><input type="checkbox" class="room-checkbox" value="Prison Escape"> <i class="fa-solid fa-lock"></i> Prison Escape</label>
                                <label class="room-item"><input type="checkbox" class="room-checkbox" value="Steampunk Submarine"> <i class="fa-solid fa-gears"></i> Steampunk Submarine</label>
                                <label class="room-item"><input type="checkbox" class="room-checkbox" value="Museum Heist"> <i class="fa-solid fa-landmark"></i> Museum Heist</label>
                                <label class="room-item"><input type="checkbox" class="room-checkbox" value="Ancient Egypt"> <i class="fa-solid fa-monument"></i> Ancient Egypt</label>
                                <label class="room-item"><input type="checkbox" class="room-checkbox" value="Any"> <i class="fa-solid fa-monument"></i> Any</label>
                            </div>
                        </div>
                    </div>

                    <!-- Right side -->
                    <div class="col-md-6">
                        <div class="modal-box">
                            <h5>Your Selection</h5>
                            <textarea id="selectionTextarea" placeholder="Your selected rooms will appear here..." style="width:100%;height:150px;" readonly></textarea>
                            <small>💡 If you don’t have a preference, just select <b>Any</b>.</small>
                        </div>
                    </div>

                   
                </div>
            </div>

            <!-- Footer buttons -->
            <div class="all_button_main_header">
                <a href="javascript:void(0)" id="escape-selection" class="bg_bnt_custom disabled custom_scroll"
                    aria-disabled="true">Next</a>
            </div>

            <div id="escapeRoomError" class="alert alert-warning d-none" role="alert"
                style="padding: 8px 12px; font-size: 14px;"></div>
        </div>
    </div>
</div>

<style>
.custom-modal {
    background: linear-gradient(135deg, #0e0e0e, #1a1a1a);
    border-radius: 18px;
    overflow: hidden;
}

.custom-modal-header {
    background: rgba(0, 230, 246, 0.1);
    border-bottom: none;
    color: #00e6f6;
    text-align: center;
    font-weight: bold;
    letter-spacing: 1px;
}

.custom-modal-body {
    padding: 20px;
}

.modal-box {
    background: rgba(255, 255, 255, 0.05);
    padding: 15px;
    height: 100%;
}

.room-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.room-item {
    background: rgba(255, 255, 255, 0.08);
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.room-item:hover {
    background: rgba(0, 230, 246, 0.15);
}

.room-item input {
    accent-color: #00e6f6;
    transform: scale(1.2);
}

#selectionTextarea {
    width: 100%;
    min-height: 180px;
    background: #111;
    border: 1px solid #00e6f6;
    color: #fff;
    padding: 10px;
    border-radius: 6px;
    resize: none;
}

.all_button_main_header {
    /*display: flex;*/
    /*justify-content: center;*/
    /*gap: 15px;*/
    /*padding: 15px;*/
    /*background: rgba(0, 230, 246, 0.05);*/
}

.bg_bnt_custom {
    padding: 8px 20px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
}

.bg_bnt_custom_tran {
    border: 1px solid #00e6f6;
    color: #00e6f6;
    background: transparent;
}

.bg_bnt_custom {
    background-color: #00e6f6;
    color: #000;
}
</style>
<!-- JavaScript -->
<script>
(function() {
    console.log("Room selection + cart update script loaded ✅");

    let selected = [];

    // -----------------------------
    // 🛒 CART RELOAD FUNCTION
    // -----------------------------
    function refreshCartForSelection() {
        if (typeof window.loadCart === "function") {
            window.loadCart();
            return;
        }

        // Always reload from backend
        fetch("cart_view.php?live=1")
            .then(res => res.text())
            .then(html => {
                // Reset state if backend says cart is empty
                if (html.includes("data-totals") === false) {
                    const summary = document.getElementById("summary-output");
                    if (summary) {
                        summary.innerHTML = "<p class='text-center mt-3 text-white'>Your cart is empty.</p>";
                    }
                    document.querySelectorAll('.cart-count').forEach(badge => {
                        badge.textContent = "0";
                        badge.style.display = 'none';
                    });
                    document.querySelectorAll('.cartUrl').forEach(link => {
                        link.href = "<?= BASE_URL ?>booking?choose-experience";
                    });
                    return;
                }

                document.getElementById("summary-output").innerHTML = html;

                // ✅ Cart count for bundle offers
                const cartCount = document.querySelectorAll('#summary-output .summary-row-group').length;
                console.log('cartCounttt',cartCount);
                document.querySelectorAll('.cartUrl').forEach(link => {
                    link.href = cartCount > 0
                        ? "<?= BASE_URL ?>booking?customer-details"
                        : "<?= BASE_URL ?>booking?choose-experience";
                });
                if (typeof updateBundleOffers === "function") {
                    updateBundleOffers(cartCount);
                }

                // ✅ Update booked buttons
                fetch("get_cart.php")
                    .then(res => res.json())
                    .then(data => {
                        if (Array.isArray(data.cart) && typeof updateBookedButtons === "function") {
                            updateBookedButtons(data.cart);
                        }
                    });

                // ✅ Read totals directly from backend JSON
                const totalsDiv = document.getElementById("bookeo-totals");
                if (!totalsDiv) return;

                let totals = {};
                try {
                    totals = JSON.parse(totalsDiv.dataset.totals);
                    window.bookeoTotals = totals;
                } catch (err) {
                    console.error("Failed to parse totals JSON:", totalsDiv.dataset.totals, err);
                    return;
                }

                // ✅ Fill in frontend UI
                document.getElementById("subtotal").innerText =
                    "$" + Number(totals.subtotal).toFixed(2);

                let admTax = 0,
                    redTax = 0;
                if (Array.isArray(totals.taxes)) {
                    totals.taxes.forEach(t => {
                        if (t.label.includes("Admission")) admTax = t.amount;
                        if (t.label.includes("Redmond")) redTax = t.amount;
                    });
                }

                document.getElementById("admission-tax").innerText =
                    "$" + Number(admTax).toFixed(2);
                document.getElementById("redmond-tax").innerText =
                    "$" + Number(redTax).toFixed(2);

                document.getElementById("grand-total").innerText =
                    "$" + Number(totals.grandTotal).toFixed(2);

                if (totals.discount && totals.discount > 0) {
                    document.getElementById("discount-amount").innerText =
                        "-$" + Number(totals.discount).toFixed(2);
                    document.getElementById("discount-row").style.display = "grid";
                } else {
                    document.getElementById("discount-row").style.display = "none";
                }
            })
            .catch(err => console.error("Cart load error:", err));
    }

    // -----------------------------
    // 🏠 ROOM SELECTION FUNCTIONS
    // -----------------------------
    function renderSelection(textareaId) {
        const textarea = document.getElementById(textareaId);
        if (!textarea) return;
        textarea.value = selected.join("\n");
        validateNextButton();
    }

    function validateNextButton() {
        const nextBtn = document.getElementById("escape-selection");
        if (selected.length > 0) {
            nextBtn.classList.remove("disabled");
            nextBtn.removeAttribute("aria-disabled");
        } else {
            nextBtn.classList.add("disabled");
            nextBtn.setAttribute("aria-disabled", "true");
        }
    }

    function handleCheckboxChange(e, textareaId) {
        const el = e.target;
        if (!el.classList.contains('room-checkbox')) return;
        const value = el.value.trim();

        if (el.checked) {
            if (!selected.includes(value)) selected.push(value);
        } else {
            selected = selected.filter(v => v !== value);
        }

        renderSelection(textareaId);
    }

    function initRoomSelection(textareaId) {
        const checkboxes = document.querySelectorAll('.room-checkbox');
        if (!checkboxes.length) return;

        checkboxes.forEach(box => {
            box.addEventListener('change', function(e) {
                handleCheckboxChange(e, textareaId);
            });
        });

        // Initialize if any pre-checked
        selected = Array.from(document.querySelectorAll('.room-checkbox:checked'))
            .map(b => b.value.trim());
        renderSelection(textareaId);
    }

    // -----------------------------
    // 🚀 DOM READY
    // -----------------------------
    document.addEventListener('DOMContentLoaded', function() {
        initRoomSelection('selectionTextarea');

        const errorBox = document.getElementById("escapeRoomError");

        function showError(message) {
            errorBox.textContent = message;
            errorBox.classList.remove("d-none");
        }

        function hideError() {
            errorBox.textContent = "";
            errorBox.classList.add("d-none");
        }

        document.getElementById("escape-selection").addEventListener("click", function(e) {
            e.preventDefault();

            if (selected.length === 0) {
                showError("⚠️ Please select at least one escape room before continuing.");
                return;
            }

            hideError();

            const selectionText = document.getElementById("selectionTextarea").value.trim();
            const btn = this;

            btn.innerText = "Saving...";
            btn.classList.add("disabled");

            fetch("save_room_selection.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "selection=" + encodeURIComponent(selectionText)
            })
            .then(res => res.json())
            .then(data => {
                console.log("Save response:", data);

                if (data.status === "success") {
                    hideError();

                    // ✅ Prevent aria-hidden focus issue
                    btn.blur();

                    const modalEl = document.getElementById('partymodalform');
                    const modal = bootstrap.Modal.getInstance(modalEl);

                    if (modal) {
                        modal.hide();

                        // ✅ Ensure proper fade cleanup (modal + backdrop)
                        modalEl.addEventListener('hidden.bs.modal', () => {
                            modalEl.classList.remove('show');

                            document.querySelectorAll('.modal-backdrop.show').forEach(el => {
                                el.classList.remove('show');
                                setTimeout(() => el.remove(), 200);
                            });

                            // ✅ Reload cart before next step
                            refreshCartForSelection();

                            // ✅ Move to next step
                            if (typeof goToAddonsOrCustomer === "function") goToAddonsOrCustomer();
                        }, { once: true });
                    } else {
                        // Fallback if modal missing instance
                        refreshCartForSelection();
                        if (typeof goToAddonsOrCustomer === "function") goToAddonsOrCustomer();
                    }
                } else {
                    showError("❌ Something went wrong while saving selection.");
                    console.error(data);
                }
            })
            .catch(err => {
                showError("❌ Network error: " + err.message);
            })
            .finally(() => {
                btn.innerText = "Next";
                btn.classList.remove("disabled");
            });
        });
    });

    // Expose init function globally if needed
    window.initRoomSelection = initRoomSelection;
})();
</script>

<script>
$(document).on("change", ".qty-input", function () {
    let eventId = $(this).data("event");
    let gameId = $(this).data("game");
    let newQty = $(this).val();

    $.ajax({
        url: "update_qty_hold.php",
        type: "POST",
        data: {
            action: "update_qty",
            gameId: gameId,
            eventId: eventId,
            qty: newQty
        },
        success: function (response) {
            console.log("Hold Updated:", response);

            // Reload cart summary section only
            $("#summary-output").load("cart_view.php");
        }
    });
});
</script>
<script>
document.querySelector(".btn_apply").addEventListener("click", function () {
    const code = document.getElementById("giftCodeInput").value.trim();
    if (!code) { alert("Please enter a code"); return; }

    const btn = this;
    btn.innerText = "Applying...";
    btn.disabled = true;

    fetch("apply_code.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "code=" + encodeURIComponent(code)
    })
    .then(res => res.json())
    .then(data => {
        console.log("API Response:", data);
        
        // ✅ SESSION EXPIRED / CART EMPTY → REFRESH PAGE
        if (data.emptySession === true) {
            Toastify({
                text: "Session expired. Reloading page...",
                duration: 2000,
                backgroundColor: "red"
            }).showToast();
    
            setTimeout(() => {
                window.location.reload();
            }, 1500);
    
            return; // ⛔ stop further JS execution
        }
        
        // Hide Modal
        const modalEl = document.getElementById("input_voucher_code_new");
        const bsModal = bootstrap.Modal.getInstance(modalEl);
        if(bsModal) bsModal.hide();

        if (data.status === "success") {
            // Success Message
            Toastify({ text: data.message + " ✅", duration: 3000, backgroundColor: "green" }).showToast();
            
            // ⭐ CHECK IF WE HAVE A VALID USER CODE OR JUST AUTO PROMO
            if (data.valid_code && data.valid_code !== "") {
                // Show the specific valid code
                document.querySelector(".applied_code .code").textContent = 'Promotion: ' + data.valid_code;
                document.getElementById("giftCodeInput").value = data.valid_code;
                
                // Update the remove button to actually clear this specific code
                document.querySelector(".applied_code_remove").style.display = "block";
            } else {
                // "Success" but only because Auto-Promo worked (User input was garbage)
                // Revert UI Text to default or "Auto Applied"
                document.querySelector(".applied_code .code").textContent = "Have a promotion or voucher code?";
                
                // Optionally verify message matches
                if(data.message.includes("Invalid voucher ignored")) {
                     Toastify({ text: "Invalid code ignored (Auto-Promo kept)", duration: 4000, backgroundColor: "orange" }).showToast();
                }
            }
            
            document.querySelector(".applied_code").style.display = "block";
        } else {
            // Error Message
            Toastify({ text: data.message + " ❌", duration: 4000, backgroundColor: "red" }).showToast();
            
            // Revert UI Text
            document.querySelector(".applied_code .code").textContent = "Have a promotion or voucher code?";
        }

        // Reload Cart View
        $("#summary-output").load("cart_view.php");
    })
    .catch(err => {
        console.error("API Error:", err);
        alert("Network error occurred.");
    })
    .finally(() => {
        btn.innerText = "Apply";
        btn.disabled = false;
    });
});
</script>
<script>
document.querySelector(".applied_code_remove").addEventListener("click", function () {
    fetch("remove_code.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "action=remove_code"
    })
    .then(res => res.json())
    .then(data => {
        console.log("Remove Code Response:", data);

        const responseDiv = document.querySelector("#booking-response");
         const giftField = document.getElementById("giftCodeInput");
         $("#summary-output").load("cart_view.php");
        if (data.status === "success") {
        if (giftField) {
            giftField.value = "";
        }
            // Restore default promo text
            document.querySelector(".applied_code .code").textContent = "Have a promotion or voucher code?";
            
            // Keep the applied_code section visible
            document.querySelector(".applied_code").style.display = "block"; // or "flex" depending on your CSS
            
            // Show success message
            responseDiv.innerHTML = '<div class="alert alert-success small py-2 mb-2">Code removed successfully! </div>';
        } else {
            // Show error message
            responseDiv.innerHTML = '<div class="alert alert-danger small py-2 mb-2">Failed to remove code. Please try again.</div>';
        }

        // Auto-clear message after 4 seconds
        setTimeout(() => {
            responseDiv.innerHTML = "";
        }, 4000);
    })
    .catch(err => {
        console.error("Error removing code:", err);
        document.querySelector("#booking-response").innerHTML = 
            '<div class="alert alert-danger small py-2 mb-2">Network error. Please check your connection and try again.</div>';
        
        setTimeout(() => {
            document.querySelector("#booking-response").innerHTML = "";
        }, 5000);
    });
});
</script>
<script>
function toggleAddButtonForSelect(selectEl) {
    if (!selectEl) return;
    const card = selectEl.closest(".add_on_booking_card");
    if (!card) return;
    const btn = card.querySelector(".add-addon-btn");
    if (!btn) return;

    // treat empty string / non-numeric as 0
    const val = parseInt(selectEl.value) || 0;
    if (val > 0) {
        btn.style.display = "inline-block";
        btn.disabled = false;
        btn.style.opacity = "1";
    } else {
        btn.style.display = "none";
        btn.disabled = true;
        btn.style.opacity = "0.6";
    }
}

/* Set initial visibility for all selects inside a container (useful after inject) */
function setInitialVisibility(container = document) {
    container.querySelectorAll(".addon-dropdown").forEach(sel => {
        toggleAddButtonForSelect(sel);
    });
}

/* Central click handler for Add buttons (delegated) */
async function handleAddButtonClick(evt) {
    const btn = evt.target.closest(".add-addon-btn");
    if (!btn) return;

    evt.preventDefault();
    if (btn.dataset.loading === "1") return;

    const card = btn.closest(".add_on_booking_card");
    if (!card) return;

    const selectEl = card.querySelector(".addon-dropdown");
    const qty = parseInt(selectEl?.value) || 0;
    if (qty <= 0) {
        alert("Please select quantity.");
        return;
    }

    const addonName = card.querySelector(".add_on_booking_card_title")?.textContent?.trim() || "";
    const opt_id    = card.querySelector(".opt_id")?.textContent?.trim() || card.querySelector(".addon-dropdown")?.dataset.optId || "";
    const game_id    = card.querySelector(".game_id")?.textContent?.trim() || card.querySelector(".addon-dropdown")?.dataset.optId || "";
    const priceText = card.querySelector(".discounted-price")?.textContent || "";
    const addonPrice = parseFloat(priceText.replace("$", "").trim()) || 0;
    let currentCode = document.getElementById("giftCodeInput")?.value || "";

    const fd = new FormData();
    // POST parameter names expected by your PHP
    fd.append("product_code", game_id);         // if your PHP expects product_code use opt_id (or adjust)
    fd.append("addon_opt_id", opt_id);
    fd.append("addon_name", addonName);
    fd.append("addon_price", addonPrice);
    fd.append("qty", qty);

    try {
        btn.dataset.loading = "1";
        btn.disabled = true;
        const resp = await fetch("add_addon_to_cart.php", {
            method: "POST",
            body: fd
        });
        const json = await resp.json();

        if (json.success || json.status === "success") {
            const refreshResp = await fetch("apply_code.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "code=" + encodeURIComponent(currentCode)
            });
            const refreshJson = await refreshResp.json();
            if (refreshJson.status !== "success") {
                throw new Error(refreshJson.message || "Failed to refresh hold");
            }

            if (typeof loadCart === "function") loadCart();

            // If your flow expects to reload addons (e.g. availability changed),
            // uncomment next line to refresh addon list:
            // if (typeof loadAddons === "function") loadAddons();

            // Optionally give visual feedback
            btn.textContent = "Added";
            btn.disabled = true;
            setTimeout(() => {
                // restore label
                btn.textContent = "Add";
                toggleAddButtonForSelect(selectEl);
            }, 900);
        } else {
            alert(json.message || "Failed to add addon");
        }
    } catch (err) {
        console.error("Addons: AJAX error", err);
        alert(err.message || "Something went wrong while adding addon.");
    } finally {
        delete btn.dataset.loading;
    }
}

/* Delegated change handler for selects */
function delegatedSelectChangeHandler(evt) {
    const sel = evt.target;
    if (!sel.matches || !sel.matches(".addon-dropdown")) return;
    toggleAddButtonForSelect(sel);
}

/* Setup delegation and initial state */
document.addEventListener("DOMContentLoaded", function () {
    // Initial visibility for items already on page
    setInitialVisibility();

    // Delegated change event (works for dynamically added selects too)
    document.addEventListener("change", delegatedSelectChangeHandler, true);

    // Delegated click for add buttons (works for dynamically added buttons too)
    document.addEventListener("click", function (evt) {
        if (evt.target.closest && evt.target.closest(".add-addon-btn")) {
            handleAddButtonClick(evt);
        }
    }, true);
});

/* Replace loadAddons to ensure visibility is set after HTML injection.
   If you already have loadAddons declared elsewhere, update it to call setInitialVisibility()
   after replacing the .add_on_section HTML. Example replacement below (drop-in): */
function loadAddons() {
    fetch("load_addons.php")
        .then(res => res.text())
        .then(html => {
            const container = document.querySelector(".add_on_section");
            if (!container) return;
            container.innerHTML = html;
            
            document.querySelectorAll(".add_on_section [data-aos]").forEach(el => {
                el.removeAttribute("data-aos");
            });

            // initialize visibility for newly-injected content
            setInitialVisibility(container);
            // Note: no need to rebind event listeners thanks to delegation
        })
        .catch(err => console.error("loadAddons error:", err));
}


document.addEventListener("DOMContentLoaded", function () {
    return;

    document.querySelectorAll(".add-addon-btn").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();

            let card = this.closest(".add_on_booking_card");
            let qty = parseInt(card.querySelector(".addon-dropdown").value);
            let addonName = card.querySelector(".add_on_booking_card_title").textContent.trim();
            let opt_id = card.querySelector(".opt_id").textContent.trim();
            let game_id = card.querySelector(".game_id").textContent.trim();
            let addonPrice = parseFloat(card.querySelector(".discounted-price").textContent.replace("$", "").trim());

            let fd = new FormData();
            fd.append("addon_opt_id", opt_id);
            fd.append("product_code", game_id);  // ✔ correct
            fd.append("addon_name", addonName);
            fd.append("addon_price", addonPrice);
            fd.append("qty", qty);

            fetch("add_addon_to_cart.php", {
                method: "POST",
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    if (typeof loadCart === "function") loadCart();
                } else {
                    alert(res.message);
                }
            });
        });
    });

});

</script>



<?php include('includes/footer.php'); ?>

// <script>
//     function triggerOrder() {
//     changeStep(1);
//     setTimeout(() => location.reload(), 200);
// }
// </script>

<script>
const dropdown = document.getElementById('mobile-tab-dropdown');

if (dropdown) {
    dropdown.addEventListener('change', function() {
        const targetId = this.value; // e.g., "#tab-vr"
        const tabToClick = document.querySelector(`.custom-tab-nav .tab-item[data-bs-target="${targetId}"]`);
        if (tabToClick) {
            tabToClick.click();
            const hashName = tabToClick.getAttribute('data-hash'); 
            if (hashName) {
                history.replaceState(null, null, "#" + hashName);
            }
        }
    });
}
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {

    // Handle tab clicks
    document.querySelectorAll(".tab-item").forEach(tab => {
        tab.addEventListener("click", function () {
            let hash = this.getAttribute("data-hash");

            if (hash) {
                // Update URL with hash (without reloading)
                history.replaceState(null, null, "#" + hash);
            }
        });
    });

    // Load correct tab if URL already has #hash
    function activateTabFromHash() {
        let hash = window.location.hash.replace("#", "").trim();

        if (!hash) return;

        let tabToActivate = document.querySelector(`.tab-item[data-hash="${hash}"]`);

        if (tabToActivate) {
            // Remove previous active
            document.querySelectorAll(".tab-item").forEach(t => t.classList.remove("active"));
            document.querySelectorAll(".tab-pane").forEach(p => p.classList.remove("show", "active"));

            // Activate this tab
            tabToActivate.classList.add("active");

            let targetPane = tabToActivate.getAttribute("data-bs-target");
            document.querySelector(targetPane).classList.add("show", "active");
        }
    }

    // Run on page load
    activateTabFromHash();

});

document.addEventListener("DOMContentLoaded", function () {

    const tabItems = document.querySelectorAll(".tab-item");

    // Auto-set hash on first/default tab load
    function setDefaultTabHash() {
        if (!window.location.hash) {
            let firstTab = document.querySelector(".tab-item.active");
            if (firstTab) {
                let hash = firstTab.getAttribute("data-hash");
                let base = window.location.pathname;
                history.replaceState(null, null, base + "#" + hash);
            }
        }
    }

    // Call default hash setter BEFORE opening tab
    setDefaultTabHash();

    // Tab click handler
    tabItems.forEach(tab => {
        tab.addEventListener("click", function () {
            let hash = this.getAttribute("data-hash");
            if (hash) {
                let base = window.location.pathname;
                history.replaceState(null, null, base + "#" + hash);
            }
        });
    });

    // Open tab if URL already contains #hash
    function openTabFromHash() {
        let hash = window.location.hash.replace("#", "");
        if (!hash) return;

        let tab = document.querySelector(`.tab-item[data-hash="${hash}"]`);
        if (!tab) return;

        document.querySelectorAll(".tab-item").forEach(t => t.classList.remove("active"));
        document.querySelectorAll(".tab-pane").forEach(p => p.classList.remove("show", "active"));

        tab.classList.add("active");
        let pane = tab.getAttribute("data-bs-target");
        document.querySelector(pane).classList.add("show", "active");
    }

    openTabFromHash();
});

// document.addEventListener("DOMContentLoaded", function () {
//     const autoScrollButtons = document.querySelectorAll(
//         ".step-1-bnt_continue, .step-2-bnt_continue, .step-3-bnt_continue, .order_summart_main_button a"
//     );

//     autoScrollButtons.forEach(button => {
//         button.addEventListener("click", () => {
//             window.scrollTo({
//                 top: 0,
//                 behavior: "smooth"
//             });
//         });
//     });
// });

// custom scroll

document.addEventListener("DOMContentLoaded", function () {
    // All buttons / links jinke click par scroll karna hai
    const autoScrollButtons = document.querySelectorAll(
        ".custom_scroll"
    );

    // Target section (pehle jo milega wahi use hoga)
    const targetEl =
        document.getElementById("stepContents") ||
        document.getElementById("custom_scroll");

    autoScrollButtons.forEach(button => {
        button.addEventListener("click", function (e) {
            if (!targetEl) return;

            // Agar <a> tag hai to redirect rokne ke liye
            e.preventDefault();

            // Fixed header height (agar hai)
            const yOffset = -120; // header ke according adjust karein
            const y =
                targetEl.getBoundingClientRect().top +
                window.pageYOffset +
                yOffset;

            window.scrollTo({
                top: y,
                behavior: "smooth"
            });
        });
    });
});




</script>



<script>
document.addEventListener("shown.bs.modal", function (event) {

    const modal = event.target;
    if (!modal.classList.contains("videoModal_z")) return;

    const video = modal.querySelector("video");
    if (!video) return;
    video.currentTime = 0;
    video.muted = false;   
    video.play().catch(() => {
        console.log("Autoplay blocked by browser");
    });
});


document.addEventListener("hidden.bs.modal", function (event) {

    const modal = event.target;

    if (!modal.classList.contains("videoModal_z")) return;

    const video = modal.querySelector("video");
    if (!video) return;

    // Force stop
    video.pause();
    video.currentTime = 0;
    video.load(); // 🔥 VERY IMPORTANT
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll('.modal').forEach(function (modal) {

        modal.addEventListener('hidden.bs.modal', function () {

            const videos = modal.querySelectorAll('video');

            videos.forEach(function (video) {

                video.pause();
                video.currentTime = 0;

                // 🔥 HARD RESET (Safari / mobile fix)
                const source = video.querySelector('source');
                if (source) {
                    const src = source.getAttribute('src');
                    video.removeAttribute('src');
                    video.load();
                    video.setAttribute('src', src);
                }
            });

        });

    });

});


// --- Update Additional Guests Dynamically ---
$(document).on('change', '.update-additional-guest', function() {
    let cartId = $(this).data('cart-id');
    let newQty = $(this).val();
    let $select = $(this);

    // Optional: Visual feedback (disable input while loading)
    $select.prop('disabled', true);

    $.ajax({
        url: 'update_additional_guests.php',
        type: 'POST',
        data: {
            cart_id: cartId,
            qty: newQty
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                // Reload the cart view to update totals and display
                $("#summary-output").load("cart_view.php", function() {
                    // Optional: Toast message
                    // Toastify({ text: "Guest count updated", duration: 2000, backgroundColor: "#00d4ff" }).showToast();
                });
            } else {
                alert("Failed to update guest count.");
                $select.prop('disabled', false); // Re-enable on error
            }
        },
        error: function() {
            alert("Network error.");
            $select.prop('disabled', false);
        }
    });
});

// --- Remove Addon Script ---
// --- Remove Addon Script ---
$(document).on('click', '.remove-addon-btn', function() {
    let cartId = $(this).data('cart-id');

    // Show modal in center
    $('#deleteAddonModal').css('display', 'flex').hide().fadeIn();

    // Remove previous click handlers
    $('#confirmDeleteAddonBtn').off('click');
    $('#cancelDeleteAddonBtn').off('click');

    $('#confirmDeleteAddonBtn').on('click', function() {
        $('#deleteAddonActions').hide();
        $('#deleteAddonLoading').css('display', 'flex'); // Spinner shows

        $.ajax({
            url: 'remove_addon.php',
            type: 'POST',
            data: { cart_id: cartId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $("#summary-output").load("cart_view.php", function() {
                        $('#deleteAddonModal').fadeOut();
                        $('#deleteAddonActions').show();
                        $('#deleteAddonLoading').hide(); // Spinner hides
                    });
                } else {
                    alert("Failed to remove addon.");
                    $('#deleteAddonActions').show();
                    $('#deleteAddonLoading').hide();
                }
            },
            error: function() {
                alert("Network error.");
                $('#deleteAddonActions').show();
                $('#deleteAddonLoading').hide();
            }
        });
    });

    $('#cancelDeleteAddonBtn').on('click', function() {
        $('#deleteAddonModal').fadeOut();
    });
});

// Remove additional guest

// JavaScript to trigger the modal and delete additional guests
$(document).on('click', '.remove-additional-guest-btn', function() {
    let cartId = $(this).data('cart-id'); // Get the cart ID from the data attribute

    // Show modal in center
    $('#deleteAdditionalGuestModal').css('display', 'flex').hide().fadeIn();

    // Remove previous click handlers (to avoid binding multiple events)
    $('#confirmDeleteAdditionalGuestBtn').off('click');
    $('#cancelDeleteAdditionalGuestBtn').off('click');

    $('#confirmDeleteAdditionalGuestBtn').on('click', function() {
        $('#deleteAdditionalGuestActions').hide();
        $('#deleteAdditionalGuestLoading').css('display', 'flex'); // Show spinner

        $.ajax({
            url: 'remove_additional_guest.php',  // PHP script to handle additional guest removal
            type: 'POST',
            data: { cart_id: cartId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Reload the cart view with the updated data
                    $("#summary-output").load("cart_view.php", function() {
                        $('#deleteAdditionalGuestModal').fadeOut();
                        $('#deleteAdditionalGuestActions').show();
                        $('#deleteAdditionalGuestLoading').hide(); // Hide spinner
                    });
                } else {
                    alert("Failed to remove additional guest.");
                    $('#deleteAdditionalGuestActions').show();
                    $('#deleteAdditionalGuestLoading').hide();
                }
            },
            error: function() {
                alert("Network error.");
                $('#deleteAdditionalGuestActions').show();
                $('#deleteAdditionalGuestLoading').hide();
            }
        });
    });

    $('#cancelDeleteAdditionalGuestBtn').on('click', function() {
        $('#deleteAdditionalGuestModal').fadeOut();
    });
});


// $(document).on('click', '.remove-addon-btn', function() {
//     let cartId = $(this).data('cart-id');
//     let $btn = $(this);

//     // Confirmation (Optional - remove if you want instant delete)
//     if(!confirm("Are you sure you want to remove this addon?")) return;

//     // Visual feedback
//     $btn.html('<i class="fa-solid fa-spinner fa-spin"></i>'); 

//     $.ajax({
//         url: 'remove_addon.php',
//         type: 'POST',
//         data: { cart_id: cartId },
//         dataType: 'json',
//         success: function(response) {
//             if (response.status === 'success') {
//                 // Reload cart to update totals and remove the row
//                 $("#summary-output").load("cart_view.php");
//             } else {
//                 alert("Failed to remove addon.");
//                 $btn.html('<i class="fa-solid fa-trash-can"></i>'); // Reset icon
//             }
//         },
//         error: function() {
//             alert("Network error.");
//             $btn.html('<i class="fa-solid fa-trash-can"></i>');
//         }
//     });
// });
</script>
