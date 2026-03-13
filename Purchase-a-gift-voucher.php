<?php
$pageTitle = 'Purchase a gift voucher';
include('includes/header.php');
?>

<section class="voucher-section">
    <div class="">
        <div class="voucher-heading">
            <h2>🎁 Purchase a gift voucher</h2>
            <p>Please select the type of reward you want to explore</p>
        </div>
        <div class="voucher-container">
            <!-- Left Card -->
            <div class="voucher-card">
                <div class="voucher-icon">🎁</div>
                <h4 class="voucher-title">Generic gift voucher</h4>
                <p class="voucher-text">
                    Purchase a gift voucher for a given amount, which can be used for any future booking.
                </p>
            </div>

            <!-- Right Card -->
            <div class="voucher-card">
                <div class="voucher-icon">⭐</div>
                <h4 class="voucher-title">Specific gift voucher</h4>
                <p class="voucher-text"> Purchase a gift voucher for a specific game and number of people. <br> The
                    recipient will only need to pick a date and time, and will never see the
                    price.
                </p>
            </div>
        </div>
        <div class="all_button_main_header" style="margin: 0;">
            <a href="#" class="bg_bnt_custom bg_bnt_custom_tran" onclick="history.back(); return false;"><i
                    class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
    </div>
</section>


<?php include('includes/footer.php'); ?>