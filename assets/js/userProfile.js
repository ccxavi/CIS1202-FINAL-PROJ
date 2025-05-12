// Updated userProfile.js with new dropdown functionality

document.addEventListener('DOMContentLoaded', function() {
    // Determine if we're in a subdirectory by checking the URL path
    const isInSubdirectory = window.location.pathname.includes('/views/');
    const baseUrl = isInSubdirectory ? '../' : './';
    
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
        
        // Fix path when modal is opened
        const settingsModal = document.getElementById('settingsModal');
        if (settingsModal) {
            settingsModal.addEventListener('show.bs.modal', function() {
                // Fix profile picture paths in modal if we're in a subdirectory
                if (isInSubdirectory) {
                    const modalProfileImages = settingsModal.querySelectorAll('img.settings-profile-pic');
                    modalProfileImages.forEach(img => {
                        // If the image src starts with "./assets", change to "../assets"
                        if (img.src.includes('/assets/') && !img.src.includes('../assets/')) {
                            const currentSrc = img.getAttribute('src');
                            if (currentSrc && currentSrc.startsWith('./')) {
                                img.src = '../' + currentSrc.substring(2);
                                console.log('Fixed modal image path:', img.src);
                            }
                        }
                    });
                }
            });
        }
    } catch (e) {
        console.log('Bootstrap objects not found, using custom implementations:', e);
    }
    
    // Settings Modal Functionality - Show preview only, upload on save
    const profilePicUploadInput = document.getElementById('profilePicUpload');
    const profilePicFeedbackDiv = document.getElementById('profilePicFeedback');
    const profilePicImages = document.querySelectorAll('img[alt="Profile"], img[alt="Profile Picture"]');
    
    // Store the selected file for later upload
    let selectedProfilePicFile = null;

    if (profilePicUploadInput) {
        profilePicUploadInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                // Store the file for later upload
                selectedProfilePicFile = file;
                
                // Show a preview of the selected image
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Update only the preview image in the modal
                    const previewImage = document.querySelector('#profilePicContent .settings-profile-pic');
                    if (previewImage) {
                        previewImage.src = e.target.result;
                    }
                    
                    if(profilePicFeedbackDiv) {
                        profilePicFeedbackDiv.textContent = 'Click "Save Changes" to upload.';
                        profilePicFeedbackDiv.className = 'form-text text-center text-info';
                    }
                };
                reader.readAsDataURL(file);
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
            if (accountInfoFeedbackDiv) displayFeedback(accountInfoFeedbackDiv, '', true);
            if (passwordChangeFeedbackDiv) displayFeedback(passwordChangeFeedbackDiv, '', true);
            
            console.log('Active tab ID:', activeTabPane.id);
            console.log('Selected file:', selectedProfilePicFile ? 'File selected' : 'No file');
            
            // Handle profile picture upload if a file was selected
            if (activeTabPane.id === 'profilePicContent' && selectedProfilePicFile) {
                if(profilePicFeedbackDiv) {
                    profilePicFeedbackDiv.textContent = 'Uploading...';
                    profilePicFeedbackDiv.className = 'form-text text-info';
                }
                
                const formData = new FormData();
                formData.append('profilePic', selectedProfilePicFile);
                
                const uploadUrl = baseUrl + 'controllers/upload.php';
                console.log('Uploading to:', uploadUrl);
                
                fetch(uploadUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Upload response:', data);
                    if (data.success) {
                        if(profilePicFeedbackDiv) {
                            profilePicFeedbackDiv.textContent = 'Updated Successfully!';
                            profilePicFeedbackDiv.className = 'form-text text-center text-success';
                            setTimeout(() => {
                                profilePicFeedbackDiv.textContent = '';
                                profilePicFeedbackDiv.className = 'form-text';
                            }, 1500);
                        }
                        
                        // Fix the image URL path based on current location
                        let newImageUrl = data.newProfilePicUrl;
                        
                        // If the path starts with './' and we're in a subdirectory, modify it
                        if (isInSubdirectory && newImageUrl.startsWith('./')) {
                            newImageUrl = '../' + newImageUrl.substring(2);
                        }
                        
                        // Add cache buster
                        newImageUrl += '?t=' + new Date().getTime();
                        
                        console.log('New profile image URL:', newImageUrl);
                        
                        // Update all profile pictures with the new image
                        profilePicImages.forEach(img => {
                            img.src = newImageUrl;
                        });
                        
                        // Reset the file input and selection
                        profilePicUploadInput.value = '';
                        selectedProfilePicFile = null;
                        
                    } else {
                        if(profilePicFeedbackDiv) {
                            profilePicFeedbackDiv.textContent = data.message || 'Upload failed. Please try again.';
                            profilePicFeedbackDiv.className = 'form-text text-danger';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error uploading profile picture:', error);
                    if(profilePicFeedbackDiv) {
                        profilePicFeedbackDiv.textContent = 'An error occurred during upload. See console for details.';
                        profilePicFeedbackDiv.className = 'form-text text-danger';
                    }
                });
            }
            else if (activeTabPane.id === 'accountInfoContent' && accountInfoForm) {
                // Handle Account Information Update
                const formData = new FormData(accountInfoForm);
                if(accountInfoFeedbackDiv) displayFeedback(accountInfoFeedbackDiv, 'Saving account details...', true);
                
                const updateUrl = baseUrl + 'controllers/updateAccount.php';
                console.log('Updating account info at:', updateUrl);

                fetch(updateUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Account update response:', data);
                    if (data.success) {
                        if(accountInfoFeedbackDiv) displayFeedback(accountInfoFeedbackDiv, data.message, true);
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
                        if(accountInfoFeedbackDiv) displayFeedback(accountInfoFeedbackDiv, data.message, false);
                    }
                })
                .catch(error => {
                    console.error('Error updating account info:', error);
                    if(accountInfoFeedbackDiv) displayFeedback(accountInfoFeedbackDiv, 'An error occurred. Please try again.', false);
                });

            } else if (activeTabPane.id === 'passwordContent' && passwordForm) {
                // Handle Password Change
                const newPasswordInput = document.getElementById('newPassword');
                const confirmPasswordInput = document.getElementById('confirmPassword');
                const currentPasswordInput = document.getElementById('currentPassword');
                
                if (!newPasswordInput || !confirmPasswordInput) {
                    console.error('Password inputs not found');
                    return;
                }
                
                if (newPasswordInput.value !== confirmPasswordInput.value) {
                    if(passwordChangeFeedbackDiv) displayFeedback(passwordChangeFeedbackDiv, 'New passwords do not match.', false);
                    return;
                }
                if (newPasswordInput.value.length < 6) {
                    if(passwordChangeFeedbackDiv) displayFeedback(passwordChangeFeedbackDiv, 'New password must be at least 6 characters.', false);
                    return;
                }

                const formData = new FormData(passwordForm);
                if(passwordChangeFeedbackDiv) displayFeedback(passwordChangeFeedbackDiv, 'Updating password...', true);
                
                const passwordUrl = baseUrl + 'controllers/updatePassword.php';
                console.log('Updating password at:', passwordUrl);

                fetch(passwordUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Password update response:', data);
                    if (data.success) {
                        if(passwordChangeFeedbackDiv) displayFeedback(passwordChangeFeedbackDiv, data.message, true);
                        passwordForm.reset(); // Clear password fields
                    } else {
                        if(passwordChangeFeedbackDiv) displayFeedback(passwordChangeFeedbackDiv, data.message, false);
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
                    if(passwordChangeFeedbackDiv) displayFeedback(passwordChangeFeedbackDiv, 'An error occurred. Please try again.', false);
                });
            }
        });
    }

    console.log('userProfile.js updated with path detection for different pages.');
});