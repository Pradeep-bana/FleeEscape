<?php session_start();
include ('link.php');
include ('admin/db.php');
// Fetch promotion page details
$stmt = $pdo->query("SELECT * FROM tbl_promotion_page LIMIT 1");
$data = $stmt->fetch(PDO::FETCH_ASSOC);
// Set dynamic meta values (with fallbacks)
$pageTitle = !empty($data['page_title']) ? $data['page_title'] : 'Promotions';
$metaKeywords = !empty($data['meta_keywords']) ? $data['meta_keywords'] : '';
$metaDescription = !empty($data['meta_description']) ? $data['meta_description'] : '';
$canonicalURL = $link . "promotions";
include ('includes/header.php');
?>
<style>
    .booking_tab_content{
         justify-content: center;   
    }

#globalLoader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgb(0 0 0 / 64%);
    z-index: 9999;
    display: none;
    /* hidden by default */
    display: flex;
    /* flexbox for centering */
    align-items: center;
    /* vertical center */
    justify-content: center;
    /* horizontal center */
}

#globalLoader .loader-content {
    display: flex;
    flex-direction: column;
    /* stack circle and text vertically */
    align-items: center;
    /* center horizontally */
    text-align: center;
}

/* centered circle */
#globalLoader .loader-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 6px solid #f3f3f3;
    border-top: 6px solid #3498db;
    animation: spin 1s linear infinite;
    margin-bottom: 12px;
    /* space between circle and text */
}

#globalLoader p {
    color: #fff;
    font-size: 16px;
    font-weight: 500;
    margin: 0;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}

.disabled-btn {
    opacity: 0.6;
    pointer-events: none;
}
</style>
<?php
// Fetch data from DB
$stmt = $pdo->query("SELECT * FROM tbl_promotion_page LIMIT 1");
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<section>
    <div class="vr_page_banner all_baneer_IMG"
        style="background-image: url('admin/<?=!empty($data['cover_image']) ? htmlspecialchars($data['cover_image']) : 'img/3Escape_Room.jpg' ?>'); height:450px">
        <div class="container">
            <div class="vr_page_banner_content" style="position: relative; z-index: 1;">
                
                <?php if (!empty($data['main_heading'])): ?>
                    <h1><?=htmlspecialchars($data['main_heading']) ?></h1>
                <?php
endif; ?>

                <?php if (!empty($data['sub_heading'])): ?>
                    <p><?=nl2br(htmlspecialchars($data['sub_heading'])) ?></p>
                <?php
endif; ?>

                <?php if (!empty($data['bottom_text'])): ?>
                    <small>
                        <i class="fa-solid fa-globe"></i>
                        <?=htmlspecialchars($data['bottom_text']) ?>
                    </small>
                <?php
endif; ?>

                <?php if (!empty($data['link'])): ?>
                    <div style="margin-top: 15px;">
                        <a href="<?=htmlspecialchars($data['link']) ?>" class="btn btn-primary">Learn More</a>
                    </div>
                <?php
endif; ?>

            </div>
        </div>
    </div>
</section>

<?php
// Fetch Flash Deal data
$flashQuery = $pdo->prepare("SELECT * FROM tbl_flash_deal WHERE is_active = 1 LIMIT 1");
$flashQuery->execute();
$flashDeal = $flashQuery->fetch(PDO::FETCH_ASSOC);
?>
<?php if ($flashDeal):
    $per = $flashDeal["discount_percent"];
?>  <!-- Section will show only if is_active = 1 -->
<section class="Last-Minute_Deals view_delete_last">
    <div class="container">
     <div class="section_heading_page">
    <?php if (!empty($data['escape_small_heading'])): ?>
        <span class="badge-custom">
            <i class="fa-solid fa-bolt"></i>
            <?=htmlspecialchars($data['escape_small_heading']) ?>
        </span>
    <?php
    endif; ?>

    <?php if (!empty($data['escape_main_heading'])): ?>
        <h2 class="section-title"><?=htmlspecialchars($data['escape_main_heading']) ?></h2>
    <?php
    endif; ?>

    <?php if (!empty($data['escape_content'])): ?>
        <p class="section-subtitle"><?=nl2br(htmlspecialchars($data['escape_content'])) ?></p>
    <?php
    endif; ?>
</div>
        <div class="row booking_tab_content">
<?php
    // Enable PHP error reporting
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    include ('link.php');
    include ("admin/db.php");

    // --- DATA FETCHING (DATABASE ONLY) ---
    $data = null;

    try {
        $stmt = $pdo->prepare("SELECT product_data FROM bookeo_products_cache WHERE id = 1 LIMIT 1");
        $stmt->execute();
        $cacheRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cacheRow && !empty($cacheRow['product_data'])) {
            $decodedData = json_decode($cacheRow['product_data'], true);
            if ($decodedData && isset($decodedData['data'])) {
                $data = $decodedData;
            }
        }
    } catch (Exception $e) {
        error_log("Database error on promotions.php (Block 1): " . $e->getMessage());
    }

    // Fail safe
    if (!isset($data['data']) || !is_array($data['data'])) {
        $data = ['data' => []];
    }

    // Collect product IDs (First 6 products for Flash Deals)
    $productIds = [];
    $count = 0;
    foreach ($data['data'] as $product) {
        if ($count >= 6) break;
        $productIds[] = htmlspecialchars($product['productCode'] ?? '');
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
        if ($count >= 6) break;
        $name = htmlspecialchars($product['name']??'');
        $productCode = htmlspecialchars($product['productCode']??'');
        $duration = isset($product['duration']) ? $product['duration'] : ['hours' => 0, 'minutes' => 0];
        $desc = trim($product['description']??'');
        // Split lines
        $lines = preg_split('/\r\n|\r|\n/', $desc);
        $players = isset($lines[0]) ? preg_replace('/^.*?:\s*/', '', trim(strip_tags($lines[0]))) : '';
        $difficulty = isset($lines[1]) ? trim($lines[1]) : '';
        $successRate = isset($lines[2]) ? trim($lines[2]) : '';
        $price = isset($lines[3]) ? preg_replace('/^.*?:\s*/', '', trim(strip_tags($lines[3]))) : '';
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
        // $price       → original Bookeo price text
        // $discount    → discount percentage (ex: 50, 20, 35)
        $rawPrice = isset($lines[3]) ? preg_replace('/^.*?:\s*/', '', trim(strip_tags($lines[3]))) : '';
        preg_match_all('/\$(\d+(\.\d+)?)/', $rawPrice, $matches);
        $per = floatval($flashDeal["discount_percent"]);
        $discounted = "";
        // If price range is found → multiple matches
        if (!empty($matches[1])) {
            // Convert all extracted prices into floats
            $prices = array_map('floatval', $matches[1]);
            if (count($prices) == 1) {
                // -------------------------
                // SINGLE PRICE CASE
                // -------------------------
                $price = $prices[0];
                if ($price > 0 && $per > 0) {
                    $discounted = number_format($price - ($price * ($per / 100)), 2);
                }
            } else {
                // -------------------------
                // RANGE PRICE CASE → MIN–MAX
                // -------------------------
                $minPrice = min($prices);
                $maxPrice = max($prices);
                // Apply discount to BOTH
                $minDisc = $minPrice - ($minPrice * ($per / 100));
                $maxDisc = $maxPrice - ($maxPrice * ($per / 100));
                $discounted = number_format($minDisc, 2) . " - " . number_format($maxDisc, 2);
            }
        }
        // Combine remaining lines for short description
        $remainingText = implode(" ", array_slice($lines, 4));
        $remainingText = strip_tags($remainingText);
        $words = preg_split('/\s+/', $remainingText);
        $shortDescription = implode(" ", array_slice($words, 0, 13));
        if (count($words) > 13) {
            $shortDescription.= "...";
        }
        // Image handling: keep original behavior if images exist
        $imageUrl = '';
        if (!empty($product['images'][0]['url'])) {
            $imageUrl = $product['images'][0]['url'];
        }
?>
    <div class="col-md-4 col-sm-12">
        <div class="booking_card">
            <div>
            <div class="booking_card_img">
                <?php
        if (!empty($imageUrl)) {
            echo '<img src="' . htmlspecialchars($imageUrl) . '"  alt="' . $name . '" loading="lazy"  decoding="async"   />';
        }
?>
                <div class="booking_card_time_and_price promotions_price_and_pad">
                    <p>
                        <i class="fa-solid fa-clock"></i>
                        <?php
        $hours = isset($duration['hours']) ? (int)$duration['hours'] : 0;
        $minutes = isset($duration['minutes']) ? (int)$duration['minutes'] : 0;
        $totalMinutes = ($hours * 60) + $minutes;
        echo $totalMinutes . " minutes";
?>
                    </p>
                    <p id="price-<?php echo $productCode; ?>" class="d-none"><?php echo $price; ?>/Guest</p>
                     <p >$<?php echo $discounted; ?>/Guest</p>
                    
                    
                     <p id="discounted-<?php echo $productCode; ?>" class="d-none"><?php echo $per; ?></p>
                    <p class="discount-badge_lat_m"><?php echo $per; ?>% OFF</p>
                </div>
                <div class="booking_card_overlay">
                    <h5><?php echo $name; ?></h5>
                    <p><?php echo $shortDescription; ?></p>
                    <div class="player-count-display d-flex align-items-center">
                        <div>
                            <span class="player-label">
                                <img class="palyear_tem_img" src="./assets/images/fleeescape_img/teampay.png"  loading="lazy" decoding="async" alt="">
                            </span>
                            <span class="player-value"><?php echo $players; ?> GUESTS</span>
                        </div>
                        <div class="icon_buttons_wrapper">
                            <div class="icon-button" data-bs-toggle="modal" data-bs-target="#liftInfoModal<?php echo $productCode; ?>">
                                <i class="fa-solid fa-circle-info"></i>
                                <span class="label">Learn more</span>
                            </div>
                            <div class="icon-button" data-bs-toggle="modal" data-bs-target="#videoModal<?php echo $productCode; ?>">
                                <i class="fa-solid fa-circle-play"></i>
                                <span class="label">Watch Trailer</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-3"> 
                <div class="custom-date-wrapper_date">
                    <!--<button class="custom-date_arrow prev-date" style="visibility: hidden;">-->
                    <!--    <i class="fa-solid fa-arrow-left"></i>-->
                    <!--</button>-->
                    <input type="text" class="custom-datepicker_input sec1-datepicker" data-product="<?php echo $productCode; ?>" data-fixed="true">
                    <!--<button class="custom-date_arrow next-date">-->
                    <!--    <i class="fa-solid fa-arrow-right"></i>-->
                    <!--</button>-->
                </div> 

                <div class="time_slots" id="timeSlotsFlesh-<?php echo $productCode; ?>"></div>
                <?php  
if ($productCode == '41551F9C679173BC114D28') { ?>
    <div class="View_All_Dates_tag">
        <a href="<?php echo $link; ?>the-lift-collection#Book_one_single_pr">View All Dates</a>
    </div>

<?php } elseif ($productCode == '41551PFXF3K14F91D8FABB') { ?>
    <div class="View_All_Dates_tag">
        <a href="<?php echo $link; ?>ice-walker-got-collection#Book_one_single_pr">View All Dates</a>
    </div>

<?php } elseif ($productCode == '41551HE99XR14F91DF96B0') { ?>
    <div class="View_All_Dates_tag">
        <a href="<?php echo $link; ?>prison-escape-collection#Book_one_single_pr">View All Dates</a>
    </div>

<?php } elseif ($productCode == '41551XJM6F314F91E1CD68') { ?>
    <div class="View_All_Dates_tag">
        <a href="<?php echo $link; ?>steampunk-submarine-collection#Book_one_single_pr">View All Dates</a>
    </div>

<?php } elseif ($productCode == '41551Y4PM9614F91DD1BC6') { ?>
    <div class="View_All_Dates_tag">
        <a href="<?php echo $link; ?>museum-heist-collection#Book_one_single_pr">View All Dates</a>
    </div>

<?php } elseif ($productCode == '415516JNCHJ14F91D5A806') { ?>
    <div class="View_All_Dates_tag">
        <a href="<?php echo $link; ?>ancient-egypt-collection#Book_one_single_pr">View All Dates</a>
    </div>
<?php } ?>


                <div class="guest_and_button">
                    <h5 class="guest-heading mb-2">Select Guests</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="guest-count-wrapper">
                            <button type="button" class="guest-btn minus-btn">−</button>
                            <span id="guest-count-<?php echo $productCode; ?>" class="guest-value">0</span>
                            <button type="button" class="guest-btn plus-btn">+</button>
                        </div>
                        <p class="m-0">
                            <i class="fa-solid fa-dollar-sign"></i>
                            <span id="total-price-<?php echo $productCode; ?>">0</span>
                        </p>
                    </div>
                   
                </div>
            </div>
            </div>
             <div class="next-button-wrapper">
                        <?php echo '<button 
                            class="continueBtn bg_bnt_custom disabled continue_nex_step" 
                            id="continueBtn-' . $productCode . '" 
                            data-game-id="' . $productCode . '" 
                            data-game-name="' . htmlspecialchars($name) . '" 
                            disabled>Continue</button>'; ?>
                    </div>
        </div>
    </div>
       
   <!-- === Video Modal ====== -->
<?php
        // Make sure $productCode is defined before this block
        if (!empty($productCode)) {
            include ('admin/db.php');
            // Fetch the trailer video safely
            $stmt = $pdo->prepare("SELECT trailer_video FROM tbl_service WHERE product_id = :product_id LIMIT 1");
            $stmt->execute([':product_id' => $productCode]);
            $videoData = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($videoData && !empty($videoData['trailer_video'])) {
                $videoPath = 'admin/uploads/' . $videoData['trailer_video'];
            } else {
                $videoPath = ''; // no video found
                
            }
?>
    <div class="modal fade blur-modal videoModal_z" id="videoModal<?php echo $productCode; ?>" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="videoModalLabel"><?=htmlspecialchars_decode($name)?></h5>
            <button type="button" class="btn btn-sm close-btn" data-bs-dismiss="modal" aria-label="Close"
              onclick="stopLocalVideo()">X</button>
          </div>
          <div class="modal-body p-0">
            <div class="ratio ratio-16x9">
              <?php if (!empty($videoPath)) { ?>
                <video id="localVideo"  controls>
                  <source src="<?php echo htmlspecialchars($videoPath); ?>" type="video/mp4">
                  Your browser does not support the video tag.
                </video>
              <?php
            } else { ?>
                <div class="d-flex align-items-center justify-content-center" style="height:300px;">
                  <p class="text-center text-muted">No trailer video available for this game.</p>
                </div>
              <?php
            } ?>
            </div>
          </div>
        </div>
      </div>
    </div>
<?php
        } else {
            echo "<!-- Error: product_id not defined for modal -->";
        }
?> 
    
    <?php
        if (!empty($productCode)) {
            include ('admin/db.php');
            // Fetch data dynamically from tbl_service
            $stmt = $pdo->prepare("SELECT * FROM tbl_service WHERE product_id = :product_id LIMIT 1");
            $stmt->execute([':product_id' => $productCode]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($data) {
                $title = !empty($data['title']) ? $data['title'] : 'Untitled Game';
                $description1 = !empty($data['blog_detl']) ? $data['blog_detl'] : 'No description available.';
                $description2 = !empty($data['story']) ? $data['story'] : '';
                $label = !empty($data['label']) ? $data['label'] : '';
                $difficulty = !empty($data['difficulty']) ? $data['difficulty'] : 'N/A';
                $duration = !empty($data['duration']) ? $data['duration'] : 'N/A';
                $success_rate = !empty($data['success_rate']) ? $data['success_rate'] . '%' : 'N/A';
                $players = !empty($data['players']) ? $data['players'] : 'N/A';
                $layout = !empty($data['layout']) ? $data['layout'] : 'N/A';
                $price = !empty($data['price']) ? $data['price'] : 'N/A';
                $cover_photo = !empty($data['cover_photo']) ? 'admin/uploads/' . $data['cover_photo'] : './assets/images/fleeescape_img/banner/baneer2.jpg';
                $thumbnail = !empty($data['thumbnail']) ? 'admin/uploads/' . $data['thumbnail'] : './assets/images/fleeescape_img/banner/banne1.jpg';
?>
        <div class="modal fade" id="liftInfoModal<?php echo $productCode; ?>" tabindex="-1" aria-labelledby="liftInfoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content custom-modal">
                    <div class="modal-header border-0" style="align-items: flex-start!important;">
                        <div class="info_modal_content" style="width: 90%;">
                            <h2 class="modal-title custom-heading" id="liftInfoModalLabel">
                                <?php echo htmlspecialchars($title); ?>
                            </h2>
                        </div>
                        <button type="button" class="btn btn-sm close-btn" data-bs-dismiss="modal" aria-label="Close">X</button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-5 text-center mb-3 mb-md-0">
                              <div id="carouselExampleFade<?php echo $productCode; ?>" class="carousel slide carousel-fade custom-carousel" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php
                // Fetch gallery images dynamically
                $galleryStmt = $pdo->prepare("SELECT image FROM tbl_escape_room_gallery WHERE category = :product_id");
                $galleryStmt->execute([':product_id' => $productCode]);
                $galleryImages = $galleryStmt->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($galleryImages)) {
                    $activeSet = false;
                    foreach ($galleryImages as $gallery) {
                        $imgPath = 'admin/uploads/' . htmlspecialchars($gallery['image']);
?>
                                        <div class="carousel-item <?php echo !$activeSet ? 'active' : ''; ?>">
                                            <img src="<?php echo $imgPath; ?>"  loading="lazy" decoding="async" class="d-block w-100" alt="Gallery Image">
                                        </div>
                                        <?php
                        $activeSet = true;
                    }
                } else {
                    // Fallback if no gallery found
                    
?>
                                    <div class="carousel-item active">
                                        <img src="<?php echo htmlspecialchars($cover_photo); ?>"  loading="lazy" decoding="async" class="d-block w-100" alt="Cover Image">
                                    </div>
                                    <div class="carousel-item">
                                        <img src="<?php echo htmlspecialchars($thumbnail); ?>" loading="lazy" decoding="async" class="d-block w-100" alt="Thumbnail">
                                    </div>
                                <?php
                } ?>
                            </div>
                        
                            <!-- Custom Arrows -->
                            <button class="carousel-control-prev custom-arrow" type="button" data-bs-target="#carouselExampleFade<?php echo $productCode; ?>" data-bs-slide="prev">
                                <span class="arrow-content">&#10094;</span>
                            </button>
                            <button class="carousel-control-next custom-arrow" type="button" data-bs-target="#carouselExampleFade<?php echo $productCode; ?>" data-bs-slide="next">
                                <span class="arrow-content">&#10095;</span>
                            </button>
                        </div>



                            </div>

                            <div class="col-md-7">
                                <div class="modal_Beginner_badel">
                                    <p><?php echo htmlspecialchars($label); ?></p>
                                </div>

                                <div class="game-stats d-flex flex-wrap gap-3 mb-3">
                                    <div class="stat-box">
                                        <span class="stat-label">Players</span>
                                        <span class="stat-value"><?php echo htmlspecialchars($players); ?></span>
                                    </div>
                                    <div class="stat-box">
                                        <span class="stat-label">Difficulty</span>
                                        <span class="stat-value"><?php echo htmlspecialchars($difficulty); ?></span>
                                    </div>
                                    <div class="stat-box">
                                        <span class="stat-label">Success Rate</span>
                                        <span class="stat-value"><?php echo htmlspecialchars($success_rate); ?></span>
                                    </div>
                                    <div class="stat-box">
                                        <span class="stat-label">Price</span>
                                        <span class="stat-value">$<?php echo htmlspecialchars($price); ?> / Player</span>
                                    </div>
                                    <div class="stat-box">
                                        <span class="stat-label"><i class="fas fa-vector-square me-1"></i> Layout</span>
                                        <span class="stat-value"><?php echo htmlspecialchars($layout); ?></span>
                                    </div>
                                </div>

                                <div class="modal_info_p">
                                    <p><?php echo nl2br($description1); ?></p>
                                    <?php if (!empty($description2)) { ?>
                                        <p><?php echo nl2br($description2); ?></p>
                                    <?php
                } ?>
                                </div>
                            </div>
                        </div>

                        <div class="all_button_main_header text-end" style="background-size: cover; background-repeat: no-repeat;">
                            <a style="border-radius: 30px!important" class="bg_bnt_custom bg_bnt_custom_tran" data-bs-toggle="modal" data-bs-target="#videoModal<?php echo $productCode; ?>">
                                <i class="fa-solid fa-play m-2"></i> Watch Trailer
                            </a>
                            <a style="border-radius: 30px!important" type="button" class="bg_bnt_custom" data-bs-dismiss="modal" aria-label="Close">OK</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
            } else {
                echo "<!-- No data found for product_id: $productCode -->";
            }
        } else {
            echo "<!-- Error: product_id not defined for modal -->";
        }
?>
    
    
    
<?php
        $count++;
    } // end foreach
    
?>
</div>



        <!-- ======== new card design none======== -->
       
        <style>
        .Last-Minute_Deals {
            margin-top: 0px;
            margin-bottom: 80px;
        }

        .Last-Minute {
            background: #191919;
            padding: 25px;
            border-radius: 12px;
            text-align: left;
            position: relative;
            border: 1px solid #00d9ff70;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            min-height: 350px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .Last-Minute:hover {
            transform: translateY(-8px);
            border-color: #00e0ff;
            transform: scale(1.05);
            box-shadow: 0 0 50px rgba(0, 212, 255, 0.9);
            border: 1px solid #00d9ff;
        }


        .Last-Minute select.form-select {
            background: transparent;
            border: 1px solid #00d4ff;
            border-radius: 5px;
            color: #00d4ff;
        }

        .Last-Minute select.form-select option {
            background: #111;
            color: #fff;
        }

        .Last-Minute .discount-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #00d4ff;
            color: #fff;
            font-size: 12px;
            padding: 0px 6px;
            border-radius: 5px;
            font-weight: bold;
        }

        .Last-Minute .price {
            font-size: 20px;
            font-weight: bold;
            color: #00d4ff;
        }

        .Last-Minute .price del {
            font-size: 14px;
            margin-left: 5px;
            color: #888;
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
    </div>
    </div>
</section>
<?php
endif; ?>




<section class="d-none">
    <div class="container">
        <div class="section_heading_page">
            <span class="badge-custom">
                <i class="fa-solid fa-users"></i>
                Group Discounts</span>
            <h2 class="section-title">Bigger Groups, Bigger Savings</h2>
            <p class="section-subtitle">The more friends you bring, the more you save!</p>
        </div>
        <div class="row g-4 text-center Bigger_Groups">
            
            <div class="col-md-4">
                <div class="Corporate_Team_Building_card p-4  h-100">
                    <i class="bi bi-people-fill fs-2 mb-3"></i>
                    <h5 class="fw-bold">Small Groups</h5>
                    <p>6-10 people</p>
                    <div class="Bigger_Groups_off_price">
                        <strong>15%</strong>
                        <p> OFF regular pricing</p>
                    </div>
                    <div class="">
                        <a href="#" class="bg_bnt_custom w-100 text-center">
                            Book Group Experience
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="Corporate_Team_Building_card p-4  h-100">
                    <i class="bi bi-people-fill fs-2 mb-3"></i>
                    <h5 class="fw-bold">Large Groups</h5>
                    <p>11-20 people</p>
                    <div class="Bigger_Groups_off_price">
                        <strong>25%</strong>
                        <p> OFF regular pricing</p>
                    </div>
                    <div class="">
                        <a href="#" class="bg_bnt_custom w-100 text-center">
                            Book Group Experience
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="Corporate_Team_Building_card p-4  h-100">
                    <i class="bi bi-people-fill fs-2 mb-3"></i>
                    <h5 class="fw-bold">Corporate Events</h5>
                    <p>20+ people</p>
                    <div class="Bigger_Groups_off_price">
                        <strong>35%</strong>
                        <p>OFF + team building extras</p>
                    </div>
                    <div class="">
                        <a href="#" class="bg_bnt_custom w-100 text-center">
                            Contact Sales
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<style>
.Bigger_Groups .Corporate_Team_Building_card p {
    margin: 0;
}

.Bigger_Groups_off_price {
    display: flex;
    align-items: baseline;
    gap: 9px;
    margin: 0 auto;
    width: max-content;
}

.Bigger_Groups .Corporate_Team_Building_card strong {
    font-size: 30px;
    margin: 10px 0;
    display: block;
    color: #00d4ff;
}
</style>
<?php
// Fetch data from DB (reuse same connection)
$stmt = $pdo->query("SELECT * FROM tbl_promotion_page LIMIT 1");
$data = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<section class="Celebrate_Style">
    <div class="container">
       <div class="section_heading_page">
    <?php if (!empty($data['party_small_heading'])): ?>
        <span class="badge-custom">
            <i class="fa-solid fa-gift"></i>
            <?=htmlspecialchars($data['party_small_heading']) ?>
        </span>
    <?php
endif; ?>

    <?php if (!empty($data['party_main_heading'])): ?>
        <h2 class="section-title"><?=htmlspecialchars($data['party_main_heading']) ?></h2>
    <?php
endif; ?>

    <?php if (!empty($data['party_content'])): ?>
        <p class="section-subtitle"><?=nl2br(htmlspecialchars($data['party_content'])) ?></p>
    <?php
endif; ?>
</div>
      <style>
    .continue_next_step_party.disabled {
    opacity: 0.6;
    pointer-events: none;
    cursor: not-allowed;
}
</style>
<?php
// Enable PHP error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include ('link.php');
include ("admin/db.php");

// --- DATA FETCHING (DATABASE ONLY) ---
$data = null;

try {
    $stmt = $pdo->prepare("SELECT product_data FROM bookeo_products_cache WHERE id = 1 LIMIT 1");
    $stmt->execute();
    $cacheRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cacheRow && !empty($cacheRow['product_data'])) {
        $decodedData = json_decode($cacheRow['product_data'], true);
        if ($decodedData && isset($decodedData['data'])) {
            $data = $decodedData;
        }
    }
} catch (Exception $e) {
    error_log("Database error on promotions.php (Block 2): " . $e->getMessage());
}

// Fail safe
if (!isset($data['data']) || !is_array($data['data'])) {
    $data = ['data' => []];
}

// Collect product IDs (Products 6 through 10 for Party Packages)
$productIds = [];
$count = 0;
foreach ($data['data'] as $product) {
    if ($count >= 6 && $count <= 10) {
        $productIds[] = htmlspecialchars($product['productCode'] ?? '');
    }
    $count++;
}

// Convert product IDs to JSON for hidden field
$productIdsJson = json_encode($productIds);
?>

<!-- Hidden input field to store product IDs -->
<input type="hidden" id="productIds" name="productIds" value="<?php echo htmlspecialchars($productIdsJson); ?>">
 <div class="filters">
            <select id="experienceType">
                <option value="all">All Experience Types</option>
                <option value="vr">VR Games Only</option>
                <option value="escape">Escape Rooms Only</option>
                <option value="combo">Escape + VR Combo</option>
            </select>

            <select id="partySize">
                <option value="all">All Party Sizes</option>
                <option value="8">Up To 8 People</option>
                <option value="16">Up To 16 People</option>
              
            </select>
        </div>
<div class="row">
    
    <?php
$count = 0;
foreach ($data['data'] as $product) {
    if ($count >= 6 && $count <= 10) {
        $onOffOptions = $product['onOffOptions']??[];
        $onOffJson = htmlspecialchars(json_encode($onOffOptions));
        $name = htmlspecialchars($product['name']??'');
        $productCode = htmlspecialchars($product['productCode']??'');
        $duration = isset($product['duration']) ? $product['duration'] : ['hours' => 0, 'minutes' => 0];
        $desc = trim($product['description']??'');
        // Split lines
        $lines = preg_split('/\r\n|\r|\n/', $desc);
        $players = isset($lines[0]) ? preg_replace('/^.*?:\s*/', '', trim(strip_tags($lines[0]))) : '';
        $difficulty = isset($lines[1]) ? trim($lines[1]) : '';
        $successRate = isset($lines[2]) ? trim($lines[2]) : '';
        $price = isset($lines[3]) ? preg_replace('/^.*?:\s*/', '', trim(strip_tags($lines[3]))) : '';
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
            $shortDescription.= "...";
        }
        // Image handling: keep original behavior if images exist
        $imageUrl = '';
        if (!empty($product['images'][0]['url'])) {
            $imageUrl = $product['images'][0]['url'];
        }
?>
    <div class="col-md-4 col-sm-12 visible-card">
        <div class="booking_card party_packages_card_new ">
            <div>
                <div class="booking_card_img">
                    <?php
        if (!empty($imageUrl)) {
            echo '<img src="' . htmlspecialchars($imageUrl) . '" alt="' . $name . '" loading="lazy"  decoding="async"  />';
        }
?>
                    <div class="booking_card_time_and_price">
                        <p>
                            <i class="fa-solid fa-clock"></i>
                            <?php
        $duration = $product['duration'];
        $hours = $duration['hours']??0;
        $minutes = $duration['minutes']??0;
        echo " {$hours} HOURS";
        if ($minutes > 0) {
            echo ", {$minutes} Minutes";
        }
        
$strikethrough_price = 0;
$stmt1 = $pdo->prepare("SELECT *
    FROM tbl_party_packages 
    WHERE product_id = :product_id 
   
");
$stmt1->execute([':product_id' => $productCode]);
$row1 = $stmt1->fetch(PDO::FETCH_ASSOC);

if ($row1) {
   
    $strikethrough_price = isset($row1['strikethrough_price']) ? $row1['strikethrough_price'] : 0;
    // optionally sanitize/format $strikethrough_price
}
?>
                        </p>                        
                        <?php foreach ($product['defaultRates'] as $rate) {
                        $amount = $rate['price']['amount'];
                        $discountPercent = 0;
                        if ($strikethrough_price > 0 && $amount < $strikethrough_price) {
                            $discountPercent = ceil(
                                (($strikethrough_price - $amount) / $strikethrough_price) * 100
                            );
                        } ?>
                        <p class="discount-badge_lat_m"><?=$discountPercent?>% OFF</p>
                        <?php } ?>
                       
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
                                    <?php foreach ($product['defaultRates'] as $rate) {
            $amount = $rate['price']['amount'];
            $currency = $rate['price']['currency'];
            echo "<strong>$" . $amount . " </strong>";
        } ?>
                                    
                                    
                                    
                                    <del>$<?=$strikethrough_price?></del>
                                </p>
                                
                                 <input type="hidden" id="price-<?php echo $productCode; ?>" name="price-<?php echo $productCode; ?>" value="<?php echo htmlspecialchars($amount); ?>">
                                 
                                   <input type="hidden" id="onoff-<?php echo $productCode; ?>" value='<?php echo $onOffJson; ?>'>
                            </div>
                            <div class="icon_buttons_wrapper">
                                <div class="icon-button" data-bs-toggle="modal" data-bs-target="#liftInfoModal<?php echo $productCode; ?>">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span class="label">Learn more</span>
                                </div>
                                <div class="icon-button" data-bs-toggle="modal" data-bs-target="#videoModal<?php echo $productCode; ?>">
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
                           
                            
                            <?php
        $playersCount = null;
        if (preg_match('/Up to\s+(\d+)\s+players/i', $name, $matches)) {
            $playersCount = (int)$matches[1];
        }
        if ($playersCount) {
            echo "<p><strong> <img class='palyear_tem_img' src='./assets/images/fleeescape_img/teampay.png'  alt='img loading' loading='lazy'  decoding='async'  ></strong> $playersCount Players</p>";
        } else {
            echo "<p> Not Found</p>";
        }
        $stmt = $pdo->prepare("
            SELECT addon_guest_price 
            FROM tbl_party_packages 
            WHERE product_id = :product_id 
            LIMIT 1
        ");
        $stmt->execute([':product_id' => $productCode]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $addonGuestPrice = $row['addon_guest_price'];
?>
                        </span>
                        
                       <input type="hidden" id="players-<?php echo $productCode; ?>" 
           value="<?php echo $playersCount; ?>">
           
               <input type="hidden" id="per-guest-price-<?php echo $productCode; ?>" 
           value="<?php echo $addonGuestPrice; ?>">
                        <!--<span class="player-value"><?php echo $players; ?> GUESTS</span>-->
                    </div>
                     <?php
// Fetch subtitle from DB
$stmt = $pdo->prepare("SELECT card_subtitle FROM tbl_party_packages WHERE product_id = :product_id LIMIT 1");
$stmt->execute([':product_id' => $productCode]);
$subtitleRow = $stmt->fetch(PDO::FETCH_ASSOC);
$cardSubtitleRaw = $subtitleRow['card_subtitle'] ?? '';

// Split lines
$lines = preg_split("/\r\n|\n|\r/", $cardSubtitleRaw);
$firstLine = array_shift($lines);

// Lines with * become <li>
$liItems = [];
foreach ($lines as $line) {
    $line = trim($line);
    if (str_starts_with($line, '*')) {
        $liItems[] = ltrim($line, '* ');
    }
}
?>
<div class="party_packages_card_new_desc">
    <p class="card-subtitle"><?= htmlspecialchars($firstLine); ?></p>
    <?php if (!empty($liItems)): ?>
        <ul>
            <?php foreach ($liItems as $li): ?>
                <li><?= htmlspecialchars($li); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
                    <!--<div class="party_packages_card_new_desc">-->
                    <!--    <p class="card-subtitle">Perfect for birthday celebrations</p>-->
                    <!--    <ul class="">-->
                    <!--        <li>Private escape room or VR experience</li>-->
                    <!--        <li>Decorated party room for 2 hours</li>-->
                    <!--        <li>Birthday cake &amp; party supplies</li>-->
                    <!--        <li>Dedicated party host</li>-->
                    <!--    </ul>-->
                    <!--</div>-->
                    <div class="custom-date-wrapper_date">
                        <button class="custom-date_arrow prev-date" style="visibility: hidden;">
                            <i class="fa-solid fa-arrow-left"></i>
                        </button>
                        <input type="text" class="custom-datepicker_input sec2-datepicker" data-product="<?php echo $productCode; ?>">
                        <button class="custom-date_arrow next-date">
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
    
                    <div class="time_slots" id="timeSlots-<?php echo $productCode; ?>"></div>
                    <!--<div class="View_All_Dates_tag"><a>View All Dates</a></div>-->
    
                    <div class="guest_and_button">
                        <h5 class="guest-heading mb-2">Add Additional Guests</h5>
                        <div class="d-flex justify-content-between align-items-center">
                         <div class="guest-count-wrapper"
         data-product-id="<?php echo $productCode; ?>"
         data-addon-price="<?php echo $addonGuestPrice; ?>">
    
        <button type="button" class="guest-btn guest-minus-btn">−</button>
        <span id="guest-count-display-<?php echo $productCode; ?>" class="guest-value">0</span>
        <button type="button" class="guest-btn guest-plus-btn">+</button>
    </div>
    
    <p class="m-0">
        <i class="fa-solid fa-dollar-sign"></i>
        <span id="extra-price-<?php echo $productCode; ?>">0</span>
    </p>
    
                        </div>
                    </div>
                </div>
            </div>
                    <div class="next-button-wrapper ">
                           <?php echo '<button  class="continueBtn bg_bnt_custom  continue_next_step_party disabled" id="continueBtn-' . $productCode . '"  data-game-id="' . $productCode . '"  data-game-name="' . htmlspecialchars($name) . '" disabled >Continue</button>'; ?> 
                    </div>
        </div>
    </div>
    
    
    
     <!-- === Video Modal ====== -->
<?php
        if (!empty($productCode)) {
            include ('admin/db.php');
            // Fetch the trailer video safely
            $stmt = $pdo->prepare("SELECT * FROM tbl_party_packages WHERE product_id = :product_id LIMIT 1");
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
                    <h5 class="modal-title" id="videoModalLabel"><?=htmlspecialchars_decode($name)?></h5>
                    <button type="button" class="btn btn-sm close-btn" data-bs-dismiss="modal" aria-label="Close" onclick="stopLocalVideo()">X</button>
                </div>
                <div class="modal-body p-0">
                    <div class="ratio ratio-16x9">
                        <?php if (!empty($videoPath)) { ?>
                            <video id="localVideo"    controls>
                                <source src="<?php echo htmlspecialchars($videoPath); ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php
            } else { ?>
                            <div class="d-flex align-items-center justify-content-center" style="height:300px;">
                                <p class="text-center text-muted">No trailer video available for this game.</p>
                            </div>
                        <?php
            } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
        } else {
            echo "<!-- Error: product_id not defined for modal -->";
        }
?>
  
  <?php
        include ('admin/db.php');
        // Get product ID from URL or request (example: ?product_id=3)
        $stmt = $pdo->prepare("SELECT id, product_id, title, slug, price, duration, players, thumbnail, bottom_heading, video, description 
                       FROM tbl_party_packages 
                       WHERE product_id = :product_id LIMIT 1");
        $stmt->execute([':product_id' => $productCode]);
        $package = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<?php if ($package): ?>
<div class="modal fade" id="liftInfoModal<?php echo htmlspecialchars($productCode); ?>" tabindex="-1" aria-labelledby="liftInfoModal" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content custom-modal">
            <div class="modal-header border-0" style="align-items: flex-start!important;">
                <div class="info_modal_content" style="width: 90%;">
                    <h2 class="modal-title custom-heading" id="liftInfoModalLabel">
                        <?=htmlspecialchars($package['title']) ?>
                    </h2>
                </div>
                <button type="button" class="btn btn-sm close-btn" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5 text-center mb-3 mb-md-0">
                        <img src="admin/uploads/<?=htmlspecialchars($package['thumbnail']) ?>"   loading="lazy" decoding="async"
                             class="img-fluid rounded" alt="<?=htmlspecialchars($package['title']) ?>">
                    </div>

                    <div class="col-md-7">

                        <div class="game-stats d-flex flex-wrap gap-3 mb-3">
                            <div class="stat-box">
                                <span class="stat-label">Players</span>
                                <span class="stat-value"><?=htmlspecialchars($package['players']) ?></span>
                            </div>
                            <div class="stat-box">
                                <span class="stat-label">Price</span>
                                <span class="stat-value">$<?=htmlspecialchars($package['price']) ?></span>
                            </div>
                            <div class="stat-box">
                                <span class="stat-label"><i class="fas fa-clock me-1"></i> Duration</span>
                                <span class="stat-value"><?=htmlspecialchars($package['duration']) ?></span>
                            </div>
                        </div>

                        <div class="modal_info_p">
                            <p><?=nl2br(htmlspecialchars($package['description'])) ?></p>
                        </div>
                    </div>
                </div>

                <div class="all_button_main_header text-end" style="background-size: cover; background-repeat: no-repeat;">
                    <?php if (!empty($package['video'])): ?>
                        <a style="border-radius: 30px!important" class="bg_bnt_custom bg_bnt_custom_tran" data-bs-toggle="modal"
                           data-bs-target="#videoModal<?php echo htmlspecialchars($productCode); ?>"><i class="fa-solid fa-play m-2"></i> Watch Trailer</a>
                    <?php
            endif; ?>
                    <a style="border-radius: 30px!important" type="button" class="bg_bnt_custom"
                       data-bs-dismiss="modal" aria-label="Close">OK</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
        endif; ?>
    
    
    
    <?php
    }
    $count++;
} // end foreach

?>

<div id="noResultMsg" style="text-align: center; color: rgb(85, 85, 85); margin: 30px auto; font-weight: 500; display: none;">No matching games found. Please adjust your filters.</div>
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

<script>
document.addEventListener('click', function(e) {
    // Detect clicks only on new guest count buttons
    if (e.target.classList.contains('guest-plus-btn') || e.target.classList.contains('guest-minus-btn')) {

        const wrapper = e.target.closest('.guest-count-wrapper');
        const addonPrice = parseFloat(wrapper.dataset.addonPrice) || 0;
        const productId = wrapper.dataset.productId;

        const valueSpan = document.getElementById('guest-count-display-' + productId);
        const priceSpan = document.getElementById('extra-price-' + productId);

        let count = parseInt(valueSpan.textContent, 10) || 0;

        // Increase or decrease
        if (e.target.classList.contains('guest-plus-btn')) {
            count++;
        } else if (e.target.classList.contains('guest-minus-btn') && count > 0) {
            count--;
        }

        // Update guest count
        valueSpan.textContent = count;

        // Calculate total price
        const totalPrice = addonPrice * count;
        priceSpan.textContent = totalPrice.toFixed(2);
    }
});
</script>
            
            
      
         
     
    </div>
</section>
<style>
/* Section styles */
.Celebrate_Style {
    padding: 60px 0 38px 0;
    background: transparent;
}

/* Cards container */
.packages-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 28px;
}



/* Features list */
.feature-list {
    margin: 0 0 0 0;
    padding: 0;
    list-style: none;
    font-size: 1.04rem;
}

.feature-list li {
    margin-bottom: 8px;
    position: relative;
    padding-left: 20px;
}

.feature-list.pink li:before {
    content: "â˜…";
    position: absolute;
    left: 0;
    color: #00d4ff;
    font-size: 1.07em;
}

.feature-list.orange li:before {
    content: "â˜…";
    position: absolute;
    left: 0;
    color: #00d4ff;
    font-size: 1.07em;
}


.Celebrate_Style .card-content button.bg_bnt_custom.continue_nex_step {
    font-weight: 700;
}
</style>

<section class="adventure-section">
    <div class="container">
        <div class="adventure-content">
            <h2>Ready for Your Next Adventure?</h2>
            <p>Don't miss out on these incredible deals. Book your escape room <br> or VR experience today!</p>
            <div class="all_button_main_header order_summart_main_button">
                <a href="booking" class="bg_bnt_custom">Book Now</a>
                <a href="indoor-real-life-escape-games" class="bg_bnt_custom bg_bnt_custom_tran">View All Experiences</a>
            </div>
        </div>
    </div>
</section>



<!-- Last_Minute_Deals_card_player -->
<script>
const minusBtn = document.querySelector(".Last_Minute_Deals_card_player .minus-btn");
const plusBtn = document.querySelector(".Last_Minute_Deals_card_player .plus-btn");
const playerValue = document.getElementById("player-count");

let count = 0;
plusBtn.addEventListener("click", () => {
    count++;
    playerValue.textContent = count;
});
minusBtn.addEventListener("click", () => {
    if (count > 0) {
        count--;
        playerValue.textContent = count;
    }
});
</script>

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

<script>
function initCardFilter() {
    const experienceSelect = document.getElementById('experienceType');
    const partySizeSelect = document.getElementById('partySize');
    const noResultMsg = document.getElementById('noResultMsg');

    if (!experienceSelect || !partySizeSelect) return;

    const cards = document.querySelectorAll('.visible-card');
    if (cards.length === 0) return;

    const cardData = [];

    cards.forEach(card => {
        const text = card.querySelector('h5')?.innerText.toLowerCase() || '';
        let type = 'escape';
        if (text.includes('vr') && text.includes('escape')) type = 'combo';
        else if (text.includes('vr')) type = 'vr';
        else if (text.includes('escape')) type = 'escape';

        const playerText = card.innerText.toLowerCase();
        let size = 'all';
        if (playerText.includes('8 players')) size = '8';
        else if (playerText.includes('16 players')) size = '16';

        cardData.push({ card, type, size });
    });

    function filterCards() {
        const selectedType = experienceSelect.value;
        const selectedSize = partySizeSelect.value;

        let visibleCount = 0;

        cardData.forEach(({ card, type, size }) => {
            let show = true;
            if (selectedType !== 'all' && selectedType !== type) show = false;
            if (selectedSize !== 'all' && selectedSize !== size) show = false;

            card.style.display = show ? 'block' : 'none';
            if (show) visibleCount++;
        });

        // Just show or hide existing message div
        if (noResultMsg) {
            noResultMsg.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    experienceSelect.addEventListener('change', filterCards);
    partySizeSelect.addEventListener('change', filterCards);
}

// Retry until dynamic content loads
const observer = new MutationObserver(() => {
    const cards = document.querySelectorAll('.booking_card.party_packages_card_new');
    if (cards.length > 0) {
        initCardFilter();
        observer.disconnect();
    }
});
observer.observe(document.body, { childList: true, subtree: true });
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
</style>

<!-- Modal Code -->
<div class="modal fade" id="partymodalform" tabindex="-1" aria-labelledby="partymodalformLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 900px;">
        <div class="modal-content custom-modal text-white">
            <div class="modal-header custom-modal-header">
                <h4 class="modal-title" id="partymodalformLabel">🗝️ Escape Room Choice</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body custom-modal-body">
                <p class="intro-text">
                    Please select your preferred escape rooms (multiple allowed).<br>
                    Your choices will appear on the right.
                </p>
                <div class="row g-4">
                    <!-- Left side -->
                    <div class="col-md-6">
                        <div class="modal-box">
                            <h5>Available Escape Rooms</h5>
                            <div class="room-list">
                                <label class="room-item">
                                    <input type="checkbox" class="room-checkbox" value="The Lift">
                                    <i class="fa-solid fa-door-open"></i> The Lift
                                </label>
                                <label class="room-item">
                                    <input type="checkbox" class="room-checkbox" value="Ice Walker - GOT">
                                    <i class="fa-solid fa-snowflake"></i> Ice Walker - GOT
                                </label>
                                <label class="room-item">
                                    <input type="checkbox" class="room-checkbox" value="Prison Escape">
                                    <i class="fa-solid fa-lock"></i> Prison Escape
                                </label>
                                <label class="room-item">
                                    <input type="checkbox" class="room-checkbox" value="Steampunk Submarine">
                                    <i class="fa-solid fa-gears"></i> Steampunk Submarine
                                </label>
                                <label class="room-item">
                                    <input type="checkbox" class="room-checkbox" value="Museum Heist">
                                    <i class="fa-solid fa-landmark"></i> Museum Heist
                                </label>
                                <label class="room-item">
                                    <input type="checkbox" class="room-checkbox" value="Ancient Egypt">
                                    <i class="fa-solid fa-monument"></i> Ancient Egypt
                                </label>
                                <label class="room-item">
                                    <input type="checkbox" class="room-checkbox" value="Any">
                                    <i class="fa-solid fa-monument"></i> Any
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- Right side -->
                    <div class="col-md-6">
                        <div class="modal-box">
                            <h5>Your Selection</h5>
                            <textarea id="selectionTextarea" placeholder="Your selected rooms will appear here..."
                                style="width:100%;height:150px;" readonly></textarea>
                            <small>💡 If you don’t have a preference, just select <b>Any</b>.</small>
                        </div>
                    </div>

                

                </div>
            </div>
            <!-- Footer buttons -->
            <div class="all_button_main_header">
                <!-- Removed Skip -->
                <a href="javascript:void(0)" id="escape-selection" class="bg_bnt_custom disabled"
                    aria-disabled="true">Next</a>
            </div>


            <div id="escapeRoomError" class="alert alert-warning d-none" role="alert"
                style="padding: 8px 12px; font-size: 14px;">
            </div>
        </div>
    </div>
</div>


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
<style>
.custom-modal {
    background: linear-gradient(135deg, #0e0e0e, #1a1a1a);
    border-radius: 18px;
    overflow: hidden;
}

.custom-modal-header {
    background: rgba(0, 230, 246, 0.1);
    border-bottom: none;
    color: #00e6f6;
    text-align: center;
    font-weight: bold;
    letter-spacing: 1px;
}

.custom-modal-body {
    padding: 20px;
}

.modal-box {
    background: rgba(255, 255, 255, 0.05);
    padding: 15px;
    height: 100%;
}

.room-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.room-item {
    background: rgba(255, 255, 255, 0.08);
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.room-item:hover {
    background: rgba(0, 230, 246, 0.15);
}

.room-item input {
    accent-color: #00e6f6;
    transform: scale(1.2);
}

#selectionTextarea {
    width: 100%;
    min-height: 180px;
    background: #111;
    border: 1px solid #00e6f6;
    color: #fff;
    padding: 10px;
    border-radius: 6px;
    resize: none;
}

.all_button_main_header {
    /*display: flex;*/
    /*justify-content: center;*/
    /*gap: 15px;*/
    /*padding: 15px;*/
    /*background: rgba(0, 230, 246, 0.05);*/
}

.bg_bnt_custom {
    padding: 8px 20px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
}

.bg_bnt_custom_tran {
    border: 1px solid #00e6f6;
    color: #00e6f6;
    background: transparent;
}

.bg_bnt_custom {
    background-color: #00e6f6;
    color: #000;
}
</style>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // --- CONFIG & LIBS ---
    const { DateTime } = luxon;
    const LA_ZONE = "America/Los_Angeles";
    const today = DateTime.now().setZone(LA_ZONE).startOf('day');
    const todayRaw = today.toFormat('yyyy-MM-dd');
    const fmtDisplay = dt => dt.toFormat("ccc, LLLL dd, yyyy");

    // --- REQUEST QUEUE / DEBOUNCE (to avoid Bookeo 429) ---
    let activeRequests = 0;
    let fetchTimers = {}; // per product/date debounce
    let maxParallel = 3;
    let runningRequests = 0;
    let requestQueue = [];
    let loadedTabs = {}; // cache tab html (optional)
    let isInitialLoad = true;

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

    // --- PRIMARY SLOT FETCHER: supports date-switching and auto-next-day (keeps datepicker updates) ---
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
                        isInitialLoad = false;
                        productIds.forEach(id => {
                            const $container = $('#timeSlots-' + id);
                            if ($container.length) $container.html('<p>No slots available</p>');
                        });
                        return;
                    }

                    let atLeastOneGameHasSlots = false;
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

                        if (!html.includes("No slots available") && !html.includes("Error loading slots")) {
                            atLeastOneGameHasSlots = true;
                        }

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

                    });

                    if (allowAutoNextDay && isInitialLoad && rawDate === todayRaw && !atLeastOneGameHasSlots) {
                        isInitialLoad = false;
                        const nextDay = today.plus({ days: 1 });
                        const nextRaw = nextDay.toFormat('yyyy-MM-dd');
                        const nextVisual = fmtDisplay(nextDay);

                        $('.custom-datepicker_input').each(function() {
                            const $input = $(this);
                            $input.data('rawdate', nextRaw);
                            $input.attr('data-rawdate', nextRaw);

                            if (this._flatpickr) {
                                this._flatpickr.setDate(nextRaw, false);
                                const yearSelect = this._flatpickr.calendarContainer.querySelector(".flatpickr-year-dropdown");
                                if (yearSelect) yearSelect.value = nextDay.year;
                            }

                            $input.val(nextVisual);
                        });

                        updatePrevButtons();
                        fetchSlotsForProducts(productIds, nextRaw, false);
                        fetchSlotsForProducts1(productIds, nextRaw);
                        return;
                    }

                    isInitialLoad = false;
                },
                error: function(xhr, status, error) {
                    console.log(`fetchSlotsForProducts: Error for date=${rawDate}, error=${error}`); 
                    isInitialLoad = false;
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

    // --- SECONDARY SLOT FETCHER: only updates flesh container HTML, DOES NOT MODIFY date inputs nor auto-advance ---
    function fetchSlotsForProducts1(productIds, rawDate) {
        // Defensive sanitize
        productIds = (productIds || []).map(p => (typeof p === 'string' ? p.trim() : '')).filter(Boolean);

        if (!productIds.length || !rawDate) {
            console.log(`fetchSlotsForProducts1: Invalid input, productIds=${JSON.stringify(productIds)}, rawDate=${rawDate}`);
            return;
        }

        const ajaxFn = () => {
            const deferred = $.Deferred();
            trackRequestStart();

            // Show loading for each product's flesh container
            productIds.forEach(id => {
                const $container = $('#timeSlotsFlesh-' + id);
                if ($container.length) $container.html('<div class="time_slots_loader">Loading slots...</div>');
            });

            console.log('fetchSlotsForProducts1: AJAX calling fetch_slots_flesh.php', { productIds, rawDate });

            $.ajax({
                url: 'fetch_slots_flesh.php',
                type: 'GET',
                data: {
                    date: rawDate,
                    productIds: JSON.stringify(productIds)
                },
                dataType: 'json',
                success: function(response) {
                    const currentRawDate = $('.custom-datepicker_input').first().data('rawdate');
                    if (currentRawDate && currentRawDate !== rawDate) {
                        console.log(`fetchSlotsForProducts1: Ignoring stale response for ${rawDate}; active date is ${currentRawDate}`);
                        return;
                    }

                    if (typeof response !== 'object' || response === null) {
                        console.log('fetchSlotsForProducts1: Invalid response', response);
                        productIds.forEach(id => {
                            const $container = $('#timeSlotsFlesh-' + id);
                            if ($container.length) $container.html('<p>No slots available</p>');
                        });
                        return;
                    }

                    productIds.forEach(productId => {
                        const res = response[productId];

                        // two shapes handled, but importantly: DO NOT modify datepicker inputs here
                        let html;
                        if (res && typeof res === 'object' && res.hasOwnProperty('html')) {
                            html = res.html;
                        } else {
                            html = res || '<p>No slots available</p>';
                        }

                        const $container = $('#timeSlotsFlesh-' + productId);
                        if ($container.length) $container.html(html);

                        // NOTE: intentionally DO NOT update .custom-datepicker_input or recurse on "No slots available".
                        // Primary fetchSlotsForProducts handles date-switching and recursion to avoid conflicts.
                    });
                },
                error: function(xhr, status, error) {
                    console.log(`fetchSlotsForProducts1: Error for date=${rawDate}, error=${error}`);
                    productIds.forEach(id => {
                        const $container = $('#timeSlotsFlesh-' + id);
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
            console.log(
                `updatePrevButtons: Checking datepicker: product=${$input.data('product')}, rawdate=${raw}, current=${current.toISODate()}, today=${today.toISODate()}, diffDays=${diffDays}`
            );
            if (diffDays > 0) anyFuture = true;
        });
        console.log(`updatePrevButtons: anyFuture=${anyFuture}`);
        const $prevAllBtn = $("#prev-all-btn");
        if (anyFuture) {
            $prevAllBtn.prop("disabled", false)
                .removeClass("disabled disabled-btn");
            console.log(
            "updatePrevButtons: Enabling #prev-all-btn, removing disabled and disabled-btn classes");
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
        const targetYear = normalized.year; // Extract the year
        console.log(`setDateAll: Setting date to ${rawDate}`);
        $('.custom-datepicker_input').each(function() {
            const $input = $(this);
            if ($input.hasClass('flatpickr-mobile')) return;
            
            if (this._flatpickr) {
                this._flatpickr.setDate(rawDate, false);
                const yearSelect = this._flatpickr.calendarContainer.querySelector(".flatpickr-year-dropdown");
                if (yearSelect) {
                    yearSelect.value = targetYear;
                }
            }
            
            $input.data('rawdate', rawDate);
            $input.val(fmtDisplay(normalized));
            console.log(
                `setDateAll: Updated datepicker: product=${$input.data('product')}, rawdate=${$input.data('rawdate')}`
            );
        });
        // Fetch for all products after setting date
        const productIds = $('.custom-datepicker_input').map(function() {
            return $(this).data('product');
        }).get();
        console.log(`setDateAll: Fetching slots for productIds: ${productIds}`);
        fetchSlotsForProducts(productIds, rawDate);
        fetchSlotsForProducts1(productIds, rawDate);
        updatePrevButtons();
        // == 2 second  section date chang=== 
        resetSection1Date();

    }
    
   // == 2 second  section date chang===
function resetSection1Date() {
    const today = luxon.DateTime.now().setZone("America/Los_Angeles").startOf('day');
    const todayStr = today.toFormat("ccc, LLL dd, yyyy");

    document.querySelectorAll(".sec1-datepicker").forEach(inp => {
        inp.value = todayStr;           // visible text reset
        inp.setAttribute("readonly", true);
        inp.style.pointerEvents = "none";

        // rawdate भी आज पर ही fix रहे
        inp.dataset.rawdate = today.toFormat("yyyy-MM-dd");
    });
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
        const productId = $input.data('product');
        if (!productId) {
            console.log(`initDatePickers: Skipping datepicker with no productId, index=${index}`);
            return;
        }

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
            disableMobile: true,

            onReady: (selectedDates, dateStr, instance) => {
                $input.val(fmtDisplay(today));
                console.log(
                    `initDatePickers: Datepicker initialized: product=${productId}, rawdate=${initialDate}`
                );

                // HIDE default year input
                const calendar = instance.calendarContainer;
                const yearInput = calendar.querySelector(".numInputWrapper");
                if (yearInput) yearInput.style.display = "none";

                // CREATE CUSTOM YEAR DROPDOWN
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

                    const monthContainer = calendar.querySelector(".flatpickr-current-month");
                    if (monthContainer) monthContainer.appendChild(yearSelect);
                }
            },

            onChange: (selectedDates) => {
                if (!selectedDates || !selectedDates[0]) {
                    console.log(
                        `initDatePickers: No date selected for product=${productId}`
                    );
                    return;
                }
                const jsDate = selectedDates[0];
                const picked = DateTime.fromObject({
                    year: jsDate.getFullYear(),
                    month: jsDate.getMonth() + 1,
                    day: jsDate.getDate()
                }, { zone: LA_ZONE }).startOf('day');
                console.log(
                    `initDatePickers: Datepicker changed: product=${productId}, setting date to ${picked.toISODate()}`
                );
                setDateAll(picked);
            },

            onYearChange: (selectedDates, dateStr, instance) => {
                const yearSelect = instance.calendarContainer.querySelector(".flatpickr-year-dropdown");
                if (yearSelect) yearSelect.value = instance.currentYear || new Date().getFullYear();
            }
        });
    });

    // Fetch slots for products in this container immediately after initializing datepickers
    const productIds = container.find('.custom-datepicker_input').map(function() {
        return $(this).data('product');
    }).get();
    console.log(`initDatePickers: Initial fetch for productIds: ${productIds}`);
    fetchSlotsForProducts(productIds, today.toFormat('yyyy-MM-dd'));
    fetchSlotsForProducts1(productIds, today.toFormat('yyyy-MM-dd'));
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
        const discountedEl = document.querySelector(`#discounted-${productCode}`);
        const discountPercent = discountedEl ? parseFloat(discountedEl.textContent.trim()) : 0;

        // hold value in a new variable
        const discountedPrice = pricePerGuest - (pricePerGuest * (discountPercent / 100));
        console.log(discountPercent);
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
            priceEl.textContent = (count * discountedPrice).toFixed(2);
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
        console.log(
            `Prev button clicked: rawdate=${raw}, current=${current.toISODate()}, today=${today.toISODate()}, diffDays=${diffDays}`
        );
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
        console.log(
            `Next button clicked: rawdate=${raw}, current=${current.toISODate()}, setting to ${newDate.toISODate()}`
        );
        setDateAll(newDate);
    });

    initTabContent($(document));
});
</script>


<!-- JavaScript -->
<script>
(function() {
    console.log("Room selection script loaded ✅");

    let selected = [];

    function renderSelection(textareaId) {
        const textarea = document.getElementById(textareaId);
        if (!textarea) return;
        textarea.value = selected.join("\n");
        validateNextButton();
    }

    function validateNextButton() {
        const nextBtn = document.getElementById("escape-selection");
        if (selected.length > 0) {
            nextBtn.classList.remove("disabled");
            nextBtn.removeAttribute("aria-disabled");
        } else {
            nextBtn.classList.add("disabled");
            nextBtn.setAttribute("aria-disabled", "true");
        }
    }

    function handleCheckboxChange(e, textareaId) {
        const el = e.target;
        if (!el.classList.contains('room-checkbox')) return;
        const value = el.value.trim();

        if (el.checked) {
            if (!selected.includes(value)) selected.push(value);
        } else {
            selected = selected.filter(v => v !== value);
        }

        renderSelection(textareaId);
    }

    function initRoomSelection(textareaId) {
        const checkboxes = document.querySelectorAll('.room-checkbox');
        if (!checkboxes.length) return;

        checkboxes.forEach(box => {
            box.addEventListener('change', function(e) {
                handleCheckboxChange(e, textareaId);
            });
        });

        // Initialize if any pre-checked
        selected = Array.from(document.querySelectorAll('.room-checkbox:checked')).map(b => b.value.trim());
        renderSelection(textareaId);
    }
    document.addEventListener('DOMContentLoaded', function() {
        initRoomSelection('selectionTextarea');

        const errorBox = document.getElementById("escapeRoomError");

        function showError(message) {
            errorBox.textContent = message;
            errorBox.classList.remove("d-none");
        }

        function hideError() {
            errorBox.textContent = "";
            errorBox.classList.add("d-none");
        }

        document.getElementById("escape-selection").addEventListener("click", function(e) {
            if (selected.length === 0) {
                showError("⚠️ Please select at least one escape room before continuing.");
                return;
            }

            hideError();

            const selectionText = document.getElementById("selectionTextarea").value.trim();
            const btn = this;
            btn.innerText = "Saving...";
            btn.classList.add("disabled");

            fetch("save_room_selection.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "selection=" + encodeURIComponent(selectionText)
                })
                .then(res => res.json())
                .then(data => {
                    console.log("Save response:", data); // ADD THIS

                    if (data.status === "success") {
                        console.log("done");
                        hideError();

                        // ✅ Hide the modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById(
                            'partymodalform'));
                        if (modal) modal.hide();

                        // ✅ Move to next step
                  window.location.href = "<?= BASE_URL ?>/booking?add-ons-";
                    } else {
                        showError("❌ Something went wrong while saving selection.");
                        console.error(data);
                    }
                })
                .catch(err => {
                    showError("❌ Network error: " + err.message);
                })
                .finally(() => {
                    btn.innerText = "Next";
                    btn.classList.remove("disabled");
                });
        });
    });


    window.initRoomSelection = initRoomSelection;
})();
</script>

<script>
let timerInterval = null;
let timerEndTime = null;

function updateBundleOffers(cartCount) {
    const offer1 = document.querySelector('.offer-card[data-offer="1"]');
    const offer2 = document.querySelector('.offer-card[data-offer="2"]');

    if (!offer1 || !offer2) return;

    if (cartCount === 1) {
        offer1.style.display = 'block';
        offer2.style.display = 'block';
    } else if (cartCount === 2) {
        offer1.style.display = 'none';
        offer2.style.display = 'block';
    } else {
        offer1.style.display = 'none';
        offer2.style.display = 'none';
    }
}

// ✅ Update buttons (disable booked + disable guest count controls + highlight slot)
function updateBookedButtons(cartItems) {
    document.querySelectorAll(".continue_nex_step").forEach(btn => {
        const gameId = btn.getAttribute("data-game-id");
        const isBooked = cartItems.some(item => item.gameId === gameId);
        const bookedItem = cartItems.find(item => item.gameId === gameId);

        const guestWrapper = document.querySelector(`#guest-count-${gameId}`)?.closest(".guest-count-wrapper");

        if (isBooked) {
            btn.innerText = "Added to Cart";
            btn.classList.add("booked_btn");
            btn.disabled = true;
            btn.style.opacity = "0.5";

            if (guestWrapper) {
                guestWrapper.querySelectorAll(".guest-btn").forEach(b => b.disabled = true);
            }

            if (bookedItem && bookedItem.slot) {
                const slotInput = document.querySelector(`input[name="lift-time-${gameId}"][value="${bookedItem.slot}"]`);
                if (slotInput) {
                    slotInput.checked = true;
                    slotInput.closest("label")?.classList.add("slot-selected");
                }
            }
        } else {
            btn.innerText = "Continue";
            btn.classList.remove("booked_btn");
            btn.disabled = false;

            if (guestWrapper) {
                guestWrapper.querySelectorAll(".guest-btn").forEach(b => b.disabled = false);
            }

            document.querySelectorAll(`input[name="lift-time-${gameId}"]`).forEach(r => {
                r.closest("label")?.classList.remove("slot-selected");
            });
        }
    });
}

/* ===========================================================
   SIMPLIFIED loadCart FUNCTION
   -----------------------------------------------------------
   - Only fetches and uses cart count
   - No UI rendering or totals updates
   =========================================================== */
function loadCart() {
    fetch("cart_view.php?live=1")
        .then(res => res.text())
        .then(html => {
            // if (html.includes("data-totals") === false) {
            //     // Redirect if cart empty
            //     window.location.href = "<?= BASE_URL ?>/booking.php?choose-experience";
            //     return;
            // }

            // ✅ Only determine cart count (no rendering)
            const tempDiv = document.createElement("div");
            tempDiv.innerHTML = html;
            const cartCount = tempDiv.querySelectorAll('.summary-row-group').length;

            // Update bundle offers based on count
            updateBundleOffers(cartCount);

            // Update booked buttons
            fetch("get_cart.php")
                .then(res => res.json())
                .then(data => {
                    if (Array.isArray(data.cart)) {
                        updateBookedButtons(data.cart);
                    }
                });
        })
        .catch(err => console.error("Cart load error:", err));
}
function loadAddons() {
    fetch("load_addons.php")
        .then(res => res.text())
        .then(html => {
            document.querySelector(".add_on_section").innerHTML = html;
        });
}
document.addEventListener("DOMContentLoaded", loadCart);

document.addEventListener("click", function(e) {
  // -------------------------------
  // REGULAR GAME CONTINUE BUTTON
  // -------------------------------
if (e.target.classList.contains("continue_nex_step") && !e.target.disabled) {
    const btn = e.target;
    
    // Prevent double click
    if (btn.dataset.processing === "true") return;
    btn.dataset.processing = "true";
    btn.disabled = true;

    const productCode = btn.getAttribute("data-game-id");
    const gameName = btn.getAttribute("data-game-name");
    const guestCount = document.getElementById(`guest-count-${productCode}`).innerText;
    const unitPrice = document.getElementById(`price-${productCode}`).innerText.replace("/Guest", "").trim();
    const selectedSlot = document.querySelector(`input[name="lift-time-${productCode}"]:checked`);
    
    let slot = "No slot";
    let eventId = "";
    let dataAvailable = "0";
    if (selectedSlot) {
        slot = selectedSlot.value;
        eventId = selectedSlot.getAttribute("data-eventid");
        dataAvailable = selectedSlot.getAttribute("data-available") || "0";
    }

    fetch("cart_session.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            action: "add_promotion_cart",
            gameId: productCode,
            gameName: gameName,
            slot: slot,
            eventId: eventId,
            guests: guestCount,
            price: unitPrice,
            dataAvailable: dataAvailable
        })
    })
    .then(res => res.json())
    .then(response => {
        if (response.status !== "success") {
            showBookeoError(response.message || "This time slot is no longer available. Please select another time.");
            return;
        }

        if (response.status === "success") {
            loadCart();
              loadAddons();
            window.location.href = "<?= BASE_URL ?>/booking?add-ons-";
        }
    })
    .catch(err => {
        console.error(err);
        showBookeoError("Network error. Please try again.");
    })
    .finally(() => {
        btn.dataset.processing = "false";
        btn.disabled = false;
    });
}


  // -------------------------------
  // PARTY PACKAGE CONTINUE BUTTON
  // -------------------------------
document.addEventListener("click", async (e) => {
    const btn = e.target.closest(".continue_next_step_party");
    
    // Agar click button pe nahi hai, toh kuch mat karo
    if (!btn || btn.disabled) return;

    // ⭐ IMPORTANT: Prevent multiple executions
    if (btn.dataset.processing === "true") return; // Already processing
    btn.dataset.processing = "true"; // Mark as processing
    btn.disabled = true; // Optional: visually disable

    try {
       const productCode = btn.dataset.gameId;
      const gameName = btn.dataset.gameName;
      const guestCount = document.getElementById(`guest-count-display-${productCode}`).innerText;
      const extraPrice = document.getElementById(`extra-price-${productCode}`).innerText || 0;
      const perGuestPrice = document.getElementById(`per-guest-price-${productCode}`).value || 0;
      const totalPrice = document.getElementById(`price-${productCode}`).value;
      const onOffJson = document.getElementById(`onoff-${productCode}`).value;
 const selectedSlot = document.querySelector(`input[name="lift-time-${productCode}"]:checked`); 
        
        let slot = "No slot";
        let eventId = "";
        if (selectedSlot) {
            slot = selectedSlot.value;
            eventId = selectedSlot.getAttribute("data-eventid");
        }

        document.getElementById("stepLoader")?.style?.setProperty("display", "flex");

        const response = await fetch("cart_session.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action: "add_party_cart",
                slot: slot,
                gameId: productCode,
                eventId: eventId,
                gameName: gameName,
                price: totalPrice,
                additional_guest: guestCount,
                per_guest_price: perGuestPrice,
                total_additional_price: extraPrice
            })
        });

        const resData = await response.json();

        // ⭐ Slot unavailable → show error ONCE and STOP
        if (resData.status !== "success") {
            document.getElementById("stepLoader")?.style?.setProperty("display", "none");
            showBookeoError("Failed to reserve slot. Please try again or choose another time.");

            // ⭐ Yeh sabse important line hai — modal ke bahar click karne pe dobara na chale
            return; // Bilkul ruk jao yahi pe
        }

        
        if (productCode === "41551LAM3LY18570132661") {
            loadCart();
            loadAddons();
            window.location.href = "<?= BASE_URL ?>/booking?add-ons-";
            return;
        }
        const modalEl = document.getElementById("partymodalform");
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
        
        loadCart();

        document.getElementById("stepLoader")?.style?.setProperty("display", "none");

    } catch (error) {
        console.error("Error in add to cart:", error);
        alert("Something went wrong. Please try again.");
    } finally {
        // Reset button state
        btn.dataset.processing = "false";
        btn.disabled = false;
    }
});



  // -------------------------------
  // DELETE CART ITEM
  // -------------------------------
  if (e.target.closest(".delete_card")) {
    const index = e.target.closest(".delete_card").getAttribute("data-index");

    const modal = document.getElementById("deleteConfirmModal");
    const modalBox = document.getElementById("deleteModalBox");

    const title = document.getElementById("deleteModalTitle");
    const text = document.getElementById("deleteConfirmText");
    const actions = document.getElementById("deleteActions");
    const loading = document.getElementById("deleteLoading");

    modal.style.display = "flex";

    document.getElementById("confirmDeleteBtn").onclick = function () {
        title.style.display = "none";
        text.style.display = "none";
        actions.style.display = "none";
        loading.style.display = "flex";
        modalBox.classList.add("loading-mode");

        fetch("cart_session.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "action=remove_from_cart&index=" + encodeURIComponent(index)
        })
        .then(res => res.json())
        .then(data => {
            modal.style.display = "none";

            title.style.display = "block";
            text.style.display = "block";
            actions.style.display = "flex";
            loading.style.display = "none";
            modalBox.classList.remove("loading-mode");

            loadCart();

            setTimeout(() => {
                fetch("get_cart.php")
                    .then(res => res.json())
                    .then(data => {
                        if (!data.cart || data.cart.length === 0) {
                            window.location.href = "<?= BASE_URL ?>/booking.php?choose-experience";
                        }
                    });
            }, 300);
        });
    };

    document.getElementById("cancelDeleteBtn").onclick = function () {
        modal.style.display = "none";
    };
  }
});
</script>





<div id="globalLoader" aria-hidden="true">
    <div class="loader-content">
        <div class="loader-circle" role="status" aria-label="Loading"></div>
        <p>Data Loading Please Wait</p>
    </div>
</div>

<div id="stepLoader" aria-hidden="true">
    <div class="step-loader-content">
        <div class="step-loader-circle" role="status" aria-label="Loading"></div>
        <p>Please wait...</p>
    </div>
</div>

<script>

// === SECTION–1 FIX (Disable Datepicker Completely) ===
document.querySelectorAll(".sec1-datepicker").forEach(inp => {

    if (inp._flatpickr) inp._flatpickr.destroy();

    const today = luxon.DateTime.now().setZone("America/Los_Angeles");
    inp.value = today.toFormat("ccc, LLL dd, yyyy");

    inp.setAttribute("readonly", true);
    inp.style.pointerEvents = "none";

    ["click","focus","mousedown","touchstart"].forEach(evt => {
        inp.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopImmediatePropagation();
        }, true);
    });
});


// === SECTION–2 FIX (Calendar Should Work Normally) ===
function initSection2Datepicker() {
    document.querySelectorAll(".sec2-datepicker").forEach(inp => {

        // Already selected date preserve करें
        let selectedDate = inp.dataset.rawdate;

        // अगर पहले कोई flatpickr instance है → destroy करो
        if (inp._flatpickr) {
            inp._flatpickr.destroy();
        }

        // Flatpickr init
        flatpickr(inp, {
            dateFormat: "Y-m-d",
            defaultDate: selectedDate || "today",  
            allowInput: false,
            clickOpens: true,
            minDate: "today",

            onOpen: function(selectedDates, dateStr, instance) {
                // Calendar open होने पर selected date को force apply करें
                if (selectedDate) {
                    instance.setDate(selectedDate, false);
                }
            },

            onChange: function(selectedDates, dateStr, instance) {
                // New selected date store करें
                selectedDate = dateStr;
                inp.dataset.rawdate = dateStr;

                instance.close(); // Calendar close issue fix
            }
        });
    });
}



</script>

<!-- 🔽 Last Minute Deals Auto Hide / Show -->
<script>
function toggleLastMinuteDealsSection() {
    const section = document.querySelector(".Last-Minute_Deals");
    if (!section) return;

    let hasAnySlot = false;

    document.querySelectorAll('[id^="timeSlotsFlesh-"]').forEach(container => {
        if (container.querySelector('input[type="radio"]')) {
            hasAnySlot = true;
        }
    });

    section.style.display = hasAnySlot ? "block" : "none";
}

const slotObserver = new MutationObserver(() => {
    toggleLastMinuteDealsSection();
});

document.addEventListener("DOMContentLoaded", () => {
    const target = document.querySelector(".Last-Minute_Deals");
    if (!target) return;

    slotObserver.observe(target, {
        childList: true,
        subtree: true
    });

    toggleLastMinuteDealsSection();
});
</script>



<?php include ('includes/footer.php'); ?>
