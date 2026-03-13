<?php

// CALL BOOKEO API
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.bookeo.com/v2/settings/products',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array(
            'X-Bookeo-apiKey: AJXRUXU3EUHNXXKFAA4ER41551N96JNR14F91CA8DAC',
            'X-Bookeo-secretKey: RV4URTDBaoNysxrVcCtDGXm7eRiVoaX4',
            'Accept: application/json'
        ),
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
    ));

    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($response === false || $curlError) {

        // API FAILED â†’ Use old cache (if exists)
        if ($cacheRow) {
            $data = json_decode($cacheRow['product_data'], true);
        } else {
            die("API Error: " . htmlspecialchars($curlError));
        }

    } else {

        $json = json_decode($response, true);
        echo '<pre>';
        echo htmlspecialchars($response, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        echo '</pre>';
        
    }
?>