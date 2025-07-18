<?php
session_start();

require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/../controllers/userAuthHandler.php';

if(isAuthenticated()){
    $userID = $_SESSION['userID'];
    $user = findUserByID($userID);
    
    // Update path handling for profile picture
    if (!empty($user['profile_pic'])) {
        $profilePic = str_replace('./assets/', '../assets/', $user['profile_pic']);
    } else {
        $profilePic = '../assets/photo/Profile_Pictures/default.jpg';
    }
}

if (isset($_POST['logout'])) {
    handleSignOut();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vero | Explore</title>
    <link rel="icon" type="image/png" href="../assets/img/logo.png">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/explore-auth.css">

    <link rel="stylesheet" href="../assets/css/userProfile.css">
    <link rel="stylesheet" href="../assets/css/settings.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" crossorigin="anonymous">
</head>
<body>

<style>
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
    <header>
        <div class="navbar">
            <div class="logo">
                <img src="../assets/img/logo.png" alt="logo">
                <h2>Vero</h2>
            </div>
            <div class="links">
                <a href="../index.php"><div class="home">Home</div></a>
                <a href="./explore.php"><div class="explore">Explore</div></a>
                <a href="./guide.php"><div class="guide">Guide</div></a>
                <?php
                    if (isAuthenticated()){
                        echo "
                            <a href='./collection.php'><div class='collection'>Collection</div></a>
                            <a href='./community.php'><div class='community'>Community</div></a>
                            ";
                    } 
                ?>
            </div>
        </div>
        <!-- Auth Section -->
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

                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#settingsModal" onclick="showSettingsModal()"><i class="bi bi-gear-fill"></i> Settings</a>
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
                echo '<a href="./loginRegister.php">Login/Register</a>';
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
                                                    <input type="text" class="form-control" id="usernameModal" name="username" value="' . htmlspecialchars($user['username'] ?? '') . '">
                                                </div>
                                                <div class="mb-4">
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
                                                    $statusClass = ['unverified' => 'text-muted', 'pending' => 'text-warning', 'verified' => 'text-success', 'rejected' => 'text-danger'][$verificationStatus];
                                                    $statusIcon = ['unverified' => 'bi-patch-question', 'pending' => 'bi-hourglass-split', 'verified' => 'bi-patch-check-fill', 'rejected' => 'bi-x-circle'][$verificationStatus];
                                                    ?>
                                                    <div class="d-flex align-items-center gap-2 ' . $statusClass . '">
                                                        <i class="bi ' . $statusIcon . '"></i>
                                                        <span class="text-capitalize">Status: ' . $verificationStatus . '</span>
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
    <main>
        <div class="container">
            <div class="header-section">
                <h1 class="header-title">Research Without the Doubt.</h1>
                <div class="search-bar">
                <form action="../controllers/search.php" method="POST">
                    <input type="text" name="query" placeholder="What are you researching today?" required>
                    <button type="submit"><i class="bi bi-search"></i></button>
                </form>
                </div>
            </div>

            <div class="trending-section">
                <h5 class="trending-heading">Trending Topics, All Verified.</h5>
                <div class="trending-layout">
                    <div class="trending-item-container">
                        <div class="trending-item">
                        <div class="title">The Impact of Climate Change on Marine Biodiversity</div>
                    </div>
                    <div class="trending-item">
                        <div class="title">Artificial Intelligence in Modern Medical Diagnostics</div>
                    </div>
                    <div class="trending-item">
                        <div class="title">The Role of Nanotechnology in Cancer Treatment</div>
                    </div>
                    <div class="trending-item">
                        <div class="title">Renewable Energy Solutions for Developing Nations</div>
                    </div>
                    <div class="trending-item">
                        <div class="title">Exploring Genetic Editing Using CRISPR Technology</div>
                    </div>
                    <div class="trending-item">
                        <div class="title">The Effects of Social Media on Adolescent Psychology</div>
                    </div>
                    <div class="trending-item">
                        <div class="title">Quantum Computing: The Next Frontier in Data Security</div>
                    </div>
                    <div class="trending-item">
                        <div class="title">Urban Farming as a Sustainable Food Source</div>
                    </div>
                    <div class="trending-item">
                        <div class="title">Language Models and Their Role in Education Reform</div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <footer>
        <div class="footer-content">
            <p>© 2025 <strong><em>Vero.</em></strong> All rights reserved.</p>
            <div class="socials">
                <a href="#"><div class="facebook"><i class="bi bi-facebook"></i></div></a>
                <a href="#"><div class="github"><i class="bi bi-github"></i></div></a>
                <a href="#"><div class="x"><i class="bi bi-twitter-x"></i></div></a>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../assets/js/userProfile.js"></script>
    <script src="../assets/js/changePassword.js"></script>
    <script src="../assets/js/profileAuth.js"></script>
    <script src="../assets/js/settingsModal.js"></script>
</body>
</html> 