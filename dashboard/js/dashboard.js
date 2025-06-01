document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    let statusChart = null;
    let sourceChart = null;

    // Function to generate a unique submission ID
    function generateSubmissionId() {
        return 'submit_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    // Function to initialize charts
    function initializeCharts() {
        const statusCtx = document.getElementById('leadStatusChart').getContext('2d');
        const sourceCtx = document.getElementById('leadSourceChart').getContext('2d');

        // Initialize Lead Status Chart
        statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Leads Status',
                        position: 'bottom'
                    }
                },
                cutout: '70%'
            }
        });

        // Initialize Lead Source Chart
        sourceChart = new Chart(sourceCtx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Leads Source',
                        position: 'bottom'
                    }
                },
                cutout: '70%'
            }
        });
    }

    // Function to update charts with new data
    function updateCharts(summary, sources) {
        // Update Status Chart
        statusChart.data.labels = summary.map(item => item.status_name);
        statusChart.data.datasets[0].data = summary.map(item => item.lead_count);
        statusChart.data.datasets[0].backgroundColor = summary.map(item => item.status_color);
        statusChart.update();

        // Update Source Chart
        sourceChart.data.labels = sources.map(item => item.source_name);
        sourceChart.data.datasets[0].data = sources.map(item => item.lead_count);
        sourceChart.data.datasets[0].backgroundColor = sources.map(item => item.source_color);
        sourceChart.update();

        // Update center text
        updateChartCenterText('leadStatusChart', summary.reduce((a, b) => a + parseInt(b.lead_count), 0));
        updateChartCenterText('leadSourceChart', sources.reduce((a, b) => a + parseInt(b.lead_count), 0));
    }

    // Function to update chart center text
    function updateChartCenterText(chartId, value) {
        const chartContainer = document.getElementById(chartId).parentElement;
        let centerText = chartContainer.querySelector('.chart-center-text');
        
        if (!centerText) {
            centerText = document.createElement('div');
            centerText.className = 'chart-center-text';
            chartContainer.appendChild(centerText);
        }
        
        centerText.innerHTML = `
            <div class="total-value">${value}</div>
            <div class="total-label">Total</div>
        `;
    }

    // Function to create lead card
    function createLeadCard(lead) {
        const createdDate = new Date(lead.created_at);
        const formattedDate = createdDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
        const formattedTime = createdDate.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });

        return `
            <div class="lead-card mb-3" data-lead-id="${lead.id}">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge" style="background-color: ${lead.status_color}">${lead.status_name}</span>
                            <span class="badge bg-info">${lead.source_name}</span>
                        </div>
                        <h6 class="mb-1">${lead.name}</h6>
                        <div class="small text-muted mb-2">${lead.company || 'No Company'}</div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-phone text-primary me-2"></i>
                            <span>${lead.phone}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            <span>${lead.email || 'No Email'}</span>
                        </div>
                        <div class="small text-muted mt-2">
                            <div><strong>Created:</strong> ${formattedDate} ${formattedTime}</div>
                            <div><strong>Assigned to:</strong> ${lead.assigned_to_name}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Function to update leads display
    function updateLeadsDisplay(leads) {
        const leadsContainer = document.getElementById('newLeadsContainer');
        const noLeadsMessage = document.getElementById('noLeadsMessage');
        
        if (!leads || leads.length === 0) {
            if (noLeadsMessage) noLeadsMessage.classList.remove('d-none');
            if (leadsContainer) leadsContainer.classList.add('d-none');
        } else {
            if (noLeadsMessage) noLeadsMessage.classList.add('d-none');
            if (leadsContainer) {
                leadsContainer.classList.remove('d-none');
                leadsContainer.innerHTML = leads.map(lead => createLeadCard(lead)).join('');
            }
        }

        // Update status counts
        updateStatusCounts();
    }

    // Function to update status counts
    function updateStatusCounts() {
        fetch('ajax/get_status_counts.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update badge counts
                    document.querySelector('#new-tab .badge-count').textContent = data.counts.new || '0';
                    document.querySelector('#processing-tab .badge-count').textContent = data.counts.processing || '0';
                    document.querySelector('#close-by-tab .badge-count').textContent = data.counts.close_by || '0';
                }
            })
            .catch(error => console.error('Error updating status counts:', error));
    }

    // Function to refresh dashboard data
    function refreshDashboard() {
        // Show loading state
        document.querySelectorAll('.loading-spinner').forEach(spinner => {
            spinner.classList.remove('d-none');
        });

        fetch('ajax/get_dashboard_data.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    updateCharts(data.data.summary, data.data.sources);
                    updateLeadsDisplay(data.data.newLeads);
                }
            })
            .catch(error => {
                console.error('Error fetching dashboard data:', error);
            })
            .finally(() => {
                // Hide loading state
                document.querySelectorAll('.loading-spinner').forEach(spinner => {
                    spinner.classList.add('d-none');
                });
            });
    }

    // Handle form submission
    const addLeadForm = document.getElementById('addLeadForm');
    if (addLeadForm) {
        // Remove any existing event listeners
        const newForm = addLeadForm.cloneNode(true);
        addLeadForm.parentNode.replaceChild(newForm, addLeadForm);

        // Add single event listener to new form
        newForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Prevent multiple submissions
            if (this.submitting) return;
            this.submitting = true;

            // Generate a unique submission ID
            const submissionId = generateSubmissionId();

            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            submitButton.disabled = true;

            // Create FormData and append submission ID
            const formData = new FormData(this);
            formData.append('submission_id', submissionId);

            // Submit form data
            fetch('ajax/save_lead.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Show success message
                    const toast = new bootstrap.Toast(document.getElementById('successToast'));
                    document.querySelector('#successToast .toast-body').textContent = 'Lead added successfully!';
                    toast.show();

                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addLeadModal'));
                    modal.hide();

                    // Reset form
                    this.reset();

                    // Refresh dashboard data
                    refreshDashboard();

                    // If we're on the leads page, reload after 2 seconds
                    if (window.location.pathname.includes('leads.php')) {
                        setTimeout(() => window.location.reload(), 2000);
                    }
                } else {
                    throw new Error(data.message || 'Failed to save lead');
                }
            })
            .catch(error => {
                console.error('Error saving lead:', error);
                alert('Failed to save lead: ' + error.message);
            })
            .finally(() => {
                // Reset button state and form state
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
                this.submitting = false;
            });
        });
    }

    // Initialize charts
    initializeCharts();

    // Initial data load
    refreshDashboard();

    // Set up auto-refresh every 30 seconds
    setInterval(refreshDashboard, 30000);
});