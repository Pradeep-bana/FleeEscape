<style>
    .continue_next_step_party.disabled {
    opacity: 0.6;
    pointer-events: none;
    cursor: not-allowed;
}
</style>

<?php
// Enable PHP error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('link.php');
include("admin/db.php");

// --- DATA FETCHING (DATABASE ONLY) ---
// The product data is now managed via the admin panel and stored in the database.
// The live API call and caching logic have been removed to improve performance.

$data = null; // Initialize variable

// 1. Fetch product data directly from the database table.
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
    // Optional: Log error. For the user, we will fallback to an empty array.
    error_log("Database error in party-packages: " . $e->getMessage());
}

// 3. Fail-safe: Ensure $data['data'] is an array to prevent errors in the loop below.
if (!isset($data['data']) || !is_array($data['data'])) {
    $data = ['data' => []]; // Set a default empty structure.
}

// 4. Collect product IDs for the relevant party packages (products 6 through 10).
$productIds = [];
$count = 0;
foreach ($data['data'] as $product) {
   if ($count >= 6 && $count <= 10) {
    $productIds[] = htmlspecialchars($product['productCode'] ?? '');
   }
    $count++;
}

// 5. Convert product IDs to JSON for the hidden field.
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
        // Limit logic from your original code
        if ($count >= 6 && $count <= 10) {
            
            // --- 1. INITIALIZE VARIABLES & DATA PREP ---
            $onOffOptions = $product['onOffOptions'] ?? [];
            $onOffJson = htmlspecialchars(json_encode($onOffOptions));
            $name = htmlspecialchars($product['name'] ?? '');
            $productCode = htmlspecialchars($product['productCode'] ?? '');
            $desc = trim($product['description'] ?? '');

            // Image handling
            $imageUrl = '';
            if (!empty($product['images'][0]['url'])) {
                $imageUrl = $product['images'][0]['url'];
            }

            // Duration String Logic
            $duration = $product['duration'];
            $hours = $duration['hours'] ?? 0;
            $minutes = $duration['minutes'] ?? 0;
            $timeString = "{$hours} HOURS";
            if ($minutes > 0) { $timeString .= ", {$minutes} Minutes"; }

            // Description Parsing (Split lines)
            $lines = preg_split('/\r\n|\r|\n/', $desc);
            
            // Short Description
            $remainingText = implode(" ", array_slice($lines, 4));
            $remainingText = strip_tags($remainingText);
            $words = preg_split('/\s+/', $remainingText);
            $shortDescription = implode(" ", array_slice($words, 0, 13));
            if (count($words) > 13) { $shortDescription .= "..."; }

            // Parsing Players Count for display
            $playersCount = null;
            if (preg_match('/Up to\s+(\d+)\s+players/i', $name, $matches)) {
                $playersCount = (int)$matches[1];
            }

            // --- 2. PRICE & DATABASE LOGIC (DONE ONCE) ---
            
            // A. Fetch Strikethrough & Addon Price from DB
            $strikethrough_price = 0;
            $addonGuestPrice = 0;
            
            // Single query to get both values
            $stmt = $pdo->prepare("SELECT strikethrough_price, addon_guest_price FROM tbl_party_packages WHERE product_id = :product_id LIMIT 1");
            $stmt->execute([':product_id' => $productCode]);
            $dbRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dbRow) {
                $strikethrough_price = isset($dbRow['strikethrough_price']) ? floatval($dbRow['strikethrough_price']) : 0;
                $addonGuestPrice = isset($dbRow['addon_guest_price']) ? floatval($dbRow['addon_guest_price']) : 0;
            }

            // B. Get Real Selling Price from API (Find Lowest)
            $selling_price = 0;
            $selling_price_display_text = ''; 

            if (!empty($product['defaultRates'])) {
                // Initialize with the first price found
                $selling_price = $product['defaultRates'][0]['price']['amount'];
                
                // Find absolute lowest price
                foreach ($product['defaultRates'] as $rate) {
                    if ($rate['price']['amount'] < $selling_price) {
                        $selling_price = $rate['price']['amount'];
                    }
                }
            }
            
            // C. Parse original text price range (from description) for display only
            $priceText = isset($lines[3]) ? preg_replace('/^.*?:\s*/', '', trim(strip_tags($lines[3]))) : '';
            if ($priceText) {
                preg_match_all('/\$\d+/', $priceText, $matches);
                if (count($matches[0]) >= 2) {
                    $priceText = $matches[0][0] . '-' . $matches[0][1];
                } elseif (count($matches[0]) === 1) {
                    $priceText = $matches[0][0];
                } else {
                    $priceText = '';
                }
            }

            // D. Calculate Discount Percentage
            $discountPercent = 0;
            if ($strikethrough_price > 0 && $selling_price > 0 && $selling_price < $strikethrough_price) {
                $discountPercent = ceil((($strikethrough_price - $selling_price) / $strikethrough_price) * 100);
            }
    ?>
    
    <!-- CARD HTML STARTS HERE -->
    <div class="col-md-6 col-sm-12 visible-card">
        <div class="booking_card party_packages_card_new">
            <div>
                <div class="booking_card_img">
                    <?php if (!empty($imageUrl)) { ?>
                        <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= $name ?>" loading="lazy" decoding="async" />
                    <?php } ?>

                    <!-- Row 1: Time & Price Range Text -->
                    <div class="booking_card_time_and_price">
                        <p><i class="fa-solid fa-clock"></i> <?= $timeString ?></p>
                        <p><?= $priceText ?></p> 
                    </div>
                    
                    <!-- Row 2: Discount Badge (Preserves Layout) -->
                    <div class="booking_card_time_and_price">
                        <p></p> <!-- Required for Flexbox alignment -->
                        <?php if ($discountPercent > 0) { ?>
                            <p class="discount-badge_lat_m"><?= $discountPercent ?>% OFF</p>
                        <?php } ?>
                    </div>

                    <!-- Overlay -->
                    <div class="booking_card_overlay">
                        <h5><?= $name; ?></h5>
                        <p><?= $shortDescription; ?></p>
                        
                        <div class="player-count-display d-flex align-items-center">
                            <div>
                                <!-- Updated Pricing Logic Display -->
                                <p class="Last_Minute_Deals_card_price">
                                    <strong>$<?= $selling_price ?> </strong>
                                    <?php if ($discountPercent > 0) { ?>
                                        <del>$<?= $strikethrough_price ?></del>
                                    <?php } ?>
                                </p>
                                
                                <input type="hidden" id="price-<?= $productCode; ?>" name="price-<?= $productCode; ?>" value="<?= htmlspecialchars($selling_price); ?>">
                                <input type="hidden" id="onoff-<?= $productCode; ?>" value='<?= $onOffJson; ?>'>
                            </div>
                            
                            <div class="icon_buttons_wrapper">
                                <div class="icon-button" data-bs-toggle="modal" data-bs-target="#liftInfoModal<?= htmlspecialchars($productCode); ?>">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span class="label">Learn more</span>
                                </div>
                                <div class="icon-button" data-bs-toggle="modal" data-bs-target="#videoModal<?= htmlspecialchars($productCode); ?>">
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
                            <?php if ($playersCount) { ?>
                                <p><strong> <img class='palyear_tem_img' src='./assets/images/fleeescape_img/teampay.png' alt='IMG loading' loading='lazy' decoding='async'></strong> <?= $playersCount ?> Players</p>
                            <?php } else { ?>
                                <p> Not Found</p>
                            <?php } ?>
                        </span>
                        
                        <input type="hidden" id="players-<?= $productCode; ?>" value="<?= $playersCount; ?>">
                        <input type="hidden" id="per-guest-price-<?= $productCode; ?>" value="<?= $addonGuestPrice; ?>">
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
                        <input type="text" class="custom-datepicker_input" data-product="<?= $productCode; ?>">
                        <button class="custom-date_arrow next-date">
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>

                    <div class="time_slots" id="timeSlots-<?= $productCode; ?>"></div>

                    <div class="guest_and_button">
                        <h5 class="guest-heading mb-2">Add Additional Guests</h5>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="guest-count-wrapper" data-product-id="<?= $productCode; ?>" data-addon-price="<?= $addonGuestPrice; ?>">
                                <button type="button" class="guest-btn guest-minus-btn">-</button>
                                <span id="guest-count-display-<?= $productCode; ?>" class="guest-value">0</span>
                                <button type="button" class="guest-btn guest-plus-btn">+</button>
                            </div>

                            <p class="m-0">
                                <i class="fa-solid fa-dollar-sign"></i>
                                <span id="extra-price-<?= $productCode; ?>">0</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="next-button-wrapper">
                <button class="continueBtn bg_bnt_custom continue_next_step_party disabled" id="continueBtn-<?= $productCode ?>" data-game-id="<?= $productCode ?>" data-game-name="<?= htmlspecialchars($name) ?>" disabled data-processing="false">Continue</button> 
            </div>
        </div>
    </div>
    
    <!-- === Video Modal Logic remains same === -->
    <?php
    // Inline fetch video again if needed (or optimized to use existing fetch)
    // NOTE: In the previous structure, you included this block *inside* the loop. 
    // It's cleaner to keep it here.
    if (!empty($productCode)) {
        // Reuse DB logic if you want, but for compatibility with your style, we keep the specific check:
        $videoPath = '';
        // We actually fetched the video row in our loop query at start, but didn't select 'video'.
        // To be safe and compatible with your old code logic, let's just grab it again cleanly.
         $stmtV = $pdo->prepare("SELECT title, video FROM tbl_party_packages WHERE product_id = :product_id LIMIT 1");
         $stmtV->execute([':product_id' => $productCode]);
         $videoRow = $stmtV->fetch(PDO::FETCH_ASSOC);

         if ($videoRow && !empty($videoRow['video'])) {
             $videoPath = 'admin/uploads/' . $videoRow['video'];
             $videoTitle = $videoRow['title'];
         } else {
             $videoTitle = "Watch Trailer";
         }
    ?>
        <div class="modal fade blur-modal videoModal_z " id="videoModal<?= htmlspecialchars($productCode); ?>" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true" >
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="videoModalLabel"><?= htmlspecialchars($videoTitle); ?></h5>
                        <button type="button" class="btn btn-sm close-btn" data-bs-dismiss="modal" aria-label="Close">X</button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="ratio ratio-16x9">
                            <?php if (!empty($videoPath)) { ?>
                                <video id="localVideo" controls>
                                    <source src="<?= htmlspecialchars($videoPath); ?>" type="video/mp4">
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
    <?php } ?>

    <!-- === Learn More Modal === -->
    <?php
    // Fetch full details for the Modal
    $stmtM = $pdo->prepare("SELECT * FROM tbl_party_packages WHERE product_id = :product_id LIMIT 1");
    $stmtM->execute([':product_id' => $productCode]);
    $package = $stmtM->fetch(PDO::FETCH_ASSOC);
    ?>

    <?php if ($package): ?>
    <div class="modal fade" id="liftInfoModal<?= htmlspecialchars($productCode); ?>" tabindex="-1" aria-labelledby="liftInfoModal" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content custom-modal">
                <div class="modal-header border-0" style="align-items: flex-start!important;">
                    <div class="info_modal_content" style="width: 90%;">
                        <h2 class="modal-title custom-heading"><?= htmlspecialchars($package['title']) ?></h2>
                    </div>
                    <button type="button" class="btn btn-sm close-btn" data-bs-dismiss="modal" aria-label="Close">X</button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5 text-center mb-3 mb-md-0">
                            <img src="admin/uploads/<?= htmlspecialchars($package['thumbnail']) ?>" loading="lazy" class="img-fluid rounded" alt="<?= htmlspecialchars($package['title']) ?>">
                        </div>

                        <div class="col-md-7">
                            <div class="game-stats d-flex flex-wrap gap-3 mb-3">
                                <div class="stat-box">
                                    <span class="stat-label">Players</span>
                                    <span class="stat-value"><?= htmlspecialchars($package['players']) ?></span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-label">Price</span>
                                    <span class="stat-value">$<?= htmlspecialchars($package['price']) ?></span>
                                </div>
                                <div class="stat-box">
                                    <span class="stat-label"><i class="fas fa-clock me-1"></i> Duration</span>
                                    <span class="stat-value"><?= htmlspecialchars($package['duration']) ?></span>
                                </div>
                            </div>

                            <div class="modal_info_p">
                                <p><?= nl2br(htmlspecialchars(mb_strlen($package['description']) > 300 ? mb_substr($package['description'], 0, 300) . '...' : $package['description'], ENT_QUOTES, 'UTF-8')) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="all_button_main_header text-end">
                        <?php if (!empty($package['video'])): ?>
                            <a style="border-radius: 30px!important" class="bg_bnt_custom bg_bnt_custom_tran" data-bs-toggle="modal" data-bs-target="#videoModal<?= htmlspecialchars($productCode); ?>"><i class="fa-solid fa-play m-2"></i> Watch Trailer</a>
                        <?php endif; ?>
                        <a style="border-radius: 30px!important" type="button" class="bg_bnt_custom" data-bs-dismiss="modal" aria-label="Close">OK</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php
        } // End count if
        $count++;
    } // end foreach
    ?>
</div>

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


