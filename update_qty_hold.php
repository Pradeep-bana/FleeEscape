<?php
// Prevent apply_code.php from starting a session if already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("admin/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_qty') {

    $gameId = $_POST['gameId'] ?? '';
    $eventId = $_POST['eventId'] ?? '';
    $qty    = (int)($_POST['qty'] ?? 1);
    $sid    = session_id();

    // ---------------------------------------------------------
    // 1. UPDATE LOCAL DATABASE (tbl_carts)
    // ---------------------------------------------------------
    // We need to update the local cart first so apply_code.php sees the new quantity.
    // We also need to update the base 'price' column based on tiered logic (2 vs 3+ guests)
    // to ensure the local database reflects the correct base unit price.
    
    // --- Fetch Cache for Pricing Logic ---
    $priceMin = 0;
    $priceMax = 0;
    
    // Fetch current item to get fallback price
    $stmt = $pdo->prepare("SELECT price FROM tbl_carts WHERE session_id=:sid AND event_id=:event_id");
    $stmt->execute([':sid' => $sid, ':event_id' => $eventId]);
    $currentItem = $stmt->fetch(PDO::FETCH_ASSOC);
    $fallbackPrice = $currentItem['price'] ?? 0;

    // Check Bookeo Cache for tiered pricing
    $cacheStmt = $pdo->prepare("SELECT product_data FROM bookeo_products_cache WHERE id = 1 LIMIT 1");
    $cacheStmt->execute();
    $cacheRow = $cacheStmt->fetch(PDO::FETCH_ASSOC);

    if ($cacheRow) {
        $cachedData = json_decode($cacheRow['product_data'], true);
        if ($cachedData && isset($cachedData['data']) && is_array($cachedData['data'])) {
            foreach ($cachedData['data'] as $product) {
                if (($product['productCode'] ?? '') === $gameId) {
                    $desc  = trim($product['description'] ?? '');
                    $lines = preg_split('/\r\n|\r|\n/', $desc);
                    // Price is usually on line index 3
                    $priceLine = isset($lines[3]) ? preg_replace('/^.*?:\s*/', '', trim(strip_tags($lines[3]))) : '';
                    preg_match_all('/\$(\d+(?:\.\d+)?)/', $priceLine, $matches);
                    $prices = array_map('floatval', $matches[1] ?? []);
                    sort($prices);

                    if (count($prices) >= 2) {
                        $priceMin = $prices[0]; // 3+ guests
                        $priceMax = $prices[1]; // 2 guests
                    } elseif (count($prices) === 1) {
                        $priceMin = $prices[0];
                        $priceMax = $prices[0];
                    }
                    break;
                }
            }
        }
    }

    // Set prices
    if ($priceMin <= 0) $priceMin = $fallbackPrice;
    if ($priceMax <= 0) $priceMax = $fallbackPrice;

    // Determine new base price
    $newPrice = ($qty <= 2) ? $priceMax : $priceMin;
    $newTotal = $qty * $newPrice;

    // Update DB
    $updateStmt = $pdo->prepare("UPDATE tbl_carts SET guests=:qty, price=:price, total=:total WHERE session_id=:sid AND event_id=:event_id");
    $updateStmt->execute([
        ':qty'     => $qty,
        ':price'   => $newPrice,
        ':total'   => $newTotal,
        ':sid'     => $sid,
        ':event_id' => $eventId
    ]);

    // ---------------------------------------------------------
    // 2. HAND OVER TO APPLY_CODE.PHP
    // ---------------------------------------------------------
    // Now that the DB is updated, we run apply_code.php.
    // It will:
    // 1. Read the cart (seeing the new qty).
    // 2. Delete old holds.
    // 3. Create new holds with ALL options (addons/promos/vouchers).
    
    // We need to pass the currently active promo/voucher code to it.
    // Assuming apply_code.php saved valid codes to SESSION['giftCode'].
    $currentCode = $_SESSION['giftCode'] ?? '';
    // if($currentCode){
        // Mock the POST variable that apply_code.php expects
        $_POST['code'] = $currentCode;
    
        // Include the file. It will execute and output the JSON response.
        // We use include instead of a cURL request to share the same Session/DB connection.
        include("apply_code.php");
    // }
    exit;
}
?>