<?php
session_start();

// Enable PHP error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('link.php');
include("admin/db.php");

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
// This handles cases where the database record is empty or the JSON is corrupt.
if (!isset($data['data']) || !is_array($data['data'])) {
    $data = ['data' => []]; // Set a default empty structure.
    // Optional: Log an error if the database data is missing or invalid.
    // error_log("Critical: Product data from 'bookeo_products_cache' is missing or corrupt.");
}


// Collect product IDs for the first 6 products, as required by this page
$productIds = [];
$count = 0;
foreach ($data['data'] as $product) {
    if ($count >= 6) break; // Stop after the 6th product
    $productIds[] = htmlspecialchars($product['productCode'] ?? '');
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
    if ($count >= 6) break;

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
?>
    <div class="col-md-6 col-sm-12">
        <div class="booking_card">
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
                            $hours = isset($duration['hours']) ? (int)$duration['hours'] : 0;
                            $minutes = isset($duration['minutes']) ? (int)$duration['minutes'] : 0;
                            $totalMinutes = ($hours * 60) + $minutes;
                            echo $totalMinutes . " minutes";
                        ?>
                    </p>
                    <p id="price-<?php echo $productCode; ?>"><?php echo $price; ?>/Guest</p>
                </div>
                <div class="booking_card_overlay">
                    <h5><?php echo $name; ?></h5>
                    <p><?php echo $shortDescription; ?></p>
                    <div class="player-count-display d-flex align-items-center">
                        <div>
                            <span class="player-label">
                                <img class="palyear_tem_img" src="./assets/images/fleeescape_img/teampay.png" alt="" loading="lazy"  decoding="async" >
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
                    <button class="custom-date_arrow prev-date" style="visibility: hidden;">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    <input type="text" class="custom-datepicker_input" data-product="<?php echo $productCode; ?>">
                    <button class="custom-date_arrow next-date">
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div> 

                <div class="time_slots" id="timeSlots-<?php echo $productCode; ?>"></div>
            <?php  if ($productCode == '41551F9C679173BC114D28') {
    ?>
    <div class="View_All_Dates_tag">
        <a href="<?php echo $link; ?>the-lift-collection#Book_one_single_pr">View All Dates</a>
    </div>
    <?php
} elseif ($productCode == '41551PFXF3K14F91D8FABB') {
    ?>
    <div class="View_All_Dates_tag">
        <a href="<?php echo $link; ?>ice-walker-got-collection#Book_one_single_pr">View All Dates</a>
    </div>
    <?php
}

elseif ($productCode == '41551HE99XR14F91DF96B0') {
    ?>
    <div class="View_All_Dates_tag">
        <a href="<?php echo $link; ?>prison-escape-collection#Book_one_single_pr">View All Dates</a>
    </div>
    <?php
}

elseif ($productCode == '41551XJM6F314F91E1CD68') {
    ?>
    <div class="View_All_Dates_tag">
        <a href="<?php echo $link; ?>steampunk-submarine-collection#Book_one_single_pr">View All Dates</a>
    </div>
    <?php
}


elseif ($productCode == '41551Y4PM9614F91DD1BC6') {
    ?>
    <div class="View_All_Dates_tag">
        <a href="<?php echo $link; ?>museum-heist-collection#Book_one_single_pr">View All Dates</a>
    </div>
    <?php
}

elseif ($productCode == '415516JNCHJ14F91D5A806') {
    ?>
    <div class="View_All_Dates_tag">
        <a href="<?php echo $link; ?>ancient-egypt-collection#Book_one_single_pr">View All Dates</a>
    </div>
    <?php
}
?>
                <div class="guest_and_button">
                    <h5 class="guest-heading mb-2">Select Guests</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="guest-count-wrapper">
                            <button type="button" class="guest-btn minus-btn">-</button>
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
             <div class="next-button-wrapper custom_scroll" >
                        <?php echo '<button 
                            class="continueBtn bg_bnt_custom disabled continue_nex_step custom_scroll" 
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
   
include('admin/db.php'); 
    // Fetch the trailer video safely
    $stmt = $pdo->prepare("SELECT trailer_video,title FROM tbl_service WHERE product_id = :product_id LIMIT 1");
    $stmt->execute([':product_id' => $productCode]);
    $videoData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($videoData && !empty($videoData['trailer_video'])) {
        $videoPath = 'admin/uploads/' . $videoData['trailer_video'];
    } else {
        $videoPath = ''; // no video found
    }
?>
    <div class="modal fade blur-modal videoModal_z" id="videoModal<?php echo $productCode; ?>" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true" >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="videoModalLabel"><?php echo htmlspecialchars($videoData['title']); ?></h5>
            <button type="button" class="btn btn-sm close-btn" data-bs-dismiss="modal" aria-label="Close"
             >X</button>
          </div>
          <div class="modal-body p-0">
            <div class="ratio ratio-16x9">
              <?php if (!empty($videoPath)) { ?>
               <video class="modal-video"  controls >
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

 
<!-- === info modal ====== -->
<?php
if (!empty($productCode)) {
    include('admin/db.php');

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
                            <p>Hurry! This game has been booked 40+ times recently.</p>
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
                                            <img src="<?php echo $imgPath; ?>" class="d-block w-100" loading="lazy"  decoding="async"  alt="Gallery Image">
                                        </div>
                                        <?php
                                        $activeSet = true;
                                    }
                                } else {
                                    // Fallback if no gallery found
                                    ?>
                                    <div class="carousel-item active">
                                        <img src="<?php echo htmlspecialchars($cover_photo); ?>" loading="lazy"  decoding="async"  class="d-block w-100" alt="Cover Image">
                                    </div>
                                    <div class="carousel-item">
                                        <img src="<?php echo htmlspecialchars($thumbnail); ?>" loading="lazy"  decoding="async"  class="d-block w-100" alt="Thumbnail">
                                    </div>
                                <?php } ?>
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
                                        <span class="stat-value">$ <?php echo htmlspecialchars($price); ?> / Player</span>
                                    </div>
                                    <div class="stat-box">
                                        <span class="stat-label"><i class="fas fa-vector-square me-1"></i> Layout</span>
                                        <span class="stat-value"><?php echo htmlspecialchars($layout); ?></span>
                                    </div>
                                </div>

                               <div class="modal_info_p">
    <?php
    echo nl2br(strip_tags($description1, '<p><br><b><strong><i><em><ul><ol><li><span>'));
    ?>

    <?php if (!empty($description2)) { ?>
        <?php
        echo nl2br(strip_tags($description2, '<p><br><b><strong><i><em><ul><ol><li><span>'));
        ?>
    <?php } ?>
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





<script>
    // custom scroll
// custom scroll

document.addEventListener("DOMContentLoaded", function () {
    // All buttons / links jinke click par scroll karna hai
    const autoScrollButtons = document.querySelectorAll(
        ".custom_scroll"
    );

    // Target section (pehle jo milega wahi use hoga)
    const targetEl =
        document.getElementById("stepContents") ||
        document.getElementById("custom_scroll");

    autoScrollButtons.forEach(button => {
        button.addEventListener("click", function (e) {
            if (!targetEl) return;

            // Agar <a> tag hai to redirect rokne ke liye
            e.preventDefault();

            // Fixed header height (agar hai)
            const yOffset = -120; // header ke according adjust karein
            const y =
                targetEl.getBoundingClientRect().top +
                window.pageYOffset +
                yOffset;

            window.scrollTo({
                top: y,
                behavior: "smooth"
            });
        });
    });
});
</script>



