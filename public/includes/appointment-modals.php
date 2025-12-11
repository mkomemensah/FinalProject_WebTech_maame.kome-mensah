<?php /** @var array $appointment */ ?>

<!-- Reschedule Appointment Modal -->
<div class="modal fade" id="rescheduleAppointmentModal" tabindex="-1" aria-labelledby="rescheduleAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="rescheduleAppointmentModalLabel">Reschedule Appointment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rescheduleAppointmentForm">
                <div class="modal-body">
                    <input type="hidden" name="appointment_id" id="reschedule_appointment_id" value="">
                    
                    <div class="mb-3">
                        <label for="new_date" class="form-label">New Date</label>
                        <input type="date" class="form-control" id="new_date" name="new_date" required>
                        <div class="form-text">Select a new date for your appointment</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_start_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="new_start_time" name="new_start_time" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_end_time" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="new_end_time" name="new_end_time" required>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Please note that rescheduling is subject to consultant availability.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Reschedule Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Appointment Modal -->
<div class="modal fade" id="cancelAppointmentModal" tabindex="-1" aria-labelledby="cancelAppointmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="cancelAppointmentModalLabel">Cancel Appointment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cancelAppointmentForm">
                <div class="modal-body">
                    <input type="hidden" name="appointment_id" id="cancel_appointment_id" value="">
                    
                    <p>Are you sure you want to cancel this appointment?</p>
                    
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">Reason for cancellation (optional)</label>
                        <textarea class="form-control" id="cancellation_reason" name="reason" rows="3" placeholder="Please let us know why you're cancelling..."></textarea>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Note: Appointments cancelled less than 1 hour before the scheduled time may be subject to our cancellation policy.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Yes, Cancel Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Alerts Container -->
<div id="alerts-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1090; max-width: 350px;">
    <!-- Alerts will be inserted here by JavaScript -->
</div>

<style>
    /* Custom styles for the modals */
    .modal-header {
        border-bottom: none;
    }
    
    .modal-footer {
        border-top: none;
        background-color: #f8f9fa;
        border-radius: 0 0 0.3rem 0.3rem;
    }
    
    .btn-close:focus {
        box-shadow: none;
    }
    
    /* Animation for alerts */
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    .alert {
        animation: slideInRight 0.3s ease-out;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        margin-bottom: 1rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 576px) {
        #alerts-container {
            max-width: 100%;
            left: 0;
            right: 0;
            padding: 1rem;
        }
    }
</style>
