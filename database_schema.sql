-- Database schema for Appraisells PHP Application
-- Run this SQL script in your MySQL database

CREATE DATABASE IF NOT EXISTS appraisells_db;
USE appraisells_db;

-- User profiles table
CREATE TABLE IF NOT EXISTS user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    user_uid VARCHAR(255) NOT NULL UNIQUE,
    wallet_address VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    total_payments DECIMAL(10,2) DEFAULT 0.00,
    subscription_status ENUM('active', 'inactive', 'expired') DEFAULT 'inactive',
    profile_data JSON,
    INDEX idx_username (username),
    INDEX idx_user_uid (user_uid)
);

-- User activities table
CREATE TABLE IF NOT EXISTS user_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    user_uid VARCHAR(255),
    activity_type VARCHAR(50) NOT NULL,
    details JSON,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    INDEX idx_username (username),
    INDEX idx_timestamp (timestamp),
    INDEX idx_activity_type (activity_type)
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(100) NOT NULL,
    user_uid VARCHAR(255),
    tx_id VARCHAR(255),
    amount DECIMAL(10,4) NOT NULL,
    memo TEXT,
    status ENUM('pending', 'approved', 'completed', 'failed') DEFAULT 'pending',
    payment_type ENUM('subscription', 'auction_bid', 'other') DEFAULT 'other',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    metadata JSON,
    INDEX idx_payment_id (payment_id),
    INDEX idx_username (username),
    INDEX idx_status (status),
    INDEX idx_payment_type (payment_type)
);

-- User subscriptions table
CREATE TABLE IF NOT EXISTS user_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    user_uid VARCHAR(255),
    payment_id VARCHAR(255),
    subscription_type ENUM('monthly', 'yearly') DEFAULT 'monthly',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (payment_id) REFERENCES payments(payment_id) ON DELETE SET NULL,
    INDEX idx_username (username),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
);

-- Auction bids table
CREATE TABLE IF NOT EXISTS auction_bids (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    user_uid VARCHAR(255),
    item_id VARCHAR(50) NOT NULL,
    bid_amount DECIMAL(10,4) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_username (username),
    INDEX idx_item_id (item_id),
    INDEX idx_bid_amount (bid_amount DESC),
    INDEX idx_timestamp (timestamp)
);

-- Auction winners table
CREATE TABLE IF NOT EXISTS auction_winners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    winner_username VARCHAR(100) NOT NULL,
    user_uid VARCHAR(255),
    item_id VARCHAR(50) NOT NULL,
    winning_bid DECIMAL(10,4) NOT NULL,
    payment_id VARCHAR(255),
    payment_status ENUM('pending', 'paid', 'expired') DEFAULT 'pending',
    payment_deadline TIMESTAMP,
    digital_art_delivered BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(payment_id) ON DELETE SET NULL,
    INDEX idx_winner_username (winner_username),
    INDEX idx_item_id (item_id),
    INDEX idx_payment_status (payment_status)
);

-- Digital art delivery table
CREATE TABLE IF NOT EXISTS digital_art_delivery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    winner_id INT,
    item_id VARCHAR(50) NOT NULL,
    file_path VARCHAR(255),
    download_url VARCHAR(255),
    license_details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    access_count INT DEFAULT 0,
    last_accessed TIMESTAMP NULL,
    FOREIGN KEY (winner_id) REFERENCES auction_winners(id) ON DELETE CASCADE,
    INDEX idx_username (username),
    INDEX idx_item_id (item_id)
);

-- Sessions table for tracking visits
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent TEXT,
    page_visited VARCHAR(255),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    additional_data JSON,
    INDEX idx_ip_address (ip_address),
    INDEX idx_timestamp (timestamp)
);

-- Insert sample auction items
INSERT IGNORE INTO auction_items (item_id, artist_name, artwork_title, reserve_bid, image_path, description) VALUES
('item1', 'Digital Artist', 'Abstract Composition #1', 5.00, 'images/middle finger man.png', 'Unique digital artwork'),
('item2', 'Digital Artist', 'Abstract Composition #2', 3.00, 'images/SueHipple1.jpg', 'Beautiful digital creation'),
('item3', 'Digital Artist', 'Abstract Composition #3', 4.00, 'images/SueHipple2.jpg', 'Artistic digital piece'),
('item4', 'Digital Artist', 'Abstract Composition #4', 6.00, 'images/SueHipple3.jpg', 'Creative digital artwork'),
('item5', 'Digital Artist', 'Abstract Composition #5', 2.00, 'images/SueHipple4.jpg', 'Expressive digital art');

-- Create auction items table (missing from above)
CREATE TABLE IF NOT EXISTS auction_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id VARCHAR(50) NOT NULL UNIQUE,
    artist_name VARCHAR(100) NOT NULL,
    artwork_title VARCHAR(255) NOT NULL,
    reserve_bid DECIMAL(10,4) NOT NULL,
    image_path VARCHAR(255),
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_item_id (item_id)
);
