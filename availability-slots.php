<?php
$washingtonTz = new DateTimeZone('America/Los_Angeles');
$productId = '41551F9C679173BC114D28';
$startTime = '2025-11-22T00:00:00Z';
$endTime = '2025-12-21T23:59:59Z';

$curl = curl_init();
$url = "https://api.bookeo.com/v2/availability/slots?productId={$productId}&startTime={$startTime}&endTime={$endTime}";

curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
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
curl_close($curl);
print_r($response);
$data = json_decode($response, true);
$slots = $data['data'] ?? [];
$info = $data['info'] ?? [];

// Function to convert UTC time to Washington local time
function convertToWashington($utcTime, $washingtonTz) {
    if (empty($utcTime)) return 'N/A';
    try {
        $utc = new DateTime($utcTime, new DateTimeZone('UTC'));
        $utc->setTimezone($washingtonTz);
        return $utc->format('Y-m-d h:i A'); // Example: 2025-10-23 05:30 PM
    } catch (Exception $e) {
        return 'Invalid Date';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookeo Availability Slots (Washington Time)</title>
    <style>
        body {
            
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f9;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .info-section {
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .info-section p {
            margin: 5px 0;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .no-seats {
            color: #e63946;
            font-weight: bold;
        }
        .available {
            color: #2a9d8f;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Bookeo Availability Slots (Washington Time)</h1>
    <div class="info-section">
        <p><strong>Total Items:</strong> <?php echo htmlspecialchars($info['totalItems'] ?? 'N/A'); ?></p>
        <p><strong>Total Pages:</strong> <?php echo htmlspecialchars($info['totalPages'] ?? 'N/A'); ?></p>
        <p><strong>Current Page:</strong> <?php echo htmlspecialchars($info['currentPage'] ?? 'N/A'); ?></p>
        <p><strong>Page Navigation Token:</strong> <?php echo htmlspecialchars($info['pageNavigationToken'] ?? 'N/A'); ?></p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Event ID</th>
                <th>Start Time (Washington)</th>
                <th>End Time (Washington)</th>
                <th>Seats Available</th>
                <th>Resource</th>
                <th>Private Event</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($slots as $slot): ?>
                <tr>
                    <td><?php echo htmlspecialchars($slot['eventId']); ?></td>
                    <td><?php echo convertToWashington($slot['startTime'], $washingtonTz); ?></td>
                    <td><?php echo convertToWashington($slot['endTime'], $washingtonTz); ?></td>
                    <td class="<?php echo $slot['numSeatsAvailable'] > 0 ? 'available' : 'no-seats'; ?>">
                        <?php echo htmlspecialchars($slot['numSeatsAvailable']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($slot['resources'][0]['name'] . ' (' . $slot['resources'][0]['id'] . ')'); ?></td>
                    <td><?php echo $slot['privateEvent'] ? 'Yes' : 'No'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
