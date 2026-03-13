<?php
session_start();
include('link.php');

// Yeh file $pdo PDO object define karti hai
include('admin/db.php'); // adjust path if needed
$stmt = $pdo->prepare("SELECT * FROM cms_contact_page WHERE id = 1");
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Fallback agar DB me data na mile
$page_title       = $data['page_title'] ?? 'Contact Us | Flee Escape';
$meta_keywords    = $data['meta_keywords'] ?? 'escape room Redmond, VR games Redmond, Zero Latency VR Seattle, Flee Escape Rooms, corporate team building Redmond, birthday party venue Redmond, VR party Redmond, escape room contact, things to do in Redmond WA';
$meta_description = $data['meta_description'] ?? 'Contact Flee Escape Rooms & Zero Latency VR Seattle in Redmond, WA for escape rooms, VR games, birthday parties, and corporate team events.';
$banner_heading   = $data['banner_heading'] ?? 'Contact Flee Escape Rooms and Zero Latency VR';
$banner_text      = $data['banner_text'] ?? 'Get in Touch for Bookings, Birthday Parties, and Corporate Team Events in Redmond, WA';

$canonicalURL = $link . "contact-us";

include('includes/header.php');
?>

<!-- Dynamic Meta Tags -->
<title><?= htmlspecialchars($page_title) ?></title>
<meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
<meta name="keywords" content="<?= htmlspecialchars($meta_keywords) ?>">

<section>
    <div class="vr_page_banner all_baneer_IMG"
        style="background-image: ur[](https://images.squarespace-cdn.com/content/v1/55e536b5e4b00e62524eaf0b/1596584853106-A14VM64KB3SHZZ6BO6QB/Line+Black+Map+background.jpg?format=2500w); height:450px">
        <div class="container">
            <div class="vr_page_banner_content" style="position: relative;z-index: 1;">
                <h1><?= htmlspecialchars($banner_heading) ?></h1>
                <h3 style="color: #fff; margin: 15px 0; font-size: 1.6rem;"><?= htmlspecialchars($banner_text) ?></h3>
                <!--<p>Greater Seattle's premier entertainment destination for escape rooms and VR gaming adventures. Winner-->
                <!--    of Best in the PNW for <br> 'Silver: Best Escape Room' and 'Bronze: Best Children's Birthday Party-->
                <!--    Venue'.</p>-->
                <!--<small>-->
                <!--    Perfect for birthday parties, corporate team building, family fun, and date nights. <br> Can you-->
                <!--    solve the puzzles and escape in time?-->
                <!--</small>-->
            </div>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <div class="gaming-container" style="background: transparent; padding:0">
            <ul class="nav nav-tabs" id="gamingTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link" onclick="window.location.href='booking'">
                        Book Your Adventure
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" onclick="window.location.href='indoor-real-life-escape-games'">
                        View Escape Rooms
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" onclick="window.location.href='vr-games-at-flee-escape-vr-games'">
                        View VR Games
                    </button>
                </li>
            </ul>
        </div>
    </div>
</section>

<section class="contact_form">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="contact_data_list">
                    <div class="contact_data_list_heading">
                        <h4><span style="color: #fff;"> CONTACT THE </span> GAME MASTERS</h4>
                        <p>Ready for escape rooms and VR adventures? Our experts are here to help you plan the perfect
                            experience for birthday parties, corporate events, family outings, or date nights.</p>
                    </div>
                    <div class="contact_data_list_content">
                        <div class="contact_data_list_box">
                            <div class="contact_data_list_box_items">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-mail h-6 w-6 text-accent">
                                    <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                </svg>
                            </div>
                            <div class="">
                                <h6>Game Master HQ</h6>
                                <p>Direct line to our puzzle masters</p>
                                <a href="mailto:info@fleeescape.com">info@fleeescape.com</a>
                            </div>
                        </div>
                        <div class="contact_data_list_box">
                            <div class="contact_data_list_box_items">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-phone h-6 w-6 text-accent-secondary">
                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                    </path>
                                </svg>
                            </div>
                            <div class="">
                                <h6>Emergency Hotline</h6>
                                <p>Need a hint? Call during business hours</p>
                                <a href="tel:4252871426">425-287-1426</a>
                            </div>
                        </div>
                        <div class="contact_data_list_box">
                            <div class="contact_data_list_box_items">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-clock h-6 w-6 text-accent">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                            </div>
                            <div class="">
                                <h6>Operating Hours</h6>
                                <p>When the puzzles are waiting</p>
                                <ul class="time_contact_list">
                                    <li>Monday - Thursday: 1:00 PM - 9:00 PM</li>
                                    <li>Friday: 1:00 PM - 10:00 PM</li>
                                    <li>Saturday - Sunday: 12:00 PM - 10:00 PM</li>
                                </ul>
                                <small>Late night escapes available!</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="contact_data_list_heading">
                    <h4><span style="color: #fff;">BOOK YOUR</span> ESCAPE</h4>
                    <p>Ready to test your puzzle-solving skills? Fill out the form below and our Game Masters will
                        prepare the perfect escape room or VR challenge for your team.</p>
                </div>
                <div class="contact_data_list_form">
                    <form id="escape-form">
                        <div class="input-group">
                            <label for="first_name" class="required-label">First Name</label>
                            <input type="text" name="first_name" placeholder="Enter your name" >
                            <small class="error-msg" data-error="first_name"></small>
                        </div>
                        <div class="input-group">
                            <label for="last_name" class="required-label">Last Name</label>
                            <input type="text" name="last_name" placeholder="Your Last Name" >
                            <small class="error-msg" data-error="last_name"></small>
                        </div>
                        <div class="input-group">
                            <label for="email" class="required-label">Email Address</label>
                            <input type="text" name="email" placeholder="your.email@domain.com">
                            <small class="error-msg" data-error="email"></small>
                        </div>
                        <div class="input-group">
                            <label for="mobile" class="required-label">Mobile Number</label>
                            <input type="tel" name="mobile" placeholder="Enter your mobile number" maxlength="10"
                                   pattern="[0-9]{10}"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                            <small class="error-msg" data-error="mobile"></small>
                        </div>
                        <div class="input-group">
                            <label for="subject" class="required-label">Inquiry Type</label>
                            <select name="subject" class="neon-select" >
                                <option value="" disabled selected hidden>Select Inquiry Category</option>
                                <option value="escape-room">Escape Room booking</option>
                                <option value="VR-gamest"> VR games</option>
                                <option value="birthday-party">Birthday Party</option>
                                <option value="corporate">Corporate team building</option>
                                <option value="private-room">Cancellations and Refunds</option>
                                <option value="gift-certificate">Other Inquiry</option>
                            </select>
                            <small class="error-msg" data-error="subject"></small>
                        </div>
                        <div class="input-group">
                            <label for="details" class="required-label">Inquiry Details</label>
                            <textarea name="details" placeholder="Describe your inquiry..."></textarea>
                            <small class="error-msg" data-error="details"></small>
                        </div>
                        <button type="submit" class="bg_bnt_custom">Submit Inquiry</button>
                    </form>
                    <!-- Placeholder for messages -->
                    <div id="form-message"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="stats-section">
    <div class="stat-card">
        <div class="stat-icon rooms"></div>
        <div class="stat-content">
            <span class="stat-number">20+</span>
            <span class="stat-label">Rooms & VR Games</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon awards"></div>
        <div class="stat-content">
            <span class="stat-number">2</span>
            <span class="stat-label">PNW Awards Won</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon time"></div>
        <div class="stat-content">
            <span class="stat-number">45min</span>
            <span class="stat-label">Avg. Escape Time</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon guests"></div>
        <div class="stat-content">
            <span class="stat-number">10K+</span>
            <span class="stat-label">Happy Guests</span>
        </div>
    </div>
</div>

<section>
    <div class="container">
        <div class="award-section">
            <div class="award-icon">
                🏆
            </div>
            <div class="award-text">
                <h3 class="award-title">Award-Winning Entertainment</h3>
                <ul class="award-list">
                    <li>
                        <span class="award-medal silver">Silver</span>
                        Best in the PNW - Best Escape Room
                    </li>
                    <li>
                        <span class="award-medal bronze">Bronze</span>
                        Best in the PNW - Best Children's Birthday Party Venue
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
.error-msg {
    color: red;
    font-size: 13px;
    margin-top: 8px;
    display: block;
}
input::placeholder,
textarea::placeholder,
.neon-select:invalid{
    color: #aaa!important;
    font-size: 13px;
    letter-spacing: 0.5px;
}
.neon-select option {
    color: #aaa!important;
}
.required-label::after {
    content: " *";
    color: #aaa;
    font-weight: bold;
}
</style>

<script>
$("#escape-form").on("submit", function(e){
    e.preventDefault();
    $(".error-msg").text("");
    let valid = true;
    let form = $(this);
    let first_name = form.find("input[name='first_name']").val().trim();
    let last_name = form.find("input[name='last_name']").val().trim();
    let email = form.find("input[name='email']").val().trim().replace(/\s+/g, "");
   
    let mobile = form.find("input[name='mobile']").val().trim();
    let subject = form.find("select[name='subject']").val();
    let details = form.find("textarea[name='details']").val().trim();
    $('input[name="email"]').val(email);

    if(first_name === ""){
        $('small[data-error="first_name"]').text("First name is required");
        valid = false;
    }
    if(last_name === ""){
        $('small[data-error="last_name"]').text("Last name is required");
        valid = false;
    }
    if(email === ""){
        $('small[data-error="email"]').text("Email is required");
        valid = false;
    } else {
        let emailFormat = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;
        if(!emailFormat.test(email)){
            $('small[data-error="email"]').text("Enter a valid email address");
            valid = false;
        }
    }
    if(mobile === ""){
        $('small[data-error="mobile"]').text("Mobile number is required");
        valid = false;
    } else {
        let cleanedMobile = mobile.replace(/\D/g, '');
        if(cleanedMobile.length < 10 || cleanedMobile.length > 15){
            $('small[data-error="mobile"]').text("Enter a valid mobile number");
            valid = false;
        }
    }
    if(!subject){
        $('small[data-error="subject"]').text("Inquiry Type is required");
        valid = false;
    }
    if(details === ""){
        $('small[data-error="details"]').text("Inquiry details are required");
        valid = false;
    }
    if(!valid) return;

    let formData = form.serialize();
    $.ajax({
        type: "POST",
        url: "booking-ajax.php",
        data: formData,
        dataType: "json",
        success: function(response){
            if(response.status === "success"){
                Swal.fire({
                    icon: 'success',
                    title: 'Inquiry Sent!',
                    text: response.message,
                    toast: true,
                    position: 'top-end',
                    timer: 2000,
                    showConfirmButton: false
                });
                form[0].reset();
            }
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php include('includes/footer.php'); ?>