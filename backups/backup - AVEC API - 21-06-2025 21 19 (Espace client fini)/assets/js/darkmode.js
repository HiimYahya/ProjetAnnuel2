(() => {
  'use strict';

  const getStoredTheme = () => localStorage.getItem('theme');
  const setStoredTheme = theme => localStorage.setItem('theme', theme);

  const getPreferredTheme = () => {
    const storedTheme = getStoredTheme();
    if (storedTheme) {
      return storedTheme;
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  };

  const setTheme = theme => {
    document.documentElement.setAttribute('data-bs-theme', theme);
  };

  setTheme(getPreferredTheme());

  window.addEventListener('DOMContentLoaded', () => {
    const themeSwitcher = document.getElementById('theme-toggle');

    if (themeSwitcher) {
      themeSwitcher.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        setStoredTheme(newTheme);
        setTheme(newTheme);
        themeSwitcher.innerText = newTheme === 'dark' ? 'Mode Clair' : 'Mode Sombre';
      });
    }
  });
})();
