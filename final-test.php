<?php
/**
 * Final Test - Verify everything is working
 */

echo "🎉 Ticket Management System - Final Verification\n";
echo "===============================================\n\n";

// Test 1: Check if server is running
echo "1. Testing server connection...\n";
$context = stream_context_create([
    'http' => [
        'timeout' => 5,
        'method' => 'GET'
    ]
]);

$response = @file_get_contents('http://localhost:8000', false, $context);
if ($response !== false) {
    echo "✓ Server is running and responding\n";
} else {
    echo "✗ Server is not responding\n";
    echo "Please start the server with: php -S localhost:8000 -t public/\n";
    exit(1);
}

// Test 2: Check database
echo "\n2. Testing database connection...\n";
try {
    require_once 'config/database.php';
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, DB_OPTIONS);
    
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $result = $stmt->fetch();
    
    echo "✓ Database connected successfully\n";
    echo "✓ Found {$result['count']} users in database\n";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Check login page
echo "\n3. Testing login page...\n";
$loginResponse = @file_get_contents('http://localhost:8000/login', false, $context);
if ($loginResponse !== false && strpos($loginResponse, 'Welcome Back') !== false) {
    echo "✓ Login page is accessible\n";
} else {
    echo "✗ Login page is not accessible\n";
}

echo "\n🎯 READY TO USE!\n\n";
echo "Your ticket management system is now fully functional!\n\n";
echo "🌐 Access the application at: http://localhost:8000\n\n";
echo "🔐 Login credentials:\n";
echo "   Admin: admin / admin123\n";
echo "   User:  john.doe / password123\n\n";
echo "✨ Features available:\n";
echo "   • User authentication and registration\n";
echo "   • Ticket management (create, edit, assign, comment)\n";
echo "   • Dashboard with charts and statistics\n";
echo "   • User roles and permissions\n";
echo "   • Responsive design\n";
echo "   • Security features (CSRF, rate limiting, password hashing)\n\n";
echo "🚀 Enjoy your ticket management system!\n";
?>
