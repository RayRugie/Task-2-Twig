<?php
/**
 * Test Actual Login - Simulate the exact web request
 */

// Define constants
define('APP_ROOT', dirname(__DIR__));
define('CONFIG_PATH', APP_ROOT . '/config');
define('TEMPLATES_PATH', APP_ROOT . '/templates');
define('PUBLIC_PATH', APP_ROOT . '/public');

require_once 'config/app.php';
require_once 'config/database.php';
require_once 'vendor/autoload.php';

use App\Core\Security;
use App\Core\Session;
use App\Controllers\AuthController;

// Start session
session_start();

echo "Testing Actual Login Process\n";
echo "===========================\n\n";

// Simulate the exact POST request that would come from the login form
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/login';
$_POST = [
    'username' => 'admin',
    'password' => 'admin123',
    '_token' => Security::generateCSRFToken()
];

echo "1. Simulating POST request to /login...\n";
echo "   Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "   URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "   POST data: " . print_r($_POST, true);

// Test the AuthController login method
echo "\n2. Testing AuthController login method...\n";
try {
    // Create a mock Twig environment
    $twig = new App\Core\SimpleTemplateEngine();
    
    // Create AuthController
    $authController = new AuthController($twig);
    
    echo "✓ AuthController created\n";
    
    // Test the login method directly
    echo "\n3. Calling login method...\n";
    
    // We need to capture the output to see what happens
    ob_start();
    
    try {
        $authController->login();
        $output = ob_get_clean();
        echo "✓ Login method executed\n";
        echo "Output: " . $output . "\n";
    } catch (Exception $e) {
        ob_end_clean();
        echo "✗ Login method failed: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error creating AuthController: " . $e->getMessage() . "\n";
}

echo "\nTest completed!\n";
?>
