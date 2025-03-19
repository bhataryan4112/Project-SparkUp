/**
 * Enhanced animations for Project SparkUp
 * Adds visual feedback and interactive elements to improve UX
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize animations for elements
    initializeAnimations();
    
    // Set up scroll animations
    setupScrollAnimations();
    
    // Add hover effects for interactive elements
    setupHoverEffects();
    
    // Setup navbar animations
    setupNavbarAnimations();
    
    // Apply professional text scramble effect to headings
    applyProfessionalTextScramble();
});

function initializeAnimations() {
    // Add animation classes to elements that should animate on page load
    document.querySelectorAll('.section-header h2').forEach((element, index) => {
        element.classList.add('animate-in', `delay-${index * 200}`);
    });
    
    document.querySelectorAll('.section-header p').forEach((element, index) => {
        element.classList.add('animate-in', `delay-${200 + index * 200}`);
    });
    
    // Hero content animations
    const heroElements = document.querySelectorAll('.hero-content > *');
    heroElements.forEach((element, index) => {
        element.classList.add('animate-in', `delay-${index * 300}`);
    });
    
    // Staggered animation for process steps
    document.querySelectorAll('.process-step').forEach((element, index) => {
        element.classList.add('animate-in', `delay-${200 + index * 200}`);
    });
    
    // Staggered animation for feature cards
    document.querySelectorAll('.feature-card').forEach((element, index) => {
        element.classList.add('animate-in', `delay-${100 + index * 100}`);
    });
}

function setupScrollAnimations() {
    // Parallax effect for background elements
    const parallaxElements = document.querySelectorAll('.about, .features, .process');
    
    if (window.innerWidth > 768) {
        window.addEventListener('scroll', function() {
            parallaxElements.forEach(element => {
                const scrollPosition = window.pageYOffset;
                const elementPosition = element.offsetTop;
                const distance = scrollPosition - elementPosition;
                
                if (Math.abs(distance) < window.innerHeight * 1.5) {
                    // Apply subtle parallax effect
                    element.style.backgroundPosition = `center ${distance * 0.05}px`;
                }
            });
        }, { passive: true });
    }
    
    // Animate elements when they come into view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
                
                // Add specific animations for different elements
                if (entry.target.classList.contains('feature-card')) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
                
                if (entry.target.classList.contains('process-step')) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
                
                if (entry.target.classList.contains('img-placeholder')) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'scale(1)';
                }
            }
        });
    }, { threshold: 0.2 });
    
    // Observe elements that should animate on scroll
    document.querySelectorAll('.feature-card, .process-step, .img-placeholder, .section-header').forEach(item => {
        item.style.opacity = '0';
        item.style.transform = 'translateY(30px)';
        item.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
        observer.observe(item);
    });
}

function setupHoverEffects() {
    // Tilt effect on cards for larger screens
    if (window.innerWidth > 768) {
        const cards = document.querySelectorAll('.feature-card, .process-step, .team-member');
        
        cards.forEach(card => {
            card.addEventListener('mousemove', e => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                // Reduce tilt amount for subtlety
                const tiltX = (y - centerY) / centerY * 3;
                const tiltY = (centerX - x) / centerX * 3;
                
                // Apply transform with subtle ease
                card.style.transform = `perspective(1000px) rotateX(${tiltX}deg) rotateY(${tiltY}deg)`;
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
                card.style.transition = 'transform 0.5s ease';
                
                setTimeout(() => {
                    card.style.transition = '';
                }, 500);
            });
        });
    }

    // Glowing effect for buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', () => {
            button.style.boxShadow = '0 0 20px rgba(58, 123, 213, 0.7)';
        });
        
        button.addEventListener('mouseleave', () => {
            button.style.boxShadow = '';
        });
    });
    
    // Fix button border radius consistency
    document.querySelectorAll('.btn').forEach(btn => {
        btn.style.borderRadius = '50px';
    });
}

function setupNavbarAnimations() {
    // Nav links hover effect
    const navLinks = document.querySelectorAll('.nav-links a');
    
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', () => {
            navLinks.forEach(otherLink => {
                if (otherLink !== link) {
                    otherLink.style.opacity = '0.6';
                }
            });
        });
        
        link.addEventListener('mouseleave', () => {
            navLinks.forEach(otherLink => {
                otherLink.style.opacity = '1';
            });
        });
    });
    
    // Animated hamburger icon
    const hamburger = document.querySelector('.hamburger');
    if (hamburger) {
        hamburger.addEventListener('mouseenter', () => {
            hamburger.querySelector('span:nth-child(2)').style.width = '70%';
        });
        
        hamburger.addEventListener('mouseleave', () => {
            hamburger.querySelector('span:nth-child(2)').style.width = '100%';
        });
    }
}

// Professional Text Scramble effect
function applyProfessionalTextScramble() {
    class TextScramble {
        constructor(el) {
            this.el = el;
            this.chars = '!<>-_\\/.,:;=+*^?#[]{}—|~`';
            this.update = this.update.bind(this);
        }
        
        setText(newText) {
            const oldText = this.el.innerText;
            const length = Math.max(oldText.length, newText.length);
            const promise = new Promise((resolve) => this.resolve = resolve);
            this.queue = [];
            
            for (let i = 0; i < length; i++) {
                const from = oldText[i] || '';
                const to = newText[i] || '';
                const start = Math.floor(Math.random() * 20);
                const end = start + Math.floor(Math.random() * 20);
                this.queue.push({ from, to, start, end });
            }
            
            cancelAnimationFrame(this.frameRequest);
            this.frame = 0;
            this.update();
            return promise;
        }
        
        update() {
            let output = '';
            let complete = 0;
            
            for (let i = 0, n = this.queue.length; i < n; i++) {
                let { from, to, start, end, char } = this.queue[i];
                
                if (this.frame >= end) {
                    complete++;
                    output += to;
                } else if (this.frame >= start) {
                    if (!char || Math.random() < 0.28) {
                        char = this.randomChar();
                        this.queue[i].char = char;
                    }
                    output += `<span class="scramble-char" style="opacity:0.7">${char}</span>`;
                } else {
                    output += from;
                }
            }
            
            this.el.innerHTML = output;
            
            if (complete === this.queue.length) {
                this.resolve();
            } else {
                this.frameRequest = requestAnimationFrame(this.update);
                this.frame++;
            }
        }
        
        randomChar() {
            return this.chars[Math.floor(Math.random() * this.chars.length)];
        }
    }
    
    // Apply to section headings
    document.querySelectorAll('.section-header h2').forEach((element) => {
        // Save the original text
        const originalText = element.innerText;
        
        // Don't scramble if already processed
        if (element.getAttribute('data-scrambled')) return;
        
        // Mark as processed
        element.setAttribute('data-scrambled', 'true');
        
        // Create scramble effect on scroll into view
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                const fx = new TextScramble(element);
                fx.setText(originalText);
                observer.disconnect();
            }
        }, { threshold: 0.5 });
        
        observer.observe(element);
    });
    
    // Apply to CTA headings
    document.querySelectorAll('.cta h2').forEach((element) => {
        const originalText = element.innerText;
        if (element.getAttribute('data-scrambled')) return;
        element.setAttribute('data-scrambled', 'true');
        
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                const fx = new TextScramble(element);
                fx.setText(originalText);
                observer.disconnect();
            }
        }, { threshold: 0.5 });
        
        observer.observe(element);
    });
}

// Add subtle pulse animation to buttons
document.addEventListener('DOMContentLoaded', function() {
    const ctaButtons = document.querySelectorAll('.cta .btn');
    ctaButtons.forEach(button => {
        button.classList.add('pulse-animation');
    });
});
