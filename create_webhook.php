<?php
// create_webhook.php
$apiKey    = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
$secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";

// Your Main Webhook URL
$webhookUrl = "https://fleeescapes.com/bookeo_webhook.php";

function createWebhook($apiKey, $secretKey, $url, $domain, $type) {
    $endpoint = "https://api.bookeo.com/v2/webhooks";
    $payload = ["url" => $url, "domain" => $domain, "type" => $type];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "X-Bookeo-apiKey: $apiKey",
        "X-Bookeo-secretKey: $secretKey"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "DOMAIN: $domain | EVENT: $type | Status: $httpCode | Response: $response\n";
}

// --- Register Missing Webhooks for Fleeescapes ---

// 1. Bookings (You already did 'deleted', adding others)
createWebhook($apiKey, $secretKey, $webhookUrl, "bookings", "created");
createWebhook($apiKey, $secretKey, $webhookUrl, "bookings", "updated");

// 2. Seatblocks (If you use blocked seats logic)
createWebhook($apiKey, $secretKey, $webhookUrl, "seatblocks", "created");
createWebhook($apiKey, $secretKey, $webhookUrl, "seatblocks", "updated");
createWebhook($apiKey, $secretKey, $webhookUrl, "seatblocks", "deleted");

echo "\nRegistration completed.\n";
?>