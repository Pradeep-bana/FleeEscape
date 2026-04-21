<?php
header("Content-Type: application/json");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --------------------
// Include PDO database connection
// --------------------
include('admin/db.php'); // $pdo

// --------------------
// Receive POST data
// --------------------
$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$email      = htmlspecialchars(trim($_POST['email'] ?? ''));
$guests     = trim($_POST['guests'] ?? '');
$duration   = trim($_POST['duration'] ?? '');
$event_date = trim($_POST['event_date'] ?? '');
$message    = trim($_POST['party_message'] ?? '');

// --------------------
// Validation (minimal required for DB insert)
// --------------------
if (!$first_name || !$last_name || !$phone || !$email) {
    echo json_encode(["status" => "error", "message" => "First name, last name, email and phone are required."]);
    exit;
}

// --------------------
// Prepare DB insert (only matching fields)
// --------------------
$subject = "Party Enquiry"; // fixed subject
$details = "Guests: $guests\nDuration: $duration\nEvent Date: $event_date\nMessage: $message";

// --------------------
// Insert into database
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
        ':subject'    => $subject,
        ':details'    => $details
    ]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database insert failed: " . $e->getMessage()]);
    exit;
}

// --------------------
// Send full email
// --------------------
$to = "info@fleeescape.com";
$from = "no-reply@fleeescape.com";
$fromName = "FLEE Escape";
$subject_email = "New Party Enquiry Received";

$htmlContent = "
<h3 style='background:#d0dfe4;padding:10px;'>New Party Enquiry</h3>
<table style='width:100%; font-family:Arial; border-collapse:collapse;'>
<tr><td><strong>First Name:</strong></td><td>".htmlspecialchars($first_name)."</td></tr>
<tr><td><strong>Last Name:</strong></td><td>".htmlspecialchars($last_name)."</td></tr>
<tr><td><strong>Guests:</strong></td><td>".htmlspecialchars($guests)."</td></tr>
<tr><td><strong>Duration:</strong></td><td>".htmlspecialchars($duration)."</td></tr>
<tr><td><strong>Phone:</strong></td><td>".htmlspecialchars($phone)."</td></tr>
<tr><td><strong>Event Date:</strong></td><td>".htmlspecialchars($event_date)."</td></tr>
<tr><td><strong>Email:</strong></td><td>".htmlspecialchars($email)."</td></tr>
<tr><td><strong>Message:</strong></td><td>".nl2br(htmlspecialchars($message))."</td></tr>
</table>
";

$headers = "From: $fromName <$from>\r\n";
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $headers .= "Reply-To: $email\r\n";
}
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";

$mailSent = mail($to, $subject_email, $htmlContent, $headers);

// --------------------
// JSON Response
// --------------------
echo json_encode([
    "status" => $mailSent ? "success" : "error",
    "message" => $mailSent ? "Your party enquiry has been submitted successfully!" : "Saved in DB but email could not be sent."
]);
?>
