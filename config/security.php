<?php
/**
 * Security Configuration
 * Handles security-related functions and settings
 */

// Start secure session
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', 'Strict');

        session_start();

        // Regenerate session ID periodically for security
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } elseif (time() - $_SESSION['created'] > SESSION_LIFETIME) {
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
}

// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Input sanitization
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Password validation
function validatePassword($password) {
    return strlen($password) >= PASSWORD_MIN_LENGTH &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[0-9]/', $password);
}

// Email validation
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Rate limiting
class RateLimiter {
    private static $attempts = [];

    public static function checkLimit($key, $maxAttempts = MAX_LOGIN_ATTEMPTS, $timeWindow = 900) { // 15 minutes
        $now = time();

        if (!isset(self::$attempts[$key])) {
            self::$attempts[$key] = [];
        }

        // Remove old attempts outside the time window
        self::$attempts[$key] = array_filter(self::$attempts[$key], function($timestamp) use ($now, $timeWindow) {
            return ($now - $timestamp) < $timeWindow;
        });

        if (count(self::$attempts[$key]) >= $maxAttempts) {
            return false; // Rate limit exceeded
        }

        self::$attempts[$key][] = $now;
        return true; // Within limit
    }

    public static function getRemainingAttempts($key, $maxAttempts = MAX_LOGIN_ATTEMPTS) {
        if (!isset(self::$attempts[$key])) {
            return $maxAttempts;
        }
        return max(0, $maxAttempts - count(self::$attempts[$key]));
    }
}

// XSS Protection for HTML output
function safeOutput($data) {
    if (is_array($data)) {
        return array_map('safeOutput', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// SQL Injection Prevention (additional layer)
function prepareStatement($sql, $params = []) {
    global $db;
    $stmt = $db->prepare($sql);

    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $db->error);
    }

    if (!empty($params)) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        $stmt->bind_param($types, ...$params);
    }

    return $stmt;
}

// Initialize security on every request
startSecureSession();
?>