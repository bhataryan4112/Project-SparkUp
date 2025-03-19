/**
 * Test mode script for Project SparkUp
 * This handles email submissions when database is not available
 */

document.addEventListener('DOMContentLoaded', function() {
    // Check if we're in test mode (no database)
    const testMode = true; // Toggle this based on environment
    
    if (testMode) {
        // Override the notify form behavior in test mode
        const notifyForm = document.querySelector('.notify-form');
        if (notifyForm) {
            const notifyInput = notifyForm.querySelector('input');
            const notifyButton = notifyForm.querySelector('button');
            
            // Remove existing click listeners
            const newButton = notifyButton.cloneNode(true);
            notifyButton.parentNode.replaceChild(newButton, notifyButton);
            
            // Add new test mode listener
            newButton.addEventListener('click', () => {
                const email = notifyInput.value;
                
                // Simple validation
                if (!email || !email.includes('@') || !email.includes('.')) {
                    showTestNotification('Please enter a valid email address', 'error');
                    return;
                }
                
                // Check for VIT email
                if (!email.endsWith('@vit.edu')) {
                    showTestNotification('Please use your VIT institutional email (@vit.edu)', 'error');
                    return;
                }
                
                // Show loading state
                newButton.disabled = true;
                newButton.innerHTML = '<span class="spinning-loader"></span> Processing...';
                
                // Simulate server delay
                setTimeout(() => {
                    // Skip database saving and just show success
                    console.log('Test mode: Email would be saved:', email);
                    
                    // Success message
                    newButton.textContent = 'You\'re on the list!';
                    notifyInput.disabled = true;
                    notifyInput.value = '';
                    notifyInput.placeholder = 'We\'ll notify you when Project SparkUp launches';
                    
                    // Show notification
                    showTestNotification('You\'ve been added to our waitlist! (Test Mode)', 'success');
                    
                    // Reset after 3 seconds
                    setTimeout(() => {
                        newButton.textContent = 'Notify Me';
                        newButton.disabled = false;
                        notifyInput.disabled = false;
                        notifyInput.placeholder = 'Your VIT Email Address';
                    }, 3000);
                }, 1000);
            });
        }
        
        // Add test mode indicator
        const testBadge = document.createElement('div');
        testBadge.style.position = 'fixed';
        testBadge.style.bottom = '10px';
        testBadge.style.left = '10px';
        testBadge.style.background = 'rgba(255, 87, 34, 0.8)';
        testBadge.style.color = 'white';
        testBadge.style.padding = '5px 10px';
        testBadge.style.borderRadius = '4px';
        testBadge.style.fontSize = '12px';
        testBadge.style.zIndex = '9999';
        testBadge.style.pointerEvents = 'none';
        testBadge.textContent = 'TEST MODE';
        document.body.appendChild(testBadge);
    }
});

// Test notification function 
function showTestNotification(message, type = 'info') {
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
