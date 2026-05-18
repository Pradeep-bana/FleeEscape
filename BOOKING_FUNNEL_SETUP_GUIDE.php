<?php
/**
 * BOOKING FUNNEL TRACKING - QUICK START GUIDE
 * 
 * This file is just documentation - safe to delete
 */
?>

<!-- 
================================================================================
BOOKING FUNNEL TRACKING SYSTEM - SETUP COMPLETE
================================================================================

You now have a dedicated booking funnel tracking system that monitors every step
of the user journey from adding items to cart through booking confirmation.

FILES CREATED:
==============
1. includes/booking_funnel.php - Core tracking library
2. log_funnel_event.php - Logging endpoint for JavaScript
3. booking_funnel_report.php - Beautiful analytics dashboard

FILES MODIFIED (with tracking added):
======================================
1. booking.php - Step tracking + JavaScript integration
2. cart_session.php - Add to cart event logging
3. add_addon_to_cart.php - Addon addition tracking
4. process_booking.php - Payment & booking confirmation tracking

LOG FILE:
=========
booking_funnel.log - Separate, clean log file (not mixed with system logs)
Each entry is a JSON object containing:
  - timestamp
  - event type
  - session_id
  - user_id (if logged in)
  - client IP
  - event-specific data

EVENTS BEING TRACKED:
=====================
1. ADD_TO_CART - User adds experience to cart (from landing page)
2. CHECKOUT_STARTED - User navigates to booking.php
3. ADDON_ADDED - User adds an addon
4. STEP_1_REACHED - User reaches Step 1: Choose Experience
5. STEP_2_REACHED - User reaches Step 2: Add Ons
6. STEP_3_REACHED - User reaches Step 3: Customer Details
7. STEP_4_REACHED - User reaches Step 4: Payment Details
8. STEP_5_REACHED - User reaches Step 5: Confirmation
9. PAYMENT_SUBMITTED - Payment processed successfully
10. BOOKING_CONFIRMED - Booking created successfully
11. BOOKING_FAILED - Booking creation failed
12. ADDON_ADDED - (tracked when addons are selected)

HOW TO VIEW THE REPORT:
=======================
1. Open: http://yoursite.com/booking_funnel_report.php
2. See conversion rates between each step
3. Identify exactly where users are dropping off
4. View individual session journeys (last 100 sessions)
5. Filter by time period (last 24h, 7d, 30d, etc.)

UNDERSTANDING THE METRICS:
===========================
The report shows:
- Total Sessions: Number of unique sessions tracked
- Conversion % between steps: Percentage of users who continued to next step
- Drop-off points: Where users stop the booking flow

Example:
  Add to Cart: 100 users
  Checkout Started: 85 users (85% conversion)
  Step 1: 80 users (94% conversion)
  Step 2: 75 users (94% conversion)
  Step 3: 70 users (93% conversion)
  Step 4: 65 users (93% conversion)
  Payment Submitted: 60 users (92% conversion)
  Booking Confirmed: 55 users (92% conversion)

The report will help you identify if the drop-off is at:
- Cart → Booking page (maybe the checkout button isn't being clicked)
- Early steps (maybe form is confusing)
- Payment step (maybe payment gateway issues)
- Confirmation (maybe something fails silently)

SECURITY NOTE:
===============
The log file is a plain text JSON file. Make sure:
1. It's not accessible from web (should be outside htdocs)
2. It contains session IDs and IP addresses (moderate sensitivity)
3. Consider backing it up before it gets too large
4. The report page currently has no auth check - add one before production

HOW TO ADD AUTH TO REPORT:
===========================
Edit booking_funnel_report.php and uncomment the auth check at the top:

    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

Or add your own admin role check.

NEXT STEPS:
===========
1. Test by going through booking flow:
   - Add item to cart
   - Go through each step
   - Complete a booking
2. Visit report page: /booking_funnel_report.php
3. You should see your test session in the "Recent Sessions" table
4. Wait a day or two for real user data
5. Analyze where bookings are dropping off
6. Debug that specific step

TIPS:
======
- The log file grows over time. You may want to archive old logs monthly
- If it gets too large (>50MB), consider truncating or moving old entries
- The report can be exported for further analysis with proper tools
- Compare drop-off points before/after website changes to measure impact
- Monitor which specific addons/experiences have lowest conversion

NO PERFORMANCE IMPACT:
======================
- Logging is non-blocking (won't slow down user experience)
- Failed logging silently (won't break booking if logging fails)
- Minimal database queries
- Report generation is efficient even with large datasets

TROUBLESHOOTING:
=================
If events aren't being logged:
1. Check file permissions on booking_funnel.log
2. Make sure includes/booking_funnel.php is being loaded
3. Check PHP error logs
4. Verify session is active (session_start() called)

If report page is blank:
1. Check if log file exists at root directory
2. Check PHP error logs
3. Make sure there are entries in the log file

Questions? Check the functions in includes/booking_funnel.php for
detailed documentation on the logging system.

================================================================================
-->
