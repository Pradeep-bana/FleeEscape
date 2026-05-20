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
    $filename = $data['filename'] ?? 'Unknown File';
    $lineno = (int)($data['lineno'] ?? 0);
    $colno = (int)($data['colno'] ?? 0);
    $stack = $data['stack'] ?? 'No stack trace available';

    $isBrowserHiddenScriptError =
        $message === 'Script error.' &&
        ($filename === '' || $filename === 'Unknown File') &&
        $lineno === 0 &&
        $colno === 0 &&
        ($stack === '' || $stack === 'No stack trace available');

    if ($isBrowserHiddenScriptError) {
        echo json_encode(['status' => 'ignored']);
        exit;
    }
    
    $fields = [
        'file'  => $filename,
        'line'  => $lineno,
        'col'   => $colno,
        'stack' => $stack,
        'url'   => $data['url'] ?? ($_SERVER['HTTP_REFERER'] ?? 'Unknown URL')
    ];

    // Log the error nicely into website_system.log using your existing function
    flee_system_log_message('FRONTEND_JS_ERROR', $message, $fields);
}

echo json_encode(['status' => 'success']);
exit;
