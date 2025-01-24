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

        $emailBody = file_get_contents('email_template.html');
    $emailBody = str_replace('{{OTP}}', $otp, $emailBody);
// Email settings
$mail->setFrom('no.reply.01.coding@gmail.com', 'Project SparkUp - No Reply');
$mail->addAddress($email);
$mail->Subject = "Project SparkUp Registration OTP - $otp";

// HTML email template
$mail->isHTML(true);
$mail->Body = $emailBody;
$mail->AltBody = "Your OTP For Project SparkUp is: $otp. Valid for 15 minutes.";
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
        if (isset($_SESSION['otp'], $_SESSION['otp_expiry'])) {
            if (time() > $_SESSION['otp_expiry']) {
                echo json_encode(['status' => false, 'message' => 'OTP expired']);
                exit;
            }
            
            if ($otp == $_SESSION['otp']) {
                $_SESSION['verified_email'] = true; // Add this line
                echo json_encode(['status' => true, 'message' => 'OTP verified successfully']);
                // Don't unset OTP session variables here
            } else {
                echo json_encode(['status' => false, 'message' => 'Invalid OTP']);
            }
        } else {
            echo json_encode(['status' => false, 'message' => 'No OTP found. Please request a new one.']);
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
                        <input type="text" id="otp" placeholder="Enter 6-digit OTP" maxlength="6">
                        <button type="button" class="btn" id="sendOtpBtn">Send OTP</button>
                        <span id="resendTimer"></span>
                    </div>
                    <div class="verification-status" id="otpStatus"></div>
                </div>

                <button type="button" class="btn next-btn" onclick="nextSection(1)" disabled>Begin Registration</button>
            </section>
            <!-- Sections 2-5 will be generated by JavaScript -->
            <!-- Email verification section will be added by JavaScript at the end of section 5 -->
        </form>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const otpInput = document.getElementById('otp');
        const sendOtpBtn = document.getElementById('sendOtpBtn');
        const otpStatus = document.getElementById('otpStatus');
        const beginBtn = document.querySelector('.next-btn');
        const form = document.getElementById('registrationForm');
        let isOtpVerified = false;

        // Update window level variable for scripts.js to access
        window.isOtpVerified = false;

        function startResendTimer() {
            let seconds = 30;
            const timerSpan = document.getElementById('resendTimer');
            sendOtpBtn.style.display = 'none';
            timerSpan.style.display = 'inline';
            
            const timer = setInterval(() => {
                timerSpan.textContent = `(${seconds}s)`;
                seconds--;
                
                if (seconds < 0) {
                    clearInterval(timer);
                    timerSpan.style.display = 'none';
                    sendOtpBtn.style.display = 'inline-block';
                    sendOtpBtn.textContent = 'Resend OTP';
                }
            }, 1000);
        }

        function sendOTP() {
            const email = document.getElementById('email').value;
            if (!email.endsWith('@vit.edu')) {
                otpStatus.textContent = 'Please enter a valid VIT email address';
                otpStatus.className = 'verification-status error';
                return;
            }

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 
                    action: 'send_otp', 
                    email: email 
                })
            })
            .then(response => response.json())
            .then(data => {
                otpStatus.textContent = data.message;
                otpStatus.className = data.status ? 'verification-status success' : 'verification-status error';
                if (data.status) {
                    startResendTimer();
                    otpInput.style.display = 'block';
                }
            });
        }

        function verifyOTP() {
            const otp = otpInput.value;
            const email = document.getElementById('email').value;
            if (otp.length !== 6) return;

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 
                    action: 'verify_otp', 
                    otp: otp,
                    email: email
                })
            })
            .then(response => response.json())
            .then(data => {
                otpStatus.textContent = data.message;
                otpStatus.className = 'verification-status ' + (data.status ? 'success' : 'error');
                
                if (data.status) {
                    window.isOtpVerified = true; // This is now the single source of truth
                    isOtpVerified = true; // Keep local copy in sync
                    otpInput.disabled = true;
                    otpInput.classList.add('verified');
                    document.getElementById('email').disabled = true;
                    document.getElementById('email').classList.add('verified');
                    sendOtpBtn.style.display = 'none';
                    document.getElementById('resendTimer').style.display = 'none';
                    checkFormCompletion(); // Check if we can enable the button
                } else {
                    otpInput.classList.add('error');
                    otpInput.classList.add('error-shake');
                    setTimeout(() => otpInput.classList.remove('error-shake'), 500);
                }
                checkFormCompletion();
            });
        }

        // Make checkFormCompletion available globally
        window.checkFormCompletion = function() {
            const requiredFields = form.querySelectorAll('#section1 input[required], #section1 select[required]');
            let allFieldsFilled = true;
            let missingFields = [];
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    allFieldsFilled = false;
                    field.classList.add('error');
                    missingFields.push(field.id);
                } else {
                    field.classList.remove('error');
                }
            });

            beginBtn.disabled = !(allFieldsFilled && window.isOtpVerified);
            beginBtn.classList.toggle('enabled', !beginBtn.disabled);
            return { allFieldsFilled, missingFields };
        };

        // Event listeners for form fields
        form.querySelectorAll('#section1 input[required], #section1 select[required]').forEach(field => {
            field.addEventListener('input', checkFormCompletion);
        });

        // Add error animation to Begin Registration button when clicked while disabled
        beginBtn.addEventListener('click', function(e) {
            const { allFieldsFilled, missingFields } = checkFormCompletion();
            
            if (!allFieldsFilled || !window.isOtpVerified) {
                this.classList.add('error-shake');
                setTimeout(() => this.classList.remove('error-shake'), 500);

                // Show what's missing
                if (!window.isOtpVerified) {
                    otpStatus.textContent = 'Please verify your email first';
                    otpStatus.className = 'verification-status error';
                    otpInput.classList.add('error');
                    otpInput.classList.add('error-shake');
                    setTimeout(() => otpInput.classList.remove('error-shake'), 500);
                }

                if (!allFieldsFilled) {
                    missingFields.forEach(fieldId => {
                        const field = document.getElementById(fieldId);
                        field.classList.add('error');
                        field.classList.add('error-shake');
                        setTimeout(() => field.classList.remove('error-shake'), 500);
                    });
                }
            }
        });

        sendOtpBtn.addEventListener('click', sendOTP);
        
        otpInput.addEventListener('keyup', function() {
            if (this.value.length === 6) {
                verifyOTP();
            }
        });
    });
    </script>
    <script src="scripts.js"></script>
</body>
</html>
