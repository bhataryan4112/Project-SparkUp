<?php
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$host = 'localhost';
$username = "";
$password = "";
$dbname = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $userEmail = $_SESSION['email'];
    
    // Get user's assignments
    $stmt = $pdo->prepare("SELECT a.*, t.classroom, t.table_number 
                          FROM table_assignments a
                          JOIN classroom_tables t ON a.table_id = t.id
                          WHERE (a.user_email_1 = ? OR a.user_email_2 = ?)
                          AND a.status IN ('pending', 'active')
                          ORDER BY a.time_slot");
    $stmt->execute([$userEmail, $userEmail]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Helper function to get user display name
    function getUserName($email, $pdo) {
        $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            return $user['first_name'] . ' ' . $user['last_name'];
        } else {
            return explode('@', $email)[0];
        }
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
    <title>SparkUp - Find Your Table</title>
    <link rel="stylesheet" href="styles-registration.css">
    <style>
        .find-table-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .table-card {
            background: rgba(0,0,0,0.2);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .table-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .active-now {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #4CAF50;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            animation: pulse 2s infinite;
        }
        
        .time-slot {
            display: inline-block;
            background: rgba(114, 137, 218, 0.2);
            color: var(--primary-color);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .table-location {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        
        .match-info {
            display: flex;
            align-items: center;
            margin-top: 1rem;
        }
        
        .match-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.2rem;
            color: #fff;
            margin-right: 1rem;
        }
        
        .no-tables {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .back-btn {
            display: block;
            margin: 2rem auto;
            width: fit-content;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="find-table-container">
            <h1>Your Table Assignments</h1>
            
            <?php if (empty($assignments)): ?>
            <div class="no-tables">
                <p>You don't have any table assignments yet.</p>
                <p>Check back later or ask the event organizer for assistance.</p>
            </div>
            <?php else: ?>
                <p>Here are your assigned tables for your speed dating matches:</p>
                
                <?php foreach ($assignments as $assignment): 
                    $isActive = $assignment['status'] === 'active';
                    $matchEmail = $assignment['user_email_1'] === $userEmail ? 
                                  $assignment['user_email_2'] : 
                                  $assignment['user_email_1'];
                    $matchName = getUserName($matchEmail, $pdo);
                    $matchInitial = substr($matchName, 0, 1);
                ?>
                <div class="table-card">
                    <?php if ($isActive): ?>
                    <div class="active-now">Active Now</div>
                    <?php endif; ?>
                    
                    <div class="time-slot">
                        <?php echo htmlspecialchars($assignment['time_slot']); ?>
                    </div>
                    
                    <div class="table-location">
                        Room <?php echo htmlspecialchars($assignment['classroom']); ?>, 
                        Table <?php echo htmlspecialchars($assignment['table_number']); ?>
                    </div>
                    
                    <div>Your match:</div>
                    <div class="match-info">
                        <div class="match-avatar"><?php echo htmlspecialchars($matchInitial); ?></div>
                        <div>
                            <strong><?php echo htmlspecialchars($matchName); ?></strong>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <a href="dashboard.php" class="btn back-btn">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
