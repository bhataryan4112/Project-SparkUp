<?php
session_start();
// Debug current session status
error_log("Session ID at assign tables: " . session_id());
error_log("Admin logged in status: " . (isset($_SESSION['admin_logged_in']) ? 'true' : 'false'));

if (!isset($_SESSION['admin_logged_in'])) {
    // Store the requested page before redirecting
    $_SESSION['requested_page'] = 'assign_tables.php';
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
    
    // Get matches
    $stmt = $pdo->query("SELECT * FROM `2025_01_12_00_35_22_matches` ORDER BY `COL 1` ASC");
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get available tables
    $tableStmt = $pdo->query("SELECT * FROM classroom_tables ORDER BY classroom, table_number");
    $tables = $tableStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get existing assignments
    $assignmentStmt = $pdo->query("SELECT * FROM table_assignments WHERE status = 'pending'");
    $existingAssignments = $assignmentStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Process form submission
    $successMessage = '';
    $errorMessage = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign'])) {
        try {
            $pdo->beginTransaction();
            
            foreach ($_POST['assignments'] as $assignment) {
                list($user1, $user2) = explode('|', $assignment['pair']);
                $tableId = $assignment['table_id'];
                $timeSlot = $assignment['time_slot'];
                
                if (empty($tableId) || empty($timeSlot)) continue;
                
                // Check if assignment already exists
                $checkStmt = $pdo->prepare("SELECT id FROM table_assignments 
                                           WHERE ((user_email_1 = ? AND user_email_2 = ?) 
                                           OR (user_email_1 = ? AND user_email_2 = ?))
                                           AND time_slot = ?");
                $checkStmt->execute([$user1, $user2, $user2, $user1, $timeSlot]);
                $existingId = $checkStmt->fetchColumn();
                
                if ($existingId) {
                    // Update existing assignment
                    $updateStmt = $pdo->prepare("UPDATE table_assignments SET table_id = ? WHERE id = ?");
                    $updateStmt->execute([$tableId, $existingId]);
                } else {
                    // Create new assignment
                    $insertStmt = $pdo->prepare("INSERT INTO table_assignments 
                                              (table_id, user_email_1, user_email_2, time_slot, status) 
                                              VALUES (?, ?, ?, ?, 'pending')");
                    $insertStmt->execute([$tableId, $user1, $user2, $timeSlot]);
                }
                
                // Update table status
                $updateTableStmt = $pdo->prepare("UPDATE classroom_tables SET status = 'reserved' WHERE id = ?");
                $updateTableStmt->execute([$tableId]);
            }
            
            $pdo->commit();
            $successMessage = 'Tables assigned successfully!';
        } catch (Exception $e) {
            $pdo->rollBack();
            $errorMessage = 'Error: ' . $e->getMessage();
        }
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Helper function to get user display name
function getUserName($email, $pdo) {
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        return $user['first_name'] . ' ' . $user['last_name'];
    } else {
        // Fall back to email username if user not in users table
        return explode('@', $email)[0];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SparkUp - Assign Tables</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Assign Tables to Matches</h1>
            <a href="table_management.php" class="btn btn-primary">Table Management</a>
        </div>
        
        <?php if($successMessage): ?>
        <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        
        <?php if($errorMessage): ?>
        <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="mb-0">Match Assignments</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="autoAssignBtn">Auto Assign</button>
                        <button type="submit" name="assign" class="btn btn-sm btn-success">Save Assignments</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Match</th>
                                    <th>Table</th>
                                    <th>Time Slot</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rowCount = 0;
                                foreach($matches as $match):
                                    $user = $match['COL 1'];
                                    // Skip header row or invalid entries
                                    if($user === 'Email' || empty($user)) continue;
                                    
                                    // Process each match for the user
                                    for($i = 2; $i <= 6; $i++):
                                        $matchEmail = $match['COL ' . $i];
                                        if(empty($matchEmail)) continue;
                                        
                                        // Don't show duplicate pairs (we'll show A-B but not B-A)
                                        if(strcmp($user, $matchEmail) > 0) continue;
                                        
                                        $rowCount++;
                                        $rowClass = $rowCount % 2 === 0 ? 'table-light' : '';
                                        
                                        // Find existing assignment
                                        $existingAssignment = null;
                                        foreach($existingAssignments as $assignment) {
                                            if(($assignment['user_email_1'] === $user && $assignment['user_email_2'] === $matchEmail) ||
                                               ($assignment['user_email_1'] === $matchEmail && $assignment['user_email_2'] === $user)) {
                                                $existingAssignment = $assignment;
                                                break;
                                            }
                                        }
                                ?>
                                <tr class="<?php echo $rowClass; ?>">
                                    <td><?php echo htmlspecialchars(getUserName($user, $pdo)); ?></td>
                                    <td><?php echo htmlspecialchars(getUserName($matchEmail, $pdo)); ?></td>
                                    <td>
                                        <input type="hidden" name="assignments[<?php echo $rowCount; ?>][pair]" value="<?php echo htmlspecialchars($user . '|' . $matchEmail); ?>">
                                        <select name="assignments[<?php echo $rowCount; ?>][table_id]" class="form-select form-select-sm">
                                            <option value="">Select Table</option>
                                            <?php foreach($tables as $table): ?>
                                            <option value="<?php echo $table['id']; ?>" 
                                                    <?php echo ($existingAssignment && $existingAssignment['table_id'] == $table['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($table['classroom'] . ' - Table ' . $table['table_number']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="assignments[<?php echo $rowCount; ?>][time_slot]" class="form-select form-select-sm">
                                            <option value="">Select Time</option>
                                            <option value="09:00-09:30" <?php echo ($existingAssignment && $existingAssignment['time_slot'] == '09:00-09:30') ? 'selected' : ''; ?>>9:00 - 9:30</option>
                                            <option value="09:30-10:00" <?php echo ($existingAssignment && $existingAssignment['time_slot'] == '09:30-10:00') ? 'selected' : ''; ?>>9:30 - 10:00</option>
                                            <option value="10:00-10:30" <?php echo ($existingAssignment && $existingAssignment['time_slot'] == '10:00-10:30') ? 'selected' : ''; ?>>10:00 - 10:30</option>
                                            <option value="10:30-11:00" <?php echo ($existingAssignment && $existingAssignment['time_slot'] == '10:30-11:00') ? 'selected' : ''; ?>>10:30 - 11:00</option>
                                            <option value="11:00-11:30" <?php echo ($existingAssignment && $existingAssignment['time_slot'] == '11:00-11:30') ? 'selected' : ''; ?>>11:00 - 11:30</option>
                                            <option value="11:30-12:00" <?php echo ($existingAssignment && $existingAssignment['time_slot'] == '11:30-12:00') ? 'selected' : ''; ?>>11:30 - 12:00</option>
                                        </select>
                                    </td>
                                </tr>
                                <?php 
                                    endfor; 
                                endforeach; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-assign button functionality
        document.getElementById('autoAssignBtn').addEventListener('click', function() {
            const tableSelects = document.querySelectorAll('select[name$="[table_id]"]');
            const timeSelects = document.querySelectorAll('select[name$="[time_slot]"]');
            
            // Get all tables
            const tables = {};
            document.querySelectorAll('select[name$="[table_id]"] option').forEach(option => {
                if (option.value && option.text.includes('Table')) {
                    const classroom = option.text.split('-')[0].trim();
                    if (!tables[classroom]) tables[classroom] = [];
                    tables[classroom].push({
                        id: option.value,
                        number: parseInt(option.text.split('Table')[1])
                    });
                }
            });
            
            // Sort tables by room and number
            Object.keys(tables).forEach(classroom => {
                tables[classroom].sort((a, b) => a.number - b.number);
            });
            
            // Get all time slots
            const timeSlots = [];
            document.querySelectorAll('select[name$="[time_slot]"] option').forEach(option => {
                if (option.value) timeSlots.push(option.value);
            });
            
            // Sort time slots
            timeSlots.sort();
            
            // Assign tables and times
            let tableIndex = 0;
            let timeIndex = 0;
            const classroomOrder = Object.keys(tables).sort();
            
            for (let i = 0; i < tableSelects.length; i++) {
                // Select classroom in order
                const currentClassroom = classroomOrder[Math.floor(timeIndex / timeSlots.length) % classroomOrder.length];
                const tableList = tables[currentClassroom];
                
                // Assign table
                const tableOption = tableList[tableIndex % tableList.length];
                tableSelects[i].value = tableOption.id;
                
                // Assign time slot
                timeSelects[i].value = timeSlots[timeIndex % timeSlots.length];
                
                // Update indexes
                tableIndex++;
                if (tableIndex % tableList.length === 0) {
                    timeIndex++;
                }
            }
        });
    </script>
</body>
</html>
