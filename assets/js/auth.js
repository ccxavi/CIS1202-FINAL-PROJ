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
        
        registerPassword.parentNode.insertBefore(registerFeedback, registerPassword.nextSibling);
        
        // Validate register password on input
        registerPassword.addEventListener('input', function() {
            if (this.value.length > 0 && this.value.length < 6) {
                registerFeedback.style.display = 'block';
            } else {
                registerFeedback.style.display = 'none';
            }
        });
    }
    
    // Password visibility toggle for login form only
    const loginPassword = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    
    if (loginPassword && togglePassword) {
        togglePassword.addEventListener('click', function() {
            // If current type is password, change to text (show password)
            const type = loginPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            loginPassword.setAttribute('type', type);
            
            // Toggle icon - use slashed icon when password is hidden
            if (type === 'password') {
                // Password is now hidden, show the slashed eye
                this.querySelector('i').classList.remove('bi-eye');
                this.querySelector('i').classList.add('bi-eye-slash');
            } else {
                // Password is now visible, show the regular eye
                this.querySelector('i').classList.remove('bi-eye-slash');
                this.querySelector('i').classList.add('bi-eye');
            }
        });
    }
}); 