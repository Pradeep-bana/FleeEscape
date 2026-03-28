<?php
/**
 * fetch_slots_by_product.php (Upgraded to New Architecture)
 */
ini_set('display_errors', 0);
error_reporting(E_ALL);

include('admin/db.php');
require_once(__DIR__ . '/includes/bookeo_runtime.php');

const API_BASE_URL = 'https://api.bookeo.com/v2/availability/slots';

function logSlots($msg) {
    flee_bookeo_log_message('fetch_slots_by_product', $msg);
}

function isSingleProductCacheFresh(PDO $pdo, $requestedDate, $productId, DateTimeZone $losAngelesTz, DateTime $nowLocal)
{
    if (flee_bookeo_is_day_cache_fresh($requestedDate, $nowLocal)) {
        return true;
    }

    $stmt = $pdo->prepare("SELECT MIN(expires_at) as earliest_expiry, COUNT(DISTINCT product_id) as found_products FROM bookeo_slots_cache WHERE slot_date = ? AND product_id = ?");
    $stmt->execute([$requestedDate, $productId]);
    $cacheCheck = $stmt->fetch(PDO::FETCH_ASSOC);

    if ((int)($cacheCheck['found_products'] ?? 0) === 0) {
        return false;
    }

    if (empty($cacheCheck['earliest_expiry'])) {
        return false;
    }

    $earliestExpiryDt = new DateTime($cacheCheck['earliest_expiry'], $losAngelesTz);
    return $earliestExpiryDt >= $nowLocal;
}

$requestedDate = $_GET['date'] ?? '';
$productId     = isset($_GET['productCode']) ? trim($_GET['productCode']) : '';
$selectedSlot  = $_GET['selectedSlot'] ?? '';

if (!$requestedDate || !$productId) {
    echo '<p>No slots available</p>';
    exit;
}

$losAngelesTz = new DateTimeZone('America/Los_Angeles');
$utcTz        = new DateTimeZone('UTC');
$nowLocal     = new DateTime('now', $losAngelesTz);

try {
    $dateObj = new DateTime($requestedDate, $losAngelesTz);
} catch (Exception $e) {
    echo '<p>Invalid date</p>';
    exit;
}

// ============================================================
// STEP 1: CACHE CHECK (Single Product)
// ============================================================
$slotsForDisplay = null;
$needsApiFetch = false;

$needsApiFetch = !isSingleProductCacheFresh($pdo, $requestedDate, $productId, $losAngelesTz, $nowLocal);

// ============================================================
// STEP 2: API FETCH (If Needed)
// ============================================================
if ($needsApiFetch) {
    $fetchLock = flee_bookeo_acquire_lock('availability_day_' . $requestedDate, 12);
    if ($fetchLock === false) {
        logSlots("Could not acquire fetch lock for $requestedDate. Reading current cache instead.");
        usleep(800000);
    }

    if (flee_bookeo_is_throttled()) {
        $waitSeconds = flee_bookeo_retry_after_seconds();
        if ($waitSeconds > 15) {
            echo '<p>System is synchronizing with Bookeo. Please try again shortly.</p>';
            exit;
        } elseif ($waitSeconds > 0) {
            sleep($waitSeconds + 1); 
        }
    }

    try {
        $nowLocal = new DateTime('now', $losAngelesTz);
        if ($fetchLock === false) {
            $refreshedByPeer = isSingleProductCacheFresh($pdo, $requestedDate, $productId, $losAngelesTz, $nowLocal);
            if ($refreshedByPeer) {
                logSlots("Skipped Bookeo fetch for $requestedDate because another request refreshed the cache while lock was busy.");
            } else {
                logSlots("Skipped Bookeo fetch for $requestedDate because the day lock stayed busy. Serving DB state without a duplicate outbound call.");
            }
        } elseif (!isSingleProductCacheFresh($pdo, $requestedDate, $productId, $losAngelesTz, $nowLocal)) {
            $startUTC = (clone $dateObj)->setTime(0, 0, 0)->setTimezone($utcTz)->format('Y-m-d\TH:i:s\Z');
            $endUTC   = (clone $dateObj)->setTime(23, 59, 59)->setTimezone($utcTz)->format('Y-m-d\TH:i:s\Z');
            $url = API_BASE_URL . "?startTime={$startUTC}&endTime={$endUTC}&itemsPerPage=300";
            
            $apiResponse = flee_bookeo_request('GET', $url, [
                'context' => 'fetch_slots_by_product_availability',
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
                $requestedProductFound = false;

                $normalizedSlots = [];
                foreach ($fetchedSlots as $apiSlot) {
                    if (($apiSlot['productId'] ?? '') === $productId) {
                        $requestedProductFound = true;
                    }
                    $apiDt = new DateTime($apiSlot['startTime']);
                    $normalizedSlots[] = [
                        'product_id' => $apiSlot['productId'],
                        'start_time_local' => $apiDt->setTimezone($losAngelesTz)->format('Y-m-d H:i:s'),
                        'event_id' => $apiSlot['eventId'],
                        'available_seats' => (int)($apiSlot['numSeatsAvailable'] ?? 0)
                    ];
                }
                $slotsForDisplay = array_values(array_filter($normalizedSlots, static function ($slot) use ($productId) {
                    return ($slot['product_id'] ?? '') === $productId;
                }));

                $pdo->beginTransaction();
                try {
                    $pdo->prepare("DELETE FROM bookeo_slots_cache WHERE slot_date = ? AND product_id = ?")->execute([$requestedDate, $productId]);
                    $nowStr = $nowLocal->format('Y-m-d H:i:s');
                    $expiresAt = (clone $nowLocal)->modify("+10 minutes")->format('Y-m-d H:i:s');
                    $placeholderEventId = flee_bookeo_placeholder_event_id($requestedDate);
                    
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
                        if (!$requestedProductFound) {
                            $pdo->prepare("
                                INSERT INTO bookeo_slots_cache (product_id, slot_date, expires_at, event_id, cached_at)
                                VALUES (?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE
                                    slot_date = VALUES(slot_date),
                                    expires_at = VALUES(expires_at),
                                    cached_at = VALUES(cached_at)
                            ")->execute([$productId, $requestedDate, $expiresAt, $placeholderEventId, $nowStr]);
                        }
                    } else {
                         $pdo->prepare("
                            INSERT INTO bookeo_slots_cache (product_id, slot_date, expires_at, event_id, cached_at)
                            VALUES (?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE
                                slot_date = VALUES(slot_date),
                                expires_at = VALUES(expires_at),
                                cached_at = VALUES(cached_at)
                        ")->execute([$productId, $requestedDate, $expiresAt, $placeholderEventId, $nowStr]);
                    }
                    $pdo->commit();
                    flee_bookeo_mark_day_cache_fresh($requestedDate, $expiresAt);
                } catch (Exception $e) {
                    $pdo->rollBack();
                    logSlots("DB ERROR during fetch: " . $e->getMessage());
                }
            } elseif ($httpCode === 429) {
                flee_bookeo_set_throttle((int)($apiResponse['retry_after'] ?? (($apiResponse['data']['retryAfter'] ?? 30))), 'fetch_slots_by_product_penalty');
                echo '<p>System is synchronizing with Bookeo. Please try again shortly.</p>';
                exit;
            } else {
                $slotsForDisplay = [];
            }
        } else {
            logSlots("Skipped Bookeo fetch for $requestedDate because another request refreshed the cache.");
        }
    } finally {
        flee_bookeo_release_lock($fetchLock);
    }
}

// ============================================================
// STEP 3: READ DB & GENERATE HTML
// ============================================================
if ($slotsForDisplay === null) {
    $stmt = $pdo->prepare("SELECT product_id, start_time_local, event_id, available_seats FROM bookeo_slots_cache WHERE slot_date = ? AND product_id = ? ORDER BY start_time_local ASC");
    $stmt->execute([$requestedDate, $productId]);
    $slotsForDisplay = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$html = '';
$counter = 0;

foreach ($slotsForDisplay as $slot) {
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

    $slotId = 'Boo_Prison_Escape_time_' . $productId . '_' . $counter++;
    $isSelected = ($selectedSlot && $selectedSlot === $localStr);

    $html .= '<div class="col-4 slot-box"><input type="radio" name="lift-time-' . htmlspecialchars($productId) . '" id="' . $slotId . '" value="' . htmlspecialchars($localStr) . '" class="Boo_Prison_Escape_time-slot" data-start-time="' . htmlspecialchars($localStr) . '" data-eventid="' . htmlspecialchars($eventId) . '" data-available="' . $available . '" ' . ($isFull ? 'disabled' : '') . ' ' . ($isSelected ? 'checked' : '') . ' /><label for="' . $slotId . '" class="Boo_Prison_Escape_time-slot-label">' . $time . '<br><span class="Available_play_time">' . ($isFull ? 'Full' : $available . ' Available') . '</span></label></div>';
}

echo $html ?: '<p>No slots available</p>';
?>
