document.addEventListener('DOMContentLoaded', function() {
    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Mobile menu toggle
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');
    if (hamburger) {
        hamburger.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('active');
            navLinks.classList.toggle('active');
            document.body.classList.toggle('no-scroll');
        });
    }

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        if (navLinks && hamburger) {
            const isClickInside = navLinks.contains(event.target) || hamburger.contains(event.target);
            if (!isClickInside && navLinks.classList.contains('active')) {
                navLinks.classList.remove('active');
                document.body.classList.remove('no-scroll');
            }
        }
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                // Close mobile menu if open
                if (navLinks && navLinks.classList.contains('active')) {
                    navLinks.classList.remove('active');
                    document.body.classList.remove('no-scroll');
                }
                
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });

    // FAQ accordion
    const faqItems = document.querySelectorAll('.faq-item');
    if (faqItems && faqItems.length > 0) {
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            if (question) {
                question.addEventListener('click', () => {
                    // Close all other items
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item && otherItem.classList.contains('active')) {
                            otherItem.classList.remove('active');
                        }
                    });

                    // Toggle current item
                    item.classList.toggle('active');
                });
            }
        });
    }

    // Form submission handling
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value;
            
            // Simple validation
            if (!name || !email || !subject || !message) {
                showNotification('Please fill out all fields', 'error');
                return;
            }
            
            // Send email to support
            sendContactFormEmail(name, email, subject, message);
            
            // Show success message
            const submitBtn = contactForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            submitBtn.textContent = 'Message Sent!';
            submitBtn.disabled = true;
            submitBtn.style.backgroundColor = '#4CAF50';
            
            // Reset form
            contactForm.reset();
            
            // Reset button after 3 seconds
            setTimeout(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                submitBtn.style.backgroundColor = '';
            }, 3000);

            // Show notification
            showNotification('Your message has been sent successfully!', 'success');
        });
    }

    // Notify form - fixed to ensure proper POST request
    const notifyForm = document.querySelector('.notify-form');
    if (notifyForm) {
        const notifyInput = notifyForm.querySelector('input');
        const notifyButton = notifyForm.querySelector('button');
        
        // Make sure we're preventing any default form behavior
        notifyForm.addEventListener('submit', function(e) {
            e.preventDefault();
        });
        
        notifyButton.addEventListener('click', (e) => {
            // Prevent default button behavior to ensure no GET request
            e.preventDefault();
            
            const email = notifyInput.value;
            
            // Simple validation
            if (!email || !email.includes('@') || !email.includes('.')) {
                showNotification('Please enter a valid email address', 'error');
                return;
            }
            
            // Check for VIT email
            if (!email.endsWith('@vit.edu')) {
                showNotification('Please use your VIT institutional email (@vit.edu)', 'error');
                return;
            }
            
            // Show loading state
            notifyButton.disabled = true;
            notifyButton.innerHTML = '<span class="spinning-loader"></span> Processing...';
            
            // Use XMLHttpRequest instead of fetch for more explicit control
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'save_email', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function() {
                if (this.readyState === 4) {
                    if (this.status === 200) {
                        try {
                            const response = JSON.parse(this.responseText);
                            if (response.success) {
                                // If saved successfully, send confirmation email using API
                                sendThankYouEmail(email);
                                
                                // Success message
                                notifyButton.textContent = 'You\'re on the list!';
                                notifyInput.disabled = true;
                                notifyInput.value = '';
                                notifyInput.placeholder = 'We\'ll notify you when Project SparkUp launches';
                                
                                // Show notification
                                showNotification('You\'ve been added to our waitlist!', 'success');
                                
                                // Reset after 3 seconds
                                setTimeout(() => {
                                    notifyButton.textContent = 'Notify Me';
                                    notifyButton.disabled = false;
                                    notifyInput.disabled = false;
                                    notifyInput.placeholder = 'Your VIT Email Address';
                                }, 3000);
                            } else {
                                showNotification('Error: ' + (response.message || 'Could not save your email'), 'error');
                                notifyButton.textContent = 'Notify Me';
                                notifyButton.disabled = false;
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            showNotification('Server error. Please try again later.', 'error');
                            notifyButton.textContent = 'Notify Me';
                            notifyButton.disabled = false;
                        }
                    } else {
                        console.error('Request failed with status:', this.status);
                        showNotification('Server error. Please try again later.', 'error');
                        notifyButton.textContent = 'Notify Me';
                        notifyButton.disabled = false;
                    }
                }
            };
            
            // Send the request with properly encoded form data
            xhr.send('email=' + encodeURIComponent(email));
            
            // Additional debugging
            console.log('POST request sent to: save_email.php');
            console.log('With email data:', email);
        });
    }
    
    // Update contact and copyright information
    updateContactInfo();
    
    // Mobile-friendly touch interaction improvements
    setupMobileInteractions();
});

// Toast notification function
function showNotification(message, type = 'info') {
    // Create notification element if it doesn't exist
    if (!document.querySelector('.notification-container')) {
        const container = document.createElement('div');
        container.className = 'notification-container';
        document.body.appendChild(container);
    }
    
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    // Add icon based on notification type
    let icon = '';
    switch (type) {
        case 'success':
            icon = '<i class="fas fa-check-circle"></i> ';
            break;
        case 'error':
            icon = '<i class="fas fa-exclamation-circle"></i> ';
            break;
        case 'info':
            icon = '<i class="fas fa-info-circle"></i> ';
            break;
    }
    
    notification.innerHTML = `${icon}${message}`;
    
    document.querySelector('.notification-container').appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 4000);
}

function updateContactInfo() {
    // Update all SparkUp mentions to Project SparkUp
    document.querySelectorAll('.logo a, footer h2, .footer-logo h2').forEach(element => {
        if (element && element.textContent === 'SparkUp') {
            element.textContent = 'Project SparkUp';
        }
    });
    
    // Update contact information
    const footerContacts = document.querySelectorAll('.footer-contact');
    footerContacts.forEach(contact => {
        const emailElement = contact.querySelector('.fa-envelope')?.parentElement;
        const phoneElement = contact.querySelector('.fa-phone')?.parentElement;
        
        if (emailElement) {
            emailElement.innerHTML = '<i class="fas fa-envelope"></i> team@thegroup11.com';
        }
        
        if (phoneElement) {
            phoneElement.innerHTML = '<i class="fas fa-phone"></i> +91 9307026265';
        }
    });
    
    // Update copyright text
    const footerBottom = document.querySelector('.footer-bottom');
    if (footerBottom) {
        footerBottom.innerHTML = `
            <p>© 2025 Project SparkUp. All rights reserved.</p>
            <p>A project by TheGroup11©</p>
        `;
    }
}

function setupMobileInteractions() {
    if ('ontouchstart' in window) {
        document.querySelectorAll('.process-step, .feature-card, .team-member').forEach(card => {
            card.addEventListener('touchstart', function() {
                this.classList.add('touch-active');
            }, {passive: true});
            
            card.addEventListener('touchend', function() {
                setTimeout(() => {
                    this.classList.remove('touch-active');
                }, 300);
            }, {passive: true});
        });
    }
    
    // Improve scrolling performance
    let isScrolling;
    window.addEventListener('scroll', function() {
        document.body.classList.add('is-scrolling');
        clearTimeout(isScrolling);
        
        isScrolling = setTimeout(() => {
            document.body.classList.remove('is-scrolling');
        }, 100);
    }, {passive: true});
}

// Helper function to send thank you email
function sendThankYouEmail(userEmail) {
    // HTML template for email - Personalized welcome message
    const emailHtml = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Thank you for joining Project SparkUp!</title>
            <style>
                body {
                    font-family: 'Arial', sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f7fa;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #ffffff;
                    border-radius: 8px;
                    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
                }
                .header {
                    text-align: center;
                    padding: 20px 0;
                    border-bottom: 1px solid #eaeaea;
                }
                .header img {
                    max-width: 200px;
                    height: auto;
                }
                .content {
                    padding: 30px 20px;
                    text-align: center;
                }
                h1 {
                    color: #3a7bd5;
                    margin-bottom: 20px;
                }
                p {
                    margin-bottom: 20px;
                    color: #555;
                }
                .button {
                    display: inline-block;
                    padding: 12px 25px;
                    background: linear-gradient(135deg, #3a7bd5, #00d2ff);
                    color: white;
                    text-decoration: none;
                    border-radius: 30px;
                    font-weight: bold;
                    margin: 15px 0;
                }
                .footer {
                    text-align: center;
                    color: #888;
                    font-size: 14px;
                    padding: 20px;
                    border-top: 1px solid #eaeaea;
                }
                .highlight {
                    color: #3a7bd5;
                    font-weight: bold;
                }
                .personalized {
                    font-style: italic;
                    background-color: #f0f7ff;
                    padding: 15px;
                    border-radius: 8px;
                    margin: 20px 0;
                    border-left: 4px solid #3a7bd5;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2 style="color: #3a7bd5;">Project SparkUp</h2>
                </div>
                <div class="content">
                    <h1>Thank You for Joining Our Waitlist!</h1>
                    
                    <div class="personalized">
                        <p>Hello <span class="highlight">${userEmail.split('@')[0]}</span>, we're thrilled that you're interested in Project SparkUp!</p>
                        <p>Your email has been registered with our waitlist and you'll be among the first to know when we launch at your campus.</p>
                    </div>
                    
                    <p>Project SparkUp is reimagining campus connections through AI-powered matchmaking, and we can't wait to help you find meaningful connections with fellow students who have complementary personalities and interests.</p>
                    
                    <p>Get ready to discover study partners, project collaborators, and friends through our structured 5-minute interactions that eliminate social anxiety and awkwardness.</p>
                    
                    <a href="https://thegroup11.com/enroll" class="button">Learn More</a>
                    
                    <p style="margin-top: 30px;">Got questions? We're here to help! Reach out to us anytime at <a href="mailto:team@thegroup11.com">team@thegroup11.com</a></p>
                    
                    <p>Looking forward to helping you create connections that spark!</p>
                    <p>- <span class="highlight">The Project SparkUp Team</span></p>
                </div>
                <div class="footer">
                    <p>&copy; 2025 Project SparkUp. All rights reserved.</p>
                    <p>A project by TheGroup11</p>
                    <p><a href="https://instagram.com/projectsparkup">Follow us on Instagram</a></p>
                </div>
            </div>
        </body>
        </html>
    `;
    
    // URL encode the HTML message
    const encodedMessage = encodeURIComponent(emailHtml);
    const subject = encodeURIComponent("Welcome to Project SparkUp's Waitlist!");
    
    // Call the email API
    fetch(`https://thegroup11.com/api/sendmail?api_key=dGhlZ3JvdXAxMQ==&to=${userEmail}&subject=${subject}&message=${encodedMessage}`);
}

// Helper function to send contact form emails
function sendContactFormEmail(name, email, subject, message) {
    // HTML template for contact form submission
    const emailHtml = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>New Contact Form Submission</title>
            <style>
                body {
                    font-family: 'Arial', sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f7fa;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #ffffff;
                    border-radius: 8px;
                    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
                }
                .header {
                    text-align: center;
                    padding: 20px 0;
                    border-bottom: 1px solid #eaeaea;
                }
                .content {
                    padding: 30px 20px;
                }
                h1 {
                    color: #3a7bd5;
                    margin-bottom: 20px;
                }
                .field {
                    margin-bottom: 20px;
                }
                .field-label {
                    font-weight: bold;
                    color: #555;
                }
                .field-value {
                    margin-top: 5px;
                    padding: 10px;
                    background-color: #f9f9f9;
                    border-radius: 4px;
                }
                .footer {
                    text-align: center;
                    color: #888;
                    font-size: 14px;
                    padding: 20px;
                    border-top: 1px solid #eaeaea;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2 style="color: #3a7bd5;">Project SparkUp - Contact Form</h2>
                </div>
                <div class="content">
                    <h1>New Contact Form Submission</h1>
                    <div class="field">
                        <div class="field-label">Name:</div>
                        <div class="field-value">${name}</div>
                    </div>
                    <div class="field">
                        <div class="field-label">Email:</div>
                        <div class="field-value">${email}</div>
                    </div>
                    <div class="field">
                        <div class="field-label">Subject:</div>
                        <div class="field-value">${subject}</div>
                    </div>
                    <div class="field">
                        <div class="field-label">Message:</div>
                        <div class="field-value">${message}</div>
                    </div>
                </div>
                <div class="footer">
                    <p>&copy; 2025 Project SparkUp. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
    `;
    
    // URL encode the HTML message
    const encodedMessage = encodeURIComponent(emailHtml);
    const emailSubject = encodeURIComponent(`Contact Form: ${subject}`);
    
    // Call the email API
    fetch(`https://thegroup11.com/api/sendmail?api_key=dGhlZ3JvdXAxMQ==&to=support@thegroup11.com&subject=${emailSubject}&message=${encodedMessage}`);
    
    // Also send a confirmation email to the user
    sendContactConfirmationEmail(name, email);
}

// Function to send confirmation email to users who submit contact forms
function sendContactConfirmationEmail(name, email) {
    const emailHtml = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>We've Received Your Message</title>
            <style>
                body {
                    font-family: 'Arial', sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f7fa;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #ffffff;
                    border-radius: 8px;
                    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
                }
                .header {
                    text-align: center;
                    padding: 20px 0;
                    border-bottom: 1px solid #eaeaea;
                }
                .content {
                    padding: 30px 20px;
                    text-align: center;
                }
                h1 {
                    color: #3a7bd5;
                    margin-bottom: 20px;
                }
                p {
                    margin-bottom: 20px;
                    color: #555;
                }
                .footer {
                    text-align: center;
                    color: #888;
                    font-size: 14px;
                    padding: 20px;
                    border-top: 1px solid #eaeaea;
                }
                .highlight {
                    color: #3a7bd5;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2 style="color: #3a7bd5;">Project SparkUp</h2>
                </div>
                <div class="content">
                    <h1>Thank You for Contacting Us!</h1>
                    <p>Hello <span class="highlight">${name}</span>,</p>
                    <p>We've received your message and appreciate you reaching out to us. A member of our team will review your inquiry and get back to you as soon as possible.</p>
                    <p>If your matter is urgent, please don't hesitate to call us at +91 9307026265.</p>
                    <p>Thank you for your interest in Project SparkUp!</p>
                    <p>- <span class="highlight">The Project SparkUp Team</span></p>
                </div>
                <div class="footer">
                    <p>&copy; 2025 Project SparkUp. All rights reserved.</p>
                    <p>A project by TheGroup11</p>
                    <p><a href="https://instagram.com/projectsparkup">Follow us on Instagram</a></p>
                </div>
            </div>
        </body>
        </html>
    `;
    
    // URL encode the HTML message
    const encodedMessage = encodeURIComponent(emailHtml);
    const subject = encodeURIComponent("We've Received Your Message - Project SparkUp");
    
    // Call the email API
    fetch(`https://thegroup11.com/api/sendmail?api_key=dGhlZ3JvdXAxMQ==&to=${email}&subject=${subject}&message=${encodedMessage}`);
}