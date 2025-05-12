// Updated userProfile.js with new dropdown functionality

document.addEventListener('DOMContentLoaded', function() {
    // User dropdown toggle functionality - either use existing dropdown-toggle or new user-dropdown-toggle
    const userDropdownToggle = document.querySelector('.user-dropdown-toggle');
    if (userDropdownToggle) {
        userDropdownToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdownMenu = this.nextElementSibling;
            
            // Close all other dropdowns first
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                if (menu !== dropdownMenu) {
                    menu.classList.remove('show');
                }
            });
            
            // Toggle current dropdown
            dropdownMenu.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-dropdown-container')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
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
    
    // File input preview for profile picture
    const profilePicUpload = document.getElementById('profilePicUpload');
    const settingsProfilePic = document.querySelector('.settings-profile-pic');
    
    if (profilePicUpload && settingsProfilePic) {
        profilePicUpload.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    settingsProfilePic.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Password validation
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match!');
            }
        });
    }
    
    // Modal functionality for systems without Bootstrap JS
    const settingsModalTrigger = document.querySelector('[data-bs-toggle="modal"][data-bs-target="#settingsModal"]');
    const settingsModal = document.getElementById('settingsModal');
    const closeModalButtons = document.querySelectorAll('[data-bs-dismiss="modal"]');
    
    if (settingsModalTrigger && settingsModal) {
        // Custom modal open function
        settingsModalTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            try {
                // Try Bootstrap method first
                const modal = bootstrap.Modal.getInstance(settingsModal) || new bootstrap.Modal(settingsModal);
                modal.show();
            } catch (e) {
                // Fallback to custom implementation
                settingsModal.classList.add('show');
                settingsModal.style.display = 'block';
                document.body.classList.add('modal-open');
                
                // Add backdrop if it doesn't exist
                let backdrop = document.querySelector('.modal-backdrop');
                if (!backdrop) {
                    backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    document.body.appendChild(backdrop);
                }
            }
        });
        
        // Close modal buttons
        if (closeModalButtons.length > 0) {
            closeModalButtons.forEach(button => {
                button.addEventListener('click', function() {
                    try {
                        // Try Bootstrap method first
                        const modal = bootstrap.Modal.getInstance(settingsModal);
                        if (modal) {
                            modal.hide();
                        } else {
                            // Fallback to custom implementation
                            settingsModal.classList.remove('show');
                            settingsModal.style.display = 'none';
                            document.body.classList.remove('modal-open');
                            const backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) {
                                backdrop.remove();
                            }
                        }
                    } catch (e) {
                        // Fallback to custom implementation
                        settingsModal.classList.remove('show');
                        settingsModal.style.display = 'none';
                        document.body.classList.remove('modal-open');
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                    }
                });
            });
        }
    }
    
    // Console log check for debugging
    console.log('userProfile.js loaded with user dropdown functionality');
});