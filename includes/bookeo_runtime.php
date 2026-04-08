<?php

if (!defined('FLEE_BOOKEO_RUNTIME')) {
    define('FLEE_BOOKEO_RUNTIME', true);
}

if (!defined('FLEE_BOOKEO_THROTTLE_FILE')) {
    define('FLEE_BOOKEO_THROTTLE_FILE', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bookeo_throttle.txt');
}

if (!defined('FLEE_BOOKEO_UNIVERSAL_LOG_FILE')) {
    define('FLEE_BOOKEO_UNIVERSAL_LOG_FILE', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bookeo_universal.log');
}

if (!defined('FLEE_BOOKEO_ERROR_HISTORY_FILE')) {
    define('FLEE_BOOKEO_ERROR_HISTORY_FILE', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bookeo_error_history.json');
}

if (!defined('FLEE_BOOKEO_LOCK_DIR')) {
    define('FLEE_BOOKEO_LOCK_DIR', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bookeo_locks');
}

if (!defined('FLEE_BOOKEO_DAY_CACHE_DIR')) {
    define('FLEE_BOOKEO_DAY_CACHE_DIR', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bookeo_day_cache');
}

if (!defined('FLEE_BOOKEO_LOG_SUCCESSFUL_GETS')) {
    define('FLEE_BOOKEO_LOG_SUCCESSFUL_GETS', true);
}

if (!function_exists('flee_bookeo_now_la')) {
    function flee_bookeo_now_la()
    {
        return new DateTime('now', new DateTimeZone('America/Los_Angeles'));
    }
}

if (!function_exists('flee_bookeo_now_india')) {
    function flee_bookeo_now_india()
    {
        return new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    }
}

if (!function_exists('flee_bookeo_client_ip')) {
    function flee_bookeo_client_ip()
    {
        $keys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ($keys as $key) {
            if (empty($_SERVER[$key])) {
                continue;
            }

            $value = trim((string)$_SERVER[$key]);
            if ($value === '') {
                continue;
            }

            if ($key === 'HTTP_X_FORWARDED_FOR') {
                $parts = array_map('trim', explode(',', $value));
                return $parts[0] ?? 'CLI';
            }

            return $value;
        }

        return PHP_SAPI === 'cli' ? 'CLI' : 'UNKNOWN';
    }
}

if (!function_exists('flee_bookeo_log')) {
    function flee_bookeo_log($context, array $fields = [])
    {
        $la = flee_bookeo_now_la()->format('Y-m-d h:i:s A T');
        $india = flee_bookeo_now_india()->format('Y-m-d h:i:s A T');
        $base = [
            'la_time' => $la,
            'india_time' => $india,
            'ip' => flee_bookeo_client_ip(),
            'script' => $_SERVER['SCRIPT_NAME'] ?? basename($_SERVER['PHP_SELF'] ?? ''),
            'method' => $_SERVER['REQUEST_METHOD'] ?? (PHP_SAPI === 'cli' ? 'CLI' : 'UNKNOWN'),
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
        ];

        $merged = array_merge($base, $fields);
        $parts = [];
        foreach ($merged as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            $parts[] = $key . '=' . str_replace(["\r", "\n"], [' ', ' '], (string)$value);
        }

        $line = '[' . strtoupper((string)$context) . '] ' . implode(' | ', $parts) . PHP_EOL;
        file_put_contents(FLEE_BOOKEO_UNIVERSAL_LOG_FILE, $line, FILE_APPEND | LOCK_EX);
    }
}

if (!function_exists('flee_bookeo_log_message')) {
    function flee_bookeo_log_message($context, $message, array $fields = [])
    {
        $fields['message'] = $message;
        flee_bookeo_log($context, $fields);
    }
}

if (!function_exists('flee_bookeo_request_caller')) {
    function flee_bookeo_request_caller()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 12);
        foreach ($trace as $frame) {
            $file = $frame['file'] ?? '';
            if ($file === '' || realpath($file) === realpath(__FILE__)) {
                continue;
            }

            return [
                'caller_file' => str_replace('\\', '/', $file),
                'caller_line' => (int)($frame['line'] ?? 0),
                'caller_function' => $frame['function'] ?? '',
            ];
        }

        return [];
    }
}

if (!function_exists('flee_bookeo_is_throttled')) {
    function flee_bookeo_is_throttled()
    {
        if (!file_exists(FLEE_BOOKEO_THROTTLE_FILE)) {
            return false;
        }

        clearstatcache(true, FLEE_BOOKEO_THROTTLE_FILE);
        return time() < (int)file_get_contents(FLEE_BOOKEO_THROTTLE_FILE);
    }
}

if (!function_exists('flee_bookeo_retry_after_seconds')) {
    function flee_bookeo_retry_after_seconds()
    {
        if (!file_exists(FLEE_BOOKEO_THROTTLE_FILE)) {
            return 0;
        }

        clearstatcache(true, FLEE_BOOKEO_THROTTLE_FILE);
        return max(0, ((int)file_get_contents(FLEE_BOOKEO_THROTTLE_FILE)) - time());
    }
}

if (!function_exists('flee_bookeo_set_throttle')) {
    function flee_bookeo_set_throttle($retryAfterSeconds, $context = 'bookeo_throttle')
    {
        $resumeAt = time() + max(0, (int)$retryAfterSeconds) + 2;
        file_put_contents(FLEE_BOOKEO_THROTTLE_FILE, (string)$resumeAt, LOCK_EX);
        flee_bookeo_log_message($context, 'Global Bookeo throttle engaged', [
            'retry_after_seconds' => (int)$retryAfterSeconds,
            'resume_after_seconds' => max(0, $resumeAt - time()),
        ]);
    }
}

if (!function_exists('flee_bookeo_error_history_read')) {
    function flee_bookeo_error_history_read()
    {
        if (!file_exists(FLEE_BOOKEO_ERROR_HISTORY_FILE)) {
            return [];
        }

        $raw = file_get_contents(FLEE_BOOKEO_ERROR_HISTORY_FILE);
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}

if (!function_exists('flee_bookeo_error_history_write')) {
    function flee_bookeo_error_history_write(array $history)
    {
        file_put_contents(
            FLEE_BOOKEO_ERROR_HISTORY_FILE,
            json_encode(array_values($history), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }
}

if (!function_exists('flee_bookeo_is_qualifying_error')) {
    function flee_bookeo_is_qualifying_error($httpCode)
    {
        $httpCode = (int)$httpCode;
        if ($httpCode >= 500) {
            return true;
        }

        return in_array($httpCode, [403, 404, 409, 422, 429], true);
    }
}

if (!function_exists('flee_bookeo_track_error_burst')) {
    function flee_bookeo_track_error_burst($httpCode, $context, $url, $method, $responseBody = '')
    {
        if (!flee_bookeo_is_qualifying_error($httpCode)) {
            return;
        }

        $now = time();
        $history = flee_bookeo_error_history_read();
        $history = array_values(array_filter($history, static function ($entry) use ($now) {
            return is_array($entry) && isset($entry['ts']) && ((int)$entry['ts'] >= ($now - 180));
        }));

        $history[] = [
            'ts' => $now,
            'code' => (int)$httpCode,
            'context' => (string)$context,
            'url' => (string)$url,
            'method' => (string)$method,
        ];

        flee_bookeo_error_history_write($history);

        $last30 = 0;
        $last120 = 0;
        foreach ($history as $entry) {
            $age = $now - (int)$entry['ts'];
            if ($age <= 30) {
                $last30++;
            }
            if ($age <= 120) {
                $last120++;
            }
        }

        $cooldown = 0;
        if ($last120 >= 12) {
            $cooldown = 90;
        } elseif ($last30 >= 6) {
            $cooldown = 20;
        }

        if ($cooldown <= 0 || flee_bookeo_is_throttled()) {
            return;
        }

        flee_bookeo_set_throttle($cooldown, $context . '_error_burst');
        flee_bookeo_log_message($context . '_error_burst', 'Global Bookeo throttle engaged because of a recent error burst', [
            'http_code' => (int)$httpCode,
            'last_30s_errors' => $last30,
            'last_120s_errors' => $last120,
            'cooldown_seconds' => $cooldown,
            'endpoint' => $url,
            'http_method' => $method,
            'response_excerpt' => is_string($responseBody) ? substr(str_replace(["\r", "\n"], [' ', ' '], $responseBody), 0, 300) : '',
        ]);

        flee_bookeo_error_history_write([]);
    }
}

if (!function_exists('flee_bookeo_request')) {
    function flee_bookeo_request($method, $url, array $options = [])
    {
        $method = strtoupper($method);
        $context = $options['context'] ?? 'bookeo_request';
        $timeout = isset($options['timeout']) ? (int)$options['timeout'] : 20;
        $headers = $options['headers'] ?? [];
        $body = $options['body'] ?? null;

        if (empty($options['skip_throttle']) && flee_bookeo_is_throttled()) {
            $waitSeconds = flee_bookeo_retry_after_seconds();
            flee_bookeo_log($context . '_THROTTLED', [
                'endpoint' => $url,
                'wait_seconds' => $waitSeconds,
            ]);

            return [
                'code' => 429,
                'body' => '',
                'data' => null,
                'error' => 'Globally throttled',
                'url' => $url,
                'throttled' => true,
                'retry_after' => $waitSeconds,
            ];
        }

        $startTime = microtime(true);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($body) ? $body : json_encode($body));
        }

        // --- KEEP: GLOBAL OUTBOUND RATE LIMITER ---
        // Force all concurrent users/scripts into a single-file line
        $globalLockHandle = flee_bookeo_acquire_lock('global_outbound_rate_limit', 30);
        if ($globalLockHandle !== false) {
            $lastCallTimeFile = dirname(FLEE_BOOKEO_THROTTLE_FILE) . DIRECTORY_SEPARATOR . 'bookeo_last_call_time.txt';
            $minSpacingSeconds = 0.6; // Max ~1.6 requests per second globally

            if (file_exists($lastCallTimeFile)) {
                $lastCallTime = (float)file_get_contents($lastCallTimeFile);
                $elapsed = microtime(true) - $lastCallTime;
                if ($elapsed > 0 && $elapsed < $minSpacingSeconds) {
                    usleep((int)(($minSpacingSeconds - $elapsed) * 1000000));
                }
            }
            file_put_contents($lastCallTimeFile, (string)microtime(true));
            flee_bookeo_release_lock($globalLockHandle);
            $globalLockHandle = false;
        } else {
            flee_bookeo_log($context . '_LOCK_WARNING', [
                'warning' => 'Global outbound rate-limit lock was not acquired before Bookeo request',
                'endpoint' => $url,
            ]);
        }
        // -----------------------------------------

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // --- RELEASE RATE LIMITER LOCK (Fallback) ---
        if ($globalLockHandle !== false) {
            flee_bookeo_release_lock($globalLockHandle);
        }
        // ---------------------------------

        $timeTaken = round((microtime(true) - $startTime) * 1000, 2) . 'ms';

        $decoded = null;
        if (is_string($response) && $response !== '') {
            $decoded = json_decode($response, true);
        }

        if ($httpCode === 429) {
            $retryAfter = (int)($decoded['retryAfter'] ?? 30);
            flee_bookeo_set_throttle($retryAfter, $context . '_429');
        }

        // --- KEEP: ERROR BURST TRACKER ---
        if ($httpCode >= 400 && function_exists('flee_bookeo_track_error_burst')) {
            flee_bookeo_track_error_burst($httpCode, $context, $url, $method, $response);
        }

        // ========================================================
        // CLEAN LOGGING: ONE CONSOLIDATED LINE
        // ========================================================
        $logMeta = [
            'http_method' => $method,
            'endpoint' => $url,
            'http_code' => $httpCode,
            'duration' => $timeTaken,
        ];

        // KEEP: Caller info
        if (function_exists('flee_bookeo_request_caller')) {
            $logMeta = array_merge($logMeta, flee_bookeo_request_caller());
        }

        if ($method !== 'GET' && $body !== null) {
            $logMeta['req_body'] = $body;
        }

        if ($response === false) {
            $logMeta['curl_error'] = $curlError;
            flee_bookeo_log($context . '_NETWORK_FAIL', $logMeta);
            
            return [
                'code' => 0,
                'body' => false,
                'data' => null,
                'error' => $curlError,
                'url' => $url,
            ];
        }

        // If it's an error (400, 403, etc.), include the response body so you can see why it failed
        if ($httpCode < 200 || $httpCode >= 300) {
            $logMeta['res_body'] = $response;
            $logTag = $context . '_FAIL';
        } else {
            $logTag = $context . '_SUCCESS'; // Don't log full response on success to keep logs clean
        }

        $isSuccessfulGet = ($method === 'GET' && $httpCode >= 200 && $httpCode < 300);
        $shouldLog = !FLEE_BOOKEO_LOG_SUCCESSFUL_GETS ? !$isSuccessfulGet : true;

        if ($shouldLog) {
            flee_bookeo_log($logTag, $logMeta);
        }

        return [
            'code' => $httpCode,
            'body' => $response,
            'data' => $decoded,
            'error' => $curlError ?: null,
            'url' => $url,
        ];
    }
}

if (!function_exists('flee_bookeo_placeholder_event_id')) {
    function flee_bookeo_placeholder_event_id($date)
    {
        $normalized = preg_replace('/[^0-9]/', '', (string)$date);
        if ($normalized === '') {
            $normalized = date('Ymd');
        }

        // Keep this short so it fits the same DB width as real Bookeo event IDs.
        return 'EMPTY' . substr($normalized, 0, 8);
    }
}

if (!function_exists('flee_bookeo_is_placeholder_event_id')) {
    function flee_bookeo_is_placeholder_event_id($eventId)
    {
        return strpos((string)$eventId, 'EMPTY') === 0 || strpos((string)$eventId, 'placeholder_empty_day_') === 0;
    }
}

if (!function_exists('flee_bookeo_acquire_lock')) {
    function flee_bookeo_acquire_lock($key, $timeoutSeconds = 15)
    {
        if (!is_dir(FLEE_BOOKEO_LOCK_DIR)) {
            @mkdir(FLEE_BOOKEO_LOCK_DIR, 0777, true);
        }

        $safeKey = preg_replace('/[^A-Za-z0-9_.-]/', '_', (string)$key);
        $path = FLEE_BOOKEO_LOCK_DIR . DIRECTORY_SEPARATOR . $safeKey . '.lock';
        $handle = fopen($path, 'c+');
        if ($handle === false) {
            return false;
        }

        $startedAt = time();
        do {
            if (@flock($handle, LOCK_EX | LOCK_NB)) {
                ftruncate($handle, 0);
                fwrite($handle, (string)getmypid());
                fflush($handle);
                return $handle;
            }

            usleep(200000);
        } while ((time() - $startedAt) < $timeoutSeconds);

        fclose($handle);
        return false;
    }
}

if (!function_exists('flee_bookeo_release_lock')) {
    function flee_bookeo_release_lock($handle)
    {
        if (is_resource($handle)) {
            @flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}

if (!function_exists('flee_bookeo_day_cache_path')) {
    function flee_bookeo_day_cache_path($date)
    {
        $normalized = preg_replace('/[^0-9-]/', '', (string)$date);
        if ($normalized === '') {
            return null;
        }

        if (!is_dir(FLEE_BOOKEO_DAY_CACHE_DIR)) {
            @mkdir(FLEE_BOOKEO_DAY_CACHE_DIR, 0777, true);
        }

        return FLEE_BOOKEO_DAY_CACHE_DIR . DIRECTORY_SEPARATOR . $normalized . '.txt';
    }
}

if (!function_exists('flee_bookeo_mark_day_cache_fresh')) {
    function flee_bookeo_mark_day_cache_fresh($date, $expiresAt)
    {
        $path = flee_bookeo_day_cache_path($date);
        if ($path === null) {
            return;
        }

        $laTz = new DateTimeZone('America/Los_Angeles');
        if ($expiresAt instanceof DateTimeInterface) {
            $expiry = (clone $expiresAt)->setTimezone($laTz)->getTimestamp();
        } else {
            $expiry = (new DateTime((string)$expiresAt, $laTz))->getTimestamp();
        }

        file_put_contents($path, (string)$expiry, LOCK_EX);
    }
}

if (!function_exists('flee_bookeo_is_day_cache_fresh')) {
    function flee_bookeo_is_day_cache_fresh($date, DateTime $nowLocal = null)
    {
        $path = flee_bookeo_day_cache_path($date);
        if ($path === null || !file_exists($path)) {
            return false;
        }

        clearstatcache(true, $path);
        $expiry = (int)file_get_contents($path);
        $nowTs = $nowLocal ? $nowLocal->getTimestamp() : flee_bookeo_now_la()->getTimestamp();
        return $expiry >= $nowTs;
    }
}

if (!function_exists('flee_bookeo_clear_day_cache')) {
    function flee_bookeo_clear_day_cache($date)
    {
        $path = flee_bookeo_day_cache_path($date);
        if ($path !== null && file_exists($path)) {
            @unlink($path);
        }
    }
}
