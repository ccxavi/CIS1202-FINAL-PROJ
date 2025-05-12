// Updated userProfile.js with new dropdown functionality

document.addEventListener('DOMContentLoaded', function() {
    // Determine if we're in the root or in a subdirectory to fix API paths
    const isInRootDir = window.location.pathname.includes('index.php') || window.location.pathname.endsWith('/');
    const apiBasePath = isInRootDir ? './controllers/' : '../controllers/';
    console.log('API Base Path:', apiBasePath);
    
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
        
        // Initialize all modals in the page
        const modalElements = document.querySelectorAll('.modal');
        if (modalElements.length > 0) {
            modalElements.forEach(modalEl => {
                try {
                    // Create Bootstrap modal instance
                    const modalInstance = new bootstrap.Modal(modalEl);
                    
                    // Setup close button handlers
                    const closeButtons = modalEl.querySelectorAll('[data-bs-dismiss="modal"]');
                    closeButtons.forEach(btn => {
                        btn.addEventListener('click', function() {
                            modalInstance.hide();
                        });
                    });
                    
                    // Setup backdrop click to close
                    modalEl.addEventListener('click', function(e) {
                        if (e.target === modalEl) {
                            modalInstance.hide();
                        }
                    });
                    
                    // Store the modal instance on the element for later access
                    modalEl._bsModal = modalInstance;
                } catch (e) {
                    console.error('Failed to initialize modal:', modalEl.id, e);
                }
            });
            
            // Setup Save Changes button in Settings Modal to close modal after click
            const settingsModal = document.getElementById('settingsModal');
            if (settingsModal) {
                const saveSettingsBtn = settingsModal.querySelector('#saveSettingsBtn');
                if (saveSettingsBtn) {
                    const origClickHandler = saveSettingsBtn.onclick;
                    saveSettingsBtn.addEventListener('click', function(e) {
                        // Let original handler run first
                        if (origClickHandler) {
                            origClickHandler.call(this, e);
                        }
                        
                        // Then close the modal after a brief delay
                        setTimeout(function() {
                            if (settingsModal._bsModal) {
                                settingsModal._bsModal.hide();
                            } else {
                                // Fallback
                                settingsModal.classList.remove('show');
                                settingsModal.style.display = 'none';
                                document.body.classList.remove('modal-open');
                                const backdrop = document.querySelector('.modal-backdrop');
                                if (backdrop) backdrop.remove();
                }
                        }, 300);
                    });
                }
            }
        }
    } catch (e) {
        console.log('Bootstrap objects not found, using custom implementations', e);
    }
    
    // Settings Modal Functionality - Modified to wait for Save Changes button
    const profilePicUploadInput = document.getElementById('profilePicUpload');
    const profilePicFeedbackDiv = document.getElementById('profilePicFeedback');
    const profilePicImages = document.querySelectorAll('img[alt="Profile"], img[alt="Profile Picture"]');
    let selectedProfilePic = null;

    // Preview image when file is selected
    if (profilePicUploadInput) {
        profilePicUploadInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                selectedProfilePic = file;
                
                // Preview the selected image
                const profilePicPreview = document.querySelector('.settings-profile-pic');
                if (profilePicPreview) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        profilePicPreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
                
                if(profilePicFeedbackDiv) {
                    profilePicFeedbackDiv.textContent = 'New profile picture selected. Click "Save Changes" to update.';
                    profilePicFeedbackDiv.className = 'form-text text-info';
                }
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

    // Helper to fix image paths based on context
    function fixImagePath(path, newPath) {
        console.log('Fixing image path:', path, 'to:', newPath);
        // If in views directory, ensure relative path
        if (!isInRootDir && !newPath.startsWith('../')) {
            return '../' + newPath;
        }
        // If in root directory, remove leading dot
        if (isInRootDir && newPath.startsWith('./')) {
            return newPath;
        }
        return newPath;
    }

    if (saveSettingsBtn) {
        saveSettingsBtn.addEventListener('click', function() {
            // Determine active tab
            const activeTabPane = document.querySelector('#settingsTabContent .tab-pane.active');
            if (!activeTabPane) return;

            // Clear previous feedback messages
            if (accountInfoFeedbackDiv) displayFeedback(accountInfoFeedbackDiv, '', true);
            if (passwordChangeFeedbackDiv) displayFeedback(passwordChangeFeedbackDiv, '', true);

            if (activeTabPane.id === 'profilePicContent') {
                // Handle Profile Picture Upload
                if (selectedProfilePic) {
                    const formData = new FormData();
                    formData.append('profilePic', selectedProfilePic);
                    formData.append('deletePrevious', 'true'); // Signal to delete the previous profile pic
                    
                    if(profilePicFeedbackDiv) {
                        profilePicFeedbackDiv.textContent = 'Uploading...';
                        profilePicFeedbackDiv.className = 'form-text text-info';
                    }

                    console.log('Sending profile pic update to:', apiBasePath + 'upload.php');
                    fetch(apiBasePath + 'upload.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if(profilePicFeedbackDiv) {
                                profilePicFeedbackDiv.textContent = 'Profile picture updated successfully!';
                                profilePicFeedbackDiv.className = 'form-text text-success';
                            }

                            const newImageUrl = data.newProfilePicUrl + '?t=' + new Date().getTime(); // Add cache buster
                            console.log('New image URL:', newImageUrl);
                            
                            // Update all profile pictures
                            profilePicImages.forEach(img => {
                                let fixedPath = fixImagePath(img.src, newImageUrl);
                                console.log('Updating image from', img.src, 'to', fixedPath);
                                img.src = fixedPath;
                            });
                            
                            selectedProfilePic = null;
                            profilePicUploadInput.value = ''; // Clear the file input
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
            } else if (activeTabPane.id === 'accountInfoContent') {
                // Handle Account Information Update
                if (!accountInfoForm) {
                    console.error('Account info form not found');
                    return;
                }
                
                const formData = new FormData(accountInfoForm);
                if (accountInfoFeedbackDiv) {
                displayFeedback(accountInfoFeedbackDiv, 'Saving account details...', true);
                }

                console.log('Sending account update to:', apiBasePath + 'updateAccount.php');
                fetch(apiBasePath + 'updateAccount.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (accountInfoFeedbackDiv) {
                        displayFeedback(accountInfoFeedbackDiv, data.message, true);
                        }
                        
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
                        if (accountInfoFeedbackDiv) {
                        displayFeedback(accountInfoFeedbackDiv, data.message, false);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating account info:', error);
                    if (accountInfoFeedbackDiv) {
                    displayFeedback(accountInfoFeedbackDiv, 'An error occurred. Please try again.', false);
                    }
                });

            } else if (activeTabPane.id === 'passwordContent') {
                // Handle Password Change
                if (!passwordForm) {
                    console.error('Password form not found');
                    return;
                }
                
                const newPasswordInput = document.getElementById('newPassword');
                const confirmPasswordInput = document.getElementById('confirmPassword');
                const currentPasswordInput = document.getElementById('currentPassword');
                
                if (!newPasswordInput || !confirmPasswordInput || !currentPasswordInput) {
                    console.error('Password inputs not found');
                    return;
                }
                
                if (newPasswordInput.value !== confirmPasswordInput.value) {
                    if (passwordChangeFeedbackDiv) {
                    displayFeedback(passwordChangeFeedbackDiv, 'New passwords do not match.', false);
                    }
                    return;
                }
                
                if (newPasswordInput.value.length < 6) {
                    if (passwordChangeFeedbackDiv) {
                    displayFeedback(passwordChangeFeedbackDiv, 'New password must be at least 6 characters.', false);
                    }
                    return;
                }

                const formData = new FormData(passwordForm);
                if (passwordChangeFeedbackDiv) {
                displayFeedback(passwordChangeFeedbackDiv, 'Updating password...', true);
                }

                console.log('Sending password update to:', apiBasePath + 'updatePassword.php');
                fetch(apiBasePath + 'updatePassword.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (passwordChangeFeedbackDiv) {
                        displayFeedback(passwordChangeFeedbackDiv, data.message, true);
                        }
                        passwordForm.reset(); // Clear password fields
                    } else {
                        if (passwordChangeFeedbackDiv) {
                        displayFeedback(passwordChangeFeedbackDiv, data.message, false);
                        }
                        // Optionally, clear only new password fields if current password was wrong
                        if (data.message && data.message.toLowerCase().includes('incorrect')) {
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
                    if (passwordChangeFeedbackDiv) {
                    displayFeedback(passwordChangeFeedbackDiv, 'An error occurred. Please try again.', false);
                    }
                });
            }
        });
    } else {
        console.error('Save Settings button not found');
    }

    console.log('userProfile.js initialized with path handling for settings functionality');
});