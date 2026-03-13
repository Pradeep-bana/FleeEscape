<!DOCTYPE html>
<html lang="en">

<head>
    <title>Flee Escape - Game </title>
    <link rel="icon" href="assets/images/icon.png" type="image/gif" sizes="16x16">
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <!-- CSS Files
    ================================================== -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" id="bootstrap">
    <link href="assets/css/plugins.css" rel="stylesheet" type="text/css">
    <link href="assets/css/swiper.css" rel="stylesheet" type="text/css">
    <link href="assets/css/style.css" rel="stylesheet" type="text/css">
    <link href="assets/css/demo.css" rel="stylesheet" type="text/css">
    <link href="assets/css/responsive.css" rel="stylesheet" type="text/css">
    <link href="assets/css/coloring.css" rel="stylesheet" type="text/css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/escape-room.css" rel="stylesheet" type="text/css">
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- color scheme -->
    <link id="colors" href="assets/css/colors/scheme-03.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body class="dark-scheme">
    <div id="wrapper">
        <div class="float-text show-on-scroll">
            <span><a href="#">Scroll to top</a></span>
        </div>
        <div class="scrollbar-v show-on-scroll"></div>
        <!-- page preloader begin -->
        <div id="de-loader"></div>
        <!-- page preloader close -->

        <!-- header begin -->
        <header class="transparent">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="de-flex sm-pt10">
                            <div class="de-flex-col">
                                <div class="de-flex-col">
                                    <!-- logo begin -->
                                    <div id="logo">
                                        <a href="index.php">
                                            <img class="logo-main" src="assets/images/logo.png" alt="" loading="lazy">
                                            <img class="second_logo_main" src="assets/images/ZL_Logo.png" alt=""
                                                loading="lazy">
                                            <img class="logo-mobile" src="assets/images/logo-mobile.png" alt=""
                                                loading="lazy">
                                        </a>
                                    </div>
                                    <!-- logo close -->
                                </div>
                            </div>
                            <div class="de-flex-col header-col-mid">
                                <ul id="mainmenu">
                                    <li>
                                        <a class="menu-item" href="index.php">Home</a>

                                    </li>
                                    <li>
                                        <a class="menu-item" href="coming-soon.php">Games</a>

                                    </li>
                                    <li>
                                        <a class="menu-item" href="coming-soon.php">Support</a>

                                    </li>
                                    <li>

                                        <a class="menu-item" href="coming-soon.php">FAQ</a>

                                    </li>
                                    <li>
                                        <a class="menu-item" href="coming-soon.php">About Us</a>

                                    </li>
                                    <li><a class="menu-item" href="coming-soon.php">Contact Us</a>
                                        <!-- <ul>
                                            <li><a class="menu-item" href="login.php">Login</a></li>
                                            <li><a class="menu-item" href="register.php">Register</a></li>
                                        </ul> -->
                                    </li>
                                </ul>
                            </div>
                            <div class="de-flex-col all_button_main_header">
                                <div class="add_to_card">
                                    <a href="#"><i class="fa-solid fa-cart-plus"></i></a>
                                </div>
                                <a href="booking.php" class=" bg_bnt_custom mb-3">BOOK NOW</a>
                            </div>

                            <!-- Toggle Icon for Mobile -->
                            <div class="d-md-none" id="menu-toggle">
                                <i class="fas fa-bars fs-3 text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- header close -->

        <script>
        document.getElementById("menu-toggle").addEventListener("click", function() {
            document.getElementById("mainmenu").classList.remove("d-none");
        });

        document.getElementById("close-menu").addEventListener("click", function() {
            document.getElementById("mainmenu").classList.add("d-none");
        });
        </script>