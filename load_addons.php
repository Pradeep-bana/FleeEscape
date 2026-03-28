<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("admin/db.php");
require_once("addon_pricing_helper.php");

/* ----------------------------------------------
   1. GET PRODUCT CACHE
------------------------------------------------*/
$products = flee_get_cached_products_data($pdo);
if (empty($products)) {
    echo "No product cache found.";
    exit;
}

/* ----------------------------------------------
   2. SERVICE ADD-ONS FOR ESCAPE ROOMS
------------------------------------------------*/
$serviceStmt = $pdo->prepare("SELECT addon_ids, addon_titles, addon_prices, addon_images, addon_strikethrough_prices FROM tbl_service");
$serviceStmt->execute();
$services = $serviceStmt->fetchAll(PDO::FETCH_ASSOC);

$addonLookup = []; // ID → price,image (escape)
foreach ($services as $srv) {
    $ids     = json_decode($srv['addon_ids'] ?? '[]', true); 
    $titles  = json_decode($srv['addon_titles'], true);
    $prices  = json_decode($srv['addon_prices'], true);
    $images  = json_decode($srv['addon_images'], true);
    $strikes = json_decode($srv['addon_strikethrough_prices'], true);

    if (!is_array($ids)) continue;

    foreach ($ids as $i => $id) {
        $cleanID = trim($id);
        $addonLookup[$cleanID] = [
            "title" => $titles[$i] ?? "",
            "fallback_price" => $prices[$i] ?? 0,
            "image" => $images[$i] ?? "",
            "strikethrough_price" => $strikes[$i] ?? 0 
        ];
    }
}

/* ----------------------------------------------
   3. PARTY PACKAGE ADD-ON TABLE (UPDATED)
------------------------------------------------*/
// 1. We now SELECT 'addon_ids'
$partyStmt = $pdo->prepare("
    SELECT product_id, addon_ids, addon_titles, addon_prices, addon_images, addon_strikethrough_prices 
    FROM tbl_party_packages
");
$partyStmt->execute();
$partyRows = $partyStmt->fetchAll(PDO::FETCH_ASSOC);

$partyLookup = []; // product_id → data
foreach ($partyRows as $pr) {

    // 2. Decode the IDs
    $addon_ids     = json_decode($pr['addon_ids'] ?? '[]', true);
    $addon_titles  = json_decode($pr['addon_titles'], true);
    $addon_prices  = json_decode($pr['addon_prices'], true);
    $addon_images  = json_decode($pr['addon_images'], true);
    $addon_strikes = json_decode($pr['addon_strikethrough_prices'] ?? '[]', true);

    $temp = [];

    // 3. Map using ID as the key
    if (is_array($addon_ids)) {
        foreach ($addon_ids as $i => $id) {
            $cleanID = trim($id);
            $temp[$cleanID] = [
                "title" => $addon_titles[$i] ?? "",
                "fallback_price" => $addon_prices[$i] ?? 0,
                "image" => $addon_images[$i] ?? "",
                "strikethrough_price" => $addon_strikes[$i] ?? 0
            ];
        }
    }

    $partyLookup[$pr['product_id']] = $temp; 
}

/* ----------------------------------------------
   4. FETCH CART ITEMS
------------------------------------------------*/
$cartStmt = $pdo->prepare("SELECT * FROM tbl_carts WHERE session_id = :sid");
$cartStmt->execute([':sid' => session_id()]);
$cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

$cartMap = [];
foreach ($cartItems as $c) {
    $cartMap[$c['game_id']] = $c;
}
$cartGameIds = array_keys($cartMap);

/* ----------------------------------------------
   5. RENDER ADD-ONS
------------------------------------------------*/
foreach ($products as $product) {

    if (!isset($product['productCode'])) continue;
    $productCode = $product['productCode'];

    if (!in_array($productCode, $cartGameIds)) continue;

    $cat = strtolower(trim($cartMap[$productCode]['cat'] ?? ""));
    $slot = $cartMap[$productCode]['slot'] ?? '';
    $defaultImage = $product['images'][0]['url'] ?? "";


    /* ================================================================
       PARTY PACKAGE ADD-ONS (UPDATED TO USE IDs)
    ================================================================*/
    if ($cat === "party-package" && isset($partyLookup[$productCode])) {

        $partyAddons = $partyLookup[$productCode];
 
        // LOOP through Bookeo onOffOptions
        if (isset($product["onOffOptions"]) && is_array($product["onOffOptions"])) {

            foreach ($product["onOffOptions"] as $opt) {

                $opt_id   = $opt["id"] ?? "";
                $opt_name = $opt["name"] ?? "";
                $opt_desc = strip_tags($opt["description"] ?? "");

                // Default values
                $addonPrice = flee_get_bookeo_option_price($product, $opt_id, $slot);
                $addonImage = $defaultImage;
                $strkPrice = 0;
                $percentLabel = "";

                // CHECK IF THIS ID EXISTS IN OUR DB LOOKUP
                if (isset($partyAddons[$opt_id])) {
                    $strkPrice = $partyAddons[$opt_id]["strikethrough_price"] ?? 0;
                    if (!empty($partyAddons[$opt_id]["image"])) {
                        $addonImage = "admin/uploads/" . $partyAddons[$opt_id]["image"];
                    }
                    // if (!empty($partyAddons[$opt_id]["title"])) {
                    //     $opt_name = $partyAddons[$opt_id]["title"] ?? "";
                    // }
                    if ($addonPrice <= 0 && !empty($partyAddons[$opt_id]["fallback_price"])) {
                        $addonPrice = (float)$partyAddons[$opt_id]["fallback_price"];
                    }
                }

                // If price is 0 or not found, skip
                if ($addonPrice <= 0) {
                    continue; 
                }

                // Calculate Percentage
                if ($strkPrice > $addonPrice && $strkPrice > 0) {
                    $percent = round((($strkPrice - $addonPrice) / $strkPrice) * 100);
                    // Percentage Logic
                    $percentLabel = $percent . "% OFF "; 
                }

                // Render
                echo '
                <div class="col-md-6" data-aos="zoom-in">
                    <div class="add_on_booking_card p-3 h-100 flex-column">
                        <img src="' . $addonImage . '" alt="' . htmlspecialchars($opt_name) . '"  loading="lazy" decoding="async">

                        <div class="card-body flex-column">
                            <!-- TITLE WITH PERCENTAGE -->
                            <h5 class="add_on_booking_card_title">' . htmlspecialchars($opt_name) . '</h5>
                            
                            <h5 class="opt_id d-none">' . $opt_id . '</h5>
                            <h5 class="game_id d-none">' . $productCode . '</h5>

                            <div class="price-box" style="font-size: 16px;">
                                <span class="discounted-price" style="font-weight:600; color:#00d4ff;">
                                    $' . $addonPrice . '
                                </span>';
                
                // STRIKETHROUGH PRICE
                if ($strkPrice > $addonPrice && $strkPrice > 0) {
                    echo '<del class="discounted-price" style="font-weight:600; color:#00d4ff;">
                            $' . $strkPrice . '
                          </del>';
                }

                echo '      </div>

                            <p style="color:#aaa;">' . htmlspecialchars($opt_desc) . '</p>

                            <div class="add_on_booking_quantity_controls">
                                <select  class="addon-dropdown Boo_Prison_Escape_select" data-opt-id="' . $opt_id . '" style="display:none">
                                    <option value="1">1</option>
                                </select>
                            </div>

                            <div class="add-summary-btn_wraper">
                                <button class="bg_bnt_custom add-addon-btn" id="add-summary-btn-' . $opt_id . '" style=" margin-top:10px;">
                                    Add
                                </button>
                            </div>
                        </div>
                    </div>
                </div>';
            }
        }

        continue;
    }


    /* ================================================================
       ESCAPE ROOM ADD-ONS (UNCHANGED)
    ================================================================*/
    if (!isset($product['numberOptions'])) continue;

    foreach ($product['numberOptions'] as $opt) {

        $opt_id   = $opt["id"] ?? "";
        $opt_name = $opt["name"] ?? "";
        $opt_desc = strip_tags($opt["description"] ?? "");

        $min = intval($opt["minValue"] ?? 0);
        $max = intval($opt["maxValue"] ?? 0);

        $addonPrice = flee_get_bookeo_option_price($product, $opt_id, $slot);
        $strkPrice = 0;
        $addonImage = $defaultImage;
        $percentLabel = "";

        // Match by ID
        if (isset($addonLookup[$opt_id])) {
            $strkPrice = $addonLookup[$opt_id]["strikethrough_price"] ?? 0;
            if (!empty($addonLookup[$opt_id]["image"])) {
                $addonImage = "admin/uploads/" . $addonLookup[$opt_id]["image"];
            }
            // if (!empty($addonLookup[$opt_id]["title"])) {
            //     $opt_name = $addonLookup[$opt_id]["title"] ?? "";
            // }
            if ($addonPrice <= 0 && !empty($addonLookup[$opt_id]["fallback_price"])) {
                $addonPrice = (float)$addonLookup[$opt_id]["fallback_price"];
            }
        }
        
        if($strkPrice > $addonPrice && $strkPrice > 0){
            $percent = round((($strkPrice-$addonPrice)/$strkPrice)*100);
            $percentLabel = $percent . "% OFF ";
        }
        
        if ($addonPrice <= 0) {
            continue;
        }

        echo '
        <div class="col-md-6" data-aos="zoom-in">
            <div class="add_on_booking_card p-3 h-100 flex-column">

                <img src="' . $addonImage . '" alt="' . htmlspecialchars($opt_name) . '"  loading="lazy" decoding="async">
                <div class="card-body flex-column">

                    <h5 class="add_on_booking_card_title">' . htmlspecialchars($opt_name) . '</h5>
                    <h5 class="opt_id d-none">' . $opt_id . '</h5>
                    <h5 class="game_id d-none">' . $productCode . '</h5>

                    <div class="price-box">
                        <span class="discounted-price" style="font-weight:600; color:#00d4ff;">
                            $' . $addonPrice . '
                        </span>
                        <del class="discounted-price" style="font-weight:600; color:#00d4ff;">
                            $' . $strkPrice . '
                        </del>
                    </div>

                    <p style="color:#aaa;">' . htmlspecialchars($opt_desc) . '</p>

                    <div class="add_on_booking_quantity_controls">
                        <select class="addon-dropdown Boo_Prison_Escape_select" data-opt-id="' . $opt_id . '">
                            <option value="">Select Quantity</option>';

        for ($i = $min; $i <= $max; $i++) {
            echo '<option value="' . $i . '">' . $i . '</option>';
        }

        echo '
                        </select>
                    </div>

                    <div class="add-summary-btn_wraper">
                        <button class="bg_bnt_custom add-addon-btn" id="add-summary-btn-' . $opt_id . '" style="display:none; margin-top:10px;">
                            Add
                        </button>
                    </div>
                </div>
            </div>
        </div>';
    }
}
?>
