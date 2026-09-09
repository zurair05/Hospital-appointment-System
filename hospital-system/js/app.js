// ===== Hospital Appointment System - JavaScript =====
// Only client-side form validation

// --- Helper: Validate email format ---
function validateEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// --- Helper: Validate 10-digit phone ---
function validatePhone(phone) {
    var re = /^[0-9]{10}$/;
    return re.test(phone);
}

// --- Helper: Check required field ---
function validateRequired(value) {
    return value.trim() !== '';
}

// --- Show error message under a field ---
function showError(inputId, message) {
    var input = document.getElementById(inputId);
    var errorDiv = document.getElementById(inputId + '-error');
    if (input) input.classList.add('invalid');
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
}

// --- Clear error message ---
function clearError(inputId) {
    var input = document.getElementById(inputId);
    var errorDiv = document.getElementById(inputId + '-error');
    if (input) input.classList.remove('invalid');
    if (errorDiv) errorDiv.style.display = 'none';
}

// --- Login Form Validation ---
function validateLogin(form) {
    var isValid = true;
    var email = form ? form.querySelector('input[type="email"]') : document.getElementById('login-email');
    var password = form ? form.querySelector('input[type="password"]') : document.getElementById('login-password');
    var emailId = email ? email.id : '';
    var passwordId = password ? password.id : '';

    if (email && !validateEmail(email.value)) {
        showError(emailId, 'Please enter a valid email');
        isValid = false;
    } else {
        clearError(emailId);
    }

    if (password && !validateRequired(password.value)) {
        showError(passwordId, 'Password is required');
        isValid = false;
    } else {
        clearError(passwordId);
    }

    if (isValid) {
        alert('Login successful! (This is a demo)');
    }
    return false;
}

// --- Registration Form Validation ---
function validateRegister() {
    var isValid = true;
    var name = document.getElementById('reg-name');
    var email = document.getElementById('reg-email');
    var phone = document.getElementById('reg-phone');
    var dob = document.getElementById('reg-dob');
    var password = document.getElementById('reg-password');
    var confirmPassword = document.getElementById('reg-confirm-password');

    if (name && !validateRequired(name.value)) {
        showError('reg-name', 'Name is required');
        isValid = false;
    } else {
        clearError('reg-name');
    }

    if (email && !validateEmail(email.value)) {
        showError('reg-email', 'Please enter a valid email');
        isValid = false;
    } else {
        clearError('reg-email');
    }

    if (phone && !validatePhone(phone.value)) {
        showError('reg-phone', 'Phone must be 10 digits');
        isValid = false;
    } else {
        clearError('reg-phone');
    }

    if (dob && !validateRequired(dob.value)) {
        showError('reg-dob', 'Date of birth is required');
        isValid = false;
    } else {
        clearError('reg-dob');
    }

    if (password && password.value.length < 6) {
        showError('reg-password', 'Password must be at least 6 characters');
        isValid = false;
    } else {
        clearError('reg-password');
    }

    if (confirmPassword && password && confirmPassword.value !== password.value) {
        showError('reg-confirm-password', 'Passwords do not match');
        isValid = false;
    } else {
        clearError('reg-confirm-password');
    }

    if (isValid) {
        alert('Registration successful! (This is a demo)');
    }
    return false;
}

// --- Appointment Form Validation ---
function validateAppointment() {
    var isValid = true;
    var doctor = document.getElementById('appt-doctor');
    var date = document.getElementById('appt-date');
    var reason = document.getElementById('appt-reason');
    var timeSlot = document.querySelector('input[name="time"]:checked');

    if (doctor && !validateRequired(doctor.value)) {
        showError('appt-doctor', 'Please select a doctor');
        isValid = false;
    } else {
        clearError('appt-doctor');
    }

    if (date && !validateRequired(date.value)) {
        showError('appt-date', 'Please select a date');
        isValid = false;
    } else if (date && date.value < new Date().toISOString().split('T')[0]) {
        showError('appt-date', 'Please select today or a future date');
        isValid = false;
    } else {
        clearError('appt-date');
    }

    if (reason && !validateRequired(reason.value)) {
        showError('appt-reason', 'Please enter a reason for the visit');
        isValid = false;
    } else {
        clearError('appt-reason');
    }

    if (!timeSlot) {
        alert('Please select a time slot');
        isValid = false;
    }

    if (isValid) {
        alert('Appointment booked successfully! (This is a demo)');
    }
    return false;
}

document.addEventListener('DOMContentLoaded', function () {
    var appointmentDate = document.getElementById('appt-date');
    if (appointmentDate) {
        appointmentDate.min = new Date().toISOString().split('T')[0];
    }
});
