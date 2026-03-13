<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$productId     = $_GET['productCode'] ?? '';
$date          = $_GET['date'] ?? ''; 
$selectedSlot  = $_GET['selectedSlot'] ?? '';

if (!$productId || !$date) {
    echo "<p style='color:red;'>Missing productId or date</p>";
    return; // ✅ do not kill script, just stop here
}

$washingtonTz = new DateTimeZone('America/Los_Angeles');

try {
    $startDateTime = new DateTime($date . ' 00:00:00', $washingtonTz);
    $endDateTime   = new DateTime($date . ' 23:59:59', $washingtonTz);
} catch (Exception $e) {
    echo "<p style='color:red;'>Invalid date format</p>";
    return; // ✅ graceful exit
}

$startDateTime->setTimezone(new DateTimeZone('UTC'));
$endDateTime->setTimezone(new DateTimeZone('UTC'));

$startTime = $startDateTime->format('Y-m-d\TH:i:s\Z');
$endTime   = $endDateTime->format('Y-m-d\TH:i:s\Z');

$url = "https://api.bookeo.com/v2/availability/slots?productId={$productId}&startTime={$startTime}&endTime={$endTime}";

// Logging helper
function logBookeo($message) {
    $logFile = __DIR__ . "/bookeo_api.log";
    $entry   = "[" . date("Y-m-d H:i:s") . "] " . $message . PHP_EOL;
    file_put_contents($logFile, $entry, FILE_APPEND);
}

// Fetch API data with retry on 429
function fetchFromBookeo($url, $retryCount = 0, $maxRetries = 3) {
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
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ($response === false) {
        $err = curl_error($curl);
        curl_close($curl);
        logBookeo("CURL Error: " . $err);
        return [500, ["error" => $err]];
    }

    curl_close($curl);
    logBookeo("HTTP {$httpcode} Response: " . $response);
    $data = json_decode($response, true);

    // Handle 429 Too Many Requests
    if ($httpcode == 429 && $retryCount < $maxRetries) {
        $wait = isset($data['retryAfter']) ? (int)$data['retryAfter'] : 5;
        logBookeo("429 Too Many Requests. Retrying after {$wait}s (Attempt: " . ($retryCount + 1) . ")");
        sleep(($wait > 0 && $wait <= 20) ? $wait : 5);
        return fetchFromBookeo($url, $retryCount + 1, $maxRetries);
    }

    return [$httpcode, $data];
}

list($httpStatus, $data) = fetchFromBookeo($url);

// ✅ Gracefully handle 429 without killing script
if ($httpStatus == 429) {
    $retryAfter = isset($data['retryAfter']) ? (int)$data['retryAfter'] : 60;
    echo "<p style='color:red;'>Too many requests. Please wait {$retryAfter} seconds and try again.</p>";
    $data['data'] = []; // ✅ prevent foreach errors later
}

// ✅ Gracefully handle empty or invalid response
if ($httpStatus != 200 || !isset($data['data']) || !is_array($data['data']) || count($data['data']) === 0) {
    echo "<p>No slots available or API error.</p>";
    return;
}

// Display slots
foreach ($data['data'] as $i => $slot) {
    $dt = new DateTime($slot['startTime']);
    $dt->setTimezone($washingtonTz);
    $time = $dt->format('g:i A');

    $eventId   = $slot['eventId'] ?? 0;
    $available = (int)($slot['numSeatsAvailable'] ?? 0);
    $isFull    = ($available === 0);

    $slotId = 'lift-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $productId) . '-' . $i;
    $isSelected = ($selectedSlot && $selectedSlot === $slot['startTime']);

    echo '<div class="time_slot_group' . ($isFull ? ' time_slot_full' : '') . '">
        <input type="radio"
           name="lift-time-' . htmlspecialchars($productId) . '"
           id="' . $slotId . '"
           value="' . htmlspecialchars($slot['startTime']) . '" 
           data-eventid="' . htmlspecialchars($eventId) . '"
           data-available="' . $available . '"
           ' . ($isFull ? 'disabled' : '') . ' 
           ' . ($isSelected ? 'checked' : '') . ' hidden >
        <label for="' . $slotId . '" class="' . ($isSelected ? 'slot-selected' : '') . '"> '
           . $time .
           ' <span class="Available_play_time">' . ($isFull ? 'Full' : $available . ' Available') . '</span>
        </label>
    </div>';
}
?>
