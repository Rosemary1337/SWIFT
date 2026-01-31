CREATE DATABASE IF NOT EXISTS swift_db;
USE swift_db;

CREATE TABLE IF NOT EXISTS swift_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(45),
    method VARCHAR(10),
    uri TEXT,
    payload_hash VARCHAR(64),
    headers TEXT,
    payload TEXT,
    risk_score INT DEFAULT 0,
    classification ENUM('normal', 'suspicious', 'malicious') DEFAULT 'normal',
    detection_tags TEXT,
    INDEX (timestamp),
    INDEX (ip),
    INDEX (classification)
);

CREATE TABLE IF NOT EXISTS swift_summary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    summary_text TEXT,
    recommendations TEXT
);

CREATE TABLE IF NOT EXISTS swift_settings (
    skey VARCHAR(50) PRIMARY KEY,
    svalue TEXT
);
