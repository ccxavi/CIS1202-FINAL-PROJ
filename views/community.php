
<?php
session_start();
require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/../controllers/userAuthHandler.php';
require_once __DIR__ . '/../controllers/CommunityController.php';

// Get sort parameter, default to 'recent'
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'recent';

// Get all posts with the specified sort order
$posts = getAllPosts($sort);

if (isAuthenticated()) {
    $userID = $_SESSION['userID'];
    $user = findUserByID($userID);
    $profilePic = $user['profile_pic'] ?? '../assets/photo/Profile_Pictures/default.jpg'; // Updated path
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
    <link rel="stylesheet" href="../assets/css/userProfile.css">

    <link rel="stylesheet" href="../assets/css/userDropdown.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/community.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>
    <style>
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
      <a href="../views/collection.php">
        <div class="icon"><i class="fa fa-folder fa-2x" aria-hidden="true"></i></div>
        <div class="collection">Collection</div>
      </a>
      <a href='../views/community.php' class="active">
        <div class="icon"><i class="fa fa-users fa-2x" aria-hidden="true"></i></div>
        <div class='community'>Community</div>
      </a>
    </div>
</aside>
  
<div class="main-container">
  <header>
      <div class="addPost">
        


        <div class="add-post-section">
            <form action="../controllers/addPost.php" method="POST" class="add-post-form">
                <div class="mb-3 input-post">
                    
                    <input type="text" class="form-control" name="title" placeholder="What's on your mind?" required>
                    <textarea class="form-control" name="content" rows="3" placeholder="Share your thoughts in detail..." required></textarea>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Publish</button>
                </div>
            </form>
        </div>
      </div>
      
      <div class="auth" id="auth">
        <?php
            if (isAuthenticated()) {
                ?>
                <div class="user-dropdown-container">
                    <div class="user-dropdown-toggle">
                        <img src=".<?php echo htmlspecialchars($profilePic); ?>" alt="Profile">
                        <div class="user-info">
                            <div class="username"><?php echo htmlspecialchars($user['username']); ?></div>
                            <div class="user-role"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <i class="bi bi-chevron-down"></i>
                    </div>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-section text-center">
                            <img src=".<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" class="settings-profile-pic" style="width: 64px; height: 64px; margin-bottom: 8px;">
                            <div><strong><?php echo htmlspecialchars($user['username']); ?></strong></div>
                            <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
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
                </div>
                <?php
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
                                                <img src=".<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" class="settings-profile-pic img-thumbnail rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="document.getElementById('settingsModal').classList.remove('show'); document.querySelector('.modal-backdrop').remove();">Close</button>
                        <button type="button" class="btn btn-primary" id="saveSettingsBtn" onclick="setTimeout(function() { document.getElementById('settingsModal').classList.remove('show'); document.querySelector('.modal-backdrop').remove(); }, 300);">Save Changes</button>
                    </div>
                </div>
            </div>
      </div>
  </header>

    <div class="allPosts">
        <div class="sort-buttons">
            <a href="?sort=recent" class="sort-btn <?php echo ($sort === 'recent' || $sort === '') ? 'active' : ''; ?>">Recent</a>
            <a href="?sort=oldest" class="sort-btn <?php echo ($sort === 'oldest') ? 'active' : ''; ?>">Oldest</a>
        </div>
        
        <?php foreach ($posts as $post): ?>
            <div class="post-card" id="post-<?php echo $post['id']; ?>">
                <div class="post-card-content">
                    <div class="post-left">
                        <img src=".<?php echo htmlspecialchars($post['profile_pic']); ?>" alt="Profile" class="post-profile-pic">
                    </div>
                    <div class="post-content">
                        <div class="post-header">
                            <span class="post-author"><?php echo htmlspecialchars($post['username']); ?></span>
                            <span class="post-time"><?php echo $post['time_ago']; ?></span>
                        </div>
                        <div class="post-container-text">
                            <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p class="post-text"><?php echo htmlspecialchars($post['content']); ?></p>
                        </div>
                    </div>
                    <div class="post-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="like_post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit">
                                <i class="fa fa-thumbs-up fa-lg" aria-hidden="true"></i>
                                <?php echo $post['reaction_count']; ?>
                            </button>
                        </form>
                        <button type="button" class="comment-toggle" data-post-id="<?php echo $post['id']; ?>">
                            <i class="fa fa-comment fa-lg" aria-hidden="true"></i>
                            <?php echo $post['comment_count']; ?>
                        </button>
                    </div>
                </div>
                <div class="blue-decoration"></div>
            </div>
            
            <!-- Comment Section (Hidden by default) -->
            <div class="comment-section" id="comments-<?php echo $post['id']; ?>" style="display: none;">
                <div class="comments-container">
                    <?php 
                    $comments = getCommentsForPost($post['id']);
                    foreach ($comments as $comment): 
                    ?>
                        <div class="comment" id="comment-<?php echo $comment['id']; ?>">
                            <div class="comment-header">
                                <img src=".<?php echo htmlspecialchars($comment['profile_pic']); ?>" alt="Profile" class="comment-profile-pic">
                                <div class="comment-meta">
                                    <div class="comment-author-time">
                                        <span class="comment-author"><?php echo htmlspecialchars($comment['username']); ?></span>
                                        <span class="comment-time"><?php echo Comment::timeAgo($comment['created_at']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="comment-body">
                                <p><?php echo htmlspecialchars($comment['content']); ?></p>
                            </div>
                            <div class="comment-actions">
                                <?php if (isset($_SESSION['userID'])): ?>
                                <form method="POST" class="comment-like-form">
                                    <input type="hidden" name="like_comment_id" value="<?php echo $comment['id']; ?>">
                                    <button type="button" class="comment-like-btn" data-comment-id="<?php echo $comment['id']; ?>">
                                        <i class="fa fa-thumbs-up"></i>
                                        <span class="like-count"><?php echo (isset($comment['likes_count']) ? $comment['likes_count'] : 0); ?></span>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <button type="button" class="reply-toggle" data-comment-id="<?php echo $comment['id']; ?>">
                                    Reply <?php if($comment['reply_count'] > 0): ?>(<?php echo $comment['reply_count']; ?>)<?php endif; ?>
                                </button>
                            </div>
                            
                            <!-- Reply Form (Hidden by default) -->
                            <div class="reply-form-container" id="reply-form-<?php echo $comment['id']; ?>" style="display: none;">
                                <form method="POST" class="reply-form">
                                    <input type="hidden" name="comment_post_id" value="<?php echo $post['id']; ?>">
                                    <input type="hidden" name="comment_parent_id" value="<?php echo $comment['id']; ?>">
                                    <textarea name="comment_content" placeholder="Write a reply..." required></textarea>
                                    <button type="submit">Reply</button>
                                </form>
                            </div>
                            
                            <!-- Replies (Hidden by default) -->
                            <div class="replies-container" id="replies-<?php echo $comment['id']; ?>" style="display: none;">
                                <?php 
                                $replies = getCommentReplies($comment['id']);
                                foreach ($replies as $reply): 
                                ?>
                                    <div class="reply">
                                        <div class="comment-header">
                                            <img src=".<?php echo htmlspecialchars($reply['profile_pic']); ?>" alt="Profile" class="comment-profile-pic">
                                            <div class="comment-meta">
                                                <div class="comment-author-time">
                                                    <span class="comment-author"><?php echo htmlspecialchars($reply['username']); ?></span>
                                                    <span class="comment-time"><?php echo Comment::timeAgo($reply['created_at']); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="comment-body">
                                            <p><?php echo htmlspecialchars($reply['content']); ?></p>
                                        </div>
                                        <div class="comment-actions">
                                            <?php if (isset($_SESSION['userID'])): ?>
                                            <form method="POST" class="comment-like-form">
                                                <input type="hidden" name="like_comment_id" value="<?php echo $reply['id']; ?>">
                                                <button type="button" class="comment-like-btn" data-comment-id="<?php echo $reply['id']; ?>">
                                                    <i class="fa fa-thumbs-up"></i>
                                                    <span class="like-count"><?php echo (isset($reply['likes_count']) ? $reply['likes_count'] : 0); ?></span>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Comment Form -->
                <div class="comment-form-container">
                    <form method="POST" class="comment-form">
                        <input type="hidden" name="comment_post_id" value="<?php echo $post['id']; ?>">
                        <textarea name="comment_content" placeholder="Write a comment..." required></textarea>
                        <button type="submit">Comment</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous"></script>
  <script src="../assets/js/userProfile.js"></script>
  <script src="../assets/js/changePassword.js"></script>
  <script src="../assets/js/changeUserInfo.js"></script>
<script src="../assets/js/community.js"></script>
<script src="../assets/js/modalInit.js"></script>
<script>
// Additional script to ensure settings modal works properly
document.addEventListener('DOMContentLoaded', function() {
  // Direct handler for settings button
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
});
</script>
</body>
</html>
