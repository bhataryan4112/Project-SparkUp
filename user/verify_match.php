<?php
session_start();
header('Content-Type: application/json');

// Constants for encryption/decryption
define('ENCRYPTION_KEY', '0123456789abcdef0123456789abcdef');
define('ENCRYPTION_IV', '1234567890abcdef');

// Database connection
$host = 'localhost';
$username = "";
$password = "";
$dbname = "";

// Function to decrypt the encrypted email from QR code
function decryptEmail($encrypted) {
    return openssl_decrypt(
        base64_decode(urldecode($encrypted)), 
        'AES-256-CBC', 
        ENCRYPTION_KEY, 
        OPENSSL_RAW_DATA, 
        ENCRYPTION_IV
    );
}

try {
    // Parse input - either from GET parameter or POST JSON body
    $scannedData = null;
    $currentUser = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Handle direct QR code scan (from external scanner)
        if (isset($_GET['data'])) {
            $scannedData = $_GET['data'];
            
            if (isset($_SESSION['email'])) {
                $currentUser = $_SESSION['email'];
            } else {
                // Redirect to login if not authenticated
                http_response_code(401);
                header('Location: /user?error=unauthorized');
                echo json_encode(['error' => 'Authentication required']);
                exit;
            }
        }
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle API request from dashboard QR scanner
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (isset($input['qrData'])) {
            $scannedData = $input['qrData'];
            $currentUser = $input['userEmail'] ?? $_SESSION['email'];
        }
    }
    
    // Verify we have the necessary data
    if (!$scannedData || !$currentUser) {
        http_response_code(400);
        header('Location: /user?error=invalid_session');
        echo json_encode(['error' => 'Missing required data']);
        exit;
    }

    // Decrypt the scanned email
    $scannedEmail = decryptEmail($scannedData);
    
    // Establish database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if users are mutual matches (user1 has user2 in their matches AND vice versa)
    $stmt = $pdo->prepare("SELECT COUNT(*) as match_count FROM (
                          SELECT 1 FROM `2025_01_12_00_35_22_matches`
                          WHERE `COL 1` = :scannedEmail
                          AND (:currentUser IN (`COL 2`, `COL 3`, `COL 4`, `COL 5`, `COL 6`))
                          
                          UNION ALL
                          
                          SELECT 1 FROM `2025_01_12_00_35_22_matches`
                          WHERE `COL 1` = :currentUser
                          AND :scannedEmail IN (`COL 2`, `COL 3`, `COL 4`, `COL 5`, `COL 6`)) as matches");

    $stmt->execute([
        ':scannedEmail' => $scannedEmail,
        ':currentUser' => $currentUser
    ]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $isMatch = ($result['match_count'] === 2);

    if ($isMatch) {
        // Check if match already exists in completed_matches
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM completed_matches 
                                   WHERE (user_email = :user1 AND match_email = :user2)
                                   OR (user_email = :user2 AND match_email = :user1)");
        $checkStmt->execute([
            ':user1' => $currentUser,
            ':user2' => $scannedEmail
        ]);
        
        $matchExists = $checkStmt->fetchColumn() > 0;
        
        if (!$matchExists) {
            // Create timer session for both users
            $pdo->beginTransaction();
            
            try {
                // Important: Include the created_at timestamp to ensure both records have the same timestamp
                $now = date('Y-m-d H:i:s');
                
                // Create records for both users - explicitly set start_time to NULL initially
                $stmt = $pdo->prepare("INSERT INTO completed_matches (user_email, match_email, start_time, created_at) 
                                      VALUES (?, ?, NULL, ?), (?, ?, NULL, ?)");
                $stmt->execute([
                    $currentUser, $scannedEmail, $now,
                    $scannedEmail, $currentUser, $now
                ]);
                
                $pdo->commit();
                
                // For GET requests, redirect to timer page
                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    http_response_code(302);
                    header("Location: timer.php?match=" . urlencode($scannedEmail));
                    exit();
                }
                
                // For API requests, return success with redirect URL
                echo json_encode([
                    'success' => true,
                    'message' => 'Match verified successfully',
                    'redirect' => "timer.php?match=" . urlencode($scannedEmail),
                    'matchEmail' => $scannedEmail
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            }
        } else {
            // Match already verified
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                http_response_code(302);
                header("Location: timer.php?match=" . urlencode($scannedEmail));
                exit();
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Match already verified',
                'redirect' => "timer.php?match=" . urlencode($scannedEmail),
                'matchEmail' => $scannedEmail
            ]);
        }
    } else {
        // Invalid match
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid match. You are not matched with this person.'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Verification failed: ' . $e->getMessage()]);
}