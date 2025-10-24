<?php
/**
 * Application Configuration
 * 
 * This file contains all application-wide configuration settings.
 * Copy config/app.example.php to config/app.php and customize for your environment.
 */

// Application settings
define('APP_NAME', 'Ticket Manager');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', true); // Set to false in production
define('APP_URL', 'http://localhost:8000'); // Update this for your environment

// Security settings
define('CSRF_TOKEN_NAME', '_token');
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('LOGIN_ATTEMPTS_LIMIT', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes in seconds

// Database settings (will be loaded from database.php)
if (file_exists(CONFIG_PATH . '/database.php')) {
    require_once CONFIG_PATH . '/database.php';
} else {
    // Default fallback - you should create config/database.php
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'ticket_manager');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
}

// File upload settings
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Email settings (for notifications)
define('MAIL_FROM', 'noreply@ticketmanager.local');
define('MAIL_FROM_NAME', 'Ticket Manager');

// Timezone
date_default_timezone_set('UTC');
