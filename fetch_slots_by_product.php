<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

include('admin/db.php');

$requestedDate = $_GET['date'] ?? '';
$productId     = isset($_GET['productCode']) ? trim($_GET['productCode']) : '';
$selectedSlot  = $_GET['selectedSlot'] ?? '';

if (!$requestedDate || !$productId) {
    echo '<p>No slots available</p>';
    exit;
}

$washingtonTz = new DateTimeZone('America/Los_Angeles');
$utcTz        = new DateTimeZone('UTC');
$nowLocal     = new DateTime('now', $washingtonTz);

// ============================================================
// LOGGING
// ============================================================
function logSlots($msg) {
    $laTime = new DateTime("now", new DateTimeZone('America/Los_Angeles'));
    $entry  = "[" . $laTime->format('Y-m-d H:i:s') . "] $msg\n";
    file_put_contents(__DIR__ . "/fetch_slots_by_product.log", $entry, FILE_APPEND);
}

// ============================================================
// CACHE TTL with jitter — matches fetch_slots.php
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
// FILE MUTEX — prevents thundering herd
// ============================================================
function acquireLock($productId, $date) {
    $lockDir = sys_get_temp_dir() . '/bookeo_locks';
    if (!is_dir($lockDir)) {
        @mkdir($lockDir, 0777, true);
    }
    $lockFile = $lockDir . '/lock_' . md5($productId . '_' . $date) . '.lock';

    if (file_exists($lockFile) && (time() - filemtime($lockFile)) > 60) {
        @unlink($lockFile);
    }

    $fp = @fopen($lockFile, 'c');
    if (!$fp) return false;

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
// FETCH FROM BOOKEO — with retry on 429
// ============================================================
function fetchFromBookeoWithRetry($productId, $startUTC, $endUTC, $maxRetries = 3) {
    $url = "https://api.bookeo.com/v2/availability/slots"
         . "?productId={$productId}&startTime={$startUTC}&endTime={$endUTC}";

    $attempt     = 0;
    $waitSeconds = 0;

    while ($attempt <= $maxRetries) {
        if ($waitSeconds > 0) sleep($waitSeconds);

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

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200) {
            $apiData = json_decode($response, true);
            if (isset($apiData['data']) && is_array($apiData['data'])) {
                return ['success' => true, 'data' => $apiData['data']];
            }
            return ['success' => false, 'reason' => 'MALFORMED_JSON'];
        }

        if ($httpCode === 429) {
            $errData     = json_decode($response, true);
            $retryAfter  = isset($errData['retryAfter']) ? (int)$errData['retryAfter'] : 10;
            $waitSeconds = min($retryAfter + 1, 30);
            $attempt++;
            continue;
        }

        $waitSeconds = min(5 * ($attempt + 1), 20);
        $attempt++;
    }

    return ['success' => false, 'reason' => 'MAX_RETRIES_EXCEEDED'];
}

// ============================================================
// SAVE SLOTS TO DB — uses expires_at (matches fetch_slots.php schema)
// ============================================================
function saveSlotsToDB($pdo, $productId, $requestedDate, $slots, $nowLocal, $washingtonTz, $utcTz, $cacheTtl) {
    $expiresAt = (clone $nowLocal)->modify("+{$cacheTtl} seconds")->format('Y-m-d H:i:s');
    $nowStr    = $nowLocal->format('Y-m-d H:i:s');

    $pdo->beginTransaction();
    try {
        $pdo->prepare("DELETE FROM bookeo_slots_cache WHERE product_id = :pid AND slot_date = :sdate")
            ->execute([':pid' => $productId, ':sdate' => $requestedDate]);
        
        foreach ($slots as $slot) {
            $localDt = (new DateTime($slot['startTime'], $utcTz))->setTimezone($washingtonTz);

            $stmt = $pdo->prepare("
                INSERT INTO bookeo_slots_cache
                    (product_id, slot_date, start_time_utc, start_time_local,
                     event_id, available_seats, max_seats, cached_at, expires_at)
                VALUES
                    (:pid, :sdate, :utc, :local, :eid, :avail, :max, :now, :exp)
                ON DUPLICATE KEY UPDATE
                    available_seats = VALUES(available_seats),
                    cached_at       = VALUES(cached_at),
                    expires_at      = VALUES(expires_at)
            ");
            $stmt->execute([
                ':pid'   => $productId,
                ':sdate' => $localDt->format('Y-m-d'),
                ':utc'   => $slot['startTime'],
                ':local' => $localDt->format('Y-m-d H:i:s'),
                ':eid'   => $slot['eventId'],
                ':avail' => (int)($slot['numSeatsAvailable'] ?? 0),
                ':max'   => (int)($slot['maxSeats'] ?? 0),
                ':now'   => $nowStr,
                ':exp'   => $expiresAt,
            ]);
        }
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        logSlots("saveSlotsToDB FAILED for $productId / $requestedDate: " . $e->getMessage());
        return false;
    }
}

// ============================================================
// READ SLOTS FROM DB
// ============================================================
function readSlotsFromDB($pdo, $productId, $requestedDate) {
    $stmt = $pdo->prepare("
        SELECT product_id, start_time_local, event_id, available_seats, expires_at
        FROM bookeo_slots_cache
        WHERE product_id = ? AND slot_date = ?
        ORDER BY start_time_local ASC
    ");
    $stmt->execute([$productId, $requestedDate]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================================
// STEP 1: Check cache status
// ============================================================
$cached    = readSlotsFromDB($pdo, $productId, $requestedDate);
$isFresh   = false;

if (!empty($cached)) {
    // Use expires_at from first row (all rows for same product/date share same expires_at)
    $expiresAt = $cached[0]['expires_at'] ?? null;
    if ($expiresAt) {
        $expiresDt = new DateTime($expiresAt, $washingtonTz);
        $isFresh   = ($expiresDt >= $nowLocal);
    }
}

// ============================================================
// STEP 2: Fetch from API if cache is missing or stale
// ============================================================
if (empty($cached) || !$isFresh) {

    $lock = acquireLock($productId, $requestedDate);

    if (!$lock) {
        // Another worker is fetching — wait up to 3s for DB to populate
        $waited = 0;
        while ($waited < 3000000) {
            usleep(200000);
            $waited += 200000;
            $cached = readSlotsFromDB($pdo, $productId, $requestedDate);
            if (!empty($cached)) break;
        }
        logSlots("Lock busy for $productId / $requestedDate — waited for other worker");
    } else {
        // Double-check DB in case another request populated while we waited for lock
        $cached = readSlotsFromDB($pdo, $productId, $requestedDate);
        $isFresh = false;
        if (!empty($cached)) {
            $expiresAt = $cached[0]['expires_at'] ?? null;
            if ($expiresAt) {
                $expiresDt = new DateTime($expiresAt, $washingtonTz);
                $isFresh   = ($expiresDt >= $nowLocal);
            }
        }

        if (empty($cached) || !$isFresh) {
            try {
                $dateObj  = new DateTime($requestedDate, $washingtonTz);
                $startUTC = (clone $dateObj)->setTime(0, 0, 0)->setTimezone($utcTz)->format('Y-m-d\TH:i:s\Z');
                $endUTC   = (clone $dateObj)->setTime(23, 59, 59)->setTimezone($utcTz)->format('Y-m-d\TH:i:s\Z');

                $result = fetchFromBookeoWithRetry($productId, $startUTC, $endUTC);

                if ($result['success']) {
                    $cacheTtl = getCacheTtlWithJitter($dateObj, $nowLocal);
                    saveSlotsToDB($pdo, $productId, $requestedDate, $result['data'], $nowLocal, $washingtonTz, $utcTz, $cacheTtl);
                    $cached = readSlotsFromDB($pdo, $productId, $requestedDate);
                    logSlots("API fetch SUCCESS for $productId / $requestedDate (" . count($cached) . " slots)");
                } else {
                    logSlots("API fetch FAILED for $productId / $requestedDate: " . ($result['reason'] ?? 'unknown'));
                }
            } catch (Exception $e) {
                logSlots("Exception fetching $productId / $requestedDate: " . $e->getMessage());
            }
        }

        releaseLock($lock);
    }
}

// ============================================================
// STEP 3: Build HTML output
// ============================================================
$html    = '';
$counter = 0;

foreach ($cached as $slot) {
    $dt        = new DateTime($slot['start_time_local'], $washingtonTz);
    $time      = $dt->format('g:i A');
    $available = (int)$slot['available_seats'];
    $isFull    = ($available === 0);
    $eventId   = $slot['event_id'];
    $localStr  = $dt->format('Y-m-d H:i:s');

    // Skip past slots
    if ($dt->getTimestamp() < $nowLocal->getTimestamp()) continue;

    $minutesDiff = ($dt->getTimestamp() - $nowLocal->getTimestamp()) / 60;

    // Slots within 20 minutes → show "Call" button
    if ($minutesDiff <= 20 && !$isFull) {
        $html .= '
            <div class="time_slot_group time_slot_call">
                <span class="call-slot-btn" onclick="showCallPopup(\'' . $time . '\')">
                    ' . $time . '
                    <span class="Available_play_time">' . $available . ' Available</span>
                    <span class="Available_play_time">Call</span>
                </span>
            </div>';
        continue;
    }

    $slotId     = 'Boo_Prison_Escape_time_' . $productId . '_' . $counter++;
    $isSelected = ($selectedSlot && $selectedSlot === $localStr);

    $html .= '
        <div class="col-4 slot-box">
            <input type="radio"
                   name="lift-time-' . htmlspecialchars($productId) . '"
                   id="' . $slotId . '"
                   value="' . htmlspecialchars($localStr) . '"
                   class="Boo_Prison_Escape_time-slot"
                   data-start-time="' . htmlspecialchars($localStr) . '"
                   data-eventid="' . htmlspecialchars($eventId) . '"
                   data-available="' . $available . '"
                   ' . ($isFull    ? 'disabled' : '') . '
                   ' . ($isSelected ? 'checked'  : '') . ' />
            <label for="' . $slotId . '" class="Boo_Prison_Escape_time-slot-label">
                ' . $time . '
                <br><span class="Available_play_time">' . ($isFull ? 'Full' : $available . ' Available') . '</span>
            </label>
        </div>';
}

echo $html ?: '<p>No slots available</p>';