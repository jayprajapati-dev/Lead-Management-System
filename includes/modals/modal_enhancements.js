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
    
    // Initialize form validation and submission
    setupFormValidation();
    setupFormSubmission();
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
 * Get the appropriate badge class for a status
 * @param {string} status - The status value
 * @returns {string} - The badge class
 */
function getStatusBadgeClass(status) {
    const classes = {
        'new': 'bg-primary',
        'processing': 'bg-purple',
        'close-by': 'bg-warning',
        'confirm': 'bg-success',
        'cancel': 'bg-danger'
    };
    return classes[status.toLowerCase()] || 'bg-secondary';
}

/**
 * Setup form validation
 */
function setupFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
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

/**
 * Setup form submission handling for the add lead form
 */
function setupFormSubmission() {
    const addLeadForm = document.getElementById('addLeadForm');
    const saveLeadBtn = document.getElementById('saveLeadBtn');
    let isSubmitting = false;
    
    if (!addLeadForm || !saveLeadBtn) {
        console.error('Form elements not found');
                return;
            }
            
    saveLeadBtn.addEventListener('click', async function(e) {
            e.preventDefault();
        
        // Prevent multiple submissions
        if (isSubmitting) return;
            
            // Validate form
            if (!addLeadForm.checkValidity()) {
                addLeadForm.classList.add('was-validated');
                return;
            }
            
        // Show loading state
        isSubmitting = true;
        const spinner = saveLeadBtn.querySelector('.spinner-border');
        if (spinner) spinner.classList.remove('d-none');
        saveLeadBtn.disabled = true;
        
        try {
            // Submit form
            const formData = new FormData(addLeadForm);
            const response = await fetch('../dashboard/add-lead.php', {
                method: 'POST',
                body: formData
            });
            
                if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            console.log('Response:', data);
                
                if (data.status === 'success') {
                // Get the selected status
                const selectedStatus = formData.get('status').toLowerCase();
                
                // Show success message
                showSuccessToast(selectedStatus);
                        
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addLeadModal'));
                if (modal) {
                    modal.hide();
                }
                
                // Reset form
                addLeadForm.reset();
                addLeadForm.classList.remove('was-validated');
                        
                // Update counts and reload page after delay
                        setTimeout(() => {
                    window.location.reload();
                }, 2000);
                                } else {
                showErrorToast(data.message || 'Failed to add lead');
            }
        } catch (error) {
            console.error('Error:', error);
            showErrorToast('Network error. Please try again.');
        } finally {
            // Reset submit button
            isSubmitting = false;
            const spinner = saveLeadBtn.querySelector('.spinner-border');
            if (spinner) spinner.classList.add('d-none');
            saveLeadBtn.disabled = false;
        }
    });
}

// Show success toast
function showSuccessToast(status) {
    const toast = document.getElementById('leadSuccessToast');
    if (!toast) {
        console.error('Success toast element not found');
        return;
    }
    
    const statusBadge = toast.querySelector('#successLeadStatus');
    if (statusBadge) {
        statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        statusBadge.className = 'badge bg-light text-dark';
    }
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
                    }

// Show error toast
function showErrorToast(message) {
    const toast = document.getElementById('leadErrorToast');
    if (!toast) {
        console.error('Error toast element not found');
        return;
                    }
    
    const messageEl = toast.querySelector('#leadErrorToastMessage');
    if (messageEl) {
        messageEl.textContent = message;
    }
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}
