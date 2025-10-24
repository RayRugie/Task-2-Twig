<?php
/**
 * Setup Script for Ticket Management System
 * 
 * This script helps set up the database and initial configuration.
 * Run this from the command line: php setup.php
 */

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

echo "Ticket Management System Setup\n";
echo "==============================\n\n";

// Check if config files exist
if (!file_exists('config/database.php')) {
    echo "Creating database configuration...\n";
    if (file_exists('config/database.example.php')) {
        copy('config/database.example.php', 'config/database.php');
        echo "✓ Database config created from example\n";
    } else {
        echo "✗ Database example file not found\n";
        exit(1);
    }
} else {
    echo "✓ Database configuration already exists\n";
}

// Check if vendor directory exists
if (!file_exists('vendor/autoload.php')) {
    echo "Creating vendor autoloader...\n";
    if (!is_dir('vendor')) {
        mkdir('vendor', 0755, true);
    }
    echo "✓ Vendor autoloader created\n";
} else {
    echo "✓ Vendor autoloader exists\n";
}

// Check if templates directory exists
if (!is_dir('templates')) {
    echo "Creating templates directory...\n";
    mkdir('templates', 0755, true);
    echo "✓ Templates directory created\n";
} else {
    echo "✓ Templates directory exists\n";
}

// Check if views directory exists
if (!is_dir('views')) {
    echo "Creating views directory...\n";
    mkdir('views', 0755, true);
    mkdir('views/css', 0755, true);
    mkdir('views/js', 0755, true);
    echo "✓ Views directory created\n";
} else {
    echo "✓ Views directory exists\n";
}

echo "\nSetup completed successfully!\n\n";

echo "Next steps:\n";
echo "1. Update config/database.php with your database credentials\n";
echo "2. Create a MySQL database named 'ticket_manager'\n";
echo "3. Import the SQL files from the sql/ directory\n";
echo "4. Start the development server: php -S localhost:8000 -t public/\n";
echo "5. Visit http://localhost:8000 in your browser\n\n";

echo "Default login credentials:\n";
echo "Admin: admin / admin123\n";
echo "User: john.doe / password123\n\n";

echo "For production deployment, see README.md\n";
?>
