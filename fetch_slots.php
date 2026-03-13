<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

include('admin/db.php');

$washingtonTz = new DateTimeZone('America/Los_Angeles');
$utcTz        = new DateTimeZone('UTC');
$nowLocal     = new DateTime('now', $washingtonTz);

$requestedDate = $_GET['date'] ?? '';
$productIds    = isset($_GET['productIds'])
    ? array_filter(array_map('trim', json_decode($_GET['productIds'], true) ?? []))
    : [];

if (!$requestedDate || empty($productIds)) {
    echo json_encode(['error' => 'Missing inputs']);
    exit;
}

try {
    $dateObj = new DateTime($requestedDate, $washingtonTz);
} catch (Exception $e) {
    echo json_encode(['error' => 'Invalid date']);
    exit;
}

// ============================================================
// LOGGING
// ============================================================
function logMessage($msg) {
    $laTime = new DateTime("now", new DateTimeZone('America/Los_Angeles'));
    $entry  = "--------------------------------------------------------\n";
    $entry .= "TIMESTAMP: " . $laTime->format('Y-m-d h:i:s A') . "\n";
    $entry .= $msg . "\n";
    $entry .= "--------------------------------------------------------\n\n";
    file_put_contents(__DIR__ . "/slots_request_response.log", $entry, FILE_APPEND);
}

// ============================================================
// CACHE TTL with jitter — prevents all products expiring at once
// ============================================================
function getCacheTtlWithJitter($requestedDate, $now) {
    $diffDays = (int)$now->diff($requestedDate)->days;
    if ($diffDays === 0)      $base = 120;
    elseif ($diffDays <= 3)   $base = 300;
    elseif ($diffDays <= 14)  $base = 900;
    elseif ($diffDays <= 60)  $base = 3600;
    else                      $base = 86400;

    $jitter = (int)($base * 0.10);
    return $base + rand(-$jitter, $jitter);
}

// ============================================================
// FILE MUTEX — prevents thundering herd (multiple workers
// fetching the same product+date simultaneously)
// Works on all shared hosting — just uses the filesystem
// ============================================================
function acquireLock($productId, $date) {
    $lockDir = sys_get_temp_dir() . '/bookeo_locks';
    if (!is_dir($lockDir)) {
        @mkdir($lockDir, 0777, true);
    }
    $lockFile = $lockDir . '/lock_' . md5($productId . '_' . $date) . '.lock';

    // Auto-clean stale locks older than 60 seconds
    if (file_exists($lockFile) && (time() - filemtime($lockFile)) > 60) {
        @unlink($lockFile);
    }

    $fp = @fopen($lockFile, 'c');
    if (!$fp) return false;

    // LOCK_NB = non-blocking: returns false immediately if locked
    if (flock($fp, LOCK_EX | LOCK_NB)) {
        return $fp;
    }
    fclose($fp);
    return false;
}

function releaseLock($fp) {
    if ($fp) {
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

// ============================================================
// FETCH FROM BOOKEO — sequential, retries on 429
// ============================================================
function fetchFromBookeoWithRetry($productId, $startUTC, $endUTC, $maxRetries = 3) {
    $url = "https://api.bookeo.com/v2/availability/slots"
         . "?productId={$productId}&startTime={$startUTC}&endTime={$endUTC}&mode=calendar";

    $attempt     = 0;
    $waitSeconds = 0;
    $attemptLog  = []; // Capture every attempt for debugging

    while ($attempt <= $maxRetries) {
        if ($waitSeconds > 0) {
            sleep($waitSeconds);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'X-Bookeo-apiKey: AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC',
                'X-Bookeo-secretKey: RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4',
                'Accept: application/json',
            ],
        ]);

        $response    = curl_exec($ch);
        $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError   = curl_error($ch);
        $totalTime   = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        curl_close($ch);

        // Log every attempt with full raw response
        $attemptLog[] = [
            'attempt'      => $attempt + 1,
            'http_code'    => $httpCode,
            'curl_error'   => $curlError ?: null,
            'response_ms'  => round($totalTime * 1000),
            'raw_response' => $response, // Full Bookeo response — never truncated
        ];

        if ($httpCode === 200) {
            $apiData = json_decode($response, true);
            if (isset($apiData['data']) && is_array($apiData['data'])) {
                return ['success' => true, 'data' => $apiData['data'], 'log' => $attemptLog];
            }
            return ['success' => false, 'reason' => 'MALFORMED_JSON', 'log' => $attemptLog];
        }

        if ($httpCode === 429) {
            $errData     = json_decode($response, true);
            $retryAfter  = isset($errData['retryAfter']) ? (int)$errData['retryAfter'] : 10;
            $waitSeconds = min($retryAfter + 1, 30);
            $attempt++;
            continue;
        }

        // 5xx or other errors
        $waitSeconds = min(5 * ($attempt + 1), 20);
        $attempt++;
    }

    return ['success' => false, 'reason' => 'MAX_RETRIES_EXCEEDED', 'log' => $attemptLog];
}

// ============================================================
// SAVE SLOTS TO DB
// ============================================================
function saveSlotsToDB($pdo, $productId, $requestedDate, $slots, $nowLocal, $washingtonTz, $utcTz, $cacheTtl) {
    $expiresAt = (clone $nowLocal)->modify("+{$cacheTtl} seconds")->format('Y-m-d H:i:s');
    $nowStr    = $nowLocal->format('Y-m-d H:i:s');

    $pdo->beginTransaction();
    try {
        // ---------------------------------------------------------
        // 1. DELETE OLD SLOTS for this specific product + date first
        //    This ensures fully booked slots (which disappear from API) 
        //    are removed from our database.
        // ---------------------------------------------------------
        $delStmt = $pdo->prepare("DELETE FROM bookeo_slots_cache WHERE product_id = :pid AND slot_date = :sdate");
        $delStmt->execute([':pid' => $productId, ':sdate' => $requestedDate]);

        // ---------------------------------------------------------
        // 2. INSERT NEW SLOTS
        // ---------------------------------------------------------
        $stmt = $pdo->prepare("
            INSERT INTO bookeo_slots_cache
                (product_id, slot_date, start_time_utc, start_time_local,
                 event_id, available_seats, max_seats, cached_at, expires_at)
            VALUES
                (:pid, :sdate, :utc, :local, :eid, :avail, :max, :now, :exp)
        ");

        foreach ($slots as $slot) {
            $localDt = (new DateTime($slot['startTime'], $utcTz))->setTimezone($washingtonTz);

            $stmt->execute([
                ':pid'   => $productId,
                ':sdate' => $localDt->format('Y-m-d'),
                ':utc'   => $slot['startTime'],
                ':local' => $localDt->format('Y-m-d H:i:s'),
                ':eid'   => $slot['eventId'],
                ':avail' => (int)(isset($slot['numSeatsAvailable']) ? $slot['numSeatsAvailable'] : 0),
                ':max'   => (int)(isset($slot['maxSeats']) ? $slot['maxSeats'] : 0),
                ':now'   => $nowStr,
                ':exp'   => $expiresAt,
            ]);
        }

        // Update registry
        $stmtReg = $pdo->prepare("
            INSERT INTO bookeo_fetch_registry (product_id, slot_date, fetched_at, fetch_source)
            VALUES (:pid, :d, :now, 'api')
            ON DUPLICATE KEY UPDATE fetched_at = :now, fetch_source = 'api'
        ");
        $stmtReg->execute([':pid' => $productId, ':d' => $requestedDate, ':now' => $nowStr]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        // Log error if needed
        file_put_contents(__DIR__ . "/db_error.log", $e->getMessage(), FILE_APPEND);
        return false;
    }
}

// ============================================================
// READ SLOTS FROM DB for one product
// ============================================================
function readSlotsFromDB($pdo, $productId, $requestedDate) {
    $stmt = $pdo->prepare("
        SELECT product_id, start_time_local, event_id, available_seats
        FROM bookeo_slots_cache
        WHERE product_id = ? AND slot_date = ?
        ORDER BY start_time_local ASC
    ");
    $stmt->execute([$productId, $requestedDate]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// STEP 1: Check cache status for all products at once
// Using GROUP BY — works on MySQL 5.6, 5.7, 8.x
// ============================================================
$cacheStatus = []; // product_id => 'fresh' | 'stale' | 'missing'
$cachedData  = []; // product_id => array of rows

$placeholders = implode(',', array_fill(0, count($productIds), '?'));
$stmt = $pdo->prepare("
    SELECT product_id, MAX(expires_at) as max_expires, COUNT(*) as slot_count
    FROM bookeo_slots_cache
    WHERE product_id IN ($placeholders)
      AND slot_date = ?
    GROUP BY product_id
");
$stmt->execute(array_merge(array_values($productIds), [$requestedDate]));
$expiryMap = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $expiryMap[$row['product_id']] = $row;
}

foreach ($productIds as $pid) {
    if (!isset($expiryMap[$pid]) || (int)$expiryMap[$pid]['slot_count'] === 0) {
        $cacheStatus[$pid] = 'missing';
    } else {
        $expires = new DateTime($expiryMap[$pid]['max_expires'], $washingtonTz);
        $cacheStatus[$pid] = ($expires >= $nowLocal) ? 'fresh' : 'stale';
    }
}

// Pre-load data for products that already have cache (fresh or stale)
$haveCache = [];
foreach ($productIds as $pid) {
    if ($cacheStatus[$pid] === 'fresh' || $cacheStatus[$pid] === 'stale') {
        $haveCache[] = $pid;
    }
}

if (!empty($haveCache)) {
    $ph   = implode(',', array_fill(0, count($haveCache), '?'));
    $stmt = $pdo->prepare("
        SELECT product_id, start_time_local, event_id, available_seats
        FROM bookeo_slots_cache
        WHERE product_id IN ($ph)
          AND slot_date = ?
        ORDER BY product_id, start_time_local ASC
    ");
    $stmt->execute(array_merge(array_values($haveCache), [$requestedDate]));
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $cachedData[$row['product_id']][] = $row;
    }
}

// ============================================================
// STEP 2: Classify what needs fetching
// ============================================================
$mustFetch   = []; // No data at all → user must wait
$shouldFetch = []; // Stale data → opportunistic refresh
$apiErrors   = [];

foreach ($productIds as $pid) {
    if ($cacheStatus[$pid] === 'missing') {
        $mustFetch[] = $pid;
    } elseif ($cacheStatus[$pid] === 'stale') {
        $shouldFetch[] = $pid;
    }
}

// ============================================================
// STEP 3: Blocking fetch for MISSING products
// Sequential + 300ms gap = max 9 products * 300ms = ~2.7s worst case
// In practice, most products will be cached after first page load
// ============================================================
if (!empty($mustFetch)) {
    $startUTC = (clone $dateObj)->setTime(0, 0, 0)->setTimezone($utcTz)->format('Y-m-d\TH:i:s\Z');
    $endUTC   = (clone $dateObj)->setTime(23, 59, 59)->setTimezone($utcTz)->format('Y-m-d\TH:i:s\Z');

    foreach ($mustFetch as $index => $productId) {
        if ($index > 0) {
            usleep(300000); // 300ms between requests
        }

        $lock = acquireLock($productId, $requestedDate);

        if (!$lock) {
            // Another worker is already fetching this — wait up to 3s for DB to populate
            $waited = 0;
            $rows   = [];
            while ($waited < 3000000) {
                usleep(200000);
                $waited += 200000;
                $rows = readSlotsFromDB($pdo, $productId, $requestedDate);
                if (!empty($rows)) break;
            }
            $cachedData[$productId]  = $rows;
            $cacheStatus[$productId] = 'fetched_by_other';
            continue;
        }

        // Double-check DB (another request may have populated while we waited for lock)
        $existing = readSlotsFromDB($pdo, $productId, $requestedDate);
        if (!empty($existing)) {
            $cachedData[$productId]  = $existing;
            $cacheStatus[$productId] = 'fresh';
            releaseLock($lock);
            continue;
        }

        // Actually fetch from Bookeo
        $result = fetchFromBookeoWithRetry($productId, $startUTC, $endUTC);
        if ($result['success']) {
            $cacheTtl = getCacheTtlWithJitter($dateObj, $nowLocal);
            saveSlotsToDB($pdo, $productId, $requestedDate, $result['data'], $nowLocal, $washingtonTz, $utcTz, $cacheTtl);
            $cachedData[$productId]  = readSlotsFromDB($pdo, $productId, $requestedDate);
            $cacheStatus[$productId] = 'fetched_fresh';
        } else {
            $apiErrors[$productId]   = $result; // Store full result for logging
            $cachedData[$productId]  = [];
        }

        releaseLock($lock);
    }
}

// ============================================================
// STEP 4: Acquire locks for stale refresh (non-blocking)
// If another worker has the lock, skip — they're handling it
// ============================================================
$lockedForRefresh = [];
$refreshLocks     = [];

foreach ($shouldFetch as $productId) {
    $lock = acquireLock($productId, $requestedDate);
    if ($lock) {
        $lockedForRefresh[]             = $productId;
        $refreshLocks[$productId]       = $lock;
    }
    // No lock = another process is refreshing — serve stale, skip
}

// ============================================================
// STEP 5: Build HTML output from best available data
// ============================================================
$output = [];

foreach ($productIds as $productId) {
    $slots   = isset($cachedData[$productId]) ? $cachedData[$productId] : [];
    $html    = '';
    $counter = 0;

    // Source label for logging
    $status = isset($cacheStatus[$productId]) ? $cacheStatus[$productId] : 'unknown';
    if ($status === 'fresh')             $sourceType = "CACHE (FRESH)";
    elseif ($status === 'stale')         $sourceType = "CACHE (STALE - refreshing bg)";
    elseif ($status === 'fetched_fresh') $sourceType = "API (FRESH)";
    elseif ($status === 'fetched_by_other') $sourceType = "DB (OTHER WORKER)";
    else                                 $sourceType = "UNKNOWN";

    // Log
    $startUTC = (clone $dateObj)->setTime(0, 0, 0)->setTimezone($utcTz)->format('Y-m-d\TH:i:s\Z');
    $endUTC   = (clone $dateObj)->setTime(23, 59, 59)->setTimezone($utcTz)->format('Y-m-d\TH:i:s\Z');
    $logUrl   = "https://api.bookeo.com/v2/availability/slots?productId={$productId}&startTime={$startUTC}&endTime={$endUTC}&mode=calendar";
    $logMsg   = "SOURCE: $sourceType\nREQUEST URL: $logUrl\n";
    if (isset($apiErrors[$productId])) {
        $errResult = $apiErrors[$productId];
        $logMsg .= "STATUS: FAILED\n";
        $logMsg .= "REASON: " . $errResult['reason'] . "\n";
        $logMsg .= "ATTEMPTS: " . count($errResult['log']) . "\n";
        foreach ($errResult['log'] as $attempt) {
            $logMsg .= "  Attempt {$attempt['attempt']}: HTTP {$attempt['http_code']} ({$attempt['response_ms']}ms)";
            if ($attempt['curl_error']) {
                $logMsg .= " CURL_ERROR: {$attempt['curl_error']}";
            }
            $logMsg .= "\n  RAW RESPONSE: " . $attempt['raw_response'] . "\n";
        }
    } else {
        $logMsg .= "STATUS: SUCCESS\nDATA: " . json_encode(['productId' => $productId, 'slots' => $slots]);
    }
    logMessage($logMsg);

    // Build slot HTML
    foreach ($slots as $slot) {
        $dt = new DateTime($slot['start_time_local'], $washingtonTz);
        if ($dt->getTimestamp() < $nowLocal->getTimestamp()) continue;

        $time        = $dt->format('g:i A');
        $available   = (int)$slot['available_seats'];
        $isFull      = ($available === 0);
        $eventId     = $slot['event_id'];
        $localStr    = $dt->format('Y-m-d H:i:s');
        $minutesDiff = ($dt->getTimestamp() - $nowLocal->getTimestamp()) / 60;

        if ($minutesDiff <= 20 && !$isFull) {
            $html .= '<div class="time_slot_group time_slot_call">
                <span class="call-slot-btn" onclick="showCallPopup(\'' . $time . '\')">
                    ' . $time . '
                    <span class="Available_play_time">' . $available . ' Available</span>
                    <span class="Available_play_time">Call</span>
                </span>
            </div>';
            continue;
        }

        $uid = 'slot-' . $productId . '-' . $counter++;
        $html .= '<div class="time_slot_group ' . ($isFull ? 'time_slot_full' : '') . '">
            <input type="radio"
                   name="lift-time-' . $productId . '"
                   id="' . $uid . '"
                   value="' . $localStr . '"
                   data-start-time="' . $localStr . '"
                   data-eventid="' . $eventId . '"
                   data-available="' . $available . '"
                   ' . ($isFull ? 'disabled' : '') . '
                   hidden>
            <label for="' . $uid . '">
                ' . $time . '
                <span class="Available_play_time">' . ($isFull ? 'Full' : $available . ' Available') . '</span>
            </label>
        </div>';
    }

    $output[$productId] = [
        'html' => $html ?: '<p>No slots available</p>',
        'date' => $requestedDate,
    ];
}

// ============================================================
// STEP 6: Send response to browser FIRST
// ============================================================
$jsonOutput = json_encode($output);

if (function_exists('fastcgi_finish_request')) {
    // PHP-FPM: closes connection instantly, script keeps running
    echo $jsonOutput;
    fastcgi_finish_request();
} else {
    // Apache mod_php fallback
    ignore_user_abort(true);
    header("Content-Length: " . strlen($jsonOutput));
    header("Connection: close");
    ob_start();
    echo $jsonOutput;
    ob_end_flush();
    flush();
}

// ============================================================
// STEP 7: Background refresh for stale products
// User already received their response above — they won't feel this
// ============================================================
if (!empty($lockedForRefresh)) {
    $startUTC = (clone $dateObj)->setTime(0, 0, 0)->setTimezone($utcTz)->format('Y-m-d\TH:i:s\Z');
    $endUTC   = (clone $dateObj)->setTime(23, 59, 59)->setTimezone($utcTz)->format('Y-m-d\TH:i:s\Z');

    foreach ($lockedForRefresh as $index => $productId) {
        if ($index > 0) {
            usleep(300000); // 300ms between requests
        }

        $result = fetchFromBookeoWithRetry($productId, $startUTC, $endUTC);
        if ($result['success']) {
            $cacheTtl = getCacheTtlWithJitter($dateObj, $nowLocal);
            saveSlotsToDB($pdo, $productId, $requestedDate, $result['data'], $nowLocal, $washingtonTz, $utcTz, $cacheTtl);
        } else {
            $pdo->prepare("
                UPDATE bookeo_slots_cache
                SET expires_at = DATE_ADD(NOW(), INTERVAL 60 SECOND)
                WHERE product_id = ? AND slot_date = ?
            ")->execute([$productId, $requestedDate]);
            // Log the background failure too
            logMessage("BG REFRESH FAILED\nPRODUCT: $productId\nDEBUG: " . json_encode($result));
        }

        releaseLock($refreshLocks[$productId]);
    }
}