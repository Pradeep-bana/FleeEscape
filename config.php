<?php
// ===============================
// Global Configuration File
// ===============================

// Root URL (local server ke liye apna full path set karein)
$rootPath = "https://localhost/FleeeEscape/";

// Project ka Base URL
define("BASE_URL", $rootPath);

// Assets Path
define("ASSETS_URL", BASE_URL . "assets/");

// Includes Path (header/footer ke liye)
define("INCLUDES_PATH", $_SERVER['DOCUMENT_ROOT'] . "/includes/");

// VR Pages Path
define("VR_URL", BASE_URL . "vr/");

// Website Title
define("SITE_TITLE", "Flee Escape");

// Default Timezone
date_default_timezone_set("Asia/Kolkata");

define('CART_TIMER_MINUTES', 10);

// Bookeo API Credentials
define('FLEE_BOOKEO_API_KEY', 'AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC');
define('FLEE_BOOKEO_SECRET_KEY', 'RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4'); 