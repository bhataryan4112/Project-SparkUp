<?php
// This file will help you add the necessary columns to your database table
// Run it once to update your schema

$host = 'localhost';
$username = "u265056410_sparkup";
$password = "Sparkup@12345";
$dbname = "u265056410_sparkup";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if the ended_early column exists
    $result = $pdo->query("SHOW COLUMNS FROM completed_matches LIKE 'ended_early'");
    if ($result->rowCount() === 0) {
        // Add the ended_early column
        $pdo->exec("ALTER TABLE completed_matches ADD COLUMN ended_early TINYINT(1) DEFAULT 0");
        echo "Added ended_early column.<br>";
    } else {
        echo "ended_early column already exists.<br>";
    }
    
    // Check if the ended_by column exists
    $result = $pdo->query("SHOW COLUMNS FROM completed_matches LIKE 'ended_by'");
    if ($result->rowCount() === 0) {
        // Add the ended_by column
        $pdo->exec("ALTER TABLE completed_matches ADD COLUMN ended_by VARCHAR(255) NULL");
        echo "Added ended_by column.<br>";
    } else {
        echo "ended_by column already exists.<br>";
    }
    
    echo "Schema update completed successfully!";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
