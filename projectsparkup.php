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

        // Email settings
        $mail->setFrom('', 'No Reply');
        $mail->addAddress($email);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "Your OTP is: $otp. It is valid for 5 minutes.";

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
    <title>Data Collection :)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body style="background-color: rgb(7,9,21); color: white;">
<div class="container" style="padding-top: 100px">
    <h1 class="gradient-text">Data Collection :)</h1>
    <div id="errorBox" style="display:none; color: white; background: rgba(255, 0, 0, 0.7); padding: 10px; margin-bottom: 20px; border-radius: 5px;"></div>
    <form id="registrationForm" class="row g-3 gradient-background needs-validation" novalidate>
     <!-- Full Name -->
      <div class="col-md-5">
        <label for="validationServer02" class="form-label">Full name</label>
        <input type="text" class="form-control is-valid" id="validationServer02" placeholder="Full Name" name="Name" required>
      </div>
      <!-- Email -->
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

        <!-- Questions -->
      <!-- Age -->
      <div class="col-md-6">
        <label for="validationServer02" class="form-label">Age</label>
        <input type="number" class="form-control is-valid" id="validationServer02" name="Age" placeholder="Age" required>
      </div>
        
        <!-- Gender -->
        <div class="col-md-4">
          <label for="validationCustom04" class="form-label">Gender</label>
          <select class="form-select" id="validationCustom04" name="Gender" required>
            <option selected disabled value="">Choose...</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
          </select>
          <div class="invalid-feedback">
            Please select a valid Choice.
          </div>
        </div>

        
       <!-- Personality section -->
        <h2 class="gradient-text2">Personality Section</h2>
        <h5>Answer the following questions honestly as this will give us a idea about your personality and the type of person you are looking to connect with:</h5>
        

        <!-- Openness to Experience -->

        <!-- Question 1 -->

                <!-- q 1 -->
                <div class="gradient-background-q">
        <h3>Q1 : I enjoy exploring new ideas and concepts. </h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck2" name="q1" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck3" name="q1" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck4" name="q1" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck4" name="q1" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck5" name="q1" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>

      <!-- q 2 -->
      <div class="gradient-background-q">
        <h3>Q2 : I am drawn to creative activities like art, music, or writing.</h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q2" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck31" name="q2" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck41" name="q2" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck41" name="q2" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck51" name="q2" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>

      <!-- q 3 -->
      <div class="gradient-background-q">
        <h3>Q3 : I enjoy solving complex problems or puzzles. </h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q3" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q3" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q3" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q3" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q3" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>

      <!-- q 4 -->
      <div class="gradient-background-q">
        <h3>Q4 : I seek out diverse cultural experiences, such as trying new cuisines or traveling to different places.</h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q4" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q4" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q4" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q4" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q4" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>


      <!-- Conscientiousness -->

      <!-- q 5 -->
      <div class="gradient-background-q">
        <h3>Q5 : I am highly organized and always plan ahead. </h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q5" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q5" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q5" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q5" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q5" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>

      <!-- q 6 -->
      <div class="gradient-background-q">
        <h3>Q6 : I complete tasks thoroughly and with attention to detail.</h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q6" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q6" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q6" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q6" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q6" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>

      <!-- q 7 -->
      <div class="gradient-background-q">
        <h3>Q7 : I prefer to stick to schedules and deadlines.</h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q7" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q7" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q7" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q7" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q7" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>

      <!-- q 8 -->
      <div class="gradient-background-q">
        <h3>Q8 : I avoid procrastinating and tackle tasks as soon as they arise.</h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q8" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q8" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q8" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q8" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q8" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>


      <!-- Extraversion -->

      <!-- q 9 -->
      <div class="gradient-background-q">
        <h3>Q9 : I enjoy being the center of attention in social settings.</h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q9" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q9" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q9" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q9" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q9" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>

      <!-- q 10 -->
      <div class="gradient-background-q">
        <h3>Q10 : I feel energized when interacting with groups of people.</h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q10" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q10" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q10" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q10" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q10" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>

      <!-- q 11 -->
      <div class="gradient-background-q">
        <h3>Q11 : I enjoy meeting new people and building new relationships.</h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q11" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q11" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q11" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q11" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q11" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>

      <!-- q 12 -->
      <div class="gradient-background-q">
        <h3>Q12 : I tend to take the initiative in social or work-related activities.</h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q12" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q12" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q12" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q12" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q12" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>


      <!-- Agreeableness -->

      <!-- q 13 -->
      <div class="gradient-background-q">
        <h3>Q13 : I enjoy helping others and often prioritize their needs over my own.</h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q13" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q13" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q13" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q13" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q13" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>

      <!-- q 14 -->
      <div class="gradient-background-q">
        <h3>Q14 : I avoid conflicts and strive to maintain harmonious relationships.</h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q14" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q14" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q14" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q14" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q14" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>

      <!-- q 15 -->
      <div class="gradient-background-q">
        <h3>Q15 : I trust people easily and believe in their good intentions.</h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q15" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q15" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q15" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div><div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q15" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q15" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>

      <!-- q 16 -->
      <div class="gradient-background-q">
        <h3>Q16 : I am empathetic and understanding toward others' emotions.</h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q16" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q16" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q16" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div><div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q16" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q16" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>
      

      <!-- Neuroticism -->

      <!-- q 17 -->
      <div class="gradient-background-q">
        <h3>Q17 :  I frequently feel stressed or anxious.</h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q17" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q17" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q17" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div><div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q17" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q17" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>

      <!-- q 18 -->
      <div class="gradient-background-q">
        <h3>Q18 : I tend to overthink situations or imagine the worst outcomes.</h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q18" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q18" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q18" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div><div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q18" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q18" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>

      <!-- q 19 -->
      <div class="gradient-background-q">
        <h3>Q19 : I feel overwhelmed by emotions more often than others.</h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q19" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q19" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q19" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div><div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q19" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q19" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>

      <!-- q 20 -->
      <div class="gradient-background-q">
        <h3>Q20 : I struggle to maintain a calm mindset in difficult situations.</h3>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q20" required value="5"  >
          <label class="form-check-label" for="validationFormCheck2">Strongly Agree</label>
        </div>
        <div class="form-check ">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q20" required value="4"  >
          <label class="form-check-label" for="validationFormCheck3">Agree</label>
        </div>
        <div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q20" required value="3">
          <label class="form-check-label" for="validationFormCheck2">Neutral</label>
        </div><div class="form-check">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q20" required value="2"  >
          <label class="form-check-label" for="validationFormCheck2">Disagree</label>
        </div>
        <div class="form-check mb-3">
          <input type="radio" class="form-check-input" id="validationFormCheck" name="q20" required value="1"  >
          <label class="form-check-label" for="validationFormCheck3">Strongly Disagree</label>
          <div class="invalid-feedback">Please Choose An Option</div>
        </div>
      </div>
      <!-- Fun Fact -->
      <div class="gradient-background-q" style="padding: 20px;">
         <div class="col-md-6">
          <label for="validationCustom04" class="form-label"><h3>Name of the friend who told you to fill the form </h3> </label>
          <select  class="form-control" id="inputFirstName" name ="Fun Fact"  required>
            <option selected disabled value="">Choose...</option>
              <option value="Arya Chavan">Arya Chavan</option>
              <option value="Arya Bharat Patil">Arya Bharat Patil</option>
              <option value="Arya Amol Patil">Arya Amol Patil</option>
              <option value="Aryan Bhat">Aryan Bhat</option>
              <option value="Ritik Arora">Ritik Arora</option>
              <option value="Aryan jagtap">Aryan jagtap</option>


          </select>
          <div class="invalid-feedback">
            Please select a valid Choice.
          </div>
        </div>
         <br>
      </div>
      
      <!-- Timestamp -->
      <input type="hidden" name="timestamp" id="timestamp">
      
        <!-- Mandatory Checkbox -->
        <div class="col-md-12">
            <input type="checkbox" id="tnc" name="tnc" value="1">
            <label for="tnc"> I agree to the <a href="/tnc" target="_blank">Terms and Conditions</a> and <a href="/privacy" target="_blank">Privacy Policy</a></label>
        </div>
      
        <!-- submit button -->
        <div class="col-12">
            <button class="btn btn-primary" type="submit">Submit form</button>
        </div>
    </form>
</div>

<script>
    const scriptURL = 'https://script.google.com/macros/s/AKfycbzUf9hi1n7xIV00pCwhpyWL5g3jWkZudBS-47Vm8sk-wcwPBJr39qu8ZARYgYLrIE2qHw/exec';

    // Calculate category averages
    function calculateAverages() {
    const categories = {
        Openness: ['q1', 'q2', 'q3', 'q4'],
        Conscientiousness: ['q5', 'q6', 'q7', 'q8'],
        Extraversion: ['q9', 'q10', 'q11', 'q12'],
        Agreeableness: ['q13', 'q14', 'q15', 'q16'],
        Neuroticism: ['q17', 'q18', 'q19', 'q20']
    };

    const averages = {};
    for (const [category, questions] of Object.entries(categories)) {
        let total = 0;
        let count = 0;
        questions.forEach(q => {
            const value = document.querySelector(`input[name="${q}"]:checked`);
            if (value) {
                total += parseInt(value.value, 10);
                count++;
            }
        });
        averages[category] = count > 0 ? (total / count).toFixed(2) : 0; // Avoid division by zero
    }
    return averages;
}

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
</body>
</html>
