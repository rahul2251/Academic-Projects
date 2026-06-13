-- PhishShield AI Database
-- Import this file via phpMyAdmin

CREATE DATABASE IF NOT EXISTS phishshield_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE phishshield_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) DEFAULT '',
    avatar VARCHAR(255) DEFAULT '',
    role ENUM('user','admin') DEFAULT 'user',
    status ENUM('active','blocked','pending') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
) ENGINE=InnoDB;

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) DEFAULT 'Administrator',
    avatar VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
) ENGINE=InnoDB;

-- URL Scans table
CREATE TABLE IF NOT EXISTS url_scans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    url TEXT NOT NULL,
    result ENUM('safe','phishing','suspicious','unknown') DEFAULT 'unknown',
    risk_score INT DEFAULT 0,
    details TEXT,
    ai_analysis TEXT,
    scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Email Scans table
CREATE TABLE IF NOT EXISTS email_scans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(255) DEFAULT '',
    sender VARCHAR(255) DEFAULT '',
    email_body TEXT,
    result ENUM('safe','phishing','suspicious','unknown') DEFAULT 'unknown',
    risk_score INT DEFAULT 0,
    details TEXT,
    ai_analysis TEXT,
    scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Feedback table
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending','read','replied') DEFAULT 'pending',
    admin_reply TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Blacklist table
CREATE TABLE IF NOT EXISTS blacklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain VARCHAR(255) NOT NULL UNIQUE,
    reason TEXT DEFAULT '',
    added_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Whitelist table
CREATE TABLE IF NOT EXISTS whitelist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain VARCHAR(255) NOT NULL UNIQUE,
    reason TEXT DEFAULT '',
    added_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Activity Logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    user_type ENUM('user','admin') DEFAULT 'user',
    action VARCHAR(255) NOT NULL,
    details TEXT DEFAULT '',
    ip_address VARCHAR(45) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Chat History table
CREATE TABLE IF NOT EXISTS chat_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role ENUM('user','assistant') NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Reports table
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    report_type ENUM('url','email','summary') DEFAULT 'summary',
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) DEFAULT '',
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insert default admin (password: admin123)
-- Hash below is bcrypt of 'admin123'
INSERT INTO admins (username, email, password, full_name) VALUES
('admin', 'admin@phishshield.ai', '$2y$10$TKh8H1.PfbuSohmhuD7.VuMGT5TZen0NHqNJ9RMz3e5KMpb4HvDC2', 'System Administrator');

-- Insert sample users (password: password)
-- Hash below is bcrypt of 'password'
INSERT INTO users (username, email, password, full_name, status) VALUES
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'active'),
('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', 'active');

-- Sample blacklist entries
INSERT INTO blacklist (domain, reason) VALUES
('phishing-site.com', 'Known phishing domain'),
('malware-download.net', 'Malware distribution'),
('fake-paypal.xyz', 'PayPal impersonation'),
('secure-login-verify.tk', 'Credential harvesting'),
('update-your-account.ga', 'Account takeover attempt');

-- Sample whitelist entries
INSERT INTO whitelist (domain, reason) VALUES
('google.com', 'Trusted search engine'),
('microsoft.com', 'Trusted software company'),
('github.com', 'Trusted developer platform'),
('paypal.com', 'Trusted payment platform'),
('amazon.com', 'Trusted e-commerce');

-- Sample URL scans
INSERT INTO url_scans (user_id, url, result, risk_score, details, ai_analysis) VALUES
(1, 'https://phishing-site.com/login', 'phishing', 95, 'Domain found in blacklist. Suspicious SSL. Lookalike domain detected.', 'High confidence phishing attempt targeting bank credentials.'),
(1, 'https://google.com', 'safe', 2, 'Domain whitelisted. Valid SSL. No suspicious patterns.', 'This is a legitimate Google URL with no phishing indicators.'),
(2, 'https://fake-paypal.xyz/verify', 'phishing', 98, 'Impersonating PayPal. Invalid SSL certificate. Suspicious redirect.', 'Clear PayPal impersonation attempt. Do not enter credentials.');

-- Sample email scans
INSERT INTO email_scans (user_id, subject, sender, email_body, result, risk_score, details, ai_analysis) VALUES
(1, 'Urgent: Verify your account', 'noreply@paypa1.com', 'Dear customer, your account has been suspended. Click here to verify: http://fake-link.com', 'phishing', 92, 'Sender domain spoofed. Urgency language. Suspicious link detected.', 'Classic phishing email using urgency tactics and domain spoofing.'),
(2, 'Your Amazon order has shipped', 'shipment-tracking@amazon.com', 'Your order #123-456 has been shipped and will arrive by Friday.', 'safe', 5, 'Legitimate Amazon sender. No suspicious links. Normal shipping notification.', 'This appears to be a legitimate Amazon shipping notification.');

-- Sample activity logs
INSERT INTO activity_logs (user_id, user_type, action, details, ip_address) VALUES
(1, 'user', 'Login', 'User logged in successfully', '127.0.0.1'),
(1, 'user', 'URL Scan', 'Scanned: https://phishing-site.com/login', '127.0.0.1'),
(2, 'user', 'Email Scan', 'Scanned suspicious email', '127.0.0.1'),
(NULL, 'admin', 'Admin Login', 'Admin logged in', '127.0.0.1');
