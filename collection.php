<?php
session_start();
require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/../controllers/userAuthHandler.php';
require_once __DIR__ . '/../models/collections.php';

if (isAuthenticated()) {
    $userID = $_SESSION['userID'];
    $user = findUserByID($userID);
    $profilePic = $user['profile_pic'] ?? './assets/photo/Profile_Pictures/default.jpg'; // fallback if null

    // Fetch user collections
    $collections = getCollectionsByUserID($userID);
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
    <title>Community</title>
    <link rel="stylesheet" href="../assets/css/userDropdown.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/collection.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>
</head>
<body>
<aside>
    <div class="logo">
      <img src="../assets/img/logo.png" alt="logo">
      <h2>Vero</h2>
    </div>
    <div class="links">
      <a href="#">
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
      <a href="../views/collection.php">
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
        <form action="../controllers/createCollection.php" method="POST">

          <div class="option">Delete bookmark</div>
          
          <label for="collectionName" class="form-label">Collection Name</label>
          <input type="text" class="form-control" id="collectionName" name="collectionName" required>
          <button type="submit" class="btn btn-primary">Add Collection</button>


          <div class="option">Delete Project</div>
        </form>

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
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="settingsModalLabel">Account Settings</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Profile Picture Section -->
                        <div class="settings-section">
                            <h6 class="settings-title">Profile Picture</h6>
                            <div class="profile-pic-container">
                                <img id="profilePicPreview" src=".<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" class="settings-profile-pic">
                                <form id="profilePicForm" action="../controllers/changeUserInfo.php" method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="profilePicUpload" class="form-label">Choose new image:</label>
                                        <input class="form-control" type="file" id="profilePicUpload" name="profilePic" accept="image/*">
                                    </div>
                                </form>
                            </div>
                        </div>

                        <hr>
                        <!-- Account Information Section -->
                        <div class="settings-section">
                            <h6 class="settings-title">Account Information</h6>
                            <form id="accountInfoForm" action="../controllers/changeUserInfo.php" method="POST">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                                </div>
                            </form>
                        </div>
                        <hr>
                        <!-- Change Password Section -->
                        <div class="settings-section">
                            <h6 class="settings-title">Change Password</h6>
                            <div id="passwordFeedback" class="alert alert-danger d-none mb-3"></div>
                            <form id="passwordForm" action="../controllers/changeUserInfo.php" method="POST">
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Save</button>
                    </div>
                </div>
            </div>
      </div>
  </header>
  
  <section class="content">
        <div class="bookmark-section collection-section">
            <div class="section-title"><i>Bookmarked</i></div>
        </div>

        <div class="section-title" id="section-title-project"><i>My Collections</i></div>

        <div class="project-section collection-section">
            
        </div>

        <!-- Add Collection Form -->
        <!-- <div class="add-collection-form">
            <form action="../controllers/createCollection.php" method="POST">
                <div class="mb-3">
                    <label for="collectionName" class="form-label">Collection Name</label>
                    <input type="text" class="form-control" id="collectionName" name="collectionName" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Collection</button>
            </form>
        </div> -->
    </section>

    <section class="content">
    <div class="bookmark-section collection-section">
      <div class="section-title"><i>Bookmarked</i></div>
    </div>
    <div class="section-title" id="section-title-project"><i>Projects</i></div>

    <div class="project-section collection-section">
      <div class="project"></div>
      <?php foreach ($collections as $collection): ?>
                <div class="project">
                    <div class="project-title"><?= htmlspecialchars($collection['name']) ?></div>
                    <div class="project-created-at"><?= htmlspecialchars($collection['created_at']) ?></div>
                </div>
            <?php endforeach; ?>
      
    </div>
  </section>
</div>

  <script src="../assets/js/userProfile.js"></script>
  <script src="../assets/js/changePassword.js"></script>
  <script src="../assets/js/changeUserInfo.js"></script>
</body>
</html>