<?php session_start();
include('link.php');

include("admin/db.php");
try {
    $prod_id='41551XJM6F314F91E1CD68';
     $stmt = $pdo->prepare("SELECT * FROM tbl_service WHERE product_id = ?");
    $stmt->execute([$prod_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        $pageTitle = htmlspecialchars($data['page_title']);
        $metaKeywords = htmlspecialchars($data['keywords']);
        $metaDescription = htmlspecialchars($data['page_description']);
    } else {
        // Fallback if table is empty
        $pageTitle = 'Steampunk Submarine';
        $metaKeywords = 'Steampunk Submarine';
        $metaDescription = 'Steampunk Submarine';
    }
} catch (Exception $e) {
    // Fallback in case of error
    $pageTitle = 'Steampunk Submarine';
    $metaKeywords = 'Steampunk Submarine';
    $metaDescription = 'Steampunk Submarine';
}

$canonicalURL = $link."steampunk-submarine-collection";
include('includes/header.php');
$prod_id='41551XJM6F314F91E1CD68';
?>

<style>
.Boo_Prison_Escape_time-slot:disabled + .Boo_Prison_Escape_time-slot-label {
    opacity: 0.5;
    pointer-events: none;
    color: #777 !important;
    border: 2px solid #777;
}
.slot-box span.Available_play_time {
    
    
    color: #fff;
    line-height: initial;
    padding-top: 4px;
    font-weight: 300;
    font-size: 14px;
}
.flatpickr-year-dropdown {
    color: #00d4ff;
    border: none;
    background: transparent;
    font-weight: 300 !important;
    border: none;
    font-size: inherit;
    padding: 0 5px;
    cursor: pointer;
    font-weight: bold;
}
.flatpickr-current-month {
    display: flex;
    align-items: center;
    justify-content: center;
}
.flatpickr-year-dropdown option {
    background: #fff;
}
</style>
<section class="choose-adventure ">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="choose_all_page_resposnive_toggle">
                <p> <span>All Games</span> <i class="fa-solid fa-bars-staggered"></i></p>
            </div>
            <!-- LEFT: Tabs list (grid same) -->
             <div class="col-md-3">
                <h5 class="ca-title mb-3">Choose Your Adventure</h5>
             <div class="nav flex-column nav-pills">
<?php
include('admin/db.php'); // adjust path if needed

try {
    // Fetch all games
    $stmt = $pdo->prepare("SELECT * FROM tbl_service 
    ORDER BY 
        CASE
            WHEN product_id = :product_id THEN 0
            ELSE 1
        END,
    id DESC");
    $stmt->execute([':product_id' => $prod_id]);
    $isFirstItem = true;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $prod_id = $row['product_id'];
        $selectedClass = $isFirstItem ? 'escape-active' : '';
        $isFirstItem = false;

        // Calculate average rating & review count
        $ratingQuery = $pdo->prepare("SELECT AVG(rating) AS avg_rating, COUNT(id) AS total_reviews FROM tbl_escape_room_testimonial WHERE category = ?");
        $ratingQuery->execute([$prod_id]);
        $ratingData = $ratingQuery->fetch(PDO::FETCH_ASSOC);

        $avgRating = $ratingData['avg_rating'] ? number_format($ratingData['avg_rating'], 1) : '0.0';
        $reviewCount = $ratingData['total_reviews'] ?? 0;

        // Prepare display data
        $slug = $row['link'];
        $difficultyClass = 'ca-' . strtolower($row['difficulty']);
        $difficultyLabel = ucfirst($row['difficulty']);
         $duration = $row['duration'];
        $thumbnail = !empty($row['thumbnail']) ? "uploads/vr/{$row['thumbnail']}" : "images/default.jpg";
        $label = !empty($row['label']) ? $row['label'] : '';
?>
    <button onclick="window.location.href='<?php echo $slug; ?>'" class="nav-link ca-item <?=$selectedClass?>">
        <div class="ca-item-head">
            <i class="bi bi-arrow-up-circle"></i>
            <div class="ca-item-name_heding">
                <div class="ca-item-name"><?php echo htmlspecialchars($row['title']); ?></div>
                <div class="ca-item-sub"><?php echo htmlspecialchars($row['middle_h2']); ?></div>
            </div>
        </div>

        <div class="ca-item-meta">
            <div>
                <span class="ca-difficulty <?php echo $difficultyClass; ?>"><?php echo $difficultyLabel; ?></span>
                <span class="ca-item-meta_minutes"><span>•</span><?php echo $duration; ?> minutes</span>
            </div>
        </div>

        <div class="ca-rating">
           
            <i class="bi bi-star-fill"></i> <?php echo $avgRating; ?> (<?php echo $row['reviewsCount']; ?> reviews)
        </div>
    </button>
<?php
    }
} catch (Exception $e) {
    echo "<p>Error loading games.</p>";
}
?>
</div>


            </div>

            <!-- RIGHT: Tab content (grid same) -->
                   <?php
include('admin/db.php');

$prod_id = '41551XJM6F314F91E1CD68'; // your current product id

try {
    // Fetch escape room data
    $stmt = $pdo->prepare("SELECT * FROM tbl_service WHERE product_id = ?");
    $stmt->execute([$prod_id]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($room) {
        // Fetch rating data
        $ratingQuery = $pdo->prepare("SELECT AVG(rating) AS avg_rating, COUNT(id) AS total_reviews FROM tbl_escape_room_testimonial WHERE category = ?");
        $ratingQuery->execute([$prod_id]);
        $ratingData = $ratingQuery->fetch(PDO::FETCH_ASSOC);

        $avgRating = $ratingData['avg_rating'] ? number_format($ratingData['avg_rating'], 1) : '0.0';
        $reviewCount = $ratingData['total_reviews'] ?? 0;

        // Prepare variables
        $title = $gameTitle = htmlspecialchars($room['title']);
        $sub = htmlspecialchars($room['middle_h2']);
        $difficulty = ucfirst($room['difficulty']);
        $duration=$room['duration'];
        $players = htmlspecialchars($room['players']);
        $cover = !empty($room['cover_photo']) ? "admin/uploads/{$room['cover_photo']}" : "img/default-bg.jpg";
        $label = !empty($room['label']) ? htmlspecialchars($room['label']) : '';
        $trailer = !empty($room['trailer_video']) ? "admin/uploads/{$room['trailer_video']}" : '';
        $price = !empty($room['price']) ? $room['price'] : '35';
?>

<div class="col-md-9">
    <div class="escpe_room_right_data_show" style="background-image: url('<?php echo $cover; ?>');">
        <div class="escpe_room_right_data_items">
            <div class="choose-adventure_tab_data">
                <div class="d-flex justify-content-center align-items-center gap-2">
                    <span class="ca-chip"><i class="bi bi-graph-up"></i> <?php echo $difficulty; ?> Difficulty</span>
                    <?php if ($label) { ?>
                        <span class="ca-chip"><i class="fa-solid fa-heart"></i> <?php echo $label; ?></span>
                    <?php } ?>
                </div>

                <h1 class="ca-heading"><?php echo $title; ?></h1>
                <p class="ca-subtitle"><?php echo $sub; ?></p>

                <div class="ca-badges">
                    <span class="ca-badge"><i class="bi bi-clock"></i> <?php echo $duration; ?> minutes</span>
                    <span class="ca-badge"><i class="bi bi-people"></i> <?php echo $players; ?> players</span>
                    <!--<span class="ca-badge"><i class="bi bi-graph-up"></i> <?php echo $difficulty; ?> Difficulty</span>-->
                    <span class="ca-badge"><i class="bi bi-star-fill"></i> <?php echo $avgRating; ?>/5 (<?php echo $room['reviewsCount']; ?> reviews)</span>
                </div>

                <div class="all_button_main_header" style="background-size: cover; background-repeat: no-repeat;">
                    <?php if ($trailer) { ?>
                    
                     <a href="#to_book_scroll" data-bs-toggle="modal" data-bs-target="#Indoormodal"
                                    class="bg_bnt_custom bg_bnt_custom_tran">Watch Trailer</a>
                       
                    <?php } ?>
                    <a href="#Book_one_single_pr" class="bg_bnt_custom">
                        Book Now – $<?php echo $price; ?>/person
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    } else {
        echo "<p>No escape room found for this ID.</p>";
    }
} catch (Exception $e) {
    echo "<p>Error loading escape room details.</p>";
}
?>
        </div>
    </div>
</section>

<section>

    <div class="container">
        <ul class="nav nav-tabs card_deatils_tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview"
                    type="button" role="tab">Overview</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="story-tab" data-bs-toggle="tab" data-bs-target="#story" type="button"
                    role="tab">Story</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="gallery-cardDetails-tab" data-bs-toggle="tab"
                    data-bs-target="#gallery-cardDetails" type="button" role="tab">Gallery</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="faq-tab" data-bs-toggle="tab" data-bs-target="#faq" type="button"
                    role="tab">FAQ</button>
            </li>
        </ul>

        <div class="tab-content card_datals_main_data" id="myTabContent">
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
             <?php
include('admin/db.php');

$prod_id = '41551XJM6F314F91E1CD68'; // example product id

try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_service WHERE product_id = ?");
    $stmt->execute([$prod_id]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($room) {
        // Assign variables
        $duration = $room['duration']; // fixed or can be dynamic if stored
        $players = htmlspecialchars($room['players']);
        $difficulty = ucfirst($room['difficulty']);
        $success = !empty($room['success_rate']) ? $room['success_rate'] . '%' : 'N/A';
        $age = htmlspecialchars($room['age']);
        $price = !empty($room['price']) ? '$' . $room['price'] . '/person' : '$35/person';
        $features = !empty($room['features']) ? $room['features'] : '';
        $description = !empty($room['blog_detl']) ? $room['blog_detl'] : 'Description coming soon.';
?>
<div class="row g-4">
    <!-- Left Column: Details -->
    <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
        <div class="Room_Detail_overview_tabs p-4 h-100">
            <h5 class="Room_Detail_overview_tabs-title">
                <i class="bi bi-lock-fill"></i> Room Details
            </h5>
            <div class="row mt-3">
                <div class="col-6 mb-3">
                    <p class="mb-1 text-muted">Duration</p>
                    <h6 class="fw-bold"><?php echo $duration; ?> Minutes</h6>
                </div>
                <div class="col-6 mb-3">
                    <p class="mb-1 text-muted">Team Size</p>
                    <h6 class="fw-bold"><?php echo $players; ?></h6>
                </div>
                <div class="col-6 mb-3">
                    <p class="mb-1 text-muted">Difficulty</p>
                    <h6 class="fw-bold text-warning"><?php echo $difficulty; ?></h6>
                </div>
                <div class="col-6 mb-3">
                    <p class="mb-1 text-muted">Success Rate</p>
                    <h6 class="fw-bold text-danger"><?php echo $success; ?></h6>
                </div>
                <div class="col-6 mb-3">
                    <p class="mb-1 text-muted">Age Recommendation</p>
                    <h6 class="fw-bold"><?php echo $age; ?></h6>
                </div>
                <div class="col-6 mb-3">
                    <p class="mb-1 text-muted">Price</p>
                    <h6 class="fw-bold text-success"><?php echo $price; ?></h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Features -->
    <div class="col-lg-6" data-aos="fade-left" data-aos-delay="400">
        <div class="Room_Detail_overview_tabs p-4 h-100">
            <h5 class="Room_Detail_overview_tabs-title">Features</h5>
            <ul class="list-unstyled mt-3">
                <?php
                echo $features;
                ?>
            </ul>
        </div>
    </div>

    <!-- Description -->
    <div class="col-12 mt-4" data-aos="fade-up" data-aos-delay="600">
        <div class="Room_Detail_overview_tabs p-4">
            <h5 class="Room_Detail_overview_tabs-title">Description</h5>
            <p class="mt-3"><?php echo nl2br($description); ?></p>
        </div>
    </div>
</div>
<?php
    } else {
        echo "<p>No data found for this room.</p>";
    }
} catch (Exception $e) {
    echo "<p>Error loading room details.</p>";
}
?>

            </div>
            <div class="tab-pane fade" id="story" role="tabpanel">
               <?php
include('admin/db.php');

$prod_id = '41551XJM6F314F91E1CD68'; // example product_id

try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_service WHERE product_id = ?");
    $stmt->execute([$prod_id]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($room) {
        $story = !empty($room['story']) ? $room['story'] : 'Story coming soon.';
?>
<div class="Room_Detail_overview_tabs p-4" data-aos="zoom-in-up">
    <!--<h5 class="Room_Detail_overview_tabs-title">Story</h5>-->
    <p class="mt-3"><?php echo nl2br($story); ?></p>
</div>
<?php
    } else {
        echo "<p>No story found for this room.</p>";
    }
} catch (Exception $e) {
    echo "<p>Error loading story data.</p>";
}
?>

            </div>

            <div class="tab-pane fade" id="gallery-cardDetails" role="tabpanel">
               <div class="row Experience_Gallery">
                     <?php
include('admin/db.php');

$prod_id = '41551XJM6F314F91E1CD68'; // Example product_id

try {
    $stmt = $pdo->prepare("SELECT id, category, first_heading, second_heading, image 
                           FROM tbl_escape_room_gallery 
                           WHERE category = ? 
                           ORDER BY id DESC");
    $stmt->execute([$prod_id]);
    $galleryItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($galleryItems && count($galleryItems) > 0) {
        echo '<div class="row Experience_Gallery">';
        foreach ($galleryItems as $item) {
            $imgPath = !empty($item['image']) ? "admin/uploads/" . $item['image'] : "images/default.jpg";
            $title = htmlspecialchars($item['first_heading']);
            $subtitle = htmlspecialchars($item['second_heading']);
?>
            <div class="col-md-3 col-6">
                <div class="gallery-item position-relative">
                    <a href="<?php echo $imgPath; ?>" data-fancybox="vr">
                        <img src="<?php echo $imgPath; ?>" loading="lazy" class="img-fluid rounded shadow-sm" alt="Escape Room">
                        <div class="overlay"><i class="fa-solid fa-eye"></i></div>
                        <div class="overlay-text">
                            <h6><?php echo $title; ?></h6>
                            <p><?php echo $subtitle; ?></p>
                        </div>
                    </a>
                </div>
            </div>
<?php
        }
        echo '</div>';
    } else {
        echo "<p class='text-center mt-4'>No gallery images available for this room.</p>";
    }
} catch (Exception $e) {
    echo "<p class='text-center text-danger'>Error loading gallery.</p>";
}
?>


                    </div>
            </div>
            <div class="tab-pane fade" id="faq" role="tabpanel">
                <div class="card_tails_faq">
                  <?php
include('admin/db.php');

$prod_id = '41551XJM6F314F91E1CD68'; // Example product_id

try {
    $stmt = $pdo->prepare("SELECT id, category, question, answer 
                           FROM tbl_escape_room_faq 
                           WHERE category = ? 
                           ORDER BY id ASC");
    $stmt->execute([$prod_id]);
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($faqs && count($faqs) > 0) {
        echo '<div class="card_tails_faq">';
        $delay = 0;
        foreach ($faqs as $faq) {
            $question = htmlspecialchars($faq['question']);
            $answer = nl2br(htmlspecialchars($faq['answer']));
?>
            <div class="Room_Detail_overview_tabs p-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                <h5 class="Room_Detail_overview_tabs-title"><?php echo $question; ?></h5>
                <p class="mt-3"><?php echo $answer; ?></p>
            </div>
<?php
            $delay += 100; // incremental animation delay
        }
        echo '</div>';
    } else {
        echo "<p class='text-center mt-4'>No FAQs available for this room.</p>";
    }
} catch (Exception $e) {
    echo "<p class='text-center text-danger'>Error loading FAQs.</p>";
}
?>

                </div>
            </div>
        </div>
    </div>
</section>



<?php
// This block now fetches product data for the booking widget from the local database
// instead of making a live API call, improving speed and reliability.

// The specific product ID for Steampunk Submarine
$productId = '41551XJM6F314F91E1CD68';
$data = null;

if (!function_exists('safe_html')) {
    function safe_html($s) {
        return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

// 1. Fetch product data directly from the database cache.
try {
    $stmt = $pdo->prepare("SELECT product_data FROM bookeo_products_cache WHERE id = 1 LIMIT 1");
    $stmt->execute();
    $cacheRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cacheRow && !empty($cacheRow['product_data'])) {
        // 2. Decode the stored JSON data.
        $decodedData = json_decode($cacheRow['product_data'], true);
        if ($decodedData && isset($decodedData['data'])) {
            $data = $decodedData;
        }
    }
} catch (Exception $e) {
    echo "<div style='color:crimson; font-weight:bold'>Database error: " . safe_html($e->getMessage()) . "</div>";
    exit;
}

// 3. Find the specific product needed for this page.
$wanted = null;
if (isset($data['data']) && is_array($data['data'])) {
    foreach ($data['data'] as $product) {
        if ((isset($product['productId']) && $product['productId'] === $productId) || 
            (isset($product['productCode']) && $product['productCode'] === $productId)) {
            $wanted = $product;
            break;
        }
    }
}

// 4. Handle case where product is not found in the database.
if (!$wanted) {
    echo "<div class='container text-center my-5 p-4' style='background-color: #ffc10720; border: 1px solid #ffc107; border-radius: 8px;'>";
    echo "<h4 style='color: #ffc107;'>Booking Not Available</h4>";
    echo "<p>The booking information for this room (ID: " . safe_html($productId) . ") could not be found. Please contact support.</p>";
    echo "</div>";
    include('includes/footer.php');
    exit;
}

// 5. Process product fields for the UI (same logic as original file).
$name = safe_html($wanted['name'] ?? '');
$productCode = safe_html($wanted['productCode'] ?? $wanted['productId'] ?? '');

// Price parsing from description
$desc = trim($wanted['description'] ?? '');
$lines = preg_split('/\r\n|\r|\n/', strip_tags($desc, "<p><br><div>")); 
$priceRaw = isset($lines[3]) ? preg_replace('/^.*?:\s*/', '', trim(strip_tags($lines[3]))) : '';
$price = '';
if ($priceRaw) {
    preg_match_all('/\$\d+(?:\.\d+)?/', $priceRaw, $matches);
    if (!empty($matches[0])) {
        $price = count($matches[0]) >= 2 ? $matches[0][0] . '-' . $matches[0][1] : $matches[0][0];
    }
}

// Booking limits for guest dropdown
$minGuests = 1;
$maxGuests = 10;
if (isset($wanted['bookingLimits']) && is_array($wanted['bookingLimits'])) {
    foreach ($wanted['bookingLimits'] as $limit) {
        if (isset($limit['min']) && is_numeric($limit['min'])) {
            $minGuests = max($minGuests, intval($limit['min']));
        }
        if (isset($limit['max']) && is_numeric($limit['max'])) {
            $maxGuests = min($maxGuests, intval($limit['max']));
        }
    }
}
?>

<section id="Book_one_single_pr">
    <div class="container my-5">
       <div class="section_heading_page" >
            <h2 class="section-title">BOOK <?php echo $name; ?></h2>
            <p class="section-subtitle">Select your preferred date,
                time, and number of guests</p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-lg-5 col-md-6" data-aos="fade-right" data-aos-duration="1500">
                <div class="Boo_Prison_Escape_time_box h-100">
                    <h5 class="sub_heading">Select Date</h5>
                    <div class="Boo_Prison_Escape_calendar_box">
                        <input type="hidden" id="Book-Prison-Date-hidden">
                        <div id="Book-Prison-Date-inline"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 col-md-6">

                <div class="Boo_Prison_Escape_time_box" data-aos="fade-left" data-aos-duration="1500" data-aos-delay="200">
                    <h5 class="sub_heading">Select Time</h5>
                    <div class="row g-2" id="timeSlots-<?php echo $productCode; ?>">
                        <!-- Time slots will be dynamically inserted here -->
                    </div>
                </div>
                <div class="Boo_Prison_Escape_time_box" data-aos="fade-left" data-aos-duration="1500" data-aos-delay="400">
                    <h5 class="sub_heading">Number of Guests</h5>
                    <select class="Boo_Prison_Escape_select" id="guest-select-<?php echo $productCode; ?>" aria-label="Select number of guests" disabled>
                        <option selected value="">Select number of guests</option>
                        <?php for ($i = $minGuests; $i <= $maxGuests; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> Guest<?php echo $i > 1 ? 's' : ''; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="Boo_Prison_Escape_time_box" data-aos="fade-up" data-aos-duration="1500" data-aos-delay="600">
                    <div class="Boo_Prison_Escape_booking_summary_box">
                        <h3 class="sub_heading Booking_Summary_ng_summary_box">Booking Summary</h3>
                        <div class="Boo_Prison_Escape_booking_summary_box_row">
                            <span class="text-white">Room:</span>
                            <span class="escape_boo_summart_data"><?php echo $name; ?></span>
                        </div>
                        <div class="Boo_Prison_Escape_booking_summary_box_row">
                            <span class="text-white">Date:</span>
                            <span class="escape_boo_summart_data" id="summary-date">Not selected</span>
                        </div>
                        <div class="Boo_Prison_Escape_booking_summary_box_row">
                            <span class="text-white">Time:</span>
                            <span class="escape_boo_summart_data" id="summary-time">Not selected</span>
                        </div>
                        
                           <div class="Boo_Prison_Escape_booking_summary_box_row">
                            <span class="text-white">Guests / Price:</span>
                            <span class="escape_boo_summart_data" id="per_guest">$0</span>
                        </div>
                        
                        <div class="Boo_Prison_Escape_booking_summary_box_row">
                            <span class="text-white">Guests:</span>
                            <span class="escape_boo_summart_data" id="summary-guests">0</span>
                        </div>
                         
                        <div class="Boo_Prison_Escape_booking_summary_box_totals">
                            <span class="escape_boo_summart_data_totale">Total:</span>
                            <span class="escape_boo_summart_data_totale" id="total-price-<?php echo $productCode; ?>">$0</span>
                        </div>

                        <div class="next-button-wrapper">
                            
                              <?php echo '<button 
                            class="continueBtn bg_bnt_custom disabled continue_nex_step1" 
                            id="continueBtn-' . $productCode . '" 
                            data-game-id="' . $productCode . '" 
                            data-game-name="' . htmlspecialchars($name) . '" 
                            disabled>Continue</button>'; ?>
                          
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<section>
    <div class="escape_room_home_Customer_Reviews_section container">
        <?php
        include('admin/db.php');

        $prod_id = '41551XJM6F314F91E1CD68'; // Example product id

        try {
            // Fetch all reviews for this escape room
            $stmt = $pdo->prepare("SELECT client_name, message, rating, image, created_at 
                                   FROM tbl_escape_room_testimonial 
                                   WHERE category = ? 
                                   ORDER BY id DESC");
            $stmt->execute([$prod_id]);
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate average rating and total reviews
            $totalReviews = count($reviews);
            $avgRating = $totalReviews > 0 ? round(array_sum(array_column($reviews, 'rating')) / $totalReviews, 1) : 0;
        } catch (Exception $e) {
            $reviews = [];
            $totalReviews = 0;
            $avgRating = 0;
        }
        ?>

        <div class="section_heading_page">
            <h2 class="section-title">Customer Reviews</h2>
            <p class="section-subtitle">See what other adventurers are saying about this Escape Room</p>
            <div class="Boo_Prison_Escap_main_heading">
                <div class="d-flex justify-content-center align-items-center gap-2">
                    <span class="escape_room_home_Customer_Reviews_stars">
                        <?php
                        // Generate average star display
                        $filledStars = str_repeat('★', floor($avgRating));
                        $emptyStars = str_repeat('☆', 5 - floor($avgRating));
                        echo $filledStars . $emptyStars;
                        ?>
                    </span>
                    <span class="fw-bold"><?php echo $avgRating; ?>/5</span>
                    <small class="text-muted">(<?php echo $room['reviewsCount']; ?> reviews)</small>
                </div>
            </div>
        </div>

        <div class="tab-content mt-4">
            <div class="tab-pane fade show active" id="escape_room_home_Customer_Reviews_all">
                <div class="owl-carousel owl-theme escapeRoomReviewsSlider">
                    <?php
                    if ($totalReviews > 0) {
                        foreach ($reviews as $review) {
                            $name = htmlspecialchars($review['client_name']);
                            $avatar = strtoupper(substr($name, 0, 1));
                            $message = nl2br(htmlspecialchars($review['message']));
                            $rating = (int)$review['rating'];
                            $created_at = date('F j, Y', strtotime($review['created_at']));
                            $image = !empty($review['image']) ? "admin/uploads/{$review['image']}" : "";

                            $filledStars = str_repeat('★', $rating);
                            $emptyStars = str_repeat('☆', 5 - $rating);
                    ?>
                             <div class="item">
                                <div class="escape_room_home_Customer_Reviews_card">
                                    <div class="d-flex mb-2">
                                        <?php if ($image) { ?>
                                            <img src="<?php echo $image; ?>" loading="lazy" class="escape_room_home_Customer_Reviews_avatar_img rounded-circle" alt="<?php echo $name; ?>" width="40" height="40">
                                        <?php } else { ?>
                                            <div class="escape_room_home_Customer_Reviews_avatar"><?php echo $avatar; ?></div>
                                        <?php } ?>
                                        <div class="ms-2">
                                            <h6 class="mb-0"><?php echo $name; ?>
                                                <span class="escape_room_home_Customer_Reviews_verified">✔ Verified</span>
                                            </h6>
                                            <small class="text-muted"><?php echo $created_at; ?></small>
                                        </div>
                                        <div class="ms-auto escape_room_home_Customer_Reviews_stars">
                                            <?php echo $filledStars . $emptyStars; ?>
                                        </div>
                                    </div>
                                    <p><?php echo $message; ?></p>
                                    <div class="escape_room_home_Customer_Reviews_footer d-flex justify-content-between">
                                        <span><i class="bi bi-fire"></i> Helpful (<?php echo rand(5, 20); ?>)</span>
                                        <span>★ <?php echo $rating; ?>/5</span>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    } else {
                        echo "<p class='text-center mt-4'>No reviews available for this escape room yet.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <div class="escape_room_home_Customer_Reviews_summary">
            <div class="Boo_Prison_Escap_main_heading">
                <h2>Review Summary</h2>
            </div>

            <?php
            include('admin/db.php');

            // Product / category ID
            $prod_id = '41551XJM6F314F91E1CD68'; // Replace dynamically if needed

            try {
                $stmt = $pdo->prepare("SELECT rating FROM tbl_escape_room_testimonial WHERE category = ?");
                $stmt->execute([$prod_id]);
                $ratings = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if ($ratings && count($ratings) > 0) {
                    $totalReviews = count($ratings);
                    $averageRating = round(array_sum($ratings) / $totalReviews, 1);

                    // Approximate recommend % based on 4★+ ratings
                  $recommendCount = count(array_filter($ratings, function($r) {
    return $r >= 4;
}));
$recommendPercent = round(($recommendCount / $totalReviews) * 100);
                } else {
                    $totalReviews = 0;
                    $averageRating = 0;
                    $recommendPercent = 0;
                }
            } catch (Exception $e) {
                $totalReviews = 0;
                $averageRating = 0;
                $recommendPercent = 0;
            }
            ?>

            <div class="row text-center">
                <div class="col-md-4 escape_room_home_Customer_Reviews_counter">
                    <div class="value escape_room_home_Customer_Reviews_avg">
                        <?php echo $averageRating; ?>/5
                    </div>
                    <small class="text-muted">Average Rating</small>
                </div>
                <div class="col-md-4 escape_room_home_Customer_Reviews_counter">
                    <div class="value escape_room_home_Customer_Reviews_recommend">
                        <?php echo $recommendPercent; ?>%
                    </div>
                    <small class="text-muted">Recommend</small>
                </div>
                <div class="col-md-4 escape_room_home_Customer_Reviews_counter">
                    <div class="value escape_room_home_Customer_Reviews_total">
                        <?php echo $room['reviewsCount']; ?>
                    </div>
                    <small class="text-muted">Total Reviews</small>
                </div>
            </div>
        </div>
    </div>
</section>

<section>
    <div class="escape_room_home_contact_section">
        <div class="container">
            <div class="Boo_Prison_Escap_main_heading">
                <h2>Ready for STEAMPUNK SUBMARINE?</h2>
            </div>
            <div class="row">
                <div class="col-md-4 escape_room_home_contact_item">
                    <div class="escape_room_home_contact_icon">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h5>Location</h5>
                    <p class="footer_address__box_p">
                            2222 152nd Ave NE, <br> Redmond, WA, 98052 
                        </p>
                </div>
                <div class="col-md-4 escape_room_home_contact_item">
                    <div class="escape_room_home_contact_icon">
                        <i class="bi bi-telephone"></i>
                    </div>
                    <h5>Phone</h5>
                    <p>
                    <a href="tel:4252871426" class="text-gray-300" >425-287-1426</a>
                    </p>
                    <!--<p>Available 24/7</p>-->
                </div>
                <div class="col-md-4 escape_room_home_contact_item">
                    <div class="escape_room_home_contact_icon">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <h5>Email</h5>
                    <p>
                        <a class="text-gray-300" href="mailto:info@fleeescape.com" >info@fleeescape.com</a>
                    </p>
                    <p>Quick response guaranteed</p>
                </div>
            </div>
            <a href="#Book_one_single_pr" class="bg_bnt_custom  continue_nex_step"> Book STEAMPUNK SUBMARINE Now</a>
        </div>
    </div>
</section>

<div class="escape_room_home_comparison">
    <div class="Boo_Prison_Escap_main_heading">
        <h2 class="" data-aos="fade-down" data-aos-duration="1000">Room Comparison</h2>
    </div>
    <div class="container">
        <div class="row g-4">
            
           
<?php
include('admin/db.php');

try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_service 
                           WHERE category_id = 'escape room'
                           ORDER BY id ASC");
    $stmt->execute();
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rooms && count($rooms) > 0) {
        $delay = 100;
        foreach ($rooms as $room) {
            $title = htmlspecialchars($room['title']);
            $story = htmlspecialchars(substr($room['middle_h2'], 0, 120)) . '...';
            $theme = htmlspecialchars($room['theme']);
            $difficulty = htmlspecialchars(ucfirst($room['difficulty']));
             $duration = htmlspecialchars(ucfirst($room['duration']));
            $players = htmlspecialchars($room['players']);
            $price = !empty($room['price']) ? htmlspecialchars($room['price']) : '—';
            $cover_photo = !empty($room['cover_photo']) ? "admin/uploads/" . $room['cover_photo'] : "img/default.jpg";
            $facilities = htmlspecialchars($room['facilities']);
          $facilitiesArray = explode(',', $facilities);

           

          
?>
    <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
        <div class="comparison_card h-100">
            <div>
                <img src="<?php echo $cover_photo; ?>" loading="lazy" alt="<?php echo $title; ?>">
                <div class="comparison_card_content">
                    <h5 class="comparison_card-title"><?php echo $title; ?></h5>
                    <p class="comparison_card-text"><?php echo $story; ?></p>
    
                   
                    
                     <p class="comparison_card_content_data_items"><strong>Theme:</strong><span
                                    class="badge bg-primary"><?php echo $theme; ?></span></p>
                                     <p class="comparison_card_content_data_items"><strong>Difficulty:</strong><span
                                    class="badge bg-danger"><?php echo $difficulty; ?></span></p>
                    <p class="comparison_card_content_data_items"><strong>Duration:</strong><span><?php echo $duration; ?> min</span>
                            </p>
                    <p class="comparison_card_content_data_items">
                        <strong>Players:</strong><span><?php echo $players; ?></span>
                    </p>
                    <p class="comparison_card_content_data_items">
                        <strong>Price:</strong><span>$<?php echo $price; ?>/person</span>
                    </p>
                    <p class="comparison_card_content_data_items">
                        <strong>Rating:</strong>
                        <span class="rating">
                            <?php 
                            // Optional: Fetch average rating dynamically from testimonial table
                            $rateStmt = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM tbl_escape_room_testimonial WHERE category = ?");
                            $rateStmt->execute([$room['product_id']]);
                            $ratingData = $rateStmt->fetch(PDO::FETCH_ASSOC);
                            $avgRating = $ratingData && $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 1) : 4.5;
                            $filledStars = str_repeat('⭐', floor($avgRating));
                            echo "$filledStars ";
                            ?>
                        </span>
                    </p>
    
                    <div class="comparison_card_content_features">
                        <?php foreach ($facilitiesArray as $feat) { ?>
                            <span><?php echo trim($feat); ?></span>
                        <?php } ?>
                    </div>
                </div>
            </div>
             <div class="comparison_card_bnt">
    <?php
    // Room slug/link से dynamic URL
    $page_url = !empty($room['link']) ? $room['link'] : 'default-page.php';
    ?>
    <!--<a href="<?php echo $page_url; ?>#Book_one_single_pr" class="bg_bnt_custom continue_nex_step">Book Now</a>-->
    <a href="<?php echo $page_url; ?>" class="bg_bnt_custom continue_nex_step">Learn More</a>
</div>
        </div>
    </div>
<?php
            $delay += 100;
        }
    } else {
        echo "<p class='text-center text-muted mt-3'>No escape rooms available right now.</p>";
    }
} catch (Exception $e) {
    echo "<p class='text-danger text-center mt-3'>Error loading rooms.</p>";
}
?>
</div>
        
    </div>
</div>
</section>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/luxon@3/build/global/luxon.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script>
document.addEventListener("DOMContentLoaded", function () {
    console.log("dateInput element:", document.getElementById("Book-Prison-Date"));
    const { DateTime } = luxon;
    const LA_ZONE     = "America/Los_Angeles";
    const productCode = '<?php echo $productCode; ?>';
    const minGuests   = <?php echo $minGuests; ?>;
    const maxGuests   = <?php echo $maxGuests; ?>;

    // State Variables
    let guestCount = 0;
    let selectedTimeSlot = null;
    let slotAvailableSeats = 0; 

    // Timezone setup
    const laNow = DateTime.now().setZone(LA_ZONE);
    const laDate = laNow.toJSDate(); 

    const dateInput = document.getElementById("Book-Prison-Date-inline");
    if (!dateInput) {
        console.error("Date input element not found!");
        return;
    }
    if (dateInput._flatpickr) {
        dateInput._flatpickr.destroy();
    }

    // --- HELPER: Create and Inject Year Dropdown ---
    function injectCustomYearDropdown(instance) {
        if (!instance || !instance.calendarContainer) return;
        const calendar = instance.calendarContainer;
        if (calendar.querySelector(".flatpickr-year-dropdown")) return;
    
        const numInputWrapper = calendar.querySelector(".numInputWrapper");
        if (numInputWrapper) numInputWrapper.style.setProperty("display", "none", "important");
    
        const yearSelect = document.createElement("select");
        yearSelect.className = "flatpickr-year-dropdown";
    
        const currentYear = new Date().getFullYear();
        for (let i = currentYear; i <= currentYear + 3; i++) {
            const opt = document.createElement("option");
            opt.value = i;
            opt.text = i;
            yearSelect.appendChild(opt);
        }
        yearSelect.value = instance.currentYear;
    
        yearSelect.addEventListener("change", function(e) {
            instance.changeYear(parseInt(e.target.value));
        });
    
        const monthContainer = calendar.querySelector(".flatpickr-current-month");
        if (monthContainer) monthContainer.appendChild(yearSelect);
    }

    if (dateInput) {
        // Destroy existing flatpickr instance if any
        if (dateInput._flatpickr) {
            dateInput._flatpickr.destroy();
        }
    
        const fp = flatpickr(dateInput, {
            inline: true,                    // ← render calendar inside the div
            dateFormat: "Y-m-d",
            defaultDate: laNow.toJSDate(),
            minDate: laNow.toJSDate(),
            prevArrow: "←",
            nextArrow: "→",
            disableMobile: true,
        
            onReady: function(selectedDates, dateStr, instance) {
                console.log("✅ Flatpickr ready");
                injectCustomYearDropdown(instance);
            },
        
            onYearChange: function(selectedDates, dateStr, instance) {
                const yearSelect = instance.calendarContainer.querySelector(".flatpickr-year-dropdown");
                if (yearSelect) yearSelect.value = instance.currentYear;
            },
        
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    console.log("✅ onChange fired:", dateStr);
                    const displayDate = selectedDates[0].toLocaleDateString('en-US', {
                        weekday: 'short', year: 'numeric', month: 'long', day: 'numeric'
                    });
                    document.getElementById("summary-date").textContent = displayDate;
                    resetSelections();
                    fetchTimeSlots(selectedDates[0]);
                }
            }
        });
        
        // Set initial summary date display
        document.getElementById("summary-date").textContent = laNow.toJSDate().toLocaleDateString('en-US', {
            weekday: 'short', year: 'numeric', month: 'long', day: 'numeric'
        });
                
        // Inject year dropdown after ready
        setTimeout(() => {
            if (fp) injectCustomYearDropdown(fp);
        }, 300);
    }

    // --- LOGIC FUNCTIONS (No changes needed here) ---
    
    function fetchTimeSlots(date) {
        if (!date) return;
        const formattedDate = DateTime.fromJSDate(date).toFormat("yyyy-MM-dd");
        const timeSlotsContainer = document.getElementById("timeSlots-" + productCode);
        if (!timeSlotsContainer) return;

        timeSlotsContainer.innerHTML = '<div class="col-12"><p>Loading time slots...</p></div>';

        $.ajax({
            url: "fetch_slots_by_product.php",
            type: "GET",
            data: { productCode: productCode, date: formattedDate },
            success: function(response) {
                timeSlotsContainer.innerHTML = response;
                setTimeout(attachSlotListeners, 50); 
            },
            error: function() {
                timeSlotsContainer.innerHTML = '<div class="col-12"><p>Error loading slots.</p></div>';
            }
        });
    }

    function attachSlotListeners() {
        const container = document.getElementById("timeSlots-" + productCode);
        if(!container) return;
        const inputs = container.querySelectorAll(".Boo_Prison_Escape_time-slot");
        
        if (inputs.length === 0) {
            container.innerHTML = '<div class="col-12"><p>No available time slots.</p></div>';
            return;
        }

        inputs.forEach(function(input) {
            input.addEventListener("change", function() {
                inputs.forEach(i => { if(i !== input) i.checked = false; }); 
                
                if (input.checked) {
                    selectedTimeSlot = input.getAttribute("data-start-time");
                    const label = input.nextElementSibling.textContent.trim();
                    const parts = label.split(/\s+/);
                    const timeOnly = parts[0] + " " + (parts[1] || "");
                    
                    document.getElementById("summary-time").textContent = timeOnly;
                    slotAvailableSeats = parseInt(input.getAttribute("data-available")) || 0;
                    updateGuestOptions(slotAvailableSeats);
                }
            });
        });
    }

    // --- GUEST LOGIC ---
    const guestSelectEl = document.getElementById("guest-select-" + productCode);
    if (guestSelectEl) { 
        guestSelectEl.disabled = true; 
        guestSelectEl.classList.add("disabled"); 
    }

    function updateGuestOptions(availableSeats) {
        const sel = document.getElementById("guest-select-" + productCode);
        if (!sel) return;
        sel.disabled = false;
        sel.classList.remove("disabled");
        while (sel.options.length > 1) sel.remove(1);
        
        const effectiveMax = Math.min(maxGuests, availableSeats);
        for (let i = minGuests; i <= effectiveMax; i++) {
            const opt = document.createElement("option");
            opt.value = i;
            opt.textContent = i + " Guest" + (i > 1 ? "s" : "");
            sel.appendChild(opt);
        }
    }

    if (guestSelectEl) {
        guestSelectEl.addEventListener("change", function() {
            guestCount = parseInt(this.value) || 0;
            document.getElementById("summary-guests").textContent =
                guestCount > 0 ? guestCount + " Guest" + (guestCount > 1 ? "s" : "") : "0";
            updateTotalPrice();
            updateContinueButton();
        });
    }

    function updateTotalPrice() {
        if (guestCount <= 0) {
            document.getElementById("total-price-" + productCode).textContent = "$0";
            return;
        }
        const priceText = "<?php echo $price; ?>";
        let pricePerGuest = 0;
        if (priceText.includes("-")) {
            const parts = priceText.split("-");
            pricePerGuest = guestCount === 2
                ? parseFloat(parts[1].replace("$", ""))
                : parseFloat(parts[0].replace("$", ""));
        } else {
            pricePerGuest = parseFloat(priceText.replace("$", "")) || 0;
        }
        document.getElementById("total-price-" + productCode).textContent = "$" + (guestCount * pricePerGuest).toFixed(2);
        document.getElementById("per_guest").textContent = "$" + pricePerGuest;
    }

    function updateContinueButton() {
        const btn = document.getElementById("continueBtn-" + productCode);
        if (!btn) return;
        const ready = !!(selectedTimeSlot && guestCount > 0);
        btn.classList.toggle("disabled", !ready);
        btn.disabled = !ready;
    }

    function resetSelections() {
        selectedTimeSlot = null; guestCount = 0; slotAvailableSeats = 0;
        document.getElementById("summary-time").textContent   = "Not selected";
        document.getElementById("summary-guests").textContent = "0";
        document.getElementById("total-price-" + productCode).textContent = "$0";
        const sel = document.getElementById("guest-select-" + productCode);
        if (sel) {
            sel.value = ""; sel.disabled = true; sel.classList.add("disabled");
            while (sel.options.length > 1) sel.remove(1);
            for (let i = minGuests; i <= maxGuests; i++) {
                const opt = document.createElement("option");
                opt.value = i; opt.textContent = i + " Guest" + (i > 1 ? "s" : "");
                sel.appendChild(opt);
            }
        }
        updateContinueButton();
    }

    // INITIAL LOAD
    fetchTimeSlots(laNow.toJSDate());
});
</script>

<script>
$(document).ready(function() {
    let isProcessing = false;

    $(document).on("click", ".continue_nex_step1", function(e) {
        e.preventDefault();

        const btn = $(this);
        if (btn.prop("disabled") || isProcessing) return;

        isProcessing = true;
        btn.prop("disabled", true).addClass("disabled");

        const productCode = btn.data("game-id");
        const gameName     = btn.data("game-name");
        const guestCount   = $("#summary-guests").text();
        const unitPrice    = $("#per_guest").text().replace("$", "").trim();
        const selectedSlot = $(`input[name="lift-time-${productCode}"]:checked`);

        let slot      = "No slot";
        let eventId   = "";
        let available = "0";

        if (selectedSlot.length) {
            slot      = selectedSlot.val();
            eventId   = selectedSlot.data("eventid");
            available = selectedSlot.data("available") || "0";
        }

        $.ajax({
            url: "cart_session.php",
            method: "POST",
            data: {
                action: "add_to_cart",
                gameId: productCode,
                gameName: gameName,
                slot: slot,
                eventId: eventId,
                guests: guestCount,
                price: unitPrice,
                dataAvailable: available
            },
            dataType: "json",
            success: function(response) {
                if (response.status === "error") {
                    // Bookeo का exact message ही दिखाओ
                    showBookeoError(response.message);
                    return;
                }

                if (response.status === "success") {
                    const promoCode = response.promo || "";

                    $.ajax({
                        url: "apply_code.php",
                        method: "POST",
                        data: { code: promoCode },
                        dataType: "json",
                        success: function(holdResponse) {
                            if (holdResponse.status === "success") {
                                window.location.href = "booking?add-ons-";
                                return;
                            }

                            if (holdResponse.status === "error") {
                                showBookeoError(holdResponse.message);
                                return;
                            }

                            showBookeoError(holdResponse.message || "Failed to reserve slot. Please try again.");
                        },
                        error: function() {
                            showBookeoError("Failed to reserve slot. Please try again.");
                        }
                    });
                }
            },
            error: function() {
                showBookeoError("Failed to reserve slot. Please try again.");
            },
            complete: function() {
                isProcessing = false;
                btn.prop("disabled", false).removeClass("disabled");
            }
        });
    });
});
</script>
<style>
.call-slot-btn {
    display: inline-block;
        margin: 5px;
    padding: 8px;
    border: 2px solid #777;
    color: #00d4ff;
    cursor: pointer;
    font-size: 15px;
    white-space: nowrap;
    transition: 0.3s ease;
    display: grid;
    text-align: center;
    line-height: normal;
    font-weight: 600;
    border-radius: 3px;
}
.Available_play_time{
        color: #fff;
    line-height: initial;
    padding-top: 4px;
    font-weight: 300;
    font-size: 14px;
}


.call-popup-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
.call-popup-box {
    background: #fff;
    padding: 20px 30px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
.call-popup-box button {
    margin-top: 10px;
    padding: 6px 14px;
    background: #1976d2;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
.call-popup-box button:hover { background: #1565c0; }
</style>
<div id="callPopup" class="popup-overlay" style="display:none;">
  <div class="popup-box">
    <p id="callText">To make a booking, call us on (425)287-1426</p>
    <div class="popup-buttons">
      <button id="cancelBtn">Cancel</button>
      <a id="callNowBtn" href="tel:(425)287-1426">Call now</a>
    </div>
  </div>
</div>
<script> 
function showCallPopup(time) {
    const popup = document.createElement("div");
    popup.className = "call-popup-overlay";
    popup.innerHTML = `
        <div class="call-popup-box">
            <p>To book for ${time}, please call our support team.</p>
             <button > <a id="callNowBtn" href="tel:(425)287-1426">Call now</a></button>
            <button onclick="this.closest(\'.call-popup-overlay\').remove()">Close</button>
        </div>`;
    document.body.appendChild(popup);
}
</script>
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
<div class="modal fade blur-modal" id="Indoormodal" tabindex="-1" aria-labelledby="IndoorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="videoModalLabel"><?=htmlspecialchars($gameTitle)?></h5>
                <button type="button" class="btn btn-sm close-btn" data-bs-dismiss="modal" aria-label="Close"
                    onclick="stopLocalVideo()">X</button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <!-- muted -->
                    <video id="localVideo" controls>
                        <source src="<?php echo $trailer ; ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
