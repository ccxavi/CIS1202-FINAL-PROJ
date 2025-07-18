<?php
session_start();
require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/../controllers/userAuthHandler.php';
require_once __DIR__ . '/../models/collections.php';

if (isAuthenticated()) {
    $userID = $_SESSION['userID'];
    $user = findUserByID($userID);
    
    // Update path handling for profile picture
    if (!empty($user['profile_pic'])) {
        $profilePic = str_replace('./assets/', '../assets/', $user['profile_pic']);
    } else {
        $profilePic = '../assets/photo/Profile_Pictures/default.jpg';
    }
    
    // Fetch user collections
    $collections = getCollectionsByUserID($userID);
    $collectionsCount = count($collections);
    $bookmarksCount = 0; // This will be updated via JavaScript
}

if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collection</title>
    <link rel="icon" type="image/png" href="../assets/img/logo.png">
    <link rel="stylesheet" href="../assets/css/userProfile.css">
    <link rel="stylesheet" href="../assets/css/settings.css">

    <link rel="stylesheet" href="../assets/css/userDropdown.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/collection.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>
    <style>
        /* Quick fix for project click issues */
        .project {
            cursor: pointer !important;
            pointer-events: auto !important;
        }
        .project * {
            pointer-events: auto;
        }
        .project img {
            pointer-events: none;
        }
        .section-title {
            z-index: 10;
        }
        
        /* Modal styles */
        .modal.show {
            display: block!important;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-backdrop {
            z-index: 1040!important;
        }
        .modal {
            z-index: 1050!important;
        }
    </style>
</head>
<body class="with-sidebar">
<aside>
    <div class="logo">
      <img src="../assets/img/logo.png" alt="logo">
      <h2>Vero</h2>
    </div>
    <div class="links">
      <a href="../index.php">
        <div class="icon"><i class="fa fa-home fa-2x" aria-hidden="true"></i></div>
        <div class="home">Home</div>
      </a>
      <a href="../views/searchResults.php">
        <div class="icon"><i class="fa fa-compass fa-2x" aria-hidden="true"></i></div>
        <div class="explore">Explore</div>
      </a>
      <a href='../views/guide.php'>
        <div class="icon"><i class="fa fa-address-book fa-2x" aria-hidden="true"></i></div>
        <div class='guide'>Guide</div>
      </a>
      <a href="../views/collection.php" class="active">
        <div class="icon"><i class="fa fa-folder fa-2x" aria-hidden="true"></i></div>
        <div class="collection">Collection</div>
      </a>
      <a href='../views/community.php'>
        <div class="icon"><i class="fa fa-users fa-2x" aria-hidden="true"></i></div>
        <div class='community'>Community</div>
      </a>
    </div>
</aside>
  
<div class="main-container">
  <header>
      <div class="collection-options">
        <div class="option" id="deleteBookmark">Delete bookmark</div>
        <div class="option" id="addCollection">Add Collection</div>
        <div class="option" id="deleteProject">Delete Project</div>
      </div>
      <div class="auth" id="auth">
        <?php
            if (isAuthenticated()) {
                echo '
                <div class="user-dropdown-container">
                    <div class="user-dropdown-toggle">
                        <img src="' . htmlspecialchars($profilePic) . '" alt="Profile">
                        <div class="user-info">
                            <div class="username">' . htmlspecialchars($user['username']) . '</div>
                            <div class="user-role">' . htmlspecialchars($user['email']) . '</div>
                        </div>
                        <i class="bi bi-chevron-down"></i>
                    </div>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-section text-center">
                            <img src="' . htmlspecialchars($profilePic) . '" alt="Profile Picture" class="settings-profile-pic" style="width: 64px; height: 64px; margin-bottom: 8px;">
                            <div><strong>' . htmlspecialchars($user['username']) . '</strong></div>
                            <div class="user-email">' . htmlspecialchars($user['email']) . '</div>
                        </li>
                        <li class="dropdown-item-wrapper">
                            <a href="#" class="dropdown-item full-width"><i class="bi bi-person-fill"></i> View Profile</a>
                        </li>
                        <li class="dropdown-item-wrapper">
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#settingsModal"><i class="bi bi-gear-fill"></i> Settings</a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" class="dropdown-item-form">
                                <button type="submit" name="logout" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Log Out</button>
                            </form>
                        </li>
                    </ul>
                </div>';
            } else {
                echo '<a href="../views/loginRegister.php">Login/Register</a>';
            }
        ?>
      </div>
      <!-- Settings Modal -->
      <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
              <div class="modal-content">
                  <div class="modal-header">
                      <h5 class="modal-title" id="settingsModalLabel">Account Settings</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body p-0">
                      <div class="row g-0">
                          <!-- Sidebar Navigation -->
                          <div class="col-md-4 border-end">
                              <div class="p-4">
                                  <ul class="nav nav-pills flex-column settings-nav" id="settingsTab" role="tablist">
                                      <li class="nav-item mb-2" role="presentation">
                                          <button class="nav-link active w-100 text-start" id="profile-pic-tab" data-bs-toggle="pill" data-bs-target="#profilePicContent" type="button" role="tab" aria-controls="profilePicContent" aria-selected="true">
                                              <i class="bi bi-person-circle me-2"></i>Profile Picture
                                          </button>
                                      </li>
                                      <li class="nav-item mb-2" role="presentation">
                                          <button class="nav-link w-100 text-start" id="account-info-tab" data-bs-toggle="pill" data-bs-target="#accountInfoContent" type="button" role="tab" aria-controls="accountInfoContent" aria-selected="false">
                                              <i class="bi bi-person-vcard me-2"></i>Account Details
                                          </button>
                                      </li>
                                      <li class="nav-item mb-2" role="presentation">
                                          <button class="nav-link w-100 text-start" id="password-tab" data-bs-toggle="pill" data-bs-target="#passwordContent" type="button" role="tab" aria-controls="passwordContent" aria-selected="false">
                                              <i class="bi bi-shield-lock me-2"></i>Change Password
                                          </button>
                                      </li>
                                      <li class="nav-item mb-2" role="presentation">
                                          <button class="nav-link w-100 text-start" id="auth-tab" data-bs-toggle="pill" data-bs-target="#authContent" type="button" role="tab" aria-controls="authContent" aria-selected="false">
                                              <i class="bi bi-patch-check me-2"></i>Profile Authentication
                                          </button>
                                      </li>
                                  </ul>
                              </div>
                          </div>
                          <!-- Tab Content Area -->
                          <div class="col-md-8">
                              <div class="tab-content p-4" id="settingsTabContent">
                                  <!-- Profile Picture Tab Content -->
                                  <div class="tab-pane fade show active" id="profilePicContent" role="tabpanel" aria-labelledby="profile-pic-tab">
                                      <div class="settings-section">
                                          <h6 class="settings-title mb-4">Profile Picture</h6>
                                          <div class="profile-pic-container text-center mb-4">
                                              <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" class="settings-profile-pic img-thumbnail rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                                          </div>
                                          <form id="profilePicForm" enctype="multipart/form-data">
                                              <div class="mb-3">
                                                  <label for="profilePicUpload" class="form-label">Choose new image:</label>
                                                  <input class="form-control" type="file" id="profilePicUpload" name="profilePic" accept="image/jpeg,image/png,image/gif">
                                              </div>
                                              <div id="profilePicFeedback" class="form-text" style="min-height: 20px;"></div>
                                          </form>
                                      </div>
                                  </div>

                                  <!-- Account Information Tab Content -->
                                  <div class="tab-pane fade" id="accountInfoContent" role="tabpanel" aria-labelledby="account-info-tab">
                                      <div class="settings-section">
                                          <h6 class="settings-title mb-4">Account Information</h6>
                                          <form id="accountInfoForm">
                                              <div class="mb-4">
                                                  <label for="usernameModal" class="form-label">Username</label>
                                                  <input type="text" class="form-control" id="usernameModal" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>">
                                              </div>
                                              <div class="mb-4">
                                                  <label for="emailModal" class="form-label">Email</label>
                                                  <input type="email" class="form-control" id="emailModal" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                                              </div>
                                              <div id="accountInfoFeedback" class="form-text" style="min-height: 20px;"></div>
                                          </form>
                                      </div>
                                  </div>

                                  <!-- Change Password Tab Content -->
                                  <div class="tab-pane fade" id="passwordContent" role="tabpanel" aria-labelledby="password-tab">
                                      <div class="settings-section">
                                          <h6 class="settings-title mb-4">Change Password</h6>
                                          <div id="passwordChangeFeedback" class="form-text" style="min-height: 20px;"></div>
                                          <form id="passwordForm">
                                              <div class="mb-4">
                                                  <label for="currentPassword" class="form-label">Current Password</label>
                                                  <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                                              </div>
                                              <div class="mb-4">
                                                  <label for="newPassword" class="form-label">New Password</label>
                                                  <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                                              </div>
                                              <div class="mb-4">
                                                  <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                                  <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                                                  <div class="form-text text-danger" id="passwordMatchError"></div>
                                              </div>
                                          </form>
                                      </div>
                                  </div>

                                  <!-- Profile Authentication Tab Content -->
                                  <div class="tab-pane fade" id="authContent" role="tabpanel" aria-labelledby="auth-tab">
                                      <div class="settings-section">
                                          <h6 class="settings-title mb-4">Profile Authentication</h6>
                                          <p class="text-muted small mb-4">Upload your ID to verify your account.</p>
                                          
                                          <form id="authForm" enctype="multipart/form-data">
                                              <div class="mb-4">
                                                  <label class="form-label">Verification ID</label>
                                                  <input type="file" class="form-control" id="verificationId" name="verificationId" accept="image/jpeg,image/png,image/gif">
                                                  <div class="form-text">Upload a clear photo of your ID</div>
                                              </div>

                                              <div class="verification-status mb-4 p-3 bg-light rounded">
                                                  <?php 
                                                  $verificationStatus = $user['verification_status'] ?? 'unverified';
                                                  $statusClass = [
                                                      'unverified' => 'text-muted',
                                                      'pending' => 'text-warning',
                                                      'verified' => 'text-success',
                                                      'rejected' => 'text-danger'
                                                  ][$verificationStatus];
                                                  $statusIcon = [
                                                      'unverified' => 'bi-patch-question',
                                                      'pending' => 'bi-hourglass-split',
                                                      'verified' => 'bi-patch-check-fill',
                                                      'rejected' => 'bi-x-circle'
                                                  ][$verificationStatus];
                                                  ?>
                                                  <div class="d-flex align-items-center gap-2 <?php echo $statusClass; ?>">
                                                      <i class="bi <?php echo $statusIcon; ?>"></i>
                                                      <span class="text-capitalize">Status: <?php echo $verificationStatus; ?></span>
                                                  </div>
                                              </div>

                                              <div id="authFeedback" class="form-text" style="min-height: 20px;"></div>
                                          </form>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-primary w-100" id="saveSettingsBtn" data-active-form="">Save Changes</button>
                  </div>
              </div>
          </div>
      </div>
      
  </header>

  <section class="content">
    
    
    <!-- Projects Section -->
    <div class="section-title collection-title" id="section-title-project">
        <i>Projects</i>
        <span class="count">(<?php echo $collectionsCount; ?>)</span>
    </div>
   

    <div class="project-section collection-section">
      <?php foreach ($collections as $collection): ?>
        <div class="project" data-collection-id="<?php echo htmlspecialchars($collection['id']); ?>">
          <div class="collection-name" data-collection-id="<?php echo htmlspecialchars($collection['id']); ?>">
            <?php echo htmlspecialchars($collection['name']); ?>
          </div>
          <button class="rename-project-btn" data-collection-id="<?php echo htmlspecialchars($collection['id']); ?>">
            <i class="bi bi-pencil"></i>
          </button>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="section-title bookmark-title" id="bookmark-title-project">
        <i>Bookmarked</i>
        <span class="count" id="bookmarks-count">(0)</span>
    </div>
    <!-- Bookmark Section -->
    <div class="bookmark-section collection-section">
      
      
      <!-- Collection Name Header -->
      <!-- <div class="collection-header no-collection">
        <div class="collection-header-name">
          <h2 id="current-collection-name">No collection selected</h2>
          <button id="edit-collection-name" class="edit-name-btn">
            <i class="fa fa-pencil"></i>
          </button>
        </div>
      </div>
       -->
      <div class="bookmarks-container">
        <div class="no-selection-message">Select a collection to view bookmarks</div>
      </div>
    </div>
  </section>
</div>

<!-- Context Menu for Collections -->
<div id="collection-context-menu" class="context-menu" style="display: none;">
  <ul>
    <li id="rename-collection">
      <i class="fa fa-pencil"></i> Rename
    </li>
  </ul>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../assets/js/userProfile.js"></script>
<script src="../assets/js/changePassword.js"></script>
<script src="../assets/js/profileAuth.js"></script>
<script src="../assets/js/settingsModal.js"></script>
<script src="../assets/js/collection.js"></script>

<script>
// Add direct click event listeners to fix the selection issue
document.addEventListener('DOMContentLoaded', function() {
    console.log('Fixing project click issue');
    
    // Context menu variables
    let activeCollectionId = null;
    let activeCollectionName = null;
    const contextMenu = document.getElementById('collection-context-menu');
    
    // Handle right-click on collection names
    document.querySelectorAll('.collection-name').forEach(nameElement => {
        nameElement.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            
            // Store the collection info
            activeCollectionId = this.dataset.collectionId;
            activeCollectionName = this.textContent.trim();
            
            // Position and show context menu
            contextMenu.style.left = e.pageX + 'px';
            contextMenu.style.top = e.pageY + 'px';
            contextMenu.style.display = 'block';
        });
    });
    
    // Hide context menu when clicking elsewhere
    document.addEventListener('click', function() {
        contextMenu.style.display = 'none';
    });
    
    // Handle rename option click
    document.getElementById('rename-collection').addEventListener('click', function() {
        if (activeCollectionId) {
            const newName = prompt('Enter a new name for this collection:', activeCollectionName);
            
            if (newName !== null && newName.trim() !== '') {
                renameCollection(activeCollectionId, newName.trim());
            }
        }
    });
    
    // Function to rename collection
    function renameCollection(collectionId, newName) {
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
                
                // Update the collection name in the header if it's the currently selected collection
                const currentCollectionHeader = document.getElementById('current-collection-name');
                if (currentCollectionHeader && currentCollectionHeader.dataset.collectionId === collectionId) {
                    currentCollectionHeader.textContent = newName;
                }
                
                alert('Collection renamed successfully');
            } else {
                alert('Failed to rename collection: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
    
    // Handle edit collection name button
    const editNameBtn = document.getElementById('edit-collection-name');
    if (editNameBtn) {
        editNameBtn.addEventListener('click', function() {
            const currentNameElement = document.getElementById('current-collection-name');
            const collectionId = currentNameElement.dataset.collectionId;
            
            if (!collectionId) {
                alert('Please select a collection first');
                return;
            }
            
            const currentName = currentNameElement.textContent;
            const newName = prompt('Enter a new name for this collection:', currentName);
            
            if (newName !== null && newName.trim() !== '') {
                renameCollection(collectionId, newName.trim());
            }
        });
    }
    
    document.querySelectorAll('.project').forEach(project => {
        project.onclick = function(e) {
            if (e.target.closest('.collection-name')) return;
            
            // Reset selected bookmarks
            window.selectedBookmarks = [];
            if (window.updateDeleteBookmarkState) {
                window.updateDeleteBookmarkState();
            }
            
            // Remove selection from others
            document.querySelectorAll('.project.selected').forEach(p => {
                if (p !== this) p.classList.remove('selected');
            });
            
            // Toggle selection
            this.classList.toggle('selected');
            
            // Update delete project button
            const deleteProjectBtn = document.getElementById('deleteProject');
            if (document.querySelectorAll('.project.selected').length > 0) {
                deleteProjectBtn.classList.add('active');
            } else {
                deleteProjectBtn.classList.remove('active');
            }
            
            // Load bookmarks if selected, clear if deselected
            const collectionId = this.dataset.collectionId;
            if (this.classList.contains('selected')) {
                console.log('Loading bookmarks for', collectionId);
                
                // Update the collection name in the header
                const collectionNameElement = this.querySelector('.collection-name');
                if (collectionNameElement) {
                    const currentCollectionHeader = document.getElementById('current-collection-name');
                    const collectionHeader = document.querySelector('.collection-header');
                    
                    if (currentCollectionHeader) {
                        currentCollectionHeader.textContent = collectionNameElement.textContent;
                        currentCollectionHeader.dataset.collectionId = collectionId;
                    }
                    
                    if (collectionHeader) {
                        collectionHeader.classList.remove('no-collection');
                    }
                }
                
                if (window.loadBookmarks) {
                    window.loadBookmarks(collectionId);
                } else {
                    loadBookmarksDirectly(collectionId);
                }
            } else {
                console.log('Clearing bookmarks');
                
                // Reset the collection name in the header
                const currentCollectionHeader = document.getElementById('current-collection-name');
                const collectionHeader = document.querySelector('.collection-header');
                
                if (currentCollectionHeader) {
                    currentCollectionHeader.textContent = 'No collection selected';
                    delete currentCollectionHeader.dataset.collectionId;
                }
                
                if (collectionHeader) {
                    collectionHeader.classList.add('no-collection');
                }
                
                if (window.clearBookmarks) {
                    window.clearBookmarks();
                } else {
                    clearBookmarksDirectly();
                }
            }
        };
    });
    
    // Additional script to ensure settings modal works properly
    const settingsButton = document.querySelector('[data-bs-target="#settingsModal"]');
    if (settingsButton) {
      settingsButton.addEventListener('click', function(e) {
        e.preventDefault();
        const modal = document.getElementById('settingsModal');
        if (modal && typeof bootstrap !== 'undefined') {
          const bsModal = new bootstrap.Modal(modal);
          bsModal.show();
        }
        return false;
      });
    }
    
    // Direct handler for close buttons
    const closeButtons = document.querySelectorAll('[data-bs-dismiss="modal"]');
    closeButtons.forEach(button => {
      button.addEventListener('click', function() {
        const modal = document.getElementById('settingsModal');
        if (modal && typeof bootstrap !== 'undefined') {
          const bsModal = bootstrap.Modal.getInstance(modal);
          if (bsModal) bsModal.hide();
        }
      });
    });
    
    // Backup functions in case collection.js doesn't load properly
    function loadBookmarksDirectly(collectionId) {
        const bookmarksContainer = document.querySelector('.bookmarks-container');
        bookmarksContainer.innerHTML = '<div class="loading">Loading bookmarks...</div>';
        
        fetch(`../controllers/getCollectionBookmarks.php?collection_id=${collectionId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.bookmarks && data.bookmarks.length > 0) {
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
                            </div>
                        `;
                    });
                    html += '</div>';
                    bookmarksContainer.innerHTML = html;
                    
                    // Update bookmark count
                    document.getElementById('bookmarks-count').textContent = `(${data.bookmarks.length})`;
                } else {
                    bookmarksContainer.innerHTML = '<div class="no-bookmarks">No bookmarks in this collection</div>';
                    document.getElementById('bookmarks-count').textContent = '(0)';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                bookmarksContainer.innerHTML = '<div class="error">Failed to load bookmarks</div>';
                document.getElementById('bookmarks-count').textContent = '(0)';
            });
    }
    
    function clearBookmarksDirectly() {
        const bookmarksContainer = document.querySelector('.bookmarks-container');
        bookmarksContainer.innerHTML = '<div class="no-selection-message">Select a collection to view bookmarks</div>';
        document.getElementById('bookmarks-count').textContent = '(0)';
    }
});

// Expose functions to window object
window.updateDeleteButtonState = updateDeleteButtonState;
window.updateDeleteBookmarkState = updateDeleteBookmarkState;
window.loadBookmarks = loadBookmarks;
window.clearBookmarks = clearBookmarks;
</script>
</body>
</html>
