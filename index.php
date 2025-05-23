<?php
session_start();

require_once __DIR__ . '/config/databaseConnection.php';
require_once __DIR__ . '/controllers/userAuthHandler.php';

if(isAuthenticated()){
    $userID = $_SESSION['userID'];
    $user = findUserByID($userID);
    
    if ($user === false) {
        // If user data can't be found, log them out
        session_unset();
        session_destroy();
        header('Location: ./views/loginRegister.php');
        exit();
    }
    
    $profilePic = $user['profile_pic'] ?? './assets/photo/Profile_Pictures/default.jpg';
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
    <title>Vero - Verified Research</title>
    <link rel="icon" type="image/png" href="./assets/img/logo.png">
    <link rel="stylesheet" href="./assets/css/global.css">
    <link rel="stylesheet" href="./assets/css/home.css">
    <link rel="stylesheet" href="./assets/css/userProfile.css">
    <link rel="stylesheet" href="./assets/css/header.css">
    <link rel="stylesheet" href="./assets/css/main.css">
    <link rel="stylesheet" href="./assets/css/footer.css">
    <link rel="stylesheet" href="./assets/css/projects.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" crossorigin="anonymous">
</head>
<body>
    <header>
        <div class="navbar">
            <div class="logo">
                <img src="./assets/img/logo.png" alt="logo">
                <h2>Vero</h2>
            </div>
            <div class="links">
                <a href="./index.php"><div class="home">Home</div></a>
                <a href="./views/explore.php"><div class="explore">Explore</div></a>
                <a href="./views/guide.php"><div class="guide">Guide</div></a>
                <?php
                    if (isAuthenticated()){
                        echo "
                            <a href='./views/collection.php'><div class='collection'>Collection</div></a>
                            <a href='./views/community.php'><div class='community'>Community</div></a>
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
                echo '<a href="./views/loginRegister.php">Login/Register</a>';
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
                                                <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" class="settings-profile-pic img-thumbnail rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
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
                                                    <input type="text" class="form-control" id="usernameModal" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>">
                                                </div>
                                                <div class="mb-3">
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
                        <button type="button" class="btn btn-primary w-100" id="saveSettingsBtn">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <main>
        <div class="hero-section">
            <div class="hero-content">
                <div class="hero-text">
                    <h1><span class="blue-text">Bridging</span> You Directly<br>to <em>Verified</em> Knowledge</h1>
                    <p>One-click link to curated, fact-checked information from trusted sources.</p>
                    <div class="cta-container">
                        <a href="./views/explore.php" class="cta-button">Start Your Search</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="info-container">
                <div class="info-left">
                    <div class="definition-card">
                        <h2>Vero <span class="pronunciation">| ˈvɛ-roʊ |</span></h2>
                        <p class="definition">n. (Latin verus: true)</p>
                        <ol>
                            <li>Verified knowledge in its purest form</li>
                            <li>The moment you find the perfect source</li>
                        </ol>
                        <p class="quote">"In a world of speculation, choose Vero."</p>
                    </div>

                    <div class="about-card">
                        <div class="about-header">About Vero</div>
                        <h2><span class="blue-text">Bridging</span> Curiosity to Credibility</h2>
                        <p>In a world overflowing with misinformation, Vero cuts through the noise. We're not just another search engine; we're a direct pipeline to verified knowledge. Our platform connects you to rigorously vetted sources in one click, eliminating the endless hunt for trustworthy information. No algorithms. No SEO spam. Just knowledge you can stand on.</p>
                    </div>

                    <div class="why-card">
                        <div class="why-header">Why We Exist</div>
                        <ul class="why-list">
                            <li><i class="bi bi-check-circle-fill"></i> To make credible research as accessible as a Google search</li>
                            <li><i class="bi bi-check-circle-fill"></i> To combat misinformation at its source</li>
                            <li><i class="bi bi-check-circle-fill"></i> To empower decisions backed by evidence, not influencers</li>
                        </ul>
                    </div>
                </div>
                <div class="info-right">
                    <img src="./assets/img/logo2.png" alt="Vero Academic Cap" class="info-image">
                </div>
            </div>
        </div>

        <div class="team-section">
            <div class="team-container">
                <h2 class="team-title">The <span class="blue-text">Truth</span> Squad</h2>
                <p class="team-subtitle">The brilliant minds behind Vero</p>
                
                <div class="team-members">
                    <div class="team-row top-row">
                        <div class="team-member">
                            <img src="./assets/img/Villarin.png" alt="Czach Villarin" class="member-avatar">
                            <h3 class="member-name">Czach</h3>
                            <p class="member-role">Full Stack Developer</p>
                        </div>
                        
                        <div class="team-member">
                            <img src="./assets/img/Canete.png" alt="Emman Cañete" class="member-avatar">
                            <h3 class="member-name">Emman</h3>
                            <p class="member-role">Full Stack Developer</p>
                        </div>
                    </div>
                    
                    <div class="team-row bottom-row">
                        <div class="team-member">
                            <img src="./assets/img/Minoza.png" alt="Jared Miñoza" class="member-avatar">
                            <h3 class="member-name">Jared</h3>
                            <p class="member-role">Backend Developer</p>
                        </div>
                        
                        <div class="team-member">
                            <img src="./assets/img/Moramosa.png" alt="Ren Moramosa" class="member-avatar">
                            <h3 class="member-name">Ren</h3>
                            <p class="member-role">Frontend Developer</p>
                        </div>
                        
                        <div class="team-member">
                            <img src="./assets/img/Galve.png" alt="Yñaki Galve" class="member-avatar">
                            <h3 class="member-name">Yñaki</h3>
                            <p class="member-role">UI/UX Designer</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="projects-section">
            <div class="projects-container">
                <h2 class="projects-title">Featured <span class="blue-text">Projects</span></h2>
                <p class="projects-subtitle">Explore our latest research initiatives</p>
                
                <div class="projects-list">
                    <div class="project-item">
                        <div class="project-content">
                            <h3>AI-Powered Research Assistant</h3>
                            <p>An intelligent system that helps researchers analyze and synthesize academic papers efficiently.</p>
                            <div class="project-meta">
                                <span class="project-category">Artificial Intelligence</span>
                                <span class="project-date">2024</span>
                            </div>
                        </div>
                    </div>

                    <div class="project-item">
                        <div class="project-content">
                            <h3>Blockchain for Academic Publishing</h3>
                            <p>Implementing blockchain technology to ensure transparency and authenticity in academic publishing.</p>
                            <div class="project-meta">
                                <span class="project-category">Blockchain</span>
                                <span class="project-date">2024</span>
                            </div>
                        </div>
                    </div>

                    <div class="project-item">
                        <div class="project-content">
                            <h3>Data Visualization Platform</h3>
                            <p>A comprehensive tool for visualizing complex research data and findings.</p>
                            <div class="project-meta">
                                <span class="project-category">Data Science</span>
                                <span class="project-date">2024</span>
                            </div>
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
    <script src="./assets/js/userProfile.js"></script>
    <script src="./assets/js/changePassword.js"></script>
</body>
</html>