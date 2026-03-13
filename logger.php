<?php
$logFile = __DIR__ . "/update_button.log";
$data = file_get_contents("php://input");

if ($data) {
    file_put_contents(
        $logFile,
        "[" . date("Y-m-d H:i:s") . "] " . $data . PHP_EOL,
        FILE_APPEND
    );
    echo "Logged OK"; // 👀 JS .then() will show this
} else {
    echo "No data";
}
