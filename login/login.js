let otpSent = false; // Track OTP status

document.getElementById('sendOtpBtn').addEventListener('click', function () {
  const email = document.getElementById('email').value.trim();

  // Validate email
  if (!email || !validateEmail(email)) {
    alert('Please enter a valid email address.');
    return;
  }

  // Simulate sending OTP
  sendOtp(email);
});

document.getElementById('verifyOtpBtn').addEventListener('click', function () {
  const otp = document.getElementById('otp').value.trim();

  // Validate OTP
  if (!otp || otp.length !== 6) {
    alert('Please enter a valid 6-digit OTP.');
    return;
  }

  // Simulate OTP verification
  verifyOtp(otp);
});

// Helper functions
function sendOtp(email) {
  // Simulate API call to send OTP
  setTimeout(() => {
    otpSent = true;
    document.querySelector('.otp-section').style.display = 'block';
    document.getElementById('sendOtpBtn').style.display = 'none';
    document.getElementById('verifyOtpBtn').style.display = 'inline-block';
    document.getElementById('otpMessage').textContent = 'OTP sent to your email!';
    document.getElementById('otpMessage').style.color = 'green';
  }, 1000);
}

function verifyOtp(otp) {
  // Simulate OTP verification logic
  if (otp === '123456') {
    alert('OTP verified! Login successful.');
    resetForm();
  } else {
    document.getElementById('otpMessage').textContent = 'Invalid OTP. Please try again.';
    document.getElementById('otpMessage').style.color = 'red';
  }
}

function resendOtp() {
  if (!otpSent) {
    alert('Please send OTP first.');
    return;
  }
  alert('OTP resent to your email!');
}

function validateEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

function resetForm() {
  document.getElementById('otp-login-form').reset();
  document.querySelector('.otp-section').style.display = 'none';
  document.getElementById('sendOtpBtn').style.display = 'inline-block';
  document.getElementById('verifyOtpBtn').style.display = 'none';
  document.getElementById('otpMessage').textContent = '';
}
