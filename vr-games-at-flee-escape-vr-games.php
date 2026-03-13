<?php session_start();
include('link.php');
include('admin/db.php'); 
// Fetch meta details
$stmt = $pdo->query("SELECT page_title, keywords, page_description FROM tbl_vr_main_page LIMIT 1");
$meta = $stmt->fetch(PDO::FETCH_ASSOC);

// Set fallback values if fields are empty
$pageTitle = !empty($meta['page_title']) ? htmlspecialchars($meta['page_title']) :'Immersive VR multiplayer';
$metaKeywords = !empty($meta['keywords']) ? htmlspecialchars($meta['keywords']) : 'Escape Rooms, Indoor Games, Adventure Activities';
$metaDescription = !empty($meta['page_description']) ? htmlspecialchars($meta['page_description']) : 'Experience thrilling indoor escape rooms full of mystery and excitement.';
$canonicalURL = $link."vr-games-at-flee-escape-vr-games";
include('includes/header.php');
?>

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

.Our_Indoor_Escape h5 {
    color: white;
    text-align: center;
    margin-bottom: 30px;
}

.Our_Indoor_Escape h3 {
    color: #00d4ff;
    margin-bottom: 18px;
}


.bg_bnt_custom {
    /*background: #00d4ff;*/
    /*color: #0a0f1c;*/
    /*box-shadow: 0 0 20px rgba(0, 212, 255, 0.5);*/
    /*transform: translateY(-2px);*/
    /*padding: 16px 40px;*/
    /*border-radius: 10px;*/
    /*font-weight: 600;*/
    /*text-transform: uppercase;*/
    /*letter-spacing: 1px;*/
    /*transition: all 0.3s ease;*/
    /*text-decoration: none;*/
    /*display: inline-block;*/
    /*margin-top: 20px;*/
    /*border: 2px solid transparent;*/
    /*cursor: pointer;*/
    /*display: flex;*/
    /*justify-self: center;*/
}

.bg_bnt_custom:hover {
    /*background: transparent;*/
    /*border: 2px solid #00d4ff;*/
    /*color: #00d4ff;*/

}

.Our_Indoor_Escape_p {
    /* justify-self: center; */
}
</style>

<?php
include('admin/db.php');

// Fetch VR main page content
$stmt = $pdo->query("SELECT * FROM tbl_vr_main_page LIMIT 1");
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Fallback empty values if no data
if (!$data) {
    $data = [
        'heading' => '',
        'middle_h2' => '',
        'sub_content' => '',
        'sub_heading_content' => '',
        'cover_photo' => ''
    ];
}
?>

<section>
    <div class="vr_page_banner all_baneer_IMG"
        style="background-image: url(admin/<?php echo htmlspecialchars($data['cover_photo']); ?>); height:650px">
        <div class="container">
            <div class="vr_page_banner_content" style="position: relative; z-index: 1;">
                <h1><?php echo nl2br(htmlspecialchars($data['heading'])); ?></h1>
                <h4><?php echo nl2br(htmlspecialchars($data['middle_h2'])); ?></h4>
                <p><?php echo nl2br(htmlspecialchars($data['sub_heading_content'])); ?></p>
                <a class="bg_bnt_custom mt-3" href="https://booking.zerolatencyvr.com/en/book-now/seattle/?zl_s=GlobalWebsite&zl_sp=%2Fen%2Fseattle&zl_ct=NavBar&zl_cin=NavBarDesktop&zl_cti=Main&_gl=1*4hguz7*_gcl_aw*R0NMLjE3NjQ3MzU1OTYuQ2owS0NRaUF1YnJKQmhDYkFSSXNBSElkeEQ4cTVaOTNxWWZSbXo5M2xyUmRIWkxTc0RGOGQydjNFT3lWTDBKeTAzMHZVOEFhcWV6bjdCNGFBZ1dpRUFMd193Y0I.*_gcl_au*NjQxMjAzOTYwLjE3NjQxMTI1ODY." target="_blank" >
                    BOOK NOW
                </a>
            </div>
        </div>
    </div>
</section>


<section>
    <div class="container">
        <div class="Our_Indoor_Escape text-center">
            <h2>A Variety of VR Games to Challenge <br> and Excite You</h2>
            <p>Our expansive library of VR games is designed to thrill every type of player. From fast-paced
                shooters to intricate puzzles and open-world <br> adventures, we’ve got something for everyone: </p>
        </div>
    </div>
</section>

<section class="in_door_vr_games_tab_home">
    <div class="gaming-container container" style="background: transparent;">
       <div class="row">
                        
                           <?php

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
   <div class="col-sm-4">
        <div class="room-card">
            <div class="room-image haunted-hotel" style="background-image: url('admin/<?php echo $bannerImage; ?>');">
            </div>
           
            <div class="room-content">
                <div class="room-content_all_ah">
                    <div class="room-details">
                        <h3 class="room-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p class="d-price">Category: <span class="price <?php echo $difficultyClass; ?>"><?php echo htmlspecialchars($row['category']); ?></span></p>
                        <p class="d-price">Game Duration: <span class="price"><?php echo htmlspecialchars($row['duration_minutes']); ?> min</span></p>
                        <p class="d-price">Difficulty: <span class="price"><?php echo htmlspecialchars($row['difficulty']); ?> </span></p>
                        <p class="d-price">Players: <span class="price"><?php echo htmlspecialchars($row['min_players']); ?>-<?php echo htmlspecialchars($row['max_players']); ?></span></p>
                        <a href="vr/<?php echo htmlspecialchars($row['slug']); ?>" class="book-btn">LEARN MORE</a>
                    </div>
                </div>
            </div>
        </div>
       
     </div>
<?php } ?>
                      
                       
                    </div>
    </div>
</section>


<?php
// Fetch data from tbl_vr_main_page
$stmt = $pdo->query("SELECT * FROM tbl_vr_main_page LIMIT 1");
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Default empty array if no data found
if (!$data) {
    $data = [
        'feature_1_title' => '',
        'feature_1_desc' => '',
        'feature_1_image' => '',
        'feature_2_title' => '',
        'feature_2_desc' => '',
        'feature_2_image' => '',
        'feature_3_title' => '',
        'feature_3_desc' => '',
        'feature_3_image' => '',
         'sub_content' => ''
    ];
}
?>

<section>
    <div class="all_box_full_width">
        <div class="container">
            <div class="row align-items-center">

                <!-- Feature 1 -->
                <div class="col-sm-6" data-aos="fade-left">
                    <div class="ENTER_NEW_WORLD_Content">
                        <h3><?= htmlspecialchars($data['feature_1_title']) ?></h3>
                        <p><?= nl2br(htmlspecialchars($data['feature_1_desc'])) ?></p>
                    </div>
                </div>
                <div class="col-sm-6" data-aos="fade-right" data-aos-duration="1200" data-aos-easing="ease-in-out">
                    <div class="ENTER_NEW_WORLD_img">
                        <img src="admin/<?= htmlspecialchars($data['feature_1_image']) ?>" loading="lazy" alt="<?= htmlspecialchars($data['feature_1_title']) ?>" class="img-fluid">
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="col-sm-6" data-aos="fade-left">
                    <div class="ENTER_NEW_WORLD_img">
                        <img src="admin/<?= htmlspecialchars($data['feature_2_image']) ?>" loading="lazy" alt="<?= htmlspecialchars($data['feature_2_title']) ?>" class="img-fluid">
                    </div>
                </div>
                <div class="col-sm-6" data-aos="fade-right" data-aos-duration="1200" data-aos-easing="ease-in-out">
                    <div class="ENTER_NEW_WORLD_Content">
                        <h3><?= htmlspecialchars($data['feature_2_title']) ?></h3>
                        <p><?= nl2br(htmlspecialchars($data['feature_2_desc'])) ?></p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="col-sm-6" data-aos="fade-right" data-aos-duration="1200" data-aos-easing="ease-in-out">
                    <div class="ENTER_NEW_WORLD_Content">
                        <h3><?= htmlspecialchars($data['feature_3_title']) ?></h3>
                        <p><?= nl2br(htmlspecialchars($data['feature_3_desc'])) ?></p>
                    </div>
                </div>
                <div class="col-sm-6" data-aos="fade-left">
                    <div class="ENTER_NEW_WORLD_img">
                        <img src="admin/<?= htmlspecialchars($data['feature_3_image']) ?>" loading="lazy" alt="<?= htmlspecialchars($data['feature_3_title']) ?>" class="img-fluid">
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>


<div class="container">
    <a class=" bg_bnt_custom mt-3" href="booking#vr-game" target="new">
    Book Your Next Adventure
</a>
</div>

<section>
    <div class="container">
       
        <div class="Our_Indoor_Escape">
    <?= strip_tags($data['sub_content'], '<h2><h3><h4><p><ul><li><br>') ?>
</div>

    </div>
</section>
<style>
    .Our_Indoor_Escape h2,
.Our_Indoor_Escape h3,
.Our_Indoor_Escape h4 {
    color: #00d4ff;               /* neon blue highlight */
    font-weight: 700;
    margin-bottom: 12px;
    letter-spacing: -0.5px;
    text-shadow: 0px 0px 10px rgba(0, 212, 255, 0.5);
}

/* H2 — Big Title */
.Our_Indoor_Escape h2 {
    font-size: 36px;
    text-align: center;
    margin: 30px 0 15px;
}

/* H3 — Medium Title */
.Our_Indoor_Escape h3 {
    font-size: 28px;
    margin-top: 25px;
}

/* H4 — Small Title */
.Our_Indoor_Escape h4 {
    font-size: 22px;
    margin-top: 20px;
}

</style>

<section>
    <div class="container">
        <div class="faq-section my-5">
            <h2 class="text-center mb-4">Frequently Asked Questions</h2>
           <?php

// Fetch FAQs where category_id = 1 and status = 1 (active)
$stmt = $pdo->prepare("SELECT id, question, answer FROM tbl_faq WHERE category_id = 2 AND status = 1 ORDER BY id ASC");
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