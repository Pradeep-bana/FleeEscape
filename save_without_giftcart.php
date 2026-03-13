<?php
session_start();

// Get POST data safely
$firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
$lastName  = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
$email     = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone     = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$type      = isset($_POST['type']) ? trim($_POST['type']) : '';
$giftCode  = isset($_POST['giftCode']) ? trim($_POST['giftCode']) : '';

// Store in session
$_SESSION['firstName'] = $firstName;
$_SESSION['lastName']  = $lastName;
$_SESSION['email']     = $email;
$_SESSION['phone']     = $phone;
$_SESSION['type']      = $type;
$_SESSION['giftCode']  = $giftCode;

// Prepare response
$response = [
    "status"   => "success",
    "message"  => "Session data saved successfully.",
    "bookings" => [] // keep structure same as your existing script
];

// Return JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
