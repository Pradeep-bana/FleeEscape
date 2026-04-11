<?php
// log_js_error.php
$message = $_POST['message'] ?? 'Unknown JS Error';
$context = $_POST['context'] ?? 'Frontend';

// Format the log entry
$logEntry = "[" . date('Y-m-d H:i:s') . "] [$context] " . $message . PHP_EOL;

// Append to website_system.log
file_put_contents('website_system.log', $logEntry, FILE_APPEND);

echo json_encode(['status' => 'logged']);
?>