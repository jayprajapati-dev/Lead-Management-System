/**
 * Global Theme Toggle System
 * This script provides a unified approach to theme toggling across the entire application
 */

document.addEventListener('DOMContentLoaded', function() {
    // Theme constants
    const THEME_STORAGE_KEY = 'theme';
    const DARK_THEME = 'dark';
    const LIGHT_THEME = 'light';
    
    // Function to set theme
    function setTheme(theme) {
        if (theme === DARK_THEME) {
            document.documentElement.setAttribute('data-theme', DARK_THEME);
            document.body.classList.add('dark-mode');
            
            // Update any theme toggle icons
            const themeToggles = document.querySelectorAll('.theme-toggle');
            themeToggles.forEach(toggle => {
                const icon = toggle.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-sun'; // Sun icon for dark mode (to switch to light)
                }
            });
            
            localStorage.setItem(THEME_STORAGE_KEY, DARK_THEME);
        } else {
            document.documentElement.removeAttribute('data-theme');
            document.body.classList.remove('dark-mode');
            
            // Update any theme toggle icons
            const themeToggles = document.querySelectorAll('.theme-toggle');
            themeToggles.forEach(toggle => {
                const icon = toggle.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-moon'; // Moon icon for light mode (to switch to dark)
                }
            });
            
            localStorage.setItem(THEME_STORAGE_KEY, LIGHT_THEME);
        }
    }
    
    // Function to toggle theme
    function toggleTheme() {
        const currentTheme = localStorage.getItem(THEME_STORAGE_KEY) || LIGHT_THEME;
        const newTheme = currentTheme === DARK_THEME ? LIGHT_THEME : DARK_THEME;
        
        setTheme(newTheme);
        
        // Show theme change feedback
        showThemeFeedback(newTheme);
    }
    
    // Function to show theme change feedback
    function showThemeFeedback(theme) {
        // Remove any existing feedback
        const existingFeedback = document.getElementById('theme-feedback');
        if (existingFeedback) {
            document.body.removeChild(existingFeedback);
        }
        
        // Create new feedback
        const feedback = document.createElement('div');
        feedback.id = 'theme-feedback';
        feedback.style.position = 'fixed';
        feedback.style.bottom = '20px';
        feedback.style.right = '20px';
        feedback.style.padding = '10px 16px';
        feedback.style.backgroundColor = theme === DARK_THEME ? '#4f46e5' : '#6366f1';
        feedback.style.color = '#ffffff';
        feedback.style.borderRadius = '4px';
        feedback.style.boxShadow = '0 2px 5px rgba(0, 0, 0, 0.2)';
        feedback.style.zIndex = '9999';
        feedback.style.opacity = '1';
        feedback.style.transition = 'opacity 0.5s ease';
        feedback.textContent = theme === DARK_THEME ? 'Dark Mode Enabled' : 'Light Mode Enabled';
        
        document.body.appendChild(feedback);
        
        // Fade out and remove after delay
        setTimeout(() => {
            feedback.style.opacity = '0';
            setTimeout(() => {
                if (document.body.contains(feedback)) {
                    document.body.removeChild(feedback);
                }
            }, 500);
        }, 2000);
    }
    
    // Load saved theme or detect system preference
    function loadTheme() {
        const savedTheme = localStorage.getItem(THEME_STORAGE_KEY);
        
        if (savedTheme) {
            // Use saved preference if available
            setTheme(savedTheme);
        } else {
            // Otherwise check system preference
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            setTheme(prefersDark ? DARK_THEME : LIGHT_THEME);
        }
    }
    
    // Add event listeners to all theme toggle buttons
    const themeToggles = document.querySelectorAll('.theme-toggle');
    themeToggles.forEach(toggle => {
        toggle.addEventListener('click', toggleTheme);
    });
    
    // Listen for system theme changes
    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (!localStorage.getItem(THEME_STORAGE_KEY)) {
                setTheme(e.matches ? DARK_THEME : LIGHT_THEME);
            }
        });
    }
    
    // Initialize theme
    loadTheme();
});
