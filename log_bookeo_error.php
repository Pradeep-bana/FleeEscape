<?php
// log_bookeo_error.php

// 1. Get the current time
$date = date('Y-m-d H:i:s');

// 2. Get User IP
$ip = $_SERVER['REMOTE_ADDR'];

// 3. Get the data sent from JavaScript
$error_msg = isset($_POST['error']) ? $_POST['error'] : 'Unknown Error';
$context   = isset($_POST['context']) ? $_POST['context'] : 'No details';

// 4. Format the log entry
$logEntry = "[$date] [IP: $ip]\nERROR: $error_msg\nDETAILS: $context\n---------------------------------\n";

// 5. Save to file (Appends to end of file)
file_put_contents('bookeo_error_log.txt', $logEntry, FILE_APPEND);
?>