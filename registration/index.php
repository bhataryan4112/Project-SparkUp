<?php
// Include PHPMailer for SMTP
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

// Start session for OTP storage
session_start();

// Function to send OTP
function sendOtp($email)
{
    $otp = rand(100000, 999999); // Generate a 6-digit OTP
    $_SESSION['otp'] = $otp; // Store OTP in session
    $_SESSION['otp_expiry'] = time() + 900; // OTP valid for 15 minutes

    // SMTP configuration
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'no.reply.01.coding@gmail.com'; // Replace with your email
        $mail->Password = 'xmel tbor nybn fznj'; // Replace with your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
// Email settings
$mail->setFrom('no.reply.01.coding@gmail.com', 'No Reply');
$mail->addAddress($email);
$mail->Subject = 'Your OTP Code';

// HTML email template
$mail->isHTML(true);
$mail->Body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Welcome to Project SparkUp!</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1a1a1a;
            margin: 0;
            padding: 0;
            color: #ffffff;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #262626;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(255, 165, 0, 0.5);
            overflow: hidden;
        }
        .header {
            background-color: #ff6600;
            text-align: center;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .header img {
            max-width: 50px; 
            margin-right: 15px;
        }
        .header h1 {
            color: #ffffff;
            font-size: 24px;
            margin: 0;
        }
        .content {
            padding: 30px;
            text-align: center;
        }
        .otp {
            font-size: 32px;
            font-weight: bold;
            color: #ff6600;
            background-color: #1a1a1a;
            padding: 20px;
            border-radius: 5px;
            display: inline-block;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 14px;
            background-color: #1a1a1a;
            border-top: 1px solid #ff6600;
        }
        .footer a {
            color: #ff6600;
            text-decoration: none;
            margin: 0 10px;
        }
        .footer a:hover {
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class='email-container'>
        <div class='header'>
            <img src='https://thegroup11.com/registration/logo-new.png' alt='Project SparkUp Logo'>
            <h1>Welcome to Project SparkUp!</h1>
        </div>
        <div class='content'>
            <p>Dear User,</p>
            <p>Your One-Time Password (OTP) is:</p>
            <div class='otp'>$otp</div>
            <p>This OTP is valid for <strong>15 minutes</strong>. Please do not share it with anyone.</p>
            <p>If you did not request this, please ignore this email.</p>
        </div>
        <div class='footer'>
            <p>Follow us on:</p>
            <a href='https://instagram.com/yourpage' target='_blank'>Instagram</a> |
            <a href='https://www.yourwebsite.com' target='_blank'>Website</a>
            <p>&copy; 2025 Project SparkUp. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
";

$mail->send();

        return ['status' => true, 'message' => 'OTP sent successfully'];
    } catch (Exception $e) {
        return ['status' => false, 'message' => "Error: {$mail->ErrorInfo}"];
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'send_otp') {
        $email = $_POST['email'];
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(sendOtp($email));
        } else {
            echo json_encode(['status' => false, 'message' => 'Invalid email format']);
        }
        exit;
    } elseif (isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
        $otp = $_POST['otp'];
        if (isset($_SESSION['otp'], $_SESSION['otp_expiry']) && time() <= $_SESSION['otp_expiry']) {
            if ($otp == $_SESSION['otp']) {
                unset($_SESSION['otp'], $_SESSION['otp_expiry']);
                echo json_encode(['status' => true, 'message' => 'OTP verified successfully']);
            } else {
                echo json_encode(['status' => false, 'message' => 'Invalid OTP']);
            }
        } else {
            echo json_encode(['status' => false, 'message' => 'OTP expired']);
        }
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="progress-container">
        <div class="progress-bar" id="progressBar"></div>
    </div>

    <div class="popup-overlay" id="otpSentPopup">
        <div class="popup-content">
            <div class="popup-header">
                <h2 class="popup-title">OTP Sent</h2>
            </div>
            <div class="popup-body">
                <p>A verification code has been sent to your email.</p>
                <p>Please check your inbox and spam folder.</p>
            </div>
            <div class="popup-buttons">
                <button class="popup-button proceed-btn" onclick="closeOtpPopup('otpSentPopup')">Got it</button>
            </div>
        </div>
    </div>

    <div class="popup-overlay" id="otpVerifiedPopup">
        <div class="popup-content">
            <div class="popup-header">
                <h2 class="popup-title">Email Verified</h2>
            </div>
            <div class="popup-body">
                <p>Your email has been successfully verified!</p>
            </div>
            <div class="popup-buttons">
                <button class="popup-button proceed-btn" onclick="closeOtpPopup('otpVerifiedPopup')">Continue</button>
            </div>
        </div>
    </div>

    <div class="popup-overlay" id="otpErrorPopup">
        <div class="popup-content">
            <div class="popup-header">
                <h2 class="popup-title">Verification Failed</h2>
            </div>
            <div class="popup-body">
                <p>The OTP you entered is incorrect.</p>
                <p>Please try again or request a new OTP.</p>
            </div>
            <div class="popup-buttons">
                <button class="popup-button cancel-btn" onclick="closeOtpPopup('otpErrorPopup')">Try Again</button>
                <button class="popup-button proceed-btn" onclick="sendOTP()">Resend OTP</button>
            </div>
        </div>
    </div>

    <div class="popup-overlay active" id="welcomePopup">
        <div class="popup-content welcome-popup">
            <div class="popup-header theme-color">
                <h2 class="popup-title">Welcome to SparkUp Registration!</h2>
            </div>
            <div class="popup-body">
                <ul class="instruction-list">
                    <li>Login using VIT email only (@vit.edu)</li>
                    <li>Make sure you submit the form only once (per email)</li>
                    <li>Answer the questions honestly to make the most of this</li>
                </ul>
            </div>
            <div class="popup-buttons">
                <button class="popup-button proceed-btn" onclick="closeWelcomePopup()">Let's Begin</button>
            </div>
        </div>
    </div>

    <div class="container">
        <form id="registrationForm">
            <!-- Section 1: Basic Info -->
            <section class="form-section" id="section1">
                <h2>Basic Information</h2>
                <br>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" required min="16" max="100">
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="campus">Campus</label>
                    <select id="campus" name="campus" required>
                        <option value="">Select Campus</option>
                        <option value="campus1">Bibvewadi</option>
                        <option value="campus2">Kondwa</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email ID </label>
                        <input type="text" class="form-control" id="email" name="Email Id" placeholder="Email" required>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
      
                    <div class="col-md-6">
                        <label for="otp" class="form-label">OTP</label>
                        <input type="text" class="form-control" id="otp" name="otp" placeholder="Enter OTP">
                        <button type="button" class="btn btn-secondary" id="sendOtpBtn">Send OTP</button>
                    </div>
                    <div id="otpMessage" style="padding-left: 15px;"></div>

                <button type="button" class="btn next-btn" onclick="nextSection(1)">Begin Registration</button>
            </section>
            <!-- Sections 2-5 will be generated by JavaScript -->
            <!-- Email verification section will be added by JavaScript at the end of section 5 -->
        </form>
    </div>
    <script>
        document.getElementById('registrationForm').addEventListener('submit', function (event) {
        event.preventDefault();
        const otp = document.getElementById('otp').value;

        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'verify_otp', otp: otp })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status) {
                const form = event.target;
                if (!form.checkValidity() || !document.getElementById('tnc').checked) {
                    document.getElementById('errorBox').innerText = "Please fill all fields and agree to the terms.";
                    document.getElementById('errorBox').style.display = 'block';
                    return;
                }
                const averages = calculateAverages();
                const formData = new FormData(form);
                Object.entries(averages).forEach(([category, avg]) => formData.append(category, avg));

                fetch(scriptURL, { method: 'POST', body: formData })
                    .then(response => {
                        alert('Form submitted successfully');
                        window.location.href = '/form-submitted';
                    })
                    .catch(error => console.error('Error!', error.message));
            } else {
                document.getElementById('otpMessage').innerText = data.message;
            }
        })
        .catch(error => console.error('Error!', error.message));
    });

    document.getElementById('sendOtpBtn').addEventListener('click', function () {
        const email = document.getElementById('email').value;
        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'send_otp', email: email })
        })
        .then(response => response.json())
        .then(data => {
            const otpMessage = document.getElementById('otpMessage');
            otpMessage.style.color = data.status ? 'green' : 'red';
            otpMessage.innerText = data.message;
            if (data.status) {
                document.getElementById('sendOtpBtn').style.display = 'none';
            }
        });
    });
    </script>
    <script src="scripts.js"></script>
</body>
</html>