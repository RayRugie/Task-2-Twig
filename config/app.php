<?php
/**
 * Application Configuration
 * 
 * This file contains all application-wide configuration settings.
 * Copy config/app.example.php to config/app.php and customize for your environment.
 */

// Application settings
define('APP_NAME', 'Ticketa');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', true); // Set to false in production
define('APP_URL', 'http://localhost:8000'); // Update this for your environment

// Supabase settings
define('SUPABASE_URL', getenv('SUPABASE_URL') ?: 'https://zarjztnhyohmtqsxwtxx.supabase.co');
define('SUPABASE_ANON_KEY', getenv('SUPABASE_ANON_KEY') ?: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inphcmp6dG5oeW9obXRxc3h3dHh4Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjEyODU1MTksImV4cCI6MjA3Njg2MTUxOX0.axhIv5N0ZhvIH8NpPvX49BSym_CLLhlETo7ZMEz9ypE');
define('SUPABASE_SERVICE_ROLE_KEY', getenv('SUPABASE_SERVICE_ROLE_KEY') ?: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inphcmp6dG5oeW9obXRxc3h3dHh4Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc2MTI4NTUxOSwiZXhwIjoyMDc2ODYxNTE5fQ.e3A5HrDrHV9M-DA2Vb_RJa8DxSKmuRAWf6glrLhtt5o');

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
