<?php
require_once __DIR__ . '/swift/boot.php';

try {
    $pdo = \Swift\Core\Database::getInstance();
    $sql = "
    CREATE TABLE IF NOT EXISTS `swift_quarantine` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `log_id` INT NOT NULL,
        `original_name` VARCHAR(255) NOT NULL,
        `quarantine_path` VARCHAR(255) NOT NULL,
        `detection_details` TEXT,
        `quarantined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`log_id`) REFERENCES `swift_logs`(`id`) ON DELETE CASCADE
    );
    ";
    $pdo->exec($sql);
    echo "Table swift_quarantine created or verified successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
