<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

include('admin/db.php');

$requestedDate = $_GET['date'] ?? '';
$productIds    = isset($_GET['productIds']) ? json_decode($_GET['productIds'], true) : [];

if (!$requestedDate || !is_array($productIds) || empty($productIds)) {
    echo json_encode(['error' => 'Missing date or product IDs']);
    exit;
}

$washingtonTz = new DateTimeZone('America/Los_Angeles');
$utcTz        = new DateTimeZone('UTC');
$logFile      = __DIR__ . "/debug_slots_flash.txt";

// ─── HELPERS ─────────────────────────────────────────────────────────────────

function logFlash(string $msg): void {
    global $logFile;
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] $msg\n", FILE_APPEND);
}

// ─── CACHE TTL with jitter — matches fetch_slots.php ─────────────────────────

function getCacheTtlWithJitter(DateTime $requestedDate, DateTime $now): int {
    $diffDays = (int)$now->diff($requestedDate)->days;
    if ($diffDays === 0)     $base = 120;
    elseif ($diffDays <= 3)  $base = 300;
    elseif ($diffDays <= 14) $base = 900;
    elseif ($diffDays <= 60) $base = 3600;
    else                     $base = 86400;

    $jitter = (int)($base * 0.10);
    return $base + rand(-$jitter, $jitter);
}

// ─── BOOKEO API ───────────────────────────────────────────────────────────────

function fetchFromBookeo(string $url, int $retry = 0, int $max = 3): array {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => [
            'X-Bookeo-apiKey: AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC',
            'X-Bookeo-secretKey: RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4',
            'Accept: application/json'
        ],
    ]);

    $response = curl_exec($curl);
    $http     = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err      = curl_error($curl);
    curl_close($curl);

    if ($response === false) {
        logFlash("CURL Error: $err");
        return [500, []];
    }

    $data = json_decode($response, true) ?? [];

    if ($http === 429 && $retry < $max) {
        $wait = isset($data['retryAfter']) ? (int)$data['retryAfter'] : 5;
        sleep(min($wait, 10));
        return fetchFromBookeo($url, $retry + 1, $max);
    }

    return [$http, $data];
}

// ─── DB HELPERS ──────────────────────────────────────────────────────────────

function getCachedSlots(PDO $pdo, string $productId, string $date): array {
    $stmt = $pdo->prepare("
        SELECT * FROM bookeo_slots_cache
        WHERE product_id = :pid AND slot_date = :d
        ORDER BY start_time_local ASC
    ");
    $stmt->execute([':pid' => $productId, ':d' => $date]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDealHours(PDO $pdo): int {
    $stmt = $pdo->prepare("SELECT deal_hours FROM tbl_flash_deal WHERE is_active = 1 LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? (int)$row['deal_hours'] : 0;
}

// ─── CACHE FRESHNESS — uses expires_at (matches fetch_slots.php schema) ───────

function isCacheFresh(array $cached, DateTimeZone $tz, string $productId, string $date): bool {
    $expiries = array_filter(
        array_column($cached, 'expires_at'),
        fn($v) => !empty($v)
    );

    if (empty($expiries)) {
        logFlash("FLASH | PID: $productId | Date: $date -> NO valid expires_at in cache rows");
        return false;
    }

    $latestExpiry = max($expiries);
    $expiresDt    = DateTime::createFromFormat('Y-m-d H:i:s', $latestExpiry, $tz)
                    ?: new DateTime($latestExpiry, $tz);
    $nowWash      = new DateTime('now', $tz);
    $fresh        = ($expiresDt >= $nowWash);

    logFlash("FLASH | PID: $productId | Date: $date | Expires: $latestExpiry -> " . ($fresh ? 'USING CACHE' : 'CACHE EXPIRED'));
    return $fresh;
}

// ─── SAVE SLOTS ───────────────────────────────────────────────────────────────
function saveSlotsToDB($pdo, $productId, $requestedDate, $slots, $nowLocal, $washingtonTz, $utcTz, $cacheTtl) {
    $expiresAt = (clone $nowLocal)->modify("+{$cacheTtl} seconds")->format('Y-m-d H:i:s');
    $nowStr    = $nowLocal->format('Y-m-d H:i:s');

    $pdo->beginTransaction();
    try {
        // ── Step 1: Delete old slots for this product + date ─────────────────
        // Removes ghost slots (fully booked slots Bookeo no longer returns).
        // Using $requestedDate directly — correct for LA business hours since
        // slots never cross the UTC midnight boundary in practice.
        $pdo->prepare("DELETE FROM bookeo_slots_cache WHERE product_id = :pid AND slot_date = :sdate")
            ->execute([':pid' => $productId, ':sdate' => $requestedDate]);

        // ── Step 2: Insert fresh slots ───────────────────────────────────────
        // Loop may be empty for fully-booked days — delete above still ran,
        // which is correct (clears any ghosts).
        if (!empty($slots)) {
            $insStmt = $pdo->prepare("
                INSERT INTO bookeo_slots_cache
                    (product_id, slot_date, start_time_utc, start_time_local,
                     event_id, available_seats, max_seats, cached_at, expires_at)
                VALUES
                    (:pid, :sdate, :utc, :local, :eid, :avail, :max, :now, :exp)
            ");

            foreach ($slots as $slot) {
                $localDt = (new DateTime($slot['startTime'], $utcTz))->setTimezone($washingtonTz);
                $insStmt->execute([
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
        }

        // ── Step 3: Update fetch registry ────────────────────────────────────
        $pdo->prepare("
            INSERT INTO bookeo_fetch_registry (product_id, slot_date, fetched_at, fetch_source)
            VALUES (:pid, :d, :now, 'api')
            ON DUPLICATE KEY UPDATE fetched_at = :now, fetch_source = 'api'
        ")->execute([':pid' => $productId, ':d' => $requestedDate, ':now' => $nowStr]);

        $pdo->commit();
        return true;

    } catch (Exception $e) {
        $pdo->rollBack();
        logSlots("saveSlotsToDB FAILED for $productId / $requestedDate: " . $e->getMessage());
        return false;
    }
}

// ─── BUILD SLOT HTML ──────────────────────────────────────────────────────────

function buildSlotHtml(array $cached, string $productId, int $dealHours, DateTimeZone $tz): string {
    $html     = '';
    $nowLocal = new DateTime('now', $tz);

    foreach ($cached as $i => $slot) {
        $dt          = new DateTime($slot['start_time_local'], $tz);
        $time        = $dt->format('g:i A');
        $available   = (int)$slot['available_seats'];
        $isFull      = ($available === 0);
        $eventId     = $slot['event_id'];
        $minutesDiff = ($dt->getTimestamp() - $nowLocal->getTimestamp()) / 60;

        if ($minutesDiff < 0) continue;

        if ($dealHours > 0 && $minutesDiff > ($dealHours * 60)) continue;

        if ($minutesDiff <= 20 && !$isFull) {
            $safeTime = htmlspecialchars($time, ENT_QUOTES);
            $html .= '
                <div class="time_slot_group time_slot_call">
                    <span class="call-slot-btn" onclick="showCallPopup(\'' . $safeTime . '\')">
                        ' . $safeTime . '
                        <span class="Available_play_time">' . $available . ' Available</span>
                        <span class="Available_play_time">Call</span>
                    </span>
                </div>';
            continue;
        }

        $id = 'lift-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $productId) . '-' . $i;

        $html .= '
            <div class="time_slot_group ' . ($isFull ? 'time_slot_full' : '') . '">
                <input type="radio"
                       name="lift-time-' . $productId . '"
                       id="' . $id . '"
                       value="' . htmlspecialchars($slot['start_time_local'], ENT_QUOTES) . '"
                       data-start-time="' . htmlspecialchars($slot['start_time_local'], ENT_QUOTES) . '"
                       data-eventid="' . htmlspecialchars($eventId, ENT_QUOTES) . '"
                       data-available="' . $available . '"
                       ' . ($isFull ? 'disabled' : '') . '
                       hidden>
                <label for="' . $id . '">
                    ' . $time . '
                    <span class="Available_play_time">' . ($isFull ? 'Full' : $available . ' Available') . '</span>
                </label>
            </div>';
    }

    return $html;
}

// ─── MAIN LOOP ────────────────────────────────────────────────────────────────

$dealHours = getDealHours($pdo);
$results   = [];

foreach ($productIds as $rawProductId) {
    $productId = trim($rawProductId);
    if (empty($productId)) continue;

    $currentDate       = $requestedDate;
    $attempts          = 0;
    $usedDateForResult = $requestedDate;

    while ($attempts < 2) {

        try {
            $dateObj = new DateTime($currentDate, $washingtonTz);
            $startDt = (clone $dateObj)->setTime(0, 0, 0)->setTimezone($utcTz);
            $endDt   = (clone $dateObj)->setTime(23, 59, 59)->setTimezone($utcTz);
        } catch (Exception $e) {
            logFlash("Invalid date '$currentDate': " . $e->getMessage());
            break;
        }

        $startTime = $startDt->format('Y-m-d\TH:i:s\Z');
        $endTime   = $endDt->format('Y-m-d\TH:i:s\Z');

        $cached   = getCachedSlots($pdo, $productId, $currentDate);
        $useCache = !empty($cached) && isCacheFresh($cached, $washingtonTz, $productId, $currentDate);

        if (!$useCache) {
            logFlash("Fetching API for PID: $productId | Date: $currentDate");

            $url = "https://api.bookeo.com/v2/availability/slots"
                 . "?productId={$productId}&startTime={$startTime}&endTime={$endTime}";

            [$status, $apiData] = fetchFromBookeo($url);

            // FIX 2: save on ANY 200 — even empty data means fully booked,
            // old ghost slots must be deleted
            if ($status === 200) {
                $slots    = $apiData['data'] ?? [];
                $nowLocal = new DateTime('now', $washingtonTz);
                $cacheTtl = getCacheTtlWithJitter($dateObj, $nowLocal);
                saveSlotsToDB($pdo, $productId, $currentDate, $slots, $nowLocal, $washingtonTz, $utcTz, $cacheTtl);
            } else {
                logFlash("API returned status $status for PID: $productId | Date: $currentDate");
            }

            $cached = getCachedSlots($pdo, $productId, $currentDate);
        }

        if (empty($cached)) {
            $attempts++;
            $currentDate = (new DateTime($currentDate, $washingtonTz))
                ->modify('+1 day')->format('Y-m-d');
            continue;
        }

        $html = buildSlotHtml($cached, $productId, $dealHours, $washingtonTz);

        if (trim($html) !== '') {
            $usedDateForResult   = $currentDate;
            $results[$productId] = ['html' => $html, 'date' => $usedDateForResult];
            break;
        }

        $attempts++;
        $currentDate = (new DateTime($currentDate, $washingtonTz))
            ->modify('+1 day')->format('Y-m-d');

        if ($attempts >= 2) {
            $results[$productId] = [
                'html' => "<p>No available slots found for {$requestedDate}" .
                          ($currentDate !== $requestedDate ? " or {$currentDate}" : "") . ".</p>",
                'date' => $currentDate,
            ];
        }
    }
}

echo json_encode($results);