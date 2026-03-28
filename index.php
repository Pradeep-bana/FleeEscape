<?php session_start();
include('link.php');
include('admin/db.php');

$pageTitle = 'Home Page';
$metaKeywords = "Home Page";
$metaDescription = "Home Page";
$canonicalURL = $link;

// Fetch meta info
$stmt = $pdo->prepare("
    SELECT page_title, keywords, page_description 
    FROM tbl_home_bottom_section 
    LIMIT 1
");
$stmt->execute();
$meta = $stmt->fetch(PDO::FETCH_ASSOC);

if ($meta) {
    // Override defaults with DB values
    $pageTitle = $meta['page_title'];
    $metaKeywords = $meta['keywords'];
    $metaDescription = $meta['page_description'];
}
include('includes/header.php');


?>


<!-- content begin -->
<div class="no-bottom no-top" id="content">

    <div id="top"></div>

    <section class="home_baneer_section no-top no-bottom sm-pt100  position-relative z-1000 jarallax">
        <!-- <img src="assets/images/background/7.webp" class="jarallax-img" alt="" loading="lazy"> -->
        <div class="de-gradient-edge-bottom"></div>
        <div class="v-center">
            <div id="swiper" class="swiper">
                <div class="swiper-wrapper">
                    <?php
include('admin/db.php'); // adjust path if needed

$query = $pdo->query("SELECT id, first_heading, second_heading, description, link, image FROM tbl_main_banner ORDER BY id asc");
$banners = $query->fetchAll(PDO::FETCH_ASSOC);
$c=0;
foreach ($banners as $row):
    $imagePath = 'admin/uploads/' . htmlspecialchars($row['image']);
    $imageUrl = (file_exists($imagePath) && !empty($row['image'])) ? $imagePath : 'assets/images/default-banner.jpg';
    
   if($c==0){
       $btn_cap='BOOK YOUR ADVENTURE NOW';
   } 
   if($c==1){
       $btn_cap='BLUR YOUR REALITY NOW';
   } 
   if($c==2){
       $btn_cap='BOOK YOUR PARTY NOW';
   } 
   if($c==3){
       $btn_cap='PLAN YOUR EVENT NOW';
   } 
    
?>
                    <div class="swiper-slide no-bg">
                        <div class="swiper-inner bg_before_bg_add"
                            style="background-image: url('<?php echo $imageUrl; ?>');">
                            <div class="sw-caption">
                                <div class="container">
                                    <div class="row gx-4 align-items-center home_slider_content_main">

                                        <?php if($c==0) { ?>
                                        <div class="AWARD_banne_AWARd_home">
                                            <div class="AWARD_banne_AWARd_home_img">
                                                <img src="img/silver-award-badge.png" loading="lazy" alt="">
                                                <img src="img/bronze-award-badge.png" loading="lazy" alt="">
                                            </div>
                                        </div>
                                        <?php } ?>
                                        <div class="col-lg-6 mb-sm-30">
                                            <h1 class="slider-title s2 mb-1">
                                                <span
                                                    style="color:#0cdede;"><?php echo htmlspecialchars($row['first_heading']); ?>
                                                    <br></span>
                                                <?php echo htmlspecialchars($row['second_heading']); ?>
                                            </h1>
                                            <p class="slider-text">
                                                <?php echo htmlspecialchars($row['description']); ?>
                                            </p>
                                            <div class="spacer-10"></div>
                                            <div class="de-flex-col all_button_main_header">
                                                <?php if (!empty($row['link'])): ?>
                                                <a href="<?php echo htmlspecialchars($row['link']); ?>"
                                                    class="bg_bnt_custom"><?php echo $btn_cap; ?></a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-7"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $c++;
endforeach; ?>
                </div>

                <div class="swiper-pagination"></div>

                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>

                <div class="swiper-scrollbar"></div>
            </div>
        </div>
    </section>

    <div class="gaming-container">
        <div class="container">
            <div class="col-lg-12 heading_main_section text-center">
                <!-- <div class="subtitle wow fadeInUp mb-3">Popular</div> -->
                <h1 style="color: #0cdede;">EXPERIENCE THE THRILL</h1>
                <h2>There’s something for everyone</h2>
            </div>
            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs  d-none d-md-flex" id="gamingTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="escape-rooms-tab" data-bs-toggle="tab"
                        data-bs-target="#escape-rooms" type="button" role="tab">
                        ESCAPE ROOMS
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="vr-games-tab" data-bs-toggle="tab" data-bs-target="#vr-games"
                        type="button" role="tab">
                        VR GAMES
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="party-packages-tab" data-bs-toggle="tab"
                        data-bs-target="#party-packages" type="button" role="tab">
                        PARTY PACKAGES
                    </button>
                </li>
            </ul>

            <!-- Gaming Dropdown for Mobile -->
            <div class="d-md-none gaming_dropdwon_select">
                <select id="gamingSelect" class="form-select">
                    <option value="#escape-rooms" selected>ESCAPE ROOMS</option>
                    <option value="#vr-games">VR GAMES</option>
                    <option value="#party-packages">PARTY PACKAGES</option>
                </select>
            </div>

            <!-- Tab Content -->
            <div class="tab-content" id="gamingTabsContent">
                <!-- Escape Rooms Tab -->
                <div class="tab-pane fade show active" id="escape-rooms" role="tabpanel">
                    <?php
include('admin/db.php'); // include your PDO connection

try {
    $stmt = $pdo->query("SELECT * FROM tbl_service ORDER BY id DESC");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching data: " . $e->getMessage();
    exit;
}
?>

                    <div class="owl-carousel owl-theme new_all_game_slider">
                        <?php foreach ($rooms as $room): ?>
                        <div class="item">
                            <div class="room-card">
                                <div class="room-image haunted-hotel"
                                    style="background-image: url('admin/uploads/<?php echo htmlspecialchars($room['thumbnail']); ?>');">
                                </div>

                                <?php if (!empty($room['label'])): ?>
                                <div class="d-label">
                                    <?php echo htmlspecialchars($room['label']); ?>
                                </div>
                                <?php endif; ?>

                                <div class="room-content">
                                    <div class="room-content_all_ah">
                                        <h3 class="room-title"><?php echo htmlspecialchars($room['title']); ?></h3>
                                        <div class="room-details">
                                            <p class="d-price">Layout: <span
                                                    class="price"><?php echo htmlspecialchars($room['layout']); ?></span>
                                            </p>
                                            <p class="d-price">Difficulty: <span
                                                    class="price"><?php echo htmlspecialchars($room['difficulty']); ?></span>
                                            </p>
                                            <p class="d-price">Success Rate: <span
                                                    class="price"><?php echo htmlspecialchars($room['success_rate']); ?>%</span>
                                            </p>
                                            <p class="d-price">Players: <span
                                                    class="price"><?php echo htmlspecialchars($room['players']); ?></span>
                                            </p>
                                            <a href="<?php echo htmlspecialchars($room['link']); ?>"
                                                class="book-btn">Learn more</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($room['bottom_heading'])): ?>
                            <h6 class="color_combination_vr_function text-center">
                                <?php echo $room['bottom_heading']; ?>
                            </h6>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>

                </div>

                <!-- ==================================================================== -->
                <!-- VR Games Tab -->

                <div class="tab-pane fade" id="vr-games" role="tabpanel">
                    <div class="owl-carousel owl-theme new_all_game_slider">
                        <?php
include('admin/db.php');

// Fetch all active VR experiences
$sql = "SELECT id, slug, title, prime_category, tagline, description, banner_image, logo_image, video_banner_image, 
        video_url, booking_url, min_players, max_players, category, duration_minutes, age_restriction, price, difficulty, 
        bottom_heading, meta_title, meta_keywords, meta_description, is_active, created_at, updated_at 
        FROM vr_experiences 
        WHERE is_active = 1 
        ORDER BY created_at DESC";

$stmt = $pdo->query($sql);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Use default image if banner_image is empty
    $bannerImage = $row['banner_image'] ?: 'path/to/default-image.jpg';
    $difficultyClass = strtolower($row['difficulty']);
?>
                        <div class="item">
                            <div class="room-card">
                                <div class="room-image haunted-hotel"
                                    style="background-image: url('admin/<?php echo $bannerImage; ?>');">
                                </div>

                                <div class="room-content">
                                    <div class="room-content_all_ah">
                                        <h3 class="room-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                                        <div class="room-details">

                                            <p class="d-price">Category: <span
                                                    class="price <?php echo $difficultyClass; ?>"><?php echo htmlspecialchars($row['category']); ?></span>
                                            </p>
                                            <p class="d-price">Difficulty: <span
                                                    class="price"><?php echo htmlspecialchars($room['difficulty']); ?></span>
                                            </p>
                                            <p class="d-price">Game Duration: <span
                                                    class="price"><?php echo htmlspecialchars($row['duration_minutes']); ?> min
                                                    </span></p>
                                            
                                            <p class="d-price">Players: <span
                                                    class="price"><?php echo htmlspecialchars($row['min_players']); ?>-<?php echo htmlspecialchars($row['max_players']); ?></span>
                                            </p>
                                            <a href="vr/<?php echo htmlspecialchars($row['slug']); ?>"
                                                class="book-btn">LEARN MORE</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php if (!empty($row['bottom_heading'])): ?>
                            <h6 class="color_combination_vr_function text-center"><?php echo $row['bottom_heading']; ?>
                            </h6>
                            <?php endif; ?>
                        </div>
                        <?php } ?>
                    </div>

                </div>

                <!-- -------------------------------------------------------------------------------- -->
                <!-- ----------vr__css------------------------------------- -->
                <style>
                .color_combination_vr_function {
                    color: #eff9fe;
                }
                </style>
                <!-- =========================================================================================== -->
                <!-- Party Packages Tab -->
                <div class="tab-pane fade" id="party-packages" role="tabpanel">
                    <div class="owl-carousel owl-theme Party_Packages_slider new_all_game_slider">
                        <?php
include('admin/db.php'); // your PDO connection

try {
    $stmt = $pdo->query("SELECT `id`, `product_id`, `title`, `slug`, `price`, `duration`, `players`, `thumbnail`, `bottom_heading`, `created_at` FROM `tbl_party_packages` WHERE 1");
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Removed local file logging - use centralized logging if needed

    foreach ($packages as $package) {
        // Optional: Add label logic based on some criteria
        $label = '';
        if (strpos(strtolower($package['title']), 'kids') !== false) {
            $label = 'Most Popular';
        } elseif (strpos(strtolower($package['title']), 'corporate') !== false) {
            $label = 'Private Event';
        } elseif (strpos(strtolower($package['title']), 'value') !== false) {
            $label = 'Most Value';
        }
        
        // Removed local file logging - use centralized logging if needed
 
        ?>
                        <div class="item">
                            <div class="room-card">
                                <div class="room-image haunted-hotel"
                                    <?php
                                        $bgImage = 'admin/uploads/' . (!empty($package['thumbnail']) ? htmlspecialchars($package['thumbnail']) : '');
                                    ?>
                                    style="background-image: url('<?= $bgImage ?>');">
                                </div>

                                <?php if ($label): ?>
                                <div class="d-label" style="background-size: cover; background-repeat: no-repeat;">
                                    <?= htmlspecialchars($label) ?>
                                </div>
                                <?php endif; ?>

                                <div class="room-content">
                                    <div class="room-content_all_ah">
                                        <h3 class="room-title"><?= htmlspecialchars($package['title']) ?></h3>
                                        <div class="room-details">
                                            <p class="d-price">Party Duration: <span
                                                    class="price Easy"><?= htmlspecialchars($package['duration']) ?></span>
                                            </p>
                                            <p class="d-price">Difficulty: <span
                                                    class="price"><?php echo htmlspecialchars($room['difficulty']); ?></span>
                                            </p>
                                            <p class="d-price">Players: <span
                                                    class="price Easy"><?= htmlspecialchars($package['players']) ?></span>
                                            </p>
                                            <p class="d-price">Price: <span
                                                    class="price">$<?= htmlspecialchars($package['price']) ?></span>
                                            </p>
                                            <a href="<?= htmlspecialchars($package['slug']) ?>" class="book-btn">LEARN
                                                MORE</a>
                                        </div>

                                    </div>
                                </div>
                            </div>
                             <?php if (!empty($package['bottom_heading'])): ?>
                            <h6 class="color_combination_vr_function text-center"><?php echo $package['bottom_heading']; ?>
                            </h6>
                            <?php endif;  ?>
                        </div>
   <?php }

} catch (PDOException $e) {
    echo "<p>Error fetching packages: " . $e->getMessage() . "</p>";
}
?>
                    </div>

                    <div class="party_not_sub_new_add">
                        <p> All Party Packages include 1 hour of free party time in the open Lobby area. All Players
                            need to be 9 years or older and taller than 4 ft 5 inches to play VR games.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    const select = document.getElementById("gamingSelect");
    const tabs = document.querySelectorAll('#gamingTabs button[data-bs-toggle="tab"]');

    // Dropdown → Tab
    select.addEventListener("change", e => {
        const tab = document.querySelector(`#gamingTabs button[data-bs-target="${e.target.value}"]`);
        new bootstrap.Tab(tab).show();
    });

    // Tab → Dropdown
    tabs.forEach(btn => {
        btn.addEventListener('shown.bs.tab', e => {
            select.value = e.target.getAttribute('data-bs-target');
        });
    });
    </script>

    <section>
        <div class="home_video_section">
            <div class="col-lg-12 heading_main_section text-center" style="margin-bottom: 0px!important;">
                <h1>Where Reality Ends and Adventure <span style="color: #0cdede;"> Begins</span></h1>
                <p style="margin-bottom: 0;">Watch how our 9,000 sq. ft. facility brings imagination to life with
                    movie-style <br /> escape rooms and cutting-edge VR battles.</p>
            </div>
            <?php
include('admin/db.php'); // adjust path if needed

$query = $pdo->query("SELECT id, thumbnail, banner_video FROM tbl_video_banner ORDER BY id DESC LIMIT 1");
$video = $query->fetch(PDO::FETCH_ASSOC);

if ($video):
    $thumbnailPath = 'admin/' . htmlspecialchars($video['thumbnail']);
    $videoPath = 'admin/' . htmlspecialchars($video['banner_video']);

    // Fallbacks
    $poster = (file_exists($thumbnailPath) && !empty($video['thumbnail']))
                ? $thumbnailPath
                : 'assets/images/default-video-thumb.jpg';

    $videoSrc = (file_exists($videoPath) && !empty($video['banner_video']))
                ? $videoPath
                : 'assets/video/default-video.mp4';
?>
            <div class="video_play_add" data-aos="zoom-in">
                <video playsinline loop controls poster="<?php echo $poster; ?>">
                    <source src="<?php echo $videoSrc; ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>

                <div class="play_button_video">
                    <i class="fa-solid fa-play"></i>
                </div>
            </div>
            <?php endif; ?>


        </div>
    </section>

    <!-- ================================================== -->

    <?php
include('admin/db.php'); // adjust path if needed

// Fetch banner data
$query = $pdo->query("SELECT id, first_heading, blog_detl, image FROM tbl_middle_image_banner WHERE 1 LIMIT 1");
$row = $query->fetch(PDO::FETCH_ASSOC);

if ($row):
    // Prepare data safely
    $heading = htmlspecialchars($row['first_heading']);
    $description = $row['blog_detl']; // allows HTML formatting if present
    $imagePath = 'admin/uploads/' . htmlspecialchars($row['image']);
    $image = (file_exists($imagePath) && !empty($row['image'])) ? $imagePath : 'assets/images/fleeescape_img/game.png';
?>
    <section class="no-bottom Summer_cmp_section_home">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="padding60 sm-padding40 sm-p-2 jarallax position-relative rooboat_after_dhsj">
                        <!-- ✅ Dynamic Background Image -->
                        <img src="<?php echo $image; ?>" class="jarallax-img" alt="<?php echo $heading; ?>"
                            loading="lazy">

                        <div class="row">
                            <div class="col-lg-6">
                                <!-- ✅ Dynamic Heading -->
                                <h2 class="wow fadeInUp" data-wow-delay=".2s">
                                    <span style="color: #0cdede;"><?php echo $heading; ?></span>
                                </h2>

                                <!-- ✅ Dynamic Description -->
                                <p style="color: #fff;" class="wow fadeInUp">
                                    <?php echo nl2br($description); ?>
                                </p>

                                <div class="spacer-10"></div>

                                <!-- ✅ Static Learn More button -->
                                <div class="de-flex-col all_button_main_header">
                                    <a href="https://www.fleeescape.com/summer-camp" class="bg_bnt_custom">Learn
                                        More</a>
                                </div>
                            </div>
                        </div>

                        <!-- Optional image placeholder -->
                        <!-- <img src="assets/images/avatar.webp" class="sm-hide position-absolute bottom-0 end-0 wow fadeIn" alt="" loading="lazy"> -->
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>



    <section class="pt90 no-bottom collcrion_sum">
        <div class="container">
            <div class="col-lg-12 heading_main_section text-center" style="margin-bottom: 0px!important;">
                <h1>WHY <span style="color: #0cdede;">CHOOSE</span> US</h1>
                <p style="margin-bottom: 0;">Only Premier Entertainment Facility in all of Greater Seattle offering
                    Escape Rooms and VR Games
                    under the same roof
                </p>
            </div>
            <div class="row game_Collection_section">
                <div class="owl-carousel owl-theme pl40 pr40" id="thumbnail-carousel">
                    <div class="item">
                        <div class="Collection_summer_camp_card">
                            <div class="Collection_summer_camp_card_img">
                                <img src="./assets/images/fleeescape_img/CHOOSE/1.jpg" class="img-fluid "
                                    loading="lazy">
                            </div>
                            <div class="Collection_summer_camp_card_cont_box">
                                <div class="Collection_summer_camp_card_icons">
                                    <div class="Collection_summer_camp_card_icons_img">
                                        <img class="card_hover_img_card_srcall"
                                            src="./assets/images/fleeescape_img/card_hover_1.png" alt="" loading="lazy">
                                        <img class="card_hover_img_card_srcall"
                                            src="./assets/images/fleeescape_img/card_hover_2.png" loading="lazy">
                                    </div>
                                    <div class="Collection_summer_camp_card_icons_i">
                                        <i class="fa-solid fa-film"></i>
                                    </div>
                                </div>
                                <div class="Collection_summer_camp_card_cont">
                                    <h1>6 MOVIE-STYLED ESCAPE ROOMS</h1>
                                    <p>High-budget, cinematic sets with immersive themes and mind-bending puzzles</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="Collection_summer_camp_card">
                            <div class="Collection_summer_camp_card_img">
                                <img src="./assets/images/fleeescape_img/CHOOSE/Gear-image.jpg" class="img-fluid "
                                    loading="lazy">
                            </div>
                            <div class="Collection_summer_camp_card_cont_box">
                                <div class="Collection_summer_camp_card_icons">
                                    <div class="Collection_summer_camp_card_icons_img">
                                        <img class="card_hover_img_card_srcall"
                                            src="./assets/images/fleeescape_img/card_hover_1.png" alt="" loading="lazy">
                                        <img class="card_hover_img_card_srcall"
                                            src="./assets/images/fleeescape_img/card_hover_2.png" loading="lazy">
                                    </div>
                                    <div class="Collection_summer_camp_card_icons_i">
                                        <i class="fas fa-vr-cardboard"></i>
                                    </div>
                                </div>
                                <div class="Collection_summer_camp_card_cont">
                                    <h1>WORLD’S LARGEST FREE-ROAM VR ARENA</h1>
                                    <p>Roam in 2000+ Sq. Ft of untethered VR action for 8 players at once - no wires, no
                                        limits</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="Collection_summer_camp_card">
                            <div class="Collection_summer_camp_card_img">
                                <img src="./assets/images/fleeescape_img/CHOOSE/3.JPG" class="img-fluid "
                                    loading="lazy">
                            </div>
                            <div class="Collection_summer_camp_card_cont_box">
                                <div class="Collection_summer_camp_card_icons">
                                    <div class="Collection_summer_camp_card_icons_img">
                                        <img class="card_hover_img_card_srcall"
                                            src="./assets/images/fleeescape_img/card_hover_1.png" alt="" loading="lazy">
                                        <img class="card_hover_img_card_srcall"
                                            src="./assets/images/fleeescape_img/card_hover_2.png" loading="lazy">
                                    </div>
                                    <div class="Collection_summer_camp_card_icons_i">
                                        <i class="fa-solid fa-cake-candles"></i>
                                    </div>
                                </div>
                                <div class="Collection_summer_camp_card_cont">
                                    <h1>1000+ Birthday Parties and 500+ Corporate team events</h1>
                                    <p>Host unforgettable celebrations with our all-in-one party and team building
                                        packages</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="Collection_summer_camp_card">
                            <div class="Collection_summer_camp_card_img">
                                <img src="./assets/images/fleeescape_img/CHOOSE/4.jpg" class="img-fluid "
                                    loading="lazy">
                            </div>
                            <div class="Collection_summer_camp_card_cont_box">
                                <div class="Collection_summer_camp_card_icons">
                                    <div class="Collection_summer_camp_card_icons_img">
                                        <img class="card_hover_img_card_srcall"
                                            src="./assets/images/fleeescape_img/card_hover_1.png" alt="" loading="lazy">
                                        <img class="card_hover_img_card_srcall"
                                            src="./assets/images/fleeescape_img/card_hover_2.png" loading="lazy">
                                    </div>
                                    <div class="Collection_summer_camp_card_icons_i">
                                        <i class="fa-solid fa-star"></i>
                                    </div>
                                </div>
                                <div class="Collection_summer_camp_card_cont">
                                    <h1>Thousands of 5-star reviews</h1>
                                    <p>Trusted by over 100,000 Players with Glowing Reviews on Google, Yelp, and
                                        TripAdvisor</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="Collection_summer_camp_card">
                            <div class="Collection_summer_camp_card_img">
                                <img src="./assets/images/fleeescape_img/CHOOSE/5.jpg" class="img-fluid "
                                    loading="lazy">
                            </div>
                            <div class="Collection_summer_camp_card_cont_box">
                                <div class="Collection_summer_camp_card_icons">
                                    <div class="Collection_summer_camp_card_icons_img">
                                        <img class="card_hover_img_card_srcall"
                                            src="./assets/images/fleeescape_img/card_hover_1.png" alt="" loading="lazy">
                                        <img class="card_hover_img_card_srcall"
                                            src="./assets/images/fleeescape_img/card_hover_2.png" loading="lazy">
                                    </div>
                                    <div class="Collection_summer_camp_card_icons_i">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                </div>
                                <div class="Collection_summer_camp_card_cont">
                                    <h1>Multiple Awards and Press</h1>
                                    <p>Best of Redmond Entertainment Winner featured in multiple local magazines and TV
                                        channels.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="main_scl_section_opn" style="display: none;">
        <div class="scroll_main_class_Swc" style="width: 80%; margin: 0 auto; height: 332px; overflow: scroll;">
            <!-- Row 1 -->
            <div class="scroll_box_anim">
                <div class="img_wrapper">
                    <img src="assets/images/covers-square/7.jpg" loading="lazy" alt="Warfare Legends" class="anim_img">
                </div>
                <div class="img_wrapper_content" style="margin-left: 32px;">
                    <h2 style="margin: 0 0 12px;">Warfare Legends</h2>
                    <p style="margin: 0;">Experience intense multiplayer battles and strategic gameplay in Warfare
                        Legends. Team up, conquer objectives, and rise to the top of the leaderboard!</p>
                </div>
            </div>
            <!-- Row 2 -->
            <div class="scroll_box_anim">
                <div class="img_wrapper">
                    <img src="assets/images/covers-square/11.jpg" loading="lazy" alt="Galactic Odyssey"
                        class="anim_img">
                </div>
                <div class="img_wrapper_content" style="margin-left: 32px;">
                    <h2 style="margin: 0 0 12px;">Galactic Odyssey</h2>
                    <p style="margin: 0;">Embark on an interstellar adventure in Galactic Odyssey. Explore new
                        worlds, complete missions, and build your own space legacy with friends.</p>
                </div>
            </div>
            <!-- Row 3 -->
            <div class="scroll_box_anim">
                <div class="img_wrapper">
                    <img src="assets/images/covers-square/5.jpg" alt="Thunder and City" loading="lazy" class="anim_img">
                </div>
                <div class="img_wrapper_content" style="margin-left: 32px;">
                    <h2 style="margin: 0 0 12px;">Thunder and City</h2>
                    <p style="margin: 0;">Race through neon-lit streets and challenge rivals in Thunder and City.
                        Unlock new vehicles, master tricky tracks, and become the ultimate street racer.</p>
                </div>
            </div>
        </div>
    </section>

    <?php
// Fetch single record from tbl_home_bottom_section
$stmt = $pdo->query("SELECT feature_1_title, feature_1_desc, feature_2_title, feature_2_desc, feature_3_title, feature_3_desc 
                     FROM tbl_home_bottom_section 
                     LIMIT 1");
$section = $stmt->fetch(PDO::FETCH_ASSOC);
?>

    <section>
        <div class="all_box_full_width">
            <div class="container">
                <div class="row align-items-center">

                    <!-- Feature 1 -->
                    <div class="col-sm-6" data-aos="fade-left">
                        <div class="ENTER_NEW_WORLD_Content">
                            <h3><?= htmlspecialchars($section['feature_1_title']) ?></h3>
                            <p><?= nl2br(htmlspecialchars($section['feature_1_desc'])) ?></p>
                        </div>
                    </div>
                    <div class="col-sm-6" data-aos="fade-right" data-aos-duration="1200" data-aos-easing="ease-in-out">
                        <div class="ENTER_NEW_WORLD_img">
                            <img src="assets/images/covers-square/5.jpg" alt="Enter New Worlds" loading="lazy"
                                class="img-fluid">
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="col-sm-6" data-aos="fade-left">
                        <div class="ENTER_NEW_WORLD_img">
                            <img src="assets/images/fleeescape_img/esaperooms.jpg" alt="Enter New Worlds" loading="lazy"
                                class="img-fluid">
                        </div>
                    </div>
                    <div class="col-sm-6" data-aos="fade-right" data-aos-duration="1200" data-aos-easing="ease-in-out">
                        <div class="ENTER_NEW_WORLD_Content">
                            <h3><?= htmlspecialchars($section['feature_2_title']) ?></h3>
                            <p><?= nl2br(htmlspecialchars($section['feature_2_desc'])) ?></p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="col-sm-6" data-aos="fade-right" data-aos-duration="1200" data-aos-easing="ease-in-out">
                        <div class="ENTER_NEW_WORLD_Content">
                            <h3><?= htmlspecialchars($section['feature_3_title']) ?></h3>
                            <p><?= nl2br(htmlspecialchars($section['feature_3_desc'])) ?></p>
                        </div>
                    </div>
                    <div class="col-sm-6" data-aos="fade-left">
                        <div class="ENTER_NEW_WORLD_img">
                            <img src="assets/images/fleeescape_img/Corporateevents.jpg" alt="Enter New Worlds"
                                loading="lazy" class="img-fluid">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <section class="game_all_project_count text-white">
        <div class="container">
            <div class="col-lg-12 heading_main_section text-center" style="margin-bottom: 0px!important;">
                <h1>Escape Play <span style="color: #0cdede;"> Repeat</span> </h1>
                <p style="margin-bottom: 0;">Over 250,000 players and counting — discover why Flee Escape is Seattle’s
                    go-to destination <br /> for epic Escape Room Adventures and next-level VR battles. </p>
            </div>
            <div class="row text-center" id="counter-section">
                <div class="col-md-3 mb-4 mb-md-0 counter-col">
                    <div class="icon-circle mb-3 animate__animated animate__bounceIn">
                        <i class="fa-solid fa-gamepad fa-3x "></i>
                    </div>
                    <h2 class="display-4 fw-bold"><span class="counter" data-target="250000">0</span>+</h2>
                    <p class="mb-0">Games Played</p>
                </div>

                <div class="col-md-3 mb-4 mb-md-0 counter-col" style="animation-delay:0.2s;">
                    <div class="icon-circle mb-3 animate__animated animate__bounceIn">
                        <i class="fa-solid fa-users fa-3x "></i>
                    </div>
                    <h2 class="display-4 fw-bold"><span class="counter" data-target="15000">0</span>+</h2>
                    <p class="mb-0">Team building &amp; Events</p>
                </div>
                <div class="col-md-3 mb-4 mb-md-0 counter-col" style="animation-delay:0.4s;">
                    <div class="icon-circle mb-3 animate__animated animate__bounceIn">
                        <i class="fa-solid fa-mountain-sun fa-3x "></i>
                    </div>
                    <h2 class="display-4 fw-bold"><span class="counter" data-target="15">0</span></h2>
                    <p class="mb-0">Adventures</p>
                </div>
                <div class="col-md-3 counter-col" style="animation-delay:0.6s;">
                    <div class="icon-circle mb-3 animate__animated animate__bounceIn">
                        <i class="fa-solid fa-building fa-3x "></i>
                    </div>
                    <h2 class="display-4 fw-bold"><span class="counter" data-target="150">0</span>+</h2>
                    <p class="mb-0">Corporate Clients</p>
                </div>
            </div>
        </div>
    </section>

    <section>
        <style>
        /* Prevent layout shift when client logos load (reserves space) */
        .our_clients_slider .owl-carousel { min-height: 120px; }
        .our_clients_slider .owl-carousel .item .p-2 { display:flex; align-items:center; justify-content:center; height:120px; }
        .our_clients_slider .owl-carousel img { max-height:100px; width:auto; display:block; object-fit:contain; }
        </style>
        <div class="container our_clients_slider">
            <div class="col-lg-12 heading_main_section text-center">
                <h1 style="color: #0cdede;">OUR CLIENTS</h1>
            </div>
            <div class="row align-items-center">
             
                <div class="col-lg-12 mt-2">
                    <div class="owl-carousel owl-theme" id="ourclinets">
                        <?php
            include('admin/db.php');
            $stmt = $pdo->query("SELECT id, image FROM tbl_client_logo ORDER BY id DESC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $imgPath = "admin/uploads/" . htmlspecialchars($row['image']);
                if (!empty($row['image']) && file_exists($imgPath)) {
                    echo '
                    <div class="item">
                        <div class="p-2 rounded-10" data-bgcolor="rgba(255, 255, 255, .05)">
                            <img src="' . $imgPath . '" class="img-fluid" alt="Client Logo" loading="lazy" decoding="async">
                        </div>
                    </div>';
                }
            }
            ?>


                    </div>
                </div>
            </div>
        </div>
    </section>


    <div class="review_section">
        <div class="container py-5">
            <div class="col-lg-12 heading_main_section text-center">
                <!-- <div class="subtitle wow fadeInUp mb-3">Popular</div> -->
                <h1 style="color: #0cdede;">Our customers love us</h1>
                <p>Countless memories and endless fun with more than 100k customers over the years...
                </p>
            </div>
            <div class="owl-carousel owl-theme Our_customers_love">
                <?php
include('admin/db.php'); // adjust path as needed

$sql = "SELECT id, client_name, message, rating, image, created_at 
        FROM tbl_testimonials 
        ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $name = htmlspecialchars($row['client_name']);
    $message = htmlspecialchars($row['message']);
    $rating = intval($row['rating']);
    $image = !empty($row['image']) && file_exists("admin/uploads/" . $row['image']) 
             ? "admin/uploads/" . htmlspecialchars($row['image']) 
             : "https://i.pravatar.cc/60"; // fallback
    $date = date("M d, Y", strtotime($row['created_at']));

    // Generate stars based on rating
    $stars = str_repeat("★", $rating) . str_repeat("☆", 5 - $rating);
?>
                <div class="item">
                    <div class="card text-center h-100">
                        <div class="star-rating" style="color:#FFD700;font-size:20px;"><?php echo $stars; ?></div>
                        <p class="reting_cont"><?php echo $message; ?></p>
                        <img src="<?php echo $image; ?>" loading="lazy" alt="<?php echo $name; ?>"
                            class="profile-img remob_img">
                        <h5 class="fw-bold"><?php echo $name; ?></h5>
                        <div class="text-muted">Posted on <?php echo $date; ?></div>

                        <div class="google-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="injected-svg"
                                data-src="https://static.elfsight.com/icons/app-all-in-one-reviews-icons-google-multicolor.svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink">
                                <path fill="#2A84FC"
                                    d="M21.579 12.234c0-.677-.055-1.359-.172-2.025h-9.403v3.839h5.384a4.615 4.615 0 0 1-1.992 3.029v2.49h3.212c1.886-1.736 2.97-4.3 2.97-7.333Z">
                                </path>
                                <path fill="#00AC47"
                                    d="M12.004 21.974c2.688 0 4.956-.882 6.608-2.406l-3.213-2.491c-.893.608-2.047.952-3.392.952-2.6 0-4.806-1.754-5.597-4.113H3.095v2.567a9.97 9.97 0 0 0 8.909 5.491Z">
                                </path>
                                <path fill="#FFBA00"
                                    d="M6.407 13.916a5.971 5.971 0 0 1 0-3.817V7.531H3.095a9.977 9.977 0 0 0 0 8.953l3.312-2.568Z">
                                </path>
                                <path fill="#FC2C25"
                                    d="M12.004 5.982a5.417 5.417 0 0 1 3.824 1.494l2.846-2.846a9.581 9.581 0 0 0-6.67-2.593A9.967 9.967 0 0 0 3.095 7.53l3.312 2.57c.787-2.363 2.996-4.117 5.597-4.117Z">
                                </path>
                            </svg> Google
                        </div>
                    </div>
                </div>
                <?php
}
?>
            </div>
        </div>
    </div>

</div>
<?php
include('includes/footer.php');
?>