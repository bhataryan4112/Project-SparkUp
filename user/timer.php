<?php
session_start();
// Clear any redirect processing flag that might cause loops
unset($_SESSION['redirect_processed']);

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

    // Make sure we have the match parameter
    if (!isset($_GET['match'])) {
        header('Location: /user');
        exit();
    }

    $matchEmail = urldecode($_GET['match']);
    
    // Check if this is a valid match
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM completed_matches 
                               WHERE user_email = :userEmail 
                               AND match_email = :matchEmail");
    $checkStmt->bindParam(':userEmail', $_SESSION['email']);
    $checkStmt->bindParam(':matchEmail', $matchEmail);
    $checkStmt->execute();
    
    if ($checkStmt->fetchColumn() == 0) {
        // Not a valid match
        header('Location: /user?error=invalid_match');
        exit();
    }
    
    // Update the timer session if needed
    $updateStmt = $pdo->prepare("UPDATE completed_matches 
                                SET start_time = COALESCE(start_time, NOW()) 
                                WHERE user_email = :userEmail 
                                AND match_email = :matchEmail 
                                AND start_time IS NULL");
    $updateStmt->bindParam(':userEmail', $_SESSION['email']);
    $updateStmt->bindParam(':matchEmail', $matchEmail);
    $updateStmt->execute();
    
    // Get user info for the match
    $userStmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE email = :email");
    $userStmt->bindParam(':email', $matchEmail);
    $userStmt->execute();
    $matchUser = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    $matchName = $matchUser ? $matchUser['first_name'] . ' ' . $matchUser['last_name'] : 'Your Match';
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SparkUp - Timer</title>
    <link rel="stylesheet" href="styles-registration.css">
    <style>
        .timer-container {
            text-align: center;
            padding: 2rem;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .circular-timer {
            position: relative;
            width: 300px;
            height: 300px;
            margin: 2rem auto;
        }
        
        .timer-circle {
            fill: none;
            stroke: #333;
            stroke-width: 10;
        }
        
        .timer-progress {
            fill: none;
            stroke: var(--primary-color);
            stroke-width: 10;
            stroke-linecap: round;
            transform: rotate(-90deg);
            transform-origin: center;
            transition: stroke-dashoffset 1s linear;
            stroke-dasharray: 880;  /* 2 * PI * (140 - 5) */
        }
        
        .timer-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 3rem;
            font-family: 'Orbitron', sans-serif;
            color: #fff;
        }
        
        .match-info {
            margin-top: 2rem;
            padding: 1.5rem;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            animation: glow 2s infinite alternate;
        }
        
        .match-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--primary-color);
            margin: 0 auto 1rem;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 2rem;
            color: #fff;
        }
        
        .btn-end {
            background-color: #ff4444;
            border: none;
            padding: 15px 40px;
            margin-top: 2rem;
            border-radius: 30px;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes glow {
            from { box-shadow: 0 0 5px rgba(114, 137, 218, 0.2); }
            to { box-shadow: 0 0 20px rgba(114, 137, 218, 0.6); }
        }
        
        .ended-early-notification {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(244, 67, 54, 0.95);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            z-index: 100;
            box-shadow: 0 0 30px rgba(0,0,0,0.5);
            animation: fadeIn 0.5s;
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(5px);
            z-index: 50;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="progress-container">
        <div class="progress-bar"></div>
    </div>
    
    <div class="container">
        <div class="timer-container">
            <h1>Chat Timer</h1>
            <p>You have 5 minutes to connect with your match!</p>
            
            <div class="match-info">
                <div class="match-avatar">
                    <?php echo substr($matchName, 0, 1); ?>
                </div>
                <h3><?php echo htmlspecialchars($matchName); ?></h3>
            </div>
            
            <div class="circular-timer">
                <svg width="300" height="300" viewBox="0 0 300 300">
                    <circle class="timer-circle" cx="150" cy="150" r="140"></circle>
                    <circle class="timer-progress" cx="150" cy="150" r="140" stroke-dashoffset="0"></circle>
                </svg>
                <div class="timer-text">05:00</div>
            </div>
            
            <button id="endBtn" class="btn btn-end">End Conversation Early</button>
        </div>
    </div>

    <script>
        // Set up the timer
        let timeLeft = 300; // 5 minutes in seconds
        const initialTime = timeLeft;
        const timerDisplay = document.querySelector('.timer-text');
        const progressCircle = document.querySelector('.timer-progress');
        const endBtn = document.getElementById('endBtn');
        const circumference = 880; // 2 * PI * (140 - 5)
        
        // Update progress bar at the top
        document.querySelector('.progress-bar').style.width = '100%';
        
        // Function to format time
        function formatTime(seconds) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
        }
        
        // Function to update the timer
        function updateTimer() {
            timeLeft--;
            
            // Update text display
            timerDisplay.textContent = formatTime(timeLeft);
            
            // Update circular progress
            const progress = timeLeft / initialTime;
            const dashoffset = circumference * (1 - progress);
            progressCircle.style.strokeDashoffset = dashoffset;
            
            // Update progress bar at top
            document.querySelector('.progress-bar').style.width = `${progress * 100}%`;
            
            // Change colors based on time remaining
            if (timeLeft <= 60) { // Last minute
                progressCircle.style.stroke = '#ff4444';
                document.querySelector('.progress-bar').style.background = 'linear-gradient(90deg, #ff4444, #ff9999, #ff4444)';
            } else if (timeLeft <= 180) { // Last 3 minutes
                progressCircle.style.stroke = '#ffa500';
                document.querySelector('.progress-bar').style.background = 'linear-gradient(90deg, #ffa500, #ffcc80, #ffa500)';
            }
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                redirectToFeedback();
            }
        }
        
        // Function to redirect to feedback page
        function redirectToFeedback(endedByMatch = false) {
            let redirectUrl = "feedback.php?match=<?php echo urlencode($matchEmail); ?>";
            if (endedByMatch) {
                redirectUrl += "&ended_by=match";
            }
            window.location.href = redirectUrl;
        }
        
        // Start the timer
        progressCircle.style.strokeDasharray = circumference;
        const timer = setInterval(updateTimer, 1000);
        
        // End button event listener
        endBtn.addEventListener('click', () => {
            if (confirm("Are you sure you want to end the conversation?")) {
                clearInterval(timer);
                clearInterval(checkStatusInterval); // Clear status check interval
                
                // Call end conversation endpoint
                fetch('../api/table_management.php?action=end_conversation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        user_email: "<?php echo $_SESSION['email']; ?>",
                        match_email: "<?php echo htmlspecialchars($matchEmail); ?>"
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        redirectToFeedback();
                    } else {
                        console.error("Failed to end conversation:", data.error);
                        redirectToFeedback(); // Redirect anyway as fallback
                    }
                })
                .catch(error => {
                    console.error("Error ending conversation:", error);
                    redirectToFeedback(); // Redirect anyway as fallback
                });
            }
        });
        
        // Check if match ended the conversation
        let checkStatusInterval = setInterval(() => {
            fetch('check_conversation_status.php?match=<?php echo urlencode($matchEmail); ?>&t=' + Date.now())
                .then(response => response.json())
                .then(data => {
                    if (data.ended) {
                        // Stop intervals
                        clearInterval(timer);
                        clearInterval(checkStatusInterval);
                        
                        // Show notification that match ended the conversation
                        const overlay = document.createElement('div');
                        overlay.className = 'overlay';
                        document.body.appendChild(overlay);
                        
                        const notification = document.createElement('div');
                        notification.className = 'ended-early-notification';
                        notification.innerHTML = `
                            <h3>Conversation Ended</h3>
                            <p>Your match has ended the conversation early.</p>
                            <p>Redirecting to feedback...</p>
                        `;
                        document.body.appendChild(notification);
                        
                        // Redirect after a short delay
                        setTimeout(() => {
                            redirectToFeedback(true);
                        }, 3000);
                    }
                })
                .catch(error => console.error('Error checking conversation status:', error));
        }, 5000); // Check every 5 seconds
    </script>
</body>
</html>