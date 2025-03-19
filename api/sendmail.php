<?php
// Load configuration
$config = require __DIR__ . '/config.php';

// Validate API key
if (!isset($_GET['api_key']) || $_GET['api_key'] !== $config['API_KEY']) {
    http_response_code(403);
    echo json_encode(["error" => "Invalid API Key"]);
    exit;
}

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

// Prepare email data
$to = $_GET['to'] ?? '';
$subject = $_GET['subject'] ?? '';
$message = $_GET['message'] ?? '';

if (!$to || !$subject || !$message) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required parameters"]);
    exit;
}

// Initialize PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $config['SMTP_USER']; // Gmail address
    $mail->Password = $config['SMTP_PASS']; // App password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Set email sender and receiver
    $mail->setFrom($config['SMTP_USER'], 'The Group 11');
    $mail->addAddress($to);

    // Enable HTML formatting
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $message;

    // Add plain text alternative (optional)
    $mail->AltBody = strip_tags($message);

    // Send email
    if ($mail->send()) {
        echo json_encode(["success" => "Email sent successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Mailer Error: " . $mail->ErrorInfo]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Email could not be sent. Error: " . $e->getMessage()]);
}
?>
