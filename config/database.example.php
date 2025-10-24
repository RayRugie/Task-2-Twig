<?php
/**
 * Database Configuration Example
 * 
 * Copy this file to config/database.php and update with your database credentials.
 * Never commit the actual database.php file to version control.
 */

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'ticket_manager');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');

// PDO options
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
]);
