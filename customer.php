<?php
$pageTitle = 'Customer ';
include('includes/header.php');
?>


<div class="container">
    <div class="user_after_login_container">
        <div class="text-center">
            <h1 class="user_after_login_heading">Hi Fleeescape !</h1>
            <div class="user_after_login_subheading">Please select one of the actions below</div>
        </div>
        <div class="user_after_login_grid">
            <a href="booking.php" class="user_after_login_card">
                <span class="user_after_login_icon">📅</span>
                <div>
                    <div class="user_after_login_card_title">New booking</div>
                    <div class="user_after_login_card_desc">Make a new booking.</div>
                </div>
            </a>
            <a href="your-booking.php" class="user_after_login_card">
                <span class="user_after_login_icon">🗓️</span>
                <div>
                    <div class="user_after_login_card_title">Your bookings</div>
                    <div class="user_after_login_card_desc">View and manage your existing bookings.</div>
                </div>
            </a>
            <a href="Purchase-a-gift-voucher.php" class="user_after_login_card">
                <span class="user_after_login_icon">🎁</span>
                <div>
                    <div class="user_after_login_card_title">Purchase a gift voucher</div>
                    <div class="user_after_login_card_desc">Purchase a gift voucher for a loved one.</div>
                </div>
            </a>
            <a href="profile.php" class="user_after_login_card">
                <span class="user_after_login_icon">👤</span>
                <div>
                    <div class="user_after_login_card_title">Your profile</div>
                    <div class="user_after_login_card_desc">Change your contact details and password.</div>
                </div>
            </a>
            <div class="user_after_login_card user_after_login_single_card">
                <span class="user_after_login_icon">↩️</span>
                <div>
                    <div class="user_after_login_card_title">Sign out</div>
                    <div class="user_after_login_card_desc">Leave the customer area.</div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include('includes/footer.php'); ?>