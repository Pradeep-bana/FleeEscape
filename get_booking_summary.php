<?php  
include "admin/db.php"; // adjust path if needed

// Turn on error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    if (empty($_SESSION['booking_summary'])) {
        echo "<p style='color:red;'>No booking found in session.</p>";
        exit;
    }

    $html = "";
    $customerShown = false;
    $grandTotal = 0;
    $grandTaxes = 0;

    foreach ($_SESSION['booking_summary'] as $b) {
        $bookingNumber = $b['bookingNumber'] ?? '';

        if (empty($bookingNumber)) {
            continue;
        }

        $query = "SELECT b.*, u.firstName, u.lastName, u.email, u.phone
                  FROM tbl_bookings b
                  LEFT JOIN tbl_users u ON b.user_id = u.id
                  WHERE b.bookingNumber = :bookingNumber";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':bookingNumber' => $bookingNumber]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) continue;

        // Show customer info only once
        if (!$customerShown) {
            $html .= '
            <div class="booking_com_customer_info">
                <h3>Customer Info</h3>
                <p>Name: <strong>' . htmlspecialchars($row["firstName"] . " " . $row["lastName"]) . '</strong></p>
                <p>Email: <strong>' . htmlspecialchars($row["email"]) . '</strong></p>
                <p>Phone: <strong>' . htmlspecialchars($row["phone"]) . '</strong></p>
            </div>';
            $customerShown = true;
        }

        // Decode JSON fields
        $price = json_decode($row['priceJson'], true) ?? [];
        $participants = json_decode($row['participantsJson'], true) ?? [];
        $priceAdjustments = json_decode($row['priceAdjustments'], true) ?? [];
        $taxes = json_decode($row['taxesJson'], true) ?? [];

        $totalGross = isset($price['totalGross']['amount']) ? (float)$price['totalGross']['amount'] : 0;
        $totalTaxes = isset($price['totalTaxes']['amount']) ? (float)$price['totalTaxes']['amount'] : 0;
        $grandTotal += $totalGross;
        $grandTaxes += $totalTaxes;

        $html .= '
        <div class="booking_com_customer_info">
            <h3 class="booking_com_customer_info_ID">Booking #' . htmlspecialchars($row["bookingNumber"]) . ' - ' . htmlspecialchars($row["productName"]) . '</h3>
            <div class="">
                <p>Event Start: <span>' . htmlspecialchars($row["startTime"]) . '</span></p>
                <p>Event End: <span>' . htmlspecialchars($row["endTime"]) . '</span></p>';

        // Participants
        if (!empty($participants['numbers'])) {
            foreach ($participants['numbers'] as $p) {
                $html .= '<p>Participants (' . htmlspecialchars($p['peopleCategoryId']) . '): <span>' . htmlspecialchars($p['number']) . '</span></p>';
            }
        }

        // Additional Guests (priceAdjustments)
        if (!empty($priceAdjustments)) {
            foreach ($priceAdjustments as $adj) {
                $qty = $adj['quantity'] ?? 0;
                $desc = $adj['description'] ?? '';
                $unitPrice = isset($adj['unitPrice']['amount']) ? (float)$adj['unitPrice']['amount'] : 0;
                $totalPrice = isset($adj['totalPrice']['amount']) ? (float)$adj['totalPrice']['amount'] : 0;

                $html .= '<p>' . htmlspecialchars($desc) . ': ' . $qty . ' x $' . number_format($unitPrice,2) . ' = <span>$' . number_format($totalPrice,2) . '</span></p>';
            }
        }

        // Taxes per booking
        if (!empty($taxes)) {
            foreach ($taxes as $tax) {
                $taxAmount = isset($tax['amount']['amount']) ? (float)$tax['amount']['amount'] : 0;
                $html .= '<p>Admission Tax: <span>$' . number_format($taxAmount,2) . '</span></p>';
            }
        }

        $html .= '<p class="total">Booking Total: <span>$' . number_format($totalGross,2) . '</span></p>';
        $html .= '</div></div>';
    }

    // Grand total
    $html .= '
    <div class="booking_com_customer_info">
        <h3 style="color:#00d4ff">Grand Total</h3>
        <p>Total Amount: <span>$' . number_format($grandTotal,2) . '</span></p>
        <p>Total Taxes: <span>$' . number_format($grandTaxes,2) . '</span></p>
    </div>';

    echo $html ?: "<p style='color:red;'>No booking summary available.</p>";

} catch (PDOException $e) {
    echo "<div style='color:red; background:#fee; padding:10px; border:1px solid #f99;'>
            <strong>Database Error:</strong> " . htmlspecialchars($e->getMessage()) . "
          </div>";
} catch (Exception $e) {
    echo "<div style='color:red; background:#fee; padding:10px; border:1px solid #f99;'>
            <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "
          </div>";
}
