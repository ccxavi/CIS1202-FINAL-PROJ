document.addEventListener('DOMContentLoaded', function() {
    const passwordForm = document.getElementById('passwordForm');
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    const currentPassword = document.getElementById('currentPassword');
    const passwordMatchError = document.getElementById('passwordMatchError');
    const passwordFeedback = document.getElementById('passwordFeedback');
    const submitBtn = document.getElementById('submitBtn');
    
    // Function to check if passwords match
    function checkPasswordsMatch() {
        if (confirmPassword.value === '') {
            passwordMatchError.textContent = '';
            return false;
        } else if (newPassword.value !== confirmPassword.value) {
            passwordMatchError.textContent = 'Passwords do not match';
            return false;
        } else {
            passwordMatchError.textContent = '';
            return true;
        }
    }
    
    // Check if new password is same as current password
    function checkNewPasswordDifferent() {
        return currentPassword.value !== newPassword.value || newPassword.value === '';
    }
    
    // Live validation as user types
    confirmPassword.addEventListener('input', checkPasswordsMatch);
    
    // Validate on form submission
    passwordForm.addEventListener('submit', function(event) {
        // Reset feedback
        passwordFeedback.classList.add('d-none');
        passwordFeedback.textContent = '';
        
        let isValid = true;
        let errorMessages = [];
        
        // Check if passwords match
        if (!checkPasswordsMatch()) {
            errorMessages.push('Passwords do not match.');
            isValid = false;
        }
        
        // Check if new password is different from current
        if (!checkNewPasswordDifferent()) {
            errorMessages.push('New password cannot be the same as your current password.');
            isValid = false;
        }
        
        // If there are validation errors, prevent form submission
        if (!isValid) {
            event.preventDefault();
            passwordFeedback.textContent = errorMessages.join(' ');
            passwordFeedback.classList.remove('d-none');
        }
    });
});