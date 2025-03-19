<?php
/**
 * Database Configuration
 * 
 * This file contains database connection settings for Project SparkUp
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', '');
define('DB_USER', ''); // Replace with actual username
define('DB_PASS', ''); // Replace with actual password

/**
 * Get database connection
 * 
 * @return PDO database connection object
 */
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // Log error (in production, don't show details to users)
        error_log("Database Connection Error: " . $e->getMessage());
        throw new PDOException("Database connection failed: " . $e->getMessage());
    }
}

/**
 * SQL Query to create waitlist_emails table if not exists
 * 
 * CREATE TABLE IF NOT EXISTS `waitlist_emails` (
 *   `id` INT AUTO_INCREMENT PRIMARY KEY,
 *   `email` VARCHAR(255) NOT NULL UNIQUE,
 *   `created_at` DATETIME NOT NULL,
 *   `status` ENUM('pending', 'notified') DEFAULT 'pending',
 *   `notify_date` DATETIME DEFAULT NULL
 * );
 */
