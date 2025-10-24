<?php
/**
 * Debug Login Process
 */

// Define constants
define('APP_ROOT', dirname(__DIR__));
define('CONFIG_PATH', APP_ROOT . '/config');
define('TEMPLATES_PATH', APP_ROOT . '/templates');

require_once 'config/app.php';
require_once 'config/database.php';
require_once 'vendor/autoload.php';

use App\Core\Security;
use App\Core\Session;
use App\Core\Database;

// Start session
session_start();

echo "Debug Login Process\n";
echo "==================\n\n";

// Test CSRF token
echo "1. Testing CSRF token...\n";
$token = Security::generateCSRFToken();
echo "Generated token: $token\n";

if (Security::verifyCSRFToken($token)) {
    echo "✓ CSRF verification works\n";
} else {
    echo "✗ CSRF verification failed\n";
}

// Test database connection
echo "\n2. Testing database connection...\n";
try {
    $db = Database::getInstance();
    echo "✓ Database connected\n";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test user lookup
echo "\n3. Testing user lookup...\n";
$username = 'admin';
$user = $db->fetchOne(
    'SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1',
    [$username, $username]
);

if ($user) {
    echo "✓ User found: " . $user['username'] . "\n";
    echo "  Role: " . $user['role'] . "\n";
    echo "  Active: " . ($user['is_active'] ? 'Yes' : 'No') . "\n";
} else {
    echo "✗ User not found\n";
    exit(1);
}

// Test password verification
echo "\n4. Testing password verification...\n";
$password = 'admin123';
if (Security::verifyPassword($password, $user['password_hash'])) {
    echo "✓ Password verification successful\n";
} else {
    echo "✗ Password verification failed\n";
    echo "  Hash: " . $user['password_hash'] . "\n";
}

// Test session login
echo "\n5. Testing session login...\n";
try {
    Session::login($user);
    echo "✓ Session login successful\n";
    
    $currentUser = Session::getUser();
    if ($currentUser) {
        echo "✓ Current user: " . $currentUser['username'] . "\n";
    } else {
        echo "✗ No current user in session\n";
    }
} catch (Exception $e) {
    echo "✗ Session login failed: " . $e->getMessage() . "\n";
}

echo "\nDebug completed!\n";
?>
