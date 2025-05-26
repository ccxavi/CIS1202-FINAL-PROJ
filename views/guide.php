<?php
session_start();
require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/../controllers/userAuthHandler.php';

if (isAuthenticated()) {
    $userID = $_SESSION['userID'];
    $user = findUserByID($userID);
    $profilePic = $user['profile_pic'] ?? './assets/photo/Profile_Pictures/default.jpg';
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
    <title>Guide - Vero</title>
    <link rel="icon" type="image/png" href="../assets/img/logo.png">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/userDropdown.css">
    <link rel="stylesheet" href="../assets/css/guide.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>
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
            <a href='../views/guide.php' class="active">
                <div class="icon"><i class="fa fa-address-book fa-2x" aria-hidden="true"></i></div>
                <div class='guide'>Guide</div>
            </a>
            <?php if (isAuthenticated()): ?>
            <a href="../views/collection.php">
                <div class="icon"><i class="fa fa-folder fa-2x" aria-hidden="true"></i></div>
                <div class="collection">Collection</div>
            </a>
            <a href='../views/community.php'>
                <div class="icon"><i class="fa fa-users fa-2x" aria-hidden="true"></i></div>
                <div class='community'>Community</div>
            </a>
            <?php endif; ?>
        </div>
    </aside>

    <div class="main-container">
        <header>
            <div></div>
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
        </header>

        <main class="guide-content">
            <div class="guide-intro">
                <h1>Welcome to Vero!</h1>
                <p>This guide will help you navigate the site and make the most out of the academic resources available.</p>
                <p>Our website is designed to help students easily find and access academic resources such as articles, research papers, study guides, and citation tools. Whether you're working on homework, preparing for exams, or doing a research project, this platform offers tools and content that support your success.</p>
            </div>

            <div class="guide-sections">
                <button class="guide-btn" data-bs-toggle="modal" data-bs-target="#gettingStartedModal">
                    <i class="bi bi-rocket-takeoff"></i>
                    Getting Started
                </button>
                <button class="guide-btn" data-bs-toggle="modal" data-bs-target="#searchFeaturesModal">
                    <i class="bi bi-search"></i>
                    Search and Filter Features
                </button>
                <button class="guide-btn" data-bs-toggle="modal" data-bs-target="#usingResourcesModal">
                    <i class="bi bi-book"></i>
                    Using the Resources
                </button>
                <button class="guide-btn" data-bs-toggle="modal" data-bs-target="#userAccountsModal">
                    <i class="bi bi-person"></i>
                    User Accounts
                </button>
                <button class="guide-btn" data-bs-toggle="modal" data-bs-target="#accountSettingsModal">
                    <i class="bi bi-gear"></i>
                    Update Account Settings
                </button>
                <button class="guide-btn" data-bs-toggle="modal" data-bs-target="#faqModal">
                    <i class="bi bi-question-circle"></i>
                    FAQs and Troubleshooting
                </button>
                <button class="guide-btn" data-bs-toggle="modal" data-bs-target="#contactSupportModal">
                    <i class="bi bi-envelope"></i>
                    Contact/Support
                </button>
            </div>
        </main>
    </div>

    <!-- Getting Started Modal -->
    <div class="modal fade" id="gettingStartedModal" tabindex="-1" aria-labelledby="gettingStartedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="gettingStartedModalLabel">Getting Started with Vero</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>Accessing the Website</h6>
                    <p>Here are things You'll Find on the homepage.</p>

                    <h6>If you are not yet Logged In:</h6>
                    <div class="guide-section">
                        <h6>On the top left:</h6>
                        <ul>
                            <li>Home Tab - The Home Page</li>
                            <li>Explore Tab - Browse Research Categories</li>
                            <li>Guide Tab - The Guide To Using Our Website</li>
                        </ul>

                        <h6>On the top Right:</h6>
                        <ul>
                            <li>Login/ Register - Click here to sign in or create an account to access full features.</li>
                        </ul>

                        <h6>The Center of the Home Page:</h6>
                        <ul>
                            <li>The main search bar - Enter any topic or keyword to begin searching</li>
                            <li>Below the Search bar - Trending Topics all Verified by trusted sources use the eye icon to view</li>
                        </ul>

                        <h6>Bottom Right:</h6>
                        <ul>
                            <li>Our Social Media Platforms</li>
                            <li>Facebook</li>
                            <li>Github</li>
                            <li>Twitter/ X</li>
                        </ul>
                    </div>

                    <h6>Tips for First-Time Users:</h6>
                    <ul>
                        <li>Start by browsing trending topics to get inspiration.</li>
                        <li>Use the search bar to explore your specific research interest.</li>
                        <li>Register or log in to unlock full access to collections, community, and saved research.</li>
                    </ul>

                    <h6>Once you are logged in you'll find:</h6>
                    <ul>
                        <li>All of the Past Features Mentioned</li>
                        <li>A Collection Tab - A collection of all your bookmarked Resources</li>
                        <li>A Community Tab - Connects users with fellow researchers, educators, and students. It serves as a collaborative space where members can</li>
                        <li>Your Profile that has Logged In</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Features Modal -->
    <div class="modal fade" id="searchFeaturesModal" tabindex="-1" aria-labelledby="searchFeaturesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchFeaturesModalLabel">Search and Filter Features</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>How to Search</h6>
                    
                    <div class="guide-section">
                        <h6>Step 1: Go to Home Page</h6>
                        <ul>
                            <li>Go to the website and navigate to the Explore/ Search section or even the Home Page.</li>
                            <li>You'll see a search bar on the page</li>
                        </ul>

                        <h6>Step 2: Enter a Search Query</h6>
                        <ul>
                            <li>Type in any keywords related to what you're looking for.</li>
                            <li>Example: "climate change solutions"</li>
                            <li>The system automatically ignores common stop words like "the," "and," "is," etc., to improve search relevance.</li>
                            <li>Click the search icon or press Enter to start the search.</li>
                        </ul>

                        <h6>Step 3: Applying Filters (Optional)</h6>
                        <p>You can narrow down results by using the dropdown filters (usually found in the interface):</p>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Filter</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Topic</td>
                                        <td>Choose a topic/category (e.g., Health, Politics)</td>
                                    </tr>
                                    <tr>
                                        <td>Source Type</td>
                                        <td>Select the type of source (e.g., News, Journal)</td>
                                    </tr>
                                    <tr>
                                        <td>Credibility</td>
                                        <td>Filter by the article's verified credibility level</td>
                                    </tr>
                                    <tr>
                                        <td>Region</td>
                                        <td>Filter by the region where the article applies</td>
                                    </tr>
                                    <tr>
                                        <td>Year</td>
                                        <td>Choose the publication year</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h6>Step 4: View the Results</h6>
                        <ul>
                            <li>The page shows articles sorted by most recent first.</li>
                            <li>Each result displays key article info like title, description, publish date and views.</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Using the Resources Modal -->
    <div class="modal fade" id="usingResourcesModal" tabindex="-1" aria-labelledby="usingResourcesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="usingResourcesModalLabel">Using the Resources</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Vero provides verified, research-based content to support your academic and professional work. Here's what you can do on our platform:</p>

                    <div class="guide-section">
                        <h6>What Kind of Content Is Available?</h6>
                        <ul>
                            <li>Articles – In-depth research discussions.</li>
                            <li>PDFs – Downloadable and printable full texts.</li>
                            <li>Summaries – Condensed versions of research for quick reading.</li>
                            <li>Citations – Ready-to-use reference formats (APA, MLA, etc.).</li>
                        </ul>

                        <h6>How to View Full Content</h6>
                        <ul>
                            <li>Click the eye icon next to any topic.</li>
                            <li>The full content will open in a viewer without needing to leave the platform.</li>
                        </ul>

                        <h6>How to Save or Bookmark Resources</h6>
                        <ul>
                            <li>When viewing an article or PDF, click the bookmark icon.</li>
                            <li>Saved items go to your Collection tab for easy access later.</li>
                        </ul>

                        <h6>How Ratings Work</h6>
                        <ul>
                            <li>You can rate resources using a 5-star system.</li>
                            <li>Simply click on the number of stars that reflects the quality or usefulness of the resource.</li>
                        </ul>

                        <h6>How to Copy Citations</h6>
                        <ul>
                            <li>Scroll to the bottom of the resource or content viewer.</li>
                            <li>Look for the "Citation" section.</li>
                            <li>Click the Copy button to copy the formatted citation to your clipboard.</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- User Accounts Modal -->
    <div class="modal fade" id="userAccountsModal" tabindex="-1" aria-labelledby="userAccountsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userAccountsModalLabel">User Accounts and Profiles</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="guide-section">
                        <h6>How To Register</h6>
                        <ol>
                            <li>Find The Login/ Register Button and click it.</li>
                            <li>Under the Register section:
                                <ul>
                                    <li>Enter your User Name.</li>
                                    <li>Enter your Email Address.</li>
                                    <li>Enter your Password.</li>
                                    <li>Click Register.</li>
                                </ul>
                            </li>
                            <li>Upon successful registration, you will need to enter your login details again under Login.</li>
                        </ol>

                        <h6>How To Login</h6>
                        <ol>
                            <li>Go to the Login/Register page.</li>
                            <li>Under the Log In section:
                                <ul>
                                    <li>Input your Email and Password.</li>
                                    <li>Click Log In.</li>
                                </ul>
                            </li>
                            <li>If your credentials are correct, you'll be redirected to the homepage/dashboard with your session active.</li>
                        </ol>

                        <h6>Features Requiring an Account</h6>
                        <p>Only registered users can:</p>
                        <ul>
                            <li>View and access the Collection and Community sections.</li>
                            <li>Use the Account Settings to:
                                <ul>
                                    <li>Change their username or email</li>
                                    <li>Upload a profile picture</li>
                                    <li>Change their password</li>
                                </ul>
                            </li>
                            <li>See their account info (username, email) in the navigation bar.</li>
                            <li>Log out securely via the dropdown.</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Account Settings Modal -->
    <div class="modal fade" id="accountSettingsModal" tabindex="-1" aria-labelledby="accountSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="accountSettingsModalLabel">Update Account Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="guide-section">
                        <h6>Accessing Account Settings</h6>
                        <ol>
                            <li>Once logged in, click on your profile icon in the navbar.</li>
                            <li>In the dropdown menu, click Settings.</li>
                        </ol>

                        <h6>Available Settings</h6>
                        <ul>
                            <li><strong>Profile Picture:</strong> Upload a new image using the file input.</li>
                            <li><strong>Account Info:</strong> Update your username and email, then submit.</li>
                            <li><strong>Password:</strong> Change your password securely.</li>
                        </ul>

                        <h6>How to Log Out</h6>
                        <ol>
                            <li>Click on your profile icon in the navbar.</li>
                            <li>Click the Logout button in the dropdown.</li>
                            <li>Your session will end, and you will be returned to the login/register page.</li>
                        </ol>

                        <h6>Citation Guide</h6>
                        <p>The research site helps with academic sources. Here are some tips for proper citation:</p>
                        <ul>
                            <li>Use the built-in citation tools for automatic formatting</li>
                            <li>Choose between APA and MLA citation styles</li>
                            <li>Copy citations directly to your clipboard</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQs and Troubleshooting Modal -->
    <div class="modal fade" id="faqModal" tabindex="-1" aria-labelledby="faqModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="faqModalLabel">FAQs and Troubleshooting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>You may have some troubles in using the website so here are some tips and FAQs to help you in navigating the website</p>

                    <div class="guide-section">
                        <h6>1. "I can't find my topic."</h6>
                        <p>Solutions:</p>
                        <ul>
                            <li>Use broader or alternative keywords.</li>
                            <li>Check spelling and punctuation.</li>
                            <li>Explore the Trending Topics or Explore tab.</li>
                        </ul>

                        <h6>2. "Downloads aren't working."</h6>
                        <p>Solution:</p>
                        <ul>
                            <li>Ensure you're logged in.</li>
                            <li>Allow downloads and pop-ups in your browser.</li>
                            <li>Try refreshing the page or switching to a different browser.</li>
                        </ul>

                        <h6>3. "I saved a file, but now I can't find it."</h6>
                        <p>Solution:</p>
                        <ul>
                            <li>Visit the Collection tab to see your saved/bookmarked content.</li>
                            <li>Confirm you're signed into the correct account.</li>
                        </ul>

                        <h6>4. "Why can't I rate or bookmark content?"</h6>
                        <p>Solution:</p>
                        <ul>
                            <li>Make sure you're logged in.</li>
                            <li>Refresh the page if buttons don't respond.</li>
                        </ul>

                        <h6>5. "I forgot my password."</h6>
                        <p>Solution:</p>
                        <ul>
                            <li>Click "Forgot Password?" on the login page.</li>
                            <li>Follow the email reset instructions.</li>
                            <li>Still need help? Message us on Facebook or X (Twitter).</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact/Support Modal -->
    <div class="modal fade" id="contactSupportModal" tabindex="-1" aria-labelledby="contactSupportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactSupportModalLabel">Contact and Support</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Need help or have questions while using Vero? Our team is here to support you!</p>

                    <div class="guide-section">
                        <h6>Reach out to us via our social media accounts:</h6>
                        <ul>
                            <li><i class="bi bi-facebook"></i> Facebook: VeroResearchPH</li>
                            <li><i class="bi bi-twitter-x"></i> Twitter (X): @VeroResearch</li>
                            <li><i class="bi bi-github"></i> GitHub: github.com/VeroSupport</li>
                        </ul>

                        <p>You can send us a direct message on any of these platforms for assistance, suggestions, or feedback.</p>
                        <p class="text-primary">We'll get back to you as soon as possible!</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/userProfile.js"></script>
    <script src="../assets/js/modalInit.js"></script>
</body>
</html> 