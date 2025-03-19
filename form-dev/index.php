<?php
// Include PHPMailer and start session
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';
session_start();

// Function to send OTP
function sendOtp($email)
{
    $otp = rand(100000, 999999); // Generate a 6-digit OTP
    $_SESSION['otp'] = $otp; // Store OTP in session
    $_SESSION['otp_expiry'] = time() + 300; // OTP valid for 5 minutes

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
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'send_otp') {
            $email = $_POST['email'];
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(sendOtp($email));
            } else {
                echo json_encode(['status' => false, 'message' => 'Invalid email format']);
            }
            exit;
        } elseif ($_POST['action'] === 'verify_otp') {
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Project SparkUp Personality Test</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="container">
    <!-- Initial Registration Form -->
    <div id="registration-form" class="form-section">
      <h2>Basic Information</h2>
      <div class="input-group">
        <input type="text" id="name" placeholder="Your Name" class="text-input">
      </div>
      <div class="input-group">
        <input type="number" id="age" placeholder="Age" class="text-input" min="15" max="99">
      </div>
      <div class="input-group">
        <label>Gender:</label>
        <div class="button-group">
          <button class="option-btn" onclick="selectGender('male')">Male</button>
          <button class="option-btn" onclick="selectGender('female')">Female</button>
        </div>
      </div>
      <div class="input-group">
        <label>Campus:</label>
        <div class="button-group">
          <button class="option-btn" onclick="selectCampus('bibewadi')">Bibewadi</button>
          <button class="option-btn" onclick="selectCampus('kondhwa')">Kondhwa</button>
        </div>
      </div>
      <button class="btn" onclick="startTest()">Begin Registration Test</button>
    </div>

    <!-- Existing Test Section (initially hidden) -->
    <div id="test-section" style="display: none;">
    <!-- Progress Bar -->
    <div class="progress-bar">
      <div class="step-circle">1</div>
      <div class="step-circle">2</div>
      <div class="step-circle">3</div>
      <div class="step-circle">4</div>
      <div class="step-circle">5</div>
    </div>
    <!-- Each section has 4 questions, all displayed together -->
    <div>
      <!-- Section 1 -->
      <div id="section1-question1" class="question-card">
        <p>Q1) I enjoy exploring new ideas and concepts.</p>
        <div class="options-container">
          <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(1,1,'agree')">Agree</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(1,1,'slightlyAgree')">Slightly Agree</div>
          <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(1,1,'neutral')">Neutral</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(1,1,'slightlyDisagree')">Slightly Disagree</div>
          <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(1,1,'disagree')">Disagree</div>
        </div>
      </div>
      <div id="section1-question2" class="question-card">
        <p>Q2) I am drawn to creative activities like art, music, or writing.</p>
        <div class="options-container">
          <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(1,2,'agree')">Agree</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(1,2,'slightlyAgree')">Slightly Agree</div>
          <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(1,2,'neutral')">Neutral</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(1,2,'slightlyDisagree')">Slightly Disagree</div>
          <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(1,2,'disagree')">Disagree</div>
        </div>
      </div>
      <div id="section1-question3" class="question-card">
        <p>Q3) I enjoy solving complex problems or puzzles.</p>
        <div class="options-container">
          <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(1,3,'agree')">Agree</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(1,3,'slightlyAgree')">Slightly Agree</div>
          <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(1,3,'neutral')">Neutral</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(1,3,'slightlyDisagree')">Slightly Disagree</div>
          <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(1,3,'disagree')">Disagree</div>
        </div>
      </div>
      <div id="section1-question4" class="question-card">
        <p>Q4) I seek out diverse cultural experiences, such as trying new cuisines or traveling to different places.</p>
        <div class="options-container">
          <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(1,4,'agree')">Agree</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(1,4,'slightlyAgree')">Slightly Agree</div>
          <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(1,4,'neutral')">Neutral</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(1,4,'slightlyDisagree')">Slightly Disagree</div>
          <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(1,4,'disagree')">Disagree</div>
        </div>
      </div>
      
      <!-- Section 2 -->
      <div id="section2-question1" class="question-card">
        <p>Question 5</p>
        <div class="options-container">
          <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(2,1,'agree')">Agree</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(2,1,'slightlyAgree')">Slightly Agree</div>
          <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(2,1,'neutral')">Neutral</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(2,1,'slightlyDisagree')">Slightly Disagree</div>
          <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(2,1,'disagree')">Disagree</div>
        </div>
      </div>
      <div id="section2-question2" class="question-card">
        <p>Question 6</p>
        <div class="options-container">
          <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(2,2,'agree')">Agree</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(2,2,'slightlyAgree')">Slightly Agree</div>
          <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(2,2,'neutral')">Neutral</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(2,2,'slightlyDisagree')">Slightly Disagree</div>
          <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(2,2,'disagree')">Disagree</div>
        </div>
      </div>
      <div id="section2-question3" class="question-card">
        <p>Question 7</p>
        <div class="options-container">
          <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(2,3,'agree')">Agree</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(2,3,'slightlyAgree')">Slightly Agree</div>
          <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(2,3,'neutral')">Neutral</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(2,3,'slightlyDisagree')">Slightly Disagree</div>
          <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(2,3,'disagree')">Disagree</div>
        </div>
      </div>
      <div id="section2-question4" class="question-card">
        <p>Question 8</p>
        <div class="options-container">
          <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(2,4,'agree')">Agree</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(2,4,'slightlyAgree')">Slightly Agree</div>
          <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(2,4,'neutral')">Neutral</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(2,4,'slightlyDisagree')">Slightly Disagree</div>
          <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(2,4,'disagree')">Disagree</div>
        </div>
      </div>

      <!-- Section 3 -->
      <div id="section3-question1" class="question-card">
        <p>Question 9</p>
        <div class="options-container">
          <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(3,1,'agree')">Agree</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(3,1,'slightlyAgree')">Slightly Agree</div>
          <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(3,1,'neutral')">Neutral</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(3,1,'slightlyDisagree')">Slightly Disagree</div>
          <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(3,1,'disagree')">Disagree</div>
        </div>
      </div>
      <div id="section3-question2" class="question-card">
        <p>Question 10</p>
        <div class="options-container">
          <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(3,2,'agree')">Agree</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(3,2,'slightlyAgree')">Slightly Agree</div>
          <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(3,2,'neutral')">Neutral</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(3,2,'slightlyDisagree')">Slightly Disagree</div>
          <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(3,2,'disagree')">Disagree</div>
        </div>
      </div>
      <div id="section3-question3" class="question-card">
        <p>Question 11</p>
        <div class="options-container">
          <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(3,3,'agree')">Agree</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(3,3,'slightlyAgree')">Slightly Agree</div>
          <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(3,3,'neutral')">Neutral</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(3,3,'slightlyDisagree')">Slightly Disagree</div>
          <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(3,3,'disagree')">Disagree</div>
        </div>
      </div>
      <div id="section3-question4" class="question-card">
        <p>Question 12</p>
        <div class="options-container">
          <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(3,4,'agree')">Agree</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(3,4,'slightlyAgree')">Slightly Agree</div>
          <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(3,4,'neutral')">Neutral</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(3,4,'slightlyDisagree')">Slightly Disagree</div>
          <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(3,4,'disagree')">Disagree</div>
        </div>
      </div>

      <!-- Section 4 -->
      <div id="section4-question1" class="question-card">
        <p>Question 13</p>
        <div class="options-container">
          <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(4,1,'agree')">Agree</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(4,1,'slightlyAgree')">Slightly Agree</div>
          <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(4,1,'neutral')">Neutral</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(4,1,'slightlyDisagree')">Slightly Disagree</div>
          <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(4,1,'disagree')">Disagree</div>
        </div>
      </div>
      <div id="section4-question2" class="question-card">
        <p>Question 14</p>
        <div class="options-container">
          <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(4,2,'agree')">Agree</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(4,2,'slightlyAgree')">Slightly Agree</div>
          <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(4,2,'neutral')">Neutral</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(4,2,'slightlyDisagree')">Slightly Disagree</div>
          <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(4,2,'disagree')">Disagree</div>
        </div>
      </div>
      <div id="section4-question3" class="question-card">
        <p>Question 15</p>
        <div class="options-container">
          <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(4,3,'agree')">Agree</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(4,3,'slightlyAgree')">Slightly Agree</div>
          <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(4,3,'neutral')">Neutral</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(4,3,'slightlyDisagree')">Slightly Disagree</div>
          <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(4,3,'disagree')">Disagree</div>
        </div>
      </div>
      <div id="section4-question4" class="question-card">
        <p>Question 16</p>
        <div class="options-container">
          <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(4,4,'agree')">Agree</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(4,4,'slightlyAgree')">Slightly Agree</div>
          <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(4,4,'neutral')">Neutral</div>
          <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(4,4,'slightlyDisagree')">Slightly Disagree</div>
          <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(4,4,'disagree')">Disagree</div>
        </div>
      </div>

      <!-- Section 5 container -->
      <div id="section5-container">
        <div id="section5-questions">
          <!-- Regular Section 5 questions -->
          <div id="section5-question1" class="question-card">
            <p>Question 17</p>
            <div class="options-container">
              <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(5,1,'agree')">Agree</div>
              <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(5,1,'slightlyAgree')">Slightly Agree</div>
              <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(5,1,'neutral')">Neutral</div>
              <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(5,1,'slightlyDisagree')">Slightly Disagree</div>
              <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(5,1,'disagree')">Disagree</div>
            </div>
          </div>
          <div id="section5-question2" class="question-card">
            <p>Question 18</p>
            <div class="options-container">
              <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(5,2,'agree')">Agree</div>
              <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(5,2,'slightlyAgree')">Slightly Agree</div>
              <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(5,2,'neutral')">Neutral</div>
              <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(5,2,'slightlyDisagree')">Slightly Disagree</div>
              <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(5,2,'disagree')">Disagree</div>
            </div>
          </div>
          <div id="section5-question3" class="question-card">
            <p>Question 19</p>
            <div class="options-container">
              <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(5,3,'agree')">Agree</div>
              <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(5,3,'slightlyAgree')">Slightly Agree</div>
              <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(5,3,'neutral')">Neutral</div>
              <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(5,3,'slightlyDisagree')">Slightly Disagree</div>
              <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(5,3,'disagree')">Disagree</div>
            </div>
          </div>
          <div id="section5-question4" class="question-card">
            <p>Question 20</p>
            <div class="options-container">
              <div class="option" onmouseover="hoverOption(event,'agree')" onmouseout="unhoverOption(event,'agree')" onclick="selectOption(5,4,'agree')">Agree</div>
              <div class="option" onmouseover="hoverOption(event,'slightlyAgree')" onmouseout="unhoverOption(event,'slightlyAgree')" onclick="selectOption(5,4,'slightlyAgree')">Slightly Agree</div>
              <div class="option" onmouseover="hoverOption(event,'neutral')" onmouseout="unhoverOption(event,'neutral')" onclick="selectOption(5,4,'neutral')">Neutral</div>
              <div class="option" onmouseover="hoverOption(event,'slightlyDisagree')" onmouseout="unhoverOption(event,'slightlyDisagree')" onclick="selectOption(5,4,'slightlyDisagree')">Slightly Disagree</div>
              <div class="option" onmouseover="hoverOption(event,'disagree')" onmouseout="unhoverOption(event,'disagree')" onclick="selectOption(5,4,'disagree')">Disagree</div>
            </div>
          </div>
        </div>

        <!-- Final elements -->
        <div id="final-elements">
          <div class="question-card">
            <textarea id="funFact" placeholder="Share a fun fact about yourself (optional)" class="fun-fact-input"></textarea>
          </div>
          <div class="question-card">
            <input type="email" id="email" placeholder="Email ID" class="text-input">
            <div class="otp-section">
              <button class="btn" id="sendOtpBtn" onclick="sendOTP()">Send OTP</button>
              <input type="text" id="otp" placeholder="Enter 6-digit OTP" maxlength="6" class="text-input otp-input">
            </div>
          </div>
          <div class="terms-section">
            <label class="terms-checkbox">
              <input type="checkbox" id="terms">
              <span>I agree to the Terms and Conditions and Privacy Policy</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Navigation Buttons -->
      <div style="text-align:center;">
        <button class="btn" id="prevBtn" onclick="goToPreviousQuestion()">Previous</button>
        <button class="btn" id="nextBtn" onclick="goToNextQuestion()">Next</button>
        <button class="btn" id="finalSubmitBtn" onclick="finalSubmit()" style="display: none;">Submit</button>
      </div>
    </div>
  </div>
  <script src="script.js"></script>
</body>
</html>