document.addEventListener('DOMContentLoaded', function() {
    const accountTypeSelect = document.getElementById('accountType');
    const studentFields = document.getElementById('studentFields');
    const teacherFields = document.getElementById('teacherFields');
    const authForm = document.getElementById('authForm');
    const authFeedback = document.getElementById('authFeedback');
    const statusDisplay = document.querySelector('.verification-status');

    // Toggle fields based on account type selection
    accountTypeSelect?.addEventListener('change', function() {
        if (this.value === 'student') {
            studentFields.classList.remove('d-none');
            teacherFields.classList.add('d-none');
        } else if (this.value === 'teacher') {
            teacherFields.classList.remove('d-none');
            studentFields.classList.add('d-none');
        } else {
            studentFields.classList.add('d-none');
            teacherFields.classList.add('d-none');
        }
    });

    // Handle form submission
    authForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        console.log('Auth form submission initiated.'); // Log: Start of submission

        const formData = new FormData(this);
        
        // Validate file upload
        const verificationFile = formData.get('verificationId');
        if (!verificationFile || verificationFile.size === 0) {
            console.warn('No verification file selected.'); // Log: Validation fail
            authFeedback.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Please select a verification document to upload.
                </div>`;
            return;
        }
        
        try {
            // Show loading state
            authFeedback.innerHTML = `
                <div class="alert alert-info">
                    <i class="bi bi-hourglass-split"></i>
                    Submitting your verification request...
                </div>`;
            
            console.log('Fetching handleProfileAuth.php...'); // Log: Fetch start
            const response = await fetch('./controllers/handleProfileAuth.php', {
                method: 'POST',
                body: formData
            });

            console.log('Fetch response received:', response.status); // Log: Fetch response status
            const data = await response.json();
            console.log('Response data from server:', data); // Log: Full server response data
            
            if (data.success) {
                console.log('Auth form submission successful according to server:', data.message);
                // Update feedback with success message
                authFeedback.innerHTML = `
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i>
                        ${data.message}
                    </div>`;
                
                // Update verification status display
                if (statusDisplay) {
                    console.log('statusDisplay element found. Current innerHTML:', statusDisplay.innerHTML);
                    statusDisplay.innerHTML = `
                        <div class="d-flex align-items-center gap-2 text-warning">
                            <i class="bi bi-hourglass-split"></i>
                            <span class="text-capitalize">Status: Pending</span>
                        </div>`;
                    console.log('statusDisplay innerHTML updated for Pending. New innerHTML:', statusDisplay.innerHTML);
                } else {
                    console.error('statusDisplay element (.verification-status) NOT found!');
                }

                // Clear file inputs
                const fileInputs = authForm.querySelectorAll('input[type="file"]');
                fileInputs.forEach(input => input.value = '');
                console.log('File inputs cleared.');

                // Optional: Close modal after successful submission
                const modalInstance = bootstrap.Modal.getInstance(document.getElementById('settingsModal'));
                if (modalInstance) {
                    console.log('Modal instance found, scheduling close.');
                    setTimeout(() => {
                        modalInstance.hide();
                        console.log('Modal hidden.');
                    }, 2000); // Close after 2 seconds
                }
            } else {
                console.warn('Auth form submission failed or reported not successful by server:', data.message);
                authFeedback.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        ${data.message}
                    </div>`;
            }
        } catch (error) {
            console.error('Error during auth form submission fetch/processing:', error);
            authFeedback.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    An error occurred. Please try again later.
                </div>`;
        }
    });
}); 