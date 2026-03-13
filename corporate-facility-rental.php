<?php session_start();
include('link.php');
include('admin/db.php');

$stmt = $pdo->query("SELECT * FROM tbl_facility_rental_page LIMIT 1");
$page = $stmt->fetch(PDO::FETCH_ASSOC);

// If blank, avoid errors
if (!$page) {
    $page = [
        'page_title' => '',
        'keywords' => '',
        'page_description' => ''
    ];
}

// Dynamic Metas
$pageTitle = !empty($page['page_title']) ? $page['page_title'] : 'Private Facility Rentals - Large Groups';
$metaKeywords = $page['keywords'] ?? '';
$metaDescription = $page['page_description'] ?? '';
$canonicalURL = $link."corporate-facility-rental";
include('includes/header.php');

include('admin/db.php');

$stmt = $pdo->query("SELECT * FROM tbl_facility_rental_page WHERE id = 1 LIMIT 1");
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>


<style>
    <style>
/* highlight the selected slot */
.time_slot_group input[type="radio"]:checked+label {
    background-color: #00d4ff;
    color: #000;
    box-shadow: 0 0 10px rgba(0, 212, 255, 0.6);
    border: 2px solid #77777700;
    border-radius: 3px;
}

/* make full slots visibly disabled & not clickable */
.time_slot_group input[type="radio"]:disabled+label {
    opacity: 0.5;
    pointer-events: none;
}
  .continue_next_step_event.disabled {
    opacity: 0.6;
    pointer-events: none;
    cursor: not-allowed;
}
</style>
</style>
<section>
    <div class="vr_page_banner all_baneer_IMG reponsive_inner_banner"
        style="background-image: url('admin/<?php echo $data['thumbnail']; ?>'); height:450px">
        
        <div class="container">
            <div class="vr_page_banner_content" style="position: relative; z-index: 1;">
                <h1><?php echo $data['heading']; ?></h1>
                <strong><?php echo $data['sub_heading']; ?></strong>
                <p><?php echo nl2br($data['sub_content']); ?></p>
            </div>
        </div>

        <div class="vr_page_banner_add_log_top">
            <img src="img/silver-award-badge.png" loading="lazy" alt="">
            <img src="img/bronze-award-badge.png" loading="lazy" alt="">
        </div>
    </div>
</section>


<section class="Facility_Rentals_Entire">
    <div class="container">
        <div class="row g-4 text-center">

            <!-- Card 1 -->
            <div class="col-md-3">
                <div class="Corporate_Team_Building_card p-4 h-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-award w-8 h-8 text-accent mx-auto mb-3">
                        <path
                            d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526">
                        </path>
                        <circle cx="12" cy="8" r="6"></circle>
                    </svg>

                    <h5 class="fw-bold"><?php echo $data['heading1']; ?></h5>
                    <p><?php echo $data['description1']; ?></p>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="col-md-3">
                <div class="Corporate_Team_Building_card p-4 h-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-building2 w-8 h-8 text-accent mx-auto mb-3">
                        <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                        <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                        <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                        <path d="M10 6h4"></path>
                        <path d="M10 10h4"></path>
                        <path d="M10 14h4"></path>
                        <path d="M10 18h4"></path>
                    </svg>

                    <h5 class="fw-bold"><?php echo $data['heading2']; ?></h5>
                    <p><?php echo $data['description2']; ?></p>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="col-md-3">
                <div class="Corporate_Team_Building_card p-4 h-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-users w-8 h-8 text-accent-secondary mx-auto mb-3">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>

                    <h5 class="fw-bold"><?php echo $data['heading3']; ?></h5>
                    <p><?php echo $data['description3']; ?></p>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="col-md-3">
                <div class="Corporate_Team_Building_card p-4 h-100">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-clock w-8 h-8 text-accent-tertiary mx-auto mb-3">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>

                    <h5 class="fw-bold"><?php echo $data['heading4']; ?></h5>
                    <p><?php echo $data['description4']; ?></p>
                </div>
            </div>

        </div>

        <div class="text-center mt-3">
            <a href="javascript:void(0)" class="bg_bnt_custom bg_bnt_custom_tran scrollToContact">
                Request a Quote
            </a>
            <a href="javascript:void(0)" class="bg_bnt_custom scrollToParty">
                View Packages
            </a>
        </div>

    </div>
</section>


<section>
    <div class="container">
        
        <div class="section_heading_page">
            <h2 class="section-title">
                <?php echo $data['subway_heading']; ?>
            </h2>

            <p class="section-subtitle">
                <?php echo nl2br($data['subway_sub_heading']); ?>
            </p>
        </div>

        <div class="vr_game_video_modal">
            <img src="admin/<?php echo $data['party_thumbnail']; ?>" loading="lazy" alt="">

            <div class="vr_game_video_modal_content" 
                 data-bs-toggle="modal" 
                     data-bs-target="#videobanner">
                <i class="fa-regular fa-circle-play"></i>
            </div>
        </div>
    </div>
</section>


<section>
    <div class="Complete_Corporate_Team ">
        <div class="container mt-5">
            <div class="row g-4 text-center">

                <!-- Feature 1 -->
                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <h5 class="fw-bold">
                            <?= !empty($data['feature_1_title']) ? $data['feature_1_title'] : '' ?>
                        </h5>
                        <p>
                            <?= !empty($data['feature_1_desc']) ? nl2br($data['feature_1_desc']) : '' ?>
                        </p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <h5 class="fw-bold">
                            <?= !empty($data['feature_2_title']) ? $data['feature_2_title'] : '' ?>
                        </h5>
                        <p>
                            <?= !empty($data['feature_2_desc']) ? nl2br($data['feature_2_desc']) : '' ?>
                        </p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <h5 class="fw-bold">
                            <?= !empty($data['feature_3_title']) ? $data['feature_3_title'] : '' ?>
                        </h5>
                        <p>
                            <?= !empty($data['feature_3_desc']) ? nl2br($data['feature_3_desc']) : '' ?>
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<?php
include('admin/db.php');

// Fetch data
$stmt = $pdo->query("SELECT * FROM tbl_facility_rental_page LIMIT 1");
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<section class="fect_Every_Occasion">
    <div class="container">
        <div class="eam-transform-section_box">

            <!-- Dynamic Heading -->
            <div class="section_heading_page">
                <h2 class="section-title">
                    <?= $data['occasion_heading'] ?>
                </h2>
                <p class="section-subtitle">
                    <?= nl2br($data['occasion_sub_heading']) ?>
                </p>
            </div>

            <div class="row g-4 Every_Occasion_cards">

                <!-- Occasion 1 -->
                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon-circle" style="background-color: #00dcb451;">
                            <!-- ICON STATIC -->
                            <svg style="color: #00dcb4;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-briefcase w-8 h-8 text-accent">
                                <path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                <rect width="20" height="14" x="2" y="6" rx="2"></rect>
                            </svg>
                        </div>
                        <h5 class="fw-bold mt-3">
                            <?= $data['occasion_1_title'] ?>
                        </h5>
                        <p><?= nl2br($data['occasion_1_desc']) ?></p>
                    </div>
                </div>

                <!-- Occasion 2 -->
                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon-circle" style="background-color: #c156ff51;">
                            <!-- ICON STATIC -->
                            <svg style="color: #c056ff;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-party-popper w-8 h-8 text-accent-secondary">
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
                        </div>
                        <h5 class="fw-bold mt-3"><?= $data['occasion_2_title'] ?></h5>
                        <p><?= nl2br($data['occasion_2_desc']) ?></p>
                    </div>
                </div>

                <!-- Occasion 3 -->
                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon-circle" style="background-color: #ff88004c;">
                            <!-- ICON STATIC -->
                            <svg style="color: #ff8800;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-users w-8 h-8 text-accent-tertiary">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <h5 class="fw-bold mt-3"><?= $data['occasion_3_title'] ?></h5>
                        <p><?= nl2br($data['occasion_3_desc']) ?></p>
                    </div>
                </div>

                <!-- Occasion 4 -->
                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon-circle" style="background-color: #00dcb451;">
                            <!-- ICON STATIC -->
                            <svg style="color: #00dcb4;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-heart w-8 h-8 text-accent">
                                <path
                                    d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z">
                                </path>
                            </svg>
                        </div>
                        <h5 class="fw-bold mt-3"><?= $data['occasion_4_title'] ?></h5>
                        <p><?= nl2br($data['occasion_4_desc']) ?></p>
                    </div>
                </div>

                <!-- Occasion 5 -->
                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon-circle" style="background-color: #c156ff51;">
                            <!-- ICON STATIC -->
                            <svg style="color: #c056ff;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-calendar w-8 h-8 text-accent-secondary">
                                <path d="M8 2v4"></path>
                                <path d="M16 2v4"></path>
                                <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                                <path d="M3 10h18"></path>
                            </svg>
                        </div>
                        <h5 class="fw-bold mt-3"><?= $data['occasion_5_title'] ?></h5>
                        <p><?= nl2br($data['occasion_5_desc']) ?></p>
                    </div>
                </div>

                <!-- Occasion 6 -->
                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon-circle" style="background-color: #ff88003b;">
                            <!-- ICON STATIC -->
                            <svg style="color: #ff8800;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-building2 w-8 h-8 text-accent-tertiary">
                                <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                                <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                                <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                                <path d="M10 6h4"></path>
                                <path d="M10 10h4"></path>
                                <path d="M10 14h4"></path>
                                <path d="M10 18h4"></path>
                            </svg>
                        </div>
                        <h5 class="fw-bold mt-3"><?= $data['occasion_6_title'] ?></h5>
                        <p><?= nl2br($data['occasion_6_desc']) ?></p>
                    </div>
                </div>

            </div>

        </div>
    </div>
</section>
         <style>
    .continue_next_step_party.disabled {
    opacity: 0.6;
    pointer-events: none;
    cursor: not-allowed;
}

.flatpickr-year-dropdown option {
    background: #fff; /* White background */
    font-weight: normal;
}

/* Hide the original number input wrapper */
.numInputWrapper.hidden-year {
    display: none !important;
}

@media (max-width: 768px) {
    .flatpickr-current-month{
        padding: 0px;
    }
}
</style>

<section class="Celebrate_Style Flexible_Rental_Packages" id="party-package">
    <div class="container">
        <div class="section_heading_page">
            <h2 class="section-title">Flexible Rental Packages</h2>
            <p class="section-subtitle">Choose the perfect package for your group. Each package includes escape <br>
                rooms, VR games, and full facility access.
            </p>
        </div>
        <div class=" booking_tab_content">
            
   
<?php
session_start();

// Enable PHP error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('link.php');
include("admin/db.php");

$expiryDays = 30;
$data = null;
$useCache = false;

// 1. Fetch cache from DB
$stmt = $pdo->prepare("SELECT * FROM bookeo_products_cache WHERE id = 1 LIMIT 1");
$stmt->execute();
$cacheRow = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cacheRow) {
    $updatedAt = strtotime($cacheRow['stored_at']);
    $now = time();

    // Check expiry (30 days)
    if (($now - $updatedAt) <= ($expiryDays * 24 * 60 * 60)) {

        // Decode the stored JSON
        $cachedData = json_decode($cacheRow['product_data'], true);

        if ($cachedData && isset($cachedData['data'])) {
            $data = $cachedData;
            $useCache = true;
        }
    }
}

if (!$useCache) {

    // CALL BOOKEO API
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.bookeo.com/v2/settings/products',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array(
            'X-Bookeo-apiKey: AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC',
            'X-Bookeo-secretKey: RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4',
            'Accept: application/json'
        ),
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
    ));

    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($response === false || $curlError) {

        // API FAILED → Use old cache (if exists)
        if ($cacheRow) {
            $data = json_decode($cacheRow['product_data'], true);
        } else {
            die("API Error: " . htmlspecialchars($curlError));
        }

    } else {

        $json = json_decode($response, true);

        // Invalid API → fallback
        if (!isset($json['data']) || !is_array($json['data'])) {

            if ($cacheRow) {
                $data = json_decode($cacheRow['product_data'], true);
            } else {
                die("Invalid API response");
            }

        } else {

            // Fresh API data
            $data = $json;

            // STORE / UPDATE CACHE
            $stmt = $pdo->prepare("
                INSERT INTO bookeo_products_cache (id, product_data, stored_at)
                VALUES (1, :json, NOW())
                ON DUPLICATE KEY UPDATE
                    product_data = VALUES(product_data),
                    stored_at = NOW()
            ");

            $stmt->execute([
                ':json' => json_encode($json)
            ]);
        }
    }
}

// Fail safe
if (!isset($data['data']) || !is_array($data['data'])) {
    $data['data'] = [];
}
// Collect product IDs
$productIds = [];
$count = 0;
foreach ($data['data'] as $product) {
   if ($count >= 13 && $count <= 14) {
    $productIds[] = htmlspecialchars($product['productCode'] ?? '');
   }
    $count++;
}
// Convert product IDs to JSON for hidden field
$productIdsJson = json_encode($productIds);
?>

<!-- Hidden input field to store product IDs -->
<input type="hidden" id="productIds" name="productIds" value="<?php echo htmlspecialchars($productIdsJson); ?>">

<div class="row">
    <?php
$count = 0;
foreach ($data['data'] as $product) {
    if ($count >= 13 && $count <= 14) {

    $name = htmlspecialchars($product['name'] ?? '');
    $productCode = htmlspecialchars($product['productCode'] ?? '');
    $duration = isset($product['duration']) ? $product['duration'] : ['hours' => 0, 'minutes' => 0];
    $desc = trim($product['description'] ?? '');

    // Split lines
    $lines = preg_split('/\r\n|\r|\n/', $desc);

    $players = isset($lines[0]) 
        ? preg_replace('/^.*?:\s*/', '', trim(strip_tags($lines[0]))) 
        : '';
    $difficulty = isset($lines[1]) ? trim($lines[1]) : '';
    $successRate = isset($lines[2]) ? trim($lines[2]) : '';
    $price = isset($lines[3]) 
        ? preg_replace('/^.*?:\s*/', '', trim(strip_tags($lines[3]))) 
        : '';

    if ($price) {
        preg_match_all('/\$\d+/', $price, $matches);
        if (count($matches[0]) >= 2) {
            $price = $matches[0][0] . '-' . $matches[0][1];
        } elseif (count($matches[0]) === 1) {
            $price = $matches[0][0];
        } else {
            $price = '';
        }
    }

    // Combine remaining lines for short description
    $remainingText = implode(" ", array_slice($lines, 4));
    $remainingText = strip_tags($remainingText);
    $words = preg_split('/\s+/', $remainingText);
    $shortDescription = implode(" ", array_slice($words, 0, 13));
    if (count($words) > 13) {
        $shortDescription .= "...";
    }

    // Image handling: keep original behavior if images exist
    $imageUrl = '';
    if (!empty($product['images'][0]['url'])) {
        $imageUrl = $product['images'][0]['url'];
    }
    
    
    $stmt = $pdo->prepare("SELECT id, product_id, title, price, strikethrough_price, duration, players, thumbnail, video, description 
                       FROM tbl_facility_rental_game 
                       WHERE product_id = :product_id LIMIT 1");
    $stmt->execute([':product_id' => $productCode]);
    $package = $stmt->fetch(PDO::FETCH_ASSOC);
?>
    <div class="col-md-6 col-sm-12">
        <div class="booking_card party_packages_card_new">
            <div>
            <div class="booking_card_img">
                <?php 
                if (!empty($imageUrl)) {
                    echo '<img src="' . htmlspecialchars($imageUrl) . '"  alt="' . $name . '" />';
                }
                ?>
                <div class="booking_card_time_and_price">
                    <p>
                        <i class="fa-solid fa-clock"></i>
                        <?php
                            $duration = $product['duration'];
                           
                           $hours = $duration['hours'] ?? 0;
$minutes = $duration['minutes'] ?? 0;

echo " {$hours} HOURS";
if ($minutes > 0) {
    echo ", {$minutes} Minutes";
}

                        ?>
                    </p>
                   
                    <p ><?php echo $price; ?></p> 
                    
                    
                   
                </div>
                
                <div class="booking_card_overlay">
                    <h5><?php echo $name; ?></h5>
                    <p><?php echo $shortDescription; ?></p>
                    <div class="player-count-display d-flex align-items-center">
                        <div>
                            <!--<span class="player-label">-->
                            <!--    <img class="palyear_tem_img" src="./assets/images/fleeescape_img/teampay.png" alt="">-->
                            <!--</span>-->
                            <!--<span class="player-value"><?php echo $players; ?> GUESTS</span>-->
                            <p class="Last_Minute_Deals_card_price">
                                <?php  foreach ($product['defaultRates'] as $rate) {
     
        $amount = $rate['price']['amount'];
        $currency = $rate['price']['currency'];
        echo "<strong>$".$amount." </strong>";
    } ?>
                                
                                
                                
                                <del><?=$package['strikethrough_price']?></del>
                            </p>
                            
                             <input type="hidden" id="price-<?php echo $productCode; ?>" name="price-<?php echo $productCode; ?>" value="<?php echo htmlspecialchars($amount); ?>">
                        </div>
                        <div class="icon_buttons_wrapper">
                            <div class="icon-button" data-bs-toggle="modal" data-bs-target="#liftInfoModal<?php echo htmlspecialchars($productCode); ?>">
                                <i class="fa-solid fa-circle-info"></i>
                                <span class="label">Learn more</span>
                            </div>
                            <div class="icon-button" data-bs-toggle="modal" data-bs-target="#videoModal<?php echo htmlspecialchars($productCode); ?>">
                                <i class="fa-solid fa-circle-play"></i>
                                <span class="label">Watch Trailer</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="p-3">
                <div class="mb-2">
                    <span class="player-label">
                       
                        
                      
                    </span>
                    
                   <input type="hidden" id="players-<?php echo $productCode; ?>" 
       value="<?php echo $playersCount; ?>">
                    <!--<span class="player-value"><?php echo $players; ?> GUESTS</span>-->
                </div>
              <?php
// Fetch data from DB
$stmt = $pdo->prepare("SELECT card_subtitle FROM tbl_facility_rental_game WHERE product_id = :product_id LIMIT 1");
$stmt->execute([':product_id' => $productCode]);
$subtitleRow = $stmt->fetch(PDO::FETCH_ASSOC);

// Store in variable
$cardSubtitleRaw = $subtitleRow['card_subtitle'] ?? '';

// Split lines
$lines = preg_split("/\r\n|\n|\r/", $cardSubtitleRaw, -1, PREG_SPLIT_NO_EMPTY);

// First line
$firstLine = array_shift($lines);

// Other lines as <li>
$liItems = [];
foreach ($lines as $line) {
    $line = trim($line);
    if (isset($line[0]) && $line[0] === '*') {
        $liItems[] = htmlspecialchars(ltrim($line, '* '));
    }
}
?>

<div class="party_packages_card_new_desc">
    <?php if ($firstLine): ?>
        <p class="card-subtitle"><?= htmlspecialchars($firstLine); ?></p>
    <?php endif; ?>

    <?php if (!empty($liItems)): ?>
        <ul>
            <?php foreach ($liItems as $item): ?>
                <li><?= $item; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

                <div class="custom-date-wrapper_date">
                    <button class="custom-date_arrow prev-date" style="visibility: hidden;">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    <input type="text" class="custom-datepicker_input" data-product="<?php echo $productCode; ?>">
                    <button class="custom-date_arrow next-date">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>

                <div class="time_slots" id="timeSlots-<?php echo $productCode; ?>"></div>
                <!--<div class="View_All_Dates_tag"><a>View All Dates</a></div>-->

                <div class="guest_and_button">
                    
                    
                    <!--<div class="next-button-wrapper">-->
                 
                    <!--</div>-->
                </div>
            </div>
            </div>
            <div class="next-button-wrapper">
                        <!--<a href="#" class="bg_bnt_custom" data-bs-toggle="modal"-->
                        <!--    data-bs-target="#partymodalform">CONTINUE </a>-->
                           <?php echo '<button  class="continueBtn bg_bnt_custom  continue_next_step_event disabled" id="continueBtn-' . $productCode . '"  data-game-id="' . $productCode . '"  data-game-name="' . htmlspecialchars($name) . '" disabled >Continue</button>'; ?> 
                    </div>
        </div>
    </div>
    
  
    
    <?php
    
    
    if (!empty($productCode)) {

    include('admin/db.php');

    // Fetch the trailer video safely
    $stmt = $pdo->prepare("SELECT * FROM tbl_facility_rental_game WHERE product_id = :product_id LIMIT 1");
    $stmt->execute([':product_id' => $productCode]);
    $videoData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if query succeeded and returned a valid row
    if ($videoData && isset($videoData['video']) && !empty($videoData['video'])) {
        $videoPath = 'admin/uploads/' . $videoData['video'];
    } else {
        $videoPath = ''; // no video found
    }
?>
    <div class="modal fade blur-modal videoModal_z " id="videoModal<?php echo htmlspecialchars($productCode); ?>" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="videoModalLabel"><?=htmlspecialchars($videoData['title'])?></h5>
                    <button type="button" class="btn btn-sm close-btn" data-bs-dismiss="modal" aria-label="Close" onclick="stopLocalVideo()">X</button>
                </div>
                <div class="modal-body p-0">
                    <div class="ratio ratio-16x9">
                        <?php if (!empty($videoPath)) { ?>
                            <video id="localVideo"  controls>
                                <source src="<?php echo htmlspecialchars($videoPath); ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php } else { ?>
                            <div class="d-flex align-items-center justify-content-center" style="height:300px;">
                                <p class="text-center text-muted">No trailer video available for this game.</p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
} else {
    echo "<!-- Error: product_id not defined for modal -->";
}
// echo 'here package';
// print_r($package);
?>

<?php if ($package): ?>
<div class="modal fade" id="liftInfoModal<?php echo htmlspecialchars($productCode); ?>" tabindex="-1" aria-labelledby="liftInfoModal" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content custom-modal">
            <div class="modal-header border-0" style="align-items: flex-start!important;">
                <div class="info_modal_content" style="width: 90%;">
                    <h2 class="modal-title custom-heading" id="liftInfoModalLabel">
                        <?= htmlspecialchars($package['title']) ?>
                    </h2>
                </div>
                <button type="button" class="btn btn-sm close-btn" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5 text-center mb-3 mb-md-0">
                        <img src="admin/uploads/<?= htmlspecialchars($package['thumbnail']) ?>"  loading="lazy"  decoding="async" 
                             class="img-fluid rounded" alt="<?= htmlspecialchars($package['title']) ?>">
                    </div>

                    <div class="col-md-7">
                        <div class="modal_Beginner_badel">
                            <p>Beginner</p>
                        </div>

                        <div class="game-stats d-flex flex-wrap gap-3 mb-3">
                            <div class="stat-box">
                                <span class="stat-label">Players</span>
                                <span class="stat-value"><?= htmlspecialchars($package['players']) ?></span>
                            </div>
                            <div class="stat-box">
                                <span class="stat-label">Price</span>
                                <span class="stat-value"><?= htmlspecialchars($package['price']) ?></span>
                            </div>
                            <div class="stat-box">
                                <span class="stat-label"><i class="fas fa-clock me-1"></i> Duration</span>
                                <span class="stat-value"><?= htmlspecialchars($package['duration']) ?></span>
                            </div>
                        </div>

                        <div class="modal_info_p">
                            <p><?= nl2br(htmlspecialchars($package['description'])) ?></p>
                        </div>
                    </div>
                </div>

                <div class="all_button_main_header text-end" style="background-size: cover; background-repeat: no-repeat;">
                    <?php if (!empty($package['video'])): ?>
                        <a style="border-radius: 30px!important" class="bg_bnt_custom bg_bnt_custom_tran" data-bs-toggle="modal"
                           data-bs-target="#videoModal<?php echo htmlspecialchars($productCode); ?>"><i class="fa-solid fa-play m-2"></i> Watch Trailer</a>
                    <?php endif; ?>
                    <a style="border-radius: 30px!important" type="button" class="bg_bnt_custom"
                       data-bs-dismiss="modal" aria-label="Close">OK</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; 

}
    $count++;
} // end foreach
?>       
     
        </div>
    </div>
</section>

<section style="display:none">
    <div class="Additional_Guests">
        <div class="container">
            <div class="row">
                <div class="col-sm-6">
                    <div class="Additional_Guests_card h-100">
                        <div>
                            <div class="d-flex  align-items-center gap-3 mb-4">
                                <div class="Additional_Guests_card_icons" style="background-color: #00dcb44f;">
                                    <svg style="color: #00dcb4;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="lucide lucide-users w-6 h-6 text-accent">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                </div>
                                <h4>Additional Guests</h4>
                            </div>
                            <p>Need more guests? No problem! Add additional guests to any package at an extra cost per
                            person. Our facility can accommodate up to 120 guests total.</p>
                        </div>
                        <a href="javascript:void(0)" class="bg_bnt_custom  continue_nex_step  scrollToContact">Contact Us for Pricing</a>
                         
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="Additional_Guests_card h-100">
                        <div>
                            <div class="d-flex  align-items-center gap-3 mb-4">
                                <div class="Additional_Guests_card_icons" style="background-color: #c156ff5e;">
                                    <svg style="color: #c056ff;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="lucide lucide-check w-6 h-6 text-accent-secondary">
                                        <path d="M20 6 9 17l-5-5"></path>
                                    </svg>
                                </div>
                                <h4>Optional Add-Ons</h4>
                            </div>
                            <p>Enhance your event with our catering options. We offer vegetarian food packages and unlimited
                            soft beverages at an additional cost.</p>
                        </div>
                        <a href="javascript:void(0)" class="bg_bnt_custom  continue_nex_step  scrollToContact">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Corporate Birthday Parties Section -->
<section class="Corporate_Team_Building_section py-5">
    <div class="section_heading_page">
        <!-- <span class="badge-custom">🎉 Birthday Parties</span> -->
        <h2 class="section-title">Why Choose Escape Rooms & <br> VR Over</h2>
        <p class="section-subtitle">Traditional Corporate Team Building Parties'? </p>
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

<section>
    <div class="container">
        <div class="eam-transform-section_box">
            <div class="section_heading_page">
                <h2 class="section-title"><?= htmlspecialchars($page['amenities_heading']) ?></h2>
                <p class="section-subtitle">
                    <?= htmlspecialchars($page['amenities_sub_heading']) ?>
                </p>
            </div>
            <div class="row g-4  Every_Occasion_cards">
                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon-circle" style="background-color: #00dcb451;">
                            <svg style="color: #00dcb4;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-users w-7 h-7 text-accent">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <h5 class="fw-bold mt-3"><?= htmlspecialchars($page['amenities_1_title']) ?></h5>
                        <p><?= htmlspecialchars($page['amenities_1_desc']) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon-circle" style="background-color: #c156ff51;">
                            <svg style="color: #c056ff;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-gamepad2 w-7 h-7 text-accent-secondary">
                                <line x1="6" x2="10" y1="11" y2="11"></line>
                                <line x1="8" x2="8" y1="9" y2="13"></line>
                                <line x1="15" x2="15.01" y1="12" y2="12"></line>
                                <line x1="18" x2="18.01" y1="10" y2="10"></line>
                                <path
                                    d="M17.32 5H6.68a4 4 0 0 0-3.978 3.59c-.006.052-.01.101-.017.152C2.604 9.416 2 14.456 2 16a3 3 0 0 0 3 3c1 0 1.5-.5 2-1l1.414-1.414A2 2 0 0 1 9.828 16h4.344a2 2 0 0 1 1.414.586L17 18c.5.5 1 1 2 1a3 3 0 0 0 3-3c0-1.545-.604-6.584-.685-7.258-.007-.05-.011-.1-.017-.151A4 4 0 0 0 17.32 5z">
                                </path>
                            </svg>
                        </div>
                        <h5 class="fw-bold mt-3"><?= htmlspecialchars($page['amenities_2_title']) ?></h5>
                        <p><?= htmlspecialchars($page['amenities_2_desc']) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon-circle" style="background-color: #ff88004c;">
                            <svg style="color: #ff8800;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-target w-7 h-7 text-accent-tertiary">
                                <circle cx="12" cy="12" r="10"></circle>
                                <circle cx="12" cy="12" r="6"></circle>
                                <circle cx="12" cy="12" r="2"></circle>
                            </svg>
                        </div>
                        <h5 class="fw-bold mt-3"><?= htmlspecialchars($page['amenities_3_title']) ?></h5>
                        <p><?= htmlspecialchars($page['amenities_3_desc']) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon-circle" style="background-color: #00dcb451;">
                            <svg style="color: #00dcb4;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-sparkles w-7 h-7 text-accent">
                                <path
                                    d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z">
                                </path>
                                <path d="M20 3v4"></path>
                                <path d="M22 5h-4"></path>
                                <path d="M4 17v2"></path>
                                <path d="M5 18H3"></path>
                            </svg>
                        </div>
                        <h5 class="fw-bold mt-3"><?= htmlspecialchars($page['amenities_4_title']) ?></h5>
                        <p><?= htmlspecialchars($page['amenities_4_desc']) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon-circle" style="background-color: #c156ff51;">
                            <svg style="color: #c056ff;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-ice-cream-cone w-7 h-7 text-accent-secondary">
                                <path d="m7 11 4.08 10.35a1 1 0 0 0 1.84 0L17 11"></path>
                                <path d="M17 7A5 5 0 0 0 7 7"></path>
                                <path d="M17 7a2 2 0 0 1 0 4H7a2 2 0 0 1 0-4"></path>
                            </svg>
                        </div>
                        <h5 class="fw-bold mt-3"><?= htmlspecialchars($page['amenities_5_title']) ?></h5>
                        <p><?= htmlspecialchars($page['amenities_5_desc']) ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="Corporate_Team_Building_card p-4 h-100">
                        <div class="icon-circle" style="background-color: #ff88003b;">
                            <svg style="color: #ff8800;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-coffee w-7 h-7 text-accent-tertiary">
                                <path d="M10 2v2"></path>
                                <path d="M14 2v2"></path>
                                <path
                                    d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1">
                                </path>
                                <path d="M6 2v2"></path>
                            </svg>
                        </div>
                        <h5 class="fw-bold mt-3"><?= htmlspecialchars($page['amenities_6_title']) ?></h5>
                        <p><?= htmlspecialchars($page['amenities_6_desc']) ?></p>
                    </div>
                </div>

            </div>
        </div>
        </div>
</section>

<section class="adventure-section">
    <div class="container">
        <div class="adventure-content">
            <div class="adventure-content_svg" style="background-color: #00dcb44f;">
                <svg style="color: #00dcb4;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" class="lucide lucide-coffee w-8 h-8 text-accent">
                    <path d="M10 2v2"></path>
                    <path d="M14 2v2"></path>
                    <path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1">
                    </path>
                    <path d="M6 2v2"></path>
                </svg>
            </div>
            <h2>Optional Catering Services</h2>
            <p>Enhance your event with our catering options. We offer delicious vegetarian <br> food packages and
                and satisfied throughout the event.
                unlimited <br> soft beverages to keep your guests energized </p>
            <div class="adventure-section_list_bnt_aa d-flex align-items-center justify-content-center gap-2 flex-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-sparkles w-5 h-5 text-accent">
                    <path
                        d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z">
                    </path>
                    <path d="M20 3v4"></path>
                    <path d="M22 5h-4"></path>
                    <path d="M4 17v2"></path>
                    <path d="M5 18H3"></path>
                </svg>
                <span>Available at additional cost</span>
            </div>
        </div>
    </div>
</section>

<!-- Experience Gallery Section -->
<section class="Experience_Gallery py-5">
    <div class="container text-center">

        <div class="section_heading_page">
            <h2 class="section-title">Experience Gallery</h2>
            <p class="section-subtitle">Take a peek at the fun that awaits you</p>
        </div>

        <div class="row">

            <?php
        

            $stmt = $pdo->query("SELECT * FROM tbl_facility_rental_gallery");
            $gallery = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($gallery as $g) {
                // Full image for fancybox
                $full_img = "admin/uploads/" . $g['image'];
                // Thumbnail image
                $thumb_img = "admin/uploads/" . $g['image'];
            ?>

                <div class="col-md-3 col-6">
                    <a data-fancybox="gallery" href="<?= $full_img ?>">
                        <div class="gallery-item">
                            <img src="<?= $thumb_img ?>" loading="lazy" alt="Gallery" class="img-fluid rounded">

                            <div class="overlay">
                                <i class="fa-solid fa-eye"></i>
                            </div>

                            <div class="overlay-text">
                                <h6><?= htmlspecialchars($g['first_heading']) ?></h6>
                                <p><?= htmlspecialchars($g['second_heading']) ?></p>
                            </div>
                        </div>
                    </a>
                </div>

            <?php } ?>

        </div>
    </div>
</section>


<section class=" review_section">
    <div class="section_heading_page">
        <h2 class="section-title">What Our Customers Say</h2>
        <p class="section-subtitle">Don't just take our word for it - hear from Fortune 500 companies who've celebrated <br> their team events with us</p>
    </div>

  <div class="owl-carousel owl-theme Our_customers_love">


        <?php
     

        $stmt = $pdo->query("SELECT * FROM tbl_facility_rental_testimonial ORDER BY id DESC");
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($reviews as $r) {

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


<section class="Facility_Rentals_owl_slider" style="display:none">
    <div class="container our_clients_slider">
        <div class="section_heading_page">
            <h2 class="section-title">Trusted by Fortune 500 Companies</h2>
            <p class="section-subtitle">500+ successful team building events hosted</p>
        </div>
        <div class="row align-items-center">
            <div class="col-lg-12 mt-2">
                <div class="owl-carousel owl-theme" id="thumbnail-carousel">
                    <div class="item">
                        <div class="p-2 rounded-10" data-bgcolor="rgba(255, 255, 255, .05)">
                            <img src="assets/images/payments/1.png"  class="img-fluid" alt="" loading="lazy">
                        </div>
                    </div>
                    <div class="item">
                        <div class="p-2 rounded-10" data-bgcolor="rgba(255, 255, 255, .05)">
                            <img src="assets/images/payments/2.png" class="img-fluid" alt="" loading="lazy">
                        </div>
                    </div>
                    <div class="item">
                        <div class="p-2 rounded-10" data-bgcolor="rgba(255, 255, 255, .05)">
                            <img src="assets/images/payments/3.png" class="img-fluid" alt="" loading="lazy">
                        </div>
                    </div>
                    <div class="item">
                        <div class="p-2 rounded-10" data-bgcolor="rgba(255, 255, 255, .05)">
                            <img src="assets/images/payments/4.png" class="img-fluid" alt="" loading="lazy">
                        </div>
                    </div>
                    <div class="item">
                        <div class="p-2 rounded-10" data-bgcolor="rgba(255, 255, 255, .05)">
                            <img src="assets/images/payments/6.png" class="img-fluid" alt="" loading="lazy">
                        </div>
                    </div>
                    <div class="item">
                        <div class="p-2 rounded-10" data-bgcolor="rgba(255, 255, 255, .05)">
                            <img src="assets/images/payments/5.png" class="img-fluid" alt="" loading="lazy">
                        </div>
                    </div>
                    <div class="item">
                        <div class="p-2 rounded-10" data-bgcolor="rgba(255, 255, 255, .05)">
                            <img src="assets/images/payments/6.png" class="img-fluid" alt="" loading="lazy">
                        </div>
                    </div>
                    <div class="item">
                        <div class="p-2 rounded-10" data-bgcolor="rgba(255, 255, 255, .05)">
                            <img src="assets/images/payments/9.jpeg" class="img-fluid" alt="" loading="lazy">
                        </div>
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

<section>
    <div class="container">
        <div class="faq-section my-5">
            <h2 class="text-center mb-4"> FAQ</h2>

            <div class="accordion" id="faqAccordion">

                <?php
               

                $stmt = $pdo->query("SELECT * FROM tbl_facility_rental_faq ORDER BY id ASC");
                $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $i = 1;
                foreach ($faqs as $faq) {
                    $collapseId = "faqCollapse" . $i;
                    $headingId = "faqHeading" . $i;
                ?>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="<?= $headingId ?>">
                            <button class="accordion-button collapsed" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#<?= $collapseId ?>"
                                    aria-expanded="false"
                                    aria-controls="<?= $collapseId ?>">

                                <?= htmlspecialchars($faq['question']) ?>

                                <span class="faq-toggle-icon ms-auto">
                                    <span class="plus">+</span>
                                    <span class="minus" style="display:none;">−</span>
                                </span>
                            </button>
                        </h2>

                        <div id="<?= $collapseId ?>" class="accordion-collapse collapse"
                             aria-labelledby="<?= $headingId ?>"
                             data-bs-parent="#faqAccordion">

                            <div class="accordion-body">
                                <?= nl2br(htmlspecialchars($faq['answer'])) ?>
                            </div>

                        </div>
                    </div>

                <?php
                    $i++;
                }
                ?>

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
                    <a href="booking.php#Facility-Rentals" class="bg_bnt_custom">Facility Rental</a>
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
                            <span><strong>Email</strong><br><a class="text-gray-300" href="mailto:info@fleeescape.com">info@fleeescape.com</a></span>
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
                   <form id="partyForm"  data-aos="fade-up" data-aos-delay="200">
    <div class="Party_contact_form_row">
        <input id="first_name" name="first_name" type="text" placeholder="Enter your first name" required>
        <input id="last_name" name="last_name" type="text" placeholder="Enter your last name" required>
    </div>

    <div class="Party_contact_form_row">
        <input id="guests" name="guests" type="number" placeholder="Enter number of guests" required>
        <input id="duration" name="duration" type="text" placeholder="Enter desired duration" required>
    </div>

    <div class="Party_contact_form_row">
      <input 
  id="phone" 
  name="phone" 
  type="tel" 
  placeholder="Enter phone number" 
  pattern="[0-9]{10}" 
  maxlength="10" 
  required
>

    <input 
        id="event_date"
        name="event_date"
        type="text"
        placeholder="MM-DD-YYYY" required
    >
    </div>

    <input id="email" name="email" type="email" placeholder="Enter your email" required>
    <textarea id="party_message" name="party_message" placeholder="Tell us about your party needs" required></textarea>

    <button type="submit" class="Party_contact_form_btn_primary Party_contact_form_w100">
        Send Message
    </button>
</form>

<style>
 .date-input {
    width: 100%;
    padding: 14px;
    background: transparent;
    border: 1px solid #0ed6d0;

    color: #fff;
    -webkit-text-fill-color: #fff;

    appearance: none;
    -webkit-appearance: none;

    position: relative;
    z-index: 2;
}

/* Android date text */
.date-input::-webkit-datetime-edit {
    color: #fff;
    -webkit-text-fill-color: #fff;
}

.date-input::-webkit-datetime-edit-text,
.date-input::-webkit-datetime-edit-day-field,
.date-input::-webkit-datetime-edit-month-field,
.date-input::-webkit-datetime-edit-year-field {
    color: #fff;
}

/* Calendar icon */
.date-input::-webkit-calendar-picker-indicator {
    filter: invert(1);
    opacity: 1;
}

/* 🔥 FORCE REPAINT AFTER SELECT */
.date-input:focus,
.date-input:valid {
    color: #fff;
    -webkit-text-fill-color: #fff;
}


</style>

<!-- POPUP -->
<div id="popupMessage" style="
    display:none; 
    position:fixed; 
    top:20px; 
    right:20px; 
    background:#222; 
    padding:15px 20px; 
    color:#fff; 
    border-radius:5px; 
    z-index:9999;">
</div>
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
<div id="errorPopup" style="
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.55);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 99999;
">
    <div style="
        background: #fff;
        padding: 25px;
        width: 350px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 0 20px rgba(0,0,0,0.2);
        
    ">
        <h3 style="margin-bottom: 10px; color:#d32f2f;">Error</h3>
        <p id="errorPopupMsg" style="font-size: 15px; margin-bottom: 20px; color:#333;"></p>
        <button onclick="closeErrorPopup()" style="
            padding: 10px 25px;
            background: #d32f2f;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        ">Close</button>
    </div>
</div>
<!-- === Video banner Modal ====== -->
<div class="modal fade blur-modal" id="videobanner" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <!--<h5 class="modal-title" id="videoModalLabel"><?=htmlspecialchars($videoModalTitle)?></h5>-->
                <h5 class="modal-title" id="videoModalLabel"><?=htmlspecialchars($videoData['title'])?></h5>
                <button type="button" class="btn btn-sm close-btn" data-bs-dismiss="modal" aria-label="Close"
                    onclick="stopLocalVideo()">X</button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <!-- muted -->
                    <video id="localVideo" controls>
                        <source src="admin/<?= htmlspecialchars($page['party_video']) ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('change', function(e) {
    if (e.target.matches('input[type="radio"][name^="lift-time-"]')) {

        // --- 1. Disable all Continue buttons ---
        document.querySelectorAll('.continueBtn').forEach(btn => {
            btn.classList.add('disabled');
            btn.setAttribute('disabled', true);
        });

        // --- 2. Remove "slot-selected" class from all slot labels ---
        document.querySelectorAll('.time_slot_group label').forEach(lbl => {
            lbl.classList.remove('slot-selected');
        });

        // --- 3. Enable only the related Continue button ---
        const name = e.target.name; // e.g. "lift-time-1234"
        const productId = name.replace('lift-time-', '');
        const btn = document.getElementById('continueBtn-' + productId);

        if (btn) {
            btn.classList.remove('disabled');
            btn.removeAttribute('disabled');
        }

        // --- 4. Highlight the selected slot label ---
        const label = e.target.closest('.time_slot_group')?.querySelector('label');
        if (label) {
            label.classList.add('slot-selected');
        }
    }
});
</script>



<script src="https://cdn.jsdelivr.net/npm/luxon@3/build/global/luxon.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- date picker script -->
<script>
const {
    DateTime
} = luxon;

// Get current date/time in America/Los_Angeles timezone
const laNow = DateTime.now().setZone("America/Los_Angeles");

// Format to YYYY-MM-DD for flatpickr
const laDate = laNow.toFormat("yyyy-MM-dd");

flatpickr("#pickDateBtn", {
    dateFormat: "D, F j, Y",
    defaultDate: laDate,
    minDate: laDate, // also respect LA time for minimum
    prevArrow: "←",
    nextArrow: "→",
    disableMobile: true
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // --- CONFIG & LIBS ---
    const { DateTime } = luxon;
    const LA_ZONE = "America/Los_Angeles";
    const today = DateTime.now().setZone(LA_ZONE).startOf('day');
    const fmtDisplay = dt => dt.toFormat("ccc, LLLL dd, yyyy");

    // --- REQUEST QUEUE / DEBOUNCE (to avoid Bookeo 429) ---
    let activeRequests = 0;
    let fetchTimers = {}; // per product/date debounce
    let maxParallel = 3;
    let runningRequests = 0;
    let requestQueue = [];
    let loadedTabs = {}; // cache tab html (optional)

    function showLoader() {
        $("#globalLoader").fadeIn(160);
    }

    function hideLoader() {
        if (activeRequests <= 0) $("#globalLoader").fadeOut(160);
    }

    function trackRequestStart() {
        activeRequests++;
        showLoader();
    }

    function trackRequestEnd() {
        activeRequests = Math.max(0, activeRequests - 1);
        if (activeRequests === 0) hideLoader();
    }

    function queueRequest(fn) {
        requestQueue.push(fn);
        processQueue();
    }

    function processQueue() {
        if (runningRequests >= maxParallel || requestQueue.length === 0) return;
        runningRequests++;
        const fn = requestQueue.shift();
        fn().always(() => {
            runningRequests--;
            processQueue();
        });
    }

    // ---------- Utility: safe extraction of productIds ----------
    function extractProductIdsFromCollection($collection) {
        // Read attr('data-product') explicitly, skip flatpickr-mobile and invalid values
        const ids = $collection.map(function() {
            const $el = $(this);
            if ($el.hasClass('flatpickr-mobile')) return null;
            // prefer attr to avoid jQuery caching quirks
            const raw = $el.attr('data-product');
            if (!raw || typeof raw !== 'string') return null;
            const trimmed = raw.trim();
            // Very small sanity check (IDs in your examples are long hex-like strings)
            if (trimmed === '' || trimmed.length < 4) return null;
            // Avoid accidental class-name or input-name being captured
            if (trimmed.toLowerCase().includes('custom-datepicker_input')) return null;
            return trimmed;
        }).get().filter(Boolean);
        return ids;
    }

    // --- SLOT FETCHING FOR MULTIPLE PRODUCTS ---
    function fetchSlotsForProducts(productIds, rawDate, allowAutoNextDay = true) {
        // Defensive sanitize of productIds (always ensure array of non-empty strings)
        productIds = (productIds || []).map(p => (typeof p === 'string' ? p.trim() : '')).filter(Boolean);

        if (!productIds.length || !rawDate) {
            console.log(`fetchSlotsForProducts: Invalid input, productIds=${JSON.stringify(productIds)}, rawDate=${rawDate}`);
            return;
        }

        const ajaxFn = () => {
            const deferred = $.Deferred();
            trackRequestStart();

            // Show loading for each product's container
            productIds.forEach(id => {
                const $container = $('#timeSlots-' + id);
                if ($container.length) $container.html('<div class="time_slots_loader">Loading slots...</div>');
            });

            console.log('fetchSlotsForProducts: AJAX calling fetch_slots.php', { productIds, rawDate });

            $.ajax({
                url: 'fetch_slots.php',
                type: 'GET',
                data: {
                    date: rawDate,
                    productIds: JSON.stringify(productIds)
                },
                dataType: 'json',
                success: function(response) {
                    if (typeof response !== 'object' || response === null) {
                        console.log('fetchSlotsForProducts: Invalid response', response);
                        productIds.forEach(id => {
                            const $container = $('#timeSlots-' + id);
                            if ($container.length) $container.html('<p>No slots available</p>');
                        });
                        return;
                    }

                    productIds.forEach(productId => {
                        const res = response[productId];

                        // support two response shapes:
                        // 1) legacy: response[productId] === "<html...>"
                        // 2) new:    response[productId] === { html: "<html...>", date: "YYYY-MM-DD" }
                        let html, returnedDate;
                        if (res && typeof res === 'object' && res.hasOwnProperty('html')) {
                            html = res.html;
                            returnedDate = res.date || rawDate;
                        } else {
                            html = res || '<p>No slots available</p>';
                            returnedDate = rawDate;
                        }

                        const $container = $('#timeSlots-' + productId);
                        if ($container.length) $container.html(html);

                        // If server explicitly returned a different date (auto-switched),
                        // update the datepicker input to reflect this (no re-fetch because slots already returned)
                        if (returnedDate && returnedDate !== rawDate) {
                            const $input = $(`.custom-datepicker_input[data-product="${productId}"]`);
                            if ($input.length) {
                                const nextDay = DateTime.fromISO(returnedDate, { zone: LA_ZONE });
                                $input.data('rawdate', returnedDate);
                                $input.val(fmtDisplay(nextDay));
                                console.log(`fetchSlotsForProducts: Server switched ${productId} from ${rawDate} -> ${returnedDate}, updated input.`);
                            }
                            // Since server already returned next-date slots, don't re-fetch for this product.
                            return;
                        }

                        // Backwards-compatible fallback:
                        // If HTML contains "No slots available" and auto-next-day recursion allowed, attempt next day client-side.
                        if (html.includes("No slots available") && allowAutoNextDay) {
                            const $input = $(`.custom-datepicker_input[data-product="${productId}"]`);
                            if ($input.length) {
                                const nextDay = DateTime.fromISO(rawDate, { zone: LA_ZONE }).plus({ days: 1 });
                                if (nextDay.diff(today, 'days').days <= 7) {
                                    const nextRaw = nextDay.toFormat('yyyy-MM-dd');
                                    $input.data('rawdate', nextRaw);
                                    $input.val(fmtDisplay(nextDay));
                                    console.log(`fetchSlotsForProducts: No slots for ${productId} on ${rawDate}, trying next day ${nextRaw}`);
                                    // Recurse for this specific product (old behavior)
                                    fetchSlotsForProducts([productId], nextRaw, false);
                                }
                            }
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.log(`fetchSlotsForProducts: Error for date=${rawDate}, error=${error}`);
                    productIds.forEach(id => {
                        const $container = $('#timeSlots-' + id);
                        if ($container.length) $container.html('<p style="color:red;">Error loading slots</p>');
                    });
                },
                complete: function() {
                    trackRequestEnd();
                    deferred.resolve();
                }
            });

            return deferred.promise();
        };

        queueRequest(ajaxFn);
    }

    function updatePrevButtons() {
        let anyFuture = false;
        $('.custom-datepicker_input').each(function() {
            const $input = $(this);
            const raw = $input.data('rawdate');
            const current = raw && DateTime.fromISO(raw, { zone: LA_ZONE }).isValid ?
                DateTime.fromISO(raw, { zone: LA_ZONE }).startOf('day') :
                today;
            const diffDays = current.diff(today, 'days').days;
            console.log(`updatePrevButtons: Checking datepicker: product=${$input.attr('data-product') || $input.data('product')}, rawdate=${raw}, current=${current.toISODate()}, today=${today.toISODate()}, diffDays=${diffDays}`);
            if (diffDays > 0) anyFuture = true;
        });
        console.log(`updatePrevButtons: anyFuture=${anyFuture}`);
        const $prevAllBtn = $("#prev-all-btn");
        if (anyFuture) {
            $prevAllBtn.prop("disabled", false)
                .removeClass("disabled disabled-btn");
            console.log("updatePrevButtons: Enabling #prev-all-btn, removing disabled and disabled-btn classes");
        } else {
            $prevAllBtn.prop("disabled", true)
                .addClass("disabled disabled-btn");
            console.log("updatePrevButtons: Disabling #prev-all-btn, adding disabled and disabled-btn classes");
        }
        $(".prev-date").prop("disabled", !anyFuture)
            .css("visibility", anyFuture ? "visible" : "hidden");
    }

    function setDateAll(dt) {
        const normalized = dt.setZone(LA_ZONE).startOf('day');
        const rawDate = normalized.toFormat('yyyy-MM-dd');
        console.log(`setDateAll: Setting date to ${rawDate}`);
        $('.custom-datepicker_input').each(function() {
            const $input = $(this);
            // skip flatpickr-mobile if somehow present
            if ($input.hasClass('flatpickr-mobile')) return;
            $input.data('rawdate', rawDate);
            $input.val(fmtDisplay(normalized));
            console.log(`setDateAll: Updated datepicker: product=${$input.attr('data-product') || $input.data('product')}, rawdate=${$input.data('rawdate')}`);
        });
        // Fetch for all products after setting date
        const productIds = extractProductIdsFromCollection($('.custom-datepicker_input'));
        console.log(`setDateAll: Fetching slots for productIds: ${JSON.stringify(productIds)}`);
        fetchSlotsForProducts(productIds, rawDate);
        updatePrevButtons();
    }

    function loadTabContent($tab) {
        const label = $.trim($tab.text());
        const file = label.toLowerCase().replace(/\s+/g, '-') + '.php';
        const target = $tab.data('bs-target');

        if (loadedTabs[target]) {
            $(target).html(loadedTabs[target]);
            initTabContent($(target));
            return;
        }

        $(target).html('<p>Loading...</p>');
        $.get(file, function(data) {
            $(target).html(data);
            loadedTabs[target] = data;
            initTabContent($(target));
        });
    }

    // --- Datepickers + init (for tab content) ---
  function initDatePickers(container = $(document)) {
    container.find('.custom-datepicker_input').each(function(index) {
        const $input = $(this);

        // Skip any flatpickr-mobile duplicates
        if ($input.hasClass('flatpickr-mobile')) return;

        const productId = $input.attr('data-product') || $input.data('product');
        if (!productId) return;

        // Destroy any previous flatpickr instance
        if ($input[0]._flatpickr) $input[0]._flatpickr.destroy();

        const initialDate = today.toFormat('yyyy-MM-dd');
        $input.data('rawdate', initialDate);
        $input.val(fmtDisplay(today));

        flatpickr($input[0], {
            dateFormat: "Y-m-d",
            defaultDate: initialDate,
            minDate: today.toFormat('yyyy-MM-dd'),
            allowInput: false,
            clickOpens: true,
            disableMobile: true, // force desktop style
            monthSelectorType: "dropdown",

            onReady: (selectedDates, dateStr, instance) => {
                const calendar = instance.calendarContainer;

                // --- Hide default year input safely ---
                const yearInput = calendar.querySelector(".numInputWrapper");
                if (yearInput) {
                    yearInput.style.height = "0px";
                    yearInput.style.overflow = "hidden";
                    yearInput.style.position = "absolute"; // remove from flow
                }

                // --- Create custom year dropdown ---
                if (!calendar.querySelector(".flatpickr-year-dropdown")) {
                    const yearSelect = document.createElement("select");
                    yearSelect.className = "flatpickr-year-dropdown flatpickr-monthDropdown-months";

                    const currentYear = new Date().getFullYear();
                    const maxYear = currentYear + 3;
                    for (let i = currentYear; i <= maxYear; i++) {
                        const option = document.createElement("option");
                        option.value = i;
                        option.text = i;
                        yearSelect.appendChild(option);
                    }

                    yearSelect.value = instance.currentYear || currentYear;

                    yearSelect.addEventListener("change", function(e) {
                        instance.changeYear(parseInt(e.target.value));
                    });

                    // Append next to month dropdown
                    const monthContainer = calendar.querySelector(".flatpickr-current-month");
                    if (monthContainer) {
                        const wrapper = document.createElement("span");
                        wrapper.style.display = "inline-block";
                        wrapper.style.marginLeft = "5px";
                        wrapper.appendChild(yearSelect);
                        monthContainer.appendChild(wrapper);
                    }

                    // Ensure visibility and style similar to month dropdown
                    yearSelect.style.display = "inline-block";
                    yearSelect.style.minWidth = "60px"; // adjust as needed
                    yearSelect.style.background = window.getComputedStyle(calendar.querySelector(".flatpickr-monthDropdown-months")).background;
                    yearSelect.style.color = window.getComputedStyle(calendar.querySelector(".flatpickr-monthDropdown-months")).color;
                    yearSelect.style.border = window.getComputedStyle(calendar.querySelector(".flatpickr-monthDropdown-months")).border;
                    yearSelect.style.borderRadius = window.getComputedStyle(calendar.querySelector(".flatpickr-monthDropdown-months")).borderRadius;
                    yearSelect.style.padding = window.getComputedStyle(calendar.querySelector(".flatpickr-monthDropdown-months")).padding;
                }

                // Remove any flatpickr-mobile duplicates
                document.querySelectorAll('.flatpickr-mobile').forEach(inp => inp.remove());

                // Ensure input visible
                $input.attr('type', 'text')
                      .removeClass('d-none')
                      .css({ display: 'block', visibility: 'visible', opacity: '1' });

                $input.val(fmtDisplay(today));
                console.log(`initDatePickers: Datepicker initialized: product=${productId}, rawdate=${initialDate}`);
            },

            // Sync year dropdown when year changes via arrows
            onYearChange: (selectedDates, dateStr, instance) => {
                const yearSelect = instance.calendarContainer.querySelector(".flatpickr-year-dropdown");
                if (yearSelect) yearSelect.value = instance.currentYear || new Date().getFullYear();
            },

            // Update selected date
            onChange: (selectedDates) => {
                if (!selectedDates || !selectedDates[0]) return;
                const jsDate = selectedDates[0];
                const picked = DateTime.fromObject({
                    year: jsDate.getFullYear(),
                    month: jsDate.getMonth() + 1,
                    day: jsDate.getDate()
                }, { zone: LA_ZONE }).startOf('day');
                setDateAll(picked);
            }
        });
    });

    // Fetch slots for all products in this container
    const productIds = extractProductIdsFromCollection(container.find('.custom-datepicker_input'));
    fetchSlotsForProducts(productIds, today.toFormat('yyyy-MM-dd'));

    updatePrevButtons();
}


    // --- GUEST / CONTINUE / TIMER LOGIC ---
    let guestCounts = {}; // productCode => count
    let slotAvailableSeats = {}; // productCode => seats available
    let timers = {}; // productCode => intervalId
    let wasGuestZero = {}; // productCode => boolean

    function getProductPrices(productCode) {
        const priceEl = document.querySelector(`#price-${productCode}`);
        if (!priceEl) return { min: 0, max: 0 };
        const text = priceEl.textContent;
        const matches = text.match(/\d+(?:\.\d+)?/g) || [];
        const uniquePrices = [...new Set(matches.map(Number))].sort((a, b) => a - b);
        return { min: uniquePrices[0] || 0, max: uniquePrices[uniquePrices.length - 1] || 0 };
    }

    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${minutes < 10 ? '0' : ''}${minutes}:${secs < 10 ? '0' : ''}${secs}`;
    }

    function startTimerForProduct(productCode) {
        clearInterval(timers[productCode]);
        let totalSeconds = 600;
        const timerWrapper = document.getElementById("timer-wrapper-" + productCode);
        if (!timerWrapper) return;
        const timerEls = timerWrapper.querySelectorAll('.timer_display');
        timerEls.forEach(el => el.innerText = formatTime(totalSeconds));
        timers[productCode] = setInterval(() => {
            totalSeconds--;
            timerEls.forEach(el => el.innerText = formatTime(totalSeconds));
            if (totalSeconds <= 0) {
                clearInterval(timers[productCode]);
            }
        }, 1000);
    }

    function stopTimerForProduct(productCode) {
        clearInterval(timers[productCode]);
        const timerWrapper = document.getElementById("timer-wrapper-" + productCode);
        if (timerWrapper) timerWrapper.style.display = "none";
    }

    function disableAllContinueButtons() {
        document.querySelectorAll(".continue_nex_step").forEach(btn => {
            btn.classList.add("disabled");
            btn.setAttribute("disabled", "true");
            btn.removeAttribute("data-bs-toggle");
            btn.removeAttribute("data-bs-target");
        });
    }

    function getPricePerGuest(productCode, guestCount) {
        const { min, max } = getProductPrices(productCode);
        if (min === max) return min;
        return guestCount === 2 ? max : min;
    }

    function updateContinueStateForProduct(productCode) {
        const btn = document.querySelector(`.continue_nex_step[data-game-id="${productCode}"]`);
        const priceEl = document.getElementById("total-price-" + productCode);
        const timerWrapper = document.getElementById("timer-wrapper-" + productCode);
        const timerEls = timerWrapper ? timerWrapper.querySelectorAll('.timer_display') : [];
        const timeSelected = !!document.querySelector(`input[name="lift-time-${productCode}"]:checked`);
        const count = guestCounts[productCode] || 0;
        const pricePerGuest = getPricePerGuest(productCode, count);
        const isEnabled = timeSelected && count > 0;

        if (btn) {
            if (isEnabled) {
                disableAllContinueButtons();
                btn.classList.remove("disabled");
                btn.removeAttribute("disabled");
            } else {
                btn.classList.add("disabled");
                btn.setAttribute("disabled", "true");
                btn.removeAttribute("data-bs-toggle");
                btn.removeAttribute("data-bs-target");
            }
        }

        if (priceEl) {
            priceEl.textContent = (count * pricePerGuest).toFixed(2);
        }

        if (timerWrapper) {
            if (count > 0) {
                timerWrapper.style.display = "block";
                if (!wasGuestZero[productCode]) {
                    // timer already started
                } else {
                    startTimerForProduct(productCode);
                    wasGuestZero[productCode] = false;
                }
            } else {
                stopTimerForProduct(productCode);
                wasGuestZero[productCode] = true;
            }
        }
    }

    function initGuestStateInContainer(container = $(document)) {
        container.find('.guest-count-wrapper').each(function() {
            const wrapper = this;
            const productCode = wrapper.querySelector(".guest-value").id.replace("guest-count-", "");
            guestCounts[productCode] = 0;
            wasGuestZero[productCode] = true;
            slotAvailableSeats[productCode] = slotAvailableSeats[productCode] || 0;
            const guestValueEl = wrapper.querySelector(".guest-value");
            if (guestValueEl) guestValueEl.textContent = 0;
            const priceEl = document.getElementById("total-price-" + productCode);
            if (priceEl) priceEl.textContent = "0";
        });

        container.find(".time_slot_group.time_slot_full input[type='radio']").each(function() {
            this.disabled = true;
        });
    }

    // --- DELEGATED EVENT HANDLERS ---
    $(document).on('click', '.guest-count-wrapper .plus-btn', function(e) {
        const wrapper = this.closest('.guest-count-wrapper');
        if (!wrapper) return;
        const productCode = wrapper.querySelector(".guest-value").id.replace("guest-count-", "");
        const guestCountEl = wrapper.querySelector(".guest-value");
        const maxSeats = slotAvailableSeats[productCode] || 99;

        if (maxSeats <= 0) return;

        if (!guestCounts[productCode]) guestCounts[productCode] = 0;

        if (guestCounts[productCode] === 0) {
            guestCounts[productCode] = Math.min(2, maxSeats);
        } else if (guestCounts[productCode] < maxSeats) {
            guestCounts[productCode]++;
        }
        guestCountEl.textContent = guestCounts[productCode];

        updateContinueStateForProduct(productCode);
    });

    $(document).on('click', '.guest-count-wrapper .minus-btn', function(e) {
        const wrapper = this.closest('.guest-count-wrapper');
        if (!wrapper) return;
        const productCode = wrapper.querySelector(".guest-value").id.replace("guest-count-", "");
        const guestCountEl = wrapper.querySelector(".guest-value");

        if (!guestCounts[productCode]) guestCounts[productCode] = 0;

        if (guestCounts[productCode] > 2) {
            guestCounts[productCode]--;
        } else if (guestCounts[productCode] === 2) {
            guestCounts[productCode] = 0;
        }
        guestCountEl.textContent = guestCounts[productCode];

        updateContinueStateForProduct(productCode);
    });

    $(document).on('change', ".time_slot_group input[type='radio'][name^='lift-time-']", function(e) {
        const current = this;
        if (!current.checked) return;

        const name = current.name;
        document.querySelectorAll(`input[name="${name}"]`).forEach(inp => {
            if (inp !== current && inp.checked) {
                inp.checked = false;
                $(inp).trigger('change');
            }
        });

        for (const product in guestCounts) {
            guestCounts[product] = 0;
            const guestEl = document.getElementById("guest-count-" + product);
            if (guestEl) guestEl.textContent = 0;
            updateContinueStateForProduct(product);
        }

        const productCode = current.name.replace("lift-time-", "");
        const available = parseInt(current.getAttribute("data-available"), 10) || 0;
        slotAvailableSeats[productCode] = available;
    });

    $(document).on('click', '.continue_nex_step', function(e) {
        const btn = this;
        if (btn.classList.contains('disabled')) {
            e.preventDefault();
            return;
        }
    });

    function initContinueButtonsInContainer(container = $(document)) {
        container.find('.continue_nex_step').each(function() {
            const btn = this;
            const productCode = btn.getAttribute('data-game-id');
            updateContinueStateForProduct(productCode);
        });
    }

    function initTabContent($container) {
        initDatePickers($container);
        initGuestStateInContainer($container);
        initContinueButtonsInContainer($container);
    }

    $(".tab-item").on("click", function() {
        loadTabContent($(this));
    });

    const $defaultTab = $(".tab-item.active");
    if ($defaultTab.length) loadTabContent($defaultTab);

    $(document).on("click", ".prev-date, #prev-all-btn", function(e) {
        e.preventDefault();
        const first = $('.custom-datepicker_input').first();
        if (!first.length) {
            console.log('Prev button clicked: No datepickers found');
            return;
        }
        const raw = first.data('rawdate') || today.toFormat('yyyy-MM-dd');
        const current = DateTime.fromISO(raw, { zone: LA_ZONE }).isValid ?
            DateTime.fromISO(raw, { zone: LA_ZONE }).startOf('day') :
            today;
        const diffDays = current.diff(today, 'days').days;
        console.log(`Prev button clicked: rawdate=${raw}, current=${current.toISODate()}, today=${today.toISODate()}, diffDays=${diffDays}`);
        if (diffDays > 0) {
            const newDate = current.minus({ days: 1 });
            console.log(`Prev button: Decreasing date to ${newDate.toISODate()}`);
            setDateAll(newDate);
        } else {
            console.log('Prev button: Cannot go before today');
        }
    });

    $(document).on("click", ".next-date, #next-all-btn", function(e) {
        e.preventDefault();
        const first = $('.custom-datepicker_input').first();
        if (!first.length) {
            console.log('Next button clicked: No datepickers found');
            return;
        }
        const raw = first.data('rawdate') || today.toFormat('yyyy-MM-dd');
        const current = DateTime.fromISO(raw, { zone: LA_ZONE }).isValid ?
            DateTime.fromISO(raw, { zone: LA_ZONE }).startOf('day') :
            today;
        const newDate = current.plus({ days: 1 });
        console.log(`Next button clicked: rawdate=${raw}, current=${current.toISODate()}, setting to ${newDate.toISODate()}`);
        setDateAll(newDate);
    });

    // --- GLOBAL PICK DATE BUTTON FUNCTIONALITY ---
    initTabContent($(document));

    // --- FIX: Remove Flatpickr duplicate input (keep only first one) ---
    setTimeout(() => {
      const duplicateInputs = document.querySelectorAll('.flatpickr-mobile');
      duplicateInputs.forEach(input => input.remove());
      const mainInputs = document.querySelectorAll('.custom-datepicker_input');
      mainInputs.forEach(input => {
        input.classList.remove('d-none');
        input.style.display = 'block';
        input.style.visibility = 'visible';
        input.style.opacity = '1';
        input.type = 'text';
      });
      console.log("✅ Removed Flatpickr duplicate inputs — only main input kept.");
    }, 1200);
});
</script>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {

    $("#partyForm").on("submit", function (e) {
        e.preventDefault();

        // --- Validation ---
        let firstName = $("#first_name").val().trim();
        let lastName = $("#last_name").val().trim();
        let guests = $("#guests").val().trim();
        let duration = $("#duration").val().trim();
        let phone = $("#phone").val().trim();
        let eventDate = $("#event_date").val().trim();
        let email = $("#email").val().trim();
        let message = $("#party_message").val().trim();

        if (!firstName || !lastName || !guests || !duration || !phone || !eventDate || !email) {
            showPopup("Please fill all required fields!", "red");
            return;
        }

        // Email format check
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            showPopup("Invalid email address!", "red");
            return;
        }

        // Phone basic check
        if (phone.length < 8) {
            showPopup("Invalid phone number!", "red");
            return;
        }

        // --- AJAX ---
        $.ajax({
            url: "send_facility_mail.php",
            type: "POST",
            data: {
                first_name: firstName,
                last_name: lastName,
                guests: guests,
                duration: duration,
                phone: phone,
                event_date: eventDate,
                email: email,
                party_message: message
            },
            success: function (response) {
                if (response.status === "success") {
                    showPopup(response.message, "green");
                    $("#partyForm")[0].reset();
                } else {
                    showPopup(response.message, "red");
                }
            },
            error: function () {
                showPopup("Something went wrong. Try again!", "red");
            }
        });
    });

    // Popup function
    function showPopup(msg, color) {
        let popup = $("#popupMessage");
        popup.css("background", color).html(msg).fadeIn(300);

        setTimeout(() => { popup.fadeOut(300); }, 3000);
    }

});
</script>
<script>
function showErrorPopup(msg) {
    document.getElementById("errorPopupMsg").innerHTML = msg;
    document.getElementById("errorPopup").style.display = "flex";
}
function closeErrorPopup() {
    document.getElementById("errorPopup").style.display = "none";
}
</script>
<script>
document.addEventListener("click", function(e) {
    const btn1 = e.target.closest(".continue_next_step_event");
    if (!btn1 || btn1.disabled) return;

    // Prevent double click
    if (btn1.dataset.processing === "true") return;
    btn1.dataset.processing = "true";
    btn1.disabled = true;

    const productCode = btn1.getAttribute("data-game-id");
    const gameName = btn1.getAttribute("data-game-name");
    const players = document.getElementById(`players-${productCode}`).value;
    const totalPrice = document.getElementById(`price-${productCode}`).value;
    const selectedSlot = document.querySelector(
        `#timeSlots-${productCode} input[name="lift-time-${productCode}"]:checked`
    );

    let slot = "No slot";
    let eventId = "";
    if (selectedSlot) {
        slot = selectedSlot.value;
        eventId = selectedSlot.getAttribute("data-eventid");
    }

    fetch("event_cart_session_new.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            action: "add_party_cart",
            gameId: productCode,
            gameName: gameName,
            slot: slot,
            eventId: eventId,
            players: players,
            price: totalPrice
        })
    })
    .then(res => res.json())
    .then(response => {
        console.log("API Response:", response);

        if (response.status === "bookeo_error") {
            // Show exact Bookeo error in modal
            document.getElementById("bookeoErrorMessage").textContent = 
                response.message || "This time slot is no longer available.";
            const modal = new bootstrap.Modal(document.getElementById("bookeoErrorModal"));
            modal.show();
            return;
        }

        if (response.status === "success") {
            window.location.href = "<?= BASE_URL ?>booking?customer-details";
        }
    })
    .catch(err => {
        console.error("Fetch error:", err);
        document.getElementById("bookeoErrorMessage").textContent = "Network error. Please try again.";
        const modal = new bootstrap.Modal(document.getElementById("bookeoErrorModal"));
        modal.show();
    })
    .finally(() => {
        btn1.dataset.processing = "false";
        btn1.disabled = false;
    });
});
</script>



<?php include('includes/footer.php'); ?>
<script>
    flatpickr("#event_date", {
  dateFormat: "d-m-Y",
  disableMobile: true
});

</script>