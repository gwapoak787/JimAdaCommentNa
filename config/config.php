<?php
/**
 * Main Configuration File
 * Centralizes all application configuration settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'scholarship_finder');

// Application Settings
define('APP_NAME', 'Scholarship Finder');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/COde-project1-main');

// Security Settings
define('SESSION_LIFETIME', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);

// File Upload Settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);

// Email Configuration (for future use)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');

// API Settings
define('API_RATE_LIMIT', 100); // requests per hour
define('API_CACHE_TIME', 300); // 5 minutes

// Development Settings
define('DEBUG_MODE', true);
define('LOG_ERRORS', true);
define('DISPLAY_ERRORS', true);

// Path Constants
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// Include additional configuration files
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/security.php';
?>