<?php
/**
 * Database configuration file
 * 
 * Use this file to maintain database connection settings across the application
 */

// Database connection settings
$db_config = [
    'host' => 'localhost',
    'username' => 'u265056410_sparkup',
    'password' => 'Sparkup@12345',
    'database' => 'u265056410_sparkup'
];

/**
 * Get database PDO connection
 * 
 * @return PDO Database connection
 */
function getDbConnection() {
    global $db_config;
    
    try {
        $pdo = new PDO(
            "mysql:host={$db_config['host']};dbname={$db_config['database']}",
            $db_config['username'],
            $db_config['password']
        );
        
        // Set error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Use UTF-8 encoding
        $pdo->exec("SET NAMES 'utf8'");
        
        return $pdo;
    } catch (PDOException $e) {
        // You might want to log this error instead of displaying it
        die("Database connection failed: " . $e->getMessage());
    }
}
