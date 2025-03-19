<?php
// Disable error display in production
ini_set('display_errors', 1); // Temporarily enable for debugging
error_reporting(E_ALL);

// Include PHPMailer
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Get JSON data
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);

    // Debug log
    error_log("Received data: " . print_r($data, true));

    // Validate data
    if (!$data) {
        throw new Exception('Invalid JSON data received');
    }

    // Database configuration
    $db_host = "";
    $db_name = "";
    $db_user = "";
    $db_pass = "";

    // Connect to database
    $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Debug log
    error_log("Database connection successful");

    // Prepare statement
    $stmt = $db->prepare("INSERT INTO registration (
        name, age, gender, campus, email, fun_fact,
        category1_score, category2_score, category3_score, 
        category4_score, category5_score
    ) VALUES (
        :name, :age, :gender, :campus, :email, :funFact,
        :category1, :category2, :category3, :category4, :category5
    )");

    // Execute with data
    $result = $stmt->execute([
        ':name' => $data['name'],
        ':age' => $data['age'],
        ':gender' => $data['gender'],
        ':campus' => $data['campus'],
        ':email' => $data['email'],
        ':funFact' => $data['funFact'],
        ':category1' => $data['category1'],
        ':category2' => $data['category2'],
        ':category3' => $data['category3'],
        ':category4' => $data['category4'],
        ':category5' => $data['category5']
    ]);

    // Debug log
    error_log("Database insertion successful");

    // After successful database insertion, send success email
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'no.reply.01.coding@gmail.com';
        $mail->Password = 'xmel tbor nybn fznj';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Debug SMTP
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $debugOutput = '';
        $mail->Debugoutput = function($str, $level) use (&$debugOutput) {
            $debugOutput .= "$str\n";
        };

        // Get email template
        $emailTemplate = __DIR__ . '/success_email_template.html';
        if (!file_exists($emailTemplate)) {
            throw new Exception('Email template file not found at: ' . $emailTemplate);
        }
        
        $emailBody = file_get_contents($emailTemplate);
        if ($emailBody === false) {
            throw new Exception('Failed to read email template');
        }

        // Email settings
        $mail->setFrom('no.reply.01.coding@gmail.com', 'Project SparkUp');
        $mail->addAddress($data['email']);
        $mail->isHTML(true);
        $mail->Subject = "Welcome to Project SparkUp!";
        $mail->Body = $emailBody;
        
        // Send email
        $mail->send();
        error_log("Success email sent to: " . $data['email']);
    } catch (Exception $e) {
        // Log email error but don't fail the registration
        error_log("Email sending failed: " . $e->getMessage());
        error_log("SMTP Debug Output: " . $debugOutput);
    }

    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Registration completed successfully'
    ]);

} catch(Exception $e) {
    // Log detailed error
    error_log("Error in submit.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Send error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed. Please try again.'
    ]);
}
?>
