<?php
header("Content-Type: application/json");

// Include the database connection
include('admin/db.php'); // Ensure the path is correct

// Receive form data
$name    = $_POST['name'] ?? '';
$mobile  = $_POST['mobile'] ?? '';
$email   = $_POST['email'] ?? '';
$message = $_POST['message'] ?? '';

// Basic validation
if (!$name || !$mobile || !$email || !$message) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}

// Agar message blank hai, toh NULL insert karo
if (empty($message)) {
    $message = null; // NULL set kar diya agar message empty ho
}

// ------------------------
// Insert data into database
// ------------------------

$sql = "INSERT INTO tbl_contact_bookings (first_name, last_name, email, mobile, subject, details) 
        VALUES (:first_name, :last_name, :email, :mobile, :subject, :details)";

$stmt = $pdo->prepare($sql);

// Try-catch block se database errors ko handle karo
try {
    // Execute the query
    $stmt->execute([
        ':first_name' => explode(" ", $name)[0], // Assuming first name is the first part of the name
        ':last_name' => implode(" ", array_slice(explode(" ", $name), 1)), // Rest is the last name
        ':email' => $email,
        ':mobile' => $mobile,
        ':subject' => 'Party Enquiry', // Subject field, you can change as per requirement
        ':details' => $message // NULL ya message insert hoga
    ]);
} catch (PDOException $e) {
    // Agar error ho, toh database error message return karo
    echo json_encode(["status" => "error", "message" => "Database mein data insert karte waqt error aayi: " . $e->getMessage()]);
    exit;
}

// ----------------------
// Email setup for sending
// ----------------------

$to = 'info@fleeescape.com'; // Aapka email address
$from = 'fleeescapes.com'; // From email address
$fromName = 'FLEE Escape';
$email_subject = 'New Party Enquiry from FLEE Escape Website';

// Email content
$htmlContent = '
<h3 style="background:#d0dfe4;padding:8px;">New Party Enquiry Received</h3>
<table style="font-family:Arial, sans-serif;border-collapse:collapse;width:100%;">
  <tr><td><strong>Full Name:</strong></td><td>' . htmlspecialchars($name) . '</td></tr>
  <tr><td><strong>Email:</strong></td><td>' . htmlspecialchars($email) . '</td></tr>
  <tr><td><strong>Mobile:</strong></td><td>' . htmlspecialchars($mobile) . '</td></tr>
  <tr><td><strong>Message:</strong></td><td>' . nl2br(htmlspecialchars($message)) . '</td></tr>
</table>';

// Headers for email
$headers = "From: $fromName <$from>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";

// Send email
if (mail($to, $email_subject, $htmlContent, $headers)) {
    echo json_encode(["status" => "success", "message" => "Your enquiry has been sent successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to send email. Please try again."]);
}
?>
