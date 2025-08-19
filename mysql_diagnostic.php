<?php
// MySQL Diagnostic Script for XAMPP
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 XAMPP MySQL Diagnostic</h1>";
echo "<style>body{font-family:Arial;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>";

// Check if MySQL extension is loaded
echo "<h2>PHP MySQL Extensions</h2>";
if (extension_loaded('pdo_mysql')) {
    echo "<p class='success'>✅ PDO MySQL extension is loaded</p>";
} else {
    echo "<p class='error'>❌ PDO MySQL extension is NOT loaded</p>";
}

if (extension_loaded('mysqli')) {
    echo "<p class='success'>✅ MySQLi extension is loaded</p>";
} else {
    echo "<p class='error'>❌ MySQLi extension is NOT loaded</p>";
}

// Test different connection methods
echo "<h2>Connection Tests</h2>";

// Test 1: PDO with default settings
echo "<h3>Test 1: PDO Connection (port 3306)</h3>";
try {
    $pdo = new PDO("mysql:host=localhost;port=3306", "root", "");
    echo "<p class='success'>✅ PDO connection successful on port 3306</p>";
    $pdo = null;
} catch (PDOException $e) {
    echo "<p class='error'>❌ PDO connection failed: " . $e->getMessage() . "</p>";
}

// Test 2: PDO with port 3307
echo "<h3>Test 2: PDO Connection (port 3307)</h3>";
try {
    $pdo = new PDO("mysql:host=localhost;port=3307", "root", "");
    echo "<p class='success'>✅ PDO connection successful on port 3307</p>";
    $pdo = null;
} catch (PDOException $e) {
    echo "<p class='error'>❌ PDO connection failed: " . $e->getMessage() . "</p>";
}

// Test 3: MySQLi
echo "<h3>Test 3: MySQLi Connection</h3>";
$mysqli = @new mysqli("localhost", "root", "", "", 3306);
if ($mysqli->connect_error) {
    echo "<p class='error'>❌ MySQLi connection failed: " . $mysqli->connect_error . "</p>";
} else {
    echo "<p class='success'>✅ MySQLi connection successful</p>";
    $mysqli->close();
}

// Check for database
echo "<h2>Database Check</h2>";
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p class='success'>✅ Available databases:</p>";
    echo "<ul>";
    foreach ($databases as $db) {
        echo "<li>$db</li>";
    }
    echo "</ul>";
    
    if (in_array('appraisells_db', $databases)) {
        echo "<p class='success'>✅ appraisells_db database exists</p>";
    } else {
        echo "<p class='warning'>⚠️ appraisells_db database does not exist</p>";
        echo "<p><a href='create_database.php'>🔧 Click here to create database</a></p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Cannot check databases: " . $e->getMessage() . "</p>";
}

// System Information
echo "<h2>System Information</h2>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>OS:</strong> " . PHP_OS . "</p>";
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// XAMPP Paths
echo "<h2>XAMPP Log Locations</h2>";
echo "<p><strong>Apache Error Log:</strong> C:\\xampp\\apache\\logs\\error.log</p>";
echo "<p><strong>Apache Access Log:</strong> C:\\xampp\\apache\\logs\\access.log</p>";
echo "<p><strong>MySQL Error Log:</strong> C:\\xampp\\mysql\\data\\mysql_error.log</p>";
echo "<p><strong>PHP Error Log:</strong> C:\\xampp\\php\\logs\\php_error_log</p>";

echo "<h2>Quick Actions</h2>";
echo "<p><a href='create_database.php'>🗄️ Create Database</a></p>";
echo "<p><a href='test.html'>🧪 Test Pi Browser Integration</a></p>";
echo "<p><a href='health'>🏥 Health Check</a></p>";

?>
