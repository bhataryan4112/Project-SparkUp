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
        $mail->Username = ''; // Replace with your email
        $mail->Password = ''; // Replace with your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $emailBody = file_get_contents('email_template.html');
    $emailBody = str_replace('{{OTP}}', $otp, $emailBody);
// Email settings
$mail->setFrom('', 'No Reply');
$mail->addAddress($email);
$mail->Subject = 'Your OTP Code';

// HTML email template
$mail->isHTML(true);
$mail->Body = $emailBody;

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
                <div class="otp-verification">
                    <div class="form-group">
                        <label for="email">VIT Email Address</label>
                        <input type="email" id="email" name="email" placeholder="yourname@vit.edu" required>
                    </div>
                    <div class="otp-input-group">
                        <input type="text" id="otp" placeholder="Enter 6-digit OTP" maxlength="6" onkeyup="if(this.value.length === 6) verifyOTP();">
                        <button type="button" class="btn" id="sendOtpBtn" onclick="sendOTP()">Send OTP</button>
                        <span id="resendTimer"></span>
                    </div>
                    <div class="verification-status" id="otpStatus"></div>
                </div>

                <button type="button" class="btn next-btn" onclick="nextSection(1)" disabled>Begin Registration</button>
            </section>
            <!-- Sections 2-5 will be generated by JavaScript -->
            <!-- Email verification section will be added by JavaScript at the end of section 5 -->
            <section class="form-section hidden" id="section5">
                <!-- ...question cards... -->
                
                <div class="form-group">
                    <label for="funFact">Share something interesting about yourself (Optional)</label>
                    <textarea id="funFact" 
                        class="fun-fact-textarea" 
                        placeholder="Tell us something unique about yourself (hobbies, achievements, interests...)"
                        rows="5"
                    ></textarea>
                </div>
                
                <div class="button-group">
                    <button type="button" class="btn" onclick="prevSection(5)">Previous</button>
                    <button type="submit" class="btn" id="submitBtn">Submit</button>
                </div>
            </section>
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
            const otpStatus = document.getElementById('otpStatus');
            otpStatus.innerText = data.message;
            otpStatus.className = data.status ? 'verification-status success' : 'verification-status error';

            if (data.status) {
                isOtpVerified = true;
                const beginBtn = document.querySelector('.next-btn');
                beginBtn.disabled = false;
                beginBtn.classList.add('enabled');
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
            const otpStatus = document.getElementById('otpStatus');
            otpStatus.innerText = data.message;
            otpStatus.className = data.status ? 'verification-status success' : 'verification-status error';
            if (data.status) {
                document.getElementById('sendOtpBtn').textContent = 'Resend OTP';
                startResendTimer();
            }
        });
    });
    </script>
    <script src="scripts.js"></script>
</body>
</html>