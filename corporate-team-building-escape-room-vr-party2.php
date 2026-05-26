<?php session_start();
include('link.php');
include('admin/db.php'); // PDO connection

// ---------------------------
// SELECTED PAGE (example: team-building)
// ---------------------------
$selected_page = 'team-building';

// Fetch page data dynamically
$sql = "SELECT * FROM tbl_other_party_package_page WHERE selected_page = :page LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':page' => $selected_page]);

$pageData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pageData) {
    // fallback if page not found
    $pageData = [
        'page_title' => 'Default Page Title',
        'keywords' => 'default,keywords',
        'page_description' => 'Default page description.',
        // you can add more defaults here
    ];
}

// ---------------------------
// Set dynamic meta tags
// ---------------------------
$pageTitle = $pageData['page_title'];
$metaKeywords = $pageData['keywords'];
$metaDescription = $pageData['page_description'];
$canonicalURL = $link."corporate-team-building-escape-room-vr-party";
include('includes/header.php');
?>
<link rel="stylesheet" href="./assets/css/birthday.css">

<?php
include('admin/db.php');

// Fetch page data for team-building
$stmt = $pdo->prepare("SELECT * FROM tbl_other_party_package_page WHERE selected_page = :page LIMIT 1");
$stmt->execute([':page' => 'team-building']);
$pageData = $stmt->fetch(PDO::FETCH_ASSOC);

// Fallback text if database fields are empty
$white_heading = $pageData['white_heading'] ?? 'Ultimate Corporate Team Building in';
$blue_heading = $pageData['blue_heading'] ?? 'Escape Rooms & VR';
$sub_content = $pageData['sub_content'] ?? 'Transform your team dynamics with our award-winning combination of immersive escape room challenges and cutting-edge virtual reality experiences.';
$small_content = $pageData['small_content'] ?? 'Corporate team building with escape rooms and VR technology';
$tag_btn1 = $pageData['tag_btn1'] ?? 'View Packages';

$benefits = [
    $pageData['benefit1'] ?? '500+ Corporate Events',
    $pageData['benefit2'] ?? '98% Satisfaction Rate',
    $pageData['benefit3'] ?? 'Fortune 500 Trusted'
];

$heroHeading = trim(($white_heading ?? '') . ' ' . ($blue_heading ?? ''));
$heroText = $sub_content ?? '';
$heroImages = [];

if (!empty($pageData['thumbnail'])) {
    $heroImages = array_filter(array_map('trim', explode(',', $pageData['thumbnail'])));
}

if (empty($heroImages) && !empty($pageData['party_thumbnail'])) {
    $heroImages[] = $pageData['party_thumbnail'];
}
?>
<section class="Unforgettable_Birthday_section">
    <div class="container">
        <div class="row align-items-center">

            <!-- LEFT CONTENT -->
            <div class="col-md-6">
                <div class="Unforgettable_Birthday_content">

                    <!-- Dynamic Title -->
                    <h1 class="Unforgettable_Birthday_title">
                        <?= htmlspecialchars($heroHeading ?: 'Corporate Team Building in Escape Rooms & VR') ?>
                    </h1>

                    <!-- Dynamic Text -->
                    <p class="Unforgettable_Birthday_text">
                        <?= nl2br(htmlspecialchars($heroText ?: 'Transform your team dynamics with our immersive escape room challenges and virtual reality experiences.')) ?>
                    </p>

                    <div class="all_button_main_header" style="background-size: cover; background-repeat: no-repeat;">
                        <a href="#BuildingPackage" class="bg_bnt_custom bg_bnt_custom_tran">View Packages</a>
                    </div>
                </div>
            </div>

            <!-- RIGHT SLIDER -->
            <div class="col-md-6">
                <div class="Unforgettable_Birthday_slider">
                    <div class="Unforgettable_slides">

                        <!-- Dynamic Slider Images -->
                        <?php if (!empty($heroImages)): ?>
                            <?php foreach ($heroImages as $index => $img): ?>
                                <img src="admin/<?= htmlspecialchars($img) ?>"  
                                     <?php echo ($index === 0) ? 'fetchpriority="high"' : 'loading="lazy"'; ?> 
                                     decoding="async"
                                     alt="Corporate team building image <?= $index + 1 ?>">
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </div>

                    <!-- Arrows -->
                    <?php if (count($heroImages) > 1): ?>
                        <button class="slider-btn prev">&#10094;</button>
                        <button class="slider-btn next">&#10095;</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Features Row (kept static as per your request) -->
            <div class="Corporate_Team_Building_content_Features_box">
                <div class="Corporate_Team_Building_content_Features_box_items">
                    <i class="bi bi-brain"></i>
                    <p>Critical Thinking</p>
                </div>
                <div class="Corporate_Team_Building_content_Features_box_items">
                    <i class="bi bi-people"></i>
                    <p>Team Collaboration</p>
                </div>
                <div class="Corporate_Team_Building_content_Features_box_items">
                    <i class="bi bi-trophy"></i>
                    <p>Achievement</p>
                </div>
                <div class="Corporate_Team_Building_content_Features_box_items">
                    <i class="bi bi-lightning-charge"></i>
                    <p>Innovation</p>
                </div>
            </div>

            <!-- Bottom Stats -->
            <div class="row mt-4 text-secondary Corporate_Team_Building_4_cont">
                <?php foreach ($benefits as $benefit): ?>
                    <div class="col-md-4">✔ <?php echo htmlspecialchars($benefit); ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
 
<section class="Corporate_Team_Building">
    <div class="container text-center">
        <div class="Corporate_Team_Building_content-box ">
            <h2 class="fw-bold text-white">
                <?php echo htmlspecialchars($white_heading); ?> <br> <span class="highlight"><?php echo htmlspecialchars($blue_heading); ?></span>
            </h2>
            <p class="lead mt-3 mb-4 text-light">
                <?php echo htmlspecialchars($sub_content); ?>
            </p>
            <p class="small text-secondary">
                <?php echo htmlspecialchars($small_content); ?>
            </p>

            <div class="all_button_main_header order_summart_main_button mt-4">
                <a href="#BuildingPackage" class="bg_bnt_custom bg_bnt_custom_tran">
                    <i class="bi bi-box"></i> <?php echo htmlspecialchars($tag_btn1); ?>
                </a>
            </div>

            <!-- Features Row (kept static as per your request) -->
            <div class="Corporate_Team_Building_content_Features_box">
                <div class="Corporate_Team_Building_content_Features_box_items">
                    <i class="bi bi-brain"></i>
                    <p>Critical Thinking</p>
                </div>
                <div class="Corporate_Team_Building_content_Features_box_items">
                    <i class="bi bi-people"></i>
                    <p>Team Collaboration</p>
                </div>
                <div class="Corporate_Team_Building_content_Features_box_items">
                    <i class="bi bi-trophy"></i>
                    <p>Achievement</p>
                </div>
                <div class="Corporate_Team_Building_content_Features_box_items">
                    <i class="bi bi-lightning-charge"></i>
                    <p>Innovation</p>
                </div>
            </div>

            <!-- Bottom Stats -->
            <div class="row mt-4 text-secondary Corporate_Team_Building_4_cont">
                <?php foreach ($benefits as $benefit): ?>
                    <div class="col-md-4">✔ <?php echo htmlspecialchars($benefit); ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>


<!-- Custom Team Building Section -->
<?php
include('admin/db.php');

// Fetch data for selected page
$selectedPage = 'team-building'; // can be dynamic as needed
$stmt = $pdo->prepare("SELECT subway_heading, subway_sub_heading, feature_1_title, feature_1_desc, feature_2_title, feature_2_desc, feature_3_title, feature_3_desc FROM tbl_other_party_package_page WHERE selected_page = :selected_page LIMIT 1");
$stmt->execute([':selected_page' => $selectedPage]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Set default values if DB is empty
$subway_heading = $data['subway_heading'] ?? 'Need a Custom Team Building Solution?';
$subway_sub_heading = $data['subway_sub_heading'] ?? 'Our party specialists will work with you to design the perfect corporate team building experience.';

$feature_1_title = $data['feature_1_title'] ?? 'Expert Consultation';
$feature_1_desc  = $data['feature_1_desc'] ?? '15-minute call with specialists';

$feature_2_title = $data['feature_2_title'] ?? 'Custom Package';
$feature_2_desc  = $data['feature_2_desc'] ?? 'Tailored to your objectives';

$feature_3_title = $data['feature_3_title'] ?? 'Detailed Proposal';
$feature_3_desc  = $data['feature_3_desc'] ?? 'Comprehensive quote & timeline';
?>

<section class="Custom_Team_Building py-5">
    <div class="container text-center">
        <div class="custom-box p-5 rounded">
            <div class="section_heading_page">
                <h2 class="section-title"><?php echo htmlspecialchars($subway_heading); ?></h2>
                <p class="section-subtitle"><?php echo htmlspecialchars($subway_sub_heading); ?></p>
            </div>
            <!-- Feature Row -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="bi bi-person-check"></i>
                        <h5><?php echo htmlspecialchars($feature_1_title); ?></h5>
                        <p class="small"><?php echo htmlspecialchars($feature_1_desc); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="bi bi-gear"></i>
                        <h5><?php echo htmlspecialchars($feature_2_title); ?></h5>
                        <p class="small"><?php echo htmlspecialchars($feature_2_desc); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="bi bi-file-earmark-text"></i>
                        <h5><?php echo htmlspecialchars($feature_3_title); ?></h5>
                        <p class="small"><?php echo htmlspecialchars($feature_3_desc); ?></p>
                    </div>
                </div>
            </div>

            <!-- Bottom Notes -->
            <p class="N_obligation_Custom_Team_Building text-secondary">
                ✓ No obligation &nbsp; • &nbsp; ✓ Same-day response &nbsp; • &nbsp; ✓ Custom pricing available
            </p>
        </div>
    </div>
</section>


<?php
include('admin/db.php');

// Fetch data for selected page
$selectedPage = 'team-building'; // change dynamically if needed
$stmt = $pdo->prepare("SELECT party_thumbnail, video FROM tbl_other_party_package_page WHERE selected_page = :selected_page LIMIT 1");
$stmt->execute([':selected_page' => $selectedPage]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Set defaults if DB is empty
$party_thumbnail = !empty($data['party_thumbnail']) ? $data['party_thumbnail'] : './img/vr/video_img.webp';
$video_link      = !empty($data['video']) ? $data['video'] : '#'; // fallback link
?>
<section>
    <div class="container">
        <div class="vr_game_video_modal">
            <img src="admin/<?php echo htmlspecialchars($party_thumbnail); ?>" loading="lazy" alt="">
            <div class="vr_game_video_modal_content" data-bs-toggle="modal" data-bs-target="#videoModal">
                <i class="fa-regular fa-circle-play"></i>
            </div>
        </div>
    </div>
</section>

<!-- === Video Modal ====== -->
<div class="modal fade blur-modal" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="videoModalLabel"><?= $videoModalTitle = 'Corporate Team Building'; ?></h5>
                <button type="button" class="btn btn-sm close-btn" data-bs-dismiss="modal" aria-label="Close"
                    onclick="stopLocalVideo()">X</button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <!-- muted -->
                    <video id="localVideo" controls>
                        <source src="admin/<?php echo htmlspecialchars($video_link); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="party-packages card_time_slot_perent" id="BuildingPackage">
    <div class="container">
        <div class="section_heading_page">
            <h2 class="section-title">Choose Your Corporate Team Building Package</h2>
            <p class="section-subtitle">Scientifically designed experiences that deliver measurable improvements in team
                performance</p>
        </div>
   <style>
    .continue_next_step_party.disabled {
    opacity: 0.6;
    pointer-events: none;
    cursor: not-allowed;
}
</style>

<?php
include("party-package-all.php"); ?>

    </div>
</section>

<!-- ==============================
   Next Info Section with Animation
================================= -->
<?php
include('admin/db.php');

// Fetch data for selected page
$selectedPage = 'team-building'; // change dynamically if needed
$stmt = $pdo->prepare("SELECT why_choose_heading, why_choose_sub_heading, why_choose_1_title, why_choose_1_desc, why_choose_1_image, why_choose_2_title, why_choose_2_desc, why_choose_2_image FROM tbl_other_party_package_page WHERE selected_page = :selected_page LIMIT 1");
$stmt->execute([':selected_page' => $selectedPage]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Set defaults if fields are empty
$why_heading = !empty($data['why_choose_heading']) ? $data['why_choose_heading'] : 'Why Choose Our Corporate Team Building Programs?';
$why_subheading = !empty($data['why_choose_sub_heading']) ? $data['why_choose_sub_heading'] : 'Scientifically proven team building activities that deliver measurable results';

$choose1_title = !empty($data['why_choose_1_title']) ? $data['why_choose_1_title'] : 'Boost Team Performance by 40%';
$choose1_desc  = !empty($data['why_choose_1_desc']) ? $data['why_choose_1_desc'] : 'Our escape room challenges require teams to communicate effectively, delegate tasks, and solve complex problems under pressure.';
$choose1_img   = !empty($data['why_choose_1_image']) ? $data['why_choose_1_image'] : 'img/1Life_Escape_Room.jpg';

$choose2_title = !empty($data['why_choose_2_title']) ? $data['why_choose_2_title'] : 'Cutting-Edge VR Technology';
$choose2_desc  = !empty($data['why_choose_2_desc']) ? $data['why_choose_2_desc'] : 'Experience the future of team building with our state-of-the-art Zero Latency VR systems.';
$choose2_img   = !empty($data['why_choose_2_image']) ? $data['why_choose_2_image'] : 'img/2prison_escape.jpg';
?>

<section class="Why_Choose_Us py-5">
    <div class="container">
        <div class="section_heading_page">
            <h2 class="section-title"><?php echo htmlspecialchars($why_heading); ?></h2>
            <p class="section-subtitle">
                <?php echo htmlspecialchars($why_subheading); ?>
            </p>
        </div>

        <!-- Row 1 -->
        <div class="row align-items-center mb-5">
            <div class="col-md-6 build_second_img_hoverjsjf" data-aos="zoom-in-right">
                <img src="admin/<?php echo htmlspecialchars($choose1_img); ?>" loading="lazy" class="" alt="<?php echo htmlspecialchars($choose1_title); ?>">
            </div>
            <div class="col-md-6 text-white" data-aos="fade-left">
                <h3 class="fw-bold"><?php echo htmlspecialchars($choose1_title); ?></h3>
                <p><?php echo$choose1_desc; ?></p>
            </div>
        </div>

        <!-- Row 2 -->
        <div class="row align-items-center flex-row-reverse">
            <div class="col-md-6 build_second_img_hoverjsjf" data-aos="zoom-in-left">
                <img src="admin/<?php echo htmlspecialchars($choose2_img); ?>" loading="lazy" class="" alt="<?php echo htmlspecialchars($choose2_title); ?>">
            </div>
            <div class="col-md-6 text-white" data-aos="fade-right">
                <h3 class="fw-bold"><?php echo htmlspecialchars($choose2_title); ?></h3>
                <p><?php echo$choose2_desc; ?></p>
            </div>
        </div>

    </div>
</section>



<!-- Corporate Birthday Parties Section -->
<section class="Corporate_Team_Building_section py-5">
    <div class="section_heading_page">
        <h2 class="section-title">Why Choose Escape Rooms & VR Over <br> Traditional Team Building Events</h2>
        <p class="section-subtitle"> See how our immersive experiences outperform traditional corporate activities </p>
    </div>
    <div class="container mt-4">
        <div class="Corporate_Team_Building_table table-responsive">
            <table class="table align-middle text-center">
                <thead>
                    <tr>
                        <th>Activity</th>
                        <th>Teamwork & Collaboration</th>
                        <th>Problem Solving</th>
                        <th>Communication</th>
                        <th>Inclusivity</th>
                        <th>Weather Independent</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="Corporate_Team_Building_highlight">
                        <td><i class="bi bi-vr me-2"></i>Escape Rooms & VR</td>
                        <td class="Corporate_Team_Building_rating">★★★★★<br><small>Excellent</small></td>
                        <td class="Corporate_Team_Building_rating">★★★★★<br><small>Excellent</small></td>
                        <td class="Corporate_Team_Building_rating">★★★★★<br><small>Excellent</small></td>
                        <td class="Corporate_Team_Building_rating">★★★★★<br><small>All Abilities</small></td>
                        <td class="Corporate_Team_Building_yes"><i class="bi bi-check-lg"></i></td>
                    </tr>
                    <tr>
                        <td><span class="Corporate_Team_Building_dot text-warning">●</span> Bowling</td>
                        <td>★★☆☆☆<br><small>Limited</small></td>
                        <td>★☆☆☆☆<br><small>Minimal</small></td>
                        <td>★★☆☆☆<br><small>Basic</small></td>
                        <td>★★★☆☆<br><small>Moderate</small></td>
                        <td class="Corporate_Team_Building_yes"><i class="bi bi-check-lg"></i></td>
                    </tr>
                    <tr>
                        <td><span class="Corporate_Team_Building_dot text-success">●</span> Golf</td>
                        <td>★☆☆☆☆<br><small>Minimal</small></td>
                        <td>★☆☆☆☆<br><small>Individual</small></td>
                        <td>★☆☆☆☆<br><small>Limited</small></td>
                        <td>★★☆☆☆<br><small>Skill-Based</small></td>
                        <td class="Corporate_Team_Building_no">Weather Dependent</td>
                    </tr>
                    <tr>
                        <td><span class="Corporate_Team_Building_dot text-danger">●</span> Go-Karting</td>
                        <td>★☆☆☆☆<br><small>Competitive</small></td>
                        <td>★☆☆☆☆<br><small>Minimal</small></td>
                        <td>★★☆☆☆<br><small>Limited</small></td>
                        <td>★★☆☆☆<br><small>Physical Limits</small></td>
                        <td class="Corporate_Team_Building_yes"><i class="bi bi-check-lg"></i></td>
                    </tr>
                    <tr>
                        <td><span class="Corporate_Team_Building_dot text-info">●</span> Paintball</td>
                        <td>★★★☆☆<br><small>Good</small></td>
                        <td>★★★☆☆<br><small>Tactical</small></td>
                        <td>★★★☆☆<br><small>Good</small></td>
                        <td>★★☆☆☆<br><small>Physical Limits</small></td>
                        <td class="Corporate_Team_Building_no">Weather Dependent</td>
                    </tr>
                    <tr>
                        <td><span class="Corporate_Team_Building_dot text-primary">●</span> Laser Tag</td>
                        <td>★★★★☆<br><small>Good</small></td>
                        <td>★★☆☆☆<br><small>Minimal</small></td>
                        <td>★★☆☆☆<br><small>Basic</small></td>
                        <td>★★★☆☆<br><small>Good</small></td>
                        <td class="Corporate_Team_Building_yes"><i class="bi bi-check-lg"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php
include('admin/db.php');

// Fetch data for selected page
$selectedPage = 'team-building'; // set dynamically if needed
$stmt = $pdo->prepare("SELECT 
    proven_results_main_heading, 
    proven_results_sub_heading,
    proven_results_1_title, proven_results_1_content,
    proven_results_2_title, proven_results_2_content,
    proven_results_3_title, proven_results_3_content,
    proven_results_4_title, proven_results_4_content
    FROM tbl_other_party_package_page 
    WHERE selected_page = :selected_page LIMIT 1");
$stmt->execute([':selected_page' => $selectedPage]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Set default values if DB is empty
$main_heading = !empty($data['proven_results_main_heading']) ? $data['proven_results_main_heading'] : 'Proven Results for Corporate Teams';
$sub_heading = !empty($data['proven_results_sub_heading']) ? $data['proven_results_sub_heading'] : 'Join hundreds of companies who have transformed their team dynamics';


?>
<section class="game_all_project_count text-white">
    <div class="container">
        <div class="game_all_project_count_bg">
            <div class="section_heading_page">
                <h2 class="section-title"><?php echo htmlspecialchars($main_heading); ?></h2>
                <p class="section-subtitle">
                   <?php echo htmlspecialchars($sub_heading); ?>
                </p>
            </div>
            <div class="row text-center" id="counter-section">
                <div class="col-md-3 mb-4 mb-md-0 counter-col">
                    <h2 class="display-4 fw-bold"><span class="counter" data-target="<?php echo htmlspecialchars($data['proven_results_1_title']); ?>">0</span>+</h2>
                    <p class="mb-0"><?php echo htmlspecialchars($data['proven_results_1_content']); ?></p>
                </div>

                <div class="col-md-3 mb-4 mb-md-0 counter-col" style="animation-delay:0.2s;">
                    <h2 class="display-4 fw-bold"><span class="counter" data-target="<?php echo htmlspecialchars($data['proven_results_2_title']); ?>">0</span>%</h2>
                    <p class="mb-0"><?php echo htmlspecialchars($data['proven_results_2_content']); ?></p>
                </div>
                <div class="col-md-3 mb-4 mb-md-0 counter-col" style="animation-delay:0.4s;">
                    <h2 class="display-4 fw-bold"><span class="counter" data-target="<?php echo htmlspecialchars($data['proven_results_3_title']); ?>">0</span>%</h2>
                    <p class="mb-0"><?php echo htmlspecialchars($data['proven_results_3_content']); ?></p>
                </div>
                <div class="col-md-3 counter-col" style="animation-delay:0.6s;">

                    <h2 class="display-4 fw-bold"><span class=""></span> <?php echo htmlspecialchars($data['proven_results_4_title']); ?></h2>
                    <p class="mb-0"><?php echo htmlspecialchars($data['proven_results_4_content']); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!--===================Review Section======================-->
<?php
include('admin/db.php');

// Define selected page
$selectedPage = 'team-building'; // or dynamic based on current page

// Fetch testimonials for this page
$stmt = $pdo->prepare("SELECT client_name, message, rating, image FROM tbl_other_party_package_testimonial WHERE selected_page = :selected_page ORDER BY id ASC");
$stmt->execute([':selected_page' => $selectedPage]);
$testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class=" review_section">
    <div class="section_heading_page">
        <h2 class="section-title">What Corporate Leaders Say</h2>
        <p class="section-subtitle">Real feedback from executives and HR professionals</p>
    </div>

  <div class="owl-carousel owl-theme Our_customers_love">


        <?php
     

        // $stmt = $pdo->query("SELECT * FROM tbl_facility_rental_testimonial ORDER BY id DESC");
        // $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($testimonials as $r) {

            // Star rating
            $stars = str_repeat("★", (int)$r['rating']);

            // Image
            $img = !empty($r['image']) ? $r['image'] : "default-user.png"; // fallback image
            ?>
            <div class="item">
              <div class="card text-center h-100">


                <!-- Rating -->
                <div class="rating" style="color: #ffd700; font-size:20px;">
                    <?= $stars ?>
                </div>
                <!-- Message -->
                <p class="reting_cont" style="color: #fff;">"<?= htmlspecialchars($r['message']) ?>"</p>

                <div class="rre_profile_name">
                         <img src="admin/uploads/<?= $img ?>" loading="lazy" alt="<?= htmlspecialchars($r['client_name']) ?>" 
                         class="testimonial-img mb-2" style="width:50px; border-radius:50%;">
                    <p class="author" style="color: #ccc;"> <?= htmlspecialchars($r['client_name']) ?></p>
                    </div>

            </div>
            </div>

        <?php } ?>

    </div>
</section>

<?php
include('admin/db.php');

// Fetch data for selected page
$selectedPage = 'team-building';
$stmt = $pdo->prepare("SELECT 
    team_building_main_heading, 
    team_building_sub_heading,
    team_building_1_title, team_building_1_desc,
    team_building_2_title, team_building_2_desc,
    team_building_3_title, team_building_3_desc,
    team_building_4_title, team_building_4_desc
    FROM tbl_other_party_package_page 
    WHERE selected_page = :selected_page LIMIT 1");
$stmt->execute([':selected_page' => $selectedPage]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Fallback default values
$main_heading = $data['team_building_main_heading'] ?? 'Complete Corporate Team Building Solutions';
$sub_heading  = $data['team_building_sub_heading'] ?? 'Everything you need for a successful corporate team building event';
$tb1_title    = $data['team_building_1_title'] ?? 'Advanced Problem Solving';
$tb1_desc     = $data['team_building_1_desc'] ?? 'Multi-layered escape room puzzles designed to enhance critical thinking and creative problem-solving skills.';
$tb2_title    = $data['team_building_2_title'] ?? 'Strategic Team Collaboration';
$tb2_desc     = $data['team_building_2_desc'] ?? 'Scenarios requiring diverse skill sets, clear communication, and role delegation.';
$tb3_title    = $data['team_building_3_title'] ?? 'Immersive VR Technology';
$tb3_desc     = $data['team_building_3_desc'] ?? 'State-of-the-art Zero Latency VR systems with full-body tracking and wireless freedom.';
$tb4_title    = $data['team_building_4_title'] ?? 'Recognition & Achievement';
$tb4_desc     = $data['team_building_4_desc'] ?? 'Custom awards, professional photography, and achievement certificates.';
?>

<section>
    <div class="Complete_Corporate_Team ">
        <div class="container mt-5">
            <div class="section_heading_page">
                <h2 class="section-title"><?php echo htmlspecialchars($main_heading); ?></h2>
                <p class="section-subtitle"><?php echo htmlspecialchars($sub_heading); ?></p>
            </div>
            <div class="row g-4 text-center">
                <div class="col-md-3">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon"><i>🧠</i></div>
                        <h5 class="fw-bold"><?php echo htmlspecialchars($tb1_title); ?></h5>
                        <p><?php echo htmlspecialchars($tb1_desc); ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon"><i>👥</i></div>
                        <h5 class="fw-bold"><?php echo htmlspecialchars($tb2_title); ?></h5>
                        <p><?php echo htmlspecialchars($tb2_desc); ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon"><i>⚡</i></div>
                        <h5 class="fw-bold"><?php echo htmlspecialchars($tb3_title); ?></h5>
                        <p><?php echo htmlspecialchars($tb3_desc); ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon"><i>🏆</i></div>
                        <h5 class="fw-bold"><?php echo htmlspecialchars($tb4_title); ?></h5>
                        <p><?php echo htmlspecialchars($tb4_desc); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<section>
             <div class="container our_clients_slider">
             <div class="section_heading_page">
            <h2 class="section-title">OUR CLIENTS</h2>
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

<section class="team-transform-section animated-section">
    <div class="container">
        <div class="eam-transform-section_box">
            <div class="section_heading_page">
                <h2 class="section-title">Ready to Transform Your Team Dynamics?</h2>
                <p class="section-subtitle">
                    Join over 500 companies who have revolutionized their team building with our experiences.
                </p>
            </div>
            <div class="features">
                <span>Same-day quotes</span>
                <span>Flexible scheduling</span>
                <span>Group discounts</span>
            </div>

            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon-circle">
                            <i class="bi bi-telephone-fill"></i>
                        </div>
                        <h5 class="fw-bold mt-3">Call Us</h5>
                        <p>Speak directly with our corporate team building experts</p>
                        <h6> <a href="tel:4252871426" target="_blank" >425-287-1426</a></h6>
                        <p>MON - THURS 1pm - 9pm, FRIDAY 1pm - 10pm, SAT - SUN 12pm - 10pm</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon-circle">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                        <h5 class="fw-bold mt-3">Speak with our event experts</h5>
                        <p>Get comprehensive proposals and custom packages</p>
                        <h6><a  href="mailto:info@fleeescape.com" target="_blank">info@fleeescape.com</a></h6>
                        <p>Response within 2 hours during business days</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon-circle">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <h5 class="fw-bold mt-3">Visit our Facility</h5>
                        <p>Tour our escape rooms and VR setups</p>
                        <h6>
                            <a href="https://www.google.com/maps/search/2222+152nd+Ave+NE,+%23112+Redmond,+%3Cbr%3E+WA+98052/@47.6585298,-122.1599095,7688m/data=!3m1!1e3?entry=ttu&g_ep=EgoyMDI2MDEyMC4wIKXMDSoKLDEwMDc5MjA2OUgBUAM%3D" target="_blank">
                            2222 152nd Ave NE, #112 Redmond, <br> WA 98052 (Next to Goodwill Redmond)
                            </a>
                        </h6>
                        <p>Free parking available</p>
                    </div>
                </div>
            </div>
           <?php
include('admin/db.php');

// Define selected page
$selectedPage = 'team-building'; // Replace dynamically if needed

// Fetch FAQs for the selected page
$stmt = $pdo->prepare("SELECT id, question, answer FROM tbl_other_party_package_faq WHERE selected_page = :selected_page ORDER BY id ASC");
$stmt->execute([':selected_page' => $selectedPage]);
$faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="faq-section fade-in-up" style="animation-delay: 0.6s;">
    <h4>Frequently Asked Questions</h4>
    <div class="accordion" id="faqAccordion">
    <?php
        foreach ($faqs as $index => $faq){
        $faqId = $faq['id'];
        $question = htmlspecialchars($faq['question']);
        $answer = nl2br(htmlspecialchars($faq['answer'])); // preserves line breaks
        $collapseId = "faqCollapse" . $index;
        $headingId = "faqHeading" . $index;
    ?>
    <div class="accordion-item">
            <h2 class="accordion-header" id="<?= $headingId ?>">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#<?= $collapseId ?>" aria-expanded="false" aria-controls="<?= $collapseId ?>">
                    <?= $question ?>
                    <span class="faq-toggle-icon ms-auto">
                        <span class="plus">+</span>
                        <span class="minus" style="display:none;">−</span>
                    </span>
                </button>
            </h2>
            <div id="<?= $collapseId ?>" class="accordion-collapse collapse" aria-labelledby="<?= $headingId ?>"
                 data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    <?= $answer ?>
                </div>
            </div>
    </div>
    <?php } ?>
 </div>

 <div class="get-in-touch-section fade-in-up" style="animation-delay: 0.6s;">
    <div class="contact-form">
        <h4 data-aos="fade-up">Get In Touch</h4>
        <p data-aos="fade-up" data-aos-delay="100">
            Have questions or need a custom package? Fill out the form and our team will get back to you within 24 hours.
        </p>

   <form id="serviceEnquiryForm" action="send_service_enquiry.php" method="POST" novalidate>
    <div class="form-group">
        <div>
            <input id="enq_fullname" name="fullname" type="text" placeholder="Full Name *">
            <span class="error-msg"></span>
        </div>
        <div>
            <input id="enq_email" name="email" type="email" placeholder="Email Address *">
            <span class="error-msg"></span>
        </div>
    </div>

    <div class="form-group">
        <div>
            <input id="enq_phone" name="mobile" type="number" placeholder="Phone Number *" maxlength="10"
                pattern="[0-9]{10}" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);">
            <span class="error-msg"></span>
        </div>
        <div>
            <input id="enq_company" name="company" type="text" placeholder="Company Name">
            <span class="error-msg"></span>
        </div>
    </div>

    <div class="form-group">
        <div>
            <select id="enq_service" name="service">
                <option value="">Select Service *</option>
                <option value="team-building">Team Building</option>
                <option value="vr-event">VR Event</option>
                <option value="custom">Custom Package</option>
            </select>
            <span class="error-msg"></span>
        </div>
        <div>
            <input id="enq_date" name="event_date" type="text" placeholder="Select Date" readonly style="background-color: transparent;">
            <span class="error-msg"></span>
        </div>
    </div>

    <div class="form-group">
        <textarea id="enq_message" name="details" placeholder="Your Message *" required></textarea>
        <span class="error-msg"></span>
    </div>

    <button type="submit" class="bg_bnt_custom" style="width: 100%;">Send Message</button>
    <p class="note">By submitting, you agree to our terms and conditions. We'll contact you within 24 hours.</p>

    <div id="formResult" style="margin-top:15px; font-weight:bold;"></div>
</form>
</div>

        </div>
    </div>
</section>

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

<script>
const form = document.getElementById("serviceEnquiryForm");
const formResult = document.getElementById("formResult");

form.addEventListener("submit", function(e){
    e.preventDefault();

    // Clear previous errors
    document.querySelectorAll(".error-msg").forEach(el => el.innerText = "");
    formResult.innerText = "";

    let isValid = true;

    function showError(input, message){
        input.classList.add("input-error");
        input.nextElementSibling.innerText = message;
        isValid = false;
    }

    // Inputs
    const fullname = document.getElementById("enq_fullname");
    const email = document.getElementById("enq_email");
    const phone = document.getElementById("enq_phone");
    const service = document.getElementById("enq_service");
    const date = document.getElementById("enq_date");
    const message = document.getElementById("enq_message");

    if(fullname.value.trim().length < 3) showError(fullname, "Enter full name");
    if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) showError(email, "Enter valid email");
    if(!/^[0-9]{10}$/.test(phone.value)) showError(phone, "Enter valid phone");
    if(service.value === "") showError(service, "Select service");
    if(date.value === "") showError(date, "Select date");
    if(message.value.trim().length < 5) showError(message, "Enter message");

    if(!isValid) return;

    // AJAX submit
    const formData = new FormData(form);

    fetch(form.action, { method: "POST", body: formData })
    .then(res => res.json())
    .then(data => {
        formResult.style.color = data.status === "success" ? "green" : "red";
        formResult.innerText = data.message;
        if(data.status === "success") form.reset();
    })
    .catch(err => {
        formResult.style.color = "red";
        formResult.innerText = "Server error, try again later.";
    });
});
</script>


<script>
document.addEventListener("DOMContentLoaded", function() {
    
    function fixEnquiryDate() {
        const enqDateInput = document.getElementById("enq_date");
        if (!enqDateInput) return;

        // 1. Destroy any existing, conflicting Flatpickr instances on this field
        if (enqDateInput._flatpickr) {
            enqDateInput._flatpickr.destroy();
        }

        // 2. Remove any stray mobile clones generated by the global script
        const parent = enqDateInput.parentElement;
        if (parent) {
            const strayMobiles = parent.querySelectorAll('.flatpickr-mobile');
            strayMobiles.forEach(el => el.remove());
        }

        // 3. Force visibility and correct type
        enqDateInput.type = 'text';
        enqDateInput.classList.remove('d-none', 'flatpickr-input');
        enqDateInput.style.display = 'block';
        enqDateInput.style.visibility = 'visible';
        enqDateInput.style.opacity = '1';

        // 4. Re-initialize cleanly with disableMobile: true
        flatpickr(enqDateInput, {
            dateFormat: "Y-m-d", // Or "m-d-Y" depending on your preference
            disableMobile: true, // Forces the custom UI calendar instead of native mobile spinner
            allowInput: false    // Prevents mobile keyboard from opening
        });
    }

    // Run immediately
    fixEnquiryDate();

    // Run again after a short delay to override any delayed global scripts
    setTimeout(fixEnquiryDate, 1200);
});
</script>

<!-- Unforgettable_Birthday_slider -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const slides = document.querySelector(".Unforgettable_Birthday_slider .Unforgettable_slides");
    const images = document.querySelectorAll(".Unforgettable_Birthday_slider img");
    const prevBtn = document.querySelector(".slider-btn.prev");
    const nextBtn = document.querySelector(".slider-btn.next");

    if (!slides || images.length <= 1 || !prevBtn || !nextBtn) return;

    let index = 0;

    function showSlide(i) {
        if (i < 0) index = images.length - 1;
        else if (i >= images.length) index = 0;
        else index = i;

        slides.style.transform = `translateX(${-index * 100}%)`;
    }

    prevBtn.addEventListener("click", () => showSlide(index - 1));
    nextBtn.addEventListener("click", () => showSlide(index + 1));
    setInterval(() => {
        showSlide(index + 1);
    }, 3000);
});
</script>

<script src="assets/js/booking-js-party.js"></script>
<?php include('includes/footer.php'); ?>
