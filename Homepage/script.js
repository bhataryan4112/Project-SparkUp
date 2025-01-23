document.addEventListener("DOMContentLoaded", () => {
  // DOM Elements
  const loader = document.getElementById("loader");
  const login = document.getElementById("login");
  const hamburger = document.querySelector(".hamburger");
  const navMenu = document.querySelector(".nav-menu");
  
  // Configuration
  const LOADER_TIMEOUT = 2000;
  
  // Initialize loader
  const initializeLoader = () => {
    setTimeout(() => {
      if (loader && login) {
        loader.style.display = "none";
        login.style.display = "block";
      }
    }, LOADER_TIMEOUT);
  };
  
  // Initialize
  initializeLoader();

  // Hamburger menu functionality
  const navLinks = document.querySelector('.nav-links');
  
  if (hamburger && navLinks) {
    // Toggle menu
    hamburger.addEventListener('click', () => {
      console.log('Hamburger clicked'); // Debug line
      hamburger.classList.toggle('active');
      navLinks.classList.toggle('active');
      document.body.style.overflow = navLinks.classList.contains('active') ? 'hidden' : 'auto';
    });

    // Close menu when clicking a link
    navLinks.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navLinks.classList.remove('active');
        document.body.style.overflow = 'auto';
      });
    });
  }

  // Add touch event handling
  document.addEventListener('touchstart', handleTouchStart, false);
  document.addEventListener('touchmove', handleTouchMove, false);

  let xDown = null;
  let yDown = null;

  function handleTouchStart(evt) {
    xDown = evt.touches[0].clientX;
    yDown = evt.touches[0].clientY;
  }

  function handleTouchMove(evt) {
    if (!xDown || !yDown) {
      return;
    }

    const xUp = evt.touches[0].clientX;
    const yUp = evt.touches[0].clientY;
    const xDiff = xDown - xUp;
    const yDiff = yDown - yUp;

    if (Math.abs(xDiff) > Math.abs(yDiff)) {
      if (xDiff > 0) {
        // Swipe left
        navMenu.classList.remove('active');
        hamburger.classList.remove('active');
      } else {
        // Swipe right
        navMenu.classList.add('active');
        hamburger.classList.add('active');
      }
    }

    xDown = null;
    yDown = null;
  }

  // Performance optimization for mobile
  const passiveSupported = () => {
    let passive = false;
    try {
      window.addEventListener("test", null, 
        Object.defineProperty({}, "passive", { get: () => passive = true })
      );
    } catch(err) {}
    return passive;
  }

  // Use passive event listeners where supported
  const eventOptions = passiveSupported() ? { passive: true } : false;
  window.addEventListener('scroll', () => {
    // Handle scroll events
  }, eventOptions);
});
