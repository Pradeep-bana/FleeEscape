<style>
    .continue_next_step_party.disabled {
    opacity: 0.6;
    pointer-events: none;
    cursor: not-allowed;
}
</style>

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
  $onOffOptions = $product['onOffOptions'] ?? [];
  $onOffJson = htmlspecialchars(json_encode($onOffOptions));
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
    <div class="col-md-4 col-sm-12 visible-card">
        <div class="booking_card party_packages_card_new">
            <div>
            <div class="booking_card_img">
                <?php 
                // --- 1. PREPARE THE DATA (Do this before printing HTML) ---
                
                // Image
                if (!empty($imageUrl)) {
                    echo '<img src="' . htmlspecialchars($imageUrl) . '" loading="lazy" decoding="async" alt="' . $name . '" />';
                }
            
                // Time
                $hours = $product['duration']['hours'] ?? 0;
                $minutes = $product['duration']['minutes'] ?? 0;
                $timeString = "{$hours} HOURS";
                if ($minutes > 0) { $timeString .= ", {$minutes} Minutes"; }
            
                // FETCH STRIKETHROUGH (From Database)
                $strikethrough_price = 0;
                $stmt1 = $pdo->prepare("SELECT strikethrough_price FROM tbl_party_packages WHERE product_id = :product_id");
                $stmt1->execute([':product_id' => $productCode]);
                $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
                if ($row1) {
                    $strikethrough_price = floatval($row1['strikethrough_price']); 
                }
            
                // GET SELLING PRICE (From API - Lowest Price)
                $selling_price = 0;
                if (!empty($product['defaultRates'])) {
                    $selling_price = $product['defaultRates'][0]['price']['amount']; 
                    foreach ($product['defaultRates'] as $rate) {
                        if ($rate['price']['amount'] < $selling_price) {
                            $selling_price = $rate['price']['amount'];
                        }
                    }
                }
            
                // CALCULATE PERCENTAGE
                $discountPercent = 0;
                if ($strikethrough_price > 0 && $selling_price > 0 && $selling_price < $strikethrough_price) {
                    $discountPercent = ceil((($strikethrough_price - $selling_price) / $strikethrough_price) * 100);
                }
                ?>
            
                <!-- --- 2. LAYOUT ROW 1: DURATION --- -->
                <div class="booking_card_time_and_price">
                    <p><i class="fa-solid fa-clock"></i> <?= $timeString ?></p>
                    <?php if ($discountPercent > 0): ?>
                        <p class="discount-badge_lat_m"><?= $discountPercent ?>% OFF</p>
                    <?php endif; ?>
                </div>
            
                <!-- --- 4. OVERLAY: TITLE AND PRICE --- -->
                <div class="booking_card_overlay">
                    <h5><?php echo $name; ?></h5>
                    <p><?php echo $shortDescription; ?></p>
                    
                    <div class="player-count-display d-flex align-items-center">
                        <div>
                            <!-- PRICE LOGIC -->
                            <p class="Last_Minute_Deals_card_price">
                                <strong>$<?= $selling_price ?> </strong>
                                <?php if ($strikethrough_price > $selling_price): ?>
                                    <del>$<?= $strikethrough_price ?></del>
                                <?php endif; ?>
                            </p>
                            
                            <!-- HIDDEN INPUTS (Essential for booking functionality) -->
                            <input type="hidden" id="price-<?php echo $productCode; ?>" name="price-<?php echo $productCode; ?>" value="<?php echo htmlspecialchars($selling_price); ?>">
                            <input type="hidden" id="onoff-<?php echo $productCode; ?>" value='<?php echo $onOffJson; ?>'>
                        </div>
            
                        <!-- BUTTONS -->
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
                       
                        
                        <?php 
                        $playersCount = null;

if (preg_match('/Up to\s+(\d+)\s+players/i', $name, $matches)) {
    $playersCount = (int)$matches[1];
}


if ($playersCount) {
    echo "<p><strong> <img class='palyear_tem_img' src='./assets/images/fleeescape_img/teampay.png' alt='img loading' loading='lazy'  decoding='async' ></strong> $playersCount Players</p>";
} else {
    echo "<p> Not Found</p>";
}
$addonGuestPrice=0;
 $stmt = $pdo->prepare("
        SELECT addon_guest_price 
        FROM tbl_party_packages 
        WHERE product_id = :product_id 
        LIMIT 1
    ");
    $stmt->execute([':product_id' => $productCode]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    
 if ($row && isset($row['addon_guest_price'])) {
    $addonGuestPrice = $row['addon_guest_price'];
}
     ?>
                    </span>
                    
                   <input type="hidden" id="players-<?php echo $productCode; ?>" 
       value="<?php echo $playersCount; ?>">
       
           <input type="hidden" id="per-guest-price-<?php echo $productCode; ?>" 
       value="<?php echo $addonGuestPrice; ?>">
                    <!--<span class="player-value"><?php echo $players; ?> GUESTS</span>-->
                </div>
               <?php
// Fetch subtitle + list from DB
$stmt = $pdo->prepare("
    SELECT card_subtitle 
    FROM tbl_party_packages 
    WHERE product_id = :product_id 
    LIMIT 1
");
$stmt->execute([':product_id' => $productCode]);
$subtitleRow = $stmt->fetch(PDO::FETCH_ASSOC);

$cardSubtitleRaw = $subtitleRow['card_subtitle'] ?? '';

// Split lines
$lines = preg_split("/\r\n|\n|\r/", $cardSubtitleRaw);
$firstLine = trim(array_shift($lines));

// Lines starting with * become <li>
$liItems = [];
foreach ($lines as $line) {
    $line = trim($line);
    if (str_starts_with($line, '*')) {
        $liItems[] = htmlspecialchars(ltrim($line, '* '));
    }
}
?>

<div class="party_packages_card_new_desc">
    <?php if (!empty($firstLine)): ?>
        <p class="card-subtitle"><?php echo htmlspecialchars($firstLine); ?></p>
    <?php endif; ?>

    <?php if (!empty($liItems)): ?>
        <ul>
            <?php foreach ($liItems as $item): ?>
                <li><?php echo $item; ?></li>
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
                    <h5 class="guest-heading mb-2">Add Additional Guests</h5>
                    <div class="d-flex justify-content-between align-items-center">
                     <div class="guest-count-wrapper"
     data-product-id="<?php echo $productCode; ?>"
     data-addon-price="<?php echo $addonGuestPrice; ?>">

    <button type="button" class="guest-btn guest-minus-btn">-</button>
    <span id="guest-count-display-<?php echo $productCode; ?>" class="guest-value">0</span>
    <button type="button" class="guest-btn guest-plus-btn">+</button>
</div>

<p class="m-0">
    <i class="fa-solid fa-dollar-sign"></i>
    <span id="extra-price-<?php echo $productCode; ?>">0</span>
</p>

                    </div>
                   
                    <!--<div class="next-button-wrapper">-->
                 
                    <!--</div>-->
                </div>
            </div>
            </div>
             <div class="next-button-wrapper">
                        <!--<a href="#" class="bg_bnt_custom" data-bs-toggle="modal"-->
                        <!--    data-bs-target="#partymodalform">CONTINUE </a>-->
                           <?php echo '<button  class="continueBtn bg_bnt_custom  continue_next_step_party disabled" id="continueBtn-' . $productCode . '"  data-game-id="' . $productCode . '"  data-game-name="' . htmlspecialchars($name) . '" disabled >Continue</button>'; ?> 
                    </div>
        </div>
    </div>
    
    
  <!-- === Video Modal ====== -->
<?php
if (!empty($productCode)) {

    include('admin/db.php');

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
                    <h5 class="modal-title" id="videoModalLabel"><?= !empty($name ?? null) ? $name : 'Watch Trailer'; ?></h5>
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
?>
  
  <?php
include('admin/db.php');

// Get product ID from URL or request (example: ?product_id=3)


$stmt = $pdo->prepare("SELECT id, product_id, title, slug, price, duration, players, thumbnail, bottom_heading, video, description, strikethrough_price 
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

/* Style the dropdown list options so they are readable when opened */
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
                        
                        fetch("apply_code.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/x-www-form-urlencoded" }
                        })
                        .then(() => {
                            // 3. After Bookeo updates the hold, reload the cart view
                            if (typeof loadCart === "function") loadCart();
                        });

                        // ✅ Move to next step
                          window.location.href = "<?= BASE_URL ?>booking?add-ons-";
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
document.addEventListener("DOMContentLoaded", function() {
    // --- CONFIG & LIBS ---
    const {
        DateTime
    } = luxon;
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

    // --- SLOT FETCHING FOR MULTIPLE PRODUCTS ---
    function fetchSlotsForProducts(productIds, rawDate, allowAutoNextDay = true) {
        if (!productIds.length || !rawDate) {
            console.log(`fetchSlotsForProducts: Invalid input, productIds=${productIds}, rawDate=${rawDate}`);
            return;
        }

        const ajaxFn = () => {
            const deferred = $.Deferred();
            trackRequestStart();

            // Show loading for each product's container
            productIds.forEach(id => {
                const $container = $('#timeSlots-' + id);
                if ($container.length) $container.html(
                    '<div class="time_slots_loader">Loading slots...</div>');
            });

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
            const current = raw && DateTime.fromISO(raw, {
                    zone: LA_ZONE
                }).isValid ?
                DateTime.fromISO(raw, {
                    zone: LA_ZONE
                }).startOf('day') :
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
        console.log(`setDateAll: Setting date to ${rawDate}`);
        $('.custom-datepicker_input').each(function() {
            const $input = $(this);
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
        if ($input.hasClass('flatpickr-mobile')) {
            return;
        }

        const productId = $input.attr('data-product') || $input.data('product');
        if (!productId) return;

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
            monthSelectorType: "dropdown",

            onReady: (selectedDates, dateStr, instance) => {
                const calendar = instance.calendarContainer;

                // HIDE default year input
                const yearInput = calendar.querySelector(".numInputWrapper");
                if (yearInput) yearInput.style.display = "none";

                // CREATE CUSTOM YEAR DROPDOWN
                if (!calendar.querySelector(".flatpickr-year-dropdown")) {
                    const yearSelect = document.createElement("select");
                    // Add month dropdown class for same style
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

                // Remove any flatpickr-mobile duplicates
                const duplicates = document.querySelectorAll('.flatpickr-mobile');
                duplicates.forEach(inp => inp.remove());

                // Ensure input is visible
                $input.attr('type', 'text')
                      .removeClass('d-none')
                      .css({
                          display: 'block',
                          visibility: 'visible',
                          opacity: '1'
                      });

                $input.val(fmtDisplay(today));
            },

            onYearChange: (selectedDates, dateStr, instance) => {
                const yearSelect = instance.calendarContainer.querySelector(".flatpickr-year-dropdown");
                if (yearSelect) yearSelect.value = instance.currentYear || new Date().getFullYear();
            },

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

    // Fetch slots for products in this container immediately after initializing datepickers
    const productIds = container.find('.custom-datepicker_input')
    .map(function() {
        return $(this).data('product');
    })
    .get();
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
        if (!priceEl) return {
            min: 0,
            max: 0
        };
        const text = priceEl.textContent;
        const matches = text.match(/\d+(?:\.\d+)?/g) || [];
        const uniquePrices = [...new Set(matches.map(Number))].sort((a, b) => a - b);
        return {
            min: uniquePrices[0] || 0,
            max: uniquePrices[uniquePrices.length - 1] || 0
        };
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
        const {
            min,
            max
        } = getProductPrices(productCode);
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
        const current = DateTime.fromISO(raw, {
                zone: LA_ZONE
            }).isValid ?
            DateTime.fromISO(raw, {
                zone: LA_ZONE
            }).startOf('day') :
            today;
        const diffDays = current.diff(today, 'days').days;
        console.log(
            `Prev button clicked: rawdate=${raw}, current=${current.toISODate()}, today=${today.toISODate()}, diffDays=${diffDays}`
            );
        if (diffDays > 0) {
            const newDate = current.minus({
                days: 1
            });
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
        const current = DateTime.fromISO(raw, {
                zone: LA_ZONE
            }).isValid ?
            DateTime.fromISO(raw, {
                zone: LA_ZONE
            }).startOf('day') :
            today;
        const newDate = current.plus({
            days: 1
        });
        console.log(
            `Next button clicked: rawdate=${raw}, current=${current.toISODate()}, setting to ${newDate.toISODate()}`
            );
        setDateAll(newDate);
    });

    initTabContent($(document));
});
</script>
