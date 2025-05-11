<?php
session_start();

require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/../controllers/userAuthHandler.php';

$searchQuery = $_GET['query'] ?? '';


if(isAuthenticated()){
    $userID = $_SESSION['userID'];
    $user = findUserByID($userID);

    
    $profilePic = $user['profile_pic'] ?? './assets/photo/Profile_Pictures/default.jpg'; // fallback if null
}

if (isset($_POST['logout'])) {
    handleSignOut();
}

if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
  $search = trim($_GET['query']);
  $searchTerm = "%" . $search . "%";

  $sql = "SELECT * FROM articles WHERE title LIKE :search OR description LIKE :search";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
  $stmt->execute();
  $results = $stmt->fetchAll();
}

?>




<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>searchResults</title>
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
        <div class='community'>Collection</div>
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
                                <form id="profilePicForm" action="./controllers/changeUserInfo.php" method="POST" enctype="multipart/form-data">
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
                            <form id="accountInfoForm" action="./controllers/changeUserInfo.php" method="POST">
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
                            <form id="passwordForm" action="./controllers/changeUserInfo.php" method="POST">
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
      <h2> Results for "<?php echo $searchQuery ?>" <span> </span></h2>
      <div class="sort-by">
        <strong>Sort by:</strong>
        <button>Recent</button>
        <button>Relevant</button>
        <button>Views</button>
        <button>Open-Access</button>
        <button>Subscription-Based</button>
      </div>

      <!-- Example Card -->
      
      <?php
        if ($results) {
          foreach ($results as $row) {
              echo "<a href='" . htmlspecialchars($row['article_link']) . "' class='article-link'>";
              echo "<article class='paper'>";
              echo "<div class='paper-info'>";
              echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
              echo "<p>Author: " . htmlspecialchars($row['author']) . "</p>";
              echo "<p class='year'>Date: " . htmlspecialchars($row['published_date']) . "</p>";
              echo "</div>";
              echo "<div class='paper-buttons'>";
              echo "<button><i class='fas fa-eye'></i> Views: 123</button>"; // You can dynamically replace 123 with actual views count
              echo "<button class='inactive'>";
              echo "<i class='fas fa-lock'></i> SB <!-- Locked icon for Subscription-Based -->";
              echo "</button>";
              echo "<button>";
              echo "<i class='fas fa-unlock'></i> OA <!-- Unlocked icon for Open-Access -->";
              echo "</button>";
              echo "</div>";
              echo "</article>";
              echo "</a>";
          }
      } else {
          echo "<p>No results found for '<strong>" . htmlspecialchars($search) . "</strong>'</p>";
      }
      ?>
    </section>
  </div>

  <script src="../assets/js/userProfile.js"></script>
    <script src="../assets/js/changePassword.js"></script>
    <script src="../assets/js/changeUserInfo.js"></script>
    
</body>
</html>
