document.addEventListener('DOMContentLoaded', function() {
    // Log the DOM structure to debug
    console.log('DOM loaded in collection.js');
    console.log('Projects found in collection.js:', document.querySelectorAll('.project').length);
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Track selected bookmarks
    window.selectedBookmarks = window.selectedBookmarks || [];

    // Debug DOM structure - log all projects
    document.querySelectorAll('.project').forEach((p, index) => {
        console.log(`Project ${index} in collection.js:`, p.dataset.collectionId);
    });

    // Note: We won't add click handlers to projects here since they're handled in the inline script
    // But we'll expose our bookmark loading/selection functions globally

    // Add Collection button
    document.getElementById('addCollection').addEventListener('click', createNewCollection);

    // Delete Project button
    document.getElementById('deleteProject').addEventListener('click', deleteSelectedProjects);

    // Delete Bookmark button
    document.getElementById('deleteBookmark').addEventListener('click', deleteSelectedBookmarks);

    // Save new name button in modal
    document.getElementById('saveNewName').addEventListener('click', saveCollectionName);

    // Handle Enter key in modal
    document.getElementById('newCollectionName').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            saveCollectionName();
        }
    });

    // Handle modal feedback clearing
    const renameModal = document.getElementById('renameCollectionModal');
    renameModal.addEventListener('hidden.bs.modal', function () {
        document.getElementById('collectionNameFeedback').textContent = '';
        document.getElementById('collectionNameFeedback').className = 'form-text';
    });

    // Add handlers for bookmark selection
    document.addEventListener('click', function(e) {
        const bookmarkItem = e.target.closest('.bookmark-item');
        if (bookmarkItem && !e.target.closest('.bookmark-link') && !e.target.closest('.remove-bookmark')) {
            const bookmarkId = bookmarkItem.dataset.bookmarkId;
            
            // Toggle selection
            bookmarkItem.classList.toggle('selected');
            
            if (bookmarkItem.classList.contains('selected')) {
                // Add to selected bookmarks if not already there
                if (!window.selectedBookmarks.includes(bookmarkId)) {
                    window.selectedBookmarks.push(bookmarkId);
                }
            } else {
                // Remove from selected bookmarks
                const index = window.selectedBookmarks.indexOf(bookmarkId);
                if (index !== -1) {
                    window.selectedBookmarks.splice(index, 1);
                }
            }
            
            updateDeleteBookmarkState();
        }
    });
});

// Expose functions to window object
window.updateDeleteButtonState = updateDeleteButtonState;
window.updateDeleteBookmarkState = updateDeleteBookmarkState;
window.loadBookmarks = loadBookmarks;
window.clearBookmarks = clearBookmarks;

function updateDeleteButtonState() {
    const selectedProjects = document.querySelectorAll('.project.selected');
    const deleteButton = document.getElementById('deleteProject');
    
    if (selectedProjects.length > 0) {
        deleteButton.classList.add('active');
    } else {
        deleteButton.classList.remove('active');
    }
}

function updateDeleteBookmarkState() {
    const deleteButton = document.getElementById('deleteBookmark');
    
    if (window.selectedBookmarks && window.selectedBookmarks.length > 0) {
        deleteButton.classList.add('active');
    } else {
        deleteButton.classList.remove('active');
    }
}

function updateBookmarkCount(count) {
    document.getElementById('bookmarks-count').textContent = `(${count})`;
}

function loadBookmarks(collectionId) {
    console.log('loadBookmarks called from collection.js for ID:', collectionId);
    const bookmarksContainer = document.querySelector('.bookmarks-container');
    bookmarksContainer.innerHTML = '<div class="loading">Loading bookmarks...</div>';
    
    // Reset selected bookmarks
    window.selectedBookmarks = [];
    updateDeleteBookmarkState();

    fetch(`../controllers/getCollectionBookmarks.php?collection_id=${collectionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.bookmarks.length > 0) {
                let html = '<div class="bookmarks-list">';
                data.bookmarks.forEach(bookmark => {
                    html += `
                        <div class="bookmark-item" data-bookmark-id="${bookmark.bookmark_id}">
                            <div class="bookmark-content">
                                <h3 class="bookmark-title">${bookmark.title || 'Untitled'}</h3>
                                <a href="${bookmark.article_link}" target="_blank" class="bookmark-link">Read Article</a>
                                <div class="bookmark-meta">
                                    <span class="bookmark-author">Author: ${bookmark.author || 'Unknown'}</span>
                                    <span class="bookmark-date">Date: ${new Date(bookmark.published_date || Date.now()).toLocaleDateString()}</span>
                                </div>
                            </div>
                            <button class="remove-bookmark" data-bookmark-id="${bookmark.bookmark_id}">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </button>
                        </div>
                    `;
                });
                html += '</div>';
                bookmarksContainer.innerHTML = html;
                
                // Update bookmark count
                updateBookmarkCount(data.bookmarks.length);

                // Add event listeners to remove buttons
                document.querySelectorAll('.remove-bookmark').forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.stopPropagation();
                        removeBookmark(this.dataset.bookmarkId, collectionId);
                    });
                });
            } else {
                bookmarksContainer.innerHTML = '<div class="no-bookmarks">No bookmarks in this collection</div>';
                updateBookmarkCount(0);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            bookmarksContainer.innerHTML = '<div class="error">Failed to load bookmarks</div>';
            updateBookmarkCount(0);
        });
}

function removeBookmark(bookmarkId, collectionId) {
    if (!confirm('Are you sure you want to remove this bookmark?')) return;

    fetch('../controllers/removeBookmark.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `bookmark_id=${bookmarkId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload bookmarks to update the display
            loadBookmarks(collectionId);
        } else {
            alert('Failed to remove bookmark: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to remove bookmark. Please try again.');
    });
}

function deleteSelectedBookmarks() {
    if (!window.selectedBookmarks || window.selectedBookmarks.length === 0) return;
    
    if (!confirm(`Are you sure you want to delete ${window.selectedBookmarks.length} bookmark(s)?`)) return;
    
    const currentCollectionId = document.querySelector('.project.selected').dataset.collectionId;
    const promises = [];
    
    // Create a promise for each delete request
    window.selectedBookmarks.forEach(bookmarkId => {
        promises.push(
            fetch('../controllers/removeBookmark.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `bookmark_id=${bookmarkId}`
            }).then(response => response.json())
        );
    });
    
    // Wait for all delete operations to complete
    Promise.all(promises)
        .then(results => {
            // Check if all operations succeeded
            const allSuccess = results.every(data => data.success);
            
            if (allSuccess) {
                showSuccess('Bookmarks deleted successfully');
                // Reload bookmarks to update the display
                loadBookmarks(currentCollectionId);
            } else {
                showError('Some bookmarks could not be deleted');
                // Reload anyway to update the display
                loadBookmarks(currentCollectionId);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to delete bookmarks');
            loadBookmarks(currentCollectionId);
        });
}

function clearBookmarks() {
    const bookmarksContainer = document.querySelector('.bookmarks-container');
    bookmarksContainer.innerHTML = '<div class="no-selection-message">Select a collection to view bookmarks</div>';
    updateBookmarkCount(0);
    
    // Reset selected bookmarks
    window.selectedBookmarks = [];
    updateDeleteBookmarkState();
}

function createNewCollection() {
    fetch('../controllers/createCollection.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Create new project element
            const projectSection = document.querySelector('.project-section');
            const newProject = document.createElement('div');
            newProject.className = 'project';
            newProject.dataset.collectionId = data.collection.id;
            
            // Match the exact HTML structure of existing collections
            newProject.innerHTML = `
                <div class="non-clicked">
                    <img src="../assets/img/folder.png" alt="">
                    <div class="collection-name" data-collection-id="${data.collection.id}">
                        ${data.collection.name}
                    </div>
                </div>
                <div class="clicked">
                    <img src="../assets/img/folderClicked.png" alt="">
                    <div class="collection-name" data-collection-id="${data.collection.id}">
                        ${data.collection.name}
                    </div>
                </div>
            `;

            // Add event listeners to new project
            newProject.addEventListener('click', function(e) {
                // Don't handle project click if clicking collection name
                if (e.target.closest('.collection-name')) {
                    return;
                }
                
                // Reset selected bookmarks
                window.selectedBookmarks = [];
                updateDeleteBookmarkState();
                
                document.querySelectorAll('.project.selected').forEach(p => {
                    if (p !== this) p.classList.remove('selected');
                });
                
                this.classList.toggle('selected');
                updateDeleteButtonState();
                
                if (this.classList.contains('selected')) {
                    loadBookmarks(this.dataset.collectionId);
                } else {
                    clearBookmarks();
                }
            });

            // Add double-click handler for collection names
            const nonClickedName = newProject.querySelector('.non-clicked .collection-name');
            const clickedName = newProject.querySelector('.clicked .collection-name');
            
            function handleDoubleClick(e) {
                e.stopPropagation();
                e.preventDefault();
                
                const projectDiv = this.closest('.project');
                const isProjectSelected = projectDiv.classList.contains('selected');
                const isInClickedState = this.closest('.clicked') !== null;
                const isInNonClickedState = this.closest('.non-clicked') !== null;
                
                if ((isProjectSelected && isInClickedState) || (!isProjectSelected && isInNonClickedState)) {
                    const collectionId = this.dataset.collectionId;
                    const currentName = this.textContent.trim();
                    
                    document.getElementById('currentCollectionId').value = collectionId;
                    document.getElementById('newCollectionName').value = currentName;
                    
                    const renameModal = new bootstrap.Modal(document.getElementById('renameCollectionModal'));
                    renameModal.show();
                }
            }
            
            nonClickedName.addEventListener('dblclick', handleDoubleClick);
            clickedName.addEventListener('dblclick', handleDoubleClick);

            projectSection.appendChild(newProject);
            
            // Update project count
            const projectCount = document.querySelectorAll('.project').length;
            document.querySelector('#section-title-project .count').textContent = `(${projectCount})`;
            
            showSuccess('Collection created successfully');
        } else {
            showError('Failed to create new collection');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to create new collection');
    });
}

function deleteSelectedProjects() {
    const selectedProject = document.querySelector('.project.selected');
    if (!selectedProject) return;
    
    if (!confirm('Are you sure you want to delete this collection and all its bookmarks?')) return;
    
    const collectionId = selectedProject.dataset.collectionId;
    
    fetch('../controllers/deleteCollection.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `collection_id=${collectionId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove from DOM
            selectedProject.remove();
            clearBookmarks();
            
            // Update project count
            const projectCount = document.querySelectorAll('.project').length;
            document.querySelector('#section-title-project .count').textContent = `(${projectCount})`;
            
            // Update delete button state
            updateDeleteButtonState();
            
            showSuccess('Collection deleted successfully');
        } else {
            showError('Failed to delete collection: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to delete collection');
    });
}

function saveCollectionName() {
    const collectionId = document.getElementById('currentCollectionId').value;
    const newName = document.getElementById('newCollectionName').value.trim();
    const feedback = document.getElementById('collectionNameFeedback');
    
    if (!newName) {
        feedback.textContent = 'Collection name cannot be empty';
        feedback.className = 'form-text text-danger';
        return;
    }
    
    fetch('../controllers/updateCollection.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `collection_id=${collectionId}&new_name=${encodeURIComponent(newName)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update collection name in DOM
            document.querySelectorAll(`.collection-name[data-collection-id="${collectionId}"]`).forEach(el => {
                el.textContent = newName;
            });
            
            // Hide modal using Bootstrap
            bootstrap.Modal.getInstance(document.getElementById('renameCollectionModal')).hide();
            
            showSuccess('Collection renamed successfully');
        } else {
            feedback.textContent = 'Failed to rename collection: ' + data.message;
            feedback.className = 'form-text text-danger';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        feedback.textContent = 'An error occurred. Please try again.';
        feedback.className = 'form-text text-danger';
    });
}

function showError(message) {
    alert(message); // Simple error notification
}

function showSuccess(message) {
    alert(message); // Simple success notification
}

// Add this CSS to your stylesheet or add it dynamically
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInOut {
        0% { opacity: 0; transform: translateY(-20px); }
        10% { opacity: 1; transform: translateY(0); }
        90% { opacity: 1; transform: translateY(0); }
        100% { opacity: 0; transform: translateY(-20px); }
    }
`;
document.head.appendChild(style); 