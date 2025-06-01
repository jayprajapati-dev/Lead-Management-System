/**
 * Lead Display JS
 * Handles dynamic lead display and form submission
 * Ensures new leads appear under the correct status section
 */

document.addEventListener('DOMContentLoaded', function() {
    // Cache DOM elements
    const statusBoxes = document.querySelectorAll('.status-box');
    const addLeadForm = document.getElementById('addLeadForm');
    const statusSelect = document.getElementById('leadStatus');
    
    // Determine if we're on the dashboard or leads page
    const isDashboardPage = window.location.pathname.includes('dashboard.php');
    const isLeadsPage = window.location.pathname.includes('leads.php');
    
    console.log('Page detected:', isDashboardPage ? 'Dashboard' : (isLeadsPage ? 'Leads' : 'Other'));
    
    // Initialize lead containers
    initializeLeadContainers();
    
    // Initialize lead counts
    initializeLeadCounts();
    
    // Check if we just added a new lead and reloaded the page
    if (isLeadsPage && sessionStorage.getItem('newLeadAdded') === 'true') {
        const newLeadId = sessionStorage.getItem('newLeadId');
        const newLeadStatus = sessionStorage.getItem('newLeadStatus');
        console.log('New lead was added before reload, ID:', newLeadId, 'Status:', newLeadStatus);
        
        // Clear the sessionStorage flags
        sessionStorage.removeItem('newLeadAdded');
        sessionStorage.removeItem('newLeadId');
        sessionStorage.removeItem('newLeadStatus');
        
        // Show a success toast
        const successToast = document.getElementById('successToast');
        if (successToast) {
            const toastBody = successToast.querySelector('.toast-body');
            if (toastBody) {
                toastBody.textContent = 'Lead added successfully! The new lead is now visible in the ' + (newLeadStatus || 'appropriate') + ' section.';
            }
            
            const toast = new bootstrap.Toast(successToast, {
                animation: true,
                autohide: true,
                delay: 5000
            });
            toast.show();
            
            // Highlight the status section where the new lead was added
            if (newLeadStatus) {
                const statusBox = document.querySelector(`.status-box[data-status="${newLeadStatus.toLowerCase()}"]`);
                if (statusBox) {
                    statusBox.classList.add('highlight-status');
                    setTimeout(() => {
                        statusBox.classList.remove('highlight-status');
                    }, 3000);
                }
            }
        }
    }
    
    // Create a global function to handle lead addition success
    // This will be called from the leads.js file
    window.handleAddLeadSuccess = function(response) {
        console.log('Lead added successfully, updating UI:', response);
        
        // Get form data to create the new lead card
        if (!addLeadForm) {
            console.error('Add lead form not found');
            return;
        }
        
        const formData = new FormData(addLeadForm);
        
        // If we're on the leads page, we need to reload the page to see the new lead
        // This is because the leads page has a different structure than the dashboard
        if (isLeadsPage) {
            console.log('On leads page, will reload after adding lead');
            // Set a flag in sessionStorage to indicate we should show a success message after reload
            sessionStorage.setItem('newLeadAdded', 'true');
            sessionStorage.setItem('newLeadId', response.lead_id);
            sessionStorage.setItem('newLeadStatus', formData.get('status'));
            
            // Wait 2 seconds before reloading to allow the success toast to be seen
            setTimeout(() => {
                window.location.reload();
            }, 2000);
            return;
        }
        
        // Map the form field names to database field names
        const leadData = {
            id: response.lead_id || Date.now(), // Use response ID or generate temporary one
            name: formData.get('customer_name'),
            email: formData.get('email'),
            phone: formData.get('customer_mobile'),
            company: formData.get('company_name'),
            address: formData.get('address'),
            notes: formData.get('comment'),
            reference: formData.get('reference'),
            created_at: new Date().toISOString()
        };
        
        // Get user information from the form
        try {
            // Try to get user information from the form data first
            if (formData.has('user_id')) {
                leadData.assigned_to = formData.get('user_id');
                
                // Try to get the user name from the select element
                const userField = document.querySelector('select[name="user_id"]');
                if (userField && userField.selectedIndex >= 0) {
                    const selectedOption = userField.options[userField.selectedIndex];
                    leadData.assigned_to_name = selectedOption.textContent.trim();
                } else {
                    // If we can't get it from the select, use a default name
                    leadData.assigned_to_name = 'Varun Dhavan';
                }
                
                console.log('Assigned to user:', leadData.assigned_to_name);
            } else {
                // Default values if user_id is not in the form
                leadData.assigned_to = '1';
                leadData.assigned_to_name = 'Varun Dhavan';
                console.log('No user_id in form, defaulting to Varun Dhavan');
            }
            
            // For created by, use Admin as default
            leadData.created_by = 'Admin';
            leadData.created_by_name = 'Admin';
        } catch (error) {
            console.error('Error getting user information:', error);
            // Fallback to defaults
            leadData.assigned_to = '1';
        }
        
        // Get the status value (need to convert to lowercase for data-status attribute)
        let status = formData.get('status');
        if (status) {
            // Store the original status for display
            leadData.statusDisplay = status;
            
            // Convert status to lowercase for data attribute matching
            status = status.toLowerCase();
            if (status === 'close-by') {
                leadData.status = 'close-by';
            } else if (status === 'new') {
                leadData.status = 'new';
            } else if (status === 'processing') {
                leadData.status = 'processing';
            } else if (status === 'confirm') {
                leadData.status = 'confirm';
            } else if (status === 'cancel') {
                leadData.status = 'cancel';
            } else {
                // Default to 'new' if status is not recognized
                leadData.status = 'new';
                leadData.statusDisplay = 'New';
            }
        } else {
            // Default to 'new' if no status is provided
            leadData.status = 'new';
            leadData.statusDisplay = 'New';
        }
        
        // Get the source value
        let source = formData.get('source');
        if (source) {
            // Store original source for display
            leadData.sourceDisplay = source;
            leadData.source = source.toLowerCase();
        } else {
            // Default to 'Online' if no source is provided
            leadData.source = 'online';
            leadData.sourceDisplay = 'Online';
        }
        
        console.log('Adding lead with status:', leadData.status);
        
        // Add the new lead to the UI
        addLeadToUI(leadData);
        
        // Update the count badge for the status
        updateStatusCount(leadData.status);
    };
    
    // We don't need to override the form submission in leads.js anymore
    // The leads.js file already has the code to call our handleAddLeadSuccess function
    // This was causing the form to be submitted twice or the fetch to be overridden incorrectly
    
    // Instead, we'll make sure our handleAddLeadSuccess function is properly defined
    // and accessible globally, which it already is above
    
    // For debugging purposes, let's add a console log when the function is called
    const originalHandleAddLeadSuccess = window.handleAddLeadSuccess;
    window.handleAddLeadSuccess = function(response) {
        console.log('Lead added successfully, updating UI:', response);
        // Call the original implementation
        originalHandleAddLeadSuccess(response);
    };
    
    // Setup mobile-friendly view with only records scrolling horizontally
    const setupMobileView = () => {
        // Make sure all status content is visible
        document.querySelectorAll('.status-content').forEach(content => {
            content.classList.remove('d-none');
        });
        
        // Get the main container and status boxes
        const leadsMainContainer = document.querySelector('.leads-main-container');
        const statusBoxesContainer = document.querySelector('.status-boxes');
        
        if (!leadsMainContainer) return;
        
        // For mobile view
        if (window.innerWidth < 768) {
            // Style the status boxes container for mobile - NO SCROLLING
            if (statusBoxesContainer) {
                // Reset horizontal scrolling for status boxes
                statusBoxesContainer.style.overflowX = '';
                statusBoxesContainer.style.whiteSpace = '';
                
                // Display status boxes normally but compact
                statusBoxesContainer.style.display = 'flex';
                statusBoxesContainer.style.flexWrap = 'wrap';
                statusBoxesContainer.style.justifyContent = 'center';
                statusBoxesContainer.style.padding = '10px 0';
                
                // Style individual status boxes for mobile
                const statusBoxes = statusBoxesContainer.querySelectorAll('.status-box');
                statusBoxes.forEach(box => {
                    box.style.display = 'inline-block';
                    box.style.width = 'auto';
                    box.style.margin = '5px';
                    box.style.flex = '0 0 auto';
                    
                    // Keep text visible but make it smaller
                    const statusText = box.querySelector('.status-text');
                    if (statusText) {
                        statusText.style.display = 'inline';
                        statusText.style.fontSize = '0.8rem';
                    }
                });
            }
            
            // Style the leads container for horizontal scrolling
            leadsMainContainer.style.overflowX = 'auto';
            leadsMainContainer.style.whiteSpace = 'nowrap';
            leadsMainContainer.style.paddingBottom = '15px';
            
            // Make the row flex and nowrap
            const row = leadsMainContainer.querySelector('.row');
            if (row) {
                row.style.display = 'flex';
                row.style.flexWrap = 'nowrap';
                row.style.width = 'max-content';
                row.style.minWidth = '100%';
            }
            
            // Set fixed width for status columns
            document.querySelectorAll('.status-content').forEach(content => {
                content.style.width = '280px';
                content.style.display = 'inline-block';
                content.style.verticalAlign = 'top';
                content.style.padding = '0 5px';
                
                // Add status title to each column for mobile
                const status = content.getAttribute('data-status');
                if (status) {
                    let title = status.charAt(0).toUpperCase() + status.slice(1);
                    if (status === 'close-by') title = 'Close-by';
                    
                    // Add title if it doesn't exist
                    if (!content.querySelector('.mobile-status-title')) {
                        const titleDiv = document.createElement('div');
                        titleDiv.className = 'mobile-status-title text-center py-2 fw-bold';
                        titleDiv.textContent = title;
                        
                        // Add title at the top of the content
                        if (content.firstChild) {
                            content.insertBefore(titleDiv, content.firstChild);
                        } else {
                            content.appendChild(titleDiv);
                        }
                    }
                }
            });
        } else {
            // Reset styles for desktop
            if (statusBoxesContainer) {
                statusBoxesContainer.style.overflowX = '';
                statusBoxesContainer.style.whiteSpace = '';
                statusBoxesContainer.style.display = '';
                statusBoxesContainer.style.justifyContent = '';
                statusBoxesContainer.style.padding = '';
                statusBoxesContainer.style.flexWrap = '';
                
                // Reset individual status boxes
                const statusBoxes = statusBoxesContainer.querySelectorAll('.status-box');
                statusBoxes.forEach(box => {
                    box.style.display = '';
                    box.style.width = '';
                    box.style.margin = '';
                    box.style.flex = '';
                    
                    // Reset status text
                    const statusText = box.querySelector('.status-text');
                    if (statusText) {
                        statusText.style.display = '';
                        statusText.style.fontSize = '';
                    }
                });
            }
            
            // Reset leads container
            leadsMainContainer.style.overflowX = '';
            leadsMainContainer.style.whiteSpace = '';
            leadsMainContainer.style.paddingBottom = '';
            
            const row = leadsMainContainer.querySelector('.row');
            if (row) {
                row.style.display = '';
                row.style.flexWrap = '';
                row.style.width = '';
                row.style.minWidth = '';
            }
            
            // Reset status columns
            document.querySelectorAll('.status-content').forEach(content => {
                content.style.width = '';
                content.style.display = '';
                content.style.verticalAlign = '';
                content.style.padding = '';
                
                // Remove mobile status titles
                const mobileTitle = content.querySelector('.mobile-status-title');
                if (mobileTitle) {
                    mobileTitle.remove();
                }
            });
        }
    };
    
    // Call setup function on load
    setupMobileView();
    
    // Update on window resize
    window.addEventListener('resize', setupMobileView);
    
    /**
     * Initialize lead containers for each status
     */
    function initializeLeadContainers() {
        // Add desktop status sections if they don't exist
        const statuses = ['new', 'processing', 'close-by', 'confirm', 'cancel'];
        const mainContentCard = document.querySelector('.card.p-4');
        
        if (!mainContentCard) {
            console.error('Main content card not found');
            return;
        }
        
        // Create a container for all lead content
        const leadsContainer = document.createElement('div');
        leadsContainer.className = 'leads-main-container mt-4';
        
        // Create a row for all status columns
        const row = document.createElement('div');
        row.className = 'row';
        leadsContainer.appendChild(row);
        
        // Create columns for each status
        statuses.forEach(status => {
            // Create column for this status
            const statusCol = document.createElement('div');
            statusCol.className = 'col status-content';
            statusCol.setAttribute('data-status', status);
            
            // Set title based on status
            let title = status.charAt(0).toUpperCase() + status.slice(1);
            if (status === 'close-by') title = 'Close-by';
            
            // Create status section HTML with simplified no records message
            statusCol.innerHTML = `
                <div class="text-center mb-3 no-records-message">
                    <i class="fas fa-info-circle"></i> No records
                </div>
                <div class="leads-container">
                    <!-- Leads will be loaded here -->
                </div>
            `;
            
            // Add column to row
            row.appendChild(statusCol);
        });
        
        // Insert after the status boxes
        const statusBoxesContainer = document.querySelector('.status-boxes');
        if (statusBoxesContainer) {
            const nextElement = statusBoxesContainer.nextElementSibling;
            if (nextElement) {
                mainContentCard.insertBefore(leadsContainer, nextElement);
            } else {
                mainContentCard.appendChild(leadsContainer);
            }
        } else {
            mainContentCard.appendChild(leadsContainer);
        }
        
        // Style no records messages after container initialization
        setTimeout(() => {
            document.querySelectorAll('.no-records-message').forEach(message => {
                // Apply styling
                message.style.color = '#6c757d';
                message.style.fontSize = '0.9rem';
                message.style.fontWeight = 'normal';
                message.style.padding = '10px';
                message.style.border = 'none';
                message.style.backgroundColor = 'transparent';
                
                // Add icon styling
                const icon = message.querySelector('i');
                if (icon) {
                    icon.style.marginRight = '5px';
                    icon.style.color = '#6c757d';
                }
            });
        }, 100);
    }
    
    // No longer need the createStatusSection function since we're using a different layout
    
    /**
     * Add a new lead to the UI under the correct status section
     * @param {Object} leadData - The lead data object
     */
    function addLeadToUI(leadData) {
        // Find the container for this status
        const statusContent = document.querySelector(`.status-content[data-status="${leadData.status}"]`);
        if (!statusContent) {
            console.error(`Status content not found for status: ${leadData.status}`);
            return;
        }
        
        const leadsContainer = statusContent.querySelector('.leads-container');
        if (!leadsContainer) {
            console.error(`Leads container not found for status: ${leadData.status}`);
            return;
        }
        
        // Hide the "no records" message
        const noRecordsMessage = statusContent.querySelector('.no-records-message');
        if (noRecordsMessage) {
            noRecordsMessage.classList.add('d-none');
        }
        
        // Create the lead card
        const leadCard = createLeadCard(leadData);
        
        // Add the lead card to the container (prepend to show newest first)
        if (leadsContainer.firstChild) {
            leadsContainer.insertBefore(leadCard, leadsContainer.firstChild);
        } else {
            leadsContainer.appendChild(leadCard);
        }
        
        // Show the container if it was hidden
        leadsContainer.classList.remove('d-none');
        
        // Update the status count badge
        updateStatusCountBadge(leadData.status);
    }
    
    /**
     * Initialize the lead counts for all status boxes
     */
    function initializeLeadCounts() {
        console.log('Initializing lead counts');
        
        // Get all status boxes
        const statusBoxes = document.querySelectorAll('.status-box');
        if (!statusBoxes || statusBoxes.length === 0) {
            console.error('No status boxes found');
            return;
        }
        
        // For each status box, count the leads with that status
        statusBoxes.forEach(statusBox => {
            const status = statusBox.getAttribute('data-status');
            if (!status) {
                console.error('Status box has no data-status attribute');
                return;
            }
            
            // Find the count badge
            const countBadge = statusBox.querySelector('.count-badge');
            if (!countBadge) {
                console.error(`Count badge not found for status: ${status}`);
                return;
            }
            
            // Count the leads with this status
            let leadCount = 0;
            
            // Check if we're on the dashboard or leads page
            if (isDashboardPage) {
                // On dashboard, count the leads in the status content section
                const statusContent = document.querySelector(`.status-content[data-status="${status}"]`);
                if (statusContent) {
                    const leadsContainer = statusContent.querySelector('.leads-container');
                    if (leadsContainer) {
                        leadCount = leadsContainer.querySelectorAll('.lead-item').length;
                    }
                }
            } else if (isLeadsPage) {
                // On leads page, count the leads in both grid and list views
                // Grid view
                const gridLeads = document.querySelectorAll(`.lead-item[data-status="${status}"]`);
                leadCount = gridLeads.length;
                
                // If no leads found in grid view, try list view
                if (leadCount === 0) {
                    const listLeads = document.querySelectorAll(`.leads-list-container .lead-item[data-status="${status}"]`);
                    leadCount = listLeads.length;
                }
            }
            
            // Update the count badge
            countBadge.textContent = leadCount;
        });
    }
    
    /**
     * Updates the count badge for a specific status
     * @param {string} status - The status to update the count for
     */
    function updateStatusCountBadge(status) {
        if (!status) return;
        
        // Convert status to lowercase for data attribute matching
        status = status.toLowerCase();
        
        // Find the status box for this status
        const statusBox = document.querySelector(`.status-box[data-status="${status}"]`);
        if (!statusBox) {
            console.error(`Status box not found for status: ${status}`);
            return;
        }
        
        // Find the count badge
        const countBadge = statusBox.querySelector('.count-badge');
        if (!countBadge) {
            console.error(`Count badge not found for status: ${status}`);
            return;
        }
        
        // Get the current count
        let currentCount = parseInt(countBadge.textContent) || 0;
        
        // Increment the count
        currentCount++;
        
        // Update the badge
        countBadge.textContent = currentCount;
        
        // Add a highlight effect to the badge
        countBadge.classList.add('badge-highlight');
        
        // Remove the highlight effect after 2 seconds
        setTimeout(() => {
            countBadge.classList.remove('badge-highlight');
        }, 2000);
    }
    
    /**
     * Create a lead card element
     * @param {Object} leadData - The lead data object
     * @returns {HTMLElement} - The lead card element
     */
    function createLeadCard(leadData) {
        // Create card container
        const cardDiv = document.createElement('div');
        cardDiv.className = 'mb-3 lead-item';
        cardDiv.setAttribute('data-lead-id', leadData.id);
        
        // Format date
        const createdDate = new Date(leadData.created_at);
        const formattedDate = createdDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
        
        // Format date as DD-MM-YYYY
        const formattedDateDashed = `${createdDate.getDate().toString().padStart(2, '0')}-${(createdDate.getMonth() + 1).toString().padStart(2, '0')}-${createdDate.getFullYear()}`;
        
        // Get time as HH:MM
        const formattedTime = `${createdDate.getHours().toString().padStart(2, '0')}:${createdDate.getMinutes().toString().padStart(2, '0')}`;
        
        // Create card HTML to match the screenshot layout
        cardDiv.innerHTML = `
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="mb-2 d-flex justify-content-between">
                        <span class="badge ${getBadgeClass(leadData.status)}">${leadData.statusDisplay || leadData.status || 'New'}</span>
                        <span class="badge ${getSourceDotClass(leadData.source)}">${leadData.sourceDisplay || leadData.source || 'Online'}</span>
                    </div>
                    
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-user text-primary me-2"></i>
                        <span>${leadData.name || 'Unnamed Lead'}</span>
                    </div>
                    
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-phone text-success me-2"></i>
                        <span>${leadData.phone || 'N/A'}</span>
                    </div>
                    
                    <div class="mt-2">
                        <div><strong>CD :</strong> ${formattedDateDashed} ${formattedTime}</div>
                        <div><strong>BY :</strong> Admin</div>
                        <div><strong>TO :</strong> ${leadData.assigned_to_name || 'Varun Dhavan'}</div>
                    </div>
                    
                    <div class="d-flex mt-3 border-top pt-2">
                        <a href="#" class="text-danger me-2"><i class="fas fa-trash"></i></a>
                        <a href="#" class="text-secondary me-2"><i class="fas fa-tag"></i></a>
                        <a href="#" class="text-secondary me-2"><i class="fas fa-user"></i></a>
                        <a href="#" class="text-secondary me-2"><i class="fas fa-share"></i></a>
                        <a href="#" class="text-secondary"><i class="fas fa-comment"></i></a>
                        <div class="ms-auto">
                            <span class="badge bg-secondary">0</span>
                        </div>
                        <div class="dropdown ms-2">
                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#">Edit</a></li>
                                <li><a class="dropdown-item" href="#">Delete</a></li>
                                <li><a class="dropdown-item" href="#">View Details</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        return cardDiv;
    }
    
    /**
     * Update the count badge for a specific status
     * @param {string} status - The status to update the count for
     */
    function updateStatusCount(status) {
        const statusBox = document.querySelector(`.status-box[data-status="${status}"]`);
        if (!statusBox) return;
        
        const countBadge = statusBox.querySelector('.count-badge');
        if (!countBadge) return;
        
        // Get the current count
        let count = parseInt(countBadge.textContent);
        if (isNaN(count)) count = 0;
        
        // Increment the count
        count++;
        
        // Update the badge
        countBadge.textContent = count;
    }
    
    /**
     * Get the appropriate badge class for a status
     * @param {string} status - The status value
     * @returns {string} - The badge class
     */
    function getBadgeClass(status) {
        if (!status) return 'bg-secondary';
        
        status = status.toLowerCase();
        switch (status) {
            case 'new':
                return 'bg-primary';
            case 'processing':
                return 'bg-info';
            case 'close-by':
                return 'bg-warning';
            case 'confirm':
                return 'bg-success';
            case 'cancel':
                return 'bg-danger';
            default:
                return 'bg-secondary';
        }
    }
    
    /**
     * Get the appropriate badge class for a source
     * @param {string} source - The source value
     * @returns {string} - The badge class
     */
    function getSourceDotClass(source) {
        if (!source) return 'bg-secondary';
        
        source = source.toLowerCase();
        switch (source) {
            case 'online':
                return 'bg-primary';
            case 'offline':
                return 'bg-info';
            case 'website':
                return 'bg-success';
            case 'whatsapp':
                return 'bg-warning';
            case 'customer reminder':
                return 'bg-danger';
            default:
                return 'bg-secondary';
        }
    }
});
