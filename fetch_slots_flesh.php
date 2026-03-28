<?php
/**
 * fetch_slots_flash.php (Upgraded to New Architecture)
 */
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

include('admin/db.php');
require_once(__DIR__ . '/includes/bookeo_runtime.php');

const API_BASE_URL = 'https://api.bookeo.com/v2/availability/slots';

function logFlash($msg) {
    flee_bookeo_log_message('fetch_slots_flash', $msg);
}

function isFlashProductCacheFresh(PDO $pdo, $requestedDate, $productId, DateTimeZone $losAngelesTz, DateTime $nowLocal)
{
    if (flee_bookeo_is_day_cache_fresh($requestedDate, $nowLocal)) {
        return true;
    }

    $stmtCache = $pdo->prepare("SELECT MIN(expires_at) as earliest_expiry, COUNT(DISTINCT product_id) as found_products FROM bookeo_slots_cache WHERE slot_date = ? AND product_id = ?");
    $stmtCache->execute([$requestedDate, $productId]);
    $cacheCheck = $stmtCache->fetch(PDO::FETCH_ASSOC);

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
$productIds    = isset($_GET['productIds']) ? json_decode($_GET['productIds'], true) : [];

if (!$requestedDate || !is_array($productIds) || empty($productIds)) {
    echo json_encode(['error' => 'Missing inputs']);
    exit;
}

$losAngelesTz = new DateTimeZone('America/Los_Angeles');
$utcTz        = new DateTimeZone('UTC');

$stmt = $pdo->prepare("SELECT deal_hours FROM tbl_flash_deal WHERE is_active = 1 LIMIT 1");
$stmt->execute();
$dealRow = $stmt->fetch(PDO::FETCH_ASSOC);
$dealHours = $dealRow ? (int)$dealRow['deal_hours'] : 0;

$results = [];

foreach ($productIds as $rawProductId) {
    $productId = trim($rawProductId);
    if (empty($productId)) continue;

    $currentDate = $requestedDate;
    $attempts    = 0;
    
    while ($attempts < 2) {
        $nowLocal = new DateTime('now', $losAngelesTz);
        
        try {
            $dateObj = new DateTime($currentDate, $losAngelesTz);
        } catch (Exception $e) {
            break;
        }

        // --- CACHE CHECK ---
        $slotsForDisplay = null;
        $needsApiFetch = false;

        $needsApiFetch = !isFlashProductCacheFresh($pdo, $currentDate, $productId, $losAngelesTz, $nowLocal);

        // --- API FETCH ---
        if ($needsApiFetch) {
            $fetchLock = flee_bookeo_acquire_lock('availability_day_' . $currentDate, 12);
            if ($fetchLock === false) {
                logFlash("Could not acquire fetch lock for $currentDate. Reading current cache instead.");
                usleep(800000);
            }

            if (flee_bookeo_is_throttled()) {
                $waitSeconds = flee_bookeo_retry_after_seconds();
                if ($waitSeconds > 15) {
                    $results[$productId] = ['html' => '<p>System synchronizing. Please retry.</p>', 'date' => $currentDate];
                    break 2; // Exit both loops to prevent freezing
                } elseif ($waitSeconds > 0) {
                    sleep($waitSeconds + 1); 
                }
            }

            try {
                $nowLocal = new DateTime('now', $losAngelesTz);
                if ($fetchLock === false) {
                    $refreshedByPeer = isFlashProductCacheFresh($pdo, $currentDate, $productId, $losAngelesTz, $nowLocal);
                    if ($refreshedByPeer) {
                        logFlash("Skipped Bookeo fetch for $currentDate because another request refreshed the cache while lock was busy.");
                    } else {
                        logFlash("Skipped Bookeo fetch for $currentDate because the day lock stayed busy. Serving DB state without a duplicate outbound call.");
                    }
                } elseif (!isFlashProductCacheFresh($pdo, $currentDate, $productId, $losAngelesTz, $nowLocal)) {
                    $startUTC = (clone $dateObj)->setTime(0, 0, 0)->setTimezone($utcTz)->format('Y-m-d\TH:i:s\Z');
                    $endUTC   = (clone $dateObj)->setTime(23, 59, 59)->setTimezone($utcTz)->format('Y-m-d\TH:i:s\Z');
                    $url = API_BASE_URL . "?startTime={$startUTC}&endTime={$endUTC}&itemsPerPage=300";
                    
                    $apiResponse = flee_bookeo_request('GET', $url, [
                        'context' => 'fetch_slots_flash_availability',
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
                            $pdo->prepare("DELETE FROM bookeo_slots_cache WHERE slot_date = ? AND product_id = ?")->execute([$currentDate, $productId]);
                            $nowStr = $nowLocal->format('Y-m-d H:i:s');
                            $expiresAt = (clone $nowLocal)->modify("+10 minutes")->format('Y-m-d H:i:s');
                            $placeholderEventId = flee_bookeo_placeholder_event_id($currentDate);
                            
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
                                    ")->execute([$productId, $currentDate, $expiresAt, $placeholderEventId, $nowStr]);
                                }
                            } else {
                                $pdo->prepare("
                                    INSERT INTO bookeo_slots_cache (product_id, slot_date, expires_at, event_id, cached_at)
                                    VALUES (?, ?, ?, ?, ?)
                                    ON DUPLICATE KEY UPDATE
                                        slot_date = VALUES(slot_date),
                                        expires_at = VALUES(expires_at),
                                        cached_at = VALUES(cached_at)
                                ")->execute([$productId, $currentDate, $expiresAt, $placeholderEventId, $nowStr]);
                            }
                            $pdo->commit();
                            flee_bookeo_mark_day_cache_fresh($currentDate, $expiresAt);
                        } catch (Exception $e) {
                            $pdo->rollBack();
                            logFlash("DB ERROR during fetch: " . $e->getMessage());
                        }
                    } elseif ($httpCode === 429) {
                        flee_bookeo_set_throttle((int)($apiResponse['retry_after'] ?? (($apiResponse['data']['retryAfter'] ?? 30))), 'fetch_slots_flash_penalty');
                        $results[$productId] = ['html' => '<p>System synchronizing. Please retry.</p>', 'date' => $currentDate];
                        break 2;
                    } else {
                        $slotsForDisplay = [];
                    }
                } else {
                    logFlash("Skipped Bookeo fetch for $currentDate because another request refreshed the cache.");
                }
            } finally {
                flee_bookeo_release_lock($fetchLock);
            }
        }

        // --- DB READ ---
        if ($slotsForDisplay === null) {
            $stmt = $pdo->prepare("SELECT product_id, start_time_local, event_id, available_seats FROM bookeo_slots_cache WHERE slot_date = ? AND product_id = ? ORDER BY start_time_local ASC");
            $stmt->execute([$currentDate, $productId]);
            $slotsForDisplay = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // --- HTML GENERATION WITH FLASH DEAL LIMITS ---
        $html = '';
        $counter = 0;
        $validSlotsFound = false;

        foreach ($slotsForDisplay as $slot) {
            if (flee_bookeo_is_placeholder_event_id($slot['event_id'])) continue;
            
            $dt = new DateTime($slot['start_time_local'], $losAngelesTz);
            if ($dt->getTimestamp() < $nowLocal->getTimestamp()) continue;

            $minutesDiff = ($dt->getTimestamp() - $nowLocal->getTimestamp()) / 60;
            
            // FLASH DEAL LOGIC
            if ($dealHours > 0 && $minutesDiff > ($dealHours * 60)) continue;

            $validSlotsFound = true;
            $time = $dt->format('g:i A');
            $available = (int)$slot['available_seats'];
            $isFull = ($available === 0);
            $eventId = $slot['event_id'];
            $localStr = $dt->format('Y-m-d H:i:s');

            if ($minutesDiff <= 20 && !$isFull) {
                $safeTime = htmlspecialchars($time, ENT_QUOTES);
                $html .= '<div class="time_slot_group time_slot_call"><span class="call-slot-btn" onclick="showCallPopup(\'' . $safeTime . '\')">' . $safeTime . '<span class="Available_play_time">' . $available . ' Available</span><span class="Available_play_time">Call</span></span></div>';
                continue;
            }

            $id = 'lift-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $productId) . '-' . $counter++;
            $html .= '<div class="time_slot_group ' . ($isFull ? 'time_slot_full' : '') . '"><input type="radio" name="lift-time-' . $productId . '" id="' . $id . '" value="' . htmlspecialchars($localStr, ENT_QUOTES) . '" data-start-time="' . htmlspecialchars($localStr, ENT_QUOTES) . '" data-eventid="' . htmlspecialchars($eventId, ENT_QUOTES) . '" data-available="' . $available . '" ' . ($isFull ? 'disabled' : '') . ' hidden><label for="' . $id . '">' . $time . '<span class="Available_play_time">' . ($isFull ? 'Full' : $available . ' Available') . '</span></label></div>';
        }

        if ($validSlotsFound) {
            $results[$productId] = ['html' => $html, 'date' => $currentDate];
            break; // Break the 'attempts' while loop, move to next product
        }

        // If no valid slots found, increment attempt and jump to tomorrow
        $attempts++;
        $currentDate = (new DateTime($currentDate, $losAngelesTz))->modify('+1 day')->format('Y-m-d');

        if ($attempts >= 2) {
            $results[$productId] = [
                'html' => "<p>No available slots found for {$requestedDate}" . ($currentDate !== $requestedDate ? " or {$currentDate}" : "") . ".</p>",
                'date' => $currentDate,
            ];
        }
    }
}

echo json_encode($results);
?>
