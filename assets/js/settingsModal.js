document.addEventListener('DOMContentLoaded', function() {
    const settingsModal = document.getElementById('settingsModal');
    const saveButton = document.getElementById('saveSettingsBtn');
    const closeButton = document.querySelector('#settingsModal .btn-close');
    const tabButtons = document.querySelectorAll('#settingsTab button[data-bs-toggle="pill"]');
    
    // Track active form
    let activeFormId = 'profilePicForm'; // Default to profile pic form
    
    // Update active form when tabs are clicked
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetPaneId = this.getAttribute('data-bs-target').replace('#', '');
            const formElement = document.querySelector(`#${targetPaneId} form`);
            if (formElement) {
                activeFormId = formElement.id;
                saveButton.setAttribute('data-active-form', activeFormId);
                console.log('Active form updated to:', activeFormId); // Debug log
            }
        });
    });

    // Handle save button click
    saveButton.addEventListener('click', async function() {
        const activeFormId = this.getAttribute('data-active-form');
        const activeForm = document.getElementById(activeFormId);
        
        if (!activeForm) {
            console.error('No active form found with ID:', activeFormId);
            return;
        }

        console.log('Submitting form:', activeFormId); // Debug log

        // Create and dispatch the submit event
        const submitEvent = new Event('submit', {
            bubbles: true,
            cancelable: true
        });
        
        // Dispatch the event and handle the result
        const wasEventHandled = activeForm.dispatchEvent(submitEvent);
        console.log('Form submission event handled:', wasEventHandled); // Debug log
    });

    // Initialize Bootstrap modal
    if (typeof bootstrap !== 'undefined') {
        const modal = new bootstrap.Modal(settingsModal);
    }

    // Handle close button click
    closeButton.addEventListener('click', function() {
        const modalInstance = bootstrap.Modal.getInstance(settingsModal);
        if (modalInstance) {
            modalInstance.hide();
        }
    });

    // Handle modal closing events
    settingsModal.addEventListener('hide.bs.modal', function(event) {
        // Get the clicked element
        const clickedElement = event.target.querySelector('.btn-close');
        
        // If the close button was clicked, or if it's a backdrop click, allow the modal to close
        if (event.target === settingsModal || clickedElement) {
            return;
        }
        
        // Prevent closing only when escape key is pressed
        if (event.keyCode === 27) {
            event.preventDefault();
        }
    });

    // Set initial active form
    saveButton.setAttribute('data-active-form', activeFormId);
    console.log('Initial active form set to:', activeFormId); // Debug log
}); 