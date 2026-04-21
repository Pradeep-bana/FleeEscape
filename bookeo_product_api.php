<?php
include('admin/db.php'); 
require_once('config.php');

$apiKey = FLEE_BOOKEO_API_KEY;
$secretKey = FLEE_BOOKEO_SECRET_KEY;

// 1. CALL BOOKEO API
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.bookeo.com/v2/settings/products',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
        'X-Bookeo-apiKey: '.$apiKey,
        'X-Bookeo-secretKey: '.$secretKey,
        'Accept: application/json'
    ),
    CURLOPT_TIMEOUT => 20,
    CURLOPT_CONNECTTIMEOUT => 10,
));

$response = curl_exec($curl);
$curlError = curl_error($curl);
curl_close($curl);

if ($response === false || $curlError) {
    die("<h3 style='color:red;'>API Error: " . htmlspecialchars($curlError) . "</h3>");
}

// 2. CLEAN THE CORRUPTED DATA
// Remove the corrupted emoji characters (\ufffd and the physical  symbol)
$cleanResponse = str_replace(['\ufffd', ''], '', $response);

// 3. DECODE AND RE-ENCODE CLEANLY
$jsonArray = json_decode($cleanResponse, true);

if (!$jsonArray) {
    die("<h3 style='color:red;'>Error: Bookeo API did not return valid JSON.</h3>");
}

// Re-encode it perfectly for MySQL
$finalJson = json_encode($jsonArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// 4. DIRECTLY UPDATE THE DATABASE
try {
    // Assuming your cache table has 1 row. 
    // If your table has a specific ID column (like id = 1), you can add: WHERE id = 1
    $stmt = $pdo->prepare("UPDATE bookeo_products_cache SET product_data = :data");
    $stmt->execute([':data' => $finalJson]);

    echo "<div style='font-family: sans-serif; padding: 20px; border: 2px solid green; background: #eaffea; max-width: 600px; margin: 50px auto; border-radius: 8px;'>";
    echo "<h2 style='color:green; margin-top:0;'>✅ Success! Database updated directly.</h2>";
    echo "<p>The corrupted characters were removed and the JSON was safely saved to MySQL. You no longer need to copy and paste.</p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='font-family: sans-serif; padding: 20px; border: 2px solid red; background: #ffeaea; max-width: 600px; margin: 50px auto; border-radius: 8px;'>";
    echo "<h2 style='color:red; margin-top:0;'>❌ Database Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>