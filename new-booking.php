<?php
$pageTitle = 'Your bookings';
include('includes/header.php');
?>

<div class="user_after_login_next_section">
    <div class="">
        <h1 class="user_after_login_heading">Your bookings</h1>
        <div class="user_after_login_subheading">No upcoming bookings found.</div>

        <div class="all_button_main_header" style="margin: 0;">
            <a href="#" class="bg_bnt_custom bg_bnt_custom_tran" onclick="history.back(); return false;"><i
                    class="fa-solid fa-arrow-left"></i> Back</a>
            <a href="booking.php" class="bg_bnt_custom">Past bookings</a>
        </div>
    </div>
</div>

<style>
header,
footer {
    display: none;
}

.user_after_login_next_section {
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100vh;
}

.user_after_login_heading {
    color: #00d4ff;
    font-size: 2.5em;
    font-weight: 700;
    margin-bottom: 18px;
}

.user_after_login_subheading {
    color: #fff;
    font-size: 1.18em;
    /* margin-bottom: 38px; */
    font-weight: 500;
}
</style>


<?php include('includes/footer.php'); ?>