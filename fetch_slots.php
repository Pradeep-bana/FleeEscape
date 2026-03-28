<?php
/**
 * fetch_slots.php (Corrected, Normalized, and Optimized)
 */
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

include('admin/db.php');
require_once(__DIR__ . '/includes/bookeo_runtime.php');

const API_BASE_URL = 'https://api.bookeo.com/v2/availability/slots';

$losAngelesTz = new DateTimeZone('America/Los_Angeles');
$utcTz        = new DateTimeZone('UTC');
$nowLocal     = new DateTime('now', $losAngelesTz);

$requestedDate = $_GET['date'] ?? '';
$productIds    = isset($_GET['productIds']) ? array_filter(array_map('trim', json_decode($_GET['productIds'], true) ?? [])) : [];

if (!$requestedDate || empty($productIds)) {
    echo json_encode(['error' => 'Missing inputs']);
    exit;
}

try {
    $dateObj = new DateTime($requestedDate, $losAngelesTz);
} catch (Exception $e) {
    echo json_encode(['error' => 'Invalid date']);
    exit;
}

function logMessage($msg) {
    flee_bookeo_log_message('fetch_slots', $msg);
}

function isCacheFreshForProducts(PDO $pdo, $requestedDate, array $productIds, DateTimeZone $losAngelesTz, DateTime $nowLocal)
{
    if (empty($productIds)) {
        return false;
    }

    if (flee_bookeo_is_day_cache_fresh($requestedDate, $nowLocal)) {
        return true;
    }

    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = $pdo->prepare("
        SELECT MIN(expires_at) as earliest_expiry, COUNT(DISTINCT product_id) as found_products_count
        FROM bookeo_slots_cache 
        WHERE slot_date = ? AND product_id IN ($placeholders)
    ");
    $stmt->execute(array_merge([$requestedDate], $productIds));
    $cacheCheckResult = $stmt->fetch(PDO::FETCH_ASSOC);

    $numProductsRequested = count($productIds);
    $numProductsFound = (int)($cacheCheckResult['found_products_count'] ?? 0);

    if ($numProductsFound < $numProductsRequested) {
        return false;
    }

    if (empty($cacheCheckResult['earliest_expiry'])) {
        return false;
    }

    $earliestExpiryDt = new DateTime($cacheCheckResult['earliest_expiry'], $losAngelesTz);
    return $earliestExpiryDt >= $nowLocal;
}

// ============================================================
// STEP 1: CHECK CACHE VALIDITY (Single, Optimized Query)
// ============================================================
$slotsForDisplay = null;
$needsApiFetch = false;

$needsApiFetch = !isCacheFreshForProducts($pdo, $requestedDate, $productIds, $losAngelesTz, $nowLocal);


// ============================================================
// STEP 2: ON-DEMAND API FETCH (if needed)
// ============================================================
if ($needsApiFetch) {
    $fetchLock = flee_bookeo_acquire_lock('availability_day_' . $requestedDate, 12);
    if ($fetchLock === false) {
        logMessage("Could not acquire fetch lock for $requestedDate. Reading current cache instead.");
        usleep(800000);
    }
    
    // --- DYNAMIC THROTTLE CHECK & SMART WAIT ---
    if (flee_bookeo_is_throttled()) {
        $waitSeconds = flee_bookeo_retry_after_seconds();

        if ($waitSeconds > 15) {
            // Wait time is too high. Abort instantly.
            logMessage("Frontend aborted. Global API lock is active for {$waitSeconds}s (Too long).");
            echo json_encode(['error' => "System is synchronizing with Bookeo. Please try again in {$waitSeconds} seconds."]);
            exit;
        } elseif ($waitSeconds > 0) {
            logMessage("Frontend sleeping for {$waitSeconds}s waiting for Global API lock to clear...");
            sleep($waitSeconds + 1); 
        }
    }

    try {
        $nowLocal = new DateTime('now', $losAngelesTz);
        if ($fetchLock === false) {
            $refreshedByPeer = isCacheFreshForProducts($pdo, $requestedDate, $productIds, $losAngelesTz, $nowLocal);
            if ($refreshedByPeer) {
                logMessage("Skipped Bookeo fetch for $requestedDate because another request refreshed the cache while lock was busy.");
            } else {
                logMessage("Skipped Bookeo fetch for $requestedDate because the day lock stayed busy. Serving DB state without a duplicate outbound call.");
            }
        } elseif (!isCacheFreshForProducts($pdo, $requestedDate, $productIds, $losAngelesTz, $nowLocal)) {
            logMessage("CACHE MISS/STALE for $requestedDate. Fetching from Bookeo API.");
            $startUTC = (clone $dateObj)->setTime(0, 0, 0)->setTimezone($utcTz)->format('Y-m-d\TH:i:s\Z');
            $endUTC   = (clone $dateObj)->setTime(23, 59, 59)->setTimezone($utcTz)->format('Y-m-d\TH:i:s\Z');
            
            $url = API_BASE_URL . "?startTime={$startUTC}&endTime={$endUTC}&itemsPerPage=300";
            
            $apiResponse = flee_bookeo_request('GET', $url, [
                'context' => 'fetch_slots_availability',
                'timeout' => 20,
                'headers' => [
                    'X-Bookeo-apiKey: ' . FLEE_BOOKEO_API_KEY,
                    'X-Bookeo-secretKey: ' . FLEE_BOOKEO_SECRET_KEY,
                    'Accept: application/json',
                ],
            ]);
            $response = $apiResponse['body'];
            $httpCode = $apiResponse['code'];
            
            if ($httpCode === 200) {
                $apiData = json_decode($response, true);
                $fetchedSlots = $apiData['data'] ?? [];
                $fetchedProductMap = [];

                $normalizedSlots = [];
                foreach ($fetchedSlots as $apiSlot) {
                    if (!empty($apiSlot['productId'])) {
                        $fetchedProductMap[(string)$apiSlot['productId']] = true;
                    }
                    $apiDt = new DateTime($apiSlot['startTime']);
                    $normalizedSlots[] = [
                        'product_id' => $apiSlot['productId'],
                        'start_time_local' => $apiDt->setTimezone($losAngelesTz)->format('Y-m-d H:i:s'),
                        'event_id' => $apiSlot['eventId'],
                        'available_seats' => (int)($apiSlot['numSeatsAvailable'] ?? 0),
                    ];
                }
                $slotsForDisplay = $normalizedSlots; 

                $pdo->beginTransaction();
                try {
                    $deletePlaceholders = implode(',', array_fill(0, count($productIds), '?'));
                    $delStmt = $pdo->prepare("DELETE FROM bookeo_slots_cache WHERE slot_date = ? AND product_id IN ($deletePlaceholders)");
                    $delStmt->execute(array_merge([$requestedDate], $productIds));
                    
                    $nowStr = $nowLocal->format('Y-m-d H:i:s');
                    $expiresAt = (clone $nowLocal)->modify("+10 minutes")->format('Y-m-d H:i:s');
                    $placeholderEventId = flee_bookeo_placeholder_event_id($requestedDate);
                    $missingProductIds = array_values(array_filter($productIds, static function ($pid) use ($fetchedProductMap) {
                        return !isset($fetchedProductMap[(string)$pid]);
                    }));
                    
                    if (!empty($fetchedSlots)) {
                        $insStmt = $pdo->prepare("
                            INSERT INTO bookeo_slots_cache
                                (product_id, slot_date, start_time_utc, start_time_local, event_id, available_seats, max_seats, cached_at, expires_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE
                                slot_date = VALUES(slot_date),
                                start_time_utc = VALUES(start_time_utc),
                                start_time_local = VALUES(start_time_local),
                                available_seats = VALUES(available_seats),
                                max_seats = VALUES(max_seats),
                                cached_at = VALUES(cached_at),
                                expires_at = VALUES(expires_at)
                        ");
                        foreach ($fetchedSlots as $slot) {
                            if (empty($slot['productId']) || empty($slot['eventId'])) continue;
                            $apiDt = new DateTime($slot['startTime']);
                            $insStmt->execute([$slot['productId'], $apiDt->setTimezone($losAngelesTz)->format('Y-m-d'), $apiDt->setTimezone($utcTz)->format('Y-m-d\TH:i:s\Z'), $apiDt->setTimezone($losAngelesTz)->format('Y-m-d H:i:s'), $slot['eventId'], (int)($slot['numSeatsAvailable'] ?? 0), (int)($slot['maxSeats'] ?? 0), $nowStr, $expiresAt]);
                        }
                        if (!empty($missingProductIds)) {
                            $emptyStmt = $pdo->prepare("
                                INSERT INTO bookeo_slots_cache (product_id, slot_date, expires_at, event_id, cached_at)
                                VALUES (?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE
                                    slot_date = VALUES(slot_date),
                                    expires_at = VALUES(expires_at),
                                    cached_at = VALUES(cached_at)
                            ");
                            foreach ($missingProductIds as $pid) {
                                $emptyStmt->execute([$pid, $requestedDate, $expiresAt, $placeholderEventId, $nowStr]);
                            }
                        }
                        logMessage("API FETCH COMPLETE: Fetched and cached " . count($fetchedSlots) . " slots for $requestedDate.");
                    } else {
                        $insStmt = $pdo->prepare("
                            INSERT INTO bookeo_slots_cache (product_id, slot_date, expires_at, event_id, cached_at)
                            VALUES (?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE
                                slot_date = VALUES(slot_date),
                                expires_at = VALUES(expires_at),
                                cached_at = VALUES(cached_at)
                        ");
                        foreach ($productIds as $pid) {
                            $insStmt->execute([$pid, $requestedDate, $expiresAt, $placeholderEventId, $nowStr]);
                        }
                        logMessage("API FETCH COMPLETE: Day $requestedDate has 0 slots. Cached this empty result for all requested products.");
                    }
                    $pdo->commit();
                    flee_bookeo_mark_day_cache_fresh($requestedDate, $expiresAt);
                } catch (Exception $e) {
                    $pdo->rollBack();
                    logMessage("DB ERROR during manual fetch: " . $e->getMessage());
                }
                
            } elseif ($httpCode === 429) {
                $retryAfter = (int)($apiResponse['retry_after'] ?? (($apiResponse['data']['retryAfter'] ?? 30)));
                flee_bookeo_set_throttle($retryAfter, 'fetch_slots_penalty');
                logMessage("API ERROR: 429 Too Many Requests. Locked system for {$retryAfter}s.");
                
                echo json_encode(['error' => "System is synchronizing with Bookeo. Please try again in {$retryAfter} seconds."]);
                exit;
            } else {
                logMessage("API ERROR: Http Code $httpCode for $requestedDate");
                $slotsForDisplay = [];
            }
        } else {
            logMessage("Skipped Bookeo fetch for $requestedDate because another request refreshed the cache.");
        }
    } finally {
        flee_bookeo_release_lock($fetchLock);
    }
}

// ============================================================
// STEP 3: READ FROM DB (Only if we didn't just fetch from API)
// ============================================================
if ($slotsForDisplay === null) {
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $stmt = $pdo->prepare("SELECT product_id, start_time_local, event_id, available_seats FROM bookeo_slots_cache WHERE slot_date = ? AND product_id IN ($placeholders) ORDER BY start_time_local ASC");
    $stmt->execute(array_merge([$requestedDate], $productIds));
    $slotsForDisplay = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// STEP 4: GENERATE HTML from the final, unified data
// ============================================================
$groupedSlots = [];
foreach ($productIds as $pid) $groupedSlots[$pid] = [];
foreach ($slotsForDisplay as $row) {
    if (in_array($row['product_id'], $productIds)) {
        $groupedSlots[$row['product_id']][] = $row;
    }
}

$output = [];
foreach ($productIds as $productId) {
    $slots = $groupedSlots[$productId];
    $html = '';
    $counter = 0;
    foreach ($slots as $slot) {
        if (flee_bookeo_is_placeholder_event_id($slot['event_id'])) continue;
        $dt = new DateTime($slot['start_time_local'], $losAngelesTz);
        if ($dt->getTimestamp() < $nowLocal->getTimestamp()) continue;

        $time = $dt->format('g:i A');
        $available = (int)$slot['available_seats'];
        $isFull = ($available === 0);
        $eventId = $slot['event_id'];
        $localStr = $dt->format('Y-m-d H:i:s');
        $minutesDiff = ($dt->getTimestamp() - $nowLocal->getTimestamp()) / 60;

        if ($minutesDiff <= 20 && !$isFull) {
            $html .= '<div class="time_slot_group time_slot_call"><span class="call-slot-btn" onclick="showCallPopup(\'' . $time . '\')">' . $time . '<span class="Available_play_time">' . $available . ' Available</span><span class="Available_play_time">Call</span></span></div>';
            continue;
        }

        $uid = 'slot-' . $productId . '-' . $counter++;
        $html .= '<div class="time_slot_group ' . ($isFull ? 'time_slot_full' : '') . '"><input type="radio" name="lift-time-' . $productId . '" id="' . $uid . '" value="' . $localStr . '" data-start-time="' . $localStr . '" data-eventid="' . $eventId . '" data-available="' . $available . '" ' . ($isFull ? 'disabled' : '') . ' hidden><label for="' . $uid . '">' . $time . '<span class="Available_play_time">' . ($isFull ? 'Full' : $available . ' Available') . '</span></label></div>';
    }
    $output[$productId] = ['html' => $html ?: '<p>No slots available</p>', 'date' => $requestedDate];
}

echo json_encode($output);
exit;
