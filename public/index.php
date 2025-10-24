<?php
/**
 * Front Controller - Entry point for the ticket management application
 * 
 * This file handles all incoming requests and routes them to appropriate controllers.
 * It also initializes the application environment, database connection, and Twig templating.
 */

// Start output buffering to prevent any accidental output
ob_start();

// Set error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define application constants
define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('CONFIG_PATH', APP_ROOT . '/config');
define('TEMPLATES_PATH', APP_ROOT . '/templates');
define('VIEWS_PATH', APP_ROOT . '/views');

// Include Composer autoloader
require_once APP_ROOT . '/vendor/autoload.php';

// Include application configuration
require_once CONFIG_PATH . '/app.php';

// Start session with secure settings
session_start();

// Regenerate session ID periodically for security
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Initialize the application
try {
    $app = new App\Core\Application();
    $app->run();
} catch (Exception $e) {
    // Log error in production, show friendly message
    error_log('Application Error: ' . $e->getMessage());
    
    if (APP_DEBUG) {
        echo '<h1>Application Error</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        echo '<h1>Something went wrong</h1>';
        echo '<p>Please try again later or contact support if the problem persists.</p>';
    }
}

// Clean output buffer
ob_end_flush();
