<?php

$apiKey = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
$secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";

$url = "https://api.bookeo.com/v2/webhooks";

$headers = [
    "X-Bookeo-apiKey: $apiKey",
    "X-Bookeo-secretKey: $secretKey",
    "Content-Type: application/json"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($httpCode == 200) {
    echo "Webhooks List:<br>";
    echo "<pre>" . print_r(json_decode($response, true), true) . "</pre>";
} else {
    echo "Error ($httpCode): $response";
}
