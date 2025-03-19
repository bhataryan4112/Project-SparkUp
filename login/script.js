const totalSections = 5;
const questionsPerSection = 4;
const answers = {};
let currentSection = 1;
let currentQuestion = 1;

// Global variables for form state
let selectedGender = null;
let selectedCampus = null;
let otpSent = false;
let otpVerified = false;

function selectOption(section, qNumber, value) {
  const card = document.getElementById(`section${section}-question${qNumber}`);
  const clickedOpt = event.target;
  const alreadySelected = clickedOpt.classList.contains('selected');
  card.querySelectorAll('.option').forEach(opt => {
    opt.classList.remove(
      'selected','selectedAgree','selectedSlight','selectedNeutral','selectedDisagree',
      'agree','slightlyAgree','neutral','slightlyDisagree','disagree',
      'selectedSlightlyAgree','selectedSlightlyDisagree'
    );
  });
  if (alreadySelected) {
    answers[`s${section}q${qNumber}`] = null;
    updateView(); // update circle alpha on deselect
    return;
  }
  let selClass = 'selectedNeutral'; 
  clickedOpt.classList.add('selected');
  if (value === 'agree') {
    selClass = 'selectedAgree';
    clickedOpt.classList.add('agree');
  }
  else if (value === 'slightlyAgree') {
    selClass = 'selectedSlightlyAgree';
    clickedOpt.classList.add('slightlyAgree');
  }
  else if (value === 'neutral') {
    clickedOpt.classList.add('neutral');
  }
  else if (value === 'slightlyDisagree') {
    selClass = 'selectedSlightlyDisagree';
    clickedOpt.classList.add('slightlyDisagree');
  }
  else if (value === 'disagree') {
    selClass = 'selectedDisagree';
    clickedOpt.classList.add('disagree');
  }
  clickedOpt.classList.add(selClass);
  answers[`s${section}q${qNumber}`] = value;
  updateView();
}

function hoverOption(event, value) {
  const target = event.target;
  // only add hover class if not already selected
  if (!target.classList.contains('selected')) {
    target.classList.add(value);
  }
}

function unhoverOption(event, value) {
  const target = event.target;
  // only remove hover class if not already selected
  if (!target.classList.contains('selected')) {
    target.classList.remove(value);
  }
}

function highlightUnanswered(section, qNumber) {
  const card = document.querySelector(`#section${section}-question${qNumber}`);
  card.classList.add('unanswered-alert');
  setTimeout(() => card.classList.remove('unanswered-alert'), 2000);
}

function goToNextQuestion() {
  if (currentSection < totalSections) { 
    currentSection++; 
  } else {
    // Show final section when reaching the end
    document.getElementById('final-section').style.display = 'block';
  }
  updateView();
}

function goToPreviousQuestion() {
  if (currentSection > 1) { currentSection--; }
  updateView();
}

function scrollToQuestion(s, q) {
  document.querySelector(`#section${s}-question${q}`).scrollIntoView({ behavior: 'smooth' });
}

function updateView() {
  // Show all cards of currentSection, hide others
  for (let s = 1; s <= totalSections; s++) {
    const sectionContainer = document.getElementById(`section${s}-container`);
    const sectionQuestions = document.querySelectorAll(`[id^="section${s}-question"]`);
    
    sectionQuestions.forEach(question => {
      question.style.display = (s === currentSection) ? 'block' : 'none';
    });
  }
  
  // Handle final elements visibility
  const finalElements = document.getElementById('final-elements');
  if (finalElements) {
    finalElements.style.display = (currentSection === 5) ? 'block' : 'none';
  }
  
  // Update progress circles
  const circles = document.querySelectorAll('.step-circle');
  circles.forEach((circle, index) => {
    const s = index + 1;
    const answered = [...Array(questionsPerSection)].filter((_, i) => answers[`s${s}q${i+1}`]).length;
    let alpha = answered === 0 ? 0.15 : 0.25 * answered;
    if (alpha > 1) alpha = 1;
    circle.style.borderColor = `rgba(0,255,0,${alpha})`;
  });

  // Handle navigation buttons
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const finalSubmitBtn = document.getElementById('finalSubmitBtn');
  
  if (prevBtn) prevBtn.style.display = (currentSection === 1) ? 'none' : 'inline-block';
  if (nextBtn) nextBtn.style.display = (currentSection === totalSections) ? 'none' : 'inline-block';
  if (finalSubmitBtn) finalSubmitBtn.style.display = (currentSection === totalSections) ? 'inline-block' : 'none';
}

function selectGender(gender) {
  selectedGender = gender;
  document.querySelectorAll('.button-group button').forEach(btn => {
    btn.classList.remove('selected');
  });
  event.target.classList.add('selected');
}

function selectCampus(campus) {
  selectedCampus = campus;
  const buttons = event.target.parentElement.querySelectorAll('button');
  buttons.forEach(btn => btn.classList.remove('selected'));
  event.target.classList.add('selected');
}
//changes for checks 
function startTest() {
    if (validateRegistrationForm()) {
        document.getElementById('registration-form').style.display = 'none';
        document.getElementById('test-section').style.display = 'block';
        updateView();
    }
}

function showValidationPopup(messages) {
    const messageDiv = document.getElementById('validationMessages');
    messageDiv.innerHTML = messages.map(msg => `<p>${msg}</p>`).join('');
    document.getElementById('validationPopup').classList.add('active');
}

function closeValidationPopup() {
    document.getElementById('validationPopup').classList.remove('active');
}

function validateRegistrationForm() {
    let isValid = true;
    const errorMessages = [];

    // Name validation
    const name = document.getElementById('name').value.trim();
    if (!name) {
        errorMessages.push("Name is required");
        isValid = false;
    } else if (name.length < 2) {
        errorMessages.push("Name must be at least 2 characters");
        isValid = false;
    }

    // Age validation
    const age = document.getElementById('age').value;
    if (!age) {
        errorMessages.push("Age is required");
        isValid = false;
    } else {
        const ageNum = parseInt(age);
        if (isNaN(ageNum) || ageNum < 16 || ageNum > 30) {
            errorMessages.push("Age must be between 16 and 30");
            isValid = false;
        }
    }

    // Gender validation
    if (!selectedGender) {
        errorMessages.push("Please select your gender");
        isValid = false;
    }

    // Campus validation
    if (!selectedCampus) {
        errorMessages.push("Please select your campus");
        isValid = false;
    }

    // Show validation popup if there are errors
    if (!isValid) {
        showValidationPopup(errorMessages);
    }

    return isValid;
}

function showTestInstructions() {
    if (validateRegistrationForm()) {
        document.getElementById('testInstructionsPopup').classList.add('active');
    }
}

function startTestAfterPopup() {
    closeTestPopup();
    if (validateRegistrationForm()) {
        document.getElementById('registration-form').style.display = 'none';
        document.getElementById('test-section').style.display = 'block';
        updateView();
    }
}

// Replace the existing sendOTP function with this one
function sendOTP() {
    const email = document.getElementById('email').value;
    if (!email || !email.endsWith('@vit.edu')) {
        alert('Please enter a valid VIT email address');
        return;
    }

    fetch('handle_otp', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'send_otp',
            'email': email
        })
    })
    .then(response => response.json())
    .then(data => {
        const otpMessage = document.getElementById('otpMessage');
        otpMessage.style.color = data.status ? 'green' : 'red';
        otpMessage.innerText = data.message;
        if (data.status) {
            document.getElementById('sendOtpBtn').style.display = 'none';
        }
    })
    .catch(error => console.error('Error:', error));
}

// Replace the existing checkOTP function with this one
function checkOTP() {
    const otp = document.getElementById('otp').value;
    if (otp.length !== 6) return;

    fetch('handle_otp', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'verify_otp',
            'otp': otp
        })
    })
    .then(response => response.json())
    .then(data => {
        const otpInput = document.getElementById('otp');
        const otpMessage = document.getElementById('otpMessage');
        
        if (data.status) {
            otpVerified = true;
            otpInput.classList.add('otp-valid');
            otpInput.classList.remove('otp-invalid');
            otpMessage.style.color = 'green';
        } else {
            otpVerified = false;
            otpInput.classList.add('otp-invalid');
            otpInput.classList.remove('otp-valid');
            otpMessage.style.color = 'red';
        }
        otpMessage.innerText = data.message;
    })
    .catch(error => console.error('Error:', error));
}

// Add a small helper function to check if all required questions are answered and if the user has agreed to terms, then call this function before verifying OTP.  
// Only proceed with final submission if the checks pass.
function checkAllRequirements() {
  // Email validation
  const email = document.getElementById('email').value.trim();
  if (!email || !email.endsWith('@vit.edu')) {
    alert('Please enter a valid VIT email address');
    return false;
  }

  // OTP verification check
  if (!otpVerified) {
    alert('Please verify your email with OTP');
    return false;
  }

  // Name validation
  const name = document.getElementById('name').value.trim();
  if (!name || name.length < 2) {
    alert('Please enter a valid name');
    return false;
  }

  // Gender validation
  if (!selectedGender) {
    alert('Please select your gender');
    return false;
  }

  // Check for unanswered questions
  let unansweredFound = false;
  for (let s = 1; s <= totalSections; s++) {
    for (let q = 1; q <= questionsPerSection; q++) {
      if (!answers[`s${s}q${q}`]) {
        highlightUnanswered(s, q);
        if (!unansweredFound) {
          currentSection = s;
          updateView();
          scrollToQuestion(s, q);
          unansweredFound = true;
        }
      }
    }
  }
  if (unansweredFound) {
    return false;
  }
  const termsChecked = document.getElementById('terms').checked;
  if (!termsChecked) {
    alert('Please accept the terms and conditions');
    return false;
  }
  return true;
}

// Add form access protection
document.addEventListener('DOMContentLoaded', function() {
    updateView();
  
    // Remove OTP button click handler to avoid double trigger
    // Add OTP input handler
    const otpInput = document.getElementById('otp');
    if (otpInput) {
        otpInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
            if (this.value.length === 6) {
                checkOTP();
            }
        });
    }

    // Prevent direct access to test section
    const testSection = document.getElementById('test-section');
    const registrationForm = document.getElementById('registration-form');
    
    if (testSection && registrationForm) {
        testSection.style.display = 'none';
        registrationForm.style.display = 'block';
    }

    // Add form submission prevention
    const form = document.getElementById('registration-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            startTest();
        });
    }
});

const scriptURL = 'https://script.google.com/macros/s/AKfycbzUf9hi1n7xIV00pCwhpyWL5g3jWkZudBS-47Vm8sk-wcwPBJr39qu8ZARYgYLrIE2qHw/exec';

function calculateAverages() {
  const scaleMap = { 'agree': 5, 'slightlyAgree': 4, 'neutral': 3, 'slightlyDisagree': 2, 'disagree': 1 };
  const categories = {
    Openness: ['s1q1','s1q2','s1q3','s1q4'],
    Conscientiousness: ['s2q1','s2q2','s2q3','s2q4'],
    Extraversion: ['s3q1','s3q2','s3q3','s3q4'],
    Agreeableness: ['s4q1','s4q2','s4q3','s4q4'],
    Neuroticism: ['s5q1','s5q2','s5q3','s5q4']
  };
  const averages = {};
  // Calculate averages
  for (const [cat, qs] of Object.entries(categories)) {
    let total = 0, count = 0;
    qs.forEach(q => {
      if (answers[q]) {
        total += scaleMap[answers[q]] || 0;
        count++;
      }
    });
    averages[cat] = count ? (total / count).toFixed(2) : 0;
  }
  return averages;
}

// Replace finalSubmit with event listener
document.getElementById('finalSubmitBtn').addEventListener('click', function(event) {
  event.preventDefault();
  
  // Check both registration form and all requirements
  if (!validateRegistrationForm() || !checkAllRequirements()) {
    return;
  }

  const formData = new FormData();
  
  // Basic info
  formData.append('Name', document.getElementById('name').value);
  formData.append('Email Id', document.getElementById('email').value);
  formData.append('Age', document.getElementById('age').value);
  formData.append('Campus', selectedCampus);
  formData.append('Gender', selectedGender);
  
  // Individual question answers
  for (let i = 1; i <= 20; i++) {
    const section = Math.ceil(i/4);
    const questionInSection = ((i-1) % 4) + 1;
    const answer = answers[`s${section}q${questionInSection}`] || '';
    formData.append(`Question${i}`, answer);
  }
  
  // Fun fact
  formData.append('Fun Fact', document.getElementById('funFact').value);
  
  // Personality scores
  const averages = calculateAverages();
  Object.entries(averages).forEach(([cat, avg]) => formData.append(cat, avg));

  // Submit to Google Sheet
  fetch(scriptURL, { 
    method: 'POST', 
    body: formData 
  })
  .then(response => response.text())
  .then(text => {
    console.log('Google script response:', text);
    alert('Form submitted successfully');
    // Optional: Reset form or redirect
    window.location.reload();
  })
  .catch(error => {
    console.error('Error!', error);
    alert('There was an error submitting the form');
  });
});

// OTP Handling
function sendOTP() {
    const email = document.getElementById('email').value;
    if (!email || !email.endsWith('@vit.edu')) {
        showAlert('Please enter a valid VIT email address');
        return;
    }

    fetch('handle_otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=send_otp&email=${encodeURIComponent(email)}`
    })
    .then(response => response.json())
    .then(data => {
        showAlert(data.message, data.status ? 'success' : 'error');
        if (data.status) {
            document.querySelector('.otp-section').style.display = 'block';
        }
    });
}

function verifyOTP() {
    const otp = document.getElementById('otp').value;
    const email = document.getElementById('email').value;

    fetch('handle_otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=verify_otp&otp=${otp}&email=${encodeURIComponent(email)}`
    })
    .then(response => response.json())
    .then(data => {
        showAlert(data.message, data.status ? 'success' : 'error');
        if (data.status) {
            window.location.href = '/user/';
        }
    });
}

// Match Handling
function checkMatch(email) {
    fetch('check_match.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `email=${encodeURIComponent(email)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.hasMatch) {
            generateQR(email);
        } else {
            showAlert('No matches available', 'error');
        }
    });
}

// QR Code Generation
function generateQR(email) {
    const qrData = btoa(JSON.stringify({
        email: email,
        timestamp: Date.now()
    }));
    
    fetch('generate_qr.php', {
        method: 'POST',
        body: JSON.stringify({ data: qrData })
    })
    .then(response => response.blob())
    .then(blob => {
        const qrImage = URL.createObjectURL(blob);
        document.getElementById('qrDisplay').src = qrImage;
        startQRCheckInterval(email);
    });
}

// Real-time QR scan check
function startQRCheckInterval(email) {
    const checkInterval = setInterval(() => {
        fetch('check_scan.php', {
            method: 'POST',
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.scanned) {
                clearInterval(checkInterval);
                showPartnerDetails(data.partnerInfo);
                startMatchTimer();
            }
        });
    }, 5000);
}

// Match Timer
function startMatchTimer() {
    let timeLeft = 300; // 5 minutes
    const timerDisplay = document.getElementById('timer');
    
    const timer = setInterval(() => {
        timeLeft--;
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            endMatchSession();
        }
    }, 1000);
}

// Helper Functions
function showAlert(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 3000);
}

function endMatchSession() {
    fetch('end_session.php', {
        method: 'POST'
    }).then(() => {
        showAlert('Match session ended', 'info');
        window.location.reload();
    });
}

// Session management helpers
function checkSession() {
    fetch('check_session.php')
    .then(response => response.json())
    .then(data => {
        if (data.active) {
            window.location.href = '/user/';
        }
    });
}

// OTP handling
function sendOTP() {
    const email = document.getElementById('email').value;
    if (!email || !email.endsWith('@vit.edu')) {
        showAlert('Please enter a valid VIT email address');
        return;
    }

    fetch('handle_otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            'action': 'send_otp',
            'email': email
        })
    })
    .then(response => response.json())
    .then(data => {
        showAlert(data.message, data.status ? 'success' : 'error');
        if (data.status) {
            document.querySelector('.otp-section').style.display = 'block';
            document.getElementById('sendOtpBtn').style.display = 'none';
            document.getElementById('verifyOtpBtn').style.display = 'inline-block';
        }
    })
    .catch(error => console.error('Error:', error));
}

function verifyOTP() {
    const otp = document.getElementById('otp').value;
    const email = document.getElementById('email').value;

    fetch('handle_otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            'action': 'verify_otp',
            'otp': otp,
            'email': email
        })
    })
    .then(response => response.json())
    .then(data => {
        const otpInput = document.getElementById('otp');
        const otpMessage = document.getElementById('otpMessage');
        
        if (data.status) {
            otpInput.classList.add('otp-valid');
            otpInput.classList.remove('otp-invalid');
            otpMessage.style.color = 'green';
            setTimeout(() => {
                window.location.href = '/user/';
            }, 1000);
        } else {
            otpInput.classList.add('otp-invalid');
            otpInput.classList.remove('otp-valid');
            otpMessage.style.color = 'red';
        }
        otpMessage.innerText = data.message;
    })
    .catch(error => console.error('Error:', error));
}

// Utility functions
function showAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 3000);
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    checkSession();

    const otpInput = document.getElementById('otp');
    if (otpInput) {
        otpInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
            if (this.value.length === 6) {
                verifyOTP();
            }
        });
    }

    // Popup handlers
    const closeLoginPopup = document.querySelector('.cancel-btn');
    if (closeLoginPopup) {
        closeLoginPopup.addEventListener('click', () => {
            window.location.href = 'homepage.html';
        });
    }
});

// Remove any duplicate functions and keep only these OTP-related functions

function sendOTP() {
    const email = document.getElementById('email').value;
    if (!email || !email.endsWith('@vit.edu')) {
        showAlert('Please enter a valid VIT email address', 'error');
        return;
    }

    // Show loading state
    const sendOtpBtn = document.getElementById('sendOtpBtn');
    sendOtpBtn.disabled = true;
    sendOtpBtn.textContent = 'Sending...';

    fetch('handle_otp', {  // Changed from index.php to handle_otp.php
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'send_otp',
            'email': email
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Server response:', data); // Add this for debugging
        if (data.status) {
            showAlert('OTP sent successfully', 'success');
            document.querySelector('.otp-section').style.display = 'block';
            document.getElementById('sendOtpBtn').style.display = 'none';
            document.getElementById('verifyOtpBtn').style.display = 'inline-block';
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to send OTP. Please try again.', 'error');
    })
    .finally(() => {
        sendOtpBtn.disabled = false;
        sendOtpBtn.textContent = 'Send OTP';
    });
}

function verifyOTP() {
    const otp = document.getElementById('otp').value;
    const email = document.getElementById('email').value;

    if (!otp || otp.length !== 6) {
        showAlert('Please enter a valid 6-digit OTP', 'error');
        return;
    }

    fetch('handle_otp', {  // Changed from index.php to handle_otp.php
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'verify_otp',
            'otp': otp,
            'email': email
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Verify response:', data); // Add this for debugging
        if (data.status) {
            showAlert('OTP verified successfully', 'success');
            setTimeout(() => {
                window.location.href = '/user/';
            }, 1000);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Verification failed. Please try again.', 'error');
    });
}

function showAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 3000);
}

// Add these event listeners when the document loads
document.addEventListener('DOMContentLoaded', function() {
    // Clear any existing handlers
    const sendOtpBtn = document.getElementById('sendOtpBtn');
    const verifyOtpBtn = document.getElementById('verifyOtpBtn');
    const otpInput = document.getElementById('otp');

    if (sendOtpBtn) {
        sendOtpBtn.onclick = sendOTP;
    }

    if (verifyOtpBtn) {
        verifyOtpBtn.onclick = verifyOTP;
    }

    if (otpInput) {
        otpInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
            if (this.value.length === 6) {
                verifyOTP();
            }
        });
    }
});

// Remove all duplicate OTP functions and keep only these core functions
document.addEventListener('DOMContentLoaded', function() {
    const sendOtpBtn = document.getElementById('sendOtpBtn');
    const verifyOtpBtn = document.getElementById('verifyOtpBtn');
    const otpInput = document.getElementById('otp');

    // Show initial popup
    document.getElementById('loginPopup').classList.add('active');

    // Add click handler for Send OTP button
    if (sendOtpBtn) {
        sendOtpBtn.addEventListener('click', function() {
            const email = document.getElementById('email').value;
            if (!email || !email.endsWith('@vit.edu')) {
                showAlert('Please enter a valid VIT email address', 'error');
                return;
            }

            // Show loading state
            sendOtpBtn.disabled = true;
            sendOtpBtn.textContent = 'Sending...';

            fetch('handle_otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'action': 'send_otp',
                    'email': email
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Server response:', data);
                if (data.status) {
                    showAlert('OTP sent successfully', 'success');
                    document.querySelector('.otp-section').style.display = 'block';
                    sendOtpBtn.style.display = 'none';
                    verifyOtpBtn.style.display = 'inline-block';
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to send OTP. Please try again.', 'error');
            })
            .finally(() => {
                sendOtpBtn.disabled = false;
                sendOtpBtn.textContent = 'Send OTP';
            });
        });
    }

    // Add input handler for OTP verification
    if (otpInput) {
        otpInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
            if (this.value.length === 6) {
                verifyOTP();
            }
        });
    }

    // Add click handler for Verify OTP button
    if (verifyOtpBtn) {
        verifyOtpBtn.addEventListener('click', verifyOTP);
    }
});

function verifyOTP() {
    const otp = document.getElementById('otp').value;
    const email = document.getElementById('email').value;

    if (!otp || otp.length !== 6) {
        showAlert('Please enter a valid 6-digit OTP', 'error');
        return;
    }

    fetch('handle_otp', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'verify_otp',
            'otp': otp,
            'email': email
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Verify response:', data);
        if (data.status) {
            showAlert('OTP verified successfully', 'success');
            setTimeout(() => {
                window.location.href = '/user/';
            }, 1000);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Verification failed. Please try again.', 'error');
    });
}

function showAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 3000);
}

// Keep the popup related functions
function closeLoginPopup() {
    document.getElementById('loginPopup').classList.remove('active');
    window.location.href = 'homepage.html';
}

function proceedLogin() {
    document.getElementById('loginPopup').classList.remove('active');
    document.getElementById('loginForm').style.display = 'block';
}

function showOtpSentPopup() {
    document.getElementById('otpSentPopup').classList.add('active');
}

function closeOtpSentPopup() {
    document.getElementById('otpSentPopup').classList.remove('active');
}

// Define OTP functions
function sendOTP() {
    const email = document.getElementById('email').value;
    if (!email || !email.endsWith('@vit.edu')) {
        showAlert('Please enter a valid VIT email address', 'error');
        return;
    }

    // Show loading state
    const sendOtpBtn = document.getElementById('sendOtpBtn');
    sendOtpBtn.disabled = true;
    sendOtpBtn.textContent = 'Sending...';

    fetch('handle_otp', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'send_otp',
            'email': email
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Server response:', data);
        if (data.status) {
            showAlert('OTP sent successfully', 'success');
            document.querySelector('.otp-section').style.display = 'block';
            sendOtpBtn.style.display = 'none';
            document.getElementById('verifyOtpBtn').style.display = 'inline-block';
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to send OTP. Please try again.', 'error');
    })
    .finally(() => {
        sendOtpBtn.disabled = false;
        sendOtpBtn.textContent = 'Send OTP';
    });
}

function verifyOTP() {
    const otp = document.getElementById('otp').value;
    const email = document.getElementById('email').value;

    if (!otp || otp.length !== 6) {
        showAlert('Please enter a valid 6-digit OTP', 'error');
        return;
    }

    fetch('handle_otp', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'verify_otp',
            'otp': otp,
            'email': email
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Verify response:', data);
        if (data.status) {
            showAlert('OTP verified successfully', 'success');
            setTimeout(() => {
                window.location.href = '/user/';
            }, 1000);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Verification failed. Please try again.', 'error');
    });
}

function showAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 3000);
}

// Single DOMContentLoaded event listener
document.addEventListener('DOMContentLoaded', function() {
    // Show initial popup
    document.getElementById('loginPopup').classList.add('active');

    // Set up OTP handlers
    const sendOtpBtn = document.getElementById('sendOtpBtn');
    const verifyOtpBtn = document.getElementById('verifyOtpBtn');
    const otpInput = document.getElementById('otp');

    if (sendOtpBtn) {
        sendOtpBtn.addEventListener('click', sendOTP);
    }

    if (verifyOtpBtn) {
        verifyOtpBtn.addEventListener('click', verifyOTP);
    }

    if (otpInput) {
        otpInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
            if (this.value.length === 6) {
                verifyOTP();
            }
        });
    }
});

// Keep popup related functions
function closeLoginPopup() {
    document.getElementById('loginPopup').classList.remove('active');
    window.location.href = 'homepage.html';
}

function proceedLogin() {
    document.getElementById('loginPopup').classList.remove('active');
    document.getElementById('loginForm').style.display = 'block';
}

function showOtpSentPopup() {
    document.getElementById('otpSentPopup').classList.add('active');
}

function closeOtpSentPopup() {
    document.getElementById('otpSentPopup').classList.remove('active');
}

function resendOtpConfirm() {
    closeResendPopup();
    sendOTP();
}

document.addEventListener('DOMContentLoaded', function() {
    // Show initial popup
    document.getElementById('loginPopup').classList.add('active');

    // Setup form handlers
    const form = document.getElementById('otp-login-form');
    const sendOtpBtn = document.getElementById('sendOtpBtn');
    const verifyOtpBtn = document.getElementById('verifyOtpBtn');
    const resendOtpBtn = document.getElementById('resendOtpBtn');
    const otpInput = document.getElementById('otp');

    if (form) {
        form.onsubmit = (e) => e.preventDefault();
    }

    if (sendOtpBtn) {
        sendOtpBtn.onclick = sendOTP;
    }

    if (verifyOtpBtn) {
        verifyOtpBtn.onclick = verifyOTP;
    }

    if (resendOtpBtn) {
        resendOtpBtn.onclick = sendOTP;
    }

    if (otpInput) {
        otpInput.oninput = function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
            if (this.value.length === 6) {
                verifyOTP();
            }
        };
    }
});

function closeLoginPopup() {
    document.getElementById('loginPopup').classList.remove('active');
    window.location.href = 'homepage.html';
}

function proceedLogin() {
    document.getElementById('loginPopup').classList.remove('active');
    document.getElementById('loginForm').style.display = 'block';
}

function sendOTP() {
    const email = document.getElementById('email').value;
    if (!email || !email.endsWith('@vit.edu')) {
        showAlert('Please enter a valid VIT email address', 'error');
        return;
    }

    const sendOtpBtn = document.getElementById('sendOtpBtn');
    sendOtpBtn.disabled = true;
    sendOtpBtn.textContent = 'Sending...';

    fetch('handle_otp', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=send_otp&email=${encodeURIComponent(email)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            showAlert('OTP sent successfully', 'success');
            document.querySelector('.otp-section').style.display = 'block';
            sendOtpBtn.style.display = 'none';
            document.getElementById('verifyOtpBtn').style.display = 'inline-block';
            document.getElementById('resendOtpBtn').style.display = 'inline-block';
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to send OTP', 'error');
    })
    .finally(() => {
        sendOtpBtn.disabled = false;
        sendOtpBtn.textContent = 'Send OTP';
    });
}

// ...existing code until DOMContentLoaded...

// Remove all other DOMContentLoaded listeners and merge into one
document.addEventListener('DOMContentLoaded', function() {
    // Show initial popup
    document.getElementById('loginPopup').classList.add('active');

    // Setup event handlers
    const sendOtpBtn = document.getElementById('sendOtpBtn');
    const verifyOtpBtn = document.getElementById('verifyOtpBtn');
    const resendOtpBtn = document.getElementById('resendOtpBtn');
    const otpInput = document.getElementById('otp');
    const form = document.getElementById('otp-login-form');

    // Prevent form submission
    if (form) {
        form.addEventListener('submit', (e) => e.preventDefault());
    }

    // Add click handler for Send OTP button
    if (sendOtpBtn) {
        sendOtpBtn.addEventListener('click', function() {
            console.log('Send OTP button clicked'); // Debug log
            handleSendOTP();
        });
    }

    // Add click handler for Verify OTP button
    if (verifyOtpBtn) {
        verifyOtpBtn.addEventListener('click', handleVerifyOTP);
    }

    // Add click handler for Resend OTP button
    if (resendOtpBtn) {
        resendOtpBtn.addEventListener('click', handleSendOTP);
    }

    // Add input handler for OTP field
    if (otpInput) {
        otpInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
            if (this.value.length === 6) {
                handleVerifyOTP();
            }
        });
    }
});

// Define core OTP functions
function handleSendOTP() {
    const email = document.getElementById('email').value;
    if (!email || !email.endsWith('@vit.edu')) {
        showAlert('Please enter a valid VIT email address', 'error');
        return;
    }

    const sendOtpBtn = document.getElementById('sendOtpBtn');
    sendOtpBtn.disabled = true;
    sendOtpBtn.textContent = 'Sending...';

    console.log('Sending OTP request...'); // Debug log

    fetch('handle_otp', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'send_otp',
            'email': email
        })
    })
    .then(response => {
        console.log('Response received:', response); // Debug log
        return response.json();
    })
    .then(data => {
        console.log('Data:', data); // Debug log
        if (data.status) {
            showAlert('OTP sent successfully', 'success');
            document.querySelector('.otp-section').style.display = 'block';
            sendOtpBtn.style.display = 'none';
            document.getElementById('verifyOtpBtn').style.display = 'inline-block';
            document.getElementById('resendOtpBtn').style.display = 'inline-block';
        } else {
            showAlert(data.message || 'Failed to send OTP', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to send OTP. Please try again.', 'error');
    })
    .finally(() => {
        sendOtpBtn.disabled = false;
        sendOtpBtn.textContent = 'Send OTP';
    });
}

async function handleVerifyOTP() {
    const otp = document.getElementById('otp').value;
    const email = document.getElementById('email').value;

    if (!otp || otp.length !== 6) {
        showAlert('Please enter a valid 6-digit OTP', 'error');
        return;
    }

    try {
        const response = await fetch('handle_otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'action': 'verify_otp',
                'otp': otp,
                'email': email
            })
        });
        const data = await response.json();
        showAlert(data.message, data.status ? 'success' : 'error');
        if (data.status) {
            setTimeout(() => {
                window.location.href = '/user/';
            }, 1000);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Verification failed. Please try again.', 'error');
    }
}

// Helper functions
function showAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 3000);
}

// Popup functions
function closeLoginPopup() {
    document.getElementById('loginPopup').classList.remove('active');
    window.location.href = 'homepage.html';
}

function proceedLogin() {
    document.getElementById('loginPopup').classList.remove('active');
    document.getElementById('loginForm').style.display = 'block';
}

// ...rest of existing code...
