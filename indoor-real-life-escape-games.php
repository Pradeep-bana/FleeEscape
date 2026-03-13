<?php session_start();
include('link.php');
include('admin/db.php'); // ensure db connection

// Fetch meta details
$stmt = $pdo->query("SELECT page_title, keywords, page_description FROM tbl_escape_room_main_page LIMIT 1");
$meta = $stmt->fetch(PDO::FETCH_ASSOC);

// Set fallback values if fields are empty
$pageTitle = !empty($meta['page_title']) ? htmlspecialchars($meta['page_title']) :'Indoor Escape Rooms';
$metaKeywords = !empty($meta['keywords']) ? htmlspecialchars($meta['keywords']) : 'Escape Rooms, Indoor Games, Adventure Activities';
$metaDescription = !empty($meta['page_description']) ? htmlspecialchars($meta['page_description']) : 'Experience thrilling indoor escape rooms full of mystery and excitement.';

$canonicalURL = $link."indoor-real-life-escape-games";

include('includes/header.php');


// Fetch banner data
$stmt = $pdo->query("SELECT * FROM tbl_escape_room_main_page LIMIT 1");
$banner = $stmt->fetch(PDO::FETCH_ASSOC);

// Set fallback values if empty
$cover_photo = !empty($banner['cover_photo']) ? htmlspecialchars($banner['cover_photo']) : 'img/Escape-Room-Image-9.jpg';
$heading = !empty($banner['heading']) ? htmlspecialchars($banner['heading']) : 'Award-Winning';
$subheading = !empty($banner['middle_h2']) ? htmlspecialchars($banner['middle_h2']) : 'Escape Rooms in Redmond';

$sub_content = !empty($banner['sub_content']) ? $banner['sub_content'] : 'Escape Rooms in Redmond';
?>
<section>
    <div class="vr_page_banner all_baneer_IMG reponsive_inner_banner"
        style="background-image: url('admin/<?= $cover_photo ?>'); height:450px;">
        <div class="container">
            <div class="vr_page_banner_content" style="position: relative; z-index: 1;">
                <h1><?= $heading ?></h1>
                <p><?=htmlspecialchars_decode($subheading)?></p>
            </div>
        </div>
        <div class="vr_page_banner_add_log_top">
            <img src="img/silver-award-badge.png" loading="lazy" alt="Silver Award">
            <img src="img/bronze-award-badge.png" loading="lazy" alt="Bronze Award">
        </div>
    </div>
</section>


<section>
    <div class="container">
        <div class="Our_Indoor_Escape">
            <?= $sub_content ?>
        </div> </div>
</section>

<style>
.indoor_baneer_IMG p {
    color: #00d4ff;
    font-size: 44px;
    font-weight: 700;
}
</style>


<section class="in_door_vr_games_tab_home">
    <div class="gaming-container container">
        <div class="row">
            
                              <?php
include('admin/db.php'); // include your PDO connection

try {
    $stmt = $pdo->query("SELECT * FROM tbl_service ORDER BY id DESC");
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching data: " . $e->getMessage();
    exit;
}
 foreach ($rooms as $room): ?>         
                        <div class="col-sm-4">
                           
       
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
                            <p class="d-price">Layout: <span class="price"><?php echo htmlspecialchars($room['layout']); ?></span></p>
                            <p class="d-price">Difficulty: <span class="price"><?php echo htmlspecialchars($room['difficulty']); ?></span></p>
                            <p class="d-price">Success Rate: <span class="price"><?php echo htmlspecialchars($room['success_rate']); ?>%</span></p>
                            <p class="d-price">Players: <span class="price"><?php echo htmlspecialchars($room['players']); ?></span></p>
                            <a href="<?php echo htmlspecialchars($room['link']); ?>" class="book-btn">Learn more</a>
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
</section>


<?php

// Fetch data from tbl_escape_room_main_page
$stmt = $pdo->query("SELECT feature_1_title, feature_1_desc, feature_1_image, feature_2_title, feature_2_desc, feature_2_image, feature_3_title, feature_3_desc, feature_3_image FROM tbl_escape_room_main_page LIMIT 1");
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Fallback values (in case any field is missing)
$feature_1_title = htmlspecialchars($data['feature_1_title'] ?? 'Immersive VR multiplayer');
$feature_1_desc  = htmlspecialchars($data['feature_1_desc'] ?? 'Step into immersive VR multiplayer worlds...');
$feature_1_image = !empty($data['feature_1_image']) ? htmlspecialchars($data['feature_1_image']) : 'assets/images/covers-square/5.jpg';

$feature_2_title = htmlspecialchars($data['feature_2_title'] ?? 'THEMED ESCAPE ROOMS');
$feature_2_desc  = htmlspecialchars($data['feature_2_desc'] ?? 'Dive into our expertly crafted escape rooms...');
$feature_2_image = !empty($data['feature_2_image']) ? htmlspecialchars($data['feature_2_image']) : 'assets/images/fleeescape_img/esaperooms.jpg';

$feature_3_title = htmlspecialchars($data['feature_3_title'] ?? 'CORPORATE EVENTS AND PARTY PACKAGES');
$feature_3_desc  = htmlspecialchars($data['feature_3_desc'] ?? 'Our 9000 sq ft escape rooms and VR experience facility...');
$feature_3_image = !empty($data['feature_3_image']) ? htmlspecialchars($data['feature_3_image']) : 'assets/images/fleeescape_img/Corporateevents.jpg';
?>

<section>
    <div class="all_box_full_width">
        <div class="container">
            <div class="row align-items-center">

                <!-- Feature 1 -->
                <div class="col-sm-6" data-aos="fade-left">
                    <div class="ENTER_NEW_WORLD_Content">
                        <h3><?= $feature_1_title ?></h3>
                        <p><?= $feature_1_desc ?></p>
                    </div>
                </div>
                <div class="col-sm-6" data-aos="fade-right" data-aos-duration="1200" data-aos-easing="ease-in-out">
                    <div class="ENTER_NEW_WORLD_img">
                        <img src="admin/<?= $feature_1_image ?>" alt="<?= $feature_1_title ?>" loading="lazy" class="img-fluid">
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="col-sm-6" data-aos="fade-left">
                    <div class="ENTER_NEW_WORLD_img">
                        <img src="admin/<?= $feature_2_image ?>" loading="lazy" alt="<?= $feature_2_title ?>"  class="img-fluid">
                    </div>
                </div>
                <div class="col-sm-6" data-aos="fade-right" data-aos-duration="1200" data-aos-easing="ease-in-out">
                    <div class="ENTER_NEW_WORLD_Content">
                        <h3><?= $feature_2_title ?></h3>
                        <p><?= $feature_2_desc ?></p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="col-sm-6" data-aos="fade-right" data-aos-duration="1200" data-aos-easing="ease-in-out">
                    <div class="ENTER_NEW_WORLD_Content">
                        <h3><?= $feature_3_title ?></h3>
                        <p><?= $feature_3_desc ?></p>
                    </div>
                </div>
                <div class="col-sm-6" data-aos="fade-left">
                    <div class="ENTER_NEW_WORLD_img">
                        <img src="admin/<?= $feature_3_image ?>" loading="lazy" alt="<?= $feature_3_title ?>" class="img-fluid">
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>


<style>
.Our_Indoor_Escape {
    margin-top: 60px;
    margin-bottom: 50px;
}

.Our_Indoor_Escape h2 {
    color: #00d4ff;
    text-align: center;
    margin-bottom: 30px;
}

.Our_Indoor_Escape h3 {
    color: #00d4ff;
    margin-bottom: 18px;
}

.in_door_vr_games_tab_home .gaming-container {
    background: transparent !important;
}

.in_door_vr_games_tab_home .gaming-container .room-card {
    margin-top: 35px !important;
}
</style>
<section>
    <div class="container">
        <div class="faq-section my-5">
            <h2 class="text-center mb-4">Frequently Asked Questions</h2>
           <?php
include(__DIR__ . '/admin/db.php');


// Fetch FAQs where category_id = 1 and status = 1 (active)
$stmt = $pdo->prepare("SELECT id, question, answer FROM tbl_faq WHERE category_id = 1 AND status = 1 ORDER BY id ASC");
$stmt->execute();
$faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="accordion" id="faqAccordion">
    <?php if (!empty($faqs)) : ?>
        <?php foreach ($faqs as $index => $faq) : 
            $collapseId = 'faqCollapse' . $index;
            $headingId = 'faqHeading' . $index;
        ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="<?php echo $headingId; ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="false"
                        aria-controls="<?php echo $collapseId; ?>">
                        <?php echo htmlspecialchars($faq['question']); ?>
                        <span class="faq-toggle-icon ms-auto">
                            <span class="plus">+</span>
                            <span class="minus" style="display:none;">−</span>
                        </span>
                    </button>
                </h2>
                <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse"
                    aria-labelledby="<?php echo $headingId; ?>" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>No FAQs available at the moment.</p>
    <?php endif; ?>
</div>

        </div>
    </div>
</section>

<?php include('includes/footer.php'); ?>