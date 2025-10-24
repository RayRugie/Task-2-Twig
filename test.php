<?php
/**
 * Simple Test Script
 * 
 * This script tests if the application is working correctly.
 * Run: php test.php
 */

echo "Testing Ticket Management System\n";
echo "===============================\n\n";

// Test 1: Check if autoloader works
echo "1. Testing autoloader...\n";
try {
    require_once 'vendor/autoload.php';
    echo "✓ Autoloader loaded successfully\n";
} catch (Exception $e) {
    echo "✗ Autoloader failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check if Twig classes exist
echo "2. Testing Twig classes...\n";
if (class_exists('Twig\Environment')) {
    echo "✓ Twig\\Environment class exists\n";
} else {
    echo "✗ Twig\\Environment class not found\n";
}

if (class_exists('Twig\Loader\FilesystemLoader')) {
    echo "✓ Twig\\Loader\\FilesystemLoader class exists\n";
} else {
    echo "✗ Twig\\Loader\\FilesystemLoader class not found\n";
}

// Test 3: Check if our core classes exist
echo "3. Testing core classes...\n";
$coreClasses = [
    'App\Core\Application',
    'App\Core\Database',
    'App\Core\Security',
    'App\Core\Session',
    'App\Core\SimpleTemplateEngine'
];

foreach ($coreClasses as $class) {
    if (class_exists($class)) {
        echo "✓ $class exists\n";
    } else {
        echo "✗ $class not found\n";
    }
}

// Test 4: Check if controllers exist
echo "4. Testing controllers...\n";
$controllers = [
    'App\Controllers\HomeController',
    'App\Controllers\AuthController',
    'App\Controllers\TicketController',
    'App\Controllers\DashboardController'
];

foreach ($controllers as $controller) {
    if (class_exists($controller)) {
        echo "✓ $controller exists\n";
    } else {
        echo "✗ $controller not found\n";
    }
}

// Test 5: Check if templates exist
echo "5. Testing templates...\n";
$templates = [
    'templates/base.php',
    'templates/home/index.php',
    'templates/auth/login.php'
];

foreach ($templates as $template) {
    if (file_exists($template)) {
        echo "✓ $template exists\n";
    } else {
        echo "✗ $template not found\n";
    }
}

// Test 6: Check if config exists
echo "6. Testing configuration...\n";
if (file_exists('config/app.php')) {
    echo "✓ config/app.php exists\n";
} else {
    echo "✗ config/app.php not found\n";
}

if (file_exists('config/database.php')) {
    echo "✓ config/database.php exists\n";
} else {
    echo "✗ config/database.php not found\n";
}

echo "\nTest completed!\n";
echo "If all tests passed, you can now visit http://localhost:8000\n";
echo "Default login: admin / admin123\n";
?>
