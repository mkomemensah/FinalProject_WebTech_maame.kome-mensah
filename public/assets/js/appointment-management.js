/**
 * Client-side JavaScript for managing appointments
 * Handles rescheduling and cancellation of appointments
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle reschedule button clicks
    document.querySelectorAll('.btn-reschedule').forEach(button => {
        button.addEventListener('click', function() {
            const appointmentId = this.dataset.appointmentId;
            showRescheduleModal(appointmentId);
        });
    });

    // Handle cancel button clicks
    document.querySelectorAll('.btn-cancel').forEach(button => {
        button.addEventListener('click', function() {
            const appointmentId = this.dataset.appointmentId;
            showCancelModal(appointmentId);
        });
    });

    // Handle reschedule form submission
    const rescheduleForm = document.getElementById('rescheduleAppointmentForm');
    if (rescheduleForm) {
        rescheduleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const appointmentId = formData.get('appointment_id');
            
            // Validate form
            const newDate = formData.get('new_date');
            const newStartTime = formData.get('new_start_time');
            const newEndTime = formData.get('new_end_time');
            
            if (!newDate || !newStartTime || !newEndTime) {
                showAlert('Please fill in all fields', 'danger');
                return;
            }
            
            // Convert to Date objects for comparison
            const startDateTime = new Date(`${newDate}T${newStartTime}`);
            const endDateTime = new Date(`${newDate}T${newEndTime}`);
            
            if (startDateTime >= endDateTime) {
                showAlert('End time must be after start time', 'danger');
                return;
            }
            
            if (startDateTime < new Date()) {
                showAlert('Cannot schedule an appointment in the past', 'danger');
                return;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Rescheduling...';
            
            // Send request to server
            fetch('/api/appointments.php?action=reschedule', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Appointment rescheduled successfully!', 'success');
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('rescheduleAppointmentModal'));
                    modal.hide();
                    // Reload appointments
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    throw new Error(data.error || 'Failed to reschedule appointment');
                }
            })
            .catch(error => {
                showAlert(error.message, 'danger');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }

    // Handle cancel form submission
    const cancelForm = document.getElementById('cancelAppointmentForm');
    if (cancelForm) {
        cancelForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const appointmentId = formData.get('appointment_id');
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cancelling...';
            
            // Send request to server
            fetch('/api/appointments.php?action=cancel', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Appointment cancelled successfully!', 'success');
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('cancelAppointmentModal'));
                    modal.hide();
                    // Reload appointments
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    throw new Error(data.error || 'Failed to cancel appointment');
                }
            })
            .catch(error => {
                showAlert(error.message, 'danger');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }

    // Initialize date and time pickers
    const dateInput = document.getElementById('new_date');
    if (dateInput) {
        // Set min date to today
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
        
        // Set max date to 3 months from now
        const maxDate = new Date();
        maxDate.setMonth(maxDate.getMonth() + 3);
        dateInput.max = maxDate.toISOString().split('T')[0];
    }
});

/**
 * Show the reschedule modal and populate it with appointment data
 * @param {string} appointmentId - The ID of the appointment to reschedule
 */
function showRescheduleModal(appointmentId) {
    // Set the appointment ID in the form
    document.getElementById('reschedule_appointment_id').value = appointmentId;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('rescheduleAppointmentModal'));
    modal.show();
}

/**
 * Show the cancel modal and populate it with appointment data
 * @param {string} appointmentId - The ID of the appointment to cancel
 */
function showCancelModal(appointmentId) {
    // Set the appointment ID in the form
    document.getElementById('cancel_appointment_id').value = appointmentId;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('cancelAppointmentModal'));
    modal.show();
}

/**
 * Show a Bootstrap alert message
 * @param {string} message - The message to display
 * @param {string} type - The alert type (e.g., 'success', 'danger', 'warning', 'info')
 */
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const container = document.getElementById('alerts-container') || document.body;
    container.prepend(alertDiv);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = bootstrap.Alert.getOrCreateInstance(alertDiv);
        if (alert) alert.close();
    }, 5000);
}

/**
 * Format a date string to a more readable format
 * @param {string} dateString - The date string to format
 * @returns {string} Formatted date string
 */
function formatDate(dateString) {
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(dateString).toLocaleDateString(undefined, options);
}
