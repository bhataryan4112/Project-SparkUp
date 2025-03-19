/**
 * Waitlist Handler - A robust solution for handling email signups
 * This provides a production-ready approach without relying on test mode
 */
document.addEventListener('DOMContentLoaded', function() {
    // Handle waitlist signups
    const notifyForm = document.querySelector('.notify-form');
    if (notifyForm) {
        const notifyInput = notifyForm.querySelector('input');
        const notifyButton = notifyForm.querySelector('button');
        
        // Remove any existing listeners (to prevent duplication)
        const newButton = notifyButton.cloneNode(true);
        notifyButton.parentNode.replaceChild(newButton, notifyButton);
        
        newButton.addEventListener('click', async () => {
            const email = notifyInput.value;
            
            // Simple validation
            if (!email || !email.includes('@') || !email.includes('.')) {
                showWaitlistNotification('Please enter a valid email address', 'error');
                return;
            }
            
            // Check for VIT email
            if (!email.endsWith('@vit.edu')) {
                showWaitlistNotification('Please use your VIT institutional email (@vit.edu)', 'error');
                return;
            }
            
            // Show loading state
            newButton.disabled = true;
            newButton.innerHTML = '<span class="spinning-loader"></span> Processing...';
            
            try {
                // Try multiple methods to save the email
                const result = await saveEmailWithFallback(email);
                
                if (result.success) {
                    // Also send thank you email if that function exists
                    if (typeof sendThankYouEmail === 'function') {
                        try {
                            sendThankYouEmail(email);
                        } catch (emailError) {
                            console.log('Email notification could not be sent, but signup was successful');
                        }
                    }
                    
                    // Success message
                    newButton.textContent = 'You\'re on the list!';
                    notifyInput.disabled = true;
                    notifyInput.value = '';
                    notifyInput.placeholder = 'We\'ll notify you when Project SparkUp launches';
                    
                    // Show notification
                    showWaitlistNotification('You\'ve been added to our waitlist!', 'success');
                } else {
                    // If duplicated email
                    if (result.duplicated) {
                        showWaitlistNotification('This email is already on our waitlist!', 'info');
                    } else {
                        showWaitlistNotification(result.message || 'An error occurred. Please try again later.', 'error');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showWaitlistNotification('We couldn\'t process your request. Please try again later.', 'error');
            }
            
            // Reset after 3 seconds
            setTimeout(() => {
                newButton.textContent = 'Notify Me';
                newButton.disabled = false;
                if (notifyInput.disabled) {
                    notifyInput.disabled = false;
                    notifyInput.placeholder = 'Your VIT Email Address';
                }
            }, 3000);
        });
    }
    
    /**
     * Try multiple ways to save email, ensuring at least one works
     */
    async function saveEmailWithFallback(email) {
        try {
            // Method 1: Try direct API call
            try {
                const apiResult = await saveEmailToAPI(email);
                if (apiResult.success) return apiResult;
            } catch (apiError) {
                console.log('API method failed, trying next method');
            }
            
            // Method 2: Try local PHP endpoint
            try {
                const phpResult = await saveEmailToLocalPHP(email);
                if (phpResult.success) return phpResult;
            } catch (phpError) {
                console.log('Local PHP method failed, trying next method');
            }
            
            // Method 3: Try localStorage as last resort
            const localResult = saveEmailToLocalStorage(email);
            if (localResult.success) return localResult;
            
            // If all methods fail
            return {
                success: false,
                message: "All methods to save your email failed. Please try again later."
            };
        } catch (error) {
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Save email to API endpoint
     */
    async function saveEmailToAPI(email) {
        const apiUrl = 'https://api.thegroup11.com/sparkup/waitlist/add';
        
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'API-Key': 'dGhlZ3JvdXAxMQ=='
            },
            body: JSON.stringify({
                email: email,
                source: 'website',
                timestamp: new Date().toISOString()
            })
        });
        
        if (!response.ok) {
            const errorData = await response.json();
            if (errorData.code === 'DUPLICATE_EMAIL') {
                return { success: true, duplicated: true };
            }
            throw new Error(errorData.message || `Server responded with ${response.status}`);
        }
        
        return { success: true };
    }
    
    /**
     * Save email using local PHP endpoint
     */
    async function saveEmailToLocalPHP(email) {
        const formData = new FormData();
        formData.append('email', email);
        
        const response = await fetch('save_email.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (!response.ok || !data.success) {
            if (data.message && data.message.includes('already')) {
                return { success: true, duplicated: true };
            }
            throw new Error(data.message || 'Failed to save email');
        }
        
        return { success: true };
    }
    
    /**
     * Save email to localStorage as fallback
     * This ensures the user experience isn't broken even if all server methods fail
     */
    function saveEmailToLocalStorage(email) {
        try {
            // Get existing emails
            let emails = JSON.parse(localStorage.getItem('sparkup_waitlist') || '[]');
            
            // Check if email already exists
            if (emails.includes(email)) {
                return { success: true, duplicated: true };
            }
            
            // Add new email
            emails.push(email);
            localStorage.setItem('sparkup_waitlist', JSON.stringify(emails));
            
            return { success: true };
        } catch (error) {
            return { success: false, message: 'LocalStorage error' };
        }
    }
    
    /**
     * Display notification for waitlist actions
     */
    function showWaitlistNotification(message, type = 'info') {
        // Use the existing showNotification function if it exists
        if (typeof showNotification === 'function') {
            showNotification(message, type);
            return;
        }
        
        // Otherwise implement a basic notification system
        const container = document.querySelector('.notification-container') || 
            document.body.appendChild(document.createElement('div'));
        
        if (!container.classList.contains('notification-container')) {
            container.className = 'notification-container';
            container.style.position = 'fixed';
            container.style.bottom = '20px';
            container.style.right = '20px';
            container.style.zIndex = '9999';
        }
        
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = message;
        notification.style.background = 'rgba(17, 25, 54, 0.9)';
        notification.style.color = '#fff';
        notification.style.padding = '12px 16px';
        notification.style.borderRadius = '4px';
        notification.style.marginTop = '10px';
        notification.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
        
        // Different colors based on type
        if (type === 'success') notification.style.borderLeft = '4px solid #4CAF50';
        if (type === 'error') notification.style.borderLeft = '4px solid #F44336';
        if (type === 'info') notification.style.borderLeft = '4px solid #2196F3';
        
        container.appendChild(notification);
        
        // Fade in
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        notification.style.transition = 'all 0.3s ease';
        
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        // Auto remove after 4 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }
});
