<?php
session_start();
require_once 'encrypt.php';

header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$user1 = decryptData($input['user1']);
$user2 = decryptData($input['user2']);

try {
    $pdo = new PDO("mysql:host=localhost;dbname=u265056410_sparkup", "u265056410_sparkup", "Sparkup@12345");
    
    // Check if both users have active start_time values
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM completed_matches 
                          WHERE ((user_email = ? AND match_email = ?) 
                          OR (user_email = ? AND match_email = ?))
                          AND start_time IS NOT NULL
                          AND end_time IS NULL");
    $stmt->execute([$user1, $user2, $user2, $user1]);
    
    $activeCount = $stmt->fetchColumn();
    
    echo json_encode([
        'bothPresent' => ($activeCount >= 2)
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'database_error']);
}
