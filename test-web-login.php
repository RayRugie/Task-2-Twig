<?php
/**
 * Test Web Login - Simulate the actual login process
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

echo "Testing Web Login Process\n";
echo "========================\n\n";

// Simulate POST data
$_POST = [
    'username' => 'admin',
    'password' => 'admin123',
    '_token' => Security::generateCSRFToken()
];

echo "1. Simulating login POST data...\n";
echo "   Username: " . $_POST['username'] . "\n";
echo "   Password: " . $_POST['password'] . "\n";
echo "   CSRF Token: " . $_POST['_token'] . "\n";

// Test CSRF verification
echo "\n2. Testing CSRF verification...\n";
if (Security::verifyCSRFToken($_POST['_token'])) {
    echo "✓ CSRF token is valid\n";
} else {
    echo "✗ CSRF token is invalid\n";
    exit(1);
}

// Test AuthController
echo "\n3. Testing AuthController...\n";
try {
    // Create a mock Twig environment
    $twig = new App\Core\SimpleTemplateEngine();
    
    // Create AuthController
    $authController = new AuthController($twig);
    
    echo "✓ AuthController created successfully\n";
    
    // Test the login method (we'll need to modify it to not redirect)
    echo "\n4. Testing login logic...\n";
    
    $data = Security::sanitizeInput($_POST);
    echo "   Sanitized data: " . print_r($data, true);
    
    // Check if user exists
    $db = \App\Core\Database::getInstance();
    $user = $db->fetchOne(
        'SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1',
        [$data['username'], $data['username']]
    );
    
    if ($user) {
        echo "✓ User found in database\n";
        
        if (Security::verifyPassword($data['password'], $user['password_hash'])) {
            echo "✓ Password verification successful\n";
            
            // Test session login
            Session::login($user);
            echo "✓ Session login successful\n";
            
            $currentUser = Session::getUser();
            if ($currentUser) {
                echo "✓ Current user: " . $currentUser['username'] . "\n";
                echo "✓ Login should work!\n";
            }
        } else {
            echo "✗ Password verification failed\n";
        }
    } else {
        echo "✗ User not found\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\nTest completed!\n";
?>
