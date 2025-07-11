<?php
session_start();

require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/../controllers/userAuthHandler.php';

$sql = "SELECT * FROM articles WHERE 1";
$params = [];

$search = $_GET['query'] ?? '';


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
if (!empty($search)) {
  $keywords = [];
  $allWords = preg_split('/\s+/', strtolower($search));

  $stopWords = ['a', 'an', 'the', 'and', 'or', 'but', 'if', 'of', 'on', 'in', 'to', 'with', 'as', 'by', 'for', 'at', 'from', 'is', 'not', 'that'];
  foreach ($allWords as $word) {
      if (!in_array($word, $stopWords) && strlen($word) > 1) {
          $keywords[] = $word;
      }
  }

  foreach ($keywords as $word) {
      $sql .= " AND (title LIKE ? OR description LIKE ?)";
      $params[] = "%$word%";
      $params[] = "%$word%";
  }
}

// Dropdown filters
if (!empty($_GET['topic'])) {
  $sql .= " AND topic = ?";
  $params[] = $_GET['topic'];
}

if (!empty($_GET['source_type'])) {
  $sql .= " AND source_type = ?";
  $params[] = $_GET['source_type'];
}

if (!empty($_GET['credibility'])) {
  $sql .= " AND credibility = ?";
  $params[] = $_GET['credibility'];
}

if (!empty($_GET['region'])) {
  $sql .= " AND region = ?";
  $params[] = $_GET['region'];
}

if (!empty($_GET['year'])) {
  $sql .= " AND YEAR(published_date) = ?";
  $params[] = $_GET['year'];
}

// Optional: Order results (e.g., most recent first)
$sql .= " ORDER BY published_date DESC";

// Prepare and execute query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();
?>




<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>searchResults</title>
  <link rel="icon" type="image/png" href="../assets/img/logo.png">
  <link rel="stylesheet" href="../assets/css/userProfile.css">
  <link rel="stylesheet" href="../assets/css/settings.css">

  <link rel="stylesheet" href="../assets/css/userDropdown.css">
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <link rel="stylesheet" href="../assets/css/global.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins" rel="stylesheet"/>
  <link href="../assets/css/searchResultsStyles.css" rel="stylesheet"/>
  <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
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
      <a href="../views/searchResults.php" class="active">
        <div class="icon"><i class="fa fa-compass fa-2x" aria-hidden="true"></i></div>
        <div class="explore">Explore</div>
      </a>
      <a href='../views/guide.php'>
        <div class="icon"><i class="fa fa-address-book fa-2x" aria-hidden="true"></i></div>
        <div class='guide'>Guide</div>
      </a>
      <?php
	if (isAuthenticated()) {
                echo '
      <a href="../views/collection.php">
        <div class="icon"><i class="fa fa-folder fa-2x" aria-hidden="true"></i></div>
        <div class="collection">Collection</div>
      </a>
      <a href="../views/community.php">
        <div class="icon"><i class="fa fa-users fa-2x" aria-hidden="true"></i></div>
        <div class="community">Community</div>
      </a> ';
}

?>

  </aside>
  
  <div class="main-container">
    <header>
    <form action="../views/searchResults.php" method="GET" class="search-form">
        <div class="search-button-container">
          <button><i class="fa fa-search fa-lg" aria-hidden="true"></i>
          </button>
        </div>
        <input name="query" type="text" placeholder="What are you searching today? We've got verified answers." value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>" />
      </form>
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


    <?php
  if ($search) {
?>
  <section class="content">
    <h2>Results for "<?php echo htmlspecialchars($search); ?>" <span></span></h2>

    <form class="sort-by" method="GET" action="searchResults.php">
      <input type="hidden" name="query" value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>">

      <strong>Sort by:</strong>

      <select name="topic" id="topic">
        <option value="">Topic</option>
        <option>Artificial Intelligence</option>
        <option>Cybersecurity</option>
        <option>Computer Networks</option>
        <option>Data Science</option>
        <option>Blockchain</option>
        <option>Computer Vision</option>
        <option>Natural Language Processing</option>
        <option>Human-Computer Interaction</option>
        <option>Cloud Computing</option>
        <option>High Energy Astrophysical Phenomena</option>
      </select>

      <select name="source_type" id="source_type">
        <option value="">Source Type</option>
        <option>Conference Paper</option>
        <option>Journal Article</option>
        <option>Preprint</option>
        <option>Thesis</option>
        <option>Technical Report</option>
        <option>White Paper</option>
        <option>Book Chapter</option>
        <option>Dissertation</option>
      </select>

      <select name="credibility" id="credibility">
        <option value="">Credibility</option>
        <option>Peer-reviewed</option>
        <option>Non-peer-reviewed</option>
        <option>High Impact</option>
        <option>Unverified</option>
      </select>

      <select name="region" id="region">
        <option value="">Region</option>
        <option>Global</option>
        <option>International</option>
        <option>Asia</option>
        <option>Europe</option>
        <option>North America</option>
        <option>South America</option>
        <option>Africa</option>
        <option>Australia</option>
      </select>

      <select name="date" id="date">
        <option value="">Date</option>
        <?php
          $currentYear = date('Y');
          for ($year = $currentYear; $year >= 2010; $year--) {
              echo "<option>$year</option>";
          }
        ?>
      </select>

      <button type="submit">Apply Filters</button>
    </form>

    <?php if (!empty($results)): ?>
      <?php foreach ($results as $article): ?>
        <div class="paper">
          <article>
            <div class="paper-info">
              <h3><?= htmlspecialchars($article['title']) ?></h3>
              <p>Author: <?= htmlspecialchars($article['author']) ?></p>
              <p class="year">Date: <?= date('F j, Y', strtotime($article['published_date'])) ?></p>
              <a href="<?= htmlspecialchars($article['article_link']) ?>" target="_blank" class="article-link">Read Article</a>
            </div>
            <div class="bookmark-container" data-article-id="<?= htmlspecialchars($article['id']) ?>">
              <i class="fa fa-bookmark bookmark-icon" aria-hidden="true"></i>
              <div class="collections-dropdown">
                <div class="dropdown-header">Add to Collection</div>
                <div class="collections-list">
                  <?php
                    // Fetch user's collections
                    if(isAuthenticated()) {
                      $collectionsStmt = $pdo->prepare("SELECT id, name FROM collections WHERE user_id = ?");
                      $collectionsStmt->execute([$_SESSION['userID']]);
                      $collections = $collectionsStmt->fetchAll();
                      
                      if(empty($collections)) {
                        echo '<div class="no-collections">No collections found</div>';
                      } else {
                        foreach($collections as $collection) {
                          echo '<div class="collection-item" data-collection-id="' . htmlspecialchars($collection['id']) . '">';
                          echo htmlspecialchars($collection['name']);
                          echo '</div>';
                        }
                      }
                    } else {
                      echo '<div class="login-prompt">Please login to bookmark</div>';
                    }
                  ?>
                </div>
              </div>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No results found for "<?= htmlspecialchars($search) ?>".</p>
    <?php endif; ?>
  </section>
<?php
  } // end if ($search)
?>

    
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <script src="../assets/js/userProfile.js"></script>
  <script src="../assets/js/changePassword.js"></script>
  <script src="../assets/js/profileAuth.js"></script>
  <script src="../assets/js/settingsModal.js"></script>
</body>
</html>
