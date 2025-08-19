<?php
require_once 'config.php';

// Legacy file for saving user data
// Most functionality has been moved to api.php

header('Content-Type: application/json');

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['username'])) {
    $username = sanitizeInput($data['username']);
    $userUid = sanitizeInput($data['userUid'] ?? '');
    
    try {
        $pdo = getDBConnection();
        
        // Create or update user profile
        $stmt = $pdo->prepare("
            INSERT INTO user_profiles (username, user_uid, last_seen) 
            VALUES (?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE last_seen = NOW()
        ");
        
        $stmt->execute([$username, $userUid]);
        
        // Log the activity
        logActivity($username, $userUid, 'user_save', [
            'source' => 'save_user_php',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
        
        jsonResponse([
            'success' => true,
            'message' => "Welcome, {$username}! User data saved successfully.",
            'username' => $username
        ]);
        
    } catch (Exception $e) {
        error_log("Save user error: " . $e->getMessage());
        jsonResponse(['success' => false, 'error' => 'Failed to save user data'], 500);
    }
} else {
    jsonResponse(['success' => false, 'error' => 'No username received'], 400);
}
?>