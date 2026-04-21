<?php
// --------------------
// Enable error reporting (Debugging)
// --------------------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --------------------
// Return JSON
// --------------------
header("Content-Type: application/json");

// --------------------
// Include database (PDO)
// --------------------
include('admin/db.php'); // $pdo is the PDO connection

// --------------------
// Receive form data
// --------------------
$fullname   = trim($_POST['fullname'] ?? '');
$email      = htmlspecialchars(trim($_POST['email'] ?? ''));
$phone      = trim($_POST['mobile'] ?? '');
$company    = trim($_POST['company'] ?? 'N/A');     
$service    = trim($_POST['service'] ?? '');
$event_date = trim($_POST['event_date'] ?? '');
$message    = trim($_POST['details'] ?? '');

// --------------------
// Basic validation
// --------------------
if(strlen($fullname) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[0-9]{10}$/', $phone) || empty($service) || strlen($message) < 1){
    echo json_encode([
        "status" => "error",
        "message" => "Please fill all required fields correctly."
    ]);
    exit;
}

// --------------------
// Name split for table
// --------------------
$nameParts = explode(' ', $fullname, 2);
$first_name = $nameParts[0];
$last_name  = $nameParts[1] ?? null; // null if no last name

// --------------------
// Insert into database using PDO
// --------------------
try {
    $sql = "INSERT INTO tbl_contact_bookings 
            (first_name, last_name, email, mobile, subject, details, created_at) 
            VALUES (:first_name, :last_name, :email, :mobile, :subject, :details, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':first_name' => $first_name,
        ':last_name'  => $last_name,
        ':email'      => $email,
        ':mobile'     => $phone,
        ':subject'    => $service,
        ':details'    => $message
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database insert failed: " . $e->getMessage()
    ]);
    exit;
}

// --------------------
// Send email
// --------------------
$to = "info@fleeescape.com";
$from = "no-reply@fleeescape.com";
$fromName = "FLEE Escape";
$subject = "New Service Enquiry Received";

$htmlContent = "
<h3 style='background:#d0dfe4;padding:10px;'>New Enquiry Details</h3>
<table style='width:100%; font-family:Arial; border-collapse:collapse;'>
<tr><td><strong>Name:</strong></td><td>".htmlspecialchars($fullname)."</td></tr>
<tr><td><strong>Email:</strong></td><td>".htmlspecialchars($email)."</td></tr>
<tr><td><strong>Phone:</strong></td><td>".htmlspecialchars($phone)."</td></tr>
<tr><td><strong>Company:</strong></td><td>".htmlspecialchars($company)."</td></tr>
<tr><td><strong>Service:</strong></td><td>".htmlspecialchars($service)."</td></tr>
<tr><td><strong>Event Date:</strong></td><td>".htmlspecialchars($event_date)."</td></tr>
<tr><td><strong>Message:</strong></td><td>".nl2br(htmlspecialchars($message))."</td></tr>
</table>
";

// Email headers
$headers  = "From: $fromName <$from>\r\n";
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $headers .= "Reply-To: $email\r\n";
}
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";

// Send mail
$mailSent = mail($to, $subject, $htmlContent, $headers);

// --------------------
// Return JSON response
// --------------------
echo json_encode([
    "status" => $mailSent ? "success" : "error",
    "message" => $mailSent ? "Your enquiry has been submitted successfully!" : "Your enquiry was saved but mail could not be sent."
]);
?>
