<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

header('Content-Type: application/json');

function sendOtp($email) {
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_expiry'] = time() + 300;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = ''; // Replace with your email
        $mail->Password = ''; // Replace with your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('no.reply.01.coding@gmail.com', 'No Reply');
        $mail->addAddress($email);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "Your OTP is: $otp. It is valid for 5 minutes.";

        $mail->send();
        return ['status' => true, 'message' => 'OTP sent successfully'];
    } catch (Exception $e) {
        return ['status' => false, 'message' => "Error: {$mail->ErrorInfo}"];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'send_otp':
            $email = $_POST['email'] ?? '';
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(sendOtp($email));
            } else {
                echo json_encode(['status' => false, 'message' => 'Invalid email format']);
            }
            break;

        case 'verify_otp':
            $otp = $_POST['otp'] ?? '';
            $email = $_POST['email'] ?? '';
            
            if (isset($_SESSION['otp'], $_SESSION['otp_expiry']) && time() <= $_SESSION['otp_expiry']) {
                if ($otp == $_SESSION['otp']) {
                    $_SESSION['user_email'] = $email;
                    unset($_SESSION['otp'], $_SESSION['otp_expiry']);
                    echo json_encode(['status' => true, 'message' => 'OTP verified successfully']);
                } else {
                    echo json_encode(['status' => false, 'message' => 'Invalid OTP']);
                }
            } else {
                echo json_encode(['status' => false, 'message' => 'OTP expired']);
            }
            break;

        default:
            echo json_encode(['status' => false, 'message' => 'Invalid action']);
    }
    exit;
}
