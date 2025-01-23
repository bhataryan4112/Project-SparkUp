const LOADER_CONFIG = {
  minLoadTime: 2000,
  fadeOutDuration: 500
};

export const initLoader = () => {
  const loader = document.getElementById('loader');
  if (!loader) return;

  const startTime = Date.now();

  const hideLoader = () => {
    const elapsedTime = Date.now() - startTime;
    const remainingTime = Math.max(0, LOADER_CONFIG.minLoadTime - elapsedTime);

    setTimeout(() => {
      loader.classList.add('fade-out');
      setTimeout(() => {
        loader.style.display = 'none';
      }, LOADER_CONFIG.fadeOutDuration);
    }, remainingTime);
  };

  if (document.readyState === 'complete') {
    hideLoader();
  } else {
    window.addEventListener('load', hideLoader);
  }
};
