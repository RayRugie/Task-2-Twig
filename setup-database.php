<?php
/**
 * Database Setup Script for Ticket Management System
 * 
 * This script helps set up the database tables and sample data.
 * Run this from the command line: php setup-database.php
 */

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

echo "Database Setup for Ticket Management System\n";
echo "==========================================\n\n";

// Load configuration
require_once 'config/database.php';

try {
    // Create database connection
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
    
    echo "✓ Connected to MySQL server\n";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database '" . DB_NAME . "' created or already exists\n";
    
    // Connect to the specific database
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
    
    // Read and execute SQL files
    $sqlFiles = [
        'sql/001_create_users_table.sql',
        'sql/002_create_tickets_table.sql',
        'sql/003_sample_data.sql'
    ];
    
    foreach ($sqlFiles as $sqlFile) {
        if (file_exists($sqlFile)) {
            echo "Executing $sqlFile...\n";
            $sql = file_get_contents($sqlFile);
            
            // Split SQL into individual statements
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $pdo->exec($statement);
                }
            }
            
            echo "✓ $sqlFile executed successfully\n";
        } else {
            echo "✗ $sqlFile not found\n";
        }
    }
    
    echo "\n✓ Database setup completed successfully!\n\n";
    
    echo "You can now:\n";
    echo "1. Start the development server: php -S localhost:8000 -t public/\n";
    echo "2. Visit http://localhost:8000 in your browser\n";
    echo "3. Login with admin/admin123 or john.doe/password123\n\n";
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    echo "\nPlease check your database configuration in config/database.php\n";
    echo "Make sure MySQL is running and the credentials are correct.\n";
    exit(1);
}
?>
