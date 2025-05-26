document.addEventListener('DOMContentLoaded', function() {
    // Tab activation based on URL hash
    const hash = window.location.hash;
    if (hash) {
        const tabToActivate = document.querySelector('.nav-tabs button[data-bs-target="' + hash + '"]');
        if (tabToActivate) {
            const tab = new bootstrap.Tab(tabToActivate);
            tab.show();
        }
    }
    
    // Password validation for register form only
    const registerPassword = document.getElementById('registerPassword');
    
    if (registerPassword) {
        const registerFeedback = document.createElement('div');
        registerFeedback.className = 'form-text text-danger mt-1 password-feedback';
        registerFeedback.style.display = 'none';
        registerFeedback.textContent = 'Password must be at least 6 characters.';
        
        // Insert after the password-container div instead of the input
        const passwordContainer = registerPassword.closest('.password-container');
        passwordContainer.parentNode.insertBefore(registerFeedback, passwordContainer.nextSibling);
        
        // Validate register password on input
        registerPassword.addEventListener('input', function() {
            if (this.value.length > 0 && this.value.length < 6) {
                registerFeedback.style.display = 'block';
            } else {
                registerFeedback.style.display = 'none';
            }
        });
    }
    
    // Function to toggle password visibility
    function setupPasswordToggle(toggleId, passwordId) {
        const toggleButton = document.getElementById(toggleId);
        const passwordInput = document.getElementById(passwordId);
        
        if (toggleButton && passwordInput) {
            toggleButton.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle icon
                const icon = this.querySelector('i');
                icon.classList.toggle('bi-eye');
                icon.classList.toggle('bi-eye-slash');
            });
        }
    }

    // Setup password toggles for both login and register forms
    setupPasswordToggle('togglePassword', 'password');
    setupPasswordToggle('toggleRegisterPassword', 'registerPassword');
}); 