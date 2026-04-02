<?php
/**
 * cron_sync_slots.php (Final: Bulletproof Pagination + Garbage Collection)
 * 
 * this is our cron file that runs every 10 minute in our cpanel
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(600);
ignore_user_abort(true);

include('admin/db.php');
require_once('config.php');
require_once(__DIR__ . '/includes/bookeo_runtime.php');

const FETCH_DAYS_AHEAD = 7; 
const API_BASE_URL = 'https://api.bookeo.com/v2/availability/slots';

$losAngelesTz = new DateTimeZone('America/Los_Angeles');
$utcTz        = new DateTimeZone('UTC');

function log_message($message) {
    flee_bookeo_log_message('cron_sync_slots', $message);
}

function is_api_throttled() {
    return flee_bookeo_is_throttled();
}

function set_api_throttle($retryAfterSeconds) {
    flee_bookeo_set_throttle($retryAfterSeconds, 'cron_sync_slots_throttle');
    log_message("GLOBAL LOCK ENGAGED: API suspended for " . ($retryAfterSeconds + 2) . " seconds.");
}

log_message("Cron job started (Fetching " . FETCH_DAYS_AHEAD . " days).");

$startLA = new DateTime('today', $losAngelesTz);
$endLA   = (clone $startLA)->modify('+' . FETCH_DAYS_AHEAD . ' days');
$startTimeUTC = (clone $startLA)->setTime(0, 0, 0)->setTimezone($utcTz)->format('Y-m-d\TH:i:s\Z');
$endTimeUTC   = (clone $endLA)->setTime(23, 59, 59)->setTimezone($utcTz)->format('Y-m-d\TH:i:s\Z');

$allPages = [];
$totalSlotsFetched = 0;
$pageNavigationToken = null;
$pageNumber = 1;
$rangeDates = [];

$rangeCursor = clone $startLA;
while ($rangeCursor <= $endLA) {
    $rangeDates[] = $rangeCursor->format('Y-m-d');
    $rangeCursor->modify('+1 day');
}

do {
    while (is_api_throttled()) {
        log_message("Cron paused. Waiting for Global API Lock to clear...");
        sleep(5);
    }

    if ($pageNavigationToken) {
        $queryParams = [
            'pageNavigationToken' => $pageNavigationToken,
            'pageNumber' => $pageNumber,
            'itemsPerPage' => 300
        ];
    } else {
        $queryParams = [
            'startTime' => $startTimeUTC, 
            'endTime' => $endTimeUTC, 
            'itemsPerPage' => 300
        ];
    }
    
    $requestUrl = API_BASE_URL . '?' . http_build_query($queryParams);
    $apiResponse = flee_bookeo_request('GET', $requestUrl, [
        'context' => 'cron_sync_slots_availability',
        'timeout' => 30,
        'headers' => [
            'X-Bookeo-apiKey: ' . FLEE_BOOKEO_API_KEY,
            'X-Bookeo-secretKey: ' . FLEE_BOOKEO_SECRET_KEY,
            'Accept: application/json'
        ]
    ]);
    $response = $apiResponse['body'];
    $httpCode = $apiResponse['code'];
    
    if ($httpCode === 200) {
        $responseData = json_decode($response, true);
        $slotsOnPage = $responseData['data'] ?? [];
        
        if (!empty($slotsOnPage)) {
            $allPages[] = $slotsOnPage; 
            $totalSlotsFetched += count($slotsOnPage);
        }
        
        $info = $responseData['info'] ?? [];
        $pageNavigationToken = $info['pageNavigationToken'] ?? null;
        $totalPages = (int)($info['totalPages'] ?? 1);
        
        log_message("Fetched page $pageNumber of $totalPages (" . count($slotsOnPage) . " slots).");
        
        if ($pageNumber >= $totalPages) {
            break; 
        }

        $pageNumber++; 
        
    } elseif ($httpCode === 429) {
        $responseData = json_decode($response, true);
        set_api_throttle((int)($responseData['retryAfter'] ?? 30)); 
        continue; 
    } else {
        log_message("API Error $httpCode. Aborting.");
        exit;
    }
    
    usleep(500000); 

} while (true); 

// DATABASE SWAP & GARBAGE COLLECTION
if ($totalSlotsFetched > 0) {
    try {
        $pdo->beginTransaction();
        
        // --- 1. GARBAGE COLLECTION: Delete any slots older than today ---
        $gcStmt = $pdo->prepare("DELETE FROM bookeo_slots_cache WHERE slot_date < :today");
        $gcStmt->execute([':today' => $startLA->format('Y-m-d')]);

        // --- 2. ATOMIC SWAP: Delete current 7-day range (Fixes "Ghost Slots" if an owner canceled a slot) ---
        $delStmt = $pdo->prepare("DELETE FROM bookeo_slots_cache WHERE slot_date BETWEEN :start_date AND :end_date");
        $delStmt->execute([':start_date' => $startLA->format('Y-m-d'), ':end_date' => $endLA->format('Y-m-d')]);
        
        // --- 3. BULK INSERT: Insert fresh API data ---
        $nowLA = clone $startLA; $nowLA->setTimestamp(time());
        $nowStr = $nowLA->format('Y-m-d H:i:s');
        $expiresAt = clone $nowLA; $expiresAt->modify("+11 minutes"); 
        $expStr = $expiresAt->format('Y-m-d H:i:s');
        
        $sqlBase = "
            INSERT INTO bookeo_slots_cache
                (product_id, slot_date, start_time_utc, start_time_local, event_id, available_seats, max_seats, cached_at, expires_at)
            VALUES
        ";
        $sqlSuffix = "
            ON DUPLICATE KEY UPDATE
                slot_date = VALUES(slot_date),
                start_time_utc = VALUES(start_time_utc),
                start_time_local = VALUES(start_time_local),
                available_seats = VALUES(available_seats),
                max_seats = VALUES(max_seats),
                cached_at = VALUES(cached_at),
                expires_at = VALUES(expires_at)
        ";
        $insertValues = [];
        $insertParams = [];
        $rowPlaceholder = "(?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $batchSize = 900; 
        
        $dtConverter = new DateTime('now', $utcTz);

        foreach ($allPages as $page) {
            foreach ($page as $slot) {
                if (empty($slot['productId']) || empty($slot['eventId'])) continue;
                
                $dtConverter->modify($slot['startTime']); 
                $dtConverter->setTimezone($utcTz);
                $utcStr = $dtConverter->format('Y-m-d\TH:i:s\Z');
                
                $dtConverter->setTimezone($losAngelesTz);
                $localStr = $dtConverter->format('Y-m-d H:i:s');
                $localDateStr = $dtConverter->format('Y-m-d');
                
                $avail = (int)($slot['numSeatsAvailable'] ?? 0);
                $max   = (int)($slot['maxSeats'] ?? 0);

                $insertValues[] = $rowPlaceholder;
                array_push($insertParams, $slot['productId'], $localDateStr, $utcStr, $localStr, $slot['eventId'], $avail, $max, $nowStr, $expStr);
                
                if (count($insertValues) >= $batchSize) {
                    $stmt = $pdo->prepare($sqlBase . implode(', ', $insertValues) . $sqlSuffix);
                    $stmt->execute($insertParams);
                    $insertValues = []; 
                    $insertParams = [];
                }
            }
        }
        
        if (!empty($insertValues)) {
            $stmt = $pdo->prepare($sqlBase . implode(', ', $insertValues) . $sqlSuffix);
            $stmt->execute($insertParams);
        }
        
        $pdo->commit();
        foreach ($rangeDates as $cacheDate) {
            flee_bookeo_mark_day_cache_fresh($cacheDate, $expStr);
        }
        log_message("DB Swap Complete. Cleaned old records. Bulk Inserted $totalSlotsFetched slots.");
    } catch (Exception $e) {
        $pdo->rollBack();
        log_message("DB Error: " . $e->getMessage());
    }
} else {
    $nowLA = clone $startLA;
    $nowLA->setTimestamp(time());
    $expiresAt = clone $nowLA;
    $expiresAt->modify("+11 minutes");
    foreach ($rangeDates as $cacheDate) {
        flee_bookeo_mark_day_cache_fresh($cacheDate, $expiresAt);
    }
    log_message("No slots fetched from Bookeo.");
}

log_message("Cron finished.");
?>
