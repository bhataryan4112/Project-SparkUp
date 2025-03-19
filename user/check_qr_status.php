<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$currentUser = $_SESSION['email'];

try {
    // Database connection
    $pdo = new PDO("mysql:host=localhost;dbname=", "", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if there's already a pending redirect to timer page (prevents loops)
    if (isset($_SESSION['redirect_processed']) && time() - $_SESSION['redirect_processed'] < 30) {
        echo json_encode(['scanned' => false, 'reason' => 'already_processing']);
        exit;
    }
    
    // Check if someone has recently scanned our QR and created a match record
    // The key change here is removing the START_TIME IS NOT NULL condition
    // because records might be created but not yet have start_time set
    $stmt = $pdo->prepare("SELECT match_email FROM completed_matches 
                          WHERE user_email = ?
                          AND end_time IS NULL
                          ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$currentUser]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        // Mark this redirect as processed to prevent loops
        $_SESSION['redirect_processed'] = time();
        
        echo json_encode([
            'scanned' => true,
            'match_email' => $result['match_email'],
            'redirect' => 'timer.php?match=' . urlencode($result['match_email'])
        ]);
    } else {
        echo json_encode(['scanned' => false]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
