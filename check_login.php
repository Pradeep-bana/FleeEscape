<?php
header('Content-Type: application/json');

$email    = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["success" => false, "message" => "Missing email or password."]);
    exit;
}

$apiKey    = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
$secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";

// Step 1: Get customer by email
$url_lookup = "https://api.bookeo.com/v2/customers?apiKey=$apiKey&secretKey=$secretKey&emailAddress=" . urlencode($email);

$ch = curl_init($url_lookup);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$lookup_response = curl_exec($ch);
$lookup_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$lookup_data = json_decode($lookup_response, true);

if ($lookup_http !== 200 || empty($lookup_data['data'][0]['id'])) {
    file_put_contents("login.log", "Lookup failed: $lookup_response\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Customer not found."]);
    exit;
}

$customerId = $lookup_data['data'][0]['id'];

// Step 2: Authenticate
$url_auth = "https://api.bookeo.com/v2/customers/$customerId/authenticate?apiKey=$apiKey&secretKey=$secretKey&username=" . urlencode($email) . "&password=" . urlencode($password);

$ch = curl_init($url_auth);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

$logData = [
    "time" => date("Y-m-d H:i:s"),
    "email" => $email,
    "http_code" => $httpcode,
    "curl_error" => $error,
    "response" => json_decode($response, true)
];
file_put_contents("reg.log", print_r($logData, true) . "\n-----------------------------\n", FILE_APPEND);

if ($httpcode == 200) {
    echo json_encode(["success" => true, "message" => "Login successful!"]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid login credentials."]);
}
?>
