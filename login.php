<?php
$pageTitle = 'Login / Signup';
include('includes/header.php');
?>

<div class="login_page">
    <div class="login_page_container">
        <div class="login_data_row">

            <!-- Left Welcome Section -->
            <div class="login_data_row_box welcome_box"
                style="background-image:url('assets/images/fleeescape_img/banner/new/VR-Package-Image.jpg');">
                <div class="welcome_text">
                    <h1>Welcome To</h1>
                    <img src="assets/images/logo.png" alt="Logo" class="login-logo">
                    <p>Experience thrilling escape adventures <br> like never before!</p>
                </div>
                <div class="login_line_over">
                    <img src="img/login_line_bg.png" alt="">
                </div>
            </div>

            <!-- Signup Form -->
            <div class="login_data_row_box form_box" id="signupForm" style="display:none;">
                <div class="login_form_heading">
                    <h2>Create Your Account</h2>
                </div>
                <div class="heading_underline"></div>
             <form method="post" id="sign-up-form">
    <div class="input_group">
        <label for="signupFirstName">First Name</label>
        <input id="signupFirstName" type="text" placeholder="Enter your first name" />
    </div>

    <div class="input_group">
        <label for="signupLastName">Last Name</label>
        <input id="signupLastName" type="text" placeholder="Enter your last name" />
    </div>

    <div class="input_group">
        <label for="signupEmail">Email</label>
        <input id="signupEmail" type="email" placeholder="your.email@domain.com" />
    </div>

    <div class="input_group">
        <label for="signupPassword">Password</label>
        <input id="signupPassword" type="password" placeholder="Enter your password" />
        <i class="fa-solid fa-eye input_group__icon" onclick="togglePassword('signupPassword', this)"></i>
    </div>
 <div class="input_group">
        <label for="signupPhone">Phone Number</label>
        <input id="signupPhone" type="text" placeholder="Enter your phone number" />
    </div>

    <div class="input_group">
        <label for="phoneType">Phone Type</label>
        <select id="phoneType">
            <option value="mobile" selected>mobile</option>
            <option value="work">work</option>
            <option value="home">home</option>
        </select>
    </div>
    <div class="login-check">
        <input type="checkbox" id="signupcheck"> By signing up I agree with
        <a href="#" target="_blank">Terms and Conditions</a>
    </div>

    <div class="sign-btn">
        <button type="button" id="sign-up" class="bg_bnt_custom w-100">Sign Up</button>
    </div>

    <div class="login-already">
        Already have an account?
        <button type="button" class="already-login" onclick="showLogin()">Login</button>
    </div>
</form>

<div id="signup-message" style="margin-top:15px;"></div>
            </div>

            <!-- Login Form -->
            <div class="login_data_row_box form_box" id="loginForm" style="display:block;">
                <div class="login_form_heading">
                    <h2>Login</h2>
                </div>
                <div class="heading_underline"></div>
             <form id="login-form">
    <div class="input_group">
        <label for="loginEmail">Email</label>
        <input id="loginEmail" type="email" placeholder="Enter your email" />
    </div>

    <div class="input_group">
        <label for="loginPassword">Password</label>
        <input id="loginPassword" type="password" placeholder="Enter your password" />
        <i class="fa-solid fa-eye input_group__icon"
            onclick="togglePassword('loginPassword', this)"></i>
    </div>

    <div class="login-check">
        <input type="checkbox"> Remember me
    </div>

    <button type="button" class="form-h6" onclick="showForgotPass()">Forgot Password?</button>

    <div class="sign-btn">
        <button type="button" id="login-btn" class="bg_bnt_custom w-100">Login</button>
    </div>

    <div class="login-already">
        New here?
        <button type="button" class="already-login" onclick="showSignup()">Create Account</button>
    </div>

    <div id="login-message" style="margin-top:10px; font-weight:500;"></div>
</form>
            </div>

            <!-- Forgot Password Form -->
            <div class="login_data_row_box form_box" id="ForgotPassForm" style="display:none;">
                <div class="login_form_heading">
                    <h2>Forgot Password</h2>
                </div>
                <div class="heading_underline"></div>
                <form>
                    <div class="input_group">
                        <label for="forgotEmail">Email</label>
                        <input id="forgotEmail" type="email" placeholder="your.email@domain.com" />
                    </div>
                    <div class="sign-btn">
                        <button type="button" class="bg_bnt_custom w-100">Submit</button>
                    </div>
                    <div class="login-already">
                        Remembered your password?
                        <button type="button" class="already-login" onclick="showLogin()">Login</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
function showLogin() {
    document.getElementById("signupForm").style.display = "none";
    document.getElementById("ForgotPassForm").style.display = "none";
    document.getElementById("loginForm").style.display = "block";
}

function showSignup() {
    document.getElementById("loginForm").style.display = "none";
    document.getElementById("ForgotPassForm").style.display = "none";
    document.getElementById("signupForm").style.display = "block";
}

function showForgotPass() {
    document.getElementById("loginForm").style.display = "none";
    document.getElementById("signupForm").style.display = "none";
    document.getElementById("ForgotPassForm").style.display = "block";
}

function togglePassword(fieldId, icon) {
    const input = document.getElementById(fieldId);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$("#sign-up").on("click", function () {
    const firstName = $("#signupFirstName").val().trim();
    const lastName = $("#signupLastName").val().trim();
    const email = $("#signupEmail").val().trim();
    const password = $("#signupPassword").val().trim();
    const phone = $("#signupPhone").val().trim();
    const phoneType = $("#phoneType").val();
    const agree = $("#signupcheck").is(":checked");
    const msgBox = $("#signup-message");

    msgBox.html(""); // clear any previous message

    if (!firstName || !lastName || !email || !password || !phone) {
        msgBox.html('<div style="color:red;">Please fill all required fields.</div>');
        return;
    }

    if (!agree) {
        msgBox.html('<div style="color:red;">Please agree to the terms and conditions.</div>');
        return;
    }

    $.ajax({
        url: "register_bookeo.php",
        type: "POST",
        data: {
            firstName,
            lastName,
            email,
            password,
            phone,
            phoneType
        },
        dataType: "json",
        beforeSend: function () {
            $("#sign-up").prop("disabled", true).text("Processing...");
        },
        success: function (response) {
            if (response.success) {
                msgBox.html('<div style="color:green;">✅ Registration successful! Welcome aboard.</div>');
                $("#sign-up-form")[0].reset(); // clear form only on success
            } else {
                msgBox.html('<div style="color:red;">❌ ' + (response.message || 'Registration failed. Please try again.') + '</div>');
            }
        },
        error: function (xhr, status, error) {
            console.error(error);
            msgBox.html('<div style="color:red;">⚠️ Something went wrong. Please try again later.</div>');
        },
        complete: function () {
            $("#sign-up").prop("disabled", false).text("Sign Up");
        }
    });
});
</script>
<script>
document.getElementById("login-btn").addEventListener("click", function () {
    const email = document.getElementById("loginEmail").value.trim();
    const password = document.getElementById("loginPassword").value.trim();
    const msgBox = document.getElementById("login-message");

    if (!email || !password) {
        msgBox.innerHTML = "<span style='color:red;'>Please fill all fields.</span>";
        return;
    }

    msgBox.innerHTML = "<span style='color:blue;'>Checking credentials...</span>";

    fetch("check_login.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ email, password })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            msgBox.innerHTML = `<span style='color:green;'>${data.message}</span>`;
            document.getElementById("login-form").reset();
        } else {
            msgBox.innerHTML = `<span style='color:red;'>${data.message}</span>`;
        }
    })
    .catch(err => {
        msgBox.innerHTML = `<span style='color:red;'>Error: ${err.message}</span>`;
    });
});
</script>
<?php include('includes/footer.php'); ?>