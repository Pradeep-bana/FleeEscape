<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

include('admin/db.php');

$requestedDate = $_GET['date'] ?? '';
$productIds = isset($_GET['productIds']) ? json_decode($_GET['productIds'], true) : [];

if (!$requestedDate || !is_array($productIds) || empty($productIds)) {
    echo json_encode(['error' => 'Missing date or product IDs']);
    return;
}

$washingtonTz = new DateTimeZone('America/Los_Angeles');
$todayDate = (new DateTime('today', $washingtonTz))->format('Y-m-d');   // <-- define TODAY

function logBookeo($msg) {
    file_put_contents(__DIR__ . "/bookeo_api.log", "[" . date("Y-m-d H:i:s") . "] $msg\n", FILE_APPEND);
}

function fetchFromBookeo($url, $retry = 0, $max = 3)
{
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => [
            'X-Bookeo-apiKey: AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC',
            'X-Bookeo-secretKey: RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4',
            'Accept: application/json'
        ],
    ]);

    $response = curl_exec($curl);
    $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $err = curl_error($curl);
        curl_close($curl);
        logBookeo("CURL Error: $err");
        return [500, ["error" => $err]];
    }

    curl_close($curl);
    // logBookeo("HTTP {$http} Response: $response");

    $data = json_decode($response, true);

    return [$http, $data];
}

/* ---------------------- DB FUNCTIONS ---------------------- */

function getCachedSlots(PDO $pdo, $productId, $slotDate) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM bookeo_slots_cache
        WHERE product_id = :pid AND slot_date = :d
        ORDER BY start_time_utc ASC
    ");
    $stmt->execute([':pid' => $productId, ':d' => $slotDate]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function insertCachedSlot(PDO $pdo, $row) {
    $sql = "
        INSERT INTO bookeo_slots_cache
        (product_id, slot_date, start_time_utc, start_time_local, event_id, available_seats,
         max_seats, raw_slot_id, created_at, updated_at)
        VALUES
        (:product_id, :slot_date, :start_time_utc, :start_time_local, :event_id, :available_seats,
         :max_seats, :raw_slot_id, :created_at, :updated_at)
    ";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($row);
}

function getNextAvailableDate(PDO $pdo, $productId, $currentDate) {
    $stmt = $pdo->prepare("
        SELECT slot_date 
        FROM bookeo_slots_cache
        WHERE product_id = :pid AND slot_date > :d
        GROUP BY slot_date
        ORDER BY slot_date ASC
        LIMIT 1
    ");
    $stmt->execute([':pid' => $productId, ':d' => $currentDate]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['slot_date'] ?? null;
}

/* ---------------------- MAIN ---------------------- */

$results = [];
$globalHasAnySlotToday = false;  // <-- track if ANY product has a valid slot for TODAY

foreach ($productIds as $productId) {

    // 1. CLEAN THE ID (Fixes "No Cache Found" if there are spaces)
    $productId = trim($productId); 

    /* ---------------- GET SLOTS CACHE TODAY ONLY ---------------- */

    $startDateTime = new DateTime($requestedDate . ' 00:00:00', $washingtonTz);
    $endDateTime = new DateTime($requestedDate . ' 23:59:59', $washingtonTz);
    $endDateTime->modify('+30 days');

    $startDateTime->setTimezone(new DateTimeZone('UTC'));
    $endDateTime->setTimezone(new DateTimeZone('UTC'));

    $startTime = $startDateTime->format('Y-m-d\TH:i:s\Z');
    $endTime   = $endDateTime->format('Y-m-d\TH:i:s\Z');

    /* CACHE CHECK */
    $cachedToday = getCachedSlots($pdo, $productId, $requestedDate);
    $useCache = false;

    // DEBUG: Write exactly what we are looking for
    file_put_contents("debug_slots.txt", "Looking for PID: [$productId] Date: [$requestedDate] - Found: " . count($cachedToday) . " rows\n", FILE_APPEND);

    if (!empty($cachedToday)) {

        // 2. TIMEZONE FIX (Fixes "Age: -6467" or random updates)
        
        // Get the last update time string from DB (e.g. "2025-12-03 23:03:31")
        $latestStr = max(array_column($cachedToday, 'updated_at'));
        
        // Create DateTime objects ensuring both are in Washington Time
        $latestDt = DateTime::createFromFormat('Y-m-d H:i:s', $latestStr, $washingtonTz);
        $nowWash = new DateTime('now', $washingtonTz);

        // Calculate difference in seconds
        $age = $nowWash->getTimestamp() - $latestDt->getTimestamp();

        // DEBUG: Check the calculated age
        file_put_contents("debug_slots.txt", " -> Match Found! Age is: $age seconds.\n", FILE_APPEND);

        // 3. LOGIC: Update only if older than 600 seconds (10 Minutes)
        // We add ($age >= -120) to handle small server time drifts where time looks slightly in future
        if ($age <= 600 && $age >= -120) {
            $useCache = false; //for now we are disabling server side cache
            file_put_contents("debug_slots.txt", " -> CACHE DISABLED (Forced Refresh).\n", FILE_APPEND);
        } else {
              file_put_contents("debug_slots.txt", " -> CACHE EXPIRED (Too old).\n", FILE_APPEND);
        }
    } else {
          file_put_contents("debug_slots.txt", " -> NO CACHE FOUND (Empty result).\n", FILE_APPEND);
    }

    /* API CALL IF NOT USING CACHE */
    if (!$useCache) {

        $url = "https://api.bookeo.com/v2/availability/slots?productId={$productId}&startTime={$startTime}&endTime={$endTime}&mode=calendar";
        list($status, $apiData) = fetchFromBookeo($url);

        if ($status == 200 && !empty($apiData["data"])) {

            $endDateStr = (new DateTime($requestedDate))->modify('+30 days')->format("Y-m-d");

            $stmt = $pdo->prepare("
                DELETE FROM bookeo_slots_cache 
                WHERE product_id = :pid AND slot_date >= :start AND slot_date <= :end
            ");
            $stmt->execute([
                ':pid' => $productId,
                ':start' => $requestedDate,
                ':end' => $endDateStr
            ]);

            $pdo->beginTransaction();
            $now = (new DateTime('now', $washingtonTz))->format("Y-m-d H:i:s");
            
            file_put_contents("debug_slots.txt", " -> USING Database.\n", FILE_APPEND);

            foreach ($apiData["data"] as $slot) {

                $slotDate = (new DateTime(
                    $slot["startTime"],
                    new DateTimeZone('UTC')
                ))->setTimezone($washingtonTz)->format('Y-m-d');

                insertCachedSlot($pdo, [
                    'product_id' => $productId,
                    'slot_date' => $slotDate,
                    'start_time_utc' => $slot["startTime"],
                    'start_time_local' => $slot["startTime"],
                    'event_id' => $slot["eventId"],
                    'available_seats' => $slot["numSeatsAvailable"],
                    'max_seats' => $slot["maxSeats"] ?? 0,
                    'raw_slot_id' => $slot["rawSlotId"] ?? '',
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }

            $pdo->commit();
        }
    }else {
          file_put_contents("debug_slots.txt", " -> NO DB Data FOUND (Empty result).\n", FILE_APPEND);
    }

    /* LOAD REQUESTED-DATE SLOTS */
    $slots = getCachedSlots($pdo, $productId, $requestedDate);

    /* CHECK VALID SLOTS (future only) */
    $validSlots = [];
    $nowLocal = new DateTime("now", $washingtonTz);

    foreach ($slots as $slot) {
        $dt = new DateTime($slot['start_time_local'], $washingtonTz);
        if ($dt->getTimestamp() >= $nowLocal->getTimestamp()) {
            $validSlots[] = $slot;
        }
    }

    /* --------- NEW LOGIC ---------
       AUTO MOVE ONLY IF:
       - requested date is TODAY
       - AND this product has zero valid slots
       - AND ALL games also have zero valid slots
    ------------------------------------*/

    if ($requestedDate === $todayDate) {

        if (!empty($validSlots)) {
            $globalHasAnySlotToday = true; // track that at least one product has slot today
        }
    }

    $results[$productId] = [
        'original_slots' => $slots,
        'valid_slots' => $validSlots
    ];
}

/* -------- GLOBAL DECISION FOR TODAY -------- */

/* -------- GLOBAL DECISION FOR TODAY -------- */

if ($requestedDate === $todayDate) {

    // If ANY product has slots today → stay on today
    if ($globalHasAnySlotToday) {
        $finalDate = $requestedDate;

    } else {
        // ALL products have zero valid slots today → check future only for TODAY case

        $nextDateCandidates = [];

        // foreach ($productIds as $pid) {
        //     $d = getNextAvailableDate($pdo, $pid, $requestedDate);
        //     if ($d) $nextDateCandidates[] = $d;
        // }

        // Only move date IF at least one future slot exists for ANY product
        if (!empty($nextDateCandidates)) {
            $finalDate = min($nextDateCandidates);
        } else {
            // No future slots → stay on today and show "No slots available"
            $finalDate = $requestedDate;
        }
    }

} else {
    // ANY future date → NEVER auto shift date
    $finalDate = $requestedDate;
}

/* -------- BUILD FINAL HTML FOR EACH PRODUCT -------- */

$output = [];

foreach ($productIds as $productId) {

    $slots = getCachedSlots($pdo, $productId, $finalDate);

    $nowLocal = new DateTime("now", $washingtonTz);
    $html = "";

    foreach ($slots as $i => $slot) {

        $dt = new DateTime($slot['start_time_local'], $washingtonTz);

        if ($dt->getTimestamp() < $nowLocal->getTimestamp()) continue;

        $time = $dt->format("g:i A");
        $available = (int)$slot["available_seats"];
        $isFull = ($available === 0);
        $eventId = $slot["event_id"];

        $minutesDiff = ($dt->getTimestamp() - $nowLocal->getTimestamp()) / 60;

        if ($minutesDiff <= 20 && !$isFull) {
            $html .= '
                <div class="time_slot_group time_slot_call">
                    <span class="call-slot-btn" onclick="showCallPopup(\''.$time.'\')">
                        '.$time.'
                        <span class="Available_play_time">'.$available.' Available</span>
                        <span class="Available_play_time">Call</span>
                    </span>
                </div>';
            continue;
        }

        $id = 'slot-'.$productId.'-'.$i;

        $html .= '
            <div class="time_slot_group '.($isFull?"time_slot_full":"").'">
                <input type="radio"
                       name="lift-time-'.$productId.'"
                       id="'.$id.'"
                       value="'.$slot['start_time_local'].'"
                       data-start-time="'.$slot['start_time_local'].'"
                       data-eventid="'.$eventId.'"
                       data-available="'.$available.'"
                       '.($isFull?"disabled":"").'
                       hidden>
                <label for="'.$id.'">
                    '.$time.'
                    <span class="Available_play_time">'.($isFull?"Full":$available.' Available').'</span>
                </label>
            </div>';
    }

    if ($html == "") {
        $html = "<p>No slots available</p>";
    }

    $output[$productId] = [
        'html' => $html,
        'date' => $finalDate
    ];
}

echo json_encode($output);
