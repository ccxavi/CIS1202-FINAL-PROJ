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

    
    $profilePic = $user['profile_pic'] ?? './assets/photo/Profile_Pictures/default.jpg'; // fallback if null
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
  <link rel="stylesheet" href="../assets/css/userDropdown.css">
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <link rel="stylesheet" href="../assets/css/global.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins" rel="stylesheet"/>
  <link href="../assets/css/searchResultsStyles.css" rel="stylesheet"/>
  <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">

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
      <a href="../index.php">
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
    <form action="../views/searchResults.php" method="GET" class="search-form">
        <div class="search-button-container">
          <button><i class="fa fa-search fa-lg" aria-hidden="true"></i>
          </button>
        </div>
        <input name="query" type="text" placeholder="What are you searching today? We've got verified answers." />
      </form>
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
      <h2> Results for "<?php echo $search ?>" <span> </span></h2>
      
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

        <!-- Submit button -->
        <button type="submit">Apply Filters</button>
      </form>
            
      <?php if (!empty($results)): ?>
        <?php foreach ($results as $article): ?>
            <a href="<?= htmlspecialchars($article['article_link']) ?>" class="paper" target="_blank">
                <article class="paper">
                  <div class="paper-info">
                      <h3><?= htmlspecialchars($article['title']) ?></h3>
                      <p>Author: <?= htmlspecialchars($article['author']) ?></p>
                      <p class="year">Date: <?= date('F j, Y', strtotime($article['published_date'])) ?></p>
                  </div>
                  <div class="paper-buttons">
                      <button>121<i class="fa fa-eye" aria-hidden="true"></i></button>
                      <button>
                      <i class="fa fa-bookmark" aria-hidden="true"></i>

                      </button>
                  </div>
                </article>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
      <p>No results found for "<?= htmlspecialchars($search) ?>".</p>
    <?php endif; ?>
    </section>
  </div>

  <script src="../assets/js/userProfile.js"></script>
  <script src="../assets/js/changePassword.js"></script>
  <script src="../assets/js/changeUserInfo.js"></script>
    
</body>
</html>