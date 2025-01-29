CREATE DATABASE IF NOT EXISTS shorten;
USE shorten;

-- Table to Store Unique Links or their data

CREATE TABLE links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    platform VARCHAR(50) NOT NULL,
    other_platform VARCHAR(100) DEFAULT NULL,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    page_id VARCHAR(50) UNIQUE NOT NULL,
    expiry_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table to store participants' information
CREATE TABLE IF NOT EXISTS participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_id INT NOT NULL,
    username VARCHAR(255) NOT NULL,
    participant_telegram_username VARCHAR(255) UNIQUE NOT NULL,
    link VARCHAR(255) UNIQUE NOT NULL,
    total_view INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (page_id) REFERENCES links(page_id) ON DELETE CASCADE
);


-- Table to store link views
CREATE TABLE IF NOT EXISTS link_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    link VARCHAR(255) NOT NULL,
    participant_telegram_username VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    last_viewed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    referring_site VARCHAR(255) DEFAULT 'Direct',
    UNIQUE (link, ip_address),
    FOREIGN KEY (participant_telegram_username) REFERENCES participants(participant_telegram_username) ON DELETE CASCADE
);
