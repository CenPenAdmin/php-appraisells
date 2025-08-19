<?php
// Simple database setup script for Appraisells
require_once 'config.php';

echo "Setting up Appraisells database...\n";

try {
    // Try to connect to MySQL without database first
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "✅ Database created/verified: " . DB_NAME . "\n";
    
    // Switch to the database
    $pdo->exec("USE " . DB_NAME);
    
    // Read and execute schema
    $schema = file_get_contents('database_schema.sql');
    if ($schema) {
        $pdo->exec($schema);
        echo "✅ Database schema applied successfully\n";
    } else {
        echo "❌ Could not read database_schema.sql\n";
    }
    
    echo "🎉 Database setup complete!\n";
    echo "🔗 Your ngrok tunnel: " . APP_URL . "\n";
    echo "📱 Pi Browser URL: https://cenpenadmin.github.io/php-appraisells/\n";
    
} catch (PDOException $e) {
    echo "❌ Database setup failed: " . $e->getMessage() . "\n";
    echo "💡 Make sure XAMPP MySQL is running\n";
}
?>
