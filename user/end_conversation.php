<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$currentUser = $_SESSION['email'];

// Get match ID from request
$input = json_decode(file_get_contents('php://input'), true);
$matchEmail = isset($input['match_email']) ? $input['match_email'] : null;

// Fallback to GET parameter if POST data not available
if (!$matchEmail && isset($_GET['match'])) {
    $matchEmail = urldecode($_GET['match']);
}

if (!$matchEmail) {
    echo json_encode(['error' => 'Missing match email']);
    exit;
}

try {
    // Database connection
    $pdo = new PDO("mysql:host=localhost;dbname=", "", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // First, check if the columns exist, if not create them
    try {
        $checkColumns = $pdo->query("SHOW COLUMNS FROM completed_matches LIKE 'ended_early'");
        if ($checkColumns->rowCount() === 0) {
            // Column doesn't exist, create it
            $pdo->exec("ALTER TABLE completed_matches ADD COLUMN ended_early TINYINT(1) DEFAULT 0");
            error_log("Added ended_early column to completed_matches table");
        }
        
        $checkColumns = $pdo->query("SHOW COLUMNS FROM completed_matches LIKE 'ended_by'");
        if ($checkColumns->rowCount() === 0) {
            // Column doesn't exist, create it
            $pdo->exec("ALTER TABLE completed_matches ADD COLUMN ended_by VARCHAR(255) NULL");
            error_log("Added ended_by column to completed_matches table");
        }
    } catch (Exception $e) {
        error_log("Error checking or adding columns: " . $e->getMessage());
    }
    
    // Begin transaction to ensure data consistency
    $pdo->beginTransaction();
    
    // First, check if the records exist
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM completed_matches 
                                WHERE (user_email = ? AND match_email = ?) 
                                OR (user_email = ? AND match_email = ?)");
    $checkStmt->execute([$currentUser, $matchEmail, $matchEmail, $currentUser]);
    
    $recordsExist = $checkStmt->fetchColumn() > 0;
    
    if (!$recordsExist) {
        error_log("No matching records found for users: {$currentUser} and {$matchEmail}");
        echo json_encode(['error' => 'No matching conversation records found']);
        exit;
    }
    
    // Update both user records to indicate conversation ended early
    $endTime = date('Y-m-d H:i:s');
    
    // Use direct integer value for boolean to ensure compatibility
    $stmt = $pdo->prepare("UPDATE completed_matches 
                          SET end_time = :endTime, 
                              ended_early = 1,
                              ended_by = :currentUser
                          WHERE (user_email = :userEmail AND match_email = :matchEmail)
                             OR (user_email = :matchEmail AND match_email = :userEmail)");
    
    $params = [
        ':endTime' => $endTime,
        ':currentUser' => $currentUser,
        ':userEmail' => $currentUser,
        ':matchEmail' => $matchEmail
    ];
    
    $success = $stmt->execute($params);
    $rowsUpdated = $stmt->rowCount();
    
    // Debug info
    $debugInfo = [
        'success' => $success,
        'rowsUpdated' => $rowsUpdated
    ];
    error_log("End conversation update result: " . json_encode($debugInfo));
    
    // Verify the update worked
    $verifyStmt = $pdo->prepare("SELECT ended_early, ended_by FROM completed_matches 
                                WHERE user_email = ? AND match_email = ?");
    $verifyStmt->execute([$currentUser, $matchEmail]);
    $verifyResult = $verifyStmt->fetch(PDO::FETCH_ASSOC);
    error_log("Verification check: " . json_encode($verifyResult));
    
    $pdo->commit();
    
    echo json_encode([
        'success' => $success,
        'rowsUpdated' => $rowsUpdated,
        'debug' => $debugInfo,
        'verification' => $verifyResult,
        'message' => 'Conversation ended',
        'redirect' => 'feedback.php?match=' . urlencode($matchEmail)
    ]);
    
} catch (PDOException $e) {
    // Roll back transaction if error occurs
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("End conversation error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
