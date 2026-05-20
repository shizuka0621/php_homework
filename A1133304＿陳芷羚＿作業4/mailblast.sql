-- MailBlast 郵件群發系統資料庫
-- 建立資料庫
CREATE DATABASE IF NOT EXISTS mailblast CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE mailblast;

-- 收件人清單
CREATE TABLE IF NOT EXISTS recipients (
    no INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(100) DEFAULT '',
    status ENUM('active', 'unsubscribed') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 寄送記錄
CREATE TABLE IF NOT EXISTS send_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id INT,
    email VARCHAR(255),
    subject VARCHAR(500),
    status ENUM('success', 'failed') DEFAULT 'success',
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    error_message TEXT,
    FOREIGN KEY (recipient_id) REFERENCES recipients(no) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 範例資料
INSERT IGNORE INTO recipients (email, name) VALUES
('test1@example.com', '測試用戶一'),
('test2@example.com', '測試用戶二'),
('test3@example.com', '測試用戶三');
