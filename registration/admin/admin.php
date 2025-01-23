<?php
session_start();

// Database configuration
$db_host = "localhost";
$db_name = "u265056410_registration";
$db_user = "u265056410_arya";
$db_pass = "Registration_SparkUp@123";

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Handle delete request
if (isset($_POST['delete_id'])) {
    try {
        $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Changed table name from user_registrations to registration
        $stmt = $db->prepare("DELETE FROM registration WHERE id = ?");
        $stmt->execute([$_POST['delete_id']]);
        
        header('Location: admin.php?message=Record+deleted+successfully');
        exit;
    } catch(PDOException $e) {
        header('Location: admin.php?error=Delete+failed:+'.$e->getMessage());
        exit;
    }
}

// Fetch all records
try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Changed table name from user_registrations to registration
    $stmt = $db->query("SELECT * FROM registration ORDER BY created_at DESC");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <div class="admin-container">
        <header>
            <h1>Admin Dashboard</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </header>

        <?php if (isset($_GET['message'])): ?>
            <div class="alert success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Campus</th>
                        <th>Email</th>
                        <th>Category 1</th>
                        <th>Category 2</th>
                        <th>Category 3</th>
                        <th>Category 4</th>
                        <th>Category 5</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['id']); ?></td>
                            <td><?php echo htmlspecialchars($record['name']); ?></td>
                            <td><?php echo htmlspecialchars($record['age']); ?></td>
                            <td><?php echo htmlspecialchars($record['gender']); ?></td>
                            <td><?php echo htmlspecialchars($record['campus']); ?></td>
                            <td><?php echo htmlspecialchars($record['email']); ?></td>
                            <td><?php echo htmlspecialchars($record['category1_score']); ?></td>
                            <td><?php echo htmlspecialchars($record['category2_score']); ?></td>
                            <td><?php echo htmlspecialchars($record['category3_score']); ?></td>
                            <td><?php echo htmlspecialchars($record['category4_score']); ?></td>
                            <td><?php echo htmlspecialchars($record['category5_score']); ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                    <input type="hidden" name="delete_id" value="<?php echo $record['id']; ?>">
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
