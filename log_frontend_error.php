<?php
// log_frontend_error.php
// Dedicated endpoint to catch and log detailed JavaScript errors from the browser.

require_once(__DIR__ . '/includes/bookeo_runtime.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Get the raw JSON payload sent by the frontend
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (is_array($data)) {
    $message = $data['message'] ?? 'Unknown JS Error';
    
    $fields = [
        'file'  => $data['filename'] ?? 'Unknown File',
        'line'  => $data['lineno'] ?? 0,
        'col'   => $data['colno'] ?? 0,
        'stack' => $data['stack'] ?? 'No stack trace available',
        'url'   => $data['url'] ?? ($_SERVER['HTTP_REFERER'] ?? 'Unknown URL')
    ];

    // Log the error nicely into website_system.log using your existing function
    flee_system_log_message('FRONTEND_JS_ERROR', $message, $fields);
}

echo json_encode(['status' => 'success']);
exit;