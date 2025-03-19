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
    
    $matchEmail = urldecode($_GET['match']);
    
    // Verify this is a valid match
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
    
    // Get match user info
    $userStmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE email = :email");
    $userStmt->bindParam(':email', $matchEmail);
    $userStmt->execute();
    $matchUser = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    $matchName = $matchUser ? $matchUser['first_name'] . ' ' . $matchUser['last_name'] : 'Your Match';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Process the feedback submission
        $rating = $_POST['rating'] ?? 0;
        $comments = $_POST['comments'] ?? '';
        $meetAgain = isset($_POST['meet_again']) ? 1 : 0;
        
        // Insert feedback into the database
        $stmt = $pdo->prepare("UPDATE completed_matches 
                              SET feedback_rating = :rating, 
                                  feedback_comments = :comments, 
                                  meet_again = :meetAgain, 
                                  feedback_provided = 1,
                                  end_time = NOW()
                              WHERE user_email = :userEmail 
                              AND match_email = :matchEmail");
                              
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':comments', $comments);
        $stmt->bindParam(':meetAgain', $meetAgain);
        $stmt->bindParam(':userEmail', $_SESSION['email']);
        $stmt->bindParam(':matchEmail', $matchEmail);
        $stmt->execute();

        // Make sure to clear any session variables that might cause loops
        unset($_SESSION['redirect_processed']);
        
        // Redirect back to dashboard with a success parameter (not a query string that'll trigger polling)
        header('Location: /user?feedback=success&t=' . time());
        exit();
    }
    
    // Check if conversation was ended early by match
    $endedByMatch = isset($_GET['ended_by']) && $_GET['ended_by'] === 'match';

    // Check if this user ended the conversation early
    $endedByUser = false;
    $stmt = $pdo->prepare("SELECT ended_early, ended_by FROM completed_matches 
                          WHERE user_email = :userEmail 
                          AND match_email = :matchEmail");
    $stmt->execute([
        ':userEmail' => $_SESSION['email'],
        ':matchEmail' => $matchEmail
    ]);
    $conversationStatus = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($conversationStatus && $conversationStatus['ended_early']) {
        $endedByUser = $conversationStatus['ended_by'] === $_SESSION['email'];
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SparkUp - Feedback</title>
    <link rel="stylesheet" href="styles-registration.css">
    <style>
        .feedback-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .rating-group {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            margin: 2rem 0;
        }
        
        .rating-group input {
            display: none;
        }
        
        .rating-group label {
            cursor: pointer;
            width: 50px;
            height: 50px;
            background-color: #333;
            margin: 0 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            border-radius: 50%;
            color: #999;
            transition: all 0.3s ease;
        }
        
        .rating-group label:hover,
        .rating-group label:hover ~ label,
        .rating-group input:checked ~ label {
            background-color: var(--primary-color);
            color: white;
            transform: scale(1.1);
        }
        
        /* Star 5 - Excellent */
        .rating-group input:checked ~ label[for="star5"],
        .rating-group label[for="star5"]:hover,
        .rating-group label[for="star5"]:hover ~ label {
            background-color: #4CAF50;
        }
        
        /* Star 4 - Good */
        .rating-group input:checked ~ label[for="star4"],
        .rating-group label[for="star4"]:hover,
        .rating-group label[for="star4"]:hover ~ label {
            background-color: #8BC34A;
        }
        
        /* Star 3 - Average */
        .rating-group input:checked ~ label[for="star3"],
        .rating-group label[for="star3"]:hover,
        .rating-group label[for="star3"]:hover ~ label {
            background-color: #FFC107;
        }
        
        /* Star 2 - Bad */
        .rating-group input:checked ~ label[for="star2"],
        .rating-group label[for="star2"]:hover,
        .rating-group label[for="star2"]:hover ~ label {
            background-color: #FF9800;
        }
        
        /* Star 1 - Terrible */
        .rating-group input:checked ~ label[for="star1"],
        .rating-group label[for="star1"]:hover,
        .rating-group label[for="star1"]:hover ~ label {
            background-color: #F44336;
        }
        
        .comments-container {
            position: relative;
            margin: 2rem 0;
        }
        
        .comments-textarea {
            width: 100%;
            min-height: 150px;
            background: rgba(0, 0, 0, 0.5);
            border: 2px solid #666;
            color: white;
            padding: 1rem;
            border-radius: 12px;
            font-family: 'SF Pro Display', sans-serif;
            resize: vertical;
            transition: all 0.3s ease;
        }
        
        .comments-textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 10px rgba(114, 137, 218, 0.3);
        }
        
        .match-again-container {
            margin: 2rem 0;
            text-align: center;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #333;
            transition: .4s;
            border-radius: 34px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: #fff;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: var(--primary-color);
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }
        
        .match-info {
            text-align: center;
            margin-bottom: 2rem;
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
        
        .submit-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 15px 40px;
            margin-top: 2rem;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            background: #5f73bc;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(114, 137, 218, 0.3);
        }
    </style>
</head>
<body>
    <div class="progress-container">
        <div class="progress-bar" style="width: 100%;"></div>
    </div>
    
    <div class="container">
        <div class="feedback-container">
            <h1>Match Feedback</h1>
            
            <?php if($endedByMatch): ?>
            <div class="notification warning" style="background: rgba(255, 152, 0, 0.2); color: #FF9800; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                <p><strong>Note:</strong> Your match ended the conversation early.</p>
            </div>
            <?php elseif($endedByUser): ?>
            <div class="notification info" style="background: rgba(33, 150, 243, 0.2); color: #2196F3; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                <p><strong>Note:</strong> You ended this conversation early.</p>
            </div>
            <?php endif; ?>
            
            <div class="match-info">
                <div class="match-avatar">
                    <?php echo substr($matchName, 0, 1); ?>
                </div>
                <h3>How was your conversation with <?php echo htmlspecialchars($matchName); ?>?</h3>
            </div>
            
            <form method="POST">
                <div class="form-section">
                    <h2>Rate Your Experience</h2>
                    <div class="rating-group">
                        <input type="radio" id="star5" name="rating" value="5" required />
                        <label for="star5">★</label>
                        <input type="radio" id="star4" name="rating" value="4" />
                        <label for="star4">★</label>
                        <input type="radio" id="star3" name="rating" value="3" />
                        <label for="star3">★</label>
                        <input type="radio" id="star2" name="rating" value="2" />
                        <label for="star2">★</label>
                        <input type="radio" id="star1" name="rating" value="1" />
                        <label for="star1">★</label>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Comments</h2>
                    <div class="comments-container">
                        <textarea class="comments-textarea" name="comments" placeholder="Share your thoughts about this match..."></textarea>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2>Would you like to meet this person again?</h2>
                    <div class="match-again-container">
                        <label class="toggle-switch">
                            <input type="checkbox" name="meet_again">
                            <span class="toggle-slider"></span>
                        </label>
                        <p id="toggle-text">No</p>
                    </div>
                </div>
                
                <div style="text-align: center;">
                    <button type="submit" class="submit-btn">Submit Feedback</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const toggleSwitch = document.querySelector('.toggle-switch input');
        const toggleText = document.getElementById('toggle-text');
        
        toggleSwitch.addEventListener('change', function() {
            toggleText.textContent = this.checked ? 'Yes' : 'No';
            
            // Animation for text change
            toggleText.style.animation = 'none';
            setTimeout(() => {
                toggleText.style.animation = 'fadeIn 0.5s';
            }, 10);
        });
        
        // Star rating hover effect
        const stars = document.querySelectorAll('.rating-group input');
        const ratingLabels = [
            "Poor", "Below average", "Average", 
            "Good", "Excellent"
        ];
        
        stars.forEach((star, index) => {
            star.addEventListener('change', () => {
                // Create or update rating text
                let ratingText = document.getElementById('rating-text');
                if (!ratingText) {
                    ratingText = document.createElement('p');
                    ratingText.id = 'rating-text';
                    ratingText.style.textAlign = 'center';
                    ratingText.style.marginTop = '10px';
                    document.querySelector('.rating-group').after(ratingText);
                }
                ratingText.textContent = ratingLabels[star.value - 1];
                
                // Animation for rating selection
                ratingText.style.animation = 'none';
                setTimeout(() => {
                    ratingText.style.animation = 'fadeIn 0.5s';
                }, 10);
            });
        });
    </script>
</body>
</html>