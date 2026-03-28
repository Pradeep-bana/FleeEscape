<?php
require_once(__DIR__ . '/includes/bookeo_runtime.php');

$data = file_get_contents("php://input");

if ($data) {
    flee_bookeo_log_message('ui_button_update', 'Button update triggered: ' . trim($data));
    echo "Logged OK";
} else {
    echo "No data";
}
