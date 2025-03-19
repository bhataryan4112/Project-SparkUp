<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$currentUser = $_SESSION['email'];
$matchEmail = isset($_GET['match']) ? urldecode($_GET['match']) : null;

if (!$matchEmail) {
    echo json_encode(['error' => 'Missing match parameter']);
    exit;
}

try {
    // Database connection
    $pdo = new PDO("mysql:host=;dbname=", "", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if conversation was ended early by the other user
    $stmt = $pdo->prepare("SELECT ended_early, ended_by FROM completed_matches 
                          WHERE user_email = :userEmail 
                          AND match_email = :matchEmail 
                          AND ended_early = 1
                          AND ended_by != :userEmail");
    $stmt->execute([
        ':userEmail' => $currentUser,
        ':matchEmail' => $matchEmail
    ]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode([
            'ended' => true,
            'ended_by' => $result['ended_by'],
            'redirect' => 'feedback.php?match=' . urlencode($matchEmail) . '&ended_by=match'
        ]);
    } else {
        echo json_encode(['ended' => false]);
    }
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
