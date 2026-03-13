<?php
$pageTitle = 'Profile';
include('includes/header.php');
?>

<div class="profile-page">
    <div class="profile-card">
        <h1>👤 Your Profile</h1>

        <form action="" class="profile-form">
            <div class="form-grid">

                <!-- Left Column -->
                <div class="form-column">
                    <div class="form-section">
                        <h2>Personal Information</h2>
                        <div class="input-group">
                            <label>First Name</label>
                            <input type="text" placeholder="Enter your name">
                        </div>
                        <div class="input-group">
                            <label>Last Name</label>
                            <input type="text" placeholder="Your last name">
                        </div>
                    </div>
                    <div class="form-section">
                        <h2>Preferences</h2>
                        <div class="input-group">
                            <label>Preferred Language</label>
                            <select required>
                                <option value="" disabled selected hidden>Select language</option>
                                <option>English</option>
                                <option>Hindi</option>
                                <option>Spanish</option>
                                <option>French</option>
                            </select>
                        </div>
                    </div>

                </div>

                <!-- Right Column -->
                <div class="form-column">

                    <div class="form-section">
                        <h2>Contact Details</h2>
                        <div class="input-group">
                            <label>Email</label>
                            <input type="email" placeholder="Enter email">
                        </div>
                        <div class="input-group">
                            <label>Phone</label>
                            <input type="tel" placeholder="Phone number">
                        </div>
                    </div>

                    <!-- Password Section -->
                    <div class="form-section">
                        <p class="btn-password-toggle" onclick="togglePasswordSection()">Change
                            Password <i class="fa-solid fa-angle-down"></i></p>

                        <div class="password-section" id="passwordSection">
                            <h2>Change Password</h2>
                            <div class="input-group password-group">
                                <label>Current Password</label>
                                <input type="password" placeholder="Current password" id="currentPassword">
                                <span class="toggle-pass" onclick="togglePassword('currentPassword', this)"><i
                                        class="fa-solid fa-eye"></i></span>
                            </div>
                            <div class="input-group password-group">
                                <label>New Password</label>
                                <input type="password" placeholder="New password" id="newPassword">
                                <span class="toggle-pass" onclick="togglePassword('newPassword', this)"><i
                                        class="fa-solid fa-eye"></i></span>
                            </div>
                            <div class="input-group password-group">
                                <label>Confirm New Password</label>
                                <input type="password" placeholder="Confirm new password" id="confirmPassword">
                                <span class="toggle-pass" onclick="togglePassword('confirmPassword', this)"><i
                                        class="fa-solid fa-eye"></i></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="all_button_main_header" style="margin: 0;">
                <a href="#" class="bg_bnt_custom bg_bnt_custom_tran" onclick="history.back(); return false;"><i
                        class="fa-solid fa-arrow-left"></i> Back</a>
                <a href="booking.php" class="bg_bnt_custom">Save Profile</a>
            </div>
        </form>
    </div>
</div>


<script>
function togglePasswordSection() {
    let section = document.getElementById('passwordSection');
    section.style.display = section.style.display === 'block' ? 'none' : 'block';
}

function togglePassword(fieldId, icon) {
    let input = document.getElementById(fieldId);
    if (input.type === "password") {
        input.type = "text";
        icon.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
    } else {
        input.type = "password";
        icon.innerHTML = '<i class="fa-solid fa-eye"></i>';
    }
}
</script>

<?php include('includes/footer.php'); ?>