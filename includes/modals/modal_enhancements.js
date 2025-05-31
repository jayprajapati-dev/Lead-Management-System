/**
 * Modal Form Enhancements
 * Adds visual improvements to modal forms
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modals
    initializeModals();
    
    // Add event listeners for modal opening
    const allModals = document.querySelectorAll('.modal');
    allModals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            enhanceFormElements(this);
        });
    });
});

/**
 * Initialize all modals with enhanced features
 */
function initializeModals() {
    // Add visual enhancements to status dropdowns
    enhanceStatusDropdowns();
    
    // Add visual enhancements to source dropdowns
    enhanceSourceDropdowns();
    
    // Add required field indicators
    markRequiredFields();
    
    // Add form validation
    setupFormValidation();
}

/**
 * Enhance status dropdowns with colored badges
 */
function enhanceStatusDropdowns() {
    const statusDropdowns = document.querySelectorAll('select[id$="Status"]');
    
    statusDropdowns.forEach(dropdown => {
        // Create a new select with custom styling
        const options = dropdown.querySelectorAll('option');
        
        options.forEach(option => {
            const badgeClass = option.getAttribute('data-badge-class');
            if (badgeClass) {
                // Store the original text
                const originalText = option.textContent;
                
                // Create badge element
                const badge = document.createElement('span');
                badge.className = `badge ${badgeClass} me-2`;
                badge.textContent = originalText;
                
                // Set option content with badge
                if (option.selected) {
                    const dropdownButton = dropdown.closest('.form-group')?.querySelector('.dropdown-toggle');
                    if (dropdownButton) {
                        dropdownButton.innerHTML = '';
                        dropdownButton.appendChild(badge.cloneNode(true));
                        dropdownButton.appendChild(document.createTextNode(originalText));
                    }
                }
            }
        });
    });
}

/**
 * Enhance source dropdowns with colored dots
 */
function enhanceSourceDropdowns() {
    const sourceDropdowns = document.querySelectorAll('select[id$="Source"]');
    
    sourceDropdowns.forEach(dropdown => {
        // Create a new select with custom styling
        const options = dropdown.querySelectorAll('option');
        
        options.forEach(option => {
            const dotClass = option.getAttribute('data-dot-class');
            if (dotClass) {
                // Store the original text
                const originalText = option.textContent;
                
                // Create dot element
                const dot = document.createElement('span');
                dot.className = `source-dot ${dotClass} me-2`;
                
                // Set option content with dot
                if (option.selected) {
                    const dropdownButton = dropdown.closest('.form-group')?.querySelector('.dropdown-toggle');
                    if (dropdownButton) {
                        dropdownButton.innerHTML = '';
                        dropdownButton.appendChild(dot.cloneNode(true));
                        dropdownButton.appendChild(document.createTextNode(originalText));
                    }
                }
            }
        });
    });
}

/**
 * Mark required fields with visual indicators
 */
function markRequiredFields() {
    const requiredInputs = document.querySelectorAll('input[required], select[required], textarea[required]');
    
    requiredInputs.forEach(input => {
        const formGroup = input.closest('.form-group, .mb-3');
        if (formGroup) {
            const label = formGroup.querySelector('label');
            if (label && !label.querySelector('.required-indicator')) {
                const indicator = document.createElement('span');
                indicator.className = 'required-indicator text-danger ms-1';
                indicator.textContent = '*';
                label.appendChild(indicator);
            }
        }
    });
}

/**
 * Setup form validation
 */
function setupFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Highlight invalid fields
                const invalidInputs = form.querySelectorAll(':invalid');
                invalidInputs.forEach(input => {
                    input.classList.add('is-invalid');
                    
                    // Add feedback message
                    const formGroup = input.closest('.form-group, .mb-3');
                    if (formGroup && !formGroup.querySelector('.invalid-feedback')) {
                        const feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        feedback.textContent = input.validationMessage || 'This field is required';
                        formGroup.appendChild(feedback);
                    }
                });
            }
            
            form.classList.add('was-validated');
        });
        
        // Remove invalid class when input changes
        form.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('input', function() {
                if (this.checkValidity()) {
                    this.classList.remove('is-invalid');
                }
            });
        });
    });
}

/**
 * Enhance form elements in a specific modal
 */
function enhanceFormElements(modal) {
    // Add animation to form elements
    const formElements = modal.querySelectorAll('.form-control, .form-select, .form-check-input');
    formElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(10px)';
        
        setTimeout(() => {
            element.style.transition = 'all 0.3s ease';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, 50 * index);
    });
    
    // Focus first input
    setTimeout(() => {
        const firstInput = modal.querySelector('input:not([readonly]), select, textarea');
        if (firstInput) {
            firstInput.focus();
        }
    }, 300);
}
