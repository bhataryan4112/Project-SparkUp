<?php
session_start();

function sendOtp($email) {
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_expiry'] = time() + 900; // 15 minutes

    // HTML template for OTP email
    $htmlTemplate = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>OTP Verification - Project SparkUp</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <style>
        :root {
            color-scheme: light only;
            supported-color-schemes: light only;
        }
        [data-ogsc] {
            color-scheme: light only;
        }
        [data-ogsc] .text-stable,
        [data-ogsc] .preserve-color {
            color: inherit !important;
        }
        * {
            forced-color-adjust: none;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .force-light {
            forced-color-adjust: none !important;
            color-scheme: light !important;
            background-color: transparent !important;
        }
        /* Prevent Gmail\'s dark mode inversion */
        body, table, td, a, p, span, div {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            forced-color-adjust: none !important;
        }
        [data-ogsc] * {
            forced-color-adjust: none !important;
        }
        .dark-mode-text {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
        }
        .accent-text {
            color: #e67e23 !important;
            -webkit-text-fill-color: #e67e23 !important;
        }
        .instagram-button {
            color: #ffffff !important;
            -webkit-text-fill-color: #ffffff !important;
            background-color: #000000 !important;
            border: 2px solid #e67e23 !important;
        }
        /* Prevent dark mode color inversion */
        [data-ogsc] .preserve-light {
            color: #ffffff !important;
            background-color: transparent !important;
            -webkit-text-fill-color: #ffffff !important;
        }
        [data-ogsc] .preserve-accent {
            color: #e67e23 !important;
            -webkit-text-fill-color: #e67e23 !important;
        }
        @media (prefers-color-scheme: dark) {
            .preserve-light {
                color: #ffffff !important;
                background-color: transparent !important;
                -webkit-text-fill-color: #ffffff !important;
            }
            .preserve-accent {
                color: #e67e23 !important;
                -webkit-text-fill-color: #e67e23 !important;
            }
            .force-dark {
                background-color: #000000 !important;
            }
        }
        /* MSO dark mode prevention */
        [style*="color"] {
            -webkit-text-fill-color: inherit !important;
        }
        * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>
</head>

<body style="margin:0;padding:0;background-color:transparent">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="margin:0 auto">
        <tr>
            <td class="force-dark" style="background:linear-gradient(180deg, #000000 0%, #000000 60%, #e67e23 100%);padding:20px;border-radius:12px">
                <!-- Header -->
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="center" style="padding:20px">
                            <img src="https://thegroup11.com/logo.png" alt="SparkUp Logo" style="max-width:200px;height:auto">
                        </td>
                    </tr>
                    <!-- OTP Content -->
                    <tr>
                        <td align="center" style="padding:20px;background:rgba(255,255,255,0.05);border-radius:12px">
                            <h1 class="preserve-light dark-mode-text" style="font-family:\'Montserrat\',sans-serif;font-size:24px;margin:0">
                                Welcome to Project SparkUp!
                            </h1>
                            <p class="preserve-light dark-mode-text" style="font-family:\'Montserrat\',sans-serif;font-size:16px;margin:20px 0">
                                Your OTP is:
                            </p>
                            <div style="background:linear-gradient(135deg, rgba(230,126,35,0.1) 0%, rgba(255,255,255,0.05) 100%);padding:15px;border-radius:10px;border:2px solid #e67e23">
                                <span class="preserve-accent accent-text" style="font-family:\'Montserrat\',sans-serif;font-size:36px;font-weight:700">
                                    {{OTP}}
                                </span>
                            </div>
                        </td>
                    </tr>
                    <!-- Timer Info -->
                    <tr>
                        <td align="center" style="padding:10px;background:rgba(230,126,35,0.1);border-radius:8px">
                            <p style="color:#ffffff;font-family:\'Montserrat\',sans-serif;font-size:13px;margin:0">
                                ⏱️ This OTP is valid for <strong style="color:#e67e23">15 minutes</strong>
                            </p>
                        </td>
                    </tr>
                    <!-- Security Notice -->
                    <tr>
                        <td align="center" style="padding:15px 0 0">
                            <p style="color:#bbbbbb;font-family:\'Montserrat\',sans-serif;font-size:12px;line-height:1.4;margin:0">
                                Please do not share this OTP with anyone.<br>
                                If you did not request this, please ignore this email.
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding:20px;background:linear-gradient(180deg, rgba(230,126,35,0.1) 0%, rgba(230,126,35,0.3) 100%);border-top:1px solid rgba(255,255,255,0.1)">
                            <!-- Social Media -->
                            <div style="margin-bottom:15px">
                                <a href="https://instagram.com/project_sparkup" 
                                   class="instagram-button preserve-light"
                                   style="display:inline-flex;align-items:center;padding:8px 16px;border-radius:20px;font-family:\'Montserrat\',sans-serif;font-size:13px">
                                    <img src="https://thegroup11.com/registration/images/image-1.png" alt="Instagram" style="width:16px;height:16px;margin-right:6px">
                                    Follow us on Instagram
                                </a>
                            </div>
                            <!-- Contact Info -->
                            <div style="color:#ffffff;font-family:\'Montserrat\',sans-serif;font-size:12px;line-height:1.4">
                                <p style="margin:3px 0">📧 support@thegroup11.com</p>
                                <p style="margin:3px 0">📞 +91 9307026265</p>
                            </div>
                            <!-- Copyright -->
                            <div style="margin-top:15px;padding-top:15px;border-top:1px solid rgba(255,255,255,0.1)">
                                <p style="color:rgba(255,255,255,0.6);font-family:\'Montserrat\',sans-serif;font-size:11px;margin:0">
                                    © 2025 Project SparkUp - TheGroup11. All rights reserved.
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

    // Replace OTP placeholder in template
    $messageHtml = str_replace('{{OTP}}', $otp, $htmlTemplate);
    
    // URL encode parameters
    $encodedEmail = urlencode($email);
    $encodedSubject = urlencode('Your SparkUp OTP Verification');
    $encodedMessage = urlencode($messageHtml);
    
    // Create API URL with parameters
    $apiUrl = "https://thegroup11.com/api/sendmail?api_key=dGhlZ3JvdXAxMQ==&to={$encodedEmail}&subject={$encodedSubject}&message={$encodedMessage}";
    
    // Use cURL to make the API request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Skip SSL verification
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log the API response for debugging
    error_log("OTP API Response: $response, HTTP Code: $httpCode, Error: $error");
    
    if ($httpCode == 200) {
        $responseData = json_decode($response, true);
        if (isset($responseData['success']) && $responseData['success']) {
            return ['status' => true, 'message' => 'OTP sent successfully'];
        }
    }
    
    // Default error response
    return ['status' => false, 'message' => 'Error sending OTP. Please try again later.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'send_otp':
                $email = $_POST['email'];
                if (filter_var($email, FILTER_VALIDATE_EMAIL) && str_ends_with($email, "@vit.edu")) {
                    echo json_encode(sendOtp($email));
                } else {
                    echo json_encode(['status' => false, 'message' => 'Invalid VIT email format']);
                }
                break;
            case 'verify_otp':
                $otp = $_POST['otp'];
                if (isset($_SESSION['otp'], $_SESSION['otp_expiry']) && time() <= $_SESSION['otp_expiry']) {
                    if ($otp == $_SESSION['otp']) {
                        $_SESSION['verified_email'] = true;
                        $_SESSION['email'] = $_POST['email'];  // Store email in session
                        
                        unset($_SESSION['otp']);
                        unset($_SESSION['otp_expiry']);

                        $redirect = '/user';
                        if (isset($_SESSION['redirect_after_login'])) {
                            $redirect = $_SESSION['redirect_after_login'];
                            unset($_SESSION['redirect_after_login']);
                        }

                        echo json_encode(['status' => true, 'message' => 'OTP verified successfully', 'redirect' => $redirect]);
                        exit;
                        
                    } else {
                        echo json_encode(['status' => false, 'message' => 'Invalid OTP']);
                    }
                } else {
                    echo json_encode(['status' => false, 'message' => 'OTP expired or not found']);
                }
                break;
            default:
                echo json_encode(['status' => false, 'message' => 'Invalid action']);
        }
    }
    exit;  
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>SparkUp Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles-registration.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 2rem auto;
            padding: 2rem;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 15px;
            text-align: center;
        }

        .login-logo {
            width: 120px;
            height: 120px;
            background: var(--primary-color);
            border-radius: 50%;
            margin: 0 auto 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid rgba(255,255,255,0.1);
            border-radius: 25px;
            background: rgba(0,0,0,0.3);
            color: white;
            font-size: 1rem;
        }

        .verification-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }

        .verification-btn:hover {
            background: #5f73bc;
            transform: translateY(-2px);
        }

        .otp-group {
            display: flex;
            gap: 1rem;
            margin: 1rem 0;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            .login-logo {
                width: 100px;
                height: 100px;
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-logo">👋</div>
            <h1>Welcome to SparkUp</h1>
            <p>Connect with your matches</p>
            
            <div class="form-group">
                <input type="email" id="email" placeholder="Enter your VIT email" required>
            </div>
            
            <div class="otp-input-group" style="display: none;">
                <input type="text" id="otp" placeholder="Enter 6-digit OTP" maxlength="6">
            </div>
            
            <button type="button" id="sendOtpBtn" class="verification-btn">Send OTP</button>
            <span id="resendTimer" style="display:none;"></span>
            
            <div id="otpStatus" class="verification-status"></div>
        </div>
    </div>

    <script>
    const emailInput = document.getElementById('email');
    const otpInput = document.getElementById('otp');
    const sendOtpBtn = document.getElementById('sendOtpBtn');
    const verifyBtn = document.getElementById('verifyBtn');
    const otpStatus = document.getElementById('otpStatus');
    const resendTimer = document.getElementById('resendTimer');
    const otpInputGroup = document.querySelector('.otp-input-group');

    function startResendTimer() {
        let seconds = 30;
        sendOtpBtn.style.display = 'none';
        resendTimer.style.display = 'inline';
        resendTimer.textContent = `(${seconds}s)`;

        const timer = setInterval(() => {
            seconds--;
            resendTimer.textContent = `(${seconds}s)`;
            if (seconds < 0) {
                clearInterval(timer);
                resendTimer.style.display = 'none';
                sendOtpBtn.style.display = 'inline-block';
                sendOtpBtn.textContent = 'Resend OTP';
            }
        }, 1000);
    }

    sendOtpBtn.addEventListener('click', () => {
        const email = emailInput.value;
        if (!email.endsWith('@vit.edu')) {
            otpStatus.textContent = 'Please enter a valid VIT email address';
            otpStatus.className = 'verification-status error';
            return;
        }

        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'send_otp', email: email })
        })
        .then(response => response.json())
        .then(data => {
            otpStatus.textContent = data.message;
            otpStatus.className = data.status ? 'verification-status success' : 'verification-status error';
            if (data.status) {
                startResendTimer();
                otpInputGroup.style.display = 'flex'; 
                sendOtpBtn.style.display = 'none';
            }
        })
        .catch(error => {
            console.error("Fetch error:", error);
            otpStatus.textContent = "An error occurred. Please try again later.";
            otpStatus.className = 'verification-status error';
        });
    });

    otpInput.addEventListener('input', () => {
        let otp = otpInput.value;
        otp = otp.replace(/\D/g, '').trim();
        otpInput.value = otp;

        if (otp.length === 6) {
            const email = emailInput.value;
            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'verify_otp', otp: otp, email: email })
            })
            .then(response => response.json())
            .then(data => {
                otpStatus.textContent = data.message;
                otpStatus.className = data.status ? 'verification-status success' : 'verification-status error';

                if (data.status) {
                    otpInput.disabled = true;
                    emailInput.disabled = true;
                    sendOtpBtn.style.display = 'none';
                    resendTimer.style.display = 'none';
                    otpInputGroup.style.display = 'none';

                    // Redirect after OTP verification
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    }
                } else {
                    otpStatus.textContent = data.message;
                    otpStatus.className = 'verification-status error';
                }
            })
            .catch(error => {
                console.error("Fetch error:", error);
                otpStatus.textContent = "An error occurred. Please try again later.";
                otpStatus.className = 'verification-status error';
            });
        } else {
            otpInput.classList.remove('error');
        }
    });
</script>

</body>
</html>