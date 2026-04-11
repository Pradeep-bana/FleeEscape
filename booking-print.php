<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your booking with 'FLEE Escape Rooms and Zero Latency VR Seattle' - Bookeo</title>

<style>
body {
    font-family: Arial, Helvetica, sans-serif;
    margin: 0;
    background: #fff;
    color: #000;
    font-size: 11px;
}

@page {
    size: A4;
    margin: 8mm;
}

@media print {
    body { -webkit-print-color-adjust: exact; }

    .booking-row {
        flex-direction: row !important;
        flex-wrap: nowrap !important;
    }

    .booking-left, 
    .booking-right {
        width: 50% !important;
    }
}

.page {
    width: 210mm;
    margin: auto;
    padding: 10mm;
    background: white;
    border-bottom: 2px solid #000;
}

/* HEADER SECTION */
.header-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-left img {
    width: 150px;
}

.header-right {
    text-align: right;
    font-size: 11px;
    line-height: 16px;
    min-width: 230px;
}

.section-title {
    font-size: 17px;
    margin: 10px 0 8px;
    font-weight: bold;
}

.divider {
    border-top: 1px solid #ccc;
    margin: 10px 0;
}

/* BOOKING SECTION */
.booking-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    flex-wrap: wrap;
}

.booking-left {
    width: 50%;
    min-width: 240px;
}

.booking-right {
    width: 40%;
    text-align: right;
    min-width: 180px;
}

.booking-right img {
    width: 160px;
    height: 110px;
    object-fit: cover;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.small-text {
    font-size: 11px;
}

/* TABLE STYLING */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 5px;
    margin-bottom: 15px;
    font-size: 11px;
}

table th {
    background: #f7f7f7;
    border: 1px solid #ccc;
    padding: 6px;
    font-weight: bold;
}

table td {
    border: 1px solid #ccc;
    padding: 6px;
}

.right { text-align: right; }
.bold { font-weight: bold; }

</style>
</head>

<body>

<div class="page">

    <!-- HEADER -->
    <div class="header-flex">
        <div class="header-left">
            <img src="<?= BASE_URL ?>assets/images/logo.png" loading="lazy"  decoding="async" >
        </div>
        <div class="header-right">
            <b>FLEE Escape Rooms and Zero Latency VR Seattle</b><br>
            #112-2222 152nd AVE NE, Redmond, Washington 98052<br>
            (425)287-1426<br>
            info@fleeescape.com<br>
            www.fleeescape.com
        </div>
    </div>

    <div class="divider"></div>
    
    <!-- CUSTOMER -->
    <h3 class="section-title">Customer</h3>
    <div class="small-text">
        <strong>Anil Vishwakarma</strong><br>
        Email: anilcode89@gmail.com<br>
        Phone: 08982838752
    </div>

    <div class="divider"></div>

    <!-- ========================================= -->
    <!-- ITEM 1 — ICE WALKER MAY 2 -->
    <!-- ========================================= -->
    <h3 class="section-title">Booking details</h3>

    <div class="booking-row">
        <div class="booking-left">
            <div class="small-text"><strong>ICE WALKER - GOT (PRIVATE GAME ONLY)</strong></div>
            <div class="small-text">Saturday, May 2, 2026</div>
            <div class="small-text">5:00 PM - 6:00 PM</div>
            <div class="small-text">Participants: 2</div>
            <div class="small-text">Booking Number: 1590512043427048</div>
            <br>
            <div class="small-text">Total price: <strong>$103.77</strong></div>
            <div class="small-text">Amount paid: <strong>$103.77</strong></div>
            <div class="small-text">Amount due: <strong>$0</strong></div>
        </div>

        <div class="booking-right">
            <img src="https://www-1590k.bookeo.com/bookeo/cfile/41551N96JNR14F91CA8DAC/1596573427935_6YHYYMJNUU96KANRCPXM6J3CCRN9HRLR_1000_666.jpg" loading="lazy"  decoding="async" >
        </div>
    </div>

    <!-- PRICE TABLE (BOOKEO STYLE → ONLY ONE) -->
    <h3 class="section-title">Price</h3>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Unit price</th>
                <th>Quantity</th>
                <th>Price</th>
            </tr>
        </thead>

        <tbody>
            <!-- MAIN ROW -->
            <tr>
                <td>
                    <strong>Saturday, May 2, 2026 5:00 PM</strong><br>
                    ICE WALKER - GOT (PRIVATE GAME ONLY) - participants
                </td>
                <td>$45</td>
                <td>2</td>
                <td>$90</td>
            </tr>

            <!-- SUBTOTAL -->
            <tr>
                <td><strong>Subtotal</strong></td>
                <td></td>
                <td></td>
                <td><strong>$90</strong></td>
            </tr>

            <!-- TAX LINES -->
            <tr>
                <td>Admission Tax</td><td></td><td></td><td>$4.50</td>
            </tr>
            <tr>
                <td>Redmond Sales tax</td><td></td><td></td><td>$9.27</td>
            </tr>

            <!-- TOTAL -->
            <tr>
                <td></td><td></td>
                <td class="right bold">Total</td>
                <td class="bold">$103.77</td>
            </tr>
        </tbody>
    </table>


    <!-- PAYMENTS -->
    <h3 class="section-title">Payments</h3>
    <table>
        <thead>
            <tr>
                <th>When</th>
                <th>Reason</th>
                <th>Payment method</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>12/3/2025 9:11 PM</td>
                <td>Credit from gift voucher 33NKMNN</td>
                <td>Generic gift voucher</td>
                <td>$103.77</td>
            </tr>
        </tbody>
    </table>


    <!-- GRAND TOTAL -->
    <h3 class="section-title">Grand Total</h3>
    <table>
        <tbody>
            <tr>
                <td class="right bold">Grand Total</td>
                <td class="bold">$214.36</td>
            </tr>
            <tr>
                <td class="right bold">Promotion / Voucher</td>
                <td class="bold">Applied</td>
            </tr>
        </tbody>
    </table>


    <!-- CANCELLATION POLICY -->
    <h3 class="section-title">Cancellation policy</h3>
    <div class="small-text">
       <ol>
           <li>No refund will be provided for cancellations made less than 3 days in advance, or in case of no-show</li>
           <li>A cancellation fee of 50% applies for cancellations made less than 7 days in advance.</li>
            <li>A cancellation fee of 10% applies for cancellations made 7 or more days in advance.</li>
       </ol>
    </div>

    <br>

    <!-- TERMS -->
    <h3 class="section-title">Terms & Conditions</h3>
    <div class="small-text">
        <p>Tickets are sold subject to the following conditions:</p>
        <ol>
            <li> You have expressly agreed to participate, join, enter, use, play and/or access to FLEE Escape Games at
your sole risk. FLEE does not warrant the reliability, accuracy, completeness, current or error-free of the
product, content and materials included.
</li>
            <li>FLEE shall not be responsible for any risk, hazard, danger, security, thread, safety and/or protection from
FLEE Escape Game.</li>
            <li> You shall not be allowed to record, capture or snap any photograph, video, film, tape, audio recording
whatsoever before, during and after the FLEE Escape Game.</li>
            <li> FLEE Escape Game reserve the right to update or modify terms and conditions at any time without prior
notice.
</li>
            <li>5% Admission tax is a mandatory requirement from the city for all the escape game venues. FLEE
Escape Game only collects the tax on behalf of the city that our escape rooms located.
</li>
            <li> FLEE reserves the right to refuse the service if participants are under the influence of alcohol or drugs</li>
        </ol>
    </div>

    <br>

    <div class="small-text" style="text-align:center;">
      Please always quote your booking number when contacting us about this booking
    </div>

</div>

</body>
</html>
