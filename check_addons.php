<?php
session_start();
header('Content-Type: application/json');

include("admin/db.php");

$hasAddons = false;

try {
    /* --------------------------------------------------
       1. GET PRODUCT CACHE
    -------------------------------------------------- */
    $stmt = $pdo->prepare("SELECT product_data FROM bookeo_products_cache WHERE id = 1 LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['has_addons' => false]);
        exit;
    }

    $productsData = json_decode($row['product_data'], true);
    if (!isset($productsData['data']) || !is_array($productsData['data'])) {
        echo json_encode(['has_addons' => false]);
        exit;
    }

    /* --------------------------------------------------
       2. ESCAPE ROOM ADDON LOOKUP (tbl_service)
    -------------------------------------------------- */
    $serviceStmt = $pdo->prepare("SELECT addon_ids, addon_prices FROM tbl_service");
    $serviceStmt->execute();
    $services = $serviceStmt->fetchAll(PDO::FETCH_ASSOC);

    $addonLookup = [];
    foreach ($services as $srv) {
        $ids    = json_decode($srv['addon_ids'] ?? '[]', true);
        $prices = json_decode($srv['addon_prices'], true);
        if (!is_array($ids)) continue;
        foreach ($ids as $i => $id) {
            $cleanID = trim($id);
            $addonLookup[$cleanID] = ['price' => $prices[$i] ?? 0];
        }
    }

    /* --------------------------------------------------
       3. PARTY PACKAGE ADDON LOOKUP (tbl_party_packages)
    -------------------------------------------------- */
    $partyStmt = $pdo->prepare("SELECT product_id, addon_ids, addon_prices FROM tbl_party_packages");
    $partyStmt->execute();
    $partyRows = $partyStmt->fetchAll(PDO::FETCH_ASSOC);

    $partyLookup = [];
    foreach ($partyRows as $pr) {
        $addon_ids    = json_decode($pr['addon_ids'] ?? '[]', true);
        $addon_prices = json_decode($pr['addon_prices'], true);
        $temp = [];
        if (is_array($addon_ids)) {
            foreach ($addon_ids as $i => $id) {
                $cleanID = trim($id);
                if (isset($addon_prices[$i])) {
                    $temp[$cleanID] = ['price' => $addon_prices[$i] ?? 0];
                }
            }
        }
        $partyLookup[$pr['product_id']] = $temp;
    }

    /* --------------------------------------------------
       4. FETCH CART ITEMS
    -------------------------------------------------- */
    $cartStmt = $pdo->prepare("SELECT * FROM tbl_carts WHERE session_id = :sid");
    $cartStmt->execute([':sid' => session_id()]);
    $cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

    $cartMap = [];
    foreach ($cartItems as $c) {
        $cartMap[$c['game_id']] = $c;
    }
    $cartGameIds = array_keys($cartMap);

    if (empty($cartGameIds)) {
        echo json_encode(['has_addons' => false]);
        exit;
    }

    /* --------------------------------------------------
       5. CHECK IF ANY ADDON EXISTS FOR CART PRODUCTS
    -------------------------------------------------- */
    foreach ($productsData['data'] as $product) {
        if (!isset($product['productCode'])) continue;
        $productCode = $product['productCode'];
        if (!in_array($productCode, $cartGameIds)) continue;

        $cat = strtolower(trim($cartMap[$productCode]['cat'] ?? ""));

        // --- PARTY PACKAGE ADDONS ---
        if ($cat === "party-package" && isset($partyLookup[$productCode])) {
            $partyAddons = $partyLookup[$productCode];
            if (isset($product["onOffOptions"]) && is_array($product["onOffOptions"])) {
                foreach ($product["onOffOptions"] as $opt) {
                    $opt_id     = $opt["id"] ?? "";
                    $addonPrice = 0;
                    if (isset($partyAddons[$opt_id]) && !empty($partyAddons[$opt_id]["price"])) {
                        $addonPrice = $partyAddons[$opt_id]["price"];
                    }
                    if ($addonPrice > 0) {
                        $hasAddons = true;
                        break 2; // Found one — no need to check further
                    }
                }
            }
            continue;
        }

        // --- ESCAPE ROOM ADDONS ---
        if (!isset($product['numberOptions'])) continue;
        foreach ($product['numberOptions'] as $opt) {
            $opt_id     = $opt["id"] ?? "";
            $addonPrice = $opt["defaultValue"] ?? 0;
            if (isset($addonLookup[$opt_id]) && !empty($addonLookup[$opt_id]["price"])) {
                $addonPrice = $addonLookup[$opt_id]["price"];
            }
            if ($addonPrice > 0) {
                $hasAddons = true;
                break 2; // Found one — no need to check further
            }
        }
    }

} catch (Exception $e) {
    error_log("check_addons.php error: " . $e->getMessage());
    // On error, default to showing addons (safe fallback)
    echo json_encode(['has_addons' => true]);
    exit;
}

echo json_encode(['has_addons' => $hasAddons]);