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

function startTest() {
  const name = document.getElementById('name').value;
  const age = document.getElementById('age').value;
  
  if (!name || !age || !selectedGender || !selectedCampus) {
    alert('Please fill in all fields before proceeding.');
    return;
  }
  
  document.getElementById('registration-form').style.display = 'none';
  document.getElementById('test-section').style.display = 'block';
  updateView(); // Initialize the test view
}

// Replace the existing sendOTP function with this one
function sendOTP() {
    const email = document.getElementById('email').value;
    if (!email || !email.endsWith('@vit.edu')) {
        alert('Please enter a valid VIT email address');
        return;
    }

    fetch('index.php', {
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

    fetch('index.php', {
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
  const email = document.getElementById('email').value;
  if (!email || !email.endsWith('@vit.edu')) {
    alert('Please enter a valid email address');
    return false;
  }
  return true;
}

// Add event listeners when document loads
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
  if (!checkAllRequirements()) return;

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
