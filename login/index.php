<?php
session_start();
// Check if user is already logged in
if (isset($_SESSION['user_email'])) {
    header('Location: /user/');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OTP Login</title>
  <link rel="stylesheet" href="styles.css">
  <style>
        /* Remove any popup-specific styles and keep only page-specific styles */
        /* ...existing styles except popup styles... */
    </style>
</head>
<body>
    <!-- Initial Login Popup -->
    <div class="popup-overlay" id="loginPopup">
        <div class="popup-content">
            <div class="popup-header">
                <h2 class="popup-title">Welcome Back to SparkUp!</h2>
            </div>
            <div class="popup-body">
                <p>To ensure secure login:</p>
                <ul>
                    <li>Enter your registered email address</li>
                    <li>You will receive a 6-digit OTP</li>
                    <li>OTP is valid for 5 minutes</li>
                    <li>Check spam folder if you don't see the OTP</li>
                </ul>
            </div>
            <div class="popup-buttons">
                <button class="popup-button cancel-btn" id="cancelBtn">Cancel</button>
                <button class="popup-button proceed-btn" id="proceedBtn">Continue</button>
            </div>
        </div>
    </div>

    <!-- Main Login Form -->
    <div class="container" style="display: none;" id="loginForm">
        <h1>OTP Login</h1>
        <p>Enter your email to receive a one-time password (OTP).</p>

        <form id="otp-login-form">
            <div class="input-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="text-input" 
                       placeholder="Enter your VIT email" required>
            </div>

            <div class="input-group otp-section" style="display: none;">
                <label for="otp">Enter OTP</label>
                <input type="text" id="otp" name="otp" class="text-input" 
                       placeholder="Enter 6-digit OTP" maxlength="6" oninput="if(this.value.length == 6) verifyOTP()">
                <span id="otpMessage"></span>
            </div>

            <div class="button-group">
                <button type="button" id="sendOtpBtn" class="option-btn" onclick="sendOTP()">Send OTP</button>
                <button type="button" id="resendOtpBtn" class="option-btn" style="display: none;">Resend OTP</button>
            </div>
        </form>
    </div>

  <script src="script.js"></script>
  <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show popup when page loads
            document.getElementById('loginPopup').classList.add('active');
        });

        document.getElementById('cancelBtn').addEventListener('click', function() {
            document.getElementById('loginPopup').classList.remove('active');
            // Redirect back to homepage
            window.location.href = 'homepage.html';
        });

        document.getElementById('proceedBtn').addEventListener('click', function() {
            document.getElementById('loginPopup').classList.remove('active');
            document.getElementById('loginForm').style.display = 'block';
        });

        // Remove duplicate event listeners and function definitions
        function showOtpSentPopup() {
            document.getElementById('otpSentPopup').classList.add('active');
        }

        function closeOtpSentPopup() {
            document.getElementById('otpSentPopup').classList.remove('active');
        }

        function showResendPopup() {
            document.getElementById('resendOtpPopup').classList.add('active');
        }

        function closeResendPopup() {
            document.getElementById('resendOtpPopup').classList.remove('active');
        }
    </script>
</body>
</html>
