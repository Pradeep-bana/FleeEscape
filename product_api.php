<?php

// --- Logging setup ---
$logFile = __DIR__ . "/php_errors.log"; // log file in the same folder
function writeLog($message) {
    global $logFile;
    $date = date("Y-m-d H:i:s");
    file_put_contents($logFile, "[$date] $message" . PHP_EOL, FILE_APPEND);
}

// Step 1: Call Bookeo API
$curl = curl_init();
$url = 'https://api.bookeo.com/v2/settings/products';

curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'X-Bookeo-apiKey: AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC',
        'X-Bookeo-secretKey: RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4',
        'Accept: application/json'
    ),
));

$response = curl_exec($curl);
print_r($response);


// --- Log request and response ---
if (curl_errno($curl)) {
    $error = curl_error($curl);
    writeLog("CURL ERROR calling $url => $error");
} else {
    writeLog("REQUEST to $url");
    writeLog("FULL RESPONSE: " . $response); 
}

curl_close($curl);

// Step 2: Decode JSON response
$data = json_decode($response, true);

// Step 3: Display products
echo "<!DOCTYPE html>
<html>
<head>
    <title>Bookeo Products</title>
    <style>
        body { padding: 20px; background: #f4f4f4; }
        .product { background: #fff; padding: 20px; margin-bottom: 30px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .product h2 { margin-top: 0; color: #333; }
        .images img { max-width: 200px; margin: 10px; border: 1px solid #ccc; }
        .section { margin-bottom: 10px; }
        .label { font-weight: bold; }
    </style>
</head>
<body>
<h1>Bookeo Products</h1>";

if (!empty($data['data'])) {
    foreach ($data['data'] as $product) {
        echo "<div class='product'>";
        
        // Product Name
        echo "<h2>" . htmlspecialchars($product['name']) . "</h2>";
        
        echo "<div class='section'>Blurb: " . 
            (!empty($product['blurb']) ? htmlspecialchars($product['blurb']) : 'N/A') . 
            "</div>";
        
        // Description
        echo "<div class='section'>" . $product['description'] . "</div>";
        
        // Images
        echo "<div class='images section'>";
        if (!empty($product['images'])) {
            foreach ($product['images'] as $img) {
                echo "<img src='" . htmlspecialchars($img['url']) . "' alt='Product Image'>";
            }
        }
        echo "</div>";
        
        // Booking Limits
        echo "<div class='section'><span class='label'>Booking Limits:</span><ul>";
        if (!empty($product['bookingLimits'])) {
            foreach ($product['bookingLimits'] as $limit) {
                $label = isset($limit['peopleCategoryId']) ? $limit['peopleCategoryId'] : 'General';
                echo "<li>$label: Min {$limit['min']}, Max {$limit['max']}</li>";
            }
        }
        echo "</ul></div>";
        
        // Default Rates
        echo "<div class='section'><span class='label'>Default Rates:</span><ul>";
        if (!empty($product['defaultRates'])) {
            foreach ($product['defaultRates'] as $rate) {
                $category = $rate['peopleCategoryId'];
                $amount = $rate['price']['amount'];
                $currency = $rate['price']['currency'];
                echo "<li>$category: $amount $currency</li>";
            }
        }
        echo "</ul></div>";
        
        // Duration
        if (!empty($product['duration'])) {
            $duration = $product['duration'];
            echo "<div class='section'><span class='label'>Duration:</span> {$duration['hours']} HOURS, {$duration['minutes']} Minutes</div>";
        }
        
        // Basic details
        echo "<div class='section'><span class='label'>Product Code:</span> " . $product['productCode'] . "</div>";
        echo "<div class='section'><span class='label'>Type:</span> " . $product['type'] . "</div>";
        echo "<div class='section'><span class='label'>API Bookings Allowed:</span> " . 
            ($product['apiBookingsAllowed'] ? 'Yes' : 'No') . "</div>";
        
        // Product Options (Add-ons)
        if (!empty($product['options'])) {
            echo "<div class='section'><span class='label'>Add-Ons / Options:</span><ul>";
            foreach ($product['options'] as $option) {
                echo "<li>" . htmlspecialchars($option['name']);
                if (isset($option['price']['amount'])) {
                    echo " - " . $option['price']['amount'] . " " . $option['price']['currency'];
                }
                echo "</li>";
            }
            echo "</ul></div>";
        } else {
            echo "<div class='section'>No options available.</div>";
        }

        // On/Off Options
        if (!empty($product['onOffOptions'])) {
            echo "<div class='section'><span class='label'>On/Off Options:</span><ul>";
            foreach ($product['onOffOptions'] as $option) {
                echo "<li><strong>" . htmlspecialchars($option['name']) . "</strong><br>";
                echo $option['description'] . "</li>";
            }
            echo "</ul></div>";
        }

        // Text Options
        if (!empty($product['textOptions'])) {
            echo "<div class='section'><span class='label'>Text Options:</span><ul>";
            foreach ($product['textOptions'] as $option) {
                echo "<li><strong>" . htmlspecialchars($option['name']) . "</strong><br>";
                echo "Description: " . htmlspecialchars($option['description']) . "<br>";
                echo "Required: " . ($option['required'] ? 'Yes' : 'No') . "</li>";
            }
            echo "</ul></div>";
        }

        // Choice Options
        if (!empty($product['choiceOptions'])) {
            echo "<div class='section'><span class='label'>Choice Options:</span><ul>";
            foreach ($product['choiceOptions'] as $choiceOption) {
                echo "<li><strong>" . htmlspecialchars($choiceOption['name']) . "</strong><br>";
                foreach ($choiceOption['values'] as $value) {
                    echo "<div><strong>" . htmlspecialchars($value['name']) . "</strong><br>";
                    echo "Description: " . htmlspecialchars($value['description']) . "</div>";
                }
                echo "</li>";
            }
            echo "</ul></div>";
        }

        /* -----------------------------------------
           ✅ NUMBER OPTIONS (Add-on with quantity)
        ------------------------------------------ */
        if (!empty($product['numberOptions'])) {
            echo "<div class='section'><span class='label'>Number Options (Add-On Quantity Based):</span><ul>";
            foreach ($product['numberOptions'] as $opt) {
                echo "<li>";
                echo "<strong>" . htmlspecialchars($opt['name']) . "</strong><br>";
                echo "Min: {$opt['minValue']} | Max: {$opt['maxValue']} | Default: {$opt['defaultValue']}<br>";
                echo "<div style='margin-top:5px;'>" . $opt['description'] . "</div>";
                echo "</li>";
            }
            echo "</ul></div>";
        } else {
            echo "<div class='section'>No number options available.</div>";
        }

        echo "</div>"; // Close product box
    }

} else {
    echo "<p>No products found or API response invalid.</p>";
    writeLog("Invalid or empty response received.");
}

echo "</body></html>";

?>
