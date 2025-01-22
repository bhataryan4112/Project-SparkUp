document.addEventListener("DOMContentLoaded", () => {
  // DOM Elements
  const loader = document.getElementById("loader");
  const login = document.getElementById("login");
  
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
});
