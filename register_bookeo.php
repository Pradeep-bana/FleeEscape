<?php
header('Content-Type: application/json');

$firstName = $_POST['firstName'] ?? '';
$lastName  = $_POST['lastName'] ?? '';
$email     = $_POST['email'] ?? '';
$password  = $_POST['password'] ?? '';
$phone     = $_POST['phone'] ?? '';
$phoneType = $_POST['phoneType'] ?? 'mobile';

if (!$firstName || !$lastName || !$email || !$password || !$phone) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

$apiKey = "AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC";
$secretKey = "RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4";

$data = [
    "firstName" => $firstName,
    "lastName"  => $lastName,
    "emailAddress" => $email,
    "password"  => $password,
    "phoneNumbers" => [
        [
            "number" => $phone,
            "type" => $phoneType
        ]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.bookeo.com/v2/customers?secretKey=$secretKey&apiKey=$apiKey");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// === Log to reg.log for debugging ===
$logData = [
    "time" => date("Y-m-d H:i:s"),
    "input" => $data,
    "http_code" => $httpcode,
    "curl_error" => $error,
    "response" => json_decode($response, true)
];
file_put_contents("reg.log", print_r($logData, true) . "\n-----------------------------\n", FILE_APPEND);

if ($httpcode == 200 || $httpcode == 201) {
    echo json_encode(["success" => true, "message" => "Customer created successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Bookeo API error", "details" => $response]);
}
?>
