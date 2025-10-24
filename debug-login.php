<?php
/**
 * Debug Login Script
 */

require_once 'config/database.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
    
    echo "Checking admin user...\n";
    
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute(['admin']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✓ Admin user found:\n";
        echo "  Username: " . $user['username'] . "\n";
        echo "  Email: " . $user['email'] . "\n";
        echo "  Role: " . $user['role'] . "\n";
        echo "  Is Active: " . ($user['is_active'] ? 'Yes' : 'No') . "\n";
        echo "  Password Hash: " . $user['password_hash'] . "\n";
        
        // Test password verification
        $testPassword = 'admin123';
        $hash = $user['password_hash'];
        
        echo "\nTesting password verification:\n";
        echo "  Test password: " . $testPassword . "\n";
        echo "  Hash: " . $hash . "\n";
        
        if (password_verify($testPassword, $hash)) {
            echo "  ✓ Password verification: SUCCESS\n";
        } else {
            echo "  ✗ Password verification: FAILED\n";
            
            // Try to create a new hash
            echo "\nCreating new password hash...\n";
            $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
            echo "  New hash: " . $newHash . "\n";
            
            // Update the database
            $updateStmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE username = ?');
            $updateStmt->execute([$newHash, 'admin']);
            echo "  ✓ Password hash updated in database\n";
        }
        
    } else {
        echo "✗ Admin user not found!\n";
        
        // Create admin user
        echo "Creating admin user...\n";
        $password = 'admin123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $insertStmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, ?)');
        $insertStmt->execute(['admin', 'admin@ticketmanager.local', $hash, 'Admin', 'User', 'admin']);
        
        echo "✓ Admin user created with password: admin123\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
