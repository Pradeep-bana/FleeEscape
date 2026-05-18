<?php
/**
 * Booking Funnel Tracking
 * Tracks every step of the booking process from add to cart to confirmation
 * This is a dedicated, clean log file separate from noisy system logs
 */

if (!defined('FLEE_BOOKING_FUNNEL_LOG_FILE')) {
    define('FLEE_BOOKING_FUNNEL_LOG_FILE', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'booking_funnel.log');
}

if (!function_exists('flee_funnel_log')) {
    /**
     * Log a booking funnel event
     * 
     * @param string $funnel_event - Event type (e.g., 'ADD_TO_CART', 'PAYMENT_SUBMITTED', etc.)
     * @param array $data - Additional data to log
     * @param string $session_id - Optional session ID (auto-detected if not provided)
     */
    function flee_funnel_log($funnel_event, $data = [], $session_id = null) {
        if (!$session_id) {
            $session_id = session_id() ?: 'NO_SESSION';
        }

        // Get LA timezone timestamp
        $la_tz = new DateTimeZone('America/Los_Angeles');
        $timestamp = (new DateTime('now', $la_tz))->format('Y-m-d H:i:s');
        
        $client_ip = getenv('HTTP_CLIENT_IP') ?: getenv('HTTP_X_FORWARDED_FOR') ?: $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        
        // Automatically include customer details if available
        $customer_details = null;
        if (isset($_SESSION['firstName']) || isset($_SESSION['lastName']) || isset($_SESSION['email']) || isset($_SESSION['phone'])) {
            $customer_details = [
                'name' => trim(($_SESSION['firstName'] ?? '') . ' ' . ($_SESSION['lastName'] ?? '')),
                'email' => $_SESSION['email'] ?? null,
                'phone' => $_SESSION['phone'] ?? null,
                'additional_guests' => $_SESSION['additional_guests'] ?? 0
            ];
        }

        $log_entry = [
            'timestamp' => $timestamp,
            'event' => $funnel_event,
            'session_id' => $session_id,
            'client_ip' => $client_ip,
            'user_id' => $_SESSION['user_id'] ?? null,
            'event_id' => $data['event_id'] ?? null,  // Event/Experience ID
            'customer' => $customer_details,
            'data' => $data
        ];

        $log_line = json_encode($log_entry) . "\n";

        // Write to log file
        if (file_put_contents(FLEE_BOOKING_FUNNEL_LOG_FILE, $log_line, FILE_APPEND | LOCK_EX)) {
            // Success - file was written
        } else {
            error_log("Failed to write to booking funnel log: " . FLEE_BOOKING_FUNNEL_LOG_FILE);
        }
    }
}

if (!function_exists('flee_get_funnel_report')) {
    /**
     * Generate a report of booking funnel metrics
     * Shows conversion rates between each step
     * 
     * @param int $hours - Last N hours to analyze (default: 24)
     * @return array - Report data
     */
    function flee_get_funnel_report($hours = 24) {
        if (!file_exists(FLEE_BOOKING_FUNNEL_LOG_FILE)) {
            return ['error' => 'No log file found'];
        }

        $lines = file(FLEE_BOOKING_FUNNEL_LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $cutoff_time = strtotime("-$hours hours");
        
        // Track sessions and their journey
        $sessions = [];
        $event_counts = [];

        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            if (!$entry) continue;

            $entry_time = strtotime($entry['timestamp']);
            if ($entry_time < $cutoff_time) continue;

            $sid = $entry['session_id'];
            $event = $entry['event'];

            if (!isset($sessions[$sid])) {
                $sessions[$sid] = [
                    'first_event' => $entry['timestamp'],
                    'last_event' => $entry['timestamp'],
                    'events' => [],
                    'user_id' => $entry['user_id'],
                    'ip' => $entry['client_ip']
                ];
            }

            $sessions[$sid]['events'][] = $event;
            $sessions[$sid]['last_event'] = $entry['timestamp'];

            if (!isset($event_counts[$event])) {
                $event_counts[$event] = 0;
            }
            $event_counts[$event]++;
        }

        // Calculate conversion rates
        $funnel_steps = [
            'ADD_TO_CART',
            'CHECKOUT_STARTED',
            'STEP_1_REACHED',
            'STEP_2_REACHED',
            'STEP_3_REACHED',
            'STEP_4_REACHED',
            'STEP_5_REACHED',
            'PAYMENT_SUBMITTED',
            'BOOKING_CONFIRMED'
        ];

        $conversion_data = [];
        $previous_count = 0;

        foreach ($funnel_steps as $step) {
            $count = $event_counts[$step] ?? 0;
            $conversion_data[$step] = [
                'count' => $count,
                'from_previous' => $previous_count > 0 ? round(($count / $previous_count) * 100, 2) . '%' : 'N/A'
            ];
            if ($count > 0) {
                $previous_count = $count;
            }
        }

        // Sessions that reached each milestone
        $milestones = [];
        foreach ($funnel_steps as $step) {
            $count = 0;
            foreach ($sessions as $session_data) {
                if (in_array($step, $session_data['events'])) {
                    $count++;
                }
            }
            $milestones[$step] = $count;
        }

        return [
            'period_hours' => $hours,
            'total_sessions' => count($sessions),
            'event_counts' => $event_counts,
            'conversion_data' => $conversion_data,
            'milestones' => $milestones,
            'sessions_detail' => array_slice($sessions, 0, 100) // Last 100 sessions
        ];
    }
}

if (!function_exists('flee_track_abandoned_cart')) {
    /**
     * Track an abandoned cart - called when session is about to be cleared without booking
     * 
     * @param string $session_id - Session ID
     * @param string $reason - Reason for abandonment (expired, manual_clear, etc.)
     * @param array $cart_data - Cart contents (if available)
     */
    function flee_track_abandoned_cart($session_id = null, $reason = 'unknown', $cart_data = []) {
        if (!$session_id) {
            $session_id = session_id() ?: 'NO_SESSION';
        }

        // Only log if session had items (not empty cart abandonment)
        if (empty($cart_data)) {
            return; // Don't track empty cart clear
        }

        $total_items = count($cart_data);
        $total_value = 0;
        $items_list = [];
        $event_ids = [];

        foreach ($cart_data as $item) {
            $price = (float)($item['price'] ?? 0);
            $guests = (int)($item['guests'] ?? 1);
            $item_total = $price * $guests;
            $total_value += $item_total;
            $items_list[] = ($item['game_name'] ?? $item['gameName'] ?? 'Unknown') . ' (' . $guests . ' guests)';
            if (!empty($item['event_id'])) {
                $event_ids[] = $item['event_id'];
            }
        }

        flee_funnel_log('CART_ABANDONED', [
            'reason' => $reason,
            'event_ids' => $event_ids,
            'items_count' => $total_items,
            'total_value' => round($total_value, 2),
            'items' => implode(' | ', $items_list)
        ], $session_id);
    }
}

// Funnel Events Documentation:
// ============================
// ADD_TO_CART - User adds an experience to cart
// ADDON_ADDED - User adds an addon to their experience
// ADDON_REMOVED - User removes an addon
// ITEM_REMOVED_FROM_CART - User removes an experience from cart
// STEP_2_REACHED - User reaches Step 2 (Add Ons)
// STEP_3_REACHED - User reaches Step 3 (Customer Details)
// STEP_4_REACHED - User reaches Step 4 (Payment Details)
// STEP_5_REACHED - User reaches Step 5 (Booking Confirmation)
// PAYMENT_SUBMITTED - User submits payment form
// BOOKING_CONFIRMED - Booking successfully created in Bookeo
// BOOKING_FAILED - Booking creation failed
// CART_ABANDONED - User abandoned cart without completing booking (has items but no booking)
