<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include(__DIR__ . "/../config.php");
include("admin/db.php"); 
?>

<?php
$stmtVR = $pdo->prepare("
    SELECT title, slug, label
    FROM vr_experiences 
    WHERE is_active = 1 
    ORDER BY created_at DESC
");
$stmtVR->execute();
$vrGames = $stmtVR->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>
        <?php 
        if(isset($pageTitle)) {
            echo $pageTitle ; 
        } else {
            echo SITE_TITLE; 
        }
    ?>
    </title>
      <meta name="description" content="<?php echo $metaDescription; ?>">
  <meta name="keywords" content="<?php echo $metaKeywords; ?>">
    <link rel="icon" href="<?php echo ASSETS_URL; ?>images/icon.png" type="image/gif" sizes="16x16">
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
<link rel="canonical" href="<?php echo htmlspecialchars($canonicalURL); ?>" />
    <!-- CSS Files -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>css/bootstrap.min.css" rel="stylesheet" type="text/css" id="bootstrap">
    <link href="<?php echo ASSETS_URL; ?>css/plugins.css" rel="stylesheet" type="text/css">
    <link href="<?php echo ASSETS_URL; ?>css/swiper.css" rel="stylesheet" type="text/css">
    <link href="<?php echo ASSETS_URL; ?>css/style.css" rel="stylesheet" type="text/css">
    <link href="<?php echo ASSETS_URL; ?>css/demo.css" rel="stylesheet" type="text/css">
    <link href="<?php echo ASSETS_URL; ?>css/coloring.css" rel="stylesheet" type="text/css">
    <link href="<?php echo ASSETS_URL; ?>css/escape-room.css" rel="stylesheet" type="text/css">
    <link href="<?php echo ASSETS_URL; ?>css/vrgame.css" rel="stylesheet" type="text/css">
    <link href="<?php echo ASSETS_URL; ?>css/birthday.css" rel="stylesheet" type="text/css">
    <link href="<?php echo ASSETS_URL; ?>css/user.css" rel="stylesheet" type="text/css">
    <link href="<?php echo ASSETS_URL; ?>css/responsive.css" rel="stylesheet" type="text/css">
    <link href="<?php echo ASSETS_URL; ?>css/responsive1.css" rel="stylesheet" type="text/css">
    
    <link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#00e6f6">


    <!-- fancybox -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <!-- Color Scheme -->
    <link id="colors" href="<?php echo ASSETS_URL; ?>css/colors/scheme-03.css" rel="stylesheet" type="text/css">
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .add_to_card {
  position: relative;
  display: inline-block;
}

.add_to_card a {
  position: relative;
  text-decoration: none;
  color: inherit;
}

.add_to_card .cart-count {
  position: absolute;
  top: -8px;
  right: -10px;
  background: red;
  color: white;
  font-size: 12px;
  font-weight: bold;
  border-radius: 50%;
  padding: 2px 6px;
  line-height: 1;
}

.flatpickr-prev-month svg,
.flatpickr-next-month svg {
    fill: #00d4ff !important;
}
    </style>
</head>

<body class="dark-scheme">
    <div id="wrapper">
        <div class="float-text show-on-scroll">
            <span><a href="#">Scroll to top</a></span>
        </div>
        <div class="scrollbar-v show-on-scroll"></div>
        <!-- page preloader -->
        <div id="de-loader"></div>

        <!-- header begin -->
        <header class="transparent">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="de-flex sm-pt10">
                            <!-- Logo -->
                            <div class="de-flex-col">
                                <div id="logo">
                                    <a href="<?php echo BASE_URL; ?>">
                                        <img class="logo-main" src="<?php echo ASSETS_URL; ?>images/logo.png" alt=""
                                            loading="lazy">
                                        <img class="second_logo_main" src="<?php echo ASSETS_URL; ?>images/ZL_Logo.png"
                                            alt="" loading="lazy">
                                        <img class="logo-mobile" src="<?php echo ASSETS_URL; ?>images/logo-mobile.png"
                                            alt="" loading="lazy">
                                    </a>
                                </div>
                            </div>

                            <!-- Menu -->
                            <div class="de-flex-col header-col-mid">
                                <!-- Hamburger for mobile -->
                                <div class="col-auto ms-auto d-md-none nav-toggle-wrap">
                                    <div class="mobile_card_toggle_button">
                                        <div class="add_to_card">
                                            <?php
                                            // session already started at top of header
                                            // Get current session ID
                                            $sid = session_id();
                                            
                                            // Fetch total VALID (non-expired) cart items for this session
                                            $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM tbl_carts WHERE session_id = :sid AND TIMESTAMPDIFF(SECOND, created_at, NOW()) <= " . (CART_TIMER_MINUTES * 60));
                                            $stmt->execute([':sid' => $sid]);
                                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                                            $cartCount = (int)$row['count'];
                                            
                                            // Determine redirect URL
                                            if ($cartCount === 0) {
                                                $redirectUrl = BASE_URL . "booking?choose-experience";
                                            } else {
                                                $redirectUrl = BASE_URL . "booking?customer-details";
                                            }
                                            ?>
                                            
                                            <a href="<?php echo $redirectUrl; ?>" class="cartUrl">
                                                <i class="fa-solid fa-cart-plus"></i>
                                                <span class="cart-count" style="display: <?php echo ($cartCount > 0) ? 'inline-block' : 'none'; ?>;">
                                                    <?php echo $cartCount; ?>
                                                </span>
                                            </a>
                                        </div>
                                        <button id="menu-toggle" class="nav-toggle" aria-label="Open Menu">
                                            <span></span><span></span><span></span>
                                        </button>
                                    </div>
                                </div>
                                <!-- Menu -->
                                <nav id="mainnav">
                                    <ul id="mainmenu" class="d-flex align-items-center">
                                        <li><a href="<?php echo BASE_URL; ?>" class="menu-item">Home</a></li>

                                        <!-- Escape Rooms -->
                                       <li class="dropdown-parent">
                                            <a class="menu-item dropdown-toggle"
                                                href="<?php echo BASE_URL; ?>indoor-real-life-escape-games">Escape
                                                Rooms</a>
                                            <ul class="submenu">
                                                        <li><a class="submenu_tand_add_on"
                                                                href="<?php echo BASE_URL; ?>prison-escape-collection">
                                                                Prison Break <span
                                                                    class="bg-danger ms-2">Popular</span></a>
                                                        </li>
                                                        <li><a class="submenu_tand_add_on"
                                                                href="<?php echo BASE_URL; ?>the-lift-collection">
                                                                The Lift <span class="bg-success ms-2">Easy</span></a>
                                                        </li>
                                                        <li><a class="submenu_tand_add_on"
                                                                href="<?php echo BASE_URL; ?>steampunk-submarine-collection">Steampunk
                                                                Submarine</a></li>
                                                        <li><a class="submenu_tand_add_on"
                                                                href="<?php echo BASE_URL; ?>ancient-egypt-collection">
                                                                Ancient Egypt 
                                                                <span class="bg-danger ms-2">Popular</span>
                                                                </a>
                                                        </li>
                                                        <li><a class="submenu_tand_add_on"
                                                                href="<?php echo BASE_URL; ?>ice-walker-got-collection">Ice
                                                                Walker - GOT</a></li>
                                                        <li><a class="submenu_tand_add_on"
                                                                href="<?php echo BASE_URL; ?>museum-heist-collection">Museum
                                                                Heist
                                                                <span class="bg-warning ms-2">Hard</span>
                                                                </a></li>
                                                    </ul>
                                        </li>

                                        <!-- Virtual Reality -->
                                       <li class="dropdown-parent">
    <a class="menu-item dropdown-toggle" href="<?php echo BASE_URL; ?>vr-games-at-flee-escape-vr-games">Virtual Reality</a>
    <ul class="submenu">
        <li class="responsive_menu_show_d d-none">
            <a href="<?= BASE_URL ?>vr-games-at-flee-escape-vr-games" class="menu-item">
                All VR Games
            </a>
        </li>

        <?php if (!empty($vrGames)) : ?>
            <?php foreach ($vrGames as $game): ?>
                <li>
                    <a class="submenu_tand_add_on"
                       href="<?= BASE_URL . 'vr/' . htmlspecialchars($game['slug']) ?>">
                        <?= htmlspecialchars($game['title']) ?>
                         <?php if (!empty($game['label'])): ?>
    <?php
        // Default color for unknown labels
        $labelClass = 'bg-secondary'; // grey

        // Assign color based on known labels
        switch (strtolower($game['label'])) {
            case 'new':
                $labelClass = 'bg-success'; // green
                break;
            case 'popular':
                $labelClass = 'bg-danger';  // red
                break;
            case 'trending':
                $labelClass = 'bg-primary'; // blue
                break;
            case 'horror':
                $labelClass = 'bg-warning ms-2';    // dark/purple
                break;
            // No need for default here because $labelClass already set to bg-secondary
        }
    ?>
    <span class="ms-2 <?= $labelClass ?> px-2 py-1 ">
        <?= htmlspecialchars($game['label']) ?>
    </span>
<?php endif; ?>
                    </a>
                   

                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li><span class="text-muted px-3">No VR Games Available</span></li>
        <?php endif; ?>

    </ul>
</li>


                                        <!-- Parties & Events -->
                                        <li class="dropdown-parent">
                                            <a class="menu-item dropdown-toggle" href="#">Parties & Events</a>
                                            <ul class="submenu">
                                                <li>
                                                    <a href="<?php echo BASE_URL; ?>birthday-party-escape-room-vr-party">Birthday Parties</a>
                                                </li>
                                                <li>
                                                    <a href="<?php echo BASE_URL; ?>corporate-team-building-escape-room-vr-party">Team Building</a>
                                                </li>
                                                <li><a href="<?php echo BASE_URL; ?>corporate-facility-rental">Facility Rental for Large Groups </a></li>
                                                <!--<li class="dropdown-parent submenu_span_remove">-->
                                                <!--    <a class="menu-item dropdown-toggle" href="#">Other Special Occasions</a>-->
                                                <!--    <ul class="submenu">-->
                                                <!--        <li><a-->
                                                <!--                href="<?php echo BASE_URL; ?>christmas-new-year-escape-room-vr-party">Christmas-->
                                                <!--                and New Year Party Packages</a></li>-->
                                                <!--        <li><a-->
                                                <!--                href="<?php echo BASE_URL; ?>halloween-escape-room-vr-party">Halloween-->
                                                <!--                Party-->
                                                <!--                Packages</a></li>-->
                                                <!--        <li><a-->
                                                <!--                href="<?php echo BASE_URL; ?>thanksgiving-escape-room-vr-party">Thanksgiving-->
                                                <!--                Party-->
                                                <!--                Packages</a></li>-->
                                                <!--        <li><a-->
                                                <!--                href="<?php echo BASE_URL; ?>diwali-escape-room-vr-party">Diwali-->
                                                <!--                Party Packages</a>-->
                                                <!--        </li>-->
                                                <!--    </ul>-->
                                                <!--</li>-->
                                                
                                            </ul>
                                        </li>

                                        <!-- Single Links -->
                                        <li><a class="menu-item"
                                                href="<?php echo BASE_URL; ?>promotions">Promotions</a></li>
                                        <li><a class="menu-item" href="<?php echo BASE_URL; ?>gallery">Gallery</a></li>
                                        <li><a class="menu-item" href="<?php echo BASE_URL; ?>contact-us">Contact Us</a></li>
                                        <li><a class="menu-item" href="<?php echo BASE_URL; ?>faq">FAQ</a></li>
                                    </ul>
                                </nav>
                            </div>

                            <!-- Right Buttons -->
                            <div class="de-flex-col all_button_main_header">
                     <div class="add_to_card">
                        <?php
                        // session already started at top of header
                        // Get current session ID
                        $sid = session_id();
                        
                        // Fetch total VALID (non-expired) cart items for this session
                        $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM tbl_carts WHERE session_id = :sid AND TIMESTAMPDIFF(SECOND, created_at, NOW()) <= " . (CART_TIMER_MINUTES * 60));
                        $stmt->execute([':sid' => $sid]);
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $cartCount = (int)$row['count'];
                        
                        // Determine redirect URL
                        if ($cartCount === 0) {
                            $redirectUrl = BASE_URL . "booking?choose-experience";
                        } else {
                            $redirectUrl = BASE_URL . "booking?customer-details";
                        }
                        ?>
                        
                        <a href="<?php echo $redirectUrl; ?>" class="cartUrl">
                            <i class="fa-solid fa-cart-plus"></i>
                            <span class="cart-count" style="display: <?php echo ($cartCount > 0) ? 'inline-block' : 'none'; ?>;">
                                <?php echo $cartCount; ?>
                            </span>
                        </a>
                        </div>

                                <a href="<?=BASE_URL?>booking" class="bg_bnt_custom mb-3"> BOOK NOW</a>
                            </div>

                            <!-- Mobile Menu Toggle -->
                            <div id="nav-overlay"></div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <div class="respnsive_booknow_bnt">
             <a href="<?=BASE_URL?>booking" class="bg_bnt_custom mb-3">BOOK NOW</a>
        </div>

        <style>
        #mainnav {
            transition: none;
        }

        #mainmenu {
            display: flex;
            align-items: center;
        }

        .menu-item {
            font-size: 16px;
            font-weight: 500;
            color: #fff;
            transition: color 0.3s;
        }

        .menu-item:hover {
            color: #00d4ff;
        }

        .dropdown-toggle::after {
            content: " ▼";
            font-size: 11px;
            color: #00d4ff;
            border: none !important
        }

        .logo-main,
        .logo-mobile {
            transition: opacity 0.4s, transform 0.4s;
        }

        .submenu {
            display: block;
            visibility: hidden;
            opacity: 0;
            position: absolute;
            left: 0;
            top: 100%;
            background: rgba(20, 25, 40, 0.99);
            border-radius: 8px;
            min-width: auto;
            padding: 0.8rem 0;
            box-shadow: 0 8px 24px #0004;
            transform: translateY(20px) scale(0.98);
            z-index: 20;
            transition: all 0.35s cubic-bezier(.42, .01, .6, 1);
            pointer-events: none;
        }

        .dropdown-parent {
            position: relative;
            cursor: pointer;
        }

        .dropdown-parent:hover>.submenu,
        .dropdown-parent:focus-within>.submenu {
            visibility: visible;
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0) scale(1);
        }

        .submenu li {
            width: 100%;
            border-bottom: 1px solid #fff3;
            padding: 0.4rem 1rem;
            color: #00d4ff;
            transition: background 0.2s, color 0.2s;
        }

        .submenu li:last-child {
            border-bottom: none;
        }

        .submenu li a {
            color: #fff;
        }

        .nav-toggle {
            background: none;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 10px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            z-index: 99999;
        }

        .nav-toggle span {
            display: block;
            width: 28px;
            height: 3px;
            background: #fff;
            border-radius: 2px;
            transition: all 0.4s;
            z-index: 99999;
        }

        .nav-toggle.active span:nth-child(1) {
            transform: translateY(9px) rotate(45deg);
        }

        .nav-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .nav-toggle.active span:nth-child(3) {
            transform: translateY(-9px) rotate(-45deg);
        }

        #nav-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 9999;
            background: rgba(10, 20, 30, 0.87);
            backdrop-filter: blur(4px);
            opacity: 0;
            transition: opacity 0.4s;
        }

        @media (max-width: 991px) {
            #mainnav {
                position: fixed;
                top: 0;
                left: -80vw;
                height: 100vh;
                width: 80vw;
                background: #151a28;
                box-shadow: 2px 0 32px #0007;
                z-index: 10000;
                display: flex !important;
                flex-direction: column;
                transition: left 0.4s cubic-bezier(.42, .01, .6, 1);
                padding-top: 80px;
            }

            #mainnav.open {
                left: 0;
            }

            #nav-overlay.open {
                display: block;
                opacity: 1;
            }



            #mainmenu {
                flex-direction: column !important;
                align-items: flex-start;
            }

            .dropdown-parent .submenu {
                position: static;
                box-shadow: none;
                border-radius: 0;
                background: none;
                padding-left: 1rem;
                visibility: visible;
                opacity: 1;
                transform: none;
                max-height: 0;
                overflow: hidden;
                transition: max-height .4s cubic-bezier(.7, .3, .4, 1);
            }

            .dropdown-parent.open>.submenu {
                max-height: 600px;
            }
        }

        .logo-mobile {
            max-height: 48px;
        }

        .logo-main {
            max-height: 64px;
        }
        </style>

        <!-- header close -->
        <style>
        /* Dropdown Menu */
        #mainmenu .dropdown {
            position: relative;
        }

        #mainmenu .dropdown .submenu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: linear-gradient(145deg, #1a1f2e, #0f1419);
            color: #00d4ff;
            padding: 10px 0;
            list-style: none;
            min-width: fit-content;
            z-index: 999;
            border-radius: 8px;
            padding: 0px !important;
        }

        #mainmenu .dropdown .submenu li {
            width: 100%;
            padding: 0 11px;
            padding: 7px 40px;
            padding-left: 12px;
        }

        ul.submenu li {
            /* padding: 6px 10px !important; */
            display: block;
            color: #00d4ff !important;
            white-space: nowrap;
            transition: background 0.3s ease;
            border-bottom: 1px solid #ffffff4a;
        }

        ul.submenu li:last-child {
            border-bottom: none;
        }


        ul.submenu li :hover {
            /* background: #00d4ff; */
            color: #03204b;
        }

        /* Show submenu on hover */
        #mainmenu .dropdown:hover .submenu {
            display: block;
        }

        .submenu_tand_add_on {
            display: flex !important;
            /* justify-content: space-between; */
            align-items: center;
        }

        a.submenu_tand_add_on span {
            margin: 0;
            width: auto;
            border-radius: 10px;
            height: max-content;
            font-size: 10px;
            padding: 3px 5px !important;
            line-height: initial;
        }

        #mainmenu li li a {
            color: #fff !important;
            text-transform: capitalize;
        }


        #mainmenu li a {
            font-size: 16px;
            font-weight: 500;
            padding: 0 8px;
            color: #fff;
        }

        .transparent li a:hover {
            color: #00d4ff !important;
            transform: translateY(-3px);
        }

        #mainmenu li:hover {
            transform: translateY(-3px) !important;
        }

        ul.submenu li a:hover {
            color: #00d4ff !important;
            transform: translate(0px 10px);
        }

        header.transparent .container-fluid {
            max-width: 1480px;
            margin: 0 auto;
            width: 100%;
            padding: 0 8px;
        }

        #mainmenu li a:hover {
            color: #00d4ff !important;
        }

        #mainmenu li a:hover span {
            color: #000;
        }

        ul.submenu a {
            padding: 0 !important;
        }

        ul.submenu li {
            padding: 8px 14px !important;
            padding-right: 35px !important;
        }

        #mainmenu li li a.menu-item:hover,
        #mainmenu ul li:hover>a.menu-item {
            background: transparent !important;
        }

        @media (max-width: 768px) {
             ul.submenu li {
            padding-right: 0px !important;
        }
            #mainmenu {
                gap: 0px;
            }

            div#logo a {
                display: flex;
                gap: 6px;
                flex-direction: row-reverse;
            }

            #mainmenu li {
                padding: 7px 15px;
            }
            header.header-mobile #mainmenu ul {
                height: auto!important;
            }

        }
        </style>

        <script>
        const toggle = document.getElementById('menu-toggle');
        const mainnav = document.getElementById('mainnav');
        const overlay = document.getElementById('nav-overlay');

        // Hamburger and overlay functionality
        toggle.addEventListener('click', function() {
            this.classList.toggle('active');
            mainnav.classList.toggle('open');
            overlay.classList.toggle('open');
        });

        // Overlay closes menu
        overlay.addEventListener('click', function() {
            toggle.classList.remove('active');
            mainnav.classList.remove('open');
            this.classList.remove('open');
        });

        // Mobile: Accordion dropdowns
        document.querySelectorAll('#mainmenu .dropdown-toggle').forEach(el => {
            el.addEventListener('click', function(e) {
                if (window.innerWidth < 992) {
                    e.preventDefault();
                    const parent = this.closest('.dropdown-parent');
                    parent.classList.toggle('open');
                }
            });
        });
        </script>
        
        <!--choose adventure toggle js-->
<script>
document.addEventListener("DOMContentLoaded", function() {
  const toggleBtn = document.querySelector(".choose_all_page_resposnive_toggle");
  const sidebar = document.querySelector(".choose-adventure .col-md-3");

  toggleBtn.addEventListener("click", function() {
    sidebar.classList.toggle("choose-adventure_game");
  });
});
</script>

<script>
    window.CART_TIMER_MINUTES = <?php echo CART_TIMER_MINUTES; ?>;
</script>

<script>
/* ============================================================
   GLOBAL CART WATCHER — runs on every page (header.php)
   Handles timer expiry even when user is NOT on booking.php
   ============================================================ */
(function globalCartWatcher() {
    var baseUrl = "<?= BASE_URL ?>";

    function clearBadgeUI() {
        document.querySelectorAll('.cart-count').forEach(function(badge) {
            badge.textContent = '0';
            badge.style.display = 'none';
        });
        // Point cart links back to the start of the booking flow
        document.querySelectorAll('.cartUrl').forEach(function(link) {
            link.href = baseUrl + 'booking?choose-experience';
        });
    }

    function expireCartNow() {
        // Mark expired first so booking page never restarts timer from stale UI state
        localStorage.removeItem('cartTimerEnd');
        localStorage.setItem('cartTimerExpired', 'true');
        // Trigger server-side session + DB cleanup
        fetch(baseUrl + 'expire_cart.php?reason=header_watcher', { cache: 'no-store' }).catch(function() {});
        clearBadgeUI();
        // Notify the current page so it can immediately refresh slot availability
        window.dispatchEvent(new CustomEvent('flee:cartExpired'));
    }

    function initWatcher() {
        // Case 1: already flagged as expired (e.g. tab was left open)
        if (localStorage.getItem('cartTimerExpired') === 'true') {
            expireCartNow();
            return;
        }

        var endTime = parseInt(localStorage.getItem('cartTimerEnd') || '0');
        if (!endTime) return; // No active timer — nothing to do

        var remaining = endTime - Date.now();

        if (remaining <= 0) {
            // Timer has already passed but expired flag wasn't set (e.g. page was closed during countdown)
            localStorage.setItem('cartTimerExpired', 'true');
            expireCartNow();
            return;
        }

        // Timer is still running — schedule cleanup for when it fires on THIS page
        setTimeout(function() {
            localStorage.setItem('cartTimerExpired', 'true');
            expireCartNow();
        }, remaining);
    }

    document.addEventListener('DOMContentLoaded', initWatcher);
})();
</script>
