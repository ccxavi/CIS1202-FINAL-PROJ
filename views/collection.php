<?php
session_start();
require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/../controllers/userAuthHandler.php';
require_once __DIR__ . '/../models/collections.php';

if (isAuthenticated()) {
    $userID = $_SESSION['userID'];
    $user = findUserByID($userID);
    $profilePic = $user['profile_pic'] ?? './assets/photo/Profile_Pictures/default.jpg'; // Updated path

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
                        <img src=".' . htmlspecialchars($profilePic) . '" alt="Profile">
                        <div class="user-info">
                            <div class="username">' . htmlspecialchars($user['username']) . '</div>
                            <div class="user-role">' . htmlspecialchars($user['email']) . '</div>
                        </div>
                        <i class="bi bi-chevron-down"></i>
                    </div>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-section text-center">
                            <img src=".' . htmlspecialchars($profilePic) . '" alt="Profile Picture" class="settings-profile-pic" style="width: 64px; height: 64px; margin-bottom: 8px;">
                            <div><strong>' . htmlspecialchars($user['username']) . '</strong></div>
                            <div class="user-email">' . htmlspecialchars($user['email']) . '</div>
                        </li>
                        <li class="dropdown-item-wrapper">
                            <a href="profile.php" class="dropdown-item full-width"><i class="bi bi-person-fill"></i> View Profile</a>
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
          <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                  <div class="modal-header">
                      <h5 class="modal-title" id="settingsModalLabel">Account Settings</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="document.getElementById('settingsModal').classList.remove('show'); document.querySelector('.modal-backdrop').remove();"></button>
                  </div>
                  <div class="modal-body">
                      <div class="row">
                          <!-- Sidebar Navigation -->
                          <div class="col-md-7 col-lg-5">
                              <ul class="nav nav-pills flex-column" id="settingsTab" role="tablist">
                                  <li class="nav-item" role="presentation">
                                      <button class="nav-link active w-100 text-start" id="profile-pic-tab" data-bs-toggle="pill" data-bs-target="#profilePicContent" type="button" role="tab" aria-controls="profilePicContent" aria-selected="true"><i class="bi bi-person-circle me-2"></i>Profile Picture</button>
                                  </li>
                                  <li class="nav-item" role="presentation">
                                      <button class="nav-link w-100 text-start" id="account-info-tab" data-bs-toggle="pill" data-bs-target="#accountInfoContent" type="button" role="tab" aria-controls="accountInfoContent" aria-selected="false"><i class="bi bi-person-vcard me-2"></i>Account Details</button>
                                  </li>
                                  <li class="nav-item" role="presentation">
                                      <button class="nav-link w-100 text-start" id="password-tab" data-bs-toggle="pill" data-bs-target="#passwordContent" type="button" role="tab" aria-controls="passwordContent" aria-selected="false"><i class="bi bi-shield-lock me-2"></i>Change Password</button>
                                  </li>
                              </ul>
                          </div>
                          <!-- Tab Content Area -->
                          <div class="col-md-5 col-lg-7">
                              <div class="tab-content" id="settingsTabContent">
                                  <!-- Profile Picture Tab Content -->
                                  <div class="tab-pane fade show active" id="profilePicContent" role="tabpanel" aria-labelledby="profile-pic-tab">
                                      <div class="settings-section">
                                          <div class="profile-pic-container text-center mb-3">
                                              <img src=".' . htmlspecialchars($profilePic) . '" alt="Profile Picture" class="settings-profile-pic img-thumbnail rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                          </div>
                                          <form id="profilePicForm" enctype="multipart/form-data">
                                              <div class="mb-3">
                                                  <label for="profilePicUpload" class="form-label visually-hidden">Choose new image:</label>
                                                  <input class="form-control form-control-sm" type="file" id="profilePicUpload" name="profilePic" accept="image/jpeg,image/png,image/gif">
                                              </div>
                                              <div id="profilePicFeedback" class="form-text" style="min-height: 20px;"></div>
                                          </form>
                                      </div>
                                  </div>

                                  <!-- Account Information Tab Content -->
                                  <div class="tab-pane fade" id="accountInfoContent" role="tabpanel" aria-labelledby="account-info-tab">
                                      <div class="settings-section">
                                          <h6 class="settings-title">Account Information</h6>
                                          <form id="accountInfoForm">
                                              <div class="mb-3">
                                                  <label for="usernameModal" class="form-label">Username</label>
                                                  <input type="text" class="form-control" id="usernameModal" name="username" value="' . htmlspecialchars($user['username'] ?? '') . '">
                                              </div>
                                              <div class="mb-3">
                                                  <label for="emailModal" class="form-label">Email</label>
                                                  <input type="email" class="form-control" id="emailModal" name="email" value="' . htmlspecialchars($user['email'] ?? '') . '">
                                              </div>
                                              <div id="accountInfoFeedback" class="form-text" style="min-height: 20px;"></div>
                                          </form>
                                      </div>
                                  </div>

                                  <!-- Change Password Tab Content -->
                                  <div class="tab-pane fade" id="passwordContent" role="tabpanel" aria-labelledby="password-tab">
                                      <div class="settings-section">
                                          <h6 class="settings-title">Change Password</h6>
                                          <div id="passwordChangeFeedback" class="form-text" style="min-height: 20px;"></div>
                                          <form id="passwordForm">
                                              <div class="mb-3">
                                                  <label for="currentPassword" class="form-label">Current Password</label>
                                                  <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                                              </div>
                                              <div class="mb-3">
                                                  <label for="newPassword" class="form-label">New Password</label>
                                                  <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                                              </div>
                                              <div class="mb-3">
                                                  <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                                  <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                                                  <div class="form-text text-danger" id="passwordMatchError"></div>
                                              </div>
                                          </form>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="document.getElementById('settingsModal').classList.remove('show'); document.querySelector('.modal-backdrop').remove();">Close</button>
                      <button type="button" class="btn btn-primary" id="saveSettingsBtn" onclick="setTimeout(function() { document.getElementById('settingsModal').classList.remove('show'); document.querySelector('.modal-backdrop').remove(); }, 300);">Save Changes</button>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous"></script>
<script src="../assets/js/userProfile.js"></script>
<script src="../assets/js/changePassword.js"></script>
<script src="../assets/js/changeUserInfo.js"></script>
<script src="../assets/js/modalInit.js"></script>

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