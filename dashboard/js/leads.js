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

        addLeadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            
            // Show loading state
            const submitBtn = addLeadForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            
            // Clear previous error messages
            clearErrors();
            
            // Send AJAX request
            fetch('../dashboard/add-lead.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    // Show success message using Bootstrap toast
                    const successToast = document.getElementById('successToast');
                    if (successToast) {
                        successToast.querySelector('.toast-body').textContent = data.message;
                        const bsToast = new bootstrap.Toast(successToast);
                        bsToast.show();
                    } else {
                        showAlert('success', data.message);
                    }
                    
                    // Reset form
                    addLeadForm.reset();
                    
                    // Set default date again
                    if (leadDate) {
                        leadDate.valueAsDate = new Date();
                    }
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addLeadModal'));
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Refresh leads list if available
                    if (typeof refreshLeads === 'function') {
                        refreshLeads();
                    } else {
                        // If no refresh function, reload the page after a short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                } else {
                    // Show error messages
                    if (data.errors && Array.isArray(data.errors)) {
                        // Show validation errors in the form
                        data.errors.forEach(error => {
                            showError(error);
                        });
                    } else {
                        // Show error message using Bootstrap toast
                        const errorToast = document.getElementById('errorToast');
                        if (errorToast) {
                            errorToast.querySelector('.toast-body').textContent = data.message || 'An error occurred while saving the lead.';
                            const bsToast = new bootstrap.Toast(errorToast);
                            bsToast.show();
                        } else {
                            showAlert('error', data.message || 'An error occurred while saving the lead.');
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Show error message using Bootstrap toast
                const errorToast = document.getElementById('errorToast');
                if (errorToast) {
                    errorToast.querySelector('.toast-body').textContent = 'An error occurred while saving the lead. Please try again.';
                    const bsToast = new bootstrap.Toast(errorToast);
                    bsToast.show();
                } else {
                    showAlert('error', 'An error occurred while saving the lead. Please try again.');
                }
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });

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
