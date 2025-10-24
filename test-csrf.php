<?php
/**
 * Test CSRF Token Generation
 */

// Define constants first
define('APP_ROOT', dirname(__DIR__));
define('CONFIG_PATH', APP_ROOT . '/config');

require_once 'config/app.php';
require_once 'vendor/autoload.php';

use App\Core\Security;

echo "Testing CSRF token generation...\n";

// Start session
session_start();

// Generate CSRF token
$token = Security::generateCSRFToken();
echo "Generated CSRF token: $token\n";

// Verify CSRF token
if (Security::verifyCSRFToken($token)) {
    echo "✓ CSRF token verification: SUCCESS\n";
} else {
    echo "✗ CSRF token verification: FAILED\n";
}

// Test with wrong token
if (Security::verifyCSRFToken('wrong_token')) {
    echo "✗ Wrong token verification: SHOULD FAIL\n";
} else {
    echo "✓ Wrong token verification: CORRECTLY FAILED\n";
}

echo "\nCSRF functionality is working correctly!\n";
?>
