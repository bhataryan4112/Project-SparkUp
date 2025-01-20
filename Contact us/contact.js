// Contact Form Validation
document.getElementById('contact-form').addEventListener('submit', function (e) {
    e.preventDefault();
  
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const message = document.getElementById('message').value.trim();
  
    // Validate Name
    if (!name || name.length < 3) {
      alert('Please enter a valid name (at least 3 characters).');
      return;
    }
  
    // Validate Email
    if (!validateEmail(email)) {
      alert('Please enter a valid email address.');
      return;
    }
  
    // Validate Message
    if (!message || message.length < 10) {
      alert('Message must be at least 10 characters long.');
      return;
    }
  
    // Success Message
    alert('Your message has been sent successfully!');
    this.reset();
  });
  
  // Email Validation Function
  function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }
  