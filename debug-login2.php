<?php
/**
 * Debug Login Script - Fix all users
 */

require_once 'config/database.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
    
    echo "Fixing all user passwords...\n\n";
    
    // Fix admin user
    $adminPassword = 'admin123';
    $adminHash = password_hash($adminPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE username = ?');
    $stmt->execute([$adminHash, 'admin']);
    echo "✓ Admin user password updated: admin / admin123\n";
    
    // Fix john.doe user
    $johnPassword = 'password123';
    $johnHash = password_hash($johnPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE username = ?');
    $stmt->execute([$johnHash, 'john.doe']);
    echo "✓ John.doe user password updated: john.doe / password123\n";
    
    // Fix jane.smith user
    $janePassword = 'password123';
    $janeHash = password_hash($janePassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE username = ?');
    $stmt->execute([$janeHash, 'jane.smith']);
    echo "✓ Jane.smith user password updated: jane.smith / password123\n";
    
    // Fix mike.wilson user
    $mikePassword = 'password123';
    $mikeHash = password_hash($mikePassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE username = ?');
    $stmt->execute([$mikeHash, 'mike.wilson']);
    echo "✓ Mike.wilson user password updated: mike.wilson / password123\n";
    
    echo "\nAll user passwords have been fixed!\n";
    echo "You can now login with:\n";
    echo "- admin / admin123 (admin user)\n";
    echo "- john.doe / password123 (regular user)\n";
    echo "- jane.smith / password123 (regular user)\n";
    echo "- mike.wilson / password123 (regular user)\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
