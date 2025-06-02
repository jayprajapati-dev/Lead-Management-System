// Country codes data with flags
const countryCodes = [
    { code: '+91', country: 'India', flag: 'in' },
    { code: '+1', country: 'United States', flag: 'us' },
    { code: '+44', country: 'United Kingdom', flag: 'gb' },
    { code: '+971', country: 'UAE', flag: 'ae' },
    { code: '+966', country: 'Saudi Arabia', flag: 'sa' },
    // Add more country codes as needed
];

document.addEventListener('DOMContentLoaded', function() {
    // Initialize country code dropdown
    initializeCountryCodeDropdown();
    
    // Initialize form submission
    initializeFormSubmission();
    
    // Initialize labels dropdown
    fetchLabels();
});

function initializeCountryCodeDropdown() {
    const dropdown = document.querySelector('.country-code-dropdown');
    const dropdownButton = document.querySelector('.country-flag-dropdown');
    
    // Populate country codes
    countryCodes.forEach(country => {
        const li = document.createElement('li');
        li.innerHTML = `
            <a class="dropdown-item" href="#" data-code="${country.code}">
                <span class="flag-icon flag-icon-${country.flag}"></span>
                ${country.code} ${country.country}
            </a>
        `;
        dropdown.appendChild(li);
    });
    
    // Handle country selection
    dropdown.addEventListener('click', function(e) {
        if (e.target.closest('.dropdown-item')) {
            e.preventDefault();
            const item = e.target.closest('.dropdown-item');
            const code = item.dataset.code;
            const flag = item.querySelector('.flag-icon').cloneNode(true);
            
            dropdownButton.innerHTML = '';
            dropdownButton.appendChild(flag);
            dropdownButton.appendChild(document.createTextNode(' ' + code));
        }
    });
}

function fetchLabels() {
    // Fetch labels from the server
    fetch('ajax/get-labels.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                populateLabelsDropdown(data.labels);
            }
        })
        .catch(error => console.error('Error fetching labels:', error));
}

function populateLabelsDropdown(labels) {
    const labelSelect = document.getElementById('label');
    
    if (labels && labels.length > 0) {
        labels.forEach(label => {
            const option = document.createElement('option');
            option.value = label.id;
            option.textContent = label.name;
            labelSelect.appendChild(option);
        });
    } else {
        const option = document.createElement('option');
        option.value = "";
        option.textContent = "No options available";
        labelSelect.appendChild(option);
    }
}

function initializeFormSubmission() {
    const form = document.getElementById('addLeadForm');
    const submitBtn = document.getElementById('submitLeadBtn');
    
    submitBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Get form data
        const formData = new FormData(form);
        
        // Add country code to phone number
        const countryCode = document.querySelector('.country-flag-dropdown').textContent.trim().split(' ')[0];
        formData.set('customer_mobile', countryCode + formData.get('customer_mobile'));
        
        // Submit form
        submitLead(formData);
    });
}

function submitLead(formData) {
    // Show loading state
    const submitBtn = document.getElementById('submitLeadBtn');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
    
    fetch('add-lead.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Reset form and close modal
                document.getElementById('addLeadForm').reset();
                bootstrap.Modal.getInstance(document.getElementById('addLeadModal')).hide();
                
                // Refresh leads list if available
                if (typeof refreshLeadsList === 'function') {
                    refreshLeadsList();
                }
            });
        } else {
            // Show error message
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to add lead. Please try again.',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An unexpected error occurred. Please try again.',
            confirmButtonText: 'OK'
        });
    })
    .finally(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
} 