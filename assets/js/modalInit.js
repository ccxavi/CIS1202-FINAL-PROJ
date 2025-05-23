// Modal initialization helper
document.addEventListener('DOMContentLoaded', function() {
    console.log('Modal initialization script loaded');
    
    // Fix profile image paths if they start with a dot
    document.querySelectorAll('img[alt="Profile"], img[alt="Profile Picture"]').forEach(img => {
        if (img.src.includes('./assets/')) {
            img.src = img.src.replace('./assets/', '../assets/');
        } else if (img.src.includes('.../assets/')) {
            img.src = img.src.replace('.../assets/', '../assets/');
        }
    });
    
    // Direct click handler for settings button - most reliable approach
    document.querySelectorAll('[data-bs-target="#settingsModal"]').forEach(button => {
        console.log('Found settings button, adding direct click handler');
        button.onclick = function(e) {
            e.preventDefault();
            showSettingsModal();
            return false;
        };
    });
    
    // Global handlers for closing modals
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('settingsModal');
        if (modal && modal.classList.contains('show') && e.target === modal) {
            hideSettingsModal();
        }
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('settingsModal');
            if (modal && modal.classList.contains('show')) {
                hideSettingsModal();
            }
        }
    });
    
    // Handle close buttons directly
    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal && modal.id === 'settingsModal') {
                hideSettingsModal();
            }
        });
    });
    
    // Initialize all modals
    const allModals = document.querySelectorAll('.modal');
    console.log('Found', allModals.length, 'modals on the page');
    
    if (allModals.length > 0) {
        allModals.forEach(modalElement => {
            try {
                const modal = new bootstrap.Modal(modalElement);
                console.log('Modal initialized:', modalElement.id);
                
                // Store the modal instance on the element for later access
                modalElement._bsModal = modal;
                
                // Setup close button handlers
                const closeButtons = modalElement.querySelectorAll('[data-bs-dismiss="modal"]');
                closeButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        hideSettingsModal();
                    });
                });
                
                // Setup backdrop click to close
                modalElement.addEventListener('click', function(e) {
                    if (e.target === modalElement) {
                        hideSettingsModal();
                    }
                });
                
                // Setup buttons that open the modal manually
                const modalId = modalElement.id;
                if (modalId) {
                    const triggers = document.querySelectorAll(`[data-bs-target="#${modalId}"]`);
                    console.log(`Found ${triggers.length} triggers for modal #${modalId}`);
                    
                    triggers.forEach(trigger => {
                        trigger.addEventListener('click', function(e) {
                            e.preventDefault();
                            console.log(`Showing modal #${modalId}`);
                            showSettingsModal();
                        });
                    });
                }
            } catch (e) {
                console.error('Failed to initialize modal:', modalElement.id, e);
            }
        });
    }
    
    // Special handler for settings modal 
    const settingsLinks = document.querySelectorAll('a[data-bs-target="#settingsModal"]');
    const settingsModal = document.getElementById('settingsModal');
    
    if (settingsLinks.length > 0 && settingsModal) {
        console.log('Found settings modal links:', settingsLinks.length);
        
        try {
            settingsLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Settings link clicked, showing modal');
                    showSettingsModal();
                });
            });
            
            // Setup Save Changes button in Settings Modal to close modal after click
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
                        hideSettingsModal();
                    }, 300);
                });
            }
            
            // Add keyboard event for ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && settingsModal.classList.contains('show')) {
                    hideSettingsModal();
                }
            });
        } catch (e) {
            console.error('Error initializing settings modal:', e);
        }
    } else if (!settingsModal) {
        console.error('Settings modal element not found in the DOM');
    } else {
        console.warn('No settings modal links found');
    }
});

// Function to show settings modal
function showSettingsModal() {
    const modal = document.getElementById('settingsModal');
    if (modal) {
        // Fix profile image paths in the modal
        modal.querySelectorAll('img').forEach(img => {
            if (img.src.includes('./assets/')) {
                img.src = img.src.replace('./assets/', '../assets/');
            } else if (img.src.includes('.../assets/')) {
                img.src = img.src.replace('.../assets/', '../assets/');
            }
        });
        
        if (typeof bootstrap !== 'undefined' && modal._bsModal) {
            // Use Bootstrap modal if available
            modal._bsModal.show();
        } else {
            // Manual implementation if Bootstrap JS is not loaded
            modal.classList.add('show');
            modal.style.display = 'block';
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('role', 'dialog');
            document.body.classList.add('modal-open');
            
            // Add backdrop if needed
            let backdrop = document.querySelector('.modal-backdrop');
            if (!backdrop) {
                backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
            }
        }
    }
}

// Function to hide settings modal
function hideSettingsModal() {
    const modal = document.getElementById('settingsModal');
    if (modal) {
        if (typeof bootstrap !== 'undefined' && modal._bsModal) {
            // Use Bootstrap modal if available
            modal._bsModal.hide();
        } else {
            // Manual implementation if Bootstrap JS is not loaded
            modal.classList.remove('show');
            modal.style.display = 'none';
            modal.removeAttribute('aria-modal');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
            
            // Remove backdrop
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }
    }
} 