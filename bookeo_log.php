<?php

// 1. CONFIGURATION
// ----------------
// Replace these with your actual Bookeo API credentials
$apiKey = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
$secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";
date_default_timezone_set('America/Los_Angeles');

// The Bookeo API endpoint you want to test (e.g., fetching products)
$url = "https://api.bookeo.com/v2/settings/products?apiKey=$apiKey&secretKey=$secretKey";

// 2. MAKE THE REQUEST
// -------------------
$ch = curl_init($url);

// Return the response as a string instead of outputting it directly
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Optional: Set timeout to avoid hanging scripts if Bookeo is unreachable
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

// Execute the request
$response = curl_exec($ch);

// 3. MEASURE TIME & LOG
// ---------------------
if(curl_errno($ch)) {
    // Handle error if the request failed completely
    $logMessage = "[" . date('Y-m-d H:i:s') . "] ERROR: " . curl_error($ch) . "\n";
} else {
    // Get the total time taken for the transaction in seconds
    $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    
    // Get the HTTP status code (to see if the API actually worked)
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Create a log entry format: Timestamp | Status Code | Time Taken
    $logMessage = "[" . date('Y-m-d H:i:s') . "] Status: $httpCode | Time: " . $totalTime . " seconds\n";
}

// Close the cURL session
curl_close($ch);

// 4. WRITE TO FILE
// ----------------
// This will append the log message to 'api_response_log.txt' in the same directory
file_put_contents('api_response_log.txt', $logMessage, FILE_APPEND);

// Optional: Echo to screen for testing
echo "Request complete. Logged: " . $logMessage;

?>