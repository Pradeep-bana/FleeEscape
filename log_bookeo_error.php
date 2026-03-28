<?php
// log_bookeo_error.php
require_once(__DIR__ . '/includes/bookeo_runtime.php');

// 1. Get the data sent from JavaScript
$error_msg = isset($_POST['error']) ? $_POST['error'] : 'Unknown Error';
$context   = isset($_POST['context']) ? $_POST['context'] : 'No details';

flee_bookeo_log_message('frontend_bookeo_error', $error_msg, [
    'frontend_context' => $context,
]);
?>
