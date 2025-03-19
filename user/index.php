<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

// Database connection
$host = 'localhost';
$username = "";
$password = "";
$dbname = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get user's matches from original table
    $stmt = $pdo->prepare("SELECT `COL 2`, `COL 3`, `COL 4`, `COL 5`, `COL 6` 
                          FROM `2025_01_12_00_35_22_matches` 
                          WHERE `COL 1` = :email");
    $stmt->bindParam(':email', $_SESSION['email']);
    $stmt->execute();
    $matchesRow = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Create a clean array of matches
    $matches = [];
    if ($matchesRow) {
        foreach(['COL 2', 'COL 3', 'COL 4', 'COL 5', 'COL 6'] as $col) {
            if (!empty($matchesRow[$col])) {
                $matches[] = $matchesRow[$col];
            }
        }
    }

    // Generate placeholder matches if none exist
    if (empty($matches)) {
        $matches = array_fill(0, 5, null); // Create 5 placeholder matches
    }

    // Get completed matches with feedback
    $feedbackStmt = $pdo->prepare("SELECT match_email FROM completed_matches 
                                  WHERE user_email = :email AND feedback_provided = 1");
    $feedbackStmt->bindParam(':email', $_SESSION['email']);
    $feedbackStmt->execute();
    $visibleMatches = $feedbackStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get user info if available
    $userInfoStmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE email = :email");
    $userInfoStmt->bindParam(':email', $_SESSION['email']);
    $userInfoStmt->execute();
    $userInfo = $userInfoStmt->fetch(PDO::FETCH_ASSOC);
    $userName = $userInfo ? $userInfo['first_name'] . ' ' . $userInfo['last_name'] : 'User';

    // Get match user info 
    $matchInfo = [];
    if (!empty($visibleMatches)) {
        $placeholders = str_repeat('?,', count($visibleMatches) - 1) . '?';
        $matchInfoStmt = $pdo->prepare("SELECT email, first_name, last_name FROM users WHERE email IN ($placeholders)");
        $matchInfoStmt->execute($visibleMatches);
        while ($row = $matchInfoStmt->fetch(PDO::FETCH_ASSOC)) {
            $matchInfo[$row['email']] = $row;
        }
    }

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Encryption setup
define('ENCRYPTION_KEY', '0123456789abcdef0123456789abcdef');
define('ENCRYPTION_IV', '1234567890abcdef');

function encryptEmail($email) {
    return urlencode(base64_encode(openssl_encrypt(
        $email, 
        'AES-256-CBC', 
        ENCRYPTION_KEY, 
        OPENSSL_RAW_DATA, 
        ENCRYPTION_IV
    )));
}

$qrLink = "https://thegroup11.com/user/verify_match.php?data=" . encryptEmail($_SESSION['email']);
$successMessage = isset($_GET['feedback']) && $_GET['feedback'] === 'success' ? 'Feedback submitted successfully!' : '';
$errorMessage = isset($_GET['error']) ? 'Error: ' . htmlspecialchars($_GET['error']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SparkUp - Dashboard</title>
    <link rel="stylesheet" href="styles-registration.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        .dashboard-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1rem;
            background: rgba(0,0,0,0.2);
            border-radius: 15px;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 2rem;
            color: #fff;
            flex-shrink: 0;
        }
        
        .user-details {
            flex-grow: 1;
        }
        
        .qr-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: rgba(0,0,0,0.2);
            border-radius: 15px;
        }
        
        #qrcode {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin: 1.5rem 0;
            box-shadow: 0 0 20px rgba(114, 137, 218, 0.3);
        }
        
        .qr-instructions {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #ccc;
        }
        
        .scanner-section {
            margin-bottom: 2rem;
        }
        
        .btn-scan {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 15px 30px;
            margin: 1rem 0;
            border-radius: 30px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Orbitron', sans-serif;
        }
        
        .btn-scan:hover {
            background: #5f73bc;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(114, 137, 218, 0.3);
        }
        
        #qr-reader {
            width: 100%;
            max-width: 500px;
            margin: 1rem auto;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .matches-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .match-card {
            position: relative;
            padding: 1.5rem;
            border-radius: 15px;
            background: var(--card-bg);
            border: 2px solid #444;
            overflow: hidden;
            transition: all 0.5s ease;
        }
        
        .match-card.hidden {
            background: rgba(0, 0, 0, 0.3);
            cursor: not-allowed;
        }
        
        .match-card.revealed {
            border-color: var(--primary-color);
            animation: revealGlow 2s infinite alternate;
        }
        
        @keyframes revealGlow {
            from { box-shadow: 0 0 5px rgba(114, 137, 218, 0.3); }
            to { box-shadow: 0 0 20px rgba(114, 137, 218, 0.7); }
        }
        
        .match-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-color);
            margin: 0 auto 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            color: #fff;
        }
        
        .match-card.hidden .match-avatar {
            background: #666;
        }
        
        .match-card.hidden .match-content {
            filter: blur(8px);
            user-select: none;
            pointer-events: none;
        }
        
        .match-card.hidden .lock-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2rem;
            color: #fff;
            z-index: 10;
        }
        
        .match-name {
            font-size: 1.2rem;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        
        .match-email {
            font-size: 0.9rem;
            text-align: center;
            color: #aaa;
            margin-bottom: 1rem;
        }
        
        .match-status {
            text-align: center;
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-block;
            margin: 0 auto;
            width: auto;
        }
        
        .status-completed {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
        }
        
        .notification {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            text-align: center;
            animation: fadeIn 0.5s;
        }
        
        .notification.success {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
        }
        
        .notification.error {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
        }
        
        .logout-btn {
            margin-top: 2rem;
            text-align: center;
        }
        
        .hidden-text {
            height: 20px;
            width: 80%;
            background: #444;
            margin: 0.5rem auto;
            border-radius: 4px;
        }
        
        @media (max-width: 768px) {
            .matches-container {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
    </style>
</head>
<body>
    
    <div class="container">
        <div class="dashboard-container">
            <h1>Your Dashboard</h1>
            
            <?php if($successMessage): ?>
            <div class="notification success">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
            <?php endif; ?>
            
            <?php if($errorMessage): ?>
            <div class="notification error">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
            <?php endif; ?>
            
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo substr($userName, 0, 1); ?>
                </div>
                <div class="user-details">
                    <h2><?php echo htmlspecialchars($userName); ?></h2>
                    <p><?php echo htmlspecialchars($_SESSION['email']); ?></p>
                </div>
            </div>
            
            <div class="qr-section">
                <h2>Your Match QR Code</h2>
                <p>Have your match scan this QR code to start your conversation!</p>
                <div id="qrcode"></div>
                <div class="qr-instructions">
                    <p>This QR contains your encrypted information</p>
                </div>
            </div>
            
            <div class="scanner-section">
                <h2>Scan a Match</h2>
                <p>Click the button below to scan your match's QR code</p>
                <button id="startScanBtn" class="btn-scan">Start QR Scanner</button>
                <div id="qr-reader"></div>
                <p id="qr-result" style="text-align: center; margin-top: 1rem;"></p>
            </div>
            
            <h2>Your Matches</h2>
            <p>Meet and chat with your matches to reveal their information</p>
            
            <div class="matches-container">
                <?php foreach($matches as $index => $matchEmail): 
                    $isPlaceholder = is_null($matchEmail);
                    $isVisible = !$isPlaceholder && in_array($matchEmail, $visibleMatches);
                    
                    if ($isPlaceholder) {
                        $matchName = 'Hidden Meetup #' . ($index + 1);
                        $matchInitial = '?';
                    } else {
                        $matchName = $isVisible && isset($matchInfo[$matchEmail]) 
                            ? $matchInfo[$matchEmail]['first_name'] . ' ' . $matchInfo[$matchEmail]['last_name']
                            : 'Hidden Match ' . ($index + 1);
                        $matchInitial = $isVisible && isset($matchInfo[$matchEmail])
                            ? substr($matchInfo[$matchEmail]['first_name'], 0, 1)
                            : '?';
                    }
                ?>
                <div class="match-card <?php echo $isVisible ? 'revealed' : 'hidden'; ?>">
                    <?php if(!$isVisible): ?>
                    <div class="lock-icon">🔒</div>
                    <?php endif; ?>
                    
                    <div class="match-avatar">
                        <?php echo htmlspecialchars($matchInitial); ?>
                    </div>
                    
                    <div class="match-content">
                        <div class="match-name">
                            <?php echo htmlspecialchars($matchName); ?>
                        </div>
                        
                        <?php if($isVisible): ?>
                            <div class="match-email">
                                <?php echo htmlspecialchars($matchEmail); ?>
                            </div>
                            <div style="text-align: center;">
                                <span class="match-status status-completed">Conversation Completed</span>
                            </div>
                        <?php else: ?>
                            <div class="hidden-text"></div>
                            <div class="hidden-text" style="width: 60%;"></div>
                            <p class="match-hint">Scan QR codes to reveal your match!</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="logout-btn">
                <a href="logout.php" class="btn">Logout</a>
            </div>
        </div>
    </div>

    <script>
        // Generate QR code
        const qrLink = "<?php echo $qrLink; ?>";
        new QRCode(document.getElementById("qrcode"), {
            text: qrLink,
            width: 200,
            height: 200,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        // Scanner functionality
        const startScanBtn = document.getElementById('startScanBtn');
        const qrReader = document.getElementById('qr-reader');
        const qrResult = document.getElementById('qr-result');
        let html5QrCode;

        startScanBtn.addEventListener('click', () => {
            // Show scanning UI
            qrReader.style.display = 'block';
            qrResult.textContent = 'Scanning...';
            startScanBtn.disabled = true;
            
            html5QrCode = new Html5Qrcode("qr-reader");
            
            html5QrCode.start(
                { facingMode: "environment" }, // Use back camera if available
                { 
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                },
                (decodedText) => {
                    // QR Code scanned successfully
                    qrResult.textContent = 'QR code detected! Verifying...';
                    
                    // Extract data parameter from URL
                    let qrData;
                    try {
                        const scannedUrl = new URL(decodedText);
                        qrData = scannedUrl.searchParams.get('data');
                        if (!qrData) {
                            // If not in URL params, try to use the whole text as encrypted data
                            qrData = decodedText;
                        }
                    } catch (e) {
                        // If not a valid URL, use whole text
                        qrData = decodedText;
                    }

                    // Send to verification API
                    fetch('verify_match.php', {
                        method: 'POST',
                        body: JSON.stringify({ 
                            qrData: qrData,
                            userEmail: "<?php echo $_SESSION['email']; ?>"
                        }),
                        headers: { 'Content-Type': 'application/json' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            qrResult.innerHTML = '<span style="color: #4CAF50;">Match verified! Redirecting...</span>';
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1000);
                        } else {
                            qrResult.innerHTML = '<span style="color: #f44336;">Invalid match! ' + (data.error || '') + '</span>';
                            setTimeout(() => {
                                stopScanner();
                            }, 2000);
                        }
                    })
                    .catch(error => {
                        qrResult.innerHTML = '<span style="color: #f44336;">Verification failed! Please try again.</span>';
                        console.error("Verification error:", error);
                        setTimeout(() => {
                            stopScanner();
                        }, 2000);
                    });
                    
                    // Stop scanning after successful scan
                    stopScanner();
                },
                (errorMessage) => {
                    // QR scan error (usually non-critical)
                    console.log("QR scan error:", errorMessage);
                }
            ).catch((err) => {
                qrResult.innerHTML = '<span style="color: #f44336;">Failed to start scanner! Please check camera permissions.</span>';
                console.error("Scanner initialization failed:", err);
                stopScanner();
            });
        });
        
        // Function to stop the QR scanner
        function stopScanner() {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => {
                    startScanBtn.disabled = false;
                }).catch(err => {
                    console.error("Failed to stop scanner:", err);
                    startScanBtn.disabled = false;
                });
            } else {
                startScanBtn.disabled = false;
            }
        }
        
        // Handle hidden matches animation
        const hiddenMatches = document.querySelectorAll('.match-card.hidden');
        hiddenMatches.forEach(match => {
            match.addEventListener('click', () => {
                match.classList.add('error-shake');
                setTimeout(() => {
                    match.classList.remove('error-shake');
                }, 500);
            });
        });

        // Poll for QR code scans when QR code is displayed
        let scanCheckInterval;
        let consecutiveDetections = 0;
        
        function startScanPolling() {
            // Clear any existing poll interval
            if (scanCheckInterval) clearInterval(scanCheckInterval);
            
            // Add visual indicator that polling is active
            const pollingIndicator = document.createElement('div');
            pollingIndicator.id = 'polling-indicator';
            pollingIndicator.style.position = 'fixed';
            pollingIndicator.style.bottom = '10px';
            pollingIndicator.style.right = '10px';
            pollingIndicator.style.padding = '5px 10px';
            pollingIndicator.style.background = 'rgba(0,0,0,0.5)';
            pollingIndicator.style.borderRadius = '5px';
            pollingIndicator.style.fontSize = '12px';
            pollingIndicator.textContent = 'Listening for QR scans...';
            document.body.appendChild(pollingIndicator);
            
            // Start new polling
            scanCheckInterval = setInterval(function() {
                fetch('check_qr_status.php?t=' + Date.now()) // Add timestamp to prevent caching
                    .then(response => response.json())
                    .then(data => {
                        // Update indicator with last check time
                        const indicator = document.getElementById('polling-indicator');
                        if (indicator) {
                            indicator.textContent = 'Last check: ' + new Date().toLocaleTimeString();
                            
                            // Flash the indicator to show activity
                            indicator.style.background = 'rgba(114, 137, 218, 0.7)';
                            setTimeout(() => {
                                indicator.style.background = 'rgba(0,0,0,0.5)';
                            }, 200);
                        }
                        
                        // Only redirect if scan is detected
                        if (data.scanned) {
                            // Stop polling
                            clearInterval(scanCheckInterval);
                            
                            // Show notification
                            const notification = document.createElement('div');
                            notification.className = 'notification success';
                            notification.style.position = 'fixed';
                            notification.style.top = '20px';
                            notification.style.left = '50%';
                            notification.style.transform = 'translateX(-50%)';
                            notification.style.zIndex = '9999';
                            notification.innerHTML = 'Your QR code was scanned! Redirecting to conversation...';
                            document.body.appendChild(notification);
                            
                            // Set a flag in sessionStorage to prevent loops
                            sessionStorage.setItem('redirecting_to_timer', 'true');
                            
                            // Redirect after a short delay
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1500);
                        }
                    })
                    .catch(error => {
                        console.error('Error checking scan status:', error);
                        // Update indicator on error
                        const indicator = document.getElementById('polling-indicator');
                        if (indicator) {
                            indicator.textContent = 'Error: ' + error.message;
                            indicator.style.background = 'rgba(244, 67, 54, 0.7)'; // Red background
                        }
                    });
            }, 2000); // Check every 2 seconds for better responsiveness
        }
        
        // Only start polling if we're not coming from a timer page redirect
        if (document.getElementById('qrcode') && !sessionStorage.getItem('redirecting_to_timer')) {
            startScanPolling();
        } else {
            // Clear the redirect flag after we've used it
            sessionStorage.removeItem('redirecting_to_timer');
        }
        
    </script>
</body>
</html>