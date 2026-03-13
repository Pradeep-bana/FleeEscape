<?php
// 1. Enable error reporting to see PHP issues
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Credentials (USE NEW KEYS HERE AFTER REGENERATING)
$apiKey    = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
$secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";

// 3. The URL to get all promotions
$url = "https://api.bookeo.com/v2/settings/promotions?apiKey={$apiKey}&secretKey={$secretKey}";

// 4. Initialize Curl
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// 5. Execute
$response = curl_exec($ch);
curl_close($ch);

// 6. Decode JSON
$data = json_decode($response, true);

// 7. PRINT EVERYTHING (Formatted)
echo "<h1>API Response:</h1>";
echo "<pre>"; // This HTML tag makes the Array print nicely
print_r($data);
echo "</pre>";

// Check if we actually got data or an error
if (isset($data['httpStatus']) && $data['httpStatus'] == 404) {
    echo "<h3 style='color:red'>Error: The API returned 404.</h3>";
    echo "<p>Please go to Bookeo Settings -> API Integration and ensure 'access to configuration settings' is ENABLED for this API Key.</p>";
}
?>