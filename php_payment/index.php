<?php
require 'vendor/autoload.php';
include 'utils/location-info.php';

use Square\Environment;
use Ramsey\Uuid\Uuid;

// Load dotenv (new syntax for PHP 8+)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); 
$dotenv->load();

$env = getenv('ENVIRONMENT') ?: 'sandbox';

$web_payment_sdk_url = ($env === 'production')
  ? "https://web.squarecdn.com/v1/square.js"
  : "https://sandbox.web.squarecdn.com/v1/square.js";
?>
<html>
<head>
  <title>My Payment Flow</title>
  <script type="text/javascript" src="<?php echo $web_payment_sdk_url ?>"></script>
  <script type="text/javascript">
    window.applicationId = "<?php echo getenv('SQUARE_APPLICATION_ID'); ?>";
    window.locationId    = "<?php echo getenv('SQUARE_LOCATION_ID'); ?>";
    window.currency      = "<?php echo $location_info->getCurrency(); ?>";
    window.country       = "<?php echo $location_info->getCountry(); ?>";
    window.idempotencyKey = "<?php echo Uuid::uuid4(); ?>";
  </script>
  <link rel="stylesheet" type="text/css" href="public/stylesheets/style.css">
  <link rel="stylesheet" type="text/css" href="public/stylesheets/sq-payment.css">
</head>
<body>
  <!-- Your form unchanged -->
</body>
</html>
