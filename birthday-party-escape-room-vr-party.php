<?php  session_start();
include('link.php');

include('admin/db.php');

$stmt = $pdo->query("SELECT * FROM tbl_birthday_party_page LIMIT 1");
$data = $partyPageData = $stmt->fetch(PDO::FETCH_ASSOC);

$pageTitle = !empty($data['page_title']) ? $data['page_title'] : 'Escape Room Birthday Parties';
$metaKeywords = !empty($data['keywords']) ? $data['keywords'] : '';
$metaDescription = !empty($data['page_description']) ? $data['page_description'] : '';

$canonicalURL = $link."birthday-party-escape-room-vr-party";
include('includes/header.php');
?>
<style>
    .booking_tab_content{
         justify-content: center;   
    }
</style>
<!--<link rel="stylesheet" href="./assets/css/birthday.css">-->
<?php 
// Convert comma-separated images into array
$sliderImages = array_filter(array_map('trim', explode(',', $data['thumbnail'])));
?>
<section class="Unforgettable_Birthday_section">
    <div class="container">
        <div class="row align-items-center">

            <!-- LEFT CONTENT -->
            <div class="col-md-6">
                <div class="Unforgettable_Birthday_content">

                    <!-- Dynamic Title -->
                    <h1 class="Unforgettable_Birthday_title">
                        <?= htmlspecialchars($data['heading']) ?>
                    </h1>

                    <!-- Dynamic Text -->
                    <p class="Unforgettable_Birthday_text">
                        <?= nl2br(htmlspecialchars($data['sub_content'])) ?>
                    </p>

                    <div class="all_button_main_header" style="background-size: cover; background-repeat: no-repeat;">
                        <a href="javascript:void(0)" class="bg_bnt_custom bg_bnt_custom_tran scrollToParty">Book Birthday Party</a>
                    </div>
                </div>
            </div>

            <!-- RIGHT SLIDER -->
            <div class="col-md-6">
                <div class="Unforgettable_Birthday_slider">
                    <div class="Unforgettable_slides">

                        <!-- Dynamic Slider Images -->
                        <?php if (!empty($sliderImages)): ?>
                            <?php foreach ($sliderImages as $index => $img): ?>
                                <img src="admin/<?= htmlspecialchars($img) ?>"  loading="lazy" decoding="async"
                                     alt="Birthday Party Image <?= $index + 1 ?>">
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </div>

                    <!-- Arrows -->
                    <button class="slider-btn prev">&#10094;</button>
                    <button class="slider-btn next">&#10095;</button>
                </div>
            </div>

        </div>
    </div>
</section>



<section class="Ultimate_Birthday_Experience">
    <div class="container">
        <div class="section_heading_page">

            <!-- Static Small Heading -->
            <span class="badge-custom">🎉 Birthday Parties</span>

            <!-- Dynamic Main Title -->
            <h2 class="section-title">
                <?= htmlspecialchars($data['subway_heading']) ?>
            </h2>

            <!-- Dynamic Subtitle -->
            <p class="section-subtitle">
                <?= nl2br(htmlspecialchars($data['subway_sub_heading'])) ?>
            </p>
        </div>

        <div class="row g-4 align-items-center">

            <!-- Left: Dynamic Features -->
            <div class="col-md-6">
                <div class="features-card">

                    <!-- Feature 1 -->
                    <div class="feature-box">
                        <i class="bi bi-people-fill"></i>
                        <div>
                            <h5><?= htmlspecialchars($data['feature_1_title']) ?></h5>
                            <p><?= nl2br(htmlspecialchars($data['feature_1_desc'])) ?></p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="feature-box">
                        <i class="bi bi-gift-fill"></i>
                        <div>
                            <h5><?= htmlspecialchars($data['feature_2_title']) ?></h5>
                            <p><?= nl2br(htmlspecialchars($data['feature_2_desc'])) ?></p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="feature-box">
                        <i class="bi bi-clock-fill"></i>
                        <div>
                            <h5><?= htmlspecialchars($data['feature_3_title']) ?></h5>
                            <p><?= nl2br(htmlspecialchars($data['feature_3_desc'])) ?></p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Right: Dynamic Image -->
            <div class="col-md-6">
                <div class="birthday-card">
                    <img src="admin/<?= htmlspecialchars($data['party_thumbnail']) ?>"  loading="lazy" decoding="async"
                         alt="Birthday Party" 
                         class="img-fluid" />
                </div>
            </div>

        </div>
    </div>
</section>


<!-- Corporate Birthday Parties Section -->
<section class="Corporate_Team_Building_section py-5" id="Parties_bir">
    <div class="section_heading_page">
        <span class="badge-custom">🎉 Birthday Parties</span>
        <h2 class="section-title">Why Choose Escape Rooms & VR Over <br> Traditional Birthday Parties?</h2>
        <p class="section-subtitle"> See how our immersive experiences outperform traditional birthday party activities </p>
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

    <!-- Features -->
    <div class="container mt-5">
     <div class="row g-4 text-center">
    <?php
    // Optional icons for each feature
    $icons = ['bi-people-fill', 'bi-lightbulb-fill', 'bi-calendar-check-fill'];

    for ($i = 1; $i <= 3; $i++):
        $title = htmlspecialchars($data["example_{$i}_title"]);
        $desc  = nl2br(htmlspecialchars($data["example_{$i}_content"]));
        $icon  = $icons[$i - 1];
    ?>
        <div class="col-md-4">
            <div class="Corporate_Team_Building_card p-4 h-100">
                <i class="bi <?= $icon ?> fs-2 mb-3"></i>
                <h5 class="fw-bold"><?= $title ?></h5>
                <p><?= $desc ?></p>
            </div>
        </div>
    <?php endfor; ?>
</div>
        <!--<div class="text-center mt-3">-->
        <!--    <a href="#Parties_bir" class="bg_bnt_custom bg_bnt_custom_tran">-->
        <!--        <i class="bi bi-people-fill me-2"></i> Book Birthday Parties-->
        <!--    </a>-->
        <!--</div>-->
    </div>
</section>


<section class="party-packages card_time_slot_perent" id="party-package">
    <div class="container">
        <div class="section_heading_page">
            <span class="badge-custom">
                🎉 Birthday Packages
            </span>
            <h2 class="section-title">Choose Your Perfect Party Package</h2>
            <p class="section-subtitle">Customizable birthday party experiences for groups of 8–24 people </p>
        </div>

       <style>
    .continue_next_step_party.disabled {
    opacity: 0.6;
    pointer-events: none;
    cursor: not-allowed;
}
</style>

<?php 
$videoModalTitle = 'Birthday Party';
include("party-package-all.php"); ?>



    </div>
</section>


<section class="Need_Custom_Package">
    <div class="container">
        <div class="Need_Custom_Package_box">
            <h3>Need a Custom Package?</h3>
            <p>Have a larger group or special requirements? We can create a custom birthday package tailored to your
                needs.</p>
            <div class="all_button_main_header order_summart_main_button mt-4">
                <!--<a href="#birt_scroll_opne_contact" class="bg_bnt_custom">Request Custom Quote</a>-->
                <a href="javascript:void(0)" 
                   class="bg_bnt_custom scrollToContact">
                   Request Custom Quote
                </a>
                <a href="tel:4252871426" class="bg_bnt_custom bg_bnt_custom_tran">Call 425-287-1426</a>
            </div>
        </div>
    </div>
</section>

<section class="Immersive_Escap_Adventures Ultimate_Birthday_Experience gaming-container">
    <div class="section_heading_page">
        <span class="badge-custom">🎉 Birthday Parties</span>
        <h2 class="section-title">Immersive Escape Room Adventures</h2>
        <p class="section-subtitle"> Test your wits and teamwork with our challenging and themed escape rooms </p>
    </div>
    <div class="container ">
        <div class="owl-carousel owl-theme new_all_game_slider">
            
            
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
        <div class="item">
            <div class="room-card">
                <div class="room-image haunted-hotel check kaka"
                    style="background-image: url('<?= BASE_URL ?>admin/uploads/<?php echo htmlspecialchars($room['thumbnail']); ?>');">
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


<section class="Immersive_Escap_Adventures  gaming-container">
    <div class="container ">
        <div class="section_heading_page">
            <span class="badge-custom">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-gamepad2 h-3 w-3 mr-1">
                    <line x1="6" x2="10" y1="11" y2="11"></line>
                    <line x1="8" x2="8" y1="9" y2="13"></line>
                    <line x1="15" x2="15.01" y1="12" y2="12"></line>
                    <line x1="18" x2="18.01" y1="10" y2="10"></line>
                    <path
                        d="M17.32 5H6.68a4 4 0 0 0-3.978 3.59c-.006.052-.01.101-.017.152C2.604 9.416 2 14.456 2 16a3 3 0 0 0 3 3c1 0 1.5-.5 2-1l1.414-1.414A2 2 0 0 1 9.828 16h4.344a2 2 0 0 1 1.414.586L17 18c.5.5 1 1 2 1a3 3 0 0 0 3-3c0-1.545-.604-6.584-.685-7.258-.007-.05-.011-.1-.017-.151A4 4 0 0 0 17.32 5z">
                    </path>
                </svg> Zero Latency VR
            </span>
            <h2 class="section-title">Free-Roam VR Experiences</h2>
            <p class="section-subtitle"> Cutting-edge virtual reality adventures with full freedom of movement - 8
                incredible worlds to explore </p>
        </div>
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
            <div class="room-image haunted-hotel" style="background-image: url('<?= BASE_URL ?>admin/<?php echo $bannerImage; ?>');"> 
            </div>
           
            <div class="room-content">
                <div class="room-content_all_ah">
                    <h3 class="room-title"><?php echo htmlspecialchars($row['title']); ?></h3>
                    <div class="room-details">
                        
                        <p class="d-price">Difficulty: <span class="price <?php echo $difficultyClass; ?>"><?php echo htmlspecialchars($row['difficulty']); ?></span></p>
                        <p class="d-price">Game Duration: <span class="price"><?php echo htmlspecialchars($row['duration_minutes']); ?> min</span></p>
                        <p class="d-price">Players: <span class="price"><?php echo htmlspecialchars($row['min_players']); ?>-<?php echo htmlspecialchars($row['max_players']); ?></span></p>
                        <a href="vr/<?php echo htmlspecialchars($row['slug']); ?>" class="book-btn">LEARN MORE</a>
                    </div>
                </div>
            </div>
        </div>
        <?php if (!empty($row['bottom_heading'])): ?>
        <h6 class="color_combination_vr_function text-center"><?php echo $row['bottom_heading']; ?></h6>
        <?php endif; ?>
    </div>
<?php } ?> 
            
     

        </div>
    </div>
  <div class="container Why_Choose_VR">
      
    <div class="section_heading_page">

        <!-- Dynamic Main Heading -->
        <h2 class="section-title"><?= htmlspecialchars($partyPageData['occasions_heading']) ?></h2>

        <!-- Dynamic Subtitle -->
        <p class="section-subtitle"><?= nl2br(htmlspecialchars($partyPageData['occasions_sub_heading'])) ?></p>
    </div>

    <div class="row text-center">
        <?php
        // Optional icons for each card
        $icons = ['fa-gamepad', 'fa-users', 'fa-star', 'fa-clock'];

        for ($i = 1; $i <= 4; $i++):
            $title = htmlspecialchars($partyPageData["occasion_{$i}_title"]);
            $desc  = nl2br(htmlspecialchars($partyPageData["occasion_{$i}_desc"]));
            $icon  = $icons[$i - 1];
        ?>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="choose-card">
                    <div class="icon-circle">
                        <i class="fa-solid <?= $icon ?>"></i>
                    </div>
                    <h5 class="choose-title"><?= $title ?></h5>
                    <p class="choose-text"><?= $desc ?></p>
                </div>
            </div>
        <?php endfor; ?>
    </div>
</div>



</section>


<div class="review_section">
    <div class="section_heading_page">
        <span class="badge-custom">
            <i class="fa-solid fa-star-half-stroke"></i> Birthday Parties Customers Reviews
        </span>
        <h2 class="section-title">What Our Customers Say</h2>
        <p class="section-subtitle">Don't just take our word for it - hear from families who've celebrated with us </p>
    </div>

 <div class="owl-carousel owl-theme Our_customers_love">
    <?php
    // Fetch testimonials from DB
    $stmt = $pdo->query("SELECT client_name, message, rating, image FROM tbl_birthday_party_testimonial ORDER BY id ASC");
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($testimonials as $testimonial):
        $name    = htmlspecialchars($testimonial['client_name']);
        $message = nl2br(htmlspecialchars($testimonial['message']));
        $rating  = intval($testimonial['rating']); // convert rating to integer
        $image   = !empty($testimonial['image']) ? htmlspecialchars($testimonial['image']) : ''; // optional client image
    ?>
         <div class="item">
              <div class="card text-center h-100">
                    <div class="rating" style="color: #ffd700;">
                        <?= str_repeat('★', $rating) ?>
                    </div>
                    <p class="reting_cont" style="color: #fff;"><?= $message ?></p>
                    <div class="rre_profile_name">
                        <?php if ($image): ?>
                        <img src="admin/uploads/<?= $image ?>" loading="lazy" decoding="async" alt="<?= $name ?>" class="testimonial-img mb-2" style="width:50px; border-radius:50%;">
                    <?php endif; ?>
                    <p class="author" style="color: #ccc;">- <?= $name ?></p>
                    </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</div>

<!-- Experience Gallery Section -->
<section class="Experience_Gallery py-5">
    <div class="container text-center">
        <div class="section_heading_page">
            <span class="badge-custom">
                <i class="fa-solid fa-images"></i> Birthday Parties Gallery
            </span>
            <h2 class="section-title">Experience Gallery</h2>
            <p class="section-subtitle">Take a peek at the fun that awaits you </p>
        </div>

     <div class="row g-4">
    <?php
    // Fetch gallery images from DB
    $stmt = $pdo->query("SELECT first_heading, second_heading, image FROM tbl_birthday_party_gallery ORDER BY id ASC");
    $galleryItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($galleryItems as $item):
        $firstHeading  = htmlspecialchars($item['first_heading']);
        $secondHeading = htmlspecialchars($item['second_heading']);
        $image         = htmlspecialchars($item['image']);
    ?>
        <div class="col-md-3 col-6">
            <div class="gallery-item position-relative">
                <a href="admin/uploads/<?= $image ?>" data-fancybox="escape">
                    <img src="admin/uploads/<?= $image ?>" loading="lazy" decoding="async" class="img-fluid rounded shadow-sm" alt="<?= $firstHeading ?>">
                    <div class="overlay">
                        <i class="fa-solid fa-eye"></i>
                    </div>
                    <div class="overlay-text">
                        <h6><?= $firstHeading ?></h6>
                        <p><?= $secondHeading ?></p>
                    </div>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

    </div>
</section>


<!-- faq_pary_ask Section -->
<section class="  ">
    <div class="container ">
        <div class="faq-section">
            <div class="section_heading_page">
                <span class="badge-custom">
                   Birthday Parties FAQ
                </span>
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="section-subtitle">Everything you need to know about our birthday party packages </p>
            </div>
          <div class="accordion" id="faqAccordion">
    <?php
    // Fetch FAQs from DB
    $stmt = $pdo->query("SELECT id, question, answer FROM tbl_birthday_party_faq ORDER BY id ASC");
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($faqs as $index => $faq):
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
    <?php endforeach; ?>
</div>

        </div>
    </div>
</section>




<section class="Party_contact_form_section" id="birt_scroll_opne_contact">
    <div class="container">
        <div class="Party_contact_form_container">
            <!-- Heading + Buttons -->
            <div class="Party_contact_form_header d-flex justify-content-between align-items-center">
                <div>
                    <h2>Ready to Book Your Party?</h2>
                    <p>Contact us today to check availability and customize your perfect birthday experience.</p>
                </div>
               <div class="all_button_main_header order_summart_main_button ">
                    <a href="javascript:void(0)" 
                       class="bg_bnt_custom scrollToParty">
                       🎉 Book Birthday Party
                    </a>

                   <a href="https://maps.app.goo.gl/wLQBAEprUetD7t3U7" 
                       class="bg_bnt_custom bg_bnt_custom_tran" 
                       target="_blank" 
                       rel="noopener noreferrer">
                       <i class="fas fa-location-dot"></i> Get Directions
                    </a>
                </div>

            </div>

            <!-- Contact Info + Form -->
            <div class="Party_contact_form_content">
                <!-- Left: Info -->
                <div class="Party_contact_form_info">
                    <h3>Contact Information</h3>
                    <ul>
                        <li>
                            <i class="fa-solid fa-location-dot"></i>
                            <span><strong>Address</strong><br> 
                            2222 152nd Ave NE, #112, Redmond WA 98052 (Next to Goodwill Redmond)
                           </span>
                        </li>
                        <li>
                            <i class="fa-solid fa-phone"></i>
                            <span><strong>Phone</strong><br><a href="tel:4252871426" class="text-gray-300">425-287-1426</a></span>
                        </li>
                        <li>
                            <i class="fa-solid fa-envelope"></i>
                            <span><strong>Email</strong><br> <a class="text-gray-300" href="mailto:info@fleeescape.com">info@fleeescape.com</a></span>
                        </li>
                        <li>
                            <i class="fa-solid fa-calendar"></i>
                            <span>
                                <strong>Hours</strong>
                                <br> Monday to Thursday: 1PM-9PM
                                <br> Friday: 1PM-10PM
                                <br> Saturday and Sunday: 12PM-10PM
                            </span>
                        </li>
                    </ul>
                </div>

                <!-- Right: Form -->
                <div class="Party_contact_form_form">
                    <h3>Send Us a Message</h3>
                   <form id="partyEnquiryForm">
                        <div class="Party_contact_form_row">
                            <input id="enq_name" type="text" placeholder="Enter Your Full Name" required>
                            <input id="enq_mobile" type="number" placeholder="Mobile Number" required  maxlength="10" pattern="[0-9]{10}"  oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                        </div>
                        <input id="enq_email" type="email" placeholder="Enter your email" required>
                        <textarea id="enq_message" placeholder="Tell us about your party needs" required></textarea>
                    
                        <button type="submit" class="Party_contact_form_btn_primary Party_contact_form_w100">
                            Send Message
                        </button>
                    </form>
                    
                    <div id="enq_response_msg"></div>
                </div>
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
document.getElementById("partyEnquiryForm").addEventListener("submit", function(e){
    e.preventDefault();

    let name    = document.getElementById("enq_name").value.trim();
    let mobile  = document.getElementById("enq_mobile").value.trim();
    let email   = document.getElementById("enq_email").value.trim();
    let message = document.getElementById("enq_message").value.trim();

    let responseBox = document.getElementById("enq_response_msg");

    // -----------------------------
    // SIMPLE FRONT-END VALIDATION
    // -----------------------------

    if (name.length < 2) {
        responseBox.innerHTML = "<p style='color:red'>Please enter your full name.</p>";
        return;
    }

    if (!/^[0-9]{10,15}$/.test(mobile)) {
        responseBox.innerHTML = "<p style='color:red'>Please enter a valid mobile number (10+ digits).</p>";
        return;
    }

    let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        responseBox.innerHTML = "<p style='color:red'>Please enter a valid email address.</p>";
        return;
    }

    if (message.length < 5) {
        responseBox.innerHTML = "<p style='color:red'>Please enter your message.</p>";
        return;
    }

    // Validation Passed → Send Request
    let formData = new FormData();
    formData.append("name", name);
    formData.append("mobile", mobile);
    formData.append("email", email);
    formData.append("message", message);

    fetch("send_party_enquiry.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        // Show message
        responseBox.innerHTML =
            `<p style="color:${data.status === 'success' ? 'green' : 'red'}">${data.message}</p>`;

        // -----------------------------------
        // RESET FORM AFTER SUCCESS
        // -----------------------------------
        if (data.status === 'success') {
            document.getElementById("partyEnquiryForm").reset();

            // optional: clear after reset delay
            // setTimeout(() => responseBox.innerHTML = "", 3000);
        }
    })
    .catch(err => {
        responseBox.innerHTML = "<p style='color:red'>Something went wrong.</p>";
    });
});
</script>



<!-- Unforgettable_Birthday_slider -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const slides = document.querySelector(".Unforgettable_Birthday_slider .Unforgettable_slides");
    const images = document.querySelectorAll(".Unforgettable_Birthday_slider img");
    const prevBtn = document.querySelector(".slider-btn.prev");
    const nextBtn = document.querySelector(".slider-btn.next");

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