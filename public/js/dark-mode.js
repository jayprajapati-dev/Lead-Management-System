// dark-mode.js

document.addEventListener('DOMContentLoaded', () => {
    const toggleButton = document.getElementById('dark-mode-toggle');
    const body = document.body;
    const darkModeStorageKey = 'darkModeEnabled';

    // Check local storage for dark mode preference
    const darkModeEnabled = localStorage.getItem(darkModeStorageKey);

    if (darkModeEnabled === 'true') {
        body.classList.add('dark-mode');
    }

    // Add event listener to the toggle button
    if (toggleButton) {
        toggleButton.addEventListener('click', () => {
            body.classList.toggle('dark-mode');

            // Save the preference to local storage
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem(darkModeStorageKey, 'true');
            } else {
                localStorage.setItem(darkModeStorageKey, 'false');
            }
        });
    }
}); 