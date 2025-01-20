// Fix the DOMContentLoaded event syntax and initialization
document.addEventListener("DOMContentLoaded", function() {
    // Initialize global variables
    window.currentSection = 1;
    const totalSections = 5;
    let isOtpVerified = false;
    
    // Generate questions and setup form
    generateQuestions();
    setupErrorClearance();
    updateProgress();
    
    // Rest of your initialization code...
});

const questions = [
    "I prefer working in teams rather than individually",
    "I enjoy taking leadership roles in group projects",
    "I am comfortable speaking in public",
    "I adapt easily to new environments",
    "I enjoy solving complex problems",
    "I manage my time effectively",
    "I am open to receiving constructive criticism",
    "I remain calm under pressure",
    "I enjoy learning new technologies",
    "I prefer structured schedules over flexible ones",
    "I am comfortable with remote/online learning",
    "I actively participate in class discussions",
    "I seek help when facing difficulties",
    "I enjoy creative problem-solving",
    "I set clear goals for myself",
    "I am punctual with deadlines",
    "I take initiative in group settings",
    "I am comfortable with change",
    "I enjoy mentoring others",
    "I maintain a good work-life balance"
];

// Add global variables at the top
let otpTimer = null;
let isOtpVerified = false;

// Generate question sections
function generateQuestions() {
    const form = document.getElementById('registrationForm');
    
    for(let section = 2; section <= 5; section++) {
        const sectionDiv = document.createElement('section');
        sectionDiv.className = 'form-section hidden';
        sectionDiv.id = `section${section}`;
        
        const startQuestion = ((section - 2) * 5);
        
        for(let i = startQuestion; i < startQuestion + 5; i++) {
            sectionDiv.innerHTML += `
                <div class="question-card">
                    <h3>${questions[i]}</h3>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="q${i+1}" value="5" required>
                            <span class="custom-radio"></span>
                            Strongly Agree
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="q${i+1}" value="4">
                            <span class="custom-radio"></span>
                            Agree
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="q${i+1}" value="3">
                            <span class="custom-radio"></span>
                            Neutral
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="q${i+1}" value="2">
                            <span class="custom-radio"></span>
                            Disagree
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="q${i+1}" value="1">
                            <span class="custom-radio"></span>
                            Strongly Disagree
                        </label>
                    </div>
                </div>
            `;
        }

        // Navigation buttons
        const buttonContainer = document.createElement('div');
        buttonContainer.className = 'button-group';
        
        if(section < 5) {
            buttonContainer.innerHTML = `
                <button type="button" class="btn" onclick="prevSection(${section})">Previous</button>
                <button type="button" class="btn" onclick="nextSection(${section})">Next</button>
            `;
        } else {
            buttonContainer.innerHTML = `
                <button type="button" class="btn" onclick="prevSection(${section})">Previous</button>
                <button type="submit" class="btn">Submit</button>
            `;
        }
        
        sectionDiv.appendChild(buttonContainer);
        form.appendChild(sectionDiv);

        // Add email verification section at the end of section 5
        if(section === 5) {
            sectionDiv.innerHTML += `
                <div class="email-verification-section">
                    <h3>Share something interesting about yourself (Optional)</h3>
                    <div class="form-group">
                        <label for="funFact">Fun Fact</label>
                        <textarea id="funFact" 
                            class="fun-fact-textarea" 
                            placeholder="Tell us something unique about yourself (hobbies, achievements, interests...)"
                            rows="5"
                        ></textarea>
                    </div>
                    <h3>Email Verification</h3>
                    <div class="form-group">
                        <label for="email">VIT Email Address</label>
                        <input type="email" id="email" placeholder="yourname@vit.edu" required>
                    </div>
                    <div class="otp-section">
                        <button type="button" class="btn" id="sendOtpBtn" onclick="sendOTP()">Send OTP</button>
                        <span id="otpTimer" class="timer-text"></span>
                        <div class="loader" id="otpLoader"></div>
                        <div class="form-group">
                            <input type="text" id="otp" placeholder="Enter 6-digit OTP" maxlength="6">
                        </div>
                        <button type="button" class="btn verify-btn" id="verifyOtpBtn" onclick="verifyOTP()">Verify OTP</button>
                        <div class="loader" id="verifyLoader"></div>
                    </div>
                    <div class="button-group">
                        <button type="button" class="btn" onclick="prevSection(5)">Previous</button>
                        <button type="submit" class="btn submit-btn" id="submitBtn" disabled>Submit</button>
                    </div>
                </div>
            `;
        }
    }
}

// Fix section transitions
function nextSection(section) {
    if (!validateSection(section)) return;
    
    const currentSectionElement = document.getElementById(`section${section}`);
    const nextSectionElement = document.getElementById(`section${section + 1}`);
    
    if (!currentSectionElement || !nextSectionElement) return;

    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        // Ensure smooth transition
        currentSectionElement.style.position = 'absolute';
        nextSectionElement.style.position = 'absolute';
        
        currentSectionElement.classList.add('hidden');
        
        // Small delay to ensure smooth animation
        setTimeout(() => {
            nextSectionElement.style.display = 'block';
            nextSectionElement.classList.remove('hidden');
            nextSectionElement.classList.add('entering');
            
            window.currentSection = section + 1;
            updateProgress();
            
            setTimeout(() => {
                nextSectionElement.classList.remove('entering');
            }, 600);
        }, 50);
    } else {
        // PC transition
        currentSectionElement.classList.add('hidden');
        nextSectionElement.style.display = 'block';
        nextSectionElement.classList.remove('hidden');
        nextSectionElement.classList.add('entering');
        
        setTimeout(() => {
            nextSectionElement.classList.remove('entering');
        }, 400);
    }
    
    window.currentSection = section + 1;
    updateProgress();
}

function prevSection(section) {
    document.getElementById(`section${section}`).classList.add('hidden');
    document.getElementById(`section${section - 1}`).classList.remove('hidden');
    currentSection = section - 1;
    updateProgress();
}

// Update progress bar calculation
function updateProgress() {
    const progress = ((window.currentSection - 1) / (totalSections - 1)) * 100;
    const progressBar = document.getElementById('progressBar');
    if(progressBar) {
        progressBar.style.width = `${progress}%`;
        // Force repaint
        void progressBar.offsetWidth;
    }
}

function validateSection(section) {
    const currentSectionElement = document.getElementById(`section${section}`);
    let isValid = true;
    let firstUnanswered = null;
    
    if(section === 1) {
        // Basic info validation
        const requiredInputs = currentSectionElement.querySelectorAll('input[required], select[required]');
        requiredInputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.remove('error');
                void input.offsetWidth; // Trigger reflow
                input.classList.add('error');
                if (!firstUnanswered) firstUnanswered = input;
            } else {
                input.classList.remove('error');
            }
        });
    } else {
        // Question validation
        const startQuestion = ((section - 2) * 5) + 1;
        const endQuestion = startQuestion + 5;
        
        for(let i = startQuestion; i < endQuestion; i++) {
            const radioGroup = document.getElementsByName(`q${i}`);
            let questionAnswered = false;
            
            radioGroup.forEach(radio => {
                if(radio.checked) questionAnswered = true;
            });
            
            if(!questionAnswered) {
                isValid = false;
                const questionCard = radioGroup[0].closest('.question-card');
                questionCard.classList.remove('error-card');
                void questionCard.offsetWidth; // Trigger reflow
                questionCard.classList.add('error-card');
                
                if (!firstUnanswered) {
                    firstUnanswered = questionCard;
                }
            }
        }
    }
    
    if (!isValid && firstUnanswered) {
        setTimeout(() => {
            firstUnanswered.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center'
            });
        }, 100);
    }
    
    return isValid;
}

function validateAllQuestions() {
    let isValid = true;
    let firstUnanswered = null;

    // First validate basic info
    const basicInfo = document.getElementById('section1');
    const requiredBasicInputs = basicInfo.querySelectorAll('input[required], select[required]');
    requiredBasicInputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error');
            if (!firstUnanswered) firstUnanswered = input;
        }
    });

    // Then validate all questions
    for(let i = 1; i <= 20; i++) {
        const radioGroup = document.getElementsByName(`q${i}`);
        let questionAnswered = false;
        
        radioGroup.forEach(radio => {
            if(radio.checked) questionAnswered = true;
        });
        
        if(!questionAnswered) {
            isValid = false;
            const questionCard = radioGroup[0].closest('.question-card');
            questionCard.classList.add('error-card');
            questionCard.classList.remove('error-shake');
            void questionCard.offsetWidth;
            questionCard.classList.add('error-shake');
            
            if (!firstUnanswered) {
                firstUnanswered = questionCard;
            }
        }
    }

    // Scroll to first unanswered question/field with a slight delay for better UX
    if (!isValid && firstUnanswered) {
        const section = firstUnanswered.closest('.form-section');
        if (section) {
            // First show the section containing the unanswered question
            document.querySelectorAll('.form-section').forEach(s => s.classList.add('hidden'));
            section.classList.remove('hidden');
            currentSection = parseInt(section.id.replace('section', ''));
            updateProgress();
            
            // Then scroll to the specific question
            setTimeout(() => {
                firstUnanswered.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center'
                });
            }, 100);
        }
    }

    if (currentSection === 5) {
        const email = document.getElementById('email');
        if (!email.value || !email.value.endsWith('@vit.edu')) {
            isValid = false;
            email.classList.add('error');
            if (!firstUnanswered) firstUnanswered = email;
        }

        if (!isOtpVerified) {
            isValid = false;
            alert('Please verify your email with OTP first');
            return false;
        }
    }

    return isValid;
}

// Remove error highlighting when an option is selected
function setupErrorClearance() {
    document.querySelectorAll('.radio-option input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const card = this.closest('.question-card');
            if(card) card.classList.remove('error-card');
        });
    });
}

// Initialize the form
document.addEventListener('DOMContentLoaded', () => {
    generateQuestions();
    setupErrorClearance();
    updateProgress(); // Initialize progress bar
    
    document.getElementById('registrationForm').addEventListener('submit', (e) => {
        e.preventDefault();
        if(validateAllQuestions()) {
            // Handle successful form submission here
            alert('Form submitted successfully!');
        }
    });
    
    // Prevent elastic scrolling on iOS
    document.body.addEventListener('touchmove', function(e) {
        if (document.querySelector('#section1').contains(e.target)) {
            e.preventDefault();
        }
    }, { passive: false });
    
    // Add animation delays to form groups
    document.querySelectorAll('.form-group').forEach((group, index) => {
        group.style.setProperty('--i', index);
    });
    
    // Show welcome popup on load
    document.getElementById('welcomePopup').classList.add('active');
    
    // Fix for iOS height
    setVH();
});

// Fix iOS height issues
function setVH() {
    let vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
}

window.addEventListener('resize', setVH);
window.addEventListener('orientationchange', setVH);
setVH();

function closeWelcomePopup() {
    document.getElementById('welcomePopup').classList.remove('active');
}

function closeOtpPopup(popupId) {
    document.getElementById(popupId).classList.remove('active');
}

function showPopup(popupId) {
    document.getElementById(popupId).classList.add('active');
}

// Add OTP timer function
function startOtpTimer() {
    const timerSpan = document.getElementById('otpTimer');
    const resendBtn = document.getElementById('sendOtpBtn');
    let timeLeft = 30;

    resendBtn.disabled = true;
    resendBtn.classList.add('btn-disabled');

    otpTimer = setInterval(() => {
        timerSpan.textContent = `(${timeLeft}s)`;
        timeLeft--;

        if (timeLeft < 0) {
            clearInterval(otpTimer);
            timerSpan.textContent = '';
            resendBtn.disabled = false;
            resendBtn.classList.remove('btn-disabled');
        }
    }, 1000);
}

function sendOTP() {
    const email = document.getElementById('email').value;
    if (!email.endsWith('@vit.edu')) {
        alert('Please enter a valid VIT email address');
        return;
    }
    
    const sendBtn = document.getElementById('sendOtpBtn');
    sendBtn.style.display = 'inline-block';
    document.getElementById('otpLoader').style.display = 'block';
    
    setTimeout(() => {
        document.getElementById('otpLoader').style.display = 'none';
        document.getElementById('verifyOtpBtn').style.display = 'block';
        document.getElementById('otp').style.display = 'block';
        showOtpPopup('otpSentPopup');
        startOtpTimer();
    }, 2000);
}

function verifyOTP() {
    const otp = document.getElementById('otp').value;
    
    document.getElementById('verifyOtpBtn').style.display = 'none';
    document.getElementById('verifyLoader').style.display = 'block';
    
    setTimeout(() => {
        document.getElementById('verifyLoader').style.display = 'none';
        
        if (otp === '1234') {
            isOtpVerified = true;
            showOtpPopup('otpVerifiedPopup');
            document.getElementById('submitBtn').disabled = false;
            document.getElementById('submitBtn').classList.remove('btn-disabled');
            document.getElementById('verifyOtpBtn').style.display = 'none';
            document.getElementById('otp').disabled = true;
            document.getElementById('sendOtpBtn').style.display = 'none';
            document.getElementById('otpTimer').style.display = 'none';
        } else {
            showOtpPopup('otpErrorPopup');
            document.getElementById('verifyOtpBtn').style.display = 'block';
        }
    }, 1500);
}
