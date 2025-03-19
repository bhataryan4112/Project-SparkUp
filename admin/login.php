<?php
session_start();

// Debug session status
error_log("Session ID at login: " . session_id());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['admin_logged_in'] = true;
        
        // Debug session variables
        error_log("Admin logged in, admin_logged_in set to: " . $_SESSION['admin_logged_in']);
        
        // Check if there was a requested page before login
        if (isset($_SESSION['requested_page'])) {
            $redirect = $_SESSION['requested_page'];
            unset($_SESSION['requested_page']);
            header("Location: $redirect");
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <div class="login-container">
        <form method="post" class="login-form">
            <h2>Admin Login</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>
    </div>
</body>
</html>
