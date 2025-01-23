let currentSection = 1;
const totalSections = 5;
let isOtpVerified = false;
let isWelcomePopupClosed = false;

// Update questions array with all questions from old_index.html
const questions = [
    "I enjoy exploring new ideas and concepts.",
    "I am drawn to creative activities like art, music, or writing.",
    "I enjoy solving complex problems or puzzles.",
    "I seek out diverse cultural experiences, such as trying new cuisines or traveling to different places.",
    "I prefer working in teams rather than individually.",
    "I enjoy taking leadership roles in group projects.",
    "I am comfortable speaking in public.",
    "I adapt easily to new environments.",
    "I enjoy solving complex problems.",
    "I manage my time effectively.",
    "I am open to receiving constructive criticism.",
    "I remain calm under pressure.",
    "I enjoy learning new technologies.",
    "I prefer structured schedules over flexible ones.",
    "I am comfortable with remote/online learning.",
    "I actively participate in class discussions.",
    "I seek help when facing difficulties.",
    "I enjoy creative problem-solving.",
    "I enjoy mentoring others.",
    "I maintain a good work-life balance."
];

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

        // Add email verification section only at the end of section 5
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
                        <div class="loader" id="otpLoader"></div>
                        <div class="form-group">
                            <input type="text" id="otp" placeholder="Enter 6-digit OTP" maxlength="6">
                        </div>
                        <button type="button" class="btn verify-btn" id="verifyOtpBtn" onclick="verifyOTP()">Verify OTP</button>
                        <div class="loader" id="verifyLoader"></div>
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
                <button type="submit" class="btn" id="submitBtn" disabled>Submit</button>
            `;
        }
        
        sectionDiv.appendChild(buttonContainer);
        form.appendChild(sectionDiv);
    }
}

function nextSection(section) {
    if (!isWelcomePopupClosed) {
        alert('Please click "Let\'s Begin" to start the registration.');
        return;
    }
    if (!validateSection(section)) return;
    
    document.getElementById(`section${section}`).classList.add('hidden');
    document.getElementById(`section${section + 1}`).classList.remove('hidden');
    currentSection = section + 1;
    updateProgress();

    // Scroll to top of the page smoothly
    window.scrollTo({
        top: 0,
    });
}

function prevSection(section) {
    if (!isWelcomePopupClosed) {
        alert('Please click "Let\'s Begin" to start the registration.');
        return;
    }
    document.getElementById(`section${section}`).classList.add('hidden');
    document.getElementById(`section${section - 1}`).classList.remove('hidden');
    currentSection = section - 1;
    updateProgress();

    // Also add smooth scroll to top for previous button
    window.scrollTo({
        top: 0,
    });
}

function updateProgress() {
    const progress = ((currentSection - 1) / totalSections) * 100;
    const progressBar = document.getElementById('progressBar');
    if(progressBar) {
        progressBar.style.width = `${progress}%`;
    }
}

function validateSection(section) {
    const currentSectionElement = document.getElementById(`section${section}`);
    let isValid = true;
    let firstUnanswered = null;
    
    if(section === 1) {
        // Validate basic info section
        const requiredInputs = currentSectionElement.querySelectorAll('input[required], select[required]');
        requiredInputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('error');
                if (!firstUnanswered) firstUnanswered = input;
            } else {
                input.classList.remove('error');
            }
        });
    } else {
        // Validate question sections
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
                questionCard.classList.add('error-card');
                questionCard.classList.remove('error-shake');
                void questionCard.offsetWidth; // Trigger reflow
                questionCard.classList.add('error-shake');
                
                if (!firstUnanswered) {
                    firstUnanswered = questionCard;
                }
            }
        }
    }
    
    // Scroll to the first unanswered question/field if validation fails
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

    // Remove fun fact from validation since it's optional
    
    // Email verification check
    const email = document.getElementById('email');
    if (!email.value || !email.value.endsWith('@vit.edu')) {
        isValid = false;
        email.classList.add('error');
        if (!firstUnanswered) firstUnanswered = email;
    }

    // Update OTP verification check
    if (!isOtpVerified) {
        isValid = false;
        alert('Please verify your email with OTP first');
        return false;
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

// Add these functions for email verification
function sendOTP() {
    const email = document.getElementById('email').value;
    if (!email.endsWith('@vit.edu')) {
        alert('Please enter a valid VIT email address');
        return;
    }
    
    // Show loader and hide send button
    document.getElementById('sendOtpBtn').style.display = 'none';
    document.getElementById('otpLoader').style.display = 'block';
    
    const formData = new FormData();
    formData.append('email', email);

    fetch('send_otp.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Hide loader
        document.getElementById('otpLoader').style.display = 'none';
        
        if (data.success) {
            // Show verify button and OTP field
            document.getElementById('verifyOtpBtn').style.display = 'block';
            document.getElementById('otp').style.display = 'block';
            showOtpPopup('otpSentPopup');
        } else {
            alert('Failed to send OTP: ' + data.message);
            document.getElementById('sendOtpBtn').style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to send OTP. Please try again.');
        document.getElementById('sendOtpBtn').style.display = 'block';
        document.getElementById('otpLoader').style.display = 'none';
    });
}

function verifyOTP() {
    const otp = document.getElementById('otp').value;
    
    // Show loader and hide verify button
    document.getElementById('verifyOtpBtn').style.display = 'none';
    document.getElementById('verifyLoader').style.display = 'block';
    
    const formData = new FormData();
    formData.append('otp', otp);

    fetch('verify_otp.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Hide loader
        document.getElementById('verifyLoader').style.display = 'none';
        
        if (data.success) {
            isOtpVerified = true;
            showOtpPopup('otpVerifiedPopup');
            document.getElementById('submitBtn').disabled = false;
            document.getElementById('verifyOtpBtn').style.display = 'none';
            document.getElementById('otp').disabled = true;
        } else {
            showOtpPopup('otpErrorPopup');
            document.getElementById('verifyOtpBtn').style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to verify OTP. Please try again.');
        document.getElementById('verifyOtpBtn').style.display = 'block';
        document.getElementById('verifyLoader').style.display = 'none';
    });
}

function showOtpPopup(popupId) {
    // First, hide all popups
    const popups = document.querySelectorAll('.popup-overlay');
    popups.forEach(popup => popup.classList.remove('active'));
    
    // Then show the requested popup
    const popup = document.getElementById(popupId);
    if (popup) {
        popup.classList.add('active');
    }
}

function closeOtpPopup(popupId) {
    const popup = document.getElementById(popupId);
    if (popup) {
        popup.classList.remove('active');
    }
}

function closeWelcomePopup() {
    const welcomePopup = document.getElementById('welcomePopup');
    if (welcomePopup) {
        welcomePopup.classList.remove('active');
        isWelcomePopupClosed = true;
        document.querySelector('.container').style.display = 'block';
    }
}

function calculateCategoryScores() {
    const categories = {
        category1: [1, 2, 3, 4],
        category2: [5, 6, 7, 8],
        category3: [9, 10, 11, 12],
        category4: [13, 14, 15, 16],
        category5: [17, 18, 19, 20]
    };

    const scores = {};

    // Calculate average for each category
    for (let category in categories) {
        const questions = categories[category];
        let sum = 0;
        questions.forEach(qNum => {
            const selected = document.querySelector(`input[name="q${qNum}"]:checked`);
            if (selected) {
                sum += parseInt(selected.value);
            }
        });
        scores[category] = Math.round((sum / 4) * 100) / 100; // Average rounded to 2 decimal places
    }

    return scores;
}

function submitForm() {
    if (!validateAllQuestions()) return;

    // Debug logs
    console.log('Form validation passed, preparing to submit...');

    // Get form data
    const formData = {
        name: document.getElementById('name').value,
        age: document.getElementById('age').value,
        gender: document.getElementById('gender').value,
        campus: document.getElementById('campus').value,
        email: document.getElementById('email').value,
        funFact: document.getElementById('funFact').value || ''
    };

    // Calculate and add category scores
    const categoryScores = calculateCategoryScores();
    Object.assign(formData, {
        category1: categoryScores.category1,
        category2: categoryScores.category2,
        category3: categoryScores.category3,
        category4: categoryScores.category4,
        category5: categoryScores.category5
    });

    console.log('Sending data:', formData);

    // Send to server with correct URL and method
    fetch('https://thegroup11.com/registration/submit.php', { // Updated URL
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Origin': window.location.origin
        },
        mode: 'cors', // Add CORS mode
        credentials: 'include',
        body: JSON.stringify(formData)
    })
    .then(response => {
        console.log('Raw Response:', response);
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error Response:', text);
                throw new Error(`Server error: ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Parsed Response:', data);
        if (data.success) {
            alert('Registration completed successfully!');
            window.location.href = 'success.html';
        } else {
            throw new Error(data.message || 'Unknown error occurred');
        }
    })
    .catch(error => {
        console.error('Submission Error:', error);
        alert('An error occurred while submitting the form. Please check console for details.');
    });
}

// Initialize the form
document.addEventListener('DOMContentLoaded', () => {
    generateQuestions();
    setupErrorClearance();
    updateProgress(); // Initialize progress bar
    
    // Hide the form container initially
    document.querySelector('.container').style.display = 'none';
    
    // Show welcome popup by default (it's already active in HTML)
    document.getElementById('welcomePopup').classList.add('active');
    
    document.getElementById('registrationForm').addEventListener('submit', (e) => {
        e.preventDefault();
        console.log('Form submission started');
        submitForm();
    });
});
