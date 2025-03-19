<?php
session_start();
require_once 'encrypt.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

// Get user email from session
$userEmail = $_SESSION['email'];

// Create the encrypted data for the QR code
$encryptedData = encryptData($userEmail);

// Generate the full link for QR code
$qrLink = "https://thegroup11.com/user/verify_match.php?data=" . $encryptedData;

// Database connection to get user details if needed
$host = 'localhost';
$username = "";
$password = "";
$dbname = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
    <title>SparkUp - Your Match QR Code</title>
    <link rel="stylesheet" href="styles-registration.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        .qr-container {
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        #qrcode {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(114, 137, 218, 0.4);
            margin: 2rem auto;
            width: fit-content;
            transition: transform 0.3s ease;
            animation: float 3s ease-in-out infinite;
        }
        
        #qrcode:hover {
            transform: scale(1.05);
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .instructions {
            margin: 2rem 0;
            padding: 1.5rem;
            background: var(--card-bg);
            border-radius: 12px;
            text-align: left;
        }
        
        .instructions ol {
            margin-left: 1.5rem;
            margin-top: 1rem;
        }
        
        .instructions li {
            margin-bottom: 0.8rem;
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
        }
        
        .download-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-family: 'Orbitron', sans-serif;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        
        .download-btn:hover {
            background: #5f73bc;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(114, 137, 218, 0.3);
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
        <div class="qr-container">
            <h1>Your SparkUp QR Code</h1>
            
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo substr($userName, 0, 1); ?>
                </div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($userName); ?></h3>
                    <p><?php echo htmlspecialchars($userEmail); ?></p>
                </div>
            </div>
            
            <div id="qrcode"></div>
            
            <button id="downloadBtn" class="download-btn">Download QR Code</button>
            
            <div class="instructions">
                <h2>How to use your QR Code:</h2>
                <ol>
                    <li>Show this QR code to your match.</li>
                    <li>Have them scan it with the SparkUp scanner or any QR code scanner.</li>
                    <li>If you're both matched in our system, a 5-minute timer will start for your conversation.</li>
                    <li>After the conversation, provide feedback to reveal their contact details.</li>
                </ol>
            </div>
            
            <a href="/user" class="back-btn">Back to Dashboard</a>
        </div>
    </div>

    <script>
        // Generate QR code
        const qrLink = "<?php echo $qrLink; ?>";
        const qrcode = new QRCode(document.getElementById("qrcode"), {
            text: qrLink,
            width: 256,
            height: 256,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        // Download QR code functionality
        document.getElementById('downloadBtn').addEventListener('click', function() {
            // Get the QR code as an image
            const qrImg = document.querySelector('#qrcode img');
            
            // Create a canvas
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.width = qrImg.width + 40; // Add padding
            canvas.height = qrImg.height + 100; // Add space for text
            
            // Fill background
            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, canvas.width, canvas.height);
            
            // Add QR code image
            context.drawImage(qrImg, 20, 20);
            
            // Add text
            context.font = '14px Arial';
            context.fillStyle = '#000000';
            context.textAlign = 'center';
            context.fillText('SparkUp: <?php echo htmlspecialchars($userName); ?>', canvas.width/2, qrImg.height + 40);
            context.font = '12px Arial';
            context.fillText('Scan to connect!', canvas.width/2, qrImg.height + 65);
            
            // Create download link
            const link = document.createElement('a');
            link.download = 'sparkup-qr-<?php echo strtolower(str_replace(" ", "-", $userName)); ?>.png';
            link.href = canvas.toDataURL('image/png');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        // Poll for QR code scans
        let scanCheckInterval = setInterval(function() {
            fetch('check_qr_status.php')
                .then(response => response.json())
                .then(data => {
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
                        notification.style.padding = '15px 25px';
                        notification.style.borderRadius = '10px';
                        notification.style.background = 'rgba(76, 175, 80, 0.9)';
                        notification.style.color = 'white';
                        notification.style.boxShadow = '0 4px 12px rgba(0,0,0,0.2)';
                        notification.innerHTML = 'QR code scanned! Redirecting to conversation...';
                        document.body.appendChild(notification);
                        
                        // Redirect after a short delay
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                    }
                })
                .catch(error => console.error('Error checking scan status:', error));
        }, 3000); // Check every 3 seconds

        // Clean up interval when page is unloaded
        window.addEventListener('beforeunload', function() {
            clearInterval(scanCheckInterval);
        });
    </script>
</body>
</html>
