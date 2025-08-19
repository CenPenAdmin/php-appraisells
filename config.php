<?php
// Configuration for Appraisells PHP Application
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, ngrok-skip-browser-warning');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Load environment variables from .env file if it exists
function loadEnvFile($filePath = '.env') {
    if (file_exists($filePath)) {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue; // Skip comments
            }
            
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Load .env file
loadEnvFile(__DIR__ . '/.env');

// Database Configuration - Use environment variables or fallback to defaults
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'appraisells_db');
define('DB_USER', $_ENV['DB_USER'] ?? 'your_db_username');
define('DB_PASS', $_ENV['DB_PASS'] ?? 'your_db_password');

// Pi Network Configuration
define('PI_API_URL', 'https://api.minepi.com');
define('PI_API_KEY', $_ENV['PI_API_KEY'] ?? ''); // Get from environment variable

// Application Configuration
define('APP_URL', $_ENV['APP_URL'] ?? 'https://4943480ece59.ngrok-free.app'); // Updated with your ngrok tunnel
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('SUBSCRIPTION_DURATION_DAYS', 30);
define('AUCTION_PAYMENT_TIMEOUT_HOURS', 24);

// Debug mode
define('DEBUG_MODE', filter_var($_ENV['DEBUG_MODE'] ?? 'false', FILTER_VALIDATE_BOOLEAN));

// Error reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Database connection function
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]));
            } else {
                error_log("Database connection failed: " . $e->getMessage());
                die(json_encode(['success' => false, 'error' => 'Database connection failed']));
            }
        }
    }
    
    return $pdo;
}

// Utility function to respond with JSON
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

// Utility function to validate required fields
function validateRequired($data, $requiredFields) {
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            jsonResponse([
                'success' => false,
                'error' => "Required field '$field' is missing"
            ], 400);
        }
    }
}

// Utility function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Utility function to log activities
function logActivity($username, $userUid, $activityType, $details = []) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            INSERT INTO user_activities (username, user_uid, activity_type, details, timestamp, ip_address) 
            VALUES (?, ?, ?, ?, NOW(), ?)
        ");
        
        $stmt->execute([
            $username,
            $userUid,
            $activityType,
            json_encode($details),
            $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
    } catch (Exception $e) {
        // Log error but don't fail the main operation
        error_log("Activity logging failed: " . $e->getMessage());
    }
}

// Check if Pi API key is configured
if (empty(PI_API_KEY)) {
    if (DEBUG_MODE) {
        error_log("WARNING: PI_API_KEY not configured. Please set it in .env file or environment variable.");
    }
}
?>
