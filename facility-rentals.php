<style>
    .continue_next_step_party.disabled {
    opacity: 0.6;
    pointer-events: none;
    cursor: not-allowed;
}
</style>
<?php
// Note: session_start() may be needed if not already in a header file.
// Enable PHP error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('link.php');
include("admin/db.php");

// --- DATA FETCHING (DATABASE ONLY) ---
// The product data is now managed via the admin panel and stored in the database.
// The live API call and caching logic have been removed to improve performance
// and rely on the admin-managed data as the single source of truth.

$data = null; // Initialize variable

// 1. Fetch product data directly from the database table.
$stmt = $pdo->prepare("SELECT product_data FROM bookeo_products_cache WHERE id = 1 LIMIT 1");
$stmt->execute();
$cacheRow = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cacheRow && !empty($cacheRow['product_data'])) {
    // 2. Decode the stored JSON data.
    $decodedData = json_decode($cacheRow['product_data'], true);

    // 3. Check if the JSON is valid and has the expected 'data' key.
    if ($decodedData && isset($decodedData['data'])) {
        $data = $decodedData;
    }
}

// 4. Fail-safe: Ensure $data['data'] is an array to prevent errors in the loop below.
if (!isset($data['data']) || !is_array($data['data'])) {
    $data = ['data' => []]; // Set a default empty structure.
}

// Collect product IDs for the relevant facility rentals (products 13 and 14).
$productIds = [];
$count = 0;
foreach ($data['data'] as $product) {
   if ($count >= 13 && $count <= 14) {
    $productIds[] = htmlspecialchars($product['productCode'] ?? '');
   }
    $count++;
}
// Convert product IDs to JSON for the hidden field
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
    
$stmt = $pdo->prepare("SELECT * FROM tbl_facility_rental_game 
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
                    echo '<img src="' . htmlspecialchars($imageUrl) . '" loading="lazy"  decoding="async"  alt="' . $name . '" />';
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
                <div class="party_packages_card_new_desc">
<?php

$cardSubtitleRaw = $package['card_subtitle'] ?? ''; // $package = tbl_facility_rental_game row


$lines = preg_split("/\r\n|\n|\r/", $cardSubtitleRaw, -1, PREG_SPLIT_NO_EMPTY);


$firstLine = array_shift($lines);
?>
<p class="card-subtitle"><?php echo htmlspecialchars($firstLine); ?></p>

<?php if(!empty($lines)): ?>
    <ul>
        <?php foreach($lines as $line):
            $line = trim($line);
           
            if(isset($line[0]) && $line[0] === '*') {
                $line = ltrim($line, '* ');
                echo '<li>' . htmlspecialchars($line) . '</li>';
            }
        endforeach; ?>
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
    
    
      <!-- === Video Modal ====== -->
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
    <div class="modal fade blur-modal videoModal_z " id="videoModal<?php echo htmlspecialchars($productCode); ?>" tabindex="-1" >
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="videoModalLabel"><?= $name ?></h5>
                    <button type="button" class="btn btn-sm close-btn" data-bs-dismiss="modal" aria-label="Close" >X</button>
                </div>
                <div class="modal-body p-0">
                    <div class="ratio ratio-16x9">
                        <?php if (!empty($videoPath)) { ?>
                            <video class="modal-video"  controls>
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
                        <img src="admin/uploads/<?= htmlspecialchars($package['thumbnail']) ?>" loading="lazy"  decoding="async" 
                             class="img-fluid rounded" alt="<?= htmlspecialchars($package['title']) ?>">
                    </div>

                    <div class="col-md-7">
                        <div class="modal_Beginner_badel">
                            <p>Beginner</p>
                        </div>

                        <div class="game-stats d-flex flex-wrap gap-3 mb-3">
                            <div class="stat-box">
                                <span class="stat-label">Guests</span>
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
<?php endif; ?>
    
    
    
    
    
    
    
    <?php
}
    $count++;
} // end foreach
?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.videoModal_z').forEach(function(modal) {

        // Modal OPEN → video play
        modal.addEventListener('shown.bs.modal', function () {
            const video = modal.querySelector('video');
            if (video) {
                video.currentTime = 0;
                video.play();
            }
        });

        // Modal CLOSE → video stop
        modal.addEventListener('hidden.bs.modal', function () {
            const video = modal.querySelector('video');
            if (video) {
                video.pause();
                video.currentTime = 0;
            }
        });

    });

});
</script>

