// public/assets/js/validation.js

function showError(input, msg) {
    input.classList.add('is-invalid');
    let errorDiv = input.parentNode.querySelector('.invalid-feedback');
    if(errorDiv) errorDiv.textContent = msg;
}
function clearError(input) {
    input.classList.remove('is-invalid');
    let errorDiv = input.parentNode.querySelector('.invalid-feedback');
    if(errorDiv) errorDiv.textContent = '';
}

document.addEventListener('DOMContentLoaded', function() {
    let forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            let valid = true;

            // Email
            let email = form.querySelector('[name="email"]');
            if(email && !/^\S+@\S+\.\S+$/.test(email.value)) {
                showError(email, "Enter a valid email address");
                valid = false;
            } else if(email) clearError(email);

            // Password
            let password = form.querySelector('[name="password"]');
            if(password && !/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}$/.test(password.value)) {
                showError(password, "Password: 8+ chars, upper/lower/number/symbol");
                valid = false;
            } else if(password) clearError(password);

            // Confirm password
            let confirmPassword = form.querySelector('[name="confirm_password"]');
            if(confirmPassword && password) {
                if(confirmPassword.value !== password.value) {
                    showError(confirmPassword, "Passwords do not match");
                    valid = false;
                } else {
                    clearError(confirmPassword);
                }
            }

            // Name
            let name = form.querySelector('[name="name"]');
            if(name && !/^[a-zA-Z ]+$/.test(name.value)) {
                showError(name, "Name: letters & spaces only");
                valid = false;
            } else if(name) clearError(name);

            // Phone (Ghana)
            let phone = form.querySelector('[name="phone"]');
            if(phone && !/^0[23549][0-9]{8}$/.test(phone.value)) {
                showError(phone, "Phone: valid Ghana number");
                valid = false;
            } else if(phone) clearError(phone);

            if(!valid) e.preventDefault();
        });
    });

    // Add 'blur' validators
    document.querySelectorAll('input').forEach(function(input) {
        input.addEventListener('blur', function() {
            let event = new Event('submit', {cancelable: true});
            let form = input.form;
            form.dispatchEvent(event);
        });
    });
});