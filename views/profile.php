<?php
session_start();
require_once __DIR__ . '/../config/databaseConnection.php';
require_once __DIR__ . '/../controllers/userAuthHandler.php';
require_once __DIR__ . '/../controllers/CommunityController.php';

// Check if user is authenticated
if (!isAuthenticated()) {
    header('Location: loginRegister.php');
    exit();
}

$userID = $_SESSION['userID'];
$user = findUserByID($userID);
$profilePic = $user['profile_pic'] ?? './assets/photo/Profile_Pictures/default.jpg';

// Get user's posts
$posts = getUserPosts($userID);

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
    <title>User Profile - Vero</title>
    <link rel="icon" type="image/png" href="../assets/img/logo.png">
    <link rel="stylesheet" href="../assets/css/userProfile.css">
    <link rel="stylesheet" href="../assets/css/userDropdown.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
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
            <div class="auth" id="auth">
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
                </div>
            </div>
        </header>

        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-pic-container">
                    <img src=".<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" class="profile-pic">
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                    <p class="email"><?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="join-date">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                </div>
            </div>

            <div class="profile-content">
                <div class="posts-section">
                    <h2>My Posts</h2>
                    <?php if (empty($posts)): ?>
                        <p class="no-posts">You haven't made any posts yet.</p>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="post-card">
                                <div class="post-header">
                                    <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                    <span class="post-time"><?php echo timeAgo($post['created_at']); ?></span>
                                </div>
                                <div class="post-content">
                                    <?php echo htmlspecialchars($post['content']); ?>
                                </div>
                                <div class="post-stats">
                                    <span><i class="fa fa-thumbs-up"></i> <?php echo $post['reaction_count']; ?> likes</span>
                                    <span><i class="fa fa-comment"></i> <?php echo $post['comment_count']; ?> comments</span>
                                </div>
                                <div class="post-actions">
                                    <a href="community.php?post_id=<?php echo $post['id']; ?>" class="view-post-btn">View Post</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/userProfile.js"></script>
    <script src="../assets/js/modalInit.js"></script>
</body>
</html> 