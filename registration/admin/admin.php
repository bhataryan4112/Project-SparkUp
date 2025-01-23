<?php
session_start();

// Security check
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Database configuration
$db_host = "localhost";
$db_name = "u265056410_registration";
$db_user = "u265056410_arya";
$db_pass = "Registration_SparkUp@123";

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

// Modify the query to include search
$search = isset($_GET['search']) ? $_GET['search'] : '';
try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($search) {
        $stmt = $db->prepare("SELECT * FROM registration WHERE 
            name LIKE ? OR 
            email LIKE ? OR 
            campus LIKE ? OR 
            gender LIKE ? 
            ORDER BY created_at DESC");
        $searchTerm = "%$search%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    } else {
        $stmt = $db->query("SELECT * FROM registration ORDER BY created_at DESC");
    }
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
    <title>Admin Panel | Project SparkUp</title>
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <div class="admin-container">
        <header>
            <h1>Project SparkUp- Admin Panel</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </header>

        <?php if (isset($_GET['message'])): ?>
            <div class="alert success" id="successAlert">
                <?php echo htmlspecialchars($_GET['message']); ?>
                <span class="alert-close">&times;</span>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert error" id="errorAlert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <span class="alert-close">&times;</span>
            </div>
        <?php endif; ?>

        <div class="search-container">
            <form method="get" class="search-form">
                <input type="text" name="search" placeholder="Search by name, email, campus..." 
                    value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-btn">Search</button>
                <?php if ($search): ?>
                    <a href="admin.php" class="clear-btn">Clear</a>
                <?php endif; ?>
            </form>
        </div>

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
                                <button class="edit-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($record)); ?>)">
                                    Edit
                                </button>
                                <form method="post" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this record?');">
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

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Record</h2>
            <form id="editForm" method="post" action="update_record.php" onsubmit="return false;">
                <input type="hidden" name="id" id="edit_id">                
                <div class="form-group">
                    <label for="edit_name">Name</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="edit_age">Age</label>
                    <input type="number" id="edit_age" name="age" required>
                </div>

                <div class="form-group">
                    <label for="edit_gender">Gender</label>
                    <select id="edit_gender" name="gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="edit_campus">Campus</label>
                    <input type="text" id="edit_campus" name="campus" required>
                </div>

                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="edit_category1">Category 1 Score</label>
                    <input type="number" id="edit_category1" name="category1_score" required>
                </div>

                <div class="form-group">
                    <label for="edit_category2">Category 2 Score</label>
                    <input type="number" id="edit_category2" name="category2_score" required>
                </div>

                <div class="form-group">
                    <label for="edit_category3">Category 3 Score</label>
                    <input type="number" id="edit_category3" name="category3_score" required>
                </div>

                <div class="form-group">
                    <label for="edit_category4">Category 4 Score</label>
                    <input type="number" id="edit_category4" name="category4_score" required>
                </div>

                <div class="form-group">
                    <label for="edit_category5">Category 5 Score</label>
                    <input type="number" id="edit_category5" name="category5_score" required>
                </div>

                <div class="form-actions">
                    <button type="button" class="preview-btn" onclick="previewChanges()">Preview Changes</button>
                    <button type="button" class="save-btn" onclick="previewChanges()">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Confirm Changes</h2>
            <div class="changes-preview">
                <div class="changes-grid"></div>
            </div>
            <div class="confirm-actions">
                <button class="confirm-btn" onclick="confirmChanges()">Confirm</button>
                <button class="cancel-btn" onclick="closeConfirmModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script src="admin.js"></script>
</body>
</html>
