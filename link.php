<?php 
// --- DYNAMIC BASE URL ---
// This automatically detects if the site is running on http or https
// and constructs the correct full base URL.

$is_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
$protocol = $is_secure ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$link = $protocol . $host . $path . '/';

// Define a constant for easier and more consistent use across your application.
if (!defined('BASE_URL')) {
    define('BASE_URL', $link);
}
?>