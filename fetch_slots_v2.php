<?php
// ==========================================
// CONFIGURATION: PRODUCTION MODE
// ==========================================
ini_set('display_errors', 0); 
error_reporting(E_ALL);
header('Content-Type: application/json');

// Define Timezones
$washingtonTz = new DateTimeZone('America/Los_Angeles');
$utcTz = new DateTimeZone('UTC');

// Define Cache Folder
$cacheFolder = __DIR__ . '/json_cache';
if (!file_exists($cacheFolder)) {
    mkdir($cacheFolder, 0777, true);
}

// ==========================================
// 1. LOGGING
// ==========================================
function logMessage($msg) {
    $laTime = new DateTime("now", new DateTimeZone('America/Los_Angeles'));
    $entry = "========================================\n";
    $entry .= "DATE (LA Time): " . $laTime->format('Y-m-d h:i:s A') . "\n";
    $entry .= $msg . "\n";
    file_put_contents(__DIR__ . "/slots_request_response.log", $entry, FILE_APPEND);
}

// ==========================================
// 2. GARBAGE COLLECTION
// ==========================================
if (rand(1, 100) == 1) {
    $files = glob($cacheFolder . '/*.json');
    $now = time();
    foreach ($files as $file) {
        if (is_file($file)) {
            if ($now - filemtime($file) >= 3600) { 
                @unlink($file); 
            }
        }
    }
}

// ==========================================
// 3. API FUNCTION (With Retry)
// ==========================================
function fetchFromBookeo($url, $isRetry = false) {
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
    $err = curl_error($curl);
    curl_close($curl);

    // Retry Logic for 429
    if ($http == 429 && !$isRetry) {
        usleep(1500000); // Wait 1.5s
        return fetchFromBookeo($url, true);
    }
    
    // Log Errors
    if ($http != 200) {
        logMessage("URL: $url\nHTTP: $http\nError: " . ($response ? $response : $err));
    }

    if ($response === false) {
        return [500, []];
    }
    return [$http, json_decode($response, true)];
}

// ==========================================
// 4. MAIN LOGIC
// ==========================================
$requestedDate = $_GET['date'] ?? '';
$productIds = isset($_GET['productIds']) ? json_decode($_GET['productIds'], true) : [];

if (!$requestedDate || empty($productIds)) {
    echo json_encode(['error' => 'Missing inputs']);
    exit;
}

try {
    $startDateTime = new DateTime($requestedDate . ' 00:00:00', $washingtonTz);
    $endDateTime   = new DateTime($requestedDate . ' 23:59:59', $washingtonTz);
    $startDateTime->setTimezone($utcTz);
    $endDateTime->setTimezone($utcTz);
    $startTimeStr = $startDateTime->format('Y-m-d\TH:i:s\Z');
    $endTimeStr   = $endDateTime->format('Y-m-d\TH:i:s\Z');
} catch (Exception $e) {
    echo json_encode(['error' => 'Date error']);
    exit;
}

$output = [];
$nowLocal = new DateTime("now", $washingtonTz);

foreach ($productIds as $productId) {
    $productId = trim($productId);
    $cacheFile = $cacheFolder . "/slots_" . $productId . "_" . $requestedDate . ".json";
    
    $apiData = null;
    $cacheLifetime = 300; // 5 Minutes
    
    // --- CACHE & API LOGIC ---
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheLifetime)) {
        $raw = @file_get_contents($cacheFile);
        $apiData = json_decode($raw, true);
    }

    if (!$apiData) {
        $fp = fopen($cacheFile, 'c+'); 
        if (flock($fp, LOCK_EX)) {
            clearstatcache();
            $stat = fstat($fp);
            if ($stat['size'] > 0 && (time() - $stat['mtime'] < $cacheLifetime)) {
                rewind($fp);
                $apiData = json_decode(fread($fp, $stat['size']), true);
            } else {
                $url = "https://api.bookeo.com/v2/availability/slots?productId={$productId}&startTime={$startTimeStr}&endTime={$endTimeStr}&mode=calendar";
                usleep(400000); 
                list($status, $fetchedData) = fetchFromBookeo($url);
                
                if ($status == 200) {
                    ftruncate($fp, 0);
                    rewind($fp);
                    fwrite($fp, json_encode($fetchedData));
                    $apiData = $fetchedData;
                } elseif ($status == 429) {
                    rewind($fp);
                    $content = stream_get_contents($fp);
                    $apiData = json_decode($content, true);
                }
            }
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }
    
    $logUrl = "https://api.bookeo.com/v2/availability/slots?productId={$productId}&startTime={$startTimeStr}&endTime={$endTimeStr}&mode=calendar";
    $sourceType = (isset($fetchedData)) ? "API (FRESH)" : "CACHE (SAVED)";
    logMessage(
        "SOURCE: $sourceType\n" .
        "REQUEST URL: $logUrl\n" .
        "RAW RESPONSE: " . json_encode($apiData)
    );

    // --- HTML GENERATION (FIXED TO MATCH OLD FILE) ---
    $html = "";
    if (isset($apiData['data']) && is_array($apiData['data'])) {
        $counter = 0;
        foreach ($apiData['data'] as $slot) {
            $dt = new DateTime($slot['startTime'], $utcTz);
            $dt->setTimezone($washingtonTz);
            
            if ($dt->getTimestamp() < $nowLocal->getTimestamp()) continue;

            $time = $dt->format("g:i A");
            $localTimeStr = $dt->format('Y-m-d H:i:s'); // This matches old file format
            $available = (int)($slot['numSeatsAvailable'] ?? 0);
            $isFull = ($available === 0);
            $eventId = $slot['eventId'] ?? ''; // This is vital for the Cart
            
            // "Call" Button Logic
            if ((($dt->getTimestamp() - $nowLocal->getTimestamp()) / 60) <= 20 && !$isFull) {
                 $html .= '<div class="time_slot_group time_slot_call"><span class="call-slot-btn" onclick="showCallPopup(\''.$time.'\')">'.$time.'<span class="Available_play_time">'.$available.' Available</span><span class="Available_play_time">Call</span></span></div>';
                continue;
            }
            
            $uid = 'slot-' . $productId . '-' . $counter++;
            
            // --- THE FIX: Added data-eventid and data-start-time ---
            $html .= '
            <div class="time_slot_group '.($isFull?"time_slot_full":"").'">
                <input type="radio"
                       name="lift-time-'.$productId.'"
                       id="'.$uid.'"
                       value="'.$localTimeStr.'"
                       data-start-time="'.$localTimeStr.'"
                       data-eventid="'.$eventId.'"
                       data-available="'.$available.'"
                       '.($isFull?"disabled":"").'
                       hidden>
                <label for="'.$uid.'">
                    '.$time.'
                    <span class="Available_play_time">'.($isFull?"Full":$available.' Available').'</span>
                </label>
            </div>';
        }
    }
    
    $output[$productId] = [
        'html' => ($html ?: "<p>No slots available</p>"),
        'date' => $requestedDate 
    ];
}

echo json_encode($output);
?>