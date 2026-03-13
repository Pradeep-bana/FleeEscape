<?php session_start();
include('link.php');
include("admin/db.php");
try {
    $stmt = $pdo->query("SELECT page_title, keywords, page_description FROM tbl_portable_escape_room LIMIT 1");
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        $pageTitle = htmlspecialchars($data['page_title']);
        $metaKeywords = htmlspecialchars($data['keywords']);
        $metaDescription = htmlspecialchars($data['page_description']);
    } else {
        // Fallback if table is empty
        $pageTitle = 'Portable Escape Rooms';
        $metaKeywords = 'Portable Escape Rooms';
        $metaDescription = 'Portable Escape Rooms';
    }
} catch (Exception $e) {
    // Fallback in case of error
    $pageTitle = 'Portable Escape Rooms';
    $metaKeywords = 'Portable Escape Rooms';
    $metaDescription = 'Portable Escape Rooms';
}


include('includes/header.php');
?>

<?php
include("admin/db.php");
// Fetch data from database
$stmt = $pdo->query("SELECT * FROM tbl_portable_escape_room LIMIT 1");
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Fallback values if database is empty
$heading = $data['heading'] ?? 'Ultimate Portable Adventure';
$sub_content = $data['sub_content'] ?? 'Experience immersive adventures anywhere with our mobile escape room units. Ideal for events, team-building activities, or private gatherings.';
$players = $data['players'] ?? '2-6 per room';
$duration = $data['duration'] ?? '15 minutes';
$units_available = $data['units_available'] ?? '10 rooms';
$thumbnail = $data['thumbnail'] ?? 'img/portable_banner.jpg';
?>
<section class="portable_baner_section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 text-start text-white">
                <span class="tag_onsite_tag_top"><i class="fa-solid fa-truck-moving me-2"></i> WE COME ANYWHERE</span>
                <h3><?php echo htmlspecialchars($heading); ?></h3>
                <p><?php echo htmlspecialchars($sub_content); ?></p>
                <div class="Portable_payrer_desc_time">
                    <div class="Portable_payrer_desc_time_items">
                        <i style="color: #00dcb4;" class="fa-solid fa-users"></i>
                        <div class="Portable_payrer_desc_time_items_name">
                            <p>Players</p>
                            <h6><?php echo htmlspecialchars($players); ?></h6>
                        </div>
                    </div>
                    <div class="Portable_payrer_desc_time_items">
                        <i style="color: #c056ff;" class="fa-regular fa-clock"></i>
                        <div class="Portable_payrer_desc_time_items_name">
                            <p>Duration</p>
                            <h6><?php echo htmlspecialchars($duration); ?></h6>
                        </div>
                    </div>
                    <div class="Portable_payrer_desc_time_items">
                        <i style="color: #ff8800;" class="fa-solid fa-box"></i>
                        <div class="Portable_payrer_desc_time_items_name">
                            <p>Units Available</p>
                            <h6><?php echo htmlspecialchars($units_available); ?></h6>
                        </div>
                    </div>
                </div>

                <div class="all_button_main_header order_summart_main_button">
                    <a href="booking#event-room" class="bg_bnt_custom ">Book Your Event</a>
                    <a href="#birt_scroll_opne_contact" class="bg_bnt_custom bg_bnt_custom_tran">Contact us for pricing</a>
                </div>
            </div>
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="portable_image-box">
                    <img src="admin/<?php echo htmlspecialchars($thumbnail); ?>" loading="lazy" alt="Portable Escape Room" class="img-fluid rounded-4 shadow-lg">
                    <div class="Portable_img_box_img_verly">
                        <i style="color: #00dcb4;" class="fas fa-location-dot"></i>
                        <div>
                            <h6>Suitcase-Sized</h6>
                            <p>Easy transport & setup</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<?php
// Fetch data from database
$stmt = $pdo->query("SELECT * FROM tbl_portable_escape_room LIMIT 1");
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Fallback values if database is empty
$subway_heading = $data['subway_heading'] ?? 'THE SUBWAY BOMB THREAT';
$subway_sub_heading = $data['subway_sub_heading'] ?? 'A bomb has been hidden somewhere on a subway train at the station. Your team must work together to find the clues, solve the puzzles, and defuse the bomb before time runs out.';

$feature_1_title = $data['feature_1_title'] ?? 'Subway Setting';
$feature_1_desc = $data['feature_1_desc'] ?? 'Immerse yourself in a realistic subway train environment with authentic props and atmospheric details.';

$feature_2_title = $data['feature_2_title'] ?? 'Bomb Defusal';
$feature_2_desc = $data['feature_2_desc'] ?? 'Race against the clock to locate hidden clues and solve intricate puzzles to defuse the device.';

$feature_3_title = $data['feature_3_title'] ?? '45-Minute Challenge';
$feature_3_desc = $data['feature_3_desc'] ?? 'Perfect timing for team building exercises and party activities. Can your team beat the clock?';
?>

<section class="fect_Every_Occasion">
    <div class="container">
        <div class="eam-transform-section_box">
            <div class="section_heading_page">
                <h2 class="section-title"><?php echo htmlspecialchars($subway_heading); ?></h2>
                <p class="section-subtitle"><?php echo nl2br(htmlspecialchars($subway_sub_heading)); ?></p>
            </div>
            <div class="row g-4 Every_Occasion_cards">
                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon-circle" style="background-color: #00dcb451;">
                            <svg style="color: #00dcb4;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-tram-front w-6 h-6 text-accent">
                                <rect width="16" height="16" x="4" y="3" rx="2"></rect>
                                <path d="M4 11h16"></path>
                                <path d="M12 3v8"></path>
                                <path d="m8 19-2 3"></path>
                                <path d="m18 22-2-3"></path>
                                <path d="M8 15h.01"></path>
                                <path d="M16 15h.01"></path>
                            </svg>
                        </div>
                        <h5 class="fw-bold mt-3"><?php echo htmlspecialchars($feature_1_title); ?></h5>
                        <p><?php echo htmlspecialchars($feature_1_desc); ?></p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon-circle" style="background-color: #c156ff51;">
                            <svg style="color: #c056ff;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-bomb w-6 h-6 text-accent-secondary">
                                <circle cx="11" cy="13" r="9"></circle>
                                <path d="M14.35 4.65 16.3 2.7a2.41 2.41 0 0 1 3.4 0l1.6 1.6a2.4 2.4 0 0 1 0 3.4l-1.95 1.95"></path>
                                <path d="m22 2-1.5 1.5"></path>
                            </svg>
                        </div>
                        <h5 class="fw-bold mt-3"><?php echo htmlspecialchars($feature_2_title); ?></h5>
                        <p><?php echo htmlspecialchars($feature_2_desc); ?></p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon-circle" style="background-color: #ff88004c;">
                            <svg style="color: #ff8800;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-target w-6 h-6 text-accent-tertiary">
                                <circle cx="12" cy="12" r="10"></circle>
                                <circle cx="12" cy="12" r="6"></circle>
                                <circle cx="12" cy="12" r="2"></circle>
                            </svg>
                        </div>
                        <h5 class="fw-bold mt-3"><?php echo htmlspecialchars($feature_3_title); ?></h5>
                        <p><?php echo htmlspecialchars($feature_3_desc); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<?php
// Fetch data from database
$stmt = $pdo->query("SELECT * FROM tbl_portable_escape_room LIMIT 1");
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Fallback values if database is empty
$occasions_heading = $data['occasions_heading'] ?? 'Perfect For Every Occasion';

$occasion_1_title = $data['occasion_1_title'] ?? 'Corporate Events';
$occasion_1_desc = $data['occasion_1_desc'] ?? 'Build stronger teams through collaborative problem-solving and communication';

$occasion_2_title = $data['occasion_2_title'] ?? 'Birthday Parties';
$occasion_2_desc = $data['occasion_2_desc'] ?? 'Create unforgettable memories with an adventure that comes right to your celebration';

$occasion_3_title = $data['occasion_3_title'] ?? 'Team Building';
$occasion_3_desc = $data['occasion_3_desc'] ?? 'Strengthen workplace relationships with engaging challenges at your office';

$occasion_4_title = $data['occasion_4_title'] ?? 'Special Events';
$occasion_4_desc = $data['occasion_4_desc'] ?? 'Add excitement to any gathering with a unique entertainment experience';
?>

<section class="Facility_Rentals_Entire">
    <div class="container">
        <div class="section_heading_page">
            <h2 class="section-title"><?php echo htmlspecialchars($occasions_heading); ?></h2>
        </div>
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="Corporate_Team_Building_card p-4  h-100">
                    <!-- SVG stays exactly as original -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-building2 w-8 h-8 text-accent">
                        <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                        <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                        <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                        <path d="M10 6h4"></path>
                        <path d="M10 10h4"></path>
                        <path d="M10 14h4"></path>
                        <path d="M10 18h4"></path>
                    </svg>
                    <h5 class="fw-bold"><?php echo htmlspecialchars($occasion_1_title); ?></h5>
                    <p><?php echo htmlspecialchars($occasion_1_desc); ?></p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="Corporate_Team_Building_card p-4  h-100">
                    <svg style="color: #c056ff;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-party-popper w-8 h-8 text-accent-secondary">
                        <path d="M5.8 11.3 2 22l10.7-3.79"></path>
                        <path d="M4 3h.01"></path>
                        <path d="M22 8h.01"></path>
                        <path d="M15 2h.01"></path>
                        <path d="M22 20h.01"></path>
                        <path d="m22 2-2.24.75a2.9 2.9 0 0 0-1.96 3.12c.1.86-.57 1.63-1.45 1.63h-.38c-.86 0-1.6.6-1.76 1.44L14 10"></path>
                        <path d="m22 13-.82-.33c-.86-.34-1.82.2-1.98 1.11c-.11.7-.72 1.22-1.43 1.22H17"></path>
                        <path d="m11 2 .33.82c.34.86-.2 1.82-1.11 1.98C9.52 4.9 9 5.52 9 6.23V7"></path>
                        <path d="M11 13c1.93 1.93 2.83 4.17 2 5-.83.83-3.07-.07-5-2-1.93-1.93-2.83-4.17-2-5 .83-.83 3.07.07 5 2Z"></path>
                    </svg>
                    <h5 class="fw-bold"><?php echo htmlspecialchars($occasion_2_title); ?></h5>
                    <p><?php echo htmlspecialchars($occasion_2_desc); ?></p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="Corporate_Team_Building_card p-4  h-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-users w-8 h-8 text-accent-secondary mx-auto mb-3">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <h5 class="fw-bold"><?php echo htmlspecialchars($occasion_3_title); ?></h5>
                    <p><?php echo htmlspecialchars($occasion_3_desc); ?></p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="Corporate_Team_Building_card p-4  h-100">
                    <svg style="color: #00dcb4;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-target w-8 h-8 text-accent">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="6"></circle>
                        <circle cx="12" cy="12" r="2"></circle>
                    </svg>
                    <h5 class="fw-bold"><?php echo htmlspecialchars($occasion_4_title); ?></h5>
                    <p><?php echo htmlspecialchars($occasion_4_desc); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>



<?php
// Fetch data from database
$stmt = $pdo->query("SELECT * FROM tbl_portable_escape_room LIMIT 1");
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Fallback values
$example_heading = $data['example_heading'] ?? 'Example Scenarios';

$example_1_title = $data['example_1_title'] ?? 'Small Team (6 people)';
$example_1_content = $data['example_1_content'] ?? '1 escape room unit';
$example_1_price = $data['example_1_price'] ?? '$299';

$example_2_title = $data['example_2_title'] ?? 'Medium Event (24 people)';
$example_2_content = $data['example_2_content'] ?? '4 escape room units';
$example_2_price = $data['example_2_price'] ?? '$299';

$example_3_title = $data['example_3_title'] ?? 'Large Party (60 people)';
$example_3_content = $data['example_3_content'] ?? '10 escape room units';
$example_3_price = $data['example_3_price'] ?? '$299';
?>

<section class="Facility_Rentals_Entire">
    <div class="container">
        <div class="section_heading_page">
            <h2 class="section-title"><?php echo htmlspecialchars($example_heading); ?></h2>
        </div>
        <div class="row g-4 text-center">
            <!-- Example 1 -->
            <div class="col-md-4">
                <div class="Corporate_Team_Building_card p-4 h-100">
                    <svg style="color: #00dcb4;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-users w-8 h-8 text-accent mx-auto mb-3">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <h5 class="fw-bold"><?php echo htmlspecialchars($example_1_title); ?></h5>
                    <p><?php echo htmlspecialchars($example_1_content); ?></p>
                    <div class="Corporate_Team_Building_card_price">
                        <p class="mb-0"><strong style="color: #00dcb4;">$<?php echo htmlspecialchars($example_1_price); ?></strong> <span>+ tax</span></p>
                    </div>
                </div>
            </div>

            <!-- Example 2 -->
            <div class="col-md-4">
                <div class="Corporate_Team_Building_card p-4 h-100">
                    <svg style="color: #c056ff;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-users w-8 h-8 text-accent-secondary mx-auto mb-3">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <h5 class="fw-bold"><?php echo htmlspecialchars($example_2_title); ?></h5>
                    <p><?php echo htmlspecialchars($example_2_content); ?></p>
                    <div class="Corporate_Team_Building_card_price">
                        <p class="mb-0"><strong style="color: #c056ff;">$<?php echo htmlspecialchars($example_2_price); ?></strong> <span>+ tax</span></p>
                    </div>
                </div>
            </div>

            <!-- Example 3 -->
            <div class="col-md-4">
                <div class="Corporate_Team_Building_card p-4 h-100">
                    <svg style="color: #ff8800;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-users w-8 h-8 text-accent-secondary mx-auto mb-3">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <h5 class="fw-bold"><?php echo htmlspecialchars($example_3_title); ?></h5>
                    <p><?php echo htmlspecialchars($example_3_content); ?></p>
                    <div class="Corporate_Team_Building_card_price">
                        <p class="mb-0"><strong style="color: #ff8800;">$<?php echo htmlspecialchars($example_3_price); ?></strong> <span>+ tax</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>




<!-- Experience Gallery Section -->
<section class="Experience_Gallery onsite-team-event_gallery py-5">
    <div class="container text-center">
        <div class="section_heading_page">
            <h2 class="section-title">SEE IT IN ACTION</h2>
            <p class="section-subtitle">Compact, portable, and packed with puzzles. Each unit is designed for <br> easy
                transport and quick setup.</p>
        </div>

     <div class="row g-4">
    <?php
    // include('db.php');
    include("admin/db.php");

    // Fetch all gallery items
    $stmt = $pdo->query("SELECT `id`, `first_heading`, `second_heading`, `image` FROM `tbl_portable_gallery` ORDER BY id ASC");
    $galleryItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($galleryItems)) {
        foreach ($galleryItems as $item) {
            // Check if image file exists
            $imgSrc = !empty($item['image']) && file_exists('admin/uploads/' . $item['image']) ? 'admin/uploads/' . $item['image'] : 'img/default.jpg';
            ?>
            <div class="col-md-4 col-sm-6">
                <a data-fancybox="gallery" href="<?php echo $imgSrc; ?>">
                    <div class="gallery-item">
                        <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($item['first_heading']); ?>" loading="lazy" class="img-fluid rounded">
                        <div class="overlay">
                            <i class="fa-solid fa-eye"></i>
                        </div>
                        <div class="overlay-text">
                            <h6><?php echo htmlspecialchars($item['first_heading']); ?></h6>
                            <p><?php echo htmlspecialchars($item['second_heading']); ?></p>
                        </div>
                    </div>
                </a>
            </div>
        <?php
        }
    } else {
        echo '<p class="text-center">No gallery items found.</p>';
    }
    ?>
</div>

    </div>
</section>

<section class="review-section Ultimate_Birthday_Experience ">
    <div class="section_heading_page">
        <h2 class="section-title">What Our Customers Say</h2>
        <p class="section-subtitle">Don't just take our word for it - hear from families who've celebrated with us </p>
    </div>

    <div class="d-flex flex-wrap justify-content-center gap-3">
    <?php
    // include('db.php');
    include("admin/db.php");

    // Fetch testimonials
    $stmt = $pdo->query("SELECT `id`, `client_name`, `message`, `rating`, `image` FROM `tbl_portable_testimonial` ORDER BY id DESC");
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($testimonials)) {
        foreach ($testimonials as $review) {
            // Star rating
            $stars = str_repeat('â˜…', (int)$review['rating']);
            
            // Image check
            $imgSrc = (!empty($review['image']) && file_exists('admin/uploads/' . $review['image'])) 
                      ? 'admin/uploads/' . $review['image'] 
                      : 'img/default-user.png'; // fallback image
            ?>
            <div class="review-card text-center p-3" style="background: #222; border-radius: 10px; width: 300px;">
                <img src="<?php echo $imgSrc; ?>" loading="lazy" alt="<?php echo htmlspecialchars($review['client_name']); ?>" 
                     class="rounded-circle mb-2" style="width:60px; height:60px; object-fit:cover;">
                <div class="rating mb-2" style="color: #ffd700; font-size: 1.1rem;"><?php echo $stars; ?></div>
                <p style="color: #fff;"><?php echo htmlspecialchars($review['message']); ?></p>
                <p class="author" style="color: #ccc;">- <?php echo htmlspecialchars($review['client_name']); ?></p>
            </div>
        <?php
        }
    } else {
        echo '<p class="text-center text-white">No reviews found.</p>';
    }
    ?>
</div>

</section>

<section>
    <div class="container">
        <div class="faq-section my-5">
            <h2 class="text-center mb-4">Portable Escape Rooms FAQ</h2>
           <div class="accordion" id="faqAccordion">
    <?php
    // include('db.php');
    include("admin/db.php");

    // Fetch all FAQs
    $stmt = $pdo->query("SELECT `id`, `question`, `answer` FROM `tbl_portable_faq` ORDER BY id ASC");
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($faqs)) {
        $counter = 1;
        foreach ($faqs as $faq) {
            $headingId = "faqHeading" . $faq['id'];
            $collapseId = "faqCollapse" . $faq['id'];
            ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="<?php echo $headingId; ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="false"
                            aria-controls="<?php echo $collapseId; ?>">
                        <?php echo htmlspecialchars($faq['question']); ?>
                        <span class="faq-toggle-icon ms-auto">
                            <span class="plus">+</span>
                            <span class="minus" style="display:none;">âˆ’</span>
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
            <?php
            $counter++;
        }
    } else {
        echo '<p class="text-center text-muted">No FAQs found.</p>';
    }
    ?>
</div>

        </div>
    </div>
</section>


<section class="adventure-section">
    <div class="container">
        <div class="adventure-content">
            <h2>Ready to Bring
                The Adventure to You?</h2>
            <p>Book your portable escape room experience today. Perfect for corporate events, <br> birthday parties, and
                any
                occasion that needs excitement. </p>
            <div class="all_button_main_header order_summart_main_button">
                <a href="booking#event-room" class="bg_bnt_custom ">Book Your Event</a>
                <a href="contact-us" class="bg_bnt_custom bg_bnt_custom_tran">Contact Us</a>
            </div>
          <?php
// include('db.php');
include("admin/db.php");

// Fetch the escape room data
$stmt = $pdo->query("SELECT `units_available`, `players`, `duration` FROM `tbl_portable_escape_room` LIMIT 1");
$room = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="Bring_Adventure_time">
    <?php if ($room): ?>
        <div class="Bring_Adventure_time_iteme">
            <h3><?php echo htmlspecialchars($room['units_available']); ?></h3>
            <p>Units Available</p>
        </div>
        <div class="Bring_Adventure_time_iteme">
            <h3><?php echo htmlspecialchars($room['players']); ?></h3>
            <p>Players Per Room</p>
        </div>
        <div class="Bring_Adventure_time_iteme">
            <h3><?php echo htmlspecialchars($room['duration']); ?></h3>
            <p>Experience Duration</p>
        </div>
    <?php else: ?>
        <p class="text-center text-muted">Data not available.</p>
    <?php endif; ?>
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
                    <a href="booking#party-package" class="bg_bnt_custom">Book Birthday Party</a>
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
                            <span><strong>Address</strong><br>2222 152nd Ave
                                NE,
                                Redmond, WA, 98052 (Across from the Silver Cloud In)</span>
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
                            <span><strong>Hours</strong><br>Thu: 10AM - 10PM <br>
                                Sun:
                                10AM - 8PM</span>
                        </li>
                    </ul>
                </div>

                <!-- Right: Form -->
                <div class="Party_contact_form_form">
                    <h3>Send Us a Message</h3>
                              <form id="partyEnquiryForm">
    <div class="Party_contact_form_row">
        <input id="enq_name" type="text" placeholder="Enter Your Full Name" required>
<input 
  id="enq_mobile"
  type="tel"
  placeholder="Mobile Number"
  required
  maxlength="10"
  pattern="[0-9]{10}"
  oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
>

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


<!-- === Video Modal ====== -->
<div class="modal fade blur-modal" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="videoModalLabel">Watch Trailer</h5>
                <button type="button" class="btn btn-sm close-btn" data-bs-dismiss="modal" aria-label="Close"
                    onclick="stopLocalVideo()">X</button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <!-- muted -->
                    <video id="localVideo"  muted  controls>
                        <source src="./assets/video/video.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
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
<?php include('includes/footer.php'); ?>