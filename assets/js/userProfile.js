// Updated userProfile.js with new dropdown functionality

document.addEventListener('DOMContentLoaded', function() {
    // User dropdown toggle functionality - either use existing dropdown-toggle or new user-dropdown-toggle
    const userDropdownToggle = document.querySelector('.user-dropdown-toggle');
    const userDropdownMenu = document.querySelector('.user-dropdown-container .dropdown-menu');
    
    if (userDropdownToggle && userDropdownMenu) {
        userDropdownToggle.addEventListener('click', function(event) {
            event.stopPropagation();
            userDropdownMenu.classList.toggle('show');
        });
        
        document.addEventListener('click', function(event) {
            if (!userDropdownToggle.contains(event.target) && !userDropdownMenu.contains(event.target)) {
                userDropdownMenu.classList.remove('show');
            }
        });
    }
    
    // Bootstrap components initialization
    try {
        // Initialize Bootstrap tooltips if they exist
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        if (tooltipTriggerList.length > 0) {
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }
        
        // Try to manually initialize Bootstrap dropdowns
        const dropdownElementList = document.querySelectorAll('.dropdown-toggle, .user-dropdown-toggle');
        if (dropdownElementList.length > 0) {
            const dropdownList = [...dropdownElementList].map(dropdownToggleEl => {
                try {
                    return new bootstrap.Dropdown(dropdownToggleEl);
                } catch (e) {
                    console.log('Bootstrap Dropdown initialization failed, using custom implementation');
                    return null;
                }
            });
        }
        
        // Try to manually initialize Bootstrap modals
        const modalElementList = document.querySelectorAll('.modal');
        if (modalElementList.length > 0) {
            const modalList = [...modalElementList].map(modalEl => {
                try {
                    return new bootstrap.Modal(modalEl);
                } catch (e) {
                    console.log('Bootstrap Modal initialization failed');
                    return null;
                }
            });
        }
    } catch (e) {
        console.log('Bootstrap objects not found, using custom implementations');
    }
    
    // Settings Modal Functionality - Reverted to direct upload without Cropper.js
    const profilePicUploadInput = document.getElementById('profilePicUpload');
    const profilePicFeedbackDiv = document.getElementById('profilePicFeedback');
    const profilePicImages = document.querySelectorAll('img[alt="Profile"], img[alt="Profile Picture"]');
    // const settingsProfilePicInModal = document.querySelector('#profilePicContent .settings-profile-pic'); // No longer needed for special handling

    if (profilePicUploadInput) {
        profilePicUploadInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('profilePic', file);

                if(profilePicFeedbackDiv) profilePicFeedbackDiv.textContent = 'Uploading...';
                if(profilePicFeedbackDiv) profilePicFeedbackDiv.className = 'form-text text-info'; 

                fetch('./controllers/upload.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if(profilePicFeedbackDiv) profilePicFeedbackDiv.textContent = data.message || 'Upload successful!';
                        if(profilePicFeedbackDiv) profilePicFeedbackDiv.className = 'form-text text-success';
                        
                        const newImageUrl = data.newProfilePicUrl + '?t=' + new Date().getTime(); // Add cache buster
                        profilePicImages.forEach(img => {
                            img.src = newImageUrl;
                        });
                        profilePicUploadInput.value = ''; // Clear the file input

                    } else {
                        if(profilePicFeedbackDiv) profilePicFeedbackDiv.textContent = data.message || 'Upload failed. Please try again.';
                        if(profilePicFeedbackDiv) profilePicFeedbackDiv.className = 'form-text text-danger';
                    }
                })
                .catch(error => {
                    console.error('Error uploading profile picture:', error);
                    if(profilePicFeedbackDiv) profilePicFeedbackDiv.textContent = 'An error occurred during upload. See console for details.';
                    if(profilePicFeedbackDiv) profilePicFeedbackDiv.className = 'form-text text-danger';
                });
            }
        });
    }
    
    // Save Settings Button Logic
    const saveSettingsBtn = document.getElementById('saveSettingsBtn');
    const accountInfoForm = document.getElementById('accountInfoForm');
    const passwordForm = document.getElementById('passwordForm');
    const accountInfoFeedbackDiv = document.getElementById('accountInfoFeedback');
    const passwordChangeFeedbackDiv = document.getElementById('passwordChangeFeedback');

    // Helper to display feedback
    function displayFeedback(element, message, isSuccess) {
        if (element) {
            element.textContent = message;
            element.className = 'form-text ' + (isSuccess ? 'text-success' : 'text-danger');
        }
    }

    if (saveSettingsBtn) {
        saveSettingsBtn.addEventListener('click', function() {
            // Determine active tab
            const activeTabPane = document.querySelector('#settingsTabContent .tab-pane.active');
            if (!activeTabPane) return;

            // Clear previous feedback messages
            displayFeedback(accountInfoFeedbackDiv, '', true);
            displayFeedback(passwordChangeFeedbackDiv, '', true);

            if (activeTabPane.id === 'accountInfoContent') {
                // Handle Account Information Update
                const formData = new FormData(accountInfoForm);
                displayFeedback(accountInfoFeedbackDiv, 'Saving account details...', true);

                fetch('./controllers/updateAccount.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayFeedback(accountInfoFeedbackDiv, data.message, true);
                        // Update username/email in the header dropdown if elements exist
                        const headerUsername = document.querySelector('.user-dropdown-container .user-info .username');
                        const headerUserEmail = document.querySelector('.user-dropdown-container .user-info .user-role'); // Assuming role displays email
                        const dropdownMenuUsername = document.querySelector('.dropdown-menu .dropdown-section strong');
                        const dropdownMenuEmail = document.querySelector('.dropdown-menu .dropdown-section .user-email');

                        if(data.newUsername) {
                            if(headerUsername) headerUsername.textContent = data.newUsername;
                            if(dropdownMenuUsername) dropdownMenuUsername.textContent = data.newUsername;
                            // Also update the value in the modal form input itself
                            const usernameModalInput = document.getElementById('usernameModal');
                            if(usernameModalInput) usernameModalInput.value = data.newUsername;
                        }
                        if(data.newEmail) {
                            if(headerUserEmail) headerUserEmail.textContent = data.newEmail;
                            if(dropdownMenuEmail) dropdownMenuEmail.textContent = data.newEmail;
                            const emailModalInput = document.getElementById('emailModal');
                            if(emailModalInput) emailModalInput.value = data.newEmail;
                        }
                    } else {
                        displayFeedback(accountInfoFeedbackDiv, data.message, false);
                    }
                })
                .catch(error => {
                    console.error('Error updating account info:', error);
                    displayFeedback(accountInfoFeedbackDiv, 'An error occurred. Please try again.', false);
                });

            } else if (activeTabPane.id === 'passwordContent') {
                // Handle Password Change
                const newPasswordInput = document.getElementById('newPassword');
                const confirmPasswordInput = document.getElementById('confirmPassword');
                const currentPasswordInput = document.getElementById('currentPassword');
                
                if (newPasswordInput.value !== confirmPasswordInput.value) {
                    displayFeedback(passwordChangeFeedbackDiv, 'New passwords do not match.', false);
                    return;
                }
                if (newPasswordInput.value.length < 6) {
                    displayFeedback(passwordChangeFeedbackDiv, 'New password must be at least 6 characters.', false);
                    return;
                }

                const formData = new FormData(passwordForm);
                displayFeedback(passwordChangeFeedbackDiv, 'Updating password...', true);

                fetch('./controllers/updatePassword.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayFeedback(passwordChangeFeedbackDiv, data.message, true);
                        passwordForm.reset(); // Clear password fields
                    } else {
                        displayFeedback(passwordChangeFeedbackDiv, data.message, false);
                        // Optionally, clear only new password fields if current password was wrong
                        if (data.message && data.message.toLowerCase().includes('incorrect current password')) {
                            currentPasswordInput.value = '';
                            newPasswordInput.value = '';
                            confirmPasswordInput.value = '';
                            currentPasswordInput.focus();
                        } else {
                             newPasswordInput.value = '';
                             confirmPasswordInput.value = '';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating password:', error);
                    displayFeedback(passwordChangeFeedbackDiv, 'An error occurred. Please try again.', false);
                });
            }
        });
    }

    // Remove or integrate password validation from changePassword.js if it's redundant
    // const passwordFormValidation = document.getElementById('passwordForm');
    // if (passwordFormValidation) { ... existing validation ... }

    console.log('userProfile.js updated with Save Changes button logic.');
});