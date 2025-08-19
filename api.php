<?php
require_once 'config.php';

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/api', '', $path); // Remove /api prefix if present

// Route the request
switch ($method) {
    case 'GET':
        handleGetRequest($path);
        break;
    case 'POST':
        handlePostRequest($path);
        break;
    case 'PUT':
        handlePutRequest($path);
        break;
    case 'DELETE':
        handleDeleteRequest($path);
        break;
    default:
        jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

function handleGetRequest($path) {
    switch ($path) {
        case '/':
        case '/health':
            healthCheck();
            break;
        case '/auction-status':
            getAuctionStatus();
            break;
        case '/payments':
            getPayments();
            break;
        case '/user-profiles':
            getUserProfiles();
            break;
        case '/user-activities':
            getUserActivities();
            break;
        case '/sessions':
            getSessions();
            break;
        case '/analytics':
            getAnalytics();
            break;
        case '/subscriptions':
            getSubscriptions();
            break;
        case '/auction-highest-bids':
            getAuctionHighestBids();
            break;
        default:
            // Handle dynamic routes
            if (preg_match('/^\/subscription-status\/(.+)$/', $path, $matches)) {
                getSubscriptionStatus($matches[1]);
            } elseif (preg_match('/^\/auction-bids\/(.+)$/', $path, $matches)) {
                getAuctionBids($matches[1]);
            } elseif (preg_match('/^\/user-bid-status\/(.+)$/', $path, $matches)) {
                getUserBidStatus($matches[1]);
            } elseif (preg_match('/^\/user-wins\/(.+)$/', $path, $matches)) {
                getUserWins($matches[1]);
            } elseif (preg_match('/^\/calculate-winners\/(.+)$/', $path, $matches)) {
                calculateWinners($matches[1]);
            } elseif (preg_match('/^\/user\/(.+)$/', $path, $matches)) {
                getUserDetails($matches[1]);
            } elseif (preg_match('/^\/digital-art\/(.+)$/', $path, $matches)) {
                getDigitalArt($matches[1]);
            } elseif (preg_match('/^\/debug\/auction-data$/', $path)) {
                getDebugAuctionData();
            } else {
                jsonResponse(['success' => false, 'error' => 'Endpoint not found'], 404);
            }
    }
}

function handlePostRequest($path) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($path) {
        case '/log-authentication':
            logAuthentication($input);
            break;
        case '/create-user-profile':
            createUserProfile($input);
            break;
        case '/approve-payment':
            approvePayment($input);
            break;
        case '/complete-payment':
            completePayment($input);
            break;
        case '/auction-bid':
        case '/place-auction-bid':
            submitAuctionBid($input);
            break;
        case '/remove-auction-bid':
            removeAuctionBid($input);
            break;
        case '/close-auction':
            closeAuction($input);
            break;
        case '/test-create-subscription':
            createTestSubscription($input);
            break;
        case '/approve-payment':
            approvePayment($input);
            break;
        case '/complete-payment':
            completePayment($input);
            break;
        case '/approve-subscription-payment':
            approveSubscriptionPayment($input);
            break;
        case '/complete-subscription-payment':
            completeSubscriptionPayment($input);
            break;
        default:
            jsonResponse(['success' => false, 'error' => 'Endpoint not found'], 404);
    }
}

function handlePutRequest($path) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (preg_match('/^\/subscriptions\/(.+)$/', $path, $matches)) {
        updateSubscription($matches[1], $input);
    } else {
        jsonResponse(['success' => false, 'error' => 'Endpoint not found'], 404);
    }
}

function handleDeleteRequest($path) {
    jsonResponse(['success' => false, 'error' => 'Delete operations not implemented'], 501);
}

// Health check endpoint
function healthCheck() {
    $pdo = getDBConnection();
    
    jsonResponse([
        'status' => 'Server is running',
        'timestamp' => date('c'),
        'piApiKeySet' => !empty(PI_API_KEY),
        'databaseConnected' => $pdo !== null,
        'php_version' => PHP_VERSION
    ]);
}

// Authentication logging
function logAuthentication($data) {
    validateRequired($data, ['username', 'userUid', 'authSuccess']);
    
    $username = sanitizeInput($data['username']);
    $userUid = sanitizeInput($data['userUid']);
    $authSuccess = (bool)$data['authSuccess'];
    
    try {
        $pdo = getDBConnection();
        
        // Update user profile with last seen
        updateUserProfile($username, $userUid);
        
        // Log the authentication activity
        logActivity($username, $userUid, 'authentication', [
            'authSuccess' => $authSuccess,
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'origin' => $_SERVER['HTTP_ORIGIN'] ?? ''
        ]);
        
        jsonResponse(['success' => true, 'message' => 'Authentication logged']);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to log authentication'], 500);
    }
}

// Update or create user profile
function updateUserProfile($username, $userUid, $wallet = null) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        INSERT INTO user_profiles (username, user_uid, wallet_address, last_seen) 
        VALUES (?, ?, ?, NOW()) 
        ON DUPLICATE KEY UPDATE 
        last_seen = NOW(), 
        wallet_address = COALESCE(?, wallet_address)
    ");
    
    $stmt->execute([$username, $userUid, $wallet, $wallet]);
}

// Create user profile
function createUserProfile($data) {
    validateRequired($data, ['username']);
    
    $username = sanitizeInput($data['username']);
    $userUid = sanitizeInput($data['userUid'] ?? '');
    $wallet = sanitizeInput($data['wallet'] ?? '');
    
    try {
        updateUserProfile($username, $userUid, $wallet);
        
        logActivity($username, $userUid, 'profile_created', [
            'wallet' => $wallet,
            'source' => 'pi_network'
        ]);
        
        jsonResponse([
            'success' => true,
            'message' => 'User profile created/updated successfully',
            'username' => $username
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to create user profile'], 500);
    }
}

// Get subscription status
function getSubscriptionStatus($username) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM user_subscriptions 
            WHERE username = ? AND is_active = 1 
            ORDER BY end_date DESC 
            LIMIT 1
        ");
        $stmt->execute([$username]);
        $subscription = $stmt->fetch();
        
        if ($subscription) {
            $endDate = new DateTime($subscription['end_date']);
            $now = new DateTime();
            $daysRemaining = max(0, $endDate->diff($now)->days);
            $isExpired = $endDate <= $now;
            
            $subscription['daysRemaining'] = $daysRemaining;
            $subscription['isExpired'] = $isExpired;
            $subscription['status'] = $isExpired ? 'expired' : ($subscription['is_active'] ? 'active' : 'inactive');
            
            jsonResponse([
                'success' => true,
                'hasActiveSubscription' => !$isExpired,
                'subscription' => $subscription
            ]);
        } else {
            jsonResponse([
                'success' => true,
                'hasActiveSubscription' => false,
                'subscription' => null
            ]);
        }
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to fetch subscription'], 500);
    }
}

// Payment approval (Pi Network integration)
function approvePayment($data) {
    validateRequired($data, ['paymentId']);
    
    $paymentId = sanitizeInput($data['paymentId']);
    
    if (empty(PI_API_KEY)) {
        jsonResponse(['success' => false, 'error' => 'Pi API key not configured'], 500);
    }
    
    try {
        // Call Pi Network API to approve payment
        $url = PI_API_URL . "/v2/payments/{$paymentId}/approve";
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Authorization: Key ' . PI_API_KEY,
                    'Content-Type: application/json'
                ]
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);
        
        if ($response !== false) {
            $result = json_decode($response, true);
            
            // Store payment in database
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("
                INSERT INTO payments (payment_id, status, created_at) 
                VALUES (?, 'approved', NOW()) 
                ON DUPLICATE KEY UPDATE status = 'approved', updated_at = NOW()
            ");
            $stmt->execute([$paymentId]);
            
            jsonResponse(['success' => true, 'message' => 'Payment approved']);
        } else {
            jsonResponse(['success' => false, 'error' => 'Payment approval failed'], 400);
        }
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Internal server error'], 500);
    }
}

// Payment completion
function completePayment($data) {
    validateRequired($data, ['paymentId', 'txId']);
    
    $paymentId = sanitizeInput($data['paymentId']);
    $txId = sanitizeInput($data['txId']);
    
    try {
        // Call Pi Network API to complete payment
        $url = PI_API_URL . "/v2/payments/{$paymentId}/complete";
        
        $postData = json_encode(['txid' => $txId]);
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Authorization: Key ' . PI_API_KEY,
                    'Content-Type: application/json'
                ],
                'content' => $postData
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);
        
        if ($response !== false) {
            $result = json_decode($response, true);
            
            // Update payment in database
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("
                UPDATE payments 
                SET status = 'completed', tx_id = ?, updated_at = NOW() 
                WHERE payment_id = ?
            ");
            $stmt->execute([$txId, $paymentId]);
            
            // Get payment details to determine type
            $stmt = $pdo->prepare("SELECT * FROM payments WHERE payment_id = ?");
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch();
            
            if ($payment && $payment['payment_type'] === 'subscription') {
                // Create subscription
                createSubscriptionFromPayment($payment);
            }
            
            jsonResponse(['success' => true, 'message' => 'Payment completed']);
        } else {
            jsonResponse(['success' => false, 'error' => 'Payment completion failed'], 400);
        }
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Internal server error'], 500);
    }
}

// Create subscription from completed payment
function createSubscriptionFromPayment($payment) {
    $pdo = getDBConnection();
    
    $startDate = date('Y-m-d');
    $endDate = date('Y-m-d', strtotime('+' . SUBSCRIPTION_DURATION_DAYS . ' days'));
    
    $stmt = $pdo->prepare("
        INSERT INTO user_subscriptions (username, user_uid, payment_id, start_date, end_date, is_active, status) 
        VALUES (?, ?, ?, ?, ?, 1, 'active')
    ");
    
    $stmt->execute([
        $payment['username'],
        $payment['user_uid'],
        $payment['payment_id'],
        $startDate,
        $endDate
    ]);
    
    // Update user profile subscription status
    $stmt = $pdo->prepare("UPDATE user_profiles SET subscription_status = 'active' WHERE username = ?");
    $stmt->execute([$payment['username']]);
}

// Get payments
function getPayments() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT * FROM payments ORDER BY created_at DESC");
        $payments = $stmt->fetchAll();
        
        jsonResponse([
            'success' => true,
            'count' => count($payments),
            'payments' => $payments
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to fetch payments'], 500);
    }
}

// Get user profiles
function getUserProfiles() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT * FROM user_profiles ORDER BY last_seen DESC");
        $profiles = $stmt->fetchAll();
        
        jsonResponse([
            'success' => true,
            'count' => count($profiles),
            'profiles' => $profiles
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to fetch user profiles'], 500);
    }
}

// Get user activities
function getUserActivities() {
    $username = $_GET['username'] ?? null;
    $limit = min(100, (int)($_GET['limit'] ?? 50));
    
    try {
        $pdo = getDBConnection();
        
        if ($username) {
            $stmt = $pdo->prepare("
                SELECT * FROM user_activities 
                WHERE username = ? 
                ORDER BY timestamp DESC 
                LIMIT ?
            ");
            $stmt->execute([$username, $limit]);
        } else {
            $stmt = $pdo->prepare("
                SELECT * FROM user_activities 
                ORDER BY timestamp DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
        }
        
        $activities = $stmt->fetchAll();
        
        jsonResponse([
            'success' => true,
            'count' => count($activities),
            'username' => $username ?: 'all users',
            'activities' => $activities
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to fetch user activities'], 500);
    }
}

// Get sessions
function getSessions() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT * FROM sessions ORDER BY timestamp DESC LIMIT 50");
        $sessions = $stmt->fetchAll();
        
        jsonResponse([
            'success' => true,
            'pageVisits' => [
                'count' => count($sessions),
                'data' => $sessions
            ]
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to fetch sessions'], 500);
    }
}

// Get analytics
function getAnalytics() {
    try {
        $pdo = getDBConnection();
        
        // Get counts
        $paymentCount = $pdo->query("SELECT COUNT(*) FROM payments")->fetchColumn();
        $userCount = $pdo->query("SELECT COUNT(*) FROM user_profiles")->fetchColumn();
        $sessionCount = $pdo->query("SELECT COUNT(*) FROM sessions")->fetchColumn();
        
        jsonResponse([
            'success' => true,
            'overview' => [
                'totalPayments' => $paymentCount,
                'totalUsers' => $userCount,
                'totalSessions' => $sessionCount
            ]
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to fetch analytics'], 500);
    }
}

// Get subscriptions
function getSubscriptions() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT * FROM user_subscriptions ORDER BY created_at DESC");
        $subscriptions = $stmt->fetchAll();
        
        $activeCount = 0;
        foreach ($subscriptions as $sub) {
            if ($sub['is_active'] && strtotime($sub['end_date']) > time()) {
                $activeCount++;
            }
        }
        
        jsonResponse([
            'success' => true,
            'total' => count($subscriptions),
            'active' => $activeCount,
            'subscriptions' => $subscriptions
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to fetch subscriptions'], 500);
    }
}

// Update subscription
function updateSubscription($subscriptionId, $data) {
    try {
        $pdo = getDBConnection();
        
        $updates = [];
        $params = [];
        
        if (isset($data['isActive'])) {
            $updates[] = 'is_active = ?';
            $params[] = (bool)$data['isActive'];
        }
        
        if (isset($data['endDate'])) {
            $updates[] = 'end_date = ?';
            $params[] = $data['endDate'];
        }
        
        if (isset($data['notes'])) {
            $updates[] = 'notes = ?';
            $params[] = $data['notes'];
        }
        
        if (!empty($updates)) {
            $updates[] = 'updated_at = NOW()';
            $params[] = $subscriptionId;
            
            $sql = "UPDATE user_subscriptions SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }
        
        jsonResponse(['success' => true, 'message' => 'Subscription updated']);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to update subscription'], 500);
    }
}

// Submit auction bid
function submitAuctionBid($data) {
    validateRequired($data, ['username', 'userUid', 'itemId', 'bidAmount']);
    
    $username = sanitizeInput($data['username']);
    $userUid = sanitizeInput($data['userUid']);
    $itemId = sanitizeInput($data['itemId']);
    $bidAmount = (float)$data['bidAmount'];
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            INSERT INTO auction_bids (username, user_uid, item_id, bid_amount) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$username, $userUid, $itemId, $bidAmount]);
        
        logActivity($username, $userUid, 'auction_bid', [
            'itemId' => $itemId,
            'bidAmount' => $bidAmount
        ]);
        
        jsonResponse(['success' => true, 'message' => 'Bid submitted successfully']);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to submit bid'], 500);
    }
}

// Get auction bids for user
function getAuctionBids($username) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM auction_bids WHERE username = ? ORDER BY timestamp DESC");
        $stmt->execute([$username]);
        $bids = $stmt->fetchAll();
        
        jsonResponse($bids);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to get auction bids'], 500);
    }
}

// Get highest bids for all auction items
function getAuctionHighestBids() {
    try {
        $pdo = getDBConnection();
        
        $items = ['item1', 'item2', 'item3', 'item4', 'item5'];
        $highestBids = [];
        
        foreach ($items as $itemId) {
            $stmt = $pdo->prepare("
                SELECT * FROM auction_bids 
                WHERE item_id = ? 
                ORDER BY bid_amount DESC 
                LIMIT 1
            ");
            $stmt->execute([$itemId]);
            $bid = $stmt->fetch();
            
            $highestBids[$itemId] = $bid ?: ['bid_amount' => 0, 'username' => null];
        }
        
        jsonResponse(['success' => true, 'highestBids' => $highestBids]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to get highest bids'], 500);
    }
}

// Close auction and determine winners
function closeAuction($data) {
    try {
        $pdo = getDBConnection();
        
        $items = ['item1', 'item2', 'item3', 'item4', 'item5'];
        $winners = [];
        
        foreach ($items as $itemId) {
            $stmt = $pdo->prepare("
                SELECT * FROM auction_bids 
                WHERE item_id = ? 
                ORDER BY bid_amount DESC 
                LIMIT 1
            ");
            $stmt->execute([$itemId]);
            $winningBid = $stmt->fetch();
            
            if ($winningBid) {
                // Create winner record
                $paymentDeadline = date('Y-m-d H:i:s', strtotime('+' . AUCTION_PAYMENT_TIMEOUT_HOURS . ' hours'));
                
                $stmt = $pdo->prepare("
                    INSERT INTO auction_winners (winner_username, user_uid, item_id, winning_bid, payment_deadline) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $winningBid['username'],
                    $winningBid['user_uid'],
                    $itemId,
                    $winningBid['bid_amount'],
                    $paymentDeadline
                ]);
                
                $winners[] = [
                    'itemId' => $itemId,
                    'winner' => $winningBid['username'],
                    'winningBid' => $winningBid['bid_amount']
                ];
            }
        }
        
        jsonResponse(['success' => true, 'winners' => $winners]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to close auction'], 500);
    }
}

// Get user details
function getUserDetails($username) {
    try {
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("SELECT * FROM user_profiles WHERE username = ?");
        $stmt->execute([$username]);
        $profile = $stmt->fetch();
        
        if (!$profile) {
            jsonResponse(['success' => false, 'error' => 'User profile not found'], 404);
        }
        
        $stmt = $pdo->prepare("SELECT * FROM user_activities WHERE username = ? ORDER BY timestamp DESC LIMIT 50");
        $stmt->execute([$username]);
        $activities = $stmt->fetchAll();
        
        jsonResponse([
            'success' => true,
            'user' => [
                'profile' => $profile,
                'recentActivities' => $activities,
                'activityCount' => count($activities)
            ]
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to fetch user details'], 500);
    }
}

// Get digital art for user
function getDigitalArt($username) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM digital_art_delivery WHERE username = ? ORDER BY created_at DESC");
        $stmt->execute([$username]);
        $artworks = $stmt->fetchAll();
        
        jsonResponse([
            'success' => true,
            'username' => $username,
            'artworks' => $artworks,
            'count' => count($artworks)
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to get digital art'], 500);
    }
}

// Create test subscription
function createTestSubscription($data) {
    validateRequired($data, ['username']);
    
    $username = sanitizeInput($data['username']);
    
    try {
        $pdo = getDBConnection();
        
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+30 days'));
        
        $stmt = $pdo->prepare("
            INSERT INTO user_subscriptions (username, subscription_type, start_date, end_date, is_active, status, notes) 
            VALUES (?, 'monthly', ?, ?, 1, 'active', 'Test subscription')
        ");
        $stmt->execute([$username, $startDate, $endDate]);
        
        jsonResponse([
            'success' => true,
            'message' => 'Test subscription created successfully',
            'subscription' => [
                'username' => $username,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'status' => 'active'
            ]
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to create test subscription'], 500);
    }
}

// Get auction status with timer
function getAuctionStatus() {
    try {
        // Fixed auction end time - adjust this as needed
        $auctionEndTime = '2025-08-25 23:59:59'; // Set your auction end time here
        $now = new DateTime();
        $endTime = new DateTime($auctionEndTime);
        
        $isActive = $now < $endTime;
        $status = $isActive ? 'active' : 'ended';
        
        jsonResponse([
            'success' => true,
            'auctionName' => 'Auction 1',
            'status' => $status,
            'isActive' => $isActive,
            'startTime' => '2025-08-18 00:00:00', // Auction start time
            'endTime' => $auctionEndTime,
            'message' => $isActive ? 'Auction is currently active' : 'Auction has ended'
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to get auction status'], 500);
    }
}

// Remove auction bid
function removeAuctionBid($data) {
    validateRequired($data, ['username', 'userUid', 'itemId']);
    
    $username = sanitizeInput($data['username']);
    $userUid = sanitizeInput($data['userUid']);
    $itemId = sanitizeInput($data['itemId']);
    
    try {
        $pdo = getDBConnection();
        
        // Mark bid as inactive instead of deleting
        $stmt = $pdo->prepare("
            UPDATE auction_bids 
            SET is_active = 0, updated_at = NOW() 
            WHERE username = ? AND item_id = ? AND is_active = 1
        ");
        $stmt->execute([$username, $itemId]);
        
        if ($stmt->rowCount() > 0) {
            logActivity($username, $userUid, 'auction_bid_removed', [
                'itemId' => $itemId
            ]);
            
            jsonResponse(['success' => true, 'message' => 'Bid removed successfully']);
        } else {
            jsonResponse(['success' => false, 'error' => 'No active bid found to remove'], 404);
        }
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to remove bid'], 500);
    }
}

// Get user bid status
function getUserBidStatus($username) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT item_id, bid_amount, is_active, timestamp 
            FROM auction_bids 
            WHERE username = ? 
            ORDER BY timestamp DESC
        ");
        $stmt->execute([$username]);
        $bids = $stmt->fetchAll();
        
        $bidStatus = [];
        foreach ($bids as $bid) {
            $bidStatus[$bid['item_id']] = [
                'bidAmount' => $bid['bid_amount'],
                'status' => $bid['is_active'] ? 'active' : 'removed',
                'timestamp' => $bid['timestamp']
            ];
        }
        
        jsonResponse([
            'success' => true,
            'bidStatus' => $bidStatus
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to get bid status'], 500);
    }
}

// Get user wins
function getUserWins($username) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT * FROM auction_winners 
            WHERE winner_username = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$username]);
        $wins = $stmt->fetchAll();
        
        jsonResponse([
            'success' => true,
            'wins' => $wins,
            'count' => count($wins)
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to get user wins'], 500);
    }
}

// Calculate winners for auction
function calculateWinners($auctionId) {
    try {
        $pdo = getDBConnection();
        
        // Get highest bids for each item
        $items = ['item1', 'item2', 'item3', 'item4', 'item5'];
        $winners = [];
        $winnersCount = 0;
        
        foreach ($items as $itemId) {
            $stmt = $pdo->prepare("
                SELECT username, user_uid, bid_amount 
                FROM auction_bids 
                WHERE item_id = ? AND is_active = 1 
                ORDER BY bid_amount DESC 
                LIMIT 1
            ");
            $stmt->execute([$itemId]);
            $highestBid = $stmt->fetch();
            
            if ($highestBid) {
                // Check if winner already exists
                $stmt = $pdo->prepare("
                    SELECT id FROM auction_winners 
                    WHERE winner_username = ? AND item_id = ?
                ");
                $stmt->execute([$highestBid['username'], $itemId]);
                
                if (!$stmt->fetch()) {
                    // Create winner record
                    $paymentDeadline = date('Y-m-d H:i:s', strtotime('+' . AUCTION_PAYMENT_TIMEOUT_HOURS . ' hours'));
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO auction_winners (winner_username, user_uid, item_id, winning_bid, payment_deadline) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $highestBid['username'],
                        $highestBid['user_uid'],
                        $itemId,
                        $highestBid['bid_amount'],
                        $paymentDeadline
                    ]);
                    
                    $winners[] = [
                        'itemId' => $itemId,
                        'winner' => $highestBid['username'],
                        'winningBid' => $highestBid['bid_amount']
                    ];
                    $winnersCount++;
                }
            }
        }
        
        jsonResponse([
            'success' => true,
            'winners' => $winners,
            'winnersCount' => $winnersCount
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to calculate winners'], 500);
    }
}

// Get debug auction data
function getDebugAuctionData() {
    try {
        $pdo = getDBConnection();
        
        // Get auction status
        $auctionEndTime = '2025-08-25 23:59:59';
        $now = new DateTime();
        $endTime = new DateTime($auctionEndTime);
        $isActive = $now < $endTime;
        
        // Get all bids
        $stmt = $pdo->query("
            SELECT username, item_id as itemId, bid_amount as bidAmount, is_active, timestamp,
                   CASE WHEN is_active = 1 THEN 'active' ELSE 'removed' END as status
            FROM auction_bids 
            ORDER BY timestamp DESC
        ");
        $bids = $stmt->fetchAll();
        
        // Get all winners
        $stmt = $pdo->query("
            SELECT winner_username as winnerUsername, item_id as itemId, winning_bid as winningBid, 
                   payment_status as paymentStatus, created_at 
            FROM auction_winners 
            ORDER BY created_at DESC
        ");
        $winners = $stmt->fetchAll();
        
        jsonResponse([
            'success' => true,
            'debug' => [
                'auctionStatus' => [
                    'status' => $isActive ? 'active' : 'ended',
                    'isActive' => $isActive,
                    'endTime' => $auctionEndTime,
                    'message' => $isActive ? 'Auction is active' : 'Auction has ended'
                ],
                'bids' => $bids,
                'totalBids' => count($bids),
                'winners' => $winners,
                'totalWinners' => count($winners)
            ]
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to get debug data'], 500);
    }
}

// Approve payment
function approvePayment($data) {
    validateRequired($data, ['paymentId', 'winnerId', 'username']);
    
    $paymentId = sanitizeInput($data['paymentId']);
    $winnerId = sanitizeInput($data['winnerId']);
    $username = sanitizeInput($data['username']);
    
    try {
        $pdo = getDBConnection();
        
        // Update winner record with payment ID
        $stmt = $pdo->prepare("
            UPDATE auction_winners 
            SET payment_id = ?, payment_status = 'processing' 
            WHERE id = ? AND winner_username = ?
        ");
        $stmt->execute([$paymentId, $winnerId, $username]);
        
        if ($stmt->rowCount() > 0) {
            logActivity($username, '', 'payment_approved', [
                'paymentId' => $paymentId,
                'winnerId' => $winnerId
            ]);
            
            jsonResponse(['success' => true, 'message' => 'Payment approved']);
        } else {
            jsonResponse(['success' => false, 'error' => 'Winner record not found'], 404);
        }
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to approve payment'], 500);
    }
}

// Complete payment
function completePayment($data) {
    validateRequired($data, ['paymentId', 'txid', 'winnerId', 'username']);
    
    $paymentId = sanitizeInput($data['paymentId']);
    $txid = sanitizeInput($data['txid']);
    $winnerId = sanitizeInput($data['winnerId']);
    $username = sanitizeInput($data['username']);
    
    try {
        $pdo = getDBConnection();
        
        // Update winner record as paid
        $stmt = $pdo->prepare("
            UPDATE auction_winners 
            SET payment_status = 'completed', transaction_id = ?, payment_completed_at = NOW() 
            WHERE id = ? AND winner_username = ? AND payment_id = ?
        ");
        $stmt->execute([$txid, $winnerId, $username, $paymentId]);
        
        if ($stmt->rowCount() > 0) {
            logActivity($username, '', 'payment_completed', [
                'paymentId' => $paymentId,
                'txid' => $txid,
                'winnerId' => $winnerId
            ]);
            
            jsonResponse(['success' => true, 'message' => 'Payment completed successfully']);
        } else {
            jsonResponse(['success' => false, 'error' => 'Winner record not found or payment not approved'], 404);
        }
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to complete payment'], 500);
    }
}

// Approve subscription payment
function approveSubscriptionPayment($data) {
    validateRequired($data, ['paymentId', 'username', 'userUid']);
    
    $paymentId = sanitizeInput($data['paymentId']);
    $username = sanitizeInput($data['username']);
    $userUid = sanitizeInput($data['userUid']);
    
    try {
        $pdo = getDBConnection();
        
        // Log the payment approval
        logActivity($username, $userUid, 'subscription_payment_approved', [
            'paymentId' => $paymentId
        ]);
        
        jsonResponse(['success' => true, 'message' => 'Subscription payment approved']);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to approve subscription payment'], 500);
    }
}

// Complete subscription payment
function completeSubscriptionPayment($data) {
    validateRequired($data, ['paymentId', 'txId', 'username', 'userUid']);
    
    $paymentId = sanitizeInput($data['paymentId']);
    $txId = sanitizeInput($data['txId']);
    $username = sanitizeInput($data['username']);
    $userUid = sanitizeInput($data['userUid']);
    
    try {
        $pdo = getDBConnection();
        
        // Create 30-day subscription
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+30 days'));
        
        // Check if user already has an active subscription
        $stmt = $pdo->prepare("
            SELECT id FROM user_subscriptions 
            WHERE username = ? AND is_active = 1 AND end_date > NOW()
        ");
        $stmt->execute([$username]);
        $existingSubscription = $stmt->fetch();
        
        if ($existingSubscription) {
            // Extend existing subscription by 30 days
            $stmt = $pdo->prepare("
                UPDATE user_subscriptions 
                SET end_date = DATE_ADD(end_date, INTERVAL 30 DAY),
                    payment_id = ?,
                    transaction_id = ?
                WHERE username = ? AND is_active = 1
            ");
            $stmt->execute([$paymentId, $txId, $username]);
        } else {
            // Create new subscription
            $stmt = $pdo->prepare("
                INSERT INTO user_subscriptions 
                (username, user_uid, subscription_type, start_date, end_date, is_active, status, payment_id, transaction_id) 
                VALUES (?, ?, 'monthly', ?, ?, 1, 'active', ?, ?)
            ");
            $stmt->execute([$username, $userUid, $startDate, $endDate, $paymentId, $txId]);
        }
        
        // Log the payment completion
        logActivity($username, $userUid, 'subscription_payment_completed', [
            'paymentId' => $paymentId,
            'txId' => $txId,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
        
        jsonResponse([
            'success' => true, 
            'message' => 'Subscription payment completed successfully',
            'subscription' => [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'status' => 'active'
            ]
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'error' => 'Failed to complete subscription payment'], 500);
    }
}
?>
