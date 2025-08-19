<?php
// Simple Database Creation Script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🗄️ Database Creation</h1>";
echo "<style>body{font-family:Arial;} .success{color:green;} .error{color:red;}</style>";

try {
    // Try to connect to MySQL without specifying a database
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p class='success'>✅ Connected to MySQL server</p>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS appraisells_db");
    echo "<p class='success'>✅ Database 'appraisells_db' created/verified</p>";
    
    // Switch to the database
    $pdo->exec("USE appraisells_db");
    
    // Create basic tables
    $schema = "
    CREATE TABLE IF NOT EXISTS user_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) UNIQUE NOT NULL,
        user_uid VARCHAR(255) UNIQUE NOT NULL,
        wallet VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    
    CREATE TABLE IF NOT EXISTS subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        end_date TIMESTAMP,
        status ENUM('active', 'expired', 'cancelled') DEFAULT 'active'
    );
    
    CREATE TABLE IF NOT EXISTS user_activities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        user_uid VARCHAR(255),
        activity_type VARCHAR(100) NOT NULL,
        details JSON,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ip_address VARCHAR(45)
    );
    ";
    
    $pdo->exec($schema);
    echo "<p class='success'>✅ Basic tables created successfully</p>";
    
    echo "<h2>🎉 Database Setup Complete!</h2>";
    echo "<p><a href='test.html'>🧪 Test Your App</a></p>";
    echo "<p><a href='index.html'>🏠 Go to Main App</a></p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Database creation failed: " . $e->getMessage() . "</p>";
    
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "<h2>🔧 MySQL Connection Issues</h2>";
        echo "<p>MySQL server is not running. Please:</p>";
        echo "<ol>";
        echo "<li>Open XAMPP Control Panel as Administrator</li>";
        echo "<li>Click 'Start' next to MySQL</li>";
        echo "<li>If it fails, check the logs at: <code>C:\\xampp\\mysql\\data\\mysql_error.log</code></li>";
        echo "<li>Try running XAMPP as Administrator</li>";
        echo "</ol>";
    }
    
    echo "<p><a href='mysql_diagnostic.php'>🔍 Run Full Diagnostic</a></p>";
}
?>
