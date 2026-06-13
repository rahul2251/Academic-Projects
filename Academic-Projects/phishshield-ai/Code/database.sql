

CREATE DATABASE IF NOT EXISTS phishshield_db
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE phishshield_db;

-- ---------- USERS ----------
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  profile_image VARCHAR(255) DEFAULT NULL,
  is_blocked TINYINT(1) DEFAULT 0,
  dark_mode TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- ADMINS ----------
CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- URL SCANS ----------
CREATE TABLE IF NOT EXISTS url_scans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  url TEXT NOT NULL,
  result ENUM('Safe','Suspicious','Phishing') NOT NULL,
  risk_score INT DEFAULT 0,
  reasons TEXT,
  recommendation TEXT,
  source VARCHAR(50) DEFAULT 'hybrid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- EMAIL SCANS ----------
CREATE TABLE IF NOT EXISTS email_scans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  content TEXT NOT NULL,
  result ENUM('Legitimate','Suspicious','Phishing') NOT NULL,
  risk_score INT DEFAULT 0,
  reasons TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- FEEDBACK ----------
CREATE TABLE IF NOT EXISTS feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  rating INT DEFAULT 5,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- BLACKLIST ----------
CREATE TABLE IF NOT EXISTS blacklist (
  id INT AUTO_INCREMENT PRIMARY KEY,
  domain VARCHAR(255) NOT NULL UNIQUE,
  reason VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- WHITELIST ----------
CREATE TABLE IF NOT EXISTS whitelist (
  id INT AUTO_INCREMENT PRIMARY KEY,
  domain VARCHAR(255) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- CHAT HISTORY ----------
CREATE TABLE IF NOT EXISTS chat_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  role ENUM('user','bot') NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- ACTIVITY LOGS ----------
CREATE TABLE IF NOT EXISTS activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT DEFAULT NULL,
  action VARCHAR(255) NOT NULL,
  ip VARCHAR(50) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- DEFAULT ADMIN ----------
-- Email: admin@phishshield.ai
-- Password: admin123  (hashed with PHP password_hash, BCRYPT)
INSERT INTO admins (name, email, password) VALUES
('Super Admin', 'admin@phishshield.ai',
 '$2b$10$cNz4BWJEzO0e4BH9q6ZQ7OC/l.0ZPtq6C97TBLFULO9rKT2hMl592')
ON DUPLICATE KEY UPDATE email = email;

-- ---------- SAMPLE BLACKLIST / WHITELIST ----------
INSERT IGNORE INTO blacklist (domain, reason) VALUES
('phishing-example.com', 'Known phishing'),
('malicious-site.ru', 'Malware'),
('fake-paypal-login.tk', 'Credential theft');

INSERT IGNORE INTO whitelist (domain) VALUES
('google.com'),
('microsoft.com'),
('github.com'),
('paypal.com');
