<?php
/**
 * Test Login Functionality
 */

require_once 'config/database.php';
require_once 'vendor/autoload.php';

use App\Core\Security;

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
    
    echo "Testing login functionality...\n\n";
    
    // Test admin login
    $username = 'admin';
    $password = 'admin123';
    
    echo "Testing admin login:\n";
    echo "Username: $username\n";
    echo "Password: $password\n";
    
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND is_active = 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✓ User found in database\n";
        
        if (Security::verifyPassword($password, $user['password_hash'])) {
            echo "✓ Password verification: SUCCESS\n";
            echo "✓ Login should work!\n";
        } else {
            echo "✗ Password verification: FAILED\n";
        }
    } else {
        echo "✗ User not found\n";
    }
    
    echo "\nTesting john.doe login:\n";
    $username = 'john.doe';
    $password = 'password123';
    
    echo "Username: $username\n";
    echo "Password: $password\n";
    
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND is_active = 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✓ User found in database\n";
        
        if (Security::verifyPassword($password, $user['password_hash'])) {
            echo "✓ Password verification: SUCCESS\n";
            echo "✓ Login should work!\n";
        } else {
            echo "✗ Password verification: FAILED\n";
        }
    } else {
        echo "✗ User not found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
