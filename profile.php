<?php
require_once 'config.php';

// This file handles user profile operations
// Now most functionality is in api.php, but this can be used for additional profile features

header('Content-Type: application/json');

// Get the request method and input data
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = getDBConnection();
    
    switch ($method) {
        case 'POST':
            // Handle user profile creation/update
            if (isset($input['username'])) {
                $username = sanitizeInput($input['username']);
                $userUid = sanitizeInput($input['userUid'] ?? '');
                $email = sanitizeInput($input['email'] ?? '');
                $preferences = json_encode($input['preferences'] ?? []);
                
                // Update or create user profile with additional data
                $stmt = $pdo->prepare("
                    INSERT INTO user_profiles (username, user_uid, profile_data, last_seen) 
                    VALUES (?, ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE 
                    profile_data = ?, 
                    last_seen = NOW()
                ");
                
                $profileData = json_encode([
                    'email' => $email,
                    'preferences' => $input['preferences'] ?? [],
                    'updated_at' => date('c')
                ]);
                
                $stmt->execute([$username, $userUid, $profileData, $profileData]);
                
                // Log the profile update
                logActivity($username, $userUid, 'profile_updated', [
                    'email' => $email,
                    'has_preferences' => !empty($input['preferences'])
                ]);
                
                jsonResponse([
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'username' => $username
                ]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Username is required'], 400);
            }
            break;
            
        case 'GET':
            // Handle profile retrieval
            $username = $_GET['username'] ?? '';
            
            if (empty($username)) {
                jsonResponse(['success' => false, 'error' => 'Username parameter is required'], 400);
            }
            
            $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE username = ?");
            $stmt->execute([$username]);
            $profile = $stmt->fetch();
            
            if ($profile) {
                // Remove sensitive information
                unset($profile['id']);
                
                jsonResponse([
                    'success' => true,
                    'profile' => $profile
                ]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Profile not found'], 404);
            }
            break;
            
        default:
            jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
    }
    
} catch (Exception $e) {
    error_log("Profile.php error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Internal server error'], 500);
}
?>
