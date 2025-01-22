import { initLoader } from './modules/loader.js';
import { initNavigation } from './modules/navigation.js';
import { initForms } from './modules/forms.js';

class App {
  constructor() {
    this.init();
    this.initNavbar();
    this.initParallax();
    this.initAnimations();
    this.initIntersectionObserver();
    this.initHeroParallax();
    this.initScrollAnimations();
    this.initLocationDemo();
  }

  init() {
    document.addEventListener('DOMContentLoaded', () => {
      this.initializeModules();
    });
  }

  initializeModules() {
    initLoader();
    initNavigation();
    initForms();
  }

  initNavbar() {
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
      if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });
  }

  initParallax() {
    window.addEventListener('scroll', () => {
      const scrolled = window.pageYOffset;
      const heroContent = document.querySelector('.hero-content');
      if (heroContent) {
        heroContent.style.transform = `translateY(${scrolled * 0.4}px)`;
        heroContent.style.opacity = 1 - (scrolled * 0.002);
      }
    });
  }

  initAnimations() {
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
        }
      });
    }, observerOptions);

    document.querySelectorAll('.feature-card, .safety-card').forEach(el => {
      observer.observe(el);
    });
  }

  initIntersectionObserver() {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-fade-up');
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('.feature-card').forEach(card => {
      observer.observe(card);
    });
  }

  initHeroParallax() {
    window.addEventListener('scroll', () => {
      const scroll = window.pageYOffset;
      const heroContent = document.querySelector('.hero-content');
      if (heroContent) {
        heroContent.style.transform = `translateY(${scroll * 0.5}px)`;
        heroContent.style.opacity = 1 - (scroll * 0.003);
      }
    });
  }

  initScrollAnimations() {
    // ...existing animation code...
  }

  initLocationDemo() {
    // Simulated location crossing animation
    const locationDemo = document.querySelector('.location-demo');
    if (locationDemo) {
      // Add demo animation logic here
    }
  }
}

new App();
