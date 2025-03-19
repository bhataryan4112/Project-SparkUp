<?php
header('Content-Type: application/json');

// Enable detailed error reporting for debugging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log("Email save process started");

// Allow CORS for development
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Debug logging
error_log("Request received with method: " . $_SERVER['REQUEST_METHOD']);
error_log("Raw POST data: " . file_get_contents('php://input'));
error_log("POST array: " . print_r($_POST, true));

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

// Get email from POST request - handle both form data and JSON
$email = '';
if (!empty($_POST['email'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
} else {
    // Try to parse JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() === JSON_ERROR_NONE && !empty($input['email'])) {
        $email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
    }
}

if (!$email) {
    error_log("No email found in request");
    echo json_encode(["success" => false, "message" => "Email is required"]);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_log("Invalid email format: $email");
    echo json_encode(["success" => false, "message" => "Invalid email format"]);
    exit;
}

// Validate VIT email
if (strpos($email, '@vit.edu') === false) {
    error_log("Non-VIT email: $email");
    echo json_encode(["success" => false, "message" => "Please use your VIT institutional email (@vit.edu)"]);
    exit;
}

try {
    error_log("Attempting database connection");
    
    // Include database configuration
    require_once('config/database.php');
    
    // Get database connection
    $db = getDbConnection();
    error_log("Database connection successful");
    
    // Create table if not exists
    $db->exec("CREATE TABLE IF NOT EXISTS `waitlist_emails` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `created_at` DATETIME NOT NULL,
        `status` ENUM('pending', 'notified') DEFAULT 'pending',
        `notify_date` DATETIME DEFAULT NULL
    )");
    error_log("Table check/creation completed");
    
    // Check if email already exists
    $checkStmt = $db->prepare("SELECT * FROM waitlist_emails WHERE email = :email");
    $checkStmt->bindParam(':email', $email);
    $checkStmt->execute();
    error_log("Checked for existing email: " . ($checkStmt->rowCount() > 0 ? "found" : "not found"));
    
    if ($checkStmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Your email is already on our waitlist!"]);
        exit;
    }
    
    // Insert email into database
    $insertStmt = $db->prepare("INSERT INTO waitlist_emails (email, created_at) VALUES (:email, NOW())");
    $insertStmt->bindParam(':email', $email);
    $result = $insertStmt->execute();
    
    if ($result) {
        error_log("Successfully inserted email: $email");
        
        // Add backup file storage for redundancy
        file_put_contents('waitlist_emails.txt', $email . PHP_EOL, FILE_APPEND);
        
        // Success response
        echo json_encode(["success" => true, "message" => "Thank you for joining our waitlist!"]);
    } else {
        error_log("Failed to insert email: $email. Error: " . print_r($insertStmt->errorInfo(), true));
        throw new Exception("Failed to insert email into database");
    }
    
} catch (Exception $e) {
    // Log error but don't expose details to user
    error_log("Error saving email: " . $e->getMessage());
    
    // Try file-based fallback
    try {
        $file = 'waitlist_emails.txt';
        file_put_contents($file, $email . PHP_EOL, FILE_APPEND);
        error_log("Saved email to backup file: $email");
        echo json_encode(["success" => true, "message" => "Thank you for joining our waitlist!"]);
    } catch (Exception $fe) {
        error_log("Failed to save to backup file: " . $fe->getMessage());
        echo json_encode(["success" => false, "message" => "Something went wrong. Please try again later."]);
    }
}
?>
