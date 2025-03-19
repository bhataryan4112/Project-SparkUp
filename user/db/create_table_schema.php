<?php
// Database connection
$host = 'localhost';
$username = "u265056410_sparkup";
$password = "Sparkup@12345";
$dbname = "u265056410_sparkup";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create classroom_tables table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `classroom_tables` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `table_number` int(11) NOT NULL,
      `classroom` varchar(50) NOT NULL,
      `capacity` int(11) DEFAULT 2,
      `status` enum('available','occupied','reserved') DEFAULT 'available',
      PRIMARY KEY (`id`),
      UNIQUE KEY `table_classroom` (`table_number`, `classroom`)
    )");
    
    // Create table_assignments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `table_assignments` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `table_id` int(11) NOT NULL,
      `user_email_1` varchar(255) NOT NULL,
      `user_email_2` varchar(255) NOT NULL,
      `start_time` datetime DEFAULT NULL,
      `end_time` datetime DEFAULT NULL,
      `time_slot` varchar(50) NOT NULL,
      `status` enum('pending','active','completed','cancelled') DEFAULT 'pending',
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `table_id` (`table_id`),
      CONSTRAINT `fk_table_id` FOREIGN KEY (`table_id`) REFERENCES `classroom_tables` (`id`) ON DELETE CASCADE
    )");
    
    // Alter completed_matches table to add table assignment reference
    $pdo->exec("ALTER TABLE `completed_matches` 
                ADD COLUMN `table_assignment_id` INT NULL,
                ADD CONSTRAINT `fk_table_assignment` 
                FOREIGN KEY (`table_assignment_id`) 
                REFERENCES `table_assignments` (`id`) ON DELETE SET NULL");
    
    // Modify completed_matches table to ensure ended_early column exists
    $checkColumns = $pdo->query("SHOW COLUMNS FROM completed_matches LIKE 'ended_early'");
    if ($checkColumns->rowCount() === 0) {
        $pdo->exec("ALTER TABLE completed_matches ADD COLUMN ended_early TINYINT(1) DEFAULT 0");
    }
    
    $checkColumns = $pdo->query("SHOW COLUMNS FROM completed_matches LIKE 'ended_by'");
    if ($checkColumns->rowCount() === 0) {
        $pdo->exec("ALTER TABLE completed_matches ADD COLUMN ended_by VARCHAR(255) NULL");
    }
    
    echo "Tables created successfully!";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
