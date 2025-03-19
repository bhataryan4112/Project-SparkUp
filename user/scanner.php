<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

// Get user email from session
$userEmail = $_SESSION['email'];

// Database connection to get user details
$host = 'localhost';
$username = "";
$password = "";
$dbname = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get user info if available
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE email = :email");
    $stmt->bindParam(':email', $userEmail);
    $stmt->execute();
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    $userName = $userInfo ? $userInfo['first_name'] . ' ' . $userInfo['last_name'] : 'User';
} catch (PDOException $e) {
    $userName = 'User';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SparkUp - QR Scanner</title>
    <link rel="stylesheet" href="styles-registration.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        .scanner-container {
            max-width: 500px;
            margin: 0 auto;
            text-align: center;
        }
        
        #qr-reader {
            width: 100%;
            border-radius: 20px;
            overflow: hidden;
            margin: 2rem 0;
            position: relative;
        }
        
        #qr-reader::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 3px solid var(--primary-color);
            border-radius: 20px;
            z-index: 10;
            pointer-events: none;
        }
        
        .scanner-status {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 10px;
            font-weight: 500;
        }
        
        .status-idle {
            background: rgba(0, 0, 0, 0.2);
        }
        
        .status-scanning {
            background: rgba(255, 193, 7, 0.2);
            color: #FFC107;
        }
        
        .status-success {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
        }
        
        .status-error {
            background: rgba(244, 67, 54, 0.2);
            color: #f44336;
        }
        
        .scanner-btn {
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
            display: block;
            width: 100%;
            max-width: 300px;
            margin: 1rem auto;
        }
        
        .scanner-btn:hover {
            background: #5f73bc;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(114, 137, 218, 0.3);
        }
        
        .scanner-btn:disabled {
            background: #666;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            color: #fff;
            flex-shrink: 0;
        }
        
        .user-details {
            flex-grow: 1;
            text-align: left;
        }
        
        .instructions {
            text-align: left;
            margin: 2rem 0;
        }
        
        .instructions ol {
            margin-left: 1.5rem;
            margin-top: 1rem;
        }
        
        .instructions li {
            margin-bottom: 0.8rem;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
            vertical-align: middle;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .back-btn {
            display: inline-block;
            margin-top: 2rem;
            color: var(--primary-color);
            text-decoration: none;
            padding: 10px 20px;
            border: 2px solid var(--primary-color);
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body>
    <div class="progress-container">
        <div class="progress-bar" style="width: 100%;"></div>
    </div>
    
    <div class="container">
        <div class="scanner-container">
            <h1>SparkUp QR Scanner</h1>
            
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo substr($userName, 0, 1); ?>
                </div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($userName); ?></h3>
                    <p><?php echo htmlspecialchars($userEmail); ?></p>
                </div>
            </div>
            
            <div class="instructions">
                <h2>How to scan your match's QR code:</h2>
                <ol>
                    <li>Click "Start Scanner" below</li>
                    <li>Point your camera at your match's QR code</li>
                    <li>Hold the QR code steady until it's detected</li>
                    <li>If you're a match, you'll be redirected to the timer page</li>
                </ol>
            </div>
            
            <div id="scanner-status" class="scanner-status status-idle">
                Ready to scan
            </div>
            
            <button id="startBtn" class="scanner-btn">Start Scanner</button>
            <button id="stopBtn" class="scanner-btn" style="display: none; background: #ff4444;">Stop Scanner</button>
            
            <div id="qr-reader" style="display: none;"></div>
            
            <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
        </div>
    </div>

    <script>
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');
        const scannerStatus = document.getElementById('scanner-status');
        const qrReader = document.getElementById('qr-reader');
        let html5QrCode;
        
        function updateStatus(message, type) {
            scannerStatus.textContent = message;
            scannerStatus.className = `scanner-status status-${type}`;
        }
        
        startBtn.addEventListener('click', () => {
            // Show scanner and stop button, hide start button
            qrReader.style.display = 'block';
            stopBtn.style.display = 'block';
            startBtn.style.display = 'none';
            
            updateStatus('Initializing scanner...', 'scanning');
            
            html5QrCode = new Html5Qrcode("qr-reader");
            
            html5QrCode.start(
                { facingMode: "environment" }, // Use back camera if available
                { 
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                },
                (decodedText) => {
                    // Success callback - QR code detected
                    console.log(`QR Code detected: ${decodedText}`);
                    updateStatus('QR code detected! Verifying...', 'scanning');
                    
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
                    
                    // Show processing indicator
                    stopBtn.innerHTML = '<span class="loading-spinner"></span> Verifying...';
                    stopBtn.disabled = true;
                    
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
                            updateStatus('Match verified! Redirecting...', 'success');
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1000);
                        } else {
                            updateStatus('Invalid match: ' + (data.error || 'Not matched'), 'error');
                            stopScanner();
                            setTimeout(() => {
                                updateStatus('Ready to scan', 'idle');
                                startBtn.style.display = 'block';
                                stopBtn.style.display = 'none';
                            }, 3000);
                        }
                    })
                    .catch(error => {
                        console.error("Verification error:", error);
                        updateStatus('Verification failed! Please try again.', 'error');
                        stopScanner();
                        setTimeout(() => {
                            updateStatus('Ready to scan', 'idle');
                            startBtn.style.display = 'block';
                            stopBtn.style.display = 'none';
                        }, 3000);
                    });
                },
                (errorMessage) => {
                    // Error callback - non-critical errors
                    console.log(`QR Code scanning error: ${errorMessage}`);
                }
            ).catch((err) => {
                // Critical error in starting the scanner
                console.error("Scanner initialization failed:", err);
                updateStatus('Failed to start scanner! Check camera permissions.', 'error');
                startBtn.style.display = 'block';
                stopBtn.style.display = 'none';
            });
            
            updateStatus('Scanner active - aim at a QR code', 'scanning');
        });
        
        stopBtn.addEventListener('click', () => {
            stopScanner();
            updateStatus('Scanner stopped', 'idle');
            startBtn.style.display = 'block';
            stopBtn.style.display = 'none';
        });
        
        function stopScanner() {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().catch(err => {
                    console.error("Failed to stop scanner:", err);
                });
            }
            qrReader.style.display = 'none';
            stopBtn.innerHTML = 'Stop Scanner';
            stopBtn.disabled = false;
        }
    </script>
</body>
</html>
