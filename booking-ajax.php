<?php
include('admin/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Sanitize inputs
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name  = htmlspecialchars(trim($_POST['last_name']));
    $email      = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $mobile     = htmlspecialchars(trim($_POST['mobile']));
    $subject    = htmlspecialchars(trim($_POST['subject']));
    $details    = htmlspecialchars(trim($_POST['details']));

    // Validation
    $errors = [];
    if (empty($first_name)) $errors[] = "First name is required.";
    if (empty($last_name)) $errors[] = "Last name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email address.";
    if (empty($mobile)) {
        $errors[] = "Mobile number is required.";
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $errors[] = "Please enter a valid 10-digit mobile number.";
    }
    if (empty($subject)) $errors[] = "Please select an escape challenge.";
    if (empty($details)) $errors[] = "Please provide challenge details.";

    if (!empty($errors)) {
        echo json_encode(["status" => "error", "errors" => $errors]);
        exit;
    }

    // --- Insert into database ---
    $stmt = $pdo->prepare("INSERT INTO tbl_contact_bookings (first_name, last_name, email, mobile, subject, details, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $inserted = $stmt->execute([$first_name, $last_name, $email, $mobile, $subject, $details]);

    if ($inserted) {

        // --- Email setup ---
        $to = 'info@fleeescape.com';  // Receiver email
        $from = 'fleeescapes.com';
        $fromName = 'FLEE Escape';
        $email_subject = 'New Booking / Inquiry from FLEE Escape Website';

        // Email HTML Content
        $htmlContent = '
        <h3 style="background:#d0dfe4;padding:8px;">New Booking Request Received</h3>
        <table style="border-collapse:collapse;width:100%;">
          <tr><td><strong>First Name:</strong></td><td>' . $first_name . '</td></tr>
          <tr><td><strong>Last Name:</strong></td><td>' . $last_name . '</td></tr>
          <tr><td><strong>Email:</strong></td><td>' . $email . '</td></tr>
          <tr><td><strong>Mobile:</strong></td><td>' . $mobile . '</td></tr>
          <tr><td><strong>Escape Challenge:</strong></td><td>' . $subject . '</td></tr>
          <tr><td><strong>Details:</strong></td><td>' . nl2br($details) . '</td></tr>
        </table>';

        // Headers
        $headers = "From: $fromName <$from>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";

        // Send Email
        if (mail($to, $email_subject, $htmlContent, $headers)) {
            echo json_encode(["status" => "success", "message" => "Message Sent. Someone will be in touch shortly."]);
        } else {
            echo json_encode(["status" => "warning", "message" => "Failed to send mail. Please try again"]);
        }

    } else {
        echo json_encode(["status" => "error", "errors" => ["Something went wrong. Please try again"]]);
    }
}
?>
