<?php
/**
 * Application Configuration
 * 
 * This file contains all application-wide configuration settings.
 * Copy config/app.example.php to config/app.php and customize for your environment.
 */

// Application settings
// Support environment variables for deployment (Fly.io, Heroku, etc.)
$appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'development';
$appDebug = ($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: 'true') === 'true';

define('APP_NAME', $_ENV['APP_NAME'] ?? getenv('APP_NAME') ?: 'Ticketa');
define('APP_VERSION', '1.0.0');
define('APP_ENV', $appEnv);
define('APP_DEBUG', $appDebug);
define('APP_URL', $_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'http://localhost:8000');

// Load .env file if it exists (local development)
if (file_exists(APP_ROOT . '/.env')) {
    $envFile = file_get_contents(APP_ROOT . '/.env');
    $lines = explode("\n", $envFile);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $_ENV[$key] = $value;
        }
    }
}

// Supabase settings - Support environment variables for production deployment
// Priority: .env file → $_ENV → getenv() → fallback to empty (will error if not set)
define('SUPABASE_URL', $_ENV['SUPABASE_URL'] ?? getenv('SUPABASE_URL') ?: '');
define('SUPABASE_ANON_KEY', $_ENV['SUPABASE_ANON_KEY'] ?? getenv('SUPABASE_ANON_KEY') ?: '');
define('SUPABASE_SERVICE_ROLE_KEY', $_ENV['SUPABASE_SERVICE_ROLE_KEY'] ?? getenv('SUPABASE_SERVICE_ROLE_KEY') ?: '');

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
