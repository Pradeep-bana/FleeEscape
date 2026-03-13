<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
include "admin/db.php";

header('Content-Type: application/json');

// 1. Sanitize Input
$firstName = trim($_POST['firstName'] ?? '');
$lastName  = trim($_POST['lastName'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$type      = trim($_POST['type'] ?? 'mobile');
$giftCode  = trim($_POST['giftCode'] ?? '');

if (empty($firstName) || empty($lastName) || empty($email)) {
    echo json_encode(["status" => "error", "message" => "Missing required fields."]);
    exit;
}

// 2. Save to Session
$_SESSION['firstName'] = $firstName;
$_SESSION['lastName']  = $lastName;
$_SESSION['email']     = $email;
$_SESSION['phone']     = $phone;
$_SESSION['type']      = $type;

// Only update gift code if user typed something new, otherwise keep existing
if (!empty($giftCode)) {
    $_SESSION['giftCode'] = $giftCode;
}

// 3. Update/Create User in DB
try {
    $stmtUser = $pdo->prepare("SELECT id FROM tbl_users WHERE email=? LIMIT 1");
    $stmtUser->execute([$email]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $userId = $user['id'];
        // Optional: Update phone/name if changed
        $stmtUpdate = $pdo->prepare("UPDATE tbl_users SET firstName=?, lastName=?, phone=? WHERE id=?");
        $stmtUpdate->execute([$firstName, $lastName, $phone, $userId]);
    } else {
        $stmtInsertUser = $pdo->prepare("INSERT INTO tbl_users (firstName,lastName,email,phone,type,created_at) VALUES (?,?,?,?,?,NOW())");
        $stmtInsertUser->execute([$firstName, $lastName, $email, $phone, $type]);
        $userId = $pdo->lastInsertId();
    }
    $_SESSION['user_id'] = $userId;

    // 4. Return Success (No API Calls yet)
    // We redirect to #payment_details in JS, or we can send a URL here
    echo json_encode([
        "status" => "success",
        "message" => "Details saved",
        "redirectUrl" => "", // JS handles the step change
        "paymentRequired" => true // Assume true, Step 4 will calculate total
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "DB Error: " . $e->getMessage()]);
}
?>