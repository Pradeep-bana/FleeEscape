<?php
// register_seatblocks.php
$apiKey    = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
$secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";
$myUrl     = "https://fleeescapes.com/api/bookeo_webhook.php"; 

$baseUrl = "https://api.bookeo.com/v2/webhooks";
$headers = ["X-Bookeo-apiKey: $apiKey", "X-Bookeo-secretKey: $secretKey", "Content-Type: application/json"];

// We need these 3 missing ones
$events = [
    ['domain' => 'seatblocks', 'type' => 'created'],
    ['domain' => 'seatblocks', 'type' => 'updated'],
    ['domain' => 'seatblocks', 'type' => 'deleted']
];

foreach ($events as $evt) {
    $payload = json_encode([
        "url"    => $myUrl,
        "domain" => $evt['domain'],
        "type"   => $evt['type']
    ]);

    $ch = curl_init($baseUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Registered <b>{$evt['domain']} -> {$evt['type']}</b> | Status: $httpCode | Response: $response<br>";
}
?>