// Leads management JavaScript

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap toasts
    const toastElList = [].slice.call(document.querySelectorAll('.toast'));
    const toastList = toastElList.map(function(toastEl) {
        return new bootstrap.Toast(toastEl);
    });

    // Get the form element
    const addLeadForm = document.getElementById('addLeadForm');
    
    if (addLeadForm) {
        // Set default date to today
        const leadDate = document.getElementById('leadDate');
        if (leadDate) {
            leadDate.valueAsDate = new Date();
        }
        
        // NOTE: Form submission is now handled in modal_enhancements.js
        // This prevents duplicate submissions

        // Handle mobile number input
        const customerMobile = document.getElementById('customerMobile');
        if (customerMobile) {
            customerMobile.addEventListener('input', function(e) {
                // Remove any non-digit characters
                this.value = this.value.replace(/\D/g, '');
                
                // Limit to 10 digits
                if (this.value.length > 10) {
                    this.value = this.value.slice(0, 10);
                }
            });
        }
    }
});

// Function to show error messages in the form
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger alert-dismissible fade show mt-2';
    errorDiv.role = 'alert';
    errorDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const form = document.getElementById('addLeadForm');
    if (form) {
        form.insertBefore(errorDiv, form.firstChild);
    }
}

// Function to clear all error messages
function clearErrors() {
    const form = document.getElementById('addLeadForm');
    if (form) {
        const errors = form.querySelectorAll('.alert-danger');
        errors.forEach(error => error.remove());
    }
}

// Function to show alert messages (fallback if toast is not available)
function showAlert(type, message) {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alertDiv.style.zIndex = '1050';
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add alert to the page
    document.body.appendChild(alertDiv);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
