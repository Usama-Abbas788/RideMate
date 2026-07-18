<?php
require 'config/database.php';
$queries = [
    "ALTER TABLE users ADD COLUMN email VARCHAR(255) NOT NULL UNIQUE",
    "ALTER TABLE users MODIFY COLUMN phone VARCHAR(20) NULL DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 0",
    "ALTER TABLE users ADD COLUMN verification_token VARCHAR(100) NULL DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN verification_expires DATETIME NULL DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN reset_token VARCHAR(100) NULL DEFAULT NULL",
    "ALTER TABLE users ADD COLUMN reset_expires DATETIME NULL DEFAULT NULL",
    "ALTER TABLE pending_registrations ADD COLUMN email VARCHAR(255) NOT NULL UNIQUE",
    "ALTER TABLE pending_registrations MODIFY COLUMN phone VARCHAR(20) NULL DEFAULT NULL",
    "ALTER TABLE pending_registrations ADD COLUMN verification_token VARCHAR(100) NULL DEFAULT NULL",
    "ALTER TABLE pending_registrations ADD COLUMN verification_expires DATETIME NULL DEFAULT NULL",
    "CREATE TABLE IF NOT EXISTS notifications (
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        message VARCHAR(255) NOT NULL,
        type ENUM('info','success','warning','error') NOT NULL DEFAULT 'info',
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($queries as $query) {
    if (!$conn->query($query)) {
        echo 'ERROR: ' . $conn->error . PHP_EOL;
        echo 'QUERY: ' . $query . PHP_EOL;
    } else {
        echo 'OK: ' . str_replace("\n", ' ', $query) . PHP_EOL;
    }
}
